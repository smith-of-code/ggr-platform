# План: Удаление и копирование форм в админке ЛК туров

## Milestones

1. **Бэкенд: метод `duplicate`** — добавить `Lms\Admin\FormController::duplicate(LmsEvent, LmsForm)` с deep copy `LmsForm` + `LmsFormField` в одной транзакции; редирект на индекс с flash «Форма продублирована».
2. **Бэкенд: роуты** — зарегистрировать `POST forms/{form}/duplicate` в обеих группах: `lms.admin.*` (`routes/lms.php`) и `admin.tour-cabinet.lms.*` (`routes/web.php`). Названия — `forms.duplicate`. Записать имена роутов в `lmsFormsRouteNamesForInertia()`.
3. **Фронт: панель форм на хабе ЛК туров** — в `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue` добавить кнопки «Дублировать» и «Удалить» в каждой карточке формы; обработчики через `router.post`/`router.delete` + `confirm()` с указанием `submissions_count`.
4. **Фронт: список форм в LMS Admin** — в `resources/js/Pages/Lms/Admin/Forms/Index.vue` добавить те же кнопки, использовать `routeNames.duplicate` и `routeNames.destroy`; пробросить новые имена через `lmsFormsRouteNames` и `defaultLmsAdminFormRouteNames`.
5. **Spec-документация** — обновить `spec/features/lms-forms/spec.md` (раздел Роуты + workflow `duplicate`) и `spec/features/tour-cabinet/spec.md` (раздел про админку форм) кратким упоминанием новых действий.
6. **Verify** — собрать фронт, открыть `/admin/tour-cabinet` (хаб), `/admin/tour-cabinet/forms` и `/admin/tour-cabinet/lms/{event}/forms`: проверить дублирование и удаление формы (с одной и без ответов); проверить редиректы и flash-сообщения.

### Update 2026-05-07 (soft delete)

7. **Миграция `add_soft_deletes_to_lms_forms_table`** — `Schema::table('lms_forms', fn ($t) => $t->softDeletes())`; rollback — `dropSoftDeletes()`.
8. **Модель `LmsForm`** — добавить `use Illuminate\Database\Eloquent\SoftDeletes` и трейт `SoftDeletes`.
9. **`FormController::checkSlug`** — менять запрос на `LmsForm::withTrashed()->where('slug', $baseSlug)` чтобы корректно выявлять занятый slug у удалённых форм; suggestion-цикл тоже учитывает trashed.
10. **Spec и progress** — обновить `lms-forms/spec.md` (workflow «Удаление формы» + БД-описание `lms_forms`), `tour-cabinet-forms-delete-copy/spec.md` (header-блок Update + Constraints + Out-of-scope) и `progress.md` фичи.
11. **Verify (soft delete)** — `php artisan migrate` (Docker), tinker-смока: `LmsForm::find($id)->delete()` ставит `deleted_at`, `LmsForm::find($id)` возвращает `null`, `LmsForm::withTrashed()->find($id)` находит, fields сохраняются. `npm run build` без ошибок (фронт не меняется, но регресс возможен через типы).

### Update 2026-05-07 (trash UI)

12. **Контроллер `Admin\LmsFormTrashController`** — `index` (Inertia render с пагинацией), `restore(int $form)` (`LmsForm::onlyTrashed()->findOrFail`+`->restore()` + flash), `forceDelete(int $form)` (`->forceDelete()` + flash).
13. **Роуты в `routes/web.php`** в admin-группе: `GET /settings/forms-trash` (`admin.settings.forms-trash.index`), `POST /settings/forms-trash/{form}/restore` (`admin.settings.forms-trash.restore`, `whereNumber('form')`), `DELETE /settings/forms-trash/{form}` (`admin.settings.forms-trash.destroy`, `whereNumber('form')`).
14. **Vue-страница `Admin/Settings/FormsTrash.vue`** — таблица: title / event / slug / fields / submissions / deleted_at / actions; пагинация по `forms.links`/`forms.meta`; confirm-диалоги: restore («Восстановить форму "{title}"?»), forceDelete («Удалить навсегда форму "{title}"? Это действие нельзя отменить. Будут безвозвратно удалены все её поля, отправки ({n}) и ответы.»).
15. **Карточка в `Admin/Settings/Index.vue`** — пункт «Корзина форм» в массиве `sections` со ссылкой на `admin.settings.forms-trash.index`, иконка корзины, цвет nginx-серый/red-50.
16. **Spec lms-forms** — workflow удаления дополнен пунктом про восстановление/полное удаление через `/admin/settings/forms-trash`.
17. **Verify (trash UI)** — tinker-сценарии: создать форму, soft-delete, `LmsFormTrashController@index` payload содержит её, `restore` возвращает в обычный список, `forceDelete` каскадирует. `php artisan route:list --path=admin/settings/forms-trash` показывает 3 роута. `npm run build` (Docker) без ошибок.

## UI Components

- `RButton` (variant `outline`/`ghost`/`danger` — где есть; иначе `outline` + красный текст по аналогии с другими destructive-действиями) — уже глобально зарегистрирован, новый компонент не нужен.
- `RBadge`, `RCard` — без изменений, только выравнивание кнопок.
- Confirm-диалог — нативный `window.confirm()` (как в `TourCabinetAdminStage2QuestionsPanel.vue`, `TourCabinetAdminDirectionCitiesPanel.vue`); собственный модальный компонент создавать не нужно (нет переиспользуемого `ConfirmDialog` в проекте — см. `Reuse before create`).
- Иконки — inline SVG (24x24 stroke), стиль как в существующих кнопках карточки.

## Verification

Проверка через docker по «Command Execution Pattern» из `spec-continuation.mdc`: `npm run build` для сборки фронта; ручная проверка UI на `/admin/tour-cabinet`, `/admin/tour-cabinet/forms`, `/admin/tour-cabinet/lms/{event}/forms` (создать форму → дублировать → проверить копию → удалить копию).
