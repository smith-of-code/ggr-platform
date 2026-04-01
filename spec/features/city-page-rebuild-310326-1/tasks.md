# Задачи: city-page-rebuild-310326-1

## T1. Миграция — добавить колонку `energy_cities_block`
- Цель: добавить JSON nullable колонку в таблицу `cities`
- Scope: `database/migrations/`
- DoD: миграция создана, содержит `$table->json('energy_cities_block')->nullable()`
- Verify: Command Execution Pattern

## T2. Модель City — fillable + cast + accessor
- Цель: добавить `energy_cities_block` в `$fillable`, `$casts`, создать accessor для безопасного чтения
- Scope: `app/Models/City.php`
- DoD: accessor возвращает объект с дефолтными `null` для каждого поля
- Verify: Command Execution Pattern

## T3. Валидация в Admin\CityController
- Цель: добавить правила валидации для `energy_cities_block.*` в `store` и `update`
- Scope: `app/Http/Controllers/Admin/CityController.php`
- DoD: поля `video_url`, `video_title`, `video_subtitle`, `description`, `button_text`, `button_url` валидируются
- Verify: Command Execution Pattern

## T4. Админ-форма — секция «Энергия городов»
- Цель: добавить секцию с полями блока в Form.vue
- Scope: `resources/js/Pages/Admin/Cities/Form.vue`
- DoD: секция с RInput для URL/заголовков, RichTextEditor для описания, инициализация формы
- Verify: Command Execution Pattern

## T5. Публичная страница — секция «Город в объективе»
- Цель: добавить секцию на Show.vue по макету (два столбца, тёмный фон #003274)
- Scope: `resources/js/Pages/Cities/Show.vue`
- DoD: видео слева (iframe embed), описание + кнопка справа, отображается только при наличии данных
- Verify: Command Execution Pattern

## T6. Стили секции «Энергия городов»
- Цель: добавить scoped CSS для rich-text внутри тёмного блока (светлый текст)
- Scope: `resources/js/Pages/Cities/Show.vue`
- DoD: стили для `.energy-cities-html` аналогичны `.html-content` но с белым текстом
- Verify: Command Execution Pattern

## T7. Рефакторинг videoEmbedSrc в утилитарную функцию
- Цель: вынести логику парсинга YouTube/RuTube URL в переиспользуемую функцию
- Scope: `resources/js/Pages/Cities/Show.vue`
- DoD: функция `parseVideoEmbedUrl(url)` используется и для старого видео, и для нового блока
- Verify: Command Execution Pattern

## T8. Верификация и финализация (фаза 1)
- Цель: проверка линтером, обновление progress.md
- Scope: все затронутые файлы
- DoD: линтер чист, progress обновлён
- Verify: Command Execution Pattern

---

## T9. Миграция — колонка `block_visibility`
- Цель: добавить JSON nullable колонку `block_visibility` в таблицу `cities`
- Scope: `database/migrations/`
- DoD: миграция создана, содержит `$table->json('block_visibility')->nullable()->after('energy_cities_block')`
- Verify: Command Execution Pattern

## T10. Модель City — fillable, cast, accessor для `block_visibility`
- Цель: добавить `block_visibility` в `$fillable`, `$casts`; accessor с дефолтами `true` для всех 6 блоков
- Scope: `app/Models/City.php`
- DoD: accessor возвращает `{ facts: true, infrastructure: true, video: true, attractions: true, social_objects: true, energy_cities_block: true }` при null
- Verify: Command Execution Pattern

## T11. Валидация `block_visibility` в CityController
- Цель: добавить правила валидации для `block_visibility` и вложенных boolean полей в `store`/`update`
- Scope: `app/Http/Controllers/Admin/CityController.php`
- DoD: `block_visibility` и `block_visibility.*` валидируются как boolean
- Verify: Command Execution Pattern

## T12. Админ-форма — переключатели видимости блоков
- Цель: добавить карточку с RCheckbox для каждого блока в правую колонку формы
- Scope: `resources/js/Pages/Admin/Cities/Form.vue`
- DoD: 6 переключателей в отдельной RCard, инициализация формы с дефолтами true
- Verify: Command Execution Pattern

## T13. Публичная страница — условный рендер по visibility
- Цель: обернуть блоки Facts/Infrastructure/Video/Attractions/SocialObjects/EnergyCities в проверку `block_visibility`
- Scope: `resources/js/Pages/Cities/Show.vue`
- DoD: каждый из 6 блоков отображается только при `block_visibility.<key> !== false` И наличии данных
- Verify: Command Execution Pattern

## T14. Верификация фазы 2
- Цель: линтер, build, обновление progress.md
- Scope: все затронутые файлы
- DoD: линтер чист, build успешен, progress обновлён
- Verify: Command Execution Pattern
