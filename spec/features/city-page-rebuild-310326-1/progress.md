# Прогресс: city-page-rebuild-310326-1

## Выполнено

- T1. Миграция — `database/migrations/2026_04_01_100000_add_energy_cities_block_to_cities.php`, колонка `energy_cities_block` (JSON nullable) добавлена, миграция выполнена
- T2. Модель City — `energy_cities_block` добавлен в `$fillable`, `$casts`, accessor `getEnergyCitiesBlockAttribute` с дефолтами (`app/Models/City.php`)
- T3. Валидация — правила `energy_cities_block.*` в `store` и `update` (`app/Http/Controllers/Admin/CityController.php`)
- T4. Админ-форма — секция «Город в объективе "Энергии городов"» с полями: video_url, video_title, video_subtitle, description (RichTextEditor), button_text, button_url (`resources/js/Pages/Admin/Cities/Form.vue`)
- T5. Публичная страница — полноширинная секция с тёмно-синим фоном (#003274), декоративные круги, двухколоночный layout: видео+заголовок слева, описание+кнопка справа (`resources/js/Pages/Cities/Show.vue`)
- T6. Стили — `.energy-cities-html` scoped CSS для rich-text на тёмном фоне (белый текст, светлые ссылки) (`resources/js/Pages/Cities/Show.vue`)
- T7. Рефакторинг — `parseVideoEmbedUrl()` функция вынесена из computed, переиспользуется для обоих видео-блоков (`resources/js/Pages/Cities/Show.vue`)
- T8. Верификация — линтер чист, build успешен, progress обновлён
- T9. Миграция — `database/migrations/2026_04_01_110000_add_block_visibility_to_cities.php`, колонка `block_visibility` (JSON nullable) добавлена
- T10. Модель City — `block_visibility` в `$fillable`, `$casts`, accessor `getBlockVisibilityAttribute` с дефолтами `true` для 6 блоков (`app/Models/City.php`)
- T11. Валидация — правила `block_visibility` и `block_visibility.*` (boolean) в `store`/`update` (`app/Http/Controllers/Admin/CityController.php`)
- T12. Админ-форма — карточка «Видимость блоков» с 6 RCheckbox в правой колонке, инициализация с дефолтами true (`resources/js/Pages/Admin/Cities/Form.vue`)
- T13. Публичная страница — computed `blockVisible`, проверки `blockVisible.<key>` для Facts, Infrastructure, Video, Attractions, SocialObjects, EnergyCities (`resources/js/Pages/Cities/Show.vue`)
- T14. Верификация — линтер чист, build успешен, progress обновлён
- T15. Редактируемые заголовок/подзаголовок секции — добавлены ключи `section_title` и `section_subtitle` в JSON `energy_cities_block`: accessor модели, валидация в `store`/`update`, два поля RInput в админ-форме, динамический вывод на публичной странице вместо захардкоженного текста

## Частично выполнено

(пусто)

## Не начато

(пусто)

## Открытые вопросы

(пусто)
