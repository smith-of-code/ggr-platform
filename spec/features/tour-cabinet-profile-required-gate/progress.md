# Progress: tour-cabinet-profile-required-gate

## Не начато

— (пусто)

## Частично выполнено

— (пусто)

## Выполнено

- **Задача 9 — Финальный verify + Реализация (как сделано)** (`spec/features/tour-cabinet-profile-required-gate/spec.md`, `spec/features/tour-cabinet-profile-required-gate/progress.md`):
  - End-to-end `php artisan tinker` (Docker, transaction-rollback): пустой профиль → `profileGate.complete=0 missing=[last_name,first_name,gender,birth_date,phone,personal_data_consent]`; полный → `complete=1 missing=[]`; middleware на пустом профиле → `302 redirect`, на полном → `200 passthrough`.
  - `npm run build` (Docker) — `built in 7.98s`. `ReadLints` по всем затронутым файлам — чисто.
  - Существующий тест `TourCabinetSupportTicketTest` падает на pre-existing sqlite-проблеме миграции `2026_04_24_100100` (см. `spec/90-open-questions.md` пункт 9), не связана с фичей.
  - Заполнен раздел «Реализация (как сделано)» в `spec.md` фичи.
  - Files: `spec/features/tour-cabinet-profile-required-gate/spec.md`, `spec/features/tour-cabinet-profile-required-gate/progress.md`.
- **Задача 8 — Обновить родительский спек** (`spec/features/tour-cabinet/spec.md`):
  - В раздел «Документы участника и модерация», в перечень `TourCabinetDocument::allowedTypes()` добавлен `personal_data_consent` («Согласие на обработку персональных данных», обязательный) со ссылкой на текущую фичу.
  - В раздел «Защита дашборда» добавлен абзац про middleware `tour-cabinet.profile-complete`, `TourCabinetProfileCompleteness::isComplete`, поведение GET/mutating, исключения (профиль, документы, поддержка, upload, logout), а также абзац про Inertia-проп `profileGate` и UI-блокировку секций.
  - Verify: `git diff spec/features/tour-cabinet/spec.md` — точечные правки, история не переписана.
  - Files: `spec/features/tour-cabinet/spec.md`.
- **Задача 7 — Блок согласия на ОПД в `#tour-cabinet-documents`** (`resources/js/Pages/TourCabinet/Dashboard.vue`):
  - Запись `{ type: 'personal_data_consent', label: 'Согласие на обработку персональных данных *', required: true, hint: 'Обязательный документ — без него нельзя приступить к этапам конкурса и заявкам на туры.' }` добавлена первой в `docConfig`.
  - Шаблон элемента списка теперь применяет акцентную рамку/фон (`border-amber-300 bg-amber-50/70`) для `dt.required === true` и отображает строку-подсказку `dt.hint` (font-semibold, amber-900). Кнопки `Загрузить` / `Заменить` / `Удалить` работают через существующий эндпоинт `tour-cabinet.profile.documents.upload` без изменений.
  - Verify: `npm run build` (Docker) — `built in 8.53s`. `ReadLints` — чисто.
  - Files: `resources/js/Pages/TourCabinet/Dashboard.vue`.
- **Задача 6 — Блокировка секций дашборда** (`resources/js/Pages/TourCabinet/Dashboard.vue`):
  - Введён computed `gatedSectionClass = profileGateActive ? 'pointer-events-none select-none opacity-50 grayscale' : ''`.
  - Применён к секциям `#tour-cabinet-favorites`, `#tour-cabinet-standard-form`, `#tour-cabinet-atomic-ticket`, `#tour-cabinet-contest`, `#tour-cabinet-commerce-tours` (для последней — через массив `:class`, чтобы не перебить существующий highlight-class). На каждой секции добавлен `:aria-hidden="profileGateActive"`. Секции `#tour-cabinet-profile`, `#tour-cabinet-documents` и заголовочный блок (карточка участника / статус заявок) остались доступны.
  - Verify: `npm run build` (Docker) — `built in 8.35s`, без ошибок. `ReadLints` — чисто.
  - Files: `resources/js/Pages/TourCabinet/Dashboard.vue`.
- **Задача 5 — Плашка-уведомление на дашборде** (`resources/js/Pages/TourCabinet/Dashboard.vue`):
  - Добавлен проп `profileGate` (с безопасным дефолтом `{ complete: true, missing: [], message: '' }`); computed `profileGateActive`, `profileGateMissingLabels` (через локальную карту `PROFILE_GATE_FIELD_LABELS`).
  - Над секцией `#tour-cabinet-profile` отрисована контрастная плашка `bg-amber-100/80 border-2 border-amber-300 text-amber-900` с `ExclamationTriangleIcon`, заголовком «Сначала заполните профиль», сообщением из пропа, списком пустых полей (badge-pills) и двумя кнопками-якорями: «Перейти к профилю» (`#tour-cabinet-profile`) и «Загрузить согласие на ОПД» (`#tour-cabinet-documents`).
  - Verify: `npm run build` (Docker) — `built in 8.66s`, без ошибок. `ReadLints` по `Dashboard.vue` — чисто.
  - Files: `resources/js/Pages/TourCabinet/Dashboard.vue`.
