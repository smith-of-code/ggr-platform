# Tasks: Коммерческие туры

> Каждая задача — независимо верифицируемая. Verify-команды — по паттерну `spec-continuation` (раздел «Command patterns»). Если объём задачи превышает 5 файлов / 150 строк — разбить.

## Task 1. Миграции: `tour_cabinet_commerce_progress` + `tour_cabinet_commerce_city_forms`

- Goal: создать новые таблицы для прогресса участника и маппинга «город → LmsForm slug».
- Scope: `database/migrations/<дата>_create_tour_cabinet_commerce_progress_table.php`, `database/migrations/<дата>_create_tour_cabinet_commerce_city_forms_table.php`.
- DoD: первая таблица — `id`, `user_id` (unique FK на `users`), `current_stage` (unsignedTinyInteger 1..3), `city_id` (nullable FK на `cities`), `tour_id` (nullable FK на `tours`), `lms_form_submission_id` (nullable FK на `lms_form_submissions`), `completed_at` (timestamp nullable), `timestamps`. Вторая — `id`, `city_id` (unique FK на `cities`), `lms_form_slug` (string), `timestamps`. Sqlite-совместимы (без drop/rename), есть `up`/`down`.
- Verify: `php artisan migrate --pretend` (Docker pattern) показывает корректный SQL `create table`; `php artisan migrate` — DONE без ошибок.

## Task 2. Модели + связи

- Goal: открыть БД-операции для прогресса и маппинга.
- Scope: `app/Models/TourCabinetCommerceProgress.php`, `app/Models/TourCabinetCommerceCityForm.php`, `app/Models/User.php`.
- DoD: обе модели имеют `$fillable`, `$casts` (`completed_at => datetime`); связи `user()`, `city()`, `tour()`, `lmsFormSubmission()` для `TourCabinetCommerceProgress`; связь `city()` для `TourCabinetCommerceCityForm`. В `User.php` добавлен hasOne `tourCabinetCommerceProgress()`.
- Verify: php-однострочник через Docker — `User::factory()->create()->tourCabinetCommerceProgress()` возвращает Relation; `TourCabinetCommerceCityForm::factory(...)` (или прямой save) принимает поля.

## Task 3. Конфиг: дефолты `commerce_tours.*` в `config/tour_cabinet.php`

- Goal: добавить дефолты `enabled`/`stage3_subject`/`stage3_body` с env-перекрытием.
- Scope: `config/tour_cabinet.php`.
- DoD: ключ `commerce_tours` (`enabled` через `FILTER_VALIDATE_BOOLEAN`, `stage3_subject` строка, `stage3_body` строка); env: `TOUR_CABINET_COMMERCE_TOURS_ENABLED|STAGE3_SUBJECT|STAGE3_BODY`; дефолтное тело — «Спасибо за проявленный интерес! Мы с вами свяжемся — ожидайте обратной связи.»
- Verify: `php artisan config:show tour_cabinet.commerce_tours` (Docker pattern) выводит три ключа с ожидаемыми дефолтами.

## Task 4. SettingsService: чтение настроек коммерческих туров

- Goal: единый метод чтения с приоритетом БД → config.
- Scope: `app/Services/SettingsService.php`.
- DoD: метод `getTourCabinetCommerceToursStage3Notification(): array{enabled,subject,body}`; пустые значения в БД → fallback на config; `enabled` парсится через `FILTER_VALIDATE_BOOLEAN`; кэширование через существующий `getGroup`.
- Verify: php-однострочник через Docker — без записей вернутся config-дефолты; после `Setting::setGroup('tour_cabinet', […])` значения подменяются (и cache forget работает через `setGroup`).

## Task 5. Сервис данных дашборда: `TourCabinetCommerceToursDashboardData`

