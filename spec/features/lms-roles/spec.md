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
  - ограниченный доступ (геймификация + просмотр ответов в обучении + группы): роли `куратор-эксперт`, `тренер команды`/`тренер команд`, `трекер`, `эксперт` (и slug-варианты)
    - разрешены маршруты:
      - `lms.admin.gamification.index`, `lms.admin.gamification.manual-points`
      - `lms.admin.tests.index`, `lms.admin.tests.results`
      - `lms.admin.assignments.index`, `lms.admin.assignments.show`
      - `lms.admin.assignments.review`, `lms.admin.assignments.comment`
      - `lms.admin.assignments.mark-read`
      - `lms.admin.groups.index`, `lms.admin.groups.create`, `lms.admin.groups.store`, `lms.admin.groups.edit`, `lms.admin.groups.update`, `lms.admin.groups.destroy`
    - создание/редактирование/удаление тестов и заданий остаются недоступными (403)
    - в `LmsAdminLayout` для этих ролей в левом меню отображаются только разделы: `Тесты`, `Задания`, `Геймификация`, `Группы` (остальные вкладки скрыты)
    - при создании/редактировании группы куратор для этих ролей фиксируется как текущий пользователь; управлять можно только группами, где пользователь указан куратором
    - в кабинете участника (`LmsLayout`) показывается пункт «Админ-панель LMS (проверка)», который ведёт на `lms.admin.tests.index`
    - остальные `lms-admin` маршруты закрыты (403)
  - для списка/создания мероприятий (`/lms-admin/events`) доступ остаётся только у полного админа
- После авторизации пользователь с полным LMS-админ доступом (через `role=admin` или `lms_roles.slug=admin`) перенаправляется в LMS-админку события (`/lms-admin/{event}` → `courses.index`)