- **Задача 4 — Middleware `tour-cabinet.profile-complete`** (`app/Http/Middleware/EnsureTourCabinetProfileComplete.php`, `bootstrap/app.php`, `routes/web.php`):
  - Создан middleware: для авторизованного пользователя при незаполненном профиле GET/HEAD → `redirect()->route('tour-cabinet.dashboard')->withErrors(['profile' => ...])->withFragment('tour-cabinet-profile')`, иначе `back()->withInput()->withErrors(...)`.
  - Зарегистрирован alias `tour-cabinet.profile-complete` в `bootstrap/app.php` (использует DI-контейнер для `TourCabinetProfileCompleteness`).
  - В `routes/web.php` маршруты конкурса (включая `tour-cabinet.contest`), коммерческих туров обёрнуты в подгруппу `Route::middleware('tour-cabinet.profile-complete')`. Открытыми остались `dashboard`, `profile.update`, `profile.documents.{upload,delete}`, `support.*`, `upload.{presigned-url,confirm}`, `login*`, `register*`, `logout`.
  - Verify: `php artisan tinker` (route enumeration через `Route::getRoutes()`) — все 17 маршрутов конкурса/коммерческих туров помечены `GATED`, остальные `open`. `ReadLints` — чисто.
  - Files: `app/Http/Middleware/EnsureTourCabinetProfileComplete.php`, `bootstrap/app.php`, `routes/web.php`.
- **Задача 3 — Inertia-проп `profileGate`** (`app/Http/Controllers/TourCabinetController.php`):
  - В `dashboard()` инжектится `TourCabinetProfileCompleteness`; в Inertia-render добавлен ключ `profileGate = { complete: bool, missing: string[], message: string }`. Сообщение — «Сначала заполните профиль и загрузите согласие на обработку персональных данных, чтобы получить доступ к остальным разделам.»
  - Verify: `php artisan tinker` (transaction-rollback) — `empty: profileGate.complete=0 missing=[last_name,first_name,gender,birth_date,phone,personal_data_consent]`; `full: profileGate.complete=1 missing=[]`. `ReadLints` — чисто.
  - Files: `app/Http/Controllers/TourCabinetController.php`.
- **Задача 2 — Тип документа `personal_data_consent`** (`app/Models/TourCabinetDocument.php`):
  - В `allowedTypes()` новый тип добавлен первым в списке; метка `typeLabel` — «Согласие на обработку персональных данных». Контроллер `TourCabinetController::uploadProfileDocument` уже использует `Rule::in(TourCabinetDocument::allowedTypes())`, поэтому отдельной правки не потребовалось.
  - Verify: `php artisan tinker` — `allowedTypes() = ["personal_data_consent","passport_spread","passport_registration","snils"]`, `typeLabel('personal_data_consent') = "Согласие на обработку персональных данных"`. `ReadLints` — чисто.
  - Files: `app/Models/TourCabinetDocument.php`.
- **Задача 1 — Сервис `TourCabinetProfileCompleteness`** (`app/Services/TourCabinetProfileCompleteness.php`, `app/Models/TourCabinetDocument.php`):
  - Создан класс с константами обязательных полей и методами `isComplete(User)` / `missingFields(User)`. Согласие на ОПД проверяется через `tour_cabinet_documents` (тип `personal_data_consent`, статус ∈ `{pending_review, approved}`, непустой `file_path`).
  - В `TourCabinetDocument` добавлена константа `TYPE_PERSONAL_DATA_CONSENT = 'personal_data_consent'` (минимальная зависимость для сервиса; полное расширение `allowedTypes()` / `typeLabel()` — в задаче 2).
  - Verify: `php artisan tinker` — таблица из 5 кейсов: `empty → complete=0 missing=[last_name,first_name,gender,birth_date,phone,personal_data_consent]`; `full_no_consent → complete=0 missing=[personal_data_consent]`; `full_pending → complete=1`; `full_approved → complete=1`; `full_annulled → complete=0 missing=[personal_data_consent]`. `ReadLints` по сервису и модели — чисто.
  - Files: `app/Services/TourCabinetProfileCompleteness.php`, `app/Models/TourCabinetDocument.php`.

## Открытые вопросы

1. Состав обязательных полей профиля — `patronymic` и `avatar_path` оставляем опциональными до уточнения от заказчика (см. `spec.md` Open question 1).
2. Зачёт согласия на ОПД при статусе `annulled` — на MVP теряем доступ к этапам и просим перезагрузить файл (см. `spec.md` Open question 2).
3. Зачёт документов паспорта/СНИЛС в гейте — на MVP не учитываем (см. `spec.md` Open question 3).
