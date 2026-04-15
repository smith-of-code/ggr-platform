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

**Связи**: `socialAccounts()` HasMany SocialAccount

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
| coat_of_arms | string | nullable |
| region | string | nullable |
| population | integer | nullable |
| population_year | integer | nullable |
| founded_year | integer | nullable |
| timezone | string | nullable |
| lat | decimal | nullable |
| lng | decimal | nullable |
| attractions | json | cast: array |
| social_objects | json | cast: array |
| gallery | json | cast: array |
| video_url | string | nullable |
| facts | json | cast: array |
| energy_cities_block | json | cast: array |
| block_visibility | json | cast: array |
| position | integer | |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: `tours()` BelongsToMany Tour через city_tour, `favorites()` MorphMany Favorite, `recipes()` HasMany Recipe, `vacancies()` HasMany Vacancy

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
| for_children | boolean | cast: boolean |
| for_foreigners | boolean | cast: boolean |
| closed_city | boolean | cast: boolean |
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
| program_days | json | cast: array |
| memo_text | text | nullable |
| pass_info | text | nullable |
| accommodations | json | cast: array |
| gallery | json | cast: array |
| videos | json | cast: array |
| target_audience | text | nullable |
| organizer_info | text | nullable |
| reactions_count | integer | cast: integer |
| bchp_participant | boolean | cast: boolean |
| is_featured | boolean | cast: boolean |
| image | string | |
| position | integer | |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: `cities()` BelongsToMany City, `departures()` HasMany TourDeparture, `media()` morphMany Media, `reactions()` HasMany TourReaction, `reviews()` HasMany TourReview, `favorites()` MorphMany Favorite

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

### TourReaction

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| tour_id | FK → tours | |
| user_id | FK → users | |
| ip_address | string | |
| emoji | string | |
| timestamps | | |

**Связи**: `tour()` BelongsTo Tour, `user()` BelongsTo User
**Константы**: `EMOJIS` — массив (love, wow, fire, cool, star)

---

### TourReview

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| tour_id | FK → tours | |
| user_id | FK → users | |
| rating | integer | cast: integer |
| text | text | |
| is_approved | boolean | cast: boolean |
| timestamps | | |

**Связи**: `tour()` BelongsTo Tour, `user()` BelongsTo User

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
| category | string | CATEGORIES — константа модели (news, announcements, partner_articles) |
| tags | json | cast: array |
| videos | json | cast: array |
| is_published | boolean | cast: boolean |
| published_at | datetime | cast: datetime |
| timestamps | | |

**Связи**: нет

---

### Direction

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | |
| description | text | |
| image | string | |
| project_key | string | |
| sub_directions_title | string | nullable |
| sub_directions_description | text | nullable |
| sub_directions | json | cast: array |
| target_audiences | json | cast: array |
| target_audience_note | text | nullable |
| free_participation_steps | json | cast: array |
| free_participation_details | json | cast: array |
| paid_participation_steps | json | cast: array |
| paid_form_slug | string | nullable |
| featured_tour_ids | json | cast: array |
| is_active | boolean | cast: boolean |
| position | integer | |
| timestamps | | |

**Связи**: `featuredTours()` — метод (не Eloquent relation), возвращает Builder → Tour::whereIn по featured_tour_ids

---

### AtomsVkusaContent

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| hero_title | string | |
| hero_description | text | |
| hero_image | string | |
| competition_stages | json | cast: array |
| participation_conditions | json | cast: array |
| selection_criteria | json | cast: array |
| results_year | string | |
| results_content | text | |
| results_gallery | json | cast: array |
| results_videos | json | cast: array |
| results_cases | json | cast: array |
| why_important_content | text | |
| why_important_stats | json | cast: array |
| map_cities | json | cast: array |
| application_form_title | string | |
| application_form_text | text | |
| partners | json | cast: array |
| reviews | json | cast: array |
| tourism_help_content | text | |
| tourism_help_items | json | cast: array |
| timestamps | | |

**Таблица**: `atoms_vkusa_content` (singleton — `firstOrCreate` через static method `content()`)

---

### BlogSubscriber

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| email | string | |
| is_active | boolean | cast: boolean |
| token | string | |
| timestamps | | |

**Связи**: нет

---

### Recipe

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | |
| description | text | |
| content | text | |
| image | string | |
| city_id | FK → cities | nullable |
| cooking_time | string | |
| difficulty | string | |
| servings | string | |
| ingredients | json | cast: array |
| is_published | boolean | cast: boolean |
| published_at | datetime | cast: datetime |
| timestamps | | |

**Связи**: `city()` BelongsTo City

---

### EducationProduct

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | |
| description | text | |
| content | text | |
| image | string | |
| duration | string | |
| format | string | |
| target_audience | text | |
| price_info | text | |
| position | integer | |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: нет

---

### Vacancy

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| slug | string unique | |
| city_id | FK → cities | nullable |
| company | string | |
| employment_type | string | |
| salary | string | nullable |
| description | text | |
| requirements | text | |
| conditions | text | |
| responsibilities | text | |
| contact_email | string | |
| contact_phone | string | nullable |
| image | string | nullable |
| is_published | boolean | cast: boolean |
| published_at | datetime | cast: datetime |
| position | integer | |
| timestamps | | |

