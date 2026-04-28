<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;

use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsProfileDocument;
use App\Models\Lms\LmsProfileDocumentReplaceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

        $profile->load(['documents', 'lmsRole:id,name,slug']);
        $canGamificationAdminAccessFromProfile = LmsProfile::isGamificationPointsOnlyProfile($profile);

        $pendingDocumentReplaceRequests = LmsProfileDocumentReplaceRequest::where('lms_profile_id', $profile->id)
            ->where('status', LmsProfileDocumentReplaceRequest::STATUS_PENDING)
            ->orderBy('created_at')
            ->get(['type', 'user_comment', 'created_at']);

        $socialAccounts = $user->socialAccounts()
            ->get(['provider', 'created_at'])
            ->keyBy('provider');

        $programFacultyOptions = LmsCourseEnrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'enrolled', 'in_progress', 'completed'])
            ->whereHas('course', fn ($q) => $q->where('lms_event_id', $event->id))
            ->with('course:id,title,faculties')
            ->orderBy('created_at')
            ->get()
            ->map(function (LmsCourseEnrollment $enrollment) {
                $course = $enrollment->course;
                $faculties = collect($course ? $course->faculties : [])
                    ->filter(fn ($item) => is_string($item))
                    ->map(fn (string $item) => trim($item))
                    ->filter(fn (string $item) => $item !== '')
                    ->unique()
                    ->values()
                    ->all();

                if ($faculties === []) {
                    return null;
                }

                return [
                    'enrollment_id' => $enrollment->id,
                    'course_title' => $course ? $course->title : 'Программа',
                    'faculty' => $enrollment->faculty,
                    'faculties' => $faculties,
                ];
            })
            ->filter()
            ->values();

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
            'programFacultyOptions' => $programFacultyOptions,
            'pendingDocumentReplaceRequests' => $pendingDocumentReplaceRequests,
            'canGamificationAdminAccessFromProfile' => $canGamificationAdminAccessFromProfile,
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
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'project_description' => ['nullable', 'string', 'max:5000'],
            'preferred_channel' => ['nullable', Rule::in(['telegram', 'max'])],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'program_faculties' => ['nullable', 'array'],
            'program_faculties.*' => ['nullable', 'string', 'max:120'],
        ], [
            'email.unique' => 'Этот адрес электронной почты уже используется.',
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
        if ($request->filled('avatar_url')) {
            $avatarUrl = $request->input('avatar_url');
        } elseif ($request->hasFile('avatar')) {
            $disk = config('filesystems.upload_disk');
            $path = $request->file('avatar')->store('avatars', $disk);
            $avatarUrl = Storage::disk($disk)->url($path);
        }

        unset($validated['avatar'], $validated['avatar_url']);
        $programFaculties = $validated['program_faculties'] ?? [];
        unset($validated['program_faculties']);

        $profile->update($validated);

        if ($avatarUrl) {
            $profile->update(['avatar' => $avatarUrl]);
        }

        if (is_array($programFaculties) && $programFaculties !== []) {
            $enrollmentIds = collect(array_keys($programFaculties))
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->values()
                ->all();

            $enrollments = LmsCourseEnrollment::query()
                ->whereIn('id', $enrollmentIds)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'enrolled', 'in_progress', 'completed'])
                ->whereHas('course', fn ($q) => $q->where('lms_event_id', $event->id))
                ->with('course:id,faculties')
                ->get()
                ->keyBy('id');

            $errors = [];

            foreach ($programFaculties as $enrollmentIdRaw => $facultyRaw) {
                $enrollmentId = (int) $enrollmentIdRaw;
                /** @var LmsCourseEnrollment|null $enrollment */
                $enrollment = $enrollments->get($enrollmentId);
                if (! $enrollment) {
                    continue;
                }

                $course = $enrollment->course;
                $allowedFaculties = collect($course ? $course->faculties : [])
                    ->filter(fn ($item) => is_string($item))
                    ->map(fn (string $item) => trim($item))
                    ->filter(fn (string $item) => $item !== '')
                    ->unique()
                    ->values();

                if ($allowedFaculties->isEmpty()) {
                    continue;
                }

                $faculty = is_string($facultyRaw) ? trim($facultyRaw) : '';
                if ($faculty === '') {
                    $enrollment->update(['faculty' => null]);
                    continue;
                }

                if (! $allowedFaculties->contains($faculty)) {
                    $errors["program_faculties.{$enrollmentId}"] = 'Выбран недоступный факультет для программы.';
                    continue;
                }

                $enrollment->update(['faculty' => $faculty]);
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
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
            'file' => ['required_without:file_url', 'nullable', 'file', 'max:51200', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'file_url' => ['required_without:file', 'nullable', 'string', 'url'],
            'file_name' => ['nullable', 'string', 'max:255'],
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

        if ($request->filled('file_url')) {
            $filePath = $request->input('file_url');
            $originalName = $request->input('file_name', basename(parse_url($filePath, PHP_URL_PATH) ?: 'document'));
        } else {
            $filePath = $request->file('file')->store('profile-documents', $disk);
            $originalName = $request->file('file')->getClientOriginalName();
        }

        $profile->documents()->updateOrCreate(
            ['type' => $request->type],
            [
                'file_path' => $filePath,
                'original_name' => $originalName,
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
