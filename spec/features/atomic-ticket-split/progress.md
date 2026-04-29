# Progress — Atomic Ticket Split

## Completed tasks

- Шаг 1 — Бэкенд: дефолты + сервис
  - `config/tour_cabinet.php` — добавлен ключ `atomic_ticket_block` с дефолтами (enabled=true, title, free/paid с тремя шагами).
  - `SettingsService::getTourCabinetAtomicTicketBlock()` + приватные нормализаторы; приоритет БД (JSON в `settings.atomic_ticket_block`) → дефолты config.
- Шаг 2 — Бэкенд: пользовательский payload
  - `TourCabinetController::dashboard` пробрасывает `atomicTicketBlock` (или `null`, когда `enabled=false`).
- Шаг 3 — Бэкенд: админ — payload + контроллер + маршруты
  - `TourCabinetHubPageData::atomicTicketBlockPayload()` + `Hub::index` отдаёт `atomicTicketSection`.
  - `Admin\TourCabinetAtomicTicketController` (`index` + `update`); сохранение JSON-строки в `settings.atomic_ticket_block`, redirect на хаб с фрагментом `tour-cabinet-admin-atomic-ticket`.
  - Маршруты: `GET /admin/tour-cabinet/atomic-ticket` и `PUT /admin/tour-cabinet/atomic-ticket`.

- Шаг 4 — Фронт ЛК: блок на дашборде
  - В `Dashboard.vue` добавлены проп `atomicTicketBlock`, секция `#tour-cabinet-atomic-ticket` (две карточки: free — голубая, paid — янтарная), хелпер `scrollAndHighlight(anchorId)` (плавный скролл + подсветка `ring-4 ring-rosatom-400` или `ring-amber-400` на ~2.2с).
  - Целевые секции `#tour-cabinet-contest-detail` и `#tour-cabinet-commerce-tours` получают подсветку через реактивный `highlightedAnchor`; таймер очищается в `onUnmounted`.
  - При `commerceTours.enabled === false` правая кнопка отображается как заглушка «… — скоро» без скролла.

- Шаг 5 — Фронт админки: панель + страница
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminAtomicTicketPanel.vue` — `useForm` (`enabled`, `title`, `free`, `paid`); UI с двумя колонками, добавление/удаление шагов (до 10).
  - `resources/js/Pages/Admin/TourCabinet/AtomicTicket/Index.vue` — отдельная страница, обёртка `AdminLayout` + панель.
  - `Hub.vue` — новая `<section>` с панелью, новый проп `atomicTicketSection`.

- Шаг 6 — Verify
  - `route:list | grep atomic-ticket` → `GET /admin/tour-cabinet/atomic-ticket` и `PUT /admin/tour-cabinet/atomic-ticket` зарегистрированы.
  - `npm run build` — сборка прошла без ошибок (Dashboard.vue, AtomicTicket/Index.vue, Hub.vue, TourCabinetAdminAtomicTicketPanel.vue в бандле).
  - `tinker` — `SettingsService::getTourCabinetAtomicTicketBlock()` отдаёт дефолты + корректно читает запись из БД (round-trip enabled=false, title=TEST).
  - `cache:clear` после теста.

## Partially completed

(пусто)

## Not started
- Шаг 3 — Бэкенд: админ — payload + контроллер + маршруты
- Шаг 4 — Фронт ЛК: блок на дашборде
- Шаг 5 — Фронт админки: панель + страница
- Шаг 6 — Verify

## Open issues

(пусто)
