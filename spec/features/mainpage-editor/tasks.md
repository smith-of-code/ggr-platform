# Задачи — mainpage-editor

## T1. Модель данных: ключи и дефолты

- **Goal:** Определить константы GROUP и JSON_KEYS для `MainPageController`, описать дефолтные значения (из текущего хардкода `MainPage.vue`).
- **Scope:** `app/Http/Controllers/Admin/MainPageController.php` (черновик констант + приватный метод `defaults()`).
- **DoD:** Список ключей покрывает все 13 блоков + `block_order` + `section_titles`. JSON-ключи по аналогии с `OpportunityToursPageController`.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php -l app/Http/Controllers/Admin/MainPageController.php`

## T2. Сидер начальных значений

- **Goal:** Перенести текущие захардкоженные данные `MainPage.vue` в сидер `MainPageSeeder`.
- **Scope:** `database/seeders/MainPageSeeder.php`.
- **DoD:** Сидер создаёт записи в `settings` (group `main_page`) для всех ключей с актуальными данными из текущего хардкода.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan db:seed --class=MainPageSeeder`

## T3. Админ-контроллер, маршруты, навигация

- **Goal:** `index` (Inertia + JSON-распаковка) и `update` (валидация + `SettingsService::setGroup`); маршруты; пункт в сайдбаре.
- **Scope:** `app/Http/Controllers/Admin/MainPageController.php`, `routes/web.php`, `resources/js/Layouts/AdminLayout.vue`.
- **DoD:** Имена маршрутов `admin.main-page.index` / `admin.main-page.update`; пункт «Главная страница» в секции «Сайт» сайдбара.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=main-page`

## T4. Админ-страница: скалярные блоки

- **Goal:** Форма для Hero (title, description, bg_image), Moving (title, description), CTA (title, description), Contact (title, description, left_text, bullets), Stats guests (value, label).
- **Scope:** `resources/js/Pages/Admin/MainPage/Index.vue`.
- **DoD:** `form.put(route('admin.main-page.update'))`, отображение ошибок, структура карточек как у `OpportunityToursPage`.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T5. Админ-страница: массивные блоки (часть 1)

- **Goal:** Формы для `program_stages`, `city_benefits`, `additional_initiatives`, `videos` через `DynamicList`.
- **Scope:** `resources/js/Pages/Admin/MainPage/Index.vue` (расширение).
- **DoD:** Добавление/удаление элементов, все поля каждого типа, сохранение PUT.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T6. Админ-страница: массивные блоки (часть 2)

- **Goal:** Формы для `program_cities` (по годам), `program_results` (по годам), `contacts`, `socials`, `section_titles`.
- **Scope:** `resources/js/Pages/Admin/MainPage/Index.vue` (расширение).
- **DoD:** Динамическое управление годами и вложенными массивами; сохранение PUT.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T7. Админ-страница: порядок и видимость блоков

- **Goal:** Drag-and-drop список блоков с toggle видимости для `block_order`.
- **Scope:** `resources/js/Pages/Admin/MainPage/Index.vue` (расширение), при необходимости новый компонент `BlockOrderEditor.vue`.
- **DoD:** Перетаскивание меняет порядок; toggle скрывает/показывает блок; данные сохраняются в `block_order`.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T8. Публичный контроллер: проброс данных

- **Goal:** `HomeController::buildHomePageProps()` загружает группу `main_page`, мержит с дефолтами, передаёт `pageData` в Inertia.
- **Scope:** `app/Http/Controllers/HomeController.php`.
- **DoD:** `pageData` содержит полную структуру для шаблона; при пустой группе в БД работают дефолты (текущий хардкод).
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="echo json_encode(array_keys(app(App\\Http\\Controllers\\HomeController::class)->buildHomePageProps()));"`

## T9. Публичный шаблон: рендер из pageData

- **Goal:** Заменить хардкод в `MainPage.vue` на значения из `pageData` с fallback; рендерить блоки в порядке `block_order`; скрывать блоки с `enabled: false`.
- **Scope:** `resources/js/Pages/MainPage.vue`.
- **DoD:** Все 13+ блоков берут данные из props; порядок рендера определяется `block_order`; визуально без регрессии при наличии дефолтных данных.
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T10. Финализация: линтер, кэш, регрессия

- **Goal:** Проверить кэш (`getGroup` vs `getGroupFresh`); линтер на изменённых файлах; обновить spec/progress.
- **Scope:** Все затронутые файлы (только чтение + линтер); `spec/features/mainpage-editor/*`.
- **DoD:** Линтер чист; после сохранения в админке публичная страница видит актуальные данные; progress.md финализирован.
- **Verify:** Ручной сценарий: сохранить в админке → обновить `/mainpage`.
