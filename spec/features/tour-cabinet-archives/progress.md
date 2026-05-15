# Progress — tour-cabinet-archives

> Режим: sequential. В «Частично выполнен» одновременно должна быть не более одной задачи. Следующая берётся top-down из «Не начато» после завершения текущей.

## Completed tasks

### Task 10 — Архивы в админ-карточке клиента + sync spec'ов

Files:
- `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (`use TourCabinetCommerceArchive/TourCabinetContestArchive`; `show()` дополнительно прокидывает `contestArchives` и `commerceArchives`; приватные хелперы `contestArchivesForUser` / `commerceArchivesForUser` с `Schema::hasTable`-гардами)
- `resources/js/Pages/Admin/TourCabinet/TourUsers/Show.vue` (две новые секции «Архив конкурсы» и «Архив коммерческих туров» через `<details>`-раскрывающиеся карточки с полной декомпозицией payload'а; обновлён `defineProps`)
- `spec/features/tour-cabinet-archives/spec.md` (статус «реализовано»)
- `spec/features/tour-cabinet-archives/plan.md` (статус «milestones завершены»)

Verify:
- ReadLints чисто.
- `npm run build` (Docker) — `built in 4.74s`, без ошибок.

### Task 9 — Нав-ссылки на архивы в TourCabinetQuickNav

Files:
- `resources/js/Components/TourCabinet/TourCabinetQuickNav.vue` (импорт `ArchiveBoxIcon`; две новые ссылки «Архив конкурсы» / «Архив коммерческих туров» после «Поддержка», с разделителями `|`; computed `isContestArchiveSection` / `isCommerceArchiveSection` по `page.url.startsWith('/tour-cabinet/archives/contest')` / `'/tour-cabinet/archives/commerce'`)

Verify:
- ReadLints чисто.
- `npm run build` (Docker) — `built in 5.16s`, без ошибок.
- QuickNav рендерится через `TourCabinetHeader`, который используется как в Dashboard, так и в новых Archives-страницах — нав-ссылки автоматически видны везде.

### Task 8 — Inertia-страницы архивов в ЛК

Files:
- `resources/js/Pages/TourCabinet/Archives/Contest/Index.vue` (карточки с датой, RBadge «Отправлено», направлением, кнопкой «детали», пагинация preserve-scroll)
- `resources/js/Pages/TourCabinet/Archives/Contest/Show.vue` (детальный просмотр: прогресс/направление, Этап 1 — анкеты по городам, Этап 2 — Q&A, Этап 3 — текст/видео/имя файла; read-only)
- `resources/js/Pages/TourCabinet/Archives/Commerce/Index.vue` (карточки с датой, городом+туром, RBadge «Отправлено»)
- `resources/js/Pages/TourCabinet/Archives/Commerce/Show.vue` (детальный просмотр: город, тур, ответы LMS-формы, snapshot текста этапа 3)

Verify:
- ReadLints чисто.
- `npm run build` (Docker) — `built in 5.50s`, без ошибок.

### Task 7 — Маршруты и контроллеры архивов в ЛК

Files:
- `app/Http/Controllers/TourCabinet/Archives/ContestArchiveController.php` (`index`, `show`; гард `archive->user_id === auth()->id()` → 404)
- `app/Http/Controllers/TourCabinet/Archives/CommerceArchiveController.php` (аналогично)
- `routes/web.php` (4 новых GET-маршрута под middleware `tour-cabinet`, БЕЗ `tour-cabinet.profile-complete`; `whereNumber('archive')` для route-model-binding)

Verify:
- ReadLints чисто.
- `php artisan route:list --name=tour-cabinet.archives` (Docker) — 4 маршрута: `tour-cabinet.archives.contest.{index,show}`, `tour-cabinet.archives.commerce.{index,show}`.

### Task 6 — UX коммерческих туров на дашборде: автоскролл и подсветка

Files:
- `resources/js/Pages/TourCabinet/Dashboard.vue` (import `usePage`; функция `autoScrollToCommerceArchiveIfFlashed` + вызов в `onMounted`; `watch` на `inertiaPage.props.flash.tour_cabinet_commerce_just_archived` → `nextTick(scrollAndHighlight('tour-cabinet-commerce-tours'))`. Реюз существующего `scrollAndHighlight` и `highlightedAnchor` — кольцевая amber-подсветка уже навешена на `<section id="tour-cabinet-commerce-tours">` ранее.)

Verify:
- ReadLints чисто.
- `npm run build` (Docker) — `built in 4.84s`, без ошибок.

### Task 5 — Сервис архивации коммерческих туров + reset + flash

Files:
- `app/Services/TourCabinetCommerceArchiveService.php` (новый сервис: snapshot city/tour/responses/stage3_notification + создаёт архив + сбрасывает progress в начальное состояние)
- `app/Services/TourCabinetCommerceToursFormLinker.php` (вместо прямого `current_stage=3` save — делегирование в archive-service; session-flash `success` + `tour_cabinet_commerce_just_archived`; session-marker `tour_cabinet_commerce_redirect_to_dashboard` для controller'а; fallback: если архивация упала — старая логика `current_stage=3` чтобы не «потерять» сабмит)
- `app/Http/Controllers/Lms/FormPublicController.php` (после linker'а: `session()->pull('tour_cabinet_commerce_redirect_to_dashboard')` → redirect на `tour-cabinet.dashboard#tour-cabinet-commerce-tours`)

