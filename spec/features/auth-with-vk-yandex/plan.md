# План: Авторизация через ВКонтакте и Яндекс

## Milestones

1. **Инфраструктура** — установка `laravel/socialite` + community-провайдеры, конфигурация `config/services.php`, env-переменные
2. **Data layer** — миграция `social_accounts`, модель `SocialAccount`, связь `User::socialAccounts()`
3. **Привязка backend** — `SocialAuthController` с link/callback/unlink, маршруты в `routes/lms.php`
4. **Привязка frontend** — секция «Привязанные аккаунты» в `Profile/Edit.vue`, передача данных из `ProfileController`
5. **SSO-логин backend** — login redirect/callback в `SocialAuthController`, поиск по `social_accounts`, ошибка если не привязан
6. **SSO-логин frontend** — кнопки ВК/Яндекс на `Login.vue`
7. **Верификация** — ручное E2E-тестирование потока привязка → логин → отвязка

## UI Components

- `RButton` — кнопки «Привязать ВК», «Войти через ВК» и т.д.
- `RCard` — секция привязанных аккаунтов в профиле
- `RBadge` — статус привязки (привязан / не привязан)
- `RAlert` — ошибка «Аккаунт не привязан» на странице логина
- Иконки ВК и Яндекс — inline SVG

## Verification

Верификация по паттерну «Command Execution Pattern» из spec-continuation:
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=social`
- Ручная проверка потока через браузер
