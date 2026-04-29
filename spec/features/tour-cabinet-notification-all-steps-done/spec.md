# Tour Cabinet — уведомление о завершении всех 3 этапов конкурса

## Goal

Отправлять участнику ЛК Туры (`/tour-cabinet`) e-mail-уведомление сразу после прохождения всех **трёх** этапов блока «Конкурс»; тема, тело письма и флаг активности отправки редактируются администратором в `/admin/tour-cabinet`.

## In-scope

- Триггер отправки: в `TourCabinetContestController::storeStage3` после успешного сохранения ответа этапа 3 — если для направления `max_contest_stages = 3`, ответы этапов 1 и 2 отмечены завершёнными, ответ этапа 3 теперь полностью валиден (`isStage3ResponseCompleteForLock`) и письмо ещё не отправлялось.
- Идемпотентность: новая колонка `tour_cabinet_contest_progress.completion_notified_at` (timestamp, nullable) — фиксирует факт первичной отправки; повторных писем нет.
- Mailable `App\Mail\TourCabinetContestCompletionMail` + blade-шаблон `resources/views/emails/tour-cabinet-contest-completion.blade.php` с подстановкой темы/тела из настроек и ссылкой на `/tour-cabinet`.
- Настройки в таблице `settings`, группа `tour_cabinet`:
  - `contest_completion_notification_enabled` — boolean (по умолчанию `false` — рассылка выключена, пока админ не активирует).
  - `contest_completion_notification_subject` — строка (тема письма).
  - `contest_completion_notification_body` — текст письма (поддержка переносов строк, без HTML).
- Дефолты в `config/tour_cabinet.php` (`contest_completion_notification.*`) с env-переопределением: подставляются, если в `settings` пусто; начальный текст тела — «Спасибо за участие! Ожидайте — мы обязательно вернёмся с обратной связью и результатами конкурса.»
- `SettingsService::getTourCabinetContestCompletionNotification(): array{enabled: bool, subject: string, body: string}` — единая точка чтения (БД → config → встроенные дефолты).
- Админ-маршрут `PUT admin.tour-cabinet.contest-completion-notification.update` → новый метод `Admin\TourCabinetFormsController::updateContestCompletionNotification`; валидация `enabled:boolean`, `subject:string|max:255`, `body:string|max:20000`; запись через `Setting::setGroup('tour_cabinet', …)`.
- Админ-payload: `TourCabinetHubPageData::formsPayload()` отдаёт ключ `contestCompletionNotification` (`{ enabled, subject, body }`) для предзаполнения формы.
- Админ-фронт: новая `RCard`-секция «Уведомление о завершении конкурса» в `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue` (так же отображается на `Hub.vue` и `Forms/Index.vue`): чекбокс «Отправка активна», `RInput` темы, `<textarea>` тела, `RButton` «Сохранить».

## Out-of-scope

- Уведомления о завершении этапов 1 или 2 (отправляем только после полного прохождения этапа 3).
- Письма участникам направлений с `max_contest_stages < 3` — для них «3 этапа» не определены.
- Повторная отправка уведомления после редактирования ответа этапа 3 (повторное прохождение в ЛК уже заблокировано — см. `TourCabinetContestController::storeStage3`).
- HTML-редактор/WYSIWYG для тела письма — простой `<textarea>` с переносами строк (`nl2br` в blade).
- Дополнительные адресаты (CC/BCC администратору, копия в Telegram и т.п.).
- Кастомизация шаблона письма из админки (логотип, стили, подпись) — кроме темы и тела.
- Персонализация подстановками (`{first_name}`, `{direction}` и т.п.) — не в текущей итерации.

## Constraints

