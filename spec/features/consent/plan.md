# Consent — план реализации

## Milestones

1. **Backend-основа** — миграция `consents`, модель `Consent`, сервис `ConsentService`
2. **Интеграция: регистрация портала** — логирование в `RegisteredUserController::store`
3. **Интеграция: LMS auth** — логирование в `Lms\AuthController` (register, invite, activate)
4. **Интеграция: заявки** — добавление чекбокса на фронте + логирование в `ApplicationController::store`
5. **Интеграция: контактная форма** — добавление чекбокса на фронте + логирование в `HomeController::contactSubmit`
6. **Проверка и тесты** — smoke-тесты, проверка записей в таблице

## UI Components

- `Checkbox.vue` — существующий компонент, используется для чекбокса согласия
- `InputError.vue` — для отображения ошибки валидации чекбокса
- Текст согласия со ссылкой на политику — inline HTML рядом с чекбоксом

## Verification

Проверка выполняется по паттерну "Command Execution Pattern" из spec-continuation:
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker` (проверка модели)
- Ручная проверка через браузер (submit формы → запись в таблице)
