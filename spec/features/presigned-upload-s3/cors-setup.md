# CORS-конфигурация S3 bucket для presigned uploads

## Требования

Для работы presigned PUT upload из браузера, S3 bucket должен иметь CORS-конфигурацию, разрешающую:
- **PUT** запросы с домена приложения
- Заголовок `Content-Type`

## Конфигурация

### AWS S3

В AWS Console → S3 → Bucket → Permissions → CORS configuration:

```json
[
  {
    "AllowedHeaders": ["Content-Type"],
    "AllowedMethods": ["PUT"],
    "AllowedOrigins": ["https://your-domain.ru"],
    "ExposeHeaders": ["ETag"],
    "MaxAgeSeconds": 3600
  }
]
```

### AWS CLI

```bash
aws s3api put-bucket-cors --bucket YOUR_BUCKET_NAME --cors-configuration '{
  "CORSRules": [
    {
      "AllowedHeaders": ["Content-Type"],
      "AllowedMethods": ["PUT"],
      "AllowedOrigins": ["https://your-domain.ru"],
      "ExposeHeaders": ["ETag"],
      "MaxAgeSeconds": 3600
    }
  ]
}'
```

### Yandex Cloud Object Storage

Аналогичная конфигурация через консоль YC или CLI:

```bash
yc storage bucket update --name YOUR_BUCKET_NAME \
  --cors '[{"allowed_origins": ["https://your-domain.ru"], "allowed_methods": ["PUT"], "allowed_headers": ["Content-Type"], "expose_headers": ["ETag"], "max_age_seconds": 3600}]'
```

## Безопасность

- `AllowedOrigins` — указать **только** домен production (не `*`)
- `AllowedMethods` — только `PUT` (не `GET`, `DELETE`)
- `AllowedHeaders` — только `Content-Type` (минимум необходимого)
- Presigned URL уже содержит авторизацию — CORS только разрешает browser cross-origin запрос

## Проверка

```bash
curl -I -X OPTIONS \
  -H "Origin: https://your-domain.ru" \
  -H "Access-Control-Request-Method: PUT" \
  -H "Access-Control-Request-Headers: Content-Type" \
  https://YOUR_BUCKET.s3.amazonaws.com/test
```

Ответ должен содержать:
```
Access-Control-Allow-Origin: https://your-domain.ru
Access-Control-Allow-Methods: PUT
```
