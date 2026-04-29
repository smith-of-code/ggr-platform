# Привязка форм Этапа 1 конкурса к городу (contest-city-forms)

## Goal

Перенести привязку формы **Этапа 1** конкурса с пары «направление → флаг `needs_more_data`» на **конкретный город направления**: для каждой строки `tour_cabinet_direction_cities` админ может выбрать отдельную `LmsForm` (или не выбирать ничего). Если форма у города не задана — на ЛК участника город получает автоматический статус «Заполнено», без открытия анкеты. Поведение Этапа 2 / Этапа 3 не меняется.

> **Update 2026-04-29 (drop-fallback):** глобальный fallback (`contest_stage1_form_slug_standard` / `more_data`) и админ-блок «Конкурс, этап 1 — какие формы открывать» полностью удалены. Источник формы — только колонка `lms_form_slug` строки `tour_cabinet_direction_cities`. Пустой `lms_form_slug` всегда означает «без формы → автозавершение в ЛК», вне зависимости от значений в `settings`/`config`.

Связанные фичи: `tour-cabinet`, `lk-participant-contests`.

## In-scope

### БД

- В таблицу `tour_cabinet_direction_cities` добавляется колонка `lms_form_slug` (`string(191)`, nullable). Семантика «не задано» — `NULL` или пустая строка → форма к городу не привязана.
- Миграция sqlite-совместимая (`Schema::table(...)->addColumn(...)`), без `dropColumn` старых полей. Существующие колонки (`needs_more_data`, `position`, FK `direction_id`/`city_id`) **не меняются**.
- Backwards-compatibility данных: миграция оставляет `lms_form_slug = NULL` у всех существующих строк → продолжают работать через текущий глобальный fallback (см. ниже).

### Источник формы для города

- Единственный источник: `tour_cabinet_direction_cities.lms_form_slug` для пары `(direction_id, city_id)`.
- Если строка непустая → используется эта `LmsForm`.
- Если пусто (`null` / пустая строка) → формы у города нет → автостатус «Заполнено» в ЛК.
- Глобальный fallback из `settings` / `config` **не используется** (удалён 2026-04-29).

### Админка `/admin/tour-cabinet`

- Блок «Города направлений» (`TourCabinetAdminDirectionCitiesPanel.vue` + `Admin\TourCabinetDirectionCitiesController`):
  - В форме добавления города — селект «Форма этапа 1 (опционально)» из любых активных `LmsForm` (поле `allFormsOptions`, как у блока «Стандартная анкета»). Пустое значение = не задано.
  - В строке существующего города — inline-селект формы рядом с текущим чекбоксом `needs_more_data` и полем `position`. Сохранение через существующий `PATCH admin.tour-cabinet.direction-cities.update`.
  - При смене / снятии формы у города, **на котором уже есть отправленные сабмиты участников** (`tour_cabinet_contest_city_submissions`), показываем флаш-сообщение / `window.confirm`: «У города N сабмитов от участников. Старые ответы останутся, новые участники получат новую форму». Никаких автоматических действий с существующими `tour_cabinet_contest_progress` или `tour_cabinet_contest_city_submissions`.
  - Чекбокс «Нужно больше данных» (`needs_more_data`) **остаётся отдельной независимой настройкой** — управляет только бейджем «Необходимо заполнение дополнительных персональных данных» в ЛК; на выбор формы больше не влияет.
- Блок «Конкурс, этап 1 — какие формы открывать» (`TourCabinetAdminFormsPanel.vue` + `Admin\TourCabinetFormsController::updateContestFormSlugs`) **полностью удалён** из админки (UI, контроллер-метод, роут `admin.tour-cabinet.forms.contest-form-slugs.update`). Сами getter-ы `getTourCabinetContestStage1FormSlug*` в `SettingsService` остаются как dead-friendly код, но не вызываются.

### Бэкенд

