# Блокировка следующего этапа в Коммерческих турах до нажатия кнопки перехода (commerce-tours-stage-progression-lock)

## Goal

В блоке «Коммерческие туры» (`#tour-cabinet-commerce-tours` в `TourCabinet/Dashboard.vue`) запретить участнику открывать и заполнять следующий этап, пока он не нажал кнопку перехода:

- **Этап 2 (анкета доп. данных)** должен быть заблокирован, пока участник не нажал «Перейти к этапу 2 →» (метод `TourCabinetCommerceToursController::completeStage1` не выставил `current_stage = 2`).
- **Этап 3 (статичный экран ожидания)** должен быть заблокирован, пока сабмит формы этапа 2 не перевёл `current_stage = 3` (через `TourCabinetCommerceToursFormLinker`).

Аналог фичи `contest-stage1-incomplete-lock`, расширенный на коммерческие туры. Бэкенд блокировки в Конкурсе уже реализован (`TourCabinetContestDashboardData::isStage2LockedForParticipant` + `TourCabinetContestController::storeStage2/storeStage3` уже валидируют `current_stage`); в Коммерческих турах не хватает только фронтовой плашки и одного defense-in-depth-гарда в линкере.

Связанные фичи: `commerce-tours`, `contest-stage1-incomplete-lock`, `tour-cabinet`.

## Бажный сценарий

1. Участник в блоке «Коммерческие туры» выбрал город и тур (Этап 1, `current_stage = 1`).
2. **НЕ нажал** «Перейти к этапу 2 →».
3. Кликнул по вкладке «Этап II» в дашборде → видит резюме «Город / Тур» и кнопку «Открыть анкету» как будто этап доступен.
4. По клику «Открыть анкету» бэкенд (`TourCabinetCommerceToursController::startCityForm`) уже корректно отбивает (возвращает на дашборд при `current_stage < 2`), но визуально пользователь не понимает, что этап заблокирован — кнопка кликабельна, плашки «недоступно» нет.

В блоке «Конкурс» аналогичный сценарий уже исправлен: `ContestStage2Panel.vue` показывает плашку «Этот этап станет доступен для заполнения после завершения этапа 1.» по пропу `locked`, а бэкенд (`isStage2LockedForParticipant`, метод `forUser`) выставляет `stage2_locked = true` при `current_stage < 2`.

## In-scope

### Бэкенд

- `App\Services\TourCabinetCommerceToursDashboardData::buildPayload`:
  - добавить в payload поле `stage2Locked` — `true` при `currentStage < 2 || currentStage >= 3` (то есть Этап 2 разблокирован только в окне `current_stage === 2`, идентично `isStage2LockedForParticipant` в Конкурсе, но без проверки `stage2_submitted_at` — у коммерческих туров переход 2 → 3 идёт через сабмит LMS-формы).
  - вынести логику в private-метод `isStage2LockedForParticipant(int $currentStage): bool`.
- `App\Services\TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission`:
  - добавить ранний `return`, если `current_stage < 2` — defense-in-depth: запрещаем скачок `current_stage` 1 → 3 при возможной ручной подмене сессии. На текущем UI-флоу сессия `tour_cabinet_commerce_form_city_id` ставится только в `startCityForm`, который уже валидирует `current_stage >= 2`, но дублирующий гард в линкере соответствует общему правилу «следующий этап разблокирован только после явного перехода».
- Никаких новых контроллеров, миграций, моделей, маршрутов. `TourCabinetCommerceToursController::startCityForm` уже корректно отбивает при `current_stage < 2`.

### Фронт

- `resources/js/Pages/TourCabinet/Dashboard.vue`:
  - пробросить `commerceTours.stage2Locked` в `CommerceToursStage2Panel` через проп `locked` (вместо общего `commerceToursLocked`, который сейчас означает «весь блок просмотр после Этапа 3»).
  - добавить computed-проп `commerceToursStage3Locked` = `currentStage < 3` и пробросить его в `CommerceToursStage3Panel` через проп `locked` (новый проп панели).
  - `CommerceToursStage1Panel` оставить как есть — он уже использует общий `commerceToursLocked` для блокировки изменения выбора после Этапа 3.
- `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage2Panel.vue`:
  - добавить проп `currentStage: { type: Number, default: 1 }`.
  - при `locked === true` показать плашку: `currentStage === 1` → «Этот этап станет доступен после нажатия кнопки «Перейти к этапу 2» на этапе 1.»; `currentStage >= 3` → «Анкета этапа 2 уже отправлена. Редактирование недоступно.» (для согласованности с `ContestStage2Panel`).
  - при `locked` скрыть кнопки «Открыть анкету» и «Изменить выбор» (они уже скрыты по `!locked`, но логика проп `locked` теперь точнее).
- `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage3Panel.vue`:
  - добавить проп `locked: { type: Boolean, default: false }`.
  - при `locked === true` показать плашку: «Этот этап станет доступен после успешной отправки анкеты на этапе 2.»; subject/body не отображать (или скрыть основной контент).

## Out-of-scope

