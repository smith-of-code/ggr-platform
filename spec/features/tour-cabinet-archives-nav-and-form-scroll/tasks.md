# Задачи — tour-cabinet-archives-nav-and-form-scroll

## Task 1 — Усиленный активный стиль для вкладок «Архивы» в `TourCabinetQuickNav`

- **Goal**: в `itemClass(active)` дополнить ветку «активный» для пунктов «Архив конкурсы» / «Архив коммерческих туров»: вместо текущего `bg-[#003274]/10 text-[#003274]` ставить высококонтрастный `bg-[#003274] text-white font-semibold` + иконка `text-white`. Текущая ветка «active» переиспользуется через `isContestArchiveSection || isCommerceArchiveSection`.
- **Scope**: `resources/js/Components/TourCabinet/TourCabinetQuickNav.vue` (функции `itemClass`, `iconClass`; опционально — extra `border-b-2 border-[#003274]` или иной акцент). 1 файл.
- **DoD**: при заходе на `/tour-cabinet/archives/contest` или `/tour-cabinet/archives/commerce` соответствующий пункт визуально выделяется тёмно-синим фоном с белым текстом и иконкой; неактивные пункты (включая «На сайт», «Поддержка», «ВШГР — обучение») сохраняют прежний стиль; ReadLints чисто.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок. Визуальный QA отложен на M5.

## Task 2 — Primary-action для кнопок «Личный кабинет» на индексных страницах архивов

- **Goal**: в обоих файлах сменить `<RButton variant="outline">` → `<RButton variant="primary">` для кнопки «Личный кабинет» и добавить иконку `HomeIcon` слева (по аналогии с пунктами nav). Опционально — увеличить `size="md"` для большей заметности.
- **Scope**: `resources/js/Pages/TourCabinet/Archives/Contest/Index.vue` (toolbar), `resources/js/Pages/TourCabinet/Archives/Commerce/Index.vue` (toolbar). 2 файла.
- **DoD**: кнопки «Личный кабинет» на обеих страницах архива визуально доминируют над соседней кнопкой «Выход» (или «Logout»); иконка `HomeIcon` отображается слева; кликабельность сохранена (`Link` → `tour-cabinet.dashboard`).
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок. ReadLints на оба файла чисто.

## Task 3 — Проверка кнопок «Личный кабинет» на детальных страницах архивов

- **Goal**: проверить, есть ли кнопки «Личный кабинет» в `Archives/Contest/Show.vue` и `Archives/Commerce/Show.vue`. Если есть — применить такую же замену variant (Task 2). Если нет — ничего не делать, зафиксировать в `progress.md` «детальные страницы кнопки не содержат, action не нужен».
- **Scope**: `resources/js/Pages/TourCabinet/Archives/Contest/Show.vue`, `resources/js/Pages/TourCabinet/Archives/Commerce/Show.vue`. До 2 файлов (могут оба оказаться без правок).
- **DoD**: либо обе Show-страницы содержат primary-кнопку «Личный кабинет», либо в `progress.md` явно зафиксировано «правки не требовались».
- **Verify**: Grep `Личный кабинет` по обоим файлам; при правке — `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`.

## Task 4 — Дозаполнение `preserveScroll: true` в submit-вызовах коммерческих туров

- **Goal**: пройти Grep'ом `router.post|router.put|router.patch|router.delete|form.post|form.put|form.patch|form.delete` по `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage1Panel.vue`, `CommerceToursStage2Panel.vue`, `CommerceToursStage3Panel.vue`. Для каждого вызова, где опция `preserveScroll: true` отсутствует, добавить её в options-объект. Известный пропуск: `CommerceToursStage3Panel.archiveAndReset()` — `router.post(route('tour-cabinet.commerce-tours.archive-and-reset'), {}, { onFinish: ... })` → дополнить `preserveScroll: true`.
- **Scope**: `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage1Panel.vue`, `CommerceToursStage2Panel.vue`, `CommerceToursStage3Panel.vue`. До 3 файлов.
- **DoD**: во всех submit/router-вызовах коммерческих панелей присутствует `preserveScroll: true`; после нажатия любой кнопки в этих панелях viewport не прыгает в начало страницы (визуальный QA в Task 8). Существующий flash-флаг `tour_cabinet_commerce_just_archived` продолжает работать как страховка.
- **Verify**: Grep по трём файлам — все `router.post/form.post` содержат `preserveScroll: true`; `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`; ReadLints чисто.

## Task 5 — Аудит `preserveScroll: true` в submit-вызовах конкурса

- **Goal**: Grep по `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue`, `ContestStage2Panel.vue`, `ContestStage3Panel.vue`. Аналогично Task 4 — для каждого `router.post|form.post|...` без `preserveScroll: true` дозаполнить. На момент написания спека все три файла уже содержат `preserveScroll: true` в каждом вызове — задача может оказаться no-op, но обязательная для контроля.
- **Scope**: `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue`, `ContestStage2Panel.vue`, `ContestStage3Panel.vue`. До 3 файлов (вероятно 0).
- **DoD**: во всех submit/router-вызовах конкурсных панелей присутствует `preserveScroll: true`; если правок не требовалось — зафиксировать в `progress.md` «существующий код уже соблюдает контракт».
- **Verify**: Grep по трём файлам — все вызовы содержат `preserveScroll: true`; ReadLints чисто.

