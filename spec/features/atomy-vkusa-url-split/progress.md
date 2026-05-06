# Прогресс фичи atomy-vkusa-url-split

## Режим: Sequential

## Не начатые

- (нет)

## Частично выполненные

- (нет)

## Завершённые

- **Шаг 1 — Backend** ✓
  - `app/Http/Controllers/AtomsVkusaPageController.php` (новый): загружает `Direction(slug=atomy-vkusa)`, `AtomsVkusaContent`, рецепты с фильтром `recipe_city`, города, новости (`category=atoms_vkusa`), `paidForm` через приватный helper. Рендерит `Directions/ShowAtomsVkusa` с теми же props, что и прежний `DirectionController::showAtomsVkusa()`.
  - `app/Http/Controllers/DirectionController.php` (упрощение): удалена ветка `if ($direction->slug === 'atomy-vkusa')`, удалён private `showAtomsVkusa()`, убраны лишние `use` (`AtomsVkusaContent`, `City`, `Post`, `Recipe`).
  - `routes/web.php`: добавлен `Route::get('/atomy-vkusa', [AtomsVkusaPageController::class, 'show'])->name('atomy-vkusa.show');` рядом с `directions.show`. Добавлен `use` для нового контроллера.
  - `config/page_visibility.php`: `route_prefix` для `atomy-vkusa` изменён с `/directions/atomy-vkusa` на `/atomy-vkusa`.
- **Шаг 2 — Frontend** ✓
  - `resources/js/Layouts/MainLayout.vue`: пункт меню «Атомы вкуса» — `href: route('atomy-vkusa.show')`, `active: page.url.startsWith('/atomy-vkusa')`.
  - `resources/js/Pages/Directions/ShowAtomsVkusa.vue`: `filterRecipes()` теперь отправляет `router.get(route('atomy-vkusa.show'), ...)` вместо `route('directions.show', props.direction.slug)`.
- **Verify** ✓
  - `php artisan route:list` показывает `GET /atomy-vkusa → atomy-vkusa.show → AtomsVkusaPageController@show` и сохранённый `GET /directions/{slug} → directions.show`.
  - `/atomy-vkusa` (под middleware `CheckPageVisibility` для гостей) рендерит `PageUnderConstruction` — потому что страница `atomy-vkusa` помечена скрытой в админке `Видимость страниц` (флаг автоматически переехал вместе с новым префиксом). Прямой вызов `AtomsVkusaPageController::show()` корректно рендерит `Directions/ShowAtomsVkusa` со всеми ожидаемыми props.
  - `/directions/atomy-vkusa` рендерит `Directions/Show` с данными `Direction` id=2 — заказчик теперь видит контент, заполненный в `/admin/directions/2/edit`.
  - `npm run build` прошёл без ошибок (`built in 12.80s`).

## Открытые вопросы

- Заказчику: чтобы Грантовый конкурс снова стал доступен публично, нужно в админке `Видимость страниц` включить страницу «Атомы вкуса» (флаг скрытия был установлен ранее, до этой фичи).
- Опционально на будущее: добавить 301-редирект со старого `/directions/atomy-vkusa` на `/atomy-vkusa` для устаревших закладок/ссылок (не входило в scope, заказчик не просил).