- Модель `TourCabinetDirectionCity` — `$fillable` пополняется `lms_form_slug`. Cast не требуется (string|null).
- `Admin\TourCabinetDirectionCitiesController::store|update`:
  - Правило для `lms_form_slug`: `nullable|string|max:191` + проверка существования среди активных `LmsForm` (`exists:lms_forms,slug` + `where('is_active', true)`); пустая строка нормализуется в `null`.
  - При `update` — если новое значение отличается от старого и для города существуют сабмиты в `tour_cabinet_contest_city_submissions`, redirect возвращает в hub дополнительный `with('warning', '…')` вместо `success`.
  - При `destroy` строки direction-city (полное удаление города из направления) — поведение не меняется (старые сабмиты остаются, ссылка на удалённую форму больше не используется).
- `Services\TourCabinetContestFormLinker::tryLinkAfterSubmission` — резолв ожидаемого slug:
  - Только `row->lms_form_slug` (через резолвер `resolveForRow`).
  - Если резолвер вернул `null` → линкер не создаёт `TourCabinetContestCitySubmission` (для города без формы submissions не нужны: статус «Заполнено» вычисляется по `auto_completed`).
  - Гард `expectedSlug === $form->slug` остаётся.
- `App\Http\Controllers\TourCabinetContestController::startCityForm` — резолв slug по той же иерархии (per-row → global fallback).
- `App\Http\Controllers\TourCabinetContestController::completeStage1` (метод `stage1Complete` в контроллере и `Services\TourCabinetContestDashboardData::stage1Complete`):
  - Город считается «выполненным», если **либо** его `resolvedFormSlug === null` (нет формы) — автозавершение, **либо** существует `tour_cabinet_contest_city_submissions` для пары `(user_id, city_id)`.
  - Хелпер вычисления `resolvedFormSlug` для `(direction_id, city_id)` выносится в `Services\TourCabinetContestStage1FormResolver` (новый сервис) и используется тремя клиентами: `TourCabinetContestController`, `TourCabinetContestDashboardData`, `TourCabinetContestFormLinker`.
- `App\Services\TourCabinetContestDashboardData::forUser`:
  - В `selectedCitiesForForms[*]` поле `form_slug` берём через резолвер.
  - Поле `submitted` остаётся прежним (`exists` в `tour_cabinet_contest_city_submissions`).
  - Поле `auto_completed` (новое, bool) = `true`, если `form_slug === null` (формы нет → автостатус). Не путать с `submitted`.
  - В `cities[*]` (списке для шага «cities») добавляется `has_form` (bool), чтобы UI мог сразу подсветить городам без формы метку «Стандартная анкета (форма не требуется)».

### Пользовательский фронт `Contest/ContestStage1Panel.vue`

- Шаг `forms`: по каждому выбранному городу:
  - Если `auto_completed === true` (форма не требуется) → бейдж «Заполнено» (зелёный, `bg-emerald-50/text-emerald-800`); под названием города остаётся подпись «Стандартная анкета»; кнопка «Заполнить анкету» **не показывается**; кнопка «Убрать город» доступна как и раньше (так как сабмита нет).
  - Если `submitted === true` → бейдж «Отправлено» (как сейчас).
  - Иначе → кнопка «Заполнить анкету» + ссылка на `tour-cabinet.contest.city-form` (как сейчас).
- Под названием города:
  - Если `needs_more_data === true` → плашка «Необходимо заполнение дополнительных персональных данных» (как сейчас).
  - Иначе → «Стандартная анкета».
- Условие появления кнопки «Перейти к этапу 2» расширяется: `stage1Complete && maxContestStages >= 2` (предыдущая жёсткая проверка `formSlugsConfigured.standard && formSlugsConfigured.more_data` снимается, так как глобалы теперь не обязательны: достаточно того, что у каждого выбранного города либо привязана своя форма + есть сабмит, либо формы нет).
- Заглушка «задайте оба slug в админке» снимается / преобразуется в более мягкий текст: показываем только когда у одного из выбранных городов нет ни своей формы, ни глобального fallback **и** нужно показать пользователю, что админ ещё не настроил его пару (при `step === 'forms'`).

## Out-of-scope

