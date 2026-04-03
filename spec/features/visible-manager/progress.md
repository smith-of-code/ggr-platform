# Progress — Visible Manager

## Completed tasks

1. Task 1: Конфигурация управляемых страниц
   - Files: `config/page_visibility.php`
2. Task 2: Расширение SettingsService
   - Files: `app/Services/SettingsService.php` — добавлены `getHiddenPages()`, `setPageVisibility()`
3. Task 3: Middleware CheckPageVisibility
   - Files: `app/Http/Middleware/CheckPageVisibility.php`
4. Task 4: Vue-страница заглушки
   - Files: `resources/js/Pages/PageUnderConstruction.vue`
5. Task 5: Админ-контроллер PageVisibilityController
   - Files: `app/Http/Controllers/Admin/PageVisibilityController.php`
6. Task 6: Маршруты админки
   - Files: `routes/web.php` — GET/PUT `/admin/settings/page-visibility`
7. Task 7: Админ-UI — страница управления видимостью
   - Files: `resources/js/Pages/Admin/Settings/PageVisibility.vue`
8. Task 8: Интеграция в хаб настроек
   - Files: `resources/js/Pages/Admin/Settings/Index.vue` — добавлена карточка «Видимость страниц»
9. Task 9: Скрытие навигации для скрытых страниц
   - Files: `app/Http/Middleware/HandleInertiaRequests.php` (shared prop `hiddenPages`), `resources/js/Layouts/MainLayout.vue` (фильтрация navItems + динамические mobile/footer)
10. Task 10: Подключение middleware к маршрутам
    - Files: `bootstrap/app.php` — `CheckPageVisibility` добавлен в web middleware group

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