**Связи**: `city()` BelongsTo City
**Константы**: `EMPLOYMENT_TYPES` — массив (full_time, part_time, remote, internship, contract)

---

### TimelineEvent

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| title | string | |
| description | text | |
| event_date | date | cast: date |
| link | string | nullable |
| type | string | |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: нет

---

### Favorite

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| user_id | FK → users | |
| favorable_type | string | polymorphic |
| favorable_id | bigint | polymorphic |
| timestamps | | |

**Связи**: `user()` BelongsTo User, `favorable()` MorphTo

---

### ContactSubmission

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| name | string | |
| email | string | |
| phone | string | nullable |
| message | text | |
| source | string | |
| status | string | |
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

### UploadedMedia

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| filename | string | |
| original_name | string | |
| path | string | |
| url | string | |
| disk | string | |
| mime_type | string | |
| size | integer | |
| collection | string | nullable |
| entity_type | string | nullable |
| entity_id | bigint | nullable |
| timestamps | | |

**Таблица**: `uploaded_media`
**Связи**: нет

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
| menu_config | json | cast: array, с дефолтным значением DEFAULT_MENU_CONFIG |
| is_active | boolean | cast: boolean |
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
| invited_at | datetime | cast: datetime, nullable |
| activated_at | datetime | cast: datetime, nullable |
| position | string | nullable |
| phone | string | nullable |
| city | string | nullable |
| city_id | FK → cities | nullable |
| organization | string | nullable |
| project_description | text | nullable |
| preferred_channel | string | nullable |
| avatar | string | nullable |
| timestamps | | |
| **unique** | (user_id, lms_event_id) | |

**Связи**: `user()` BelongsTo User, `event()` BelongsTo LmsEvent, `lmsRole()` BelongsTo LmsRole, `cityRelation()` BelongsTo City, `documents()` HasMany LmsProfileDocument
**Методы**: `generateInviteToken()`, `isProfileComplete()`, `getMissingFields()`

---

### LmsProfileDocument

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_profile_id | FK → lms_profiles | |
| type | string | |
| file_path | string | |
| original_name | string | |
| timestamps | | |

**Связи**: `profile()` BelongsTo LmsProfile
**Константы**: `TYPE_ENROLLMENT_APPLICATION`, `TYPE_SNILS`, `TYPE_DIPLOMA`, `TYPE_PERSONAL_DATA_CONSENT`, `TYPE_NAME_CHANGE_CERTIFICATE`, `TYPES`, `TYPES_WITH_TEMPLATE`

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
| is_mandatory | boolean | |
| unlocks_gamification | boolean | |
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
| source_stage_id | bigint | nullable, FK → self |
| is_locked | boolean | cast: boolean |
| position | integer | |
| available_from | datetime | cast: datetime, nullable |
| duration_minutes | integer | cast: integer, nullable |
| timestamps | | |

**Связи**: `course()` BelongsTo LmsCourse, `module()` BelongsTo LmsCourseModule, `test()` BelongsTo LmsTest, `assignment()` BelongsTo LmsAssignment, `video()` BelongsTo LmsVideo, `progress()` HasMany LmsStageProgress, `sourceStage()` BelongsTo self, `blocks()` HasMany LmsStageBlock

---

### LmsStageBlock

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_course_stage_id | FK → lms_course_stages | |
| type | string | |
| content | text | nullable |
| scorm_package | string | nullable |
| lms_test_id | FK → lms_tests | nullable |
| lms_assignment_id | FK → lms_assignments | nullable |
| lms_video_id | FK → lms_videos | nullable |
| position | integer | |
| scheduled_at | datetime | cast: datetime, nullable |
| scheduled_ends_at | datetime | cast: datetime, nullable; время окончания для воркшопа/встреч |
| timestamps | | |

**Связи**: `stage()` BelongsTo LmsCourseStage, `test()` BelongsTo LmsTest, `assignment()` BelongsTo LmsAssignment, `video()` BelongsTo LmsVideo

---

### LmsCourseEnrollment

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_course_id | FK → lms_courses | cascade |
| user_id | FK → users | cascade |
| status | varchar(20) | enum: enrolled, in_progress, completed, pending, rejected |
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
| status | string | добавлено миграцией 2026_03_24 |
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
| template_file_name | string | nullable |
| completion_mode | enum(on_submit, on_review) | |
| deadline | datetime | nullable, cast: datetime |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `submissions()` HasMany LmsAssignmentSubmission, `tasks()` HasMany LmsAssignmentTask

---

### LmsAssignmentTask

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_assignment_id | FK → lms_assignments | |
| title | string | |
| description | text | nullable |
| response_type | string | |
| template_file | string | nullable |
| template_file_name | string | nullable |
| position | integer | |
| timestamps | | |

**Связи**: `assignment()` BelongsTo LmsAssignment

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

