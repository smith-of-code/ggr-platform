# Модуль: Направления (directions)

## Статус: Реализован

## Цель

Управление контентом блока «Проекты программы» через админку + отдельные публичные страницы для каждого направления (проекта) с возможностью перехода по ссылке.

## В scope

- Модель `Direction` — сущность «Направление/Проект программы» с хранением всех блоков страницы в БД
- Миграция для таблицы `directions`
- Seed с данными трёх направлений из прототипов (Старт в Атомград, Атомы вкуса, Лучшие люди Росатома) в `PortalSeeder`
- CRUD в админке: `/admin/directions` — список, создание, редактирование, удаление, toggle активности
- Публичный маршрут `GET /directions/{slug}` → `DirectionController@show`
- Vue-страница `Pages/Directions/Show.vue` со всеми секциями согласно прототипам:
  - Hero: название проекта + описание
  - Направления (поднаправления): заголовок, описание, 3 карточки
  - Целевые аудитории: нумерованные карточки
  - «Твой билет в атомный город»: два пути — бесплатно (конкурс → ЛК) и платно (купить тур → заявка)
  - Конкурсные детали: развёрнутые ответы + проверочное задание
  - Слайдшоу туров: выбранные админом туры данного направления
- Обновление ссылок в секции «Проекты программы» на `OpportunityTours/Index.vue` — ведут на страницы направлений
- Ссылка «Направления» в навигации `AdminLayout.vue`

## Вне scope

- Механика проведения конкурса / проверочных заданий (только отображение текстового описания)
- Оплата туров и интеграция с платёжными системами
- Личный кабинет участника конкурса (кнопки ведут на route('login') / заглушку)
- Изменение footer в `MainLayout.vue`
- Управление footer-контентом из админки

## Ограничения

- Используем существующий `MainLayout.vue` для публичной части
- Используем существующий `AdminLayout.vue` для админки
- Tailwind CSS 4.x, Vue 3, Inertia.js
- Паттерн CRUD аналогичен `Admin\TourController`
- UI-компоненты из `@rosatom-ggr/ui-kit` (RCard, RBadge и т.д.)
- Данные хранятся в БД, управляются из админки (не захардкожены на фронте)
- `project_key` связывает Direction с Tour::PROJECTS для фильтрации туров

## Маршруты

### Публичные
- `GET /directions/{slug}` → `DirectionController@show` (route name: `directions.show`)

### Админка
- `GET /admin/directions` → `Admin\DirectionController@index` (route name: `admin.directions.index`)
- `GET /admin/directions/create` → `Admin\DirectionController@create`
- `POST /admin/directions` → `Admin\DirectionController@store`
- `GET /admin/directions/{direction}/edit` → `Admin\DirectionController@edit`
- `PUT /admin/directions/{direction}` → `Admin\DirectionController@update`
- `DELETE /admin/directions/{direction}` → `Admin\DirectionController@destroy`
- `PATCH /admin/directions/{direction}/toggle-active` → `Admin\DirectionController@toggleActive`

## Модель данных: Direction

| Поле | Тип | Описание |
|------|-----|----------|
| id | bigIncrements | PK |
| title | string(255) | Название направления |
| slug | string(255), unique | URL-slug |
| description | text, nullable | Описание в hero-блоке |
| image | string, nullable | Путь к изображению hero |
| project_key | string(50), nullable | Ключ из Tour::PROJECTS |
| sub_directions_title | string(255), nullable | Заголовок секции поднаправлений |
| sub_directions_description | text, nullable | Описание секции поднаправлений |
| sub_directions | JSON, nullable | [{name, description}] |
| target_audiences | JSON, nullable | [{number, title, description}] |
| target_audience_note | text, nullable | Примечание (напр. об иностранных гражданах) |
| free_participation_steps | JSON, nullable | [{title, description}] — шаги бесплатного участия |
| free_participation_details | JSON, nullable | {questions: [], challenge_title, challenge_description} |
| paid_participation_steps | JSON, nullable | [{title, description}] — шаги платного участия |
| featured_tour_ids | JSON, nullable | [tour_id, ...] — ID туров для слайдшоу |
| is_active | boolean, default true | Активность |
| position | integer, default 0 | Порядок сортировки |
| created_at | timestamp | |
| updated_at | timestamp | |

## Файлы

### Backend
- `app/Models/Direction.php`
- `app/Http/Controllers/DirectionController.php`
- `app/Http/Controllers/Admin/DirectionController.php`
- `database/migrations/XXXX_create_directions_table.php`
- `database/seeders/PortalSeeder.php` (добавление seedDirections)
- `routes/web.php` (добавление маршрутов)

### Frontend
- `resources/js/Pages/Directions/Show.vue`
- `resources/js/Pages/Admin/Directions/Index.vue`
- `resources/js/Pages/Admin/Directions/Form.vue`
- `resources/js/Layouts/AdminLayout.vue` (добавление ссылки)
- `resources/js/Pages/OpportunityTours/Index.vue` (обновление ссылок)
