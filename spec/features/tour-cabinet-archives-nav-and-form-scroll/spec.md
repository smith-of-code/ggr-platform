# UX-доработка: подсветка навигации архивов и сохранение прокрутки форм в ЛК (tour-cabinet-archives-nav-and-form-scroll)

## Goal

Усилить визуальное выделение активных пунктов «Архив конкурсы» / «Архив коммерческих туров» в `TourCabinetQuickNav` и кнопки «Личный кабинет» на индексных страницах архивов, а также гарантировать, что после отправки **любой** формы в блоках «Конкурс» и «Коммерческие туры» дашборда `/tour-cabinet` страница НЕ прокручивается в начало, а сохраняет позицию рядом с отправленной формой (включая возврат с отдельных LMS-страниц анкет этапа 1 конкурса и этапа 2 коммерческого тура).

## In-scope

- **Подсветка вкладок «Архивы» в `TourCabinetQuickNav.vue`**: усилить стиль активного пункта (текущий `bg-[#003274]/10 text-[#003274]` слабо контрастен на белой шапке). На страницах с `page.url.startsWith('/tour-cabinet/archives/contest')` и `'/tour-cabinet/archives/commerce')` активный `Link` должен иметь более тёмный фон (например, `bg-[#003274] text-white`), жирный шрифт (`font-semibold`) и опционально нижнюю акцентную полосу. Иконка `ArchiveBoxIcon` тоже белая в активном состоянии. Изменения применяются и для «Архив конкурсы», и для «Архив коммерческих туров».
- **Подсветка кнопки «Личный кабинет»** в `RButton`-toolbar на `resources/js/Pages/TourCabinet/Archives/Contest/Index.vue` и `resources/js/Pages/TourCabinet/Archives/Commerce/Index.vue`: поменять `variant="outline"` на `variant="primary"` (или эквивалент с заметным заливочным цветом — `bg-[#003274] text-white`), добавить иконку `HomeIcon`/`ArrowLeftIcon` слева для визуальной подсказки «возврат в ЛК».
- **Аудит `preserveScroll: true` для всех submit-обработчиков** в панелях `Contest/ContestStage1Panel.vue`, `Contest/ContestStage2Panel.vue`, `Contest/ContestStage3Panel.vue`, `CommerceTours/CommerceToursStage1Panel.vue`, `CommerceTours/CommerceToursStage2Panel.vue`, `CommerceTours/CommerceToursStage3Panel.vue`. Каждый `router.post/put/patch/delete` и `useForm().post(...)` должен явно передавать `{ preserveScroll: true }`. Известные пропуски:
  - `CommerceToursStage3Panel.vue`: `archiveAndReset()` вызывает `router.post(route('tour-cabinet.commerce-tours.archive-and-reset'), {}, { onFinish: ... })` без `preserveScroll`. Дополнить (хотя поверх работает flash-флаг `tour_cabinet_commerce_just_archived`, опция `preserveScroll: true` страхует и снимает мерцание в начало страницы перед автоскроллом).
- **Сохранение позиции после возврата с отдельных LMS-страниц анкет**:
  - **Конкурс, этап 1, анкета города** (`tour-cabinet.contest.start-city-form` → `/forms/{slug}` → submit → редирект на `tour-cabinet.dashboard`): пользователь оказывается в самом верху страницы. Решение — на стороне `FormPublicController` (или в `TourCabinetContestController::startCityForm`) ставить session-flash `tour_cabinet_contest_just_submitted = true` ИЛИ редирект с фрагментом `#tour-cabinet-contest`, плюс в `Dashboard.vue` добавить `watch` по аналогии с уже существующим `tour_cabinet_commerce_just_archived` → `scrollAndHighlight('tour-cabinet-contest')`. Реюз функции `scrollAndHighlight` и состояния `highlightedAnchor`.
  - **Коммерческие туры, этап 2, LMS-анкета** (`tour-cabinet.commerce-tours.tour.store` → `/forms/{slug}` → submit → редирект на `tour-cabinet.dashboard`): аналогично, ставить flash `tour_cabinet_commerce_just_form_submitted = true` (отдельный от `_just_archived`, т.к. архивация теперь — отдельный шаг 3), `Dashboard.vue` слушает и скроллит к `'tour-cabinet-commerce-tours'`. Flash-ключ обязательно регистрируется в `HandleInertiaRequests::share()['flash']` — иначе Inertia его не передаст.
