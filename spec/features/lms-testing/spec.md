# Фича: LMS — Система тестирования

**Статус**: auto-detected, needs review

## Описание

Система тестирования с поддержкой различных типов вопросов (single, multiple, text), ограничением по времени, перемешиванием вопросов/ответов, проходным баллом и множественными попытками.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsTest`
- `App\Models\Lms\LmsTestQuestion`
- `App\Models\Lms\LmsTestAnswer`
- `App\Models\Lms\LmsTestAttempt`
- `App\Models\Lms\LmsTestResponse`

### Контроллеры
- `App\Http\Controllers\Lms\TestController` — участник: список, старт, прохождение, отправка, результат
- `App\Http\Controllers\Lms\Admin\TestController` — админ: CRUD тестов с вопросами/ответами

### Страницы
- `Pages/Lms/Tests/Index.vue`, `Show.vue`, `Take.vue`, `Result.vue`
- `Pages/Lms/Admin/Tests/Index.vue`, `Form.vue`

## Ключевые workflow

- Тест может быть в меню (in_menu) или привязан к этапу курса (через lms_course_stages.lms_test_id)
- Старт попытки → ответы на вопросы → отправка → автоматическая проверка → результат
- Ограничение max_attempts, time_limit_minutes
- Перемешивание: shuffle_questions, shuffle_answers
- Показ правильных ответов: show_correct_answers
