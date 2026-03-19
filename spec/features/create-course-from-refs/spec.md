# Фича: Копирование модулей и этапов из существующих курсов

**Статус**: done

## Цель

Дать возможность админу при создании/редактировании курса находить и копировать модули и этапы из других курсов того же event, сохраняя ссылку на источник в БД.

## In-scope

- Ссылка «Найти модуль из другого курса» внутри каждого блока модуля (над полем «Название модуля»)
- Ссылка «Найти этап из другого курса» внутри каждого блока этапа (над полем «Название этапа», компонент `StageEditor`)
- Попап поиска (`SearchRefModal.vue` на базе `RModal` из UI-kit) с текстовым вводом и debounce-запросом:
  - Для модулей: название модуля + название курса-источника + количество этапов
  - Для этапов: название этапа + название модуля (если есть) + название курса-источника
- При клике на результат — окно подтверждения «Скопировать?»
- При подтверждении — данные из найденного элемента заполняют текущий блок (replace, не push)
- При копировании модуля — его этапы также копируются (заменяют текущие)
- Сохранение FK-ссылки на источник: `source_module_id` / `source_stage_id` (только в БД, не отображается в UI)
- Backend API: `GET /lms-admin/{event}/search-modules?q=...`, `GET /lms-admin/{event}/search-stages?q=...`
- Регистронезависимый поиск (PostgreSQL `ILIKE`)

## UX-поток

1. Админ нажимает «Добавить модуль» / «Добавить этап» — появляется пустой блок, страница прокручивается к нему
2. Внутри блока — ссылка «Найти ... из другого курса» (видна только пока название пустое)
3. Клик → открывается попап поиска (autocomplete отключен)
4. Ввод минимум 2 символов → debounce 300ms → результаты
5. Клик по результату → подтверждение
6. «Копировать» → данные заполняют текущий блок, кнопка поиска скрывается
7. Админ может далее редактировать скопированные данные
8. При сохранении курса — `source_module_id` / `source_stage_id` записываются в БД

## Out-of-scope

- Синхронизация изменений между копией и источником (копия полностью независима)
- Отображение ссылки на источник в UI
- Копирование целого курса целиком

## Constraints

- Поиск ограничен курсами того же `lms_event_id`
- При копировании модуля копируются все его этапы (с проставлением `source_stage_id` у каждого)
- Используются компоненты: `RModal`, `RButton`, `RInput` (глобальный ui-kit `@rosatom-ggr/ui-kit`)
- Миграции: nullable FK `source_module_id` → `lms_course_modules`, `source_stage_id` → `lms_course_stages` (nullOnDelete)

## Затронутые файлы

- `database/migrations/2026_03_19_300000_add_source_refs_to_modules_and_stages.php`
- `app/Models/Lms/LmsCourseModule.php` — fillable + связь `sourceModule()`
- `app/Models/Lms/LmsCourseStage.php` — fillable + связь `sourceStage()`
- `app/Http/Controllers/Lms/Admin/CourseController.php` — методы `searchModules`, `searchStages`, обновлены `syncModulesAndStages`, `createStage`, `courseRules`
- `routes/lms.php` — маршруты `search-modules`, `search-stages`
- `resources/js/Pages/Lms/Admin/Courses/SearchRefModal.vue` — новый компонент
- `resources/js/Pages/Lms/Admin/Courses/StageEditor.vue` — добавлена кнопка поиска + emit `search`
- `resources/js/Pages/Lms/Admin/Courses/Form.vue` — интеграция поиска (replace-логика), передача `source_*_id` при сохранении/загрузке
