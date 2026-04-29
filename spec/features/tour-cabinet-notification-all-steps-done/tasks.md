# Tasks: уведомление о завершении всех 3 этапов конкурса

> Каждая задача — независимо верифицируемая. Verify-команды — по паттерну `spec-continuation` (раздел «Command patterns»). Если объём задачи превышает 5 файлов / 150 строк — разбить.

## Task 1. Миграция: `completion_notified_at` в `tour_cabinet_contest_progress`

- Goal: добавить nullable `timestamp completion_notified_at` для идемпотентной отправки письма.
- Scope: `database/migrations/<дата>_add_completion_notified_at_to_tour_cabinet_contest_progress.php`.
- DoD: миграция содержит `up`/`down`, добавляет колонку без drop/rename (sqlite-совместимо); `php artisan migrate --pretend` показывает корректный SQL.
- Verify: `php artisan migrate --pretend` и `php artisan migrate` (Docker pattern).

## Task 2. Модель: `TourCabinetContestProgress` — fillable + cast

- Goal: открыть запись/чтение `completion_notified_at`.
- Scope: `app/Models/TourCabinetContestProgress.php`.
- DoD: `completion_notified_at` в `$fillable`; `casts()` отдаёт `'completion_notified_at' => 'datetime'`.
- Verify: `php artisan tinker` — `TourCabinetContestProgress::factory()` (или прямой save) принимает поле и читает как Carbon.

## Task 3. Конфиг: дефолты уведомления в `config/tour_cabinet.php`

- Goal: добавить дефолты темы/тела/флага активности с env-перекрытием.
- Scope: `config/tour_cabinet.php`.
- DoD: ключ `contest_completion_notification` (`enabled`, `subject`, `body`) с env `TOUR_CABINET_CONTEST_COMPLETION_NOTIFICATION_ENABLED|SUBJECT|BODY`; дефолтное тело — «Спасибо за участие! Ожидайте — мы обязательно вернёмся с обратной связью и результатами конкурса.».
- Verify: `php artisan config:show tour_cabinet.contest_completion_notification` (Docker pattern).

## Task 4. SettingsService: чтение настройки уведомления

- Goal: единый метод чтения с приоритетом БД → config.
- Scope: `app/Services/SettingsService.php`.
- DoD: метод `getTourCabinetContestCompletionNotification(): array` возвращает `{enabled: bool, subject: string, body: string}`; пустые значения в БД заменяются дефолтами из config; кэширование как у `getGroup`.
- Verify: `php artisan tinker` — вызвать метод без записей в `settings` (вернутся config-дефолты), затем `Setting::setGroup('tour_cabinet', […])` и убедиться, что значения подменяются (cache forget работает через `setGroup`).

## Task 5. Mailable + blade-шаблон письма

- Goal: класс письма с подстановкой subject/body из settings и blade-вью.
- Scope: `app/Mail/TourCabinetContestCompletionMail.php`, `resources/views/emails/tour-cabinet-contest-completion.blade.php`.
- DoD: Mailable использует trait `UsesMailDisplayName`, конструктор `(User $user, string $subject, string $body)`, blade выводит body c `nl2br(e(...))`, ссылку `route('tour-cabinet.dashboard')`, имя отправителя из `mailDisplayName()`.
- Verify: `php artisan tinker` — `Mail::fake(); Mail::to('test@example.com')->send(new TourCabinetContestCompletionMail(...));` затем `Mail::assertSent(...)`.

## Task 6. Триггер отправки в `storeStage3`

- Goal: после сохранения этапа 3 при полном прохождении конкурса отправить письмо однократно.
- Scope: `app/Http/Controllers/TourCabinetContestController.php` (метод `storeStage3` + приватный helper `dispatchCompletionNotification`).
- DoD: проверка `max_contest_stages === 3`, этапы 1+2 завершены, `isStage3ResponseCompleteForLock` теперь true, `completion_notified_at` пустой, настройка `enabled = true`, `user->email` непустой; `Mail::to($user->email)->send(new TourCabinetContestCompletionMail(...))` (или `queue()`); `progress->update(['completion_notified_at' => now()])`; ошибки отправки оборачиваются `try/catch` + `Log::warning`, чтобы не сломать ответ контроллера.
- Verify: feature-pest сценарий: ставим `Mail::fake()`, готовим прогресс с этапами 1+2 завершёнными, отправляем `POST /tour-cabinet/contest/stage-3`, ожидаем `Mail::assertSent(TourCabinetContestCompletionMail::class)` и заполненный `completion_notified_at`; повторный вызов письма не отправляет.

## Task 7. Админ-маршрут + контроллер

- Goal: `PUT admin.tour-cabinet.contest-completion-notification.update` для сохранения настройки.
- Scope: `routes/web.php`, `app/Http/Controllers/Admin/TourCabinetFormsController.php`.
- DoD: маршрут зарегистрирован в группе `/admin/tour-cabinet`; метод `updateContestCompletionNotification` валидирует `enabled:boolean`, `subject:nullable|string|max:255`, `body:nullable|string|max:20000`, пишет через `Setting::setGroup('tour_cabinet', [...])`, делает redirect на `admin.tour-cabinet.index` с `withFragment('tour-cabinet-admin-forms')` и flash success.
- Verify: `php artisan route:list --path=admin/tour-cabinet/contest-completion-notification`.

## Task 8. Payload админки: `contestCompletionNotification`

- Goal: предзаполнить форму в админке текущими значениями.
- Scope: `app/Services/Admin/TourCabinetHubPageData.php`.
- DoD: `formsPayload()` дополнительно возвращает `'contestCompletionNotification' => ['enabled' => bool, 'subject' => string, 'body' => string]`, источник — `SettingsService::getTourCabinetContestCompletionNotification()`.
- Verify: `php artisan tinker` — вызвать `app(TourCabinetHubPageData::class)->formsPayload()` и убедиться, что новый ключ присутствует с дефолтными значениями.

## Task 9. Админ-фронт: блок «Уведомление о завершении конкурса»

- Goal: новая `RCard`-секция в админ-панели форм + проброс пропа.
- Scope: `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue`, `resources/js/Pages/Admin/TourCabinet/Hub.vue`, `resources/js/Pages/Admin/TourCabinet/Forms/Index.vue`.
- DoD: `RCard` с `RCheckbox` (enabled), `RInput` (subject), `<textarea rows="6">` (body), `RButton` «Сохранить»; submit через `useForm({...}).put(route('admin.tour-cabinet.contest-completion-notification.update'))`; ошибки валидации отображаются под полями; проп `contestCompletionNotification` объявлен в `defineProps` и пробрасывается из `Hub.vue` и `Forms/Index.vue`.
- Verify: `npm run build` (Docker pattern); вручную: открыть `/admin/tour-cabinet#tour-cabinet-admin-forms` (smoke-проверка после деплоя).

## Task 10. Финализация: spec/progress + полная верификация

- Goal: зафиксировать итоговое состояние фичи.
- Scope: `spec/features/tour-cabinet-notification-all-steps-done/spec.md`, `spec/features/tour-cabinet-notification-all-steps-done/progress.md`, опционально — кросс-ссылка в `spec/features/tour-cabinet/spec.md`.
- DoD: spec.md дополнен разделом «Реализация (как сделано)» с финальными именами роутов/полей; progress.md помечает все задачи `Completed`; `php artisan migrate`, `php artisan route:list`, `npm run build` отработали без ошибок; pest-сценарий (Task 6) зелёный.
- Verify: финальный прогон `pest --filter=ContestCompletion` (или эквивалент) и `npm run build` (Docker pattern).
