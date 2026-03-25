# Модуль 6. Исследования и «Атомы вкуса»

## Статус: Реализован

## Описание
Исследования туристического потенциала атомных городов и книга атомных рецептов.

## Исследования

### Каталог (Research/Index)
- Сетка карточек с фильтром по городу
- Пагинация (12 на странице)
- Каждая карточка: изображение, заголовок, город, год, описание

### Детальная страница (Research/Show)
- Навигация, заголовок, город, год
- Секция методологии
- HTML контент
- Итоги исследования
- Ссылка на PDF-файл

### Админка (Admin/Research)
- CRUD: title, slug, city_id, year, methodology, description, content, results_summary, image, pdf_file, is_published

## Книга атомных рецептов

### Каталог (Recipes/Index)
- Тёплый дизайн (amber/orange акценты)
- Фильтр по городу
- Карточки: фото, название, город, сложность, время, порции
- Пагинация

### Детальная страница (Recipes/Show)
- Информация: время, сложность, порции
- Ингредиенты (из JSON)
- Инструкция (HTML)
- Ссылка на город

### Админка (Admin/Recipes)
- CRUD: title, slug, city_id, cooking_time, difficulty, servings, ingredients (динамический список), description, content, image, is_published

## Модели
- `Research` — исследования, связь с City
- `Recipe` — рецепты, связь с City, ingredients как JSON

## Маршруты
- `GET /research` — каталог исследований (публичный)
- `GET /research/{slug}` — исследование (публичный)
- `GET /recipes` — каталог рецептов (публичный)
- `GET /recipes/{slug}` — рецепт (публичный)
- Admin CRUD: `/admin/research/*`, `/admin/recipes/*`

## Файлы
- `resources/js/Pages/Research/Index.vue`, `Show.vue`
- `resources/js/Pages/Recipes/Index.vue`, `Show.vue`
- `resources/js/Pages/Admin/Research/Index.vue`, `Form.vue`
- `resources/js/Pages/Admin/Recipes/Index.vue`, `Form.vue`
- `app/Http/Controllers/ResearchController.php`
- `app/Http/Controllers/Admin/ResearchController.php`
- `app/Http/Controllers/Admin/RecipeController.php`
- `app/Models/Research.php`
- `app/Models/Recipe.php`
