# Прогресс: tours-directions-sync

## Completed tasks

### Task 1: Direction helper-методы + Tour relationship ✓
- `Direction::activeProjectMap()`, `Direction::allProjectMap()`, `Direction::projectList()`, `Direction::tours()` hasMany
- `Tour::direction()` belongsTo
- Files: `app/Models/Direction.php`, `app/Models/Tour.php`

### Task 2: Миграция — tours.direction_id ✓
- Миграция: add `direction_id` FK, migrate data via `directions.project_key`, drop `project`
- Tour fillable: `project` → `direction_id`
- Files: `database/migrations/2026_04_24_100000_replace_tours_project_with_direction_id.php`, `app/Models/Tour.php`

### Task 3: Миграция — direction_id в tour_cabinet таблицах ✓
- 5 таблиц: direction_cities, contest_progress, stage2_questions, stage3_configs, direction_settings
- Модели: fillable `project_key` → `direction_id`, добавлены `direction()` BelongsTo
- Files: migration `2026_04_24_100100_...`, 5 моделей

### Task 4: Миграция — удалить directions.project_key ✓
- Миграция: drop `project_key` from `directions`
- Direction fillable: убран `project_key`
- Files: `database/migrations/2026_04_24_100200_...`, `app/Models/Direction.php`

### Task 5: Удалить Tour::PROJECTS + обновить TourController ✓
- Удалён `Tour::PROJECTS` const
- `TourController@index`: фильтр `direction_id`, eager-load `direction:id,title`, передача `Direction::projectList()`
- Admin `TourController`: `Direction::allProjectMap()` как prop, валидация `direction_id`
- `Admin\DirectionController`: убран `project_key` из валидации и пропсов
- Files: `app/Models/Tour.php`, 3 контроллера

### Task 6: Обновить TourCabinet сервисы ✓
- `TourCabinetHubPageData`, `TourCabinetContestDashboardData`, `TourCabinetClientContestDataService`
- Все `Tour::PROJECTS` → `Direction::projectList()` / `Direction::allProjectMap()`
- Все `project_key` → `direction_id`
- Files: 3 сервиса

### Task 7: Обновить TourCabinet контроллеры + маршруты ✓
- DirectionCitiesController, Stage2QuestionsController, Stage3ConfigsController, Stage3AnswersController, TourCabinetContestController
- `project_key` → `direction_id`, Route Model Binding для Direction
- `routes/web.php`: `/{project_key}` → `/{direction}`
- Files: 5 контроллеров, `routes/web.php`

### Task 8: Обновить фронтенд — каталог + админка туров/направлений ✓
- `Tours/Index.vue`: динамические `directionOptions` из пропсов
- `Admin/Tours/Form.vue`: `form.direction_id`, динамический select
- `Admin/Directions/Form.vue`, `Index.vue`: убран `project_key`
- Files: 4 Vue-компонента

### Task 9: Обновить фронтенд — TourCabinet Vue-компоненты ✓
- `ContestStage1Panel.vue`: `direction_id` вместо `project_key`
- `TourCabinetAdminStage3ConfigsPanel.vue`: `c.direction_id` вместо `c.project_key`
- `TourCabinetAdminStage2QuestionsPanel.vue`: `direction_id`
- `TourCabinetAdminDirectionCitiesPanel.vue`: `directionId` prop
- `DirectionCities/Index.vue`: `directionId` prop
- `TourUsers/Show.vue`: убран fallback на `project_key`
- Files: 6 Vue-компонентов

### Task 10: Seeders + финализация ✓
- `DatabaseSeeder`: `project` → `direction_id`, firstOrCreate для Direction перед турами
- `PortalSeeder`: убран `project_key`, `Tour::where('project', ...)` → `Tour::where('direction_id', ...)`
- `AtomsVkusaSeeder`: убран `project_key`
- `TourCabinetContestFormLinker`: `project_key` → `direction_id`
- `DirectionController`: `project_key === 'atoms_vkusa'` → `slug === 'atomy-vkusa'`
- `OpportunityToursController`: убран `project_key` из select, eager-load `direction:id,title`
- `Admin/TourController@index`: eager-load `direction:id,title`
- `TourController@show`: eager-load `direction:id,title`
- `Admin/Tours/Index.vue`, `Tours/Show.vue`, `OpportunityTours/Index.vue`: `tour.direction.title` вместо hardcoded `projectLabel()`
- Files: 3 seeders, 2 services, 4 controllers, 3 Vue-компонента

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
