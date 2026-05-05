# План: LMS — Отчёты: соблюдение сроков и прогресс участников

## Milestones

- **M1 — Управляемый фолбэк-дедлайн (БД + админка события).**
  - Миграция: `lms_events.default_assignment_deadline` (timestamp, nullable).
  - Модель `App\Models\Lms\LmsEvent` — `fillable` + cast `datetime` + `serializeDate` (UTC `…Z`) аналогично `LmsAssignment` (см. `app/Models/Lms/LmsAssignment.php:14-17`).
  - `EventController@store/update` (`app/Http/Controllers/Lms/Admin/EventController.php:30-76`) — валидация `nullable|date`.
  - UI `Pages/Lms/Admin/Events/Edit.vue` (и `Create.vue`) — поле `<input type="datetime-local">` с `lmsDeadlineToDatetimeLocalUtc` / `datetimeLocalToUtcIso` (хелперы из `resources/js/utils/lmsAssignmentDeadline.js`).
  - `config/lms.php` ключ `reports.default_deadline = '2026-06-20T23:59:59Z'` как «фолбэк-фолбэк».
  - Сервис `App\Services\Lms\Reports\DeadlineService`: `resolveDeadline(LmsAssignment $a, LmsEvent $e): Carbon` → `$a->deadline ?? $e->default_assignment_deadline ?? config('lms.reports.default_deadline')`.
- **M2 — Соблюдение сроков ДЗ.** Метод `ReportController::getDeadlineCompliance($eventId, $filters)` с разбивкой по `city_id × faculty × course_id × assignment_id`. Вкладка «Сроки» в UI.
- **M3 — Индивидуальный % прогресса.** Метод `ReportController::getPersonalProgress($eventId, $filters)`: % по типам (задания/тесты/этапы) и общий %, с источником плана из `lms_stage_blocks` enrolled-курсов. Вкладка «Прогресс участников».
- **M4 — Сравнение город × город.** Метод `ReportController::getCityComparison($eventId, $courseId)` (обязателен фильтр программы). Вкладка «Города».
- **M5 — Недельная динамика.** Расширить `getActivityTimeline` параметром `granularity=week` (`DATE_TRUNC('week', …)`). Вкладка «Динамика».
- **M6 — Экспорт.** Расширить `download()` и `sendEmail()` секциями `deadline`, `personal`, `cities`, `dynamics`.
- **M7 — UI.** Новые вкладки в `resources/js/Pages/Lms/Admin/Reports/Index.vue`. Новые фильтры: «Город», «Факультет», «Программа» (уже есть), «Период (недели)».

## UI Components

Реюз из `@rosatom-ggr/ui-kit` (зарегистрированы глобально в `resources/js/app.js:39-51`):

- **RCard** — обёртки таблиц и карточек метрик (паттерн `Pages/Lms/Admin/Reports/Index.vue:52-58, 78-133`).
- **RTabs** — добавление новых вкладок «Сроки», «Прогресс», «Города», «Динамика» поверх существующих кастомных tab-кнопок (`Index.vue:61-74`); либо оставить текущий стиль и просто добавить элементы — решить в M7.
- **RProgress** — прогресс-бары «% выполнения по участнику», «% сдачи в срок».
- **RBadge** — статусы «в срок» (зелёный) / «с задержкой» (жёлтый) / «просрочено» (красный).
- **RButton** — экспортные кнопки (паттерн `Index.vue:13-18`).
- Селекты фильтров — нативные `<select>` (паттерн `Index.vue:23-48`); UI Kit-аналога нет, новый компонент не вводим.

Новых компонентов не создаём — всё покрыто UI Kit.

## Verification

Команды запуска — по «Command Execution Pattern» из `.cursor/rules/spec-continuation.mdc` (Docker-only, через `docker exec ${APP_NAME}_fpm` после `source docker/.env.local`). Команды: artisan миграции/тесты, Pest, npm/Vite билд для UI. Никаких host-команд.
