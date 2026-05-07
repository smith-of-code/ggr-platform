# Фича: Конструктор форм (опросы, анкеты)

**Статус**: implemented

## Описание

Конструктор форм позволяет администратору создавать опросы, анкеты и формы обратной связи в рамках события. Формы поддерживают анонимное прохождение, встраивание на внешние сайты через iframe (embed), просмотр статистики ответов и автоматическое создание пользователей из ответов с последующей отправкой приглашений.

## Возможности

1. **Конструктор полей**: визуальный редактор с 10 типами полей
2. **Публичная ссылка**: уникальный slug для анонимного прохождения
3. **Embed (script / iframe)**: виджет через `<script>` или iframe для встраивания на сторонние сайты
4. **Статистика**: диаграммы ответов для select/radio/checkbox/rating; таблица всех ответов
5. **Создание пользователей**: из ответов анкеты (если есть поля ФИО + email) → автоматическое создание User + LmsProfile + LmsInvitation

## Связанные сущности

### Модели
- `App\Models\Lms\LmsForm` — форма/опрос
- `App\Models\Lms\LmsFormField` — поле формы
- `App\Models\Lms\LmsFormSubmission` — отправка (ответ целиком)
- `App\Models\Lms\LmsFormResponse` — значение одного поля

### Контроллеры
- `App\Http\Controllers\Lms\Admin\FormController` — CRUD + статистика + создание пользователей
- `App\Http\Controllers\Lms\FormPublicController` — публичное прохождение формы

### Страницы
- `Pages/Lms/Admin/Forms/Index.vue` — список форм с кол-вом ответов
- `Pages/Lms/Admin/Forms/Form.vue` — конструктор: основные настройки + маппинг + поля
- `Pages/Lms/Admin/Forms/Stats.vue` — статистика + embed-код + создание пользователей
- `Pages/Forms/Public.vue` — публичная страница прохождения (standalone, без layout)

## Схема БД

### lms_forms
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_event_id | FK → lms_events | cascade delete |
| title | string | Название формы |
| description | text | Описание |
| slug | string | Уникальный slug для публичного URL (unique-индекс БД; занятые slug удалённых форм НЕ освобождаются — см. Soft delete) |
| is_active | boolean | Активность |
| is_anonymous | boolean | Анонимное прохождение |
| allow_embed | boolean | Разрешить iframe-встраивание |
| create_users | boolean | Создавать пользователей из ответов |
| fio_field_key | string | Ключ поля ФИО (для маппинга) |
| email_field_key | string | Ключ поля email |
| phone_field_key | string | Ключ поля телефона |
| position_field_key | string | Ключ поля должности |
| thank_you_message | text | Сообщение после отправки |
| deleted_at | timestamp nullable | Soft delete (трейт `Illuminate\Database\Eloquent\SoftDeletes`); миграция `2026_05_07_180000_add_soft_deletes_to_lms_forms_table` |

### lms_form_fields
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_form_id | FK → lms_forms | cascade delete |
| key | string | Уникальный ключ поля (латиница) |
| label | string | Метка (название) |
| type | string(30) | Тип поля |
| validation | string(50) | Пресет валидации (СНИЛС/паспорт/ИНН/ОГРН/КПП/ИНН/дата рождения/индекс), nullable. Применяется только к `text`/`textarea`. См. фичу `form-validation`. |
| required | boolean | Обязательность |
| placeholder | text | Подсказка |
| options | json | Варианты ответов (для select/radio/checkbox) |
| position | integer | Порядок |

### Типы полей
| Тип | Описание |
|---|---|
| `text` | Однострочный текст |
| `textarea` | Многострочный текст |
| `email` | Email с валидацией |
| `phone` | Телефон |
| `number` | Число |
| `select` | Выпадающий список |
| `radio` | Радио-кнопки (один вариант) |
| `checkbox` | Чекбоксы (несколько вариантов) |
| `date` | Дата |
| `rating` | Рейтинг 1-5 (звёзды) |

### Пресеты валидации (колонка `validation`)

Каталог пресетов: `App\Services\Lms\Forms\FieldValidationPresets` (PHP) и `resources/js/constants/formFieldValidations.js` (JS).
Применяется только к полям типа `text` / `textarea`. Подробнее — `spec/features/form-validation/spec.md`.

