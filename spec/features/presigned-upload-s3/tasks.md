# Задачи: Presigned Upload S3

## Task 1: PresignedUploadService

- **Цель**: Создать сервис генерации presigned PUT URL и подтверждения загрузки
- **Scope**: `app/Services/PresignedUploadService.php`
- **DoD**: Сервис генерирует presigned URL для S3 диска, возвращает fallback для local диска, метод `confirm()` проверяет наличие файла и создаёт `UploadedMedia`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="app(App\Services\PresignedUploadService::class)"`

## Task 2: PresignedUploadController + маршруты

- **Цель**: API-эндпоинты для presigned URL и confirm
- **Scope**: `app/Http/Controllers/Api/PresignedUploadController.php`, `routes/web.php`, `routes/lms.php`
- **DoD**: `POST presigned-url` и `POST confirm-upload` доступны в admin, lms-admin и authenticated LMS контексте
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=presigned`

## Task 3: Frontend composable usePresignedUpload

- **Цель**: Composable для прямой загрузки в S3 с прогрессом и fallback
- **Scope**: `resources/js/composables/usePresignedUpload.js`
- **DoD**: Экспортирует функцию `uploadFile(file, options)` → `{ url, progress, error }`, автоматический fallback на server upload при `mode === "server"`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 4: Интеграция в ImageUploadCrop.vue

- **Цель**: Переключить `ImageUploadCrop` на presigned upload
- **Scope**: `resources/js/Components/ImageUploadCrop.vue`
- **DoD**: При наличии `presignedUrl` проп — использует presigned flow, прогресс-бар вместо спиннера, обратная совместимость при отсутствии пропа
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 5: Интеграция в MediaPickerModal.vue

- **Цель**: Переключить upload в MediaPickerModal на presigned flow
- **Scope**: `resources/js/Components/MediaPickerModal.vue`
- **DoD**: Upload файлов в модале использует presigned upload, прогресс отображается
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 6: Интеграция в assignment upload (user)

- **Цель**: Перевести загрузку файлов заданий (user) на presigned upload
- **Scope**: `resources/js/Pages/Lms/Assignments/Show.vue`, `resources/js/Components/Lms/InlineAssignment.vue`
- **DoD**: Файлы заданий загружаются через presigned URL, backend `AssignmentController` принимает URL вместо файлов
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 7: Интеграция в assignment upload (admin) + grants + videos

- **Цель**: Перевести admin-загрузки на presigned upload
- **Scope**: `resources/js/Pages/Lms/Admin/Assignments/Form.vue`, `resources/js/Pages/Lms/Admin/Grants/Form.vue`, `resources/js/Pages/Lms/Admin/Videos/Form.vue`, контроллеры
- **DoD**: Admin-формы используют presigned upload для файлов, backend контроллеры принимают URL
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 8: Интеграция в profile + tour cabinet

- **Цель**: Перевести загрузку аватаров и документов на presigned upload
- **Scope**: `resources/js/Pages/Lms/Profile/Edit.vue`, `resources/js/Pages/TourCabinet/Dashboard.vue`, контроллеры
- **DoD**: Avatar и document upload используют presigned flow
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## Task 9: CORS-документация + финальная проверка

- **Цель**: Документировать CORS-настройку S3 и провести финальную проверку
- **Scope**: `spec/features/presigned-upload-s3/cors-setup.md`, все изменённые файлы
- **DoD**: Документация CORS-конфигурации, все маршруты и фронтенд собираются без ошибок
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build && docker exec ${APP_NAME}_fpm php artisan route:list --path=presigned`
