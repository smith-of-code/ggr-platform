# Прогресс: Сброс прогресса конкурса участника из `/admin/settings/`

## Завершённые задачи

### Task 1. Сервис `TourCabinetContestProgressResetter` ✓

- Files: `app/Services/Admin/TourCabinetContestProgressResetter.php` — новый класс с публичным методом `reset(User $user): void`. Сначала читаем `stage3_attachment_path` из прогресса, в `DB::transaction` удаляем по `user_id` записи `tour_cabinet_contest_city_submissions`, `tour_cabinet_contest_stage2_answers`, `tour_cabinet_contest_progress`. После commit удаляем файл вложения этапа 3 со storage в try-catch, ошибки логируются как `tour_cabinet_contest_progress_reset_storage_failed`. Финальный `Log::info('contest_progress_reset', [...])` фиксирует `user_id`, путь файла и счётчики удалённых строк. Идемпотентно (re-run без ошибок). LMS-`lms_form_submissions` не трогаем.
- Verify (Docker, tinker, transaction-rollback):
  - BEFORE: `{"p":1,"c":1,"s":1,"f":1,"lms":1}` (прогресс + city-submission + stage2-answer + файл этапа 3 + лежащая под ней `LmsFormSubmission`).
  - `app(TourCabinetContestProgressResetter::class)->reset($u)`.
  - AFTER: `{"p":0,"c":0,"s":0,"f":0,"lms":1}` — три таблицы пустые, файл удалён, `LmsFormSubmission` сохранена.
  - Повторный вызов `reset` отрабатывает без exception (`IDEMPOTENT_OK`).
- `ReadLints` — чисто.

### Task 2. Контроллер `Admin\ContestProgressResetController` ✓

- Files: `app/Http/Controllers/Admin/ContestProgressResetController.php` — `index(Request)` фильтрует `users` (`whereHas tourCabinetContestProgress` ИЛИ `tourCabinetContestCitySubmissions` ИЛИ `whereExists` на `tour_cabinet_contest_stage2_answers`), поиск по `q` (email, name, last_name, first_name, patronymic, id с `ctype_digit`); leftJoin на `tour_cabinet_contest_progress as tccp` для сортировки `tccp.updated_at DESC NULLS LAST`; `paginate(25)->withQueryString()->through(userRow)` → Inertia `Admin/Settings/ContestReset/Index`. `userRow` отдаёт `id, email, fio, direction_label (через Direction::allProjectMap), current_stage, stage2_submitted_at`. `reset(User, TourCabinetContestProgressResetter)` вызывает сервис и возвращает `back()->with('success', ...)`.
- Fix (после первого smoke): все колонки в `where` для поиска квалифицированы префиксом `users.` (иначе ambiguous "id" из-за leftJoin).
- Verify (Docker, tinker, через ReflectionClass на `Inertia\Response`):
  - `by_email`: `found=yes (rows=1)`.
  - `by_fio` (Сидоров): `found=yes (rows=1)`.
  - `by_id`: `found=yes (rows=1)`.
  - `by_empty` (без q): `rows=22` (текущие реальные участники в БД).
  - `reset`: `status=302`, `flash="Прогресс конкурса для {email} сброшен."`, `AFTER_RESET: progress_count=0`.
- `ReadLints` — чисто.

### Task 3. Маршруты `admin.settings.contest-reset.*` ✓

- Files: `routes/web.php` — добавлен импорт `ContestProgressResetController as AdminContestProgressResetController` и два маршрута внутри admin-группы (`prefix=admin`, middleware `['auth', 'portal.admin']`):
  - `GET /admin/settings/contest-reset` → `admin.settings.contest-reset.index`.
  - `POST /admin/settings/contest-reset/{user}` → `admin.settings.contest-reset.reset` (`whereNumber('user')`).
- Verify (Docker): `php artisan route:list --json | grep admin.settings.contest-reset` → 2 имени; смежные `admin.settings.{index,mail,page-visibility,forms-trash}` остались на местах.
- `ReadLints` по `routes/web.php` — чисто.

### Task 4. Страница `Admin/Settings/ContestReset/Index.vue` ✓

- Files: `resources/js/Pages/Admin/Settings/ContestReset/Index.vue` — `AdminLayout` + `Head`, обратная стрелка на `admin.settings.index`, заголовок и краткое описание; форма поиска (`useForm({ q })` → `router.get` на `index` с `preserveState/preserveScroll/replace: true`) + кнопка «Найти» + кнопка «Сбросить фильтр» (видна только при активном `filters.q`); таблица колонок `ID / Email / ФИО / Направление / Этап (badge indigo) / Этап 2 отправлен / Действия`; кнопка «Сбросить» в строке открывает `Modal` (`@/Components/Modal.vue`) с буллит-списком, что именно будет удалено (направление+города, анкеты этапа 1, ответы этапа 2, материалы этапа 3 с файлом), и предупреждением, что email-уведомление не отправляется. Confirm = `router.post` на `admin.settings.contest-reset.reset` с `preserveScroll`. Локальный `resettingUserId` блокирует кнопку, меняет текст на «Сбрасывается…» и не даёт закрыть модалку до завершения. Пустое состояние («Никто не найден по запросу» / «Никто из участников не начал конкурс») с пояснением. Пагинация по `users.links` — стандартный паттерн.
- Verify (Docker): `npm run build` — `built in 7.83s`, без ошибок; `ReadLints` — чисто.

