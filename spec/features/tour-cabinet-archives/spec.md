# Архивы заявок в ЛК Туров (tour-cabinet-archives)

**Status:** реализовано (см. `progress.md` — все 10 задач Completed).

## Goal

Автоматически перемещать отправленные заявки участника в ЛК `/tour-cabinet` в два read-only архива — «Архив конкурсы» (однократная заявка, после отправки блок «Конкурс» блокируется) и «Архив коммерческих туров» (множественные заявки, после каждой отправки блок «Коммерческие туры» очищается и снова активен для следующей заявки) — с сохранением полного снапшота данных и расширением админ-карточки клиента архивами.

## In-scope

### Хранилище (БД)

- Новая таблица `tour_cabinet_contest_archives` (один user — одна-несколько записей; в текущем MVP однократно, но без `unique(user_id)` — на случай ручного admin-reset с последующим повторным прохождением):
  - `id` PK, `user_id` FK→`users` (index), `direction_id` FK→`directions` nullable, `submitted_at` timestamp (момент завершения этапа 3), `status` varchar(32) с дефолтом `sent` (для будущего расширения; в UI отображается «Отправлено»), `payload` JSON (полный снапшот, см. ниже), `timestamps`.
  - JSON `payload` для конкурса: `{ direction: {id,label,key}, current_stage_at_archive, selected_cities: [{id,name,needs_more_data}], stage1_city_forms: [{city_id, city_name, lms_form_slug, lms_form_submission_id, submitted_at, responses: [{label,value}]}], stage2: {questions: [{id, body, answer_text, updated_at}], submitted_at}, stage3: {assignment_title, response_format, task_body, text, video_url, attachment_path, attachment_original_name}, completion_notified_at }`. Источник данных — те же сервисы, что использует админка `TourCabinetClientContestDataService::contestPayloadForUser` (реюз).
- Новая таблица `tour_cabinet_commerce_archives` (множественные записи на пользователя):
  - `id` PK, `user_id` FK→`users` (index), `city_id` FK→`cities` nullable, `tour_id` FK→`tours` nullable, `lms_form_submission_id` FK→`lms_form_submissions` nullable (SET NULL on cascade — LMS-сабмит остаётся первичным источником данных LMS-модуля), `submitted_at` timestamp, `status` varchar(32) дефолт `sent`, `payload` JSON, `timestamps`.
  - JSON `payload` для коммерческих туров: `{ city: {id,name}, tour: {id,title,date_range_label?}, lms_form: {slug, title, submission_id, responses: [{label,value}]}, stage3_notification: {subject, body} }`. Тексты этапа 3 берутся из текущей настройки `settings.tour_cabinet.commerce_tours_stage3_*` на момент архивации (заморозка для read-only-просмотра).
- В `tour_cabinet_contest_progress` добавить колонку `archived_at` timestamp nullable (флаг «архивная заявка создана»; при `!== null` блок «Конкурс» в ЛК заблокирован и очищен). Колонка добавляется отдельной миграцией, sqlite-совместимой.
- Архивы — иммутабельны: в коде нет update/delete-методов; админ-сброс через фичу `admin-settings-reset-contest-progress` архивы НЕ трогает (см. Out-of-scope ниже про политику reset).

### Логика «Конкурс → Архив конкурсы»

