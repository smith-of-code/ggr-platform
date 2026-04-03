# Задачи: Интеграция медиабиблиотеки

## T1. Добавить `media-picker-url` в Cities/Form.vue и Tours/Form.vue

- **Цель**: Подключить медиабиблиотеку к формам городов и туров
- **Scope**: `resources/js/Pages/Admin/Cities/Form.vue`, `resources/js/Pages/Admin/Tours/Form.vue`
- **DoD**: Каждый `<ImageUploadCrop>` содержит `:media-picker-url="route('admin.media.index')"`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npx vue-tsc --noEmit`

## T2. Добавить `media-picker-url` в Blog/Form.vue и Vacancies/Form.vue

- **Цель**: Подключить медиабиблиотеку к формам блога и вакансий
- **Scope**: `resources/js/Pages/Admin/Blog/Form.vue`, `resources/js/Pages/Admin/Vacancies/Form.vue`
- **DoD**: Каждый `<ImageUploadCrop>` содержит `:media-picker-url="route('admin.media.index')"`
- **Verify**: lint clean

## T3. Добавить `media-picker-url` в Recipes/Form.vue и EducationProducts/Form.vue

- **Цель**: Подключить медиабиблиотеку к формам рецептов и образовательных продуктов
- **Scope**: `resources/js/Pages/Admin/Recipes/Form.vue`, `resources/js/Pages/Admin/EducationProducts/Form.vue`
- **DoD**: Каждый `<ImageUploadCrop>` содержит `:media-picker-url="route('admin.media.index')"`
- **Verify**: lint clean

## T4. Рефакторинг ResearchPage/Index.vue — заменить кастомный upload

- **Цель**: Заменить `<input type="file">` + `uploadResultsImage()` на `ImageUploadCrop` с media-picker
- **Scope**: `resources/js/Pages/Admin/ResearchPage/Index.vue`
- **DoD**: Используется `<ImageUploadCrop>` с `:media-picker-url`, кастомный upload-код удалён
- **Verify**: lint clean, функционально — изображение results_image загружается и выбирается из библиотеки

## T5. Добавить поддержку медиабиблиотеки в DynamicList.vue

- **Цель**: В полях типа `image-upload` добавить кнопку «Из библиотеки»
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue`
- **DoD**: Поля `image-upload` показывают кнопку «Библиотека», открывающую `MediaPickerModal`
- **Verify**: lint clean

## T6. Проверить доступ LMS Admin к media route и создать endpoint при необходимости

- **Цель**: Убедиться, что LMS admin имеет доступ к эндпоинту медиабиблиотеки
- **Scope**: `routes/web.php`, `app/Http/Controllers/` (при необходимости)
- **DoD**: Маршрут для медиабиблиотеки доступен из LMS Admin middleware
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=media`

## T7. Интеграция в LMS Admin — Courses/Form.vue и Videos/Form.vue

- **Цель**: Подключить медиабиблиотеку к загрузке обложек курсов и видео
- **Scope**: `resources/js/Pages/Lms/Admin/Courses/Form.vue`, `resources/js/Pages/Lms/Admin/Videos/Form.vue`
- **DoD**: Обе формы используют `ImageUploadCrop` с media-picker или имеют кнопку «Из библиотеки»
- **Verify**: lint clean

## T8. Интеграция в RichTextEditor.vue

- **Цель**: Добавить кнопку «Из библиотеки» в toolbar редактора для вставки изображений
- **Scope**: `resources/js/Components/RichTextEditor.vue`
- **DoD**: Toolbar содержит кнопку библиотеки, выбранное изображение вставляется в контент
- **Verify**: lint clean

## T9. Финальная верификация и build

- **Цель**: Убедиться что всё собирается и линтер чист
- **Scope**: весь frontend
- **DoD**: `npm run build` успешен, `vue-tsc --noEmit` без ошибок
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

---

## REV-001. Backend — расширить `file()` и `mediaIndex()` для видео/PDF

- **Цель**: Загрузки через `file()` должны попадать в `uploaded_media`; `mediaIndex` должен фильтровать по типу
- **Scope**: `app/Http/Controllers/Admin/UploadController.php`
- **DoD**:
  - `file()` создаёт запись `UploadedMedia` после загрузки
  - `mediaIndex()` принимает query-param `type` (image, video, document, all) и фильтрует по `mime_type`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=upload`

## REV-002. Frontend — расширить `MediaPickerModal` для видео и документов

