# Progress — admin-tour-users-commerce-archives

> Режим: sequential. В «Partially completed» одновременно должна быть не более одной задачи. Следующая берётся top-down из «Not started» после завершения текущей.

## Completed tasks

### Task 1 — Расширение city-фильтра коммерческими архивами

Files:
- `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (`applyCityFilterToQuery`: fallback `whereRaw('1 = 0')` теперь требует отсутствие всех 3 таблиц; добавлена третья OR-ветка `whereHas('tourCabinetCommerceArchives', fn ($a) => $a->where('city_id', $cityIdInt))` под `Schema::hasTable('tour_cabinet_commerce_archives')`-гардом)

Verify:
- ReadLints чисто.
- Tinker-smoke (transaction-rollback): пользователь без конкурсных данных, только с `TourCabinetCommerceArchive(city_id=4)` — найден фильтром (`found_count=1, user_in_result=YES`); фильтр по несуществующему городу — `found_count=0`.

### Task 2 — Distinct city_id из коммерческих архивов в списке городов селекта

Files:
- `app/Services/Admin/TourCabinetClientContestDataService.php` (импорт `TourCabinetCommerceArchive`; в `cityOptionsForExport()` под `Schema::hasTable('tour_cabinet_commerce_archives')`-гардом добавлен merge distinct `city_id` из архива с `whereNotNull('city_id')`)

Verify:
- ReadLints чисто.
- Tinker-smoke (transaction-rollback): для города «Сосновый Бор», который изначально отсутствовал в опциях (before=5, contains_target=NO), после создания одного `TourCabinetCommerceArchive(city_id=...)` метод возвращает обновлённый список (after=6, contains_target=YES, delta=1).

### Task 3 — Пометка коммерческих архивов в `listContestSummary`

Files:
- `app/Services/Admin/TourCabinetClientContestDataService.php` (`listContestSummary` теперь учитывает наличие таблицы `tour_cabinet_commerce_archives`: добавляет часть `'коммерческие туры: N архив.'`, eager-load `tourCabinetCommerceArchives` через `loadMissing`; ранний `return '—'` срабатывает только если нет ни одной из двух таблиц)
- `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (в `index()` добавлен `$query->with(['tourCabinetCommerceArchives'])` под Schema-гардом для устранения N+1 при пагинации 25)

Verify:
- ReadLints чисто.
- Tinker-smoke (transaction-rollback): три пользователя — `summary_u0='—'`, `summary_u1='коммерческие туры: 1 архив.'`, `summary_u2='коммерческие туры: 2 архив.'`.

### Task 4 — Сервис `TourCabinetCommerceArchiveExportRowsService`

Files:
- `app/Services/Admin/TourCabinetCommerceArchiveExportRowsService.php` (новый сервис: `appendRowsForUser(array $row, User $user, ?int $cityFilterId): array`; eager-load `['city:id,name', 'tour:id,title']`; `orderByDesc('submitted_at')->orderByDesc('id')`; постфильтр по `city_id` через коллекцию; ключи `commerce_archives_count`, `commerce_archives_summary` + до 10 широких колонок `commerce_arch_{N}_{id,city,tour,submitted_at,status,lms_responses}`; fallback при отсутствии таблицы — нулевой count + пустая summary)

Verify:
- ReadLints чисто.
- Tinker-smoke (transaction-rollback) с 3 архивами (city A, A, B): без фильтра `count=3`, summary содержит 3 записи, есть `commerce_arch_3_*`, нет `commerce_arch_4_*`; с фильтром по A — `count=2`, есть `commerce_arch_2_*`, нет `commerce_arch_3_*`; для пустого юзера `count=0`, summary пуст, широких колонок нет.

### Task 5 — Интеграция CSV-обогащения в `export()` + порядок + русские заголовки

Files:
- `app/Http/Controllers/Admin/TourCabinetTourUsersController.php` (импорт `TourCabinetCommerceArchiveExportRowsService`; добавлен в конструктор; в `export()` каждая строка обогащается через `appendRowsForUser($row, $u, $cityIdInt)` ДО вычисления `$allKeys/$orderedKeys`; `orderExportKeys()` priority-лист расширен `commerce_archives_count`, `commerce_archives_summary` + 60 `commerce_arch_{1..10}_{id,city,tour,submitted_at,status,lms_responses}`; `csvColumnTitleRu()` получил 2 фиксированных лейбла + regex для `commerce_arch_(\d+)_*` → «Коммерческий тур №N — ...»)

Verify:
- ReadLints чисто.
- Tinker-smoke (transaction-rollback): полный `export($requestWithCityId)` → CSV содержит заголовки «Архивов коммерческих туров (количество)», «Коммерческий тур №1 — Город» (а 10-й — отсутствует ожидаемо, поскольку у тестового Петрова 2 архива); строка Петрова найдена и включает имя города, «Тур №1» и «Тур №2».

### Task 6 — Verify-блок: route:list, npm build, tinker полного флоу

Files: (только проверки, без правок кода)

Verify:
- `php artisan route:list --name=tour-users` (Docker) — 6 маршрутов `admin.tour-cabinet.tour-users.*` на месте, без неожиданных дублей.
- `npm run build` (Docker) — `built in 9.02s`, без ошибок.
- Tinker-полный-флоу (transaction-rollback): пользователь «Петров» без конкурсных данных, 2 архива `TourCabinetCommerceArchive` на один city_id:
  - `[index_filter] petrov_found=YES total=1` — фильтр через `filteredTourUsersQuery` с `city_id` находит Петрова (решает исходный баг репорта про Глазов).
  - `[summary] коммерческие туры: 2 архив.` — сводка корректна.
  - `[export]` header содержит «Архивов коммерческих туров» и «Коммерческий тур №1 — Тур»; строка Петрова найдена, содержит «Коммерческий тур №1», «Коммерческий тур №2» и имя города.

### Task 7 — Синхронизация спека `tour-cabinet-archives`

Files:
- `spec/features/tour-cabinet-archives/spec.md` (раздел Out-of-scope: пункт «Экспорт архивных заявок в CSV/Excel» зачёркнут и помечен «Реализовано в фиче `admin-tour-users-commerce-archives`»; добавлено уточнение, что экспорт конкурсного payload в эту фичу не вошёл и при необходимости — отдельная фича)
- `spec/features/admin-tour-users-commerce-archives/progress.md` (этот файл — финальная фиксация)

Verify:
- Ручная вычитка `spec/features/tour-cabinet-archives/spec.md` — пункт Out-of-scope обновлён, нет противоречий между двумя спеками.

## Partially completed

(пусто)

## Not started

(пусто — все 7 задач завершены)

## Open issues

(пусто)
