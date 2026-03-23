# Прогресс: auth-with-vk-yandex

## Completed tasks

### Task 1: Установка пакетов Socialite ✓
- Files: `composer.json`, `composer.lock`, `app/Providers/AppServiceProvider.php`, `config/services.php`

### Task 2: Конфигурация env-переменных ✓
- Files: `.env.example`, `docker/.env.local`, `config/services.php`

### Task 3: Миграция и модель SocialAccount ✓
- Files: `database/migrations/2026_03_23_100000_create_social_accounts_table.php`, `app/Models/SocialAccount.php`, `app/Models/User.php`

### Task 4: SocialAuthController — привязка (link + callback) ✓
- Files: `app/Http/Controllers/Lms/SocialAuthController.php`, `routes/lms.php`, `routes/web.php`

### Task 5: SocialAuthController — отвязка (unlink) ✓
- Files: `app/Http/Controllers/Lms/SocialAuthController.php`, `routes/lms.php`

### Task 6: SocialAuthController — SSO-логин (login + callback) ✓
- Files: `app/Http/Controllers/Lms/SocialAuthController.php`, `routes/lms.php`

### Task 7: Frontend — секция привязки в Profile/Edit.vue ✓
- Files: `resources/js/Pages/Lms/Profile/Edit.vue`, `app/Http/Controllers/Lms/ProfileController.php`

### Task 8: Frontend — кнопки SSO на Login.vue ✓
- Files: `resources/js/Pages/Lms/Auth/Login.vue`

### Task 9: Обновление spec и data-model ✓
- Files: `spec/03-data-model.md`, `spec/05-flows.md`, `spec/01-architecture.md`, `spec/features/auth-with-vk-yandex/spec.md`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
