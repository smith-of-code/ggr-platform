# Коммерческие туры (commerce-tours)

## Goal

Добавить в ЛК Туры (`/tour-cabinet`) под блоком «Конкурс» новый блок «Коммерческие туры» с тремя редактируемыми из админки этапами: выбор города → выбор тура (Этап 1), анкета доп. данных по городу через `LmsForm` (Этап 2), статичное уведомление об ожидании обратной связи (Этап 3).

## In-scope

- Якорь блока на дашборде: `#tour-cabinet-commerce-tours`, расположен сразу под `#tour-cabinet-contest-detail` в `resources/js/Pages/TourCabinet/Dashboard.vue`. Структурно повторяет блок «Конкурс» (заголовок + строка прогресса + три секции «Этап I–III» с панелями `CommerceToursStage*Panel.vue`).
- Этап 1: пользователь выбирает **ровно 1** город и **ровно 1** тур; список городов — все `City` (`is_active = true`), у которых есть хотя бы один активный `Tour` через связь `city_tour`; список туров — `Tour::where('is_active', true)` с тем же `city_tour` для выбранного города. После выбора кнопка «К этапу 2» сохраняет `city_id`/`tour_id` и переводит `current_stage = 2`.
- Этап 2: при наличии маппинга для выбранного города (см. ниже) открывается `LmsForm` через редирект на `forms.public.show` (по аналогии с этапом 1 конкурса). После успешного сабмита формы `current_stage = 3` (через сервис, аналогичный `TourCabinetContestFormLinker`). Если маппинга для города нет — UI показывает заглушку «Анкета пока недоступна, обратитесь в поддержку».
- Этап 3: статичный экран с темой/телом, редактируемыми в админке (см. settings ниже). По умолчанию текст: «Спасибо за проявленный интерес! Мы с вами свяжемся — ожидайте обратной связи». Никаких полей ввода для участника.
- Прогресс хранится в новой таблице `tour_cabinet_commerce_progress` (1 запись на пользователя): `user_id` (unique FK), `current_stage` (1..3), `city_id` (nullable FK), `tour_id` (nullable FK), `lms_form_submission_id` (nullable FK на `lms_form_submissions`), `completed_at` (nullable timestamp). Прохождение однократное — после `current_stage = 3` блок переходит в режим только-просмотр.
- Маппинг «город → LmsForm для этапа 2» хранится в новой таблице `tour_cabinet_commerce_city_forms`: `city_id` (unique FK), `lms_form_slug` (string), CRUD только в админке.
- Админка `/admin/tour-cabinet/commerce-tours` (одна страница + панель `TourCabinetAdminCommerceToursPanel.vue` на хабе `Hub.vue`): таблица маппингов `city → форма` (CRUD: `POST/PATCH/DELETE` через `Admin\TourCabinetCommerceToursController`) + форма редактирования текста этапа 3 (subject + body + флаг активности блока, аналогично `contest-completion-notification`).
- Настройки в таблице `settings`, группа `tour_cabinet`:
  - `commerce_tours_enabled` — boolean (по умолчанию `false`, чтобы блок не появился внезапно у пользователей до первичной настройки админом).
  - `commerce_tours_stage3_subject` — строка (заголовок панели этапа 3).
  - `commerce_tours_stage3_body` — текст уведомления (поддержка переносов строк, без HTML).
- Дефолты в `config/tour_cabinet.php`, ключ `commerce_tours.*` (`enabled`, `stage3_subject`, `stage3_body`) с env-переопределением `TOUR_CABINET_COMMERCE_TOURS_ENABLED|STAGE3_SUBJECT|STAGE3_BODY`.
- Новый раздел в `Admin\TourCabinetHubPageData::commerceToursPayload()` с массивом маппингов, списком доступных городов/форм, текстом этапа 3 и флагом `enabled`.

## Out-of-scope