- Переписывание блока `contestLocationOffers` («сейчас доступно участие…», скрыт флагом `showContestLocationOffers = false`). Пометки `*проверка службы безопасности/требуется заполнение доп. анкеты` остаются завязаны на `needs_more_data`.
- Изменение Этапов 2 и 3, моделей `TourCabinetContestStage2*`, `TourCabinetContestStage3Config`, обновлений в e-mail-уведомлении о завершении конкурса.
- Множественность форм на один город (одна `lms_form_slug` на строку `tour_cabinet_direction_cities`).
- Удаление таблицы `tour_cabinet_contest_city_submissions` или миграция её на «ноль сабмитов для городов без формы» — мы просто не создаём для них записи; UI считает по `auto_completed`.
- Удаление getter-ов `getTourCabinetContestStage1FormSlug*` из `SettingsService` и ключей из `config/tour_cabinet.php` — оставляем (dead-friendly, могут пригодиться позже). UI блока в админке и роут — удалены (см. update 2026-04-29).
- Принудительный сброс выбора городов / `tour_cabinet_contest_progress` для in-flight участников при раскатке (решение пользователя `force_resubmit` интерпретируется как «без авто-сброса; админ может вручную править данные при необходимости»; реальный сброс должен делаться отдельной админ-кнопкой — отдельная фича).
- Кастомизация текста «Стандартная анкета» / «Необходимо заполнение …» из админки — формулировки зашиты в шаблон.

## Constraints

