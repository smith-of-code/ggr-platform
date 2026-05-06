# Унификация боковой навигации

## Цель

Привести три текущих лейаута приложения к двум визуально согласованным видам сайдбара
и добавить во все три унифицированный блок переходов между «зонами» приложения:
портал-админка / LMS-админка / ЛК LMS / ЛК туров.

## Текущее состояние (фактический код)

| Лейаут                 | Файл                                       | Маршрут           | Тема                                              |
|------------------------|--------------------------------------------|-------------------|---------------------------------------------------|
| Портал-админ           | `resources/js/Layouts/AdminLayout.vue`     | `/admin/*`        | Светлая (`AppSidebar theme="light"`)              |
| LMS участника          | `resources/js/Layouts/LmsLayout.vue`       | `/lms/{event}/*`  | Тёмная (`AppSidebar theme="dark"`)                |
| LMS-админ              | `resources/js/Layouts/LmsAdminLayout.vue`  | `/lms-admin/*`    | Тёмная (`AppSidebar theme="dark"`)                |

### Общий компонент `AppSidebar`

Расположение: `resources/js/Components/AppSidebar.vue`. Заменяет прежнюю
комбинацию из самописной разметки в `AdminLayout` и `RSidebar` из UI-kit
в `LmsLayout` / `LmsAdminLayout`. Обеспечивает идентичную структуру и
поведение для всех трёх лейаутов; различие — только в значении `theme`.

Props:

| Prop          | Тип                | Default | Описание                                                                                  |
|---------------|--------------------|---------|-------------------------------------------------------------------------------------------|
| `items`       | `Array<Item>`      | —       | Пункты меню. Элемент: `{ id, label, icon?, badge?, type? }`. `type='section'` — заголовок группы. |
| `activeItem`  | `string`           | `''`    | `id` подсвеченного пункта (с индикатором-полосой слева).                                  |
| `collapsed`   | `boolean`          | `false` | Свёрнутый сайдбар (только иконки, ширина 60px).                                           |
| `theme`       | `'dark' \| 'light'` | `'dark'` | Тема. Управляет фоном, цветом текста и акцентом индикатора активного пункта.             |

Slots: `logo` (верхняя зона — лого, user-card), `footer` (нижняя зона — кнопки).
Emits: `select(itemId)` при клике на пункт меню.

## Целевое состояние

### Два визуальных вида

1. **Стиль «admin» (светлый)** — только `AdminLayout`. Эталон.
2. **Стиль «lms» (тёмный, `RSidebar`)** — `LmsLayout` (эталон) и `LmsAdminLayout`.

### Унифицированный блок переходов между зонами (footer-секция)

В нижней части сайдбара (footer) каждого из трёх лейаутов располагается
**одинаковый набор из четырёх кнопок** в указанном порядке:

| ID            | Лейбл           | Условие отображения                                | Куда ведёт                                                                                                  |
|---------------|-----------------|----------------------------------------------------|-------------------------------------------------------------------------------------------------------------|
| portalAdmin   | Админка портала | `auth.user.is_admin === true`                      | `route('admin.dashboard')`                                                                                   |
| lmsAdmin      | Админка LMS    | `auth.user.is_admin` ИЛИ `hasAnyLmsAdminAccess` ИЛИ `canAnyBackofficeAccess` (для LmsLayout) | `lms.admin.home` для текущего event (если есть), `lms.admin.tests.index` для limited backoffice, иначе `lms.admin.events.index` |
| lms           | ЛК LMS          | `lmsEntryUrl !== null` (есть LMS-профиль)          | `lms.dashboard` для текущего event (если есть `props.event`), иначе `lmsEntryUrl`                              |
| tourCabinet   | ЛК туров        | `tourCabinetUrl !== null` (есть доступ к ЛК туров) | `tourCabinetPortalUrl` (если задан и хост `lms.*`) либо `tourCabinetUrl`                                     |

Ниже блока 4 кнопок располагаются персональные действия пользователя:

- `AdminLayout`: «На сайт» / «Выход».
- `LmsLayout`: «Мой профиль» / «Выйти».
- `LmsAdminLayout`: «Мой профиль» / «Выйти» (унифицировано с `LmsLayout`,
  кнопка «Вернуться в LMS» убрана, переход в LMS-кабинет участника доступен
  через кнопку «ЛК LMS» из основного блока 4 кнопок).

### Стиль кнопок

- В тёмных лейаутах (`LmsLayout`, `LmsAdminLayout`) — `RButton variant="ghost" size="sm" block`
  (как в существующих кнопках футера).
- В светлом `AdminLayout` — кнопки-`<a>`/`<Link>` в ghost-стиле
  (`text-gray-600 hover:bg-gray-50 hover:text-gray-900`), с теми же иконками
  и общим визуальным ритмом, что и пункты меню.

### Поведение клика «Админка LMS» в зависимости от лейаута

- `AdminLayout` (нет `props.event`): `route('lms.admin.events.index')`.
- `LmsAdminLayout` (есть `props.event`): кнопка ведёт пользователя в дашборд
  админки текущего события (`lms.admin.courses.index` для admin, либо
  `lms.admin.tests.index` для limited backoffice), но при отсутствии события —
  `lms.admin.events.index`.
- `LmsLayout`: использует существующий `onNavigate('lms.admin')`,
  который ведёт `admin` на `lms.admin.home`, а ограниченные роли — на `lms.admin.tests.index`.

### Поведение клика «ЛК туров»

Существующая логика из `LmsLayout.visitTourCabinet()` повторно используется на всех лейаутах:
если в `usePage().props.tourCabinetPortalUrl` задан абсолютный URL (то есть мы
смотрим LMS на поддомене `lms.*`), переходим на портальный хост; иначе
`router.visit(tourCabinetUrl)`.

### Поведение клика «Мой профиль» / «Выйти» в LmsAdminLayout

- «Мой профиль»: при наличии `props.event` ведёт на `lms.profile.edit` для текущего
  события; иначе fallback на `lmsEntryUrl` (URL профиля дефолтного LMS-события
  пользователя).
- «Выйти»: при наличии `props.event` и не из режима `usePortalLmsFormShell`
  отправляет `POST lms.logout` для текущего события; иначе обычный
  `POST route('logout')` (Breeze).

## Серверная сторона (Inertia shared)

`app/Http/Middleware/HandleInertiaRequests.php` дополняется ключом:

- `hasAnyLmsAdminAccess: bool` — `LmsProfile::userHasAnyLmsAdminProfile($user)`,
  глобальный признак (не привязан к event).

Существующие ключи `auth.user.is_admin`, `lmsEntryUrl`, `tourCabinetUrl`,
`tourCabinetPortalUrl` не меняются — они уже служат входами для соответствующих кнопок.

## Не входит в скоп

- Изменение пунктов основного меню каждого лейаута.
- Изменение `MainLayout`, `AuthenticatedLayout`, `GuestLayout`.
- Скрытие кнопки текущей зоны: пользователь явно попросил «на всех добавь все 4», — клик по кнопке текущей зоны ведёт на «домашний» URL зоны.
