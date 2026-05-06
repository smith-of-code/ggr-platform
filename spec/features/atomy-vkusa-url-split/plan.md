# План реализации

## Шаг 1 — Backend (≤4 файлов)

1. Создать `app/Http/Controllers/AtomsVkusaPageController.php` с методом `show(Request)` — копия логики `DirectionController::showAtomsVkusa()`:
   - Загрузить `Direction::where('slug','atomy-vkusa')->where('is_active', true)->firstOrFail()`
   - Подгрузить `featuredTours` по `featured_tour_ids` из Direction
   - Загрузить `paidForm` через приватный helper по `paid_form_slug`
   - `AtomsVkusaContent::content()`, `Recipe::paginate(12)` с фильтром `recipe_city`, `City::where('is_active',true)`, `Post::where('category','atoms_vkusa')`
   - Рендер `Directions/ShowAtomsVkusa` с теми же props, что и сейчас.
2. В `app/Http/Controllers/DirectionController.php` удалить:
   - ветку `if ($direction->slug === 'atomy-vkusa') { return $this->showAtomsVkusa(...); }`
   - приватный метод `showAtomsVkusa(...)`
   - неиспользуемые `use`: `AtomsVkusaContent`, `City`, `Post`, `Recipe`
   - Метод `loadPaidForm(Direction)` оставить — он используется как для обычных направлений, так и для нового AtomsVkusaPageController (импортируется отдельно копией).
3. В `routes/web.php` добавить:
   ```php
   Route::get('/atomy-vkusa', [AtomsVkusaPageController::class, 'show'])->name('atomy-vkusa.show');
   ```
   рядом с `/directions/{slug}`.
4. В `config/page_visibility.php` поменять `route_prefix` пункта `atomy-vkusa`: `/directions/atomy-vkusa` → `/atomy-vkusa`.

### Verify шага 1

```bash
source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --columns=Method,URI,Name | grep -E "(atomy-vkusa|directions)"
```

Ожидаем:
- `GET /atomy-vkusa` → `atomy-vkusa.show`
- `GET /directions/{slug}` → `directions.show`

## Шаг 2 — Frontend (2 файла)

5. `resources/js/Layouts/MainLayout.vue:258`:
   - `href`: `route('atomy-vkusa.show')`
   - `active`: `page.url.startsWith('/atomy-vkusa')`
6. `resources/js/Pages/Directions/ShowAtomsVkusa.vue:553`:
   - `router.get(route('atomy-vkusa.show'), params, {...})`

### Verify шага 2

```bash
source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build
```

Сборка должна пройти без ошибок. Дополнительно (опционально, если доступен браузер):
- `/atomy-vkusa` → Грантовый конкурс
- `/directions/atomy-vkusa` → стандартная страница направления (Direction id=2)

## Шаг 3 — Финализация spec

7. Обновить `spec/features/atomy-vkusa-url-split/progress.md` (Completed tasks).
8. В `spec/90-open-questions.md` пометить п. 11 как решённый (через зачёркивание + ссылка на эту фичу).
