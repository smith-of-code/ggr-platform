# Задачи: form-validation

## T1. Миграция и модель

- **Goal**: добавить колонку `validation` (string, 50, nullable) в `lms_form_fields`; включить в `fillable` модели.
- **Scope**:
  - `database/migrations/<timestamp>_add_validation_to_lms_form_fields.php`
  - `app/Models/Lms/LmsFormField.php`
- **DoD**: миграция up/down работает; `LmsFormField::create([..., 'validation' => 'snils'])` сохраняет значение.
- **Verify**: `php artisan migrate` и `php artisan tinker` (см. паттерн в plan.md → Verification).

## T2. PHP-каталог пресетов

- **Goal**: единый источник правды для правил валидации на бэке.
- **Scope**:
  - `app/Services/Lms/Forms/FieldValidationPresets.php` (методы `rule(string $key): ?Closure`, `message(string $key): string`, `available(): array<string,string>`).
- **DoD**: реализованы пресеты `snils`, `passport_series_rf`, `passport_number_rf`, `inn_personal`, `inn_company`, `ogrn`, `ogrnip`, `kpp`, `birth_date`, `postal_code_rf` с контрольными суммами там, где они применимы.
- **Verify**: добавить минимальный Pest-тест в `tests/Unit/FieldValidationPresetsTest.php` (валидные/невалидные значения каждого пресета).

## T3. Админ-контроллер: приём `validation`

- **Goal**: `FormController` принимает и сохраняет `validation` для каждого поля.
- **Scope**:
  - `app/Http/Controllers/Lms/Admin/FormController.php` (методы `validateForm`, `syncFields`).
- **DoD**: `fields.*.validation` валидируется как `nullable|string|in:<ключи из FieldValidationPresets::available()>`; значение пробрасывается в `LmsFormField::create`.
- **Verify**: ручной POST через UI; проверить запись в БД.

## T4. Публичная валидация (сервер)

- **Goal**: применить пресет к `answers.{key}` с русскими сообщениями.
- **Scope**:
  - `app/Http/Controllers/Lms/FormPublicController.php` (методы `buildPublicFormAnswerRules`, `publicFormValidationMessages`).
- **DoD**: при наличии `field->validation` в правила добавляется `Closure`/regex из `FieldValidationPresets`; сообщение берётся из сервиса; не ломается логика email/phone (они остаются как сейчас).
- **Verify**: `tests/Feature/Lms/FormPublicSubmitValidationTest.php` — кейсы для 2-3 пресетов (валидное/невалидное).

## T5. Прокидывание `validation` во фронт

- **Goal**: значение `validation` доступно в Inertia/JSON-ответах.
- **Scope**:
  - `app/Http/Controllers/Lms/FormPublicController.php` (`show`, `apiShow`) — добавить `validation` в маппинг полей.
  - `app/Http/Controllers/Lms/Admin/FormController.php` (`edit`) — `with('fields')` уже отдаёт всё, проверить, что `validation` попадает в payload.
- **DoD**: на странице `/forms/{slug}` и в `GET /api/forms/{slug}` поле `fields[].validation` присутствует.
- **Verify**: ручная проверка через DevTools / `curl /api/forms/{slug}`.

## T6. JS-каталог пресетов

- **Goal**: зеркальный список валидаций для фронта.
- **Scope**:
  - `resources/js/constants/formFieldValidations.js` — массив `{ value, label, regex?, validate? (fn), message }`.
- **DoD**: набор ключей идентичен PHP-каталогу; для пресетов с контрольными суммами реализована функция `validate(value)`.
- **Verify**: импорт в `Form.vue` и `FormRenderer.vue` без ошибок (`npm run lint`).

## T7. Конструктор форм: селектор валидации

- **Goal**: в админке у поля `text`/`textarea` появляется выпадающий список «Тип валидации».
- **Scope**:
  - `resources/js/Pages/Lms/Admin/Forms/Form.vue` — селектор внутри блока поля; включён в инициализацию `form.fields`, `addField()`, `submit()`.
- **DoD**: при сохранении формы `validation` передаётся на бэк; при редактировании выбранное значение восстанавливается.
- **Verify**: ручная проверка в браузере.

## T8. Клиентская валидация в `FormRenderer.vue`

- **Goal**: показывать ошибку валидации до отправки формы.
- **Scope**:
  - `resources/js/Components/FormRenderer.vue` — функция `validateField(field, value)`; вывод ошибки рядом с полем; блокировка submit при наличии ошибок.
- **DoD**: для пресета `snils` ввод `123` → красная подсказка; ввод `12345678901` (валидный) → ошибки нет.
- **Verify**: ручная проверка на `/forms/{slug}`.

## T9. Клиентская валидация в `form-widget.js`

- **Goal**: повторить клиентскую логику в standalone-виджете.
- **Scope**:
  - `public/js/form-widget.js` — каталог пресетов (inline, без бандлера) + проверка перед `fetch` submit.
- **DoD**: при `data-form="…"` и поле с `validation: 'snils'` некорректный ввод не отправляет форму; показывает ошибку.
- **Verify**: HTML-страница с `<script src="…/js/form-widget.js" data-form="…">` локально.

## T10. Обновить spec фичи lms-forms

- **Goal**: отразить новую колонку и каталог пресетов в исторической документации.
- **Scope**:
  - `spec/features/lms-forms/spec.md` (раздел «lms_form_fields», новая строка `validation`; ссылка на `spec/features/form-validation/spec.md`).
- **DoD**: в spec есть запись о колонке и таблица пресетов либо ссылка на каталог.
- **Verify**: визуально (markdown).
