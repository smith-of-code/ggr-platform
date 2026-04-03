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
- REV-001. Backend — расширить `file()` и `mediaIndex()` для видео/PDF
  - Files: `app/Http/Controllers/Admin/UploadController.php`
  - `file()` теперь создаёт `UploadedMedia` с collection/entity_type/entity_id
  - `mediaIndex()` принимает query-param `type` (image|video|document|all) и фильтрует по `mime_type`
- REV-002. Frontend — расширить `MediaPickerModal` для видео и документов
  - Files: `resources/js/Components/MediaPickerModal.vue`
  - Добавлены props: `accept`, `fileType`, `uploadField`
  - `fetchMedia()` передаёт `type` в API
  - `uploadFile()` использует `uploadField` вместо hardcoded `image`
  - Рендеринг: `<img>` для изображений, иконка видео, иконка документа для остальных
  - Тексты подсказок адаптированы под `typeLabel`
- REV-003. Интеграция PDF в Tours/Form.vue через библиотеку
  - Files: `resources/js/Pages/Admin/Tours/Form.vue`
  - Добавлены кнопки «Из библиотеки» для `program_pdf` и `memo_pdf`
  - Добавлен `MediaPickerModal` с `accept=".pdf"`, `file-type="document"`, `upload-field="file"`
- REV-004. Интеграция видео в DynamicList.vue через библиотеку
  - Files: `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue`
  - Добавлен `filePicker` state + `openFilePicker` / `onFilePickerSelect`
  - FieldRenderer `file-upload` — кнопка «Библиотека» с emit `file-pick`
  - Видео-карточка — кнопка «Библиотека» рядом с «Заменить»
  - Второй `MediaPickerModal` с динамическим `accept`/`file-type`
- REV-005. Интеграция видео/файлов в KnowledgeBase/Form.vue через библиотеку
  - Files: `resources/js/Pages/Lms/Admin/KnowledgeBase/Form.vue`
  - Импорт `MediaPickerModal`, добавлен `kbFilePicker` state
  - Кнопка «Библиотека» в пустом состоянии и «Заменить» в загруженном
  - `MediaPickerModal` с динамическим accept/file-type на основе `item.type`
- REV-006. Расширить `IndexExistingMedia` для `uploads/files/`
  - Files: `app/Console/Commands/IndexExistingMedia.php`
  - Сканирует `uploads/images/` и `uploads/files/`
  - Расширен MIME_MAP: video (mp4, webm, mov, avi), документы (pdf, doc, docx, xls, xlsx, zip)
- REV-007. Финальная верификация и build
  - `npm run build` — успешен (3.97s, 0 errors)
  - lint — clean
- REV-008. LMS UploadController — создавать UploadedMedia
  - Files: `app/Http/Controllers/Lms/Admin/UploadController.php`
  - `image()` и `file()` теперь создают `UploadedMedia` с collection/entity_type/entity_id
- REV-009. Materials/Form.vue — кнопка «Из библиотеки» для вложений
  - Files: `resources/js/Pages/Lms/Admin/Materials/Form.vue`
  - Импорт `MediaPickerModal`, `matFilePicker` state
  - Кнопка «Библиотека» в пустом состоянии, «Заменить» в загруженном
- REV-010. Assignments/Form.vue — кнопка «Из библиотеки» для шаблонов
  - Files: `resources/js/Pages/Lms/Admin/Assignments/Form.vue`
  - `tplPicker` state с target (main/task) + idx
  - Кнопки «Библиотека» для основного шаблона и шаблонов подзаданий
- REV-011. Grants/Form.vue — кнопка «Из библиотеки» для документов
  - Files: `resources/js/Pages/Lms/Admin/Grants/Form.vue`, `app/Http/Controllers/Lms/Admin/GrantController.php`
  - Фронт: `MediaPickerModal` с multiple, `media_document_urls` в форме
  - Бэкенд: `syncDocuments()` обрабатывает `media_document_urls` (создаёт LmsGrantDocument из URL)
- REV-012. Расширить IndexExistingMedia для uploads/kb/
  - Files: `app/Console/Commands/IndexExistingMedia.php`
  - Добавлен `uploads/kb/` в список сканируемых директорий
- REV-013. Обновление spec + финальная верификация
  - spec.md обновлён: добавлены Группа 5, Инфраструктура LMS, уточнён Out of scope
  - tasks.md обновлён: добавлены REV-008..REV-013
  - progress.md обновлён
  - `npm run build` — успешен (3.78s, 0 errors)

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
