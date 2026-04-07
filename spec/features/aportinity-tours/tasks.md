# Задачи: Туры возможностей (aportinity-tours)

## Task 1–9 (первоначальная реализация) ✓

Все задачи по первоначальной статической реализации завершены.

---

## REV-задачи (правки заказчика 2026-03-30)

### REV-001: Модель данных — сидер начальных значений блоков

- **Цель**: Перенести все захардкоженные данные страницы в таблицу `settings` (group `opportunity_tours`)
- **Scope**: `database/seeders/OpportunityToursSeeder.php`
- **DoD**: Сидер создаёт записи для всех ключей: `hero_title`, `hero_description`, `stats`, `emotions`, `partners`, `socials`, `faq`, `videos`, `participation_steps`, `featured_tour_ids`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan db:seed --class=OpportunityToursSeeder`

### REV-002: Admin backend — контроллер + маршруты + пункт в сайдбаре

- **Цель**: Создать `Admin\OpportunityToursPageController` (index/update), маршруты, ссылку в AdminLayout
- **Scope**: `app/Http/Controllers/Admin/OpportunityToursPageController.php`, `routes/web.php`, `resources/js/Layouts/AdminLayout.vue`
- **DoD**: `/admin/opportunity-tours-page` доступна, пункт «Туры возможностей» в сайдбаре админки
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=admin/opportunity-tours-page`

### REV-003: Admin Vue — формы для Hero, Stats, Emotions, Participation Steps

- **Цель**: Страница админки с формами для заголовка/описания, счётчиков, эмоций, шагов участия
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`
- **DoD**: Формы с полями ввода для каждой секции; динамическое добавление/удаление для Stats; сохранение PUT
- **Verify**: Визуальная проверка `/admin/opportunity-tours-page`

### REV-004: Admin Vue — формы для Partners, Socials, FAQ, Videos

- **Цель**: Динамические CRUD-формы для списков партнёров, соцсетей, FAQ, видео
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`
- **DoD**: Добавление/удаление элементов, поля: партнёры (название, URL, логотип), соцсети (название, URL, иконка), FAQ (вопрос, ответ с HTML), видео (название, embed URL)
- **Verify**: Визуальная проверка

### REV-005: Admin Vue — выбор популярных туров из каталога

- **Цель**: Мультиселект туров из каталога для блока «Популярные туры»
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`, `app/Http/Controllers/Admin/OpportunityToursPageController.php`
- **DoD**: Список всех активных туров с чекбоксами или drag-and-drop, сохранение массива ID
- **Verify**: Визуальная проверка

### REV-006: Публичная страница — backend: динамические данные из Settings

- **Цель**: Контроллер передаёт данные всех блоков из Settings вместо хардкода
- **Scope**: `app/Http/Controllers/OpportunityToursController.php`
- **DoD**: Все props заполнены из БД; при пустых данных — разумные defaults
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="echo json_encode(App\\Models\\Setting::getGroup('opportunity_tours'));"`

### REV-007: Публичная страница — frontend: props вместо хардкода + UI правки

- **Цель**: Vue-компонент использует props из бэкенда; партнёры кликабельны; FAQ поддерживает HTML
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD**: Все блоки рендерят динамические данные; логотипы партнёров — `<a>` ссылки; ответы FAQ — `v-html`
- **Verify**: Визуальная проверка

### REV-008: Финализация — линтер, обновление spec

- **Цель**: Проверить линтер на изменённых файлах, обновить spec до финального состояния
- **Scope**: Все затронутые файлы, `spec/features/aportinity-tours/*`
- **DoD**: Линтер чист, spec финализирован, progress.md актуален
- **Verify**: Линтер на изменённых файлах

---

## REV-задачи (правки заказчика 2026-03-30, итерация 2)

### REV-009: DynamicList — типы icon-select и image-upload

- **Цель**: Расширить DynamicList для рендеринга SVG-превью иконок в select и загрузки/preview картинок
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue`
- **DoD**: Тип `icon-select` показывает SVG рядом с выбором; тип `image-upload` — URL + upload + превью
- **Verify**: Визуальная проверка

### REV-010: Admin — превью иконок (эмоции, соцсети) + image-upload для партнёров

- **Цель**: Использовать icon-select для emotions/socials, image-upload для partners в формах
- **Scope**: `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`
- **DoD**: Иконки с превью, партнёры с upload/preview логотипов
- **Verify**: Визуальная проверка

### REV-011: Партнёры — обновить сидер с реальными данными

- **Цель**: Скачать логотипы партнёров, обновить сидер реальными данными (Росмолодёжь, Больше чем путешествие, ДОБРО.РФ, Минобрнауки, Студтуризм)
- **Scope**: `database/seeders/OpportunityToursSeeder.php`, `public/images/partners/*`
- **DoD**: Сидер содержит реальные данные, картинки в `public/images/partners/`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan db:seed --class=OpportunityToursSeeder`

### REV-012: Видео — загрузка файла + прямая ссылка

- **Цель**: Добавить тип видео (embed / direct URL / uploaded file) в форму и frontend
- **Scope**: Admin Index.vue, DynamicList.vue, admin controller, public Index.vue
- **DoD**: Можно добавлять видео embed-ссылкой, прямой URL или загрузить файл
- **Verify**: Визуальная проверка

### REV-013: Проекты программы — выбор из Direction + кастомные

- **Цель**: В админке секция для выбора Direction из списка + добавление кастомных элементов (title, description, image, link)
- **Scope**: Admin Index.vue, admin controller, public controller, public Index.vue, seeder
- **DoD**: Управление проектами из админки, frontend рендерит выбранные + кастомные
- **Verify**: Визуальная проверка

### REV-014: Финализация итерации 2

- **Цель**: Линтер, обновление spec, progress
- **Scope**: Все затронутые файлы, spec
- **DoD**: Линтер чист, spec финализирован
- **Verify**: Линтер на изменённых файлах

---

## BUG-задачи

### BUG-001: Фильтрация удалённых туров из featured_tour_ids

- В `index()`: отфильтровать `featured_tour_ids` — оставить только ID существующих активных туров
- В `update()`: заменить `exists:tours,id` на ручную фильтрацию — отбросить несуществующие ID после валидации (integer)
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=admin/opportunity-tours-page`
