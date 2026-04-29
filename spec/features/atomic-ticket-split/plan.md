# План реализации — Atomic Ticket Split

Шаги по порядку (sequential, ≤5 файлов на шаг):

1. **Бэкенд: дефолты + сервис** — `config/tour_cabinet.php` (ключ `atomic_ticket_block`), `SettingsService::getTourCabinetAtomicTicketBlock()`. Файлы: 2.
2. **Бэкенд: пользовательский payload** — пробросить `atomicTicketBlock` в `TourCabinetController::dashboard`. Файлы: 1.
3. **Бэкенд: админ — payload + контроллер + маршруты** — `TourCabinetHubPageData::atomicTicketBlockPayload`, `TourCabinetHubController::index`, новый `Admin\TourCabinetAtomicTicketController`, маршруты в `routes/web.php`. Файлы: 4.
4. **Фронт ЛК: блок на дашборде** — `resources/js/Pages/TourCabinet/Dashboard.vue` (новая секция + хелпер `scrollAndHighlight`, реактивная подсветка целевых блоков). Файлы: 1.
5. **Фронт админки: панель + страница** — `TourCabinetAdminAtomicTicketPanel.vue`, `Admin/TourCabinet/AtomicTicket/Index.vue`, подключение в `Hub.vue`. Файлы: 3.
6. **Verify** — `route:list | rg atomic-ticket`, `npm run build`, `php artisan view:clear` через Docker. Файлов нет.

После каждого шага — обновить `progress.md` и проверить, что step verifiable.
