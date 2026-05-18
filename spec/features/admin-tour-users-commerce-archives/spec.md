# Учёт коммерческих архивов в фильтре и экспорте админ-клиентов ЛК туров (admin-tour-users-commerce-archives)

## Goal

Расширить админ-список `/admin/tour-cabinet/tour-users` и CSV-экспорт так, чтобы фильтр по городу и выгрузка учитывали записи `tour_cabinet_commerce_archives` наравне с конкурсными источниками (`tour_cabinet_contest_city_submissions`, `tour_cabinet_contest_progress.selected_city_ids`). Архивные коммерческие заявки на тур должны находиться по фильтру «Город», подсвечиваться в сводке клиента и выгружаться в CSV отдельными колонками с пометкой тура.

## In-scope

### Фильтр «Город» в админ-списке клиентов

- В `App\Http\Controllers\Admin\TourCabinetTourUsersController::applyCityFilterToQuery` добавить третью OR-ветку (под `Schema::hasTable('tour_cabinet_commerce_archives')`-гардом): `orWhereHas('tourCabinetCommerceArchives', fn ($a) => $a->where('city_id', $cityIdInt))`. Связь `User::tourCabinetCommerceArchives()` уже существует (фича `tour-cabinet-archives`, M1).
- Условие fallback `whereRaw('1 = 0')` (когда нет ни одной из таблиц) обновить так, чтобы оно срабатывало только при отсутствии всех трёх таблиц.
- Хелпер `validatedCityId` не трогать — `City::query()->whereKey($cityIdInt)->exists()` уже корректно валидирует существование города.

### Список городов в селекте фильтра/экспорта

- В `App\Services\Admin\TourCabinetClientContestDataService::cityOptionsForExport()` добавить distinct `city_id` из `tour_cabinet_commerce_archives` (под `Schema::hasTable`-гардом) к уже собранным `ids` (контест-сабмишены этапа 1 + `selected_city_ids` прогресса). Пустые/нулевые `city_id` отфильтровать существующим `->filter(fn ($id) => $id > 0)`.
- Альтернатива «отдельный метод `commerceCityOptionsForExport()`» отвергнута — UI-селект один, фильтр один, контекст использования совпадает (выбор города для фильтрации и выгрузки).

### Сводка клиента в табличной строке

- В `TourCabinetClientContestDataService::listContestSummary(User $user)` после существующих parts добавить часть `«коммерческие туры: N архив.»` если у пользователя `count(tourCabinetCommerceArchives) > 0`. Загрузка — через `loadMissing('tourCabinetCommerceArchives')`, чтобы не размножать N+1.
- При фильтре по конкретному городу (когда `applyCityFilterToQuery` отработал) сводка дополнительно подсвечивает: `«коммерческие туры (Глазов): 2 архив.»` — но реализация этого «контекстного» суффикса вынесена в Out-of-scope (см. ниже), чтобы не плодить лишний параметр в сервисе. В MVP отображается только общий счётчик.

### Экспорт CSV — колонки коммерческих архивов

- Новый сервис `App\Services\Admin\TourCabinetCommerceArchiveExportRowsService` с методом `appendRowsForUser(array $row, User $user, ?int $cityFilterId): array`. Сервис:
  - Загружает архивы через `$user->tourCabinetCommerceArchives()->with(['city:id,name', 'tour:id,title'])->orderByDesc('submitted_at')->orderByDesc('id')->get()`.
  - При `$cityFilterId !== null` дополнительно фильтрует коллекцию через `->where('city_id', $cityFilterId)` (фильтр согласован с `buildExportRow` для контест-сабмишенов).
  - Возвращает обогащённый массив `$row` с ключами:
    - `commerce_archives_count` — int, общее число архивов (после city-фильтра, если он есть).
    - `commerce_archives_summary` — string, "#1 Глазов · Тур по Удмуртии [05.05.2026]; #2 Глазов · Зимний тур [10.05.2026]" (для CSV-readable сводки).
    - Для первых 10 архивов: `commerce_arch_{N}_id`, `commerce_arch_{N}_city`, `commerce_arch_{N}_tour`, `commerce_arch_{N}_submitted_at`, `commerce_arch_{N}_status`, `commerce_arch_{N}_lms_responses` (последний — JSON-encoded строка из payload `lms_form.responses` для аудита; нативный CSV-плоский dump в отдельные колонки отвергнут — кол-во полей у разных LMS-форм разное).
  - Лимит 10 архивов в широких колонках согласован с существующим лимитом для `app_N_*` (заявки на туры) в `buildExportRow`.
