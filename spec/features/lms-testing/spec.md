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
  - `results()` — админ: детальные результаты конкретного теста (попытки, фильтры, ответы)

### Страницы
- `Pages/Lms/Tests/Index.vue`, `Show.vue`, `Take.vue`, `Result.vue`
- `Pages/Lms/Admin/Tests/Index.vue`, `Form.vue`
- `Pages/Lms/Admin/Tests/Results.vue` — детальная таблица попыток по тесту
- `Components/Lms/InlineTest.vue` — встроенный тест на этапе курса

### Поле `gamification_points` (таблица `lms_tests`)

- Целое неотрицательное значение: сколько баллов геймификации начислить участнику при **успешной сдаче** теста (см. `lms-gamification`).
- Значение `0` означает: начисление только по auto-правилам с действием `test_pass`, без фиксированной суммы с теста.
- Не смешивать с `lms_test_questions.points` / score попытки — это оценка ответов, а не рейтинг.

## Ключевые workflow

- Тест может быть в меню (in_menu) или привязан к этапу курса (через lms_course_stages.lms_test_id)
- Старт попытки → ответы на вопросы → отправка → автоматическая проверка → результат
- Ограничение max_attempts, time_limit_minutes
- Перемешивание: shuffle_questions, shuffle_answers
- Показ правильных ответов: show_correct_answers
- Админ может открыть детальную страницу результатов теста (`GET /lms-admin/{event}/tests/{test}/results`) с фильтрами:
  - поиск по участнику (ФИО/email)
  - статус сдачи (all/passed/failed)
  - опциональный показ ответов по каждой попытке
- Роли с ограниченным backoffice-доступом (`куратор-эксперт`, `тренер команды`, `трекер`, `эксперт`) имеют read-only доступ к `lms.admin.tests.index` и `lms.admin.tests.results` для просмотра попыток и ответов, без прав на CRUD тестов.
