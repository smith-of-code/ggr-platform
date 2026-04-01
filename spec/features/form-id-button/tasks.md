# Задачи: form-id-button

## Task 1: Миграция — добавить `paid_form_slug` в `directions`

- **Цель**: новая колонка для хранения slug привязанной LMS-формы
- **Scope**: `database/migrations/XXXX_add_paid_form_slug_to_directions_table.php`
- **DoD**: миграция создана, выполняется без ошибок
- **Verify**: `php artisan migrate` — успешно, колонка `paid_form_slug` в таблице

## Task 2: Обновить модель `Direction`

- **Цель**: добавить `paid_form_slug` в `$fillable`
- **Scope**: `app/Models/Direction.php`
- **DoD**: поле в fillable, значение корректно сохраняется/читается
- **Verify**: `php artisan tinker` — `Direction::first()->paid_form_slug` возвращает null

## Task 3: Бэкенд админки — передать список форм и валидация

- **Цель**: `Admin\DirectionController` передаёт `lmsForms` (id, title, slug) в `create`/`edit`, валидирует `paid_form_slug`
- **Scope**: `app/Http/Controllers/Admin/DirectionController.php`
- **DoD**: пропсы `lmsForms` доступны на фронте, валидация `paid_form_slug` — `nullable|string|exists:lms_forms,slug`
- **Verify**: открыть `/admin/directions/create` — в DevTools проверить Inertia props содержат `lmsForms`

## Task 4: Фронт админки — селект формы в блоке «Платное участие»

- **Цель**: добавить `<select>` для выбора LMS-формы в блоке «Платное участие»
- **Scope**: `resources/js/Pages/Admin/Directions/Form.vue`
- **DoD**: селект отображает список форм, выбранное значение сохраняется в `form.paid_form_slug`, есть опция «— Нет (скролл к турам)»
- **Verify**: визуально в браузере — селект видим, сохранение работает

## Task 5: Бэкенд публичной страницы — загрузка формы

- **Цель**: `DirectionController@show` загружает `LmsForm` + `fields` по `paid_form_slug` и передаёт на фронт
- **Scope**: `app/Http/Controllers/DirectionController.php`
- **DoD**: если `paid_form_slug` задан и форма активна → пропс `paidForm` с полями; иначе → null
- **Verify**: открыть публичную страницу направления — в DevTools Inertia props содержат `paidForm`

## Task 6: Вынести рендер полей формы в компонент `FormRenderer.vue`

- **Цель**: переиспользуемый компонент рендера полей формы (из `Public.vue`) для использования в модалке
- **Scope**: `resources/js/Components/FormRenderer.vue`, рефактор `resources/js/Pages/Forms/Public.vue`
- **DoD**: `FormRenderer` принимает `form`, `fields`, emit `submitted`; `Public.vue` использует его
- **Verify**: публичная страница формы `/forms/{slug}` работает как раньше

## Task 7: Модалка с формой на `Directions/Show.vue`

- **Цель**: по клику «Оставить заявку» открыть модалку с `FormRenderer`, если `paidForm` задан; иначе скролл к турам
- **Scope**: `resources/js/Pages/Directions/Show.vue`
- **DoD**: модалка открывается, форма рендерится, submit работает, success-сообщение отображается, модалку можно закрыть
- **Verify**: визуально — клик → модалка → заполнить → отправить → success

## Task 8: Модалка с формой на `Directions/ShowAtomsVkusa.vue`

- **Цель**: аналогичная логика модалки для шаблона Атомов вкуса
- **Scope**: `resources/js/Pages/Directions/ShowAtomsVkusa.vue`
- **DoD**: кнопка «Подать заявку» (если `paidForm`) открывает модалку
- **Verify**: визуально — клик → модалка → заполнить → отправить → success

## Task 9: Обновить spec и progress

- **Цель**: финализация документации
- **Scope**: `spec/features/form-id-button/spec.md`, `spec/features/form-id-button/progress.md`, `spec/features/directions/spec.md`
- **DoD**: spec отражает финальное состояние, progress — все задачи completed
- **Verify**: чтение spec/progress — всё актуально
