# Модуль 5. ВШГР — Информационная часть

## Статус: Реализован

## Описание
Раздел «Высшая школа гостеприимного развития» (ВШГР) — каталог образовательных продуктов,
детальные страницы программ, формы заявок, а также управление продуктами и LMS-курсами в админке.

Навигация: пункт «ВШГР» в `MainLayout.vue` (slug `education`).
Видимость страницы управляется через `config/page_visibility.php` (slug `education`, route_prefix `/vshgr`).

---

## Публичная часть

### Главная ВШГР — `Education/Index`

URL: `GET /vshgr` → `EducationController@index` → Inertia `Education/Index`

**Props от контроллера:**
| Prop | Тип | Описание |
|------|-----|----------|
| `products` | `EducationProduct[]` | Активные продукты (`is_active=true`), сортировка: `position ASC`, `title ASC` |
| `latestAnnouncements` | `Post[]` | Последние 3 опубликованных поста категории `announcements` (по `published_at DESC`) |

**Секции страницы (сверху вниз):**
1. **Hero** — градиент `#003274`, текст «Высшая школа гостеприимного развития», описание миссии.
2. **Каталог «Программы обучения»** — сетка карточек (1/2/3 колонки по брейкпоинтам).
   - Каждая карточка: изображение (или заглушка-иконка), бейджи `duration` (белый) и `format` (синий),
     заголовок, краткое описание (strip HTML, `line-clamp-3`), кнопка «Подробнее» → `education.show`.
   - Если продуктов нет — заглушка «Программы скоро появятся в каталоге.»
3. **Анонсы и новости** — показывается только если есть `latestAnnouncements`.
   Карточки: изображение, дата (формат `ru-RU`), заголовок, excerpt.
4. **CTA-секция** — градиентный блок с якорной ссылкой `#application-form`.
5. **Положение** — блок со светло-серым фоном (`#e9eef4`), кнопка-ссылка «Положение» (иконка скачивания + текст),
   подпись «Положение о грантовом конкурсе "Высшая школа гостеприимства Росатома"».
   URL документа хранится в константе `regulationUrl` (фронтенд). Текущий URL: Яндекс.Диск (временный, ожидается финальный документ от юристов).
6. **Социальные сети** — белый фон, заголовок «Мы в социальных сетях», подзаголовок, ряд карточек-ссылок (иконка SVG + название).
   Визуал идентичен блоку на `/opportunity-tours`. Иконки из `@/utils/opportunityToursIcons.js` (`socialIcon`).
   Данные: массив `socials` (name, url, icon) — захардкожены на фронтенде.
7. **Форма заявки** — блок `id="application-form"`, подробности ниже.

### Детальная страница продукта — `Education/Show`

URL: `GET /vshgr/{slug}` → `EducationController@show` → Inertia `Education/Show`

**Props от контроллера:**
| Prop | Тип | Описание |
|------|-----|----------|
| `product` | `EducationProduct` | Продукт по slug, `is_active=true`, иначе 404 |

**Секции страницы (сверху вниз):**
1. **Навигация назад** — ссылка `education.index` «Каталог программ».
2. **Hero** — двухколоночный блок (изображение + текст). Бейджи: `duration`, `format`, `price_info`.
3. **Контент** — `v-html` для поля `content` (стилизация через scoped-класс `.html-content`).
4. **Целевая аудитория** — выделенный блок, `v-html` для `target_audience`.
5. **Форма заявки** — блок `id="application-form"`.

**Scoped-стили `.html-content`:**
Стилизация вложенных `p`, `a`, `ul`, `ol`, `h2`, `h3`, `img` через `:deep()`.

---

## Форма заявки (общая для Index и Show)

Использует `useForm` из `@inertiajs/vue3`.

**Поля формы:**
| Поле | HTML-тип | required | Описание |
|------|----------|----------|----------|
| `type` | hidden | — | Всегда `program_info` |
| `name` | text | да | Имя |
| `email` | email | да | Email |
| `phone` | tel | нет | Телефон |
| `message` | textarea | нет | Сообщение |

**Отправка:** `POST` на `route('applications.store')` — обрабатывается `ApplicationController@store`.
После успеха: `form.reset()`, flash-сообщение `$page.props.flash.success`.

---

## Админка

### Список продуктов — `Admin/EducationProducts/Index`

URL: `GET /admin/education-products` → `EducationProductController@index`

