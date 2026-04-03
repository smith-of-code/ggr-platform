# Задачи: Логирование в базу данных

## Task 1: Миграции для таблиц логирования

- **Цель**: Создать таблицы `activity_logs` и `exception_logs`
- **Scope**: `database/migrations/`
- **DoD**: Миграции проходят без ошибок; таблицы созданы с индексами на `user_id`, `created_at`, `status_code`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## Task 2: Eloquent-модели

- **Цель**: Создать модели `ActivityLog` и `ExceptionLog`
- **Scope**: `app/Models/ActivityLog.php`, `app/Models/ExceptionLog.php`
- **DoD**: Модели с fillable, casts, связью `belongsTo(User::class)`, без timestamps (только created_at)
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="new App\Models\ActivityLog"`

## Task 3: Конфигурация логирования

- **Цель**: Создать `config/activity-logging.php` с управляющими флагами
- **Scope**: `config/activity-logging.php`
- **DoD**: Конфиг содержит `enabled`, `log_422`, `excluded_paths`, `retention_days`; значения подтягиваются из env
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="dd(config('activity-logging'))"`

## Task 4: Сервис ActivityLogService

- **Цель**: Создать сервис для безопасной записи логов в БД
- **Scope**: `app/Services/ActivityLogService.php`
- **DoD**: Методы `logActivity(Request, Response)`, `logException(Throwable, ?Request)` с try/catch обёрткой (silent fail); проверка `config('activity-logging.enabled')`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="app(App\Services\ActivityLogService::class)"`

## Task 5: Middleware LogUserActivity

- **Цель**: Создать middleware для записи HTTP-активности аутентифицированных пользователей
- **Scope**: `app/Http/Middleware/LogUserActivity.php`
- **DoD**: Перехватывает response (terminate), пропускает excluded_paths, фильтрует 422 по конфигу, вызывает `ActivityLogService::logActivity()`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="new App\Http\Middleware\LogUserActivity(app(App\Services\ActivityLogService::class))"`

## Task 6: Exception handler — логирование исключений

- **Цель**: Настроить запись исключений в БД через `bootstrap/app.php`
- **Scope**: `bootstrap/app.php`
- **DoD**: В `withExceptions` добавлен `reportable` callback; исключения пишутся в `exception_logs`; фильтрация 422 по конфигу
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="report(new \RuntimeException('test'))"`

## Task 7: Регистрация middleware

- **Цель**: Подключить `LogUserActivity` в web middleware группу
- **Scope**: `bootstrap/app.php`
- **DoD**: Middleware добавлен через `$middleware->web(append: [...])`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --columns=middleware | head -20`

## Task 8: Artisan-команда очистки логов

- **Цель**: Создать команду `logs:cleanup` для удаления записей старше `retention_days`
- **Scope**: `app/Console/Commands/CleanupActivityLogs.php`
- **DoD**: Удаляет записи из обеих таблиц; выводит количество удалённых; зарегистрирована в schedule
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan logs:cleanup --dry-run`

## Task 9: Финальная верификация

- **Цель**: Проверить полный цикл: запрос → activity_log, исключение → exception_log, cleanup
- **Scope**: все файлы фичи
- **DoD**: Записи появляются в таблицах при HTTP-запросах; исключения логируются; cleanup удаляет старые записи
- **Verify**: ручная проверка через tinker + curl
