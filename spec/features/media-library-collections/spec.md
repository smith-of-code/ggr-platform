# Media Library: коллекции + привязка к сущности

## Описание

Расширение медиабиблиотеки для привязки загружаемых изображений к конкретным сущностям (город, тур, статья и т.д.) через поля `collection`, `entity_type`, `entity_id` в таблице `uploaded_media`.

## Схема БД

`uploaded_media`:
- `collection` (string, nullable) — тип раздела: cities, tours, blog, vacancies, recipes, education_products, directions, research_page, atoms_vkusa, lms_courses, lms_materials, lms_grants
- `entity_type` (string, nullable) — FQCN модели Laravel (полиморфная связь)
- `entity_id` (unsignedBigInteger, nullable) — ID конкретной сущности
- Composite index: `(collection, entity_type, entity_id)`

## API

### POST /admin/upload/image
Дополнительные поля: `collection`, `entity_type`, `entity_id` (все nullable).

### GET /admin/media
Query params: `collection`, `entity_type`, `entity_id`, `scope` (entity|collection|all), `search`, `page`.
Возвращает: стандартный paginated + `counts` (entity, collection, all) + `entity_label`.

## UI: MediaPickerModal

Табы (видны если передан collection или entityId):
- **Этот объект** (scope=entity) — только если передан entityId
- **Вся коллекция** (scope=collection) — если передан collection
- **Вся библиотека** (scope=all) — всегда

По умолчанию активен первый доступный таб.

## Правила

- При создании сущности (create) — `entity_id = null`, передаётся только `collection`
- Singleton-страницы (ResearchPage, AtomsVkusa) — только `collection`, без entity
- `entity_type` хранится как FQCN модели для будущей совместимости с `morphTo()`
