# Открытые вопросы

## Обнаружено при bootstrap

1. ~~**LmsTestAttempt.status**~~: исправлено — добавлена миграция `2026_03_24_100000_add_status_to_lms_test_attempts.php`, исправлен `TestController::submit()` (status → completed), observer переключён на `updated`.

2. **Авторизация ролей**: в маршрутах LMS и LMS Admin middleware ограничен только `auth`. Проверка ролей (admin, leader, participant) скорее всего происходит в контроллерах, но явного middleware для ролей не обнаружено. Уточнить механизм разграничения доступа.

3. **app/Enums/**: директория отсутствует. Перечисления (status, type, role и т.д.) реализованы как DB enum в миграциях и константы в моделях. Нет PHP enum-классов.

4. **Layouts**: директория `resources/js/Layouts/` не просканирована детально. Предположительно содержит `AuthenticatedLayout`, `GuestLayout`, `AdminLayout`, `LmsLayout`, `LmsAdminLayout`, `MainLayout` (видно по build-артефактам).
