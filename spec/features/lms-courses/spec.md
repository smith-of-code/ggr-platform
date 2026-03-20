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
- Ролевой доступ к курсам через lms_course_role_access

## SCORM-интеграция

### Общие сведения

SCORM доступен **без отдельного переключателя** в настройках — это один из пяти типов этапа курса (`content`, `scorm`, `test`, `assignment`, `video`). Никакой предварительной активации не требуется.

### Загрузка SCORM-пакета (админ)

**Путь в интерфейсе**: Админ-панель LMS → Курсы → Создать/Редактировать курс → Добавить этап → Тип: "SCORM"

| Шаг | Описание |
|-----|----------|
| 1 | Админ выбирает тип этапа `SCORM` в `StageEditor.vue` |
| 2 | Появляется дропзона для загрузки ZIP-файла (до 100 МБ) |
| 3 | Файл отправляется `POST /lms-admin/{event}/scorm-upload` → `AdminCourseController::uploadScorm()` |
| 4 | Бэкенд проверяет наличие `imsmanifest.xml` в архиве (иначе 422) |
| 5 | ZIP распаковывается в `public/scorm/{event-slug}/{hash}/` |
| 6 | Из `imsmanifest.xml` извлекается launch-файл (href ресурса с `scormtype="sco"`) |
| 7 | URL launch-файла сохраняется в `LmsCourseStage.scorm_package` |

**Связанный код**:
- `resources/js/Pages/Lms/Admin/Courses/StageEditor.vue` — UI загрузки
- `app/Http/Controllers/Lms/Admin/CourseController.php` — `uploadScorm()`, `findScormLaunchFile()`
- Роут: `routes/lms.php:117` → `lms.admin.scorm.upload`

### Прохождение SCORM (участник)

На странице этапа (`Stage.vue`) SCORM-контент отображается в `<iframe>`. На `window` регистрируются два API-адаптера:

| API | Стандарт | Ключевые методы |
|-----|----------|----------------|
| `window.API` | SCORM 1.2 | `LMSInitialize`, `LMSFinish`, `LMSGetValue`, `LMSSetValue`, `LMSCommit` |
| `window.API_1484_11` | SCORM 2004 | `Initialize`, `Terminate`, `GetValue`, `SetValue`, `Commit` |

Данные сохраняются через `POST /lms/{event}/courses/{course}/stages/{stage}/scorm` → `StageController::scormData()` в поле `LmsStageProgress.scorm_data` (JSON).

При повторном открытии этапа сохранённые `scorm_data` восстанавливаются из `progress.scorm_data`.

**Связанный код**:
- `resources/js/Pages/Lms/Courses/Stage.vue` — iframe + API-адаптеры
- `app/Http/Controllers/Lms/StageController.php` — `scormData()`
- `app/Models/Lms/LmsStageProgress.php` — поле `scorm_data`