- **Цель**: Компонент должен поддерживать загрузку и отображение видео и PDF
- **Scope**: `resources/js/Components/MediaPickerModal.vue`
- **DoD**:
  - Новый prop `accept` (default `image/*`) — контролирует `<input accept>`
  - Новый prop `fileType` (image|video|document|all) — передаётся в API
  - Новый prop `uploadField` (default `image`) — имя поля в FormData
  - Превью: `<img>` для изображений, иконка видео для video/*, иконка файла для PDF
  - Текст подсказок адаптируется по типу
- **Verify**: lint clean

## REV-003. Интеграция PDF в Tours/Form.vue через библиотеку

- **Цель**: Добавить кнопку «Из библиотеки» для полей `program_pdf` и `memo_pdf`
- **Scope**: `resources/js/Pages/Admin/Tours/Form.vue`
- **DoD**: Рядом с кнопками «Загрузить PDF» появляется «Из библиотеки», открывающая `MediaPickerModal` с `accept=".pdf"` и `file-type="document"`
- **Verify**: lint clean

## REV-004. Интеграция видео в DynamicList.vue через библиотеку

- **Цель**: Добавить кнопку «Библиотека» для полей типа `file-upload`
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue`
- **DoD**: В FieldRenderer для `file-upload` есть кнопка «Библиотека» аналогично `image-upload`
- **Verify**: lint clean

## REV-005. Интеграция видео/файлов в KnowledgeBase/Form.vue через библиотеку

- **Цель**: Добавить кнопку «Из библиотеки» для загрузки видео/файлов в элементах KB
- **Scope**: `resources/js/Pages/Lms/Admin/KnowledgeBase/Form.vue`
- **DoD**: В режиме upload для video/file элементов есть кнопка «Из библиотеки»
- **Verify**: lint clean

## REV-006. Расширить `IndexExistingMedia` для `uploads/files/`

- **Цель**: Команда `media:index` должна сканировать и `uploads/files/`
- **Scope**: `app/Console/Commands/IndexExistingMedia.php`
- **DoD**: Команда индексирует файлы из `uploads/files/` с корректным `mime_type`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan media:index`

## REV-007. Финальная верификация и build (Группа 4)

- **Цель**: Убедиться что всё собирается
- **Scope**: весь frontend
- **DoD**: `npm run build` успешен
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

---

## REV-008. LMS UploadController — создавать UploadedMedia

- **Цель**: Файлы загружаемые через LMS Admin должны попадать в медиабиблиотеку
- **Scope**: `app/Http/Controllers/Lms/Admin/UploadController.php`
- **DoD**: `image()` и `file()` создают `UploadedMedia` с collection/entity_type/entity_id
- **Verify**: lint clean

## REV-009. Materials/Form.vue — «Из библиотеки» для вложений

- **Цель**: Прикреплённые файлы можно выбрать из медиабиблиотеки
- **Scope**: `resources/js/Pages/Lms/Admin/Materials/Form.vue`
- **DoD**: Кнопка «Библиотека» и «Заменить» через `MediaPickerModal`
- **Verify**: lint clean

## REV-010. Assignments/Form.vue — «Из библиотеки» для шаблонов

- **Цель**: Шаблоны заданий и подзаданий можно выбрать из медиабиблиотеки
- **Scope**: `resources/js/Pages/Lms/Admin/Assignments/Form.vue`
- **DoD**: Кнопка «Библиотека» для основного шаблона и шаблонов подзаданий
- **Verify**: lint clean

## REV-011. Grants/Form.vue — «Из библиотеки» для документов

- **Цель**: Документы гранта можно выбрать из медиабиблиотеки (множественный выбор)
- **Scope**: `resources/js/Pages/Lms/Admin/Grants/Form.vue`, `app/Http/Controllers/Lms/Admin/GrantController.php`
- **DoD**: Кнопка «Из библиотеки» + multiple `MediaPickerModal`; бэкенд `syncDocuments()` обрабатывает `media_document_urls`
- **Verify**: lint clean, `npm run build`

## REV-012. IndexExistingMedia — сканировать `uploads/kb/`

- **Цель**: Команда `media:index` должна сканировать `uploads/kb/` (файлы KB LMS)
- **Scope**: `app/Console/Commands/IndexExistingMedia.php`
- **DoD**: Директория `uploads/kb/` добавлена в список сканируемых
- **Verify**: lint clean

## REV-013. Финальная верификация и build (Группа 5)

- **Цель**: Убедиться что всё собирается после Группы 5
- **Scope**: весь frontend
- **DoD**: `npm run build` успешен
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