- Интеграция: в `TourCabinetTourUsersController::export()` после `$rows = $users->map(fn (User $u) => $this->contestData->buildExportRow($u, $cityIdInt));` прогнать каждую строку через `$exportRowsService->appendRowsForUser($row, $u, $cityIdInt)`. Альтернатива «инжектить сервис прямо в `buildExportRow`» отвергнута, чтобы не раздувать ответственность `TourCabinetClientContestDataService` (он отвечает за конкурс).
- В `orderExportKeys()` добавить ключи commerce-блока в priority-список после `tour_applications` (в порядке: `commerce_archives_count`, `commerce_archives_summary`, потом `commerce_arch_1_*` … `commerce_arch_10_*`).
- В `csvColumnTitleRu()` добавить русские заголовки для новых ключей:
  - `commerce_archives_count` → «Архивов коммерческих туров (количество)»
  - `commerce_archives_summary` → «Коммерческие туры — сводка»
  - `commerce_arch_N_id` → «Коммерческий тур №N — ID архива»
  - `commerce_arch_N_city` → «Коммерческий тур №N — Город»
  - `commerce_arch_N_tour` → «Коммерческий тур №N — Тур»
  - `commerce_arch_N_submitted_at` → «Коммерческий тур №N — Дата отправки»
  - `commerce_arch_N_status` → «Коммерческий тур №N — Статус»
  - `commerce_arch_N_lms_responses` → «Коммерческий тур №N — Ответы на анкету (JSON)»
- В filename экспорта префикс `tour-cabinet-clients` не меняется; добавлять `+commerce` в имя — не требуется (контекст и так понятен по содержимому).

## Out-of-scope

- Контекстный суффикс в сводке клиента «коммерческие туры (Глазов): 2 архив.» — пока показываем только общий счётчик, без передачи `cityFilterId` в `listContestSummary` (вынесено для простоты сервиса; при реальном запросе пользователя — отдельный шаг).
- Включение текущего, незавершённого прогресса коммерческих туров (`tour_cabinet_commerce_progress`) в фильтр/экспорт — это «в работе»-данные, в архив они попадают только после `archive-and-reset`. Источник правды для админа — архивные таблицы.
- Сортировка/фильтр админ-списка по дате архивации, статусу архива, конкретному туру — пока только city_id (как и сейчас для конкурса).
- Удаление/правка архивных коммерческих заявок из админ-карточки клиента — read-only, как зафиксировано в фиче `tour-cabinet-archives`.
- Пагинация CSV-экспорта — текущая выгрузка по-прежнему `$users->get()` по всей отфильтрованной выдаче (для текущих объёмов клиентов ЛК это не проблема; chunked export — потенциальная следующая фича).
- Excel/XLSX-формат экспорта — формат остаётся CSV `text/csv; charset=UTF-8` с разделителем `;`.
- Изменения админ-страницы карточки клиента `/admin/tour-cabinet/tour-users/{user}` — секции архивов уже добавлены фичей `tour-cabinet-archives` (Task 10). В этой фиче меняем только Index + Export.
- Расширение фронт-фильтра (Vue) — UI-селект «Город» (`Admin/TourCabinet/TourUsers/Index.vue`) уже умеет работать со списком из `exportCityOptions`; никаких правок Vue не требуется, поскольку добавление новых city_id в источник автоматически появится в `<option>`.
- Изменения в архивных страницах ЛК (`tour-cabinet.archives.commerce.{index,show}`) — фильтр/экспорт по городам касаются только админки.

## Constraints

- Все команды (artisan, route:list, npm build, pest, tinker) — через Docker по `spec-continuation`: `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
- Миграции в этой фиче НЕ требуются — все нужные таблицы и FK уже созданы фичей `tour-cabinet-archives` (`tour_cabinet_commerce_archives.city_id` nullable FK → `cities`).
- Реюз (правило `Reuse before create`):
  - Связь `User::tourCabinetCommerceArchives()` — уже есть (`tour-cabinet-archives` M1).
  - Метод `cityOptionsForExport` — расширяется, а не дублируется.
  - Метод `applyCityFilterToQuery` — расширяется новой OR-веткой, а не пересоздаётся.
  - UI-Kit — никаких новых компонентов. Селект и кнопка экспорта уже на месте (`Admin/TourCabinet/TourUsers/Index.vue`).
- sqlite-совместимость: `whereHas` по `tour_cabinet_commerce_archives.city_id` — обычный SQL JOIN-условный exists, sqlite-совместим.
- Лимит на размер одного шага — до 5 файлов и до ~150 строк diff (см. правило `Change scope limits`); деление на задачи в `tasks.md` соответствует.
- В CSV-колонках `commerce_arch_N_lms_responses` — JSON-encoded строка (`JSON_UNESCAPED_UNICODE`) для совместимости с CSV-парсерами (плоский dump в `f{id}`-колонки даст «рваную» схему при разных LMS-формах).
- При экспорте после обогащения commerce-колонками возможно расширение `$allKeys` — поэтому обогащение должно происходить ДО вычисления `$orderedKeys = $this->orderExportKeys(...)`. Альтернатива «считать ключи отдельно» отвергнута — текущий `array_keys($row)` уже корректно соберёт всё.

## Open questions

(пусто — все принципиальные решения зафиксированы выше: суффикс с подсветкой выбранного города в сводке — Out-of-scope; широкие колонки на 10 архивов согласованы с лимитом `app_N_*`; LMS-ответы коммерческих туров — JSON в одной колонке.)
