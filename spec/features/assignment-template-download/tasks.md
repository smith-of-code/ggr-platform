# Задачи — assignment-template-download

## Task 1: Бэкенд — метод downloadTemplate в AssignmentController (participant)

- **Цель**: proxy-download файла шаблона задания с оригинальным именем
- **Scope**: `app/Http/Controllers/Lms/AssignmentController.php`
- **DoD**: метод `downloadTemplate(LmsEvent, LmsAssignment)` возвращает `StreamedResponse` с `Content-Disposition: attachment; filename="<original_name>"`; fallback на basename если `template_file_name` пуст
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=assignments`

## Task 2: Бэкенд — метод downloadTaskTemplate в AssignmentController (participant)

- **Цель**: proxy-download файла шаблона подзадачи (task) с оригинальным именем
- **Scope**: `app/Http/Controllers/Lms/AssignmentController.php`
- **DoD**: метод `downloadTaskTemplate(LmsEvent, LmsAssignment, LmsAssignmentTask)` возвращает `StreamedResponse`; проверка принадлежности task к assignment
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=assignments`

## Task 3: Маршруты — регистрация в routes/lms.php

- **Цель**: добавить GET-маршруты для скачивания шаблонов
- **Scope**: `routes/lms.php`
- **DoD**: `GET /assignments/{assignment}/template-download` и `GET /assignments/{assignment}/tasks/{task}/template-download` зарегистрированы
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=assignments`

## Task 4: Фронт — Show.vue — замена ссылок на route

- **Цель**: ссылки «Скачать шаблон» ведут на серверный эндпоинт вместо прямого S3 URL
- **Scope**: `resources/js/Pages/Lms/Assignments/Show.vue`
- **DoD**: `<a :href>` использует `route('lms.assignments.template-download', ...)` для assignment и task шаблонов
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 5: Фронт — InlineAssignment.vue — замена ссылок на route

- **Цель**: аналогичная замена для inline-компонента
- **Scope**: `resources/js/Components/Lms/InlineAssignment.vue`
- **DoD**: `<a :href>` использует серверные маршруты
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 6: Обновление spec

- **Цель**: обновить `spec/features/lms-assignments/spec.md` — описать новые маршруты
- **Scope**: `spec/features/lms-assignments/spec.md`, `spec/features/assignment-template-download/spec.md`
- **DoD**: spec отражает финальное состояние

## Task 7: Верификация

- **Цель**: build + route:list + проверка линтера
- **Scope**: —
- **DoD**: `npm run build` успешен, `route:list` показывает новые маршруты, линтер чист
- **Verify**: Docker command pattern
