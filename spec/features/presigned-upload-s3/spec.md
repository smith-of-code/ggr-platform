# Presigned Upload S3

## Цель

Реализовать загрузку файлов напрямую в S3 через presigned PUT URL, минуя прокси через PHP-сервер. AWS credentials остаются на сервере — клиент получает только подписанный URL с ограниченным TTL.

## In-scope

- Backend-сервис `PresignedUploadService`: генерация presigned PUT URL, подтверждение загрузки
- API-эндпоинты: `POST presigned-url` (получить подписанный URL), `POST confirm-upload` (подтвердить загрузку, создать `UploadedMedia`)
- Frontend-composable `usePresignedUpload`: запрос URL → прямой PUT в S3 → confirm → получение финального URL
- Интеграция в `ImageUploadCrop.vue` и `MediaPickerModal.vue`
- Интеграция в формы загрузки файлов: assignments (user + admin), profile documents, grant documents, tour cabinet
- Fallback на традиционную загрузку через сервер при `UPLOAD_DISK != s3` (локальная среда)
- Прогресс-бар загрузки (xhr.upload.onprogress)

## Out-of-scope

- Multipart upload для файлов > 100 МБ
- CORS-настройка S3 bucket (документируется, применяется вручную)
- MinIO для локальной разработки
- Миграция существующих файлов
- Client-side сжатие изображений (отдельная фича)
- SCORM-пакеты (отдельный механизм)

## Constraints

- AWS credentials **никогда** не передаются клиенту — только подписанный URL
- Presigned URL живёт не более 15 минут (`+15 minutes`)
- Presigned URL привязан к конкретному `key` (пути), `Content-Type` и максимальному размеру
- При `UPLOAD_DISK=public` (локалка) — используется традиционный upload через сервер, фронтенд переключается автоматически
- Валидация mime-type и размера происходит при генерации presigned URL (server-side) И при confirm (проверка `Storage::exists`)
- `UploadedMedia` создаётся только после confirm (а не при генерации URL)
- Существующие маршруты upload остаются рабочими (обратная совместимость)

## Безопасность presigned URL

1. Сервер подписывает URL HMAC-подписью с помощью AWS Secret Key
2. Клиент получает URL вида `PUT https://bucket.s3.../path?X-Amz-Expires=900&X-Amz-Signature=...`
3. URL действует только для указанного пути и Content-Type
4. Повторное использование после истечения невозможно
5. Credentials (Access Key ID, Secret) остаются на сервере

## Точки загрузки в кодовой базе

| Контроллер | Методы | Тип файлов |
|---|---|---|
| `Admin\UploadController` | `image()`, `file()` | изображения, любые файлы |
| `Lms\Admin\UploadController` | `image()`, `file()` | изображения, файлы KB |
| `Lms\AssignmentController` | `submit()`, `save()`, `resubmit()`, `comment()` | файлы заданий |
| `Lms\Admin\AssignmentController` | `review()`, `comment()`, `saveTemplate()` | файлы ревью, шаблоны |
| `Lms\ProfileController` | `update()`, `uploadDocument()` | аватар, документы |
| `Lms\Admin\VideoController` | thumbnail | изображения |
| `Lms\Admin\GrantController` | documents | PDF/документы |
| `TourCabinetController` | avatar, documents | аватар, документы |
| `TourCabinetContestController` | stage3 attachment | документы/архивы |
| `TourCabinetSupportMessageService` | attachments | любые файлы |
