# Plan: Коммерческие туры

## Milestones

- M1. БД: миграции на новые таблицы `tour_cabinet_commerce_progress` (с FK на `users`/`cities`/`tours`/`lms_form_submissions`) и `tour_cabinet_commerce_city_forms` (FK на `cities`, поле `lms_form_slug`). Без правок существующих таблиц.
- M2. Модели + связи: `TourCabinetCommerceProgress`, `TourCabinetCommerceCityForm`. Расширение `User` методом `tourCabinetCommerceProgress()`.
- M3. Конфиг + Settings: ключ `commerce_tours.*` в `config/tour_cabinet.php` с env-переопределением; геттер `SettingsService::getTourCabinetCommerceToursStage3Notification()`; геттер `SettingsService::isCommerceToursEnabled()` (или объединённый payload).
- M4. Сервис данных дашборда: `TourCabinetCommerceToursDashboardData::buildPayload(User): array` — текущий прогресс + список доступных городов и туров; вызывается из `TourCabinetController` (или smart-data сервиса дашборда).
- M5. Пользовательский контроллер `TourCabinetCommerceToursController`: маршруты выбора города/тура, инициации этапа 2 (редирект на `forms.public.show`), отображения этапа 3. Рассылочный сервис `TourCabinetCommerceToursFormLinker` (или метод в `TourCabinetContestFormLinker`-аналогичный) — слушатель сабмита `LmsForm` для перехода `stage 2 → 3`.
- M6. Админ-маршруты + контроллер `Admin\TourCabinetCommerceToursController`: CRUD маппинга `city → LmsForm slug`; обновление `commerce_tours.*` через `Admin\TourCabinetFormsController` или новый метод в нём.
- M7. Расширение `Admin\TourCabinetHubPageData::commerceToursPayload()`: маппинги, список городов/форм, текст этапа 3, флаг `enabled`.
- M8. Админ-фронт: новая панель `TourCabinetAdminCommerceToursPanel.vue` на `Hub.vue` + страница `CommerceTours/Index.vue` (по аналогии с `Stage2Questions/Index.vue`). Проброс пропа `commerceTours` через `Hub.vue` (`v-bind="commerceToursSection"`) и `CommerceTours/Index.vue`.
- M9. Пользовательский фронт: новый блок-якорь `#tour-cabinet-commerce-tours` в `Dashboard.vue`; компоненты `CommerceTours/CommerceToursStage1Panel.vue`, `CommerceToursStage2Panel.vue`, `CommerceToursStage3Panel.vue`.
- M10. Финализация: pest-сценарий happy-path (city + tour → form → stage 3), `npm run build`, `migrate`, `route:list`, обновление `spec.md` (раздел «Реализация (как сделано)») и `progress.md`.

## UI Components

- `RCard` — обёртка карточек панелей (на дашборде и в админке).
- `RButton` — все кнопки действий (`variant=primary` для CTA, `variant=outline` для второстепенных).
- `RSelect` — выпадающие списки выбора города и тура на этапе 1, выбора `LmsForm` в админке.
- `RInput` — поля темы этапа 3, поиск по городам в админ-таблице.
- `RCheckbox` — чекбокс «Блок активен» в админ-панели.
- `<textarea>` (нативный, tailwind-классы как в `TourCabinetAdminFormsPanel.vue`) — поле «Тело уведомления этапа 3».
- `useConfirm` — подтверждение удаления маппинга `city → form` в админке.
- `useToast` — флэш-сообщения после CRUD-операций.
- На дашборде используется один `RCard` с табами «Этап I/II/III» по аналогии с конкурсом — отдельные `Tab*` компоненты не вводим, повторяем шаблон `Contest/ContestStage*Panel.vue`.

## Verification

- Команды выполнять строго через Docker по паттерну из `spec-continuation` (раздел «Command patterns» / «PHP commands» / «Frontend commands»). Конкретные команды задаёт каждая Task в её разделе «Verify» — отдельные примеры здесь не дублируются.
- Минимум проверок: `php artisan migrate --pretend` → `migrate`, `php artisan route:list --path=tour-cabinet/commerce-tours`, `php artisan route:list --path=admin/tour-cabinet/commerce-tours`, `npm run build`, pest-сценарий happy-path для пользовательского flow (Task 10), smoke-проверка админ-панели (`Hub.vue` + страница CRUD) после деплоя.
