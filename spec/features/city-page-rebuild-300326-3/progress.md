# Прогресс: city-page-rebuild-300326-3

## Выполнено

- T1. Accessor нормализации фактов — `getFactsAttribute` в `app/Models/City.php`, legacy `string[]` → `{title, url, description}`
- T2. Валидация — `facts.*.title` (required|string|max:500), `facts.*.url` (nullable|string|max:2048), `facts.*.description` (nullable|string) в `store` и `update` (`app/Http/Controllers/Admin/CityController.php`)
- T3. Инициализация формы — `addFact()` пушит объект `{title, url, description}`, нормализация props в `resources/js/Pages/Admin/Cities/Form.vue`
- T4. Шаблон админ-формы — блок «Факты» переделан в карточки: input `title`, input `url`, `RichTextEditor` для `description` (`resources/js/Pages/Admin/Cities/Form.vue`)
- T5. Публичная страница — `normalizedFacts` computed, `component :is` для `a`/`div` по наличию URL, `v-html` для description, fallback на title (`resources/js/Pages/Cities/Show.vue`)
- T6. Верификация — линтер чист, progress обновлён

## Частично выполнено

(пусто)

## Не начато

(пусто)

## Открытые вопросы

(пусто)
