# План: Промокоды

## Этапы (milestones)

1. **Модель и миграции** — таблица `promocodes`, модель `Promocode`, полиморфная связь `promocodeable`, миграция `add_promocode_id_to_applications`
2. **API валидации промокода** — endpoint `POST /api/promocodes/validate` (принимает `code` + `tour_id`, возвращает `discount_percent` или ошибку)
3. **Админка: CRUD промокодов** — контроллер `Admin\PromocodeController`, Vue-страницы `Admin/Promocodes/Index.vue` и `Admin/Promocodes/Form.vue`, маршруты, пункт в сайдбаре
4. **Фронт: UI промокода на странице тура** — кнопка «У меня есть промокод», поле ввода, вызов API, мгновенный пересчёт цен (price_from, карточки заездов, sidebar)
5. **Фиксация промокода в заявке** — передача `promocode_id` при `POST /applications`, сохранение в `Application`, отображение в админке заявок
6. **Отображение промокода в админке заявок** — колонка/бейдж промокода в списке заявок и на детальной странице

## UI-компоненты

| Компонент | Источник | Назначение |
|-----------|----------|------------|
| `RButton` | `@rosatom-ggr/ui-kit` | Кнопки «Применить», toggle, CTA |
| `RInput` | `@rosatom-ggr/ui-kit` | Поле ввода промокода, поля формы в админке |
| `RCard` | `@rosatom-ggr/ui-kit` | Обёртка таблиц в админке |
| `RBadge` | `@rosatom-ggr/ui-kit` | Статус промокода (активен/истёк), бейдж скидки |
| `RModal` | `@rosatom-ggr/ui-kit` | Не требуется (промокод inline) |
| `AdminLayout` | `@/Layouts/AdminLayout.vue` | Layout для админ-страниц промокодов |

## Верификация

Каждая задача верифицируется через Docker-команды по паттерну из spec-continuation (Command Execution Pattern).
