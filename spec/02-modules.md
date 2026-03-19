# Модули

## 1. Public Site — Публичный сайт

**Назначение**: Лендинг с городами, турами и формой заявок.

| Элемент | Путь |
|---------|------|
| Контроллеры | `HomeController`, `CityController`, `TourController`, `ApplicationController` |
| Route prefix | `/` |
| Страницы | `Pages/Home.vue`, `Pages/Welcome.vue`, `Pages/Cities/*`, `Pages/Tours/*` |

## 2. Auth — Аутентификация (Breeze)

**Назначение**: Стандартная auth-система Laravel Breeze.

| Элемент | Путь |
|---------|------|
| Контроллеры | `Auth/AuthenticatedSessionController`, `Auth/RegisteredUserController`, `Auth/PasswordResetLinkController`, `Auth/NewPasswordController`, `Auth/VerifyEmailController`, `Auth/EmailVerificationPromptController`, `Auth/EmailVerificationNotificationController`, `Auth/ConfirmablePasswordController`, `Auth/PasswordController` |
| Route file | `routes/auth.php` |
| Страницы | `Pages/Auth/*` |

## 3. Profile — Профиль пользователя

**Назначение**: Редактирование профиля и пароля.

| Элемент | Путь |
|---------|------|
| Контроллер | `ProfileController` |
| Route prefix | `/profile` |
| Страницы | `Pages/Profile/Edit.vue`, `Pages/Profile/Partials/*` |

## 4. Admin — Админка основного сайта

**Назначение**: Управление городами, турами, заявками, настройками почты.

| Элемент | Путь |
|---------|------|
| Контроллеры | `Admin/DashboardController`, `Admin/ApplicationController`, `Admin/CityController`, `Admin/TourController`, `Admin/SettingsController` |
| Route prefix | `/admin` |
| Route name | `admin.*` |
| Middleware | `auth` |
| Страницы | `Pages/Admin/*` |

## 5. LMS — Учебная платформа (участники)

**Назначение**: Основной LMS-интерфейс для участников мероприятий: курсы, тесты, задания, траектории, видео, база знаний, материалы, геймификация.

| Элемент | Путь |
|---------|------|
| Контроллеры | `Lms/DashboardController`, `Lms/CourseController`, `Lms/StageController`, `Lms/TestController`, `Lms/AssignmentController`, `Lms/TrajectoryController`, `Lms/VideoController`, `Lms/KnowledgeBaseController`, `Lms/MaterialController`, `Lms/GamificationController`, `Lms/ProfileController` |
| Route prefix | `/lms/{event:slug}` |
| Route name | `lms.*` |
| Middleware | `auth` |
| Route file | `routes/lms.php` |
| Страницы | `Pages/Lms/Dashboard.vue`, `Pages/Lms/Courses/*`, `Pages/Lms/Tests/*`, `Pages/Lms/Assignments/*`, `Pages/Lms/Trajectories/*`, `Pages/Lms/Videos/*`, `Pages/Lms/KnowledgeBase/*`, `Pages/Lms/Materials/*`, `Pages/Lms/Gamification/*`, `Pages/Lms/Profile/*` |

## 6. LMS Auth — Аутентификация LMS

**Назначение**: Отдельная auth-система для LMS с инвайтами, активацией, SSO.

| Элемент | Путь |
|---------|------|
| Контроллер | `Lms/AuthController` |
| Route prefix | `/lms/{event:slug}` |
| Route name | `lms.*` |
| Middleware | нет (guest-маршруты) |
| Страницы | `Pages/Lms/Auth/Login.vue`, `Pages/Lms/Auth/Register.vue`, `Pages/Lms/Auth/Invite.vue`, `Pages/Lms/Auth/Activate.vue` |

## 7. LMS Leader — Интерфейс лидера

**Назначение**: Мониторинг прогресса групп и участников для лидеров/кураторов.

| Элемент | Путь |
|---------|------|
| Контроллер | `Lms/LeaderController` |
| Route prefix | `/lms/{event:slug}/leader` |
| Route name | `lms.leader.*` |
| Middleware | `auth` |
| Страницы | `Pages/Lms/Leader/Dashboard.vue`, `Pages/Lms/Leader/Groups.vue`, `Pages/Lms/Leader/GroupDetail.vue`, `Pages/Lms/Leader/UserProgress.vue` |

## 8. LMS Admin — Админка LMS

**Назначение**: Управление мероприятиями, курсами, тестами, заданиями, траекториями, видео, базой знаний, материалами, группами, пользователями, ролями, геймификацией, записями на курсы, инвайтами.

| Элемент | Путь |
|---------|------|
| Контроллеры | `Lms/Admin/EventController`, `Lms/Admin/CourseController`, `Lms/Admin/TestController`, `Lms/Admin/AssignmentController`, `Lms/Admin/TrajectoryController`, `Lms/Admin/VideoController`, `Lms/Admin/KnowledgeBaseController`, `Lms/Admin/MaterialController`, `Lms/Admin/GroupController`, `Lms/Admin/UserController`, `Lms/Admin/EnrollmentController`, `Lms/Admin/GamificationController`, `Lms/Admin/InvitationController`, `Lms/Admin/RoleController`, `Lms/Admin/UploadController` |
| Route prefix | `/lms-admin` |
| Route name | `lms.admin.*` |
| Middleware | `auth` |
| Страницы | `Pages/Lms/Admin/Events/*`, `Pages/Lms/Admin/Courses/*`, `Pages/Lms/Admin/Tests/*`, `Pages/Lms/Admin/Assignments/*`, `Pages/Lms/Admin/Trajectories/*`, `Pages/Lms/Admin/Videos/*`, `Pages/Lms/Admin/KnowledgeBase/*`, `Pages/Lms/Admin/Materials/*`, `Pages/Lms/Admin/Groups/*`, `Pages/Lms/Admin/Users/*`, `Pages/Lms/Admin/Enrollments/*`, `Pages/Lms/Admin/Gamification/*`, `Pages/Lms/Admin/Roles/*` |
