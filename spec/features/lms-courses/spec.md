# Фича: LMS — Система курсов

**Статус**: implemented

## Описание

Многоуровневая система курсов с модулями, мультиблочными этапами (stages), записью на курсы (enrollments), поддержкой SCORM, контролем последовательного прохождения, ролевым доступом и копированием модулей/этапов между курсами.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsCourse`
- `App\Models\Lms\LmsCourseModule`
- `App\Models\Lms\LmsCourseStage`
- `App\Models\Lms\LmsStageBlock`
- `App\Models\Lms\LmsCourseEnrollment`
- `App\Models\Lms\LmsStageProgress`

### Контроллеры
- `App\Http\Controllers\Lms\CourseController` — участник: список, просмотр, запись
- `App\Http\Controllers\Lms\StageController` — участник: прохождение этапов, SCORM, heartbeat
- `App\Http\Controllers\Lms\Admin\CourseController` — админ: CRUD курсов, загрузка SCORM, поиск модулей/этапов
- `App\Http\Controllers\Lms\Admin\EnrollmentController` — админ: управление записями

### Страницы
- `Pages/Lms/Courses/Index.vue`, `Show.vue`, `Stage.vue`
- `Pages/Lms/Admin/Courses/Index.vue`, `Form.vue`, `StageEditor.vue`, `SearchRefModal.vue`
- `Pages/Lms/Admin/Enrollments/Index.vue`

### Параметр `{course}` в URL (LMS)

В маршрутах под префиксом `lms/{event}/…` и `lms-admin/{event}/…` сегмент `{course}` резолвится в `LmsCourse` так: если значение целиком состоит из цифр — по `id` в рамках `lms_event_id` события; иначе — по `slug`. Событие берётся из уже срезолвленного `LmsEvent` или, если в `Route::bind('course')` параметр `event` ещё строка — загружается `LmsEvent` по `slug` из URI. В маршрутах без параметра `event` (например, toggle курса в админке портала) допускается только числовой `id`.

Реализация: `Route::bind('course', …)` в `AppServiceProvider`.

## Схема БД

### lms_courses
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_event_id | FK → lms_events | cascade delete |
| title | string | Название |
| slug | string | URL-слаг |
| description | text | Описание |
| image | string | Путь к обложке |
| sequential | boolean | Последовательное прохождение |
| is_active | boolean | Активность |
| requires_approval | boolean | Требуется одобрение для записи |
| position | integer | Порядок сортировки |

### lms_course_modules
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_course_id | FK → lms_courses | cascade delete |
| title | string | Название модуля |
| description | text | Описание |
| position | integer | Порядок |
| available_from | timestamp | Дата открытия |
| available_to | timestamp | Дата закрытия |
| unlock_type | string(20) | Тип разблокировки (date) |
| source_module_id | FK → self | nullable, для копирования |

### lms_course_stages
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_course_id | FK → lms_courses | cascade delete |
| lms_course_module_id | FK → modules | nullable |
| title | string | Название этапа |
| type | enum / CHECK | Тип первого блока (content, scorm, test, assignment, video, workshop, city_meeting, curator_meeting, file) |
| content | text | Контент первого блока (обратная совместимость) |
| scorm_package | string | SCORM URL |
| lms_test_id | FK | nullable |
| lms_assignment_id | FK | nullable |
| lms_video_id | FK | nullable |
| is_locked | boolean | Заблокирован |
| position | integer | Порядок |
| available_from | timestamp | Дата открытия |
| duration_minutes | integer | Длительность |
| source_stage_id | FK → self | nullable, для копирования |

### lms_stage_blocks
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_course_stage_id | FK → stages | cascade delete |
| type | string(20) | content, scorm, test, assignment, video, workshop, city_meeting, curator_meeting, file |
| content | text | HTML-контент или ID связанной сущности |
| scorm_package | string | SCORM URL (для type=scorm) |
| lms_test_id | FK | nullable (для type=test) |
| lms_assignment_id | FK | nullable (для type=assignment) |
| lms_video_id | FK | nullable (для type=video) |
| position | integer | Порядок блока внутри этапа |

### lms_course_enrollments
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_course_id | FK → lms_courses | cascade delete |
| user_id | FK → users | cascade delete |
| status | varchar(20) | enrolled, in_progress, completed, pending, rejected |
| completed_at | timestamp | nullable |
| reviewed_at | timestamp | nullable |
| reviewed_by | FK → users | nullable |

### lms_stage_progress
| Колонка | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| lms_course_stage_id | FK → stages | cascade delete |
| user_id | FK → users | cascade delete |
| status | enum | not_started, in_progress, completed |
| scorm_data | json | Данные SCORM-сессии |
| score | integer | Баллы |
| watched_seconds | integer | Просмотрено видео (секунды) |
| completed_at | timestamp | nullable |

## Мультиблочные этапы

Каждый этап может содержать несколько блоков контента разных типов. Блоки хранятся в таблице `lms_stage_blocks` и отображаются последовательно.

### Типы блоков
| Тип | Описание | Поле контента |
|---|---|---|
| `content` | HTML-текст (WYSIWYG-редактор) | `content` |
| `scorm` | SCORM-пакет (iframe) | `scorm_package` |
| `test` | Ссылка на тест | `lms_test_id` (id в `content`) |
| `assignment` | Ссылка на задание | `lms_assignment_id` (id в `content`) |
| `video` | Встроенное видео (YouTube, Rutube) | `lms_video_id` (id в `content`) |
| `workshop` | Живой воркшоп | `content` (описание / ссылка) |
| `city_meeting` | Встреча города | `content` |
| `curator_meeting` | Встреча с куратором | `content` |
| `file` | Файл для скачивания | `content` — публичный URL после загрузки (`POST …/stage-block-file-upload`) |

### Как работает

1. **Админка (`Form.vue`, `StageEditor.vue`)**: этапы модуля и этапы без модуля выводятся списком; **после каждого этапа** есть кнопка «Добавить этап» (вставка нового этапа сразу ниже, без прокрутки к концу страницы). В `StageEditor` у этапа — список блоков, кнопка «Добавить блок» создаёт новый блок.
2. **Сохранение (CourseController)**: при сохранении курса для каждого этапа создаются записи в `lms_stage_blocks`. Первый блок дублируется в поля самого `lms_course_stages` для обратной совместимости.
3. **Отображение (Stage.vue)**: бэкенд передаёт массив `blocks` с загруженными связями. Vue рендерит каждый блок в соответствии с его типом.

### Обратная совместимость

Миграция автоматически создаёт по одному блоку для каждого существующего этапа. Если у этапа нет блоков, фронтенд создаёт один блок из полей `type`/`content` самого этапа.

## Модульная структура

```
Курс
├── Модуль 1 (available_from, available_to)
│   ├── Этап 1 (блоки: контент + видео)
│   └── Этап 2 (блоки: тест)
├── Модуль 2
│   └── Этап 3 (блоки: контент + SCORM + задание)
└── Этапы без модуля
    └── Этап 4 (блоки: контент)
