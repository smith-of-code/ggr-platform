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
| template_file | text | nullable, legacy URL или JSON-массив шаблонов `[{name,path}]` |
| template_file_name | string | nullable, имя первого шаблона для обратной совместимости |
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
| participant_last_activity_at | timestamp | nullable, время последней активности участника (ответ/комментарий) |
| timestamps | | |

### lms_assignment_submission_reads
| Колонка | Тип | Ограничения |
|---|---|---|
| id | bigint | PK |
| lms_assignment_submission_id | FK → lms_assignment_submissions | cascade delete |
| user_id | FK → users | cascade delete |
| last_read_at | timestamp | nullable |
| timestamps | | |

Уникальность: `(lms_assignment_submission_id, user_id)`.

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
| GET | `/assignments/{assignment}/template-download?template=N` | AssignmentController@downloadTemplate |
| GET | `/assignments/{assignment}/tasks/{task}/template-download` | AssignmentController@downloadTaskTemplate |

### Админ (prefix: `/lms-admin/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET/POST/PUT/DELETE | `/assignments` (resource) | Admin\AssignmentController CRUD |
| POST | `/assignments/{assignment}/submissions/{submission}/review` | Admin\AssignmentController@review |
| POST | `/assignments/{assignment}/submissions/{submission}/comment` | Admin\AssignmentController@comment |
| POST | `/assignments/{assignment}/submissions/{submission}/mark-read` | Admin\AssignmentController@markRead |

Для ролей с ограниченным backoffice-доступом (`куратор-эксперт`, `тренер команды`, `трекер`, `эксперт`) доступно:
- `GET /lms-admin/{event}/assignments` (список заданий)
- `GET /lms-admin/{event}/assignments/{assignment}` (просмотр ответов и диалога по submissions)
- `POST /lms-admin/{event}/assignments/{assignment}/submissions/{submission}/review` (принять / на доработку / отклонить)
- `POST /lms-admin/{event}/assignments/{assignment}/submissions/{submission}/comment` (комментарий преподавателя)
- `POST /lms-admin/{event}/assignments/{assignment}/submissions/{submission}/mark-read` (отметка ветки прочитанной)

CRUD заданий для этих ролей недоступен (403).

## Непрочитанное в админке заданий

- На списке заданий (`Index.vue`) показывается бейдж с количеством submissions, где есть непрочитанная активность участника.
- Доступен фильтр «Только с новыми».
- На странице submissions (`Submissions.vue`) у каждой ветки участника показывается бейдж «Новое», а в обсуждении — метки «Новое» у сообщений участника после `last_read_at`.
- При открытии ветки вызывается `mark-read`, фиксируя `last_read_at` для текущего админа/эксперта. Запрос выполняется фоново через `axios`, без Inertia-перезагрузки списка, чтобы первая попытка открыть «новую» работу не закрывала раскрытую карточку и не сбрасывала плашку статуса.
- На странице submissions добавлены:
  - поиск по участнику (ФИО/email);
  - пагинация списка submissions (с сохранением активных фильтров).

## Дедлайн (API и отображение)

- В JSON для Inertia поле `deadline` у `LmsAssignment` сериализуется как **ISO 8601 в UTC с суффиксом `Z`** (`serializeDate`), чтобы в браузере однозначно определялся один и тот же момент времени.
- На фронте дедлайн форматируется через `resources/js/utils/lmsAssignmentDeadline.js` (`toLocaleString('ru-RU', …)` с **`timeZone: 'UTC'`**): админ-таблица, список и карточки участника, страница задания показывают **те же календарные дату и время, что в UTC в БД**, без сдвига в часовой пояс браузера (согласовано с полем дедлайна в админ-форме).
- В **админ-форме** задания (`Form.vue`) поле `datetime-local` заполняется через **`lmsDeadlineToDatetimeLocalUtc`**: часы и дата как в UTC из API (без сдвига в часовой пояс браузера), чтобы в поле отображалось то же «настенное» время, что хранится как UTC. При сохранении значение из поля передаётся как **`datetimeLocalToUtcIso`** (`…T09:00` → `…T09:00:00Z`) для однозначного разбора на бэкенде.

## Доступность заданий участнику

- В пользовательском списке `/lms/{event}/assignments` и на dashboard показываются только активные задания, которые добавлены в этапы программ, где участник записан на курс со статусом не `pending` и не `rejected`.
- Привязка учитывается как через legacy-поле `lms_course_stages.lms_assignment_id`, так и через блоки `lms_stage_blocks.lms_assignment_id`.
- Задания, созданные в админке, но ещё не добавленные в программу/этап, участнику не показываются.
- Прямой переход на страницу задания без записи на связанную программу запрещён (`403`).

