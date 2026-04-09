<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use App\Support\MailDisplayName;
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
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $totalUsers = LmsProfile::where('lms_event_id', $eventId)
            ->when($roleFilter, fn ($q) => $q->where('role', $roleFilter))
            ->count();

        $courseStats = $this->getCourseStats($eventId, $roleFilter);
        $testStats = $this->getTestStats($eventId, $roleFilter);
        $assignmentStats = $this->getAssignmentStats($eventId, $roleFilter);
        $userDetails = $this->getUserDetails($eventId, $roleFilter, $courseFilter);
        $stageProgress = $this->getStageProgress($eventId);
        $activityTimeline = $this->getActivityTimeline($eventId, $dateFrom, $dateTo);
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
                ->where('lms_event_id', $eventId)->sum('points'),
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

        return Inertia::render('Lms/Admin/Reports/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'summary' => $summary,
            'courseStats' => $courseStats,
            'testStats' => $testStats,
            'assignmentStats' => $assignmentStats,
            'userDetails' => $userDetails,
            'stageProgress' => $stageProgress,
            'activityTimeline' => $activityTimeline,
            'groupStats' => $groupStats,
            'gamificationBreakdown' => $gamificationBreakdown,
            'inactiveUsers' => $inactiveUsers,
            'filters' => [
                'role' => $roleFilter,
                'course_id' => $courseFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'availableRoles' => $roles,
            'availableCourses' => $courses,
        ]);
    }

    public function download(Request $request, LmsEvent $event): StreamedResponse
    {
        $eventId = $event->id;
        $section = $request->query('section', 'all');

        return response()->streamDownload(function () use ($eventId, $event, $section) {
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
        }, 'report_'.$event->slug.'_'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function sendEmail(Request $request, LmsEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'sections' => ['required', 'array'],
        ]);

        $eventId = $event->id;

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
            $rows[] = ['Курс', 'Этап', 'Начали', 'В процессе', 'Завершили', '% завершения'];
            $stages = $this->getStageProgress($eventId);
            foreach ($stages as $s) {
                $total = $s->started + $s->in_progress + $s->completed;
                $pct = $total > 0 ? round($s->completed / $total * 100, 1) : 0;
                $rows[] = [$s->course_title, $s->stage_title, $s->started, $s->in_progress, $s->completed, $pct.'%'];
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
            ->leftJoin(DB::raw('(SELECT user_id, SUM(points) as total FROM lms_gamification_points WHERE lms_event_id = '.$eventId.' GROUP BY user_id) as gp'), 'gp.user_id', '=', 'lms_profiles.user_id')
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

    private function getStageProgress(int $eventId)
    {
        return DB::table('lms_course_stages')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_stages.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->leftJoin('lms_stage_progress', 'lms_stage_progress.lms_course_stage_id', '=', 'lms_course_stages.id')
            ->select(
                'lms_course_stages.id',
                'lms_course_stages.title as stage_title',
                'lms_courses.id as course_id',
                'lms_courses.title as course_title',
                DB::raw("COUNT(DISTINCT CASE WHEN lms_stage_progress.status = 'not_started' THEN lms_stage_progress.user_id END) as started"),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_stage_progress.status = 'in_progress' THEN lms_stage_progress.user_id END) as in_progress"),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_stage_progress.status = 'completed' THEN lms_stage_progress.user_id END) as completed"),
                DB::raw('COUNT(DISTINCT lms_stage_progress.user_id) as total_users'),
            )
            ->groupBy('lms_course_stages.id', 'lms_course_stages.title', 'lms_courses.id', 'lms_courses.title')
            ->orderBy('lms_courses.title')
            ->orderBy('lms_course_stages.position')
            ->get();
    }

    private function getActivityTimeline(int $eventId, ?string $dateFrom, ?string $dateTo)
    {
        $from = $dateFrom ? $dateFrom : now()->subDays(30)->format('Y-m-d');
        $to = $dateTo ? $dateTo : now()->format('Y-m-d');

        $enrollments = DB::table('lms_course_enrollments')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->whereBetween('lms_course_enrollments.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw('DATE(lms_course_enrollments.created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $completions = DB::table('lms_course_enrollments')
            ->join('lms_courses', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
            ->where('lms_courses.lms_event_id', $eventId)
            ->where('lms_course_enrollments.status', 'completed')
            ->whereNotNull('lms_course_enrollments.completed_at')
            ->whereBetween('lms_course_enrollments.completed_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw('DATE(lms_course_enrollments.completed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $testAttempts = DB::table('lms_test_attempts')
            ->join('lms_tests', 'lms_tests.id', '=', 'lms_test_attempts.lms_test_id')
            ->where('lms_tests.lms_event_id', $eventId)
            ->whereBetween('lms_test_attempts.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select(DB::raw('DATE(lms_test_attempts.created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return [
            'from' => $from,
            'to' => $to,
            'enrollments' => $enrollments,
            'completions' => $completions,
            'test_attempts' => $testAttempts,
        ];
    }

    private function getGroupStats(int $eventId)
    {
        return DB::table('lms_groups')
            ->where('lms_groups.lms_event_id', $eventId)
            ->leftJoin('lms_group_members', 'lms_groups.id', '=', 'lms_group_members.lms_group_id')
            ->leftJoin(DB::raw('(SELECT ce.user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = '.$eventId." AND ce.status = 'completed' GROUP BY ce.user_id) as cc"), 'cc.user_id', '=', 'lms_group_members.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, SUM(points) as total FROM lms_gamification_points WHERE lms_event_id = '.$eventId.' GROUP BY user_id) as gp'), 'gp.user_id', '=', 'lms_group_members.user_id')
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
        echo "\"Курс\";\"Этап\";\"Начали\";\"В процессе\";\"Завершили\"\n";
        $stages = $this->getStageProgress($eventId);
        foreach ($stages as $s) {
            echo "\"{$s->course_title}\";\"{$s->stage_title}\";\"{$s->started}\";\"{$s->in_progress}\";\"{$s->completed}\"\n";
        }
        echo "\n";
    }
}
