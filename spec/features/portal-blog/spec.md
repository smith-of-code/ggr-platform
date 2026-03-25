# Модуль 7. Блог

## Статус: Реализован

## Описание
Публикация новостей, статей и материалов о проекте. Категории, теги, админка.

## Публичная часть

### Каталог статей (Blog/Index)
- Сетка карточек (3 колонки)
- Фильтр по категориям (вкладки)
- Пагинация (12 на странице)
- Каждая карточка: изображение, бейдж категории, заголовок, excerpt, дата

### Детальная страница (Blog/Show)
- Hero с фоновым изображением и градиентом
- Бейдж категории, дата публикации
- HTML контент (prose formatting)
- Теги (badges)
- Связанные статьи (до 3, той же категории)

## Категории
- `news` — Новости программы
- `announcements` — Анонсы
- `partner_articles` — Статьи партнёров

## Админка (Admin/Blog)

### Список статей
- Таблица: превью, заголовок, категория, статус, дата, действия
- Toggle публикации
- Удаление с подтверждением
- Пагинация

### Форма создания/редактирования
- Поля: заголовок, slug (автогенерация), категория (select), excerpt, контент, URL изображения, теги (через запятую), публикация (чекбокс)
- При публикации auto-set `published_at`

## Модель Post
- `title`, `slug`, `excerpt`, `content`, `image`
- `category` — ключ из CATEGORIES
- `tags` — JSON массив
- `is_published`, `published_at`

## Маршруты
- `GET /blog` — каталог (публичный)
- `GET /blog/{slug}` — статья (публичный)
- `GET /admin/blog` — список (admin)
- `GET /admin/blog/create` — создание (admin)
- `POST /admin/blog` — сохранение (admin)
- `GET /admin/blog/{post}/edit` — редактирование (admin)
- `PUT /admin/blog/{post}` — обновление (admin)
- `DELETE /admin/blog/{post}` — удаление (admin)
- `PATCH /admin/blog/{post}/toggle-publish` — публикация (admin)

## Файлы
- `resources/js/Pages/Blog/Index.vue`
- `resources/js/Pages/Blog/Show.vue`
- `resources/js/Pages/Admin/Blog/Index.vue`
- `resources/js/Pages/Admin/Blog/Form.vue`
- `app/Http/Controllers/BlogController.php`
- `app/Http/Controllers/Admin/BlogController.php`
- `app/Models/Post.php`
