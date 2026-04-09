# ВШГР — редактор лендинга `/vshgr`

## Goal

Дать администратору редактировать контент публичной страницы `/vshgr` (каталог программ) через отдельный раздел админки по образцу `admin/opportunity-tours-page`.

## In-scope

- Группа настроек в БД через существующий `SettingsService` (новый `group`, например `vshgr_page`), хранение скалярных полей и JSON-массивов по аналогии с `opportunity_tours`.
- Админ-маршруты `GET` + `PUT` под префиксом `admin` (имена маршрутов в стиле `admin.vshgr-page.*`).
- Контроллер в `App\Http\Controllers\Admin\` с валидацией и `redirect()->back()->with('success', ...)`.
- Страница Inertia `resources/js/Pages/Admin/VshgrPage/Index.vue`: блоки hero, заголовки секций (каталог, анонсы, CTA, положение, форма заявки, соцсети), редактируемый список соцсетей (как у туров возможностей: `name`, `url`, `icon`).
- Пункт в `AdminLayout.vue` для перехода к редактору.
- `EducationController::index`: загрузка группы настроек, слияние с дефолтами (текущие захардкоженные тексты/URL как fallback), передача в `Education/Index` как `pageData` (или согласованное имя props).
- `resources/js/Pages/Education/Index.vue`: отображение полей из props с fallback на нынешние значения; логика каталога, анонсов и формы заявки без изменения бизнес-поведения.

## Out-of-scope

- Редактирование сущностей `EducationProduct` и постов (остаётся существующий CRUD).
- Страницы `/vshgr/{slug}` (карточка программы).
- Изменение эндпоинта отправки заявки и валидации `ApplicationController`.
- Отдельная CMS для произвольной вёрстки (только оговорённые поля и списки).

## Constraints

- Повторять паттерн `OpportunityToursPageController` + `Admin/OpportunityToursPage/Index.vue` (Inertia, `useForm`, те же UI-примитивы админки).
- Команды верификации — только по правилу Docker из `spec-continuation` (`source docker/.env.*`, `docker exec ${APP_NAME}_fpm`).
- Идентификаторы в коде (имена классов, маршрутов, ключей settings) — на английском.

## Open questions

(нет — интерфейсы подтверждены существующим кодом.)