```

Модули имеют даты доступности (`available_from`, `available_to`), тип разблокировки (`unlock_type`). Этапы привязаны к модулю через `lms_course_module_id` (nullable — этап без модуля).

## Копирование модулей и этапов

### Поиск
- `GET /lms-admin/{event}/search-modules?q=...` — поиск модулей по названию (с этапами и блоками)
- `GET /lms-admin/{event}/search-stages?q=...` — поиск этапов по названию (с блоками)

### Механизм
1. Кнопка «Найти модуль из другого курса» в форме модуля (появляется при пустом названии)
2. Кнопка «Найти этап из другого курса» в StageEditor (появляется при пустом названии)
3. Модальное окно `SearchRefModal.vue` — поиск с debounce 300ms, подтверждение копирования
4. При копировании: title, description, даты, все блоки переносятся в форму
5. Сохраняется `source_module_id` / `source_stage_id` — ссылка на оригинал

### Ограничение
Поиск ведётся только среди курсов **текущего события**.

## Ролевой доступ

Курс может быть ограничен определёнными ролями через M2M-таблицу `lms_course_role_access`. Если ни одна роль не выбрана — курс доступен всем.

## SCORM-интеграция

### Загрузка SCORM-пакета (админ)

| Шаг | Описание |
|-----|----------|
| 1 | Админ выбирает тип блока `SCORM` в StageEditor |
| 2 | Появляется дропзона для загрузки ZIP (до 100 МБ) |
| 3 | `POST /lms-admin/{event}/scorm-upload` → `AdminCourseController::uploadScorm()` |
| 4 | Проверка `imsmanifest.xml` в архиве (иначе 422) |
| 5 | ZIP → `public/scorm/{event-slug}/{hash}/` |
| 6 | Из `imsmanifest.xml` извлекается launch-файл |
| 7 | URL сохраняется в `LmsStageBlock.scorm_package` |

### Прохождение SCORM (участник)

На странице этапа SCORM-контент в `<iframe>`. На `window` регистрируются API-адаптеры:

| API | Стандарт | Методы |
|-----|----------|--------|
| `window.API` | SCORM 1.2 | LMSInitialize, LMSFinish, LMSGetValue, LMSSetValue, LMSCommit |
| `window.API_1484_11` | SCORM 2004 | Initialize, Terminate, GetValue, SetValue, Commit |

Данные сохраняются через `POST /lms/{event}/courses/{course}/stages/{stage}/scorm` в `LmsStageProgress.scorm_data` (JSON).

## Роуты

### Участник (prefix: `/lms/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET | `/courses` | CourseController@index |
| GET | `/courses/{course}` | CourseController@show |
| POST | `/courses/{course}/enroll` | CourseController@enroll |
| GET | `/courses/{course}/stages/{stage}` | StageController@show |
| POST | `/courses/{course}/stages/{stage}/complete` | StageController@complete |
| POST | `/courses/{course}/stages/{stage}/scorm` | StageController@scormData |
| POST | `/courses/{course}/stages/{stage}/heartbeat` | StageController@heartbeat |

