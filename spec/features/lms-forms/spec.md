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
| slug | string | Уникальный slug для публичного URL |
| is_active | boolean | Активность |
| is_anonymous | boolean | Анонимное прохождение |
| allow_embed | boolean | Разрешить iframe-встраивание |
| create_users | boolean | Создавать пользователей из ответов |
| fio_field_key | string | Ключ поля ФИО (для маппинга) |
| email_field_key | string | Ключ поля email |
| phone_field_key | string | Ключ поля телефона |
| position_field_key | string | Ключ поля должности |
| thank_you_message | text | Сообщение после отправки |

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
| GET | `/forms/{form}/stats` | FormController@stats |
| POST | `/forms/{form}/create-users` | FormController@createUsersFromSubmissions |

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
