# Прогресс: LMS — Отчёты: соблюдение сроков и прогресс участников

## Completed tasks

### Task 1 — Поле `default_assignment_deadline` на событии (БД + модель + контроллер) ✓

- Done:
  1. Миграция `database/migrations/2026_05_05_140000_add_default_assignment_deadline_to_lms_events.php` (timestamp nullable after `menu_config`).
  2. `app/Models/Lms/LmsEvent.php` — `Carbon` import, `serializeDate(): …Z`, `default_assignment_deadline` в `$fillable` и `$casts => 'datetime'`.
  3. `app/Http/Controllers/Lms/Admin/EventController.php` — правило `default_assignment_deadline => nullable|date` в `store/update`.
  4. Миграция накачена в Docker; `Schema::hasColumn('lms_events','default_assignment_deadline') === true`.
  5. Линт: Pint clean на изменённых файлах. Smoke через tinker: `create+update+null+serialize` → `2026-06-20T23:59:59Z`. Unit-тесты `tests/Unit/Lms/LmsEventDefaultDeadlineTest.php` (4 теста / 5 утверждений) — зелёные.
- Files:
  - `database/migrations/2026_05_05_140000_add_default_assignment_deadline_to_lms_events.php` (new)
  - `app/Models/Lms/LmsEvent.php`
  - `app/Http/Controllers/Lms/Admin/EventController.php`
  - `tests/Unit/Lms/LmsEventDefaultDeadlineTest.php` (new)

### Task 2 — UI редактирования фолбэк-дедлайна в админке события ✓

- Done:
  1. UI Kit Lookup: реюз `RInput type="datetime-local"`, `RCard`, `RCheckbox`, `RButton`; хелперы `lmsDeadlineToDatetimeLocalUtc` / `datetimeLocalToUtcIso` уже есть; паттерн как в `Pages/Lms/Admin/Assignments/Form.vue:170,202,257`.
  2. `resources/js/Pages/Lms/Admin/Events/Edit.vue` — импорт хелперов; поле формы `default_assignment_deadline` (init из `props.event.default_assignment_deadline` через `lmsDeadlineToDatetimeLocalUtc`); `<RInput type="datetime-local" label="Дедлайн по умолчанию">` с подсказкой об UTC; `submit()` оборачивается в `form.transform(...)` с `datetimeLocalToUtcIso`.
  3. `resources/js/Pages/Lms/Admin/Events/Create.vue` — импорт `datetimeLocalToUtcIso`; поле формы `default_assignment_deadline: ''`; такой же `<RInput>`; `submit()` через `form.transform(...)`.
  4. Vite production-сборка в Docker: `npm run build` → ✓ built in 5.74s, без ошибок.
  5. Линт: ReadLints clean на обоих файлах; pre-existing pint issue `class_attributes_separation` в `EventController.php` (не вносился) оставлен.
- Files:
  - `resources/js/Pages/Lms/Admin/Events/Edit.vue`
  - `resources/js/Pages/Lms/Admin/Events/Create.vue`

### Task 3 — Сервис `DeadlineService` ✓

- Done:
  1. `config/lms.php` (новый): ключ `reports.default_deadline = env('LMS_REPORTS_DEFAULT_DEADLINE', '2026-06-20T23:59:59Z')` как «фолбэк-фолбэк»; smoke `config('lms.reports.default_deadline')` → `2026-06-20T23:59:59Z`.
  2. `app/Services/Lms/Reports/DeadlineService.php` (новый): `resolveDeadline(?LmsAssignment, LmsEvent): Carbon` (цепочка `assignment.deadline → event.default_assignment_deadline → config(...)`, всегда UTC); `resolveApprovedAt(LmsAssignmentSubmission): ?Carbon` (последний review с `decision='approve'`, использует `relationLoaded('reviews')` для anti-N+1); `classify(deadline, ?approvedAt)` → `on_time` / `late` / `overdue` (одобрение ровно в дедлайн = `on_time`, отсутствие одобрения = `overdue`); `delayDays(...)` → `int >= 0` с округлением вверх.
  3. `tests/Unit/Lms/Reports/DeadlineServiceTest.php` — 13 тестов / 19 утверждений (резолв дедлайна по всем веткам цепочки + null-assignment для тестов; approved_at для approve / reject / пустой коллекции; классификация в граничных точках; delay_days для on_time, partial day, > суток).
  4. Verify: `php artisan test --filter='DeadlineServiceTest'` в Docker — `13 passed (19 assertions)`. Тесты не зависят от sqlite-миграций (используют `setRelation`), что обходит блокер по `90-open-questions.md` п.9.
  5. Линт: ReadLints clean; Pint `--test` на 3 файлах — `PASS`.
