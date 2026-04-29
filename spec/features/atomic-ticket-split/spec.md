# Atomic Ticket Split — блок «Твой билет в атомный город» в ЛК Туры

## Goal

Добавить редактируемый из админки блок «Твой билет в атомный город» (далее — «Разделение») на дашборд `/tour-cabinet`, между блоком «Стандартная анкета» и блоком «Конкурс». Блок повторяет визуально две карточки одноимённого блока со страниц направлений (`/directions/{slug}`) — путь «Победить в конкурсе» и путь «За свой счёт» — но кнопки ведут не в LMS-форму и не на страницу логина, а на ближайшие блоки на этом же дашборде:

- Кнопка левой карточки (по умолчанию «Участвовать в конкурсе») — плавный скролл + подсветка блока `#tour-cabinet-contest-detail`.
- Кнопка правой карточки (по умолчанию «Оставить заявку») — плавный скролл + подсветка блока `#tour-cabinet-commerce-tours`.

Все тексты блока (заголовок, заголовки карточек, шаги, тексты кнопок) должны редактироваться из портальной админки `/admin/tour-cabinet`.

## In-scope

- Новая секция `#tour-cabinet-atomic-ticket` в `resources/js/Pages/TourCabinet/Dashboard.vue` — сразу после `#tour-cabinet-standard-form` и до `#tour-cabinet-contest`.
- Содержимое блока — копия двух карточек блока «Твой билет в атомный город» из `resources/js/Pages/Directions/Show.vue` (без вложенного «Конкурсного испытания»). Левая карточка (синяя, «Победить в конкурсе»), правая карточка (янтарная, «За свой счёт»).
- Кнопки внутри карточек — обычные `<button type="button">`, по клику плавный `scrollIntoView({behavior: 'smooth'})` к целевому якорю + кратковременная подсветка целевой секции (CSS-кольцо `ring-rosatom-400` + `transition`, ~2 секунды). Якорь URL не подменяем (чтобы не переоткрывать `#tour-cabinet-profile`).
- Целевой якорь левой кнопки — `#tour-cabinet-contest-detail` (карточка конкурса, не вся секция); правой кнопки — `#tour-cabinet-commerce-tours`.
- Кнопка правой карточки видна и активна только когда блок «Коммерческие туры» включён (`commerceTours.enabled === true`); если выключен — кнопка отключена с подсказкой «скоро будет доступно». Кнопка левой карточки видна всегда (блок «Конкурс» в ЛК всегда есть).
- Тексты блока хранятся как одна JSON-строка в таблице `settings` (группа `tour_cabinet`, ключ `atomic_ticket_block`) — единым полем, чтобы не плодить десяток ключей. Ключ читается через `SettingsService::getTourCabinetAtomicTicketBlock(): array` с фолбэком на дефолты из `config/tour_cabinet.php`.
- Структура контента (`config/tour_cabinet.php`, ключ `atomic_ticket_block`):
  - `enabled` (bool, дефолт `true`) — общий выключатель блока на дашборде.
  - `title` (string, по умолчанию «Твой билет в атомный город»).
  - `free` (object): `title`, `cta_label`, `target_anchor` (зашит в код = `tour-cabinet-contest-detail`, в админке не правится), `steps` (array из `{title, description}`).
  - `paid` (object): аналогично, `target_anchor` = `tour-cabinet-commerce-tours`.
- Бэкенд:
  - Метод `SettingsService::getTourCabinetAtomicTicketBlock(): array` — приоритет БД (JSON в `settings.atomic_ticket_block`) → дефолты `config('tour_cabinet.atomic_ticket_block')`.
  - `TourCabinetController::dashboard` отдаёт Inertia-проп `atomicTicketBlock` (`{ enabled, title, free: {title, cta_label, steps:[...]}, paid: {title, cta_label, steps:[...]} }` или `null` при `enabled=false`).
- Админка:
  - Новый контроллер `App\Http\Controllers\Admin\TourCabinetAtomicTicketController` с методами `index` (отдельная страница) и `update` (`PUT admin.tour-cabinet.atomic-ticket.update`).
  - Страница `Admin/TourCabinet/AtomicTicket/Index.vue` (по аналогии с `CommerceTours/Index.vue`).
  - На хабе `/admin/tour-cabinet` (`Hub.vue`) — новая `<section id="tour-cabinet-admin-atomic-ticket">` с `TourCabinetAdminAtomicTicketPanel.vue`.
  - В `TourCabinetHubPageData` — метод `atomicTicketBlockPayload(): array` (текущие значения + дефолты).
  - Валидация в `update`: `enabled` nullable boolean, `title` nullable string max 255, `free.title` nullable string max 255, `free.cta_label` nullable string max 100, `free.steps` array max 10, `free.steps.*.title` nullable string max 255, `free.steps.*.description` nullable string max 1000; для `paid` — то же.
  - Сохранение через `Setting::setGroup('tour_cabinet', ['atomic_ticket_block' => json_encode(...)])`. После сохранения — redirect на хаб с фрагментом `tour-cabinet-admin-atomic-ticket`.

## Out-of-scope

- Кастомизация цветов/иконок карточек из админки — в этой итерации захардкожено в шаблоне (синяя левая, янтарная правая, иконки `ShieldCheckIcon` и `BanknotesIcon` из Heroicons как в `Directions/Show.vue`).
- Загрузка изображений в блок — текстовый блок без картинок.
- Аналитика кликов / метрики.
- Перенос «развёрнутых ответов» и «проверочного задания» из `Directions/Show.vue` (заказчик попросил «только две карточки»).
- Отдельная страница в ЛК — блок только на дашборде.
- Многоязычность — только русский.

