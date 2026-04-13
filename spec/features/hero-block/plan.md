# План реализации hero-block

## Milestones

1. **Расширение HeroSection.vue** — добавить props `bgColor`, `textColor`, `bgColorEnabled`; при наличии `bgColor` + `bgColorEnabled=true` заменять градиент на сплошной цвет; при наличии `textColor` менять цвет текста.
2. **Миграция directions** — добавить `hero_bg_color`, `hero_text_color`, `hero_bg_image`, `hero_bg_color_enabled` в таблицу `directions`.
3. **Бэкенд directions** — обновить модель, валидацию, админ-контроллер.
4. **Админ-форма directions** — добавить секцию hero-настроек в `Admin/Directions/Form.vue`.
5. **Фронт directions** — обновить `Directions/Show.vue` и `ShowAtomsVkusa.vue` для проброса hero-props.
6. **Бэкенд + админ MainPage** — добавить валидацию hero-цветов в `MainPageController`, обновить админ-форму.
7. **Фронт MainPage** — обновить `MainPage.vue` для проброса hero-props.
8. **Бэкенд + админ OpportunityTours** — добавить валидацию hero-цветов/изображения в `OpportunityToursPageController`, обновить админ-форму.
9. **Фронт OpportunityTours** — обновить `OpportunityTours/Index.vue` для проброса hero-props.

## UI Components

- `HeroSection.vue` (`resources/js/Components/shared/`) — расширение существующего компонента.
- `RInput` — для текстовых полей цвета (type="color" или текст с HEX).
- `RCard`, `SectionHeader` — обёртка секции Hero в админ-формах (уже используются).
- `Switch` из `ui/` — переключатель `hero_bg_color_enabled`.
- `FileUpload` / `RInput` — загрузка/URL фонового изображения.

## Verification

Верификация по паттерну из spec-continuation (Docker exec).
