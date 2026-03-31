# Задачи: Направления (directions)

## Task 1: Модель Direction, миграция и seed

- **Цель**: Создать модель `Direction` с JSON-кастами, миграцию и seed с данными трёх направлений из прототипов
- **Scope**: `app/Models/Direction.php`, `database/migrations/XXXX_create_directions_table.php`, `database/seeders/PortalSeeder.php`
- **DoD**: Модель создана, миграция проходит, seed заполняет 3 направления (Старт в Атомград, Атомы вкуса, Лучшие люди Росатома) с реальными данными из прототипов
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate --force && docker exec ${APP_NAME}_fpm php artisan db:seed --class=PortalSeeder`

## Task 2: Публичный контроллер и маршрут

- **Цель**: Маршрут `GET /directions/{slug}` с контроллером, подгружающим Direction + связанные туры
- **Scope**: `app/Http/Controllers/DirectionController.php`, `routes/web.php`
- **DoD**: Маршрут зарегистрирован, контроллер возвращает Inertia-рендер с данными направления и туров
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=directions`

## Task 3: Админский CRUD — контроллер и маршруты

- **Цель**: CRUD-контроллер `Admin\DirectionController` + resource-маршруты + toggle-active
- **Scope**: `app/Http/Controllers/Admin/DirectionController.php`, `routes/web.php`
- **DoD**: Все CRUD-операции работают, валидация JSON-полей, toggle активности
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=admin/directions`

## Task 4: Админ-страница: список направлений

- **Цель**: Таблица направлений с действиями: редактировать, toggle активности, удалить
- **Scope**: `resources/js/Pages/Admin/Directions/Index.vue`, `resources/js/Layouts/AdminLayout.vue`
- **DoD**: Список отображается с пагинацией, работают toggle и удаление, ссылка в навигации админки

## Task 5: Админ-страница: форма создания/редактирования

- **Цель**: Форма со всеми полями направления, включая динамические JSON-массивы
- **Scope**: `resources/js/Pages/Admin/Directions/Form.vue`
- **DoD**: Создание и редактирование работает, JSON-поля управляются через dynamic fields (добавить/удалить)

## Task 6: Публичная страница направления — hero + направления + аудитории

- **Цель**: Vue-страница `Directions/Show.vue` — первые 3 секции: hero, поднаправления, целевые аудитории
- **Scope**: `resources/js/Pages/Directions/Show.vue`
- **DoD**: Секции отображаются с данными из БД, responsive-дизайн

## Task 7: Публичная страница — участие + конкурс + слайдшоу туров

- **Цель**: Оставшиеся секции: пути участия (бесплатно/платно), конкурсные детали, слайдшоу туров
- **Scope**: `resources/js/Pages/Directions/Show.vue`
- **DoD**: Два блока участия с кнопками (ЛК / оставить заявку), конкурсные вопросы + задание, слайдшоу туров

## Task 8: Интеграция — ссылки в OpportunityTours

- **Цель**: Обновить ссылки в секции «Проекты программы» (OpportunityTours) — вести на `/directions/{slug}`
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Карточки проектов ведут на страницы направлений из БД

## Task 9: Финализация — линтер, spec, прогресс

- **Цель**: Проверить линтер, обновить spec до финального состояния
- **Scope**: Все затронутые файлы, spec/features/directions/*
- **DoD**: Линтер чист, spec отражает финальное состояние, progress.md заполнен
- **Verify**: Визуальная проверка в браузере + линтер

---

## REV-001: Визуальное отличие «Для кого» от «Направления» + убрать звёздочку

- **Цель**: В секции «Для кого» заменить нумерацию на иконки, изменить стиль карточек для визуального отличия от «Направления». Убрать `target_audience_note` (звёздочку).
- **Scope**: `resources/js/Pages/Directions/Show.vue`
- **DoD**: Карточки «Для кого» визуально отличаются от «Направления» (иконки вместо цифр, другая цветовая схема), звёздочка не отображается.
- **Verify**: Визуальная проверка в браузере.

## REV-002: Конкурсные детали — часть блока «Победить в конкурсе»

- **Цель**: Переместить секцию «Конкурсные детали» (Развёрнутые ответы + Проверочное задание) из отдельной секции внутрь/непосредственно после блока «Победить в конкурсе». Расположить вертикально.
- **Scope**: `resources/js/Pages/Directions/Show.vue`
- **DoD**: Конкурсные детали визуально связаны с блоком «Победить в конкурсе», Проверочное задание под Развёрнутыми ответами.
- **Verify**: Визуальная проверка в браузере.

## REV-003: Исправить кнопки

- **Цель**: Кнопка «Оставить заявку» вызывает ошибку (использует Inertia Link с POST-маршрутом + конфликтующий @click.prevent). Заменить на обычную кнопку с прокруткой к турам.
- **Scope**: `resources/js/Pages/Directions/Show.vue`
- **DoD**: Обе кнопки работают без ошибок: «Личный кабинет» → login, «Оставить заявку» → прокрутка к турам.
- **Verify**: Клик по обеим кнопкам в браузере без ошибок в консоли.
