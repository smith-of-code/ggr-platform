# Docker-инфраструктура проекта VSHGR Platform

## Обзор

Проект использует Docker Compose для оркестрации контейнеров. Инфраструктура поддерживает два окружения — **local** (разработка) и **production** — с единой базовой конфигурацией и окружение-специфичными override-файлами.

### Стек технологий

| Компонент | Технология | Версия |
|-----------|-----------|--------|
| Web-сервер | Nginx | Alpine (latest) |
| PHP | PHP-FPM | 8.3 |
| База данных | PostgreSQL | 16 |
| Кеш / Очереди / Сессии | Redis | Alpine (latest) |
| Очереди (worker) | Laravel Horizon | via Supervisor |
| Node.js | Node.js | 20.x |
| Почта (local) | Mailpit | latest |

---

## Структура файлов

```
app/docker/
├── docker-compose.yml              # Базовая конфигурация (общие сервисы)
├── docker-compose.override.yml     # Override для локальной разработки (загружается автоматически)
├── docker-compose.prod.yml         # Override для production
├── Fpm.Dockerfile                  # Dockerfile для PHP-FPM контейнера
├── php.ini                         # Кастомные настройки PHP
├── .env.local                      # Переменные окружения (локальная разработка)
├── .env.prod                       # Переменные окружения (production)
├── run.sh                          # Скрипт запуска окружения
├── stop.sh                         # Скрипт остановки окружения
├── seed.sh                         # Скрипт запуска сидеров БД
├── nginx/
│   ├── local/templates/
│   │   └── default.conf.template   # Конфигурация Nginx для local
│   └── prod/templates/
│       └── default.conf.template   # Конфигурация Nginx для production
├── supervisor/
│   ├── supervisord.conf            # Основная конфигурация Supervisor
│   └── horizon.conf                # Конфигурация процесса Laravel Horizon
└── ssl/
    └── gitkeep                     # Директория для SSL-сертификатов (production)
```

---

## Сервисы

### Nginx

Обратный прокси, обслуживает статику и проксирует PHP-запросы в FPM-контейнер через FastCGI (порт 9000).

| Параметр | Local | Production |
|----------|-------|------------|
| HTTP порт | `40101:80` | `40180:80` |
| HTTPS порт | `40102:443` | `40143:443` |
| Конфиг | `nginx/local/templates/` | `nginx/prod/templates/` |
| SSL | — | `./ssl:/etc/ssl/certs` |
| Логи | Только stdout | `../../${APP_NAME}-nginx_log` |

Nginx подключен к сетям `service_bridge` (внутренняя) и `proxy` (внешняя, для reverse proxy).

### PHP-FPM

Основной application-контейнер. Собирается из `Fpm.Dockerfile` на базе `php:8.3-fpm`.

**Установленные PHP-расширения:** zip, pdo, pdo_pgsql, mbstring, exif, pcntl, bcmath, gd, redis, xdebug.

**Дополнительно установлено:**
- Composer (latest)
- Node.js 20.x + npm
- Supervisor (для Horizon)

Контейнер создаёт пользователя `laravel` с UID/GID хост-системы, чтобы избежать проблем с правами доступа к файлам.

**PHP-настройки** (`php.ini`):

| Параметр | Значение |
|----------|----------|
| `upload_max_filesize` | 100M |
| `post_max_size` | 100M |
| `memory_limit` | 512M |
| `max_execution_time` | 600 сек |
| `date.timezone` | UTC |

**Xdebug** установлен, но по умолчанию отключён в `php.ini`. Для активации раскомментируйте секцию `[xdebug]`.

В **local-окружении** FPM получает дополнительные переменные для Xdebug и проброс порта Vite dev-server.

### PostgreSQL

Реляционная БД. Запускается только через override-файлы (не включена в базовый `docker-compose.yml`).

| Параметр | Local | Production |
|----------|-------|------------|
| Порт | `40005:5432` | `40103:5432` |
| Данные | `../../${APP_NAME}-data/dbdata` | `../../${APP_NAME}-data/dbdata` |

Креденшалы задаются через env-переменные `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`.

### Redis

Кеш, хранилище сессий, брокер очередей.

| Параметр | Local | Production |
|----------|-------|------------|
| Порт | `40004:6379` (проброшен наружу) | Только внутренний |
| Данные | `../../${APP_NAME}-data/redis_data` | `../../${APP_NAME}-data/redis_data` |

### Mailpit (только local)

SMTP-мок для перехвата email во время разработки.

| Порт | Назначение |
|------|-----------|
| `1027:1025` | SMTP |
| `8027:8025` | Web UI для просмотра писем |

В production используется реальный SMTP-сервер (Outlook).

### Laravel Horizon (Supervisor)

Воркер очередей запускается внутри FPM-контейнера через Supervisor. Horizon стартует автоматически, перезапускается при сбоях. Логи пишутся в `/var/www/storage/logs/horizon.log`.

---

## Сети

| Имя | Тип | Назначение |
|-----|-----|-----------|
| `${DOCKER_NETWORK_NAME}_bridge` | external, с явной подсетью | Внутренняя связь между контейнерами проекта |
| `proxy` | external | Подключение к внешнему reverse proxy |

Подсеть bridge-сети: `171.74.0.0/16` (задаётся в env-файле).

