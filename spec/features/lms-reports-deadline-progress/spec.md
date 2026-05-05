# Фича: LMS — Отчёты: соблюдение сроков и прогресс участников

**Статус**: planned

## Goal

Расширить отчёт LMS (`/lms-admin/{event}/reports`) тремя блоками: соблюдение сроков ДЗ, индивидуальный % прогресса по персональному плану программы, сравнительный анализ «город × город» внутри одной программы и недельная динамика прогресса.

## In-scope

- Расчёт «% задач в срок / с задержкой / просрочено» по заданиям с разбивкой по городу × факультету × программе и средней длительностью задержки в днях.
- **Фолбэк-дедлайн редактируется в админке события** (`/lms-admin/events/{event}/edit`): новое поле `lms_events.default_assignment_deadline` (timestamp, nullable). Используется для заданий без `deadline` и для тестов (у `lms_tests` нет своего поля `deadline`). Текущее значение по продукту — `2026-06-20 23:59:59Z`, но любой админ может его поменять.
- Расчёт индивидуального % прогресса участника по персональному плану программы (принятые задания + сданные тесты + completed-этапы курса).
- Сравнительный отчёт «город × город» внутри одной программы (фильтр по `lms_course_id`).
- Недельная динамика прогресса: `lms_stage_progress.completed_at`, `lms_assignment_reviews.created_at` (decision=`approve`), `lms_test_attempts.finished_at` (passed=true).
- Экспорт новых блоков в CSV (`reports.download`) и в email-вложение (`reports.send`).

## Out-of-scope

- Добавление колонки `deadline` в `lms_tests` (используется фолбэк из настроек события).
- Per-program и per-assignment-type настройки фолбэка: в этой фиче — только одна общая дата на событие.
- Чистка orphan-заданий (не привязанных к `lms_stage_blocks` ни одной программы): в этой фиче они трактуются как «общие» и **не учитываются** в плане программ, отображаются отдельной строкой.
- Сравнительные отчёты между ролями/группами/событиями (вне согласованного scope «город × программа»).
- Дневная гранулярность динамики (только неделя).
- Графический рендер диаграмм (только таблицы и прогресс-бары на UI Kit).

## Constraints

- Postgres-only SQL: `DATE_TRUNC('week', …)` для недельной агрегации.
- Источник правды по фолбэк-дедлайну: `LmsEvent::default_assignment_deadline`. Если поле события не заполнено — используется hard-default из `config('lms.reports.default_deadline')` (значение `2026-06-20T23:59:59Z`) как «фолбэк-фолбэк».
- Дата на UI редактируется через `<input type="datetime-local">` по тем же UTC-конвенциям, что и `LmsAssignment.deadline` (см. `spec/features/lms-assignments/spec.md` пп. «Дедлайн (API и отображение)»): хелперы `lmsDeadlineToDatetimeLocalUtc` / `datetimeLocalToUtcIso`.
- Не дублировать SQL — выделять private-методы в `ReportController` по аналогии с `getCourseStats`/`getTestStats` (см. `app/Http/Controllers/Lms/Admin/ReportController.php:211-290`).
- UI исключительно через UI Kit `@rosatom-ggr/ui-kit` (RCard, RTabs, RProgress, RBadge, RButton — зарегистрированы глобально в `resources/js/app.js:39-51`).
- Согласованные в чате правила:
  - «не сдавал совсем» → считается «просрочено»;
  - «без дедлайна у задания» → дедлайн = `LmsEvent::default_assignment_deadline` → fallback `config('lms.reports.default_deadline')`;
  - «момент принятия» → `lms_assignment_reviews.created_at` где `decision='approve'`;
  - тесты считаем «сдано хотя бы раз» (`COUNT DISTINCT user_id` где `passed=true`);
  - сравнение городов — только внутри одной программы.

## Open questions

Нет. Все методологические вопросы согласованы с заказчиком (см. историю чата по аналитике 05.05.2026).
