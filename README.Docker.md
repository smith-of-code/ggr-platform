# Запуск в Docker

## Быстрый старт

```bash
# Из корня проекта
docker-compose up -d --build

# Подождать ~30 сек, пока поднимется БД и выполнятся миграции
# Сайт: http://localhost:8080
```

## Доступ

- **Сайт:** http://localhost:8080
- **Админка:** http://localhost:8080/admin
- **Логин:** admin@rosatom-travel.ru
- **Пароль:** password

## Команды

```bash
# Остановить
docker-compose down

# Пересобрать (после изменений в коде)
docker-compose up -d --build

# Логи
docker-compose logs -f app

# Выполнить artisan в контейнере
docker-compose exec app php artisan migrate
```
