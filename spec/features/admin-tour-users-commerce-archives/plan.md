# План — admin-tour-users-commerce-archives

## Milestones

- **M1 — Фильтр по городу учитывает коммерческие архивы**: расширить `TourCabinetTourUsersController::applyCityFilterToQuery` (`whereHas('tourCabinetCommerceArchives', ...)`) и обновить fallback `whereRaw('1 = 0')` для трёх таблиц. Цель — Петров (Глазов, 2 коммерческих архива) находится на `/admin/tour-cabinet/tour-users?city_id=21`.
- **M2 — Список городов в селекте**: в `TourCabinetClientContestDataService::cityOptionsForExport()` добавить distinct `city_id` из `tour_cabinet_commerce_archives`. Цель — селект «Город» в Index.vue показывает все города, по которым есть архивная коммерческая заявка (даже если нет конкурсных).
- **M3 — Сводка клиента**: в `listContestSummary(User)` добавить часть «коммерческие туры: N архив.». Цель — в таблице админа видно, что у клиента есть коммерческие архивы.
- **M4 — Сервис обогащения CSV-строк**: новый `App\Services\Admin\TourCabinetCommerceArchiveExportRowsService::appendRowsForUser(array $row, User $user, ?int $cityFilterId): array` с фиксированными ключами `commerce_archives_count`, `commerce_archives_summary`, `commerce_arch_{1..10}_*`.
- **M5 — Интеграция CSV-обогащения + русские заголовки + порядок**: вызов сервиса в `TourCabinetTourUsersController::export()`, расширение `orderExportKeys()` priority-листа и `csvColumnTitleRu()` русских лейблов.
- **M6 — Verify + спек-синхронизация**: route:list (без изменений; контрольная проверка), tinker-smoke (Петров находится фильтром; CSV-строка содержит commerce-колонки), `npm run build` (sanity, хотя Vue не правим), синхронизировать `spec/features/tour-cabinet-archives/spec.md` (вынести «Экспорт архивных заявок» из Out-of-scope в «реализовано в `admin-tour-users-commerce-archives`»).

## UI Components (UI Kit Lookup)

Поиск по `resources/js/Components/`, `resources/js/composables/`, UI Kit `@rosatom-ggr/ui-kit` (`RButton, RInput, RCheckbox, RAvatar, RBadge, RCard, RModal, RTabs, RProgress, RSidebar`) — см. `resources/js/app.js`.

**Решение:** в этой фиче UI-правок нет. Селект «Город» в `resources/js/Pages/Admin/TourCabinet/TourUsers/Index.vue` уже принимает `exportCityOptions: Array` через `defineProps` и рендерит `<option v-for="c in exportCityOptions">`; кнопка экспорта (`<a :href="exportDownloadHref">Скачать CSV`) уже передаёт `city_id` в query-string. Расширение источника данных `cityOptionsForExport()` (M2) автоматически добавит новые города в UI без правок Vue.

Сводка клиента в таблице (`u.contest_summary`) — рендерится одним `<td>{{ u.contest_summary || '—' }}</td>` (cell в `Index.vue`). Расширение строки на бэкенде через `listContestSummary` (M3) автоматически отобразится. Если в будущем понадобится отдельный визуальный бейдж «коммерческие туры» — использовать inline-`<span>` (как для `ЛК туров`/`ВШГР` в колонке «Доступ»), без отдельного компонента.

Новые Vue-компоненты, которые потребуется создать в этой фиче: **нет**.

## Verification

Каждый шаг проверяется через Docker по «Command Execution Pattern» из `spec-continuation`:

- Артизан-команды: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan <cmd>` (route:list, tinker).
- npm/vite: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` (sanity, не обязательно — Vue не правим, но прогон не повредит для уверенности).
- ReadLints на изменённые PHP-файлы (`TourCabinetTourUsersController.php`, `TourCabinetClientContestDataService.php`, новый `TourCabinetCommerceArchiveExportRowsService.php`) после каждого этапа.
- Tinker-smoke (transaction-rollback): создать `User`, `TourCabinetCommerceArchive(user_id, city_id=21, tour_id, payload=...)`, проверить:
  1. `(new TourCabinetTourUsersController(...))->index($requestWithCityId21)` (или прямой запрос через builder) — пользователь в выдаче.
  2. `$contestData->cityOptionsForExport()` содержит `['id' => 21, 'name' => 'Глазов']`.
  3. `$exportService->appendRowsForUser([], $user, 21)['commerce_arch_1_city'] === 'Глазов'`.
- Запрещены: host-команды, `docker compose exec`, отсутствие `source docker/.env.*` — см. правило `spec-continuation`.
