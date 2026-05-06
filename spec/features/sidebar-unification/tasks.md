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

## Active task

- (нет, фича закрыта)
