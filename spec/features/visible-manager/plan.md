# Plan — Visible Manager

## Milestones

1. **Бэкенд: конфигурация и сервис** — определить массив управляемых страниц, расширить `SettingsService` методами для группы `page_visibility`
2. **Middleware** — создать `CheckPageVisibility` middleware, который при совпадении пути со скрытой страницей возвращает Inertia-заглушку
3. **Заглушка (Vue-страница)** — создать `Pages/PageUnderConstruction.vue` с красивым дизайном «Страница в разработке»
4. **Админ-контроллер** — добавить `PageVisibilityController` (index, update) в `Admin/`
5. **Админ-UI** — создать `Pages/Admin/Settings/PageVisibility.vue` с toggle-переключателями для каждой страницы
6. **Навигация** — передать список скрытых страниц в `MainLayout.vue`, скрыть ссылки на скрытые страницы
7. **Интеграция** — подключить middleware к маршрутам, добавить ссылку в хаб настроек

## UI Components

- `RButton` — кнопки сохранения
- Tailwind toggle / checkbox — переключатели видимости (кастомный toggle на Tailwind, т.к. отдельного компонента нет)
- `ToastNotifications` — уведомления при сохранении
- Inertia `Link` — навигация

## Verification

Все команды выполняются по паттерну из spec-continuation:
```
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
