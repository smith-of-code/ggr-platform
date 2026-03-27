# Задачи: Туры возможностей (aportinity-tours)

## Task 1: Backend-каркас — контроллер и маршрут

- **Цель**: Создать контроллер и маршрут, рендерящий Inertia-страницу
- **Scope**: `app/Http/Controllers/OpportunityToursController.php`, `routes/web.php`
- **DoD**: `GET /opportunity-tours` отдаёт Inertia-рендер `OpportunityTours/Index`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=opportunity`

## Task 2: Навигация — ссылка в шапке, мобильном меню и footer

- **Цель**: Добавить «Туры возможностей» в навигацию `MainLayout.vue`
- **Scope**: `resources/js/Layouts/MainLayout.vue`
- **DoD**: Ссылка отображается в desktop nav, mobile menu, footer navigation
- **Verify**: Визуальная проверка в браузере

## Task 3: Vue-страница — каркас + Цифры проекта

- **Цель**: Создать `OpportunityTours/Index.vue` с layout и секцией счётчиков
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Страница рендерится с 4 счётчиками (статические данные)
- **Verify**: Визуальная проверка в браузере

## Task 4: Секция «Проекты программы»

- **Цель**: Блок с 3 проектами — название (ссылка-заглушка) + описание
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: 3 карточки проектов отображаются с кликабельными названиями
- **Verify**: Визуальная проверка

## Task 5: Секция «Видео туров» — слайдшоу

- **Цель**: Слайдшоу с embed ВК Видео
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Слайдшоу с 3+ placeholder-видео, кнопки навигации
- **Verify**: Визуальная проверка

## Task 6: Секции «Как принять участие» + «Счётчик эмоций»

- **Цель**: 3 шага участия + статистика эмоций
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Блок с 3 шагами + кнопка «Личный кабинет», блок со статистикой эмоций
- **Verify**: Визуальная проверка

## Task 7: Секция «Популярные туры» — слайдшоу карточек

- **Цель**: Горизонтальное слайдшоу карточек туров
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Слайдшоу с 4+ карточками (placeholder), навигация prev/next
- **Verify**: Визуальная проверка

## Task 8: Секции «Соцсети» + «FAQ» + «Партнёры»

- **Цель**: Финальные секции страницы
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Ссылки на соцсети, аккордеон FAQ (5+ вопросов), блок партнёров
- **Verify**: Визуальная проверка

## Task 9: Финализация — линтер, spec, прогресс

- **Цель**: Проверить линтер, обновить spec до финального состояния
- **Scope**: Все затронутые файлы, spec/features/aportinity-tours/*
- **DoD**: Линтер чист, spec отражает финальное состояние, progress.md заполнен
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npx vue-tsc --noEmit` (если настроен)
