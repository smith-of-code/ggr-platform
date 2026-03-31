# План: research

## Milestones

1. Вынести рецепты из `ResearchController` в отдельный `RecipeController` (публичный)
2. Удалить старый Research CRUD: модель, контроллеры, Vue-страницы, маршруты, связи
3. Создать `Admin\ResearchPageController` (index + update), маршруты, навигация
4. Создать админ Vue-страницу `Admin/ResearchPage/Index.vue` — форма редактирования блоков
5. Создать публичный `ResearchPageController`, маршрут `/research`
6. Создать публичную Vue-страницу `Research/Index.vue` — все 6 блоков
7. Создать seeder начальных данных из оригинала
8. Верификация и финализация

## UI Components

- Переиспользуются из `Admin/OpportunityToursPage/`:
  - `DynamicList.vue` — редактор повторяющихся блоков (города, статистика, задачи)
  - `SectionHeader.vue` — заголовки секций в админ-форме
- Из UI kit (`@rosatom-ggr/ui-kit`): `RCard`, `RBadge`, `RButton`
- `ImageUploadCrop.vue` — загрузка изображений городов
- `RichTextEditor.vue` — редактирование описаний (если нужен HTML)

## Хранение данных

Таблица `settings`, group = `research_page`:

| Key | Тип | Описание |
|-----|-----|----------|
| `hero_title` | string | Заголовок hero |
| `hero_subtitle` | string | Подзаголовок |
| `hero_description` | string | Описание |
| `tasks_title` | string | Заголовок секции задач |
| `tasks` | JSON[] | `[{text}]` — список задач |
| `pilot_cities_title` | string | Заголовок секции (напр. "ПИЛОТНЫЕ ГОРОДА 2024") |
| `pilot_cities` | JSON[] | `[{name, region, description, image}]` |
| `stats_title` | string | Заголовок секции статистики |
| `stats` | JSON[] | `[{value, label}]` |
| `results_title` | string | Заголовок секции результатов |
| `results_description` | string | Текст |
| `results_button_text` | string | Текст кнопки PDF |
| `results_button_url` | string | URL файла |
| `program_cities_title` | string | Заголовок секции (напр. "ГОРОДА ПРОГРАММЫ 2025") |
| `program_cities` | JSON[] | `[{name, region}]` |

## Verification

Все верификационные шаги — через Docker-паттерн из spec-continuation:
```bash
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
