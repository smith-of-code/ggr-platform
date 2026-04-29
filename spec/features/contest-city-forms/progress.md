# Прогресс: contest-city-forms

## Completed tasks

### Task 1. Миграция + модель ✓

- Files:
  - `database/migrations/2026_04_29_190000_add_lms_form_slug_to_tour_cabinet_direction_cities.php`
  - `app/Models/TourCabinetDirectionCity.php` (`$fillable` пополнен `lms_form_slug`)
- Verify (Docker):
  - `php artisan migrate` — `2026_04_29_190000_… DONE 5.33ms`.
  - php-однострочник: `Schema::hasColumn('tour_cabinet_direction_cities', 'lms_form_slug') = true`.
  - php-однострочник: `Eloquent::save` пишет/читает `lms_form_slug` (`'test-slug-tmp'` → `null`).

### Task 2. Резолвер формы Этапа 1 + использование в FormLinker ✓

- Files:
  - `app/Services/TourCabinetContestStage1FormResolver.php` (новый сервис: `resolveForRow(TourCabinetDirectionCity)`, `resolveBatchForDirection(int, list<int>)`)
  - `app/Services/TourCabinetContestFormLinker.php` — `expectedSlug` берётся из `resolver->resolveForRow($row)`; локальная логика `needs_more_data ? more_data : standard` удалена.
- Verify (Docker):
  - php-однострочник для трёх кейсов:
    1. `lms_form_slug = "city-specific-form"` → возвращает `"city-specific-form"`.
    2. `lms_form_slug = null`, `needs_more_data = false` → глобальный standard slug (`zayavka-na-tur`).
    3. `lms_form_slug = ""`, `needs_more_data = true` → глобальный more_data slug (`testovaya-forma-111`).
  - `ReadLints` по обоим файлам — чисто.

### Task 3. Админ-бэкенд (валидация + payload) ✓

- Files:
  - `app/Http/Controllers/Admin/TourCabinetDirectionCitiesController.php` — `store`/`update` принимают `lms_form_slug` (правило `nullable|string|max:191` + проверка активной `LmsForm` через хелпер `normalizeAndValidateFormSlug`); при смене формы у города с сабмитами — flash `success` с префиксом «Внимание:» и счётчиком.
  - `app/Services/Admin/TourCabinetHubPageData.php` — `directionCitiesPayload` теперь возвращает массив явных полей (`id`, `direction_id`, `city_id`, `city`, `needs_more_data`, `lms_form_slug`, `position`, `submissions_count`) + `allFormsOptions` (любая `LmsForm` платформы).
- Verify (Docker):
  - php-однострочник: `directionCitiesPayload` содержит ключи `directions, directionId, rows, cityOptions, allFormsOptions`; первая строка содержит `submissions_count` и `lms_form_slug`.
  - `php artisan route:list --path=admin/tour-cabinet/direction-cities` — 4 роута на месте (GET/POST/PATCH/DELETE).
  - `ReadLints` по обоим файлам — чисто.

### Task 4. Админ-фронт (Vue) ✓