- Все команды (миграция, route:list, npm build, pest) выполняются через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`) согласно `spec-continuation`.
- Reuse: используем существующий `SearchSelect` (`resources/js/Components/SearchSelect.vue`), как в `TourCabinetAdminFormsPanel.vue` для блока «Стандартная анкета»; новые компоненты не плодим.
- Чекбокс `needs_more_data` остаётся независимым флагом, без авто-привязки к привязке формы.
- Изменения в `TourCabinetContestFormLinker` не должны ломать сценарий, когда `lms_form_slug` пустой и используется глобальный fallback (legacy цепочка должна продолжать работать).
- Сервис-резолвер `TourCabinetContestStage1FormResolver` не делает запросов к БД на каждый вызов — на запрос дашборда подгружаем `tour_cabinet_direction_cities` пачкой (`whereIn city_id`) и резолвим в памяти.
- Миграция:
  - up: `Schema::table('tour_cabinet_direction_cities', fn ($t) => $t->string('lms_form_slug', 191)->nullable()->after('needs_more_data'))`.
  - down: `Schema::table(... , fn ($t) => $t->dropColumn('lms_form_slug'))` (sqlite-совместимо: новая колонка, удаляется без проблем).
- Валидация в админке: при попытке сохранить `lms_form_slug` указывающий на неактивную форму — ошибка валидации `lms_form_slug` (по аналогии с `dashboard_standard_form_slug`).
- UI Kit: `RCard`, `RButton`, `SearchSelect` (глобально), `<select>` для inline в табличных строках (как у `needs_more_data`-чекбокса в текущем `TourCabinetAdminDirectionCitiesPanel.vue`).

## Реализация (как сделано)

- БД: миграция `2026_04_29_190000_add_lms_form_slug_to_tour_cabinet_direction_cities.php` добавляет `string('lms_form_slug', 191)->nullable()->after('needs_more_data')` в `tour_cabinet_direction_cities`. Существующие колонки (`direction_id`, `city_id`, `needs_more_data`, `position`) не тронуты.
- Модель `App\Models\TourCabinetDirectionCity` — `$fillable` пополнен `lms_form_slug`. Cast не нужен (строка / null).
- Резолвер: сервис `App\Services\TourCabinetContestStage1FormResolver`:
  - `resolveForRow(TourCabinetDirectionCity): ?string` — возвращает `trim($row->lms_form_slug)` (если непустой), иначе `null`. Глобальный fallback **не вызывается** (drop-fallback 2026-04-29).
  - `resolveBatchForDirection(int $directionId, list<int> $cityIds): array<int, ?string>` — пакетный запрос для дашборда (та же логика — только per-row).
- `TourCabinetContestFormLinker::tryLinkAfterSubmission` — ожидаемый slug берётся из `resolver->resolveForRow($row)`; гард `expectedSlug === $form->slug`.
- Админ-бэкенд:
  - `Admin\TourCabinetDirectionCitiesController::store|update` — правило `lms_form_slug: nullable|string|max:191` + хелпер `normalizeAndValidateFormSlug` (пустая строка → `null`, иначе проверка существования активной `LmsForm`). При изменении формы у города с `submissions_count > 0` — flash `success` с префиксом «Внимание:» и количеством сабмитов.
  - `Admin\TourCabinetHubPageData::directionCitiesPayload` — `rows[]` теперь массив явных ключей (`id`, `direction_id`, `city_id`, `city`, `needs_more_data`, `lms_form_slug`, `position`, `submissions_count`); добавлен ключ `allFormsOptions` (любая `LmsForm` платформы — slug, title, is_active).
  - `Admin\TourCabinetHubPageData::formsPayload` — ключ `contestFormSlugOverrides` удалён (drop-fallback). `formOptions` (формы события `tour_cabinet.lms_event_slug`) удалён, остаётся только `allFormsOptions`, который нужен «Стандартной анкете» дашборда.
  - `Admin\TourCabinetFormsController::updateContestFormSlugs` — метод удалён, роут `admin.tour-cabinet.forms.contest-form-slugs.update` снят с `routes/web.php`.
- Админ-фронт:
  - `Admin/TourCabinet/TourCabinetAdminDirectionCitiesPanel.vue` — prop `allFormsOptions`. В форме добавления города — отдельный `<select>` «Форма Этапа 1 (опционально)» с опцией «— Без формы (автозавершение) —». В таблице — отдельная колонка с inline `<select>` по `row.lms_form_slug`; при наличии `submissions_count > 0` показывается надпись «N сабмит(ов) от участников» и `window.confirm` перед сменой/снятием формы. Чекбокс `needs_more_data` остаётся отдельным управлением. Подсказка под таблицей — без упоминания глобального fallback («Если форма не задана — у города в ЛК автоматически статус "Заполнено"»).
  - `Admin/TourCabinet/DirectionCities/Index.vue` — явно пробрасывает проп `:all-forms-options="allFormsOptions"`. `Hub.vue` — без правок (`v-bind="directionCitiesSection"` подхватывает новый ключ payload автоматически).
  - `Admin/TourCabinet/TourCabinetAdminFormsPanel.vue` — RCard «Конкурс, этап 1 — какие формы открывать» удалена; пропсы `contestFormSlugOverrides`, `formOptions` сняты; computed `formSelectOptions`, `useForm slugForm`, watch и `submitSlugs` — удалены. Остальные секции («Дашборд: Стандартная анкета», «Уведомление о завершении конкурса», список форм) — без изменений.
  - `Admin/TourCabinet/Forms/Index.vue` — пропсы `contestFormSlugOverrides`, `formOptions` и их проброс в панель удалены.
- Пользовательский бэкенд:
  - `TourCabinetContestController::__construct` принимает `TourCabinetContestStage1FormResolver`. `startCityForm` резолвит slug через сервис; при `null` — flash `info` «Для этого города анкета не требуется» и редирект на `#tour-cabinet-contest`. `stage1Complete` использует `resolveBatchForDirection` (города без формы пропускаются как уже выполненные).
  - `Services\TourCabinetContestDashboardData::__construct` принимает резолвер. В `cities[*]` (для шага «cities») добавлен `has_form`. В `selectedCitiesForForms[*]`: `form_slug` через резолвер, новый ключ `auto_completed = (form_slug === null && ! submitted)`. Приватный `stage1Complete` зеркалит логику контроллера через резолвер. Ключ `formSlugsConfigured` из payload `contestStage1` удалён (drop-fallback).
