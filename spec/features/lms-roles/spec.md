# Фича: LMS — Кастомные роли и доступ

**Статус**: auto-detected, needs review

## Описание

Система кастомных ролей в рамках мероприятия с привязкой к курсам для ограничения доступа.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsRole`
- `App\Models\Lms\LmsProfile` (поле lms_role_id)
- `App\Models\Lms\LmsCourse` (связь roleAccess)

### Контроллеры
- `App\Http\Controllers\Lms\Admin\RoleController` — CRUD ролей

### Страницы
- `Pages/Lms/Admin/Roles/Index.vue`, `Form.vue`

## Ключевые workflow

- Админ создаёт роли для мероприятия (name, slug, description)
- Роль привязывается к профилю участника (LmsProfile.lms_role_id)
- Курсы получают ролевой доступ через pivot lms_course_role_access
- Роль может быть назначена через инвайт (LmsInvitation.lms_role_id)
- Базовые роли (participant, curator, leader, admin) заданы enum в LmsProfile.role
- Доступ к маршрутам `lms-admin` (middleware `lms.backoffice`): для мероприятия с параметром `event` — профиль пользователя с `lms_roles.slug = admin` либо без `lms_role_id` и `LmsProfile.role = admin`; для списка/создания мероприятий — наличие хотя бы одного такого профиля (`App\Models\Lms\LmsProfile`, `App\Http\Middleware\EnsureLmsBackofficeAccess`)
