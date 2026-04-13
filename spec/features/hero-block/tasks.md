# Задачи hero-block

## T1 — Расширение HeroSection.vue

- **Goal:** Добавить поддержку кастомных цветов фона и текста в `HeroSection.vue`.
- **Scope:** `resources/js/Components/shared/HeroSection.vue`
- **DoD:** Props `bgColor`, `textColor`, `bgColorEnabled` работают; без них — поведение не меняется (градиент + белый). При `bgColorEnabled=true` и `bgColor` — фон заменяется на указанный цвет. При `textColor` — цвет текста и description меняется.
- **Verify:** визуально в браузере на любой странице с `HeroSection`.

## T2 — Миграция: hero-поля в directions

- **Goal:** Добавить колонки hero-настроек в таблицу `directions`.
- **Scope:** `database/migrations/XXXX_add_hero_fields_to_directions_table.php`, `app/Models/Direction.php`
- **DoD:** Колонки `hero_bg_color`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled` в таблице. Модель обновлена (`fillable`, `casts`).
- **Verify:** `php artisan migrate` через Docker.

## T3 — Бэкенд directions: валидация + админ-контроллер

- **Goal:** Добавить валидацию hero-полей в `Admin\DirectionController`.
- **Scope:** `app/Http/Controllers/Admin/DirectionController.php`
- **DoD:** `validateDirection` включает правила для `hero_bg_color`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled`.
- **Verify:** `PUT /admin/directions/{id}` с hero-полями — данные сохраняются.

## T4 — Админ-форма directions: секция Hero

- **Goal:** Добавить секцию «Hero-блок» в форму редактирования направления.
- **Scope:** `resources/js/Pages/Admin/Directions/Form.vue`
- **DoD:** Поля: color picker для `hero_bg_color`, `hero_text_color`; input для `hero_bg_image`; switch для `hero_bg_color_enabled`. Данные сохраняются и восстанавливаются.
- **Verify:** открыть форму направления в админке, заполнить hero-поля, сохранить, перезагрузить — данные на месте.

## T5 — Фронт directions: проброс hero-props

- **Goal:** Передать hero-настройки из `direction` в `HeroSection` на публичных страницах направлений.
- **Scope:** `resources/js/Pages/Directions/Show.vue`, `resources/js/Pages/Directions/ShowAtomsVkusa.vue`
- **DoD:** `HeroSection` получает `bgColor`, `textColor`, `bgColorEnabled`, `bgImage` из данных направления.
- **Verify:** на публичной странице направления с заданными hero-настройками — цвета/фон отображаются корректно.

## T6 — Бэкенд + админ MainPage: hero-цвета

- **Goal:** Добавить настройки hero-цветов для MainPage.
- **Scope:** `app/Http/Controllers/Admin/MainPageController.php`, `resources/js/Pages/Admin/MainPage/Index.vue`
- **DoD:** Валидация `hero_bg_color`, `hero_text_color`, `hero_bg_color_enabled` в `MainPageController::update`. Поля в админ-форме секции Hero.
- **Verify:** сохранить hero-цвета через админку MainPage — данные персистятся.

## T7 — Фронт MainPage: проброс hero-props

- **Goal:** Передать hero-цвета из `pageData` в `HeroSection` на MainPage.
- **Scope:** `resources/js/Pages/MainPage.vue`
- **DoD:** `HeroSection` получает `bgColor`, `textColor`, `bgColorEnabled` из `pageData`.
- **Verify:** на `/mainpage` с заданными hero-цветами — отображение корректно.

## T8 — Бэкенд + админ OpportunityTours: hero-цвета и изображение

- **Goal:** Добавить настройки hero-цветов и фонового изображения для OpportunityTours.
- **Scope:** `app/Http/Controllers/Admin/OpportunityToursPageController.php`, `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`
- **DoD:** Валидация `hero_bg_color`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled`. Поля в админ-форме.
- **Verify:** сохранить hero-настройки через админку OpportunityTours — данные персистятся.

## T9 — Фронт OpportunityTours: проброс hero-props

- **Goal:** Передать hero-настройки из `pageData` в `HeroSection` на OpportunityTours.
- **Scope:** `resources/js/Pages/OpportunityTours/Index.vue`
- **DoD:** `HeroSection` получает `bgColor`, `textColor`, `bgColorEnabled`, `bgImage` из `pageData`.
- **Verify:** на `/opportunity-tours` с заданными hero-настройками — отображение корректно.
