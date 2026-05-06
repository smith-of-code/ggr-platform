# Progress — Унификация боковой навигации

## Decisions

- 2 визуальных вида: «admin» (светлый, только `AdminLayout`) и «lms» (тёмный
  `RSidebar`, для `LmsLayout` и `LmsAdminLayout`).
- В футере каждого из трёх лейаутов располагается одинаковый набор из четырёх
  кнопок переходов между зонами: «Админка портала», «Админка LMS», «ЛК LMS»,
  «ЛК туров». Каждая кнопка отображается только при наличии прав/доступа.
- Кнопка текущей зоны не скрывается (по запросу пользователя «на всех добавь»),
  при клике ведёт на «домашний» URL зоны.
- Inertia shared расширен ключом `hasAnyLmsAdminAccess: bool`
  (`LmsProfile::userHasAnyLmsAdminProfile`), глобальный признак доступа к LMS-админке.
- Персональные действия пользователя располагаются ниже блока 4 кнопок:
  `AdminLayout`: «На сайт» / «Выход»; `LmsLayout`: «Мой профиль» / «Выйти»;
  `LmsAdminLayout`: «Мой профиль» / «Выйти» (унифицировано с `LmsLayout`,
  «Вернуться в LMS» удалено — его роль закрывает кнопка «ЛК LMS» из общего блока).

## Open issues

- (нет)

## Status

- [x] T1 HandleInertiaRequests.php: shared `hasAnyLmsAdminAccess`
- [x] T2 AdminLayout.vue: блок 4 кнопок (`portalAdmin`/`lmsAdmin`/`lms`/`tourCabinet`)
- [x] T3 LmsLayout.vue: блок 4 кнопок перед «Мой профиль / Выйти»
- [x] T4 LmsAdminLayout.vue: блок 4 кнопок перед «Вернуться в LMS»
- [x] T5 Build verification (`npm run build` внутри `vshgr-platform_fpm`, ✓ built in 8.82s)

## Артефакты

- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/Layouts/AdminLayout.vue`
- `resources/js/Layouts/LmsLayout.vue`
- `resources/js/Layouts/LmsAdminLayout.vue`
- `spec/features/sidebar-unification/{spec,plan,tasks,progress}.md`

## Реализованные URL-ы кнопок

| Кнопка           | AdminLayout (`/admin/*`)          | LmsLayout (`/lms/{event}/*`)                | LmsAdminLayout (`/lms-admin/*`)                                  |
|------------------|-----------------------------------|---------------------------------------------|------------------------------------------------------------------|
| Админка портала  | `route('admin.dashboard')`        | `route('admin.dashboard')`                  | `route('admin.dashboard')`                                       |
| Админка LMS      | `route('lms.admin.events.index')` | `onNavigate('lms.admin')` (admin/limited)   | `lms.admin.courses.index`/`lms.admin.tests.index`/`events.index` |
| ЛК LMS           | `lmsEntryUrl` (shared)            | `lmsEntryUrl` (shared)                      | `lms.dashboard` для `props.event` или `lmsEntryUrl`              |
| ЛК туров         | `tourCabinetUrl`                  | `visitTourCabinet()` (учитывает `lms.*` host) | `visitTourCabinet()` (учитывает `lms.*` host)                  |
