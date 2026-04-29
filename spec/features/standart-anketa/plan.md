# План: Standard Anketa

## Milestones

1. **Settings + бэкенд** — новый ключ `tour_cabinet_dashboard_standard_form_slug`, метод в `SettingsService`, проп `dashboardStandardForm` в `TourCabinetController::dashboard`.
2. **Админ-роут + контроллер** — `PUT admin.tour-cabinet.dashboard-form.update`, payload `dashboardStandardForm` + `allFormsOptions` в `TourCabinetHubPageData::formsPayload()` (для хаба и `Forms/Index.vue`).
3. **Админ-фронт** — карточка «Дашборд: Стандартная анкета» в `TourCabinetAdminFormsPanel.vue` (`SearchSelect` всех форм платформы + сохранение).
4. **Дашборд клиента** — новая `<section id="tour-cabinet-standard-form">` в `Dashboard.vue` непосредственно перед `#tour-cabinet-contest`: иконка, заголовок, описание, кнопка «Заполнить», контрастная плашка-уведомление; статус «Отправлено» при наличии submission.
5. **Полировка + spec** — финальные тексты, обновление `spec/features/standart-anketa/spec.md` (если контракт уточнялся), `progress.md`.

## UI Components

- `RCard` (`resources/js/Components/ui/RCard.vue`) — контейнер карточки в админке.
- `RButton` — кнопка «Сохранить» в админке.
- `SearchSelect` (`resources/js/Components/SearchSelect.vue`) — селект формы (с поиском при > 5).
- `IdentificationIcon` из `@heroicons/vue/24/outline` — иконка блока на дашборде (по умолчанию).
- На дашборде — собственная вёрстка `<section>` (без новых компонентов), в стиле соседних блоков (документы / избранное / конкурс): rounded-2xl, контрастная подложка, явный CTA.
- Реюз навигации/перехода: `<a :href="route('forms.public.show', slug)">` — единообразно с этапом 1 конкурса (`ContestStage1Panel.vue`).

## Verification

Все команды — через Docker по паттерну из `spec-continuation`:
- PHP / artisan / pest / tinker — через `docker exec ${APP_NAME}_fpm`.
- Frontend — `npm run …` / `npx …` через `docker exec ${APP_NAME}_fpm`.
- При неработающих контейнерах — одноразовый запуск через `docker compose run --rm fpm`.

DoD-подтверждение для каждой задачи:
- Settings: `php artisan tinker` — чтение/запись ключа.
- Контроллер: открыть `/tour-cabinet` авторизованным, проверить пропс `dashboardStandardForm` в Inertia (DevTools).
- Админ-роут: `PUT /admin/tour-cabinet/dashboard-form` сохраняет slug, редирект на хаб с якорем `#tour-cabinet-admin-forms`.
- Админ-фронт: `/admin/tour-cabinet` и `/admin/tour-cabinet/forms` — карточка отображает текущий slug, селект показывает все формы платформы.
- Дашборд: блок отображается выше блока «Конкурс», скрыт при пустом slug, показывает «Отправлено» при наличии submission текущего пользователя.
