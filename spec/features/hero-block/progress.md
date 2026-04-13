# Прогресс hero-block

## Completed

1. T1 — Расширение HeroSection.vue
   - `resources/js/Components/shared/HeroSection.vue` — props `bgColorFrom`, `bgColorVia`, `bgColorTo`, `textColor`, `bgColorEnabled`; при включении генерирует CSS `linear-gradient(to bottom right, from, [via,] to)`.

2. T2 — Миграция: hero-поля в directions
   - `database/migrations/2026_04_13_100000_add_hero_fields_to_directions_table.php`
   - `database/migrations/2026_04_13_110000_rename_hero_bg_color_to_gradient_in_directions.php`
   - `app/Models/Direction.php` — fillable: `hero_bg_color_from`, `hero_bg_color_via`, `hero_bg_color_to`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled`

3. T3 — Бэкенд directions: валидация + админ-контроллер
   - `app/Http/Controllers/Admin/DirectionController.php`

4. T4 — Админ-форма directions: секция Hero
   - `resources/js/Pages/Admin/Directions/Form.vue` — 3 color pickers (from/via/to), text color, image, switch

5. T5 — Фронт directions: проброс hero-props
   - `resources/js/Pages/Directions/Show.vue`
   - `resources/js/Pages/Directions/ShowAtomsVkusa.vue`

6. T6 — Бэкенд + админ MainPage: hero-цвета
   - `app/Http/Controllers/Admin/MainPageController.php`
   - `resources/js/Pages/Admin/MainPage/Index.vue`

7. T7 — Фронт MainPage: проброс hero-props
   - `resources/js/Pages/MainPage.vue`

8. T8 — Бэкенд + админ OpportunityTours: hero-цвета и изображение
   - `app/Http/Controllers/Admin/OpportunityToursPageController.php`
   - `resources/js/Pages/Admin/OpportunityToursPage/Index.vue`

9. T9 — Фронт OpportunityTours: проброс hero-props
   - `resources/js/Pages/OpportunityTours/Index.vue`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
