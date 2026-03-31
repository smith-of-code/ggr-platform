# Задачи: city-page-rebuild-300326-3

## T1. Accessor нормализации фактов в модели City

- **Цель**: обратная совместимость — строковые факты автоматически конвертируются в `{title, url, description}`
- **Scope**: `app/Models/City.php`
- **DoD**: accessor `getFactsAttribute` возвращает массив объектов `{title, url, description}` независимо от формата данных в БД
- **Verify**: паттерн docker exec

## T2. Обновление валидации в CityController

- **Цель**: принять новый формат фактов `{title, url, description}`
- **Scope**: `app/Http/Controllers/Admin/CityController.php`
- **DoD**: правила `facts.*.title` (required|string|max:500), `facts.*.url` (nullable|url|max:2048), `facts.*.description` (nullable|string) в `store` и `update`
- **Verify**: паттерн docker exec

## T3. Обновление инициализации формы в админке

- **Цель**: `form.facts` инициализируется как массив объектов `{title, url, description}`
- **Scope**: `resources/js/Pages/Admin/Cities/Form.vue` (скрипт)
- **DoD**: `addFact()` пушит `{title: '', url: '', description: ''}`, инициализация из props нормализует строки
- **Verify**: линтер

## T4. Обновление шаблона админ-формы: блок «Факты»

- **Цель**: UI для редактирования `title`, `url`, `description` каждого факта
- **Scope**: `resources/js/Pages/Admin/Cities/Form.vue` (template, блок Facts)
- **DoD**: каждый факт — карточка с input `title`, input `url`, `RichTextEditor` для `description`, кнопка удаления
- **Verify**: линтер

## T5. Обновление публичной страницы: отображение фактов

- **Цель**: факты отображаются с учётом `url` и `description`
- **Scope**: `resources/js/Pages/Cities/Show.vue`
- **DoD**: если `url` — title как ссылка; если `description` — вывод `v-html`; если только `title` — чипс как раньше
- **Verify**: линтер

## T6. Верификация и обновление progress.md

- **Цель**: проверить линтер, обновить progress
- **Scope**: `spec/features/city-page-rebuild-300326-3/progress.md`
- **DoD**: линтер чист, progress актуален
- **Verify**: линтер
