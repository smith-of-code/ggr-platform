# Задачи: LMS — Отчёты: соблюдение сроков и прогресс участников

## Task 1 — Поле `default_assignment_deadline` на событии (БД + модель + контроллер)

- **Goal**: дать админу управлять фолбэк-дедлайном события.
- **Scope**: миграция `database/migrations/...add_default_assignment_deadline_to_lms_events.php`; `app/Models/Lms/LmsEvent.php` (`fillable` + cast `datetime` + `serializeDate` UTC `…Z`); `app/Http/Controllers/Lms/Admin/EventController.php` (валидация `default_assignment_deadline => nullable|date` в store/update).
- **DoD**: миграция накатывается и откатывается; модель сериализует поле в `…Z`; админ может сохранять/очищать значение через `update`.
- **Verify**: миграции (`migrate` / `migrate:rollback`) и Pest Feature-тест на `EventController` по pattern из `spec-continuation.mdc`.

## Task 2 — UI редактирования фолбэк-дедлайна в админке события

- **Goal**: поле даты в форме события на тех же UTC-конвенциях, что у `LmsAssignment.deadline`.
- **Scope**: `resources/js/Pages/Lms/Admin/Events/Edit.vue` и `Create.vue` — `<input type="datetime-local">` с `lmsDeadlineToDatetimeLocalUtc` / `datetimeLocalToUtcIso` из `resources/js/utils/lmsAssignmentDeadline.js`; подпись «Дедлайн по умолчанию (для заданий без своего дедлайна и для тестов)».
- **DoD**: значение из БД корректно показывается в локальном инпуте без сдвига часового пояса; пустое значение допустимо; при сохранении уходит ISO-UTC `…Z`.
- **Verify**: `npm run build` в Docker по pattern; ручная проверка формы.

## Task 3 — Сервис `DeadlineService`

- **Goal**: единая точка истины для дедлайна и расчёта approved_at.
- **Scope**: `config/lms.php` (новый файл, ключ `reports.default_deadline = '2026-06-20T23:59:59Z'`); `app/Services/Lms/Reports/DeadlineService.php` (новый).
- **DoD**: сервис умеет (a) `resolveDeadline(LmsAssignment, LmsEvent): Carbon` с цепочкой `assignment.deadline → event.default_assignment_deadline → config(...)`; (b) определять approved_at для submission по последней `LmsAssignmentReview` где `decision='approve'`; (c) возвращать `enum`/string «on_time / late / overdue» и длительность задержки в днях.
- **Verify**: Pest unit-тесты по pattern из `spec-continuation.mdc`.

## Task 4 — Метод `getDeadlineCompliance` в ReportController

- **Goal**: SQL-агрегация «% в срок / с задержкой / просрочено» + средняя задержка по заданиям.
- **Scope**: `app/Http/Controllers/Lms/Admin/ReportController.php` (новый private-метод по аналогии с `getAssignmentStats`); включает разбивку по `lms_profiles.city_id`, `lms_course_enrollments.faculty`, `lms_course_enrollments.lms_course_id`, `lms_assignment_id`. Использует `DeadlineService::resolveDeadline`.
- **DoD**: метод возвращает массив со столбцами `assignment_id`, `course_id`, `city_id`, `faculty`, `on_time`, `late`, `overdue`, `avg_delay_days`. Учитывает фолбэк-дедлайн события.
- **Verify**: Pest Feature-тест на репорт-контроллер по pattern.

## Task 5 — Метод `getPersonalProgress` в ReportController

- **Goal**: индивидуальный % прогресса по персональному плану программы.
- **Scope**: `ReportController` (новый private-метод); источник плана = объединение `lms_course_stages` + `lms_stage_blocks` курсов из `lms_course_enrollments` пользователя.
- **DoD**: возвращает на каждого user_id поля `assignments_total/done/pct`, `tests_total/done/pct`, `stages_total/done/pct`, `overall_pct`. Orphan-задания НЕ включаются в total.
- **Verify**: Pest Feature-тест.

## Task 6 — Метод `getCityComparison` в ReportController

- **Goal**: сравнение городов внутри одной программы по ключевым метрикам.
- **Scope**: `ReportController` (новый private-метод); параметр `course_id` обязателен.
- **DoD**: на каждый `city_id` возвращает `participants_count`, `on_time_pct`, `overdue_pct`, `avg_overall_pct`, `avg_test_score`. Если `course_id` не передан — выбрасывает 422.
- **Verify**: Pest Feature-тест.

## Task 7 — Недельная динамика

- **Goal**: расширить `getActivityTimeline` режимом `granularity=week`.
- **Scope**: `ReportController::getActivityTimeline` (модификация); добавить серии `assignments_approved`, `tests_passed`, `stages_completed` (помимо текущих enrollments/completions/test_attempts) с агрегацией `DATE_TRUNC('week', …)`.
- **DoD**: при `request('granularity') === 'week'` возвращается понедельная сетка; контракт совместим с дневным режимом (поле `granularity` в ответе).
- **Verify**: Pest Feature-тест на 2 режима (day/week).

## Task 8 — Экспорт CSV и Email новых секций

- **Goal**: добавить новые секции в скачивание и email-отправку.
- **Scope**: `ReportController::download` и `ReportController::sendEmail` — секции `deadline`, `personal`, `cities`, `dynamics`; вспомогательные `writeCsvDeadline`, `writeCsvPersonal`, `writeCsvCities`, `writeCsvDynamics`.
- **DoD**: CSV корректно открывается в Excel (UTF-8 BOM, разделитель `;`), email-вложение содержит выбранные секции.
- **Verify**: Pest Feature-тест на каждую секцию.

## Task 9 — UI: новые вкладки/фильтры + документация фичи lms-reports

- **Goal**: вывести 4 новые вкладки в админ-отчёте и зафиксировать финальное поведение в спеке lms-reports.
- **Scope**: `resources/js/Pages/Lms/Admin/Reports/Index.vue` — добавить вкладки «Сроки», «Прогресс участников», «Города», «Динамика»; фильтры «Город», «Факультет», «Период (недели)»; биндинг на новые поля Inertia-пропсов. `spec/features/lms-reports/spec.md` — раздел «Расширения отчёта (deadline-progress)» со ссылками на ключи Inertia-пропсов, роуты и поле события `default_assignment_deadline`.
- **DoD**: вкладки рендерятся, фильтры синхронизируются с URL, экспорт по новым секциям работает; компоненты только из `@rosatom-ggr/ui-kit` (RCard/RTabs/RProgress/RBadge/RButton); спека lms-reports описывает финальное состояние.
- **Verify**: `npm run build` в Docker по pattern; визуальная проверка через Playwright MCP опционально.

## Task 10 — Регресс: orphan-задания

- **Goal**: явно показать orphan-задания в отчёте отдельной строкой и пометить их «вне плана программ».
- **Scope**: `ReportController` (расчёт `getDeadlineCompliance` и `getPersonalProgress`) + UI таблица в Index.vue.
- **DoD**: orphan-задания (без записи в `lms_stage_blocks`) выводятся в отдельный блок «Общие/факультативные» и не влияют на `overall_pct` участника.
- **Verify**: Pest Feature-тест с фикстурой orphan-задания.
