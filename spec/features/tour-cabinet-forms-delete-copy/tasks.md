# Задачи: Удаление и копирование форм в админке ЛК туров

## 1. Бэкенд: метод `FormController::duplicate`

- **Goal**: реализовать deep copy формы вместе с полями.
- **Scope**: `app/Http/Controllers/Lms/Admin/FormController.php`.
- **DoD**: метод `duplicate(LmsEvent $event, LmsForm $form): RedirectResponse`; в транзакции создаёт `LmsForm` (`title = "{old_title} — копия"`, новый `slug = Str::slug(title) . '-' . Str::random(6)`, `is_active = false`, остальные boolean/нullable-поля копируются как у оригинала), копирует все `LmsFormField` через `LmsFormField::create([...])`; submissions/responses не трогает; `ensureFormBelongsToEvent($form, $event)`; редирект на `redirectToFormsIndex($event)` с flash `success = "Форма продублирована"`.
- **Verify**: проверить через `tinker` или ручной POST: `LmsForm::count()` увеличился на 1, `LmsFormField::where('lms_form_id', $copy->id)->count()` равно числу полей оригинала.

## 2. Бэкенд: регистрация роута `forms.duplicate`

- **Goal**: добавить `POST forms/{form}/duplicate` в обе группы.
- **Scope**: `routes/lms.php` (группа `lms.admin.*`), `routes/web.php` (группа `admin.tour-cabinet.lms.*`).
- **DoD**: оба роута объявлены **выше или рядом** с `Route::resource('forms', ...)`; имя `forms.duplicate`; вызывают `[FormController::class, 'duplicate']`. Также добавить `'duplicate'` и `'destroy'` (если ещё не) в `FormController::lmsFormsRouteNamesForInertia()` для обеих веток.
- **Verify**: `php artisan route:list | rg forms.duplicate` показывает оба маршрута (`lms.admin.forms.duplicate`, `admin.tour-cabinet.lms.forms.duplicate`).

## 3. Фронт: панель форм на хабе ЛК туров

- **Goal**: добавить кнопки «Дублировать» и «Удалить» в карточку формы хаба.
- **Scope**: `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue`.
- **DoD**: внутри блока действий карточки формы появляются кнопки «Дублировать» (`RButton variant="ghost"`, иконка дубль) и «Удалить» (`RButton variant="ghost"` с красным текстом, иконка корзина); обработчики через `router.post(route('admin.tour-cabinet.lms.forms.duplicate', [lmsEvent.slug, form.id]), {}, { preserveScroll: true })` и `router.delete(route('admin.tour-cabinet.lms.forms.destroy', [lmsEvent.slug, form.id]), { preserveScroll: true })`; перед удалением `confirm("Удалить форму «{title}»?" + (count > 0 ? " Будут удалены все ответы (N)." : ""))`.
- **Verify**: на `/admin/tour-cabinet` карточка формы показывает 4 кнопки в ряд («Публичная страница», «Статистика», «Редактировать», «Дублировать», «Удалить»); проверить функциональность.

## 4. Фронт: список форм в LMS Admin

- **Goal**: те же кнопки в списке `Lms/Admin/Forms/Index.vue`.
- **Scope**: `resources/js/Pages/Lms/Admin/Forms/Index.vue`, `resources/js/constants/lmsAdminFormRoutes.js`.
- **DoD**: добавлены кнопки «Дублировать» и «Удалить» с тем же UX; используется `routeNames.duplicate`/`routeNames.destroy` (расширить `defaultLmsAdminFormRouteNames` ключами `duplicate`, `destroy`, и значения `lms.admin.forms.duplicate`/`lms.admin.forms.destroy`); confirm-сообщение идентично.
- **Verify**: на `/lms-admin/{event}/forms` и `/admin/tour-cabinet/lms/{event}/forms` карточка формы показывает кнопки; дублирование и удаление работают; преданные через Inertia `lmsFormsRouteNames` имена корректно используются.

## 5. Обновление спецификаций

- **Goal**: отразить новые действия в существующих спеках.
- **Scope**: `spec/features/lms-forms/spec.md`, `spec/features/tour-cabinet/spec.md`.
- **DoD**: в `lms-forms/spec.md` раздел «Роуты → Админ» получает строку `POST /forms/{form}/duplicate`; в «Ключевых workflow» добавлен пункт «Дублирование формы» (deep copy + new slug, `is_active=false`); в `tour-cabinet/spec.md` раздел «Портальная админка (ЛК туров)» — упоминание про дублирование/удаление форм в карточках списка.
- **Verify**: `git diff spec/features/lms-forms/spec.md spec/features/tour-cabinet/spec.md` показывает добавленные строки.

## 6. Smoke-проверка через docker