- Триггер: `TourCabinetContestController::storeStage3` — после успешного сохранения этапа 3 и вызова `dispatchContestCompletionNotificationIfReady`. Условия архивации: `current_stage >= 3` И `isStage3ResponseCompleteForLock(progress) === true` И `archived_at IS NULL`. Идемпотентно: повторный сабмит после архивации заблокирован существующей проверкой `isStage3ResponseCompleteForLock` (она возвращает true после первого сохранения текста этапа 3), архивная запись не дублируется.
- Сервис `App\Services\TourCabinetContestArchiveService::archiveProgress(TourCabinetContestProgress $progress, User $user): TourCabinetContestArchive` — в одной DB-транзакции: собрать `payload` через реюз `TourCabinetClientContestDataService::contestPayloadForUser($user)`, создать `tour_cabinet_contest_archives`-запись с `submitted_at = now()` и `status = 'sent'`, поставить `progress->archived_at = now()`. Ошибки логируются (`tour_cabinet_contest_archive_failed`) и не валят сохранение этапа 3 (UX: участник видит «Данные этапа 3 сохранены», архив создастся при следующем триггере или повторно через ручную миграцию).
- Гард в пользовательских роутах конкурса: middleware `EnsureTourCabinetContestNotArchived` (или inline-проверка в `TourCabinetContestController`) — при `archived_at !== null` все mutating-методы конкурса (`storeDirection`, `storeCities`, `removeSelectedCity`, `completeStage1`, `storeStage2`, `storeStage3`, `startStage1Form`) возвращают редирект на дашборд с flash-error «Заявка на конкурс уже отправлена. Просмотр — в Архиве конкурсы.». GET-маршруты дашборда продолжают работать.
- Состояние блока «Конкурс» на дашборде при `archived_at !== null`:
  - Все панели `ContestStage1Panel`/`ContestStage2Panel`/`ContestStage3Panel` рендерятся с классами `pointer-events-none opacity-50 grayscale aria-hidden`.
  - Над секцией показывается контрастная плашка-уведомление (RCard с `tone="warning"` или эквивалент): «Вы уже оформили заявку на конкурс. Она отображается в Архиве конкурсы. Новый конкурс стартует в следующем году.» + иконка `LockClosedIcon` + кнопка `RButton` «Перейти в архив» → `tour-cabinet.archives.contest.index`.
  - Заполненные поля визуально скрыты/заменены сообщением (см. подробности в `plan.md`).
- Повторное участие — только через существующий админ-флоу `admin.settings.contest-reset.reset` (`App\Services\Admin\TourCabinetContestProgressResetter`); см. также раздел «Расширение admin-reset» ниже.

### Логика «Коммерческие туры → Архив коммерческих туров»

Архивация — **двухшаговый** пользовательский флоу, чтобы участник увидел экран этапа 3 («Заявка принята» с настраиваемым subject/body) перед очисткой блока:

1. **Шаг 1 — сабмит анкеты этапа 2.** `TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission` переводит прогресс на `current_stage = 3`, сохраняет `lms_form_submission_id` и `completed_at` (как было до фичи). Архивация на этом шаге **НЕ** запускается. Пользователь после сабмита формы возвращается на `forms.public.show` (как обычно для LMS-форм) и, перейдя на дашборд, видит на вкладке «Этап 3» уведомление «Заявка принята» + кнопку «Сохранить в архив и оформить новую заявку».
2. **Шаг 2 — пользовательский action.** Кнопка в `CommerceToursStage3Panel.vue` (показывается только при `current_stage >= 3 && !locked`) отправляет POST через Inertia `router.post(route('tour-cabinet.commerce-tours.archive-and-reset'))` → `TourCabinetCommerceToursController::archiveAndReset` → вызывает `TourCabinetCommerceArchiveService::archiveAndResetProgress(progress, user)`. Сервис:
   - Достаёт LMS-сабмит из `progress->lms_form_submission_id` (nullable — если по каким-то причинам null, payload `lms_form.responses = []`).
   - Собирает снапшот (город, тур, ответы LMS-формы через `LmsFormSubmission::with('responses.field')`, текущий `settings.tour_cabinet.commerce_tours_stage3_*`).
   - В одной DB-транзакции создаёт `tour_cabinet_commerce_archives`-запись (`submitted_at = now()`, `status = 'sent'`) и **сбрасывает** прогресс: `current_stage = 1`, `city_id = null`, `tour_id = null`, `lms_form_submission_id = null`, `completed_at = null`.
   - Альтернатива «удалить запись прогресса целиком» отвергнута — `TourCabinetCommerceProgress::firstOrCreate` в контроллерах уже корректно создаст пустую запись, но обнуление полей сохраняет `id`/`timestamps` для аудита.