- Files:
  - `config/lms.php` (new)
  - `app/Services/Lms/Reports/DeadlineService.php` (new)
  - `tests/Unit/Lms/Reports/DeadlineServiceTest.php` (new)

### Task 4 — Метод `getDeadlineCompliance` в ReportController ✓

- Done:
  1. `app/Http/Controllers/Lms/Admin/ReportController.php` — добавлен private `getDeadlineCompliance(int $eventId, ?string $roleFilter, ?int $courseFilter, ?int $cityFilter, ?string $facultyFilter)`. SQL: JOIN-цепочка `lms_assignments → lms_events → lms_stage_blocks → lms_course_stages → lms_courses → lms_course_enrollments → users → lms_profiles + LEFT JOIN cities`. Подзапрос `ar` агрегирует MAX(review.created_at) WHERE decision='approve' по (assignment_id, user_id). COALESCE-цепочка `a.deadline → e.default_assignment_deadline → '<config>'::timestamp` (Postgres-only). Дефолт берётся из `config('lms.reports.default_deadline')` через `Carbon::parse(...)->utc()->format('Y-m-d H:i:s')` — без SQL-инъекций (формат строго numeric/dash). Метрики: `total_users`, `on_time` (approved_at <= deadline), `late` (approved_at > deadline), `overdue` (approved_at IS NULL — включая «не сдавал совсем»), `avg_delay_days = ROUND(AVG(CEIL(EXTRACT(EPOCH FROM (approved_at - deadline)) / 86400.0)), 2)` только для late.
  2. `index()` — добавлены query-params `city_id` и `faculty`; вызов `getDeadlineCompliance` с приведением типов; Inertia-пропсы `deadlineCompliance`, `availableCities`, `availableFaculties`; `filters` дополнен `city_id`/`faculty`. Фильтр city_id строится из distinct `lms_profiles.city_id × cities.name` (только не-null), фильтр faculty — distinct `lms_course_enrollments.faculty` (не-null/не-пустые) внутри события.
  3. Импорт `Carbon\Carbon`; импорт `DeadlineService` не понадобился — SQL дублирует семантику `resolveDeadline` через COALESCE (для batch-агрегации иначе никак).
  4. Verify: smoke в Docker через reflection (`new ReflectionMethod($ctrl, 'getDeadlineCompliance')->invoke(...)`) на событии `vshgr-2026` — `rows=15`, корректные группировки по (assignment, course, city, faculty). На текущих данных `approve`-ревью = 0, поэтому все строки overdue, что ожидаемо. Дополнительно проверена арифметика SQL `CEIL(EXTRACT(EPOCH FROM ...)/86400.0)` напрямую: `7 / 1 / 2` дня для дельт `+7д / +23ч / +24ч 1с` — совпадает с PHP `DeadlineService::delayDays`.
  5. Линт: ReadLints clean; Pint `--test` PASS (поправлен single_quote стиль для не-интерполируемых частей `$approvedSubquery`). Регресс: `php artisan test --testsuite=Unit --filter='Lms'` — `17 passed (24 assertions)`.
- Files:
  - `app/Http/Controllers/Lms/Admin/ReportController.php`

### Task 5 — Метод `getPersonalProgress` в ReportController ✓

