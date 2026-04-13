# Потоки (маршруты)

## 1. Public Site (`routes/web.php`)

### Публичные маршруты (без auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/` | HomeController@index | home |
| GET | `/mainpage` | HomeController@mainpage | home.mainpage |
| GET | `/cities` | CityController@index | cities.index |
| GET | `/cities/{slug}` | CityController@show | cities.show |
| GET | `/tours` | TourController@index | tours.index |
| GET | `/tours/{slug}` | TourController@show | tours.show |
| POST | `/applications` | ApplicationController@store | applications.store |
| POST | `/contact` | HomeController@contactSubmit | contact.submit |
| GET | `/blog` | BlogController@index | blog.index |
| POST | `/blog/subscribe` | BlogSubscriptionController@subscribe | blog.subscribe |
| GET | `/blog/unsubscribe/{token}` | BlogSubscriptionController@unsubscribe | blog.unsubscribe |
| GET | `/blog/{slug}` | BlogController@show | blog.show |
| GET | `/vshgr` | EducationController@index | education.index |
| GET | `/vshgr/{slug}` | EducationController@show | education.show |
| GET | `/research` | ResearchPageController@index | research.index |
| GET | `/recipes` | RecipeController@index | recipes.index |
| GET | `/recipes/{slug}` | RecipeController@show | recipes.show |
| GET | `/opportunity-tours` | OpportunityToursController@index | opportunity-tours.index |
| GET | `/directions/{slug}` | DirectionController@show | directions.show |
| GET | `/vacancies` | VacancyController@index | vacancies.index |
| GET | `/vacancies/{vacancy:slug}` | VacancyController@show | vacancies.show |

### Auth-маршруты портала (middleware: auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| POST | `/favorites/{type}/{id}` | FavoriteController@toggle | favorites.toggle |
| GET | `/favorites` | FavoriteController@index | favorites.index |
| POST | `/tours/{tour}/react` | TourController@react | tours.react |
| POST | `/tours/{tour}/reviews` | TourReviewController@store | tours.reviews.store |

## 2. Profile (`routes/web.php`, middleware: auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/profile` | ProfileController@edit | profile.edit |
| PATCH | `/profile` | ProfileController@update | profile.update |
| DELETE | `/profile` | ProfileController@destroy | profile.destroy |

## 3. Admin (`routes/web.php`, prefix: `/admin`, middleware: auth)

### Dashboard & Applications

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin` | Admin\DashboardController@index | admin.dashboard |
| GET | `/admin/applications/export` | Admin\ApplicationController@export | admin.applications.export |
| GET | `/admin/applications` | Admin\ApplicationController@index | admin.applications.index |
| GET | `/admin/applications/{application}` | Admin\ApplicationController@show | admin.applications.show |
| PATCH | `/admin/applications/{application}/status` | Admin\ApplicationController@updateStatus | admin.applications.updateStatus |

### Cities (resource без show)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/cities` | Admin\CityController@index | admin.cities.index |
| GET | `/admin/cities/create` | Admin\CityController@create | admin.cities.create |
| POST | `/admin/cities` | Admin\CityController@store | admin.cities.store |
| GET | `/admin/cities/{city}/edit` | Admin\CityController@edit | admin.cities.edit |
| PUT | `/admin/cities/{city}` | Admin\CityController@update | admin.cities.update |
| DELETE | `/admin/cities/{city}` | Admin\CityController@destroy | admin.cities.destroy |
| PATCH | `/admin/cities/{city}/toggle-active` | Admin\CityController@toggleActive | admin.cities.toggleActive |

### Tours (resource без show)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/tours` | Admin\TourController@index | admin.tours.index |
| GET | `/admin/tours/create` | Admin\TourController@create | admin.tours.create |
| POST | `/admin/tours` | Admin\TourController@store | admin.tours.store |
| GET | `/admin/tours/{tour}/edit` | Admin\TourController@edit | admin.tours.edit |
| PUT | `/admin/tours/{tour}` | Admin\TourController@update | admin.tours.update |
| DELETE | `/admin/tours/{tour}` | Admin\TourController@destroy | admin.tours.destroy |
| PATCH | `/admin/tours/{tour}/toggle-active` | Admin\TourController@toggleActive | admin.tours.toggleActive |