### Task 5. Карточка в `Admin/Settings/Index.vue` ✓

- Files: `resources/js/Pages/Admin/Settings/Index.vue` — в массив `sections` добавлена 4-я карточка «Сброс прогресса конкурса» со ссылкой на `admin.settings.contest-reset.index`, иконка-«arrow-uturn-left» (heroicon `M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3`), `iconBg='bg-indigo-50 group-hover:bg-indigo-100'`, `iconColor='text-indigo-600'` (отличается от красной «trash» Корзины форм).
- Verify (Docker): `npm run build` без ошибок; `ReadLints` чисто.

### Task 6. Документация (`lk-participant-contests`, `settings`) ✓

- Files:
  - `spec/features/settings/spec.md` — раздел «Контроллеры» дополнен `PageVisibilityController`, `LmsFormTrashController`, `ContestProgressResetController` со ссылками на соответствующие фичи; раздел «Страницы» расширен на `FormsTrash.vue` и `ContestReset/Index.vue`.
  - `spec/features/lk-participant-contests/spec.md` — новый раздел «Сброс прогресса конкурса (админка)»: описание `/admin/settings/contest-reset`, какие таблицы чистятся, что `lms_form_submissions` сохраняются, что после сброса участник снова увидит выбор направления; ссылка на `spec/features/admin-settings-reset-contest-progress/spec.md`.
- Verify: `git diff spec/` показывает только добавления.

### Task 7. Финальная Verify (Docker) ✓

- Routes: `php artisan route:list --json | grep admin.settings.` — 12 имён, в т.ч. `admin.settings.contest-reset.{index,reset}`.
- End-to-end smoke (tinker, transaction-rollback):
  - Сервис: BEFORE/AFTER (Task 1) — все три таблицы пустые, файл удалён, LMS-сабмишен сохранён, идемпотентность ОК.
  - Контроллер `index`: поиск по email/ФИО/id находит созданного участника; без `q` возвращает 22 реальных участника из БД.
  - Контроллер `reset`: возвращает 302 redirect с flash `"Прогресс конкурса для {email} сброшен."`, после вызова `progress_count = 0`.
- Build: `npm run build` (Docker) — `built in 7.83s`, без ошибок.
- Lint (`ReadLints`): по `app/Services/Admin/TourCabinetContestProgressResetter.php`, `app/Http/Controllers/Admin/ContestProgressResetController.php`, `routes/web.php`, `resources/js/Pages/Admin/Settings/ContestReset/Index.vue`, `resources/js/Pages/Admin/Settings/Index.vue` — чисто.

### Task 8. Финализация `progress.md` ✓

- Files: `spec/features/admin-settings-reset-contest-progress/progress.md` (этот файл).

## Частично выполненные

_Пусто_

## Не начатые

_Пусто_ — фича реализована полностью.

## Open issues

_Пусто_

## Verify summary

- PHP-lint (`ReadLints`): чисто на всех затронутых `.php`.
- Vue-lint (`ReadLints`): чисто на затронутых `.vue`.
- Frontend build (Docker, `npm run build`): зелёный.
- Tinker smoke (Docker, transaction-rollback):
  - Сервис `TourCabinetContestProgressResetter::reset` стирает три таблицы прогресса, удаляет файл вложения этапа 3 со storage, не трогает `LmsFormSubmission`. Идемпотентно.
  - Контроллер `Admin\ContestProgressResetController::index` корректно ищет по email / ФИО / id и возвращает только участников с записями в одной из трёх таблиц прогресса.
  - Контроллер `Admin\ContestProgressResetController::reset` возвращает 302 redirect с flash и фактически удаляет прогресс участника.
- Маршруты (Docker, `php artisan route:list`): `admin.settings.contest-reset.{index,reset}` присутствуют.
- Затронутые файлы (всего 7):
  - `app/Services/Admin/TourCabinetContestProgressResetter.php` (new)
  - `app/Http/Controllers/Admin/ContestProgressResetController.php` (new)
  - `routes/web.php` (импорт + 2 маршрута)
  - `resources/js/Pages/Admin/Settings/ContestReset/Index.vue` (new)
  - `resources/js/Pages/Admin/Settings/Index.vue` (новая карточка в `sections`)
  - `spec/features/settings/spec.md` (расширены разделы «Контроллеры» и «Страницы»)
  - `spec/features/lk-participant-contests/spec.md` (новый раздел «Сброс прогресса конкурса (админка)»)
