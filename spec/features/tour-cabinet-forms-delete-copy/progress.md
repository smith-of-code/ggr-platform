# Прогресс: Удаление и копирование форм в админке ЛК туров

## Завершённые задачи

### Task 1. Бэкенд: метод `FormController::duplicate` ✓

- Files:
  - `app/Http/Controllers/Lms/Admin/FormController.php` — метод `duplicate(LmsEvent, LmsForm): RedirectResponse` (deep copy в `DB::transaction`: новая `LmsForm` с `title = "{old} — копия"` (mb-trim до 255), сгенерированный `slug = Str::slug($title) . '-' . Str::random(6)`, `is_active = false`, копируются все `LmsFormField` через `LmsFormField::create([...])` без id и lms_form_id оригинала).
  - В `lmsFormsRouteNamesForInertia()` добавлены ключи `destroy` и `duplicate` для обеих веток (`lms.admin.*` и `admin.tour-cabinet.lms.*`).
- Verify (Docker):
  - smoke-test через `php artisan tinker --execute=...`: до — 7 форм; вызов `FormController@duplicate` создал форму id=8 с `title="тестовая форма для проектов — копия"`, `slug="testovaia-forma-dlia-proektov-kopiia-hax2u9"`, `is_active=0`, поля скопированы (1→1); после очистки `forms_count` вернулся к 7.
  - `ReadLints` — чисто.

### Task 2. Бэкенд: регистрация роута `forms.duplicate` ✓

- Files:
  - `routes/lms.php` — `Route::post('forms/{form}/duplicate', [AdminFormController::class, 'duplicate'])->name('forms.duplicate')` (выше `Route::resource('forms', ...)`).
  - `routes/web.php` — то же в группе `admin.tour-cabinet.lms.*`.
- Verify (Docker): `php artisan route:list --json | grep -oE '"name":"[^"]*forms\.(duplicate|destroy)[^"]*"'` показывает 4 имени: `lms.admin.forms.duplicate`, `lms.admin.forms.destroy`, `admin.tour-cabinet.lms.forms.duplicate`, `admin.tour-cabinet.lms.forms.destroy`.

### Task 3. Фронт: панель форм на хабе ЛК туров ✓

