# Задачи — vshgr-products

## T1 — Миграция: добавить колонки type, sections, countries, regulation_file

- **Цель:** Расширить таблицу `education_products` для поддержки трёх типов продуктов
- **Scope:** `database/migrations/2026_04_07_*_add_type_sections_to_education_products.php`
- **DoD:** Миграция проходит `php artisan migrate` без ошибок; существующие записи получают `type = 'education'`
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate`

## T2 — Модель EducationProduct: обновить fillable, casts, accessors

- **Цель:** Добавить `type`, `sections`, `countries`, `regulation_file` в fillable/casts; accessor для получения секций по типу
- **Scope:** `app/Models/EducationProduct.php`
- **DoD:** Модель корректно кастит JSON-поля, `type` доступен; константы типов определены
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="echo App\Models\EducationProduct::first()->type;"`

## T3 — Контроллер: валидация type, sections, countries, regulation_file

- **Цель:** Обновить `validatedProduct()` в `EducationProductController` — валидация зависит от type
- **Scope:** `app/Http/Controllers/Admin/EducationProductController.php`
- **DoD:** Валидация проходит для всех 3 типов; некорректные данные отклоняются; `type` immutable при update
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=education-products`

## T4 — Админка: выбор типа при создании нового продукта

- **Цель:** При нажатии «Новый продукт» показать модальное окно / dropdown с выбором типа → перенаправить на `create?type=education|partner|international`
- **Scope:** `resources/js/Pages/Admin/EducationProducts/Index.vue`, контроллер `create()` принимает `?type`
- **DoD:** Кнопка «Новый продукт» открывает выбор типа; при переходе на форму `type` передаётся как prop
- **Verify:** Визуальная проверка в браузере

## T5 — Админ-форма: динамические поля по типу (базовые поля + секции education)

- **Цель:** Адаптировать `Form.vue` — показывать разные наборы полей в зависимости от `type`; для `education`: чекбоксы секций + RichText-редакторы для каждой включённой секции
- **Scope:** `resources/js/Pages/Admin/EducationProducts/Form.vue`
- **DoD:** Форма рендерит поля по типу; секции с чекбоксами сворачиваются/разворачиваются; данные сохраняются в `sections` JSON
- **Verify:** Визуальная проверка в браузере + сохранение данных

## T6 — Админ-форма: блок экспертов (тип education)

- **Цель:** Компонент для добавления/редактирования/удаления экспертов (name, position, photo, bio) внутри формы
- **Scope:** `resources/js/Pages/Admin/EducationProducts/Form.vue` (inline или отдельный компонент)
- **DoD:** Можно добавлять, удалять, редактировать экспертов; фото через ImageUploadCrop; данные хранятся в `sections.experts`
- **Verify:** Визуальная проверка + сохранение/чтение данных

## T7 — Админ-форма: поля для типов partner и international

- **Цель:** Для `partner`: описание + условия участия. Для `international`: описание и цель + редактор стран (name, slug, description, content)
- **Scope:** `resources/js/Pages/Admin/EducationProducts/Form.vue`
- **DoD:** Поля рендерятся корректно для каждого типа; данные сохраняются
- **Verify:** Визуальная проверка + сохранение/чтение данных

## T8 — Публичная страница: тип education (блочный лендинг)

- **Цель:** Переработать `Education/Show.vue` для типа `education` — секционная структура с якорной навигацией, визуально близкая к `rosatom-travel.ru/vshgr/osnovy-turisticheskogo-menedzhmenta`
- **Scope:** `resources/js/Pages/Education/Show.vue`
- **DoD:** Все включённые секции рендерятся в правильном порядке; навигация по секциям; PDF-положение открывается в новой вкладке; форма заявки работает
- **Verify:** Визуальная проверка в браузере

## T9 — Публичная страница: типы partner и international

- **Цель:** Разные визуальные шаблоны для `partner` (описание + условия) и `international` (разделение по странам)
- **Scope:** `resources/js/Pages/Education/Show.vue`
- **DoD:** Partner: простая страница с двумя блоками. International: страновые блоки с visual separator. Форма заявки при наличии секции.
- **Verify:** Визуальная проверка в браузере

## T10 — Seed-данные и каталог: обновить Index.vue под типы

- **Цель:** Обновить PortalSeeder (type для существующих продуктов), обновить каталог `Education/Index.vue` — бейдж типа на карточке, возможная фильтрация/группировка
- **Scope:** `database/seeders/PortalSeeder.php`, `resources/js/Pages/Education/Index.vue`
- **DoD:** Каталог корректно отображает продукты всех типов; бейджи типов различимы визуально
- **Verify:** `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan db:seed --class=PortalSeeder`
