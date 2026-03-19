# Фича: LMS — Система заданий

**Статус**: auto-detected, needs review

## Описание

Задания с двумя режимами завершения (on_submit / on_review), отправкой работ (текст, ссылки, файлы) и системой рецензирования.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsAssignment`
- `App\Models\Lms\LmsAssignmentSubmission`
- `App\Models\Lms\LmsAssignmentReview`

### Контроллеры
- `App\Http\Controllers\Lms\AssignmentController` — участник: список, просмотр, отправка, обновление
- `App\Http\Controllers\Lms\Admin\AssignmentController` — админ: CRUD, рецензирование

### Страницы
- `Pages/Lms/Assignments/Index.vue`, `Show.vue`
- `Pages/Lms/Admin/Assignments/Index.vue`, `Form.vue`, `Submissions.vue`

## Ключевые workflow

- Участник отправляет работу (text_content, link, files)
- Статусы submission: draft → submitted → revision / approved / rejected
- Рецензент добавляет review с decision: approve / revision / reject
- Режим on_submit: задание считается выполненным при отправке
- Режим on_review: задание считается выполненным после одобрения рецензентом
- Шаблон файла для скачивания (template_file)
- Дедлайн (deadline)
