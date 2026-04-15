# Фича: LMS — Система заданий

**Статус**: reviewed

## Описание

Задания с двумя режимами завершения (on_submit / on_review), отправкой работ (текст, ссылки, файлы), системой рецензирования и диалогом между участником и преподавателем.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsAssignment`
- `App\Models\Lms\LmsAssignmentSubmission`
- `App\Models\Lms\LmsAssignmentReview`
- `App\Models\Lms\LmsAssignmentComment`

### Контроллеры
- `App\Http\Controllers\Lms\AssignmentController` — участник: список, просмотр, отправка, обновление, комментарии
- `App\Http\Controllers\Lms\Admin\AssignmentController` — админ: CRUD, рецензирование, комментарии

### Страницы
- `Pages/Lms/Assignments/Index.vue`, `Show.vue`
- `Pages/Lms/Admin/Assignments/Index.vue`, `Form.vue`, `Submissions.vue`

## Схема БД

### lms_assignments
| Колонка | Тип | Ограничения |
|---|---|---|
| id | bigint | PK |
| lms_event_id | FK → lms_events | cascade delete |
| title | string | NOT NULL |
| description | text | nullable |
| template_file | string | nullable |
| completion_mode | enum(on_submit, on_review) | NOT NULL, default `on_review` |
| deadline | timestamp | nullable |
| is_active | boolean | default true |
| timestamps | | |

### lms_assignment_submissions
| Колонка | Тип | Ограничения |
|---|---|---|
| id | bigint | PK |
| lms_assignment_id | FK → lms_assignments | cascade delete |
| user_id | FK → users | cascade delete |
| text_content | text | nullable |
| link | string | nullable |
| files | json | nullable |
| status | enum(draft, submitted, revision, approved, rejected) | default `draft` |
| timestamps | | |

### lms_assignment_reviews
| Колонка | Тип | Ограничения |
|---|---|---|
| id | bigint | PK |
| lms_assignment_submission_id | FK → lms_assignment_submissions | cascade delete |
| reviewer_id | FK → users | cascade delete |
| comment | text | nullable |
| files | json | nullable, массив `{name, path}` |
| decision | enum(approve, revision, reject) | NOT NULL |
| timestamps | | |

### lms_assignment_comments
| Колонка | Тип | Ограничения |
|---|---|---|
| id | bigint | PK |
| lms_assignment_submission_id | FK → lms_assignment_submissions | cascade delete |
| user_id | FK → users | cascade delete |
| text | text | NOT NULL |
| files | json | nullable, массив `{name, path}` |
| timestamps | | |

## Роуты

### Участник (prefix: `/lms/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET | `/assignments` | AssignmentController@index |
| GET | `/assignments/{assignment}` | AssignmentController@show |
| POST | `/assignments/{assignment}/submit` | AssignmentController@submit |
| POST | `/assignments/{assignment}/comment` | AssignmentController@comment |
| PATCH | `/assignments/{assignment}/submissions/{submission}` | AssignmentController@update |

### Админ (prefix: `/lms-admin/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET/POST/PUT/DELETE | `/assignments` (resource) | Admin\AssignmentController CRUD |
| POST | `/assignments/{assignment}/submissions/{submission}/review` | Admin\AssignmentController@review |
| POST | `/assignments/{assignment}/submissions/{submission}/comment` | Admin\AssignmentController@comment |

## Дедлайн (API и отображение)

- В JSON для Inertia поле `deadline` у `LmsAssignment` сериализуется как **ISO 8601 в UTC с суффиксом `Z`** (`serializeDate`), чтобы в браузере однозначно определялся один и тот же момент времени.
- На фронте дедлайн форматируется через `resources/js/utils/lmsAssignmentDeadline.js` (`toLocaleString('ru-RU', …)` с датой и временем): админ-таблица (краткий вид месяца), список и карточки участника, страница задания — **одинаковые часы в локальном времени браузера** при одной строке из API.
- В админ-форме поле `datetime-local` заполняется функцией `lmsDeadlineToDatetimeLocal` (локальное представление того же момента, без обрезки ISO по `slice`).

