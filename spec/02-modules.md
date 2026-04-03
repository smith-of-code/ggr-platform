# Модули

## 1. Public Site — Публичный сайт

**Назначение**: Портал с городами, турами, блогом, рецептами, вакансиями, образованием, направлениями, исследованиями, формой заявок и контактной формой.

| Элемент | Путь |
|---------|------|
| Контроллеры | `HomeController`, `CityController`, `TourController`, `ApplicationController`, `BlogController`, `BlogSubscriptionController`, `RecipeController`, `EducationController`, `ResearchPageController`, `OpportunityToursController`, `DirectionController`, `VacancyController`, `TourReviewController`, `FavoriteController` |
| Route prefix | `/` |
| Route file | `routes/web.php` |
| Middleware | нет (публичный), `auth` только для favorites, reactions, reviews |
| Страницы | `Pages/Home.vue`, `Pages/Welcome.vue`, `Pages/Cities/*`, `Pages/Tours/*`, `Pages/Blog/*`, `Pages/Recipes/*`, `Pages/Education/*`, `Pages/Research/*`, `Pages/OpportunityTours/*`, `Pages/Directions/*`, `Pages/Vacancies/*`, `Pages/Favorites/*` |

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
| Middleware | `auth` |
| Страницы | `Pages/Profile/Edit.vue`, `Pages/Profile/Partials/*` |

## 4. Admin — Админка основного сайта

**Назначение**: Управление городами, турами, заявками, блогом, рецептами, образовательными продуктами, вакансиями, направлениями, таймлайном, «Атомами вкуса», отзывами, страницей возможностей, страницей исследований, настройками, медиа-файлами.

| Элемент | Путь |
|---------|------|
| Контроллеры | `Admin/DashboardController`, `Admin/ApplicationController`, `Admin/CityController`, `Admin/TourController`, `Admin/BlogController`, `Admin/RecipeController`, `Admin/EducationProductController`, `Admin/VacancyController`, `Admin/DirectionController`, `Admin/AtomsVkusaController`, `Admin/TimelineEventController`, `Admin/TourReviewController`, `Admin/OpportunityToursPageController`, `Admin/ResearchPageController`, `Admin/UploadController`, `Admin/SettingsController` |
| Route prefix | `/admin` |
| Route name | `admin.*` |
| Middleware | `auth` |
| Страницы | `Pages/Admin/*` |

## 5. LMS — Учебная платформа (участники)

**Назначение**: Основной LMS-интерфейс для участников: курсы, тесты, задания, траектории, гранты, видео, база знаний, материалы, геймификация, отчёты, формы (публичные).

| Элемент | Путь |
|---------|------|
| Контроллеры | `Lms/DashboardController`, `Lms/CourseController`, `Lms/StageController`, `Lms/TestController`, `Lms/AssignmentController`, `Lms/TrajectoryController`, `Lms/GrantController`, `Lms/VideoController`, `Lms/KnowledgeBaseController`, `Lms/MaterialController`, `Lms/GamificationController`, `Lms/ProfileController`, `Lms/ReportController`, `Lms/FormPublicController` |
| Route prefix | `/lms/{event:slug}` |
| Route name | `lms.*` |
| Middleware | `auth` |
| Route file | `routes/lms.php` |
| Страницы | `Pages/Lms/Dashboard.vue`, `Pages/Lms/Courses/*`, `Pages/Lms/Tests/*`, `Pages/Lms/Assignments/*`, `Pages/Lms/Trajectories/*`, `Pages/Lms/Grants/*`, `Pages/Lms/Videos/*`, `Pages/Lms/KnowledgeBase/*`, `Pages/Lms/Materials/*`, `Pages/Lms/Gamification/*`, `Pages/Lms/Profile/*`, `Pages/Lms/Reports/*`, `Pages/Forms/Public.vue` |

## 6. LMS Auth — Аутентификация LMS

**Назначение**: Auth-система LMS: redirect на глобальный login, инвайты, активация, SSO.

| Элемент | Путь |
|---------|------|
| Контроллер | `Lms/AuthController`, `Lms/SocialAuthController` |
| Route prefix | `/lms/{event:slug}` |
| Route name | `lms.*` |
| Middleware | нет (guest-маршруты) |
| Страницы | `Pages/Lms/Auth/Register.vue`, `Pages/Lms/Auth/Invite.vue`, `Pages/Lms/Auth/Activate.vue` |

Примечание: LMS login (`lms.login`) перенаправляет на глобальную страницу входа через `AuthController::redirectToGlobalLogin`. Отдельных страниц Login/Register в LMS нет — используется Breeze `/login`.

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

**Назначение**: Управление мероприятиями, курсами, тестами, заданиями, траекториями, грантами, видео, базой знаний, материалами, группами, пользователями, ролями, геймификацией, записями, инвайтами, отчётами, формами.

| Элемент | Путь |
|---------|------|
| Контроллеры | `Lms/Admin/EventController`, `Lms/Admin/CourseController`, `Lms/Admin/TestController`, `Lms/Admin/AssignmentController`, `Lms/Admin/TrajectoryController`, `Lms/Admin/GrantController`, `Lms/Admin/VideoController`, `Lms/Admin/KnowledgeBaseController`, `Lms/Admin/MaterialController`, `Lms/Admin/GroupController`, `Lms/Admin/UserController`, `Lms/Admin/EnrollmentController`, `Lms/Admin/GamificationController`, `Lms/Admin/InvitationController`, `Lms/Admin/RoleController`, `Lms/Admin/UploadController`, `Lms/Admin/ReportController`, `Lms/Admin/FormController` |
| Route prefix | `/lms-admin` |
| Route name | `lms.admin.*` |
| Middleware | `auth` |
| Страницы | `Pages/Lms/Admin/Events/*`, `Pages/Lms/Admin/Courses/*`, `Pages/Lms/Admin/Tests/*`, `Pages/Lms/Admin/Assignments/*`, `Pages/Lms/Admin/Trajectories/*`, `Pages/Lms/Admin/Grants/*`, `Pages/Lms/Admin/Videos/*`, `Pages/Lms/Admin/KnowledgeBase/*`, `Pages/Lms/Admin/Materials/*`, `Pages/Lms/Admin/Groups/*`, `Pages/Lms/Admin/Users/*`, `Pages/Lms/Admin/Enrollments/*`, `Pages/Lms/Admin/Gamification/*`, `Pages/Lms/Admin/Roles/*`, `Pages/Lms/Admin/Reports/*`, `Pages/Lms/Admin/Forms/*` |
