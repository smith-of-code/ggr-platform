# Фича: LMS — Геймификация

**Статус**: implemented

## Описание

Система начисления баллов за действия в LMS с таблицей лидеров (участники + группы), ручным начислением и автоматическими триггерами.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsGamificationRule`
- `App\Models\Lms\LmsGamificationPoint`

### Сервисы
- `App\Services\GamificationService` — awardPoints, getLeaderboard, getAdminLeaderboardRows, getRecentPointsByUserIds, getUserPoints, getUserRank

### Observer
- `App\Observers\LmsProgressObserver` — автоматические триггеры через Eloquent events

### Контроллеры
- `App\Http\Controllers\Lms\GamificationController` — участник: leaderboard, myPoints
- `App\Http\Controllers\Lms\Admin\GamificationController` — админ: CRUD правил, ручное начисление

### Страницы
- `Pages/Lms/Gamification/Leaderboard.vue` — рейтинг участников и групп (подиум, таблица, вкладки)
- `Pages/Lms/Gamification/MyPoints.vue` — история баллов пользователя
- `Pages/Lms/Admin/Gamification/Index.vue` — список правил (первый блок), ссылка «Создать правило», рейтинг участников (топ 100, без admin в событии), расшифровка по пользователю (раскрывающаяся подстрока таблицы), модалка ручного начисления
- `Pages/Lms/Admin/Gamification/Form.vue` — создание/редактирование правила

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
| lms_gamification_rule_id | FK → rules | nullable, null = ручное начисление |
| points | integer | Баллы |
| reason | string | Причина начисления |
| timestamps | | |

## Автоматические действия (триггеры)

| Код действия | Описание | Триггер |
|---|---|---|
| `module_complete` | Завершение модуля (все этапы модуля `lms_course_modules` со статусом completed; этап без модуля считается модулем из одного этапа) | `LmsStageProgress::created` / `updated` → `LmsProgressObserver::maybeAwardModuleComplete` |
| `course_complete` | Завершение курса | `LmsCourseEnrollment::updated` → Observer |
| `test_pass` | Успешное прохождение теста | `LmsTestAttempt::created` → Observer |
| `assignment_approved` | Одобрение задания | `LmsAssignmentReview::created` → Observer |
| `trajectory_complete` | Завершение траектории | `LmsTrajectoryEnrollment::updated` → Observer |
| `login_daily` | Ежедневный вход | `AuthController::login()` |
| `video_watch` | Просмотр видео | `VideoController::show()` |
| `kb_view` | Просмотр базы знаний | `KnowledgeBaseController::show()` |
| `material_view` | Просмотр материала | `MaterialController::show()` |

Регистрация observer listeners: `AppServiceProvider::boot()`.

## Механизм начисления

1. Триггер вызывает `GamificationService::awardPoints($event, $user, $action, $reason)`
2. Сервис ищет активные auto-правила по `action` и `lms_event_id`
3. Для каждого правила проверяет `max_times` — не превышен ли лимит
4. Создаёт `LmsGamificationPoint` с привязкой к правилу

## Ручное начисление (Admin)

- Маршрут: `POST /lms-admin/{event}/gamification/manual-points`
- Модальное окно с поиском по ФИО/email, фильтром по роли, массовым выбором
- Поля: участники (чекбоксы), баллы (число), причина (текст)
- Создаёт `LmsGamificationPoint` с `lms_gamification_rule_id = null`

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
- Действие — выпадающий список из `GamificationService::$defaultActions`
- Баллы
- Макс. раз (лимит)
- Чекбоксы: автоматическое, активно

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