| Ключ | Назначение |
|---|---|
| `snils` | СНИЛС (11 цифр + контрольная сумма) |
| `passport_series_rf` | Серия паспорта РФ (4 цифры) |
| `passport_number_rf` | Номер паспорта РФ (6 цифр) |
| `inn_personal` | ИНН физ. лица (12 цифр + 2 контрольные суммы) |
| `inn_company` | ИНН юр. лица (10 цифр + контрольная сумма) |
| `ogrn` | ОГРН (13 цифр + контрольная сумма) |
| `ogrnip` | ОГРНИП (15 цифр + контрольная сумма) |
| `kpp` | КПП (формат `NNNN##NNN`) |
| `birth_date` | Дата рождения (`ДД.ММ.ГГГГ`, не в будущем, ≤120 лет) |
| `postal_code_rf` | Почтовый индекс РФ (6 цифр) |

### lms_form_submissions
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_form_id | FK → lms_forms | cascade delete |
| user_id | FK → users | nullable (анонимные ответы) |
| ip_address | string(45) | IP-адрес отправителя |
| user_agent | string | User-Agent |
| user_created | boolean | Был ли создан пользователь из этого ответа |

### lms_form_responses
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_form_submission_id | FK → submissions | cascade delete |
| lms_form_field_id | FK → fields | cascade delete |
| value | text | Значение ответа |

## Роуты

### Админ (prefix: `/lms-admin/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET | `/forms` | FormController@index |
| GET | `/forms/create` | FormController@create |
| POST | `/forms` | FormController@store |
| GET | `/forms/{form}/edit` | FormController@edit |
| PUT | `/forms/{form}` | FormController@update |
| DELETE | `/forms/{form}` | FormController@destroy |
| POST | `/forms/{form}/duplicate` | FormController@duplicate |
| GET | `/forms/{form}/stats` | FormController@stats |
| POST | `/forms/{form}/create-users` | FormController@createUsersFromSubmissions |

Те же роуты зеркалятся под префиксом `/admin/tour-cabinet/lms/{event}` (group `admin.tour-cabinet.lms.*`) — используются из карточек форм на хабе портальной админки ЛК туров (см. `spec/features/tour-cabinet/spec.md`).

### Публичный
| Метод | URI | Действие |
|---|---|---|
| GET | `/forms/{slug}` | FormPublicController@show |
| POST | `/forms/{slug}/submit` | FormPublicController@submit |

### Widget API (CORS, без CSRF)
| Метод | URI | Действие |
|---|---|---|
| GET | `/api/forms/{slug}` | FormPublicController@apiShow |
| POST | `/api/forms/{slug}/submit` | FormPublicController@apiSubmit |
| OPTIONS | `/api/forms/{slug}/submit` | FormPublicController@apiCorsOptions |

Статический виджет: `/js/form-widget.js` (файл в `public/js/`).

## Ключевые workflow

### 1. Создание формы
1. Админ переходит «Формы» → «Создать»
2. Заполняет название, описание, настройки
3. Добавляет поля через конструктор (тип, метка, ключ, обязательность, варианты)
4. Включает «Создавать пользователей» → настраивает маппинг полей
5. Сохраняет → генерируется slug

### 2. Прохождение формы
1. Участник открывает URL `/forms/{slug}` (или через iframe / `<script>`-виджет на стороннем сайте)
2. Заполняет поля, нажимает «Отправить»
3. На клиенте применяется пресет валидации (если задан) — ошибка показывается мгновенно, отправка блокируется до исправления
4. POST → серверная валидация (формат email/phone + пресет `validation`) → сохранение submission + responses
5. Показывается сообщение благодарности

### 3. Просмотр статистики
1. Админ → «Формы» → «Статистика»
2. Вкладка «Ответы»: таблица всех ответов с пагинацией
3. Вкладка «Статистика»: по каждому select/radio/checkbox/rating полю — полосы с процентами

### 4. Embed (встраивание)
1. На странице статистики доступны два варианта встраивания:
   - **Script (виджет)** — рекомендуемый: `<script src=".../js/form-widget.js" data-form="slug"></script>`. Виджет загружается, запрашивает данные формы через API (`GET /api/forms/{slug}`), рендерит форму прямо на странице, отправляет ответы через `POST /api/forms/{slug}/submit`. Не требует iframe, адаптируется к ширине страницы.
   - **iFrame** — классический: `<iframe src=".../forms/{slug}" ...></iframe>`.
2. Код вставляется на внешний сайт
3. Пользователь проходит форму прямо на стороннем сайте

### 5. Создание пользователей из ответов
1. На странице статистики отображаются чекбоксы рядом с ответами (если `create_users = true`)
2. Админ выбирает ответы, нажимает «Создать пользователей»
3. Для каждого выбранного ответа:
   - Извлекаются ФИО, email, телефон, должность по заданному маппингу
   - Создаётся User (или находится существующий)
   - Создаётся LmsProfile для текущего события
   - Создаётся LmsInvitation для отправки ссылки регистрации
   - Submission помечается `user_created = true`

