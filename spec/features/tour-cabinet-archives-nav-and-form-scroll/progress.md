# Progress — tour-cabinet-archives-nav-and-form-scroll

> Режим: sequential. В «Частично выполнен» одновременно должна быть не более одной задачи. Следующая берётся top-down из «Не начато» после завершения текущей.

## Completed tasks

### Task 8 — Финальный Verify-блок: build + route:list + ручной QA-чек-лист

Files: (только Docker-команды, без правок кода)

Verify:
- `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — `built in 8.45s`, без ошибок.
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=tour-cabinet` — маршруты `tour-cabinet.dashboard`, `tour-cabinet.archives.contest.{index,show}`, `tour-cabinet.archives.commerce.{index,show}`, `tour-cabinet.commerce-tours.archive-and-reset` присутствуют и не сломаны.

QA-чек-лист (для ручного прохождения в превью-окружении):
1. `/tour-cabinet` → выбор направления → submit ⇒ остаётесь рядом с блоком «Конкурс».
2. `/tour-cabinet` → выбор городов (этап 1) → submit ⇒ остаётесь рядом с блоком «Конкурс».
3. `/tour-cabinet` → удалить город → confirm ⇒ остаётесь рядом с блоком «Конкурс».
4. `/tour-cabinet` → переоткрыть выбор городов ⇒ остаётесь рядом с блоком «Конкурс».
5. `/tour-cabinet` → «Заполнить анкету» города (`/forms/{slug}`) → submit ⇒ возврат на дашборд + автоскролл к блоку «Конкурс» (амбер-кольцо).
6. `/tour-cabinet` → «Перейти к этапу 2» ⇒ остаётесь рядом с блоком «Конкурс».
7. `/tour-cabinet` → ответы этапа 2 конкурса → submit ⇒ остаётесь рядом с блоком «Конкурс».
8. `/tour-cabinet` → stage 3 конкурса → submit ⇒ остаётесь рядом с блоком «Конкурс».
9. `/tour-cabinet` → выбор города коммерческого тура → submit ⇒ остаётесь рядом с блоком «Коммерческие туры».
10. `/tour-cabinet` → выбор тура → submit ⇒ остаётесь рядом с блоком «Коммерческие туры».
11. `/tour-cabinet` → LMS-анкета коммерческого тура (`/forms/{slug}`) → submit ⇒ возврат на дашборд + автоскролл к блоку «Коммерческие туры».
12. `/tour-cabinet` → «Сохранить в архив и оформить новую заявку» (stage 3 коммерции) ⇒ редирект на дашборд + автоскролл и подсветка блока «Коммерческие туры».
13. `/tour-cabinet/archives/contest` ⇒ вкладка «Архив конкурсы» в QuickNav подсвечена тёмно-синим фоном с белым текстом; кнопка «Личный кабинет» — primary с иконкой `HomeIcon`.
14. `/tour-cabinet/archives/commerce` ⇒ вкладка «Архив коммерческих туров» подсвечена; кнопка «Личный кабинет» — primary с иконкой.

### Task 7 — Flash-флаг + автоскролл после возврата с LMS-анкеты этапа 2 коммерческого тура

Files:
- `app/Services/TourCabinetCommerceToursFormLinker.php` (после `session()->forget(...)` добавлен `session()->flash('tour_cabinet_commerce_just_form_submitted', true)` — после успешного связывания LMS-сабмита с прогрессом этапа 2 коммерции)
- `app/Http/Middleware/HandleInertiaRequests.php` (расширен массив `flash` ключом `tour_cabinet_commerce_just_form_submitted`)
- `resources/js/Pages/TourCabinet/Dashboard.vue` (новая функция `autoScrollToCommerceIfFormSubmitted()` + вызов в `onMounted` + новый `watch` на тот же flash → `scrollAndHighlight('tour-cabinet-commerce-tours')`)

