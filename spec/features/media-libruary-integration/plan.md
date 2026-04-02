# План интеграции медиабиблиотеки

## Этапы

1. **Группа 1: Добавить `media-picker-url` к 6 админским формам** — простейшее изменение, по 1 пропу на каждый `<ImageUploadCrop>`
2. **Группа 2: Рефакторинг ResearchPage/Index.vue** — заменить кастомную загрузку на `ImageUploadCrop` + media-picker
3. **Группа 2: Рефакторинг OpportunityToursPage/DynamicList.vue** — добавить поддержку медиабиблиотеки в `image-upload` поля
4. **Группа 3: Проверить доступность маршрута `admin.media.index` из LMS Admin** — если нет, создать эндпоинт в LMS
5. **Группа 3: Интеграция в LMS Admin формы** — Courses/Form.vue, Videos/Form.vue
6. **Группа 3: Интеграция в RichTextEditor.vue** — добавить кнопку «Из библиотеки» в toolbar
7. **Верификация** — build frontend, lint clean

## UI Components

- `ImageUploadCrop` — существующий, с пропом `mediaPickerUrl` (уже поддерживается)
- `MediaPickerModal` — существующий, используется внутри `ImageUploadCrop`
- Новых компонентов не требуется

## Верификация

Для каждого шага: `source docker/.env.local && docker exec ${APP_NAME}_fpm npx vue-tsc --noEmit` + `npm run build` (Docker pattern из spec-continuation).
