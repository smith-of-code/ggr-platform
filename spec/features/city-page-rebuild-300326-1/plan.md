# План: city-page-rebuild-300326-1

## Milestones

1. **Миграция** — добавить `founded_year` и `timezone` в таблицу `cities`.
2. **Модель** — обновить `City` model `$fillable`.
3. **Админ-бэкенд** — обновить валидацию в `Admin\CityController` (`store`, `update`).
4. **Админ-форма** — добавить поля `founded_year` и `timezone` в `Admin/Cities/Form.vue`.
5. **Публичная страница** — переделать блок stats bar в `Cities/Show.vue` по дизайну старого сайта.
6. **Cleanup** — убрать неиспользуемые computed (если `attractionsCount`/`toursCount` больше нигде не нужны в stats bar).

## UI Components

- Нет новых shared-компонентов. Используются стандартные input/label из существующей формы.
- Stats bar — чистый HTML/Tailwind в `Show.vue`, без выделения в отдельный компонент.

## Verification

Проверка по паттерну «Docker SSoT» из spec-continuation.
