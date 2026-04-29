# Progress: contest-stage1-incomplete-lock

## Не начато

— (пусто)

## Частично выполнено

— (пусто)

## Выполнено

- **Блокировка Этапа 2 при `current_stage < 2`** в `App\Services\TourCabinetContestDashboardData::isStage2LockedForParticipant`:
  - Добавлена ветка `if ($st < 2) return true;` сразу после проверки `$maxContestStages < 2`.
  - Verify (Docker): tinker-таблица из 5 кейсов `isStage2LockedForParticipant` — все ожидаемые значения; end-to-end `forUser` для пользователя без выбранных городов — `stage2_locked=true`, `step="cities"`, `stage1Complete=false`.
  - `npm run build` — успешно (regression на фронте проверен; правок в Vue не было).
  - `ReadLints` по затронутому PHP-файлу — чисто.

## Открытые вопросы

— (пусто)