- Done:
  1. `app/Http/Controllers/Lms/Admin/ReportController.php` — добавлен private `getPersonalProgress(int $eventId, ?string $roleFilter, ?int $courseFilter, ?int $cityFilter, ?string $facultyFilter)`. План = `lms_course_stages + lms_stage_blocks` enrolled-курсов пользователя в рамках события. 6 LEFT JOIN-подзапросов на `lms_profiles × users`: `at`/`ad` (assignments_total/done через `sb.lms_assignment_id IS NOT NULL` + `lms_assignment_submissions.status='approved'`), `tt`/`td` (tests_total/done через `sb.lms_test_id IS NOT NULL` + `lms_test_attempts.passed=true`), `st` (stages_total из `lms_course_stages`), `sd` (stages_done из `lms_stage_progress.status='completed'`). `$planWhere` динамически строится из `course_id` и `faculty` фильтров (для faculty экранирована `'` через двойную); `role`/`city_id` применяются к внешнему `lms_profiles`. Orphan-задания (без `lms_stage_blocks`) автоматически исключены, т.к. план берётся именно из stage_blocks. PHP-обвязка считает `*_pct` и `overall_pct = (sum done) / (sum total) * 100` (item-fairness, каждая единица плана весит одинаково); `min(done, total)` страхует от арифметического превышения 100%.
  2. `index()` — введены приведённые `$courseFilterInt/$cityFilterInt/$facultyFilterStr` (использованы и в `getDeadlineCompliance`, и в `getPersonalProgress`); вызов `getPersonalProgress(...)`; Inertia-пропс `personalProgress`.
  3. Verify: smoke через reflection на `vshgr-2026` — `rows=424`, `with_plan=423` (1 профиль без enrollment — leader). Аверьянова Галина: `1/2 assignments + 1/1 tests + 6/6 stages` → assignments_pct=50, tests_pct=100, stages_pct=100, overall_pct=88.9 (математически (1+1+6)/(2+1+6)=8/9=88.89%). Фильтр `role='participant', course_id=3`: rows=417, корректно сужает план.
  4. Линт: ReadLints clean; Pint `--test` PASS на ReportController. Регресс: `php artisan test --testsuite=Unit --filter='Lms'` — `17 passed (24 assertions)`.
- Files:
  - `app/Http/Controllers/Lms/Admin/ReportController.php`

### Task 6 — Метод `getCityComparison` в ReportController ✓

- Done:
  1. `app/Http/Controllers/Lms/Admin/ReportController.php` — добавлен private `getCityComparison(int $eventId, ?int $courseId)`. При `courseId === null` — `abort(422, 'course_id is required for city comparison.')`. Реюз: `getDeadlineCompliance(eventId, null, courseId, null, null)` (per-(assignment,course,city,faculty)) + `getPersonalProgress(eventId, null, courseId, null, null)` (per-user). Дополнительно: SQL `participants` (DISTINCT users enrolled в course с профилем события, GROUP BY city_id) и `testScores` (best attempt per user×test для тестов курса из stage_blocks → AVG по city_id). PHP-обвязка: суммирует on_time/late/overdue/total по city из compliance; собирает overall_pct по city из progress (фильтр `total_plan > 0`, чтобы users без плана не размывали среднее); финальные метрики `participants_count, on_time_pct, overdue_pct, avg_overall_pct, avg_test_score`. Сортировка по city_name (NULL последним).
  2. `index()` — `$cityComparison = $courseFilterInt !== null ? $this->getCityComparison(...) : null;` (без course_id вкладка пустая, без 422 на странице); Inertia-пропс `cityComparison`.
  3. Verify: smoke через reflection на `vshgr-2026` с `courseId=3`: 2 строки (Билибино, NULL). Билибино: 1 уч., overdue_pct=100, avg_overall=66.7, avg_test_score=80. NULL-город: 422 уч. (большинство без city_id), avg_overall=38.7, avg_test_score=93.8. Отдельно проверен `abort(422)` при `courseId=null`: `STATUS=422 MSG=course_id is required for city comparison.`
  4. Линт: ReadLints clean; Pint `--test` PASS. Регресс: `php artisan test --testsuite=Unit --filter='Lms'` — `17 passed (24 assertions)`.
- Files:
  - `app/Http/Controllers/Lms/Admin/ReportController.php`

### Task 7 — Недельная динамика ✓

