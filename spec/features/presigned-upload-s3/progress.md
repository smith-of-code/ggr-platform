# Прогресс: presigned-upload-s3

## Completed tasks

### Task 1: PresignedUploadService ✓
- Files: `app/Services/PresignedUploadService.php`
- Методы: `generatePresignedUrl()`, `confirm()`

### Task 2: PresignedUploadController + маршруты ✓
- Files: `app/Http/Controllers/PresignedUploadController.php`, `routes/web.php`, `routes/lms.php`
- 4 группы: admin, lms auth, lms admin, tour-cabinet

### Task 3: Frontend composable usePresignedUpload ✓
- Files: `resources/js/composables/usePresignedUpload.js`
- `uploadFile()` с progress, fallback на server upload

### Task 4: Интеграция в ImageUploadCrop.vue ✓
- Files: `resources/js/Components/ImageUploadCrop.vue`
- Пропы `presignedUrlEndpoint` + `confirmEndpoint`, прогресс-бар, обратная совместимость

### Task 5: Интеграция в MediaPickerModal.vue ✓
- Files: `resources/js/Components/MediaPickerModal.vue`
- Пропы `presignedUrlEndpoint` + `confirmEndpoint`, проброс из ImageUploadCrop

### Task 6: Интеграция в assignment upload (user) ✓
- Files: `app/Http/Controllers/Lms/AssignmentController.php`, `app/Http/Controllers/Lms/StageController.php`, `resources/js/Pages/Lms/Assignments/Show.vue`, `resources/js/Components/Lms/InlineAssignment.vue`, `resources/js/Pages/Lms/Courses/Stage.vue`
- Backend: `file_urls`, `file_url` параметры в submit/draft/comment/update/saveAnswers
- Frontend: pre-upload файлов через presigned перед submit

### Task 7: Интеграция в assignment upload (admin) + grants + videos ✓
- Files: `app/Http/Controllers/Lms/Admin/AssignmentController.php`
- Backend: `file_urls` в review() и comment()
- GrantController уже поддерживает `media_document_urls`
- VideoController thumbnail через ImageUploadCrop — покрыт Task 4

### Task 8: Интеграция в profile + tour cabinet ✓
- Files: `app/Http/Controllers/Lms/ProfileController.php`, `app/Http/Controllers/TourCabinetController.php`
- Backend: `avatar_url`, `file_url` + `file_name` альтернативы

### Task 9: CORS-документация + финальная проверка ✓
- Files: `spec/features/presigned-upload-s3/cors-setup.md`
- Build: clean (`npm run build` — OK)
- Routes: 4 presigned-url + 4 confirm маршрута

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
