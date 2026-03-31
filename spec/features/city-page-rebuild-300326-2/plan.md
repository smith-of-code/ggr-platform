# План: Герб города в баннере

## Milestones

1. **Миграция** — добавить колонку `coat_of_arms` (nullable text) в `cities`
2. **Модель** — добавить поле в `$fillable`
3. **Валидация** — правила в `Admin\CityController::store` и `update`
4. **Админ-форма** — поле `ImageUploadCrop` для герба в правой колонке формы
5. **Hero-баннер** — отобразить герб в hero-секции `Cities/Show.vue` с условной рамкой
6. **Верификация** — миграция, lint, визуальная проверка

## UI Components

- `ImageUploadCrop` (существующий) — для загрузки герба в админке
- Кастомная разметка в hero-баннере `Cities/Show.vue`:
  - Контейнер герба: `w-16 h-16 sm:w-20 sm:h-20` рядом с `h1`
  - Условные классы: JPEG → белая рамка (`ring-2 ring-white/80 shadow-lg rounded-lg bg-white/20 p-1`), PNG/WebP → без рамки (`drop-shadow-lg`)
  - Определение по расширению: `.jpg`/`.jpeg` → рамка, иначе → без рамки

## Verification

Команды выполняются по паттерну из spec-continuation (Docker exec через `source docker/.env.local`).
