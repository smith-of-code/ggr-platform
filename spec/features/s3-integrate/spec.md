# S3 Integrate

## Цель

Переключить хранение загружаемых файлов на S3-совместимое хранилище на production. Локально — без изменений (диск `local`/`public`).

## In-scope

- Заменить хардкод диска `'public'` на конфигурируемый диск (через `FILESYSTEM_DISK` или отдельную env-переменную)
- Заменить хардкод URL `/storage/` на вызовы `Storage::url()`
- Добавить AWS env-переменные в `.env.prod` и `.env.example`
- Обновить `config/filesystems.php` — добавить `visibility: 'public'` для S3
- Обеспечить единый диск для всех загрузок: `UploadController`, `VideoController`, `ProfileController`, `AssignmentController` (user + admin)
- SCORM-пакеты: отдельный подход (распаковка в public — оставить как есть или перенести)

## Out-of-scope

- Миграция существующих файлов с `public` диска в S3
- CDN-настройка / CloudFront
- Изменение SCORM-механизма (extractTo public) — оставляем как есть, запланировать отдельно
- Presigned URLs / временные ссылки
- MinIO или другое S3-совместимое локальное хранилище для dev

## Constraints

- Локальная среда: `FILESYSTEM_DISK=local`, файлы через `public` диск, URL через `storage:link`
- Production: `FILESYSTEM_DISK=s3`, файлы в S3, URL через `Storage::url()`
- Все контроллеры должны использовать один и тот же абстрактный подход к диску
- Не ломать существующий функционал на локалке
- `league/flysystem-aws-s3-v3` уже включён в Laravel 12 framework (не нужен отдельный пакет)
