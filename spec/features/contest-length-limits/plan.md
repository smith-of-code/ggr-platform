# План: contest-length-limits

## Принципы

- Sequential mode (одна задача в Partially completed, переходим только после завершения).
- Не более 5 файлов на шаг; иначе — дробление.
- Источник правды — `spec.md`. Историю выполнения — в `progress.md`.

## Порядок задач

### Task 1. Миграция + правка моделей

- Миграция `add_length_limits_to_contest_stage2_and_stage3.php` (новая):
  - `tour_cabinet_contest_stage2_questions`: `min_length` unsigned int nullable, `max_length` unsigned int nullable.
  - `tour_cabinet_contest_stage3_configs`: `text_min_length` unsigned int nullable, `text_max_length` unsigned int nullable.
- Модель `TourCabinetContestStage2Question`: расширить `$fillable` (`min_length`, `max_length`).
- Модель `TourCabinetContestStage3Config`: расширить `$fillable` (`text_min_length`, `text_max_length`).
- Verify: `php artisan migrate` (Docker), php-однострочник: `Schema::hasColumn(...)` для всех 4 колонок.

### Task 2. Админ-бэкенд (валидация + payload)

- `Admin/TourCabinetStage2QuestionsController`: добавить правила `min_length`, `max_length` в `store`/`update`, проверка `min <= max`, проброс в `create`/`update`.
- `Admin/TourCabinetStage3ConfigsController`: правила `text_min_length`, `text_max_length` в `update`, проверка `min <= max`.
- `Services/Admin/TourCabinetHubPageData`: пробрасывать `min_length`/`max_length` в `stage2QuestionsPayload` и `text_min_length`/`text_max_length` в `stage3ConfigsPayload`.
- Verify: php-однострочник — после `store` и `update` колонки в БД корректные; после `update` сабмита этапа 3 значения сохраняются.

### Task 3. Админ-фронт (Vue)

- `TourCabinetAdminStage2QuestionsPanel.vue`: два поля `min_length`/`max_length` в форме создания и в inline-редактировании.
- `TourCabinetAdminStage3ConfigsPanel.vue`: секция «Лимиты длины текста ответа» с двумя `<input type="number">` в форме каждой карточки направления.
- Verify: `npm run build` (Docker).

### Task 4. Пользовательский бэкенд

- `TourCabinetContestController::storeStage2`: валидация `max:max_length` для draft, `min:min_length`+`max:max_length` для finalize; единичные ошибки через `ValidationException::withMessages('answers.<id>')`.
- `TourCabinetContestController::storeStage3` (все три ветки): подмена `'string', 'max:20000'` на `'string', 'min:text_min_length', 'max:text_max_length'` (если значения заданы), либо просто `'string', 'max:20000'`. Лимиты берутся из `TourCabinetContestStage3Config`.
- `Services/TourCabinetContestDashboardData`: пробрасывать `min_length`/`max_length` в каждом элементе `contestStage2Questions`; `text_min_length`/`text_max_length` в `contestStage3Progress.assignment`.
- Verify: php-однострочник — request с длиной ниже min/выше max бракуется; в пределах — проходит.

### Task 5. Пользовательский фронт

- `Contest/ContestStage2Panel.vue`: счётчик символов + сообщение про лимиты под `<textarea>`; красный цвет при превышении.
- `Contest/ContestStage3Panel.vue`: счётчик символов + сообщение про лимиты под `stage3_text`; красный при превышении.
- Verify: `npm run build` (Docker).

### Task 6. Финальная верификация

- `npm run build` (Docker)
- `php artisan migrate:status` (Docker) — миграция применена
- `php artisan route:list --path=admin/tour-cabinet` и `--path=tour-cabinet/contest` — без регрессии
- `ReadLints` по всем затронутым файлам.

### Task 7. Финализация spec/progress

- Заполнить раздел «Реализация (как сделано)» в `spec.md`.
- Все 7 задач в `progress.md` → Completed.