- Done:
  1. `app/Http/Controllers/Lms/Admin/ReportController.php` — `getActivityTimeline(int $eventId, ?string $dateFrom, ?string $dateTo, string $granularity = 'day')`. Bucket-выражение через closure: `day → DATE(col)`, `week → DATE_TRUNC('week', col)::date` (Postgres-only, понедельник недели). Дефолтный период расширен для week-режима: `subDays(90)` вместо `subDays(30)`. Добавлены 3 новые серии `assignments_approved` (`lms_assignment_reviews.created_at WHERE decision='approve'`), `tests_passed` (`lms_test_attempts.finished_at WHERE passed=true`), `stages_completed` (`lms_stage_progress.completed_at WHERE status='completed'`). В ответ включено поле `granularity`. Контракт совместим: ключи `enrollments / completions / test_attempts` и формат корзины `YYYY-MM-DD` сохранены; новые серии возвращаются в обоих режимах (UI вправе их не отображать в day-режиме).
  2. `index()` — добавлен query-param `granularity` (default `'day'`); проброшен в `getActivityTimeline(...)`; поле `filters.granularity` в Inertia-пропсах.
  3. Verify: smoke через reflection на `vshgr-2026` за период `2026-04-01..2026-05-05` для обоих режимов. day: enrollments 20 buckets (06.04: 4, 07.04: 382), 0 approved (валидно — данных нет), tests_passed 18 buckets, stages_completed 18 buckets. week: enrollments 4 buckets с понедельниками (`2026-04-06`: 480 = сумма 386+… за неделю, `2026-04-13`: 91), test_attempts 3, stages_completed 3 (404, 633, …) — недельная агрегация совпадает с ожиданиями. Поле `granularity` в ответе корректное.
  4. Линт: ReadLints clean; Pint `--test` PASS. Регресс: `php artisan test --testsuite=Unit --filter='Lms'` — `17 passed (24 assertions)`.
- Files:
  - `app/Http/Controllers/Lms/Admin/ReportController.php`

### Task 8 — Экспорт CSV и Email новых секций ✓

- Done:
  1. `app/Http/Controllers/Lms/Admin/ReportController.php` — добавлены private-writer-методы `writeCsvDeadline(eventId)`, `writeCsvPersonal(eventId)`, `writeCsvCities(eventId, courseId)`, `writeCsvDynamics(eventId, dateFrom, dateTo, granularity)` и хелпер `csvCell(?string)` (нормализация `null → '—'` + экранирование `"`). Колонки и формат — по аналогии с существующими секциями (`UTF-8 BOM` + `;`-разделитель, кавычки вокруг каждой ячейки). `writeCsvDynamics` сливает 6 серий в общую сетку buckets через `ksort` всех ключей.
  2. `download()` — введены query-params `course_id`, `date_from`, `date_to`, `granularity`; ранний `abort(422, 'course_id is required for cities section.')` при `section=cities` без `course_id`; добавлены ветки `deadline / personal / cities / dynamics`. В режиме `section=all` `cities` пишется только при наличии `course_id` (избегаем 422 на «общем» экспорте).
  3. `sendEmail()` — добавлена строгая валидация `sections.* in:users,courses,tests,stages,deadline,personal,cities,dynamics`, `course_id|nullable|integer`, `date_from/date_to|nullable|date`, `granularity|nullable|in:day,week`. Симметричный `abort(422)` при `cities` без `course_id`. Расширены `if`-ветки `deadline / personal / cities / dynamics` (для `dynamics` обходит 6 серий и сливает buckets через `ksort`).
  4. Verify smoke в Docker (через reflection writer-методов и `Request::create + $ctrl->download(...)`) на `vshgr-2026`:
     - `writeCsvDeadline` → 18 строк (заголовок + 17 группировок), city/faculty `null → '—'`, avg_delay `null → '—'`.
     - `writeCsvPersonal` → 427 строк; Аверьянова Галина: `2/1=50%, 1/1=100%, 6/6=100%, overall=88.9%` — соответствует Task 5.
     - `writeCsvCities(courseId=3)` → 290 байт, 5 строк (заголовок + 2 города), `Билибино: 1, 0%, 100%, 66.7%, 80%`.
     - `writeCsvDynamics(2026-04-01..2026-05-05, week)` → 8 строк, корзина `2026-04-06: 480 / 37 / 84 / 0 / 79 / 404`.
     - `download(section=cities)` без `course_id` → `STATUS=422 MSG='course_id is required for cities section.'`; с `course_id=3` → 293 байта валидного CSV.
  5. Линт: ReadLints clean; Pint `--test` PASS. Регресс: `php artisan test --testsuite=Unit --filter='Lms'` — `17 passed (24 assertions)`.
- Files:
  - `app/Http/Controllers/Lms/Admin/ReportController.php`

### Task 9 — UI: новые вкладки/фильтры + документация фичи lms-reports ✓

