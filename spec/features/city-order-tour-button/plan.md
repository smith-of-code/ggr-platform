# Plan — city-order-tour-button

## Milestones

1. **Миграция** — добавить `show_order_tour_button boolean default false` в `cities`.
2. **Модель + контроллер** — `City` fillable/casts + валидация в `Admin\CityController` (store + update).
3. **Форма админки** — чекбокс в `Admin/Cities/Form.vue` (правая колонка, рядом с `is_active`).
4. **UI клиентской части** — кнопка «Заказать тур» в hero-блоке `Cities/Show.vue` (условие `city.show_order_tour_button`).
5. **Модальное окно** — форма заявки в `Cities/Show.vue`: RModal + поля + submit → `applications.store`.

## UI Components

| Компонент | Откуда |
|---|---|
| `RModal` | `resources/js/components/ui/` (используется в Tours/Show.vue) |
| `RInput` | UI-кит |
| `RButton` | UI-кит |
| `RCheckbox` | UI-кит (уже в Form.vue) |

## Verification

Каждый шаг верифицировать по паттерну:
```
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
- PHP: `php artisan migrate --force`
- Frontend: `npm run build`
