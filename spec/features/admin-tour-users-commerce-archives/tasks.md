# Задачи — admin-tour-users-commerce-archives

## Task 1 — Расширение city-фильтра коммерческими архивами

- **Goal**: в `TourCabinetTourUsersController::applyCityFilterToQuery` добавить OR-ветку `whereHas('tourCabinetCommerceArchives', fn ($a) => $a->where('city_id', $cityIdInt))` под `Schema::hasTable('tour_cabinet_commerce_archives')`-гардом; обновить fallback `whereRaw('1 = 0')` так, чтобы он срабатывал только при отсутствии всех трёх таблиц (contest_city_submissions, contest_progress, commerce_archives).
- **Scope**: `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (метод `applyCityFilterToQuery`). 1 файл.
- **DoD**: Петров (с двумя `tour_cabinet_commerce_archives.city_id=21` и без конкурсных данных по Глазову) попадает в выдачу `/admin/tour-cabinet/tour-users?city_id=21`; пользователи с конкурсными выборами/сабмишенами по Глазову по-прежнему находятся; fallback при отсутствии всех таблиц возвращает пустой набор.
- **Verify**: Docker-pattern + tinker-smoke (transaction-rollback): создать пользователя без конкурсных данных, прикрепить `TourCabinetCommerceArchive(city_id=21)`, выполнить `User::query()->where(...)` с применённым `applyCityFilterToQuery($q, 21)` (через reflection, как в Task 3 предыдущей фичи), убедиться, что пользователь найден.

## Task 2 — Distinct city_id из коммерческих архивов в списке городов селекта

- **Goal**: в `TourCabinetClientContestDataService::cityOptionsForExport()` добавить `TourCabinetCommerceArchive::query()->distinct()->pluck('city_id')` к существующим `$ids` (под `Schema::hasTable('tour_cabinet_commerce_archives')`-гардом, с фильтрацией null/0 через существующее `->filter(fn ($id) => $id > 0)`).
- **Scope**: `app/Services/Admin/TourCabinetClientContestDataService.php` (метод `cityOptionsForExport`). 1 файл.
- **DoD**: после задачи селект «Город» в `/admin/tour-cabinet/tour-users` содержит города из коммерческих архивов (Глазов виден, даже если ни одна конкурсная заявка/сабмишен по этому городу не создан); существующие города из контеста по-прежнему присутствуют; порядок алфавитный (через `orderBy('name')`).
- **Verify**: Docker-pattern + tinker-smoke (после Task 1): `app(TourCabinetClientContestDataService::class)->cityOptionsForExport()` содержит запись с `id=21, name='Глазов'`.

## Task 3 — Пометка коммерческих архивов в `listContestSummary`

- **Goal**: в `TourCabinetClientContestDataService::listContestSummary(User $user)` после существующих parts добавить часть `'коммерческие туры: '.$count.' архив.'` если `$user->tourCabinetCommerceArchives->count() > 0`. Загружать связь через `loadMissing('tourCabinetCommerceArchives')` рядом с уже существующим `loadMissing(['tourCabinetContestProgress', 'tourCabinetContestCitySubmissions.city'])`.
- **Scope**: `app/Services/Admin/TourCabinetClientContestDataService.php` (метод `listContestSummary`); опционально расширить `with([...])` в `TourCabinetTourUsersController::index` для предзагрузки `tourCabinetCommerceArchives` (избежать N+1). До 2 файлов.
- **DoD**: в столбце «Конкурс / города» таблицы `/admin/tour-cabinet/tour-users` для клиента с архивами видна часть, например, `«коммерческие туры: 2 архив.»`; для клиентов без архивов сводка не изменилась; N+1 на 25 строках не появляется (проверка по `Telescope`/`DB::getQueryLog()` в tinker).
- **Verify**: Docker-pattern + tinker-smoke на трёх пользователях (без архива, с 1 архивом, с 2 архивами) — строки сводки соответствуют ожидаемым.

## Task 4 — Сервис `TourCabinetCommerceArchiveExportRowsService`

- **Goal**: реализовать `App\Services\Admin\TourCabinetCommerceArchiveExportRowsService::appendRowsForUser(array $row, User $user, ?int $cityFilterId): array`, который загружает архивы пользователя (`tourCabinetCommerceArchives()->with(['city:id,name', 'tour:id,title'])->orderByDesc('submitted_at')->orderByDesc('id')->get()`), при необходимости фильтрует по `$cityFilterId`, формирует ключи `commerce_archives_count`, `commerce_archives_summary` (формат: `"#1 Глазов · Тур по Удмуртии [05.05.2026]; #2 ..."`) и для первых 10 архивов — `commerce_arch_{N}_id`, `commerce_arch_{N}_city`, `commerce_arch_{N}_tour`, `commerce_arch_{N}_submitted_at`, `commerce_arch_{N}_status`, `commerce_arch_{N}_lms_responses` (JSON-encoded `payload.lms_form.responses`).
- **Scope**: `app/Services/Admin/TourCabinetCommerceArchiveExportRowsService.php` (новый файл). 1 файл.
- **DoD**: метод возвращает обогащённый `$row` со всеми перечисленными ключами; при отсутствии архивов — `commerce_archives_count = 0`, `commerce_archives_summary = ''`, никаких `commerce_arch_*` колонок не добавляется; при `$cityFilterId` отбрасывает «чужие» архивы.
- **Verify**: Docker-pattern + tinker-smoke (transaction-rollback): пользователь с 3 архивами (city_id=21, city_id=21, city_id=99) — `appendRowsForUser([], $u, 21)['commerce_archives_count'] === 2`; `appendRowsForUser([], $u, null)['commerce_archives_count'] === 3`; ключи `commerce_arch_1_city`, `commerce_arch_2_city` присутствуют.

## Task 5 — Интеграция CSV-обогащения в `export()` + порядок + русские заголовки

- **Goal**: в `TourCabinetTourUsersController::export()` инжектить новый сервис через метод-сигнатуру (или constructor; constructor предпочтительнее для консистентности с `contestData`), вызывать `$exportRowsService->appendRowsForUser($row, $u, $cityIdInt)` для каждой строки ДО вычисления `$allKeys/$orderedKeys`; в `orderExportKeys()` расширить priority-лист ключами commerce-блока сразу после `tour_applications` (`commerce_archives_count`, `commerce_archives_summary`, `commerce_arch_1_id`, … `commerce_arch_10_lms_responses`); в `csvColumnTitleRu()` добавить русские лейблы для всех новых ключей (фиксированные + два regex-шаблона `^commerce_arch_(\d+)_(id|city|tour|submitted_at|status|lms_responses)$`).
- **Scope**: `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (constructor + `export()` + `orderExportKeys()` + `csvColumnTitleRu()`). 1 файл.
- **DoD**: CSV содержит новые колонки в правильной русской локализации; для клиента без коммерческих архивов колонки `commerce_arch_*` отсутствуют (либо пусты); для клиента с архивами видны 2 строки сводки + до 60 широких колонок (6 полей × 10 архивов); ReadLints чисто.
- **Verify**: Docker-pattern + tinker-smoke (`(new \Mockery\Container)` или прямой вызов `$controller->export($requestWithCityId21)`) — собрать CSV в строку, проверить присутствие заголовков и строки Петрова с двумя `commerce_arch_*`-колонками.