## Task 6 — Flash-флаг + автоскролл после возврата с LMS-анкеты этапа 1 конкурса

- **Goal**: после успешного сабмита LMS-формы анкеты города конкурса (триггер — `FormPublicController` или `TourCabinetContestController::startCityForm` finisher; финализация по сценарию идёт через `tryLinkAfterSubmission`-аналог или `redirect()->route('tour-cabinet.dashboard')`) ставить session-flash `tour_cabinet_contest_just_form_submitted = true`. Зарегистрировать ключ в `App\Http\Middleware\HandleInertiaRequests::share()['flash']`. В `Dashboard.vue` добавить `watch` на `inertiaPage.props.flash.tour_cabinet_contest_just_form_submitted` → `nextTick(() => scrollAndHighlight('tour-cabinet-contest'))` (или `'tour-cabinet-contest-detail'` — выбрать самый видимый якорь).
- **Scope**: `app/Http/Controllers/Lms/FormPublicController.php` ИЛИ `app/Services/TourCabinetCommerceToursFormLinker.php`-аналог для конкурса (точное место установки flash определит при имплементации; ориентир — где сейчас редиректят на `tour-cabinet.dashboard` после сабмита LMS-формы конкурса), `app/Http/Middleware/HandleInertiaRequests.php`, `resources/js/Pages/TourCabinet/Dashboard.vue`. До 3 файлов.
- **DoD**: после сабмита анкеты города этапа 1 пользователь возвращается на дашборд и автоматически скроллится к секции «Конкурс»; flash-флаг очищается одноразово (стандартное поведение Laravel session-flash).
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=tour-cabinet.contest` — маршруты не сломаны; `npm run build` — без ошибок; tinker-smoke (при необходимости) — проверить, что `HandleInertiaRequests::share()['flash']` содержит ключ `tour_cabinet_contest_just_form_submitted`.

## Task 7 — Flash-флаг + автоскролл после возврата с LMS-анкеты этапа 2 коммерческого тура

- **Goal**: аналогично Task 6, но для коммерческой LMS-анкеты этапа 2. Триггер — `TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission` (после сохранения `current_stage = 3` и до редиректа на дашборд). Ставить session-flash `tour_cabinet_commerce_just_form_submitted = true` (отдельно от `tour_cabinet_commerce_just_archived`, чтобы различать «сабмит анкеты этапа 2» и «архивация этапа 3»). Зарегистрировать в `HandleInertiaRequests::share()['flash']`. В `Dashboard.vue` — `watch` → `scrollAndHighlight('tour-cabinet-commerce-tours')`.
- **Scope**: `app/Services/TourCabinetCommerceToursFormLinker.php`, `app/Http/Middleware/HandleInertiaRequests.php` (один блок share — расширяем тот же массив `flash`, что и в Task 6), `resources/js/Pages/TourCabinet/Dashboard.vue` (второй watch). До 3 файлов.
- **DoD**: после сабмита LMS-анкеты коммерческого тура (этап 2) пользователь возвращается на дашборд и автоматически скроллится к секции «Коммерческие туры»; существующий поток `tour_cabinet_commerce_just_archived` (этап 3 archive-and-reset) продолжает работать независимо.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`; tinker-smoke (transaction-rollback) — после вызова `tryLinkAfterSubmission(...)` `session()->get('tour_cabinet_commerce_just_form_submitted') === true`.

## Task 8 — Финальный Verify-блок: build + route:list + ручной QA-чек-лист

- **Goal**: финальная Docker-верификация: `npm run build` (целиковая сборка после всех правок), `php artisan route:list --name=tour-cabinet` (контроль, что маршруты не сломаны и нет неожиданных дублей). Опционально — `php artisan route:list --name=lms.forms` (контроль форм-маршрутов, через которые проходят LMS-анкеты конкурса и коммерции). Составить чек-лист из ~12 пунктов для ручного QA (направление → submit, города → submit, удаление города, complete-stage-1, анкета города (через LMS-форму) → submit → возврат на дашборд, ответы этапа 2 → submit, stage3 → submit, выбор города коммерции → submit, выбор тура → submit, LMS-анкета коммерции → submit → возврат на дашборд, archive-and-reset → submit). На каждом пункте проверять, что viewport остаётся рядом с формой / соответствующая секция плавно подсвечивается amber-кольцом.
- **Scope**: только проверки (без правок кода). 0 файлов.
- **DoD**: `npm run build` и `route:list` зелёные; чек-лист из 12 пунктов записан в `progress.md` (Completed tasks → Task 8); все пункты пройдены вручную (или зафиксированы как «требует браузерного QA, проверить в превью-окружении»).
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`; `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=tour-cabinet`.
