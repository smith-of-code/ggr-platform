# Прогресс — vshgr-editor

## Completed

### T1–T9 — Редактор лендинга `/vshgr`

**Сделано:**

1. `app/Support/VshgrPageContent.php` — группа `vshgr_page`, дефолты, `JSON_KEYS` (`socials`), `mergeFromStored()`.
2. `app/Http/Controllers/Admin/VshgrPageController.php` — `index` / `update`, валидация, `SettingsService::setGroup`.
3. `routes/web.php` — `admin/vshgr-page` GET/PUT, имена `admin.vshgr-page.*`.
4. `resources/js/Pages/Admin/VshgrPage/Index.vue` — форма (RCard, SectionHeader/DynamicList из туров возможностей).
5. `resources/js/Layouts/AdminLayout.vue` — пункт «Страница ВШГР», `isActive`.
6. `app/Http/Controllers/EducationController.php` — `getGroup` + `pageData` в Inertia.
7. `resources/js/Pages/Education/Index.vue` — тексты/URL/соцсети из `pageData`, фильтр соцсетей с `url`+`name`.
8. Кэш: публичная страница использует `getGroup` (после `setGroup` кэш сбрасывается в `SettingsService`).
9. Сид только ВШГР: `database/seeders/VshgrPageSeeder.php` → `VshgrPageContent::seedDefaultsIntoDatabase()`; из `PortalSeeder` убрано. Полный `DatabaseSeeder` по-прежнему вызывает `VshgrPageSeeder` для чистой установки.

**Verify (Docker, `source docker/.env.local`, `docker exec ${APP_NAME}_fpm`):**

- `php -l` на новых/изменённых PHP-файлах — OK.
- `php artisan route:list --name=vshgr-page` — 2 маршрута.
- `npm run build` — OK.

**Регрессия (ручная):** форма заявки, consent, маски email/телефона на `/vshgr` — не менялись, только подстановка текстов из `pageData`.

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
