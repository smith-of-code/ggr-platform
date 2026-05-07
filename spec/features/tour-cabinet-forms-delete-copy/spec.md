# Удаление и копирование форм в админке ЛК туров

> **Update 2026-05-07 (soft delete)**: `LmsForm` переведена на `SoftDeletes`. Кнопка «Удалить» теперь выполняет мягкое удаление (`deleted_at` ставится, строка остаётся в БД, slug продолжает занимать unique-индекс). Запись формы и все связанные `lms_form_fields/submissions/responses` физически НЕ удаляются — восстановление возможно через UI «корзины» (см. блок ниже). `FormPublicController` через default scope автоматически перестаёт показывать удалённые формы, `checkSlug` использует `withTrashed()` для корректной проверки коллизии слагов.

> **Update 2026-05-07 (trash UI)**: добавлена страница «Корзина форм» в `/admin/settings/forms-trash` (`admin.settings.forms-trash.index`). Список soft-deleted `LmsForm` с пагинацией; в каждой строке две кнопки: **«Восстановить»** (`POST .../{form}/restore` — снимает `deleted_at`, возвращает форму в админку и публичный доступ) и **«Удалить навсегда»** (`DELETE .../{form}` — `forceDelete` со срабатыванием FK cascade на `lms_form_fields/submissions/responses`). Карточка-вход — в `/admin/settings`.

## Goal

Дать редактору на `/admin/tour-cabinet` (и зеркальном `/admin/tour-cabinet/forms`, и в LMS Admin `/lms-admin/{event}/forms`) возможность удалить форму и быстро создать её копию (deep copy с полями) прямо из списка форм события, без перехода в LMS Admin или ручного пересоздания.

## In-scope

- Кнопка «Удалить» в карточке формы списка форм (хаб `/admin/tour-cabinet`, страница `/admin/tour-cabinet/forms`, LMS Admin `/lms-admin/{event}/forms`).
- Кнопка «Дублировать» рядом с «Редактировать» в той же карточке формы.
- Бэкенд-метод `Lms\Admin\FormController::duplicate(LmsEvent $event, LmsForm $form)`: создаёт новую `LmsForm` в том же событии с заголовком `"{original} — копия"`, уникальным сгенерированным `slug`, `is_active = false` по умолчанию, копирует все `LmsFormField` (включая `validation`, `options`, `required`, `position`); submissions и responses **не копируются**.
- Роут `POST forms/{form}/duplicate` (named `forms.duplicate`) — в обоих route-группах: `lms.admin.*` (`routes/lms.php`) и `admin.tour-cabinet.lms.*` (`routes/web.php`). Редирект на index списка форм с flash-сообщением «Форма продублирована».
- `destroy` (`DELETE /forms/{form}`) уже зарегистрирован через `Route::resource(...)->except(['show'])` — повторно не объявлять.
- Confirm-диалог удаления упоминает количество ответов (`form.submissions_count`) если > 0: «Удалить форму «X»? Будут удалены все ответы (N).».
- **Soft delete для `LmsForm`** (Update 2026-05-07): миграция `add_soft_deletes_to_lms_forms_table` добавляет колонку `deleted_at`; модель `App\Models\Lms\LmsForm` использует трейт `Illuminate\Database\Eloquent\SoftDeletes`; существующий `FormController@destroy` без изменений (`$form->delete()` теперь soft-deletes благодаря трейту). `FormController@checkSlug` использует `withTrashed()`, чтобы корректно сообщать об уже занятом slug (включая удалённые формы), иначе `unique`-индекс БД даст 500. Связанные `lms_form_fields`/`lms_form_submissions`/`lms_form_responses` физически остаются (FK cascade срабатывает только при `forceDelete`).
- **Корзина форм** (Update 2026-05-07): отдельный экран `/admin/settings/forms-trash` для админов портала. Контроллер `App\Http\Controllers\Admin\LmsFormTrashController`:
  - `index()` — `LmsForm::onlyTrashed()->with('event:id,slug,title')->withCount(['fields','submissions'])->orderByDesc('deleted_at')->paginate(20)` → Inertia-страница `Admin/Settings/FormsTrash`.
  - `restore(int $form)` — `LmsForm::onlyTrashed()->findOrFail($form)->restore()` + flash `success`. Возвращает форму в обычные списки и публичный доступ (если `is_active`).
  - `forceDelete(int $form)` — `findOrFail` через `onlyTrashed`, затем `forceDelete()` (FK cascade чистит fields/submissions/responses) + flash `success`.
