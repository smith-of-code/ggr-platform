# Задачи: research

## T1. Вынести рецепты в отдельный публичный контроллер

- **Цель**: Отвязать рецепты от `ResearchController` перед его удалением
- **Скоуп**: `app/Http/Controllers/RecipeController.php` (новый), `routes/web.php`
- **DoD**: Маршруты `/recipes`, `/recipes/{slug}` работают через новый `RecipeController`
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=recipes`

## T2. Удалить старый Research CRUD (backend)

- **Цель**: Удалить модель `Research`, `Admin\ResearchController`, оставшийся `ResearchController`, связь `researches()` в `City`
- **Скоуп**: `app/Models/Research.php`, `app/Http/Controllers/Admin/ResearchController.php`, `app/Http/Controllers/ResearchController.php`, `app/Models/City.php`, `routes/web.php`
- **DoD**: Маршруты `/admin/research/*` и `/research/{slug}` удалены, модель удалена, связь в City удалена
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=research`

## T3. Удалить старый Research CRUD (frontend)

- **Цель**: Удалить Vue-страницы старого Research и компонент карты
- **Скоуп**: `resources/js/Pages/Admin/Research/` (Index.vue, Form.vue), `resources/js/Pages/Research/` (Index.vue, Show.vue), `resources/js/Components/ResearchCityMap.vue`
- **DoD**: Все перечисленные файлы удалены
- **Verify**: файлы не существуют

## T4. Создать Admin\ResearchPageController + маршруты + навигация

- **Цель**: Админ-контроллер для управления блоками страницы, обновить навигацию
- **Скоуп**: `app/Http/Controllers/Admin/ResearchPageController.php` (новый), `routes/web.php`, `resources/js/Layouts/AdminLayout.vue`
- **DoD**: Маршруты `admin.research-page.index` и `admin.research-page.update` работают; в сайдбаре ссылка «Исследования» ведёт на новую страницу
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=admin/research-page`

## T5. Создать админ Vue-страницу

- **Цель**: Форма редактирования всех блоков страницы исследований
- **Скоуп**: `resources/js/Pages/Admin/ResearchPage/Index.vue` (новый)
- **DoD**: Форма со всеми секциями (hero, задачи, пилотные города, статистика, результаты, города программы), сохранение через PUT
- **Verify**: Страница `/admin/research-page` открывается, форма отправляется

## T6. Создать публичный ResearchPageController + маршрут

- **Цель**: Контроллер для публичной страницы /research
- **Скоуп**: `app/Http/Controllers/ResearchPageController.php` (новый), `routes/web.php`
- **DoD**: Маршрут `GET /research` → `ResearchPageController@index`, данные из settings передаются в Inertia
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=research`

## T7. Создать публичную Vue-страницу

- **Цель**: Страница `/research` со всеми 6 блоками по дизайну оригинала
- **Скоуп**: `resources/js/Pages/Research/Index.vue` (новый)
- **DoD**: Все блоки отрисовываются из `pageData`, адаптивная вёрстка, анимации reveal
- **Verify**: Страница открывается в браузере, все блоки отображаются

## T8. Seeder начальных данных

- **Цель**: Заполнить settings группу `research_page` данными с оригинальной страницы
- **Скоуп**: `database/seeders/ResearchPageSeeder.php` (новый), `database/seeders/DatabaseSeeder.php`
- **DoD**: Seeder создаёт все ключи группы, данные соответствуют оригиналу
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan db:seed --class=ResearchPageSeeder`

## T9. Миграция на удаление таблицы researches

- **Цель**: Убрать неиспользуемую таблицу из БД
- **Скоуп**: `database/migrations/YYYY_MM_DD_HHMMSS_drop_researches_table.php` (новый)
- **DoD**: Миграция удаляет таблицу `researches`, down-метод пересоздаёт её
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate --pretend`

## T10. Верификация и финализация

- **Цель**: Проверить линтер, обновить spec, закрыть progress
- **Скоуп**: spec/features/research/*.md
- **DoD**: Линтер чист, все задачи в progress.md отмечены как завершённые
- **Verify**: Линтер без ошибок
