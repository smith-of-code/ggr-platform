# План — vshgr-products

## Milestones

1. **Миграция и модель** — добавить `type`, `sections`, `countries`, `regulation_file` в `education_products`; обновить модель `EducationProduct` (casts, fillable, accessors)
2. **Выбор типа в админке** — модальное окно / шаг выбора типа при нажатии «Новый продукт» на `Admin/EducationProducts/Index.vue`
3. **Динамическая админ-форма** — адаптировать `Admin/EducationProducts/Form.vue` под 3 типа: разные наборы полей, чекбоксы секций, редакторы блоков, загрузка PDF, JSON-редактор экспертов
4. **Серверная валидация** — обновить `EducationProductController` (валидация `type`, `sections.*`, `countries.*`, `regulation_file`)
5. **Публичная страница education** — переработать `Education/Show.vue` для типа `education`: блочная структура с навигацией по секциям
6. **Публичная страница partner** — шаблон для типа `partner`: описание + условия участия
7. **Публичная страница international** — шаблон для типа `international`: разделение на страны, блок описания

## UI Components

- `RichTextEditor` — для блоков RichText в секциях
- `ImageUploadCrop` — для фото экспертов и основного изображения продукта
- `RInput`, `RCheckbox`, `RCard`, `RButton` — UI-kit
- `ContentPreview` — предпросмотр
- Новый: `ExpertEditor.vue` (или inline) — компонент добавления/удаления экспертов (name, position, photo, bio)
- Новый: `SectionToggle.vue` (или inline) — чекбокс + сворачиваемый блок контента секции
- Новый: `CountryEditor.vue` (или inline) — редактор стран для типа international

## Verification

Верификация по паттерну из spec-continuation:
```
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
