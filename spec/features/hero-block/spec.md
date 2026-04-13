# Hero-блок — настройка цветов и фона (hero-block)

## Goal

Дать администратору возможность настраивать внешний вид hero-секции (цвет фона, цвет текста, фоновое изображение, вкл/выкл цвет фона) на страницах MainPage, OpportunityTours и всех страницах направлений (Directions).

## In-scope

- Расширение компонента `HeroSection.vue` — новые props: `bgColor`, `textColor`, `bgColorEnabled` для кастомизации цветов вместо захардкоженного градиента.
- Миграция: добавить поля `hero_bg_color`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled` в таблицу `directions`.
- Обновление модели `Direction` (`fillable`, `casts`).
- Обновление `Admin\DirectionController` — валидация новых полей.
- Обновление `Admin/Directions/Form.vue` — секция «Hero-блок» с полями цвета, изображения, переключатель фона.
- Обновление `Directions/Show.vue` и `Directions/ShowAtomsVkusa.vue` — проброс новых hero-props.
- Обновление `Admin\MainPageController` — валидация `hero_bg_color`, `hero_text_color`, `hero_bg_color_enabled`.
- Обновление `Admin/MainPage/Index.vue` — поля hero-цветов в секции Hero.
- Обновление `MainPage.vue` — проброс новых hero-props.
- Обновление `Admin\OpportunityToursPageController` — валидация `hero_bg_color`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled`.
- Обновление `Admin/OpportunityToursPage/Index.vue` — поля hero-цветов.
- Обновление `OpportunityTours/Index.vue` — проброс новых hero-props.

## Out-of-scope

- Страницы Home.vue, Research/Index.vue, Education/Index.vue — не затрагиваются.
- WYSIWYG-редактор или произвольная HTML-вёрстка в hero.
- Изменение размеров/padding hero (уже управляется через prop `size`).
- Кастомизация слотов `#decorations` / `#title` через админку.

## Constraints

- `HeroSection.vue` — обратная совместимость: без новых props поведение идентично текущему (градиент + белый текст).
- Для страниц настроек (MainPage, OpportunityTours) — данные hero хранятся в таблице `settings` через `SettingsService`.
- Для направлений — данные hero хранятся в колонках таблицы `directions`.
- Docker-only выполнение.
- Max 5 файлов на шаг.
- Цветовые поля — формат HEX (#003274).
- Реюзать существующие UI-компоненты из `ui/` и `shared/`.

## Open questions

(нет)
