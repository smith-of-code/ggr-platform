# Фича: LMS — Система заданий

**Статус**: reviewed

## Описание

Задания с двумя режимами завершения (on_submit / on_review), отправкой работ (текст, ссылки, файлы) и системой рецензирования.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsAssignment`
- `App\Models\Lms\LmsAssignmentSubmission`
- `App\Models\Lms\LmsAssignmentReview`

### Контроллеры
- `App\Http\Controllers\Lms\AssignmentController` — участник: список, просмотр, отправка, обновление
- `App\Http\Controllers\Lms\Admin\AssignmentController` — админ: CRUD, рецензирование

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
| files | json | nullable |
| decision | enum(approve, revision, reject) | NOT NULL |
| timestamps | | |

## Роуты

### Участник (prefix: `/lms/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET | `/assignments` | AssignmentController@index |
| GET | `/assignments/{assignment}` | AssignmentController@show |
| POST | `/assignments/{assignment}/submit` | AssignmentController@submit |
| PATCH | `/assignments/{assignment}/submissions/{submission}` | AssignmentController@update |

### Админ (prefix: `/lms/{event}/admin`)
| Метод | URI | Действие |
|---|---|---|
| GET/POST/PUT/DELETE | `/assignments` (resource) | Admin\AssignmentController CRUD |
| POST | `/assignments/{assignment}/submissions/{submission}/review` | Admin\AssignmentController@review |

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
2. Создаётся запись `LmsAssignmentReview`.
3. Статус submission обновляется: approve→approved, revision→revision, reject→rejected.

### Жизненный цикл статусов submission
```
draft → submitted → [revision ↔ resubmitted → submitted] → approved / rejected
```

## Известные проблемы и исправления

### [ИСПРАВЛЕНО] completion_mode = null при сохранении
- **Проблема**: валидация в `Admin\AssignmentController` допускала `nullable` для `completion_mode`. Когда форма не передавала значение, `null` явно вставлялся в SQL, перекрывая DB-default `on_review`. PostgreSQL выдавал NOT NULL violation.
- **Исправление**: валидация заменена на `sometimes|string|in:on_submit,on_review`. В `store` добавлен fallback `$validated['completion_mode'] ??= 'on_review'`, в `update` — `??= $assignment->completion_mode`.

### [ОТКРЫТО] Статус `resubmitted` отсутствует в DB enum
- **Проблема**: `AssignmentController@update` (участник) устанавливает статус `resubmitted`, но DB enum для `status` допускает только: `draft`, `submitted`, `revision`, `approved`, `rejected`.
- **Влияние**: при обновлении submission участником PostgreSQL может выдать ошибку enum constraint.
- **Решение**: либо добавить `resubmitted` в enum миграцией, либо использовать `submitted` вместо `resubmitted`.
