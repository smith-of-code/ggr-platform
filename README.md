# GGR Platform — Образовательная платформа ВШГР

Образовательная платформа «Высшая школа гостеприимного развития» для подготовки специалистов в области гостеприимства городов Росатома.

## Стек технологий

- **Backend:** Laravel 12, PHP 8.3
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **UI Kit:** rosatom-ggr-ui-kit
- **Database:** PostgreSQL 16
- **Cache / Queues / Sessions:** Redis
- **Queue Worker:** Laravel Horizon (Supervisor)
- **Web Server:** Nginx
- **Containerization:** Docker, Docker Compose

## Быстрый старт

### Требования

- Docker Desktop (или Docker Engine + Docker Compose)
- Git

### 1. Клонирование

```bash
git clone git@github.com:smith-of-code/ggr-platform.git
cd ggr-platform
```

### 2. Запуск

```bash
cd docker

# Локальная разработка
./run.sh local

# Production
./run.sh prod
```

Скрипт автоматически выполнит:
- Создание Docker-сетей
- Сборку контейнеров
- Копирование env-файла в `.env`
- `composer update`, `npm update`, `npm run build`
- `php artisan key:generate`, `migrate`, `storage:link`

Первый запуск занимает ~5-7 минут.

### 3. Сидирование БД

```bash
cd docker
./seed.sh local
```

### 4. Остановка

```bash
cd docker
./stop.sh
```

### 5. Доступ (local)

| URL | Описание |
|-----|----------|
| http://localhost:40101 | Главная страница |
| http://localhost:40101/lms/vshgr-2026/login | Вход в LMS |
| http://localhost:40101/lms-admin/vshgr-2026/users | Админ-панель |
| http://localhost:8027 | Mailpit — перехват email |

### Учётные записи по умолчанию

| Роль | Email | Пароль |
|------|-------|--------|
| Администратор | `admin@rosatom-travel.ru` | `password` |
| Участник | `student@rosatom-travel.ru` | `password` |

## Структура проекта

```
├── app/
│   ├── Http/Controllers/Lms/         # Контроллеры LMS
│   ├── Models/Lms/                   # Модели LMS
│   └── Http/Middleware/              # Middleware
├── resources/js/
│   ├── Components/                   # Переиспользуемые компоненты
│   ├── Layouts/                      # Макеты (LmsLayout, LmsAdminLayout)
│   └── Pages/Lms/                    # Страницы LMS
├── routes/lms.php                    # Маршруты LMS
├── docker/                           # Docker-инфраструктура
│   ├── docker-compose.yml            # Базовая конфигурация
│   ├── docker-compose.override.yml   # Override для local
│   ├── docker-compose.prod.yml       # Override для production
│   ├── Fpm.Dockerfile                # PHP-FPM образ
│   ├── run.sh / stop.sh / seed.sh    # Скрипты управления
│   ├── .env.local / .env.prod        # Переменные окружения
│   └── nginx/ supervisor/ ssl/       # Конфигурации сервисов
├── public/                           # Статика
├── .github/workflows/                # CI/CD (lint, tests, deploy)
└── design-references/                # Дизайн-референсы
```

## Функциональность

### LMS (участник)
- Каталог курсов с модулями и расписанием
- Прохождение этапов: теория, видео, тесты, SCORM, практические задания
- Автоматическое открытие модулей по датам
- Загрузка файлов в заданиях
- Геймификация и рейтинг
- Траектории обучения

### Админ-панель
- Управление пользователями (создание, импорт из Excel/CSV)
- Система ролей (участник, куратор, лидер, эксперт, администратор)
- Назначение курсов индивидуально и по ролям
- Управление курсами, модулями, тестами, заданиями
- Загрузка SCORM-пакетов
- Управление видеоматериалами (Rutube, YouTube)
- Группы, траектории, база знаний

### Поиск и навигация
- Быстрый поиск на всех страницах
- Пагинация списков
- Фильтрация по ролям, группам, статусам

## Разработка

### Вход в контейнер

```bash
docker exec -it vshgr-platform_fpm bash
```

### Artisan-команды

```bash
docker exec vshgr-platform_fpm php artisan tinker
docker exec vshgr-platform_fpm php artisan migrate
docker exec vshgr-platform_fpm php artisan cache:clear
```

### Фронтенд (hot reload)

```bash
docker exec vshgr-platform_fpm npm run dev
```

### Пересборка фронтенда

```bash
docker exec vshgr-platform_fpm npm run build
```

## UI Kit

Проект использует `rosatom-ggr-ui-kit` из приватного npm-реестра. Для установки:

```bash
# В файле .npmrc указан реестр:
@rosatom-ggr:registry=https://nexus.wizandr.ru/repository/npm-private/
```

## Документация

- [Docker-инфраструктура](docker/DOCKER.md) — подробное описание контейнеров, сетей, томов, переменных окружения

## Лицензия

Проприетарное ПО. Все права защищены.
