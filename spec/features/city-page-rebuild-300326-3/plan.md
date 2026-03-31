# План: Расширение фактов о городе

## Milestones

1. **Модель City** — accessor для нормализации строковых фактов в объекты `{title, url, description}`
2. **Валидация** — обновить правила в `Admin\CityController` для структуры `facts.*`
3. **Админ-форма** — переделать блок «Факты о городе» в карточки с полями `title`, `url`, `RichTextEditor`
4. **Публичная страница** — обновить отображение фактов: ссылки, форматированное описание
5. **Верификация** — линтер, проверка отображения

## UI Components

- `RichTextEditor` — существующий (`resources/js/Components/RichTextEditor.vue`), для поля `description` каждого факта
- `RInput` — для `title` и `url`
- `RCard` — обёртка блока фактов (уже используется)

## Verification

Выполнение команд по паттерну из spec-continuation (source docker/.env.* + docker exec).