- Пользовательский фронт `TourCabinet/Contest/ContestStage1Panel.vue`:
  - Бейдж «Заполнено» (зелёный) при `auto_completed`; «Отправлено» при `submitted`; кнопка «Заполнить анкету» иначе. Кнопка «Убрать город» доступна только при `!submitted && !auto_completed` (логично: автозавершённый город можно убрать через смену выбора, но не через явное удаление при `auto_completed=true` — там просто бейдж).
  - Условие «Перейти к этапу 2» — `stage1Complete && maxContestStages >= 2`.
  - Условная плашка через computed `hasUnconfiguredCity` — показывается, если у одного из выбранных городов: нет своей формы, нет сабмита и нет автозавершения. На практике после drop-fallback `auto_completed` совпадает с «нет формы», поэтому плашка фактически не показывается; оставлена защитно.
  - Prop `formSlugsConfigured` удалён (drop-fallback).

## Verify summary

### Исходный rollout (2026-04-29 первая итерация)

- `php artisan migrate` (Docker) — `2026_04_29_190000_… DONE 5.33ms`.
- php-однострочник (`Schema::hasColumn`): `lms_form_slug` присутствует.
- php-однострочники резолвера: 3 кейса (per-row / global standard / global more_data) и `resolveBatchForDirection` возвращают корректные значения.
- php-однострочники `TourCabinetContestDashboardData::forUser` (3 кейса):
  1. Без формы и без globals → `form_slug=null, auto_completed=1, submitted=0, stage1Complete=1`.
  2. Per-row slug, без сабмита → `form_slug='X', auto_completed=0, submitted=0, stage1Complete=0`.
  3. Per-row slug + сабмит → `submitted=1, stage1Complete=1`.
- `php artisan route:list --path=tour-cabinet/contest` — 14 роутов на месте; `--path=admin/tour-cabinet` — 42 роута, без регрессии.
- `npm run build` (Docker) — `built in 5.54s`, без ошибок.
- `ReadLints` по 11 затронутым файлам (PHP + Vue + миграция) — чисто.

### Drop-fallback rollout (2026-04-29 вторая итерация)

- php-однострочник `TourCabinetContestStage1FormResolver::resolveForRow` — 4 кейса:
  1. `slug=NULL, needs_more_data=false` → `NULL`.
  2. `slug="", needs_more_data=false` → `NULL`.
  3. `slug=NULL, needs_more_data=true` → `NULL` (глобал не вызывается).
  4. `slug='explicit-slug', needs_more_data=true` → `'explicit-slug'`.
- php-однострочник: даже с `settings.contest_stage1_form_slug_standard='legacy-standard'` и `..._more_data='legacy-more'` резолвер для `lms_form_slug=NULL` возвращает `NULL` (fallback не используется).
- php-однострочник `TourCabinetContestDashboardData::forUser` для tour-cabinet user — `contestStage1` payload содержит ключи `step, progress, directions, cities, selectedCitiesForForms, stage1Complete`; `formSlugsConfigured` отсутствует.
- php-однострочник `Admin\TourCabinetHubPageData::formsPayload` — payload содержит `lmsEvent, forms, configSlug, dashboardStandardFormSlug, contestCompletionNotification, allFormsOptions`; `contestFormSlugOverrides` и `formOptions` отсутствуют.
- `php artisan route:list --path=admin/tour-cabinet` — `Showing [43] routes`, роут `admin.tour-cabinet.forms.contest-form-slugs.update` отсутствует, `admin.tour-cabinet.forms.index` (страница «Стандартная анкета» / уведомление / список форм) — на месте.
- `npm run build` (Docker) — `built in 5.76s`, без ошибок.
- `ReadLints` по 12 файлам (PHP + Vue + 3 spec) — чисто.

## Open questions

(пусто — ключевые вопросы зафиксированы через `AskQuestion`:
- 2026-04-29 (исходные): `needs_more_data_semantics=independent`, `global_slugs_fate=keep_as_fallback`, `in_flight_users=force_resubmit (интерпретировано как no-op при раскатке)`, `form_change_after_submit=allow_warn`, `form_options_scope=all_forms`.
- 2026-04-29 (drop-fallback): `fallback_strategy=drop_fallback_clean` — fallback в резолвере отключён, любой `lms_form_slug=NULL` → автозавершение; `global_block_visible=hide_completely` — UI блока «Конкурс, этап 1» полностью удалён, роут снят.
)
