# Потоки (маршруты)

## 1. Public Site (`routes/web.php`)

| Метод | URI | Controller@action | Name | Middleware |
|-------|-----|-------------------|------|------------|
| GET | `/` | HomeController@index | home | auth |
| GET | `/cities` | CityController@index | cities.index | auth |
| GET | `/cities/{slug}` | CityController@show | cities.show | auth |
| GET | `/tours` | TourController@index | tours.index | auth |
| GET | `/tours/{slug}` | TourController@show | tours.show | auth |
| POST | `/applications` | ApplicationController@store | applications.store | auth |

## 2. Profile (`routes/web.php`)

| Метод | URI | Controller@action | Name | Middleware |
|-------|-----|-------------------|------|------------|
| GET | `/profile` | ProfileController@edit | profile.edit | auth |
| PATCH | `/profile` | ProfileController@update | profile.update | auth |
| DELETE | `/profile` | ProfileController@destroy | profile.destroy | auth |

## 3. Admin (`routes/web.php`, prefix: `/admin`)

| Метод | URI | Controller@action | Name | Middleware |
|-------|-----|-------------------|------|------------|
| GET | `/admin` | Admin\DashboardController@index | admin.dashboard | auth |
| GET | `/admin/applications/export` | Admin\ApplicationController@export | admin.applications.export | auth |
| GET | `/admin/applications` | Admin\ApplicationController@index | admin.applications.index | auth |
| PATCH | `/admin/applications/{application}/status` | Admin\ApplicationController@updateStatus | admin.applications.updateStatus | auth |
| GET | `/admin/cities` | Admin\CityController@index | admin.cities.index | auth |
| GET | `/admin/cities/create` | Admin\CityController@create | admin.cities.create | auth |
| POST | `/admin/cities` | Admin\CityController@store | admin.cities.store | auth |
| GET | `/admin/cities/{city}/edit` | Admin\CityController@edit | admin.cities.edit | auth |
| PUT | `/admin/cities/{city}` | Admin\CityController@update | admin.cities.update | auth |
| DELETE | `/admin/cities/{city}` | Admin\CityController@destroy | admin.cities.destroy | auth |
| PATCH | `/admin/cities/{city}/toggle-active` | Admin\CityController@toggleActive | admin.cities.toggleActive | auth |
| GET | `/admin/tours` | Admin\TourController@index | admin.tours.index | auth |
| GET | `/admin/tours/create` | Admin\TourController@create | admin.tours.create | auth |
| POST | `/admin/tours` | Admin\TourController@store | admin.tours.store | auth |
| GET | `/admin/tours/{tour}/edit` | Admin\TourController@edit | admin.tours.edit | auth |
| PUT | `/admin/tours/{tour}` | Admin\TourController@update | admin.tours.update | auth |
| DELETE | `/admin/tours/{tour}` | Admin\TourController@destroy | admin.tours.destroy | auth |
| PATCH | `/admin/tours/{tour}/toggle-active` | Admin\TourController@toggleActive | admin.tours.toggleActive | auth |
| GET | `/admin/settings` | Admin\SettingsController@index | admin.settings.index | auth |
| GET | `/admin/settings/mail` | Admin\SettingsController@mail | admin.settings.mail | auth |
| PUT | `/admin/settings/mail` | Admin\SettingsController@updateMail | admin.settings.mail.update | auth |
| POST | `/admin/settings/mail/test` | Admin\SettingsController@testMail | admin.settings.mail.test | auth |

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

## 5. LMS Auth (`routes/lms.php`, prefix: `/lms/{event:slug}`)

| Метод | URI | Controller@action | Name | Middleware |
|-------|-----|-------------------|------|------------|
| GET | `/lms/{event:slug}/login` | Lms\AuthController@showLogin | lms.login | — |
| POST | `/lms/{event:slug}/login` | Lms\AuthController@login | — | — |
| GET | `/lms/{event:slug}/register` | Lms\AuthController@showRegister | lms.register | — |
| POST | `/lms/{event:slug}/register` | Lms\AuthController@register | — | — |
| POST | `/lms/{event:slug}/logout` | Lms\AuthController@logout | lms.logout | — |
| GET | `/lms/{event:slug}/invite/{token}` | Lms\AuthController@showInvite | lms.invite | — |
| POST | `/lms/{event:slug}/invite/{token}` | Lms\AuthController@registerByInvite | lms.invite.register | — |
| GET | `/lms/{event:slug}/activate/{token}` | Lms\AuthController@showActivate | lms.activate | — |
| POST | `/lms/{event:slug}/activate/{token}` | Lms\AuthController@activate | lms.activate.submit | — |

## 6. LMS Участник (`routes/lms.php`, prefix: `/lms/{event:slug}`, middleware: auth)

