# Progress — city-order-tour-button

## Выполненные задачи

- Task 1 — Миграция: добавить show_order_tour_button в cities
  - `database/migrations/2026_05_07_110000_add_show_order_tour_button_to_cities_table.php`

- Task 2 — Модель City + контроллер Admin\CityController
  - `app/Models/City.php` — добавлено в `$fillable`, `$casts`
  - `app/Http/Controllers/Admin/CityController.php` — добавлена валидация в `store` и `update`

- Task 3 — Admin/Cities/Form.vue: чекбокс «Показывать кнопку "Заказать тур"»
  - `resources/js/Pages/Admin/Cities/Form.vue` — `RCheckbox` в правой колонке, поле в `form`

- Task 4 — Cities/Show.vue: кнопка «Заказать тур» в hero-блоке
  - `resources/js/Pages/Cities/Show.vue` — кнопка цвета `#C91E5B` при `city.show_order_tour_button`

- Task 5 — Cities/Show.vue: модальная форма «Заявка на тур»
  - `resources/js/Pages/Cities/Show.vue` — `RModal` + форма + submit → `applications.store`

## Частично выполненные

_Пусто_

## Не начатые

_Пусто_

## Открытые вопросы

_Пусто_