3. **Гарды.** Контроллер отбрасывает запросы с `current_stage < 3` (flash-error «Нельзя сохранить в архив, пока не завершена анкета этапа 2.»). При ошибке сервиса (`archive === null`) — flash-error «Не удалось сохранить заявку в архив. Попробуйте ещё раз или обратитесь в поддержку.»; ошибки логируются (`tour_cabinet_commerce_archive_failed`) и не валят первоначальное `current_stage = 3` (try/catch в сервисе).
4. **Редирект.** `redirect()->route('tour-cabinet.dashboard')->withFragment('tour-cabinet-commerce-tours')->with('success', '…')->with('tour_cabinet_commerce_just_archived', true)`. На дашборде:
   - Flash `success` = «Заявка отправлена и сохранена в Архиве коммерческих туров. Новая заявка может быть создана прямо сейчас.».
   - Флаг `tour_cabinet_commerce_just_archived = true` — должен быть зарегистрирован в `HandleInertiaRequests::share()::flash` (иначе Inertia его не передаст в `$page.props.flash`). Vue-watcher в `Dashboard.vue` через `nextTick` вызывает `scrollAndHighlight('tour-cabinet-commerce-tours')` (амбер-подсветка на 2.2 сек).
5. **Цикл повторяется** без ограничений — после reset блок снова на этапе 1, пользователь выбирает новый город/тур.

### Архивные страницы в ЛК

- Новый раздел в `TourCabinetQuickNav` после «Поддержка»: ссылки «Архив конкурсы» и «Архив коммерческих туров» (видны для пользователей с доступом к `/tour-cabinet`; пустой архив отображается с заглушкой «Архивных заявок пока нет.»). Альтернатива «вкладка только при наличии хотя бы одной записи» — отвергнута для предсказуемости UX.
- Новые маршруты под middleware `tour-cabinet` (без гейта `tour-cabinet.profile-complete` — read-only-просмотр архивных заявок не зависит от полноты профиля):
  - `GET /tour-cabinet/archives/contest` (`tour-cabinet.archives.contest.index`) — список карточек конкурсных заявок пользователя, сортировка `submitted_at DESC`. Каждая карточка `RCard`: дата «отправлено DD.MM.YYYY HH:MM», `RBadge` «Отправлено» (зелёный), кнопка «Посмотреть детали» → переход на `tour-cabinet.archives.contest.show`.
  - `GET /tour-cabinet/archives/contest/{archive}` (`tour-cabinet.archives.contest.show`) — детальная страница: реюз шаблона из админ-`Admin/TourCabinet/TourUsers/Show.vue` (блоки «Прогресс и направление», «Этап 1 — анкеты по городам», «Этап 2 — ответы на вопросы», «Этап 3 — проверочное задание»), но read-only (без admin-кнопок). Гард: `archive->user_id === auth()->id()` (иначе 404).
  - `GET /tour-cabinet/archives/commerce` (`tour-cabinet.archives.commerce.index`) — список карточек коммерческих заявок, сортировка `submitted_at DESC`. Каждая карточка: дата, `RBadge` «Отправлено», название тура + город (для быстрого распознавания), кнопка «Посмотреть детали».
  - `GET /tour-cabinet/archives/commerce/{archive}` (`tour-cabinet.archives.commerce.show`) — детальная страница: город/тур, ответы LMS-формы, текст уведомления этапа 3 (всё из `payload` JSON архива). Гард: `archive->user_id === auth()->id()`.
- Контроллеры: `App\Http\Controllers\TourCabinet\Archives\ContestArchiveController` (`index`, `show`) и `CommerceArchiveController` (`index`, `show`).
- Inertia-страницы: `resources/js/Pages/TourCabinet/Archives/Contest/Index.vue`, `Contest/Show.vue`, `Commerce/Index.vue`, `Commerce/Show.vue`.

### Расширение админ-карточки клиента

- В `Admin/TourCabinet/TourUsers/Show.vue` (страница `/admin/tour-cabinet/tour-users/{user}`) добавить две новые секции после блока «Конкурс и заявки на туры»:
  - **«Архив конкурсы»** — список архивных карточек этого пользователя (`tour_cabinet_contest_archives` по `user_id`), для каждой — раскрывающийся блок (`<details>` или `RCard` с toggle) с теми же данными, что в детальной странице ЛК.
  - **«Архив коммерческих туров»** — список архивных карточек (`tour_cabinet_commerce_archives` по `user_id`), сортировка `submitted_at DESC`, с раскрытием деталей.
