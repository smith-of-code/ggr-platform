# Задачи: brake-line-tour-page

## T1: Добавить whitespace-pre-line к описанию дня программы

- **Цель**: Переносы строк из textarea отображаются как абзацы на витрине
- **Скоуп**: `resources/js/Pages/Tours/Show.vue` (строка ~142)
- **DoD**: Класс `whitespace-pre-line` добавлен к `<p>` с `day.description`
- **Verify**: Открыть `/tours/zelenyy-atom`, убедиться что описание дней программы сохраняет переносы строк
