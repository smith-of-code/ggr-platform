# Личный кабинет туров (Tour cabinet)

## Назначение

Отдельная регистрация и вход для участников туров/конкурса (не путать с регистрацией на портале и с LMS-участником). После входа — дашборд **`/tour-cabinet`**: сверху **двухколоночный блок** — слева карточка участника (UserID, ФИО из `profile.display_name`, возраст по `birth_date`, аватар, переключатель «Показать полный профиль» / «Скрыть полный профиль»), блок **полного профиля** по умолчанию скрыт (`v-show`), при якоре `#tour-cabinet-profile` (редирект после сохранения профиля и ссылки с других страниц ЛК) открывается и прокручивается в видимую область; при ошибке валидации профиля блок открывается автоматически. Справа **«Статус заявок»** — заявки из `applications` с `type = tour`, отбор по `email` участника (без учёта регистра), подгрузка `tour`, `tourDeparture` для названия тура и диапазона дат, подписи статусов: новая/в работе → «На проверке», одобрена/отклонена. Ниже — **полный профиль** (редактирование, `PATCH /tour-cabinet/profile` с multipart при аватаре) и блок **конкурса** (`#tour-cabinet-contest`): список **«сейчас доступно участие…»** (`contestLocationOffers`) на бэкенде как раньше, но **на дашборде скрыт** флагом `showContestLocationOffers` в `Dashboard.vue` (`true` — вернуть блок; кнопки — `cursor-pointer`). Основной UI — одна карточка (`#tour-cabinet-contest-detail`): заголовок «Конкурс» и строка прогресса; затем три секции **Этап I–III** — у каждой сводная строка (клик = выбор этапа, `aria-selected`, кольцо фокуса) и под ней панель действий этого этапа (`ContestStage*Panel`), отображаемая при выбранном этапе. Отдельных вкладок «Этап 1/2/3» нет. Анкеты этапа 1 — переход на **`GET /forms/{slug}`** по кнопке города (`tour-cabinet.contest.city-form`).

## Конфигурация

- `config/tour_cabinet.php`: ключ `lms_event_slug` (по умолчанию `vshgr-2026`).
- Переменная окружения: `TOUR_CABINET_LMS_EVENT_SLUG` (опционально).

Формы события создаются в LMS Admin: `lms-admin/{slug}/forms`. В дашборде ЛК туров списка всех форм нет — доступ через конкурс после выбора города.

## Конкурсный сценарий (направления → города → этапы)

**ЛК конкурса для участника** — тот же префикс **`/tour-cabinet`** (см. `spec/features/lk-participant-contests/spec.md`): выбор направления, городов из таблицы `cities`, две формы этапа 1 на базе **`LmsForm`** (white-label = конструктор форм в LMS Admin для события `lms_event_slug`).

Slug двух форм этапа 1: `contest_stage1_form_slug_standard`, `contest_stage1_form_slug_more_data`. **Приоритет:** значения из таблицы `settings` (группа `tour_cabinet`, те же ключи), затем `config/tour_cabinet.php` / env `TOUR_CABINET_CONTEST_STAGE1_FORM_SLUG_*`. Редактирование в админке: **GET** `/admin/tour-cabinet/forms` — блок «Конкурс, этап 1»; **PUT** `admin.tour-cabinet.forms.contest-form-slugs.update`. Пустой выбор в админке = только env/config. Пока оба итоговых slug пустые — UI ЛК показывает заглушку.

## Модель пользователя

- Колонка `users.is_tour_cabinet_user` (boolean).
- Профиль ЛК: `last_name`, `first_name`, `patronymic`, `gender`, `birth_date`, `phone`, `email`, `avatar_path` (часть полей nullable; `name` синхронизируется из ФИО при сохранении профиля, если строка не пустая). Файл аватара: `POST` с `multipart` на `PATCH /tour-cabinet/profile` (method spoof), поле `avatar`, диск `filesystems.upload_disk`, каталог `tour-cabinet/avatars/{user_id}`; валидация изображения до 2 МБ (JPEG/PNG/WebP/GIF). На дашборде в props передаётся публичный `profile.avatar_url` при наличии файла.
- Регистрация через `/tour-cabinet/register` выставляет флаг в `true`.
- Вход в ЛК туров (`POST /tour-cabinet/login`) разрешён только при `is_tour_cabinet_user === true` (иначе сессия сбрасывается и показывается ошибка).