- `TourCabinetTourUsersController::show` дополняется загрузкой архивов через `Schema::hasTable`-проверки (как для других таблиц) и реюзом сервиса `TourCabinetArchiveDisplayService::buildContestRows / buildCommerceRows`.
- Существующий блок «Конкурс (этапы 1–3) и заявки на туры» **остаётся** — он показывает текущий прогресс конкурса (`tour_cabinet_contest_progress`), а архивы — это исторические снапшоты (даже после ручного admin-reset архивы остаются видны).

### Расширение admin-reset конкурса

- Фича `admin-settings-reset-contest-progress` (`App\Services\Admin\TourCabinetContestProgressResetter::reset`) **архивы НЕ удаляет** — это исторические данные, нужные для «не пропадают заявки из админки» (см. требование пользователя). После reset:
  - `tour_cabinet_contest_progress` удаляется (включая `archived_at`) → блок «Конкурс» снова доступен для участника, он может пройти конкурс заново.
  - `tour_cabinet_contest_archives` остаются → видны и в ЛК-архиве, и в админ-карточке клиента.
  - Если участник снова пройдёт конкурс → создастся вторая запись `tour_cabinet_contest_archives` (поэтому в схеме нет `unique(user_id)`).
- Никаких изменений в коде `TourCabinetContestProgressResetter` не требуется — он по-прежнему трогает только три таблицы прогресса, архивная — отдельная.

## Out-of-scope

- Авто-разблокировка конкурса «через год» по таймеру/scheduled-команде. В MVP — только ручной admin-reset через существующую фичу `admin-settings-reset-contest-progress`. Авто-разблокировка по дате (`config('tour_cabinet.contest_next_year_starts_at')` + `php artisan tour-cabinet:contest:unlock-yearly`) — потенциальная следующая фича.
- Возможность участника удалить запись из своего архива (по требованию — архив только для чтения).
- Возможность редактирования заявки в архиве (read-only).
- Email-уведомление участнику или администратору о факте архивации (письмо о завершении конкурса уже отправляется через существующий `dispatchContestCompletionNotificationIfReady`; отдельных писем «заявка в архиве» не добавляем).
- Сброс/удаление коммерческих архивов из админки — пока нет UI-сценария «удалить заявку из архива клиента». При необходимости — отдельная фича (массовые операции, аудит).
- Экспорт архивных заявок в CSV/Excel — пока работает существующий экспорт `admin.tour-cabinet.tour-users.export` (он берёт текущий прогресс + applications); расширение экспорта архивами — потенциальная следующая фича.
- Пагинация архивов в ЛК (для MVP `paginate(20)` достаточно; виртуальная прокрутка/бесконечный список — out of scope).
- Кастомизация текста информсообщения «Новый конкурс стартует в следующем году» из админки (`settings.tour_cabinet.contest_archive_locked_message`) — в MVP текст хардкод в `Dashboard.vue` (точно по ТЗ); вынос в settings — следующая итерация при появлении необходимости.
- Изменение поведения LMS-сабмишенов: `lms_form_submissions` остаются неизменными (как и в `admin-settings-reset-contest-progress`), архивы лишь ссылаются на них через `lms_form_submission_id` nullable FK.
- Доступ к админ-архивам через отдельную страницу `/admin/tour-cabinet/archives/*` — в этой фиче архивы только встроены в карточку клиента (`/admin/tour-cabinet/tour-users/{user}`).

## Constraints

