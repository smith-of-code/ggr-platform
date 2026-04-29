# Задачи: standart-anketa

## Task 1: SettingsService — ключ `tour_cabinet_dashboard_standard_form_slug`

- **Goal**: добавить getter/resolver для нового ключа в группе `tour_cabinet`.
- **Scope**: `app/Services/SettingsService.php`, `config/tour_cabinet.php` (default `null`).
- **DoD**: `SettingsService::getTourCabinetDashboardStandardFormSlug()` возвращает строку из БД (если задана и не пустая) или `null`; fallback на `config('tour_cabinet.dashboard_standard_form_slug')` при отсутствии.
- **Verify**: `php artisan tinker` через Docker — `app(SettingsService::class)->getTourCabinetDashboardStandardFormSlug()` (паттерн команды — см. `spec-continuation`).

## Task 2: Бэкенд дашборда — проп `dashboardStandardForm`

- **Goal**: `TourCabinetController::dashboard` отдаёт `dashboardStandardForm` (`{ slug, title, submitted }` или `null`).
- **Scope**: `app/Http/Controllers/TourCabinetController.php` (+ при необходимости вспомогательный сервис в `app/Services/`).
- **DoD**: если slug пуст / форма неактивна / отсутствует → `null`; если есть submission текущего `user_id` по slug → `submitted = true`.
- **Verify**: `/tour-cabinet` авторизованным — Inertia-проп присутствует.

## Task 3: Админ-маршрут + контроллер — `PUT admin.tour-cabinet.dashboard-form.update`

- **Goal**: сохранение slug стандартной анкеты дашборда из админки.
- **Scope**: `routes/web.php`, `app/Http/Controllers/Admin/TourCabinetFormsController.php` (новый метод `updateDashboardStandardFormSlug`).
- **DoD**: валидация `nullable|string|exists:lms_forms,slug`; запись в `Setting::setGroup('tour_cabinet', …)`; редирект на хаб с якорем `#tour-cabinet-admin-forms`.
- **Verify**: `curl`/Inertia-сабмит из админки — slug сохраняется, повторное открытие страницы показывает выбранное значение.

## Task 4: Payload админки — `dashboardStandardForm` + `allFormsOptions`

- **Goal**: пробросить текущий slug и список **всех** активных форм платформы (`{ slug, title, is_active }[]`) в данные админки.
- **Scope**: `app/Services/Admin/TourCabinetHubPageData.php` (метод `formsPayload`), консьюмеры `Admin\TourCabinetHubController`, `Admin\TourCabinetFormsController::index`.
- **DoD**: на хабе и на странице `forms` доступны новые ключи; `allFormsOptions` не ограничен `lms_event_id` события туров.
- **Verify**: открыть `/admin/tour-cabinet` и `/admin/tour-cabinet/forms` — Inertia props содержат `dashboardStandardFormSlug` и `allFormsOptions` (DevTools).

## Task 5: Админ-фронт — карточка «Дашборд: Стандартная анкета»

- **Goal**: `SearchSelect` со всеми формами платформы + кнопка «Сохранить» в `TourCabinetAdminFormsPanel.vue`.
- **Scope**: `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue`, `Hub.vue` и `Forms/Index.vue` (проброс новых пропсов).
- **DoD**: новый `RCard` отображается; selected slug подгружается; submit делает `PUT` на `admin.tour-cabinet.dashboard-form.update`; неактивные формы помечены в подписи.
- **Verify**: `/admin/tour-cabinet` и `/admin/tour-cabinet/forms` — карточка работает, slug сохраняется и подгружается после reload.

## Task 6: Клиентский блок «Стандартная анкета» на дашборде

- **Goal**: новая `<section id="tour-cabinet-standard-form">` выше `#tour-cabinet-contest` в `Dashboard.vue` — иконка, заголовок, описание (`title` формы), кнопка «Заполнить», статус «Отправлено» если `submitted`.
- **Scope**: `resources/js/Pages/TourCabinet/Dashboard.vue`.
- **DoD**: блок виден только при `dashboardStandardForm !== null`; кнопка ведёт на `forms.public.show` по slug; стили согласованы с соседними секциями.
- **Verify**: визуально на `/tour-cabinet` — блок выше «Конкурса», корректно скрывается при пустом slug.

## Task 7: Контрастная плашка-уведомление под блоком

- **Goal**: текст «Заполните, пожалуйста, стандартную анкету» (или согласованный) — контрастная подложка, заметная глазу.
- **Scope**: `resources/js/Pages/TourCabinet/Dashboard.vue` (внутри новой секции).
- **DoD**: плашка видна только пока `submitted = false`; цвет контраста (например, `bg-amber-50` + `text-amber-900` + `border`); адаптивна.
- **Verify**: визуально на десктопе и мобильной ширине; при `submitted = true` плашка скрыта.

## Task 8: Финализация — spec/progress

- **Goal**: довести `spec.md` до состояния «как реализовано», обновить `progress.md`.
- **Scope**: `spec/features/standart-anketa/spec.md`, `spec/features/standart-anketa/progress.md`, при необходимости `spec/features/tour-cabinet/spec.md` (упомянуть новый блок дашборда и админ-настройку).
- **DoD**: все Open questions либо решены и убраны, либо вынесены в `spec/90-open-questions.md`; progress полностью отражает финальное состояние.
- **Verify**: ручной обзор изменений в spec; `git status` чистый по spec-файлам после правок.
