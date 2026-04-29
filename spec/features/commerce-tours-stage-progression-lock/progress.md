# Progress: commerce-tours-stage-progression-lock

## Не начато

— (пусто)

## Частично выполнено

— (пусто)

## Выполнено

- **Блокировка Этапа 2 в блоке «Коммерческие туры» при `current_stage < 2`** — `App\Services\TourCabinetCommerceToursDashboardData::buildPayload` отдаёт новое поле `stage2Locked`; логика вынесена в private `isStage2LockedForParticipant(int $currentStage): bool` (`true` при `currentStage < 2 || currentStage >= 3`).
- **Defense-in-depth-гард в линкере** — `App\Services\TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission` теперь делает ранний `return` при `current_stage < 2`, исключая возможный скачок `current_stage` 1 → 3 при ручной подмене сессии.
- **Фронт-плашка «недоступно» в Этапе 2** — `CommerceToursStage2Panel.vue`: новый проп `currentStage`, плашка для `locked === true && currentStage <= 1` («Этот этап станет доступен после нажатия кнопки «Перейти к этапу 2 →» на этапе 1.») и для `locked === true && currentStage > 1` («Анкета этапа 2 уже отправлена. Редактирование недоступно.»). Резюме и кнопки скрыты при `locked`.
- **Фронт-плашка «недоступно» в Этапе 3** — `CommerceToursStage3Panel.vue`: новый проп `locked`, плашка «Этот этап станет доступен после успешной отправки анкеты на этапе 2.»; subject/body не отображаются при `locked`.
- **Проброс пропов из дашборда** — `Dashboard.vue`: добавлены computed `commerceToursStage2Locked` (с fallback при отсутствии `stage2Locked` в payload) и `commerceToursStage3Locked` (`currentStage < 3`); пропы переданы в `CommerceToursStage2Panel` (вместе с `currentStage`) и `CommerceToursStage3Panel`. В дефолте пропа `commerceTours` добавлено `stage2Locked: true` (безопасный fallback).
- Verify (Docker):
  - tinker-таблица из 4 кейсов `isStage2LockedForParticipant` (через рефлексию) — все ожидаемые значения.
  - end-to-end `buildPayload` при `current_stage = 1/2/3` — `stage2Locked = true/false/true`.
  - sanity-check файла `TourCabinetCommerceToursFormLinker.php` — гард `current_stage < 2` присутствует.
  - `npm run build` — `built in 5.66s`, без ошибок.
  - `ReadLints` по затронутым файлам — чисто.

## Открытые вопросы

— (пусто)