- **Goal**: убедиться, что фронт собирается и нет регрессии.
- **Scope**: docker-сборка фронта.
- **DoD**: `npm run build` без ошибок; ручная проверка трёх страниц (`/admin/tour-cabinet`, `/admin/tour-cabinet/forms`, `/admin/tour-cabinet/lms/{event}/forms`) — кнопки рендерятся, дублирование создаёт копию, удаление удаляет, `confirm()` отрабатывает.
- **Verify**: согласно «Command Execution Pattern» из `spec-continuation.mdc` (docker exec в `${APP_NAME}_fpm`).

## 7. Документация прогресса

- **Goal**: финальная синхронизация `progress.md`.
- **Scope**: `spec/features/tour-cabinet-forms-delete-copy/progress.md`.
- **DoD**: задачи 1–6 в «Завершённых», `Open issues` пуст.
- **Verify**: `cat spec/features/tour-cabinet-forms-delete-copy/progress.md` — все задачи отмечены.

## Update 2026-05-07 (soft delete)

## 8. Миграция: `softDeletes()` в `lms_forms`

- **Goal**: добавить колонку `deleted_at` в `lms_forms`.
- **Scope**: `database/migrations/2026_05_07_180000_add_soft_deletes_to_lms_forms_table.php`.
- **DoD**: `up()` — `Schema::table('lms_forms', fn (Blueprint $t) => $t->softDeletes())`; `down()` — `Schema::table('lms_forms', fn (Blueprint $t) => $t->dropSoftDeletes())`.
- **Verify**: `php artisan migrate` (Docker) — миграция выполнена; `php artisan migrate:rollback --step=1` + повторный `migrate` работает без ошибок.

## 9. Модель `LmsForm`: трейт `SoftDeletes`

- **Goal**: подключить трейт `SoftDeletes` к `App\Models\Lms\LmsForm`.
- **Scope**: `app/Models/Lms/LmsForm.php`.
- **DoD**: `use Illuminate\Database\Eloquent\SoftDeletes;` импорт + `use SoftDeletes;` внутри класса. Без изменений в `$fillable` (`deleted_at` не нужен в fillable).
- **Verify**: tinker (Docker) — `(new App\Models\Lms\LmsForm())->getDeletedAtColumn() === 'deleted_at'`; `LmsForm::find($id)->delete()` теперь soft-deletes.

## 10. `FormController::checkSlug`: учёт удалённых

- **Goal**: чтобы AJAX-проверка слага не давала ложных «свободно» для slug, занятого удалённой формой.
- **Scope**: `app/Http/Controllers/Lms/Admin/FormController.php` — методы `checkSlug` (как в основной проверке `$query`, так и в цикле `suggestions`).
- **DoD**: `LmsForm::withTrashed()->where('slug', ...)` в обоих местах. Поведение: уже занятый slug (включая удалённые формы) даёт `available: false` и валидные `suggestions`.
- **Verify**: tinker — soft-delete форма с slug `s`; `checkSlug` для `title` → `s` сообщает `available=false`.

## 11. Финальная Verify

- **Goal**: убедиться, что новые сценарии работают и нет регрессии.
- **Scope**: docker-сборка фронта + smoke на бэкенде.
- **DoD**: `php artisan migrate` (Docker) — `add_soft_deletes_to_lms_forms_table` отмечен; tinker-смок: `(1)` обычный `delete()` ставит `deleted_at` и `LmsForm::find($id) === null`; `(2)` `LmsForm::withTrashed()->find($id)` возвращает форму и её `fields` доступны; `(3)` публичный `LmsForm::where('slug', $deletedSlug)->where('is_active', true)->first()` равен `null`; `(4)` `forceDelete` каскадирует и удаляет всё; `npm run build` без ошибок.
- **Verify**: artefacts из tinker-сценариев + успех сборки фронта.

## 12. Обновление документации

- **Goal**: финальная синхронизация спек и `progress.md`.
- **Scope**: `spec/features/lms-forms/spec.md`, `spec/features/tour-cabinet-forms-delete-copy/{spec,plan,tasks,progress}.md`.
- **DoD**: в `lms-forms/spec.md` workflow «Удаление формы» переписан под soft-delete (включая упоминание `withTrashed` в `checkSlug`); в БД-описании `lms_forms` добавлена строка `deleted_at`; раздел «Роуты» без изменений (та же `forms.destroy`); `progress.md` фичи дополнен задачами 8–12.
- **Verify**: `git diff` показывает обновления; «Не начатые» в progress.md пуст.

## Update 2026-05-07 (trash UI)

## 13. Контроллер `Admin\LmsFormTrashController`

