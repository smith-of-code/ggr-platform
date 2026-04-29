# План реализации: form-validation

## Milestones

1. **БД и модель**: миграция на колонку `validation` в `lms_form_fields`, обновление `LmsFormField::$fillable`.
2. **PHP-пресеты**: сервис `App\Services\Lms\Forms\FieldValidationPresets` с правилами (`Closure`/regex) и русскими сообщениями. Единая точка истины для серверной стороны.
3. **Админка форм (бэк)**: расширение `FormController::validateForm`/`syncFields` — принимать и сохранять `validation` для каждого поля.
4. **Публичная валидация**: использовать пресет в `FormPublicController::buildPublicFormAnswerRules` и сообщениях; не ломать существующую логику email/phone.
5. **Передача в фронт**: прокинуть `validation` в payload `show`/`apiShow` и в `Inertia::render` админки (`edit`/`create`).
6. **JS-пресеты**: `resources/js/constants/formFieldValidations.js` (regex + label + checkSum где надо) — зеркало PHP-каталога.
7. **Конструктор форм UI**: в `Pages/Lms/Admin/Forms/Form.vue` добавить выпадающий список «Тип валидации» (виден для `text`/`textarea`).
8. **Публичный рендер**: предварительная клиентская валидация в `Components/FormRenderer.vue` — показывать ошибку до submit.
9. **Виджет**: предварительная валидация в `public/js/form-widget.js`.
10. **Документация**: обновить `spec/features/lms-forms/spec.md` (раздел про типы полей и валидации).

## UI Components

Из UI Kit `@rosatom-ggr/ui-kit` (зарегистрированы глобально в `resources/js/app.js`):
- `RInput` — для ввода значений в `Form.vue` и `FormRenderer.vue`.
- `RCheckbox` — без изменений.
- `RButton`, `RCard` — без изменений.

Нативные элементы (как и раньше для типа поля):
- `<select>` — селектор «Тип валидации» (стиль повторяет существующий селектор `field.type` в `Form.vue`).

Новые компоненты не создаём.

## Verification

Команды выполняются по паттерну из `.cursor/rules/spec-continuation.mdc` (раздел «Command Execution Pattern», запуск через `${APP_NAME}_fpm`).

- Миграция: `php artisan migrate` (см. паттерн).
- Линт фронта: `npm run lint` или `npx eslint <files>`.
- Pest: `php artisan test --filter=Form` (если появятся новые тесты — добавим в `tests/Feature/Lms/`).
- Smoke-проверка вручную:
  1. Открыть `/lms-admin/{event}/forms/create`, добавить поле `text`, выбрать «СНИЛС» → сохранить.
  2. Открыть публичную форму, ввести некорректный СНИЛС → ошибка от сервера и от клиента.
  3. Ввести корректный СНИЛС → submission создаётся.