### 6. Дублирование формы
1. На списке форм (LMS Admin или хаб ЛК туров) админ нажимает «Дублировать» в карточке формы.
2. `POST /forms/{form}/duplicate` (`forms.duplicate`) → `FormController@duplicate` в одной транзакции:
   - создаётся новая `LmsForm` в том же событии: `title = "{original} — копия"` (с обрезкой до 255 символов), уникальный сгенерированный `slug` (`Str::slug(title) . '-' . Str::random(6)`), `is_active = false`, остальные booleans/nullable копируются;
   - копируются все `LmsFormField` (`key`, `label`, `type`, `validation`, `required`, `placeholder`, `options`, `position`).
3. **Не копируются** submissions, responses, статистика — у дубля счётчик ответов 0.
4. Редирект на список форм с flash «Форма продублирована». Дубль выключен (`is_active = false`) — редактор активирует его вручную после правки.

### 7. Удаление формы (soft delete)
1. На списке форм админ нажимает «Удалить» в карточке формы; нативный confirm с упоминанием количества ответов (`form.submissions_count`).
2. `DELETE /forms/{form}` (`forms.destroy`) → `FormController@destroy` → `$form->delete()`. Благодаря трейту `SoftDeletes` модели `LmsForm` это **мягкое удаление**: ставится `deleted_at = now()`, строка остаётся в БД. Связанные `lms_form_fields`, `lms_form_submissions`, `lms_form_responses` физически НЕ удаляются — FK cascade срабатывает только при `forceDelete()`, вызывается только из «Корзины форм» (см. workflow 8).
3. После soft-delete форма исчезает из всех админских списков (default scope `SoftDeletes` исключает её), а `FormPublicController::show/apiShow` отдаёт 404 (там обычный `where('slug', ...)`).
4. `FormController::checkSlug` использует `LmsForm::withTrashed()->where('slug', ...)` — занятый удалённой формой slug **не** считается свободным, чтобы не нарваться на DB unique constraint при создании новой формы.
5. **Внимание**: внешние ссылки по slug в портальной админке ЛК туров (`tour_cabinet_settings.dashboard_standard_form_slug`, `tour_cabinet_direction_cities.lms_form_slug`, `tour_cabinet_commerce_city_forms.lms_form_slug`) после удаления **не очищаются** автоматически — slug там хранится как текст, не FK. Соответствующие места считаются «без формы». См. `spec/features/tour-cabinet-forms-delete-copy/spec.md`.

### 8. Корзина форм (восстановление и полное удаление)

Точка входа: карточка «Корзина форм» на `/admin/settings` → страница `/admin/settings/forms-trash`.

| Метод | URI | Имя роута | Действие |
|---|---|---|---|
| GET | `/admin/settings/forms-trash` | `admin.settings.forms-trash.index` | `Admin\LmsFormTrashController@index` — пагинированный список soft-deleted форм с `event`, `fields_count`, `submissions_count`, `deleted_at`, отсортированный по `deleted_at desc`. |
| POST | `/admin/settings/forms-trash/{form}/restore` | `admin.settings.forms-trash.restore` | `LmsFormTrashController@restore` — `LmsForm::onlyTrashed()->findOrFail($form)->restore()`; форма возвращается в обычные списки и в публичный доступ (если `is_active`). Flash «Форма «X» восстановлена». |
| DELETE | `/admin/settings/forms-trash/{form}` | `admin.settings.forms-trash.destroy` | `LmsFormTrashController@forceDelete` — `findOrFail` через `onlyTrashed`, затем `forceDelete()`. FK cascade удаляет `lms_form_fields`, `lms_form_submissions`, `lms_form_responses`. Безвозвратно. Flash «Форма «X» удалена навсегда». |

UI (`Admin/Settings/FormsTrash.vue`) — таблица с колонками «Форма (title + slug)», «Событие LMS», «Полей», «Отправок», «Удалена», «Действия». Кнопки в каждой строке: **Восстановить** (`router.post`) и **Удалить навсегда** (`router.delete`). Перед обоими — нативный `confirm()` с явным текстом; для force-delete confirm перечисляет, сколько полей и отправок будет потеряно. Пагинация — стандартный `paginate(20)` payload (`forms.data`/`forms.links`). Пустое состояние — «Корзина пуста».

Восстановление через БД (`update lms_forms set deleted_at = null where id = X` или tinker `LmsForm::withTrashed()->find($id)->restore()`) по-прежнему работает, но штатный путь — UI «Корзина форм».
