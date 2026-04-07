# Consent — задачи

## Task 1: Миграция таблицы consents

- **Цель:** Создать таблицу `consents` для хранения логов согласий
- **Scope:** `database/migrations/2026_04_07_200000_create_consents_table.php`
- **DoD:** Миграция применяется без ошибок, таблица создаётся с корректными колонками и индексами
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## Task 2: Модель Consent

- **Цель:** Создать Eloquent-модель для таблицы `consents`
- **Scope:** `app/Models/Consent.php`
- **DoD:** Модель с fillable, casts, константами event_type; записи создаются корректно
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="App\Models\Consent::create([...])"`

## Task 3: ConsentService

- **Цель:** Создать сервис для записи согласий с автоматическим сбором IP, UA, URL
- **Scope:** `app/Services/ConsentService.php`
- **DoD:** Метод `log(Request $request, string $eventType, array $identifiers, array $additional = [])` создаёт запись в `consents`
- **Verify:** Unit-вызов через tinker

## Task 4: Интеграция — регистрация портала

- **Цель:** Логировать согласие при регистрации на портале
- **Scope:** `app/Http/Controllers/Auth/RegisteredUserController.php`
- **DoD:** После успешной регистрации создаётся запись в `consents` с `event_type = 'registration'`
- **Verify:** Регистрация через браузер → запись в таблице `consents`

## Task 5: Интеграция — LMS auth (register, invite, activate)

- **Цель:** Логировать согласие при регистрации/активации в LMS
- **Scope:** `app/Http/Controllers/Lms/AuthController.php`
- **DoD:** При register/invite/activate создаётся запись с `event_type = 'lms_registration' | 'lms_invite' | 'lms_activate'`
- **Verify:** Регистрация/активация через LMS → запись в `consents`

## Task 6: Интеграция — форма заявок (ApplicationController)

- **Цель:** Добавить чекбокс согласия на фронте и логирование на бэкенде
- **Scope:** `app/Http/Controllers/ApplicationController.php`, `resources/js/Pages/Education/Partials/ApplicationForm.vue`
- **DoD:** Чекбокс обязателен; при submit создаётся запись с `event_type = 'application'`
- **Verify:** Submit заявки → запись в `consents`

## Task 7: Интеграция — контактная форма (HomeController)

- **Цель:** Добавить чекбокс согласия на фронте и логирование на бэкенде
- **Scope:** `app/Http/Controllers/HomeController.php`, `resources/js/Pages/Home.vue`
- **DoD:** Чекбокс обязателен; при submit создаётся запись с `event_type = 'contact_form'`
- **Verify:** Submit контактной формы → запись в `consents`

## Task 8: Smoke-тест и финальная проверка

- **Цель:** Убедиться, что все интеграции работают корректно
- **Scope:** Все изменённые файлы
- **DoD:** Каждый event_type порождает корректную запись; валидация чекбокса работает; нет регрессий
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="App\Models\Consent::count()"`