### Админ (prefix: `/lms-admin/{event}`)
| Метод | URI | Действие |
|---|---|---|
| GET/POST/PUT/DELETE | `/courses` (resource) | Admin\CourseController CRUD |
| GET | `/search-modules` | Admin\CourseController@searchModules |
| GET | `/search-stages` | Admin\CourseController@searchStages |
| POST | `/scorm-upload` | Admin\CourseController@uploadScorm |
| GET/PATCH | `/enrollments` | Admin\EnrollmentController |

## Ключевые workflow

1. **Создание курса**: заполнение основной информации → добавление модулей → добавление этапов с блоками → сохранение
2. **Запись на курс**: участник записывается → одобрение (если requires_approval) → статус enrolled
3. **Прохождение**: enrolled → in_progress (при первом открытии этапа) → completed (все этапы пройдены)
4. **Прохождение этапа**: last_completed_at → mark complete (контент/видео) или автозавершение (тест/задание). При включённой геймификации курса баллы за действие `module_complete` начисляются, когда **все этапы одного модуля** (или один этап без модуля) получили `LmsStageProgress` completed — см. `lms-gamification/spec.md`.
5. **Видео-этап**: таймер просмотра, heartbeat каждые 15с, завершение при 90% просмотра

## UX: заявки на программы (админка)

- В `Pages/Lms/Admin/Enrollments/Index.vue` для поля «Проект / Идея» доступен просмотр полного текста через кнопку с иконкой «глаз».
- Кнопка открывает модальное окно с деталями участника и полным `project_description` без обрезки.
- В фильтре «Программа» в заявках названия опций отображаются многострочно (без принудительного `truncate`), чтобы длинные названия курсов были читаемы.