- Done:
  1. UI Kit Lookup: подтверждено, что `RCard / RBadge / RButton / RModal / RCheckbox / RInput` достаточно; `RTabs` намеренно НЕ заменяет существующие кастомные tab-кнопки (минимальный диф, единый стиль с другими табами `Index.vue`); прогресс-бары — кастомные `<div>` (`progressBarColor(pct)`), т.к. для item-fairness % нужен один сегмент.
  2. `resources/js/Pages/Lms/Admin/Reports/Index.vue` — расширен `defineProps`: `deadlineCompliance / personalProgress / cityComparison / availableCities / availableFaculties` (плюс прежние). В блок фильтров добавлены селекты «Город», «Факультет», «Гранулярность» (`day` / `week`); `applyFilters / clearFilters` синхронизируют `city_id / faculty / granularity` с URL (день — без явного query, как дефолт).
  3. Добавлены 3 новые вкладки в общий список табов (между `assignments` и `activity`): «Сроки» (10 колонок: assignment/course/city/faculty/totals/on_time/late/overdue/avg_delay_days/распределение через 3-сегментный бар), «Прогресс участников» (assignments/tests/stages с дробями + `done/total (pct%)` + общий `overall_pct` бар), «Города» (с placeholder-карточкой при отсутствии `course_id`, иначе таблица participants/on_time/overdue/avg_overall/avg_test_score). Цвет статусов через `RBadge variant=success/warning/danger`. Раздел «Динамика» расширен: добавлен лейбл текущей гранулярности; компьют `timelineDates` поддерживает `granularity='week'` (старт от понедельника, шаг 7 дней); `timelineSeries` дополнен 3 новыми рядами `assignments_approved / tests_passed / stages_completed`.
  4. Email-форма: добавлены 4 чекбокса (`deadline / personal / cities / dynamics`); при выборе «Города» без `course_id` показывается amber-предупреждение и блокируется кнопка отправки (`emailNeedsCourse`). `sendReport()` дополняет payload `course_id` (если выбран) и для секции `dynamics` — `date_from / date_to / granularity` из текущих фильтров.
  5. `spec/features/lms-reports/spec.md` — добавлен раздел «Расширения отчёта (deadline-progress)»: контракты Inertia-пропсов, фильтры (включая 422 на `cities` без `course_id`), описание SQL-источников, секции CSV/email, явная фиксация UI-решения «без RTabs». Обновлены роуты (добавлен `download`) и связанные сущности (`DeadlineService`, config-ключ, `lms_events.default_assignment_deadline`).
  6. Verify: `npm run build` в Docker — `built in 5.41s`, без warning'ов; `pint --test` на `ReportController.php` — PASS; `ReadLints` на `Index.vue` — clean. Backend-контракт перепроверен grep'ом по `ReportController.php`: пропсы `deadlineCompliance / personalProgress / cityComparison / availableCities / availableFaculties` и фильтры `granularity / city_id / faculty` присутствуют → UI читает то, что бэкенд отдаёт.
- Files:
  - `resources/js/Pages/Lms/Admin/Reports/Index.vue`
  - `spec/features/lms-reports/spec.md`

### Task 10 — Регресс: orphan-задания ✓

