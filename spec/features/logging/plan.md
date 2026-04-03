# План: Логирование в базу данных

## Milestones

1. **Инфраструктура данных** — миграции + модели для `activity_logs` и `exception_logs`
2. **Конфигурация** — `config/activity-logging.php` с флагами управления
3. **Сервис записи** — `ActivityLogService` для безопасной записи логов
4. **Middleware активности** — `LogUserActivity` для перехвата HTTP-запросов
5. **Exception handler** — кастомизация `bootstrap/app.php` для записи исключений
6. **Регистрация** — подключение middleware в web-группу
7. **Очистка** — Artisan-команда + scheduling для удаления старых записей

## Схема таблиц

### activity_logs

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigIncrements | PK |
| user_id | foreignId nullable | Ссылка на users |
| method | string(10) | HTTP method |
| url | text | Полный URL запроса |
| route_name | string nullable | Имя маршрута |
| ip_address | string(45) nullable | IP клиента |
| user_agent | text nullable | User-Agent |
| status_code | unsignedSmallInteger nullable | HTTP-код ответа |
| duration_ms | unsignedInteger nullable | Время выполнения в мс |
| created_at | timestamp | Время события |

### exception_logs

| Колонка | Тип | Описание |
|---------|-----|----------|
| id | bigIncrements | PK |
| user_id | foreignId nullable | Ссылка на users |
| exception_class | string | Класс исключения |
| message | text | Сообщение |
| code | string(50) nullable | Код исключения |
| file | text | Файл |
| line | unsignedInteger | Строка |
| trace | longText nullable | Stack trace |
| url | text nullable | URL запроса (если HTTP) |
| method | string(10) nullable | HTTP method |
| ip_address | string(45) nullable | IP клиента |
| status_code | unsignedSmallInteger nullable | HTTP status code |
| created_at | timestamp | Время события |

## Конфигурация (config/activity-logging.php)

```php
return [
    'enabled' => env('ACTIVITY_LOG_ENABLED', true),
    'log_422' => env('ACTIVITY_LOG_422', false),
    'excluded_paths' => [
        'up',
        'horizon/*',
        'livewire/*',
    ],
    'retention_days' => env('ACTIVITY_LOG_RETENTION_DAYS', 90),
];
```

## Верификация

Все проверки через Docker по паттерну из spec-continuation:
`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`
