# Tour Cabinet — обязательное заполнение профиля до доступа к остальным этапам (tour-cabinet-profile-required-gate)

## Goal

В ЛК `/tour-cabinet` запретить участнику доступ ко всем остальным разделам/этапам (Стандартная анкета, Атомный билет / Конкурс, Коммерческие туры, заявки на туры, обращения в поддержку — кроме самого профиля и документов поддержки) до тех пор, пока не заполнен «полный профиль» и не загружено согласие на обработку персональных данных. После заполнения — все разделы автоматически разблокируются.

Связанные фичи: `tour-cabinet`, `contest-stage1-incomplete-lock`, `commerce-tours-stage-progression-lock`, `standart-anketa`.

## Бажный сценарий (как сейчас)

- Участник зарегистрировался / вошёл в `/tour-cabinet`.
- Не заполнил ФИО / пол / дату рождения / телефон / e-mail / не загрузил согласие на ОПД.
- Сразу видит блоки «Стандартная анкета», «Атомный билет», «Конкурс», «Коммерческие туры», «Поддержка» — может кликать кнопки, открывать формы, отправлять заявки. Это контракт: «нет персональных данных → нет участия» нарушается.

Нужно: пока профиль не «полностью заполнен» — никакие другие этапы недоступны (UI заблокирован, серверные эндпоинты отбивают валидацией), и в шапке профиля висит крупная контрастная плашка с призывом дозаполнить.

## In-scope

### Бэкенд

- Сервис `App\Services\TourCabinetProfileCompleteness`:
  - метод `isComplete(User $user): bool` — `true`, если заполнены все обязательные поля профиля **и** есть запись `tour_cabinet_documents` типа `personal_data_consent` со статусом ∈ `{pending_review, approved}` (наличие файла, не `annulled`).
  - метод `missingFields(User $user): array` — список ключей пустых обязательных полей (`last_name`, `first_name`, `gender`, `birth_date`, `phone`, `email`, `personal_data_consent`).
  - используется в дашборде, контроллерах конкурса, коммерческих туров, стандартной анкеты, поддержки, загрузки документов профиля.
- `App\Http\Controllers\TourCabinetController::dashboard`: пробрасывает в Inertia новый проп `profileGate` (`{ complete: bool, missing: string[], message: string }`).
- Серверный гард в эндпоинтах участия (defense-in-depth): любой `POST/PATCH/DELETE` маршрут `tour-cabinet.*` (кроме `tour-cabinet.profile.update`, `tour-cabinet.profile.documents.upload` для типа `personal_data_consent`, `tour-cabinet.logout`, `tour-cabinet.support.*`) при `! isComplete($user)` возвращает `back()->withErrors(['profile' => 'Сначала заполните профиль и загрузите согласие на обработку персональных данных.'])`. Реализуется через middleware `tour-cabinet.profile-complete` и навешивается на нужные роуты.
- Тип документа `personal_data_consent` — добавить новую константу в `App\Models\TourCabinetDocument::TYPE_PERSONAL_DATA_CONSENT`, дополнить `allowedTypes()` и `typeLabel()` («Согласие на обработку персональных данных»). Расширить `uploadProfileDocument` валидацию `type` на новый тип. Карточка/блок «Документы» в дашборде показывает новый тип отдельно (см. UI ниже). Файл хранится по тому же диску `filesystems.upload_disk` в каталоге `tour-cabinet/documents/{user_id}`. Допустимые форматы и лимит — как у остальных документов (`pdf,jpg,jpeg,png,doc,docx`, до 50 МБ).
- Документация в `spec/features/tour-cabinet/spec.md` (раздел «Документы участника и модерация», `TourCabinetDocument::allowedTypes()`): добавить запись `personal_data_consent`. **Обновляется по ходу реализации.**

### Фронт

