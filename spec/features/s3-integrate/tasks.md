# Задачи: S3 Integrate

## Task 1: Конфигурация filesystems и env

- **Цель**: Подготовить конфигурацию для S3 на production
- **Scope**: `config/filesystems.php`, `.env.example`, `docker/.env.prod`, `docker/.env.local`
- **DoD**: В `filesystems.php` добавлен диск `uploads` (driver из env), в `.env.prod` добавлены AWS-переменные + `UPLOAD_DISK=s3`, в `.env.local` — `UPLOAD_DISK=public`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan config:show filesystems`

## Task 2: Рефакторинг UploadController — диск и URL

- **Цель**: Заменить хардкод `'public'` и `'/storage/'` на конфигурируемые
- **Scope**: `app/Http/Controllers/Lms/Admin/UploadController.php`
- **DoD**: Используется `config('filesystems.upload_disk')` или аналог, URL через `Storage::disk()->url()`
- **Verify**: Ручная проверка или `php artisan route:list`

## Task 3: Рефакторинг VideoController — диск и URL

- **Цель**: Заменить хардкод диска/URL для thumbnails
- **Scope**: `app/Http/Controllers/Lms/Admin/VideoController.php`
- **DoD**: Thumbnail загружается на настроенный диск, URL через `Storage::url()`
- **Verify**: `php artisan route:list`

## Task 4: Рефакторинг ProfileController — диск и URL

- **Цель**: Аватары на настроенный диск
- **Scope**: `app/Http/Controllers/Lms/ProfileController.php`
- **DoD**: `->store('avatars', $disk)`, URL корректный
- **Verify**: `php artisan route:list`

## Task 5: Рефакторинг AssignmentController (user) — диск и URL

- **Цель**: Файлы заданий/комментариев на настроенный диск
- **Scope**: `app/Http/Controllers/Lms/AssignmentController.php`
- **DoD**: Все `->store(...)` используют конфигурируемый диск
- **Verify**: `php artisan route:list`

## Task 6: Рефакторинг AssignmentController (admin) — диск и URL

- **Цель**: Review-файлы и комментарии на настроенный диск
- **Scope**: `app/Http/Controllers/Lms/Admin/AssignmentController.php`
- **DoD**: Все `->store(...)` используют конфигурируемый диск
- **Verify**: `php artisan route:list`

## Task 7: Фронтенд — корректные URL файлов

- **Цель**: Убедиться что фронтенд корректно работает с абсолютными URL (S3) и относительными (local)
- **Scope**: Vue-компоненты, отображающие загруженные файлы / изображения
- **DoD**: Если URL начинается с `http` — используется как есть, если с `/` — относительный
- **Verify**: Визуальная проверка на локалке

## Task 8: Обновление spec и финальная проверка

- **Цель**: Обновить spec-документацию, проверить что всё работает локально
- **Scope**: `spec/`, `config/filesystems.php`
- **DoD**: Spec обновлён, локальная проверка пройдена
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan config:show filesystems`
