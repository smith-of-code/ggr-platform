# videos-permissions — прогресс

## Completed

1. Task 1: Уточнение модели видимости и контракт API — миграция `visible_to_all`, `normalizeVideoAccess`, валидация в `Admin\VideoController`, тесты
2. Task 2: Список видео участника без фильтра — `VideoController::index` с `visible_to_all` и пересечением программ
3. Task 3: Фильтр `lms_group_id` в `VideoController::index`, props `programFilterGroups`
4. Task 4: `VideoController::show` — доступ по событию + `is_active`, без проверки членства в программе
5. Task 5: UI админки «Кому показывать» в `Admin/Videos/Form.vue`
6. Task 6: UI фильтра в `Lms/Videos/Index.vue`
7. Task 7: Колонка «Видимость» в `Admin/Videos/Index.vue`
8. Task 8: `Lms/Videos/Show.vue` не менялся (регрессия lms-events не затронута)
9. Task 9: `php artisan test --filter=VideoPermissionsTest`, `npm run build` в контейнере `fpm`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
