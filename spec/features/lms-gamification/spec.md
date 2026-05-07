# Фича: LMS — Геймификация

**Статус**: implemented

## Описание

Система начисления баллов за действия в LMS с таблицей лидеров (участники + группы), ручным начислением и автоматическими триггерами.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsGamificationRule`
- `App\Models\Lms\LmsGamificationPoint`

### Сервисы
- `App\Services\GamificationService` — `awardPoints`, `awardFixedPoints` (идемпотентно по `user_id` + `source_type` + `source_id` в рамках события), getLeaderboard, getAdminLeaderboardRows, getRecentPointsByUserIds, getUserPoints, getUserRank

### Observer
- `App\Observers\LmsProgressObserver` — автоматические триггеры через Eloquent events

### Контроллеры
- `App\Http\Controllers\Lms\GamificationController` — участник: leaderboard, myPoints
- `App\Http\Controllers\Lms\Admin\GamificationController` — админ: CRUD правил, ручное начисление

### Страницы
- `Pages/Lms/Gamification/Leaderboard.vue` — рейтинг участников и групп (подиум, таблица, вкладки)
- `Pages/Lms/Gamification/MyPoints.vue` — история баллов пользователя
- `Pages/Lms/Admin/Gamification/Index.vue` — список правил (первый блок), ссылка «Создать правило», рейтинг участников (топ 100, без admin в событии), расшифровка по пользователю (раскрывающаяся подстрока таблицы), модалка ручного начисления
  и отдельный блок «Подробная история начислений» (глобальная лента начислений по событию с фильтрами и пагинацией)
- `Pages/Lms/Admin/Gamification/Form.vue` — создание/редактирование правила
- `Pages/Lms/Profile/Edit.vue` — для ролей с ограниченным backoffice-доступом показывается блок «Геймификация» с переходом к начислению баллов

## Схема БД

### lms_gamification_rules
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_event_id | FK → lms_events | cascade delete |
| title | string | Название правила |
| action | string | Код действия (триггер) |
| points | integer | Количество баллов |
| max_times | integer | nullable, лимит начислений (null = ∞) |
| is_auto | boolean | Автоматическое начисление |
| is_active | boolean | Активность правила |
| timestamps | | |

### lms_gamification_points
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_event_id | FK → lms_events | cascade delete |
| user_id | FK → users | cascade delete |
| lms_gamification_rule_id | FK → rules | nullable, null если не по правилу |
| source_type | string(64) | nullable; тип источника фиксированного начисления (константы `GamificationService::SOURCE_*`) |
| source_id | bigint | nullable; id сущности-источника (например id теста или задания) |
| points | integer | Баллы |
| reason | string | Причина начисления |
| timestamps | | |

Индекс: `(lms_event_id, user_id, source_type, source_id)` для защиты от повторного начисления за тот же тест/задание.

**Семантика `lms_gamification_rule_id` / source:**

- Ручное начисление из модалки админки: `lms_gamification_rule_id = null`, `source_type` и `source_id` = null.
- Авто по правилу: `lms_gamification_rule_id` заполнен.
- Фиксированные баллы за тест/задание: `lms_gamification_rule_id = null`, `source_type` и `source_id` заполнены (`awardFixedPoints`).

## Автоматические действия (триггеры)

| Код действия | Описание | Триггер |
|---|---|---|
| `module_complete` | Завершение модуля (все этапы модуля `lms_course_modules` со статусом completed; этап без модуля считается модулем из одного этапа) | `LmsStageProgress::created` / `updated` → `LmsProgressObserver::maybeAwardModuleComplete` |
| `course_complete` | Завершение курса | `LmsCourseEnrollment::updated` → Observer |
| `test_pass` | Успешное прохождение теста | `LmsTestAttempt::created` → Observer; если у теста `gamification_points > 0`, вызывается `awardFixedPoints` с `SOURCE_TEST_PASSED` и `source_id = lms_tests.id`, иначе — только `awardPoints` по правилам |
| `assignment_approved` | Одобрение задания | `LmsAssignmentReview::created` (решение approve) **или** отправка участником при `completion_mode = on_submit` (статус сразу `approved`) → `LmsProgressObserver::awardAssignmentGamificationPoints`; если у задания `gamification_points > 0`, вызывается `awardFixedPoints` с `SOURCE_ASSIGNMENT_APPROVED` и `source_id = lms_assignments.id`, иначе — только `awardPoints` по правилам |
| `trajectory_complete` | Завершение траектории | `LmsTrajectoryEnrollment::updated` → Observer |
| `login_daily` | Ежедневный вход | `AuthController::login()` |
| `video_watch` | Просмотр видео | `VideoController::show()` |
| `kb_view` | Просмотр базы знаний | `KnowledgeBaseController::show()` |
| `material_view` | Просмотр материала | `MaterialController::show()` |

Регистрация observer listeners: `AppServiceProvider::boot()`.

## Механизм начисления

1. Триггер вызывает `awardPoints` и/или `awardFixedPoints` в зависимости от настроек теста/задания (см. таблицу триггеров).
2. Для `awardPoints`: сервис ищет активные auto-правила по `action` и `lms_event_id`, проверяет `max_times`, создаёт строки с `lms_gamification_rule_id`.
3. Для `awardFixedPoints`: если запись с тем же `(lms_event_id, user_id, source_type, source_id)` уже есть — выход; иначе создаётся одна строка с указанными `points` и пустым `lms_gamification_rule_id`.

## Ручное начисление (Admin)

- Маршрут: `POST /lms-admin/{event}/gamification/manual-points`
- Модальное окно с поиском по ФИО/email, фильтрами по роли, городу и группе, массовым выбором
- Поля: участники (чекбоксы), баллы (число), причина (текст)
- Создаёт `LmsGamificationPoint` с `lms_gamification_rule_id = null`
- Удаление начисления: `DELETE /lms-admin/{event}/gamification/points/{point}` из блока расшифровки баллов в админке
- Право на удаление начислений: все не-участники (admin и прочие роли backoffice, кроме `participant`)

## Подробная история начислений (Admin)

- На странице `lms.admin.gamification.index` отображается таблица всех `lms_gamification_points` по событию.
- Фильтры: поиск (`history_search` — по имени/email участника, причине и названию правила), тип начисления (`history_type`: `manual`/`auto`), группа (`history_group`), диапазон дат (`history_date_from`, `history_date_to`).
- `history_type=manual`: только записи без правила **и** без пары `source_type`/`source_id` (чисто ручные из модалки).
- `history_type=auto`: есть `lms_gamification_rule_id` **или** заполнены оба поля `source_type` и `source_id` (в т.ч. фиксированные баллы за тест/задание).
- Сортировка: по `created_at DESC, id DESC`.
- Пагинация: 30 записей на страницу.

## Рейтинг (Leaderboard)

### Участники
- Верхние карточки: место пользователя, его баллы, лидер
- Подиум для топ-3 (золото/серебро/бронза)
- Полный список с прогресс-барами
- Подсветка текущего пользователя

### Группы
- Отдельная вкладка «Группы»
- Карточка первого места
- Список с количеством участников и прогресс-барами
- Агрегация баллов участников группы через `lms_group_members`

## Форма правила (Admin)

- Название
- Действие — обязательный выпадающий список из `GamificationService::$defaultActions` (пустое значение не допускается)
- Баллы
- Макс. раз (лимит)
- Чекбоксы: автоматическое, активно
- Валидация обязательных полей (`title`, `action`, `points`) выполняется на клиенте и на сервере, с отображением ошибок у соответствующих полей

## Роуты

### Участник (prefix: `/lms/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET | `/leaderboard` | GamificationController@leaderboard |
| GET | `/my-points` | GamificationController@myPoints |

### Админ (prefix: `/lms-admin/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET/POST/PUT/DELETE | `/gamification` (resource) | Admin\GamificationController CRUD |
| POST | `/gamification/manual-points` | Admin\GamificationController@manualPoints |
| DELETE | `/gamification/points/{point}` | Admin\GamificationController@destroyPoint |