- `resources/js/Pages/TourCabinet/Dashboard.vue`:
  - добавить компьютед `profileGateActive = !profileGate.complete`.
  - над секцией `#tour-cabinet-profile` (или сразу под верхним двухколоночным блоком) — крупная контрастная плашка-уведомление: «Заполните профиль, чтобы получить доступ к остальным разделам» + список недозаполненных полей и пометка «загрузите согласие на обработку персональных данных», если оно отсутствует. Дизайн — аналогично существующему уведомлению `Стандартная анкета` (`bg-amber-100/80` + `border-amber-300` + `text-amber-900`), но более заметный (`border-2`, иконка `ExclamationTriangleIcon`).
  - при `profileGateActive === true` скрыть/заблокировать секции: `#tour-cabinet-favorites`, `#tour-cabinet-standard-form`, `#tour-cabinet-atomic-ticket`, `#tour-cabinet-contest`, `#tour-cabinet-commerce-tours`. Точнее: каждую секцию обернуть в `<div :class="profileGateActive ? 'pointer-events-none opacity-50 grayscale' : ''">` + плашку поверх «Сначала заполните профиль» с якорем-ссылкой `#tour-cabinet-profile`. Альтернатива — `v-show="!profileGateActive"`; финальный вариант выбирается на этапе реализации (см. план).
  - блок `#tour-cabinet-documents` остаётся доступен — там лежит загрузка согласия. Внутри блока `#tour-cabinet-documents` отдельно отображается тип `personal_data_consent` (вверху, с пометкой «обязательный документ»).
- `resources/js/Pages/TourCabinet/Dashboard.vue` — после сабмита профиля (`profileForm.put`) и/или после успешной загрузки `personal_data_consent` Inertia-ответ обновит `profileGate` на сервере, плашка/блокировки исчезнут автоматически.
- Поведение «Поддержка» (`/tour-cabinet/support`) — **не блокируется** профилем (участник должен иметь возможность написать в поддержку до заполнения профиля).

## Out-of-scope

- Принудительный редирект участника с любых страниц `/tour-cabinet/*` обратно на `#tour-cabinet-profile` при попытке открыть прямую URL (например, `/tour-cabinet/contest/cities/{city}/form`). Серверный гард уже возвращает `back()->withErrors(...)`, отдельной cross-route-логики выхода нет.
- Модерация согласия на ОПД (approve/annul) с отдельной email-нотификацией. На MVP документ `personal_data_consent` загружается, попадает в очередь модерации (`pending_review`), но валидируется в `isComplete()` ровно по факту наличия файла и не-`annulled` статусу. Расширение workflow модерации согласия — отдельная фича.
- Изменение валидации полей профиля (формат телефона, имени и т.п.) — гейт использует ровно текущий набор обязательных полей: `last_name`, `first_name`, `gender`, `birth_date`, `phone`, `email`. Поле `patronymic` остаётся опциональным (Out-of-scope).
- Отдельная страница «Заполните профиль» вместо плашки на дашборде. UI-решение — плашка + блокировка секций на месте.
- Изменение публичной формы регистрации `/tour-cabinet/register` (там уже есть чекбокс согласия — это операционный лог `Consent::TYPE_REGISTRATION`, не файл).
- Блокировка раздела «Поддержка» (`tour-cabinet.support.*`) — наоборот, должна остаться доступной без полного профиля.
- LMS-кабинет (`/lms-event/*`) — фича касается только `/tour-cabinet`, профиль LMS живёт отдельно.

## Constraints