- Запрет кликов по самой вкладке «Этап II» / «Этап III» в `Dashboard.vue` при `locked === true` — вкладки остаются кликабельными для просмотра плашки «недоступно» (как и в блоке Конкурс).
- Блокировка изменения выбора города/тура на Этапе 1 при `current_stage === 2`. На практике пользователь может вернуться через «Изменить выбор» (`reopenSelection`), который сбрасывает `current_stage = 1`. Поведение выходит за рамки текущего бага.
- Изменение поведения блока «Конкурс» — фикс для него уже реализован в фиче `contest-stage1-incomplete-lock` (изменение `isStage2LockedForParticipant`). Текущая фича — про коммерческие туры.
- Изменение схемы БД, маршрутов, модельных связей, конфига `tour_cabinet`.
- Серверный запрет на сохранение Этапа 3 (его и так нет — Stage3Panel это просто отображение настройки, серверного `store`-метода у пользователя нет).

## Constraints

- Все команды (artisan, tinker, npm, pest) — в Docker по правилу `spec-continuation`: `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
- Меняется до 5 файлов:
  - `app/Services/TourCabinetCommerceToursDashboardData.php`
  - `app/Services/TourCabinetCommerceToursFormLinker.php`
  - `resources/js/Pages/TourCabinet/Dashboard.vue`
  - `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage2Panel.vue`
  - `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage3Panel.vue`
- Не трогать `CommerceToursStage1Panel.vue` (его проп `locked` остаётся «весь блок завершён», смысл не меняется).
- Не нарушать существующий контракт payload `commerceTours`: только добавление поля `stage2Locked`. Дефолт в `Dashboard.vue` props должен быть совместим (`stage2Locked: true` по умолчанию — безопасный fallback, если поле не пришло).
- Сохранить ветку `commerceToursLocked = currentStage >= 3` в `Dashboard.vue` для `CommerceToursStage1Panel` (UX блокировки изменения выбора после завершения).

## Реализация (как сделано)

### Бэкенд

- `App\Services\TourCabinetCommerceToursDashboardData::buildPayload` — добавлено поле `stage2Locked` в возвращаемый payload, значение через приватный метод `isStage2LockedForParticipant(int $currentStage): bool` — `true` при `currentStage < 2 || currentStage >= 3`. Поле `currentStage` в payload теперь формируется как `clampedStage = max(1, min(3, $currentStage))` отдельной локальной переменной — реюз для `stage2Locked` (ранее inline-clamp). Никаких изменений сигнатур, маршрутов, моделей, миграций.
- `App\Services\TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission` — добавлен ранний `return`, если `current_stage < 2` (defense-in-depth). На штатном UI-флоу сюда нельзя попасть (`startCityForm` уже отбивает при `current_stage < 2`), но дублирующий гард предотвращает скачок `current_stage` 1 → 3 при возможной ручной подмене сессии `tour_cabinet_commerce_form_city_id`.

### Фронт

- `resources/js/Pages/TourCabinet/Dashboard.vue`:
  - в дефолте пропа `commerceTours` добавлено `stage2Locked: true` (безопасный fallback).
  - добавлены computed: `commerceToursStage2Locked` (читает `commerceTours.stage2Locked`, фолбэк — локальная формула `currentStage < 2 || currentStage >= 3`); `commerceToursStage3Locked` (`currentStage < 3`).
  - `CommerceToursStage2Panel` теперь принимает `current-stage` и `locked` (= `commerceToursStage2Locked`) вместо общего `commerceToursLocked`. `CommerceToursStage3Panel` принимает `locked` (= `commerceToursStage3Locked`).
  - `CommerceToursStage1Panel` оставлен без изменений (его `locked` = «весь блок завершён»).
- `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage2Panel.vue`:
  - добавлен проп `currentStage: { type: Number, default: 1 }`.
  - при `locked === true` показывается плашка «Этот этап станет доступен после нажатия кнопки «Перейти к этапу 2 →» на этапе 1.» (для `currentStage <= 1`) либо «Анкета этапа 2 уже отправлена. Редактирование недоступно.» (для `currentStage > 1`). Резюме «Город / Тур» и кнопки «Открыть анкету» / «Изменить выбор» скрыты при `locked`.
- `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage3Panel.vue`:
  - добавлен проп `locked: { type: Boolean, default: false }`.
  - при `locked === true` показывается плашка «Этот этап станет доступен после успешной отправки анкеты на этапе 2.»; subject/body не отображаются.

## Verify summary

- `php artisan tinker --execute=...` (Docker) — таблица из 4 кейсов `isStage2LockedForParticipant` (через рефлексию):
  1. `currentStage = 1` → `stage2Locked = true` ✅ (BUG-кейс: пользователь не нажал «Перейти к этапу 2 →»).
  2. `currentStage = 2` → `stage2Locked = false` ✅ (Этап 2 разблокирован).
  3. `currentStage = 3` → `stage2Locked = true` ✅ (анкета отправлена, переход в режим ожидания).
  4. `currentStage = 4` (out-of-range) → `stage2Locked = true` ✅.
- `php artisan tinker --execute=...` end-to-end через `TourCabinetCommerceToursDashboardData::buildPayload` (transaction-rollback) — для одного и того же пользователя при `current_stage = 1, 2, 3`: payload корректно отдаёт `stage2Locked = true / false / true`.
- Sanity check: в `app/Services/TourCabinetCommerceToursFormLinker.php` присутствует строка `current_stage < 2` (`GUARD_PRESENT`).
- `npm run build` (Docker) — `built in 5.66s`, без ошибок.
- `ReadLints` по затронутым PHP- и Vue-файлам — чисто.

## Open questions

(пусто)