**Props:**
| Prop | Тип | Описание |
|------|-----|----------|
| `products` | `LengthAwarePaginator` | Пагинация (15 записей), сортировка `position ASC`, `id ASC` |
| `lmsCourses` | `LmsCourse[]` | Все LMS-курсы с `withCount('enrollments')` (если таблица существует, иначе пустая коллекция) |

**Таблица продуктов:** колонки — Название (+ slug), Длительность, Формат, Активен (RBadge), Позиция, Действия (Редактировать, Удалить).
Пагинация отображается при `last_page > 1`.
Удаление через `confirm()` + `router.delete`.

**Блок LMS-курсов ВШГР** (показывается если `lmsCourses.length > 0`):
Таблица: Курс (изображение + название + описание), Slug, Записаны (`enrollments_count`), Даты, Публикация (кнопка toggle).
Toggle → `PATCH /admin/education-products/course/{course}/toggle` → `toggleCourseActive`.

### Форма создания/редактирования — `Admin/EducationProducts/Form`

URL создания: `GET /admin/education-products/create` → `EducationProductController@create`
URL редактирования: `GET /admin/education-products/{education_product}/edit` → `EducationProductController@edit`

**Props:**
| Prop | Тип | Описание |
|------|-----|----------|
| `product` | `EducationProduct\|null` | `null` при создании, объект при редактировании |

**Поля формы:**
| Поле | Компонент | Описание |
|------|-----------|----------|
| `title` | `RInput` | Название * |
| `slug` | `RInput` | Slug URL — автогенерация через транслитерацию кириллицы при вводе title (отключается при ручном вводе slug или при редактировании) |
| `description` | `<textarea>` | Краткое описание |
| `content` | `RichTextEditor` | Содержание программы (с `upload-url` и `media-picker-url`) |
| `image` | `ImageUploadCrop` | Изображение (с `upload-url` и `media-picker-url`) |
| `duration` | `RInput` | Длительность |
| `format` | `RInput` | Формат |
| `target_audience` | `<textarea>` | Целевая аудитория |
| `price_info` | `RInput` | Информация о стоимости |
| `position` | `RInput[number]` | Позиция в списке |
| `is_active` | `RCheckbox` | Активен |

**Дополнительные возможности:**
- Кнопка «Предпросмотр» — компонент `ContentPreview` (модальное окно с метаданными и target_audience).
- Media collection: `education_products`, entity type: `App\Models\EducationProduct`.

**Сохранение:**
- Создание: `POST /admin/education-products` → `store`.
- Обновление: `PUT /admin/education-products/{id}` → `update`.
- `position` нормализуется в Number (default 0).

---

## Валидация (серверная, `EducationProductController`)

| Поле | Правила |
|------|---------|
| `title` | `required\|string\|max:255` |
| `slug` | `required\|string\|max:255\|unique:education_products,slug,{id}` |
| `description` | `nullable\|string` |
| `content` | `nullable\|string` |
| `image` | `nullable\|string\|max:2048` |
| `duration` | `nullable\|string\|max:255` |
| `format` | `nullable\|string\|max:255` |
| `target_audience` | `nullable\|string` |
| `price_info` | `nullable\|string\|max:255` |
| `position` | `nullable\|integer\|min:0` |
| `is_active` | `boolean` |

`is_active` при store/update: `$request->boolean('is_active', true)`.
`position` при store/update: default `0`.

---

## Модель `EducationProduct`

**Таблица:** `education_products`
**Миграция:** `2026_03_25_100000_create_portal_modules_tables.php`

| Колонка | Тип (migration) | Nullable | Default |
|---------|-----------------|----------|---------|
| `id` | `bigIncrements` | нет | auto |
| `title` | `string` | нет | — |
| `slug` | `string` (unique) | нет | — |
| `description` | `text` | да | null |
| `content` | `longText` | да | null |
| `image` | `string` | да | null |
| `duration` | `string` | да | null |
| `format` | `string` | да | null |
| `target_audience` | `text` | да | null |
| `price_info` | `string` | да | null |
| `position` | `integer` | нет | 0 |
| `is_active` | `boolean` | нет | true |
| `created_at` | `timestamp` | да | null |
| `updated_at` | `timestamp` | да | null |

**Fillable:** все колонки кроме `id`, `created_at`, `updated_at`.
**Casts:** `is_active` → `boolean`.
**Relationships:** нет.

---

## Маршруты

### Публичные (без middleware auth)

| Метод | URI | Controller@action | Route name |
|-------|-----|-------------------|------------|
| GET | `/vshgr` | `EducationController@index` | `education.index` |
| GET | `/vshgr/{slug}` | `EducationController@show` | `education.show` |