- Все команды (artisan, tinker, npm, pest) — в Docker по правилу `spec-continuation`: `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
- Меняется до 5 файлов на шаг (см. план). Общий объём фичи — больше, поэтому разбивается на 7–10 последовательных задач.
- Не нарушать существующий контракт payload `profile`/`profileDocuments`/`commerceTours`/`contestProgress`: только добавление поля `profileGate` в Inertia-проп и расширение `TourCabinetDocument::allowedTypes()` новым типом.
- Реюз: `RCard`, `RButton`, иконки `@heroicons/vue/24/outline` (`ExclamationTriangleIcon`, `IdentificationIcon`); никаких новых композаблов / общих компонентов, если хватит существующих.
- Миграции для нового типа документа НЕ требуется — `tour_cabinet_documents.type` это уже строковая колонка с whitelist в модели; достаточно расширить `allowedTypes()`.
- Сообщения об ошибках на русском (UI-надписи, серверные сообщения `withErrors`).
- Совместимость с уже существующими блокировками (`isStage2LockedForParticipant`, `commerceToursStage2Locked`, `contestStage3Locked`) — новый гейт работает «выше» них (если профиль не заполнен — все секции скрыты целиком).

## Реализация (как сделано)

### Бэкенд

- `App\Services\TourCabinetProfileCompleteness` — единая SSoT гейта. Константы обязательных полей (`FIELD_LAST_NAME`, `FIELD_FIRST_NAME`, `FIELD_GENDER`, `FIELD_BIRTH_DATE`, `FIELD_PHONE`, `FIELD_EMAIL`, `FIELD_PERSONAL_DATA_CONSENT`); `REQUIRED_PROFILE_FIELDS` — список полей профиля (без `personal_data_consent`). Метод `isComplete(User)` делегирует в `missingFields(User) === []`. `missingFields(User)` обходит `REQUIRED_PROFILE_FIELDS` и приватный `profileFieldFilled` (для `birth_date` — `!== null`, для строк — `trim() !== ''`), затем добавляет `personal_data_consent`, если нет валидной записи `tour_cabinet_documents` (тип `personal_data_consent`, статус ∈ `{pending_review, approved}`, непустой `file_path`). При отсутствии таблицы `tour_cabinet_documents` (миграции не запущены) согласие считается валидным (graceful degrade).
- `App\Models\TourCabinetDocument` — добавлена константа `TYPE_PERSONAL_DATA_CONSENT = 'personal_data_consent'`, в `allowedTypes()` тип добавлен **первым** в списке, в `typeLabel()` — метка «Согласие на обработку персональных данных». `TourCabinetController::uploadProfileDocument` уже использует `Rule::in(TourCabinetDocument::allowedTypes())`, поэтому новый тип принимается без правки контроллера.
- `App\Http\Middleware\EnsureTourCabinetProfileComplete` — DI получает `TourCabinetProfileCompleteness`. При незаполненном профиле GET/HEAD → `redirect()->route('tour-cabinet.dashboard')->withErrors(['profile' => '...'])->withFragment('tour-cabinet-profile')`, остальные методы → `back()->withInput()->withErrors(...)->withFragment(...)`. Сообщение: «Сначала заполните профиль и загрузите согласие на обработку персональных данных, чтобы получить доступ к остальным разделам.»
- `bootstrap/app.php` — зарегистрирован alias `tour-cabinet.profile-complete => EnsureTourCabinetProfileComplete::class`.
- `routes/web.php` — маршруты `tour-cabinet.contest.*` (включая `tour-cabinet.contest`, `tour-cabinet.contest.stage3.attachment`) и `tour-cabinet.commerce-tours.*` обёрнуты в подгруппу `Route::middleware('tour-cabinet.profile-complete')`. Открытыми остались `dashboard`, `profile.update`, `profile.documents.*`, `support.*`, `upload.*`, `login*`, `register*`, `logout`. Verify (`Route::getRoutes()` через tinker) — 17 маршрутов гейта помечены `GATED`.
- `App\Http\Controllers\TourCabinetController::dashboard` — инжектит `TourCabinetProfileCompleteness`; в Inertia-render отдаёт `profileGate = { complete: bool, missing: string[], message: string }`.

### Фронт (`resources/js/Pages/TourCabinet/Dashboard.vue`)

- Импорт `ExclamationTriangleIcon`. Проп `profileGate` с дефолтом `{ complete: true, missing: [], message: '' }`. Карта `PROFILE_GATE_FIELD_LABELS` (RU-метки полей), computed `profileGateActive`, `profileGateMissingLabels`, `gatedSectionClass`.
- Над секцией `#tour-cabinet-profile` — секция `#tour-cabinet-profile-gate` (`v-if="profileGateActive"`): амбер-плашка `bg-amber-100/80 border-2 border-amber-300 text-amber-900` с `ExclamationTriangleIcon`, заголовком «Сначала заполните профиль», текстом сообщения, badge-pill списком отсутствующих полей и двумя кнопками-якорями: «Перейти к профилю» (`#tour-cabinet-profile`), «Загрузить согласие на ОПД» (`#tour-cabinet-documents`).
- Секции `#tour-cabinet-favorites`, `#tour-cabinet-standard-form`, `#tour-cabinet-atomic-ticket`, `#tour-cabinet-contest`, `#tour-cabinet-commerce-tours` получают `:class="gatedSectionClass"` (`pointer-events-none select-none opacity-50 grayscale` при активном гейте) и `:aria-hidden="profileGateActive"`. Для `#tour-cabinet-commerce-tours` — через массив `:class`, чтобы не перебить highlight-class.
- В `docConfig` добавлена запись `personal_data_consent` (label, `required: true`, `hint`); рендер обязательных пунктов выделен `border-amber-300 bg-amber-50/70`, под label выводится `dt.hint` шрифтом `font-semibold text-amber-900`.

