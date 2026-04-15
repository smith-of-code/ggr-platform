<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AtomsVkusaController as AdminAtomsVkusaController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\BlogSubscriberController as AdminBlogSubscriberController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DirectionController as AdminDirectionController;
use App\Http\Controllers\Admin\EducationProductController as AdminEducationProductController;
use App\Http\Controllers\Admin\MainPageController as AdminMainPageController;
use App\Http\Controllers\Admin\OpportunityToursPageController as AdminOpportunityToursPageController;
use App\Http\Controllers\Admin\PageVisibilityController as AdminPageVisibilityController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\ResearchPageController as AdminResearchPageController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TimelineEventController as AdminTimelineController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\TourReviewController as AdminTourReviewController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\TourCabinetDirectionCitiesController as AdminTourCabinetDirectionCitiesController;
use App\Http\Controllers\Admin\TourCabinetHubController as AdminTourCabinetHubController;
use App\Http\Controllers\Admin\TourCabinetStage2QuestionsController as AdminTourCabinetStage2QuestionsController;
use App\Http\Controllers\Admin\TourCabinetFormsController as AdminTourCabinetFormsController;
use App\Http\Controllers\Admin\VacancyController as AdminVacancyController;
use App\Http\Controllers\Admin\VshgrPageController as AdminVshgrPageController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogSubscriptionController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Lms\SocialAuthController;
use App\Http\Controllers\OpportunityToursController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ResearchPageController;
use App\Http\Controllers\TourCabinetContestController;
use App\Http\Controllers\TourCabinetController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourReviewController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;

// ── Public portal (no auth required) ──
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/{slug}', [CityController::class, 'show'])->name('cities.show');
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');
Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');

Route::get('/moving', [HomeController::class, 'moving'])->name('moving');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::post('/blog/subscribe', [BlogSubscriptionController::class, 'subscribe'])->name('blog.subscribe');
Route::get('/blog/unsubscribe/{token}', [BlogSubscriptionController::class, 'unsubscribe'])->name('blog.unsubscribe');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/vshgr', [EducationController::class, 'index'])->name('education.index');
Route::get('/vshgr/{slug}', [EducationController::class, 'show'])->name('education.show');

Route::get('/research', [ResearchPageController::class, 'index'])->name('research.index');
Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/recipes/{slug}', [RecipeController::class, 'show'])->name('recipes.show');

Route::get('/opportunity-tours', [OpportunityToursController::class, 'index'])->name('opportunity-tours.index');

// Личный кабинет участника туров / конкурса (отдельная регистрация; формы из LMS события из config/tour_cabinet.php)
Route::prefix('tour-cabinet')->name('tour-cabinet.')->group(function () {
    Route::middleware('tour-cabinet')->group(function () {
        Route::get('/', [TourCabinetController::class, 'dashboard'])->name('dashboard');
        Route::patch('/profile', [TourCabinetController::class, 'updateProfile'])->name('profile.update');
        Route::get('/contest', [TourCabinetContestController::class, 'show'])->name('contest');
        Route::post('/contest/direction', [TourCabinetContestController::class, 'storeDirection'])->name('contest.direction');
        Route::post('/contest/cities', [TourCabinetContestController::class, 'storeCities'])->name('contest.cities');
        Route::get('/contest/cities/{city}/form', [TourCabinetContestController::class, 'startCityForm'])->name('contest.city-form');
        Route::post('/contest/complete-stage-1', [TourCabinetContestController::class, 'completeStage1'])->name('contest.complete-stage-1');
        Route::get('/contest/stage-2', [TourCabinetContestController::class, 'showStage2'])->name('contest.stage2');
        Route::post('/contest/stage-2', [TourCabinetContestController::class, 'storeStage2'])->name('contest.stage2.store');
        Route::get('/contest/stage-3', [TourCabinetContestController::class, 'showStage3'])->name('contest.stage3');
        Route::post('/contest/stage-3', [TourCabinetContestController::class, 'storeStage3'])->name('contest.stage3.store');
    });
    Route::middleware('tour-cabinet.guest')->group(function () {
        Route::get('/login', [TourCabinetController::class, 'showLogin'])->name('login');
        Route::post('/login', [TourCabinetController::class, 'login'])->name('login.store');
        Route::get('/register', [TourCabinetController::class, 'showRegister'])->name('register');
        Route::post('/register', [TourCabinetController::class, 'register'])->name('register.store');
    });
    Route::post('/logout', [TourCabinetController::class, 'logout'])->middleware('auth')->name('logout');
});