### Админские (middleware: `auth`, prefix: `/admin`)

| Метод | URI | Controller@action | Route name |
|-------|-----|-------------------|------------|
| GET | `/admin/education-products` | `EducationProductController@index` | `admin.education-products.index` |
| GET | `/admin/education-products/create` | `EducationProductController@create` | `admin.education-products.create` |
| POST | `/admin/education-products` | `EducationProductController@store` | `admin.education-products.store` |
| GET | `/admin/education-products/{education_product}/edit` | `EducationProductController@edit` | `admin.education-products.edit` |
| PUT/PATCH | `/admin/education-products/{education_product}` | `EducationProductController@update` | `admin.education-products.update` |
| DELETE | `/admin/education-products/{education_product}` | `EducationProductController@destroy` | `admin.education-products.destroy` |
| PATCH | `/admin/education-products/course/{course}/toggle` | `EducationProductController@toggleCourseActive` | `admin.education-products.toggleCourse` |

---

## Seed-данные (`PortalSeeder`)

3 образовательных продукта (updateOrCreate по slug):
1. `osnovy-turisticheskogo-menedzhmenta` — «Основы туристического менеджмента», 3 месяца, Онлайн
2. `gostepriimstvo-atomnyh-gorodov` — «Гостеприимство атомных городов», 2 месяца, Смешанный
3. `sobytiynyy-menedzhment` — «Событийный менеджмент для территорий», 1.5 месяца, Очно

---

## Файлы

### Бэкенд
- `app/Http/Controllers/EducationController.php` — публичный контроллер (index, show)
- `app/Http/Controllers/Admin/EducationProductController.php` — админский CRUD + toggleCourseActive
- `app/Models/EducationProduct.php` — Eloquent-модель
- `database/migrations/2026_03_25_100000_create_portal_modules_tables.php` — миграция (совместная с другими модулями)
- `database/seeders/PortalSeeder.php` — seed-данные
- `config/page_visibility.php` — конфигурация видимости страницы (slug `education`)

### Фронтенд
- `resources/js/Pages/Education/Index.vue` — публичный каталог ВШГР
- `resources/js/Pages/Education/Show.vue` — публичная детальная страница продукта
- `resources/js/Pages/Admin/EducationProducts/Index.vue` — админский список продуктов + LMS-курсы
- `resources/js/Pages/Admin/EducationProducts/Form.vue` — админская форма создания/редактирования

### Используемые переиспользуемые компоненты
- `RichTextEditor` — WYSIWYG-редактор с загрузкой изображений и медиа-picker
- `ImageUploadCrop` — загрузка и обрезка изображений
- `ContentPreview` — модальный предпросмотр контента
- `RCard`, `RBadge`, `RButton`, `RInput`, `RCheckbox` — UI-kit

---

## Правки заказчика (2026-04-07)

### REV-001 — Нормальная валидация email и телефона в форме заявки [BEHAVIOR_CHANGE]

Заказчик запросил полноценную (не стандартную) валидацию полей email и телефон в формах заявки на страницах Index и Show.

**Серверная валидация (`ApplicationController@store`):**
- Email: `email:rfc,dns` — проверка RFC-формата + DNS MX/A-записи домена.
- Телефон: нормализация (удаление нецифровых символов, замена ведущей 8→7), проверка формата `7XXXXXXXXXX` (11 цифр), сохранение в формате `+7XXXXXXXXXX`.
- Русскоязычные сообщения об ошибках.

**Клиентская валидация (Education/Index.vue, Education/Show.vue):**
- Маска телефона: автоформатирование при вводе → `+7 (XXX) XXX-XX-XX`.
- Placeholder: `+7 (___) ___-__-__`.
- Клиентская pre-validation email на blur (regex).
- Блокировка отправки при невалидных полях.

**Примечание:** `ApplicationController` — общий для всех типов заявок (tour, research, program_info, atoms_vkusa). Улучшение валидации распространяется на все формы проекта.

### REV-002 — Блок социальных сетей на странице ВШГР [UI_TWEAK]

Добавить блок «Мы в социальных сетях» на главную ВШГР — визуал идентичен `/opportunity-tours`.
Размещение: между секцией «Положение» и формой заявки.
Иконки: переиспользование `socialIcon()` из `@/utils/opportunityToursIcons.js`.
Данные: статичный массив (ВКонтакте + Telegram).
