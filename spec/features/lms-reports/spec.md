# Фича: LMS — Отчёты

**Статус**: implemented

## Описание

Страница аналитики и отчётов по образовательной деятельности в рамках события. Администратор может просматривать статистику в различных разрезах, скачивать CSV и отправлять отчёт на email в формате CSV.

## Связанные сущности

### Контроллеры
- `App\Http\Controllers\Lms\Admin\ReportController` — index, download, sendEmail

### Страницы
- `Pages/Lms/Admin/Reports/Index.vue`

### Сервисы / конфиг
- `App\Services\Lms\Reports\DeadlineService` — единая точка истины по дедлайну (`resolveDeadline / resolveApprovedAt / classify / delayDays`).
- `config('lms.reports.default_deadline')` — «фолбэк-фолбэк» дедлайн (env `LMS_REPORTS_DEFAULT_DEADLINE`, по умолчанию `2026-06-20T23:59:59Z`).
- `lms_events.default_assignment_deadline` (timestamp, nullable) — фолбэк-дедлайн события (редактируется в админке `/lms-admin/events/{event}/edit`).

## Роуты

| Метод | URI | Действие |
|---|---|---|
| GET | `/lms-admin/{event}/reports` | ReportController@index |
| GET | `/lms-admin/{event}/reports/download` | ReportController@download |
| POST | `/lms-admin/{event}/reports/send` | ReportController@sendEmail |

## Разрезы статистики

### Сводка (summary cards)
- Количество участников
- Количество курсов
- Количество тестов
- Количество заданий
- Среднее завершение курсов (%)
- Средняя сдача тестов (%)

### Вкладка «Курсы»
| Метрика | Описание |
|---|---|
| Записано | Уникальных пользователей с enrollment |
| В процессе | Со статусом `in_progress` |
| Завершено | Со статусом `completed` |
| % завершения | completed / enrolled × 100 |
| Прогресс-бар | Визуальная полоса % завершения |

### Вкладка «Тесты»
| Метрика | Описание |
|---|---|
| Попыток | Общее количество `lms_test_attempts` |
| Участников | Уникальных user_id |
| Сдало | Уникальных с `passed = true` |
| % сдачи | passed / attempted × 100 |
| Ср. балл | AVG(percentage) по всем попыткам |

### Вкладка «Задания»
| Метрика | Описание |
|---|---|
| Сдано работ | Уникальных submissions |
| На проверке | Со статусом `submitted` / `resubmitted` |
| Принято | Со статусом `approved` |
| Отклонено | Со статусом `rejected` |
| % принятия | approved / submitted × 100 |

### Вкладка «Участники»
Таблица с детализацией по каждому пользователю:
- ФИО, email, роль
- Курсов записан / завершено
- Тестов сдано, средний балл
- Заданий принято
- Баллы геймификации
- Поиск по ФИО/email

## Расширения отчёта (deadline-progress)

Раздел добавлен фичей `lms-reports-deadline-progress`. Все private-методы — в `ReportController` рядом с существующими `getCourseStats / getTestStats / getAssignmentStats`.

### Фильтры (URL query → `filters` Inertia-пропс)
| Параметр | Тип | Описание |
|---|---|---|
| `role` | string | Фильтр по `lms_profiles.role` |
| `course_id` | int | Фильтр по `lms_courses.id` (программа). Обязателен для секции «Города» — иначе 422 |
| `city_id` | int | Фильтр по `lms_profiles.city_id` |
| `faculty` | string | Фильтр по `lms_course_enrollments.faculty` |
| `date_from` / `date_to` | date `YYYY-MM-DD` | Период для динамики |
| `granularity` | `day` (default) или `week` | Шаг агрегации динамики (`week` — Postgres `DATE_TRUNC('week', …)::date`, понедельник) |

### Вкладка «Сроки» (Inertia-пропс `deadlineCompliance`)
Источник: `ReportController::getDeadlineCompliance(eventId, role?, course?, city?, faculty?)`. Группировка по `(assignment_id, course_id, city_id, faculty)`. Дедлайн: `COALESCE(a.deadline, e.default_assignment_deadline, config('lms.reports.default_deadline')::timestamp)`. Метрики: `total_users / on_time / late / overdue / avg_delay_days`. Postgres-only (`EXTRACT(EPOCH FROM …)`, `CEIL(... / 86400.0)`). «Не сдавал совсем» считается как `overdue`. Каждая строка содержит флаг `is_orphan` (см. ниже «Orphan-задания»).

### Вкладка «Прогресс участников» (Inertia-пропс `personalProgress`)
Источник: `ReportController::getPersonalProgress(eventId, role?, course?, city?, faculty?)`. План = `lms_course_stages + lms_stage_blocks` enrolled-курсов пользователя в рамках события. Orphan-задания НЕ входят в `assignments_total/done/overall_pct`. Поля: `assignments_total/done/pct, tests_total/done/pct, stages_total/done/pct, overall_pct` (item-fairness: `(sum done) / (sum total) * 100`) + `assignments_orphan_total/assignments_orphan_done` (см. ниже).

