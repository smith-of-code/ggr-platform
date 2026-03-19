# Задачи: create-course-from-refs

## Task 1: Миграция — source_module_id и source_stage_id

- **Goal**: Добавить nullable FK для хранения ссылки на источник копирования
- **Scope**: `database/migrations/` (новая миграция)
- **DoD**: Колонки `source_module_id` (nullable FK → lms_course_modules) и `source_stage_id` (nullable FK → lms_course_stages) созданы
- **Verify**: `php artisan migrate` без ошибок

## Task 2: Обновление моделей

- **Goal**: Добавить новые поля в fillable и связи sourceModule/sourceStage
- **Scope**: `app/Models/Lms/LmsCourseModule.php`, `app/Models/Lms/LmsCourseStage.php`
- **DoD**: fillable обновлены, связи `sourceModule()` / `sourceStage()` добавлены
- **Verify**: Модели загружаются без ошибок (`php artisan tinker` — `new LmsCourseModule`)

## Task 3: API — поиск модулей по названию

- **Goal**: Endpoint для поиска модулей по title в пределах event
- **Scope**: `app/Http/Controllers/Lms/Admin/CourseController.php`, `routes/lms.php`
- **DoD**: `GET /lms/admin/{event}/search-modules?q=...` возвращает JSON с модулями и названием курса
- **Verify**: curl / pest тест

## Task 4: API — поиск этапов по названию

- **Goal**: Endpoint для поиска этапов по title в пределах event
- **Scope**: `app/Http/Controllers/Lms/Admin/CourseController.php`, `routes/lms.php`
- **DoD**: `GET /lms/admin/{event}/search-stages?q=...` возвращает JSON с этапами, названием модуля и курса
- **Verify**: curl / pest тест

## Task 5: Vue-компонент SearchRefModal

- **Goal**: Универсальный попап поиска с результатами и подтверждением
- **Scope**: `resources/js/Pages/Lms/Admin/Courses/SearchRefModal.vue` (новый файл)
- **DoD**: Компонент принимает prop `type` (module|stage), выполняет debounce-поиск, показывает результаты с контекстом (курс/модуль), по клику показывает подтверждение, emit'ит выбранный объект
- **Verify**: Компонент рендерится, поиск работает

## Task 6: Интеграция кнопки «Найти модуль» в Form.vue

- **Goal**: Добавить кнопку рядом с «Добавить модуль», по клику открывать SearchRefModal
- **Scope**: `resources/js/Pages/Lms/Admin/Courses/Form.vue`
- **DoD**: Кнопка «Найти модуль» открывает попап, при выборе — модуль со всеми этапами добавляется в form.modules с `source_module_id`
- **Verify**: Визуально в браузере + данные формы содержат скопированный модуль

## Task 7: Интеграция кнопки «Найти этап» в Form.vue и StageEditor-секции

- **Goal**: Добавить кнопку «Найти этап» рядом с «Добавить этап» в каждом блоке
- **Scope**: `resources/js/Pages/Lms/Admin/Courses/Form.vue`
- **DoD**: Кнопки в блоке модуля и в блоке «Этапы без модуля», при выборе — этап добавляется в соответствующий массив с `source_stage_id`
- **Verify**: Визуально в браузере + данные формы содержат скопированный этап

## Task 8: Backend — сохранение source_module_id / source_stage_id

- **Goal**: При store/update передавать и сохранять ссылки на источник
- **Scope**: `app/Http/Controllers/Lms/Admin/CourseController.php` (методы `syncModulesAndStages`, `createStage`, `courseRules`)
- **DoD**: Валидация принимает nullable source_*_id, `syncModulesAndStages` и `createStage` сохраняют значения в БД
- **Verify**: Создание курса с копией сохраняет source_*_id в БД

## Task 9: Загрузка source-ссылок при редактировании

- **Goal**: При edit передавать source_module_id / source_stage_id на фронт, чтобы при update они не терялись
- **Scope**: `app/Http/Controllers/Lms/Admin/CourseController.php` (edit), `Form.vue` (buildModules, buildOrphanStages)
- **DoD**: При редактировании курса с копированными элементами source_*_id сохраняются в форме и отправляются обратно
- **Verify**: Открыть курс с копиями на редактирование → сохранить → проверить БД — source_*_id не потерялись
