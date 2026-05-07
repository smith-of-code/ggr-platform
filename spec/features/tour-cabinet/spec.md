# Личный кабинет туров (Tour cabinet)

## Назначение

Отдельная регистрация и вход для участников туров/конкурса (не путать с регистрацией на портале и с LMS-участником). После входа — дашборд **`/tour-cabinet`**: сверху **двухколоночный блок** — слева карточка участника (UserID, ФИО из `profile.display_name`, возраст по `birth_date`, аватар, переключатель «Показать полный профиль» / «Скрыть полный профиль»), блок **полного профиля** по умолчанию скрыт (`v-show`), при якоре `#tour-cabinet-profile` (редирект после сохранения профиля и ссылки с других страниц ЛК) открывается и прокручивается в видимую область; при ошибке валидации профиля блок открывается автоматически. Справа **«Статус заявок»** — заявки из `applications` с `type = tour`, отбор по `email` участника (без учёта регистра), подгрузка `tour`, `tourDeparture` для названия тура и диапазона дат, подписи статусов: новая/в работе → «На проверке», одобрена/отклонена. Ниже — **полный профиль** (редактирование, `PATCH /tour-cabinet/profile` с multipart при аватаре) и блок **конкурса** (`#tour-cabinet-contest`): список **«сейчас доступно участие…»** (`contestLocationOffers`) на бэкенде как раньше, но **на дашборде скрыт** флагом `showContestLocationOffers` в `Dashboard.vue` (`true` — вернуть блок; кнопки — `cursor-pointer`). Основной UI — одна карточка (`#tour-cabinet-contest-detail`): заголовок «Конкурс» и строка прогресса; затем три секции **Этап I–III** — у каждой сводная строка (клик = выбор этапа, `aria-selected`, кольцо фокуса) и под ней панель действий этого этапа (`ContestStage*Panel`), отображаемая при выбранном этапе. Отдельных вкладок «Этап 1/2/3» нет. Анкеты этапа 1 — переход на **`GET /forms/{slug}`** по кнопке города (`tour-cabinet.contest.city-form`).

## Конфигурация

- `config/tour_cabinet.php`: ключ `lms_event_slug` (по умолчанию `vshgr-2026`).
- Переменная окружения: `TOUR_CABINET_LMS_EVENT_SLUG` (опционально).
- `config/tour_cabinet.php`: ключ `dashboard_standard_form_slug` (`TOUR_CABINET_DASHBOARD_STANDARD_FORM_SLUG`) — slug любой LmsForm платформы для отдельного блока «Стандартная анкета» на дашборде (см. фичу `standart-anketa`); переопределяется из админки `/admin/tour-cabinet`.

Формы события создаются в LMS Admin: `lms-admin/{slug}/forms`. В дашборде ЛК туров списка всех форм нет — доступ через конкурс после выбора города.

## Конкурсный сценарий (направления → города → этапы)

**ЛК конкурса для участника** — тот же префикс **`/tour-cabinet`** (см. `spec/features/lk-participant-contests/spec.md`): выбор направления, городов из таблицы `cities`, форма этапа 1 на базе **`LmsForm`** (white-label = конструктор форм в LMS Admin для события `lms_event_slug`) — индивидуально для каждого города направления.

Источник формы Этапа 1 для каждой пары `(direction_id, city_id)` — колонка `tour_cabinet_direction_cities.lms_form_slug`. Если пусто — у города в ЛК статус «Заполнено» (автозавершение, без анкеты). Подробности см. `spec/features/contest-city-forms/spec.md`. Глобальный fallback (`contest_stage1_form_slug_standard` / `more_data`) и админ-блок «Конкурс, этап 1 — какие формы открывать» удалены 2026-04-29 (фича `contest-city-forms`, update «drop-fallback»).

## Модель пользователя