### Blog (resource без show, parameter: post)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/blog` | Admin\BlogController@index | admin.blog.index |
| GET | `/admin/blog/create` | Admin\BlogController@create | admin.blog.create |
| POST | `/admin/blog` | Admin\BlogController@store | admin.blog.store |
| GET | `/admin/blog/{post}/edit` | Admin\BlogController@edit | admin.blog.edit |
| PUT | `/admin/blog/{post}` | Admin\BlogController@update | admin.blog.update |
| DELETE | `/admin/blog/{post}` | Admin\BlogController@destroy | admin.blog.destroy |
| PATCH | `/admin/blog/{post}/toggle-publish` | Admin\BlogController@togglePublish | admin.blog.togglePublish |

### Recipes (resource без show)

`admin.recipes.*` → Admin\RecipeController

### Education Products (resource без show)

`admin.education-products.*` → Admin\EducationProductController
`PATCH /admin/education-products/course/{course}/toggle` → toggleCourseActive (`admin.education-products.toggleCourse`)

### Vacancies (resource без show)

`admin.vacancies.*` → Admin\VacancyController
`PATCH /admin/vacancies/{vacancy}/toggle-publish` → togglePublish (`admin.vacancies.togglePublish`)

### Directions (resource без show)

`admin.directions.*` → Admin\DirectionController
`PATCH /admin/directions/{direction}/toggle-active` → toggleActive (`admin.directions.toggleActive`)

### Атомы вкуса

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/atoms-vkusa` | Admin\AtomsVkusaController@edit | admin.atoms-vkusa.edit |
| PUT | `/admin/atoms-vkusa` | Admin\AtomsVkusaController@update | admin.atoms-vkusa.update |

### Timeline (resource без show)

`admin.timeline.*` → Admin\TimelineEventController
`PATCH /admin/timeline/{timeline}/toggle-active` → toggleActive (`admin.timeline.toggleActive`)

### Upload & Media

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| POST | `/admin/upload/image` | Admin\UploadController@image | admin.upload.image |
| POST | `/admin/upload/file` | Admin\UploadController@file | admin.upload.file |
| GET | `/admin/media` | Admin\UploadController@mediaIndex | admin.media.index |

### Tour Reviews

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/tour-reviews` | Admin\TourReviewController@index | admin.tour-reviews.index |
| PATCH | `/admin/tour-reviews/{tourReview}/approve` | Admin\TourReviewController@approve | admin.tour-reviews.approve |
| PATCH | `/admin/tour-reviews/{tourReview}/reject` | Admin\TourReviewController@reject | admin.tour-reviews.reject |
| DELETE | `/admin/tour-reviews/{tourReview}` | Admin\TourReviewController@destroy | admin.tour-reviews.destroy |

### Opportunity Tours Page (singleton)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/opportunity-tours-page` | Admin\OpportunityToursPageController@index | admin.opportunity-tours-page.index |
| PUT | `/admin/opportunity-tours-page` | Admin\OpportunityToursPageController@update | admin.opportunity-tours-page.update |

### Research Page (singleton)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/research-page` | Admin\ResearchPageController@index | admin.research-page.index |
| PUT | `/admin/research-page` | Admin\ResearchPageController@update | admin.research-page.update |

### Settings

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/admin/settings` | Admin\SettingsController@index | admin.settings.index |
| GET | `/admin/settings/mail` | Admin\SettingsController@mail | admin.settings.mail |
| PUT | `/admin/settings/mail` | Admin\SettingsController@updateMail | admin.settings.mail.update |
| POST | `/admin/settings/mail/test` | Admin\SettingsController@testMail | admin.settings.mail.test |

## 4. Auth (`routes/auth.php`)

### Guest-маршруты (middleware: guest)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/register` | Auth\RegisteredUserController@create | register |
| POST | `/register` | Auth\RegisteredUserController@store | — |
| GET | `/login` | Auth\AuthenticatedSessionController@create | login |
| POST | `/login` | Auth\AuthenticatedSessionController@store | — |
| GET | `/forgot-password` | Auth\PasswordResetLinkController@create | password.request |
| POST | `/forgot-password` | Auth\PasswordResetLinkController@store | password.email |
| GET | `/reset-password/{token}` | Auth\NewPasswordController@create | password.reset |
| POST | `/reset-password` | Auth\NewPasswordController@store | password.store |

### Auth-маршруты (middleware: auth)

| Метод | URI | Controller@action | Name | Extra middleware |
|-------|-----|-------------------|------|-----------------|
| GET | `/verify-email` | EmailVerificationPromptController | verification.notice | — |
| GET | `/verify-email/{id}/{hash}` | VerifyEmailController | verification.verify | signed, throttle:6,1 |
| POST | `/email/verification-notification` | EmailVerificationNotificationController@store | verification.send | throttle:6,1 |
| GET | `/confirm-password` | ConfirmablePasswordController@show | password.confirm | — |
| POST | `/confirm-password` | ConfirmablePasswordController@store | — | — |
| PUT | `/password` | PasswordController@update | password.update | — |
| POST | `/logout` | AuthenticatedSessionController@destroy | logout | — |

