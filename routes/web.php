<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\EducationProductController as AdminEducationProductController;
use App\Http\Controllers\Admin\OpportunityToursPageController as AdminOpportunityToursPageController;
use App\Http\Controllers\Admin\ResearchPageController as AdminResearchPageController;
use App\Http\Controllers\Admin\PageVisibilityController as AdminPageVisibilityController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\TimelineEventController as AdminTimelineController;
use App\Http\Controllers\Admin\DirectionController as AdminDirectionController;
use App\Http\Controllers\Admin\AtomsVkusaController as AdminAtomsVkusaController;
use App\Http\Controllers\Admin\BlogSubscriberController as AdminBlogSubscriberController;
use App\Http\Controllers\Admin\VacancyController as AdminVacancyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogSubscriptionController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OpportunityToursController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ResearchPageController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourReviewController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\Admin\TourReviewController as AdminTourReviewController;
use Illuminate\Support\Facades\Route;

// ── Public portal (no auth required) ──
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/{slug}', [CityController::class, 'show'])->name('cities.show');
Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');
Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');

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

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
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

    Route::get('/opportunity-tours-page', [AdminOpportunityToursPageController::class, 'index'])->name('opportunity-tours-page.index');
    Route::put('/opportunity-tours-page', [AdminOpportunityToursPageController::class, 'update'])->name('opportunity-tours-page.update');

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
Route::get('/auth/social/{provider}/login', [\App\Http\Controllers\Lms\SocialAuthController::class, 'redirectToGlobalLogin'])
    ->name('social.login');
Route::get('/auth/social/{provider}/callback', [\App\Http\Controllers\Lms\SocialAuthController::class, 'callback'])
    ->name('social.callback');

require __DIR__.'/auth.php';