- Колонка `users.is_tour_cabinet_user` (boolean).
- Профиль ЛК: `last_name`, `first_name`, `patronymic`, `gender`, `birth_date`, `phone`, `email`, `avatar_path` (часть полей nullable; `name` синхронизируется из ФИО при сохранении профиля, если строка не пустая). Файл аватара: `POST` с `multipart` на `PATCH /tour-cabinet/profile` (method spoof), поле `avatar`, диск `filesystems.upload_disk`, каталог `tour-cabinet/avatars/{user_id}`; валидация изображения до 2 МБ (JPEG/PNG/WebP/GIF). На дашборде в props передаётся публичный `profile.avatar_url` при наличии файла.
- Регистрация через `/tour-cabinet/register` выставляет флаг в `true`.
- Вход в ЛК туров по **отдельной форме** (`POST /tour-cabinet/login`) по-прежнему только при `is_tour_cabinet_user === true` (самостоятельная регистрация в конкурсе).
- Участник с **профилем LMS** (`lms_profiles`) может заходить в раздел `/tour-cabinet` через общий вход на сайт: middleware `tour-cabinet` допускает пользователя, если `is_tour_cabinet_user` **или** есть любая запись `lms_profiles` для этого пользователя (`PostAuthRedirect::canAccessTourCabinet`).

## Вход с портала

В `MainLayout` для гостей: в шапке кнопка «Зарегистрироваться» (primary, как основной CTA) на `tour-cabinet.register` и контурная «Вход» на сайт; в мобильном меню — то же по смыслу; в футере — текстовые ссылки на регистрацию/вход ЛК туров.

В `MainLayout` для авторизованных: основная кнопка «Личный кабинет» ведёт на **`lmsEntryUrl`** (профиль LMS в событии), если профиль есть; иначе на **`tourCabinetUrl`** (`tour-cabinet.dashboard`), если доступен кабинет туров; иначе на главную. Если доступны **и** LMS, **и** туры, показывается дополнительная ссылка «ЛК туров». Проп `tourCabinetUrl` и `lmsEntryUrl` задаются в `HandleInertiaRequests` (см. `App\Support\PostAuthRedirect`).

После входа на портале (`POST /login`, режим «Я клиент»): редирект по `PostAuthRedirect::clientPortalDefaultUrl` — сначала профиль LMS при наличии профиля, иначе дашборд туров для `is_tour_cabinet_user`, иначе главная.

## Маршруты (`routes/web.php`)

| Метод | Путь | Назначение |
|--------|------|------------|
| GET | `/tour-cabinet` | Дашборд (middleware `tour-cabinet`) |
| PATCH | `/tour-cabinet/profile` | Сохранение профиля с дашборда (`TourCabinetController@updateProfile`) |
| POST | `/tour-cabinet/profile/documents` | Загрузка/замена файла документа профиля (`tour-cabinet.profile.documents.upload`, throttle `tour-cabinet-profile-document`); см. раздел «Документы участника и модерация» |
| DELETE | `/tour-cabinet/profile/documents/{document}` | Удаление файла документа участника (`tour-cabinet.profile.documents.delete`); запрещено при `STATUS_APPROVED` |
| GET | `/tour-cabinet/contest` | Редирект на дашборд с якорем блока конкурса (`#tour-cabinet-contest`; совместимость со старыми ссылками) |
| POST | `/tour-cabinet/contest/direction` | Сохранить выбранное направление (`project_key`) |
| POST | `/tour-cabinet/contest/cities` | Сохранить 1–3 `city_id` из `tour_cabinet_direction_cities` |
| GET | `/tour-cabinet/contest/cities/{city}/form` | Сессия `tour_cabinet_contest_form_city_id` + редирект на `forms.public.show` |
| POST | `/tour-cabinet/contest/complete-stage-1` | Завершение этапа 1 (`current_stage` → 2), если все анкеты по выбранным городам отправлены |
| GET | `/tour-cabinet/contest/stage-2` | Редирект на дашборд `#tour-cabinet-contest` |
| POST | `/tour-cabinet/contest/stage-2` | Сохранение ответов этапа 2, переход на этап 3; редирект на дашборд |
| GET | `/tour-cabinet/contest/stage-3` | Редирект на дашборд `#tour-cabinet-contest` |
| POST | `/tour-cabinet/contest/stage-3` | Первичное сохранение `stage3_text` / `stage3_video_url` (повторное недоступно: в ЛК только просмотр) |
| GET | `/tour-cabinet/support` | Список обращений в поддержку (`TourCabinetSupportController@index`) |
| GET | `/tour-cabinet/support/create` | Форма нового обращения |
| POST | `/tour-cabinet/support` | Создать тикет + первое сообщение (throttle `tour-cabinet-support-ticket`) |
| GET | `/tour-cabinet/support/attachments/{attachment}` | Скачивание вложения (владелец тикета или админ) |
| GET | `/tour-cabinet/support/{ticket}` | Карточка тикета и переписка |
| POST | `/tour-cabinet/support/{ticket}/messages` | Сообщение участника (throttle `tour-cabinet-support-message`) |
| GET/POST | `/tour-cabinet/login` | Гостевые страницы входа |
| GET/POST | `/tour-cabinet/register` | Регистрация |
| POST | `/tour-cabinet/logout` | Выход (требуется `auth`); после выхода редирект на `home` |

