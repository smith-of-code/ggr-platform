# Фича: LMS — Геймификация

**Статус**: auto-detected, needs review

## Описание

Система начисления очков за действия в LMS с таблицей лидеров и ручным начислением.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsGamificationRule`
- `App\Models\Lms\LmsGamificationPoint`

### Сервисы
- `App\Services\GamificationService` — awardPoints, getLeaderboard, getUserPoints, getUserRank

### Контроллеры
- `App\Http\Controllers\Lms\GamificationController` — участник: leaderboard, myPoints
- `App\Http\Controllers\Lms\Admin\GamificationController` — админ: CRUD правил, ручное начисление

### Страницы
- `Pages/Lms/Gamification/Leaderboard.vue`, `MyPoints.vue`
- `Pages/Lms/Admin/Gamification/Index.vue`, `Form.vue`

## Ключевые workflow

- Правила: action + points + max_times + is_auto
- Автоматическое начисление (is_auto) через GamificationService.awardPoints при действиях: course_complete, stage_complete, test_pass и др.
- Ручное начисление админом через manualPoints
- Таблица лидеров с рангами
- Ограничение max_times предотвращает многократное начисление за одно действие
