# update-tour — Задачи

## Task 1: Admin — переименование labels

- **Цель**: переименовать «Город старта» → «Логистические точки» в админке
- **Scope**: `resources/js/Pages/Admin/Tours/Form.vue`, `resources/js/Pages/Admin/Tours/Index.vue`
- **DoD**: label в форме и колонка в таблице отображают «Логистические точки»
- **Verify**: визуальная проверка / grep по файлам

## Task 2: Backend — eager-loading cities

- **Цель**: обеспечить загрузку `cities` relation во всех контроллерах, отдающих туры
- **Scope**: `app/Http/Controllers/DirectionController.php`, `app/Http/Controllers/FavoriteController.php`
- **DoD**: `cities` подгружаются для всех туров, передаваемых во view
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --json | grep -c tours`

## Task 3: Каталог туров — карточка

- **Цель**: на карточке тура вывести чипсы городов проведения и «Логистические точки» вместо «start_city»
- **Scope**: `resources/js/Pages/Tours/Index.vue`
- **DoD**: чипсы `tour.cities` + текст `tour.start_city` с лейблом «Логистические точки» под ними
- **Verify**: визуальная проверка

## Task 4: Детальная страница тура

- **Цель**: обновить шапку и секцию «Город отправления» — чипсы городов + «Логистические точки»
- **Scope**: `resources/js/Pages/Tours/Show.vue`
- **DoD**: в шапке — чипсы городов + «Логистические точки: ...»; секция переименована
- **Verify**: визуальная проверка

## Task 5: Главная страница

- **Цель**: обновить карточку тура на главной — чипсы городов + логистические точки
- **Scope**: `resources/js/Pages/Home.vue`
- **DoD**: чипсы `tour.cities` отображаются, `start_city` как «Логистические точки»
- **Verify**: визуальная проверка

## Task 6: Направления (Show + ShowAtomsVkusa)

- **Цель**: обновить карточки туров на страницах направлений
- **Scope**: `resources/js/Pages/Directions/Show.vue`, `resources/js/Pages/Directions/ShowAtomsVkusa.vue`
- **DoD**: чипсы городов + логистические точки на карточках туров
- **Verify**: визуальная проверка

## Task 7: Возможности + Избранное

- **Цель**: обновить карточки туров на страницах «Возможности» и «Избранное»
- **Scope**: `resources/js/Pages/OpportunityTours/Index.vue`, `resources/js/Pages/Favorites/Index.vue`
- **DoD**: чипсы городов + логистические точки на карточках туров
- **Verify**: визуальная проверка

## Task 8: Финальная верификация

- **Цель**: убедиться, что все изменения работают, нет ошибок компиляции
- **Scope**: все изменённые файлы
- **DoD**: `npm run build` успешен, нет ошибок Vite
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
