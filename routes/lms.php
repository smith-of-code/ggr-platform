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
use App\Http\Controllers\Lms\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Lms\Admin\GamificationController as AdminGamificationController;
use App\Http\Controllers\Lms\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Lms\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Lms\Admin\UploadController as AdminUploadController;
use Illuminate\Support\Facades\Route;

// ── LMS Auth ──
Route::prefix('lms/{event:slug}')->name('lms.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/invite/{token}', [AuthController::class, 'showInvite'])->name('invite');
    Route::post('/invite/{token}', [AuthController::class, 'registerByInvite'])->name('invite.register');
    Route::get('/activate/{token}', [AuthController::class, 'showActivate'])->name('activate');
    Route::post('/activate/{token}', [AuthController::class, 'activate'])->name('activate.submit');
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
    Route::post('/courses/{course}/stages/{stage}/heartbeat', [StageController::class, 'heartbeat'])->name('stages.heartbeat');

    // Tests
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index');
    Route::get('/tests/{test}', [TestController::class, 'show'])->name('tests.show');
    Route::post('/tests/{test}/start', [TestController::class, 'start'])->name('tests.start');
    Route::post('/tests/{test}/attempts/{attempt}/submit', [TestController::class, 'submit'])->name('tests.submit');
    Route::get('/tests/{test}/attempts/{attempt}', [TestController::class, 'take'])->name('tests.take');
    Route::get('/tests/{test}/attempts/{attempt}/result', [TestController::class, 'result'])->name('tests.result');

    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::post('/assignments/{assignment}/comment', [AssignmentController::class, 'comment'])->name('assignments.comment');
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
        Route::get('search-modules', [AdminCourseController::class, 'searchModules'])->name('search.modules');
        Route::get('search-stages', [AdminCourseController::class, 'searchStages'])->name('search.stages');
        Route::post('scorm-upload', [AdminCourseController::class, 'uploadScorm'])->name('scorm.upload');
        Route::resource('tests', AdminTestController::class);
        Route::resource('assignments', AdminAssignmentController::class);
        Route::post('assignments/{assignment}/submissions/{submission}/review', [AdminAssignmentController::class, 'review'])->name('assignments.review');
        Route::post('assignments/{assignment}/submissions/{submission}/comment', [AdminAssignmentController::class, 'comment'])->name('assignments.comment');
        Route::resource('trajectories', AdminTrajectoryController::class);
        Route::resource('videos', AdminVideoController::class);
        Route::resource('kb', AdminKbController::class);
        Route::resource('materials', AdminMaterialController::class);
        Route::resource('groups', AdminGroupController::class);
        Route::resource('users', AdminUserController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);
        Route::post('users-import', [AdminUserController::class, 'import'])->name('users.import');
        Route::post('users-send-invitations', [AdminUserController::class, 'sendInvitations'])->name('users.send-invitations');
        Route::post('users-bulk-enroll', [AdminUserController::class, 'bulkEnroll'])->name('users.bulk-enroll');
        Route::get('users-template', [AdminUserController::class, 'downloadTemplate'])->name('users.template');
        Route::post('invitations', [AdminInvitationController::class, 'store'])->name('invitations.store');
        Route::post('invitations/{invitation}/toggle', [AdminInvitationController::class, 'toggle'])->name('invitations.toggle');
        Route::delete('invitations/{invitation}', [AdminInvitationController::class, 'destroy'])->name('invitations.destroy');
        Route::get('enrollments', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('courses/{course}/enrollments', [AdminEnrollmentController::class, 'courseEnrollments'])->name('enrollments.course');
        Route::post('enrollments/{enrollment}/approve', [AdminEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('enrollments/{enrollment}/reject', [AdminEnrollmentController::class, 'reject'])->name('enrollments.reject');
        Route::resource('gamification', AdminGamificationController::class);
        Route::post('gamification/manual-points', [AdminGamificationController::class, 'manualPoints'])->name('gamification.manual-points');
        Route::resource('roles', AdminRoleController::class)->except(['show']);
        Route::post('upload/image', [AdminUploadController::class, 'image'])->name('upload.image');
    });
});
