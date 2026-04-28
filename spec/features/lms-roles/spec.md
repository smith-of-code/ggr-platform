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
- Доступ к маршрутам `lms-admin` (middleware `lms.backoffice`):
  - полный доступ: профиль пользователя с `lms_roles.slug = admin` либо без `lms_role_id` и `LmsProfile.role = admin`
  - ограниченный доступ (только начисление баллов в геймификации): роли `куратор-эксперт`, `тренер команды`, `трекер`, `эксперт` (и slug-варианты)
    - разрешены только маршруты: `lms.admin.gamification.index`, `lms.admin.gamification.manual-points`
    - остальные `lms-admin` маршруты закрыты (403)
  - для списка/создания мероприятий (`/lms-admin/events`) доступ остаётся только у полного админа
- После авторизации пользователь с полным LMS-админ доступом (через `role=admin` или `lms_roles.slug=admin`) перенаправляется в LMS-админку события (`/lms-admin/{event}` → `courses.index`)
