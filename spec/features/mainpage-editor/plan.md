# План — mainpage-editor

## Milestones

1. **Модель данных** — определить ключи группы `main_page`, JSON_KEYS, дефолты (текущие захардкоженные значения из `MainPage.vue`).
2. **Сидер** — `MainPageSeeder` для начальных значений всех блоков.
3. **Админ-контроллер и маршруты** — `Admin\MainPageController` (index/update), маршруты, пункт в `AdminLayout`.
4. **Админ-страница: скалярные блоки** — Hero, Moving, CTA, Contact, Stats guests, заголовки секций.
5. **Админ-страница: массивные блоки** — Program stages, Program cities, Program results, City benefits, Additional initiatives, Videos, Contacts, Socials (через `DynamicList`).
6. **Админ-страница: порядок блоков** — drag-and-drop для `block_order` + переключатели видимости.
7. **Публичный контроллер** — `HomeController::buildHomePageProps()` — загрузка группы `main_page`, слияние с дефолтами, передача `pageData`.
8. **Публичный шаблон** — `MainPage.vue` — замена хардкода на props из `pageData`, рендер блоков по `block_order`.
9. **Финализация** — линтер, обновление spec, регрессия.

## UI Components

По образцу `Admin/OpportunityToursPage/Index.vue`:

- `AdminLayout` — layout
- `SectionHeader` — заголовки секций в форме
- `DynamicList` — динамические списки (stages, cities, benefits, initiatives, videos, contacts, socials)
- `RCard` — карточки группировки полей
- `useForm` из Inertia для `PUT`
- Drag-and-drop для `block_order` — `vuedraggable` (если уже в зависимостях) или нативный HTML drag-and-drop

## Verification

Все команды выполняются по паттерну из spec-continuation:
```bash
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
