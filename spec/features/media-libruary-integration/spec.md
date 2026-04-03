# Интеграция медиабиблиотеки во все формы с загрузкой изображений

## Цель

Подключить компонент медиабиблиотеки (`MediaPickerModal` через `ImageUploadCrop`) ко всем местам загрузки изображений в админке, аналогично `AtomsVkusa/Form.vue`.

## В скоупе

- **Группа 1 — Добавить `media-picker-url` к существующим `ImageUploadCrop`** (уже используют компонент, просто нет пропа):
  - `resources/js/Pages/Admin/Cities/Form.vue` — 3 экземпляра (фото города, герб, фото аттракций)
  - `resources/js/Pages/Admin/Tours/Form.vue` — 1 экземпляр (изображение тура)
  - `resources/js/Pages/Admin/Blog/Form.vue` — 1 экземпляр (изображение статьи)
  - `resources/js/Pages/Admin/Vacancies/Form.vue` — 1 экземпляр (изображение вакансии)
  - `resources/js/Pages/Admin/Recipes/Form.vue` — 1 экземпляр (фото блюда)
  - `resources/js/Pages/Admin/EducationProducts/Form.vue` — 1 экземпляр (изображение продукта)

- **Группа 2 — Заменить кастомный upload на `ImageUploadCrop` + media-picker** (используют `<input type="file" accept="image/*">` напрямую):
  - `resources/js/Pages/Admin/ResearchPage/Index.vue` — поле `results_image`
  - `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue` — тип поля `image-upload`

- **Группа 3 — LMS Admin (требуют проверки маршрутов)**:
  - `resources/js/Pages/Lms/Admin/Courses/Form.vue` — обложка курса (кастомный upload)
  - `resources/js/Pages/Lms/Admin/Videos/Form.vue` — thumbnail видео (кастомный upload)
  - `resources/js/Components/RichTextEditor.vue` — инлайн-вставка изображений в редактор

- **Группа 4 — Видео и PDF через медиабиблиотеку** (правка заказчика 2026-04-03):
  - `resources/js/Pages/Admin/Tours/Form.vue` — загрузка PDF (`program_pdf`, `memo_pdf`)
  - `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue` — загрузка видео (`file-upload` с `accept: 'video/*'`)
  - `resources/js/Pages/Lms/Admin/KnowledgeBase/Form.vue` — загрузка видео/файлов (элементы типа `video`/`file`)

- **Группа 5 — Остальные файловые загрузки в LMS Admin** (правка заказчика 2026-04-03):
  - `resources/js/Pages/Lms/Admin/Materials/Form.vue` — прикреплённые файлы (любой тип)
  - `resources/js/Pages/Lms/Admin/Assignments/Form.vue` — шаблоны заданий и подзаданий (любой тип)
  - `resources/js/Pages/Lms/Admin/Grants/Form.vue` — документы для скачивания (PDF, DOC, изображения)

- **Инфраструктура — LMS UploadController**:
  - `app/Http/Controllers/Lms/Admin/UploadController.php` — `image()` и `file()` не создавали `UploadedMedia`

## Вне скоупа

- `resources/js/Pages/Lms/Profile/Edit.vue` — загрузка аватара и документов профиля (не админ-панель)
- `resources/js/Pages/Lms/Assignments/Show.vue` — файлы ответов участников (не админ-панель)
- `resources/js/Pages/Lms/Admin/Assignments/Submissions.vue` — файлы к ревью/комментариям (специфический паттерн)
- `resources/js/Pages/Lms/Admin/Courses/StageEditor.vue` — SCORM zip-пакеты (не подходит для библиотеки)
- `resources/js/Pages/Lms/Admin/Users/Index.vue` — импорт Excel (не подходит для библиотеки)
- Создание папок/коллекций в медиабиблиотеке

## Ограничения

- Паттерн интеграции для изображений: передать prop `:media-picker-url="route('admin.media.index')"` в `ImageUploadCrop`
- Паттерн интеграции для видео/PDF: кнопка «Из библиотеки» → `MediaPickerModal` с `accept`/`file-type` пропами
- Для LMS Admin нужно проверить, есть ли доступ к `route('admin.media.index')` или нужен отдельный эндпоинт
- Не менять поведение загрузки — только добавить возможность выбора из библиотеки
- Максимум 5 файлов за шаг

## Эталон

Файл `resources/js/Pages/Admin/AtomsVkusa/Form.vue` — каждый `ImageUploadCrop` содержит `:media-picker-url="route('admin.media.index')"`.

## Правки заказчика (2026-04-03)

### Анализ мест загрузки видео и PDF

**Загрузка видео (файл, не URL):**
- `Admin/OpportunityToursPage/DynamicList.vue` — `file-upload` с `accept: 'video/*'` → `admin.upload.file` (NEW_REQUIREMENT)
- `Lms/Admin/KnowledgeBase/Form.vue` — элементы `type=video`, `accept="video/*"` → `lms.admin.upload.file` (NEW_REQUIREMENT)

**Загрузка PDF:**
- `Admin/Tours/Form.vue` — `accept=".pdf"` для `program_pdf`, `memo_pdf` → `admin.upload.file` (NEW_REQUIREMENT)

**Инфраструктурные изменения (выполнено):**
- `Admin\UploadController::file()` — создаёт `UploadedMedia` ✅
- `Lms\Admin\UploadController::image()` и `file()` — создают `UploadedMedia` ✅
- `Admin\UploadController::mediaIndex()` — фильтр по `type` param ✅
- `MediaPickerModal.vue` — props `accept`, `fileType`, `uploadField`, превью для видео/PDF ✅
- `IndexExistingMedia` — сканирует `uploads/images/`, `uploads/files/`, `uploads/kb/` ✅
- `Lms\Admin\GrantController::syncDocuments()` — обработка `media_document_urls` ✅