Гостевые маршруты: middleware `tour-cabinet.guest` (залогиненный тур-участник редиректится на дашборд).

Защита дашборда: `EnsureTourCabinetUser` — без сессии редирект на `tour-cabinet.login`, без флага — на `home` с flash `error`.

**Гейт «полного профиля» (фича `tour-cabinet-profile-required-gate`)**: доступ к маршрутам участия (конкурс `tour-cabinet.contest.*`, коммерческие туры `tour-cabinet.commerce-tours.*`) дополнительно ограничен middleware `tour-cabinet.profile-complete` (`App\Http\Middleware\EnsureTourCabinetProfileComplete`). Middleware пропускает запрос только если `App\Services\TourCabinetProfileCompleteness::isComplete(User)` возвращает `true` — т.е. заполнены `last_name`, `first_name`, `gender`, `birth_date`, `phone`, `email` И в `tour_cabinet_documents` есть запись типа `personal_data_consent` со статусом `pending_review` или `approved` (непустой `file_path`). Иначе GET → редирект на `tour-cabinet.dashboard` с flash-ошибкой и якорем `#tour-cabinet-profile`, mutating → `back()->withInput()->withErrors(['profile' => '...'])`. Маршруты `tour-cabinet.profile.*`, `tour-cabinet.profile.documents.*`, `tour-cabinet.support.*`, `tour-cabinet.upload.*`, `tour-cabinet.logout` остаются доступными.

Дашборд `TourCabinetController::dashboard` отдаёт в Inertia-проп `profileGate = { complete: bool, missing: string[], message: string }`; на фронте в `Dashboard.vue` при `profileGate.complete === false` рендерится контрастная плашка-уведомление над секцией `#tour-cabinet-profile` и блокируются (через `pointer-events-none + opacity-50 + grayscale + aria-hidden`) секции `#tour-cabinet-favorites`, `#tour-cabinet-standard-form`, `#tour-cabinet-atomic-ticket`, `#tour-cabinet-contest`, `#tour-cabinet-commerce-tours`. Секции `#tour-cabinet-profile`, `#tour-cabinet-documents` и блок «Поддержка» доступны без ограничений.

## Отправка форм

Используются существующие маршруты `forms.public.show` / `forms.public.submit`; при авторизованном пользователе ответы привязываются к `user_id` в логике `FormPublicController`.

## Поддержка (обращения из ЛК)

Спецификация: **`support.md`** — тикеты, сообщения, вложения в MVP; ответы только админы портала; опционально в UI текст «напишите на …» через `config('tour_cabinet.support_contact_email')` / `TOUR_CABINET_SUPPORT_CONTACT_EMAIL`; **авто-письма из приложения не отправляются** — только ЛК и админка. Раздел «Поддержка» виден всем с доступом к `/tour-cabinet` (в т.ч. LMS без `is_tour_cabinet_user`).

## Документы участника и модерация

В ЛК `/tour-cabinet`, секция `#tour-cabinet-documents`, участник загружает сканы паспорта (разворот, прописка) и СНИЛС. Модель — `App\Models\TourCabinetDocument` (таблица `tour_cabinet_documents`):

