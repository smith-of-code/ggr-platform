# Открытые вопросы

## Решённые ранее

1. ~~**LmsTestAttempt.status**~~: исправлено — добавлена миграция `2026_03_24_100000_add_status_to_lms_test_attempts.php`, исправлен `TestController::submit()` (status → completed), observer переключён на `updated`.

## Актуальные

2. **Авторизация ролей**: в маршрутах LMS и LMS Admin middleware ограничен только `auth`. Проверка ролей (admin, leader, participant) скорее всего происходит в контроллерах, но явного middleware для ролей не обнаружено. Уточнить механизм разграничения доступа.

3. **app/Enums/**: директория отсутствует. Перечисления (status, type, role и т.д.) реализованы как DB enum в миграциях и константы в моделях. Нет PHP enum-классов.

4. **Layouts**: 6 layout-файлов: `AdminLayout`, `AuthenticatedLayout`, `GuestLayout`, `LmsAdminLayout`, `LmsLayout`, `MainLayout`.

5. **Новые миграции без полного аудита**: ряд миграций (2026_03_25 — portal_modules, 2026_03_26 — vacancies/gallery, 2026_03_27 — directions/trajectory_blocks/grants/profiles_documents, 2026_03_30 — blog_subscribers/videos_to_posts/grants_type_city, 2026_03_31 — material_files/assignment_tasks/enrollment_status/gamification_courses/researches_drop/cities_fields, 2026_04_01 — atoms_vkusa/energy_cities/block_visibility/media_table/paid_form_slug, 2026_04_02 — stage_blocks_scheduled/workshop_types/media_collection_entity) добавляют таблицы и колонки, отражённые в моделях. Полный аудит columns-vs-fillable не проводился для каждого поля — модели приняты за source of truth.

6. **UploadedMedia vs Media**: существуют две модели для медиа — `Media` (polymorphic mediable, коллекция tour/etc.) и `UploadedMedia` (общее хранилище загруженных файлов). Взаимосвязь и разграничение использования не документированы.
