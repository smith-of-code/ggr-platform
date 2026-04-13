# Прогресс — mainpage-editor

## Completed tasks

- T1. Модель данных: ключи и дефолты
  - `app/Http/Controllers/Admin/MainPageController.php` — GROUP, JSON_KEYS, defaults(), defaultBlockOrder()
- T2. Сидер начальных значений
  - `database/seeders/MainPageSeeder.php` — все блоки из хардкода MainPage.vue
- T3. Админ-контроллер, маршруты, навигация
  - `app/Http/Controllers/Admin/MainPageController.php` — index/update
  - `routes/web.php` — admin.main-page.index / admin.main-page.update
  - `resources/js/Layouts/AdminLayout.vue` — пункт «Главная страница» в секции «Сайт»
- T4. Админ-страница: скалярные блоки
  - `resources/js/Pages/Admin/MainPage/Index.vue` — Hero, Moving, CTA, Contact, Stats guests
- T5. Админ-страница: массивные блоки (часть 1)
  - `resources/js/Pages/Admin/MainPage/Index.vue` — program_stages, city_benefits, additional_initiatives, videos через DynamicList
- T6. Админ-страница: массивные блоки (часть 2)
  - `resources/js/Pages/Admin/MainPage/Index.vue` — program_cities (по годам), program_results (по годам), contacts, socials, section_titles
- T7. Порядок и видимость блоков
  - `resources/js/Pages/Admin/MainPage/Index.vue` — drag-and-drop block_order + toggle enabled
- T8. Публичный контроллер: проброс данных
  - `app/Http/Controllers/HomeController.php` — buildMainPageData(), pageData в buildHomePageProps()
- T9. Публичный шаблон: рендер из pageData
  - `resources/js/Pages/MainPage.vue` — все блоки из pageData, block_order (CSS order + v-if), section_titles
- T10. Финализация: линтер, кэш, регрессия
  - Линтер: чист (0 ошибок)
  - Кэш: getGroup (кэш) для публичной, getGroupFresh для админки; setGroup сбрасывает кэш
  - Сборка: npm run build — OK

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
