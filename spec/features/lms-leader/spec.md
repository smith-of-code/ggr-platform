# Фича: LMS — Панель лидера/куратора

**Статус**: auto-detected, needs review

## Описание

Интерфейс для лидеров и кураторов групп: мониторинг прогресса участников, просмотр групп, отправка отчётов.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsGroup`
- `App\Models\Lms\LmsProfile`
- `App\Models\Lms\LmsCourseEnrollment`
- `App\Models\Lms\LmsStageProgress`
- `App\Models\Lms\LmsTrajectoryEnrollment`

### Контроллеры
- `App\Http\Controllers\Lms\LeaderController` — dashboard, groups, groupDetail, userProgress, sendReport

### Страницы
- `Pages/Lms/Leader/Dashboard.vue`
- `Pages/Lms/Leader/Groups.vue`
- `Pages/Lms/Leader/GroupDetail.vue`
- `Pages/Lms/Leader/UserProgress.vue`

## Ключевые workflow

- Лидер видит список своих групп и участников
- Детальный прогресс участника: курсы, этапы, траектории
- Отправка сводного отчёта (sendReport)
