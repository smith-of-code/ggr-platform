# План: Направления (directions)

## Milestones

1. **Модель и миграция** — создать модель `Direction`, миграцию, JSON-касты
2. **Публичный контроллер и маршрут** — `DirectionController@show`, route `directions.show`
3. **Админский CRUD** — `Admin\DirectionController`, маршруты resource + toggle-active
4. **Админ-страница: список** — `Admin/Directions/Index.vue` с таблицей, toggle, delete
5. **Админ-страница: форма** — `Admin/Directions/Form.vue` с полями для всех секций
6. **Публичная страница** — `Directions/Show.vue` со всеми секциями по прототипам
7. **Интеграция** — обновление OpportunityTours ссылок на страницы направлений, AdminLayout навигации
8. **Финализация** — линтер, spec, прогресс

## UI Components

- Публичная страница: `MainLayout.vue` как layout
- Карточки туров: `RCard`, `RBadge` из `@rosatom-ggr/ui-kit`
- Слайдшоу: нативная реализация на Vue (scroll-snap + кнопки prev/next) — аналог `OpportunityTours/Index.vue`
- Аккордеон/таб целевых аудиторий: нумерованные карточки в сетке
- Админ: `AdminLayout.vue`, стандартные формы и таблица по паттернам `Admin/Tours`
- JSON-поля (sub_directions, target_audiences, steps): динамические массивы в форме (добавить/удалить запись)

## Верификация

Все команды выполняются по паттерну из spec-continuation:
```bash
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