- Типы (`TourCabinetDocument::allowedTypes()`): `personal_data_consent` («Согласие на обработку персональных данных», обязательный документ для допуска к этапам — см. фичу `tour-cabinet-profile-required-gate`), `passport_spread` («Паспорт: разворот с 1–2 страницей»), `passport_registration` («Паспорт: страница с пропиской»), `snils` («СНИЛС»). Метки — `TourCabinetDocument::typeLabel($type)`.
- Статусы: `pending_review` (после загрузки), `approved` (модератор подтвердил), `annulled` (модератор отклонил — файл удалён, сохраняются `admin_comment` + `reviewed_at`).
- Поля: `user_id`, `type`, `file_path`, `original_name`, `status`, `admin_comment`, `reviewed_at`. После `annul` `file_path`/`original_name` сбрасываются в пустую строку (запись по типу остаётся, чтобы хранить комментарий и историю).
- Хранилище — `config('filesystems.upload_disk')`, каталог `tour-cabinet/documents/{user_id}`. Допустимые форматы загрузки: `pdf,jpg,jpeg,png,doc,docx`, до 50 МБ.

### Поведение в ЛК участника

- `POST tour-cabinet.profile.documents.upload` принимает `type` + `file` (или `file_url` + опц. `file_name`); создаёт/перезаписывает запись по `(user_id, type)` со статусом `pending_review`. Если у документа `STATUS_APPROVED` (`isLockedForParticipant() === true`) — операция запрещена с ошибкой «Этот документ подтверждён модератором. Для изменения обратитесь в поддержку.»
- `DELETE tour-cabinet.profile.documents.delete` — удаляет файл со storage и саму запись; для подтверждённых документов запрещено («Подтверждённый документ можно удалить только через поддержку.»).
- На дашборде после approve по типу показывается подпись «Документ подтверждён модератором…», после annul — красная плашка `«Документ отклонён модератором: <admin_comment>»` (или fallback «Документ отклонён. Загрузите файл заново.») и кнопка «Загрузить» снова доступна.

### Действия модератора в админке

Карточка `/admin/tour-cabinet/tour-users/{user}`, блок «Документы ЛК» (`Admin/TourCabinet/TourUsers/Show.vue`):

- **GET** `admin.tour-cabinet.tour-users.documents.download` — скачивание оригинального файла (404, если файла нет).
- **POST** `admin.tour-cabinet.tour-users.documents.approve` — кнопка «Подтвердить» (доступна, если `has_file && status = pending_review`).
- **POST** `admin.tour-cabinet.tour-users.documents.annul` — кнопка «Отклонить с комментарием» (доступна, если `has_file && status ∈ {pending_review, approved}`); обязательное поле `comment` (`required|string|min:3|max:2000`, сообщение «Укажите комментарий для участника.»).

Бизнес-логика — `App\Services\TourCabinetDocumentReviewService`:

- `approve(TourCabinetDocument $d)`: требует наличие файла; идемпотентно; ставит `status = approved`, `reviewed_at = now()`, `admin_comment = null`.
- `annul(TourCabinetDocument $d, string $comment)`: требует наличие файла и непустой trimmed комментарий; в транзакции удаляет файл со storage (ошибки `Storage::delete` логируются как `tour_cabinet_document_annul_delete_failed`, но не валят операцию), сбрасывает `file_path`/`original_name` в пустую строку, ставит `status = annulled`, сохраняет `admin_comment` и `reviewed_at = now()`. После транзакции, если `recipient->email` непустой, ставит в очередь `emails` `SendMailJob` с письмом `TourCabinetDocumentAnnulledMail($recipient, $document->fresh(), $comment)`.

### Email-уведомление об отклонении документа

- Mailable: `App\Mail\TourCabinetDocumentAnnulledMail` (`Queueable`, `UsesMailDisplayName`); конструктор `(User, TourCabinetDocument, string $adminComment)`; subject «Документ в личном кабинете туров отклонён».
- Шаблон: `resources/views/emails/tour-cabinet-document-annulled.blade.php` — обращение по ФИО (если заполнено), `documentTypeLabel` (через `TourCabinetDocument::typeLabel`), блок «Комментарий модератора» с `white-space: pre-wrap`, кнопка-ссылка на `route('tour-cabinet.dashboard') . '#tour-cabinet-documents'`, подпись `mailFromName` из `UsesMailDisplayName`.
- Доставка — через единый `App\Jobs\SendMailJob` на очереди `emails`. Письмо — единственное автописьмо, отправляемое из ЛК туров (см. раздел «Поддержка»: тикеты — без авто-писем).
- Письма об одобрении документа (`approve`) **не отправляются** — out of scope: участник видит статус в ЛК.