## Task 6 — Verify-блок: route:list, npm build, tinker полного флоу

- **Goal**: финальная Docker-верификация: `php artisan route:list --name=tour-users` (контрольная проверка, что existing-роуты не сломаны и нет неожиданных дублей), `npm run build` (sanity, даже без Vue-правок), tinker-полный-флоу: создать пользователя «Петров» (без конкурсного прогресса) + 2 архива коммерческих туров для city_id=21 → выполнить index-выдачу с `city_id=21` → убедиться, что Петров в выдаче + сводка содержит «коммерческие туры: 2 архив.»; затем выполнить export-выдачу с `city_id=21` → убедиться, что CSV содержит строку Петрова с заполненными колонками `commerce_arch_1_*` и `commerce_arch_2_*`.
- **Scope**: только проверка, без правок кода (если что-то всплывёт — отдельный шаг). 0 файлов.
- **DoD**: все три проверки (`route:list`, `npm run build`, tinker) — зелёные; гэпов нет; `90-open-questions.md` не пополняется.
- **Verify**: Docker-pattern (все три команды).

## Task 7 — Синхронизация спека `tour-cabinet-archives`

- **Goal**: в `spec/features/tour-cabinet-archives/spec.md` (раздел Out-of-scope) вынести «Экспорт архивных заявок в CSV/Excel» из «потенциальной следующей фичи» в «реализовано в `admin-tour-users-commerce-archives`», добавить cross-ссылку; в `spec/features/tour-cabinet-archives/plan.md` / `progress.md` — добавить раздел «Последующие фичи» со ссылкой; убедиться, что admin-секция карточки клиента (Show.vue, Task 10) и новая фильтрация/экспорт (Index/Export) не конфликтуют.
- **Scope**: `spec/features/tour-cabinet-archives/spec.md`, `spec/features/tour-cabinet-archives/plan.md` (опционально), `spec/features/admin-tour-users-commerce-archives/progress.md` (фиксация финального статуса). До 3 файлов.
- **DoD**: спек-крафт согласован: `tour-cabinet-archives` явно ссылается на новую фичу; новая фича в progress.md помечена «реализовано»; нет противоречий между двумя спеками.
- **Verify**: ручная вычитка двух spec-файлов; `git diff` показывает только спек-правки, без кодовых.
