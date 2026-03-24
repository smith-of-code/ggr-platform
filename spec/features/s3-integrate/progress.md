# Прогресс: s3-integrate

## Completed tasks

### Task 1: Конфигурация filesystems и env ✓
- Files: `config/filesystems.php`, `docker/.env.prod`, `docker/.env.local`, `.env.example`
- Добавлен `upload_disk` config key (`UPLOAD_DISK` env), `visibility: 'public'` для S3, AWS-переменные в `.env.prod`

### Task 2: Рефакторинг UploadController — диск и URL ✓
- Files: `app/Http/Controllers/Lms/Admin/UploadController.php`
- `'public'` → `config('filesystems.upload_disk')`, `'/storage/' . $path` → `Storage::disk($disk)->url($path)`

### Task 3: Рефакторинг VideoController — диск и URL ✓
- Files: `app/Http/Controllers/Lms/Admin/VideoController.php`
- Thumbnail upload на конфигурируемый диск, URL через `Storage::disk()->url()`

### Task 4: Рефакторинг ProfileController — диск и URL ✓
- Files: `app/Http/Controllers/Lms/ProfileController.php`
- Avatar upload на конфигурируемый диск, хранение полного URL в БД

### Task 5: Рефакторинг AssignmentController (user) — диск и URL ✓
- Files: `app/Http/Controllers/Lms/AssignmentController.php`
- Все `->store()` на конфигурируемый диск, URL через `Storage::disk()->url()`

### Task 6: Рефакторинг AssignmentController (admin) — диск и URL ✓
- Files: `app/Http/Controllers/Lms/Admin/AssignmentController.php`
- Review + comment файлы на конфигурируемый диск

### Task 7: Фронтенд — корректные URL файлов ✓
- Files: `resources/js/lib/fileUrl.js`, `resources/js/Pages/Lms/Assignments/Show.vue`, `resources/js/Pages/Lms/Admin/Assignments/Submissions.vue`, `resources/js/Pages/Lms/Profile/Edit.vue`
- Создана утилита `fileUrl()` — обрабатывает старые пути, `/storage/` URL, и S3 URL
- Все хардкоды `/storage/${...}` заменены на `fileUrl()`

### Task 8: Обновление spec и финальная проверка ✓
- Files: `spec/features/s3-integrate/progress.md`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

- Docker не был запущен — верификация через `php artisan config:show` не проведена. Рекомендуется проверить при запуске.
- Существующие данные в БД хранят относительные пути (например `avatars/xxx.jpg`). Утилита `fileUrl()` на фронте корректно их обрабатывает, но для полной согласованности можно запустить миграцию данных (out of scope).
- SCORM-пакеты оставлены в `public_path()` — требуют отдельного решения.