### Orphan-задания (вне плана программ)
Orphan = `lms_assignments` события без записи в `lms_stage_blocks` (не привязано к этапу ни одной программы). Сводное правило — orphan **видны в отчёте, но не влияют на план**:
- `getDeadlineCompliance` — orphan-задание выводится отдельной строкой через UNION с `is_orphan=true`, `course_id/course_title/city_id/city_name/faculty=null`. База участников = `lms_profiles` события (фильтры `role`/`city_id` применяются). Orphan-строки **скрываются** при наличии фильтра `course_id` или `faculty` (т.к. orphan по определению вне программы и факультета).
- `getPersonalProgress` — каждая строка получает `assignments_orphan_total` (константа на event = `COUNT(orphan_ids)`) и `assignments_orphan_done` (per user = `COUNT(DISTINCT lms_assignment_id)` где `status='approved'` среди orphan_ids). Поля **не входят** в `assignments_done/total` и **не влияют** на `assignments_pct/overall_pct`.
- `getCityComparison` — реюз `getDeadlineCompliance($eventId, …, $courseId, …)`, поэтому orphan-задания автоматически исключаются (фильтр `course_id` всегда задан). Orphan-поля `personalProgress` сравнением городов не используются.
- UI: бейдж `RBadge variant=warning` «Вне плана» в колонке «Задание» вкладки «Сроки» + amber-подложка строки; компактная колонка «Доп.» (`done/total`) во вкладке «Прогресс участников» (скрыто `—`, если в событии нет orphan-задания).
- CSV (`writeCsvDeadline`): добавлена колонка `Вне плана` (да/нет). CSV (`writeCsvPersonal`): добавлены колонки `Доп. (вне плана)` и `Доп. принято`.

### Вкладка «Города» (Inertia-пропс `cityComparison`)
Источник: `ReportController::getCityComparison(eventId, courseId)`. Без `course_id` метод даёт `abort(422, 'course_id is required for city comparison.')`; UI прячет вкладку до выбора программы. Группировка по `lms_profiles.city_id` (включая null = «Не указан»). Метрики: `participants_count, on_time_pct, overdue_pct, avg_overall_pct, avg_test_score` (best attempt per user×test для тестов программы из stage_blocks).

### Вкладка «Динамика» (Inertia-пропс `activityTimeline`)
Источник: `ReportController::getActivityTimeline(eventId, dateFrom?, dateTo?, granularity = 'day')`. В ответ добавлено поле `granularity`. Старые серии `enrollments / completions / test_attempts` сохранены, добавлены `assignments_approved` (`lms_assignment_reviews.created_at WHERE decision='approve'`), `tests_passed` (`lms_test_attempts.finished_at WHERE passed=true`), `stages_completed` (`lms_stage_progress.completed_at WHERE status='completed'`). Bucket: `day → DATE(col)`, `week → DATE_TRUNC('week', col)::date` (понедельник). Дефолт периода: 30 дней (day) / 90 дней (week).

### Списки фильтров (Inertia-пропсы)
- `availableCities` — `[{ id, name }]` distinct city_id из `lms_profiles` события (только не-null).
- `availableFaculties` — `string[]` distinct `lms_course_enrollments.faculty` внутри события (без null/пустых).

### Экспорт CSV (download / sendEmail)
Секции `section` (download) / `sections[]` (sendEmail): `users / courses / tests / stages / deadline / personal / cities / dynamics`.
- `cities` без `course_id` → `abort(422)`.
- `dynamics` подхватывает `date_from / date_to / granularity` из query (download) или payload (sendEmail).
- writer-методы: `writeCsvDeadline / writeCsvPersonal / writeCsvCities(courseId) / writeCsvDynamics(dateFrom, dateTo, granularity)`. Хелпер `csvCell(?string)` нормализует `null → '—'` и экранирует `"`.

### UI
Все компоненты — `@rosatom-ggr/ui-kit` (`RCard, RBadge, RButton, RModal, RCheckbox, RInput`). RTabs не используется — сохранён существующий стиль кастомных tab-кнопок поверх `bg-gray-100 p-1`. Прогресс-бары — кастомные `<div>` с `progressBarColor(pct)` (зелёный ≥80%, янтарный ≥50%, красный >0).

## Отправка на email

- Модальное окно: email + чекбоксы разделов (Участники, Курсы, Тесты, Этапы, Сроки, Прогресс, Города, Динамика).
- Формат: CSV с разделителем `;`, кодировка UTF-8 с BOM (корректно открывается в Excel).
- Вложение: `report_{slug}_{date}.csv`.
- Отправка через Laravel `Mail::raw()` с `attachData()`.
- Кнопка «Отправить» дизейблится, если выбран раздел «Города» без активного фильтра программы (UI-страховка от 422).

## Агрегация данных

Все данные собираются на стороне бэкенда через SQL-запросы с JOIN и подзапросами:
- `lms_profiles` → участники события
- `lms_course_enrollments` → записи на курсы
- `lms_test_attempts` → попытки тестов
- `lms_assignment_submissions` → сдачи заданий
- `lms_gamification_points` → баллы

## Навигация

Пункт «Отчёты» добавлен в sidebar админки LMS (иконка столбчатой диаграммы). Доступен при выбранном событии.
