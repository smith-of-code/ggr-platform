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

## Вне скоупа

- `resources/js/Pages/Lms/Profile/Edit.vue` — загрузка аватара пользователем (не админ-панель)
- Создание папок/коллекций в медиабиблиотеке
- Изменение модели `UploadedMedia` или бэкенда

## Ограничения

- Паттерн интеграции: передать prop `:media-picker-url="route('admin.media.index')"` в `ImageUploadCrop`
- Для LMS Admin нужно проверить, есть ли доступ к `route('admin.media.index')` или нужен отдельный эндпоинт
- Не менять поведение загрузки — только добавить возможность выбора из библиотеки
- Максимум 5 файлов за шаг

## Эталон

Файл `resources/js/Pages/Admin/AtomsVkusa/Form.vue` — каждый `ImageUploadCrop` содержит `:media-picker-url="route('admin.media.index')"`.
