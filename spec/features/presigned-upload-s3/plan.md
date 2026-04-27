# План: Presigned Upload S3

## Milestones

1. **Backend-сервис** — `PresignedUploadService` для генерации presigned URL и подтверждения загрузки
2. **API-эндпоинты** — маршруты и контроллер `PresignedUploadController` (admin + lms admin + public LMS)
3. **Frontend-composable** — `usePresignedUpload` с прогрессом, fallback, error handling
4. **Интеграция ImageUploadCrop + MediaPickerModal** — переход на presigned upload
5. **Интеграция форм заданий** — assignment submission/comment (user + admin)
6. **Интеграция профильных загрузок** — avatar, documents (LMS + TourCabinet)
7. **Интеграция остальных загрузок** — grants, videos, support, contest
8. **CORS-документация + финальная проверка**

## Архитектура

### Backend flow

```
POST /api/upload/presigned-url
  Body: { filename, content_type, size, directory?, collection?, entity_type?, entity_id? }
  Response (S3):  { mode: "presigned", url: "https://...", key: "uploads/images/2026/04/xxx.jpg", headers: {...} }
  Response (local): { mode: "server", upload_url: "/admin/upload/image" }

# Клиент загружает файл напрямую в S3:
PUT <presigned_url> с Content-Type и файлом

POST /api/upload/confirm
  Body: { key, original_name, content_type, size, collection?, entity_type?, entity_id? }
  Response: { url: "https://bucket.s3.../uploads/images/...", name: "photo.jpg" }
```

### Frontend flow

```
usePresignedUpload(file, options) →
  1. POST /presigned-url → получить URL + mode
  2. if mode === "presigned": PUT файл в S3 (xhr с onprogress)
     if mode === "server": POST файл на сервер (существующий flow)
  3. POST /confirm → получить финальный URL
  4. emit URL
```

## UI Components

- Изменений в UI-структуре нет — используются существующие компоненты
- `ImageUploadCrop.vue` — внутренне переключается на presigned upload
- Добавляется полоса прогресса (уже есть spinner, заменяем на progress bar)

## Верификация

Для каждого шага:
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=upload` (маршруты)
- `source docker/.env.local && docker exec ${APP_NAME}_fpm npx vue-tsc --noEmit` (типы)
- `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` (сборка)
