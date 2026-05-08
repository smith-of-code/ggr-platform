# Задачи: Сброс прогресса конкурса участника из `/admin/settings/`

## Task 1. Сервис `TourCabinetContestProgressResetter`

- Goal: реализовать атомарный сброс конкурсного прогресса по одному пользователю.
- Scope: `app/Services/Admin/TourCabinetContestProgressResetter.php` (новый класс, метод `reset(User $user): void`).
- DoD: метод запоминает `stage3_attachment_path`, в `DB::transaction` удаляет `tour_cabinet_contest_city_submissions` / `tour_cabinet_contest_stage2_answers` / `tour_cabinet_contest_progress` для `user_id`; после commit удаляет файл со storage с try-catch и логом `tour_cabinet_contest_progress_reset_storage_failed`. Идемпотентность.
- Verify: tinker-smoke (Docker) — создать пользователя с записью прогресса + одной city-submission + ответом этапа 2; вызвать сервис; убедиться, что все три таблицы пустые для этого `user_id`.

## Task 2. Контроллер `Admin\ContestProgressResetController`

- Goal: HTTP-обвязка для `index` (Inertia) и `reset` (POST → редирект).
- Scope: `app/Http/Controllers/Admin/ContestProgressResetController.php`.
- DoD: `index(Request)` строит запрос по `users` с `whereHas('tourCabinetContestProgress')` ИЛИ `whereHas('tourCabinetContestCitySubmissions')` ИЛИ `whereExists` на `tour_cabinet_contest_stage2_answers`; фильтр по `q` (email/ФИО/id); `paginate(25)->withQueryString()` → `Inertia::render('Admin/Settings/ContestReset/Index', [...])`. `reset(User $user, TourCabinetContestProgressResetter)` вызывает сервис, возвращает `back()->with('success', "Прогресс конкурса для {$user->email} сброшен.")`.
- Verify: `ReadLints` чисто; `php artisan route:list` показывает оба роута.

## Task 3. Маршруты `admin.settings.contest-reset.*`

- Goal: зарегистрировать маршруты внутри admin-группы рядом с `admin.settings.forms-trash.*`.
- Scope: `routes/web.php` (импорт `ContestProgressResetController`, две строчки `Route::get`/`Route::post`).
- DoD: `admin.settings.contest-reset.index` (GET) и `admin.settings.contest-reset.reset` (POST, `whereNumber('user')`) присутствуют в `php artisan route:list`.
- Verify: `php artisan route:list --json | grep contest-reset` (Docker) — 2 имени.

## Task 4. Страница `Admin/Settings/ContestReset/Index.vue`

- Goal: UI поиска и сброса.
- Scope: `resources/js/Pages/Admin/Settings/ContestReset/Index.vue`.
- DoD: `AdminLayout` + `Head`, обратная стрелка на `admin.settings.index`, заголовок и краткое описание; форма поиска (`TextInput` + `PrimaryButton`, `useForm({ q })` → `router.get` на `index` с `preserveScroll/State`); таблица колонок ID/Email/ФИО/Направление/Этап/Дата отправки этапа 2/Действие; кнопка «Сбросить» открывает `Modal` со списком, что именно будет удалено (анкеты этапа 1, ответы этапа 2, материалы этапа 3, выбор направления и городов), confirm = `router.post` на `reset` с `preserveScroll`. Пустое состояние («Никто не начал конкурс»), пагинация. Локальный `resettingUserId` блокирует кнопку и меняет текст.
- Verify: `npm run build` (Docker) без ошибок; `ReadLints` чисто.

## Task 5. Карточка в `Admin/Settings/Index.vue`

- Goal: вход на новую страницу с хаба настроек.
- Scope: `resources/js/Pages/Admin/Settings/Index.vue`.
- DoD: в массив `sections` добавлен пункт «Сброс прогресса конкурса» со ссылкой на `admin.settings.contest-reset.index`, иконка-«arrow-uturn-left» (heroicon outline), `iconBg`/`iconColor` в фиолетовой/индиго-палитре (отличается от красного «trash» Корзины форм).
- Verify: `npm run build` (Docker) без ошибок; `ReadLints` чисто.

## Task 6. Документация

- Goal: связать новую фичу с существующими spec.
- Scope: `spec/features/lk-participant-contests/spec.md` (или `tour-cabinet/spec.md`) — короткий абзац-ссылка на `admin-settings-reset-contest-progress`; обновить общий список карточек настроек в `spec/features/settings/spec.md`.
- DoD: ссылки на новую фичу присутствуют в обоих местах; `git diff spec/` показывает только добавления.
- Verify: визуальная проверка диффа.

## Task 7. Финальная Verify (Docker)

- Goal: подтвердить, что все слои (БД, сервис, маршруты, фронт-сборка) работают вместе.
- Scope: tinker-смок сценарий end-to-end + сборка.
- DoD: `php artisan tinker --execute=...` (Docker) — создаёт user + прогресс + city-submission + stage2-answer + (опц.) фейковый файл `stage3_attachment_path` через `Storage::fake`; вызывает `TourCabinetContestProgressResetter::reset`; проверяет, что таблицы пустые и storage-файл удалён. `php artisan route:list` показывает оба новых имени. `npm run build` зелёный. `ReadLints` по затронутым PHP/Vue — чисто.
- Verify: см. Command Execution Pattern.

## Task 8. Финализация `progress.md`

- Goal: зафиксировать итог фичи.
- Scope: `spec/features/admin-settings-reset-contest-progress/progress.md`.
- DoD: все Task 1–7 в «Завершённых», «Не начатые» пуст, «Open issues» пуст (или с конкретикой). Verify summary с командами и ссылками на затронутые файлы.
- Verify: визуальная проверка.
