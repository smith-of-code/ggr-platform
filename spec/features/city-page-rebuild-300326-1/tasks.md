# Задачи: city-page-rebuild-300326-1

## T1. Миграция — добавить поля founded_year, population_year и timezone

- **Цель**: добавить колонки `founded_year` (integer nullable), `population_year` (integer nullable) и `timezone` (string nullable) в `cities`.
- **Scope**: `database/migrations/xxxx_add_founded_year_timezone_to_cities.php`
- **DoD**: миграция создана, `php artisan migrate` проходит без ошибок.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## T2. Модель City — добавить fillable

- **Цель**: добавить `founded_year`, `population_year` и `timezone` в `$fillable` модели City.
- **Scope**: `app/Models/City.php`
- **DoD**: поля присутствуют в `$fillable`.
- **Verify**: визуально в коде.

## T3. Валидация в Admin\CityController

- **Цель**: добавить правила валидации для `founded_year`, `population_year` и `timezone` в методы `store` и `update`.
- **Scope**: `app/Http/Controllers/Admin/CityController.php`
- **DoD**: правила `'founded_year' => 'nullable|integer|min:1000|max:2100'`, `'population_year' => 'nullable|integer|min:1900|max:2100'`, `'timezone' => 'nullable|string|max:20'`.
- **Verify**: визуально в коде.

## T4. Админ-форма — добавить поля

- **Цель**: добавить инпуты «Год основания», «Население — по состоянию на год» и «Часовой пояс» в форму `Admin/Cities/Form.vue`.
- **Scope**: `resources/js/Pages/Admin/Cities/Form.vue`
- **DoD**: поля отображаются в форме, значения сохраняются через Inertia form.
- **Verify**: визуально в браузере.

## T5. Stats bar — переделать дизайн

- **Цель**: заменить текущий stats bar (население, достопримечательности, туры) на новый (год основания, население с указанием года, часовой пояс) по скриншоту.
- **Scope**: `resources/js/Pages/Cities/Show.vue` (секция «Key stats bar», строки ~60–102)
- **DoD**: 3 колонки с крупными цветными числами, подписями внизу, разделителями; адаптивность.
- **Verify**: визуально в браузере.

## T6. Cleanup computed свойств

- **Цель**: убрать `hasPopulationStat`, обновить `aboutInfographicRows` — убрать дублирование населения (оно теперь в stats bar).
- **Scope**: `resources/js/Pages/Cities/Show.vue` (script секция)
- **DoD**: computed свойства актуальны, нет неиспользуемого кода.
- **Verify**: визуально в коде + страница рендерится без ошибок.

## T7. Верификация

- **Цель**: проверить работоспособность: миграция, админ-форма, публичная страница.
- **Scope**: Docker-команды.
- **DoD**: `php artisan migrate` успешна, страница города рендерится без console-ошибок.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate:status`
