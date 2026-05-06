# Задачи — Унификация боковой навигации

## Not started

- (нет)

## Partially completed

- (нет)

## Completed tasks

- T1: HandleInertiaRequests.php — добавлен shared prop `hasAnyLmsAdminAccess`
  (`LmsProfile::userHasAnyLmsAdminProfile($user)`).
- T2: AdminLayout.vue — в footer добавлен блок 4 кнопок (`Админка портала` /
  `Админка LMS` / `ЛК LMS` / `ЛК туров`). Computed: `canAccessPortalAdmin`,
  `canAccessLmsAdmin`, `lmsEntryUrl`, `tourCabinetUrl`. Существующие
  «На сайт / Выход» сохранены ниже.
- T3: LmsLayout.vue — добавлены кнопки `Админка портала`, `ЛК LMS` к уже
  существующим `Админка LMS`, `ЛК туров`. Иконки `ShieldCheckIcon`,
  `AcademicCapIcon` добавлены в импорты, неиспользуемый `WrenchScrewdriverIcon`
  удалён. Добавлены handler-ы `onGoToPortalAdmin`, `onGoToLmsEntry`.
  Разделитель и существующие «Мой профиль / Выйти» оставлены ниже.
- T4: LmsAdminLayout.vue — добавлены кнопки `Админка LMS`, `ЛК LMS`, `ЛК туров`
  к уже существующей `Админка портала`. Computed: `canAccessLmsAdmin`,
  `lmsEntryUrl`, `tourCabinetUrl`, `tourCabinetPortalUrl`. Handler-ы
  `onGoToLmsAdmin`, `onGoToLmsEntry`, `visitTourCabinet`. Кнопка «Вернуться в LMS»
  оставлена под разделителем для контекста event/portal-shell.
- T5: Build verification — `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
  → ✓ built in 8.82s, ошибок нет.
- T6: Создан общий компонент `resources/js/Components/AppSidebar.vue`. Props:
  `items` (с поддержкой `type='section'` для заголовков групп), `activeItem`,
  `collapsed`, `theme: 'dark' | 'light'`. Slots: `logo`, `footer`. Emits: `select`.
  Стили построены на CSS-токенах из `resources/css/app.css` (`--color-primary-dark`,
  `--color-primary-light`, `--color-gray-*`, `--space-*`, `--radius-lg`, `--text-*`).
  Активный пункт получает 3-px индикатор-полосу слева, цвет которой меняется по теме.
- T7: `LmsLayout.vue` и `LmsAdminLayout.vue` — `<RSidebar ...>` заменён на
  `<AppSidebar ... theme="dark">`. Импорт `AppSidebar` добавлен. `RButton`,
  `RAvatar` и handler-ы остались без изменений.
- T8: `AdminLayout.vue` полностью переписан под `AppSidebar theme="light"`:
  22 пункта меню и 3 заголовка групп (`Контент портала` / `Сайт` / `HR`)
  переведены в массив `sidebarItems` (с `type='section'` для заголовков).
  Иконки оставлены как HTML-строки (`v-html` внутри `app-sidebar__link-icon`,
  inline-стили `width:20px;height:20px`). `ROUTE_MAP` маппит `id` пункта на
  имя маршрута для `router.visit(route(...))` в обработчике `onSelect`.
  `activeItemId` — computed по `usePage().url`. User-card вынесен в `#logo`,
  блок 4 кнопок и «На сайт / Выход» — в `#footer`. Scoped CSS
  `.admin-link.is-active::before` удалён (индикатор теперь живёт в `AppSidebar`).
- T9: Build verification — `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
  → ✓ built in 8.87s, линт-ошибок нет.

## Active task

- (нет, фича закрыта)