### Dashboard & Profile

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/lms/{event:slug}` | Lms\DashboardController@index | lms.dashboard |
| GET | `/lms/{event:slug}/profile` | Lms\ProfileController@edit | lms.profile.edit |
| PATCH | `/lms/{event:slug}/profile` | Lms\ProfileController@update | lms.profile.update |

### Courses & Stages

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../courses` | Lms\CourseController@index | lms.courses.index |
| GET | `.../courses/{course}` | Lms\CourseController@show | lms.courses.show |
| POST | `.../courses/{course}/enroll` | Lms\CourseController@enroll | lms.courses.enroll |
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
| GET | `.../tests/{test}/attempts/{attempt}` | Lms\TestController@take | lms.tests.take |
| POST | `.../tests/{test}/attempts/{attempt}/submit` | Lms\TestController@submit | lms.tests.submit |
| GET | `.../tests/{test}/attempts/{attempt}/result` | Lms\TestController@result | lms.tests.result |

### Assignments

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../assignments` | Lms\AssignmentController@index | lms.assignments.index |
| GET | `.../assignments/{assignment}` | Lms\AssignmentController@show | lms.assignments.show |
| POST | `.../assignments/{assignment}/submit` | Lms\AssignmentController@submit | lms.assignments.submit |
| PATCH | `.../assignments/{assignment}/submissions/{submission}` | Lms\AssignmentController@update | lms.assignments.update |

### Trajectories

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../trajectories` | Lms\TrajectoryController@index | lms.trajectories.index |
| GET | `.../trajectories/{trajectory}` | Lms\TrajectoryController@show | lms.trajectories.show |
| POST | `.../trajectories/{trajectory}/enroll` | Lms\TrajectoryController@enroll | lms.trajectories.enroll |

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

## 7. LMS Leader (`routes/lms.php`, prefix: `/lms/{event:slug}/leader`, middleware: auth)

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../leader` | Lms\LeaderController@index | lms.leader.dashboard |
| GET | `.../leader/groups` | Lms\LeaderController@groups | lms.leader.groups |
| GET | `.../leader/groups/{group}` | Lms\LeaderController@groupDetail | lms.leader.groups.show |
| GET | `.../leader/users/{user}` | Lms\LeaderController@userProgress | lms.leader.users.progress |
| POST | `.../leader/report` | Lms\LeaderController@sendReport | lms.leader.report |

## 8. LMS Admin (`routes/lms.php`, prefix: `/lms-admin`, middleware: auth)

### Events

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `/lms-admin/events` | Lms\Admin\EventController@index | lms.admin.events.index |
| GET | `/lms-admin/events/create` | Lms\Admin\EventController@create | lms.admin.events.create |
| POST | `/lms-admin/events` | Lms\Admin\EventController@store | lms.admin.events.store |
| GET | `/lms-admin/events/{event}` | Lms\Admin\EventController@show | lms.admin.events.show |
| GET | `/lms-admin/events/{event}/edit` | Lms\Admin\EventController@edit | lms.admin.events.edit |
| PUT | `/lms-admin/events/{event}` | Lms\Admin\EventController@update | lms.admin.events.update |
| DELETE | `/lms-admin/events/{event}` | Lms\Admin\EventController@destroy | lms.admin.events.destroy |

### Вложенные ресурсы (prefix: `/lms-admin/{event}`)

Все нижеперечисленные ресурсы вложены внутри `{event}`:

**Courses**: resource → Lms\Admin\CourseController (name: `lms.admin.courses.*`)
**SCORM Upload**: `POST .../scorm-upload` → Lms\Admin\CourseController@uploadScorm (`lms.admin.scorm.upload`)
**Tests**: resource → Lms\Admin\TestController (`lms.admin.tests.*`)
**Assignments**: resource → Lms\Admin\AssignmentController (`lms.admin.assignments.*`)
**Assignment Review**: `POST .../assignments/{assignment}/submissions/{submission}/review` → Lms\Admin\AssignmentController@review (`lms.admin.assignments.review`)
**Trajectories**: resource → Lms\Admin\TrajectoryController (`lms.admin.trajectories.*`)
**Videos**: resource → Lms\Admin\VideoController (`lms.admin.videos.*`)
**Knowledge Base**: resource → Lms\Admin\KnowledgeBaseController (`lms.admin.kb.*`)
**Materials**: resource → Lms\Admin\MaterialController (`lms.admin.materials.*`)
**Groups**: resource → Lms\Admin\GroupController (`lms.admin.groups.*`)
**Roles**: resource → Lms\Admin\RoleController (`lms.admin.roles.*`)
**Gamification**: resource → Lms\Admin\GamificationController (`lms.admin.gamification.*`)
**Manual Points**: `POST .../gamification/manual-points` → Lms\Admin\GamificationController@manualPoints (`lms.admin.gamification.manual-points`)

### Users & Invitations

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| GET | `.../users` | Lms\Admin\UserController@index | lms.admin.users.index |
| GET | `.../users/create` | Lms\Admin\UserController@create | lms.admin.users.create |
| POST | `.../users` | Lms\Admin\UserController@store | lms.admin.users.store |
| GET | `.../users/{user}` | Lms\Admin\UserController@show | lms.admin.users.show |
| PUT | `.../users/{user}` | Lms\Admin\UserController@update | lms.admin.users.update |
| DELETE | `.../users/{user}` | Lms\Admin\UserController@destroy | lms.admin.users.destroy |
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

### Upload

| Метод | URI | Controller@action | Name |
|-------|-----|-------------------|------|
| POST | `.../upload/image` | Lms\Admin\UploadController@image | lms.admin.upload.image |
