# Ограничения длины ответов конкурса (contest-length-limits)

## Goal

Добавить в админку ЛК Туры (`/admin/tour-cabinet`) возможность задавать **минимальное** и **максимальное количество символов**:

- по каждому **вопросу Этапа 2** конкурса (`tour_cabinet_contest_stage2_questions`);
- для **текста ответа Этапа 3** конкурса (поле `stage3_text` в `tour_cabinet_contest_progress`, настройки лежат в `tour_cabinet_contest_stage3_configs` — на каждое направление).

Лимиты применяются и в админке (валидация перед сохранением вопроса/конфига), и в ЛК участника (валидация при отправке ответа + счётчик символов в UI).

## In-scope

- Расширение существующих таблиц:
  - `tour_cabinet_contest_stage2_questions`: новые nullable колонки `min_length` (unsigned int) и `max_length` (unsigned int).
  - `tour_cabinet_contest_stage3_configs`: новые nullable колонки `text_min_length` (unsigned int) и `text_max_length` (unsigned int).
- Семантика «лимит не задан»: значение `NULL` (или `0`) в БД → ограничения нет.
- Админка `/admin/tour-cabinet`:
  - Блок «Этап 2 — вопросы»: в форме создания и в строках существующих вопросов появляются два поля `Мин. символов` / `Макс. символов`. Inline-редактирование (как у `sort_order`) через `PATCH admin.tour-cabinet.stage2-questions.update`.
  - Блок «Этап 3 — задание»: в карточке каждого направления появляется секция «Лимиты длины текста ответа» с двумя полями `Мин. символов` / `Макс. символов`, сохранение через существующий `PUT admin.tour-cabinet.stage3-config.update`.
- Валидация в админке (контроллеры): оба поля `nullable|integer|min:0|max:100000`; если заданы оба, то `min_length <= max_length` (отдельная проверка в контроллере).
- ЛК участника:
  - Этап 2: при `finalize=true` для каждого ответа проверяется длина строки (`mb_strlen` без ведущих/хвостовых пробелов) против `min_length`/`max_length` соответствующего вопроса. На черновике (`finalize=false`) применяется только `max_length` (чтобы не накапливать огромный текст), `min_length` игнорируется.
  - Этап 3: при сохранении ответа проверяется `text_min_length`/`text_max_length` для `stage3_text`. Видео/файл не лимитируются (только текст).
- UI участника: под `<textarea>` показывается строка-подсказка `«N / max»` или `«N / min..max»` (счётчик), при превышении max — счётчик красный.
- Пробрасываемые в Inertia поля:
  - `contestStage2Questions[*].min_length`, `contestStage2Questions[*].max_length` (nullable int).
  - `contestStage3Progress.assignment.text_min_length`, `contestStage3Progress.assignment.text_max_length` (nullable int).

## Out-of-scope

- Лимиты для текста ответа этапа 1 (вопросы там — на стороне `LmsForm`, не управляются этой фичей).
- Лимиты для `stage3_video_url` (URL — не текстовый ответ).
- Кастомизация сообщений ошибок в админке/ЛК сверх дефолтного формата Laravel (`The :attribute must be at least :min characters.`); русские формулировки берём из существующих lang-файлов проекта без правок.
- Локализация лимитов на каждого пользователя/город — единые лимиты на вопрос (этап 2) и на направление (этап 3).
- Блок «Коммерческие туры» — фича не трогает `tour_cabinet_commerce_*`.

## Constraints