- Все команды (миграции, route:list, npm build, pest) — через Docker по правилу `spec-continuation`: `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
- Миграции sqlite-совместимы: только `Schema::create` для новых таблиц + `addColumn` (без `DROP COLUMN`, без переименований, без удаления unique-индексов на существующих колонках). Колонка `archived_at` добавляется отдельной миграцией.
- Реюз композаблов/сервисов (правило `Reuse before create`):
  - Сборка снапшота конкурса — через существующий `App\Services\Admin\TourCabinetClientContestDataService::contestPayloadForUser` (текущий источник истины для UI админ-карточки). При архивации payload «замораживается» в JSON.
  - Сборка ответов LMS-формы коммерческого тура — через `LmsFormSubmission::with('responses.field')` (тот же паттерн, что в `FormPublicController`).
  - Уже существующий `admin-settings-reset-contest-progress` остаётся источником истины для повторного открытия конкурса участнику.
- Контракт payload архивов сохраняется иммутабельным: после создания записи `payload` не пересчитывается, даже если изменились настройки или справочники (это позволяет показывать «исторический срез» — например, заголовок направления на момент сабмита).
- Состояние LMS-сабмишенов не меняется: `lms_form_submission_id` в коммерческом архиве — FK с `onDelete('set null')`. Удаление LMS-формы или её сабмита (через soft-delete LmsForm) не валит просмотр архива — UI показывает «Форма больше недоступна» при отсутствии сабмита.
- UI-Kit reuse: `RCard`, `RButton`, `RBadge`, `RModal` (UI Kit `@rosatom-ggr/ui-kit`, глобально зарегистрированы в `resources/js/app.js`); иконки — Heroicons (`LockClosedIcon`, `ArchiveBoxIcon`, `ClockIcon`, `CheckCircleIcon`); композабл `useToast` для подсветок после редиректа.
- Маршруты архивов в ЛК — под middleware `tour-cabinet` (`EnsureTourCabinetUser`), но БЕЗ `tour-cabinet.profile-complete` (read-only-просмотр доступен независимо от полноты профиля). Маршруты-mutators у пользовательских контрольников **в архивах отсутствуют** — только GET.
- Скролл к блоку коммерческих туров после архивации: реализуется на фронте через flash-флаг + `nextTick` + `scrollIntoView`. Не использовать `window.location.hash` — Inertia-навигация может терять hash. Альтернатива (если flash недостаточен) — `router.on('finish', ...)` хук, обсуждение в `plan.md`.
- Лимит на размер одного шага — до 5 файлов и до ~150 строк diff (см. правило `Change scope limits`). Деление на задачи в `tasks.md` соответствует этому ограничению.

## Open questions

(пусто — все принципиальные решения зафиксированы выше: однократность конкурса = архив через ручной admin-reset; множественность коммерческих туров = обнуление прогресса после архивации; админ-карточка клиента расширяется архивами, существующие данные не теряются.)

## Ответ на уточнение пользователя

Уточнение: «в Клиентах в админке... будут отображаться все заявки у клиента со всего портала и ЛК клиента, включая коммерческие туры и конкурс?»

Текущее состояние (до фичи) — карточка клиента в админке `/admin/tour-cabinet/tour-users/{user}` (`TourCabinetTourUsersController::show` + `Admin/TourCabinet/TourUsers/Show.vue`):

- **Конкурс** — отображается текущий прогресс `tour_cabinet_contest_progress` (направление, этап, выбранные города, ответы этапов 1–3 через `TourCabinetClientContestDataService`). ✅
- **Заявки на туры** — отображаются заявки из таблицы `applications.type = 'tour'` по совпадению `email` пользователя (это заявки через публичные формы туров на портале). ✅
- **Коммерческие туры из ЛК** (таблица `tour_cabinet_commerce_progress`) — **сейчас не отображаются**. ❌
- **Документы ЛК** (`tour_cabinet_documents`) — отображаются. ✅

После этой фичи:

- Архивные заявки конкурса и коммерческих туров будут отображаться в новых секциях карточки клиента (см. раздел «Расширение админ-карточки клиента» выше). ✅
- Существующий блок «Конкурс (этапы 1–3) и заявки на туры» **остаётся без изменений** — заявки `applications.type='tour'` не пропадут. ✅
- Текущий прогресс коммерческих туров (если пользователь начал, но не завершил заявку) **в эту фичу не включён** — это отдельная задача (расширить `Show.vue` секцией «Коммерческие туры — текущий прогресс»). При необходимости — заведём отдельной фичей.
