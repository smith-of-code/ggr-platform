# Прогресс: aportinity-tours

## Completed tasks

### Task 1: Backend-каркас — контроллер и маршрут ✓
- Files: `app/Http/Controllers/OpportunityToursController.php`, `routes/web.php`

### Task 2: Навигация — ссылка в шапке, мобильном меню и footer ✓
- Files: `resources/js/Layouts/MainLayout.vue`

### Task 3: Vue-страница — каркас + Цифры проекта ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`

### Task 4: Секция «Проекты программы» ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`

### Task 5: Секция «Видео туров» — слайдшоу ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`

### Task 6: Секции «Как принять участие» + «Счётчик эмоций» ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`

### Task 7: Секция «Популярные туры» — слайдшоу карточек ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`

### Task 8: Секции «Соцсети» + «FAQ» + «Партнёры» ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`

### Task 9: Финализация — линтер, spec, прогресс ✓
- Lint: clean
- Files: spec/features/aportinity-tours/progress.md, spec/features/aportinity-tours/spec.md

### REV-001: Модель данных — сидер начальных значений блоков ✓
- Files: `database/seeders/OpportunityToursSeeder.php`, `database/seeders/DatabaseSeeder.php`

### REV-002: Admin backend — контроллер + маршруты + пункт в сайдбаре ✓
- Files: `app/Http/Controllers/Admin/OpportunityToursPageController.php`, `routes/web.php`, `resources/js/Layouts/AdminLayout.vue`

### REV-003+004+005: Admin Vue — страница редактирования всех блоков ✓
- Files: `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`, `DynamicList.vue`, `SectionHeader.vue`

### REV-006: Публичная страница — backend: динамические данные ✓
- Files: `app/Http/Controllers/OpportunityToursController.php`

### REV-007: Публичная страница — frontend: props + UI правки ✓
- Files: `resources/js/Pages/OpportunityTours/Index.vue`
- Партнёры: кликабельные (component :is с <a>/<div>), поддержка логотипов
- FAQ: v-html для ответов, CSS для ссылок
- Все данные из pageData props с fallback на defaults

### REV-008: Финализация ✓
- Lint: clean
- Все spec-файлы обновлены

## Partially completed

(пусто)

## Not started
### REV-014: Финализация итерации 2

## Completed (итерация 2)

### REV-009: DynamicList — типы icon-select и image-upload ✓
- Тип `icon-select` — select с SVG-превью рядом
- Тип `image-upload` — URL-поле + кнопка загрузки + preview
- Тип `file-upload` — URL или загрузка файла (видео и др.)
- Files: `DynamicList.vue`

### REV-010: Admin — превью иконок + image-upload для партнёров ✓
- Иконки эмоций и соцсетей через общий `opportunityToursIcons.js`
- Партнёры с image-upload для логотипов
- Files: `Index.vue` (admin), `opportunityToursIcons.js`, `Index.vue` (public)

### REV-012: Видео — обложка + загрузка файла + модальное окно ✓
- Админка: поля thumbnail (image-upload), embedUrl, videoFile (file-upload)
- Публичная: карточки с обложкой + play-кнопка → модальное окно
- Модальное окно: `<video>` для загруженных файлов, `<iframe>` для embed
- Files: `Index.vue` (admin), `Index.vue` (public), `OpportunityToursPageController.php`

### REV-013: Проекты программы — выбор из Direction + кастомные ✓
- Admin: секция «Проекты программы» c DynamicList — тип «Из направлений» (select Direction) или «Кастомный» (название, описание, изображение, ссылка)
- Backend: `projects` добавлен в JSON_KEYS, валидация, `allDirections` передаётся в админку
- Public: `directionsOrFallback` резолвит `direction_id` из настроек или использует кастомные данные, fallback на все активные направления
- Files: `OpportunityToursPageController.php` (admin), `OpportunityToursController.php` (public), `Index.vue` (admin), `Index.vue` (public)

### REV-011: Партнёры — обновить сидер с реальными данными ✓
- Скачаны логотипы 5 партнёров в `public/images/partners/`
- Росмолодёжь, Больше чем путешествие, ДОБРО.РФ, Минобрнауки, Студтуризм
- Сидер обновлён с реальными названиями, URL и путями к логотипам
- Files: `OpportunityToursSeeder.php`, `public/images/partners/*`

### BUG-001: Фильтрация удалённых туров из featured_tour_ids ✓
- `index()`: фильтрация `featured_tour_ids` по существующим активным турам
- `update()`: `exists:tours,id` → `integer` + ручная фильтрация после валидации
- Lint: clean
- Files: `app/Http/Controllers/Admin/OpportunityToursPageController.php`

## Open issues

(пусто)