- Files:
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue` — в карточке формы добавлены кнопки «Дублировать» (`RButton variant="ghost"`) и «Удалить» (`RButton variant="danger"`); `router.post`/`router.delete` на `admin.tour-cabinet.lms.forms.duplicate|destroy` с `preserveScroll: true`; перед удалением `confirm()` с упоминанием `form.submissions_count` если > 0; локальные `ref`-ы `duplicatingFormId`/`deletingFormId` для блокировки повторных кликов и текстов «Дублирование…»/«Удаление…» во время запроса.
- Verify: `npm run build` (Docker) — `built in 11.05s`, без ошибок; `ReadLints` — чисто.

### Task 4. Фронт: список форм в LMS Admin ✓

- Files:
  - `resources/js/constants/lmsAdminFormRoutes.js` — `defaultLmsAdminFormRouteNames` дополнено ключами `destroy` и `duplicate` (значения `lms.admin.forms.destroy`/`lms.admin.forms.duplicate`).
  - `resources/js/Pages/Lms/Admin/Forms/Index.vue` — те же кнопки/обработчики; используется `routeNames.value.duplicate`/`routeNames.value.destroy` (т.е. при `lmsFormsRouteNames`-override через Inertia вызовы попадают в роуты соответствующей группы — портальной или классической LMS Admin); контейнер кнопок переключён на `flex flex-wrap gap-2`.
- Verify: `npm run build` (Docker) — `built in 11.05s`, без ошибок; `ReadLints` — чисто.

### Task 5. Обновление спецификаций (`lms-forms`, `tour-cabinet`) ✓

- Files:
  - `spec/features/lms-forms/spec.md` — в таблице роутов админа добавлен `POST /forms/{form}/duplicate`; добавлены workflow «6. Дублирование формы» и «7. Удаление формы» (с явным указанием, что внешние slug-ссылки на форму в портальной админке ЛК туров не очищаются); упомянуто зеркало под `/admin/tour-cabinet/lms/{event}`.
  - `spec/features/tour-cabinet/spec.md` — раздел «Портальная админка (ЛК туров)» дополнен описанием действий «Дублировать»/«Удалить» в карточке формы со ссылкой на эту фичу.
- Verify: визуальная проверка дифа, `git diff spec/`.

### Task 6. Smoke-проверка через docker ✓

- `docker exec ${APP_NAME}_fpm npm run build` — `built in 11.05s`, без ошибок.
- `php artisan route:list --json` (Docker) — оба зеркальных набора маршрутов на месте.
- `php artisan tinker --execute=...` (Docker) — `FormController@duplicate` отрабатывает: счётчик форм +1, дубль выключен, поля скопированы; cleanup корректен.

### Task 7. Финализация прогресса ✓

- Этот файл.

## Update 2026-05-07 (soft delete) — выполнено

### Task 8. Миграция: `softDeletes()` в `lms_forms` ✓

- Files: `database/migrations/2026_05_07_180000_add_soft_deletes_to_lms_forms_table.php` (`up()` — `Schema::table('lms_forms', fn ($t) => $t->softDeletes())`; `down()` — `dropSoftDeletes()`).
- Verify (Docker): `php artisan migrate` — `2026_05_07_180000_add_soft_deletes_to_lms_forms_table … 8.55ms DONE`.

### Task 9. Модель `LmsForm`: трейт `SoftDeletes` ✓

- Files: `app/Models/Lms/LmsForm.php` — добавлен импорт `Illuminate\Database\Eloquent\SoftDeletes` и `use SoftDeletes;` в классе.
- Verify (Docker, tinker): `LmsForm::find($id)->delete()` → `deleted_at` ставится; `LmsForm::find($id)` → `null`; `LmsForm::withTrashed()->find($id)` → найдена; `lms_form_fields` остаются.

### Task 10. `FormController::checkSlug`: учёт удалённых ✓

- Files: `app/Http/Controllers/Lms/Admin/FormController.php` — оба запроса в `checkSlug` переведены на `LmsForm::withTrashed()->where('slug', ...)`.
- Verify (Docker, tinker): создана форма slug=`soft-delete-test-form`, soft-deleted; `checkSlug` для того же title → `available=false` + 3 suggestion `soft-delete-test-form-{1,2,3}`. С `exclude_id` своей записи → `available=true` (для редактирования восстановленной формы).

### Task 11. Финальная Verify ✓

- Migrate (Docker): миграция применилась.
- Tinker-смоки (Docker, см. выше): soft-delete сохраняет fields, default scope скрывает форму, `FormPublicController`-style запрос возвращает `null`, `forceDelete` каскадирует и удаляет fields.
- `npm run build` (Docker): `built in 9.65s`, без ошибок.
- `ReadLints`: чисто.

### Task 12. Обновление документации ✓

- Files:
  - `spec/features/lms-forms/spec.md` — workflow «7. Удаление формы (soft delete)» переписан: упоминание `SoftDeletes`-трейта, `withTrashed` в `checkSlug`, поведения при `forceDelete`, отсутствия UI восстановления; в БД-описании `lms_forms` добавлена колонка `deleted_at` и пометка про unique-индекс slug.
  - `spec/features/tour-cabinet-forms-delete-copy/spec.md` — header-блок Update 2026-05-07; раздел In-scope дополнен пунктом про soft delete; Out-of-scope скорректирован (soft delete вычеркнут, добавлен пункт про отсутствие авто-смены slug); Constraints обновлены.
  - `spec/features/tour-cabinet-forms-delete-copy/plan.md` — добавлены milestones 7–11 (Update 2026-05-07).
  - `spec/features/tour-cabinet-forms-delete-copy/tasks.md` — добавлены задачи 8–12.
  - Этот файл (`progress.md`).
- Verify: `git diff` показывает обновления по spec-файлам; «Не начатые» пуст.

## Update 2026-05-07 (trash UI) — выполнено

### Task 13. Контроллер `Admin\LmsFormTrashController` ✓

- Files: `app/Http/Controllers/Admin/LmsFormTrashController.php` — `index()` (`onlyTrashed()` + `with('event:id,slug,title')` + `withCount(['fields','submissions'])` + `paginate(20)->withQueryString()` → Inertia `Admin/Settings/FormsTrash`); `restore(int $form)` — `findOrFail` через `onlyTrashed`, `restore()` + flash; `forceDelete(int $form)` — `findOrFail`, `forceDelete()` + flash.
- Verify: `ReadLints` чисто.

### Task 14. Роуты `admin.settings.forms-trash.*` ✓

- Files: `routes/web.php` — добавлен импорт `AdminLmsFormTrashController` и 3 роута в admin-группе:
  - `GET /admin/settings/forms-trash` → `admin.settings.forms-trash.index`
  - `POST /admin/settings/forms-trash/{form}/restore` → `admin.settings.forms-trash.restore` (`whereNumber('form')`)
  - `DELETE /admin/settings/forms-trash/{form}` → `admin.settings.forms-trash.destroy` (`whereNumber('form')`)
- Verify (Docker): `php artisan route:list --json | grep '"name":"admin\.settings\.forms-trash[^"]*"'` → 3 имени.

### Task 15. Страница `Admin/Settings/FormsTrash.vue` ✓

- Files: `resources/js/Pages/Admin/Settings/FormsTrash.vue` — `AdminLayout`, заголовок «Корзина форм» + sub-text, обратная стрелка на `admin.settings.index`. flash-блоки (success/error). Таблица: «Форма» (title + `/forms/{slug}`), «Событие LMS» (`event.title` + `event.slug`), «Полей», «Отправок», «Удалена» (форматированный `deleted_at` через `Date.toLocaleString('ru-RU')`), «Действия» (две кнопки: «Восстановить» — `router.post`, «Удалить навсегда» — `router.delete`). Пустое состояние «Корзина пуста». Пагинация по `forms.links` (стандартный паттерн как в `Admin/Applications/Index.vue`). `restoringId`/`forceDeletingId`-refs блокируют повторные клики и меняют надпись на «Восстановление…»/«Удаление…». Confirm-диалоги: `Восстановить форму «X»? Она снова появится в обычных списках админки.` и `Удалить навсегда форму «X»?\n\nЭто действие нельзя отменить. Будут безвозвратно удалены: N полей, M отправок и все связанные ответы.`.
- Verify: `npm run build` (Docker) — `built in 8.99s`, без ошибок; `ReadLints` чисто.

### Task 16. Карточка в `Admin/Settings/Index.vue` ✓

- Files: `resources/js/Pages/Admin/Settings/Index.vue` — в массив `sections` добавлен пункт «Корзина форм» со ссылкой на `admin.settings.forms-trash.index`, иконка-«trash» (heroicon outline), `iconBg='bg-red-50 group-hover:bg-red-100'`, `iconColor='text-red-600'`.
- Verify: `npm run build` без ошибок; `ReadLints` чисто.

### Task 17. Обновление spec ✓

- Files:
  - `spec/features/lms-forms/spec.md` — workflow «7. Удаление формы» переписан про soft-delete и про cascade только при `forceDelete`; добавлен раздел «8. Корзина форм (восстановление и полное удаление)» с таблицей роутов и описанием UI.
  - `spec/features/tour-cabinet-forms-delete-copy/spec.md` — header-блок Update 2026-05-07 (trash UI); In-scope дополнен пунктами «Корзина форм» и «Карточка-вход»; Out-of-scope обновлён (UI восстановления переведён в in-scope).
  - `spec/features/tour-cabinet-forms-delete-copy/plan.md` — добавлены milestones 12–17.
  - `spec/features/tour-cabinet-forms-delete-copy/tasks.md` — задачи 13–18.
  - Этот файл (`progress.md`).

### Task 18. Финальная Verify (trash UI) ✓

- `php artisan route:list --json` (Docker): 3 trash-роута зарегистрированы.
- Tinker (Docker, smoke):
  - Создал две тестовые формы (slug `trash-ui-test-a` с 1 полем, и `trash-ui-test-b`), soft-deleted обе.
  - `LmsForm::onlyTrashed()->with('event')->withCount(['fields','submissions'])->paginate(20)` → `total=2`, обе формы в результате; `f1_fields_count=1, submissions_count=0, event_title='ВШГР 2026'`.
  - `LmsFormTrashController->restore($f1->id)` → форма видна в `LmsForm::find` (default scope), `deleted_at = NULL`, `f1_fields_intact=1`.
  - `LmsFormTrashController->forceDelete($f2->id)` → `LmsForm::withTrashed()->find($f2->id) === null` (физически удалено).
- `npm run build` (Docker): `built in 8.99s`, без ошибок.
- `ReadLints` по новому контроллеру и Vue-страницам — чисто.

## Частично выполненные

_Пусто_

## Не начатые

_Пусто_ — фича реализована полностью (включая Update 2026-05-07: soft delete + trash UI).

## Verify summary (Update 2026-05-07: trash UI)

- Routes (Docker): `admin.settings.forms-trash.{index,restore,destroy}` — 3 шт.
- Controller smoke (tinker, Docker): index выдаёт корректный paginated payload с relations + counts; `restore` снимает `deleted_at` и сохраняет fields; `forceDelete` удаляет каскадно.
- `npm run build` (Docker): успех.
- `ReadLints` (`LmsFormTrashController.php`, `routes/web.php`, `Admin/Settings/FormsTrash.vue`, `Admin/Settings/Index.vue`): чисто.

## Verify summary (Update 2026-05-07: soft delete)

- Migration: `2026_05_07_180000_add_soft_deletes_to_lms_forms_table` применена.
- Tinker (Docker):
  - `(new LmsForm())->getDeletedAtColumn() === 'deleted_at'`.
  - Soft delete: `deleted_at` ставится; default scope исключает; `withTrashed` находит; fields остаются.
  - `FormPublicController`-style запрос (`where('slug')->where('is_active', true)`) возвращает `null` для soft-deleted.
  - `checkSlug` детектит занятый slug у soft-deleted формы и выдаёт корректные suggestions.
  - `forceDelete` каскадирует и удаляет `lms_form_fields`.
- `npm run build` (Docker): успех.
- `ReadLints` по затронутым файлам: чисто.

## Verify summary

- PHP-lint: чисто (`ReadLints` по `app/Http/Controllers/Lms/Admin/FormController.php`, `routes/lms.php`, `routes/web.php`).
- Vue-lint: чисто (`ReadLints` по `TourCabinetAdminFormsPanel.vue`, `Lms/Admin/Forms/Index.vue`, `lmsAdminFormRoutes.js`).
- Frontend build (Docker, `npm run build`): успешно.
- `php artisan route:list` (Docker): новые маршруты `lms.admin.forms.duplicate`, `admin.tour-cabinet.lms.forms.duplicate` присутствуют; `forms.destroy` для обеих групп работает (через `Route::resource`).
- Tinker smoke-тест (Docker): дубликация формы создаёт копию с полями, новым slug и `is_active=false`; cleanup корректен.

## Open issues

_Пусто_
