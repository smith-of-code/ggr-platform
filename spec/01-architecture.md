# Архитектура

## Стек

| Компонент | Версия / Технология |
|-----------|---------------------|
| PHP | ^8.2 (Fpm.Dockerfile) |
| Laravel | ^12.0 |
| Vue | ^3.5.28 |
| Inertia.js | @inertiajs/vue3 |
| Tailwind CSS | ^4.0.0 |
| Vite | ^7.0.7 |
| PostgreSQL | 16 |
| Redis | (queue, cache, session) |
| Laravel Horizon | очереди (supervisor-default, supervisor-emails) |
| Nginx | reverse proxy |
| Docker | docker-compose (local + prod) |
| Node.js | 22.x (установлен в fpm-контейнере) |

## Ключевые пакеты

### PHP (composer.json require)

- `inertiajs/inertia-laravel` ^2.0
- `laravel/horizon` *
- `laravel/sanctum` ^4.0
- `laravel/socialite` ^5.25
- `socialiteproviders/yandex` ^4.1
- Кастомный провайдер `App\Socialite\VkIdProvider` для VK ID API (`id.vk.ru`, PKCE S256)
- `tightenco/ziggy` ^2.0

### JavaScript (package.json dependencies)

- `@heroicons/vue` ^2.2.0
- `@tiptap/starter-kit` ^2.11.0 + расширения (image, link, underline) — WYSIWYG-редактор
- `tailwindcss` ^4.0.0

## Структура директорий

```
app/
├── Http/Controllers/
│   ├── Admin/          # Админка основного сайта (13 контроллеров)
│   ├── Auth/           # Breeze auth (8 контроллеров)
│   ├── Lms/            # LMS для участников (14 контроллеров)
│   │   └── Admin/      # Админка LMS (15 контроллеров)
│   ├── ApplicationController.php
│   ├── BlogController.php
│   ├── BlogSubscriptionController.php
│   ├── CityController.php
│   ├── DirectionController.php
│   ├── EducationController.php
│   ├── FavoriteController.php
│   ├── HomeController.php
│   ├── OpportunityToursController.php
│   ├── ProfileController.php
│   ├── RecipeController.php
│   ├── ResearchPageController.php
│   ├── TourController.php
│   ├── TourReviewController.php
│   └── VacancyController.php
├── Models/
│   ├── Lms/            # 41 LMS-модель
│   └── ...             # 21 корневая модель (User, Tour, City, Direction, Post, Recipe, Vacancy, etc.)
├── Services/
│   ├── SettingsService.php
│   └── GamificationService.php
├── Jobs/
│   └── SendMailJob.php
└── Mail/
    └── InvitationMail.php

resources/js/
├── Pages/
│   ├── Admin/          # Админка основного сайта (13 подпапок)
│   ├── Auth/           # Аутентификация (Breeze)
│   ├── Blog/           # Блог (публичный)
│   ├── Cities/         # Публичные страницы городов
│   ├── Directions/     # Направления (публичный)
│   ├── Education/      # Образование / ВШГР (публичный)
│   ├── Favorites/      # Избранное
│   ├── Forms/          # Публичные формы
│   ├── OpportunityTours/ # Возможности для туров
│   ├── Profile/        # Профиль пользователя
│   ├── Recipes/        # Рецепты (публичный)
│   ├── Research/       # Исследования (публичный)
│   ├── Tours/          # Публичные страницы туров
│   ├── Vacancies/      # Вакансии (публичный)
│   ├── Lms/            # LMS
│   │   ├── Admin/      # Админка LMS
│   │   ├── Auth/       # Аутентификация LMS
│   │   ├── Leader/     # Интерфейс лидера
│   │   ├── Courses/
│   │   ├── Tests/
│   │   ├── Assignments/
│   │   ├── Trajectories/
│   │   ├── Grants/     # Гранты
│   │   ├── Videos/
│   │   ├── KnowledgeBase/
│   │   ├── Materials/
│   │   ├── Gamification/
│   │   ├── Reports/    # Отчёты
│   │   └── Profile/
│   ├── Dashboard.vue
│   ├── Home.vue
│   └── Welcome.vue
├── Components/         # 22 общих Vue-компонента
├── composables/        # useScrollReveal
└── Layouts/
    ├── AdminLayout.vue
    ├── AuthenticatedLayout.vue
    ├── GuestLayout.vue
    ├── LmsAdminLayout.vue
    ├── LmsLayout.vue
    └── MainLayout.vue

database/migrations/    # 56 миграций
routes/
├── web.php             # Публичный сайт + админка + social auth
├── lms.php             # LMS Auth + LMS Participant + LMS Admin + Forms public
├── auth.php            # Breeze auth
└── console.php

docker/
├── docker-compose.yml          # Базовый
├── docker-compose.override.yml # Local (порты, postgres, xdebug)
├── docker-compose.prod.yml     # Production
├── Fpm.Dockerfile
├── run.sh
├── .env.local
└── .env.prod
```

## Аутентификация

- **Основной сайт**: Laravel Breeze (session-based), middleware `auth` (только для профиля и избранного)
- **Публичные страницы** (города, туры, блог, рецепты, вакансии, образование, направления, исследования): без аутентификации
- **LMS**: Вход через глобальную страницу `/auth/social/{provider}/login` или `/login` (Breeze). Отдельного LMS-логина нет, `AuthController::redirectToGlobalLogin` перенаправляет на основную страницу входа.
  - Инвайт-токены (LmsInvitation, invite_token в LmsProfile)
  - Активация профиля
  - OAuth SSO через ВКонтакте и Яндекс (`Lms\SocialAuthController`, `laravel/socialite` + кастомный `App\Socialite\VkIdProvider` для VK ID + `socialiteproviders/yandex`)
    - Регистрация через SSO невозможна — только привязка существующего аккаунта из профиля
    - Глобальный callback: `/auth/social/{provider}/callback`
    - Глобальный login redirect: `/auth/social/{provider}/login`
    - Данные привязок: таблица `social_accounts`
- Guard: стандартный `web` (session)

## Docker

- Контейнер-паттерн: `${APP_NAME}_<service>` (APP_NAME = `vshgr-platform`)
- Основной контейнер: `vshgr-platform_fpm` (PHP + Node.js + Supervisor/Horizon)
- ENV: `docker/.env.local` / `docker/.env.prod`
- Запуск: `cd docker && ./run.sh local`

## Очереди

- Driver: Redis
- Horizon supervisors: `supervisor-default` (queue: default), `supervisor-emails` (queue: emails)
- Horizon config: `config/horizon.php`
