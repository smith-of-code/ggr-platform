# Plan: уведомление о завершении всех 3 этапов конкурса

## Milestones

- M1. БД и настройки: миграция `add_completion_notified_at_to_tour_cabinet_contest_progress`, обновление `TourCabinetContestProgress` (fillable + casts), новые ключи в `config/tour_cabinet.php` (с env), методы в `SettingsService`.
- M2. Mailable + шаблон: `App\Mail\TourCabinetContestCompletionMail` + `resources/views/emails/tour-cabinet-contest-completion.blade.php` (подставляет subject/body из настроек, ссылка на `/tour-cabinet`).
- M3. Триггер отправки: дополнить `TourCabinetContestController::storeStage3` — после успешного `update()` детектируем «всё пройдено» (`max_contest_stages = 3`, этапы 1+2 завершены, этап 3 теперь валиден), отправляем письмо при `enabled = true` и `completion_notified_at IS NULL`, фиксируем `completion_notified_at = now()`.
- M4. Админ-маршрут + контроллер: `PUT /admin/tour-cabinet/contest-completion-notification` (`admin.tour-cabinet.contest-completion-notification.update`) → `Admin\TourCabinetFormsController::updateContestCompletionNotification` с валидацией.
- M5. Payload админки: расширить `TourCabinetHubPageData::formsPayload()` ключом `contestCompletionNotification` (`{ enabled, subject, body }`).
- M6. Админ-фронт: новая `RCard`-секция «Уведомление о завершении конкурса» в `TourCabinetAdminFormsPanel.vue`; проп проброшен через `Hub.vue` и `Forms/Index.vue`.
- M7. Финализация: верификация в Docker (`route:list`, `migrate`, `npm run build`, ручная проверка отправки `--queue` либо `send`); обновление `spec.md` (раздел «Реализация (как сделано)») и `progress.md`.

## UI Components

- `RCard` — обёртка карточки в админ-панели (`TourCabinetAdminFormsPanel.vue`).
- `RInput` — поле «Тема письма».
- `RCheckbox` — переключатель «Отправка активна» (`enabled`).
- `<textarea>` (нативный, tailwind-классы как у других панелей) — поле «Тело письма» (многострочный текст, без WYSIWYG).
- `RButton` — кнопка «Сохранить» (variant=primary, `:loading=processing`).
- На стороне ЛК участника UI-изменений нет — только серверный триггер и mailable.

## Verification

- Команды выполнять строго через Docker по паттерну из `spec-continuation` (раздел «Command patterns» / «PHP commands» / «Frontend commands»). Конкретные команды задаёт каждая Task в её разделе «Verify» — отдельные примеры здесь не дублируются.
- Минимум проверок: `php artisan migrate --pretend` → `migrate`, `php artisan route:list --path=tour-cabinet/contest-completion-notification`, `npm run build`, ручная проверка триггера через `pest` или `tinker` (создать `TourCabinetContestProgress`, вызвать `storeStage3`, проверить `completion_notified_at` и факт `Mail::fake` отправки).