## Портальная админка (ЛК туров)

Точка входа для редакторов: **GET** `/admin/tour-cabinet` (`admin.tour-cabinet.index`) — одна страница «ЛК туров» с тремя блоками на месте: города по направлениям (переключение направления через query `project_key` + якорь `#tour-cabinet-admin-cities`), формы этапа 1, вопросы этапа 2. Отдельного раздела «этап 3» в админке нет (данные — в ЛК участника). После POST-операций редирект обратно на эту страницу с якорем соответствующего блока.

- **GET** `/admin/tour-cabinet/forms` (`admin.tour-cabinet.forms.index`) — отдельная страница с теми же блоками, что на хабе под `#tour-cabinet-admin-forms`: «Дашборд: Стандартная анкета», «Уведомление о завершении конкурса», список форм события. Блок «Конкурс, этап 1 — какие формы открывать» удалён вместе с роутом `admin.tour-cabinet.forms.contest-form-slugs.update` (см. `contest-city-forms` update 2026-04-29). В каждой карточке формы есть действия: «Публичная страница», «Статистика», «Редактировать», **«Дублировать»** (`POST admin.tour-cabinet.lms.forms.duplicate` — deep copy с полями, новый slug, `is_active=false`), **«Удалить»** (`DELETE admin.tour-cabinet.lms.forms.destroy` с подтверждением и упоминанием количества ответов; см. `spec/features/tour-cabinet-forms-delete-copy/spec.md`).
- **PUT** `admin.tour-cabinet.dashboard-form.update` (`/admin/tour-cabinet/dashboard-form`) — привязка формы к блоку «Стандартная анкета» на дашборде ЛК (см. фичу `standart-anketa`). Селект использует `allFormsOptions` (любая активная форма платформы, без ограничения `lms_event_id`). Запись в settings группу `tour_cabinet`, ключ `dashboard_standard_form_slug`. Пустое значение скрывает блок на дашборде.
- **GET** `/admin/tour-cabinet/direction-cities` (`admin.tour-cabinet.direction-cities.index`, query `project_key`) — отдельная страница с тем же UI; CRUD через **POST/PATCH/DELETE** `admin.tour-cabinet.direction-cities.*`, редиректы на хаб с якорем `#tour-cabinet-admin-cities`.
- **GET** `/admin/tour-cabinet/stage2-questions` — отдельная страница с тем же UI; CRUD через **POST/PATCH/DELETE** `admin.tour-cabinet.stage2-questions.*`, редиректы на хаб с якорем `#tour-cabinet-admin-stage2`.
- **GET** `/admin/tour-cabinet/support` (`admin.tour-cabinet.support.index`) — очередь обращений участников ЛК туров; фильтры query `status`, `category`.
- **GET** `/admin/tour-cabinet/support/{ticket}` (`admin.tour-cabinet.support.show`) — переписка, смена статуса (**PATCH** `admin.tour-cabinet.support.status.update`), ответ (**POST** `admin.tour-cabinet.support.messages.store`, throttle `tour-cabinet-support-message`).
- **GET** `/admin/tour-cabinet/tour-users` (`admin.tour-cabinet.tour-users.index`) — список «Клиенты ЛК туров» (фильтры query: `q`, `city_id`, `segment` ∈ `all|tour_only|lms`); **GET** `/admin/tour-cabinet/tour-users/export` (`admin.tour-cabinet.tour-users.export`) — выгрузка CSV (`;`, BOM, заголовки на русском).
- **GET** `/admin/tour-cabinet/tour-users/{user}` (`admin.tour-cabinet.tour-users.show`) — карточка участника: профиль, прогресс конкурса (этапы 1–3), заявки на туры по email и блок «Документы ЛК» (см. раздел «Документы участника и модерация»). **GET/POST** `admin.tour-cabinet.tour-users.documents.{download,approve,annul}` — действия над документами.
