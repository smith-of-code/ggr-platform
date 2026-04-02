# Media Library: коллекции + привязка к сущности

## Статус: Completed

## Завершённые задачи

- T1. Миграция: добавлены `collection`, `entity_type`, `entity_id` в `uploaded_media` + обновлена модель `UploadedMedia`
  - Files: `database/migrations/2026_04_02_100000_add_collection_entity_to_uploaded_media.php`, `app/Models/UploadedMedia.php`

- T2. Backend: `UploadController` — `image()` принимает `collection`/`entity_type`/`entity_id` при загрузке, `mediaIndex()` поддерживает `scope` (entity/collection/all) с counts и `entity_label`
  - Files: `app/Http/Controllers/Admin/UploadController.php`

- T3. `MediaPickerModal` — новые props `collection`/`entityType`/`entityId`, табы [Этот объект | Вся коллекция | Вся библиотека] с counts, передача контекста при загрузке
  - Files: `resources/js/Components/MediaPickerModal.vue`

- T4. `ImageUploadCrop` — props `collection`/`entityType`/`entityId`, прокидываются в `MediaPickerModal` и добавляются в `FormData` при прямой загрузке
  - Files: `resources/js/Components/ImageUploadCrop.vue`

- T5. `RichTextEditor` — props `collection`/`entityType`/`entityId`, прокидываются в `MediaPickerModal` и добавляются в `FormData` при загрузке через toolbar
  - Files: `resources/js/Components/RichTextEditor.vue`

- T6. Прокинуты `collection`/`entityType`/`entityId` во все Admin-формы:
  - `Cities/Form.vue` → collection=cities, entity=City
  - `Tours/Form.vue` → collection=tours, entity=Tour
  - `Blog/Form.vue` → collection=blog, entity=BlogPost
  - `Vacancies/Form.vue` → collection=vacancies, entity=Vacancy
  - `Recipes/Form.vue` → collection=recipes, entity=Recipe
  - `EducationProducts/Form.vue` → collection=education_products, entity=EducationProduct
  - `Directions/Form.vue` → collection=directions, entity=Direction
  - `ResearchPage/Index.vue` → collection=research_page (singleton)
  - `AtomsVkusa/Form.vue` → collection=atoms_vkusa (singleton)
  - OpportunityToursPage/DynamicList.vue — не использует media-picker-url, пропущен

- T7. Прокинуты `collection`/`entityType`/`entityId` во все LMS Admin-формы:
  - `Lms/Admin/Courses/Form.vue` → collection=lms_courses, entity=Lms\Course
  - `Lms/Admin/Courses/StageEditor.vue` → collection=lms_courses (без entity, нет доступа к course ID)
  - `Lms/Admin/Materials/Form.vue` → collection=lms_materials, entity=Lms\Material
  - `Lms/Admin/Grants/Form.vue` → collection=lms_grants, entity=Lms\Grant

- T8. Верификация: миграция выполнена, `npm run build` успешно, линтер без ошибок

- T9 (hotfix). Исправлены entity_type — приведены к реальным FQCN моделей:
  - `App\Models\BlogPost` → `App\Models\Post` (Blog/Form.vue)
  - `App\Models\Lms\Course` → `App\Models\Lms\LmsCourse` (Lms/Admin/Courses/Form.vue)
  - `App\Models\Lms\Material` → `App\Models\Lms\LmsMaterialSection` (Lms/Admin/Materials/Form.vue)
  - `App\Models\Lms\Grant` → `App\Models\Lms\LmsGrant` (Lms/Admin/Grants/Form.vue)

- T10. Artisan-команда `media:backfill-collections` для индексации существующих медиа
  - Сканирует все таблицы сущностей (cities, tours, posts, vacancies, recipes, education_products, directions, lms_courses, atoms_vkusa_content, settings)
  - Ищет URL изображений в прямых колонках и JSON-полях (gallery, attractions, accommodations и т.д.)
  - Матчит с `uploaded_media.url` и проставляет `collection`/`entity_type`/`entity_id`
  - Поддерживает `--dry-run` режим
  - Files: `app/Console/Commands/BackfillMediaCollections.php`