- Бэкенд бронирования / интеграция с CRM / отправка уведомлений менеджерам — текущий блок только фиксирует факт интереса и показывает экран ожидания. Письма участнику/менеджеру отправляются отдельной фичей при необходимости.
- Многократные заявки от одного пользователя (несколько городов/туров параллельно) — однократное прохождение, сброс возможен только вручную через БД (отдельная админ-кнопка «Сбросить» — будущая итерация).
- Платёжный поток / промокоды / интеграция с `aportinity-tours`-фичей — не в этой итерации.
- Кастомизация текста этапа 3 по городу или направлению — в текущей итерации единый текст для всех.
- Изменение блока «Конкурс» — фича не трогает существующие маршруты `/tour-cabinet/contest/*` и таблицы `tour_cabinet_contest_*`.
- Отдельная админ-страница «Список ответов этапа 2 коммерческих туров» (для просмотра LmsFormSubmission'ов) — пока используется существующая страница ответов LMS-форм.

## Constraints

- Все команды (миграции, route:list, npm build, pest) выполняются через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`) согласно `spec-continuation`.
- UI Kit reuse: `RCard`, `RButton`, `RInput`, `RSelect`, `RCheckbox` (глобально зарегистрированы — см. `resources/js/app.js`); композаблы — `useToast`, `useConfirm`; нативный `<textarea>` для тела этапа 3 (как в `TourCabinetAdminFormsPanel.vue` для уведомления конкурса). Не плодить новые компоненты под уже существующие задачи.
- Хранение настроек — только через `SettingsService::setGroup('tour_cabinet', …)`; отдельный геттер `getTourCabinetCommerceToursStage3Notification(): array{enabled,subject,body}` по аналогии с `getTourCabinetContestCompletionNotification`.
- Миграции sqlite-совместимы (без `DROP COLUMN` / без переименований существующих колонок); новые таблицы создаются через `Schema::create`.
- Проброс пропов в Inertia: страницы `Hub.vue` и `CommerceTours/Index.vue` используют общий payload из `TourCabinetHubPageData::commerceToursPayload()`; `Dashboard.vue` получает данные блока из существующего `TourCabinetController` (расширить payload `commerceTours`).
- Связи моделей в `TourCabinetCommerceProgress`: `user`, `city`, `tour`, `lmsFormSubmission`. Без полиморфных связей.
- Не блокировать сохранение этапа 3 ошибками вспомогательных интеграций — в этой итерации этап 3 это просто отображение настройки, серверного сохранения от пользователя нет (только переход `current_stage = 2 → 3` через сабмит формы этапа 2).

## Реализация (как сделано)

- БД: миграции `2026_04_29_140000_create_tour_cabinet_commerce_progress_table.php` (таблица `tour_cabinet_commerce_progress`: `user_id` unique FK, `current_stage` tinyint, `city_id`/`tour_id`/`lms_form_submission_id` nullable FK на `cities`/`tours`/`lms_form_submissions`, `completed_at` timestamp nullable, `timestamps`) и `2026_04_29_140100_create_tour_cabinet_commerce_city_forms_table.php` (`city_id` unique FK, `lms_form_slug` varchar(191) + index, `timestamps`).
- Модели: `App\Models\TourCabinetCommerceProgress` (`fillable`, cast `completed_at => datetime`, связи `user/city/tour/lmsFormSubmission` BelongsTo), `App\Models\TourCabinetCommerceCityForm` (`fillable`, связь `city`). `User::tourCabinetCommerceProgress(): HasOne`.
- Конфиг: `config/tour_cabinet.php`, ключ `commerce_tours` (`enabled` через `FILTER_VALIDATE_BOOLEAN`, `stage3_subject`, `stage3_body`); env: `TOUR_CABINET_COMMERCE_TOURS_ENABLED|STAGE3_SUBJECT|STAGE3_BODY`. Дефолтные значения: subject «Заявка принята», body «Спасибо за проявленный интерес! Мы с вами свяжемся — ожидайте обратной связи.»
- Settings: `SettingsService::getTourCabinetCommerceToursStage3Notification(): array{enabled,subject,body}` — приоритет БД (группа `tour_cabinet`, ключи `commerce_tours_enabled|stage3_subject|stage3_body`) → config → дефолты; пустые строки в БД → fallback на config; `enabled` парсится через `FILTER_VALIDATE_BOOLEAN`.
- Сервис данных дашборда: `App\Services\TourCabinetCommerceToursDashboardData::buildPayload(User): array` отдаёт `enabled, currentStage, cityId, tourId, completedAt, availableCities[], availableTours[], hasCityForm, stage3{subject,body}`. Список городов — `City::active().whereHas('tours', fn ($q) => $q->where('is_active', true))`; список туров — `Tour::isActive.whereHas('cities', cityId)` (только при выбранном городе). Подключение в `TourCabinetController::dashboard` — ключ `commerceTours` в Inertia payload.
- Пользовательский контроллер: `App\Http\Controllers\TourCabinetCommerceToursController` (`storeCity`/`storeTour`/`completeStage1`/`startCityForm`/`reopenSelection`); все redirect возвращают на дашборд с фрагментом `tour-cabinet-commerce-tours`. Маршруты под middleware `tour-cabinet`:
  - `POST /tour-cabinet/commerce-tours/city` (`tour-cabinet.commerce-tours.city.store`)
  - `POST /tour-cabinet/commerce-tours/tour` (`tour-cabinet.commerce-tours.tour.store`)
  - `POST /tour-cabinet/commerce-tours/complete-stage-1` (`tour-cabinet.commerce-tours.complete-stage-1`)
  - `GET /tour-cabinet/commerce-tours/stage-2/form` (`tour-cabinet.commerce-tours.stage-2.form`)
  - `POST /tour-cabinet/commerce-tours/reopen-selection` (`tour-cabinet.commerce-tours.reopen-selection`)
- Linker: `App\Services\TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission(LmsForm, LmsFormSubmission)` — гарды `Auth + canAccessTourCabinet`, ключ сессии `tour_cabinet_commerce_form_city_id`, маппинг `tour_cabinet_commerce_city_forms`, совпадение `form->slug`, прогресс пользователя; идемпотентность через `current_stage >= 3`. Вызов вставлен в обоих сабмит-методах `Lms\FormPublicController` рядом с `TourCabinetContestFormLinker`.
- Админ-контроллер: `App\Http\Controllers\Admin\TourCabinetCommerceToursController` (методы `index`, `storeCityForm`, `updateCityForm`, `destroyCityForm`, `updateStage3Notification`); валидация `lms_form_slug` — активная LmsForm; redirect-fragment `tour-cabinet-admin-commerce-tours`. Маршруты в группе `admin.tour-cabinet`:
  - `GET /admin/tour-cabinet/commerce-tours` (`admin.tour-cabinet.commerce-tours.index`)
  - `POST/PATCH/DELETE /admin/tour-cabinet/commerce-tours/city-forms[/{cityForm}]` (`admin.tour-cabinet.commerce-tours.city-forms.{store,update,destroy}`)
  - `PUT /admin/tour-cabinet/commerce-tours/stage3-notification` (`admin.tour-cabinet.commerce-tours.stage3-notification.update`)
- Hub payload: `TourCabinetHubPageData::commerceToursPayload()` — `enabled`, `stage3{subject,body}`, `cityForms[]` (с `city_name`), `availableCities[]` (только города с активными турами), `availableForms[]` (активные `LmsForm`). `TourCabinetHubController::index` пробрасывает `commerceToursSection`.
- Админ-фронт: `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminCommerceToursPanel.vue` — `RCard` с настройками этапа 3 (нативный чекбокс «Блок активен», `<input type="text">` темы, `<textarea rows="6">` тела) + `RCard` с таблицей маппингов «город → форма» (inline-редактирование через `<select>`, удаление через `window.confirm`, форма добавления). Подключена в `Hub.vue` (новая `<section>` после блока этапа 3) и в отдельной странице `CommerceTours/Index.vue` (по аналогии с `Forms/Index.vue`).
- Пользовательский фронт: новый `<section id="tour-cabinet-commerce-tours">` в `Dashboard.vue` сразу после блока конкурса; три панели в `resources/js/Pages/TourCabinet/CommerceTours/`:
  - `CommerceToursStage1Panel.vue` — двухшаговый выбор города и тура с тремя `useForm` (city.store, tour.store, complete-stage-1).
  - `CommerceToursStage2Panel.vue` — резюме выбора + ссылка «Открыть анкету» (`tour-cabinet.commerce-tours.stage-2.form`) или плашка о ненастроенной форме + кнопка «Изменить выбор».
  - `CommerceToursStage3Panel.vue` — статичный экран subject + body с `whitespace-pre-line`.
  Иконки `IdentificationIcon`/`ChatBubbleLeftRightIcon`/`CheckCircleIcon` повторяют шаблон конкурса. Блок скрыт при `commerceTours.enabled === false`.

## Open questions

(пусто — критические вопросы по источнику туров, источнику городов, форме этапа 2 и однократности прохождения подтверждены пользователем; см. историю фичи)