- Все команды (миграция, route:list, npm build, pest) выполняются через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`) согласно `spec-continuation`.
- Миграция sqlite-совместима: только `Schema::table(...)->addColumn(...)`, без `dropColumn`/переименований.
- Backwards-compatibility: для существующих вопросов и конфигов значения колонок инициализируются как `NULL` → действующие сейчас лимиты не меняются.
- Reuse: не плодим новые компоненты — используем существующие `<input type="number">` в админ-панелях и текущие `<textarea>` в пользовательских; счётчик символов делается inline в `<p class="text-xs">` рядом с полем (без отдельного компонента).
- Вспомогательная проверка соответствия `min <= max` живёт в контроллерах (`ValidationException::withMessages`), без отдельных Form Request классов (соответствует прецеденту `TourCabinetStage2QuestionsController`).
- Геттер длины — `mb_strlen(trim($value))`, чтобы не штрафовать участника за пробелы по краям.

## Реализация (как сделано)

- БД: миграция `2026_04_29_180000_add_length_limits_to_contest_stage2_and_stage3.php` добавляет `min_length`/`max_length` в `tour_cabinet_contest_stage2_questions` (после `is_active`) и `text_min_length`/`text_max_length` в `tour_cabinet_contest_stage3_configs` (после `response_format`). Все четыре колонки `unsignedInteger nullable`.
- Модели: `TourCabinetContestStage2Question` — `$fillable` пополнен `min_length`/`max_length`, `casts` добавляет `min_length|max_length => integer`. `TourCabinetContestStage3Config` — `$fillable` пополнен `text_min_length`/`text_max_length`, добавлен метод `casts(): array` с обоими ключами `=> integer`.
- Админ-бэкенд:
  - `Admin/TourCabinetStage2QuestionsController::store|update` — правила `min_length|max_length: nullable|integer|min:0|max:100000`. Хелперы `normalizeLength` (пусто/`<=0` → `null`) и `ensureLengthRangeIsValid` (`min > max` → `ValidationException` на ключ `min_length`).
  - `Admin/TourCabinetStage3ConfigsController::update` — те же правила для `text_min_length|text_max_length`, та же проверка `min <= max` через локальный `normalizeLength`. Поля сохраняются в `updateOrCreate` рядом с `title`, `task_body`, `response_format`.
  - `Admin/TourCabinetHubPageData` — `stage2QuestionsPayload()` отдаёт массив с явным набором полей вопроса (включая `min_length`/`max_length`); `stage3ConfigsPayload()` пробрасывает `text_min_length`/`text_max_length` в каждый элемент `configs[]`.
- Пользовательский бэкенд:
  - `TourCabinetContestController::storeStage2` — после получения `$questions` вызывает `validateStage2AnswerLengths(...)`: для draft проверяется только `max_length`, для finalize — `max_length` (обязательно) и `min_length` (если ответ непустой). Сообщения адресуются ключу `answers.<id>`.
  - `TourCabinetContestController::storeStage3` — все три ветки (`$config === null`, `usesFileUpload()`, ветка video_link) вызывают `validateStage3TextLength($config, $validated['stage3_text'])`: проверяет `mb_strlen(trim($text))` против `text_min_length`/`text_max_length`; ошибка кладётся на ключ `stage3_text`.
  - `Services/TourCabinetContestDashboardData::buildDashboardData()` — `contestStage2Questions[*]` пополнен `min_length`/`max_length`; `contestStage3Progress.assignment` пополнен `text_min_length`/`text_max_length`.
- Админ-фронт:
  - `Admin/TourCabinet/TourCabinetAdminStage2QuestionsPanel.vue` — два `<input type="number">` (`Мин./Макс. символов`) в форме создания и в строке каждой записи; кнопка «Сохранить лимиты» патчит вопрос через `router.patch`. Хелпер `formatLengthRange(q)` показывает «не задан / 10–500 / от 10 / до 500» в строке метаданных. Пустое значение/0 нормализуется в `null` перед отправкой.
  - `Admin/TourCabinet/TourCabinetAdminStage3ConfigsPanel.vue` — секция «Лимиты длины текста ответа» внутри блока «все три этапа» каждого направления; два `<input type="number">` биндятся через `v-model.number` на `drafts[directionId].text_min_length|text_max_length`, сохраняются вместе с остальными настройками этапа 3.
- Пользовательский фронт:
  - `TourCabinet/Contest/ContestStage2Panel.vue` — для каждого вопроса с заданными лимитами добавлены подсказка `«Ограничения: от X до Y…»`, `:maxlength="q.max_length"` на `<textarea>`, счётчик `«N / max символов»` под полем; цвет: красный при `len > max`, янтарный при `0 < len < min`, серый по умолчанию.
  - `TourCabinet/Contest/ContestStage3Panel.vue` — те же три элемента (подсказка, `:maxlength`, счётчик) для поля `stage3_text`; computed `assignment` парсит `text_min_length`/`text_max_length` из `progress.assignment` (в фолбэке оба `null`).

## Verify summary

- `php artisan migrate` (Docker) — `2026_04_29_180000_add_length_limits_... DONE 7.31ms`.
- php-однострочник (Schema::hasColumn): все 4 колонки = `true`.
- php-однострочник (модели): сохранение/обновление `min_length=10,max_length=100` и `text_min_length=50,text_max_length=500` идёт корректно через `Eloquent::create|update`.
- php-однострочник (HubPageData): `stage2QuestionsPayload.questions[*]` включает `min_length,max_length`; `stage3ConfigsPayload.configs[*]` включает `text_min_length,text_max_length`.
- `npm run build` (Docker) — `built in 5.27s`, ошибок нет.
- `ReadLints` по 12 затронутым файлам — чисто.

