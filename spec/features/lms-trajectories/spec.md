# Фича: LMS — Траектории обучения

**Статус**: auto-detected, needs review

## Описание

Траектории обучения — упорядоченные последовательности курсов с контролем блокировки и расписанием открытия.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsTrajectory`
- `App\Models\Lms\LmsTrajectoryStep`
- `App\Models\Lms\LmsTrajectoryEnrollment`

### Контроллеры
- `App\Http\Controllers\Lms\TrajectoryController` — участник: список, просмотр, запись
- `App\Http\Controllers\Lms\Admin\TrajectoryController` — админ: CRUD

### Страницы
- `Pages/Lms/Trajectories/Index.vue`, `Show.vue`
- `Pages/Lms/Admin/Trajectories/Index.vue`, `Form.vue`

## Ключевые workflow

- Траектория содержит шаги (steps), каждый ссылается на курс
- Шаги могут быть заблокированы (is_locked) и открываться по расписанию (opens_at)
- Позиционирование шагов (position) определяет порядок
- Запись на траекторию: enrolled → in_progress → completed