Verify:
- ReadLints чисто.
- `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок (общий прогон в Task 8).

### Task 6 — Flash-флаг + автоскролл после возврата с LMS-анкеты этапа 1 конкурса

Files:
- `app/Services/TourCabinetContestFormLinker.php` (после `session()->forget(...)` добавлен `session()->flash('tour_cabinet_contest_just_form_submitted', true)` — после успешного связывания LMS-сабмита с городской анкетой конкурса)
- `app/Http/Middleware/HandleInertiaRequests.php` (расширен массив `flash` ключом `tour_cabinet_contest_just_form_submitted`)
- `resources/js/Pages/TourCabinet/Dashboard.vue` (новая функция `autoScrollToContestIfFormSubmitted()` + вызов в `onMounted` + новый `watch` на тот же flash → `scrollAndHighlight('tour-cabinet-contest-detail')`)

Verify:
- ReadLints чисто.
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=tour-cabinet` — маршруты не сломаны.

### Task 5 — Аудит preserveScroll в submit-вызовах конкурса

Files: (правки не потребовались — существующий код уже соблюдает контракт)

Аудит-результат:
- `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue` — все 5 вызовов (direction, cities, complete-stage-1, remove-city, reopen-city-selection) уже содержат `{ preserveScroll: true }`.
- `resources/js/Pages/TourCabinet/Contest/ContestStage2Panel.vue` — оба `form.post(stage2.store, { preserveScroll: true })` корректны.
- `resources/js/Pages/TourCabinet/Contest/ContestStage3Panel.vue` — `form.post(stage3.store, opts)` где `opts = { preserveScroll: true, [forceFormData: true | undefined] }` — корректно.

Verify: Grep `router\.(post|put|patch|delete)|form\.(post|put|patch|delete)` по трём файлам — все вызовы содержат `preserveScroll: true`.

### Task 4 — Дозаполнение preserveScroll: true в submit-вызовах коммерческих туров

Files:
- `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage3Panel.vue` (в `archiveAndReset()` добавлен `preserveScroll: true` в options-объект `router.post(...)`)

Аудит-результат:
- `CommerceToursStage1Panel.vue` — все 3 вызова (city.store, tour.store, complete-stage-1) уже содержали `preserveScroll: true`.
- `CommerceToursStage2Panel.vue` — `reopenForm.post(..., { preserveScroll: true })` уже корректен.
- `CommerceToursStage3Panel.vue` — единственный пропуск был исправлен.

Verify:
- ReadLints чисто.
- Существующий flash-флаг `tour_cabinet_commerce_just_archived` продолжает работать как страховка.

### Task 3 — Проверка кнопок «Личный кабинет» на детальных страницах архивов

Files: (правки не потребовались — кнопок «Личный кабинет» на Show-страницах нет)

Аудит-результат:
- `resources/js/Pages/TourCabinet/Archives/Contest/Show.vue` — в `<template #toolbar>` только кнопка «← К списку» (→ `tour-cabinet.archives.contest.index`). Возврат в «Личный кабинет» — через QuickNav (Task 1 уже усилил подсветку).
- `resources/js/Pages/TourCabinet/Archives/Commerce/Show.vue` — аналогично: только «← К списку».

Verify: Grep `Личный кабинет|tour-cabinet.dashboard` по обоим файлам — 0 совпадений.

### Task 2 — Primary-action для кнопок «Личный кабинет» на индексных страницах архивов

Files:
- `resources/js/Pages/TourCabinet/Archives/Contest/Index.vue` (`<RButton variant="outline">` → `<RButton variant="primary">`, добавлен слот `#icon` с `HomeIcon`, импорт `HomeIcon` из `@heroicons/vue/24/outline`)
- `resources/js/Pages/TourCabinet/Archives/Commerce/Index.vue` (аналогично)

Verify:
- ReadLints чисто.
- UI Kit Lookup: подтверждено наличие слота `icon` в `RButton` (см. `node_modules/@rosatom-ggr/ui-kit/dist/components/Button/RButton.vue.d.ts`).

### Task 1 — Усиленный активный стиль для вкладок «Архивы» в TourCabinetQuickNav

Files:
- `resources/js/Components/TourCabinet/TourCabinetQuickNav.vue` (функции `itemClass`/`iconClass`: active-ветка стала контрастной — `bg-[#003274] text-white font-semibold shadow-sm ring-1 ring-[#003274]/30 hover:bg-[#002357]` для пункта, `text-white` для иконки; неактивные пункты не изменены)

Verify:
- ReadLints чисто.
- Эффект распространяется на все «активные» пункты nav (Поддержка, Архив конкурсы, Архив коммерческих туров) — единая визуальная семантика «вы здесь».

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
