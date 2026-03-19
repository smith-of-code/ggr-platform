# Фича: LMS — Система курсов

**Статус**: auto-detected, needs review

## Описание

Многоуровневая система курсов с модулями, этапами (stages), записью на курсы (enrollments), поддержкой SCORM, контролем последовательного прохождения и ролевым доступом.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsCourse`
- `App\Models\Lms\LmsCourseModule`
- `App\Models\Lms\LmsCourseStage`
- `App\Models\Lms\LmsCourseEnrollment`
- `App\Models\Lms\LmsStageProgress`

### Контроллеры
- `App\Http\Controllers\Lms\CourseController` — участник: список, просмотр, запись
- `App\Http\Controllers\Lms\StageController` — участник: прохождение этапов, SCORM, heartbeat
- `App\Http\Controllers\Lms\Admin\CourseController` — админ: CRUD курсов, загрузка SCORM
- `App\Http\Controllers\Lms\Admin\EnrollmentController` — админ: управление записями

### Страницы
- `Pages/Lms/Courses/Index.vue`, `Show.vue`, `Stage.vue`
- `Pages/Lms/Admin/Courses/Index.vue`, `Form.vue`, `StageEditor.vue`
- `Pages/Lms/Admin/Enrollments/Index.vue`

## Ключевые workflow

- Запись на курс (enroll) → одобрение (если requires_approval) → прохождение этапов → завершение
- Этапы курса: content, scorm, test, assignment, video
- Модульная структура с доступностью по датам (available_from, available_to, unlock_type)
- SCORM-интеграция: загрузка пакетов, сохранение scorm_data, heartbeat
- Ролевой доступ к курсам через lms_course_role_access
