# Блокировка Этапа 2 конкурса, пока Этап 1 не выполнен (contest-stage1-incomplete-lock)

## Goal

Не пускать участника в Этап 2 конкурса (вкладка «Этап II» в `TourCabinet/Dashboard.vue`), пока Этап 1 не пройден. В частности, бажный сценарий:

- участник убирает все выбранные города на Этапе 1 (`selected_city_ids` → пусто), → должно вернуть участника к выбору городов и заблокировать ввод ответов в Этапе 2.
- сейчас же `contestProgress.stage2_locked = false` при `current_stage = 1`, и пользователь может зайти на вкладку «Этап II» и набирать ответы на вопросы, хотя на Этапе 1 не отправлено ни одной анкеты.

Связанные фичи: `tour-cabinet`, `contest-city-forms`, `lk-participant-contests`.

## In-scope

### Бэкенд

- `App\Services\TourCabinetContestDashboardData::isStage2LockedForParticipant`:
  - добавить условие `if ($st < 2) return true;` сразу после проверки `$maxContestStages < 2`.
  - смысл: пока участник не нажал «Перейти к этапу 2» на Этапе 1 (метод `TourCabinetContestController::completeStage1` не выставил `current_stage = 2`), Этап 2 для него заблокирован — даже если на UI он успел кликнуть по вкладке «Этап II».
- Никаких других изменений на бэкенде. Кнопка «Перейти к этапу 2» уже корректно показывается только при `stage1Complete && maxContestStages >= 2` (см. `Contest/ContestStage1Panel.vue`), а сам endpoint `completeStage1` повторно валидирует `stage1Complete` и кидает `ValidationException`, если городов нет.

### Фронт

- `resources/js/Pages/TourCabinet/Contest/ContestStage2Panel.vue`: уже показывает плашку «Этот этап станет доступен для заполнения после завершения этапа 1.» при `locked === true && contestStage === 1`. После фикса бэкенда плашка покажется именно в нужном кейсе. Дополнительные правки фронта не требуются.

## Out-of-scope

- Запрет кликов по самой кнопке-вкладке «Этап II» в `Dashboard.vue` при `stage2_locked = true` (вкладка остаётся кликабельной для просмотра плашки «недоступно», как и сейчас).
- Изменение поведения Этапа 3, кнопок «Перейти к этапу 3», или email-уведомлений.
- Изменение endpoint `removeSelectedCity`: текущая реализация уже сбрасывает `selected_city_ids = null`, удаляет сессию `tour_cabinet_contest_reopen_cities` и возвращает участника на дашборд; `current_stage` не трогается, и теперь это безопасно благодаря фиксу выше.
- Автосброс `current_stage` обратно на 1, если у пользователя уже был `current_stage = 2` и он каким-то образом удалил все города. На практике сценарий невозможен: убрать город с отправленной анкетой запрещено в `TourCabinetContestController::removeSelectedCity`, а в Этап 2 переходят только при выполненном Этапе 1.

## Constraints

- Все команды (artisan, tinker, npm, pest) — в Docker по правилу `spec-continuation`: `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
- Меняется ровно один файл на бэкенде (`TourCabinetContestDashboardData.php`). Без новых сервисов, миграций, моделей.
- Сохранить остальные ветки `isStage2LockedForParticipant` (`maxContestStages < 2`, `current_stage > 2`, submitted) — баг проявляется только при `current_stage === 1`.
- Не трогать `Contest/ContestStage2Panel.vue` и `Dashboard.vue` — поведение там уже корректное относительно пропа `locked`.

## Реализация (как сделано)

- `App\Services\TourCabinetContestDashboardData::isStage2LockedForParticipant` — добавлено условие `if ($st < 2) return true;` сразу после проверки `$maxContestStages < 2`. Комментарий поясняет, что Этап 2 разблокируется только после явного `completeStage1` (который выставляет `current_stage = 2`). Остальные ветки метода сохранены: `maxContestStages < 2`, `current_stage > 2`, `current_stage === 2 && stage2_submitted_at` — продолжают работать как раньше.
- Никаких изменений на фронтенде, в моделях, миграциях, роутах, конфиге `tour_cabinet`. `Contest/ContestStage2Panel.vue` уже корректно отрисовывает плашку «недоступно, пока не завершён Этап 1» по пропу `locked`.

## Verify summary

- `php artisan tinker --execute=...` (Docker) — таблица из 5 кейсов `isStage2LockedForParticipant`:
  1. `st=1, max=3, no submit` (BUG-кейс) → `locked=1` ✅ (раньше было `0`).
  2. `st=2, max=3, no submit` → `locked=0` ✅ (Этап 2 открыт для заполнения).
  3. `st=2, max=3, submitted` → `locked=1` ✅ (просмотр после отправки).
  4. `st=3, max=3` → `locked=1` ✅ (после перехода в Этап 3).
  5. `st=1, max=1` → `locked=1` ✅ (1-этапное направление).
- End-to-end через `TourCabinetContestDashboardData::forUser` (transaction-rollback) с `current_stage=1, selected_city_ids=null`: `stage2_locked=true`, `stage1Complete=false`, `step="cities"`, `current_stage=1`, `max_contest_stages=3`.
- `npm run build` (Docker) — `built in 5.65s`, без ошибок.
- `ReadLints` по `app/Services/TourCabinetContestDashboardData.php` — чисто.

## Open questions

(пусто)
