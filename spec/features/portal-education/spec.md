# Модуль 5. ВШГР — Информационная часть

## Статус: Реализован

## Описание
Раздел о Высшей школе гостеприимного развития: каталог образовательных продуктов, заявки на обучение.

## Публичная часть

### Главная ВШГР (Education/Index)
- Hero с брендингом ВШГР
- Описание миссии
- Сетка карточек продуктов (duration, format бейджи)
- Блок анонсов (последние 3 поста категории announcements)
- CTA-секция с формой заявки

### Детальная страница продукта (Education/Show)
- Навигация назад
- Hero с изображением и заголовком
- Бейджи: длительность, формат, стоимость
- Полное описание (HTML)
- Целевая аудитория
- Форма заявки (type=program_info)

## Форма заявки
- Поля: имя, email, телефон, сообщение
- POST `/applications` с типом `program_info`
- Flash-сообщение об успехе

## Админка (Admin/EducationProducts)
- CRUD для образовательных продуктов
- Поля: title, slug, description, content, image, duration, format, target_audience, price_info, position, is_active

## Модель EducationProduct
- `title`, `slug`, `description`, `content`, `image`
- `duration` — строка (напр. «3 месяца»)
- `format` — строка (напр. «Онлайн», «Очно»)
- `target_audience`, `price_info`
- `position`, `is_active`

## Маршруты
- `GET /vshgr` — каталог (публичный)
- `GET /vshgr/{slug}` — продукт (публичный)
- Admin CRUD: `/admin/education-products/*`

## Файлы
- `resources/js/Pages/Education/Index.vue`
- `resources/js/Pages/Education/Show.vue`
- `resources/js/Pages/Admin/EducationProducts/Index.vue`
- `resources/js/Pages/Admin/EducationProducts/Form.vue`
- `app/Http/Controllers/EducationController.php`
- `app/Http/Controllers/Admin/EducationProductController.php`
- `app/Models/EducationProduct.php`
