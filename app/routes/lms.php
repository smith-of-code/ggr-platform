<?php

use App\Http\Controllers\Lms\AuthController;
use App\Http\Controllers\Lms\DashboardController;
use App\Http\Controllers\Lms\CourseController;
use App\Http\Controllers\Lms\StageController;
use App\Http\Controllers\Lms\TestController;
use App\Http\Controllers\Lms\AssignmentController;
use App\Http\Controllers\Lms\TrajectoryController;
use App\Http\Controllers\Lms\VideoController;
use App\Http\Controllers\Lms\KnowledgeBaseController;
use App\Http\Controllers\Lms\MaterialController;
use App\Http\Controllers\Lms\LeaderController;
use App\Http\Controllers\Lms\GamificationController;
use App\Http\Controllers\Lms\ProfileController;
use App\Http\Controllers\Lms\Admin\EventController as AdminEventController;
use App\Http\Controllers\Lms\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Lms\Admin\TestController as AdminTestController;
use App\Http\Controllers\Lms\Admin\AssignmentController as AdminAssignmentController;
use App\Http\Controllers\Lms\Admin\TrajectoryController as AdminTrajectoryController;
use App\Http\Controllers\Lms\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Lms\Admin\KnowledgeBaseController as AdminKbController;
use App\Http\Controllers\Lms\Admin\MaterialController as AdminMaterialController;
use App\Http\Controllers\Lms\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Lms\Admin\UserController as AdminUserController;
use App\Http\Controllers\Lms\Admin\GamificationController as AdminGamificationController;
use Illuminate\Support\Facades\Route;

// ── LMS Auth ──
Route::prefix('lms/{event:slug}')->name('lms.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ── LMS Participant & Leader (auth required) ──
Route::prefix('lms/{event:slug}')->name('lms.')->middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');

    // Course stages
    Route::get('/courses/{course}/stages/{stage}', [StageController::class, 'show'])->name('stages.show');
    Route::post('/courses/{course}/stages/{stage}/complete', [StageController::class, 'complete'])->name('stages.complete');
    Route::post('/courses/{course}/stages/{stage}/scorm', [StageController::class, 'scormData'])->name('stages.scorm');

    // Tests
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/{test}', [TestController::class, 'show'])->name('tests.show');
    Route::post('/tests/{test}/start', [TestController::class, 'start'])->name('tests.start');
    Route::post('/tests/{test}/attempts/{attempt}/submit', [TestController::class, 'submit'])->name('tests.submit');
    Route::get('/tests/{test}/attempts/{attempt}/result', [TestController::class, 'result'])->name('tests.result');

    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::patch('/assignments/{assignment}/submissions/{submission}', [AssignmentController::class, 'update'])->name('assignments.update');

    // Trajectories
    Route::get('/trajectories', [TrajectoryController::class, 'index'])->name('trajectories.index');
    Route::get('/trajectories/{trajectory}', [TrajectoryController::class, 'show'])->name('trajectories.show');
    Route::post('/trajectories/{trajectory}/enroll', [TrajectoryController::class, 'enroll'])->name('trajectories.enroll');

    // Videos
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');

    // Knowledge Base
    Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('kb.index');
    Route::get('/knowledge/{section}', [KnowledgeBaseController::class, 'show'])->name('kb.show');

    // Materials
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{section}', [MaterialController::class, 'show'])->name('materials.show');

    // Gamification
    Route::get('/leaderboard', [GamificationController::class, 'leaderboard'])->name('gamification.leaderboard');
    Route::get('/my-points', [GamificationController::class, 'myPoints'])->name('gamification.my-points');

    // Leader Cabinet
    Route::prefix('leader')->name('leader.')->group(function () {
        Route::get('/', [LeaderController::class, 'index'])->name('dashboard');
        Route::get('/groups', [LeaderController::class, 'groups'])->name('groups');
        Route::get('/groups/{group}', [LeaderController::class, 'groupDetail'])->name('groups.show');
        Route::get('/users/{user}', [LeaderController::class, 'userProgress'])->name('users.progress');
        Route::post('/report', [LeaderController::class, 'sendReport'])->name('report');
    });
});

// ── LMS Admin ──
Route::prefix('lms-admin')->name('lms.admin.')->middleware(['auth'])->group(function () {
    Route::resource('events', AdminEventController::class);

    Route::prefix('{event}')->group(function () {
        Route::resource('courses', AdminCourseController::class);
        Route::resource('tests', AdminTestController::class);
        Route::resource('assignments', AdminAssignmentController::class);
        Route::post('assignments/{assignment}/submissions/{submission}/review', [AdminAssignmentController::class, 'review'])->name('assignments.review');
        Route::resource('trajectories', AdminTrajectoryController::class);
        Route::resource('videos', AdminVideoController::class);
        Route::resource('kb', AdminKbController::class);
        Route::resource('materials', AdminMaterialController::class);
        Route::resource('groups', AdminGroupController::class);
        Route::resource('users', AdminUserController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('gamification', AdminGamificationController::class);
        Route::post('gamification/manual-points', [AdminGamificationController::class, 'manualPoints'])->name('gamification.manual-points');
    });
});