- Goal: payload блока коммерческих туров на дашборде.
- Scope: `app/Services/TourCabinetCommerceToursDashboardData.php`, `app/Http/Controllers/TourCabinetController.php`.
- DoD: метод `buildPayload(User): array{enabled, currentStage, cityId, tourId, completedAt, availableCities[], availableTours[], stage3{subject,body}}`. Список городов — `City::active()->whereHas('tours', fn ($q) => $q->where('is_active', true))->get(['id','name'])`; список туров строится по выбранному `cityId` (если есть). `enabled` — из настроек. Сервис подключается к payload `Dashboard.vue` под ключ `commerceTours`.
- Verify: php-однострочник через Docker — для тестового пользователя возвращается массив с ключами `enabled`, `availableCities`; для пользователя с прогрессом — текущий `cityId`/`tourId`.

## Task 6. Пользовательские маршруты + контроллер `TourCabinetCommerceToursController`

- Goal: маршруты выбора города/тура и редиректа на форму этапа 2.
- Scope: `routes/web.php`, `app/Http/Controllers/TourCabinetCommerceToursController.php`.
- DoD: маршруты под middleware `tour-cabinet`:
  - `POST /tour-cabinet/commerce-tours/city` (`tour-cabinet.commerce-tours.city.store`) — сохранить `city_id`, обнулить `tour_id`, `current_stage = 1`.
  - `POST /tour-cabinet/commerce-tours/tour` (`tour-cabinet.commerce-tours.tour.store`) — сохранить `tour_id` для уже выбранного города (валидация: тур доступен в выбранном городе).
  - `POST /tour-cabinet/commerce-tours/complete-stage-1` — переход `current_stage = 2` при наличии `city_id` + `tour_id`.
  - `GET /tour-cabinet/commerce-tours/stage-2/form` — редирект на `forms.public.show` по `lms_form_slug` из `tour_cabinet_commerce_city_forms` для текущего `city_id`; в сессии — `tour_cabinet_commerce_form_city_id`.
  - `POST /tour-cabinet/commerce-tours/reopen-selection` — вернуться к этапу 1 (только если `current_stage <= 2`).
- Verify: `php artisan route:list --path=tour-cabinet/commerce-tours` (Docker) — все 5 маршрутов видны.

## Task 7. Связка сабмита формы этапа 2 → переход на этап 3

- Goal: после успешного сабмита `LmsForm` этапа 2 поднять `current_stage` до 3.
- Scope: `app/Services/TourCabinetCommerceToursFormLinker.php`, `app/Http/Controllers/Lms/FormPublicController.php`.
- DoD: новый сервис `tryLinkAfterSubmission(LmsFormSubmission)` — если в сессии `tour_cabinet_commerce_form_city_id` совпадает с маппингом для slug, обновляет `TourCabinetCommerceProgress` (`lms_form_submission_id`, `current_stage = 3`, `completed_at = now()` если ещё не установлен) и забывает ключ сессии. Вызов вставляется в `FormPublicController::submit` рядом с существующим `TourCabinetContestFormLinker`.
- Verify: pest-сценарий — авторизуем пользователя, выставляем `commerce-tours/stage-2/form` (сессия), сабмитим LmsForm соответствующего slug, проверяем `current_stage = 3` и `lms_form_submission_id` в `tour_cabinet_commerce_progress`.

## Task 8. Админ-маршруты + контроллер `Admin\TourCabinetCommerceToursController`

- Goal: CRUD маппинга «город → LmsForm slug» + обновление настроек этапа 3.
- Scope: `routes/web.php`, `app/Http/Controllers/Admin/TourCabinetCommerceToursController.php`.
- DoD: маршруты в группе `admin.tour-cabinet`:
  - `GET /admin/tour-cabinet/commerce-tours` (`admin.tour-cabinet.commerce-tours.index`) — отдельная страница с тем же UI, что в Hub.
  - `POST /admin/tour-cabinet/commerce-tours/city-forms` / `PATCH .../{cityForm}` / `DELETE .../{cityForm}` — CRUD маппинга (валидация: `city_id` exists+unique, `lms_form_slug` exists в `LmsForm`).
  - `PUT /admin/tour-cabinet/commerce-tours/stage3-notification` (`admin.tour-cabinet.commerce-tours.stage3-notification.update`) — валидация `enabled:nullable|boolean`, `subject:nullable|string|max:255`, `body:nullable|string|max:20000`; запись через `Setting::setGroup('tour_cabinet', […])`.
  - Все redirect-ы на `admin.tour-cabinet.index` с `withFragment('tour-cabinet-admin-commerce-tours')` + flash success.
