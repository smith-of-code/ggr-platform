# Задачи: Герб города в баннере

## T1. Миграция — добавить `coat_of_arms`

- **Цель**: новая колонка `coat_of_arms` (nullable text) в таблице `cities`
- **Scope**: `database/migrations/2026_03_31_200000_add_coat_of_arms_to_cities.php`
- **DoD**: миграция создана, выполняется без ошибок
- **Verify**: `docker exec … php artisan migrate`

## T2. Модель City — `$fillable`

- **Цель**: добавить `coat_of_arms` в `$fillable`
- **Scope**: `app/Models/City.php`
- **DoD**: поле в массиве `$fillable`
- **Verify**: lint (без Docker)

## T3. Валидация в Admin\CityController

- **Цель**: правило `'coat_of_arms' => 'nullable|string|max:2048'` в `store()` и `update()`
- **Scope**: `app/Http/Controllers/Admin/CityController.php`
- **DoD**: правило присутствует в обоих методах
- **Verify**: lint

## T4. Админ-форма — поле загрузки герба

- **Цель**: добавить `ImageUploadCrop` для `coat_of_arms` в правую колонку формы
- **Scope**: `resources/js/Pages/Admin/Cities/Form.vue`
- **DoD**: поле герба отображается в форме, значение сохраняется через `form.coat_of_arms`
- **Verify**: визуально в браузере

## T5. Hero-баннер — отображение герба

- **Цель**: показать герб в hero-секции рядом с названием города
- **Scope**: `resources/js/Pages/Cities/Show.vue`
- **DoD**: герб отображается слева от `h1`; JPEG — с рамкой, PNG/WebP — без рамки; адаптивен
- **Verify**: визуально в браузере

## T6. Верификация — миграция + lint

- **Цель**: убедиться, что миграция проходит, линтер чист
- **Scope**: `docker exec … php artisan migrate`, lint
- **DoD**: всё зелёное
- **Verify**: вывод команд

## T7. Обновление progress.md

- **Цель**: финальное обновление прогресса
- **Scope**: `spec/features/city-page-rebuild-300326-2/progress.md`
- **DoD**: все задачи в «Выполнено»
- **Verify**: —
