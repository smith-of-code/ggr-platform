# План — tour-cabinet-archives-nav-and-form-scroll

## Milestones

- **M1 — Усиленная подсветка вкладок «Архивы» в `TourCabinetQuickNav`**: в `itemClass(active)` для пары пунктов «Архив конкурсы» / «Архив коммерческих туров» (когда `isContestArchiveSection || isCommerceArchiveSection` равно true) применять более контрастный стиль — `bg-[#003274] text-white font-semibold` + иконка `text-white`. Также допустимо добавить нижнюю акцентную полосу `border-b-2 border-[#003274]` для согласованности с другими «активными» интерфейсами портала.
- **M2 — Кнопка «Личный кабинет» становится primary-action**: в `Archives/Contest/Index.vue` и `Archives/Commerce/Index.vue` в `<template #toolbar>` поменять `variant="outline"` → `variant="primary"` для `RButton` с текстом «Личный кабинет» и добавить `HomeIcon` слева. Цель — пользователь визуально понимает, что это основной способ вернуться к рабочему пространству.
- **M3 — Аудит `preserveScroll: true` во всех submit-вызовах панелей конкурса и коммерческих туров**: дозаполнить опцию в `CommerceToursStage3Panel.archiveAndReset()` (известный пропуск); пройтись по `ContestStage1/2/3Panel.vue` и `CommerceToursStage1/2/3Panel.vue` Grep'ом по `router.post|form.post|form.put|form.patch|form.delete` и убедиться, что у каждого `{ preserveScroll: true }` есть в options-объекте.
- **M4 — Flash-флаги + автоскролл для возврата с LMS-страниц анкет**: после сабмита LMS-формы анкеты этапа 1 конкурса (`startCityForm` → `forms.public.show`) и анкеты этапа 2 коммерческого тура (`tour.store` → `forms.public.show`) при редиректе на `tour-cabinet.dashboard` поставить отдельные session-flash ключи (`tour_cabinet_contest_just_form_submitted`, `tour_cabinet_commerce_just_form_submitted`). Зарегистрировать ключи в `HandleInertiaRequests::share()['flash']`. В `Dashboard.vue` добавить `watch`/`onMounted`-обработчики по аналогии с существующим `tour_cabinet_commerce_just_archived` → `scrollAndHighlight('tour-cabinet-contest')` / `scrollAndHighlight('tour-cabinet-commerce-tours')`.
- **M5 — Verify + sanity (Docker)**: `npm run build`, `php artisan route:list --name=tour-cabinet` (контроль, что маршруты не сломаны), ручной QA-флоу через `npm run dev` или браузер (если доступен) с проверкой всех ~10 submit-точек.

## UI Components (UI Kit Lookup)

Поиск по `resources/js/Components/`, `resources/js/composables/`, UI Kit `@rosatom-ggr/ui-kit` (см. `resources/js/app.js`):

- `RButton` (variant `primary` | `outline`, size `sm/md/lg`) — уже используется в Archives/*/Index.vue. Реюз: меняем только variant.
- Heroicons `@heroicons/vue/24/outline` (`HomeIcon`, `ArchiveBoxIcon`, `ArrowLeftIcon`) — уже импортируются в `TourCabinetQuickNav.vue` (`ArchiveBoxIcon`). Реюз: добавим `HomeIcon` в Archives/*/Index.vue toolbar.
- `scrollAndHighlight(anchorId: string): void` + `highlightedAnchor: Ref<string>` в `Dashboard.vue` — кастомная функция, уже реализована для `tour-cabinet-commerce-tours` и `tour-cabinet-contest-detail`. Реюз без изменений сигнатуры.
- `usePage().props.flash` (Inertia `@inertiajs/vue3`) — стандартный source-of-truth для toast-флагов. Паттерн уже применён для `tour_cabinet_commerce_just_archived`.

**Решение:** новых Vue-компонентов в этой фиче **не создаём**. Все правки — расширение существующих файлов (стили в `TourCabinetQuickNav.vue`, props/variant в Archives/*/Index.vue, watch-handlers в `Dashboard.vue`, flash-keys в `HandleInertiaRequests.php` + точечно в `FormPublicController.php` / `TourCabinetContestController.php` / `TourCabinetCommerceToursController.php`).

## Verification

Каждый шаг проверяется через Docker по «Command Execution Pattern» из `spec-continuation`:

- npm/vite: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — после M1, M2, M3, M4.
- Артизан: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=tour-cabinet` — после M4 (контрольная проверка, что новые flash-ключи не сломали биндинги маршрутов; ожидание: список тот же, что до фичи).
- ReadLints на каждый изменённый PHP/Vue-файл после правок.
- Tinker-smoke (transaction-rollback) — при необходимости для проверки flash-ключей: эмулировать вызов контроллера с session, прочитать `session()->get('tour_cabinet_contest_just_form_submitted')`. Опционально — основная проверка ручная (визуальный QA с открытием браузера, но он out-of-scope Docker'а).
- Запрещены: host-команды, `docker compose exec`, отсутствие `source docker/.env.*` — см. правило `spec-continuation`.

## Конфликтное сканирование

- Активные/частичные фичи в `spec/features/*/progress.md` — проверить, пересекаются ли пути:
  - `tour-cabinet-archives` — все таски Completed (см. `progress.md`); фича стабильная. Изменяемые в текущей фиче файлы (`TourCabinetQuickNav.vue`, `Archives/*/Index.vue`, `Dashboard.vue`, `HandleInertiaRequests.php`) являются результатом её работы — нынешняя фича делает поверх-стилизацию, не перетирает логику.
  - `admin-tour-users-commerce-archives` — все таски Completed, но фича в активной зоне (на момент создания не помечена `реализовано`). Пути: `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` и `app/Services/Admin/TourCabinetClientContestDataService.php` — НЕ пересекаются с текущей фичей.
  - Других overlap'ов не обнаружено.
