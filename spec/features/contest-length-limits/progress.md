# Прогресс: contest-length-limits

## Completed tasks

### Task 1. Миграция + правка моделей ✓

- Files:
  - `database/migrations/2026_04_29_180000_add_length_limits_to_contest_stage2_and_stage3.php`
  - `app/Models/TourCabinetContestStage2Question.php` (`fillable`, `casts`)
  - `app/Models/TourCabinetContestStage3Config.php` (`fillable`, новый `casts(): array`)
- Verify:
  - `php artisan migrate` (Docker) — `DONE 7.31ms`.
  - php-однострочник (Schema::hasColumn) — `min_length`, `max_length`, `text_min_length`, `text_max_length` = `true`.

### Task 2. Админ-бэкенд (валидация + payload) ✓

- Files:
  - `app/Http/Controllers/Admin/TourCabinetStage2QuestionsController.php` (правила `nullable|integer|min:0|max:100000` + `normalizeLength` + `ensureLengthRangeIsValid`)
  - `app/Http/Controllers/Admin/TourCabinetStage3ConfigsController.php` (правила для `text_min_length|text_max_length` + локальный `normalizeLength` + проверка `min <= max`)
  - `app/Services/Admin/TourCabinetHubPageData.php` (`stage2QuestionsPayload` теперь отдаёт явный список ключей; `stage3ConfigsPayload` пополнен `text_min_length`/`text_max_length`)
- Verify: php-однострочник через Docker — `Eloquent::create/update/updateOrCreate` сохраняет/читает все 4 поля; payload-сервис отдаёт новые ключи.

### Task 3. Админ-фронт (Vue) ✓

- Files:
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminStage2QuestionsPanel.vue` — поля `Мин./Макс. символов` в форме создания и в каждой строке + кнопка «Сохранить лимиты» + строка `formatLengthRange(q)`.
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminStage3ConfigsPanel.vue` — секция «Лимиты длины текста ответа» внутри блока «все три этапа», `v-model.number` на `drafts[id].text_min_length|text_max_length`.
- Verify: `npm run build` (Docker) — `built in 5.27s`.

### Task 4. Пользовательский бэкенд ✓

- Files:
  - `app/Http/Controllers/TourCabinetContestController.php` (новые `validateStage2AnswerLengths`, `validateStage3TextLength`; вызов первого в `storeStage2`, вызов второго в трёх ветках `storeStage3`)
  - `app/Services/TourCabinetContestDashboardData.php` (`min_length`/`max_length` в каждом элементе `contestStage2Questions`; `text_min_length`/`text_max_length` в `contestStage3Progress.assignment`)
- Verify: php-однострочник — лимиты пробрасываются в payload; ручной просмотр `TourCabinetContestController` подтверждает три точки вызова `validateStage3TextLength`.

### Task 5. Пользовательский фронт ✓

- Files:
  - `resources/js/Pages/TourCabinet/Contest/ContestStage2Panel.vue` — подсказка `lengthHint(q)`, `:maxlength="q.max_length"`, счётчик `counterLabel/counterClass` (красный при превышении max, янтарный при недоборе min).
  - `resources/js/Pages/TourCabinet/Contest/ContestStage3Panel.vue` — `textLengthHint`, `textCounterLabel`, `textCounterClass` для `stage3_text`; computed `assignment` парсит `text_min_length`/`text_max_length`.
- Verify: `npm run build` (Docker) — успех.

### Task 6. Финальная верификация ✓

- `npm run build` (Docker) — `built in 5.27s`.
- `php artisan migrate` (Docker) — миграция применена.
- `php artisan route:list --path=admin/tour-cabinet/stage` (Docker) — все 8 роутов на месте.
- `ReadLints` по 12 затронутым файлам — чисто (PHP + Vue).

### Task 7. Финализация spec/progress ✓

- `spec/features/contest-length-limits/spec.md` — раздел «Реализация (как сделано)» + Verify summary.
- `spec/features/contest-length-limits/progress.md` — все 7 задач в Completed.

## Partially completed

(пусто)

## Not started

(пусто) — фича реализована полностью.

## Open issues

(пусто)
