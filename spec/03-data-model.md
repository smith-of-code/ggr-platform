# Модель данных

## Основные модели

### User

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| name | string | legacy, данные мигрированы в last_name + first_name |
| last_name | string | добавлено миграцией 2026_03_19_100000 |
| first_name | string | добавлено миграцией 2026_03_19_100000 |
| patronymic | string | nullable |
| email | string unique | |
| phone | string | nullable |
| email_verified_at | timestamp | |
| password | string | cast: hashed |
| remember_token | string | |
| timestamps | | |

**Связи**: `socialAccounts()` HasMany SocialAccount (LmsProfile привязан через user_id)

---

### SocialAccount

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| user_id | FK → users | cascade |
| provider | string(30) | `vkontakte`, `yandex` |
| provider_id | string | ID пользователя у провайдера |
| token | text | nullable, cast: encrypted |
| refresh_token | text | nullable, cast: encrypted |
| expires_at | timestamp | nullable |
| timestamps | | |
| **unique** | (provider, provider_id) | |
| **unique** | (user_id, provider) | |

**Связи**: `user()` BelongsTo User

---

### City

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| name | string | |
| slug | string unique | |
| description | text | |
| infrastructure | json | cast: array |
| image | string | |
| position | integer | |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: `tours()` BelongsToMany Tour через city_tour

---

### Tour

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | |
| description | text | |
| start_city | string | |
| duration | string | |
| project | string | PROJECTS — константы модели |
| participation_type | string | PARTICIPATION_TYPES — константы модели |
| season | string | SEASONS — константы модели |
| for_children | boolean | |
| for_foreigners | boolean | |
| closed_city | boolean | |
| group_size | string | |
| min_age | integer | |
| price_from | decimal(10,2) | cast: decimal:2 |
| program_pdf | string | |
| memo_pdf | string | |
| departure_info | text | |
| accommodation_info | text | |
| conditions | text | |
| cost_info | text | |
| application_info | text | |
| bchp_participant | boolean | |
| is_featured | boolean | |
| image | string | |
| position | integer | |
| is_active | boolean | |
| timestamps | | |

**Связи**: `cities()` BelongsToMany City, `departures()` HasMany TourDeparture, `media()` morphMany Media

---

### TourDeparture

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| tour_id | FK → tours | cascade |
| start_date | date | cast: date |
| end_date | date | cast: date |
| price_per_person | decimal | cast: decimal:2 |
| available_places | integer | |
| timestamps | | |

**Связи**: `tour()` BelongsTo Tour

---

### Application

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| type | string | TYPES: tour, research, program_info |
| name | string | |
| email | string | |
| phone | string | |
| data | json | cast: array |
| tour_id | FK → tours | nullable, nullOnDelete |
| tour_departure_id | FK → tour_departures | nullable, nullOnDelete |
| status | string | |
| timestamps | | |

**Связи**: `tour()` BelongsTo Tour, `tourDeparture()` BelongsTo TourDeparture

---

### Post

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | |
| excerpt | text | |
| content | text | |
| image | string | |
| category | string | CATEGORIES: news, announcements, partner_articles |
| tags | json | cast: array |
| is_published | boolean | cast: boolean |
| published_at | datetime | cast: datetime |
| timestamps | | |

**Связи**: нет

---

### Media

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| mediable_type | string | polymorphic |
| mediable_id | bigint | polymorphic |
| file_path | string | |
| file_name | string | |
| mime_type | string | |
| size | integer | |
| collection | string | |
| order | integer | |
| timestamps | | |

**Связи**: `mediable()` MorphTo

---

### Setting

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| group | string | indexed |
| key | string | unique(group, key) |
| value | text | nullable |
| timestamps | | |

**Связи**: нет

---

## LMS-модели

### LmsEvent

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | routeKeyName |
| description | text | |
| auth_method | enum(email, sso) | |
| sso_provider_url | string | nullable |
| is_active | boolean | |
| timestamps | | |

