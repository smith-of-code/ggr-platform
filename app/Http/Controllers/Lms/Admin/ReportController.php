<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Support\MailDisplayName;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, LmsEvent $event): Response
    {
        $eventId = $event->id;
        $roleFilter = $request->query('role');
        $courseFilter = $request->query('course_id');
        $cityFilter = $request->query('city_id');
        $facultyFilter = $request->query('faculty');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $granularity = $request->query('granularity', 'day');

        $totalUsers = LmsProfile::where('lms_event_id', $eventId)
            ->when($roleFilter, fn ($q) => $q->where('role', $roleFilter))
            ->count();

        $courseStats = $this->getCourseStats($eventId, $roleFilter);
        $testStats = $this->getTestStats($eventId, $roleFilter);
        $assignmentStats = $this->getAssignmentStats($eventId, $roleFilter);
        $courseFilterInt = $courseFilter !== null && $courseFilter !== '' ? (int) $courseFilter : null;
        $cityFilterInt = $cityFilter !== null && $cityFilter !== '' ? (int) $cityFilter : null;
        $facultyFilterStr = $facultyFilter !== null && $facultyFilter !== '' ? (string) $facultyFilter : null;

        $deadlineCompliance = $this->getDeadlineCompliance(
            $eventId,
            $roleFilter,
            $courseFilterInt,
            $cityFilterInt,
            $facultyFilterStr,
        );
        $personalProgress = $this->getPersonalProgress(
            $eventId,
            $roleFilter,
            $courseFilterInt,
            $cityFilterInt,
            $facultyFilterStr,
        );
        $cityComparison = $courseFilterInt !== null
            ? $this->getCityComparison($eventId, $courseFilterInt)
            : null;
        $userDetails = $this->getUserDetails($eventId, $roleFilter, $courseFilter);
        $stageProgress = $this->getStageProgress($eventId, $roleFilter);
        $activityTimeline = $this->getActivityTimeline($eventId, $dateFrom, $dateTo, (string) $granularity);
        $groupStats = $this->getGroupStats($eventId);
        $gamificationBreakdown = $this->getGamificationBreakdown($eventId);

        $totalCourses = DB::table('lms_courses')->where('lms_event_id', $eventId)->count();
        $totalTests = DB::table('lms_tests')->where('lms_event_id', $eventId)->count();
        $totalAssignments = DB::table('lms_assignments')->where('lms_event_id', $eventId)->count();

        $activeUsersCount = DB::table('lms_stage_progress')
            ->join('lms_course_stages', 'lms_course_stages.id', '=', 'lms_stage_progress.lms_course_stage_id')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_stages.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->where('lms_stage_progress.updated_at', '>=', now()->subDays(7))
            ->distinct('lms_stage_progress.user_id')
            ->count('lms_stage_progress.user_id');

        $inactiveUsers = $this->getInactiveUsers($eventId);

        $summary = [
            'total_users' => $totalUsers,
            'total_courses' => $totalCourses,
            'total_tests' => $totalTests,
            'total_assignments' => $totalAssignments,
            'active_last_7_days' => $activeUsersCount,
            'inactive_users' => count($inactiveUsers),
            'avg_course_completion' => $totalUsers > 0 && $courseStats->count() > 0
                ? round($courseStats->sum('completed') / max($courseStats->count(), 1) / $totalUsers * 100, 1)
                : 0,
            'avg_test_pass_rate' => $testStats->count() > 0
                ? round($testStats->avg(fn ($t) => $t->attempted > 0 ? ($t->passed / $t->attempted * 100) : 0), 1)
                : 0,
            'total_gamification_points' => (int) DB::table('lms_gamification_points')
                ->where('lms_event_id', $eventId)
                ->where('for_city_ranking_only', false)
                ->whereNotNull('user_id')
                ->sum('points'),
        ];

        $roles = DB::table('lms_profiles')
            ->where('lms_event_id', $eventId)
            ->whereNotNull('role')
            ->distinct()
            ->pluck('role');

        $courses = DB::table('lms_courses')
            ->where('lms_event_id', $eventId)
            ->orderBy('title')
            ->get(['id', 'title']);

        $cities = DB::table('lms_profiles')
            ->where('lms_profiles.lms_event_id', $eventId)
            ->whereNotNull('lms_profiles.city_id')
            ->join('cities', 'cities.id', '=', 'lms_profiles.city_id')
            ->select('cities.id', 'cities.name')
            ->distinct()
            ->orderBy('cities.name')
            ->get();

        $faculties = DB::table('lms_course_enrollments')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->whereNotNull('lms_course_enrollments.faculty')
            ->where('lms_course_enrollments.faculty', '!=', '')
            ->distinct()
            ->orderBy('lms_course_enrollments.faculty')
            ->pluck('lms_course_enrollments.faculty');

        return Inertia::render('Lms/Admin/Reports/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'summary' => $summary,
            'courseStats' => $courseStats,
            'testStats' => $testStats,
            'assignmentStats' => $assignmentStats,
            'deadlineCompliance' => $deadlineCompliance,
            'personalProgress' => $personalProgress,
            'cityComparison' => $cityComparison,
            'userDetails' => $userDetails,
            'stageProgress' => $stageProgress,
            'activityTimeline' => $activityTimeline,
            'groupStats' => $groupStats,
            'gamificationBreakdown' => $gamificationBreakdown,
            'inactiveUsers' => $inactiveUsers,
            'filters' => [
                'role' => $roleFilter,
                'course_id' => $courseFilter,
                'city_id' => $cityFilter,
                'faculty' => $facultyFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'granularity' => $granularity,
            ],
            'availableRoles' => $roles,
            'availableCourses' => $courses,
            'availableCities' => $cities,
            'availableFaculties' => $faculties,
        ]);
    }

    public function download(Request $request, LmsEvent $event): StreamedResponse
    {
        $eventId = $event->id;
        $section = $request->query('section', 'all');
        $courseFilter = $request->query('course_id');
        $courseFilterInt = $courseFilter !== null && $courseFilter !== '' ? (int) $courseFilter : null;
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $granularity = (string) $request->query('granularity', 'day');

        if ($section === 'cities' && $courseFilterInt === null) {
            abort(422, 'course_id is required for cities section.');
        }

        return response()->streamDownload(function () use ($eventId, $event, $section, $courseFilterInt, $dateFrom, $dateTo, $granularity) {
            echo "\xEF\xBB\xBF";

            if (in_array($section, ['all', 'users'])) {
                $this->writeCsvSection('УЧАСТНИКИ', $eventId, $event);
            }
            if (in_array($section, ['all', 'courses'])) {
                $this->writeCsvCourses($eventId);
            }
            if (in_array($section, ['all', 'tests'])) {
                $this->writeCsvTests($eventId);
            }
            if (in_array($section, ['all', 'assignments'])) {
                $this->writeCsvAssignments($eventId);
            }
            if (in_array($section, ['all', 'stages'])) {
                $this->writeCsvStages($eventId);
            }
            if (in_array($section, ['all', 'deadline'])) {
                $this->writeCsvDeadline($eventId);
            }
            if (in_array($section, ['all', 'personal'])) {
                $this->writeCsvPersonal($eventId);
            }
            if ($section === 'cities' || ($section === 'all' && $courseFilterInt !== null)) {
                $this->writeCsvCities($eventId, $courseFilterInt);
            }
            if (in_array($section, ['all', 'dynamics'])) {
                $this->writeCsvDynamics($eventId, $dateFrom, $dateTo, $granularity);
            }
        }, 'report_'.$event->slug.'_'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function sendEmail(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'sections' => ['required', 'array'],
            'sections.*' => ['string', 'in:users,courses,tests,stages,deadline,personal,cities,dynamics'],
            'course_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'granularity' => ['nullable', 'in:day,week'],
        ]);

        $eventId = $event->id;
        $courseId = isset($validated['course_id']) ? (int) $validated['course_id'] : null;

        if (in_array('cities', $validated['sections'], true) && $courseId === null) {
            abort(422, 'course_id is required for cities section.');
        }

        $rows = [];
        $rows[] = ['Отчёт по событию: '.$event->title];
        $rows[] = ['Дата: '.now()->format('d.m.Y H:i')];
        $rows[] = [];

        if (in_array('users', $validated['sections'])) {
            $rows[] = ['=== УЧАСТНИКИ ==='];
            $rows[] = ['Имя', 'Email', 'Роль', 'Курсов записан', 'Курсов завершено', 'Тестов сдано', 'Ср. балл тестов', 'Заданий принято', 'Баллы'];

            $users = $this->getUserDetails($eventId, null, null);
            foreach ($users as $u) {
                $rows[] = [$u->name, $u->email, $u->role ?? '—', $u->courses_enrolled, $u->courses_completed, $u->tests_passed, $u->avg_test_score, $u->assignments_approved, $u->total_points];
            }
            $rows[] = [];
        }

        if (in_array('courses', $validated['sections'])) {
            $rows[] = ['=== КУРСЫ ==='];
            $rows[] = ['Курс', 'Записано', 'В процессе', 'Завершено', '% завершения'];
            $courses = $this->getCourseStats($eventId, null);
            foreach ($courses as $c) {
                $pct = $c->enrolled > 0 ? round($c->completed / $c->enrolled * 100, 1) : 0;
                $rows[] = [$c->title, $c->enrolled, $c->in_progress, $c->completed, $pct.'%'];
            }
            $rows[] = [];
        }

        if (in_array('tests', $validated['sections'])) {
            $rows[] = ['=== ТЕСТЫ ==='];
            $rows[] = ['Тест', 'Попыток', 'Участников', 'Сдало', 'Ср. балл'];
            $tests = $this->getTestStats($eventId, null);
            foreach ($tests as $t) {
                $rows[] = [$t->title, $t->total_attempts, $t->attempted, $t->passed, round($t->avg_score, 1).'%'];
            }
            $rows[] = [];
        }

        if (in_array('stages', $validated['sections'])) {
            $rows[] = ['=== ЭТАПЫ КУРСОВ ==='];
            $rows[] = ['Курс', 'Этап', 'Записано на программу', 'Открыли этап', 'Не начали', 'В процессе', 'Завершили', '% завершения'];
            $stages = $this->getStageProgress($eventId);
            foreach ($stages as $s) {
                $pct = $s->enrolled > 0 ? round($s->completed / $s->enrolled * 100, 1) : 0;
                $rows[] = [$s->course_title, $s->stage_title, $s->enrolled, $s->opened, $s->not_started, $s->in_progress, $s->completed, $pct.'%'];
            }
            $rows[] = [];
        }

        if (in_array('deadline', $validated['sections'], true)) {
            $rows[] = ['=== СОБЛЮДЕНИЕ СРОКОВ ==='];
            $rows[] = ['Задание', 'Курс', 'Город', 'Факультет', 'Вне плана', 'Всего', 'В срок', 'С задержкой', 'Просрочено', 'Ср. задержка (дн.)'];
            foreach ($this->getDeadlineCompliance($eventId) as $r) {
                $rows[] = [
                    $r->assignment_title, $r->course_title ?? '—', $r->city_name ?? '—', $r->faculty ?? '—',
                    $r->is_orphan ? 'да' : 'нет',
                    $r->total_users, $r->on_time, $r->late, $r->overdue,
                    $r->avg_delay_days !== null ? (string) $r->avg_delay_days : '—',
                ];
            }
            $rows[] = [];
        }

        if (in_array('personal', $validated['sections'], true)) {
            $rows[] = ['=== ПРОГРЕСС УЧАСТНИКОВ ==='];
            $rows[] = ['Имя', 'Email', 'Роль', 'Город', 'Заданий', 'Принято', '% заданий', 'Тестов', 'Сдано', '% тестов', 'Этапов', 'Завершено', '% этапов', 'Общий %', 'Доп. (вне плана)', 'Доп. принято'];
            foreach ($this->getPersonalProgress($eventId) as $r) {
                $rows[] = [
                    $r->user_name, $r->user_email, $r->role ?? '—', $r->city_name ?? '—',
                    $r->assignments_total, $r->assignments_done, $r->assignments_pct.'%',
                    $r->tests_total, $r->tests_done, $r->tests_pct.'%',
                    $r->stages_total, $r->stages_done, $r->stages_pct.'%',
                    $r->overall_pct.'%',
                    (int) ($r->assignments_orphan_total ?? 0),
                    (int) ($r->assignments_orphan_done ?? 0),
                ];
            }
            $rows[] = [];
        }

        if (in_array('cities', $validated['sections'], true)) {
            $rows[] = ['=== СРАВНЕНИЕ ГОРОДОВ ==='];
            $rows[] = ['Программа ID', $courseId];
            $rows[] = ['Город', 'Участников', '% в срок', '% просрочено', 'Ср. общий %', 'Ср. балл тестов'];
            foreach ($this->getCityComparison($eventId, $courseId) as $r) {
                $rows[] = [
                    $r->city_name ?? '—', $r->participants_count,
                    $r->on_time_pct.'%', $r->overdue_pct.'%',
                    $r->avg_overall_pct.'%', $r->avg_test_score.'%',
                ];
            }
            $rows[] = [];
        }

        if (in_array('dynamics', $validated['sections'], true)) {
            $granularity = $validated['granularity'] ?? 'day';
            $timeline = $this->getActivityTimeline($eventId, $validated['date_from'] ?? null, $validated['date_to'] ?? null, $granularity);
            $modeLabel = $timeline['granularity'] === 'week' ? 'НЕДЕЛЯ' : 'ДЕНЬ';
            $rows[] = ["=== ДИНАМИКА ({$modeLabel}) ==="];
            $rows[] = ['Период', $timeline['from'].' — '.$timeline['to']];
            $rows[] = ['Корзина', 'Записи', 'Завершения курсов', 'Попытки тестов', 'Принятые задания', 'Сданные тесты', 'Завершённые этапы'];

            $series = ['enrollments', 'completions', 'test_attempts', 'assignments_approved', 'tests_passed', 'stages_completed'];
            $allKeys = [];
            foreach ($series as $key) {
                foreach ($timeline[$key] as $bucket => $_) {
                    $allKeys[$bucket] = true;
                }
            }
            ksort($allKeys);

            foreach (array_keys($allKeys) as $bucket) {
                $vals = [$bucket];
                foreach ($series as $key) {
                    $vals[] = (int) ($timeline[$key][$bucket] ?? 0);
                }
                $rows[] = $vals;
            }
            $rows[] = [];
        }

        $csvContent = '';
        foreach ($rows as $row) {
            $csvContent .= implode(';', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\n";
        }

        $csvContent = "\xEF\xBB\xBF".$csvContent;

        $body = 'Отчёт по событию «'.$event->title.'» во вложении.'."\n\n— ".MailDisplayName::resolve();

        Mail::raw($body, function ($message) use ($validated, $event, $csvContent) {
            $message->to($validated['email'])
                ->subject('Отчёт: '.$event->title.' — '.now()->format('d.m.Y'))
                ->attachData($csvContent, 'report_'.$event->slug.'_'.now()->format('Y-m-d').'.csv', [
                    'mime' => 'text/csv',
                ]);
        });

        return redirect()->back()->with('success', 'Отчёт отправлен на '.$validated['email']);
    }

    private function getCourseStats(int $eventId, ?string $roleFilter)
    {
        $query = DB::table('lms_courses')
            ->where('lms_courses.lms_event_id', $eventId)
            ->leftJoin('lms_course_enrollments', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id');

        if ($roleFilter) {
            $query->leftJoin('lms_profiles', function ($join) use ($eventId) {
                $join->on('lms_profiles.user_id', '=', 'lms_course_enrollments.user_id')
                    ->where('lms_profiles.lms_event_id', $eventId);
            })->where('lms_profiles.role', $roleFilter);
        }

        return $query->select(
            'lms_courses.id',
            'lms_courses.title',
            DB::raw('COUNT(DISTINCT lms_course_enrollments.user_id) as enrolled'),
            DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'completed' THEN lms_course_enrollments.user_id END) as completed"),
            DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'in_progress' THEN lms_course_enrollments.user_id END) as in_progress"),
            DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'enrolled' THEN lms_course_enrollments.user_id END) as not_started"),
        )
            ->groupBy('lms_courses.id', 'lms_courses.title')
            ->orderBy('lms_courses.position')
            ->orderBy('lms_courses.title')
            ->get();
    }

    private function getTestStats(int $eventId, ?string $roleFilter)
    {
        $query = DB::table('lms_tests')
            ->where('lms_tests.lms_event_id', $eventId)
            ->leftJoin('lms_test_attempts', 'lms_tests.id', '=', 'lms_test_attempts.lms_test_id');

        if ($roleFilter) {
            $query->leftJoin('lms_profiles', function ($join) use ($eventId) {
                $join->on('lms_profiles.user_id', '=', 'lms_test_attempts.user_id')
                    ->where('lms_profiles.lms_event_id', $eventId);
            })->where('lms_profiles.role', $roleFilter);
        }

        return $query->select(
            'lms_tests.id',
            'lms_tests.title',
            DB::raw('COUNT(DISTINCT lms_test_attempts.user_id) as attempted'),
            DB::raw('COUNT(DISTINCT CASE WHEN lms_test_attempts.passed = true THEN lms_test_attempts.user_id END) as passed'),
            DB::raw('COUNT(DISTINCT CASE WHEN lms_test_attempts.passed = false THEN lms_test_attempts.user_id END) as failed'),
            DB::raw('COALESCE(AVG(lms_test_attempts.percentage), 0) as avg_score'),
            DB::raw('COALESCE(MIN(lms_test_attempts.percentage), 0) as min_score'),
            DB::raw('COALESCE(MAX(lms_test_attempts.percentage), 0) as max_score'),
            DB::raw('COUNT(lms_test_attempts.id) as total_attempts'),
        )
            ->groupBy('lms_tests.id', 'lms_tests.title')
            ->orderBy('lms_tests.title')
            ->get();
    }

    private function getAssignmentStats(int $eventId, ?string $roleFilter)
    {
        $query = DB::table('lms_assignments')
            ->where('lms_assignments.lms_event_id', $eventId)
            ->leftJoin('lms_assignment_submissions', 'lms_assignments.id', '=', 'lms_assignment_submissions.lms_assignment_id');

        if ($roleFilter) {
            $query->leftJoin('lms_profiles', function ($join) use ($eventId) {
                $join->on('lms_profiles.user_id', '=', 'lms_assignment_submissions.user_id')
                    ->where('lms_profiles.lms_event_id', $eventId);
            })->where('lms_profiles.role', $roleFilter);
        }

        return $query->select(
            'lms_assignments.id',
            'lms_assignments.title',
            DB::raw('COUNT(DISTINCT lms_assignment_submissions.user_id) as submitted'),
            DB::raw("COUNT(DISTINCT CASE WHEN lms_assignment_submissions.status = 'approved' THEN lms_assignment_submissions.user_id END) as approved"),
            DB::raw("COUNT(DISTINCT CASE WHEN lms_assignment_submissions.status = 'rejected' THEN lms_assignment_submissions.user_id END) as rejected"),
            DB::raw("COUNT(DISTINCT CASE WHEN lms_assignment_submissions.status IN ('submitted', 'resubmitted') THEN lms_assignment_submissions.user_id END) as pending"),
        )
            ->groupBy('lms_assignments.id', 'lms_assignments.title')
            ->orderBy('lms_assignments.title')
            ->get();
    }

    /**
     * Соблюдение сроков ДЗ. Разбивка по `assignment × course × city × faculty`.
     * Дедлайн считается в SQL по той же цепочке, что и `DeadlineService::resolveDeadline`:
     * `assignment.deadline → event.default_assignment_deadline → config('lms.reports.default_deadline')`.
     * Postgres-only: используется `EXTRACT(EPOCH FROM ...)` и `::timestamp`.
     */
    private function getDeadlineCompliance(
        int $eventId,
        ?string $roleFilter = null,
        ?int $courseFilter = null,
        ?int $cityFilter = null,
        ?string $facultyFilter = null,
    ) {
        $defaultDeadline = Carbon::parse(config('lms.reports.default_deadline'))
            ->utc()
            ->format('Y-m-d H:i:s');
        $deadlineExpr = "COALESCE(a.deadline, e.default_assignment_deadline, '{$defaultDeadline}'::timestamp)";

        $approvedSubquery = '(SELECT s.lms_assignment_id, s.user_id, MAX(r.created_at) AS approved_at '
            .'FROM lms_assignment_submissions s '
            .'JOIN lms_assignment_reviews r ON r.lms_assignment_submission_id = s.id '
            ."WHERE r.decision = 'approve' "
            .'GROUP BY s.lms_assignment_id, s.user_id) ar';

        $planRows = DB::table('lms_assignments as a')
            ->join('lms_events as e', 'e.id', '=', 'a.lms_event_id')
            ->join('lms_stage_blocks as sb', 'sb.lms_assignment_id', '=', 'a.id')
            ->join('lms_course_stages as cs', 'cs.id', '=', 'sb.lms_course_stage_id')
            ->join('lms_courses as c', 'c.id', '=', 'cs.lms_course_id')
            ->join('lms_course_enrollments as ce', 'ce.lms_course_id', '=', 'c.id')
            ->join('users as u', 'u.id', '=', 'ce.user_id')
            ->join('lms_profiles as p', function ($join) use ($eventId) {
                $join->on('p.user_id', '=', 'u.id')
                    ->where('p.lms_event_id', $eventId);
            })
            ->leftJoin('cities', 'cities.id', '=', 'p.city_id')
            ->leftJoin(DB::raw($approvedSubquery), function ($join) {
                $join->on('ar.lms_assignment_id', '=', 'a.id')
                    ->on('ar.user_id', '=', 'u.id');
            })
            ->where('a.lms_event_id', $eventId)
            ->when($roleFilter, fn ($q) => $q->where('p.role', $roleFilter))
            ->when($courseFilter, fn ($q) => $q->where('c.id', $courseFilter))
            ->when($cityFilter, fn ($q) => $q->where('p.city_id', $cityFilter))
            ->when($facultyFilter, fn ($q) => $q->where('ce.faculty', $facultyFilter))
            ->select(
                'a.id as assignment_id',
                'a.title as assignment_title',
                'c.id as course_id',
                'c.title as course_title',
                'p.city_id as city_id',
                'cities.name as city_name',
                'ce.faculty as faculty',
                DB::raw('false as is_orphan'),
                DB::raw('COUNT(DISTINCT u.id) as total_users'),
                DB::raw("COUNT(DISTINCT CASE WHEN ar.approved_at IS NOT NULL AND ar.approved_at <= {$deadlineExpr} THEN u.id END) as on_time"),
                DB::raw("COUNT(DISTINCT CASE WHEN ar.approved_at IS NOT NULL AND ar.approved_at >  {$deadlineExpr} THEN u.id END) as late"),
                DB::raw('COUNT(DISTINCT CASE WHEN ar.approved_at IS NULL THEN u.id END) as overdue'),
                DB::raw('ROUND(AVG(CASE WHEN ar.approved_at IS NOT NULL AND ar.approved_at > '.$deadlineExpr.' THEN CEIL(EXTRACT(EPOCH FROM (ar.approved_at - '.$deadlineExpr.')) / 86400.0) END)::numeric, 2) as avg_delay_days'),
            )
            ->groupBy('a.id', 'a.title', 'c.id', 'c.title', 'p.city_id', 'cities.name', 'ce.faculty')
            ->orderBy('a.title')
            ->orderBy('c.title')
            ->orderBy('cities.name')
            ->orderBy('ce.faculty')
            ->get();

        $orphanRows = ($courseFilter !== null || ($facultyFilter !== null && $facultyFilter !== ''))
            ? collect()
            : $this->getOrphanDeadlineCompliance($eventId, $deadlineExpr, $approvedSubquery, $roleFilter, $cityFilter);

        return $planRows->concat($orphanRows)->values();
    }

    /**
     * Orphan-задания (без `lms_stage_blocks` ни в одном курсе): база
     * участников = `lms_profiles` события (фильтры role/city_id применяются),
     * группировка только по самому заданию. Поля city/course/faculty = null,
     * `is_orphan=true`. Скрываются при наличии фильтра по программе или
     * факультету (т.к. orphan по определению вне плана).
     */
    private function getOrphanDeadlineCompliance(
        int $eventId,
        string $deadlineExpr,
        string $approvedSubquery,
        ?string $roleFilter,
        ?int $cityFilter,
    ) {
        return DB::table('lms_assignments as a')
            ->join('lms_events as e', 'e.id', '=', 'a.lms_event_id')
            ->join('lms_profiles as p', function ($join) use ($eventId) {
                $join->where('p.lms_event_id', $eventId);
            })
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin(DB::raw($approvedSubquery), function ($join) {
                $join->on('ar.lms_assignment_id', '=', 'a.id')
                    ->on('ar.user_id', '=', 'u.id');
            })
            ->where('a.lms_event_id', $eventId)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('lms_stage_blocks as sb')
                    ->whereColumn('sb.lms_assignment_id', 'a.id');
            })
            ->when($roleFilter, fn ($q) => $q->where('p.role', $roleFilter))
            ->when($cityFilter, fn ($q) => $q->where('p.city_id', $cityFilter))
            ->select(
                'a.id as assignment_id',
                'a.title as assignment_title',
                DB::raw('NULL::bigint as course_id'),
                DB::raw('NULL::text as course_title'),
                DB::raw('NULL::bigint as city_id'),
                DB::raw('NULL::text as city_name'),
                DB::raw('NULL::text as faculty'),
                DB::raw('true as is_orphan'),
                DB::raw('COUNT(DISTINCT u.id) as total_users'),
                DB::raw("COUNT(DISTINCT CASE WHEN ar.approved_at IS NOT NULL AND ar.approved_at <= {$deadlineExpr} THEN u.id END) as on_time"),
                DB::raw("COUNT(DISTINCT CASE WHEN ar.approved_at IS NOT NULL AND ar.approved_at >  {$deadlineExpr} THEN u.id END) as late"),
                DB::raw('COUNT(DISTINCT CASE WHEN ar.approved_at IS NULL THEN u.id END) as overdue'),
                DB::raw('ROUND(AVG(CASE WHEN ar.approved_at IS NOT NULL AND ar.approved_at > '.$deadlineExpr.' THEN CEIL(EXTRACT(EPOCH FROM (ar.approved_at - '.$deadlineExpr.')) / 86400.0) END)::numeric, 2) as avg_delay_days'),
            )
            ->groupBy('a.id', 'a.title')
            ->orderBy('a.title')
            ->get();
    }

    /**
     * Индивидуальный % прогресса участника по персональному плану программы.
     * План = объединение `lms_course_stages` + `lms_stage_blocks` курсов из
     * `lms_course_enrollments` пользователя в рамках события. Orphan-задания
     * (без записи в `lms_stage_blocks` ни одной программы) НЕ включаются в
     * total. На каждого user_id возвращается:
     *   assignments_total/done/pct, tests_total/done/pct,
     *   stages_total/done/pct, overall_pct.
     * `overall_pct` = item-fairness: суммируем done и total по трём секциям и
     * делим (а не среднее средних) — каждая единица плана весит одинаково.
     */
    private function getPersonalProgress(
        int $eventId,
        ?string $roleFilter = null,
        ?int $courseFilter = null,
        ?int $cityFilter = null,
        ?string $facultyFilter = null,
    ) {
        $planWhere = "c.lms_event_id = {$eventId}";
        if ($courseFilter) {
            $planWhere .= " AND c.id = {$courseFilter}";
        }
        if ($facultyFilter !== null && $facultyFilter !== '') {
            $facultyEsc = str_replace("'", "''", $facultyFilter);
            $planWhere .= " AND ce.faculty = '{$facultyEsc}'";
        }

        $assignmentsTotalSub = '(SELECT ce.user_id, COUNT(DISTINCT sb.lms_assignment_id) AS cnt '
            .'FROM lms_course_enrollments ce '
            .'JOIN lms_courses c ON c.id = ce.lms_course_id '
            .'JOIN lms_course_stages cs ON cs.lms_course_id = c.id '
            .'JOIN lms_stage_blocks sb ON sb.lms_course_stage_id = cs.id '
            ."WHERE {$planWhere} AND sb.lms_assignment_id IS NOT NULL "
            .'GROUP BY ce.user_id) as at';

        $assignmentsDoneSub = '(SELECT ce.user_id, COUNT(DISTINCT sb.lms_assignment_id) AS cnt '
            .'FROM lms_course_enrollments ce '
            .'JOIN lms_courses c ON c.id = ce.lms_course_id '
            .'JOIN lms_course_stages cs ON cs.lms_course_id = c.id '
            .'JOIN lms_stage_blocks sb ON sb.lms_course_stage_id = cs.id '
            .'JOIN lms_assignment_submissions s ON s.lms_assignment_id = sb.lms_assignment_id AND s.user_id = ce.user_id '
            ."WHERE {$planWhere} AND sb.lms_assignment_id IS NOT NULL AND s.status = 'approved' "
            .'GROUP BY ce.user_id) as ad';

        $testsTotalSub = '(SELECT ce.user_id, COUNT(DISTINCT sb.lms_test_id) AS cnt '
            .'FROM lms_course_enrollments ce '
            .'JOIN lms_courses c ON c.id = ce.lms_course_id '
            .'JOIN lms_course_stages cs ON cs.lms_course_id = c.id '
            .'JOIN lms_stage_blocks sb ON sb.lms_course_stage_id = cs.id '
            ."WHERE {$planWhere} AND sb.lms_test_id IS NOT NULL "
            .'GROUP BY ce.user_id) as tt';

        $testsDoneSub = '(SELECT ce.user_id, COUNT(DISTINCT sb.lms_test_id) AS cnt '
            .'FROM lms_course_enrollments ce '
            .'JOIN lms_courses c ON c.id = ce.lms_course_id '
            .'JOIN lms_course_stages cs ON cs.lms_course_id = c.id '
            .'JOIN lms_stage_blocks sb ON sb.lms_course_stage_id = cs.id '
            .'JOIN lms_test_attempts ta ON ta.lms_test_id = sb.lms_test_id AND ta.user_id = ce.user_id '
            ."WHERE {$planWhere} AND sb.lms_test_id IS NOT NULL AND ta.passed = true "
            .'GROUP BY ce.user_id) as td';

        $stagesTotalSub = '(SELECT ce.user_id, COUNT(DISTINCT cs.id) AS cnt '
            .'FROM lms_course_enrollments ce '
            .'JOIN lms_courses c ON c.id = ce.lms_course_id '
            .'JOIN lms_course_stages cs ON cs.lms_course_id = c.id '
            ."WHERE {$planWhere} "
            .'GROUP BY ce.user_id) as st';

        $stagesDoneSub = '(SELECT sp.user_id, COUNT(DISTINCT sp.lms_course_stage_id) AS cnt '
            .'FROM lms_stage_progress sp '
            .'JOIN lms_course_stages cs ON cs.id = sp.lms_course_stage_id '
            .'JOIN lms_courses c ON c.id = cs.lms_course_id '
            .'JOIN lms_course_enrollments ce ON ce.lms_course_id = c.id AND ce.user_id = sp.user_id '
            ."WHERE {$planWhere} AND sp.status = 'completed' "
            .'GROUP BY sp.user_id) as sd';

        $rows = DB::table('lms_profiles as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->leftJoin('cities', 'cities.id', '=', 'p.city_id')
            ->leftJoin(DB::raw($assignmentsTotalSub), 'at.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($assignmentsDoneSub), 'ad.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($testsTotalSub), 'tt.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($testsDoneSub), 'td.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($stagesTotalSub), 'st.user_id', '=', 'u.id')
            ->leftJoin(DB::raw($stagesDoneSub), 'sd.user_id', '=', 'u.id')
            ->where('p.lms_event_id', $eventId)
            ->when($roleFilter, fn ($q) => $q->where('p.role', $roleFilter))
            ->when($cityFilter, fn ($q) => $q->where('p.city_id', $cityFilter))
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                'u.email as user_email',
                'p.role',
                'p.city_id',
                'cities.name as city_name',
                DB::raw('COALESCE(at.cnt, 0) as assignments_total'),
                DB::raw('COALESCE(ad.cnt, 0) as assignments_done'),
                DB::raw('COALESCE(tt.cnt, 0) as tests_total'),
                DB::raw('COALESCE(td.cnt, 0) as tests_done'),
                DB::raw('COALESCE(st.cnt, 0) as stages_total'),
                DB::raw('COALESCE(sd.cnt, 0) as stages_done'),
            )
            ->orderBy('u.name')
            ->get();

        $orphanIds = DB::table('lms_assignments as a')
            ->where('a.lms_event_id', $eventId)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('lms_stage_blocks as sb')
                    ->whereColumn('sb.lms_assignment_id', 'a.id');
            })
            ->pluck('a.id');
        $orphanTotal = $orphanIds->count();
        $orphanDoneByUser = $orphanTotal > 0
            ? DB::table('lms_assignment_submissions')
                ->whereIn('lms_assignment_id', $orphanIds)
                ->where('status', 'approved')
                ->selectRaw('user_id, COUNT(DISTINCT lms_assignment_id) as cnt')
                ->groupBy('user_id')
                ->pluck('cnt', 'user_id')
            : collect();

        return $rows->map(function ($r) use ($orphanTotal, $orphanDoneByUser) {
            $aTotal = (int) $r->assignments_total;
            $aDone = min((int) $r->assignments_done, $aTotal);
            $tTotal = (int) $r->tests_total;
            $tDone = min((int) $r->tests_done, $tTotal);
            $sTotal = (int) $r->stages_total;
            $sDone = min((int) $r->stages_done, $sTotal);

            $totalAll = $aTotal + $tTotal + $sTotal;
            $doneAll = $aDone + $tDone + $sDone;

            $r->assignments_done = $aDone;
            $r->tests_done = $tDone;
            $r->stages_done = $sDone;
            $r->assignments_pct = $aTotal > 0 ? round($aDone / $aTotal * 100, 1) : 0.0;
            $r->tests_pct = $tTotal > 0 ? round($tDone / $tTotal * 100, 1) : 0.0;
            $r->stages_pct = $sTotal > 0 ? round($sDone / $sTotal * 100, 1) : 0.0;
            $r->overall_pct = $totalAll > 0 ? round($doneAll / $totalAll * 100, 1) : 0.0;

            // Orphan-задания (вне плана программ): отдельные счётчики, НЕ
            // влияют на assignments_*/overall_pct.
            $orphanDone = (int) ($orphanDoneByUser[$r->user_id] ?? 0);
            $r->assignments_orphan_total = $orphanTotal;
            $r->assignments_orphan_done = min($orphanDone, $orphanTotal);

            return $r;
        });
    }

    /**
     * Сравнение городов внутри одной программы. `course_id` обязателен —
     * иначе 422. На каждый `city_id` (включая null = «город не указан»):
     *   participants_count, on_time_pct, overdue_pct, avg_overall_pct, avg_test_score.
     *
     * Реюз: `getDeadlineCompliance` (per-assignment×city → суммируем) и
     * `getPersonalProgress` (per-user → группируем по city и усредняем
     * `overall_pct`). Avg test score — отдельный SQL (best attempt per user×test
     * для тестов курса) с группировкой по `lms_profiles.city_id`.
     *
     * Участник учитывается в `participants_count`, если он enrolled в course
     * И имеет профиль события; в `avg_overall_pct` — только участники с
     * непустым планом (a+t+s > 0), чтобы 0/0/0 не размывали среднее.
     */
    private function getCityComparison(int $eventId, ?int $courseId)
    {
        if ($courseId === null) {
            abort(422, 'course_id is required for city comparison.');
        }

        $compliance = $this->getDeadlineCompliance($eventId, null, $courseId, null, null);
        $progress = $this->getPersonalProgress($eventId, null, $courseId, null, null);

        $participants = DB::table('lms_course_enrollments as ce')
            ->join('lms_profiles as p', function ($join) use ($eventId) {
                $join->on('p.user_id', '=', 'ce.user_id')
                    ->where('p.lms_event_id', $eventId);
            })
            ->where('ce.lms_course_id', $courseId)
            ->select('p.city_id', DB::raw('COUNT(DISTINCT ce.user_id) as cnt'))
            ->groupBy('p.city_id')
            ->pluck('cnt', 'city_id');

        $testIdsSub = '(SELECT DISTINCT sb.lms_test_id '
            .'FROM lms_stage_blocks sb '
            .'JOIN lms_course_stages cs ON cs.id = sb.lms_course_stage_id '
            ."WHERE cs.lms_course_id = {$courseId} AND sb.lms_test_id IS NOT NULL) ct";

        $bestAttemptSub = '(SELECT ta.user_id, ta.lms_test_id, MAX(ta.percentage) AS best_pct '
            .'FROM lms_test_attempts ta '
            ."JOIN {$testIdsSub} ON ct.lms_test_id = ta.lms_test_id "
            .'GROUP BY ta.user_id, ta.lms_test_id) ba';

        $testScores = DB::table(DB::raw($bestAttemptSub))
            ->join('lms_profiles as p', function ($join) use ($eventId) {
                $join->on('p.user_id', '=', 'ba.user_id')
                    ->where('p.lms_event_id', $eventId);
            })
            ->select('p.city_id', DB::raw('AVG(ba.best_pct) as avg_score'))
            ->groupBy('p.city_id')
            ->pluck('avg_score', 'city_id');

        $cityNames = [];
        $byCity = [];
        foreach ($compliance as $row) {
            $key = $row->city_id;
            if (! isset($byCity[$key])) {
                $byCity[$key] = ['on_time' => 0, 'late' => 0, 'overdue' => 0, 'total' => 0];
                $cityNames[$key] = $row->city_name;
            }
            $byCity[$key]['on_time'] += (int) $row->on_time;
            $byCity[$key]['late'] += (int) $row->late;
            $byCity[$key]['overdue'] += (int) $row->overdue;
            $byCity[$key]['total'] += (int) $row->total_users;
        }

        $progressByCity = [];
        foreach ($progress as $row) {
            $hasPlan = ((int) $row->assignments_total + (int) $row->tests_total + (int) $row->stages_total) > 0;
            if (! $hasPlan) {
                continue;
            }
            $key = $row->city_id;
            $progressByCity[$key] ??= [];
            $progressByCity[$key][] = (float) $row->overall_pct;
            $cityNames[$key] ??= $row->city_name;
        }

        $cityIds = array_unique(array_merge(
            array_keys($participants->all()),
            array_keys($byCity),
            array_keys($progressByCity),
        ));

        $result = [];
        foreach ($cityIds as $cityId) {
            $key = $cityId === '' ? null : $cityId;
            $compRow = $byCity[$key] ?? ['on_time' => 0, 'late' => 0, 'overdue' => 0, 'total' => 0];
            $progressList = $progressByCity[$key] ?? [];
            $total = $compRow['total'];

            $result[] = (object) [
                'city_id' => $key === '' ? null : ($key !== null ? (int) $key : null),
                'city_name' => $cityNames[$key] ?? null,
                'participants_count' => (int) ($participants[$key] ?? 0),
                'on_time_pct' => $total > 0 ? round($compRow['on_time'] / $total * 100, 1) : 0.0,
                'overdue_pct' => $total > 0 ? round($compRow['overdue'] / $total * 100, 1) : 0.0,
                'avg_overall_pct' => count($progressList) > 0
                    ? round(array_sum($progressList) / count($progressList), 1)
                    : 0.0,
                'avg_test_score' => isset($testScores[$key])
                    ? round((float) $testScores[$key], 1)
                    : 0.0,
            ];
        }

        usort($result, function ($a, $b) {
            if ($a->city_name === null && $b->city_name === null) {
                return 0;
            }
            if ($a->city_name === null) {
                return 1;
            }
            if ($b->city_name === null) {
                return -1;
            }

            return strcmp($a->city_name, $b->city_name);
        });

        return collect($result);
    }

    private function getUserDetails(int $eventId, ?string $roleFilter, ?string $courseFilter)
    {
        $query = DB::table('lms_profiles')
            ->where('lms_profiles.lms_event_id', $eventId)
            ->join('users', 'users.id', '=', 'lms_profiles.user_id')
            ->when($roleFilter, fn ($q) => $q->where('lms_profiles.role', $roleFilter));

        $ceWhere = $courseFilter
            ? "c.lms_event_id = {$eventId} AND ce.lms_course_id = {$courseFilter}"
            : "c.lms_event_id = {$eventId}";

        $ccWhere = $courseFilter
            ? "c.lms_event_id = {$eventId} AND ce.status = 'completed' AND ce.lms_course_id = {$courseFilter}"
            : "c.lms_event_id = {$eventId} AND ce.status = 'completed'";

        return $query
            ->leftJoin(DB::raw("(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE {$ccWhere} GROUP BY user_id) as cc"), 'cc.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw("(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE {$ceWhere} GROUP BY user_id) as ce"), 'ce.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, COUNT(DISTINCT lms_test_id) as cnt, AVG(percentage) as avg_pct FROM lms_test_attempts ta JOIN lms_tests t ON t.id = ta.lms_test_id WHERE t.lms_event_id = '.$eventId.' AND ta.passed = true GROUP BY user_id) as tp'), 'tp.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as cnt FROM lms_assignment_submissions asub JOIN lms_assignments a ON a.id = asub.lms_assignment_id WHERE a.lms_event_id = '.$eventId." AND asub.status = 'approved' GROUP BY user_id) as aa"), 'aa.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, SUM(points) as total FROM lms_gamification_points WHERE lms_event_id = '.$eventId.' AND user_id IS NOT NULL AND COALESCE(for_city_ranking_only, false) = false GROUP BY user_id) as gp'), 'gp.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT sp.user_id, MAX(sp.updated_at) as last_activity FROM lms_stage_progress sp JOIN lms_course_stages cs ON cs.id = sp.lms_course_stage_id JOIN lms_courses c ON c.id = cs.lms_course_id WHERE c.lms_event_id = '.$eventId.' GROUP BY sp.user_id) as la'), 'la.user_id', '=', 'lms_profiles.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'lms_profiles.role',
                DB::raw('COALESCE(ce.cnt, 0) as courses_enrolled'),
                DB::raw('COALESCE(cc.cnt, 0) as courses_completed'),
                DB::raw('COALESCE(tp.cnt, 0) as tests_passed'),
                DB::raw('COALESCE(ROUND(tp.avg_pct::numeric, 1), 0) as avg_test_score'),
                DB::raw('COALESCE(aa.cnt, 0) as assignments_approved'),
                DB::raw('COALESCE(gp.total, 0) as total_points'),
                DB::raw('la.last_activity'),
            )
            ->orderBy('users.name')
            ->get();
    }

    private function getStageProgress(int $eventId, ?string $roleFilter = null)
    {
        $query = DB::table('lms_course_stages')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_stages.lms_course_id')
            ->leftJoin('lms_course_modules', 'lms_course_modules.id', '=', 'lms_course_stages.lms_course_module_id')
            ->leftJoin('lms_course_enrollments', 'lms_course_enrollments.lms_course_id', '=', 'lms_courses.id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->leftJoin('lms_stage_progress', function ($join) {
                $join->on('lms_stage_progress.lms_course_stage_id', '=', 'lms_course_stages.id')
                    ->on('lms_stage_progress.user_id', '=', 'lms_course_enrollments.user_id');
            });

        if ($roleFilter) {
            $query->leftJoin('lms_profiles', function ($join) use ($eventId) {
                $join->on('lms_profiles.user_id', '=', 'lms_course_enrollments.user_id')
                    ->where('lms_profiles.lms_event_id', $eventId);
            })->where('lms_profiles.role', $roleFilter);
        }

        return $query
            ->select(
                'lms_course_stages.id',
                'lms_course_stages.title as stage_title',
                'lms_courses.id as course_id',
                'lms_courses.title as course_title',
                'lms_courses.position as course_position',
                'lms_course_modules.position as module_position',
                'lms_course_stages.lms_course_module_id',
                'lms_course_stages.position as stage_position',
                DB::raw('COUNT(DISTINCT lms_course_enrollments.user_id) as enrolled'),
                DB::raw('COUNT(DISTINCT lms_stage_progress.user_id) as opened'),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_stage_progress.status = 'in_progress' THEN lms_stage_progress.user_id END) as in_progress"),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_stage_progress.status = 'completed' THEN lms_stage_progress.user_id END) as completed"),
                DB::raw("GREATEST(COUNT(DISTINCT lms_course_enrollments.user_id) - COUNT(DISTINCT CASE WHEN lms_stage_progress.status IN ('in_progress', 'completed') THEN lms_stage_progress.user_id END), 0) as not_started"),
            )
            ->groupBy(
                'lms_course_stages.id',
                'lms_course_stages.title',
                'lms_courses.id',
                'lms_courses.title',
                'lms_courses.position',
                'lms_course_modules.position',
                'lms_course_stages.lms_course_module_id',
                'lms_course_stages.position',
            )
            ->orderBy('lms_courses.position')
            ->orderBy('lms_courses.title')
            ->orderByRaw('CASE WHEN lms_course_stages.lms_course_module_id IS NULL THEN -1 ELSE COALESCE(lms_course_modules.position, 9999) END')
            ->orderBy('lms_course_stages.position')
            ->orderBy('lms_course_stages.title')
            ->get();
    }

    /**
     * Динамика активности за период.
     *
     * `granularity=day` (default) — DATE(col); `granularity=week` —
     * DATE_TRUNC('week', col)::date (Postgres-only). Ключ корзины во всех
     * сериях — ISO-дата `YYYY-MM-DD`. Контракт совместим: дневной режим
     * сохраняет ключи `enrollments / completions / test_attempts`, недельный
     * добавляет `assignments_approved / tests_passed / stages_completed`
     * (в дневном режиме они также возвращаются — UI вправе их не отображать).
     * В ответ включено поле `granularity` для UI-разводки.
     */
    private function getActivityTimeline(int $eventId, ?string $dateFrom, ?string $dateTo, string $granularity = 'day')
    {
        $granularity = $granularity === 'week' ? 'week' : 'day';
        $defaultDays = $granularity === 'week' ? 90 : 30;
        $from = $dateFrom ? $dateFrom : now()->subDays($defaultDays)->format('Y-m-d');
        $to = $dateTo ? $dateTo : now()->format('Y-m-d');

        $bucket = fn (string $col) => $granularity === 'week'
            ? "DATE_TRUNC('week', {$col})::date"
            : "DATE({$col})";

        $enrollments = DB::table('lms_course_enrollments')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->whereBetween('lms_course_enrollments.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw($bucket('lms_course_enrollments.created_at').' as bucket'), DB::raw('COUNT(*) as count'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('count', 'bucket');

        $completions = DB::table('lms_course_enrollments')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->where('lms_course_enrollments.status', 'completed')
            ->whereNotNull('lms_course_enrollments.completed_at')
            ->whereBetween('lms_course_enrollments.completed_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw($bucket('lms_course_enrollments.completed_at').' as bucket'), DB::raw('COUNT(*) as count'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('count', 'bucket');

        $testAttempts = DB::table('lms_test_attempts')
            ->join('lms_tests', 'lms_tests.id', '=', 'lms_test_attempts.lms_test_id')
            ->where('lms_tests.lms_event_id', $eventId)
            ->whereBetween('lms_test_attempts.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw($bucket('lms_test_attempts.created_at').' as bucket'), DB::raw('COUNT(*) as count'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('count', 'bucket');

        $assignmentsApproved = DB::table('lms_assignment_reviews as r')
            ->join('lms_assignment_submissions as s', 's.id', '=', 'r.lms_assignment_submission_id')
            ->join('lms_assignments as a', 'a.id', '=', 's.lms_assignment_id')
            ->where('a.lms_event_id', $eventId)
            ->where('r.decision', 'approve')
            ->whereBetween('r.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw($bucket('r.created_at').' as bucket'), DB::raw('COUNT(*) as count'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('count', 'bucket');

        $testsPassed = DB::table('lms_test_attempts as ta')
            ->join('lms_tests as t', 't.id', '=', 'ta.lms_test_id')
            ->where('t.lms_event_id', $eventId)
            ->where('ta.passed', true)
            ->whereNotNull('ta.finished_at')
            ->whereBetween('ta.finished_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw($bucket('ta.finished_at').' as bucket'), DB::raw('COUNT(*) as count'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('count', 'bucket');

        $stagesCompleted = DB::table('lms_stage_progress as sp')
            ->join('lms_course_stages as cs', 'cs.id', '=', 'sp.lms_course_stage_id')
            ->join('lms_courses as c', 'c.id', '=', 'cs.lms_course_id')
            ->where('c.lms_event_id', $eventId)
            ->where('sp.status', 'completed')
            ->whereNotNull('sp.completed_at')
            ->whereBetween('sp.completed_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw($bucket('sp.completed_at').' as bucket'), DB::raw('COUNT(*) as count'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('count', 'bucket');

        return [
            'from' => $from,
            'to' => $to,
            'granularity' => $granularity,
            'enrollments' => $enrollments,
            'completions' => $completions,
            'test_attempts' => $testAttempts,
            'assignments_approved' => $assignmentsApproved,
            'tests_passed' => $testsPassed,
            'stages_completed' => $stagesCompleted,
        ];
    }

    private function getGroupStats(int $eventId)
    {
        return DB::table('lms_groups')
            ->where('lms_groups.lms_event_id', $eventId)
            ->leftJoin('lms_group_members', 'lms_groups.id', '=', 'lms_group_members.lms_group_id')
            ->leftJoin(DB::raw('(SELECT ce.user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = '.$eventId." AND ce.status = 'completed' GROUP BY ce.user_id) as cc"), 'cc.user_id', '=', 'lms_group_members.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, SUM(points) as total FROM lms_gamification_points WHERE lms_event_id = '.$eventId.' AND user_id IS NOT NULL AND COALESCE(for_city_ranking_only, false) = false GROUP BY user_id) as gp'), 'gp.user_id', '=', 'lms_group_members.user_id')
            ->select(
                'lms_groups.id',
                'lms_groups.title',
                DB::raw('COUNT(DISTINCT lms_group_members.user_id) as members_count'),
                DB::raw('COALESCE(SUM(cc.cnt), 0) as total_completions'),
                DB::raw('COALESCE(SUM(gp.total), 0) as total_points'),
                DB::raw('COALESCE(ROUND(AVG(cc.cnt)::numeric, 1), 0) as avg_completions'),
            )
            ->groupBy('lms_groups.id', 'lms_groups.title')
            ->orderByDesc('total_points')
            ->get();
    }

    private function getGamificationBreakdown(int $eventId)
    {
        return DB::table('lms_gamification_points')
            ->where('lms_gamification_points.lms_event_id', $eventId)
            ->where('lms_gamification_points.for_city_ranking_only', false)
            ->leftJoin('lms_gamification_rules', 'lms_gamification_rules.id', '=', 'lms_gamification_points.lms_gamification_rule_id')
            ->select(
                DB::raw("COALESCE(lms_gamification_rules.title, 'Вручную') as rule_title"),
                DB::raw("COALESCE(lms_gamification_rules.action, 'manual') as action"),
                DB::raw('SUM(lms_gamification_points.points) as total_points'),
                DB::raw('COUNT(*) as awards_count'),
                DB::raw('COUNT(DISTINCT lms_gamification_points.user_id) as unique_users'),
            )
            ->groupBy('lms_gamification_rules.id', 'lms_gamification_rules.title', 'lms_gamification_rules.action')
            ->orderByDesc('total_points')
            ->get();
    }

    private function getInactiveUsers(int $eventId)
    {
        return DB::table('lms_profiles')
            ->where('lms_profiles.lms_event_id', $eventId)
            ->join('users', 'users.id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT sp.user_id, MAX(sp.updated_at) as last_act FROM lms_stage_progress sp JOIN lms_course_stages cs ON cs.id = sp.lms_course_stage_id JOIN lms_courses c ON c.id = cs.lms_course_id WHERE c.lms_event_id = '.$eventId.' GROUP BY sp.user_id) as la'), 'la.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = '.$eventId.' GROUP BY user_id) as ce'), 'ce.user_id', '=', 'lms_profiles.user_id')
            ->where(function ($q) {
                $q->whereNull('la.last_act')
                    ->orWhere('la.last_act', '<', now()->subDays(14));
            })
            ->where(DB::raw('COALESCE(ce.cnt, 0)'), '>', 0)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'lms_profiles.role',
                DB::raw('COALESCE(ce.cnt, 0) as courses_enrolled'),
                DB::raw('la.last_act as last_activity'),
            )
            ->orderBy('la.last_act')
            ->limit(50)
            ->get();
    }

    private function writeCsvSection(string $title, int $eventId, LmsEvent $event): void
    {
        echo "\"=== {$title} ===\"\n";
        echo "\"Имя\";\"Email\";\"Роль\";\"Курсов записан\";\"Курсов завершено\";\"Тестов сдано\";\"Ср. балл тестов\";\"Заданий принято\";\"Баллы\";\"Последняя активность\"\n";
        $users = $this->getUserDetails($eventId, null, null);
        foreach ($users as $u) {
            $lastAct = $u->last_activity ? date('d.m.Y', strtotime($u->last_activity)) : '—';
            echo "\"{$u->name}\";\"{$u->email}\";\"".($u->role ?? '—')."\";\"{$u->courses_enrolled}\";\"{$u->courses_completed}\";\"{$u->tests_passed}\";\"{$u->avg_test_score}\";\"{$u->assignments_approved}\";\"{$u->total_points}\";\"{$lastAct}\"\n";
        }
        echo "\n";
    }

    private function writeCsvCourses(int $eventId): void
    {
        echo "\"=== КУРСЫ ===\"\n";
        echo "\"Курс\";\"Записано\";\"В процессе\";\"Завершено\";\"% завершения\"\n";
        $courses = $this->getCourseStats($eventId, null);
        foreach ($courses as $c) {
            $pct = $c->enrolled > 0 ? round($c->completed / $c->enrolled * 100, 1) : 0;
            echo "\"{$c->title}\";\"{$c->enrolled}\";\"{$c->in_progress}\";\"{$c->completed}\";\"{$pct}%\"\n";
        }
        echo "\n";
    }

    private function writeCsvTests(int $eventId): void
    {
        echo "\"=== ТЕСТЫ ===\"\n";
        echo "\"Тест\";\"Попыток\";\"Участников\";\"Сдало\";\"Ср. балл\";\"Мин. балл\";\"Макс. балл\"\n";
        $tests = $this->getTestStats($eventId, null);
        foreach ($tests as $t) {
            echo "\"{$t->title}\";\"{$t->total_attempts}\";\"{$t->attempted}\";\"{$t->passed}\";\"".round($t->avg_score, 1).'%";"'.round($t->min_score, 1).'%";"'.round($t->max_score, 1)."%\"\n";
        }
        echo "\n";
    }

    private function writeCsvAssignments(int $eventId): void
    {
        echo "\"=== ЗАДАНИЯ ===\"\n";
        echo "\"Задание\";\"Сдано работ\";\"На проверке\";\"Принято\";\"Отклонено\";\"% принятия\"\n";
        $assignments = $this->getAssignmentStats($eventId, null);
        foreach ($assignments as $a) {
            $pct = $a->submitted > 0 ? round($a->approved / $a->submitted * 100, 1) : 0;
            echo "\"{$a->title}\";\"{$a->submitted}\";\"{$a->pending}\";\"{$a->approved}\";\"{$a->rejected}\";\"{$pct}%\"\n";
        }
        echo "\n";
    }

    private function writeCsvStages(int $eventId): void
    {
        echo "\"=== ЭТАПЫ КУРСОВ ===\"\n";
        echo "\"Курс\";\"Этап\";\"Записано на программу\";\"Открыли этап\";\"Не начали\";\"В процессе\";\"Завершили\";\"% завершения\"\n";
        $stages = $this->getStageProgress($eventId);
        foreach ($stages as $s) {
            $pct = $s->enrolled > 0 ? round($s->completed / $s->enrolled * 100, 1) : 0;
            echo "\"{$s->course_title}\";\"{$s->stage_title}\";\"{$s->enrolled}\";\"{$s->opened}\";\"{$s->not_started}\";\"{$s->in_progress}\";\"{$s->completed}\";\"{$pct}%\"\n";
        }
        echo "\n";
    }

    private function writeCsvDeadline(int $eventId): void
    {
        echo "\"=== СОБЛЮДЕНИЕ СРОКОВ ===\"\n";
        echo "\"Задание\";\"Курс\";\"Город\";\"Факультет\";\"Вне плана\";\"Всего\";\"В срок\";\"С задержкой\";\"Просрочено\";\"Ср. задержка (дн.)\"\n";
        $rows = $this->getDeadlineCompliance($eventId);
        foreach ($rows as $r) {
            $course = $this->csvCell($r->course_title);
            $city = $this->csvCell($r->city_name);
            $faculty = $this->csvCell($r->faculty);
            $avg = $r->avg_delay_days !== null ? (string) $r->avg_delay_days : '—';
            $orphan = $r->is_orphan ? 'да' : 'нет';
            echo "\"{$r->assignment_title}\";\"{$course}\";\"{$city}\";\"{$faculty}\";\"{$orphan}\";\"{$r->total_users}\";\"{$r->on_time}\";\"{$r->late}\";\"{$r->overdue}\";\"{$avg}\"\n";
        }
        echo "\n";
    }

    private function writeCsvPersonal(int $eventId): void
    {
        echo "\"=== ПРОГРЕСС УЧАСТНИКОВ ===\"\n";
        echo "\"Имя\";\"Email\";\"Роль\";\"Город\";\"Заданий\";\"Принято\";\"% заданий\";\"Тестов\";\"Сдано\";\"% тестов\";\"Этапов\";\"Завершено\";\"% этапов\";\"Общий %\";\"Доп. (вне плана)\";\"Доп. принято\"\n";
        $rows = $this->getPersonalProgress($eventId);
        foreach ($rows as $r) {
            $city = $this->csvCell($r->city_name);
            $role = $this->csvCell($r->role);
            $orphanTotal = (int) ($r->assignments_orphan_total ?? 0);
            $orphanDone = (int) ($r->assignments_orphan_done ?? 0);
            echo "\"{$r->user_name}\";\"{$r->user_email}\";\"{$role}\";\"{$city}\";\"{$r->assignments_total}\";\"{$r->assignments_done}\";\"{$r->assignments_pct}%\";\"{$r->tests_total}\";\"{$r->tests_done}\";\"{$r->tests_pct}%\";\"{$r->stages_total}\";\"{$r->stages_done}\";\"{$r->stages_pct}%\";\"{$r->overall_pct}%\";\"{$orphanTotal}\";\"{$orphanDone}\"\n";
        }
        echo "\n";
    }

    private function writeCsvCities(int $eventId, int $courseId): void
    {
        echo "\"=== СРАВНЕНИЕ ГОРОДОВ ===\"\n";
        echo "\"Программа ID\";\"{$courseId}\"\n";
        echo "\"Город\";\"Участников\";\"% в срок\";\"% просрочено\";\"Ср. общий %\";\"Ср. балл тестов\"\n";
        $rows = $this->getCityComparison($eventId, $courseId);
        foreach ($rows as $r) {
            $city = $this->csvCell($r->city_name);
            echo "\"{$city}\";\"{$r->participants_count}\";\"{$r->on_time_pct}%\";\"{$r->overdue_pct}%\";\"{$r->avg_overall_pct}%\";\"{$r->avg_test_score}%\"\n";
        }
        echo "\n";
    }

    private function writeCsvDynamics(int $eventId, ?string $dateFrom, ?string $dateTo, string $granularity): void
    {
        $timeline = $this->getActivityTimeline($eventId, $dateFrom, $dateTo, $granularity);
        $modeLabel = $timeline['granularity'] === 'week' ? 'НЕДЕЛЯ' : 'ДЕНЬ';
        echo "\"=== ДИНАМИКА ({$modeLabel}) ===\"\n";
        echo "\"Период\";\"{$timeline['from']} — {$timeline['to']}\"\n";
        echo "\"Корзина\";\"Записи\";\"Завершения курсов\";\"Попытки тестов\";\"Принятые задания\";\"Сданные тесты\";\"Завершённые этапы\"\n";

        $series = ['enrollments', 'completions', 'test_attempts', 'assignments_approved', 'tests_passed', 'stages_completed'];
        $allKeys = [];
        foreach ($series as $key) {
            foreach ($timeline[$key] as $bucket => $_) {
                $allKeys[$bucket] = true;
            }
        }
        ksort($allKeys);

        foreach (array_keys($allKeys) as $bucket) {
            $vals = [];
            foreach ($series as $key) {
                $vals[] = (int) ($timeline[$key][$bucket] ?? 0);
            }
            echo "\"{$bucket}\";\"{$vals[0]}\";\"{$vals[1]}\";\"{$vals[2]}\";\"{$vals[3]}\";\"{$vals[4]}\";\"{$vals[5]}\"\n";
        }
        echo "\n";
    }

    private function csvCell(?string $value): string
    {
        $value = $value ?? '—';

        return str_replace('"', '""', $value);
    }
}