## Вход с портала

В `MainLayout` для гостей: в шапке кнопка «Зарегистрироваться» (primary, как основной CTA) на `tour-cabinet.register` и контурная «Вход» на сайт; в мобильном меню — то же по смыслу; в футере — текстовые ссылки на регистрацию/вход ЛК туров.

Для авторизованных с `is_tour_cabinet_user`: кнопка «Личный кабинет» ведёт на `tour-cabinet.dashboard` (приоритет выше, чем LMS `lmsEntryUrl` и `profile.edit`), чтобы участник всегда попадал в ЛК туров с любой страницы портала.

## Маршруты (`routes/web.php`)

| Метод | Путь | Назначение |
|--------|------|------------|
| GET | `/tour-cabinet` | Дашборд (middleware `tour-cabinet`) |
| PATCH | `/tour-cabinet/profile` | Сохранение профиля с дашборда (`TourCabinetController@updateProfile`) |
| GET | `/tour-cabinet/contest` | Редирект на дашборд с якорем блока конкурса (`#tour-cabinet-contest`; совместимость со старыми ссылками) |
| POST | `/tour-cabinet/contest/direction` | Сохранить выбранное направление (`project_key`) |
| POST | `/tour-cabinet/contest/cities` | Сохранить 1–3 `city_id` из `tour_cabinet_direction_cities` |
| GET | `/tour-cabinet/contest/cities/{city}/form` | Сессия `tour_cabinet_contest_form_city_id` + редирект на `forms.public.show` |
| POST | `/tour-cabinet/contest/complete-stage-1` | Завершение этапа 1 (`current_stage` → 2), если все анкеты по выбранным городам отправлены |
| GET | `/tour-cabinet/contest/stage-2` | Редирект на дашборд `#tour-cabinet-contest` |
| POST | `/tour-cabinet/contest/stage-2` | Сохранение ответов этапа 2, переход на этап 3; редирект на дашборд |
| GET | `/tour-cabinet/contest/stage-3` | Редирект на дашборд `#tour-cabinet-contest` |
| POST | `/tour-cabinet/contest/stage-3` | Первичное сохранение `stage3_text` / `stage3_video_url` (повторное недоступно: в ЛК только просмотр) |
| GET/POST | `/tour-cabinet/login` | Гостевые страницы входа |
| GET/POST | `/tour-cabinet/register` | Регистрация |
| POST | `/tour-cabinet/logout` | Выход (требуется `auth`); после выхода редирект на `home` |

Гостевые маршруты: middleware `tour-cabinet.guest` (залогиненный тур-участник редиректится на дашборд).

Защита дашборда: `EnsureTourCabinetUser` — без сессии редирект на `tour-cabinet.login`, без флага — на `home` с flash `error`.

## Отправка форм

Используются существующие маршруты `forms.public.show` / `forms.public.submit`; при авторизованном пользователе ответы привязываются к `user_id` в логике `FormPublicController`.

## Портальная админка (ЛК туров)

Точка входа для редакторов: **GET** `/admin/tour-cabinet` (`admin.tour-cabinet.index`) — одна страница «ЛК туров» с тремя блоками на месте: города по направлениям (переключение направления через query `project_key` + якорь `#tour-cabinet-admin-cities`), формы этапа 1, вопросы этапа 2. Отдельного раздела «этап 3» в админке нет (данные — в ЛК участника). После POST-операций редирект обратно на эту страницу с якорем соответствующего блока.

- **GET** `/admin/tour-cabinet/forms` (`admin.tour-cabinet.forms.index`) — отдельная страница с тем же UI (ссылка «← ЛК туров» ведёт на хаб с `#tour-cabinet-admin-forms`); данные и **PUT** `admin.tour-cabinet.forms.contest-form-slugs.update` — как у блока форм на хабе.
- **GET** `/admin/tour-cabinet/direction-cities` (`admin.tour-cabinet.direction-cities.index`, query `project_key`) — отдельная страница с тем же UI; CRUD через **POST/PATCH/DELETE** `admin.tour-cabinet.direction-cities.*`, редиректы на хаб с якорем `#tour-cabinet-admin-cities`.
- **GET** `/admin/tour-cabinet/stage2-questions` — отдельная страница с тем же UI; CRUD через **POST/PATCH/DELETE** `admin.tour-cabinet.stage2-questions.*`, редиректы на хаб с якорем `#tour-cabinet-admin-stage2`.