**Связи**: HasMany — profiles, groups, courses, kbSections, tests, assignments, trajectories, videos, materialSections, gamificationRules

---

### LmsProfile

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| user_id | FK → users | cascade |
| lms_event_id | FK → lms_events | cascade |
| role | enum(participant, curator, leader, admin) | |
| lms_role_id | FK → lms_roles | nullable, nullOnDelete |
| status | string | nullable |
| invite_token | string | unique, nullable |
| invited_at | datetime | nullable |
| activated_at | datetime | nullable |
| position | string | nullable |
| phone | string | nullable |
| city | string | nullable |
| avatar | string | nullable |
| timestamps | | |
| **unique** | (user_id, lms_event_id) | |

**Связи**: `user()` BelongsTo User, `event()` BelongsTo LmsEvent, `lmsRole()` BelongsTo LmsRole
**Методы**: `generateInviteToken()`

---

### LmsRole

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | cascade |
| name | string | |
| slug | string | |
| description | text | nullable |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `profiles()` HasMany LmsProfile, `courses()` BelongsToMany LmsCourse через lms_course_role_access

---

### LmsGroup

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | cascade |
| title | string | |
| curator_id | FK → users | nullable, nullOnDelete |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `curator()` BelongsTo User, `members()` BelongsToMany User через lms_group_members

---

### LmsCourse

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | cascade |
| title | string | |
| slug | string | |
| description | text | |
| image | string | nullable |
| sequential | boolean | |
| is_active | boolean | |
| requires_approval | boolean | |
| position | integer | |
| starts_at | datetime | nullable |
| ends_at | datetime | nullable |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `stages()` HasMany LmsCourseStage, `enrollments()` HasMany LmsCourseEnrollment, `modules()` HasMany LmsCourseModule, `roleAccess()` BelongsToMany LmsRole через lms_course_role_access

---

### LmsCourseModule

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_course_id | FK → lms_courses | |
| title | string | |
| description | text | nullable |
| position | integer | indexed (lms_course_id, position) |
| available_from | datetime | nullable |
| available_to | datetime | nullable |
| unlock_type | string | nullable |
| timestamps | | |

**Связи**: `course()` BelongsTo LmsCourse, `stages()` HasMany LmsCourseStage
**Методы**: `isAvailable()`

---

### LmsCourseStage

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_course_id | FK → lms_courses | cascade |
| lms_course_module_id | FK → lms_course_modules | nullable, nullOnDelete |
| title | string | |
| description | text | |
| type | enum(content, scorm, test, assignment, video) | |
| content | text | nullable |
| scorm_package | string | nullable |
| lms_test_id | bigint | nullable |
| lms_assignment_id | bigint | nullable |
| lms_video_id | bigint | nullable |
| is_locked | boolean | |
| position | integer | |
| available_from | datetime | nullable |
| duration_minutes | integer | nullable |
| timestamps | | |

**Связи**: `course()` BelongsTo LmsCourse, `module()` BelongsTo LmsCourseModule, `test()` BelongsTo LmsTest, `assignment()` BelongsTo LmsAssignment, `video()` BelongsTo LmsVideo, `progress()` HasMany LmsStageProgress

---

### LmsCourseEnrollment

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_course_id | FK → lms_courses | cascade |
| user_id | FK → users | cascade |
| status | varchar(20) | enum: enrolled, in_progress, completed + pending, rejected |
| completed_at | datetime | nullable |
| reviewed_at | datetime | nullable |
| reviewed_by | FK → users | nullable, nullOnDelete |
| timestamps | | |
| **unique** | (lms_course_id, user_id) | |
| **index** | (user_id, status) | |

**Связи**: `course()` BelongsTo LmsCourse, `user()` BelongsTo User, `reviewer()` BelongsTo User

---