- Done:
  1. Дизайн: orphan = `lms_assignments` события без записи в `lms_stage_blocks`. Решение — orphan видны в отчёте, но не влияют на план: (a) в `getDeadlineCompliance` — отдельной строкой `is_orphan=true` (course/city/faculty=null), скрываются при `course_id`/`faculty` фильтрах; (b) в `getPersonalProgress` — отдельные поля `assignments_orphan_total/done` без влияния на `overall_pct`.
  2. `app/Http/Controllers/Lms/Admin/ReportController.php` — `getDeadlineCompliance` разбит на plan-запрос (с новым полем `DB::raw('false as is_orphan')`) + новый private `getOrphanDeadlineCompliance(eventId, deadlineExpr, approvedSubquery, role?, city?)` (база участников = `lms_profiles` события, `whereNotExists(lms_stage_blocks)`, NULL-cast для course/city/faculty). Финальный результат — `concat($planRows, $orphanRows)`. Orphan-блок не запускается, если задан `courseFilter !== null` или непустой `facultyFilter`.
  3. `getPersonalProgress` дополнен: вычисление `$orphanIds` (whereNotExists стейдж-блоков) → константа `$orphanTotal`, отдельный pluck `$orphanDoneByUser` (`status='approved'` среди orphan_ids). В каждом row добавлены `assignments_orphan_total` (на всё событие) и `assignments_orphan_done = min(orphanDone, orphanTotal)`. Существующие `assignments_*/overall_pct` НЕ изменены (item-fairness формула сохранена).
  4. CSV — `writeCsvDeadline` дополнен колонкой `Вне плана` (да/нет) + `course_title ?? '—'`. `writeCsvPersonal` дополнен колонками `Доп. (вне плана)` и `Доп. принято` в конце строки. `sendEmail()` — симметричные правки `deadline` и `personal` секций.
  5. UI `resources/js/Pages/Lms/Admin/Reports/Index.vue` — вкладка «Сроки»: amber-подложка строки + `<RBadge variant="warning">Вне плана</RBadge>` в колонке «Задание» при `r.is_orphan`; `course_title` отображается как `—` для orphan. Вкладка «Прогресс»: новая колонка «Доп.» (`done/total`) с tooltip «Orphan-задания вне плана программ»; при `assignments_orphan_total === 0` показывает `—` (gray-300).
  6. `spec/features/lms-reports/spec.md` — добавлен подраздел «Orphan-задания (вне плана программ)» с правилами для `getDeadlineCompliance`, `getPersonalProgress`, `getCityComparison`, UI и CSV.
  7. Verify в Docker: на `vshgr-2026` (event_id=1) реальная фикстура — 1 orphan из 6 заданий события («Отбор в акселератор ВШГР»). `getDeadlineCompliance(no filters)` → 16 строк (15 plan + 1 orphan, total_users=424, все overdue — данных по approve нет). `getDeadlineCompliance(course_id=3)` → 4 plan + 0 orphan (orphan-фильтрация работает). `getPersonalProgress(no filters)` → 424 строки, у каждой `assignments_orphan_total=1, assignments_orphan_done=0`. **Аверьянова Галина**: `assignments=1/2 (50%), tests=1/1 (100%), stages=6/6 (100%), overall_pct=88.9%, orphan=0/1` — `overall_pct` совпадает с замером Task 5 (orphan не размывает план). CSV-выход: ORPHAN-строка `"Отбор в акселератор ВШГР";"—";"—";"—";"да";"424";"0";"0";"424";"—"`; personal-строка GALA содержит хвост `"88.9%";"1";"0"`.
  8. Линт: ReadLints clean (Vue + PHP); Pint `--test` PASS на ReportController. Регресс: `php artisan test --testsuite=Unit --filter='Lms'` — `17 passed (24 assertions)`. `npm run build` в Docker — `built in 5.65s`, без warning'ов.
- Files:
  - `app/Http/Controllers/Lms/Admin/ReportController.php`
  - `resources/js/Pages/Lms/Admin/Reports/Index.vue`
  - `spec/features/lms-reports/spec.md`

## Partially completed

_(пусто)_

## Not started

_(пусто — все задачи завершены, фича готова к закрытию)_

## Open issues

- HTTP Feature-тест на `EventController@store/update` (через роуты `lms.admin.events.*`) — отложен до починки `90-open-questions.md` п.9 (миграция `2026_04_24_100100_replace_project_key_with_direction_id_in_tour_cabinet_tables` ломает все Feature-тесты с `RefreshDatabase` на sqlite `:memory:`). Для текущей задачи покрытие закрыто Unit-тестом + Pint + smoke через tinker.
- HTTP Feature-тест на `ReportController@index` (Task 4, `getDeadlineCompliance`) — заблокирован тем же п.9 90-open-questions. Для Task 4 покрытие закрыто smoke через reflection на реальной Postgres-базе и независимой проверкой SQL-арифметики `CEIL/EXTRACT EPOCH`. Полноценный Feature-тест с фикстурами (1 on_time / 1 late / 1 overdue) добавим после починки sqlite-блокера или при переключении тестовой БД на Postgres.
- HTTP Feature-тест на `ReportController@index` (Task 5, `getPersonalProgress`) — заблокирован тем же п.9 90-open-questions. Для Task 5 покрытие закрыто smoke через reflection на реальной Postgres-базе с проверкой item-fairness формулы (8/9 = 88.9%) и работы фильтров role/course_id. Полноценный Feature-тест с фикстурами (1 user × 2 курса × разные totals/done) добавим после разблокировки.
- HTTP Feature-тест на `ReportController@index` (Task 6, `getCityComparison`) и проверка 422 — заблокирован тем же п.9 90-open-questions. Для Task 6 покрытие закрыто smoke через reflection на реальной Postgres-базе (`courseId=3` → 2 города с метриками; `courseId=null` → `HttpException status=422`). Полноценный Feature-тест с фикстурами (2 города × разные участники / submissions / test attempts) добавим после разблокировки.
