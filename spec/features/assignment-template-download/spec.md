# Фича: Скачивание шаблона задания с оригинальным именем файла

**Статус**: draft

## Цель

Участник скачивает файл-шаблон задания (assignment и assignment task) с оригинальным именем, которое задал админ при загрузке, а не с хешированным именем из S3.

## В scope

- Серверный эндпоинт proxy-download для `LmsAssignment.template_file` с `Content-Disposition: attachment; filename="<original>"` 
- Аналогичный эндпоинт для `LmsAssignmentTask.template_file`
- Замена прямых ссылок на S3 URL на серверный route во фронте (`Show.vue`, `InlineAssignment.vue`)
- Передача `template_file_name` для tasks из контроллера на фронт (уже передаётся для assignment)

## Вне scope

- Изменение способа хранения файлов (S3 layout не меняется)
- Скачивание файлов submission/review/comment (там уже есть `{name, path}`)
- Миграции БД (колонки `template_file_name` уже существуют)

## Ограничения

- Все команды — через Docker
- Max 5 файлов за шаг
- Эндпоинт должен проверять принадлежность assignment к event
- Fallback: если `template_file_name` пустой — использовать basename из URL

## Связанные сущности

- `LmsAssignment` — поля `template_file`, `template_file_name`
- `LmsAssignmentTask` — поля `template_file`, `template_file_name`
- `AssignmentController` (participant) — добавить метод `downloadTemplate`
- `routes/lms.php` — добавить route
- `Show.vue`, `InlineAssignment.vue` — заменить `<a :href>` на route