- **Карточка-вход** на `/admin/settings`: добавляется в массив `sections` страницы `Admin/Settings/Index.vue` со ссылкой на `admin.settings.forms-trash.index` и описанием «Удалённые формы LMS / ЛК туров: восстановить или удалить навсегда».

## Out-of-scope

- Массовое удаление/копирование (отметить чекбоксами и удалить пачкой).
- ~~Soft delete / корзина / восстановление формы после удаления.~~ → Soft delete + UI корзины (`/admin/settings/forms-trash`) с действиями «Восстановить» и «Удалить навсегда» переведены в in-scope (Update 2026-05-07).
- Авто-перепривязка ссылок на форму после удаления (см. «Constraints»): диалог удаления **не** проверяет внешние использования slug.
- Перенос формы в другое событие при дубликате (всегда создаём в `event` исходной формы).
- Авто-«разкоррекция» slug у soft-deleted формы (например, переименование `slug__deleted-{id}`) — slug продолжает занимать unique-индекс БД; конфликт детектится через `checkSlug` (с `withTrashed`), исправление — за админом.

## Constraints

- При удалении формы (Update 2026-05-07: **soft delete** через `SoftDeletes`-трейт) `lms_form_fields`, `lms_form_submissions`, `lms_form_responses` остаются в БД — FK cascade срабатывает только при `forceDelete`, который из UI не вызывается. Внешние ссылки **на slug** в `tour_cabinet_settings.dashboard_standard_form_slug`, `tour_cabinet_direction_cities.lms_form_slug`, `tour_cabinet_commerce_city_forms.lms_form_slug` **не очищаются** (текстовое поле, не FK) — после soft-delete формы соответствующие публичные обращения по slug возвращают 404 (default scope `SoftDeletes` исключает удалённые), места без авто-сброса тихо считаются «без формы». Это поведение фиксируется здесь как осознанный выбор MVP.
- `LmsForm::query()` по умолчанию исключает soft-deleted формы — все списки в админке (хаб, LMS Admin, селекты `allFormsOptions`) автоматически их не показывают. `FormController@checkSlug` использует `withTrashed()` чтобы детектить занятый slug у удалённой формы.
- Slug дубля генерируется как в `store()`: `Str::slug($title) . '-' . Str::random(6)` (длина суффикса достаточна для уникальности; коллизия за пределами MVP).
- Deep copy полей: `LmsFormField::create([...])` для каждого, в порядке `position`. Поля копируются строго по столбцам fillable модели — без `id`/`lms_form_id` исходной формы.
- Безопасность: в обоих контроллерных вызовах действует `ensureFormBelongsToEvent($form, $event)`. Доступ ограничен middleware `lms.backoffice` (`/admin/tour-cabinet/lms/...`) и стандартной защитой LMS-админки (`/lms-admin/...`). Soft-deleted форму через `Route::resource` resolve-binding **не открыть** (Eloquent-биндинг применяет default scope), поэтому повторный delete/edit удалённой формы из UI невозможны.
- Стиль кнопок и подтверждения — как в существующих панелях (`TourCabinetAdminDirectionCitiesPanel`, `TourCabinetAdminStage2QuestionsPanel`): нативный `confirm()` + `router.delete()/router.post()` + `preserveScroll: true`.

## Open questions

_Нет._
