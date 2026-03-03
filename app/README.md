# Гостеприимные города Росатома — rosatom-travel.ru

MVP информационной платформы на Laravel + Vue (Inertia.js).

## Стек

- **Backend:** Laravel 12, PHP 8.3
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **База данных:** SQLite (по умолчанию) / PostgreSQL

## Админ-панель

- URL: `/admin`
- Логин: `admin@rosatom-travel.ru`
- Пароль: `password`

После входа доступны: дашборд, CRUD городов, CRUD туров, заявки, экспорт в CSV.

## Установка

```bash
# Клонирование и переход в директорию
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

## Запуск

### Локально (без Docker)

```bash
# Терминал 1 — Laravel
php artisan serve

# Терминал 2 — Vite (для разработки)
npm run dev
```

Сайт: http://localhost:8000  
Админ-панель: http://localhost:8000/admin

### Docker

```bash
# Из корня проекта
docker-compose up -d

# Миграции внутри контейнера
docker-compose exec app php artisan migrate --seed
```

Сайт: http://localhost:8080

## Структура MVP

- **Главная** — блоки, статистика, популярные туры
- **Города** — каталог и карточка города
- **Туры** — каталог с фильтрами, карточка тура
- **Заявки** — форма на тур (без ЛК)
- **Админ** — дашборд, список заявок

## Дальнейшее развитие

См. КП_rosatom-travel_План_разработки.md в корне проекта.
