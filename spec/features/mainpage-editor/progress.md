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
- REV-001. Фото: URL + выбор/загрузка в редакторе
  - Добавлена поддержка URL + выбор/загрузка для всех photo image-полей в редакторе главной страницы.
  - `app/Http/Controllers/Admin/MainPageController.php` — image-валидация унифицирована через `IMAGE_PATH_RULE` (`nullable|string|max:2048`).
  - `resources/js/Pages/Admin/MainPage/Index.vue` — во всех image-местах URL-only заменён на URL + upload/library UI (`DynamicList type: image-upload`, `ImageUploadCrop` для скалярных полей).
  - Verify: `source docker/.env.local && docker exec ${APP_NAME}_fpm php -l app/Http/Controllers/Admin/MainPageController.php` + `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — OK.
- REV-002. Текст в блоке «Атомные города»: устранение артефактов символов
  - `resources/js/Pages/MainPage.vue` — в карточках городов описание теперь рендерится через `stripHtml(...)` вместо сырого HTML.
  - `stripHtml(...)` расширен: удаление HTML-тегов + декодирование HTML-сущностей (`&nbsp;`, `&laquo;` и т.д.) через временный `textarea`.
  - Verify: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — OK.

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