### LmsStageProgress

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_course_stage_id | FK → lms_course_stages | cascade |
| user_id | FK → users | cascade |
| status | enum(not_started, in_progress, completed) | |
| scorm_data | json | nullable, cast: array |
| score | decimal | nullable |
| watched_seconds | integer | nullable, cast: integer |
| completed_at | datetime | nullable |
| timestamps | | |
| **unique** | (lms_course_stage_id, user_id) | |
| **index** | (user_id, status) | |

**Связи**: `stage()` BelongsTo LmsCourseStage, `user()` BelongsTo User

---

### LmsTest

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| description | text | |
| time_limit_minutes | integer | nullable |
| shuffle_questions | boolean | |
| shuffle_answers | boolean | |
| show_correct_answers | boolean | |
| passing_score | integer | nullable |
| max_attempts | integer | nullable |
| in_menu | boolean | |
| is_active | boolean | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `questions()` HasMany LmsTestQuestion, `attempts()` HasMany LmsTestAttempt

---

### LmsTestQuestion

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_test_id | FK → lms_tests | |
| question | text | |
| type | enum(single, multiple, text) | |
| points | integer | |
| position | integer | |
| timestamps | | |

**Связи**: `test()` BelongsTo LmsTest, `answers()` HasMany LmsTestAnswer

---

### LmsTestAnswer

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_test_question_id | FK → lms_test_questions | |
| answer | text | |
| is_correct | boolean | |
| position | integer | |
| timestamps | | |

**Связи**: `question()` BelongsTo LmsTestQuestion

---

### LmsTestAttempt

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_test_id | FK → lms_tests | |
| user_id | FK → users | |
| status | string | в fillable модели, колонка в миграции не обнаружена (см. open-questions) |
| score | decimal | |
| max_score | decimal | |
| percentage | decimal | |
| passed | boolean | cast: boolean |
| started_at | datetime | cast: datetime |
| finished_at | datetime | cast: datetime |
| timestamps | | |

**Связи**: `test()` BelongsTo LmsTest, `user()` BelongsTo User, `responses()` HasMany LmsTestResponse

---

### LmsTestResponse

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_test_attempt_id | FK → lms_test_attempts | |
| lms_test_question_id | FK → lms_test_questions | |
| selected_answer_ids | json | cast: array |
| text_answer | text | nullable |
| is_correct | boolean | cast: boolean |
| points_earned | decimal | |
| timestamps | | |

**Связи**: `attempt()` BelongsTo LmsTestAttempt, `question()` BelongsTo LmsTestQuestion

---

### LmsAssignment

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| description | text | |
| template_file | string | nullable |
| completion_mode | enum(on_submit, on_review) | |
| deadline | datetime | nullable, cast: datetime |
| is_active | boolean | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `submissions()` HasMany LmsAssignmentSubmission

---

### LmsAssignmentSubmission

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_assignment_id | FK → lms_assignments | |
| user_id | FK → users | |
| text_content | text | nullable |
| link | string | nullable |
| files | json | cast: array |
| status | enum(draft, submitted, revision, approved, rejected) | |
| timestamps | | |

**Связи**: `assignment()` BelongsTo LmsAssignment, `user()` BelongsTo User, `reviews()` HasMany LmsAssignmentReview

---

### LmsAssignmentReview

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_assignment_submission_id | FK → lms_assignment_submissions | |
| reviewer_id | FK → users | |
| comment | text | |
| files | json | cast: array |
| decision | enum(approve, revision, reject) | |
| timestamps | | |

**Связи**: `submission()` BelongsTo LmsAssignmentSubmission, `reviewer()` BelongsTo User

---

### LmsTrajectory

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| description | text | |
| is_active | boolean | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `steps()` HasMany LmsTrajectoryStep, `enrollments()` HasMany LmsTrajectoryEnrollment

---

### LmsTrajectoryStep

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_trajectory_id | FK → lms_trajectories | |
| lms_course_id | FK → lms_courses | |
| is_locked | boolean | |
| opens_at | datetime | nullable |
| position | integer | |
| timestamps | | |

