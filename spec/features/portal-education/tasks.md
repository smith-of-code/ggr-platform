# Задачи — portal-education

## REV-001 — Нормальная валидация email и телефона

- **Goal**: Добавить серверную и клиентскую валидацию email/телефона в формах заявки ВШГР.
- **Scope**: `app/Http/Controllers/ApplicationController.php`, `resources/js/Pages/Education/Index.vue`, `resources/js/Pages/Education/Show.vue`
- **DoD**: Email валидируется через RFC+DNS, телефон нормализуется и проверяется на формат +7XXXXXXXXXX, на фронте маска ввода телефона.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan test --filter=Application` (если тесты есть), ручная проверка формы на /vshgr.

## REV-002 — Блок социальных сетей

- **Goal**: Добавить блок «Мы в социальных сетях» на страницу ВШГР, визуал как на /opportunity-tours.
- **Scope**: `resources/js/Pages/Education/Index.vue`
- **DoD**: Секция с карточками-ссылками на соцсети (VK, Telegram) с SVG-иконками между блоком «Положение» и формой заявки.
- **Verify**: Визуальная проверка на /vshgr — блок отображается, ссылки открываются в новой вкладке.