## 5. Social Auth (`routes/web.php` + `routes/lms.php`)

### Глобальные маршруты (web.php, без auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/auth/social/{provider}/login` | Lms\SocialAuthController@redirectToGlobalLogin | social.login |
| GET | `/auth/social/{provider}/callback` | Lms\SocialAuthController@callback | social.callback |

### Per-event маршруты (lms.php)

| Метод | URI | Controller@action | Name | Middleware |
|-------|-----|-------------------|------|------------|
| GET | `/lms/{event:slug}/social/{provider}/login` | Lms\SocialAuthController@redirectToLogin | lms.social.login | — |
| GET | `/lms/{event:slug}/social/{provider}/link` | Lms\SocialAuthController@redirectToLink | lms.social.link | auth |
| DELETE | `/lms/{event:slug}/social/{provider}/unlink` | Lms\SocialAuthController@unlink | lms.social.unlink | auth |

Поддерживаемые провайдеры: `vkontakte`, `yandex`. Event slug и тип операции (login/link) сохраняются в сессии перед redirect к провайдеру.

**VK ID** (`vkontakte`): кастомный Socialite-провайдер `App\Socialite\VkIdProvider` использует VK ID API (`id.vk.ru`), обязательный PKCE (S256), формат callback с JSON `payload`, `device_id` при обмене кода. `client_secret` не используется.

**Yandex** (`yandex`): стандартный `socialiteproviders/yandex` через legacy OAuth flow.

## 6. LMS Auth (`routes/lms.php`, prefix: `/lms/{event:slug}`)

| Метод | URI | Controller@action | Name | Middleware |
|-------|-----|-------------------|------|------------|
| GET | `/lms/{event:slug}/login` | Lms\AuthController@redirectToGlobalLogin | lms.login | — |
| POST | `/lms/{event:slug}/logout` | Lms\AuthController@logout | lms.logout | — |
| GET | `/lms/{event:slug}/invite/{token}` | Lms\AuthController@showInvite | lms.invite | — |
| POST | `/lms/{event:slug}/invite/{token}` | Lms\AuthController@registerByInvite | lms.invite.register | — |
| GET | `/lms/{event:slug}/activate/{token}` | Lms\AuthController@showActivate | lms.activate | — |
| POST | `/lms/{event:slug}/activate/{token}` | Lms\AuthController@activate | lms.activate.submit | — |

## 7. LMS Участник (`routes/lms.php`, prefix: `/lms/{event:slug}`, middleware: auth)

### Dashboard & Profile

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/lms/{event:slug}` | Lms\DashboardController@index | lms.dashboard |
| GET | `/lms/{event:slug}/profile` | Lms\ProfileController@edit | lms.profile.edit |
| PATCH | `/lms/{event:slug}/profile` | Lms\ProfileController@update | lms.profile.update |
| POST | `/lms/{event:slug}/profile/documents` | Lms\ProfileController@uploadDocument | lms.profile.documents.upload |
| DELETE | `/lms/{event:slug}/profile/documents/{document}` | Lms\ProfileController@deleteDocument | lms.profile.documents.delete |
| GET | `/lms/{event:slug}/profile/templates/{type}` | Lms\ProfileController@downloadTemplate | lms.profile.templates.download |

### Courses & Stages

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../courses` | Lms\CourseController@index | lms.courses.index |
| GET | `.../courses/{course}` | Lms\CourseController@show | lms.courses.show |
| POST | `.../courses/{course}/enroll` | Lms\CourseController@enroll | lms.courses.enroll |
| DELETE | `.../courses/{course}/enroll` | Lms\CourseController@unenroll | lms.courses.unenroll |
| GET | `.../courses/{course}/stages/{stage}` | Lms\StageController@show | lms.stages.show |
| POST | `.../courses/{course}/stages/{stage}/complete` | Lms\StageController@complete | lms.stages.complete |
| POST | `.../courses/{course}/stages/{stage}/scorm` | Lms\StageController@scormData | lms.stages.scorm |
| POST | `.../courses/{course}/stages/{stage}/heartbeat` | Lms\StageController@heartbeat | lms.stages.heartbeat |