## Ключевые workflow

### Создание задания (Admin)
1. Админ заполняет форму (`Form.vue`): title (обязательно), description, один или несколько файлов `template_files`, completion_mode, deadline, is_active.
2. `Admin\AssignmentController@store` валидирует и создаёт `LmsAssignment`.
3. Если `completion_mode` не передан — используется default `on_review`.

### Шаблоны задания

- У задания может быть несколько файлов-шаблонов.
- В админке `Pages/Lms/Admin/Assignments/Form.vue` поддержаны множественный выбор в медиапикере и кнопка «Добавить ещё файл».
- Для новых/обновлённых заданий `lms_assignments.template_file` хранит JSON-массив `[{name,path}]`.
- Старые задания с одиночным URL в `template_file` продолжают работать: backend нормализует их в один элемент `template_files`.
- Участник видит все шаблоны на странице задания и во встроенном задании внутри урока; скачивание идёт через `template-download?template=N`, чтобы сохранить исходное имя файла.

### Обновление задания (Admin)
1. Та же форма с предзаполненными данными.
2. `Admin\AssignmentController@update` валидирует и обновляет запись.
3. Если `completion_mode` не передан — сохраняется текущее значение.

### Отправка работы (Участник)
1. Участник отправляет ответ: text_content, link, files.
2. Файлы сохраняются в `storage/app/public/assignments/{assignment_id}/` или через presigned upload. Для заданий с task-ответами backend принимает вложенные поля `answers.*.files` и `answers.*.file_urls`. В `files` сохраняется массив `{name, path}`: `name` — исходное имя файла на компьютере участника, `path` — технический URL/путь хранения.
3. Используется `updateOrCreate` — один пользователь = одна submission на задание.
4. Статус сразу ставится `submitted`.

### Просроченные задания
1. На странице списка заданий участника (`Pages/Lms/Assignments/Index.vue`) карточка визуально выделяется красной рамкой/фоном и бейджем `Просрочено`, если `deadline` уже прошёл, а работа ещё не находится в статусе `submitted` или `approved`.
2. Над списком заданий участника доступен фильтр `Просрочено: X`; счётчик считается на backend по доступным участнику заданиям и учитывает активный поиск.
3. На странице задания и во встроенном задании уже отображается красная отметка `Просрочено` рядом с дедлайном.

### Обновление работы (Участник, resubmit)
1. Участник обновляет существующую submission.
2. Файлы дополняются (append), не заменяются; повторное сохранение draft/submit без нового файла не затирает уже сохранённые вложения task-ответа.
3. Статус ставится `resubmitted`.

### Рецензирование (Admin)
1. Рецензент выносит решение: approve / revision / reject.
2. Может приложить комментарий и файлы к решению.
3. Создаётся запись `LmsAssignmentReview` с файлами в формате `[{name, path}]`.
4. Статус submission обновляется: approve→approved, revision→revision, reject→rejected.
5. На странице админского списка работ отправка решений и комментариев сохраняет текущую прокрутку, чтобы проверяющий оставался на том же участнике после обновления данных.
6. В карточке проверки показывается контекст участника: программы, к которым привязано текущее задание и где участник записан, а также данные `LmsProfile` (`project_description`, город, должность, организация, направление/факультет). Эти данные видны только в админском интерфейсе проверки.
7. Над списком работ доступны фильтры со счётчиками: `Принято`, `На проверке`, `На доработке`, `Новое`, `Просрочено`. Счётчики считаются по текущему заданию и учитывают активный поиск по участнику; фильтр `Новое` использует unread-логику по `participant_last_activity_at` и `LmsAssignmentSubmissionRead`.
8. Если дедлайн задания прошёл, submissions со статусом не `submitted`, не `approved` и не `resubmitted` подсвечиваются красным и получают бейдж `Просрочено`; фильтр `Просрочено` показывает только такие работы.

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

### [ИСПРАВЛЕНО] Периодические «пустые ответы» при отправке заданий с задачами
- **Проблема**: на фронте `Pages/Lms/Assignments/Show.vue` при submit/draft использовался `useForm.post` с передачей `FormData` через `options.data`, из-за чего собранный payload с `answers[...]` мог не уходить в запрос.
- **Исправление**: отправка submit/draft переведена на `router.post(..., formData, { forceFormData: true })` с явной передачей собранного `FormData`; ошибки валидации прокидываются обратно в `form.setError`.
