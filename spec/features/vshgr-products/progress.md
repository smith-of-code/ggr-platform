# Прогресс — vshgr-products

## Completed tasks

### T1 — Миграция
- ✅ `database/migrations/2026_04_07_100000_add_type_sections_to_education_products.php` — добавлены колонки `type`, `sections`, `countries`, `regulation_file`

### T2 — Модель EducationProduct
- ✅ `app/Models/EducationProduct.php` — добавлены fillable, casts (sections→array, countries→array), константы типов, SECTION_DEFINITIONS, SECTION_LABELS, accessor для type, методы getAllowedSections/getSectionContent/isSectionEnabled

### T3 — Контроллер
- ✅ `app/Http/Controllers/Admin/EducationProductController.php` — валидация type, sections.*, countries.*, regulation_file; create() принимает ?type; edit() передаёт sectionDefinitions/sectionLabels; update() защищает type от изменения

### T4 — Выбор типа при создании
- ✅ `resources/js/Pages/Admin/EducationProducts/Index.vue` — dropdown с 3 типами при нажатии «Новый продукт»; колонка «Тип» с бейджем в таблице

### T5 — Динамическая админ-форма (education)
- ✅ `resources/js/Pages/Admin/EducationProducts/Form.vue` — полная переработка: секции с чекбоксами и RichText-редакторами, сворачивание блоков, поля duration/format/price_info для education

### T6 — Блок экспертов
- ✅ Inline в Form.vue — добавление/удаление экспертов (name, position, photo через ImageUploadCrop, bio)

### T7 — Поля partner и international
- ✅ Form.vue: partner — секции description_goal + participation_conditions; international — секции + блок стран (name, slug, description, content с RichTextEditor)

### T8 — Публичная страница education
- ✅ `resources/js/Pages/Education/Show.vue` — блочный лендинг: секционная навигация (sticky), рендер RichText/experts/regulation/application_form/training_request секций, fallback для legacy content/target_audience

### T9 — Публичные страницы partner и international
- ✅ Show.vue: partner — описание + условия участия; international — описание + страновые блоки с visual separator
- ✅ `resources/js/Pages/Education/Partials/ApplicationForm.vue` — переиспользуемая форма заявки (вынесена из Show.vue)

### T10 — Seed-данные и каталог
- ✅ `database/seeders/PortalSeeder.php` — type для существующих 3 продуктов + 2 новых (partner, international с sections/countries)
- ✅ `resources/js/Pages/Education/Index.vue` — бейдж типа на карточках каталога

### BUG-T11 — Навигация education: не выходить за контейнер
- ✅ `resources/js/Pages/Education/Show.vue` — убраны `-mx-*`/`px-*` у `<nav>`, добавлены `max-w-full min-w-0`, внутренний ряд `flex w-max` для скролла; `aria-label` на nav
- ✅ Линтер IDE: без замечаний по `Show.vue`
- ✅ `php artisan test --filter=Education` в Docker — тестов нет, exit 0

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