### Tests

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../tests` | Lms\TestController@index | lms.tests.index |
| GET | `.../tests/{test}` | Lms\TestController@show | lms.tests.show |
| POST | `.../tests/{test}/start` | Lms\TestController@start | lms.tests.start |
| POST | `.../tests/{test}/attempts/{attempt}/submit` | Lms\TestController@submit | lms.tests.submit |
| GET | `.../tests/{test}/attempts/{attempt}` | Lms\TestController@take | lms.tests.take |
| GET | `.../tests/{test}/attempts/{attempt}/result` | Lms\TestController@result | lms.tests.result |

### Assignments

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../assignments` | Lms\AssignmentController@index | lms.assignments.index |
| GET | `.../assignments/{assignment}` | Lms\AssignmentController@show | lms.assignments.show |
| POST | `.../assignments/{assignment}/submit` | Lms\AssignmentController@submit | lms.assignments.submit |
| POST | `.../assignments/{assignment}/draft` | Lms\AssignmentController@draft | lms.assignments.draft |
| POST | `.../assignments/{assignment}/comment` | Lms\AssignmentController@comment | lms.assignments.comment |
| PATCH | `.../assignments/{assignment}/submissions/{submission}` | Lms\AssignmentController@update | lms.assignments.update |

### Trajectories

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../trajectories` | Lms\TrajectoryController@index | lms.trajectories.index |
| GET | `.../trajectories/{trajectory}` | Lms\TrajectoryController@show | lms.trajectories.show |
| POST | `.../trajectories/{trajectory}/enroll` | Lms\TrajectoryController@enroll | lms.trajectories.enroll |

### Grants

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../grants` | Lms\GrantController@index | lms.grants.index |
| GET | `.../grants/{grant}` | Lms\GrantController@show | lms.grants.show |
| POST | `.../grants/{grant}/enroll` | Lms\GrantController@enroll | lms.grants.enroll |
| DELETE | `.../grants/{grant}/unenroll` | Lms\GrantController@unenroll | lms.grants.unenroll |

### Videos

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../videos` | Lms\VideoController@index | lms.videos.index |
| GET | `.../videos/{video}` | Lms\VideoController@show | lms.videos.show |

### Knowledge Base

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../knowledge` | Lms\KnowledgeBaseController@index | lms.kb.index |
| GET | `.../knowledge/{section}` | Lms\KnowledgeBaseController@show | lms.kb.show |

### Materials

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../materials` | Lms\MaterialController@index | lms.materials.index |
| GET | `.../materials/{section}` | Lms\MaterialController@show | lms.materials.show |

### Gamification

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../leaderboard` | Lms\GamificationController@leaderboard | lms.gamification.leaderboard |
| GET | `.../my-points` | Lms\GamificationController@myPoints | lms.gamification.my-points |

### Reports

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../reports` | Lms\ReportController@index | lms.reports.index |
| POST | `.../reports/send` | Lms\ReportController@sendEmail | lms.reports.send |

## 8. LMS Leader (`routes/lms.php`, prefix: `/lms/{event:slug}/leader`, middleware: auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../leader` | Lms\LeaderController@index | lms.leader.dashboard |
| GET | `.../leader/groups` | Lms\LeaderController@groups | lms.leader.groups |
| GET | `.../leader/groups/{group}` | Lms\LeaderController@groupDetail | lms.leader.groups.show |
| GET | `.../leader/users/{user}` | Lms\LeaderController@userProgress | lms.leader.users.progress |
| POST | `.../leader/report` | Lms\LeaderController@sendReport | lms.leader.report |

## 9. LMS Admin (`routes/lms.php`, prefix: `/lms-admin`, middleware: auth)

### Events

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| resource | `/lms-admin/events` | Lms\Admin\EventController | lms.admin.events.* |

### Вложенные ресурсы (prefix: `/lms-admin/{event}`)

