# Задачи — tour-cabinet-archives

## Task 1 — Миграции и модели архивов

- **Goal**: создать таблицы `tour_cabinet_contest_archives`, `tour_cabinet_commerce_archives`, добавить колонку `archived_at` в `tour_cabinet_contest_progress`; завести модели `TourCabinetContestArchive`, `TourCabinetCommerceArchive` со связями.
- **Scope**: `database/migrations/2026_05_14_*_create_tour_cabinet_contest_archives_table.php`, `..._create_tour_cabinet_commerce_archives_table.php`, `..._add_archived_at_to_tour_cabinet_contest_progress.php`; `app/Models/TourCabinetContestArchive.php`, `app/Models/TourCabinetCommerceArchive.php`; `app/Models/TourCabinetContestProgress.php` (fillable+cast `archived_at`); `app/Models/User.php` (hasMany-связи `tourCabinetContestArchives`, `tourCabinetCommerceArchives`).
- **DoD**: `php artisan migrate` отрабатывает на чистой БД; `php artisan tinker --execute=...` — модели создаются и связи возвращают пустые коллекции для тестового пользователя; sqlite-совместимость подтверждена (миграции только `Schema::create` + `addColumn`).
- **Verify**: Docker-pattern из `spec-continuation` (`source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate` + tinker-smoke).

## Task 2 — Сервис архивации конкурса + триггер в storeStage3

- **Goal**: реализовать `App\Services\TourCabinetContestArchiveService::archiveProgress`, который через реюз `TourCabinetClientContestDataService::contestPayloadForUser` создаёт запись в `tour_cabinet_contest_archives` и проставляет `progress->archived_at = now()`; подключить вызов в `TourCabinetContestController::storeStage3` после `dispatchContestCompletionNotificationIfReady` (3 ветки storeStage3).
- **Scope**: `app/Services/TourCabinetContestArchiveService.php`, `app/Http/Controllers/TourCabinetContestController.php` (3 точки вставки), `app/Services/Admin/TourCabinetClientContestDataService.php` (только если требуется extract-метод для реюза без админ-контекста).
- **DoD**: после прохождения этапа 3 в архиве появляется ровно одна запись с непустым `payload`; повторный submit не дублирует архив (защита через `archived_at !== null`); ошибки сервиса логируются (`tour_cabinet_contest_archive_failed`) и не валят сохранение этапа 3.
- **Verify**: Docker-pattern + tinker — ручной прогон `archiveProgress` для тестового progress.

## Task 3 — Блокировка mutating-роутов конкурса при `archived_at !== null`

- **Goal**: запретить участнику менять данные конкурса после архивации даже после перезагрузки страницы; добавить inline-проверку `archived_at !== null` в `TourCabinetContestController` (`storeDirection`, `storeCities`, `removeSelectedCity`, `completeStage1`, `storeStage2`, `storeStage3`, `startStage1Form`) либо middleware `EnsureTourCabinetContestNotArchived`.
- **Scope**: `app/Http/Controllers/TourCabinetContestController.php` (или новый `app/Http/Middleware/EnsureTourCabinetContestNotArchived.php` + регистрация в `routes/web.php`). До 5 файлов.
- **DoD**: все mutating-роуты конкурса при `archived_at !== null` возвращают редирект на дашборд с flash-error «Заявка на конкурс уже отправлена. Просмотр — в Архиве конкурсы.»; GET-маршруты дашборда продолжают работать; `route:list | grep contest` без изменений сигнатур.
- **Verify**: Docker-pattern + tinker-smoke (`$controller->storeDirection($mockRequest)` → redirect с error).

## Task 4 — UI блока «Конкурс» в Dashboard.vue при `contestArchived === true`

- **Goal**: на дашборде ЛК при `archived_at !== null` показать плашку-уведомление с текстом из ТЗ, кнопку «Перейти в архив», и затемнить панели Этапов 1–3 (`pointer-events-none opacity-50 grayscale`); скрыть кнопки сабмита внутри панелей.
- **Scope**: `app/Http/Controllers/TourCabinetController.php` (расширить Inertia-payload флагом `contestArchived`), `resources/js/Pages/TourCabinet/Dashboard.vue` (новый computed `contestArchived` + плашка-нотис + классы на секцию `#tour-cabinet-contest-detail`). До 5 файлов.
- **DoD**: визуально блок конкурса показывает плашку с информсообщением; панели не реагируют на клики; кнопка «Перейти в архив» ведёт на `tour-cabinet.archives.contest.index`; `npm run build` без ошибок.
- **Verify**: Docker-pattern (`npm run build`) + ручной smoke через `tinker` (форсировать `archived_at` у тестового progress и проверить payload).

## Task 5 — Сервис архивации коммерческих туров + reset + flash

- **Goal**: реализовать `App\Services\TourCabinetCommerceArchiveService::archiveAndResetProgress(progress, user, submission)`: собирает снапшот (city/tour/lms-responses/stage3-notification), создаёт запись `tour_cabinet_commerce_archives`, обнуляет `progress` (`current_stage = 1`, обнуление city_id/tour_id/lms_form_submission_id/completed_at); подключить вызов в `TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission` ВЗАМЕН текущего «`current_stage = 3`»-сейва; добавить session-flash `tour_cabinet_commerce_just_archived = true` + flash-сообщение.
- **Scope**: `app/Services/TourCabinetCommerceArchiveService.php`, `app/Services/TourCabinetCommerceToursFormLinker.php` (заменить логику save → вызов сервиса + flash). До 5 файлов.
- **DoD**: после сабмита анкеты этапа 2 в архиве появляется новая запись; `progress` пользователя обнуляется (`current_stage = 1`, city_id/tour_id/submission_id/completed_at = null); session содержит `flash.success` и `flash.tour_cabinet_commerce_just_archived = true`; повторный цикл (выбор города → тур → сабмит) создаёт вторую запись в архиве.
- **Verify**: Docker-pattern + tinker (форсировать сабмит и проверить состояние таблиц).

