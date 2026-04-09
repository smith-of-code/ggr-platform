<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;

use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsProfileDocument;
use App\Models\Lms\LmsProfileDocumentReplaceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(LmsEvent $event): Response
    {
        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrCreate(
                ['user_id' => $user->id, 'lms_event_id' => $event->id],
                []
            );

        $profile->load('documents');

        $pendingDocumentReplaceRequests = LmsProfileDocumentReplaceRequest::where('lms_profile_id', $profile->id)
            ->where('status', LmsProfileDocumentReplaceRequest::STATUS_PENDING)
            ->orderBy('created_at')
            ->get(['type', 'user_comment', 'created_at']);

        $socialAccounts = $user->socialAccounts()
            ->get(['provider', 'created_at'])
            ->keyBy('provider');

        $enrollmentTemplates = [
            ['key' => 'management', 'label' => 'Управление муниципальными проектами'],
            ['key' => 'guide', 'label' => 'Гид-экскурсовод промышленного туризма'],
            ['key' => 'excursion', 'label' => 'Экскурсионная деятельность'],
            ['key' => 'entrepreneurial', 'label' => 'Управление предпринимательскими проектами'],
        ];

        return Inertia::render('Lms/Profile/Edit', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'profile' => $profile,
            'user' => $user->only(['name', 'last_name', 'first_name', 'patronymic', 'email', 'phone']),
            'socialAccounts' => $socialAccounts,
            'isProfileComplete' => $profile->isProfileComplete(),
            'missingFields' => $profile->getMissingFields(),
            'documentTypes' => LmsProfileDocument::TYPES,
            'documentTypesWithTemplate' => LmsProfileDocument::TYPES_WITH_TEMPLATE,
            'enrollmentTemplates' => $enrollmentTemplates,
            'pendingDocumentReplaceRequests' => $pendingDocumentReplaceRequests,
        ]);
    }

    public function storeDocumentReplaceRequest(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(LmsProfileDocument::TYPES)],
            'user_comment' => ['required', 'string', 'max:5000'],
        ]);

        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $document = $profile->documents()->where('type', $validated['type'])->first();
        if (! $document || ! $document->isLockedForParticipant()) {
            return redirect()->back()->withErrors([
                'replace_request' => 'Замена доступна только для подтверждённых документов.',
            ]);
        }

        $exists = LmsProfileDocumentReplaceRequest::where('lms_profile_id', $profile->id)
            ->where('type', $validated['type'])
            ->where('status', LmsProfileDocumentReplaceRequest::STATUS_PENDING)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors([
                'replace_request' => 'Заявка на этот тип документа уже отправлена.',
            ]);
        }

        LmsProfileDocumentReplaceRequest::create([
            'lms_profile_id' => $profile->id,
            'type' => $validated['type'],
            'user_comment' => $validated['user_comment'],
            'status' => LmsProfileDocumentReplaceRequest::STATUS_PENDING,
        ]);

        return redirect()->back()->with('success', 'Заявка на замену документа отправлена.');
    }

    public function update(Request $request, LmsEvent $event): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'last_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'project_description' => ['nullable', 'string', 'max:5000'],
            'preferred_channel' => ['nullable', Rule::in(['telegram', 'max'])],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $userFields = [];
        foreach (['last_name', 'first_name', 'patronymic', 'email'] as $field) {
            if (array_key_exists($field, $validated)) {
                $userFields[$field] = $validated[$field];
                unset($validated[$field]);
            }
        }
        if ($userFields) {
            $user->update($userFields);
        }

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $disk = config('filesystems.upload_disk');
            $path = $request->file('avatar')->store('avatars', $disk);
            $avatarUrl = Storage::disk($disk)->url($path);
        }

        unset($validated['avatar']);

        $profile->update($validated);

        if ($avatarUrl) {
            $profile->update(['avatar' => $avatarUrl]);
        }

        $profile->refresh();
        $profile->load('documents');

        if ($profile->isProfileComplete()) {
            return redirect()->back()->with('profile_completed', true);
        }

        return redirect()->back();
    }

    public function uploadDocument(Request $request, LmsEvent $event): RedirectResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(LmsProfileDocument::TYPES)],
            'file' => ['required', 'file', 'max:51200', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $existing = $profile->documents()->where('type', $request->type)->first();
        if ($existing && $existing->isLockedForParticipant()) {
            return redirect()->back()->withErrors([
                'file' => 'Этот документ подтверждён модератором. Для изменения обратитесь в поддержку.',
            ]);
        }

        $disk = config('filesystems.upload_disk');
        if ($existing && $existing->hasFile()) {
            Storage::disk($disk)->delete($existing->file_path);
        }

        $path = $request->file('file')->store('profile-documents', $disk);

        $profile->documents()->updateOrCreate(
            ['type' => $request->type],
            [
                'file_path' => $path,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'status' => LmsProfileDocument::STATUS_PENDING_REVIEW,
                'admin_comment' => null,
                'reviewed_at' => null,
            ]
        );

        $profile->refresh();
        $profile->load('documents');

        if ($profile->isProfileComplete()) {
            return redirect()->back()->with('profile_completed', true);
        }

        return redirect()->back();
    }

    public function deleteDocument(Request $request, LmsEvent $event, LmsProfileDocument $document): RedirectResponse
    {
        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($document->lms_profile_id !== $profile->id) {
            abort(403);
        }

        if ($document->isLockedForParticipant()) {
            return redirect()->back()->withErrors([
                'file' => 'Подтверждённый документ можно удалить только через поддержку.',
            ]);
        }

        $disk = config('filesystems.upload_disk');
        if ($document->hasFile()) {
            Storage::disk($disk)->delete($document->file_path);
        }
        $document->delete();

        return redirect()->back();
    }

    public function downloadTemplate(LmsEvent $event, string $type)
    {
        $enrollmentTemplates = [
            'enrollment_management' => ['file' => 'management_municipal_projects.doc', 'name' => 'Заявление_Управление_муниципальными_проектами.doc'],
            'enrollment_guide' => ['file' => 'industrial_tourism_guide.doc', 'name' => 'Заявление_Гид_промышленного_туризма.doc'],
            'enrollment_excursion' => ['file' => 'excursion_activity.doc', 'name' => 'Заявление_Экскурсионная_деятельность.doc'],
            'enrollment_entrepreneurial' => ['file' => 'entrepreneurial_projects.doc', 'name' => 'Заявление_Управление_предпринимательскими_проектами.doc'],
        ];

        if (isset($enrollmentTemplates[$type])) {
            $tpl = $enrollmentTemplates[$type];
            $templatePath = resource_path("templates/profile/enrollment/{$tpl['file']}");

            if (! file_exists($templatePath)) {
                abort(404, 'Шаблон пока не загружен');
            }

            return response()->download($templatePath, $tpl['name']);
        }

        if (! in_array($type, LmsProfileDocument::TYPES_WITH_TEMPLATE)) {
            abort(404);
        }

        $templatePath = resource_path("templates/profile/{$type}.docx");

        if (! file_exists($templatePath)) {
            abort(404, 'Шаблон пока не загружен');
        }

        $names = [
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие_на_обработку_ПД.docx',
        ];

        return response()->download($templatePath, $names[$type] ?? "{$type}.docx");
    }
}
