# manager-subscribe — Задачи

## Задача 1: Создать Admin\BlogSubscriberController

- **Цель**: Backend-контроллер для CRUD подписчиков
- **Скоуп**: `app/Http/Controllers/Admin/BlogSubscriberController.php`
- **DoD**: Контроллер содержит методы `index` (с поиском и пагинацией), `store` (валидация email, unique), `destroy`, `toggleActive`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=admin.blog-subscribers`

## Задача 2: Зарегистрировать маршруты

- **Цель**: Добавить admin-маршруты для подписчиков
- **Скоуп**: `routes/web.php`
- **DoD**: `Route::resource('blog-subscribers', ...)` + `Route::patch('blog-subscribers/{blog_subscriber}/toggle-active', ...)`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=admin.blog-subscribers`

## Задача 3: Создать страницу списка подписчиков (Index.vue)

- **Цель**: Vue-страница с таблицей подписчиков
- **Скоуп**: `resources/js/Pages/Admin/Subscribers/Index.vue`
- **DoD**: Таблица с колонками (Email, Статус, Дата подписки, Действия), пагинация, toggle активности, кнопка удаления
- **Verify**: Визуальная проверка в браузере `/admin/blog-subscribers`

## Задача 4: Добавить форму создания подписчика

- **Цель**: Inline-форма для добавления email прямо на странице списка
- **Скоуп**: `resources/js/Pages/Admin/Subscribers/Index.vue`
- **DoD**: Поле email + кнопка «Добавить», валидация на фронте (формат email), серверные ошибки отображаются
- **Verify**: Попытка добавить подписчика через интерфейс

## Задача 5: Добавить статистику подписчиков

- **Цель**: Блок со счётчиками над таблицей
- **Скоуп**: `app/Http/Controllers/Admin/BlogSubscriberController.php`, `resources/js/Pages/Admin/Subscribers/Index.vue`
- **DoD**: Отображаются: всего, активных, приостановленных
- **Verify**: Визуальная проверка — счётчики корректны

## Задача 6: Добавить поиск по email

- **Цель**: Фильтрация списка по email
- **Скоуп**: `app/Http/Controllers/Admin/BlogSubscriberController.php`, `resources/js/Pages/Admin/Subscribers/Index.vue`
- **DoD**: Поле поиска фильтрует подписчиков (серверная фильтрация через query-параметр `search`)
- **Verify**: Ввод email в поле поиска фильтрует таблицу

## Задача 7: Добавить ссылку в навигацию админки

- **Цель**: Пункт «Подписчики» в сайдбаре админки
- **Скоуп**: Layout-файл админки (`AdminLayout.vue` или аналог)
- **DoD**: Ссылка ведёт на `admin.blog-subscribers.index`, отмечается активной при нахождении на странице
- **Verify**: Визуальная проверка навигации

## Задача 8: Финальная верификация

- **Цель**: Проверка всего flow целиком
- **Скоуп**: Все файлы фичи
- **DoD**: Добавление, приостановка, активация, удаление, поиск — работают без ошибок
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=admin.blog-subscribers` + ручная проверка в браузере
