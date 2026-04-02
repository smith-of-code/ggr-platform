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
