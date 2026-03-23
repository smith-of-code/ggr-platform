# Архитектура

## Стек

| Компонент | Версия / Технология |
|-----------|---------------------|
| PHP | 8.3 (Fpm.Dockerfile) |
| Laravel | 12.x |
| Vue | 3.5.28 |
| Inertia.js | @inertiajs/vue3 2.3.15 |
| Tailwind CSS | 4.x |
| Vite | 7.x |
| PostgreSQL | 16 |
| Redis | (queue, cache, session) |
| Laravel Horizon | очереди (supervisor-default, supervisor-emails) |
| Nginx | reverse proxy |
| Docker | docker-compose (local + prod) |
| Node.js | 22.x (установлен в fpm-контейнере) |
| UI Kit | @rosatom-ggr/ui-kit ^1.0.16 |

## Ключевые пакеты

### PHP (composer.json require)

- `inertiajs/inertia-laravel` ^2.0
- `laravel/horizon` *
- `laravel/sanctum` ^4.0
- `laravel/socialite` ^5.25
- `socialiteproviders/vkontakte` ^5.1
- `socialiteproviders/yandex` ^4.1
- `tightenco/ziggy` ^2.0

### JavaScript (package.json dependencies)

- `@heroicons/vue` ^2.2.0
- `@tiptap/starter-kit` ^2.11.0 + расширения (image, link, underline) — WYSIWYG-редактор
- `@rosatom-ggr/ui-kit` ^1.0.16

## Структура директорий

```
app/
├── Http/Controllers/
│   ├── Admin/          # Админка основного сайта
│   ├── Auth/           # Breeze auth
│   ├── Lms/            # LMS для участников
│   │   └── Admin/      # Админка LMS
│   ├── ApplicationController.php
│   ├── CityController.php
│   ├── HomeController.php
│   ├── ProfileController.php
│   └── TourController.php
├── Models/
│   ├── Lms/            # Все LMS-модели
│   └── ...             # Корневые модели (User, Tour, City, etc.)
├── Services/
│   ├── SettingsService.php
│   └── GamificationService.php
├── Jobs/
│   └── SendMailJob.php
└── Mail/
    └── InvitationMail.php

resources/js/
├── Pages/
│   ├── Admin/          # Админка основного сайта
│   ├── Auth/           # Аутентификация (Breeze)
│   ├── Cities/         # Публичные страницы городов
│   ├── Tours/          # Публичные страницы туров
│   ├── Profile/        # Профиль пользователя
│   ├── Lms/            # LMS
│   │   ├── Admin/      # Админка LMS
│   │   ├── Auth/       # Аутентификация LMS
│   │   ├── Leader/     # Интерфейс лидера
│   │   ├── Courses/
│   │   ├── Tests/
│   │   ├── Assignments/
│   │   ├── Trajectories/
│   │   ├── Videos/
│   │   ├── KnowledgeBase/
│   │   ├── Materials/
│   │   ├── Gamification/
│   │   └── Profile/
│   ├── Dashboard.vue
│   ├── Home.vue
│   └── Welcome.vue
├── Components/         # Общие Vue-компоненты (16 шт.)
├── composables/        # useScrollReveal
└── Layouts/

database/migrations/    # 15 миграций
routes/
├── web.php             # Основной сайт + админка
├── lms.php             # LMS + LMS Admin
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

- **Основной сайт**: Laravel Breeze (session-based), middleware `auth`
- **LMS**: Отдельная auth-система через `Lms\AuthController` с поддержкой:
  - Email-регистрации
  - Инвайт-токенов (LmsInvitation)
  - Активации профиля (invite_token в LmsProfile)
  - OAuth SSO через ВКонтакте и Яндекс (`Lms\SocialAuthController`, `laravel/socialite` + `socialiteproviders/vkontakte` + `socialiteproviders/yandex`)
    - Регистрация через SSO невозможна — только привязка существующего аккаунта из профиля
    - Глобальный callback: `/auth/social/{provider}/callback`
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
