# Standard Anketa — отдельный блок «Стандартная анкета» на дашборде ЛК Туры

## Goal

Добавить контрастный отдельный блок «Стандартная анкета» на дашборде `/tour-cabinet` (выше блока «Конкурс») с кнопкой «Заполнить», и дать админу возможность привязать к этой кнопке любую LMS-форму платформы через `/admin/tour-cabinet`.

## In-scope

- Новый раздел дашборда `#tour-cabinet-standard-form` в `resources/js/Pages/TourCabinet/Dashboard.vue` — выше `#tour-cabinet-contest`.
- Отдельный блок на странице (карточка): иконка, заголовок «Стандартная анкета», краткое описание формы (`title` LMS-формы), кнопка «Заполнить» → переход на `forms.public.show` по slug привязанной формы.
- Контрастный текст-уведомление под кнопкой/блоком: «Заполните, пожалуйста, стандартную анкету» (или эквивалентная формулировка) — выделение цветом/фоном, чтобы не пропустили.
- Статус «Отправлено» (бейдж + замена кнопки) если у текущего `user_id` уже есть `lms_form_submissions` по slug привязанной формы.
- Новый ключ настройки `tour_cabinet_dashboard_standard_form_slug` в таблице `settings`, группа `tour_cabinet`.
- Бэкенд: чтение через `SettingsService` (новый метод `getTourCabinetDashboardStandardFormSlug`), проброс `dashboardStandardForm` (`{ slug, title, submitted }` или `null`) в Inertia-проп из `TourCabinetController::dashboard`.
- Админка `/admin/tour-cabinet` (хаб + страница `forms`): в `TourCabinetAdminFormsPanel.vue` новая карточка «Дашборд: Стандартная анкета» с `SearchSelect` формы (все формы платформы, без фильтра по событию) и кнопкой «Сохранить».
- Новый маршрут `PUT admin.tour-cabinet.dashboard-form.update` → `Admin\TourCabinetFormsController::updateDashboardStandardFormSlug`; валидация `nullable|string|exists:lms_forms,slug`.
- Список форм для селекта в админке: новый payload `allFormsOptions` (`{ slug, title, is_active }[]`) — все `LmsForm`, не ограниченные `lms_event_id` события туров.

## Out-of-scope

- Период действия (даты «с / по») — на картинке зачёркнут, в первой итерации не делаем.
- Кастомизация иконки и текста уведомления через админку — захардкодим в шаблоне (отдельная фича при необходимости).
- Уведомления / e-mail-напоминания участникам о незаполненной анкете.
- Создание / редактирование самих LMS-форм (используется существующий конструктор форм).
- Привязка нескольких «стандартных анкет» одновременно — поддерживаем ровно одну.

## Constraints

- Реюз: `RCard`, `RButton`, `SearchSelect` (`resources/js/Components/SearchSelect.vue`), `FormRenderer.vue` не нужен — переход идёт на отдельную страницу `/forms/{slug}`.
- Хранение slug — единым механизмом `Setting::setGroup('tour_cabinet', …)`, как для `contest_stage1_form_slug_*`.
- Если slug пустой / форма неактивна / форма удалена — блок на дашборде **скрыт** полностью (никаких заглушек участнику).
- Привязанная форма не обязана принадлежать LMS-событию туров (`config('tour_cabinet.lms_event_slug')`) — допустимо выбрать любую форму платформы.
- Все команды выполнять через Docker (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`) согласно `spec-continuation`.

## Реализация (как сделано)

- Settings: `config/tour_cabinet.php` — ключ `dashboard_standard_form_slug` (env `TOUR_CABINET_DASHBOARD_STANDARD_FORM_SLUG`); `SettingsService::getTourCabinetDashboardStandardFormSlug()` (БД-группа `tour_cabinet` → fallback `config`).
- Бэкенд дашборда: `TourCabinetController::dashboard` отдаёт `dashboardStandardForm` (`{ slug, title, submitted } | null`); `submitted = exists(LmsFormSubmission where lms_form_id and user_id)`.
- Админка: маршрут `PUT /admin/tour-cabinet/dashboard-form` (`admin.tour-cabinet.dashboard-form.update`) → `Admin\TourCabinetFormsController::updateDashboardStandardFormSlug`; валидация `nullable|string|max:255` + проверка `is_active = true` среди `lms_forms`; запись через `Setting::setGroup('tour_cabinet', …)`.
- Payload админки: `TourCabinetHubPageData::formsPayload` дополнительно отдаёт `dashboardStandardFormSlug` и `allFormsOptions` (все `LmsForm`, не ограниченные событием).
- Админ-фронт: новая `RCard`-карточка «Дашборд: Стандартная анкета» в `TourCabinetAdminFormsPanel.vue` — `SearchSelect` по `allFormsOptions` (фильтр `is_active = true`). Прокинуто в `Hub.vue` через `v-bind="formsSection"`, в `Forms/Index.vue` — явными пропсами.
- Клиентский UI: `<section id="tour-cabinet-standard-form">` сразу перед `#tour-cabinet-contest` в `Dashboard.vue`. Иконка `IdentificationIcon`, заголовок «Стандартная анкета», подзаголовок = `title` формы, кнопка «Заполнить» → `forms.public.show` по slug. При `submitted = true` кнопка заменяется бейджем «Отправлено» (`CheckCircleIcon`), плашка-уведомление скрывается.
- Контрастная плашка-уведомление — нижняя полоса блока, `bg-amber-100/80` + `border-amber-300` + `text-amber-900`; текст: «Заполните, пожалуйста, стандартную анкету — это занимает несколько минут и нужно для дальнейшего участия.»

## Open questions

1. Текст уведомления и заголовок блока — формулировка зашита в шаблон. Если заказчик попросит другую — править в `Dashboard.vue` или вынести в админ-настройку (отдельная фича).
2. Иконка блока — на референсе пометка «Другой значок например»; используется `IdentificationIcon` из `@heroicons/vue/24/outline`. Кастомизация через админку — отдельная задача.