Route::get('/directions/{slug}', [DirectionController::class, 'show'])->name('directions.show');

Route::get('/vacancies', [VacancyController::class, 'index'])->name('vacancies.index');
Route::get('/vacancies/{vacancy:slug}', [VacancyController::class, 'show'])->name('vacancies.show');

// ── Auth-required portal features ──
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/favorites/{type}/{id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::post('/tours/{tour}/react', [TourController::class, 'react'])->name('tours.react');
    Route::post('/tours/{tour}/reviews', [TourReviewController::class, 'store'])->name('tours.reviews.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'portal.admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/applications/export', [AdminApplicationController::class, 'export'])->name('applications.export');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    Route::resource('cities', AdminCityController::class)->except(['show']);
    Route::patch('/cities/{city}/toggle-active', [AdminCityController::class, 'toggleActive'])->name('cities.toggleActive');
    Route::resource('tours', AdminTourController::class)->except(['show']);
    Route::patch('/tours/{tour}/toggle-active', [AdminTourController::class, 'toggleActive'])->name('tours.toggleActive');

    Route::resource('blog', AdminBlogController::class)->except(['show'])->parameters(['blog' => 'post']);
    Route::patch('/blog/{post}/toggle-publish', [AdminBlogController::class, 'togglePublish'])->name('blog.togglePublish');

    Route::resource('blog-subscribers', AdminBlogSubscriberController::class)->only(['index', 'store', 'destroy']);
    Route::patch('/blog-subscribers/{blog_subscriber}/toggle-active', [AdminBlogSubscriberController::class, 'toggleActive'])->name('blog-subscribers.toggleActive');

    Route::resource('recipes', AdminRecipeController::class)->except(['show']);
    Route::resource('education-products', AdminEducationProductController::class)->except(['show']);
    Route::patch('/education-products/course/{course}/toggle', [AdminEducationProductController::class, 'toggleCourseActive'])->name('education-products.toggleCourse');

    Route::resource('vacancies', AdminVacancyController::class)->except(['show']);
    Route::patch('/vacancies/{vacancy}/toggle-publish', [AdminVacancyController::class, 'togglePublish'])->name('vacancies.togglePublish');

    Route::resource('directions', AdminDirectionController::class)->except(['show']);
    Route::patch('/directions/{direction}/toggle-active', [AdminDirectionController::class, 'toggleActive'])->name('directions.toggleActive');

    Route::get('/atoms-vkusa', [AdminAtomsVkusaController::class, 'edit'])->name('atoms-vkusa.edit');
    Route::put('/atoms-vkusa', [AdminAtomsVkusaController::class, 'update'])->name('atoms-vkusa.update');

    Route::resource('timeline', AdminTimelineController::class)->except(['show']);
    Route::patch('/timeline/{timeline}/toggle-active', [AdminTimelineController::class, 'toggleActive'])->name('timeline.toggleActive');

    Route::post('/upload/image', [AdminUploadController::class, 'image'])->name('upload.image');
    Route::post('/upload/file', [AdminUploadController::class, 'file'])->name('upload.file');
    Route::get('/media', [AdminUploadController::class, 'mediaIndex'])->name('media.index');

    Route::get('/tour-reviews', [AdminTourReviewController::class, 'index'])->name('tour-reviews.index');
    Route::patch('/tour-reviews/{tourReview}/approve', [AdminTourReviewController::class, 'approve'])->name('tour-reviews.approve');
    Route::patch('/tour-reviews/{tourReview}/reject', [AdminTourReviewController::class, 'reject'])->name('tour-reviews.reject');
    Route::delete('/tour-reviews/{tourReview}', [AdminTourReviewController::class, 'destroy'])->name('tour-reviews.destroy');

    Route::get('/main-page', [AdminMainPageController::class, 'index'])->name('main-page.index');
    Route::put('/main-page', [AdminMainPageController::class, 'update'])->name('main-page.update');

    Route::get('/opportunity-tours-page', [AdminOpportunityToursPageController::class, 'index'])->name('opportunity-tours-page.index');
    Route::put('/opportunity-tours-page', [AdminOpportunityToursPageController::class, 'update'])->name('opportunity-tours-page.update');

    Route::get('/vshgr-page', [AdminVshgrPageController::class, 'index'])->name('vshgr-page.index');
    Route::put('/vshgr-page', [AdminVshgrPageController::class, 'update'])->name('vshgr-page.update');

    Route::get('/tour-cabinet', [AdminTourCabinetHubController::class, 'index'])->name('tour-cabinet.index');

    Route::get('/tour-cabinet/forms', [AdminTourCabinetFormsController::class, 'index'])->name('tour-cabinet.forms.index');
    Route::put('/tour-cabinet/forms/contest-form-slugs', [AdminTourCabinetFormsController::class, 'updateContestFormSlugs'])->name('tour-cabinet.forms.contest-form-slugs.update');

    Route::get('/tour-cabinet/direction-cities', [AdminTourCabinetDirectionCitiesController::class, 'index'])->name('tour-cabinet.direction-cities.index');
    Route::post('/tour-cabinet/direction-cities', [AdminTourCabinetDirectionCitiesController::class, 'store'])->name('tour-cabinet.direction-cities.store');
    Route::patch('/tour-cabinet/direction-cities/{directionCity}', [AdminTourCabinetDirectionCitiesController::class, 'update'])->name('tour-cabinet.direction-cities.update');
    Route::delete('/tour-cabinet/direction-cities/{directionCity}', [AdminTourCabinetDirectionCitiesController::class, 'destroy'])->name('tour-cabinet.direction-cities.destroy');

    Route::get('/tour-cabinet/stage2-questions', [AdminTourCabinetStage2QuestionsController::class, 'index'])->name('tour-cabinet.stage2-questions.index');
    Route::post('/tour-cabinet/stage2-questions', [AdminTourCabinetStage2QuestionsController::class, 'store'])->name('tour-cabinet.stage2-questions.store');
    Route::patch('/tour-cabinet/stage2-questions/{question}', [AdminTourCabinetStage2QuestionsController::class, 'update'])->name('tour-cabinet.stage2-questions.update');
    Route::delete('/tour-cabinet/stage2-questions/{question}', [AdminTourCabinetStage2QuestionsController::class, 'destroy'])->name('tour-cabinet.stage2-questions.destroy');

    Route::get('/research-page', [AdminResearchPageController::class, 'index'])->name('research-page.index');
    Route::put('/research-page', [AdminResearchPageController::class, 'update'])->name('research-page.update');

    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/mail', [AdminSettingsController::class, 'mail'])->name('settings.mail');
    Route::put('/settings/mail', [AdminSettingsController::class, 'updateMail'])->name('settings.mail.update');
    Route::post('/settings/mail/test', [AdminSettingsController::class, 'testMail'])->name('settings.mail.test');
    Route::post('/settings/mail/test-direct', [AdminSettingsController::class, 'testMailDirect'])->name('settings.mail.test-direct');

    Route::get('/settings/page-visibility', [AdminPageVisibilityController::class, 'index'])->name('settings.page-visibility');
    Route::put('/settings/page-visibility', [AdminPageVisibilityController::class, 'update'])->name('settings.page-visibility.update');
});

// Social OAuth (global)
Route::get('/auth/social/{provider}/login', [SocialAuthController::class, 'redirectToGlobalLogin'])
    ->name('social.login');
Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');

require __DIR__.'/auth.php';