- **Тест прохождения**: пройти руками full flow от выбора направления до stage 3, проверить, что после каждого submit (направление, города, удаление города, переоткрытие выбора, complete-stage-1, сабмит LMS-анкеты этапа 1 города, ответы этапа 2 конкурса, сабмит stage3, выбор города коммерческого тура, выбор тура, сабмит LMS-анкеты этапа 2 коммерции, archive-and-reset этапа 3 коммерции) — viewport остаётся в районе формы.

## Out-of-scope

- Изменение поведения и логики самих submit-эндпоинтов (контроллеры/сервисы) — правки только UX-уровня (preserveScroll, flash-ключи, автоскролл, стили nav/кнопок).
- Добавление новых маршрутов или таблиц (фича чисто фронтенд + 1–2 строки во `HandleInertiaRequests`/контроллер для flash-ключей).
- Изменение содержимого панелей `Stage1/2/3Panel.vue` за пределами submit-вызовов (логика валидации, поля формы, тексты — не трогаем).
- Кастомизация подсветки nav из админки/settings — хардкод в `TourCabinetQuickNav.vue` (точечная задача из репорта).
- Сглаживание `scrollIntoView` (smooth/auto) — оставляем как в существующем `scrollAndHighlight`.
- Аудит и подсветка других секций ЛК (Поддержка, Документы) — только то, что упомянуто в репорте: «Архивы» и «Личный кабинет».
- Изменения в `Archives/*/Show.vue` (toolbar там не содержит дублирующейся кнопки «Личный кабинет» — только заголовок и навигация назад через QuickNav, что уже подсвечивается); проверить и, если кнопка там есть, действовать аналогично Index.vue.

## Constraints

- Все команды (build, route:list, tinker) — через Docker по правилу `spec-continuation`: `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
- Реюз существующих ассетов (правило `Reuse before create`):
  - `scrollAndHighlight(anchorId)` и `highlightedAnchor` в `Dashboard.vue` — уже есть, реюзим для контеста и коммерции.
  - UI-Kit `RButton` (variant `primary`/`outline`), Heroicons (`HomeIcon`, `ArrowLeftIcon`, `ArchiveBoxIcon`) — глобально зарегистрированы.
  - Flash-механизм через `HandleInertiaRequests::share()` — паттерн уже использован для `tour_cabinet_commerce_just_archived` (см. фичу `tour-cabinet-archives`, hotfix двухшаговой архивации).
- Лимит на размер одного шага — до 5 файлов и до ~150 строк diff (см. правило `Change scope limits`). Деление на задачи в `tasks.md` соответствует этому ограничению.
- Не использовать `window.location.hash` для скролла — Inertia-навигация теряет hash; только flash-флаг + `nextTick` + `scrollIntoView`.
- preserveScroll: true применяется ТОЛЬКО к запросам, ведущим на тот же дашборд (server возвращает `redirect()->route('tour-cabinet.dashboard')` или Inertia-ответ `Dashboard`). Для редиректов на LMS-страницы анкет он бесполезен — там работает flash-флаг + автоскролл при возврате.

## Open questions

(пусто — все детали подтверждены кодом: интерфейсы `TourCabinetQuickNav.vue`, `Dashboard.vue::scrollAndHighlight`, `HandleInertiaRequests::share()['flash']` существуют и доступны для реюза; submit-обработчики панелей перечислены явно.)
