# Гостеприимные города Росатома — Платформа ВШГР

Информационная платформа на Laravel + Vue (Inertia.js).

## Стек

- **Backend:** Laravel 12, PHP 8.3
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **База данных:** PostgreSQL 16
- **Кеш / Очереди / Сессии:** Redis
- **Очереди:** Laravel Horizon (Supervisor)
- **Web-сервер:** Nginx

## Быстрый старт (Docker)

```bash
cd app/docker

# Локальная разработка
./run.sh local

# Production
./run.sh prod
```

Скрипт автоматически выполнит: создание Docker-сетей, сборку контейнеров, установку зависимостей (Composer + npm), миграции, сборку фронтенда.

**Остановка:**

```bash
cd app/docker
./stop.sh
```

**Сидирование БД:**

```bash
cd app/docker
./seed.sh local
```

## Доступы (local)

| Ресурс | URL |
|--------|-----|
| Сайт | http://localhost:40101 |
| Админ-панель | http://localhost:40101/admin |
| Mailpit (почта) | http://localhost:8027 |

## Админ-панель

- URL: `/admin`
- Логин: `admin@rosatom-travel.ru`
- Пароль: `password`

После входа доступны: дашборд, CRUD городов, CRUD туров, заявки, экспорт в CSV.

## Установка без Docker

```bash
cd app

# Установка зависимостей
composer install
npm install

# Настройка окружения
cp .env.example .env
php artisan key:generate

# Миграции и сиды
php artisan migrate --seed

# Сборка фронтенда
npm run build
```

### Запуск dev-сервера

```bash
# Терминал 1 — Laravel
php artisan serve

# Терминал 2 — Vite HMR
npm run dev
```

## Частые команды (Docker)

```bash
# Вход в контейнер
docker exec -it vshgr-platform_fpm bash

# Artisan-команды
docker exec vshgr-platform_fpm php artisan <command>

# Логи Horizon
docker exec vshgr-platform_fpm cat /var/www/storage/logs/horizon.log
```

## Структура MVP

- **Главная** — блоки, статистика, популярные туры
- **Города** — каталог и карточка города
- **Туры** — каталог с фильтрами, карточка тура
- **Заявки** — форма на тур (без ЛК)
- **Админ** — дашборд, список заявок

## Docker-инфраструктура

Подробная документация по Docker: [app/docker/DOCKER.md](docker/DOCKER.md)
