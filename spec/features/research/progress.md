# Прогресс: research

## Completed tasks

### T1. Вынести рецепты в отдельный публичный контроллер ✓
- Files: `app/Http/Controllers/RecipeController.php` (новый), `routes/web.php`

### T2. Удалить старый Research CRUD (backend) ✓
- Files: `app/Models/Research.php` (удалён), `app/Http/Controllers/Admin/ResearchController.php` (удалён), `app/Http/Controllers/ResearchController.php` (удалён), `app/Models/City.php` (удалена связь researches), `routes/web.php`, `app/Http/Controllers/CityController.php` (удалён eager load researches)

### T3. Удалить старый Research CRUD (frontend) ✓
- Files: `resources/js/Pages/Admin/Research/Index.vue` (удалён), `resources/js/Pages/Admin/Research/Form.vue` (удалён), `resources/js/Pages/Research/Index.vue` (удалён), `resources/js/Pages/Research/Show.vue` (удалён), `resources/js/Components/ResearchCityMap.vue` (удалён), `resources/js/Pages/Cities/Show.vue` (удалена секция Researches)

### T4. Создать Admin\ResearchPageController + маршруты + навигация ✓
- Files: `app/Http/Controllers/Admin/ResearchPageController.php` (новый), `routes/web.php`, `resources/js/Layouts/AdminLayout.vue`
- Маршруты: `admin.research-page.index` (GET), `admin.research-page.update` (PUT)
- Навигация: ссылка «Исследования» перенесена в секцию «Сайт»

### T5. Создать админ Vue-страницу ✓
- Files: `resources/js/Pages/Admin/ResearchPage/Index.vue` (новый)
- Переиспользованы: `DynamicList.vue`, `SectionHeader.vue` из OpportunityToursPage

### T6. Создать публичный ResearchPageController + маршрут ✓
- Files: `app/Http/Controllers/ResearchPageController.php` (новый), `routes/web.php`
- Маршрут: `research.index` (GET /research)

### T7. Создать публичную Vue-страницу ✓
- Files: `resources/js/Pages/Research/Index.vue` (новый)
- 6 блоков: Hero, Общие задачи, Пилотные города, Статистика, Результаты, Города программы

### T8. Seeder начальных данных ✓
- Files: `database/seeders/ResearchPageSeeder.php` (новый), `database/seeders/DatabaseSeeder.php`
- Данные из оригинальной страницы rosatom-travel.ru/research

### T9. Миграция на удаление таблицы researches ✓
- Files: `database/migrations/2026_03_31_300000_drop_researches_table.php` (новый)
- Миграция выполнена

### T10. Верификация и финализация ✓
- Линтер: чист
- Маршруты: корректны
- Битые ссылки: нет

### T12. Hero-фон и градиент в Research ✓
- Добавлены поля hero: `hero_bg_image`, `hero_bg_color_from`, `hero_bg_color_via`, `hero_bg_color_to`, `hero_text_color`, `hero_bg_color_enabled`
- Админка `/admin/research-page`: загрузка/выбор фона + цветовые настройки градиента
- Публичная `/research`: прокинуты пропсы в `HeroSection` для фона/градиента/цвета текста
- Seeder обновлён новыми ключами hero
- Files: `app/Http/Controllers/Admin/ResearchPageController.php`, `resources/js/Pages/Admin/ResearchPage/Index.vue`, `resources/js/Pages/Research/Index.vue`, `database/seeders/ResearchPageSeeder.php`, `spec/features/research/spec.md`

### T13. UI KIT выравнивание формы ResearchPage ✓
- Текстовые поля описаний (`hero_description`, `results_description`) приведены к единому админ-стилю (`border`, focus-ring, error state)
- Кнопка сохранения в `/admin/research-page` переведена на `RButton variant="primary"` вместо кастомной кнопки
- Files: `resources/js/Pages/Admin/ResearchPage/Index.vue`

### T14. Остальные блоки редактора Research + shared DynamicList/CityPicker ✓
- Секции задач, городов, статистики и результатов — единый отступ `mt-4 space-y-4` после `SectionHeader`
- Добавлен пропущенный `RInput` для `tasks_title` (заголовок секции «Общие задачи»)
- `DynamicList`: единый класс полей (`border border-gray-300`, focus-ring) — затрагивает и `opportunity-tours-page`, и research
- `CityPicker`: те же стили для textarea описания города и для поля поиска
- Files: `resources/js/Pages/Admin/ResearchPage/Index.vue`, `resources/js/Pages/Admin/ResearchPage/CityPicker.vue`, `resources/js/Pages/Admin/OpportunityToursPage/DynamicList.vue`, `spec/features/research/progress.md`

## Partially completed

(пусто)

## Not started

(пусто)

### T11. Редизайн блока «Общие задачи» ✓
- Tasks теперь содержат `title` + `text` (ранее только `text`)
- Валидация обновлена: `tasks.*.title` (required)
- Админ-форма: DynamicList с двумя полями
- Публичная страница: двухколоночный layout:
  - Слева: карточки задач с заголовком + описанием, подсветка при наведении
  - Справа: орбитальная диаграмма — SVG-лого Росатома в центре, 3 орбитальных кольца, заголовки задач на орбитах
  - Взаимная подсветка: hover на орбитальном заголовке подсвечивает карточку слева и наоборот
  - Мобильная версия: кнопки-бейджи вместо орбит
- SVG-логотип: `public/images/rosatom-atom.svg` (атом без текста)
- Seeder обновлён с заголовками задач
- Files: `app/Http/Controllers/Admin/ResearchPageController.php`, `resources/js/Pages/Admin/ResearchPage/Index.vue`, `resources/js/Pages/Research/Index.vue`, `public/images/rosatom-atom.svg`, `database/seeders/ResearchPageSeeder.php`

## Open issues

(пусто)
