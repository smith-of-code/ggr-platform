# ЛК участника конкурса в `/tour-cabinet` + этапы 1–3

**Статус**: решения зафиксированы; миграции и основной UI этапов 1–3 и админки — **в коде**; Excel — отдельно.  
**Связь**: `spec/features/tour-cabinet/spec.md`, `config/tour_cabinet.php`.

## Принятые решения

| Вопрос | Решение |
|--------|---------|
| Где живёт ЛК | **`http://…/tour-cabinet`** (тот же ЛК туров, `is_tour_cabinet_user`) |
| Откуда города | Записи из таблицы **`cities`** (модель `City`); какие города в каком направлении показывать — см. таблицу состава ниже |
| «White-label» формы | Уже в продукте: **`LmsForm`** + LMS Admin + публичные **`/forms/{slug}`** (`FormPublicController`); две формы этапа 1 — **два разных slug** одного LMS-события (`tour_cabinet.lms_event_slug`); приоритет: **`settings`** (группа `tour_cabinet`, ключи как в config), затем **`config/tour_cabinet.php`** / env; правка в админке: `/admin/tour-cabinet/forms` |

## Направления (этап 1, шаг 1)

Три фиксированных ключа (как `Tour::PROJECTS` / `Direction.project_key`):

| UI | `project_key` |
|----|----------------|
| Старт в Атомград | `start_atomgrad` |
| Атомы вкуса | `atoms_vkusa` |
| ЛЛР | `llr` |

## Города (этап 1, шаг 2)

- Справочник города — всегда **`cities`** (`id`, `name`, `slug`, …).
- Набор городов **по направлению** и флаг **«Нужно больше данных»** хранятся **отдельно** (не поле в `cities`), чтобы не смешивать конкурс с публичным каталогом.

## Предложение по хранению (БД)

### 1) `tour_cabinet_direction_cities` (состав направления + пометка)

| Колонка | Назначение |
|---------|------------|
| `id` | PK |
| `project_key` | `start_atomgrad` \| `atoms_vkusa` \| `llr` |
| `city_id` | FK → **`cities.id`** |
| `needs_more_data` | `boolean`, default `false` — показывать пометку **«Нужно больше данных»** |
| `position` | порядок в списке |
| timestamps | |

- Уникальность: **`unique(project_key, city_id)`**.
- Редактирование набора — **админка портала**: `/admin/tour-cabinet/direction-cities` (`admin.tour-cabinet.direction-cities.*`).

### 2) `tour_cabinet_contest_progress` (один участник — одна строка)

| Колонка | Назначение |
|---------|------------|
| `user_id` | FK → `users`, **unique** |
| `project_key` | выбранное направление, nullable до выбора |
| `selected_city_ids` | JSON: массив **1–3** значений `cities.id` (валидация в приложении) |
| `current_stage` | `1` \| `2` \| `3` |
| `stage3_text` | текст проверочного задания (этап 3), nullable |
| `stage3_video_url` | ссылка на видео (этап 3), nullable |
| timestamps | |

При необходимости позже добавить `stage2_completed_at`, `stage3_submitted_at`.

### 3) Этап 2 — вопросы и ответы (текст)

**`tour_cabinet_contest_stage2_questions`**

| Колонка | Назначение |
|---------|------------|
| `id` | PK |
| `body` | текст вопроса |
| `sort_order` | int |
| `is_active` | bool |
| опционально `project_key` | если вопросы разные по направлениям; иначе null = общие |
| timestamps | |

**`tour_cabinet_contest_stage2_answers`**

| Колонка | Назначение |
|---------|------------|
| `id` | PK |
| `user_id` | FK |
| `question_id` | FK на вопрос |
| `answer_text` | text |
| timestamps | |

- Уникальность ответа: **`unique(user_id, question_id)`** (один ответ на вопрос с возможностью обновления через upsert).

Админка: **GET/PATCH/POST/DELETE** `/admin/tour-cabinet/stage2-questions` (`admin.tour-cabinet.stage2-questions.*`).

### 4) Этап 1 — связь «город ↔ отправленная форма»

Ответы этапа 1 уже лежат в **`lms_form_submissions`** (+ `lms_form_responses`). Чтобы знать, **для какого города** отправлена анкета, ввести связку (избежать дублирования payload формы):

**`tour_cabinet_contest_city_submissions`**

| Колонка | Назначение |
|---------|------------|
| `user_id` | |
| `city_id` | FK → `cities` |
| `lms_form_submission_id` | FK → `lms_form_submissions.id` |
| timestamps | |

- Уникальность: **`unique(user_id, city_id)`** — один актуальный сабмит на город на участника (при повторной отправке — обновление ссылки или новая строка по политике версий; MVP — одна запись).

Выбор формы: slug **`standard`** vs **`more_data`** по флагу `needs_more_data` у строки `tour_cabinet_direction_cities` для пары (`project_key`, `city_id`).

### 5) Конфиг и админка форм

- `contest_stage1_form_slug_standard` — slug `LmsForm` для городов **без** «Нужно больше данных»;
- `contest_stage1_form_slug_more_data` — slug для городов **с** пометкой.

Источник значений: сначала переопределение в **`settings`** (группа `tour_cabinet`), иначе `config/tour_cabinet.php` / env. Обе формы в том же `lms_event_id`, что и `lms_event_slug`.

## Поток UX (кратко)

1. Участник в `/tour-cabinet` выбирает направление → подгружаются строки из **`tour_cabinet_direction_cities`** для `project_key` с join **`cities`**.
2. Выбирает 1–3 города → сохраняется `selected_city_ids` в **`tour_cabinet_contest_progress`**.
3. По каждому выбранному городу открывается `/forms/{slug}` с нужным slug; после submit создаётся запись в **`tour_cabinet_contest_city_submissions`**.
4. Этапы 2–3: UI на дашборде **`/tour-cabinet`** (вкладки блока конкурса); сохранение через **`POST /tour-cabinet/contest/stage-2`**, **`POST /tour-cabinet/contest/stage-3`** (middleware `tour-cabinet`). Ответы этапа 2 — `tour_cabinet_contest_stage2_answers`, этап 3 — поля `stage3_text` / `stage3_video_url` в `tour_cabinet_contest_progress`. Старые **`GET`** `/tour-cabinet/contest/stage-2` и `/stage-3` ведут на дашборд с якорем блока конкурса.

## Excel и прочее

Выгрузка заявок по `tour_cabinet_contest_progress` + join городов + сабмиты форм + ответы этапа 2/3 — детали колонок при реализации экспорта.

## Открытое на реализации

- Excel-выгрузка по прогрессу и ответам; при необходимости — ужесточение валидации URL видео этапа 3.
