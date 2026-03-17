# GGR Platform — Образовательная платформа ВШГР

Образовательная платформа «Высшая школа гостеприимного развития» для подготовки специалистов в области гостеприимства городов Росатома.

## Стек технологий

- **Backend:** Laravel 11, PHP 8.3
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS
- **UI Kit:** rosatom-ggr-ui-kit
- **Database:** PostgreSQL 16
- **Cache:** Redis
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

### 2. Настройка окружения

```bash
cp .env.docker.example app/.env
```

Отредактируйте `app/.env` при необходимости. Значения по умолчанию:

| Параметр | Значение |
|----------|----------|
| `DB_HOST` | `db` |
| `DB_DATABASE` | `rosatom` |
| `DB_USERNAME` | `rosatom` |
| `DB_PASSWORD` | `secret` |
| `REDIS_HOST` | `redis` |

### 3. Запуск

```bash
docker compose up -d
```

Дождитесь сборки контейнеров (первый запуск ~3-5 минут).

### 4. Инициализация приложения

```bash
# Установка зависимостей PHP
docker compose exec app composer install

# Генерация ключа приложения
docker compose exec app php artisan key:generate

# Миграции и начальные данные
docker compose exec app php artisan migrate --seed

# Установка JS-зависимостей и сборка фронтенда
docker compose exec app npm install
docker compose exec app npm run build

# Линк storage
docker compose exec app php artisan storage:link

# Права на директории
docker compose exec app chmod -R 777 storage bootstrap/cache
```

### 5. Доступ

| URL | Описание |
|-----|----------|
| `http://localhost:8081` | Главная страница |
| `http://localhost:8081/lms/vshgr-2026/login` | Вход в LMS |
| `http://localhost:8081/lms-admin/vshgr-2026/users` | Админ-панель |

### Учётные записи по умолчанию

| Роль | Email | Пароль |
|------|-------|--------|
| Администратор | `admin@rosatom-travel.ru` | `password` |
| Участник | `student@rosatom-travel.ru` | `password` |

## Структура проекта

```
├── app/                          # Laravel-приложение
│   ├── app/
│   │   ├── Http/Controllers/Lms/ # Контроллеры LMS
│   │   ├── Models/Lms/           # Модели LMS
│   │   └── Http/Middleware/      # Middleware
│   ├── resources/js/
│   │   ├── Components/           # Переиспользуемые компоненты
│   │   ├── Layouts/              # Макеты (LmsLayout, LmsAdminLayout)
│   │   └── Pages/Lms/            # Страницы LMS
│   ├── routes/lms.php            # Маршруты LMS
│   └── public/                   # Статика
├── docker/                       # Docker-конфигурации
├── docker-compose.yml            # Docker Compose
└── .env.docker.example           # Шаблон переменных окружения
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

### Фронтенд (hot reload)

```bash
docker compose exec app npm run dev
```

### Artisan-команды

```bash
docker compose exec app php artisan tinker
docker compose exec app php artisan migrate
docker compose exec app php artisan cache:clear
```

### Пересборка фронтенда

```bash
docker compose exec app npm run build
```

## UI Kit

Проект использует `rosatom-ggr-ui-kit` из приватного npm-реестра. Для установки:

```bash
# В файле app/.npmrc указан реестр:
@rosatom-ggr:registry=https://nexus.wizandr.ru/repository/npm-private/
```

## Лицензия

Проприетарное ПО. Все права защищены.
