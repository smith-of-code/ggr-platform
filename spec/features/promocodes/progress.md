# Прогресс: Промокоды

## Выполненные задачи

1. Миграция и модель Promocode
   - `database/migrations/2026_04_24_100000_create_promocodes_table.php`
   - `app/Models/Promocode.php`

2. Миграция: добавить promocode_id в applications
   - `database/migrations/2026_04_24_100001_add_promocode_id_to_applications_table.php`
   - `app/Models/Application.php`

3. API валидации промокода
   - `app/Http/Controllers/PromocodeController.php`
   - `routes/web.php`

4. Админка: контроллер PromocodeController
   - `app/Http/Controllers/Admin/PromocodeController.php`
   - `routes/web.php`

5. Админка: Vue-страницы (Index + Form)
   - `resources/js/Pages/Admin/Promocodes/Index.vue`
   - `resources/js/Pages/Admin/Promocodes/Form.vue`

6. Пункт меню промокодов в AdminLayout sidebar
   - `resources/js/Layouts/AdminLayout.vue`

7. Фронт: кнопка «У меня есть промокод» + поле ввода на Tours/Show
   - `resources/js/Pages/Tours/Show.vue`

8. Фронт: пересчёт и отображение цены со скидкой
   - `resources/js/Pages/Tours/Show.vue`

9. Фиксация промокода в заявке
   - `resources/js/Pages/Tours/Show.vue`
   - `app/Http/Controllers/ApplicationController.php`

10. Отображение промокода в админке заявок
    - `app/Http/Controllers/Admin/ApplicationController.php`
    - `resources/js/Pages/Admin/Applications/Index.vue`

## Частично выполненные задачи

(пусто)

## Не начатые задачи

(пусто)

## Открытые проблемы

(пусто)
