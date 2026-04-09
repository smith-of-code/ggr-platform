# Задачи — vshgr-editor

## T1. Поля и дефолты `vshgr_page`

- **Goal:** Описать ключи группы settings (скаляры + `socials` как JSON) и единое место дефолтов для `EducationController` и начального состояния админ-формы.
- **Scope:** `app/Http/Controllers/Admin/VshgrPageController.php` (черновик констант), при необходимости вынесенный helper или приватные методы в контроллере; спека уже отражает состав — уточнить в коде при реализации.
- **DoD:** Список ключей согласован с текущими строками в `Education/Index.vue`; JSON-ключи совпадают с `OpportunityToursPageController` (кодирование через `json_encode` при сохранении).
- **Verify:** По паттерну spec-continuation в Docker: `php -l` или минимальный вызов после появления файлов.

## T2. Админ-контроллер и маршруты

- **Goal:** `index` (Inertia + распаковка JSON) и `update` (валидация + `SettingsService::setGroup`).
- **Scope:** `app/Http/Controllers/Admin/VshgrPageController.php`, `routes/web.php` (импорт, `Route::get/put` в группе `admin`).
- **DoD:** Имена маршрутов `admin.vshgr-page.index` / `admin.vshgr-page.update`; middleware админки без изменений контракта.
- **Verify:** Docker: `php artisan route:list --name=vshgr-page` (или эквивалент фильтра).

## T3. Страница админки Inertia

- **Goal:** Форма редактирования всех полей лендинга `/vshgr` и динамический список соцсетей.
- **Scope:** `resources/js/Pages/Admin/VshgrPage/Index.vue`; при необходимости локальные импорты из `@/components` (без копипасты `DynamicList`).
- **DoD:** `form.put(route('admin.vshgr-page.update'))`, отображение ошибок валидации, структура карточек как у туров возможностей.
- **Verify:** Docker: `npm run build` (или скрипт сборки проекта в fpm-контейнере).

## T4. Навигация админки

- **Goal:** Ссылка «ВШГР» / «Страница ВШГР» в боковом меню и корректный `isActive`.
- **Scope:** `resources/js/Layouts/AdminLayout.vue`.
- **DoD:** Пункт ведёт на `admin.vshgr-page.index`; активное состояние для префикса пути.
- **Verify:** Сборка фронта в Docker; визуально при локальном прогоне.

## T5. Проброс данных в публичный контроллер

- **Goal:** `EducationController::index` передаёт объединённые настройки и дефолты в Inertia.
- **Scope:** `app/Http/Controllers/EducationController.php`, при необходимости тонкая обёртка в сервисе (в пределах того же шага — не более 5 файлов на шаг при коммитах).
- **DoD:** Один props-объект (например `pageData`) с полной структурой для шаблона; отсутствие ошибок при пустой группе в БД.
- **Verify:** Docker: вызов контроллера через feature test или tinker по соглашению проекта — если тестов нет, зафиксировать ручную проверку в progress.

## T6. Публичный шаблон `Education/Index.vue`

- **Goal:** Заменить захардкоженные тексты/URL на значения из `pageData` с fallback.
- **Scope:** `resources/js/Pages/Education/Index.vue`.
- **DoD:** Hero, каталог, анонсы (только заголовки), CTA, блок положения, заголовки секции формы, соцсети — из props; `products` / `latestAnnouncements` / отправка формы без регрессии.
- **Verify:** Docker: сборка фронта; ручная проверка `/vshgr`.

## T7. Кэш настроек и очистка

- **Goal:** Убедиться, что после `update` публичная страница видит актуальные данные (`SettingsService::setGroup` сбрасывает кэш группы).
- **Scope:** Поведение `SettingsService` уже есть — проверить использование `getGroup` vs `getGroupFresh` в публичном контроллере.
- **DoD:** Нет устаревших данных из-за Cache::remember после сохранения в админке.
- **Verify:** Ручной сценарий: сохранить в админке → обновить `/vshgr` в инкогнито.

## T8. Итоговая регрессия и фиксация

- **Goal:** Подтвердить отсутствие поломок consent, масок телефона/email, блока анонсов.
- **Scope:** затронутые файлы только чтение; при нахождении бага — отдельный микро-фикс в рамках лимита файлов.
- **DoD:** Чеклист в `progress.md` отмечен.
- **Verify:** Ручной прогон сценариев из `portal-education` / `consent` для формы на `Education/Index.vue`.

## T9. (Опционально) Seeder дефолтов

- **Goal:** Записать начальные значения `vshgr_page` в БД для новых окружений.
- **Scope:** `VshgrPageSeeder` вызывает `VshgrPageContent::seedDefaultsIntoDatabase()`; не смешивать с `PortalSeeder` при точечном сиде.
- **DoD:** Только настройки `vshgr_page` без остального портала.
- **Verify:** Docker: `php artisan db:seed --class=VshgrPageSeeder`.
