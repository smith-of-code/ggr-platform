# План — tour-cabinet-archives

**Status:** все 7 milestone'ов завершены (см. `progress.md` / `tasks.md`). Финальный набор файлов и проверок зафиксирован в `progress.md`.

## Milestones

- **M1 — Хранилище**: миграции на две новые таблицы (`tour_cabinet_contest_archives`, `tour_cabinet_commerce_archives`) и добавление колонки `archived_at` в `tour_cabinet_contest_progress`; модели `TourCabinetContestArchive`, `TourCabinetCommerceArchive` (fillable, casts, связи с `User`/`City`/`Tour`/`LmsFormSubmission`).
- **M2 — Сервисы архивации**: `TourCabinetContestArchiveService::archiveProgress` (реюз `TourCabinetClientContestDataService::contestPayloadForUser`) + `TourCabinetCommerceArchiveService::archiveAndResetProgress` (сбор ответов LMS-формы + сброс прогресса). Триггеры — в `TourCabinetContestController::storeStage3` и `TourCabinetCommerceToursFormLinker::tryLinkAfterSubmission`.
- **M3 — Блокировка блока «Конкурс» в ЛК**: middleware/guard (`EnsureTourCabinetContestNotArchived`) для mutating-роутов конкурса; в `Dashboard.vue` — отдельный режим отображения блока при `contestArchived === true` (плашка с информсообщением, кнопка «Перейти в архив», панели Этап 1–3 затемнены через `pointer-events-none opacity-50 grayscale`).
- **M4 — Архивные страницы участника**: маршруты `tour-cabinet.archives.contest.{index,show}` и `tour-cabinet.archives.commerce.{index,show}`, контроллеры `ContestArchiveController` + `CommerceArchiveController` под middleware `tour-cabinet`, Inertia-страницы (`Archives/Contest/Index.vue`, `Show.vue`, `Archives/Commerce/Index.vue`, `Show.vue`). Сортировка `submitted_at DESC`, гард `user_id`.
- **M5 — Навигация и UX-флоу коммерческих туров**: ссылки «Архив конкурсы» / «Архив коммерческих туров» в `TourCabinetQuickNav.vue`; flash-флаг `tour_cabinet_commerce_just_archived` + автоскролл к блоку `#tour-cabinet-commerce-tours` на дашборде с временной подсветкой и сообщением «Новая заявка может быть создана прямо сейчас.».
- **M6 — Расширение админ-карточки клиента**: новые секции «Архив конкурсы» и «Архив коммерческих туров» в `Admin/TourCabinet/TourUsers/Show.vue`, серверный payload из `TourCabinetTourUsersController::show` (реюз — на сервисах из M2/M4).
- **M7 — Документация и smoke-проверки**: обновить `spec/features/tour-cabinet/spec.md` (упоминание архивов и якорей), `spec/features/admin-settings-reset-contest-progress/spec.md` (явное упоминание: архивы не трогаем), при необходимости — `spec/03-data-model.md` (две новые таблицы); `php artisan route:list | grep archives`, `npm run build`, `pest` (опционально, при наличии sqlite-фикстур).

## UI Components (UI Kit Lookup)

Поиск по `resources/js/Components/`, `resources/js/composables/`, UI Kit `@rosatom-ggr/ui-kit` (`RButton, RInput, RCheckbox, RAvatar, RBadge, RCard, RModal, RTabs, RProgress, RSidebar`) — см. `resources/js/app.js`. Решения:

