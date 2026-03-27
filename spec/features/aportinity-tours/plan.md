# План: Туры возможностей (aportinity-tours)

## Milestones

1. **Backend-каркас** — контроллер + маршрут + Inertia-рендер страницы
2. **Навигация** — ссылка «Туры возможностей» в header (desktop + mobile) и footer `MainLayout.vue`
3. **Цифры проекта** — секция со счётчиками
4. **Проекты программы** — 3 карточки проектов с описанием
5. **Видео туров** — слайдшоу с ВК Видео embed
6. **Как принять участие + Счётчик эмоций** — 3 шага + статистика
7. **Популярные туры** — слайдшоу карточек
8. **Соцсети + FAQ + Партнёры** — нижние секции страницы
9. **Финализация** — обновление spec, прогон линтера

## UI Components

- Используем `MainLayout.vue` как layout
- Tailwind-классы в стиле существующих страниц (Home.vue, Tours/Index.vue)
- Слайдшоу — нативная реализация на Vue (scroll-snap или кнопки prev/next)
- FAQ — аккордеон на `<details>/<summary>` или Vue ref-toggle
- Видео — iframe embed ВК Видео

## Верификация

Все команды выполняются по паттерну из spec-continuation:
```bash
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
