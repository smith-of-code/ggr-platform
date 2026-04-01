# План: Привязка LMS-формы к кнопке в направлении

## Milestones

1. **Миграция + модель** — колонка `paid_form_slug` в `directions`, обновить `Direction`
2. **Бэкенд админки** — `Admin\DirectionController` передаёт список форм, валидирует slug
3. **Фронт админки** — в `Directions/Form.vue` селект LMS-формы в блоке «Платное участие»
4. **Бэкенд публичной страницы** — `DirectionController@show` загружает форму + поля, передаёт в Inertia
5. **Фронт публичной страницы** — `Show.vue` + `ShowAtomsVkusa.vue`: модалка с формой, submit через axios
6. **Верификация** — проверка e2e: создать направление с формой, открыть публичную страницу, заполнить, отправить

## UI Components

- `Modal.vue` (`resources/js/Components/Modal.vue`) — maxWidth="2xl", closeable
- Рендер полей формы — inline в модалке (логика из `Pages/Forms/Public.vue`, вынести в переиспользуемый компонент `FormRenderer.vue`)
- `<select>` в админке для выбора формы (нативный, как для `project_key`)

## Verification

Все команды выполняются через Docker по паттерну из spec-continuation:
```
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
