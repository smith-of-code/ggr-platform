# Прогресс: create-course-from-refs

## Completed tasks

### Task 1: Миграция — source_module_id и source_stage_id ✓
- Files: `database/migrations/2026_03_19_300000_add_source_refs_to_modules_and_stages.php`

### Task 2: Обновление моделей ✓
- Files: `app/Models/Lms/LmsCourseModule.php`, `app/Models/Lms/LmsCourseStage.php`

### Task 3: API — поиск модулей по названию ✓
- Files: `app/Http/Controllers/Lms/Admin/CourseController.php`, `routes/lms.php`
- Исправление: `LIKE` → `ILIKE` для регистронезависимого поиска в PostgreSQL

### Task 4: API — поиск этапов по названию ✓
- Files: `app/Http/Controllers/Lms/Admin/CourseController.php`, `routes/lms.php`
- Исправление: `LIKE` → `ILIKE`

### Task 5: Vue-компонент SearchRefModal ✓
- Files: `resources/js/Pages/Lms/Admin/Courses/SearchRefModal.vue`
- Исправление: Breeze `Modal.vue` → `RModal` из UI-kit (z-index проблема с LmsAdminLayout)

### Task 6: Интеграция поиска модулей в Form.vue ✓
- Files: `resources/js/Pages/Lms/Admin/Courses/Form.vue`
- Изменение UX: кнопка перенесена из хедера внутрь каждого блока модуля (над полем названия). Replace-логика вместо push.

### Task 7: Интеграция поиска этапов в StageEditor.vue + Form.vue ✓
- Files: `resources/js/Pages/Lms/Admin/Courses/StageEditor.vue`, `Form.vue`
- Изменение UX: кнопка перенесена внутрь каждого StageEditor (над полем названия). Emit `search` → Form.vue открывает попап → replace текущего этапа.

### Task 8: Backend — сохранение source_module_id / source_stage_id ✓
- Files: `app/Http/Controllers/Lms/Admin/CourseController.php`

### Task 9: Загрузка source-ссылок при редактировании ✓
- Files: `app/Http/Controllers/Lms/Admin/CourseController.php`, `resources/js/Pages/Lms/Admin/Courses/Form.vue`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
