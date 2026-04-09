# План — vshgr-editor

## Milestones

1. Зафиксировать состав полей `vshgr_page` (hero, секции, CTA, положение, заголовки формы, `socials[]`) и дефолты на бэкенде.
2. Реализовать админ-контроллер, валидацию и маршруты; сохранение в `SettingsService::setGroup`.
3. Собрать админскую форму Inertia с карточками и списком соцсетей; пункт меню в `AdminLayout`.
4. Проброс `pageData` в `EducationController::index` и привязка в `Education/Index.vue`.
5. Ручная проверка: сохранение в админке и отображение на `/vshgr`; регрессия формы заявки и блока анонсов.

## UI Components

По образцу `Admin/OpportunityToursPage/Index.vue`, без дублирования кастомных контролов:

- `AdminLayout`
- `RCard`, `SectionHeader`, `RInput` (или эквиваленты из того же набора)
- `DynamicList` для массива соцсетей (поля `name`, `url`, `icon`; при необходимости `icon-select` как на странице туров)
- `useForm` из Inertia для `PUT`

При появлении нового UI — сначала проверить `resources/js/components/ui/` и общие админ-частичные компоненты.

## Verification

См. правило **Command Execution Pattern** в `.cursor/rules/spec-continuation.mdc`: загрузка `docker/.env.*`, выполнение команд внутри контейнера `${APP_NAME}_fpm`, без `docker compose exec` и без команд на хосте.
