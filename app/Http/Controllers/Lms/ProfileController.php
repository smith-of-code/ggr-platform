<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsProfileDocument;
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

        $socialAccounts = $user->socialAccounts()
            ->get(['provider', 'created_at'])
            ->keyBy('provider');

        $cities = City::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Lms/Profile/Edit', [
            'event' => $event->only(['id', 'slug', 'title', 'menu_config']),
            'profile' => $profile,
            'user' => $user->only(['name', 'last_name', 'first_name', 'patronymic', 'email', 'phone']),
            'socialAccounts' => $socialAccounts,
            'cities' => $cities,
            'isProfileComplete' => $profile->isProfileComplete(),
            'documentTypes' => LmsProfileDocument::TYPES,
            'documentTypesWithTemplate' => LmsProfileDocument::TYPES_WITH_TEMPLATE,
        ]);
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
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
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

        return redirect()->back();
    }

    public function uploadDocument(Request $request, LmsEvent $event): RedirectResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(LmsProfileDocument::TYPES)],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $user = auth()->user();
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $disk = config('filesystems.upload_disk');
        $path = $request->file('file')->store('profile-documents', $disk);

        $profile->documents()->updateOrCreate(
            ['type' => $request->type],
            [
                'file_path' => $path,
                'original_name' => $request->file('file')->getClientOriginalName(),
            ]
        );

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

        $disk = config('filesystems.upload_disk');
        Storage::disk($disk)->delete($document->file_path);
        $document->delete();

        return redirect()->back();
    }

    public function downloadTemplate(LmsEvent $event, string $type)
    {
        if (! in_array($type, LmsProfileDocument::TYPES_WITH_TEMPLATE)) {
            abort(404);
        }

        $templatePath = resource_path("templates/profile/{$type}.docx");

        if (! file_exists($templatePath)) {
            abort(404, 'Шаблон пока не загружен');
        }

        $names = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление_на_зачисление.docx',
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие_на_обработку_ПД.docx',
        ];

        return response()->download($templatePath, $names[$type] ?? "{$type}.docx");
    }
}
