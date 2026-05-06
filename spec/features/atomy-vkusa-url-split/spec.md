# Развязка URL: Грантовый конкурс «Атомы вкуса» ↔ направление

## Статус: Реализован

## Цель

Убрать жёсткую привязку шаблона Грантового конкурса к URL `/directions/atomy-vkusa`. Грантовый конкурс выезжает на отдельный публичный URL `/atomy-vkusa`. URL `/directions/atomy-vkusa` начинает работать как обычная страница `Direction` (рендер `Pages/Directions/Show.vue`) — это позволяет заказчику показать данные `Direction` id=2, заполненные в `/admin/directions/2/edit`, и привязывать «Атомы вкуса как направление» в `/admin/opportunity-tours-page`.

## Проблема (до)

В `app/Http/Controllers/DirectionController::show()` для любого `Direction` со `slug='atomy-vkusa'` принудительно рендерится `Directions/ShowAtomsVkusa` (Грантовый конкурс) с данными из `AtomsVkusaContent`. Поля, которые заказчик заполняет в админке `Direction` id=2, на публичной странице направления не видно. Также этот же URL зашит в шапке сайта (`MainLayout.vue`) и в `config/page_visibility.php`.

## В scope

- Новый публичный маршрут `GET /atomy-vkusa` → `AtomsVkusaPageController@show`, route name `atomy-vkusa.show`
- Новый контроллер `App\Http\Controllers\AtomsVkusaPageController` — переносит логику текущего `DirectionController::showAtomsVkusa()` (загрузка `Direction`(slug='atomy-vkusa') + `AtomsVkusaContent` + рецепты + новости + paidForm) под отдельный URL
- Удаление override-ветки `if ($direction->slug === 'atomy-vkusa')` в `DirectionController::show()` и приватного метода `showAtomsVkusa()`
- Чистка `use`-импортов в `DirectionController` (`AtomsVkusaContent`, `City`, `Post`, `Recipe`)
- Обновление пункта меню «Атомы вкуса» в `resources/js/Layouts/MainLayout.vue`: `route('directions.show', 'atomy-vkusa')` → `route('atomy-vkusa.show')`, `active`-проверка по `/atomy-vkusa`
- Обновление `config/page_visibility.php`: `route_prefix` пункта `atomy-vkusa` → `/atomy-vkusa`
- Обновление `resources/js/Pages/Directions/ShowAtomsVkusa.vue`: фильтр рецептов в `filterRecipes()` ходит на `route('atomy-vkusa.show')` вместо `route('directions.show', props.direction.slug)`

## Вне scope

- Перенос полей hero/featured_tour_ids/paid_form_slug из `Direction` в `AtomsVkusaContent`. Грантовый конкурс по-прежнему использует `Direction` со slug `atomy-vkusa` как источник этих полей (заказчик может в дальнейшем создать отдельный `Direction` со своим slug под «Атомы Вкуса как направление»).
- 301-редирект со старого URL `/directions/atomy-vkusa` на новый `/atomy-vkusa` (заказчик не просил, легко добавить отдельной задачей).
- Изменение шаблона `Directions/Show.vue` — он уже рендерит все секции `Direction` и подходит под «Атомы Вкуса как направление» без правок.
- Изменение администрирования `AtomsVkusaContent` (`/admin/atoms-vkusa`).

## Ограничения

- Docker-only выполнение команд.
- Max 5 файлов на шаг → разделено на 2 шага реализации (бэкенд + фронт).
- `Direction` со slug `atomy-vkusa` в БД остаётся (используется обоими URL).
- Минимум изменений в шаблоне `ShowAtomsVkusa.vue` — только смена адресата `router.get` в `filterRecipes`.

## Маршруты

### Добавляем
- `GET /atomy-vkusa` → `AtomsVkusaPageController@show` (route name: `atomy-vkusa.show`)

### Меняем поведение
- `GET /directions/{slug}` → `DirectionController@show` — теперь без override; для `slug='atomy-vkusa'` рендерится `Directions/Show` (как для прочих направлений).

### Удаляем
- (нет; `/admin/atoms-vkusa.*` маршруты не трогаем)

## Затрагиваемые файлы

### Backend (шаг 1)
- `app/Http/Controllers/AtomsVkusaPageController.php` (новый)
- `app/Http/Controllers/DirectionController.php` (упрощение)
- `routes/web.php` (новый маршрут)
- `config/page_visibility.php` (правка `route_prefix`)

### Frontend (шаг 2)
- `resources/js/Layouts/MainLayout.vue` (пункт меню)
- `resources/js/Pages/Directions/ShowAtomsVkusa.vue` (`filterRecipes`)

## Acceptance

1. `/atomy-vkusa` → страница Грантового конкурса (`Directions/ShowAtomsVkusa.vue`) с тем же содержимым, что и сейчас.
2. `/directions/atomy-vkusa` → страница направления (`Directions/Show.vue`) с данными `Direction` id=2.
3. Пункт меню «Атомы вкуса» в шапке ведёт на `/atomy-vkusa`, активен на `/atomy-vkusa*`.
4. В блоке «Проекты программы» на `/opportunity-tours` элемент типа `direction` со ссылкой на `Direction` id=2 ведёт на `/directions/atomy-vkusa` (страница направления).
5. Фильтр рецептов на странице Грантового конкурса остаётся рабочим (`?recipe_city=...` по `/atomy-vkusa`).
6. `php artisan route:list` показывает маршрут `atomy-vkusa.show` и сохранённый `directions.show`.
