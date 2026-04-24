# Задачи: Промокоды

## Задача 1. Миграция и модель Promocode

- **Цель**: создать таблицу `promocodes` и модель `Promocode`
- **Scope**: `database/migrations/xxxx_create_promocodes_table.php`, `app/Models/Promocode.php`
- **DoD**: миграция проходит; модель имеет fillable, casts, связи (`promocodeable` morph, `applications` hasMany); код уникален
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate` — без ошибок

## Задача 2. Миграция: добавить promocode_id в applications

- **Цель**: связать заявку с промокодом
- **Scope**: `database/migrations/xxxx_add_promocode_id_to_applications_table.php`, `app/Models/Application.php`
- **DoD**: nullable FK `promocode_id` в `applications`; связь `Application::promocode()` belongsTo
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan migrate` — без ошибок

## Задача 3. API валидации промокода

- **Цель**: endpoint для проверки промокода на фронте
- **Scope**: `app/Http/Controllers/PromocodeController.php`, `routes/web.php`
- **DoD**: `POST /promocodes/validate` принимает `{code, tour_id}`, возвращает JSON `{valid, discount_percent, message}`; проверяет: существование, is_active, срок действия, привязку к туру
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=promocodes` — маршрут виден

## Задача 4. Админка: контроллер PromocodeController

- **Цель**: серверная часть CRUD промокодов
- **Scope**: `app/Http/Controllers/Admin/PromocodeController.php`, `routes/web.php` (admin group)
- **DoD**: index (с пагинацией), create, store, edit, update, destroy, toggleActive; валидация code (unique), discount_percent (1–100), valid_from, valid_until, генерация кода (slug/random/custom)
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=admin.promocodes` — 7+ маршрутов

## Задача 5. Админка: Vue-страницы (Index + Form)

- **Цель**: UI для управления промокодами в админке
- **Scope**: `resources/js/Pages/Admin/Promocodes/Index.vue`, `resources/js/Pages/Admin/Promocodes/Form.vue`
- **DoD**: список с поиском, статус-бейджи (активен/истёк/неактивен), toggle active, удаление с подтверждением; форма создания/редактирования с выбором генерации кода, процентом скидки, датами, привязкой к туру (select)
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок

## Задача 6. Пункт меню промокодов в AdminLayout sidebar

- **Цель**: добавить ссылку на промокоды в навигацию админки
- **Scope**: `resources/js/Layouts/AdminLayout.vue`
- **DoD**: пункт «Промокоды» в сайдбаре, active-state при `/admin/promocodes*`
- **Verify**: визуально — пункт отображается в сайдбаре

## Задача 7. Фронт: кнопка «У меня есть промокод» + поле ввода на Tours/Show

- **Цель**: UI для ввода и применения промокода на странице тура
- **Scope**: `resources/js/Pages/Tours/Show.vue`
- **DoD**: кнопка-toggle раскрывает поле ввода + кнопку «Применить»; при успехе — показать бейдж скидки и кнопку «Убрать»; при ошибке — сообщение
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок

## Задача 8. Фронт: пересчёт и отображение цены со скидкой

- **Цель**: мгновенно показывать цену с учётом скидки во всех местах на странице тура
- **Scope**: `resources/js/Pages/Tours/Show.vue`
- **DoD**: price_from в шапке, цена в карточках заездов (section-dates), цена в sidebar — все отображают «старая цена → новая цена» с визуальным зачёркиванием; при снятии промокода — возврат к оригинальным ценам
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок

## Задача 9. Фиксация промокода в заявке

- **Цель**: передавать и сохранять промокод при отправке заявки на тур
- **Scope**: `resources/js/Pages/Tours/Show.vue`, `app/Http/Controllers/ApplicationController.php`
- **DoD**: `promocode_id` передаётся в `POST /applications`, сохраняется в `Application`; повторная серверная валидация промокода перед сохранением
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="..."` — создание заявки с промокодом

## Задача 10. Отображение промокода в админке заявок

- **Цель**: показывать применённый промокод в списке и деталях заявки
- **Scope**: `app/Http/Controllers/Admin/ApplicationController.php`, `resources/js/Pages/Admin/Applications/Index.vue` (или аналог), `resources/js/Pages/Admin/Applications/Show.vue` (или аналог)
- **DoD**: бейдж с кодом и процентом скидки в списке заявок; на детальной странице — блок с информацией о промокоде
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build` — без ошибок
