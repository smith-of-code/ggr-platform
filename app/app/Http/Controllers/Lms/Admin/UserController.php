<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsCourse;
use App\Models\Lms\LmsCourseEnrollment;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Models\Lms\LmsRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $query = $event->profiles()->with(['user:id,name,patronymic,email,phone', 'lmsRole:id,name,slug']);

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

        return Inertia::render('Lms/Admin/Users/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profiles' => $profiles,
            'roles' => $roles,
            'groups' => $groups,
            'courses' => $courses,
            'filters' => $request->only(['role_id', 'group', 'search']),
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
                'name' => $fullName,
                'patronymic' => $validated['patronymic'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $password,
            ]);
        } else {
            $user->update([
                'name' => $fullName,
                'patronymic' => $validated['patronymic'],
                'phone' => $validated['phone'] ?? $user->phone,
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
            ->with('lmsRole:id,name,slug')
            ->firstOrFail();

        $profile->load('user:id,name,patronymic,email,phone,created_at');

        $enrollments = LmsCourseEnrollment::where('user_id', $user->id)
            ->whereHas('course', fn($q) => $q->where('lms_event_id', $event->id))
            ->with('course:id,title')
            ->get();

        $roles = LmsRole::where('lms_event_id', $event->id)->orderBy('name')->get(['id', 'name', 'slug']);
        $courses = LmsCourse::where('lms_event_id', $event->id)->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Lms/Admin/Users/Show', [
            'event' => $event->only(['id', 'slug', 'title']),
            'profile' => $profile,
            'enrollments' => $enrollments,
            'roles' => $roles,
            'courses' => $courses,
        ]);
    }

    public function update(Request $request, LmsEvent $event, User $user): RedirectResponse
    {
        $profile = LmsProfile::where('lms_event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name'       => ['nullable', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'position'   => ['nullable', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:255'],
            'role_id'    => ['nullable', 'exists:lms_roles,id'],
            'role'       => ['nullable', 'string', 'in:participant,curator,leader,admin'],
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['exists:lms_courses,id'],
        ]);

        if (!empty($validated['name'])) {
            $user->update([
                'name' => $validated['name'],
                'patronymic' => $validated['patronymic'] ?? $user->patronymic,
                'phone' => $validated['phone'] ?? $user->phone,
            ]);
        }

        $profile->update([
            'role' => $validated['role'] ?? $profile->role,
            'lms_role_id' => $validated['role_id'] ?? $profile->lms_role_id,
            'position' => $validated['position'] ?? $profile->position,
            'city' => $validated['city'] ?? $profile->city,
            'phone' => $validated['phone'] ?? $profile->phone,
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

        $imported = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $email = trim($row['email'] ?? $row['Email'] ?? $row['E-mail'] ?? $row['Электронная почта'] ?? '');
            $lastName = trim($row['last_name'] ?? $row['Фамилия'] ?? $row['фамилия'] ?? '');
            $firstName = trim($row['first_name'] ?? $row['Имя'] ?? $row['имя'] ?? '');
            $patronymic = trim($row['patronymic'] ?? $row['Отчество'] ?? $row['отчество'] ?? '');
            $phone = trim($row['phone'] ?? $row['Телефон'] ?? $row['телефон'] ?? '');
            $position = trim($row['position'] ?? $row['Должность'] ?? $row['должность'] ?? '');

            if (!$email) {
                $errors[] = "Строка " . ($i + 2) . ": отсутствует email";
                continue;
            }

            $fullName = trim("{$lastName} {$firstName}");
            if (!$fullName || $fullName === '') {
                $fullName = explode('@', $email)[0];
            }

            $password = Str::random(10);
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $fullName,
                    'patronymic' => $patronymic ?: null,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'password' => $password,
                ]);
            }

            LmsProfile::firstOrCreate(
                ['user_id' => $user->id, 'lms_event_id' => $event->id],
                [
                    'role' => 'participant',
                    'lms_role_id' => $request->default_role_id,
                    'position' => $position ?: null,
                    'phone' => $phone ?: null,
                ]
            );

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
        $allRows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $val = (string) $c->v;
                $type = (string) $c->attributes()->t;
                if ($type === 's') {
                    $val = $strings[(int) $val] ?? $val;
                }
                $cells[] = $val;
            }
            $allRows[] = $cells;
        }
        $zip->close();

        if (count($allRows) < 2) return $rows;

        $headers = $allRows[0];
        for ($i = 1; $i < count($allRows); $i++) {
            $row = [];
            foreach ($headers as $j => $h) {
                $row[trim($h)] = $allRows[$i][$j] ?? '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public function downloadTemplate()
    {
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "Фамилия;Имя;Отчество;Электронная почта;Телефон;Должность\n";
        $csv .= "Иванов;Иван;Иванович;ivanov@example.com;+79001234567;Менеджер\n";
        $csv .= "Петрова;Мария;Сергеевна;petrova@example.com;+79007654321;Специалист\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="import_template.csv"',
        ]);
    }
}