- Verify: `php artisan route:list --path=admin/tour-cabinet/commerce-tours` (Docker) — видны 5 маршрутов.

## Task 9. Hub payload + админ-фронт

- Goal: панель «Коммерческие туры» на хабе и отдельная страница, проброс пропов.
- Scope: `app/Services/Admin/TourCabinetHubPageData.php`, `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminCommerceToursPanel.vue`, `resources/js/Pages/Admin/TourCabinet/Hub.vue`, `resources/js/Pages/Admin/TourCabinet/CommerceTours/Index.vue`.
- DoD: `commerceToursPayload()` отдаёт `{ enabled, stage3:{subject,body}, cityForms:[{id,city_id,city_name,lms_form_slug}], availableCities:[{id,name}], availableForms:[{slug,title}] }`. Панель содержит: `RCheckbox` «Блок активен», `RInput` темы этапа 3, `<textarea>` тела этапа 3, `RButton` «Сохранить»; таблицу маппингов с inline-редактированием + кнопкой «Добавить» (`RSelect` город + `RSelect` форма) + действием «Удалить» через `useConfirm`. `Hub.vue` пробрасывает через `v-bind="commerceToursSection"` (id-якорь `tour-cabinet-admin-commerce-tours`); `CommerceTours/Index.vue` — отдельная страница с тем же компонентом.
- Verify: `npm run build` (Docker pattern) — успех; ручная smoke-проверка `/admin/tour-cabinet#tour-cabinet-admin-commerce-tours` после деплоя.

## Task 10. Пользовательский фронт: блок «Коммерческие туры» в `Dashboard.vue`

- Goal: добавить новый блок под блоком конкурса с тремя этапами.
- Scope: `resources/js/Pages/TourCabinet/Dashboard.vue`, `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage1Panel.vue`, `.../CommerceToursStage2Panel.vue`, `.../CommerceToursStage3Panel.vue`.
- DoD: новый `<section id="tour-cabinet-commerce-tours">` сразу после блока конкурса; `RCard` с заголовком «Коммерческие туры», полоса прогресса (как в конкурсе), три панели этапов; стейт текущего этапа управляется через `commerceTours.currentStage` из props; этап 1 — два `RSelect` (город, тур) + кнопка «К этапу 2»; этап 2 — кнопка «Открыть анкету» (редирект на `tour-cabinet.commerce-tours.stage-2.form`) или заглушка при отсутствии маппинга; этап 3 — заголовок subject + текст body (с `nl2br` через `v-html` или `<pre>`). Блок скрыт при `commerceTours.enabled === false`.
- Verify: pest happy-path сценарий: создаём пользователя, маппинг `city → LmsForm`, активный тур, делаем `POST /tour-cabinet/commerce-tours/city`, `POST .../tour`, `POST .../complete-stage-1`, открываем `GET .../stage-2/form`, сабмитим форму через `forms.public.submit`, проверяем `current_stage = 3` и отображение блока (assertSee subject/body); `npm run build` (Docker) — успех.

## Task 11. Финализация: spec/progress + полная верификация

- Goal: зафиксировать итоговое состояние фичи.
- Scope: `spec/features/commerce-tours/spec.md`, `spec/features/commerce-tours/progress.md`, опционально — кросс-ссылка в `spec/features/tour-cabinet/spec.md`.
- DoD: `spec.md` дополнен разделом «Реализация (как сделано)» с финальными именами роутов/полей; `progress.md` помечает все задачи `Completed`; миграции применены; `php artisan route:list`, `npm run build` отработали без ошибок; pest happy-path (Task 10) и stage-2 linker (Task 7) зелёные.
- Verify: финальный прогон `pest --filter=CommerceTours` (или эквивалент) и `npm run build` (Docker pattern); `git diff --stat` — отчёт о затронутых файлах.
