<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsForm;
use App\Models\Lms\LmsFormField;
use App\Models\Lms\LmsFormSubmission;
use App\Models\Lms\LmsInvitation;
use App\Models\Lms\LmsProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FormController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $forms = LmsForm::where('lms_event_id', $event->id)
            ->withCount('submissions')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return Inertia::render('Lms/Admin/Forms/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'forms' => $forms,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        return Inertia::render('Lms/Admin/Forms/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'form' => null,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $this->validateForm($request);
        $validated['lms_event_id'] = $event->id;
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['title']) . '-' . Str::random(6);

        $form = LmsForm::create($validated);
        $this->syncFields($form, $validated['fields'] ?? []);

        return redirect()->route('lms.admin.forms.index', $event)->with('success', 'Форма создана');
    }

    public function edit(LmsEvent $event, LmsForm $form): Response
    {
        $this->ensureFormBelongsToEvent($form, $event);
        $form->load('fields');

        return Inertia::render('Lms/Admin/Forms/Form', [
            'event' => $event->only(['id', 'slug', 'title']),
            'form' => $form,
        ]);
    }

    public function update(Request $request, LmsEvent $event, LmsForm $form): RedirectResponse
    {
        $this->ensureFormBelongsToEvent($form, $event);
        $validated = $this->validateForm($request);
        $validated['slug'] = $validated['slug'] ?: $form->slug;

        $form->update($validated);
        $this->syncFields($form, $validated['fields'] ?? []);

        return redirect()->route('lms.admin.forms.index', $event)->with('success', 'Форма обновлена');
    }

    public function destroy(LmsEvent $event, LmsForm $form): RedirectResponse
    {
        $this->ensureFormBelongsToEvent($form, $event);
        $form->delete();

        return redirect()->route('lms.admin.forms.index', $event)->with('success', 'Форма удалена');
    }

    public function stats(LmsEvent $event, LmsForm $form): Response
    {
        $this->ensureFormBelongsToEvent($form, $event);
        $form->load('fields');

        $submissions = $form->submissions()
            ->with(['responses.field', 'user:id,name,email'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $fieldStats = [];
        foreach ($form->fields as $field) {
            if (in_array($field->type, ['select', 'radio', 'checkbox', 'rating'])) {
                $counts = DB::table('lms_form_responses')
                    ->where('lms_form_field_id', $field->id)
                    ->select('value', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('value')
                    ->orderByDesc('cnt')
                    ->get();
                $fieldStats[$field->id] = $counts;
            }
        }

        $embedUrl = url("/forms/{$form->slug}");
        $widgetJsUrl = url('/js/form-widget.js');
        $embedScript = '<script src="' . $widgetJsUrl . '" data-form="' . e($form->slug) . '"></script>';
        $embedIframe = '<iframe src="' . $embedUrl . '" width="100%" height="800" frameborder="0" style="border:none;"></iframe>';

        return Inertia::render('Lms/Admin/Forms/Stats', [
            'event' => $event->only(['id', 'slug', 'title']),
            'form' => $form,
            'submissions' => $submissions,
            'fieldStats' => $fieldStats,
            'embedUrl' => $embedUrl,
            'embedScript' => $embedScript,
            'embedIframe' => $embedIframe,
        ]);
    }

    public function createUsersFromSubmissions(Request $request, LmsEvent $event, LmsForm $form): RedirectResponse
    {
        $this->ensureFormBelongsToEvent($form, $event);

        $request->validate([
            'submission_ids' => ['required', 'array', 'min:1'],
            'submission_ids.*' => ['integer', 'exists:lms_form_submissions,id'],
        ]);

        $submissions = LmsFormSubmission::whereIn('id', $request->submission_ids)
            ->where('lms_form_id', $form->id)
            ->where('user_created', false)
            ->with('responses.field')
            ->get();

        $created = 0;
        foreach ($submissions as $sub) {
            $data = $this->extractUserDataFromSubmission($form, $sub);
            if (!$data['email']) {
                continue;
            }

            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $data['name'] ?: $data['email'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Str::random(32),
                ]);
            }

            $profile = LmsProfile::firstOrCreate(
                ['lms_event_id' => $event->id, 'user_id' => $user->id],
                ['role' => 'participant', 'status' => 'active']
            );

            $invitation = LmsInvitation::create([
                'lms_event_id' => $event->id,
                'token' => LmsInvitation::generateToken(),
                'label' => 'Из формы: ' . $form->title,
                'is_active' => true,
                'max_uses' => 1,
                'created_by' => auth()->id(),
            ]);

            $sub->update(['user_created' => true, 'user_id' => $user->id]);
            $created++;
        }

        return redirect()->back()->with('success', "Создано пользователей: {$created}");
    }

    private function extractUserDataFromSubmission(LmsForm $form, LmsFormSubmission $sub): array
    {
        $data = ['name' => '', 'email' => '', 'phone' => '', 'position' => ''];

        foreach ($sub->responses as $response) {
            $fieldKey = $response->field?->key;
            if ($fieldKey === $form->fio_field_key) {
                $data['name'] = $response->value;
            }
            if ($fieldKey === $form->email_field_key) {
                $data['email'] = $response->value;
            }
            if ($fieldKey === $form->phone_field_key) {
                $data['phone'] = $response->value;
            }
            if ($fieldKey === $form->position_field_key) {
                $data['position'] = $response->value;
            }
        }

        return $data;
    }

    private function validateForm(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_anonymous' => ['boolean'],
            'allow_embed' => ['boolean'],
            'create_users' => ['boolean'],
            'fio_field_key' => ['nullable', 'string', 'max:100'],
            'email_field_key' => ['nullable', 'string', 'max:100'],
            'phone_field_key' => ['nullable', 'string', 'max:100'],
            'position_field_key' => ['nullable', 'string', 'max:100'],
            'thank_you_message' => ['nullable', 'string'],
            'fields' => ['nullable', 'array'],
            'fields.*.key' => ['required', 'string', 'max:100'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:text,textarea,email,phone,number,select,radio,checkbox,date,rating'],
            'fields.*.required' => ['boolean'],
            'fields.*.placeholder' => ['nullable', 'string'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*' => ['string'],
        ]);
    }

    private function syncFields(LmsForm $form, array $fields): void
    {
        $form->fields()->delete();

        foreach ($fields as $index => $field) {
            LmsFormField::create([
                'lms_form_id' => $form->id,
                'key' => $field['key'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'] ?? false,
                'placeholder' => $field['placeholder'] ?? null,
                'options' => $field['options'] ?? null,
                'position' => $field['position'] ?? $index,
            ]);
        }
    }

    private function ensureFormBelongsToEvent(LmsForm $form, LmsEvent $event): void
    {
        if ($form->lms_event_id !== $event->id) {
            abort(404);
        }
    }
}
