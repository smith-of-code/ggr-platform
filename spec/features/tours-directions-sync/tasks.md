# Задачи: tours-directions-sync

## Task 1: Direction helper-методы + Tour relationship

- **Цель**: Добавить статические методы на `Direction` для получения карты проектов и belongsTo relationship на `Tour`. Не удаляя старый код — подготовка без поломок.
- **Scope**: `app/Models/Direction.php`, `app/Models/Tour.php`
- **DoD**: `Direction::activeProjectMap()` возвращает `[id => title]` активных направлений; `Direction::projectList()` возвращает `[{key, label}]`; `Tour::direction()` возвращает belongsTo.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="var_dump(App\Models\Direction::activeProjectMap());"`

## Task 2: Миграция — tours.direction_id

- **Цель**: Добавить `direction_id` FK в `tours`, мигрировать данные из `project` через маппинг `directions.project_key`, удалить колонку `project`.
- **Scope**: новая миграция, `app/Models/Tour.php` (обновить fillable: убрать `project`, добавить `direction_id`)
- **DoD**: Миграция проходит; существующие туры с `project` получают корректный `direction_id`; колонка `project` удалена.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## Task 3: Миграция — direction_id в tour_cabinet таблицах

- **Цель**: Заменить `project_key` на `direction_id` FK в 5 таблицах: `tour_cabinet_direction_cities`, `tour_cabinet_contest_progress`, `tour_cabinet_contest_stage2_questions`, `tour_cabinet_contest_stage3_configs`, `tour_cabinet_contest_direction_settings`. Мигрировать данные через `directions.project_key`.
- **Scope**: новая миграция, 5 моделей (`TourCabinetDirectionCity`, `TourCabinetContestProgress`, `TourCabinetContestStage2Question`, `TourCabinetContestStage3Config`, `TourCabinetContestDirectionSetting`)
- **DoD**: Миграция проходит; данные мигрированы; колонки `project_key` удалены; модели обновлены (fillable: `direction_id` вместо `project_key`).
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## Task 4: Миграция — удалить directions.project_key + обновить модель

- **Цель**: Удалить колонку `project_key` из `directions`. Обновить модель Direction (убрать из fillable). Обновить валидацию в `Admin\DirectionController` (убрать правило `project_key`).
- **Scope**: новая миграция, `app/Models/Direction.php`, `app/Http/Controllers/Admin/DirectionController.php`
- **DoD**: Миграция проходит; `project_key` убран из fillable и валидации; направление создаётся/редактируется без project_key.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## Task 5: Удалить Tour::PROJECTS + обновить публичный TourController

- **Цель**: Убрать константу `Tour::PROJECTS`. Обновить `TourController@index` — фильтр по `direction_id` вместо `project`, передавать активные направления как prop `directions`. Обновить Admin\TourController — валидация `direction_id` → `exists:directions,id`.
- **Scope**: `app/Models/Tour.php`, `app/Http/Controllers/TourController.php`, `app/Http/Controllers/Admin/TourController.php`
- **DoD**: Константа `PROJECTS` удалена; каталог фильтрует по `direction_id`; админка валидирует `direction_id`.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=tours`

## Task 6: Обновить TourCabinet сервисы

- **Цель**: Заменить все `Tour::PROJECTS` на запросы к `Direction` в трёх сервисах.
- **Scope**: `app/Services/Admin/TourCabinetHubPageData.php`, `app/Services/TourCabinetContestDashboardData.php`, `app/Services/Admin/TourCabinetClientContestDataService.php`
- **DoD**: Ни один из трёх сервисов не ссылается на `Tour::PROJECTS`; список направлений строится динамически из `Direction`.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php -r "require 'vendor/autoload.php'; echo 'OK';"`; grep Tour::PROJECTS в этих файлах = 0 результатов.

## Task 7: Обновить TourCabinet контроллеры + маршруты

- **Цель**: Заменить `Tour::PROJECTS` и `project_key` на `direction_id` / `Direction` в контроллерах: `TourCabinetDirectionCitiesController`, `TourCabinetStage2QuestionsController`, `TourCabinetStage3ConfigsController`, `TourCabinetStage3AnswersController`, `TourCabinetContestController`. Обновить маршруты в `routes/web.php`: `{project_key}` → `{direction}`.
- **Scope**: 5 контроллеров + `routes/web.php`
- **DoD**: Все контроллеры используют `direction_id`; маршруты обновлены; валидация через `exists:directions,id`.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=tour-cabinet`

## Task 8: Обновить фронтенд — каталог + админка туров/направлений

- **Цель**: `Tours/Index.vue` — динамические options из props `directions`, фильтр по `direction_id`. `Admin/Tours/Form.vue` — select direction из props. `Admin/Directions/Form.vue` — убрать поле project_key. `Admin/Directions/Index.vue` — убрать projectLabel для project_key.
- **Scope**: `resources/js/Pages/Tours/Index.vue`, `resources/js/Pages/Admin/Tours/Form.vue`, `resources/js/Pages/Admin/Directions/Form.vue`, `resources/js/Pages/Admin/Directions/Index.vue`
- **DoD**: Каталог отображает направления из props; админ-форма тура предлагает direction из БД; форма направления без project_key.
- **Verify**: Визуальная проверка + отсутствие ошибок в браузерной консоли.

## Task 9: Обновить фронтенд — TourCabinet Vue-компоненты

- **Цель**: Обновить 5 Vue-компонентов TourCabinet: заменить `project_key` → `direction_id` в формах, props, route params.
- **Scope**: `ContestStage1Panel.vue`, `TourCabinetAdminStage3ConfigsPanel.vue`, `TourCabinetAdminStage2QuestionsPanel.vue`, `TourCabinetAdminDirectionCitiesPanel.vue`, `DirectionCities/Index.vue`
- **DoD**: Все компоненты используют `direction_id`; route params обновлены; формы отправляют `direction_id`.
- **Verify**: Визуальная проверка + `Admin/TourUsers/Show.vue` корректно показывает направление.

## Task 10: Seeders + финализация

- **Цель**: Обновить seeders (`DatabaseSeeder`, `PortalSeeder`, `AtomsVkusaSeeder`) — `direction_id` вместо `project`. Прогнать полный цикл миграций + seed. Обновить spec до финального состояния.
- **Scope**: `database/seeders/DatabaseSeeder.php`, `database/seeders/PortalSeeder.php`, `database/seeders/AtomsVkusaSeeder.php`, `spec/features/tours-directions-sync/*`
- **DoD**: `php artisan migrate:fresh --seed` проходит без ошибок; spec отражает финальное состояние; progress.md заполнен.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate:fresh --seed`
