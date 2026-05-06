# План реализации — Унификация боковой навигации

## Шаги

1. **AdminLayout.vue (`/admin/*`, светлый)**
   - Удалить из `<nav>` блок-разделитель и ссылку-карточку «Админка LMS ВШГР»
     (с градиентом `from-indigo-50 to-blue-50`).
   - В существующий футер с карточкой пользователя добавить новый ряд:
     отдельную кнопку-`<a>` «Админка LMS» (визуально похожа на «На сайт / Выход»,
     но с подсветкой/иконкой админки), v-if на `$page.props.auth?.user?.is_admin`.
   - URL: `route('lms.admin.events.index')`.

2. **LmsLayout.vue (`/lms/{event}/*`, тёмный)**
   - Перед существующими `RButton`-ами «Мой профиль / ЛК Туров / Выйти»
     добавить ещё один `RButton variant="ghost" size="sm" block` «Админка LMS».
   - v-if: `effectiveRoleSlug === 'admin' || canLimitedBackofficeAccess`
     (то есть `canAnyBackofficeAccess` — уже есть computed).
   - В обработчике клика — переиспользовать существующую логику `onNavigate('lms.admin')`,
     которая уже корректно ведёт админа на `lms.admin.home`,
     а ограниченные роли — на `lms.admin.tests.index`.
   - В `sidebarItems` оставить элементы без изменений
     (включая существующий пункт меню `lms.admin`, чтобы не ломать существующие e2e).

3. **LmsAdminLayout.vue (`/lms-admin/*`, тёмный)**
   - Из слота `#logo` удалить блок-ссылку `<a href="route('admin.dashboard')">Админка портала</a>`
     вместе с обвязкой (отступы и стрелка-иконка).
   - В слот `#footer` добавить **первой** кнопкой `RButton variant="ghost" size="sm" block`
     «Админка портала» с `v-if="canAccessPortalAdmin"`. Под ней оставить
     существующую кнопку «Вернуться в LMS» (label берётся из `returnToFooterLabel`).
   - Логика клика «Админка портала»: `router.visit(route('admin.dashboard'))`
     (или `sameOriginHref(...)`, если другие переходы используют этот хелпер).

## Порядок выполнения и git-cohesion

Делаем по одному файлу за итерацию, проверяем линт после каждого:

1. `AdminLayout.vue` → ReadLints.
2. `LmsLayout.vue` → ReadLints.
3. `LmsAdminLayout.vue` → ReadLints.
4. Финальный билд фронтенда: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`.
   (если уже запущен `npm run dev`, достаточно убедиться, что HMR не падает.)