## Task 6 — UX коммерческих туров на дашборде: автоскролл и подсветка

- **Goal**: на `Dashboard.vue` при `$page.props.flash?.tour_cabinet_commerce_just_archived === true` — на `onMounted` через `nextTick` скроллить к `#tour-cabinet-commerce-tours` (`scrollIntoView({behavior: 'smooth', block: 'start'})`) и подсвечивать блок временным классом `ring-2 ring-emerald-200` на 1.5–2 секунды; показывать flash-success как обычно.
- **Scope**: `resources/js/Pages/TourCabinet/Dashboard.vue` (новый watcher + ref для контейнера блока). До 2 файлов.
- **DoD**: после сабмита анкеты этапа 2 и редиректа на дашборд страница автоматически прокручивается к блоку «Коммерческие туры»; блок кратко подсвечен; сообщение «Новая заявка может быть создана прямо сейчас.» отображается; `npm run build` без ошибок.
- **Verify**: Docker-pattern (`npm run build`) + ручной smoke в браузере через Playwright (опционально).

## Task 7 — Маршруты и контроллеры архивов в ЛК

- **Goal**: реализовать GET-маршруты архивов и контроллеры `App\Http\Controllers\TourCabinet\Archives\ContestArchiveController` (`index`, `show`) и `CommerceArchiveController` (`index`, `show`); регистрация под middleware `tour-cabinet` (БЕЗ `tour-cabinet.profile-complete`); гард `user_id` в `show`.
- **Scope**: `app/Http/Controllers/TourCabinet/Archives/ContestArchiveController.php`, `CommerceArchiveController.php`; `routes/web.php` (4 новых GET-маршрута). До 5 файлов.
- **DoD**: `php artisan route:list | grep archives` показывает 4 маршрута; вызов `index` возвращает Inertia-response с массивом архивов пользователя (сортировка `submitted_at DESC`); `show` возвращает 404 при доступе к чужой записи.
- **Verify**: Docker-pattern (`route:list` + tinker-smoke).

## Task 8 — Inertia-страницы архивов в ЛК

- **Goal**: создать 4 Vue-страницы: `Archives/Contest/Index.vue`, `Archives/Contest/Show.vue`, `Archives/Commerce/Index.vue`, `Archives/Commerce/Show.vue` с использованием `RCard`, `RBadge`, `RButton`, Inertia `Link`, иконок Heroicons; в детальной странице конкурсной заявки реюзить шаблон админ-карточки (`Admin/TourCabinet/TourUsers/Show.vue`, блоки Этап 1 / 2 / 3) без admin-кнопок.
- **Scope**: 4 Vue-файла в `resources/js/Pages/TourCabinet/Archives/`; при необходимости — дочерние компоненты `Stage1Block.vue` / `Stage2Block.vue` / `Stage3Block.vue` (см. `plan.md`). До 5 файлов.
- **DoD**: список архива отображает карточки с датой и `RBadge` «Отправлено», сортировкой `submitted_at DESC`; детальная страница отображает полный snapshot из `payload`; `npm run build` без ошибок; ReadLints — чисто.
- **Verify**: Docker-pattern (`npm run build`).

## Task 9 — Нав-ссылки на архивы в TourCabinetQuickNav

- **Goal**: добавить ссылки «Архив конкурсы» и «Архив коммерческих туров» в `TourCabinetQuickNav.vue` после «Поддержка»; подсветка активной ссылки при нахождении на `/tour-cabinet/archives/...`.
- **Scope**: `resources/js/Components/TourCabinet/TourCabinetQuickNav.vue`. 1 файл.
- **DoD**: ссылки видны во всех страницах ЛК (Dashboard, Support, новые Archives-страницы); активная ссылка корректно подсвечена; `npm run build` без ошибок.
- **Verify**: Docker-pattern (`npm run build`).

## Task 10 — Архивы в админ-карточке клиента + sync spec'ов

- **Goal**: добавить две секции «Архив конкурсы» и «Архив коммерческих туров» в `Admin/TourCabinet/TourUsers/Show.vue` (раскрывающиеся карточки с полными данными), расширить `TourCabinetTourUsersController::show` загрузкой архивов через `Schema::hasTable`; обновить `spec/features/tour-cabinet/spec.md` упоминанием архивов (раздел «Архивы заявок»), `spec/features/admin-settings-reset-contest-progress/spec.md` явным указанием «архивы не трогаем» в Out-of-scope.
- **Scope**: `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (расширить `show`-payload), `resources/js/Pages/Admin/TourCabinet/TourUsers/Show.vue` (2 новые секции), `spec/features/tour-cabinet/spec.md`, `spec/features/admin-settings-reset-contest-progress/spec.md`. До 5 файлов.
- **DoD**: на странице `/admin/tour-cabinet/tour-users/{user}` видны архивы пользователя; существующий блок «Конкурс и заявки на туры» остаётся; админ-reset через `admin.settings.contest-reset.reset` архивы не удаляет (verified через tinker); spec-документы синхронизированы с реализацией; `npm run build` без ошибок.
- **Verify**: Docker-pattern (`npm run build` + tinker-smoke на reset).