**Courses**: resource → Lms\Admin\CourseController (`lms.admin.courses.*`)
**Search**: `GET .../search-modules` → searchModules, `GET .../search-stages` → searchStages, `GET .../search-blocks` → searchBlocks
**SCORM Upload**: `POST .../scorm-upload` → Lms\Admin\CourseController@uploadScorm (`lms.admin.scorm.upload`)
**Tests**: resource → Lms\Admin\TestController (`lms.admin.tests.*`)
**Assignments**: resource → Lms\Admin\AssignmentController (`lms.admin.assignments.*`)
**Assignment Review**: `POST .../assignments/{assignment}/submissions/{submission}/review` → review (`lms.admin.assignments.review`)
**Assignment Comment**: `POST .../assignments/{assignment}/submissions/{submission}/comment` → comment (`lms.admin.assignments.comment`)
**Trajectories**: resource → Lms\Admin\TrajectoryController (`lms.admin.trajectories.*`)
**Grants**: resource → Lms\Admin\GrantController (`lms.admin.grants.*`)
**Videos**: resource → Lms\Admin\VideoController (`lms.admin.videos.*`)
**Knowledge Base**: resource → Lms\Admin\KnowledgeBaseController (`lms.admin.kb.*`)
**Materials**: resource → Lms\Admin\MaterialController (`lms.admin.materials.*`)
**Groups**: resource → Lms\Admin\GroupController (`lms.admin.groups.*`)
**Roles**: resource (без show) → Lms\Admin\RoleController (`lms.admin.roles.*`)
**Gamification**: resource → Lms\Admin\GamificationController (`lms.admin.gamification.*`)
**Manual Points**: `POST .../gamification/manual-points` → manualPoints (`lms.admin.gamification.manual-points`)

### Users & Invitations

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| resource (index,create,store,show,update,destroy) | `.../users` | Lms\Admin\UserController | lms.admin.users.* |
| POST | `.../users-import` | Lms\Admin\UserController@import | lms.admin.users.import |
| POST | `.../users-send-invitations` | Lms\Admin\UserController@sendInvitations | lms.admin.users.send-invitations |
| POST | `.../users-bulk-enroll` | Lms\Admin\UserController@bulkEnroll | lms.admin.users.bulk-enroll |
| GET | `.../users-template` | Lms\Admin\UserController@downloadTemplate | lms.admin.users.template |
| POST | `.../invitations` | Lms\Admin\InvitationController@store | lms.admin.invitations.store |
| POST | `.../invitations/{invitation}/toggle` | Lms\Admin\InvitationController@toggle | lms.admin.invitations.toggle |
| DELETE | `.../invitations/{invitation}` | Lms\Admin\InvitationController@destroy | lms.admin.invitations.destroy |

### Enrollments

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../enrollments` | Lms\Admin\EnrollmentController@index | lms.admin.enrollments.index |
| GET | `.../courses/{course}/enrollments` | Lms\Admin\EnrollmentController@courseEnrollments | lms.admin.enrollments.course |
| POST | `.../enrollments/{enrollment}/approve` | Lms\Admin\EnrollmentController@approve | lms.admin.enrollments.approve |
| POST | `.../enrollments/{enrollment}/reject` | Lms\Admin\EnrollmentController@reject | lms.admin.enrollments.reject |
| POST | `.../enrollments/{enrollment}/reassign` | Lms\Admin\EnrollmentController@reassign | lms.admin.enrollments.reassign |
| DELETE | `.../enrollments/{enrollment}` | Lms\Admin\EnrollmentController@destroy | lms.admin.enrollments.destroy |

### Upload

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| POST | `.../upload/image` | Lms\Admin\UploadController@image | lms.admin.upload.image |
| POST | `.../upload/file` | Lms\Admin\UploadController@file | lms.admin.upload.file |

### Reports

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../reports` | Lms\Admin\ReportController@index | lms.admin.reports.index |
| GET | `.../reports/download` | Lms\Admin\ReportController@download | lms.admin.reports.download |
| POST | `.../reports/send` | Lms\Admin\ReportController@sendEmail | lms.admin.reports.send |

### Forms

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../forms/check-slug` | Lms\Admin\FormController@checkSlug | lms.admin.forms.check-slug |
| resource | `.../forms` | Lms\Admin\FormController | lms.admin.forms.* |
| GET | `.../forms/{form}/stats` | Lms\Admin\FormController@stats | lms.admin.forms.stats |
| POST | `.../forms/{form}/create-users` | Lms\Admin\FormController@createUsersFromSubmissions | lms.admin.forms.create-users |

## 10. Forms Public (`routes/lms.php`, без auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/forms/{slug}` | Lms\FormPublicController@show | forms.public.show |
| POST | `/forms/{slug}/submit` | Lms\FormPublicController@submit | forms.public.submit |
| GET | `/api/forms/{slug}` | Lms\FormPublicController@apiShow | forms.api.show |
| POST | `/api/forms/{slug}/submit` | Lms\FormPublicController@apiSubmit | forms.api.submit |
| OPTIONS | `/api/forms/{slug}/submit` | Lms\FormPublicController@apiCorsOptions | — |

API-маршруты форм не требуют CSRF (withoutMiddleware ValidateCsrfToken) и предназначены для embed-виджетов.
