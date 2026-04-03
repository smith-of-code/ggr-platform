# manager-subscribe — План реализации

## Этапы

1. **Backend: контроллер** — создать `Admin\BlogSubscriberController` с методами `index`, `store`, `destroy`, `toggleActive`
2. **Backend: маршруты** — зарегистрировать resource-маршруты и `toggleActive` в admin-группе `routes/web.php`
3. **Frontend: страница списка** — `Admin/Subscribers/Index.vue` с таблицей, поиском, пагинацией, toggle, удалением
4. **Frontend: добавление подписчика** — встроенная форма или модалка для ввода email прямо на странице списка
5. **Навигация** — добавить ссылку на подписчиков в сайдбар админки

## UI-компоненты

- `AdminLayout` — основной layout
- `RCard` — карточка-обёртка таблицы
- `RButton` — кнопки действий (удаление)
- `Link` (Inertia) — навигация
- `router` (Inertia) — PATCH/DELETE запросы
- Нативный `confirm()` для подтверждения удаления (как в Cities/Index.vue)

## Верификация

Все команды выполняются по паттерну из spec-continuation:
```
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
