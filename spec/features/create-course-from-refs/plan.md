# План: create-course-from-refs

## Milestones (выполнено)

1. **Миграция БД** — `source_module_id` и `source_stage_id` (nullable FK, nullOnDelete)
2. **Обновление моделей** — fillable, связи `sourceModule()` / `sourceStage()`
3. **API поиска** — `searchModules` / `searchStages` с `ILIKE` (PostgreSQL)
4. **Vue-компонент поиска** — `SearchRefModal.vue` на базе `RModal` из UI-kit
5. **Интеграция в Form.vue** — кнопка внутри блока модуля, replace-логика при выборе
6. **Интеграция в StageEditor.vue** — кнопка внутри блока этапа, emit `search`, replace-логика
7. **Backend store/update** — валидация и сохранение `source_module_id` / `source_stage_id`
8. **Загрузка source-ссылок** — передача на фронт при edit, round-trip без потерь

## UI Components

| Компонент | Источник | Назначение |
|---|---|---|
| `RModal` | `@rosatom-ggr/ui-kit` (global) | Обёртка попапа поиска |
| `RButton` | `@rosatom-ggr/ui-kit` (global) | Кнопка подтверждения |
| `RInput` | `@rosatom-ggr/ui-kit` (global) | Поле поиска в попапе |
| `SearchRefModal.vue` | `Pages/Lms/Admin/Courses/SearchRefModal.vue` | Попап поиска модулей/этапов |

## Ключевые решения

- **RModal вместо Modal.vue** — Breeze `Modal.vue` имеет проблемы с z-index в контексте `LmsAdminLayout` (sidebar z-30). `RModal` из UI-kit корректно работает везде.
- **ILIKE вместо LIKE** — PostgreSQL: `LIKE` регистрозависим, `ILIKE` — нет. Необходимо для кириллицы.
- **Replace вместо Push** — кнопка поиска внутри существующего блока, при выборе — данные заполняют текущий блок, а не создают новый. UX: «Добавить → Найти → Заполнить».