- Реюз UI: `RCard`, `RButton`, `RInput`, `RCheckbox` (UI Kit, глобальные); `<textarea>` со стилями как в существующих панелях `TourCabinetAdmin*Panel.vue`.
- Хранение настроек — только через `SettingsService::setGroup('tour_cabinet', …)` (как `dashboard_standard_form_slug`, `contest_stage_*_deadline_*`).
- Mailable обязан использовать `App\Mail\Concerns\UsesMailDisplayName` (как `TourCabinetDocumentAnnulledMail`) и `Mailable::queue()` либо `send()` без падений при пустом тексте — fallback на дефолты.
- Отправка только при `enabled = true` И `auth()->user()->email` непустой; ошибки `Mail::send` логируются, но не валят `storeStage3` — уведомление вторично к сохранению ответа.
- Все команды (миграция, route:list, npm build, pest) — через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`) согласно `spec-continuation`.
- Колонка `completion_notified_at` добавляется отдельной миграцией, sqlite-совместимой (без `DROP COLUMN` / без переименований).

## Реализация (как сделано)

- БД: миграция `2026_04_29_120000_add_completion_notified_at_to_tour_cabinet_contest_progress.php` — nullable `timestamp completion_notified_at` после `stage3_attachment_original_name`. В `TourCabinetContestProgress` добавлены fillable + cast `datetime`.
- Конфиг: `config/tour_cabinet.php`, ключ `contest_completion_notification` (`enabled` через `FILTER_VALIDATE_BOOLEAN`, `subject`, `body`); env: `TOUR_CABINET_CONTEST_COMPLETION_NOTIFICATION_ENABLED|SUBJECT|BODY`. Дефолтное тело — «Спасибо за участие! Ожидайте — мы обязательно вернёмся с обратной связью и результатами конкурса.»
- Settings: `SettingsService::getTourCabinetContestCompletionNotification(): array{enabled,subject,body}` — приоритет БД (группа `tour_cabinet`, ключи `contest_completion_notification_enabled|subject|body`) → config → дефолты; пустые строки в БД → fallback на config; `enabled` парсится через `FILTER_VALIDATE_BOOLEAN`.
- Mailable + view: `App\Mail\TourCabinetContestCompletionMail` (`UsesMailDisplayName`, конструктор `(User, string $subjectText, string $body)`, fallback subject в `envelope()`). Blade-шаблон `resources/views/emails/tour-cabinet-contest-completion.blade.php` — обращение по ФИО, тело с `white-space: pre-wrap`, ссылка на `tour-cabinet.dashboard`.
- Триггер: в `TourCabinetContestController::storeStage3` после каждой ветки `progress->update(...)` вызывается `dispatchContestCompletionNotificationIfReady($progress, $request->user())`. Проверки: `completion_notified_at IS NULL`, `current_stage >= 3`, `maxContestStagesForDirection === 3`, `isStage3ResponseCompleteForLock`, непустой `email`, `settings.enabled = true`. Отправка через `Mail::to($email)->send(...)`; ошибки оборачиваются в `try/catch` + `Log::warning` (сохранение этапа 3 не валится). После успешной отправки — `progress->forceFill(['completion_notified_at' => now()])->save()`.
- Админ-маршрут: `PUT /admin/tour-cabinet/contest-completion-notification` (`admin.tour-cabinet.contest-completion-notification.update`) → `Admin\TourCabinetFormsController::updateContestCompletionNotification`. Валидация `enabled:nullable|boolean`, `subject:nullable|string|max:255`, `body:nullable|string|max:20000`; запись через `SettingsService::setGroup('tour_cabinet', [...])`; `enabled` пишется как `'1'|'0'`. Redirect на `admin.tour-cabinet.index` + fragment `tour-cabinet-admin-completion-notification`.
- Payload админки: `TourCabinetHubPageData::formsPayload()` дополнительно отдаёт `contestCompletionNotification` = `getTourCabinetContestCompletionNotification()`.
- Админ-фронт: новая `RCard` (id `tour-cabinet-admin-completion-notification`) в `TourCabinetAdminFormsPanel.vue` — нативный чекбокс «Отправка активна», `<input type="text">` темы (max 255), `<textarea rows="6">` тела (max 20000), `RButton` «Сохранить»; submit через `useForm.put` на новый маршрут; ошибки валидации отображаются под полями; `watch` подхватывает обновлённые пропы. `Forms/Index.vue` явно пробрасывает проп `contestCompletionNotification`; `Hub.vue` — через `v-bind="formsSection"` (без правок).

## Open questions

(пусто — все интерфейсы и пути подтверждены в коде: `TourCabinetContestController`, `TourCabinetContestProgress`, `SettingsService`, `Admin\TourCabinetFormsController`, `TourCabinetHubPageData`, `TourCabinetAdminFormsPanel.vue`, `Mail/TourCabinetDocumentAnnulledMail.php`).