- **Goal**: реализовать backend для корзины форм.
- **Scope**: `app/Http/Controllers/Admin/LmsFormTrashController.php`.
- **DoD**: класс с тремя методами:
  - `index(): Response` — `LmsForm::onlyTrashed()->with('event:id,slug,title')->withCount(['fields','submissions'])->orderByDesc('deleted_at')->paginate(20)->withQueryString()` → Inertia `Admin/Settings/FormsTrash` с пропом `forms`.
  - `restore(int $form): RedirectResponse` — `LmsForm::onlyTrashed()->findOrFail($form)->restore()` + `back()->with('success', "Форма «{$title}» восстановлена")`.
  - `forceDelete(int $form): RedirectResponse` — то же `findOrFail`, затем `forceDelete()` + flash «удалена навсегда».
- **Verify**: tinker — создать soft-deleted форму, вызвать `index()` → payload содержит её; `restore(id)` снимает `deleted_at`; `forceDelete(id)` каскадирует на fields/submissions/responses.

## 14. Роуты `admin.settings.forms-trash.*`

- **Goal**: зарегистрировать 3 маршрута.
- **Scope**: `routes/web.php` (admin-группа, рядом с другими `settings.*`).
- **DoD**:
  - `Route::get('/settings/forms-trash', [LmsFormTrashController::class, 'index'])->name('settings.forms-trash.index');`
  - `Route::post('/settings/forms-trash/{form}/restore', [LmsFormTrashController::class, 'restore'])->whereNumber('form')->name('settings.forms-trash.restore');`
  - `Route::delete('/settings/forms-trash/{form}', [LmsFormTrashController::class, 'forceDelete'])->whereNumber('form')->name('settings.forms-trash.destroy');`
- **Verify**: `php artisan route:list --path=admin/settings/forms-trash` (Docker) показывает 3 строки.

## 15. Страница `Admin/Settings/FormsTrash.vue`

- **Goal**: визуальная реализация корзины.
- **Scope**: `resources/js/Pages/Admin/Settings/FormsTrash.vue`.
- **DoD**: layout `AdminLayout`; «← Настройки» link на `admin.settings.index`; заголовок «Корзина форм» + sub-text; таблица колонок: Название (с дополнением `slug`), Событие LMS (`event.title`), Полей, Отправок, Удалена (форматированный `deleted_at`), Действия. Кнопки: «Восстановить» (`router.post` на `settings.forms-trash.restore`, confirm), «Удалить навсегда» (`router.delete` на `settings.forms-trash.destroy`, confirm с упоминанием числа сабмитов). Пустое состояние: «Корзина пуста». Пагинация — стандартный шаблон через `forms.data`/`forms.links`. flash-сообщения через `usePage().props.flash`.
- **Verify**: `npm run build` (Docker) без ошибок; `ReadLints` чисто.

## 16. Карточка в `Admin/Settings/Index.vue`

- **Goal**: точка входа на корзину со страницы настроек.
- **Scope**: `resources/js/Pages/Admin/Settings/Index.vue`.
- **DoD**: в массиве `sections` добавлен объект с `route: 'admin.settings.forms-trash.index'`, заголовок «Корзина форм», описание «Удалённые формы LMS / ЛК туров: восстановить или удалить навсегда», иконка-«trash» (heroicon outline), `iconBg` и `iconColor` в красно-сером тоне.
- **Verify**: `npm run build` без ошибок; ручная проверка `/admin/settings` показывает новую карточку.

## 17. Обновление спек (trash UI)

- **Goal**: отразить новую функциональность в spec.
- **Scope**: `spec/features/lms-forms/spec.md`, `spec/features/tour-cabinet-forms-delete-copy/spec.md`, `progress.md`.
- **DoD**: в `lms-forms/spec.md` workflow «Удаление формы» дополнен пунктами 7 (восстановление через UI), 8 (полное удаление через UI); в `tour-cabinet-forms-delete-copy/spec.md` header-блок Update 2026-05-07 (trash UI) и In-scope/Out-of-scope обновлены; в `progress.md` добавлены задачи 13–18.
- **Verify**: `git diff spec/features/lms-forms/spec.md spec/features/tour-cabinet-forms-delete-copy/`.

## 18. Финальная Verify (trash UI) и progress.md

- **Goal**: подтверждение что цикл soft-delete → restore / forceDelete работает целиком.
- **Scope**: docker-проверки.
- **DoD**: tinker-сценарии (Docker):
  - Soft-delete тестовой формы → `LmsFormTrashController::index()` payload содержит её с `fields_count`/`submissions_count`/event-relation.
  - `LmsFormTrashController::restore(id)` → `deleted_at = null`, форма видна в обычных списках.
  - `LmsFormTrashController::forceDelete(id)` → `LmsForm::withTrashed()->find` возвращает `null`, fields/submissions удалены каскадом.
  - `route:list --path=admin/settings/forms-trash` — 3 роута.
  - `npm run build` (Docker) без ошибок.
  - `ReadLints` по новому контроллеру и Vue — чисто.
- **Verify**: artefacts из tinker + успех сборки фронта; финал `progress.md`.
