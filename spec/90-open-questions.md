# Открытые вопросы

## Решённые ранее

1. ~~**LmsTestAttempt.status**~~: исправлено — добавлена миграция `2026_03_24_100000_add_status_to_lms_test_attempts.php`, исправлен `TestController::submit()` (status → completed), observer переключён на `updated`.

## Актуальные

2. **Авторизация ролей**: в маршрутах LMS и LMS Admin middleware ограничен только `auth`. Проверка ролей (admin, leader, participant) скорее всего происходит в контроллерах, но явного middleware для ролей не обнаружено. Уточнить механизм разграничения доступа.

3. **app/Enums/**: директория отсутствует. Перечисления (status, type, role и т.д.) реализованы как DB enum в миграциях и константы в моделях. Нет PHP enum-классов.

4. **Layouts**: 6 layout-файлов: `AdminLayout`, `AuthenticatedLayout`, `GuestLayout`, `LmsAdminLayout`, `LmsLayout`, `MainLayout`.

5. **Новые миграции без полного аудита**: ряд миграций (2026_03_25 — portal_modules, 2026_03_26 — vacancies/gallery, 2026_03_27 — directions/trajectory_blocks/grants/profiles_documents, 2026_03_30 — blog_subscribers/videos_to_posts/grants_type_city, 2026_03_31 — material_files/assignment_tasks/enrollment_status/gamification_courses/researches_drop/cities_fields, 2026_04_01 — atoms_vkusa/energy_cities/block_visibility/media_table/paid_form_slug, 2026_04_02 — stage_blocks_scheduled/workshop_types/media_collection_entity) добавляют таблицы и колонки, отражённые в моделях. Полный аудит columns-vs-fillable не проводился для каждого поля — модели приняты за source of truth.

6. **UploadedMedia vs Media**: существуют две модели для медиа — `Media` (polymorphic mediable, коллекция tour/etc.) и `UploadedMedia` (общее хранилище загруженных файлов). Взаимосвязь и разграничение использования не документированы.

7. **ЛК участника конкурса + этапы 1–3** — см. `spec/features/lk-participant-contests/spec.md` и `progress.md`. Реализованы админка состава городов, вопросы этапа 2, экраны этапов 2–3 в ЛК. Открыто: **Excel** (колонки/права), при необходимости — ужесточение валидации URL видео этапа 3, связь с **`Application`/LMS**.

8. ~~**Синхронизация Направлений и Проектов**~~: решено — выделено в фичу `tours-directions-sync`. Direction становится единственным источником правды, `Tour::PROJECTS` и `project_key` удаляются, все ссылки заменяются на `direction_id` FK.

9. **Миграция `2026_04_24_100100_replace_project_key_with_direction_id_in_tour_cabinet_tables` ломается на sqlite (`:memory:`)**: `alter table "tour_cabinet_direction_cities" drop column "project_key"` падает с `error in index tour_cabinet_direction_cities_project_key_city_id_unique after drop column`. Sqlite не пересоздаёт индекс автоматически — нужно сначала `dropUnique(['project_key', 'city_id'])`, потом drop column. На MySQL (prod) проблема может не воспроизводиться, поэтому раньше не выловили. Затрагивает ВСЕ Feature-тесты с `RefreshDatabase` — нужно поправить отдельной задачей в фиче `tours-directions-sync` (см. п.8). На текущей задаче «лимит вложений в ЛК туров» подтверждено, что фейл воспроизводится и на чистом master без моих правок (через `git stash`).

10. **Verify LMS Courses 2026-05-05**: проверка `npm run build` для настройки `lms_course_stages.available_from` не выполнена из-за недоступного Docker. `bash -lc "source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build"` попадает в WSL без Docker integration; PowerShell-вариант через `docker exec ${APP_NAME}_fpm npm run build` падает из-за отсутствующего `dockerDesktopLinuxEngine` pipe. Нужно повторить после запуска Docker Desktop / WSL integration.
