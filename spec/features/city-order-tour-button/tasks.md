# Tasks — city-order-tour-button

## Task 1 — Миграция: добавить show_order_tour_button в cities

- **Goal**: создать миграцию, добавляющую колонку в таблицу `cities`.
- **Scope**: `database/migrations/YYYY_MM_DD_*_add_show_order_tour_button_to_cities_table.php`
- **DoD**: миграция отрабатывает без ошибок, колонка есть в схеме.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate --force`

---

## Task 2 — Модель City + контроллер Admin\CityController

- **Goal**: добавить `show_order_tour_button` в `$fillable`, `$casts` модели `City`; добавить в валидацию `store` и `update` в `Admin\CityController`.
- **Scope**: `app/Models/City.php`, `app/Http/Controllers/Admin/CityController.php`
- **DoD**: поле сохраняется и читается корректно.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan test --filter CityTest` (если есть тесты).

---

## Task 3 — Admin/Cities/Form.vue: чекбокс «Показывать кнопку "Заказать тур"»

- **Goal**: добавить `RCheckbox` в правую колонку формы города (рядом с `is_active`), инициализировать в `form` объект.
- **Scope**: `resources/js/Pages/Admin/Cities/Form.vue`
- **DoD**: чекбокс отображается, состояние сохраняется при submit.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

---

## Task 4 — Cities/Show.vue: кнопка «Заказать тур» в hero-блоке

- **Goal**: при `city.show_order_tour_button === true` показывать кнопку «Заказать тур» в hero-блоке рядом с кнопками «В избранное» и «Поделиться». Кнопка акцентного цвета (не белая стеклянная, а брендовая тёмно-синяя/оранжевая).
- **Scope**: `resources/js/Pages/Cities/Show.vue`
- **DoD**: кнопка видна только при включённом флаге.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

---

## Task 5 — Cities/Show.vue: модальная форма «Заявка на тур»

- **Goal**: добавить `RModal` с формой (Имя, Email, Телефон, Сообщение, Согласие), submit → `applications.store` с `type: 'tour'`, `tour_id: null`. Успешная отправка показывает экран «Заявка отправлена!».
- **Scope**: `resources/js/Pages/Cities/Show.vue`
- **DoD**: форма открывается по клику на кнопку, проходит валидацию, успех показывает подтверждение.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
