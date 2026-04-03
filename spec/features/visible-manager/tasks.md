# Tasks — Visible Manager

## Task 1: Конфигурация управляемых страниц

- **Цель**: Создать конфиг-файл с массивом публичных страниц, которыми можно управлять
- **Scope**: `config/page_visibility.php`
- **DoD**: Файл содержит массив `pages` с записями: `slug`, `label` (русское название), `route_prefix` (URL-паттерн для middleware)
- **Verify**: Файл существует, структура корректна

## Task 2: Расширение SettingsService

- **Цель**: Добавить методы `getPageVisibility()` и `setPageVisibility()` в `SettingsService`
- **Scope**: `app/Services/SettingsService.php`
- **DoD**: Методы читают/пишут group `page_visibility`, кэш инвалидируется при записи
- **Verify**: Unit-логика: setGroup + getGroup для `page_visibility`

## Task 3: Middleware CheckPageVisibility

- **Цель**: Создать middleware, который при запросе к скрытой странице возвращает Inertia-заглушку
- **Scope**: `app/Http/Middleware/CheckPageVisibility.php`, `bootstrap/app.php`
- **DoD**: Middleware сравнивает текущий URL с конфигом, проверяет `settings` через сервис, при `hidden=true` рендерит `PageUnderConstruction`. Пропускает аутентифицированных пользователей (admin).
- **Verify**: `php artisan route:list` — middleware привязан

## Task 4: Vue-страница заглушки

- **Цель**: Создать красивую страницу «В разработке» с анимацией и иконкой
- **Scope**: `resources/js/Pages/PageUnderConstruction.vue`
- **DoD**: Страница с `MainLayout`, иконка строительства, заголовок, подзаголовок, кнопка «На главную»
- **Verify**: Страница рендерится при Inertia::render('PageUnderConstruction')

## Task 5: Админ-контроллер PageVisibilityController

- **Цель**: Создать контроллер для управления видимостью из админки
- **Scope**: `app/Http/Controllers/Admin/PageVisibilityController.php`
- **DoD**: `index()` — возвращает Inertia-страницу со списком страниц и их статусами; `update(Request)` — сохраняет toggle-состояния через `SettingsService`
- **Verify**: Маршруты отвечают корректным HTTP-статусом

## Task 6: Маршруты админки

- **Цель**: Добавить маршруты для PageVisibilityController в web.php
- **Scope**: `routes/web.php`
- **DoD**: `GET /admin/settings/page-visibility` → `admin.settings.page-visibility`, `PUT /admin/settings/page-visibility` → `admin.settings.page-visibility.update`
- **Verify**: `php artisan route:list --name=admin.settings.page-visibility`

## Task 7: Админ-UI — страница управления видимостью

- **Цель**: Создать Vue-страницу со списком страниц и toggle-переключателями
- **Scope**: `resources/js/Pages/Admin/Settings/PageVisibility.vue`
- **DoD**: Таблица с названием страницы, URL-превью, toggle. Кнопка «Сохранить». Flash-уведомление об успехе.
- **Verify**: Страница рендерится из Inertia, toggles переключаются, данные сохраняются

## Task 8: Интеграция в хаб настроек

- **Цель**: Добавить ссылку «Видимость страниц» в Settings/Index.vue
- **Scope**: `resources/js/Pages/Admin/Settings/Index.vue`
- **DoD**: Карточка-ссылка на `/admin/settings/page-visibility` в списке настроек
- **Verify**: Ссылка видна и ведёт на нужную страницу

## Task 9: Скрытие навигации для скрытых страниц

- **Цель**: Передавать список скрытых страниц в MainLayout и скрывать соответствующие пункты меню
- **Scope**: `app/Http/Middleware/SharePageVisibility.php` (или HandleInertiaRequests), `resources/js/Layouts/MainLayout.vue`
- **DoD**: Shared prop `hiddenPages` (массив slug-ов) передаётся во все Inertia-ответы. `MainLayout` фильтрует `navItems` и footer-ссылки.
- **Verify**: Скрытая страница не отображается в меню

## Task 10: Подключение middleware к маршрутам

- **Цель**: Применить `CheckPageVisibility` middleware к публичным маршрутам портала
- **Scope**: `routes/web.php`, `bootstrap/app.php`
- **DoD**: Middleware применяется глобально к web-группе (или к конкретным маршрутам). Скрытая страница показывает заглушку.
- **Verify**: Переход на скрытую страницу → заглушка; на открытую → обычный контент
