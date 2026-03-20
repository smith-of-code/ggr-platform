<?php

namespace App\Http\Controllers\Lms\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lms\LmsEvent;
use App\Models\Lms\LmsProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(LmsEvent $event): Response
    {
        $eventId = $event->id;

        $totalUsers = LmsProfile::where('lms_event_id', $eventId)->count();

        $courseStats = DB::table('lms_courses')
            ->where('lms_courses.lms_event_id', $eventId)
            ->leftJoin('lms_course_enrollments', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
            ->select(
                'lms_courses.id',
                'lms_courses.title',
                DB::raw('COUNT(DISTINCT lms_course_enrollments.user_id) as enrolled'),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'completed' THEN lms_course_enrollments.user_id END) as completed"),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'in_progress' THEN lms_course_enrollments.user_id END) as in_progress"),
            )
            ->groupBy('lms_courses.id', 'lms_courses.title')
            ->orderBy('lms_courses.title')
            ->get();

        $testStats = DB::table('lms_tests')
            ->where('lms_tests.lms_event_id', $eventId)
            ->leftJoin('lms_test_attempts', 'lms_tests.id', '=', 'lms_test_attempts.lms_test_id')
            ->select(
                'lms_tests.id',
                'lms_tests.title',
                DB::raw('COUNT(DISTINCT lms_test_attempts.user_id) as attempted'),
                DB::raw("COUNT(DISTINCT CASE WHEN lms_test_attempts.passed = true THEN lms_test_attempts.user_id END) as passed"),
                DB::raw('COALESCE(AVG(lms_test_attempts.percentage), 0) as avg_score'),
                DB::raw('COUNT(lms_test_attempts.id) as total_attempts'),
            )
            ->groupBy('lms_tests.id', 'lms_tests.title')
            ->orderBy('lms_tests.title')
            ->get();

        $assignmentStats = DB::table('lms_assignments')
            ->where('lms_assignments.lms_event_id', $eventId)
            ->leftJoin('lms_assignment_submissions', 'lms_assignments.id', '=', 'lms_assignment_submissions.lms_assignment_id')
            ->select(
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

        $userDetails = DB::table('lms_profiles')
            ->where('lms_profiles.lms_event_id', $eventId)
            ->join('users', 'users.id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = ' . $eventId . " AND ce.status = 'completed' GROUP BY user_id) as cc"), 'cc.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = ' . $eventId . ' GROUP BY user_id) as ce'), 'ce.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, COUNT(DISTINCT lms_test_id) as cnt, AVG(percentage) as avg_pct FROM lms_test_attempts ta JOIN lms_tests t ON t.id = ta.lms_test_id WHERE t.lms_event_id = ' . $eventId . ' AND ta.passed = true GROUP BY user_id) as tp'), 'tp.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw("(SELECT user_id, COUNT(*) as cnt FROM lms_assignment_submissions asub JOIN lms_assignments a ON a.id = asub.lms_assignment_id WHERE a.lms_event_id = " . $eventId . " AND asub.status = 'approved' GROUP BY user_id) as aa"), 'aa.user_id', '=', 'lms_profiles.user_id')
            ->leftJoin(DB::raw('(SELECT user_id, SUM(points) as total FROM lms_gamification_points WHERE lms_event_id = ' . $eventId . ' GROUP BY user_id) as gp'), 'gp.user_id', '=', 'lms_profiles.user_id')
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
            )
            ->orderBy('users.name')
            ->get();

        $totalCourses = DB::table('lms_courses')->where('lms_event_id', $eventId)->count();
        $totalTests = DB::table('lms_tests')->where('lms_event_id', $eventId)->count();
        $totalAssignments = DB::table('lms_assignments')->where('lms_event_id', $eventId)->count();

        $summary = [
            'total_users' => $totalUsers,
            'total_courses' => $totalCourses,
            'total_tests' => $totalTests,
            'total_assignments' => $totalAssignments,
            'avg_course_completion' => $totalUsers > 0 && $totalCourses > 0
                ? round($courseStats->sum('completed') / max($courseStats->count(), 1) / $totalUsers * 100, 1)
                : 0,
            'avg_test_pass_rate' => $testStats->count() > 0
                ? round($testStats->avg(fn ($t) => $t->attempted > 0 ? ($t->passed / $t->attempted * 100) : 0), 1)
                : 0,
        ];

        return Inertia::render('Lms/Admin/Reports/Index', [
            'event' => $event->only(['id', 'slug', 'title']),
            'summary' => $summary,
            'courseStats' => $courseStats,
            'testStats' => $testStats,
            'assignmentStats' => $assignmentStats,
            'userDetails' => $userDetails,
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
        $rows[] = ['Отчёт по событию: ' . $event->title];
        $rows[] = ['Дата: ' . now()->format('d.m.Y H:i')];
        $rows[] = [];

        if (in_array('users', $validated['sections'])) {
            $rows[] = ['=== УЧАСТНИКИ ==='];
            $rows[] = ['Имя', 'Email', 'Роль', 'Курсов записан', 'Курсов завершено', 'Тестов сдано', 'Ср. балл тестов', 'Заданий принято', 'Баллы'];

            $users = DB::table('lms_profiles')
                ->where('lms_profiles.lms_event_id', $eventId)
                ->join('users', 'users.id', '=', 'lms_profiles.user_id')
                ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = ' . $eventId . ' GROUP BY user_id) as ce'), 'ce.user_id', '=', 'lms_profiles.user_id')
                ->leftJoin(DB::raw('(SELECT user_id, COUNT(*) as cnt FROM lms_course_enrollments ce JOIN lms_courses c ON c.id = ce.lms_course_id WHERE c.lms_event_id = ' . $eventId . " AND ce.status = 'completed' GROUP BY user_id) as cc"), 'cc.user_id', '=', 'lms_profiles.user_id')
                ->leftJoin(DB::raw('(SELECT user_id, COUNT(DISTINCT lms_test_id) as cnt, AVG(percentage) as avg_pct FROM lms_test_attempts ta JOIN lms_tests t ON t.id = ta.lms_test_id WHERE t.lms_event_id = ' . $eventId . ' AND ta.passed = true GROUP BY user_id) as tp'), 'tp.user_id', '=', 'lms_profiles.user_id')
                ->leftJoin(DB::raw("(SELECT user_id, COUNT(*) as cnt FROM lms_assignment_submissions asub JOIN lms_assignments a ON a.id = asub.lms_assignment_id WHERE a.lms_event_id = " . $eventId . " AND asub.status = 'approved' GROUP BY user_id) as aa"), 'aa.user_id', '=', 'lms_profiles.user_id')
                ->leftJoin(DB::raw('(SELECT user_id, SUM(points) as total FROM lms_gamification_points WHERE lms_event_id = ' . $eventId . ' GROUP BY user_id) as gp'), 'gp.user_id', '=', 'lms_profiles.user_id')
                ->select('users.name', 'users.email', 'lms_profiles.role',
                    DB::raw('COALESCE(ce.cnt, 0) as ce'), DB::raw('COALESCE(cc.cnt, 0) as cc'),
                    DB::raw('COALESCE(tp.cnt, 0) as tp'), DB::raw('COALESCE(ROUND(tp.avg_pct::numeric, 1), 0) as avg_pct'),
                    DB::raw('COALESCE(aa.cnt, 0) as aa'), DB::raw('COALESCE(gp.total, 0) as gp'))
                ->orderBy('users.name')->get();

            foreach ($users as $u) {
                $rows[] = [$u->name, $u->email, $u->role ?? '—', $u->ce, $u->cc, $u->tp, $u->avg_pct, $u->aa, $u->gp];
            }
            $rows[] = [];
        }

        if (in_array('courses', $validated['sections'])) {
            $rows[] = ['=== КУРСЫ ==='];
            $rows[] = ['Курс', 'Записано', 'В процессе', 'Завершено', '% завершения'];
            $courses = DB::table('lms_courses')->where('lms_courses.lms_event_id', $eventId)
                ->leftJoin('lms_course_enrollments', 'lms_courses.id', '=', 'lms_course_enrollments.lms_course_id')
                ->select('lms_courses.title',
                    DB::raw('COUNT(DISTINCT lms_course_enrollments.user_id) as enrolled'),
                    DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'in_progress' THEN lms_course_enrollments.user_id END) as in_progress"),
                    DB::raw("COUNT(DISTINCT CASE WHEN lms_course_enrollments.status = 'completed' THEN lms_course_enrollments.user_id END) as completed"))
                ->groupBy('lms_courses.id', 'lms_courses.title')->orderBy('lms_courses.title')->get();
            foreach ($courses as $c) {
                $pct = $c->enrolled > 0 ? round($c->completed / $c->enrolled * 100, 1) : 0;
                $rows[] = [$c->title, $c->enrolled, $c->in_progress, $c->completed, $pct . '%'];
            }
            $rows[] = [];
        }

        if (in_array('tests', $validated['sections'])) {
            $rows[] = ['=== ТЕСТЫ ==='];
            $rows[] = ['Тест', 'Попыток', 'Участников', 'Сдало', 'Ср. балл'];
            $tests = DB::table('lms_tests')->where('lms_tests.lms_event_id', $eventId)
                ->leftJoin('lms_test_attempts', 'lms_tests.id', '=', 'lms_test_attempts.lms_test_id')
                ->select('lms_tests.title',
                    DB::raw('COUNT(lms_test_attempts.id) as attempts'),
                    DB::raw('COUNT(DISTINCT lms_test_attempts.user_id) as users'),
                    DB::raw("COUNT(DISTINCT CASE WHEN lms_test_attempts.passed = true THEN lms_test_attempts.user_id END) as passed"),
                    DB::raw('COALESCE(ROUND(AVG(lms_test_attempts.percentage)::numeric, 1), 0) as avg_score'))
                ->groupBy('lms_tests.id', 'lms_tests.title')->orderBy('lms_tests.title')->get();
            foreach ($tests as $t) {
                $rows[] = [$t->title, $t->attempts, $t->users, $t->passed, $t->avg_score . '%'];
            }
            $rows[] = [];
        }

        $csvContent = '';
        foreach ($rows as $row) {
            $csvContent .= implode(';', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        $csvContent = "\xEF\xBB\xBF" . $csvContent;

        Mail::raw('Отчёт по событию «' . $event->title . '» во вложении.', function ($message) use ($validated, $event, $csvContent) {
            $message->to($validated['email'])
                ->subject('Отчёт: ' . $event->title . ' — ' . now()->format('d.m.Y'))
                ->attachData($csvContent, 'report_' . $event->slug . '_' . now()->format('Y-m-d') . '.csv', [
                    'mime' => 'text/csv',
                ]);
        });

        return redirect()->back()->with('success', 'Отчёт отправлен на ' . $validated['email']);
    }
}
