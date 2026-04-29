# План: contest-city-forms

## Принципы

- Sequential mode (одна задача в Partially completed, переходим только после завершения).
- Не более 5 файлов на шаг; иначе — дробление.
- Источник правды — `spec.md`. Историю выполнения — в `progress.md`.
- Все команды — через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`).

## Порядок задач

### Task 1. Миграция + модель

**Цель**: добавить колонку `lms_form_slug` в `tour_cabinet_direction_cities` и расширить модель.

- Миграция `database/migrations/2026_04_29_190000_add_lms_form_slug_to_tour_cabinet_direction_cities.php` (новая):
  - up: `Schema::table('tour_cabinet_direction_cities', fn (Blueprint $t) => $t->string('lms_form_slug', 191)->nullable()->after('needs_more_data'))`.
  - down: `Schema::table('tour_cabinet_direction_cities', fn (Blueprint $t) => $t->dropColumn('lms_form_slug'))`.
- Модель `app/Models/TourCabinetDirectionCity.php` — добавить `lms_form_slug` в `$fillable`.
- Verify (Docker):
  - `php artisan migrate` — миграция применилась.
  - php-однострочник: `Schema::hasColumn('tour_cabinet_direction_cities', 'lms_form_slug')` = `true`.
  - php-однострочник: создание/обновление модели с `lms_form_slug` сохраняется.

**Файлов**: 2.

---

### Task 2. Резолвер формы Этапа 1 + использование в FormLinker

**Цель**: централизовать логику «какой slug формы у города» в одном сервисе и переиспользовать в `TourCabinetContestFormLinker`.

- Новый сервис `app/Services/TourCabinetContestStage1FormResolver.php`:
  - Метод `resolveForRow(TourCabinetDirectionCity $row): ?string`:
    1. Если `$row->lms_form_slug` не пустой → вернуть его.
    2. Иначе — глобальный slug из `SettingsService` по `$row->needs_more_data`.
    3. Иначе — `null`.
  - Метод `resolveBatch(array $directionIdToCityIds): array` для дашборда (одним запросом достаём строки и резолвим). Возвращает `array<int $cityId, ?string $slug>` для удобства потребителей. (Принимает direction_id единый для всех cityIds.)
  - Зависимость: `SettingsService` через DI.
- Правки `app/Services/TourCabinetContestFormLinker.php`:
  - Использовать `TourCabinetContestStage1FormResolver::resolveForRow($row)` вместо локальной логики `needs_more_data ? more_data : standard`.
  - Гард `in_array($form->slug, $allowed)` пересобрать: достаточно `$expected = $resolver->resolveForRow($row); $expected !== null && $form->slug === $expected`. Если `expected === null` — выходим (форма не привязана, не должно быть сабмита).
- Verify (Docker):
  - php-однострочник: `app(TourCabinetContestStage1FormResolver::class)->resolveForRow($row)` возвращает корректные значения для трёх случаев: per-row задан / только global / ничего.
  - `php artisan route:list --path=lms-forms` (если есть) и `--path=tour-cabinet/contest` — без регрессии.

**Файлов**: 2.

---

### Task 3. Админ-бэкенд (валидация + payload)

**Цель**: разрешить админу выбирать форму для пары `(direction, city)`.

- `app/Http/Controllers/Admin/TourCabinetDirectionCitiesController.php`:
  - В `store` — правило `lms_form_slug: nullable|string|max:191` + проверка существования и активности формы (по аналогии с `Admin\TourCabinetFormsController::updateDashboardStandardFormSlug` / `updateContestFormSlugs`).
  - В `update` — правило аналогичное; нормализация пустой строки → `null`.
  - В `update` — если `lms_form_slug` меняется (старое != новое) и существуют сабмиты `tour_cabinet_contest_city_submissions` для пары `(user_id, city_id)` (по `direction_city.city_id`), redirect возвращает `with('warning', 'У города N сабмитов: старые ответы остаются, новые участники получат новую форму.')`.
- `app/Services/Admin/TourCabinetHubPageData.php`:
  - В `directionCitiesPayload` пробрасываем `allFormsOptions` (любая активная `LmsForm`, как у `formsPayload`) — единый источник для селекта.
  - В элементы `rows[]` (которые сейчас отдают модель `TourCabinetDirectionCity` напрямую) — явно собираем массив с полями `id`, `direction_id`, `city_id`, `city`, `needs_more_data`, `position`, `lms_form_slug`, `submissions_count` (для предупреждения админа).
- Verify (Docker):
  - php-однострочник: после `update` через контроллер `lms_form_slug` сохраняется в БД; при попытке сохранить несуществующий slug — `ValidationException`.
  - Payload `directionCitiesPayload` отдаёт ключи `allFormsOptions`, и каждая строка содержит `lms_form_slug`/`submissions_count`.

**Файлов**: 2.

---

### Task 4. Админ-фронт (Vue)

**Цель**: добавить селект формы в форму создания и в каждую строку таблицы городов направления.

- `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminDirectionCitiesPanel.vue`:
  - Принять prop `allFormsOptions` (массив `{ slug, title, is_active }`).
  - В форме «Добавить город» — `SearchSelect` (или нативный `<select>`) формы рядом с чекбоксом «Нужно больше данных». Отправлять `lms_form_slug`.
  - В таблице существующих строк — колонка «Форма Этапа 1» с inline-`<select>` (по образцу `needs_more_data`-чекбокса). При смене — `router.patch` на `update` с `lms_form_slug`.
  - При наличии `submissions_count > 0` показывать рядом мини-плашку «N сабмитов» и `window.confirm` перед сменой формы.
- `resources/js/Pages/Admin/TourCabinet/Hub.vue` и `resources/js/Pages/Admin/TourCabinet/DirectionCities/Index.vue` — пробросить новый prop `allFormsOptions` (через `v-bind` либо явно).
- Verify (Docker):
  - `npm run build` — без ошибок.
  - `ReadLints` по затронутым `*.vue`.

**Файлов**: до 3.

---

### Task 5. Пользовательский бэкенд (`TourCabinetContestController` + dashboard data)

**Цель**: использовать резолвер для определения формы и обновить логику завершения Этапа 1.

- `app/Http/Controllers/TourCabinetContestController.php`:
  - Внедрить `TourCabinetContestStage1FormResolver` через конструктор.
  - `startCityForm` — резолв slug через `resolver->resolveForRow($row)`. При `null` — flash `info` «Для этого города форма не требуется» + редирект назад на дашборд (поведение для случая, если пользователь по старой ссылке/закладке зашёл).
  - `stage1Complete` (приватный) — переписать: для каждого `city_id` из `selected_city_ids` подгрузить `TourCabinetDirectionCity`, спросить резолвера; если `expectedSlug === null` → город автозавершён; иначе — проверить `tour_cabinet_contest_city_submissions`. Если есть город, у которого формы нет (после очистки fallback), он считается выполненным.
  - `completeStage1` — без изменений семантики (использует обновлённый `stage1Complete`).
- `app/Services/TourCabinetContestDashboardData.php`:
  - Использовать `TourCabinetContestStage1FormResolver::resolveBatch` для всех выбранных городов.
  - В `selectedCitiesForForms[*]` добавить `auto_completed` (bool: `form_slug === null && ! submitted`); поле `form_slug` остаётся.
  - Хелпер `stage1Complete($progress, $userId)` переписать симметрично контроллеру.
  - В `cities[*]` (для шага «cities») добавить `has_form` (bool) — для UI-метки «Стандартная анкета (форма не требуется)».
  - В `formSlugsConfigured` оставить старые ключи (`standard`/`more_data`) для бэкомпатов фронта (UI-предупреждение про админ-настройки превратится в мягкое и не блочное).
- Verify (Docker):
  - php-однострочник:
    - У города без формы (`lms_form_slug=null`, globals=пусто) с `selected_city_ids=[id]` → `stage1Complete = true`.
    - У города с формой (`lms_form_slug='X'`) и без сабмита → `stage1Complete = false`.
  - `php artisan route:list --path=tour-cabinet/contest` — без регрессии.

**Файлов**: 2.

---

### Task 6. Пользовательский фронт (`ContestStage1Panel.vue`)

**Цель**: показать «Заполнено» автоматически для городов без формы и убрать жёсткое требование «оба глобальных slug заданы».

- `resources/js/Pages/TourCabinet/Contest/ContestStage1Panel.vue`:
  - На шаге `forms` для каждого города:
    - Если `c.auto_completed` → бейдж «Заполнено» (`bg-emerald-50/text-emerald-800`), без кнопки «Заполнить анкету».
    - Если `c.submitted` → существующий бейдж «Отправлено».
    - Иначе → существующая кнопка `tour-cabinet.contest.city-form`.
  - Подпись «Стандартная анкета» / «Необходимо заполнение дополнительных персональных данных» — без изменений (уже отображается через `c.needs_more_data`).
  - Условие кнопки «Перейти к этапу 2»:
    - Старое: `stage1Complete && formSlugsConfigured.standard && formSlugsConfigured.more_data && maxContestStages >= 2`.
    - Новое: `stage1Complete && maxContestStages >= 2` (резолвер уже учитывает globals + per-row).
  - Плашка `«В админке портала… задайте оба slug…»` — заменить на условную: показывается только если у конкретного города из `selectedCitiesForForms` `form_slug === null && ! auto_completed && ! submitted` (что означает: форма ожидается, но slug не настроен).
- Verify (Docker):
  - `npm run build` — без ошибок.
  - `ReadLints` по `ContestStage1Panel.vue`.

**Файлов**: 1.

---

### Task 7. Финальная верификация

- `npm run build` (Docker) — финальная сборка фронта.
- `php artisan migrate:status` (Docker) — миграция `2026_04_29_190000_…` отмечена как применённая.
- `php artisan route:list --path=admin/tour-cabinet` и `--path=tour-cabinet/contest` — без регрессии.
- `ReadLints` по всем затронутым файлам (PHP + Vue).
- Smoke-сценарий через `php artisan tinker` (Docker):
  1. Создать пользователя `is_tour_cabinet_user`.
  2. Создать `tour_cabinet_contest_progress` с `direction_id` и `selected_city_ids = [A, B]`, где у A — `lms_form_slug = 'X'`, у B — `lms_form_slug = null` и globals пусты.
  3. Проверить `TourCabinetContestDashboardData::forUser` → `selectedCitiesForForms` содержит для A `form_slug='X', auto_completed=false`, для B `form_slug=null, auto_completed=true`. `stage1Complete` зависит от наличия submission для A.

### Task 8. Финализация spec/progress

- Заполнить раздел «Реализация (как сделано)» в `spec.md` (по аналогии с `contest-length-limits/spec.md`).
- Все 8 задач в `progress.md` → Completed; `Partially completed` пуст.
