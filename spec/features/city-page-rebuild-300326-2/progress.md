# Прогресс: city-page-rebuild-300326-2

## Выполнено

- T1. Миграция — добавлен `coat_of_arms` (nullable text) в `cities` (`database/migrations/2026_03_31_200000_add_coat_of_arms_to_cities.php`)
- T2. Модель City — добавлен `coat_of_arms` в `$fillable` (`app/Models/City.php`)
- T3. Валидация — правило `coat_of_arms => nullable|string|max:2048` в `store()` и `update()` (`app/Http/Controllers/Admin/CityController.php`)
- T4. Админ-форма — добавлен `ImageUploadCrop` для герба в правую колонку, поле `form.coat_of_arms`, `skipCrop` + `preview-class` для `object-contain` (`resources/js/Pages/Admin/Cities/Form.vue`, `resources/js/Components/ImageUploadCrop.vue`)
- T5. Hero-баннер — герб отображается слева от `h1`; JPEG — с рамкой (`ring-2 ring-white/60 rounded-lg bg-white/20 p-1 shadow-lg`), PNG/WebP — `drop-shadow` (`resources/js/Pages/Cities/Show.vue`)
- T6. Верификация — миграция прошла, линтер чист
- T7. Обновление progress.md

## Частично выполнено

(пусто)

## Не начато

(пусто)

## Открытые вопросы

(пусто)