### LmsSubmissionAnswer

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_assignment_submission_id | FK → lms_assignment_submissions | |
| lms_assignment_task_id | FK → lms_assignment_tasks | |
| text_content | text | nullable |
| link | string | nullable |
| files | json | cast: array |
| timestamps | | |

**Связи**: `submission()` BelongsTo LmsAssignmentSubmission, `task()` BelongsTo LmsAssignmentTask

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

### LmsAssignmentComment

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_assignment_submission_id | FK → lms_assignment_submissions | |
| user_id | FK → users | |
| text | text | |
| files | json | cast: array |
| timestamps | | |

**Связи**: `submission()` BelongsTo LmsAssignmentSubmission, `user()` BelongsTo User

---

### LmsTrajectory

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| description | text | |
| is_active | boolean | cast: boolean |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `steps()` HasMany LmsTrajectoryStep, `enrollments()` HasMany LmsTrajectoryEnrollment, `blocks()` HasMany LmsTrajectoryBlock

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

### LmsTrajectoryBlock

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_trajectory_id | FK → lms_trajectories | |
| type | string | |
| title | string | |
| description | text | nullable |
| date_label | string | nullable |
| date_start | date | cast: date, nullable |
| date_end | date | cast: date, nullable |
| lms_assignment_id | FK → lms_assignments | nullable |
| position | integer | |
| timestamps | | |

**Связи**: `trajectory()` BelongsTo LmsTrajectory, `assignment()` BelongsTo LmsAssignment

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

### LmsGrant

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| type | string | |
| city | json | cast: array |
| description | text | |
| application_start | datetime | cast: datetime, nullable |
| application_end | datetime | cast: datetime, nullable |
| is_active | boolean | cast: boolean |
| position | integer | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `documents()` HasMany LmsGrantDocument, `enrollments()` HasMany LmsGrantEnrollment
**Константы**: `TYPE_GRANT`, `TYPE_SUBSIDY`, `TYPE_CREDIT`, `TYPES`

---

### LmsGrantDocument

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_grant_id | FK → lms_grants | |
| file_path | string | |
| original_name | string | |
| position | integer | |
| timestamps | | |

**Связи**: `grant()` BelongsTo LmsGrant

---

### LmsGrantEnrollment

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_grant_id | FK → lms_grants | |
| user_id | FK → users | |
| timestamps | | |

**Связи**: `grant()` BelongsTo LmsGrant, `user()` BelongsTo User

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

**Связи**: `event()` BelongsTo LmsEvent, `courses()` BelongsToMany LmsCourse через lms_video_course_access

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
| in_menu | boolean | cast: boolean |
| position | integer | |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `groups()` BelongsToMany LmsGroup через lms_material_access, `files()` HasMany LmsMaterialFile

---

### LmsMaterialFile

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_material_section_id | FK → lms_material_sections | |
| title | string | |
| file_path | string | |
| file_name | string | |
| file_size | integer | |
| position | integer | |
| timestamps | | |

**Связи**: `section()` BelongsTo LmsMaterialSection

---

### LmsForm

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_event_id | FK → lms_events | |
| title | string | |
| description | text | nullable |
| slug | string | |
| is_active | boolean | cast: boolean |
| is_anonymous | boolean | cast: boolean |
| allow_embed | boolean | cast: boolean |
| create_users | boolean | cast: boolean |
| fio_field_key | string | nullable |
| email_field_key | string | nullable |
| phone_field_key | string | nullable |
| position_field_key | string | nullable |
| thank_you_message | text | nullable |
| timestamps | | |

**Связи**: `event()` BelongsTo LmsEvent, `fields()` HasMany LmsFormField, `submissions()` HasMany LmsFormSubmission

---

### LmsFormField

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_form_id | FK → lms_forms | |
| key | string | |
| label | string | |
| type | string | |
| required | boolean | cast: boolean |
| placeholder | string | nullable |
| options | json | cast: array |
| position | integer | |
| timestamps | | |

**Связи**: `form()` BelongsTo LmsForm, `responses()` HasMany LmsFormResponse

---

### LmsFormSubmission

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_form_id | FK → lms_forms | |
| user_id | FK → users | nullable |
| ip_address | string | nullable |
| user_agent | string | nullable |
| user_created | boolean | cast: boolean |
| timestamps | | |

**Связи**: `form()` BelongsTo LmsForm, `user()` BelongsTo User, `responses()` HasMany LmsFormResponse

---

### LmsFormResponse

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| lms_form_submission_id | FK → lms_form_submissions | |
| lms_form_field_id | FK → lms_form_fields | |
| value | text | nullable |
| timestamps | | |

**Связи**: `submission()` BelongsTo LmsFormSubmission, `field()` BelongsTo LmsFormField

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
| lms_video_course_access | lms_video_id → lms_videos | lms_course_id → lms_courses | (lms_video_id, lms_course_id) |
| lms_material_access | lms_material_section_id → lms_material_sections | lms_group_id → lms_groups | (lms_material_section_id, lms_group_id) |
| lms_course_role_access | lms_course_id → lms_courses | lms_role_id → lms_roles | (lms_course_id, lms_role_id) |