### Verify (Docker, `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`)

- `php artisan tinker` — таблица из 5 кейсов `TourCabinetProfileCompleteness`: `empty / full+no consent / full+pending / full+approved / full+annulled` → ожидаемые `complete` / `missing`.
- `php artisan tinker` — end-to-end через `TourCabinetController::dashboard` для пустого / полного профиля: `profileGate.complete = 0/1`, `missing = […]/[]`, `message_len = 231` (>50 ✅).
- `php artisan tinker` — middleware `EnsureTourCabinetProfileComplete` напрямую: пустой профиль → `302 redirect`, полный → `200 passthrough`.
- `php artisan tinker` (route enum) — все 17 маршрутов `tour-cabinet.contest.*` / `tour-cabinet.commerce-tours.*` помечены `GATED`, остальные `open`.
- `npm run build` — успешно (≈8.0–8.7s, без ошибок).
- `ReadLints` по всем затронутым PHP-/Vue-файлам — чисто.
- Существующий feature-тест `tests/Feature/TourCabinet/TourCabinetSupportTicketTest.php` — пре-existing sqlite-падение в миграции `2026_04_24_100100_replace_project_key_with_direction_id_in_tour_cabinet_tables.php` (см. `spec/90-open-questions.md` пункт 9, не вызвано данной фичей).

## Open questions

1. **Состав обязательных полей профиля (avatar?)**: на MVP считаем `last_name`, `first_name`, `gender`, `birth_date`, `phone`, `email` обязательными, `patronymic` и `avatar_path` — опциональными. Уточнение от заказчика — в виде отдельной задачи на расширение `TourCabinetProfileCompleteness::REQUIRED_PROFILE_FIELDS`.
2. **Зачёт согласия на ОПД**: считаем профиль «полным» при наличии файла `personal_data_consent` со статусом ∈ `{pending_review, approved}` (т.е. после `annul` модератором участник снова теряет доступ к этапам и должен перезагрузить файл). Подтвердить с заказчиком — корректно ли это поведение или достаточно факта первой загрузки.
3. **Документы паспорта/СНИЛС в гейте**: пока не учитываются (только `personal_data_consent`). Уточнение — нужно ли расширить гейт на `passport_spread`, `passport_registration`, `snils` со статусом `approved`. На MVP — Out-of-scope.