## Constraints

- Все команды (миграции, route:list, npm build, pest) — через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`) согласно `spec-continuation`.
- Реюз: `RCard`, `RButton` уже зарегистрированы глобально (см. `app.js`); шаблон карточек повторяет `Directions/Show.vue`. Никаких новых composable / shared-компонентов не вводим.
- Хранение контента — единым ключом-JSON в `settings`, чтобы не разрастаться отдельными ключами по каждому полю (как `contest_completion_notification`, но через JSON-строку).
- Миграция новой таблицы под этот блок не нужна — используем существующую `settings`.
- Подсветка целевого блока: добавление/удаление CSS-класса (`ring-2 ring-rosatom-400 ring-offset-2 transition`) на 2 секунды через `setTimeout`. Никаких глобальных стилей — внутри `Dashboard.vue` ref + reactive ref-class.
- `target_anchor` — захардкожен в коде. Админ не должен иметь возможность сменить целевой блок (логически некорректно).
- Рут страницы блока в Hub-навигации не нужен — есть якорь.

## Реализация

### Бэкенд

- `config/tour_cabinet.php`: новый ключ `atomic_ticket_block` с дефолтным `enabled=true`, `title="Твой билет в атомный город"`, `free` и `paid` с тремя шагами (копия из `PortalSeeder::seedDirections`/прототипа). Кнопки: «Участвовать в конкурсе» / «Оставить заявку».
- `SettingsService::getTourCabinetAtomicTicketBlock(): array` — приоритет БД (`settings.tour_cabinet.atomic_ticket_block`, JSON-строка) → дефолты config; пустой `enabled` парсится через `FILTER_VALIDATE_BOOLEAN`.
- `TourCabinetController::dashboard` принимает `atomicTicketBlock` из `SettingsService` и пробрасывает его в Inertia как проп `atomicTicketBlock`.
- `Admin\TourCabinetAtomicTicketController`:
  - `index()` → `Inertia::render('Admin/TourCabinet/AtomicTicket/Index', $hubPageData->atomicTicketBlockPayload())`.
  - `update(Request)` → валидация (см. выше), `Setting::setGroup('tour_cabinet', ['atomic_ticket_block' => json_encode($payload, JSON_UNESCAPED_UNICODE)])`, redirect на хаб с фрагментом.
- Маршруты в группе `admin.tour-cabinet.*`:
  - `GET /admin/tour-cabinet/atomic-ticket` (`admin.tour-cabinet.atomic-ticket.index`)
  - `PUT /admin/tour-cabinet/atomic-ticket` (`admin.tour-cabinet.atomic-ticket.update`)
- `TourCabinetHubPageData::atomicTicketBlockPayload()` — текущие значения из `SettingsService::getTourCabinetAtomicTicketBlock()` + флаг `enabled`.
- `TourCabinetHubController::index` пробрасывает `atomicTicketSection` в `Admin/TourCabinet/Hub`.

### Фронтенд (ЛК)

- В `resources/js/Pages/TourCabinet/Dashboard.vue` — новая `<section id="tour-cabinet-atomic-ticket">` сразу после блока стандартной анкеты, до блока конкурса.
- Шаблон секции: заголовок `atomicTicketBlock.title`, два `<div>` (левая «Победить в конкурсе» — синяя, правая «За свой счёт» — янтарная) с динамическими шагами; кнопки `<button>` с обработчиком `scrollAndHighlight('#tour-cabinet-contest-detail' | '#tour-cabinet-commerce-tours')`.
- `scrollAndHighlight(targetId)`: находит элемент по id, `scrollIntoView({behavior:'smooth', block:'start'})`, добавляет элемент в `highlightedAnchor` ref, `setTimeout(() => clear, 2000)`. Класс `:class="highlightedAnchor === id ? 'ring-2 ring-rosatom-400 ring-offset-2 transition' : ''"` навешивается на конкретные секции (`#tour-cabinet-contest-detail`, `#tour-cabinet-commerce-tours`).
- Если `atomicTicketBlock === null` (пришло `null` с бэка при `enabled=false`) — секция не рендерится.
- Если `commerceTours.enabled === false` — правая кнопка `disabled` с подписью «скоро будет доступно» (визуально серая, без скролла).

### Админка

- `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminAtomicTicketPanel.vue` — `useForm` с полями `enabled`, `title`, `free.title`, `free.cta_label`, `free.steps`, `paid.title`, `paid.cta_label`, `paid.steps`. UI:
  - Чекбокс `enabled`.
  - `<input>` для `title`.
  - Две колонки (free / paid): `title`, `cta_label`, динамический список `steps` (добавление/удаление, drag-drop не нужен).
  - Кнопка «Сохранить» → `useForm.put(route('admin.tour-cabinet.atomic-ticket.update'))`.
- На `Admin/TourCabinet/Hub.vue` — `<section id="tour-cabinet-admin-atomic-ticket">` с этой панелью; пропс `atomicTicketSection` от хаба.
- Отдельная страница `Admin/TourCabinet/AtomicTicket/Index.vue` (по аналогии с `CommerceTours/Index.vue`) — оборачивает ту же панель в `AdminLayout` с заголовком и ссылкой «← ЛК туров».

## Open questions

(пусто — спецификация согласована с заказчиком на этом шаге)