- Files:
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminDirectionCitiesPanel.vue` — новая колонка «Форма Этапа 1» (нативный `<select>` с опцией «— Без формы —»); в форме добавления — отдельный селект и подсказка про автозавершение; при наличии `submissions_count > 0` — `window.confirm` перед сменой формы; пометка «N сабмит(ов) от участников» в строке.
  - `resources/js/Pages/Admin/TourCabinet/DirectionCities/Index.vue` — проброс пропа `:all-forms-options="allFormsOptions"`.
  - `Hub.vue` — без правок (`v-bind="directionCitiesSection"` подхватывает новый ключ payload автоматически).
- Verify (Docker):
  - `npm run build` — `built in 5.21s`, без ошибок.
  - `ReadLints` по двум `.vue` — чисто.

### Task 5. Пользовательский бэкенд ✓

- Files:
  - `app/Http/Controllers/TourCabinetContestController.php` — `__construct` принимает `TourCabinetContestStage1FormResolver`; `startCityForm` резолвит slug через сервис (`null` → flash `info` «Для этого города анкета не требуется» и редирект на дашборд); `stage1Complete` использует `resolveBatchForDirection` (города без формы пропускаются как уже выполненные).
  - `app/Services/TourCabinetContestDashboardData.php` — `__construct` принимает резолвер; `cities[*]` дополнен `has_form`; `selectedCitiesForForms[*]` дополнен `auto_completed` (`form_slug === null && ! submitted`); приватный `stage1Complete` зеркалит логику контроллера.
- Verify (Docker):
  - php-однострочник:
    1. `resolveForRow` со старыми globals + `lms_form_slug=null` → глобальный slug (`testovaya-forma-111`).
    2. После `lms_form_slug='city-specific'` → возвращает `city-specific`.
    3. После очистки global + `lms_form_slug=null` → `NULL`.
    4. `resolveBatchForDirection` повторяет (3) для batch-вызова.
  - `ReadLints` по двум файлам — чисто.

### Task 6. Пользовательский фронт ✓

- Files:
  - `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue` — для каждого `selectedCitiesForForms[*]`: бейдж «Заполнено» при `auto_completed`; «Отправлено» при `submitted`; кнопка «Заполнить анкету» иначе. Кнопка «Убрать город» доступна только при `!submitted && !auto_completed`. Условие появления «Перейти к этапу 2» упрощено до `stage1Complete && maxContestStages >= 2`. Заглушка про два глобальных slug заменена на условную плашку (показывается, если у конкретного города ни своей формы, ни глобального fallback) через computed `hasUnconfiguredCity`.
- Verify (Docker):
  - `npm run build` — `built in 6.07s`, без ошибок.
  - `ReadLints` — чисто.

### Task 7. Финальная верификация ✓

- `php artisan migrate:status` (Docker) — миграция `2026_04_29_190000_…` отмечена как `Ran [48]`.
- `php artisan route:list --path=tour-cabinet/contest` — 14 роутов на месте, без регрессии.
- `php artisan route:list --path=admin/tour-cabinet` — 42 роута, без регрессии.
- Smoke-сценарий через php-однострочник (Docker), `TourCabinetContestDashboardData::forUser` для одного выбранного города:
  1. Без per-row формы и без globals → `form_slug=null, auto_completed=1, submitted=0, stage1Complete=1`.
  2. `lms_form_slug='test-slug-x'`, без сабмита → `form_slug='test-slug-x', auto_completed=0, submitted=0, stage1Complete=0`.
  3. `lms_form_slug='test-slug-x'` + сабмит → `submitted=1, stage1Complete=1`.
- `npm run build` (Docker) — `built in 5.54s`, без ошибок.
- `ReadLints` по 11 затронутым файлам (PHP + Vue + миграция) — чисто.

### Task 8. Финализация spec/progress ✓

- `spec/features/contest-city-forms/spec.md` — добавлен раздел «Реализация (как сделано)» и «Verify summary».
- `spec/features/contest-city-forms/progress.md` — все 8 задач в Completed.

### Task 9. Drop-fallback (2026-04-29) ✓

Причина: на проде у одного из городов админ выбрал «— Без формы —», но в ЛК участника город всё равно открывал анкету через глобальный fallback (settings `contest_stage1_form_slug_standard`). Решения по `AskQuestion`: `fallback_strategy=drop_fallback_clean`, `global_block_visible=hide_completely`.

- Files (бэкенд):
  - `app/Services/TourCabinetContestStage1FormResolver.php` — конструктор без зависимостей; `resolveForRow` возвращает только `trim($row->lms_form_slug) ?: null`. Глобал из `SettingsService` больше не вызывается.
  - `app/Services/TourCabinetContestDashboardData.php` — payload `contestStage1` без ключа `formSlugsConfigured`.
  - `app/Services/Admin/TourCabinetHubPageData.php::formsPayload` — без ключей `contestFormSlugOverrides` и `formOptions`; остаётся `allFormsOptions` (для блока «Стандартная анкета»).
  - `app/Http/Controllers/Admin/TourCabinetFormsController.php` — метод `updateContestFormSlugs` удалён, импорт `LmsEvent` удалён.
  - `routes/web.php` — роут `PUT admin.tour-cabinet.forms.contest-form-slugs.update` удалён; страница `GET admin.tour-cabinet.forms.index` остаётся (UI «Стандартная анкета»).
- Files (админ-фронт):
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue` — RCard «Конкурс, этап 1 — какие формы открывать» удалена; пропсы `contestFormSlugOverrides`, `formOptions`, computed `formSelectOptions`, `useForm slugForm`, watch и `submitSlugs` удалены.
  - `resources/js/Pages/Admin/TourCabinet/Forms/Index.vue` — пропсы `contestFormSlugOverrides`, `formOptions` сняты с `defineProps` и проброса в панель.
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminDirectionCitiesPanel.vue` — подсказка под таблицей «Текущий список» переписана: упоминание «глобальный fallback из блока Формы и этап 1» удалено.
- Files (пользовательский фронт):
  - `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue` — prop `formSlugsConfigured` удалён.
- Files (spec):
  - `spec/features/contest-city-forms/spec.md` — Goal с пометкой `Update 2026-04-29 (drop-fallback)`; раздел «Источник формы для города» переписан на единственный источник; «Реализация» обновлена; Verify summary с разделом «Drop-fallback rollout»; Open questions с новыми решениями.
  - `spec/features/tour-cabinet/spec.md` — раздел «Конкурсный сценарий» и описание `/admin/tour-cabinet/forms` обновлены.
  - `spec/features/lk-participant-contests/spec.md` — таблица «Принятые решения» обновлена.
- Verify (Docker, см. Verify summary в spec.md): резолвер 4 кейса (включая сценарий с заданными legacy globals), payload dashboard без `formSlugsConfigured`, payload админки без `contestFormSlugOverrides`/`formOptions`, `route:list` без `contest-form-slugs.update`, `npm run build` 5.76s, `ReadLints` чисто.

## Partially completed

(пусто)

## Not started

(пусто) — фича реализована полностью.

## Open issues

(пусто)
