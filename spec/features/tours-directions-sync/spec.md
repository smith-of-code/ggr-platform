# Синхронизация Направлений и Проектов (tours-directions-sync)

## Статус: В работе

## Цель

Сделать `Direction` единственным источником правды для «проектов». Заменить захардкоженную константу `Tour::PROJECTS` и все `project_key` строковые колонки на FK `direction_id` → `directions.id`. При создании/активации направления — оно автоматически появляется в каталоге и ЛК туров; при скрытии/удалении — исчезает.

## В scope

- Добавить статические методы на `Direction` для получения карты проектов (`activeProjectMap`, `projectList`)
- Добавить relationship `Tour::direction()` (belongsTo)
- Миграция: `tours.project` (string) → `tours.direction_id` (FK) с переносом данных
- Миграция: `project_key` → `direction_id` (FK) в таблицах `tour_cabinet_direction_cities`, `tour_cabinet_contest_progress`, `tour_cabinet_contest_stage2_questions`, `tour_cabinet_contest_stage3_configs`, `tour_cabinet_contest_direction_settings`
- Миграция: удалить `directions.project_key`
- Удалить константу `Tour::PROJECTS`
- Обновить все ~15 PHP-файлов, ссылающихся на `Tour::PROJECTS`, на динамический источник из `Direction`
- Обновить валидацию туров и направлений — `exists:directions,id` вместо `in:...`
- Обновить фронтенд: `Tours/Index.vue` (каталог), `Admin/Tours/Form.vue`, `Admin/Directions/Form.vue`, `Admin/Directions/Index.vue`
- Обновить фронтенд: TourCabinet Vue-компоненты (`ContestStage1Panel`, `Stage3ConfigsPanel`, `Stage2QuestionsPanel`, `DirectionCitiesPanel`, `TourUsers/Show`)
- Обновить маршруты: `{project_key}` → `{direction}` (route model binding)
- Обновить seeders: `DatabaseSeeder`, `PortalSeeder`, `AtomsVkusaSeeder`

## Вне scope

- Создание новых UI-страниц
- Изменение бизнес-логики конкурса (этапы, формы, проверки)
- Изменение публичной страницы направления (`Directions/Show.vue`)
- Изменение `OpportunityTours`

## Ограничения

- Обратная совместимость данных: существующие 3 направления имеют `project_key` → маппинг при миграции
- Max 5 файлов на задачу, max ~150 строк diff
- Docker-only выполнение команд
- Каждый шаг независимо верифицируем

## Затрагиваемые файлы

### Модели
- `app/Models/Direction.php`
- `app/Models/Tour.php`
- `app/Models/TourCabinetDirectionCity.php`
- `app/Models/TourCabinetContestProgress.php`
- `app/Models/TourCabinetContestStage2Question.php`
- `app/Models/TourCabinetContestStage3Config.php`
- `app/Models/TourCabinetContestDirectionSetting.php`

### Контроллеры
- `app/Http/Controllers/TourController.php`
- `app/Http/Controllers/Admin/TourController.php`
- `app/Http/Controllers/Admin/DirectionController.php`
- `app/Http/Controllers/Admin/TourCabinetDirectionCitiesController.php`
- `app/Http/Controllers/Admin/TourCabinetStage2QuestionsController.php`
- `app/Http/Controllers/Admin/TourCabinetStage3ConfigsController.php`
- `app/Http/Controllers/Admin/TourCabinetStage3AnswersController.php`
- `app/Http/Controllers/TourCabinetContestController.php`

### Сервисы
- `app/Services/Admin/TourCabinetHubPageData.php`
- `app/Services/Admin/TourCabinetClientContestDataService.php`
- `app/Services/TourCabinetContestDashboardData.php`

### Frontend
- `resources/js/Pages/Tours/Index.vue`
- `resources/js/Pages/Admin/Tours/Form.vue`
- `resources/js/Pages/Admin/Directions/Form.vue`
- `resources/js/Pages/Admin/Directions/Index.vue`
- `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue`
- `resources/js/Pages/Admin/TourCabinet/TourUsers/Show.vue`
- `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminStage3ConfigsPanel.vue`
- `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminStage2QuestionsPanel.vue`
- `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminDirectionCitiesPanel.vue`
- `resources/js/Pages/Admin/TourCabinet/DirectionCities/Index.vue`

### Миграции
- Новая: add `direction_id` to `tours`, migrate, drop `project`
- Новая: add `direction_id` to tour_cabinet tables, migrate, drop `project_key`
- Новая: drop `directions.project_key`

### Routes
- `routes/web.php` — обновить `{project_key}` → `{direction}`

### Seeders
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/PortalSeeder.php`
- `database/seeders/AtomsVkusaSeeder.php`