## Ключевые workflow

### Создание задания (Admin)
1. Админ заполняет форму (`Form.vue`): title (обязательно), description, template_file, completion_mode, deadline, is_active.
2. `Admin\AssignmentController@store` валидирует и создаёт `LmsAssignment`.
3. Если `completion_mode` не передан — используется default `on_review`.

### Обновление задания (Admin)
1. Та же форма с предзаполненными данными.
2. `Admin\AssignmentController@update` валидирует и обновляет запись.
3. Если `completion_mode` не передан — сохраняется текущее значение.

### Отправка работы (Участник)
1. Участник отправляет ответ: text_content, link, files.
2. Файлы сохраняются в `storage/app/public/assignments/{assignment_id}/`.
3. Используется `updateOrCreate` — один пользователь = одна submission на задание.
4. Статус сразу ставится `submitted`.

### Обновление работы (Участник, resubmit)
1. Участник обновляет существующую submission.
2. Файлы дополняются (append), не заменяются.
3. Статус ставится `resubmitted`.

### Рецензирование (Admin)
1. Рецензент выносит решение: approve / revision / reject.
2. Может приложить комментарий и файлы к решению.
3. Создаётся запись `LmsAssignmentReview` с файлами в формате `[{name, path}]`.
4. Статус submission обновляется: approve→approved, revision→revision, reject→rejected.

### Диалог по работе (Участник и Преподаватель)

Между участником и преподавателем ведётся свободный диалог в рамках submission:

1. **Комментарий участника**: `POST /assignments/{assignment}/comment` — текст + до 5 файлов (до 20 МБ каждый).
2. **Комментарий преподавателя**: `POST /lms-admin/{event}/assignments/{assignment}/submissions/{submission}/comment` — текст + до 5 файлов.
3. Файлы сохраняются в `storage/app/public/assignment-comments/{assignment_id}/` в формате `[{name, path}]`.
4. Комментарии доступны обеим сторонам: участник видит в `Show.vue`, преподаватель — в `Submissions.vue`.

#### Как отображается диалог

В UI все сообщения (reviews + comments) объединяются в единую хронологическую ленту:
- **Рецензии** (amber) — с бейджем решения (Принято / На доработку / Отклонено)
- **Комментарии участника** (blue) — с меткой «участник»
- **Комментарии преподавателя** (rosatom/фиолетовый) — с меткой «преподаватель»

Каждое сообщение показывает: имя автора, текст, прикреплённые файлы (со ссылками на скачивание), дату.

### Жизненный цикл статусов submission
```
draft → submitted → [revision ↔ resubmitted → submitted] → approved / rejected
                     ↕ комментарии в любой момент ↕
```

## Известные проблемы и исправления

### [ИСПРАВЛЕНО] completion_mode = null при сохранении
- **Проблема**: валидация в `Admin\AssignmentController` допускала `nullable` для `completion_mode`. Когда форма не передавала значение, `null` явно вставлялся в SQL, перекрывая DB-default `on_review`. PostgreSQL выдавал NOT NULL violation.
- **Исправление**: валидация заменена на `sometimes|string|in:on_submit,on_review`. В `store` добавлен fallback `$validated['completion_mode'] ??= 'on_review'`, в `update` — `??= $assignment->completion_mode`.

### [ОТКРЫТО] Статус `resubmitted` отсутствует в DB enum
- **Проблема**: `AssignmentController@update` (участник) устанавливает статус `resubmitted`, но DB enum для `status` допускает только: `draft`, `submitted`, `revision`, `approved`, `rejected`.
- **Влияние**: при обновлении submission участником PostgreSQL может выдать ошибку enum constraint.
- **Решение**: либо добавить `resubmitted` в enum миграцией, либо использовать `submitted` вместо `resubmitted`.
