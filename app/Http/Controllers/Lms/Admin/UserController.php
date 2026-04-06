<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Mail\InvitationMail;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsInvitation;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsProfileDocument;
use App\Models\Lms\LmsRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $query = $event->profiles()->with(['user:id,name,last_name,first_name,patronymic,email,phone', 'lmsRole:id,name,slug']);

        if ($request->filled('role_id')) {
            $query->where('lms_role_id', $request->role_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', 'ilike', "%{$request->city}%");
        }

        if ($request->filled('group')) {
            $query->whereIn('user_id', function ($q) use ($request) {
                $q->select('user_id')
                    ->from('lms_group_members')
                    ->where('lms_group_id', $request->group);
            });
        }

        $profiles = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $roles = LmsRole::where('lms_event_id', $event->id)->orderBy('name')->get(['id', 'name', 'slug']);
        $groups = $event->groups()->orderBy('title')->get(['id', 'title']);
        $courses = LmsCourse::where('lms_event_id', $event->id)->orderBy('title')->get(['id', 'title']);

        $invitations = LmsInvitation::where('lms_event_id', $event->id)
            ->with(['role:id,name', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $cities = LmsProfile::where('lms_event_id', $event->id)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return Inertia::render('Lms/Admin/Users/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profiles' => $profiles,
            'roles' => $roles,
            'groups' => $groups,
            'courses' => $courses,
            'cities' => $cities,
            'filters' => $request->only(['role_id', 'group', 'search', 'status', 'city']),
            'invitations' => $invitations,
            'directionLabels' => LmsProfile::DIRECTION_LABELS,
            'facultyLabels' => LmsProfile::FACULTY_LABELS,
        ]);
    }

    public function create(LmsEvent $event): Response
    {
        $roles = LmsRole::where('lms_event_id', $event->id)->orderBy('name')->get(['id', 'name', 'slug']);
        $courses = LmsCourse::where('lms_event_id', $event->id)->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Users/Create', [
            'event' => $event->only(['id', 'slug', 'title']),
            'roles' => $roles,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'last_name'  => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'position'   => ['nullable', 'string', 'max:255'],
            'role_id'    => ['nullable', 'exists:lms_roles,id'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:lms_courses,id'],
            'password'   => ['nullable', 'string', 'min:6'],
        ]);

        $fullName = trim($validated['last_name'] . ' ' . $validated['first_name']);
        $password = $validated['password'] ?? Str::random(10);

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            $user = User::create([
                'name'       => $fullName,
                'last_name'  => $validated['last_name'],
                'first_name' => $validated['first_name'],
                'patronymic' => $validated['patronymic'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'],
                'password'   => $password,
            ]);
        } else {
            $user->update([
                'name'       => $fullName,
                'last_name'  => $validated['last_name'],
                'first_name' => $validated['first_name'],
                'patronymic' => $validated['patronymic'],
                'phone'      => $validated['phone'] ?? $user->phone,
            ]);
        }

        $profile = LmsProfile::firstOrCreate(
            ['user_id' => $user->id, 'lms_event_id' => $event->id],
            [
                'role' => 'participant',
                'lms_role_id' => $validated['role_id'] ?? null,
                'position' => $validated['position'],
                'phone' => $validated['phone'],
            ]
        );
        $profile->update([
            'lms_role_id' => $validated['role_id'] ?? $profile->lms_role_id,
            'position' => $validated['position'] ?? $profile->position,
        ]);

        if (!empty($validated['course_ids'])) {
            foreach ($validated['course_ids'] as $courseId) {
                LmsCourseEnrollment::firstOrCreate(
                    ['lms_course_id' => $courseId, 'user_id' => $user->id],
                    ['status' => 'enrolled']
                );
                DB::table('lms_course_assignments')->updateOrInsert(
                    ['lms_course_id' => $courseId, 'user_id' => $user->id],
                    ['assigned_by' => auth()->id()]
                );
            }
        }

        return redirect()->route('lms.admin.users.index', $event)
            ->with('success', "Пользователь {$fullName} добавлен. Пароль: {$password}");
    }

    public function show(LmsEvent $event, User $user): Response
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->with(['lmsRole:id,name,slug', 'documents'])
            ->firstOrFail();

        $profile->load('user:id,name,last_name,first_name,patronymic,email,phone,created_at');

        $enrollments = LmsCourseEnrollment::where('user_id', $user->id)
            ->whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->with('course:id,title')
            ->get();

        $roles = LmsRole::where('lms_event_id', $event->id)->orderBy('name')->get(['id', 'name', 'slug']);
        $courses = LmsCourse::where('lms_event_id', $event->id)->orderBy('title')->get(['id', 'title']);

        $typeLabels = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление на зачисление',
            LmsProfileDocument::TYPE_SNILS => 'СНИЛС',
            LmsProfileDocument::TYPE_DIPLOMA => 'Диплом',
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие на обработку ПД',
            LmsProfileDocument::TYPE_NAME_CHANGE_CERTIFICATE => 'Свидетельство о смене фамилии',
        ];

        $documents = $profile->documents->map(fn($doc) => [
            'id' => $doc->id,
            'type' => $doc->type,
            'type_label' => $typeLabels[$doc->type] ?? $doc->type,
            'original_name' => $doc->original_name,
            'created_at' => $doc->created_at,
        ]);

        return Inertia::render('Lms/Admin/Users/Show', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profile' => $profile,
            'enrollments' => $enrollments,
            'roles' => $roles,
            'directionLabels' => LmsProfile::DIRECTION_LABELS,
            'facultyLabels' => LmsProfile::FACULTY_LABELS,
            'courses' => $courses,
            'documents' => $documents,
        ]);
    }

    public function update(Request $request, LmsEvent $event, User $user): RedirectResponse
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'last_name'  => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'position'   => ['nullable', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'project_description' => ['nullable', 'string', 'max:2000'],
            'role_id'    => ['nullable', 'exists:lms_roles,id'],
            'role'       => ['nullable', 'string', 'in:participant,curator,leader,admin'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:lms_courses,id'],
        ]);

        $lastName = $validated['last_name'] ?? $user->last_name;
        $firstName = $validated['first_name'] ?? $user->first_name;
        $fullName = trim("{$lastName} {$firstName}");

        $user->update([
            'name'       => $fullName ?: $user->name,
            'last_name'  => $lastName,
            'first_name' => $firstName,
            'patronymic' => $validated['patronymic'] ?? $user->patronymic,
            'phone'      => $validated['phone'] ?? $user->phone,
        ]);

        $profile->update([
            'role' => $validated['role'] ?? $profile->role,
            'lms_role_id' => $validated['role_id'] ?? $profile->lms_role_id,
            'position' => $validated['position'] ?? $profile->position,
            'city' => $validated['city'] ?? $profile->city,
            'phone' => $validated['phone'] ?? $profile->phone,
            'organization' => $validated['organization'] ?? $profile->organization,
            'project_description' => $validated['project_description'] ?? $profile->project_description,
        ]);

        if (isset($validated['course_ids'])) {
            $existingIds = LmsCourseEnrollment::where('user_id', $user->id)
                ->whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
                ->pluck('lms_course_id')->toArray();

            foreach ($validated['course_ids'] as $courseId) {
                LmsCourseEnrollment::firstOrCreate(
                    ['lms_course_id' => $courseId, 'user_id' => $user->id],
                    ['status' => 'enrolled']
                );
            }
        }

        return redirect()->back()->with('success', 'Профиль обновлён');
    }

    public function destroy(LmsEvent $event, User $user): RedirectResponse
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $profile->delete();

        return redirect()->route('lms.admin.users.index', $event)->with('success', 'Участник удалён из события');
    }

    public function sendInvitations(Request $request, LmsEvent $event): RedirectResponse
    {
        $request->validate([
            'profile_ids' => ['required', 'array', 'min:1'],
            'profile_ids.*' => ['integer'],
        ]);

        $profiles = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('id', $request->profile_ids)
            ->whereIn('status', ['imported', 'invited'])
            ->with('user')
            ->get();

        $sent = 0;
        foreach ($profiles as $profile) {
            $token = $profile->generateInviteToken();
            $activateUrl = url(route('lms.activate', [
                'event' => $event->slug,
                'token' => $token,
            ]));

            $profile->update([
                'status' => 'invited',
                'invited_at' => now(),
            ]);

            $mailable = new InvitationMail($profile->user, $event, $activateUrl);
            SendMailJob::dispatch($profile->user->email, $mailable);
            $sent++;
        }

        return redirect()->back()->with('success', "Приглашения отправлены: {$sent}");
    }

    public function bulkEnroll(Request $request, LmsEvent $event): RedirectResponse
    {
        $request->validate([
            'profile_ids' => ['required', 'array', 'min:1'],
            'profile_ids.*' => ['integer'],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['exists:lms_courses,id'],
        ]);

        $userIds = LmsProfile::where('lms_event_id', $event->id)
            ->whereIn('id', $request->profile_ids)
            ->pluck('user_id');

        $enrolled = 0;
        foreach ($userIds as $userId) {
            foreach ($request->course_ids as $courseId) {
                $created = LmsCourseEnrollment::firstOrCreate(
                    ['lms_course_id' => $courseId, 'user_id' => $userId],
                    ['status' => 'enrolled']
                );
                if ($created->wasRecentlyCreated) {
                    $enrolled++;
                }
            }
        }

        return redirect()->back()->with('success', "Записано на курсы: {$enrolled} записей");
    }

    public function import(Request $request, LmsEvent $event): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'default_role_id' => ['nullable', 'exists:lms_roles,id'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if ($ext === 'csv') {
            $rows = $this->parseCsv($file->getRealPath());
        } else {
            $rows = $this->parseExcel($file->getRealPath());
        }

        $rolesMap = LmsRole::where('lms_event_id', $event->id)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [mb_strtolower($name) => $id])
            ->toArray();

        $imported = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;
            $email     = trim($row['Email'] ?? $row['email'] ?? $row['E-mail'] ?? $row['Электронная почта'] ?? '');
            $lastName  = trim($row['Фамилия'] ?? $row['фамилия'] ?? $row['last_name'] ?? '');
            $firstName = trim($row['Имя'] ?? $row['имя'] ?? $row['first_name'] ?? '');
            $patronymic = trim($row['Отчество'] ?? $row['отчество'] ?? $row['patronymic'] ?? '');
            $phone     = trim($row['Телефон'] ?? $row['телефон'] ?? $row['phone'] ?? '');
            $position  = trim($row['Должность'] ?? $row['должность'] ?? $row['position'] ?? '');
            $roleName  = trim($row['Роль'] ?? $row['роль'] ?? $row['role'] ?? '');
            $city      = trim($row['Город'] ?? $row['город'] ?? $row['city'] ?? '');

            if (!$email) {
                $errors[] = "Строка {$rowNum}: отсутствует email";
                continue;
            }

            $fullName = trim("{$lastName} {$firstName}");
            if (!$fullName) {
                $fullName = explode('@', $email)[0];
            }

            $systemRolesMap = [
                'admin' => 'admin', 'админ' => 'admin', 'администратор' => 'admin',
                'curator' => 'curator', 'куратор' => 'curator',
                'leader' => 'leader', 'лидер' => 'leader', 'тимлид' => 'leader',
            ];

            $profileRole = 'participant';
            $roleId = null;

            if ($roleName) {
                $lowerRole = mb_strtolower($roleName);
                if (isset($systemRolesMap[$lowerRole])) {
                    $profileRole = $systemRolesMap[$lowerRole];
                } else {
                    $roleId = $rolesMap[$lowerRole] ?? null;
                    if (!$roleId) {
                        $errors[] = "Строка {$rowNum}: роль «{$roleName}» не найдена";
                    }
                }
            }
            $roleId = $roleId ?: ($request->default_role_id ?: null);

            $password = Str::random(10);
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name'       => $fullName,
                    'last_name'  => $lastName ?: null,
                    'first_name' => $firstName ?: null,
                    'patronymic' => $patronymic ?: null,
                    'email'      => $email,
                    'phone'      => $phone ?: null,
                    'password'   => $password,
                ]);
            } else {
                $user->update(array_filter([
                    'name'       => $fullName ?: null,
                    'last_name'  => $lastName ?: null,
                    'first_name' => $firstName ?: null,
                    'patronymic' => $patronymic ?: null,
                    'phone'      => $phone ?: null,
                ]));
            }

            $profile = LmsProfile::firstOrCreate(
                ['user_id' => $user->id, 'lms_event_id' => $event->id],
                [
                    'role'       => $profileRole,
                    'lms_role_id' => $roleId,
                    'position'   => $position ?: null,
                    'city'       => $city ?: null,
                    'phone'      => $phone ?: null,
                ]
            );

            $profile->update(array_filter([
                'role'        => $profileRole,
                'lms_role_id' => $roleId,
                'position'    => $position ?: null,
                'city'        => $city ?: null,
            ]));

            $imported++;
        }

        $msg = "Импортировано: {$imported} пользователей.";
        if (count($errors) > 0) {
            $msg .= ' Ошибки: ' . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->back()->with('success', $msg);
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            $headers = fgetcsv($handle, 0, ',');
        }
        if (!$headers) return [];

        $headers = array_map('trim', $headers);
        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if (count($data) === 1 && str_contains($data[0], ',')) {
                $data = str_getcsv($data[0], ',');
            }
            $row = [];
            foreach ($headers as $i => $h) {
                $row[$h] = $data[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function parseExcel(string $path): array
    {
        $rows = [];
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return $rows;

        $sheet = $zip->getFromName('xl/sharedStrings.xml');
        $strings = [];
        if ($sheet) {
            $xml = simplexml_load_string($sheet);
            foreach ($xml->si as $si) {
                $strings[] = (string) $si->t;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) { $zip->close(); return $rows; }

        $xml = simplexml_load_string($sheetXml);
        $colIndex = function (string $ref): int {
            $letters = preg_replace('/[0-9]/', '', $ref);
            $index = 0;
            for ($k = 0; $k < strlen($letters); $k++) {
                $index = $index * 26 + (ord(strtoupper($letters[$k])) - ord('A') + 1);
            }
            return $index - 1;
        };

        $allRows = [];
        $maxCols = 0;
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c->attributes()->r;
                $ci = $colIndex($ref);
                $val = (string) $c->v;
                $type = (string) $c->attributes()->t;
                if ($type === 's') {
                    $val = $strings[(int) $val] ?? $val;
                }
                $cells[$ci] = $val;
                if ($ci >= $maxCols) $maxCols = $ci + 1;
            }
            $allRows[] = $cells;
        }
        $zip->close();

        if (count($allRows) < 2) return $rows;

        $headers = [];
        for ($j = 0; $j < $maxCols; $j++) {
            $headers[$j] = $allRows[0][$j] ?? "col_{$j}";
        }

        for ($i = 1; $i < count($allRows); $i++) {
            $row = [];
            foreach ($headers as $j => $h) {
                $row[trim($h)] = $allRows[$i][$j] ?? '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public function downloadUserDocuments(LmsEvent $event, User $user)
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('documents')
            ->firstOrFail();

        $documents = $profile->documents;

        if ($documents->isEmpty()) {
            return redirect()->back()->with('success', 'У пользователя нет загруженных документов');
        }

        $typeLabels = [
            LmsProfileDocument::TYPE_ENROLLMENT_APPLICATION => 'Заявление',
            LmsProfileDocument::TYPE_SNILS => 'СНИЛС',
            LmsProfileDocument::TYPE_DIPLOMA => 'Диплом',
            LmsProfileDocument::TYPE_PERSONAL_DATA_CONSENT => 'Согласие_ПД',
            LmsProfileDocument::TYPE_NAME_CHANGE_CERTIFICATE => 'Смена_фамилии',
        ];

        if ($documents->count() === 1) {
            $doc = $documents->first();
            $disk = config('filesystems.upload_disk');
            if (! Storage::disk($disk)->exists($doc->file_path)) {
                return redirect()->back()->with('success', 'Файл не найден на диске');
            }
            $typeLabel = $typeLabels[$doc->type] ?? $doc->type;
            $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION);
            $fileName = $typeLabel . ($ext ? '.' . $ext : '');

            return Storage::disk($disk)->download($doc->file_path, $fileName);
        }

        $disk = config('filesystems.upload_disk');
        $tmpFile = tempnam(sys_get_temp_dir(), 'docs');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($documents as $doc) {
            if (! Storage::disk($disk)->exists($doc->file_path)) {
                continue;
            }
            $typeLabel = $typeLabels[$doc->type] ?? $doc->type;
            $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION);
            $fileName = $typeLabel . ($ext ? '.' . $ext : '');

            $zip->addFromString($fileName, Storage::disk($disk)->get($doc->file_path));
        }

        $zip->close();
        $content = file_get_contents($tmpFile);
        unlink($tmpFile);

        $userName = Str::slug($user->last_name . ' ' . $user->first_name) ?: $user->id;

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"documents-{$userName}.zip\"",
        ]);
    }

    public function downloadTemplate(LmsEvent $event)
    {
        $roles = LmsRole::where('lms_event_id', $event->id)->pluck('name');

        $headers = ['Фамилия', 'Имя', 'Отчество', 'Email', 'Телефон', 'Должность', 'Роль', 'Город'];
        $examples = [
            ['Иванов', 'Иван', 'Иванович', 'ivanov@example.com', '+79001234567', 'Менеджер', $roles->first() ?? 'Специалист', 'Москва'],
            ['Петрова', 'Мария', 'Сергеевна', 'petrova@example.com', '+79007654321', 'Инженер', $roles->skip(1)->first() ?? 'Предприниматель', 'Санкт-Петербург'],
        ];

        $xlsx = $this->buildXlsx($headers, $examples);

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="import_template.xlsx"',
        ]);
    }

    public function approveDirection(LmsEvent $event, User $user): RedirectResponse
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $profile->direction || ! $profile->faculty) {
            return redirect()->back()->with('error', 'Участник ещё не выбрал направление и факультет');
        }

        $profile->update(['direction_approved_at' => now()]);

        return redirect()->back()->with('success', 'Направление и факультет одобрены');
    }

    public function rejectDirection(LmsEvent $event, User $user): RedirectResponse
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $profile->update(['direction_approved_at' => null]);

        return redirect()->back()->with('success', 'Одобрение направления отменено');
    }

    private function buildXlsx(array $headers, array $rows): string
    {
        $escXml = fn(string $s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $allStrings = array_merge($headers, ...array_map('array_values', $rows));
        $sharedStrings = [];
        $idx = 0;
        foreach ($allStrings as $s) {
            if (!isset($sharedStrings[$s])) {
                $sharedStrings[$s] = $idx++;
            }
        }

        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($allStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach (array_keys($sharedStrings) as $str) {
            $ssXml .= '<si><t>' . $escXml($str) . '</t></si>';
        }
        $ssXml .= '</sst>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $sheetXml .= '<cols>';
        for ($c = 0; $c < count($headers); $c++) {
            $sheetXml .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="20" bestFit="1" customWidth="1"/>';
        }
        $sheetXml .= '</cols>';
        $sheetXml .= '<sheetData>';

        $letters = range('A', 'Z');

        $sheetXml .= '<row r="1">';
        foreach ($headers as $ci => $h) {
            $sheetXml .= '<c r="' . $letters[$ci] . '1" t="s" s="1"><v>' . $sharedStrings[$h] . '</v></c>';
        }
        $sheetXml .= '</row>';

        foreach ($rows as $ri => $row) {
            $rowNum = $ri + 2;
            $sheetXml .= '<row r="' . $rowNum . '">';
            foreach (array_values($row) as $ci => $val) {
                $sheetXml .= '<c r="' . $letters[$ci] . $rowNum . '" t="s"><v>' . $sharedStrings[$val] . '</v></c>';
            }
            $sheetXml .= '</row>';
        }
        $sheetXml .= '</sheetData></worksheet>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $stylesXml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $stylesXml .= '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>';
        $stylesXml .= '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFD9E2F3"/></patternFill></fill></fills>';
        $stylesXml .= '<borders count="1"><border/></borders>';
        $stylesXml .= '<cellStyleXfs count="1"><xf/></cellStyleXfs>';
        $stylesXml .= '<cellXfs count="2"><xf/><xf fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1"/></cellXfs>';
        $stylesXml .= '</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Участники" sheetId="1" r:id="rId1"/></sheets></workbook>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';

        $xlRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>';

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $xlRels);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        return $content;
    }
}