Verify:
- ReadLints чисто.
- tinker-smoke (transaction-rollback) с реальной City/Tour/LmsForm: первая архивация → archive id=1, progress полностью сброшен в `current_stage=1`, city_id/tour_id/lms_form_submission_id/completed_at = null; payload содержит ключи `city,tour,lms_form,stage3_notification`; вторая итерация → archive id=2, итого 2 записи на пользователя.

### Task 4 — UI блока «Конкурс» в Dashboard.vue при `contestArchived === true`

Files:
- `app/Services/TourCabinetContestDashboardData.php` (прокинут `archived` + `archived_at` в `contestProgress`)
- `resources/js/Pages/TourCabinet/Dashboard.vue` (импорт `LockClosedIcon`, computed `contestArchived`, плашка-уведомление над `#tour-cabinet-contest-detail` с кнопкой «Перейти в архив» (Link → `/tour-cabinet/archives/contest`), classes `pointer-events-none select-none opacity-50 grayscale` + `aria-hidden` на самой карточке с этапами при `contestArchived`)

Verify:
- ReadLints чисто.
- `npm run build` (Docker) — `built in 7.38s`, без ошибок.

### Task 3 — Блокировка mutating-роутов конкурса при `archived_at !== null`

Files:
- `app/Http/Controllers/TourCabinetContestController.php` (приватный helper `redirectIfContestArchived` + ранний `return` в 8 mutating-методах: `storeDirection`, `storeCities`, `reopenCitySelection`, `removeSelectedCity`, `startCityForm`, `completeStage1`, `storeStage2`, `storeStage3`)

Verify:
- ReadLints чисто.
- tinker-smoke (transaction-rollback) через reflection на приватный helper: archived user → `RedirectResponse` на `tour-cabinet#tour-cabinet-contest` с flash error `Заявка на конкурс уже отправлена. Просмотр — в Архиве конкурсы.`; user без progress → `null` (новый участник, гард не срабатывает); progress без `archived_at` → `null` (середина прохождения, гард не срабатывает).

### Task 2 — Сервис архивации конкурса + триггер в storeStage3

Files:
- `app/Services/TourCabinetContestArchiveService.php`
- `app/Http/Controllers/TourCabinetContestController.php`

Verify:
- ReadLints чисто.
- tinker-smoke (transaction-rollback): первичная архивация создаёт запись, проставляет `progress.archived_at`; повторный вызов идемпотентен (возвращает тот же id, дубль не создаётся); итого 1 запись в архиве с `status='sent'`, `payload` содержит ключи `progress`, `stage1_city_forms`, `stage2_qa`, `stage3`, `meta`.

### Task 1 — Миграции и модели архивов

Files:
- `database/migrations/2026_05_14_120000_create_tour_cabinet_contest_archives_table.php`
- `database/migrations/2026_05_14_120100_create_tour_cabinet_commerce_archives_table.php`
- `database/migrations/2026_05_14_120200_add_archived_at_to_tour_cabinet_contest_progress.php`
- `app/Models/TourCabinetContestArchive.php`
- `app/Models/TourCabinetCommerceArchive.php`
- `app/Models/TourCabinetContestProgress.php`
- `app/Models/User.php`

Verify:
- `php artisan migrate` (Docker) — три миграции DONE без ошибок.
- tinker-smoke: таблицы `tour_cabinet_contest_archives`/`tour_cabinet_commerce_archives` существуют, колонка `archived_at` на progress есть, fillable+cast корректны, `User->tourCabinetContestArchives()`/`tourCabinetCommerceArchives()` возвращают пустые коллекции.
- ReadLints по 7 затронутым файлам — чисто.

## Partially completed

(пусто)

## Not started

(пусто)
3. Task 3 — Блокировка mutating-роутов конкурса при `archived_at !== null`
4. Task 4 — UI блока «Конкурс» в Dashboard.vue при `contestArchived === true`
5. Task 5 — Сервис архивации коммерческих туров + reset + flash
6. Task 6 — UX коммерческих туров на дашборде: автоскролл и подсветка
7. Task 7 — Маршруты и контроллеры архивов в ЛК
8. Task 8 — Inertia-страницы архивов в ЛК
9. Task 9 — Нав-ссылки на архивы в TourCabinetQuickNav
10. Task 10 — Архивы в админ-карточке клиента + sync spec'ов

## Open issues

(пусто)