**Связи**: `trajectory()` BelongsTo LmsTrajectory, `course()` BelongsTo LmsCourse

---

### LmsTrajectoryEnrollment

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_trajectory_id | FK → lms_trajectories | |
| user_id | FK → users | |
| status | enum(enrolled, in_progress, completed) | |
| completed_at | datetime | nullable |
| timestamps | | |
| **unique** | (lms_trajectory_id, user_id) | |

**Связи**: `trajectory()` BelongsTo LmsTrajectory, `user()` BelongsTo User

---

### LmsVideo

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| description | text | |
| source | enum(upload, rutube, link) | |
| url | string | nullable |
| file_path | string | nullable |
| thumbnail | string | nullable |
| duration_seconds | integer | nullable |
| is_recording | boolean | |
| is_active | boolean | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `groups()` BelongsToMany LmsGroup через lms_video_access

---

### LmsKbSection

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| parent_id | FK → self | nullable (дерево) |
| title | string | |
| description | text | |
| in_menu | boolean | |
| position | integer | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `parent()` BelongsTo LmsKbSection, `children()` HasMany LmsKbSection, `items()` HasMany LmsKbItem, `groups()` BelongsToMany LmsGroup через lms_kb_access

---

### LmsKbItem

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_kb_section_id | FK → lms_kb_sections | |
| title | string | |
| type | enum(text, video, link, file) | |
| content | text | nullable |
| url | string | nullable |
| file_path | string | nullable |
| position | integer | |
| timestamps | | |

**Связи**: `section()` BelongsTo LmsKbSection

---

### LmsMaterialSection

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| content | text | |
| in_menu | boolean | |
| position | integer | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `groups()` BelongsToMany LmsGroup через lms_material_access

---

### LmsGamificationRule

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| action | string | |
| points | integer | |
| max_times | integer | nullable |
| is_auto | boolean | |
| is_active | boolean | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `pointEntries()` HasMany LmsGamificationPoint

---

### LmsGamificationPoint

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| user_id | FK → users | |
| lms_gamification_rule_id | FK → lms_gamification_rules | nullable |
| points | integer | |
| reason | string | nullable |
| timestamps | | |
| **index** | (lms_event_id, user_id, points) | |

**Связи**: `event()` BelongsTo LmsEvent, `user()` BelongsTo User, `rule()` BelongsTo LmsGamificationRule

---

### LmsInvitation

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| token | string unique | |
| label | string | |
| lms_role_id | FK → lms_roles | nullable |
| expires_at | datetime | nullable |
| max_uses | integer | nullable |
| uses_count | integer | default: 0 |
| is_active | boolean | |
| created_by | FK → users | nullable, nullOnDelete |
| timestamps | | |
| **index** | (lms_event_id, is_active) | |

**Связи**: `event()` BelongsTo LmsEvent, `role()` BelongsTo LmsRole, `creator()` BelongsTo User
**Методы**: `generateToken()`, `isValid()`

---

## Pivot-таблицы

| Таблица | FK 1 | FK 2 | Unique |
|---------|------|------|--------|
| city_tour | city_id → cities | tour_id → tours | — |
| lms_group_members | lms_group_id → lms_groups | user_id → users | (lms_group_id, user_id) |
| lms_kb_access | lms_kb_section_id → lms_kb_sections | lms_group_id → lms_groups | (lms_kb_section_id, lms_group_id) |
| lms_video_access | lms_video_id → lms_videos | lms_group_id → lms_groups | (lms_video_id, lms_group_id) |
| lms_material_access | lms_material_section_id → lms_material_sections | lms_group_id → lms_groups | (lms_material_section_id, lms_group_id) |
| lms_course_role_access | lms_course_id → lms_courses | lms_role_id → lms_roles | (lms_course_id, lms_role_id) |
