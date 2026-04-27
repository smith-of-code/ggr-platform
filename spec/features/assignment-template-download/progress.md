# Прогресс — assignment-template-download

## Completed

0. Баг-фикс: `template_file_name` не сохранялось при создании/обновлении задания через MediaPicker — добавлено правило `'template_file_name' => ['nullable', 'string', 'max:255']` в `assignmentRules()`
   - Files: `app/Http/Controllers/Lms/Admin/AssignmentController.php`

1. Task 1: Бэкенд — метод `downloadTemplate` в `Lms\AssignmentController`
   - Proxy-download через `Storage::disk()->download()` с оригинальным именем
   - Fallback на basename из URL если `template_file_name` пуст
   - Files: `app/Http/Controllers/Lms/AssignmentController.php`

2. Task 2: Бэкенд — метод `downloadTaskTemplate` в `Lms\AssignmentController`
   - Аналогично для task-level шаблонов, проверка принадлежности task к assignment
   - Files: `app/Http/Controllers/Lms/AssignmentController.php`

3. Task 3: Маршруты в `routes/lms.php`
   - `GET /assignments/{assignment}/template-download` → `lms.assignments.template-download`
   - `GET /assignments/{assignment}/tasks/{task}/template-download` → `lms.assignments.task-template-download`
   - Files: `routes/lms.php`

4. Task 4: Фронт — `Show.vue` — ссылки заменены на серверные маршруты
   - Files: `resources/js/Pages/Lms/Assignments/Show.vue`

5. Task 5: Фронт — `InlineAssignment.vue` — ссылки заменены на серверные маршруты
   - Files: `resources/js/Components/Lms/InlineAssignment.vue`

6. Task 6: Обновление spec — добавлены новые маршруты в `spec/features/lms-assignments/spec.md`
   - Files: `spec/features/lms-assignments/spec.md`

7. Task 7: Верификация
   - `php artisan route:list --path=assignments` — 17 маршрутов, оба новых присутствуют ✓
   - `npm run build` — OK (4.57s, 0 errors) ✓
   - Линтер — 0 ошибок ✓

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