Сети создаются как **external** — скрипт `run.sh` создаёт их перед запуском compose.

---

## Тома (Volumes)

Все данные хранятся в bind-mount директориях **за пределами репозитория**:

| Том | Путь на хосте | Назначение |
|-----|---------------|-----------|
| Код приложения | `../` → `/var/www` | Исходный код Laravel |
| Данные PostgreSQL | `../../${APP_NAME}-data/dbdata` | Персистентное хранилище БД |
| Данные Redis | `../../${APP_NAME}-data/redis_data` | Персистентное хранилище Redis |
| Логи Nginx | `../../${APP_NAME}-nginx_log` | Логи web-сервера |
| SSL-сертификаты | `./ssl` → `/etc/ssl/certs` | Только production |

---

## Управление окружением

### Запуск

```bash
cd app/docker

# Локальная разработка
./run.sh local

# Production
./run.sh prod
```

Скрипт `run.sh` выполняет следующие действия:

1. Загружает env-файл (`.env.local` или `.env.prod`)
2. Копирует env-файл в `app/.env` (корень Laravel)
3. Создаёт Docker-сеть с заданной подсетью
4. Определяет окружение по `APP_ENV`:
   - `production` → использует `docker-compose.yml` + `docker-compose.prod.yml`
   - Всё остальное → использует `docker-compose.yml` + `docker-compose.override.yml` (автоматически)
5. Запускает контейнеры с `--build`
6. Выполняет post-deploy команды:
   - `composer update`
   - `php artisan storage:link`
   - `php artisan key:generate`
   - `php artisan migrate`
   - `npm update` + `npm run build`
   - Удаляет `public/hot` (Laravel использует собранные assets)

### Остановка

```bash
cd app/docker
./stop.sh
```

Останавливает и удаляет все контейнеры проекта (по префиксу `${APP_NAME}`), удаляет Docker-сеть.

### Сидирование БД

```bash
cd app/docker
./seed.sh local   # или prod
```

---

## Переменные окружения

### Docker-специфичные переменные

| Переменная | Описание | Пример |
|-----------|----------|--------|
| `APP_NAME` | Префикс для имён контейнеров и сетей | `vshgr-platform` |
| `DOCKER_NETWORK_NAME` | Имя Docker-сети | `vshgr-platform` |
| `DOCKER_NETWORK_SUBNET` | Подсеть Docker bridge | `171.74.0.0/16` |
| `XDEBUG_CONFIG_REMOTE_HOST` | IP хоста для Xdebug | `171.74.0.1` |
| `USER_ID` / `GROUP_ID` | UID/GID хост-пользователя (авто) | — |
| `APP_ENV` | Определяет набор compose-файлов | `local` / `production` |
| `VITE_DEV_SERVER_PORT` | Порт Vite dev-server | `5173` (local) / `5174` (prod) |

### Ключевые отличия local vs production

| Параметр | Local | Production |
|----------|-------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_URL` | `http://localhost` | `https://vshgr-platform.us` |
| Почта | Mailpit (перехват) | Outlook SMTP |
| SMS | WireMock (мок) | ClickSend (реальный) |
| Xdebug | Настроен | Не используется |
| Compose-файлы | `yml` + `override.yml` | `yml` + `prod.yml` |

---

## Схема взаимодействия контейнеров

```
                    ┌─────────────┐
                    │   Client    │
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
              ┌─────│    Nginx    │─────┐
              │     │  (Alpine)   │     │
              │     └──────┬──────┘     │
              │            │ FastCGI    │
              │     ┌──────▼──────┐     │
              │     │   PHP-FPM   │     │
              │     │  (PHP 8.3)  │     │
              │     │ + Supervisor│     │
              │     │ + Horizon   │     │
              │     └──┬─────┬────┘     │
              │        │     │          │
         ┌────▼────┐   │  ┌──▼──────┐   │
         │  proxy  │   │  │  Redis  │   │
         │(network)│   │  │ (Alpine)│   │
         └─────────┘   │  └─────────┘   │
                       │                │
                ┌──────▼──────┐         │
                │ PostgreSQL  │         │
                │    (16)     │         │
                └─────────────┘         │
                                        │
                ┌───────────────┐       │
                │   Mailpit     │───────┘
                │ (local only)  │
                └───────────────┘

        ─── service_bridge network ───
```

---

## Частые операции

### Вход в контейнер FPM

```bash
docker exec -it vshgr-platform_fpm bash
```

### Выполнение Artisan-команд

```bash
docker exec vshgr-platform_fpm php artisan <command>
```

### Просмотр логов

```bash
# Логи контейнера
docker logs vshgr-platform_fpm

# Логи Horizon
docker exec vshgr-platform_fpm cat /var/www/storage/logs/horizon.log

# Логи Nginx
cat ../../vshgr-platform-nginx_log/access.log
cat ../../vshgr-platform-nginx_log/error.log
```

### Пересборка FPM-контейнера

```bash
cd app/docker
docker compose --env-file=.env.local --project-name=vshgr-platform up -d --build fpm
```

### Включение Xdebug

Раскомментировать секцию в `php.ini`:

```ini
[xdebug]
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.idekey=PHPSTORM
```

Затем пересобрать FPM-контейнер.
