# Прогресс: Интеграция медиабиблиотеки

## Completed

- T1. Добавить `media-picker-url` в Cities/Form.vue и Tours/Form.vue
  - Files: `resources/js/Pages/Admin/Cities/Form.vue`, `resources/js/Pages/Admin/Tours/Form.vue`
- T2. Добавить `media-picker-url` в Blog/Form.vue и Vacancies/Form.vue
  - Files: `resources/js/Pages/Admin/Blog/Form.vue`, `resources/js/Pages/Admin/Vacancies/Form.vue`
- T3. Добавить `media-picker-url` в Recipes/Form.vue и EducationProducts/Form.vue
  - Files: `resources/js/Pages/Admin/Recipes/Form.vue`, `resources/js/Pages/Admin/EducationProducts/Form.vue`
- T4. Рефакторинг ResearchPage/Index.vue — заменить кастомный upload
  - Files: `resources/js/Pages/Admin/ResearchPage/Index.vue`
  - Заменён `<input type="file">` + `uploadResultsImage()` на `ImageUploadCrop` + media-picker
- T5. Добавить поддержку медиабиблиотеки в DynamicList.vue
  - Files: `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue`
  - Добавлен `MediaPickerModal`, кнопка «Библиотека» в video-card, logo-card и FieldRenderer
- T6. Проверить доступ LMS Admin к media route
  - Результат: `route('admin.media.index')` доступен — обе группы используют только `auth` middleware, Ziggy экспортирует все маршруты
- T7. Интеграция в LMS Admin — Courses/Form.vue и Videos/Form.vue
  - Files: `resources/js/Pages/Lms/Admin/Courses/Form.vue`, `resources/js/Pages/Lms/Admin/Videos/Form.vue`
  - Courses: заменён кастомный upload на `ImageUploadCrop` + media-picker
  - Videos: добавлена кнопка «Из библиотеки» + `MediaPickerModal` (thumbnail отправляется как File — не переделывали)
- T8. Интеграция в RichTextEditor.vue
  - Files: `resources/js/Components/RichTextEditor.vue`
  - Добавлен prop `mediaPickerUrl`, кнопка «Из библиотеки» в toolbar, `MediaPickerModal`
  - Передан `:media-picker-url` во все использования RichTextEditor:
    - `Admin/Cities/Form.vue` (3), `Admin/Tours/Form.vue` (7), `Admin/Blog/Form.vue` (1)
    - `Admin/Vacancies/Form.vue` (4), `Admin/Recipes/Form.vue` (1), `Admin/EducationProducts/Form.vue` (1)
    - `Lms/Admin/Courses/Form.vue` (1), `Lms/Admin/Materials/Form.vue` (1)
    - `Lms/Admin/Courses/StageEditor.vue` (1), `Lms/Admin/Grants/Form.vue` (1)
- T9. Финальная верификация и build
  - `npm run build` — успешен (3.76s, 0 errors)
  - lint — clean
- T10 (hotfix). Directions/Form.vue — заменён `RInput` для URL на `ImageUploadCrop` + media-picker
  - Files: `resources/js/Pages/Admin/Directions/Form.vue`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
