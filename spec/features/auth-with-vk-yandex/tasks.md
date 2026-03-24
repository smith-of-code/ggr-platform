# Задачи: auth-with-vk-yandex

## Task 1: Установка пакетов Socialite + кастомный VK ID провайдер

- **Goal**: Установить `laravel/socialite`, `socialiteproviders/yandex`. Создать кастомный `App\Socialite\VkIdProvider` для VK ID API (`id.vk.ru`).
- **Scope**: `composer.json`, `config/services.php`, `app/Socialite/VkIdProvider.php`, `app/Providers/AppServiceProvider.php`
- **DoD**: Пакеты установлены. VK ID зарегистрирован через `Socialite::extend('vkontakte', VkIdProvider)`, Yandex — через `Event::listen(SocialiteWasCalled)` → `extendSocialite()`. Конфиг `services.php` содержит секции `vkontakte` + `yandex`. Кастомный VK ID провайдер использует endpoint'ы `id.vk.ru`, обязательный PKCE (S256), обработку `payload` формата callback, `device_id` при обмене кода.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm composer show laravel/socialite`

## Task 2: Конфигурация env-переменных

- **Goal**: Добавить OAuth credentials в `.env.example`, `docker/.env.local`, `config/services.php`
- **Scope**: `.env.example`, `docker/.env.local`, `config/services.php`
- **DoD**: Переменные `VKONTAKTE_CLIENT_ID`, `VKONTAKTE_CLIENT_SECRET`, `VKONTAKTE_REDIRECT_URI`, `YANDEX_CLIENT_ID`, `YANDEX_CLIENT_SECRET`, `YANDEX_REDIRECT_URI` описаны
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan config:show services.vkontakte`

## Task 3: Миграция и модель SocialAccount

- **Goal**: Создать таблицу `social_accounts` и модель `App\Models\SocialAccount`
- **Scope**: `database/migrations/xxxx_create_social_accounts_table.php`, `app/Models/SocialAccount.php`, `app/Models/User.php`
- **DoD**: Миграция с полями (user_id, provider, provider_id, token, refresh_token, expires_at), уникальные индексы. Модель с fillable, casts (encrypted для token/refresh_token). `User::socialAccounts()` hasMany связь.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## Task 4: SocialAuthController — привязка (link + callback)

- **Goal**: Реализовать redirect к провайдеру и callback для привязки аккаунта
- **Scope**: `app/Http/Controllers/Lms/SocialAuthController.php`, `routes/lms.php`
- **DoD**: Методы `redirectToLink()` → Socialite redirect, `handleLinkCallback()` → сохранение в social_accounts с проверкой уникальности. Маршруты зарегистрированы под middleware `auth`. Валидация provider (только `vkontakte`, `yandex`).
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=social`

## Task 5: SocialAuthController — отвязка (unlink)

- **Goal**: Реализовать удаление привязки провайдера
- **Scope**: `app/Http/Controllers/Lms/SocialAuthController.php`, `routes/lms.php`
- **DoD**: Метод `unlink()` — удаляет запись из social_accounts для текущего пользователя + провайдера. DELETE-маршрут под middleware `auth`.
- **Verify**: Маршрут виден в route:list

## Task 6: SocialAuthController — SSO-логин (login + callback)

- **Goal**: Реализовать вход через ВК/Яндекс для неавторизованных пользователей
- **Scope**: `app/Http/Controllers/Lms/SocialAuthController.php`, `routes/lms.php`
- **DoD**: Методы `redirectToLogin()` → Socialite redirect, `handleLoginCallback()` → поиск в social_accounts по provider + provider_id → `Auth::login(user)` → redirect lms.dashboard. Если не найден — redirect lms.login с flash-ошибкой «Аккаунт не привязан. Войдите по email и привяжите аккаунт в профиле.». Начисление гамификационных баллов `login_daily`.
- **Verify**: Маршруты видны в route:list, guest-маршруты без middleware auth

## Task 7: Frontend — секция привязки в Profile/Edit.vue

- **Goal**: Добавить секцию «Привязанные аккаунты» в профиль участника
- **Scope**: `resources/js/Pages/Lms/Profile/Edit.vue`, `app/Http/Controllers/Lms/ProfileController.php`
- **DoD**: ProfileController передаёт `socialAccounts` (список привязанных провайдеров). В Edit.vue — RCard с иконками ВК/Яндекс, статус (привязан/не привязан), кнопки «Привязать»/«Отвязать». Привязка — ссылка (GET redirect). Отвязка — Inertia DELETE.
- **Verify**: Визуальная проверка страницы профиля

## Task 8: Frontend — кнопки SSO на Login.vue

- **Goal**: Добавить кнопки «Войти через ВК» / «Войти через Яндекс» на страницу логина
- **Scope**: `resources/js/Pages/Lms/Auth/Login.vue`
- **DoD**: Разделитель «или» между формой и кнопками SSO. Кнопки — ссылки на `lms.social.login`. Отображение flash-ошибки (если аккаунт не привязан). Стилизация кнопок с иконками провайдеров.
- **Verify**: Визуальная проверка страницы логина

## Task 9: Обновление spec и data-model

- **Goal**: Обновить спецификацию после реализации
- **Scope**: `spec/03-data-model.md`, `spec/05-flows.md`, `spec/01-architecture.md`
- **DoD**: Таблица `social_accounts` добавлена в data-model. Маршруты SSO добавлены в flows. Раздел «Аутентификация» в architecture обновлён.
- **Verify**: Документация актуальна