- **Карточки архивных заявок (list)** — `RCard` (тот же стиль, что в `TourCabinet/Dashboard.vue`, `Admin/TourCabinet/TourUsers/Show.vue`); НЕ создавать новый компонент `ArchiveListItem.vue`.
- **Badge «Отправлено»** — `RBadge` (UI Kit) с зелёным tone'ом; запасной вариант — inline `<span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">` (как в текущем `Dashboard.vue` для статуса заявок туров). Решение: использовать `RBadge` для единообразия, fallback на inline-span — только если проп tone недоступен в текущей версии UI Kit.
- **Информационная плашка «Вы уже оформили заявку…»** — реюз паттерна из `Dashboard.vue` для `profileGate` (контрастная плашка-уведомление через `RCard` с tone, либо inline `<div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">` — текущий стиль flash-сообщений ЛК). Финальный выбор tone — `amber` (предупреждение, не ошибка) либо `info`. НЕ создавать `ContestArchivedNotice.vue` — inline-блок в `Dashboard.vue`.
- **Кнопка «Перейти в архив»** — `RButton` (`variant="outline"` или `"primary"`); Inertia `<Link>` для перехода (`route('tour-cabinet.archives.contest.index')`).
- **Иконки** — Heroicons (`@heroicons/vue/24/outline`): `LockClosedIcon` (блокированный конкурс), `ArchiveBoxIcon` (нав-ссылки), `ClockIcon` (дата), `CheckCircleIcon` (статус «Отправлено»). Все уже используются в проекте — см. `Dashboard.vue` / `TourCabinetQuickNav.vue`.
- **Модальное окно деталей заявки** — отвергнуто в пользу отдельных страниц `Show.vue` (см. spec): легче переиспользовать render-шаблон админ-карточки и поддерживать deep-link. Если в финальной реализации модалка окажется удобнее — использовать `RModal` из UI Kit, НЕ создавать кастомный модал.
- **Список деталей конкурсной заявки (`Show.vue`)** — реюз template-блоков из `Admin/TourCabinet/TourUsers/Show.vue` (Этап 1 / Этап 2 / Этап 3), вынесенных в отдельные дочерние компоненты `Archives/Contest/Stage1Block.vue`, `Stage2Block.vue`, `Stage3Block.vue` ТОЛЬКО при необходимости (если шаблон ровно копируется в админку + ЛК — вынести). Решение принимается на M4 после реальной реализации, по умолчанию — inline в `Show.vue`.
- **Композаблы** — `useToast` для подсветки/тостов в коммерческом флоу (если потребуется in-flow notification); скролл к блоку — нативный `nextTick` + `scrollIntoView`, без отдельного composable.
- **Подсветка блока после редиректа** — inline `setTimeout` + reactive `boolean` в `Dashboard.vue` (`ring-2 ring-emerald-200` на 1.5–2 сек), без отдельного composable `useHighlight`.

Новые Vue-компоненты, которые потребуется создать в этой фиче:

- `resources/js/Pages/TourCabinet/Archives/Contest/Index.vue` — список архивных конкурсных заявок.
- `resources/js/Pages/TourCabinet/Archives/Contest/Show.vue` — детальный просмотр конкурсной заявки.
- `resources/js/Pages/TourCabinet/Archives/Commerce/Index.vue` — список архивных коммерческих заявок.
- `resources/js/Pages/TourCabinet/Archives/Commerce/Show.vue` — детальный просмотр коммерческой заявки.

Прочие .vue-файлы (`Dashboard.vue`, `TourCabinetQuickNav.vue`, `Admin/TourCabinet/TourUsers/Show.vue`) — редактируются, не создаются.

## Verification

Каждый шаг проверяется через Docker-команды по правилу «Command Execution Pattern» из `spec-continuation`:

- Артизан-команды: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan <cmd>` (миграции, route:list, tinker).
- npm/vite: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`.
- pest: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan test --filter <...>` (опционально, при наличии sqlite-фикстур; блокер `90-open-questions.md` п.9 — Feature-тесты с `RefreshDatabase` могут падать на sqlite).
- ReadLints на затронутые PHP- и Vue-файлы после каждого этапа.
- Запрещены: host-команды, `docker compose exec`, отсутствие `source docker/.env.*` — см. правило `spec-continuation`.
