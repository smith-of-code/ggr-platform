# Редактор главной страницы (mainpage-editor)

## Goal

Дать администратору возможность редактировать все контентные блоки главной страницы (`/`) и управлять их порядком через раздел админки, по образцу `admin/opportunity-tours-page` и `admin/vshgr-page`.

## In-scope

- Группа настроек `main_page` в таблице `settings` через `SettingsService` для хранения всех редактируемых данных блоков.
- Админ-маршруты `GET /admin/main-page` + `PUT /admin/main-page` (имена `admin.main-page.index`, `admin.main-page.update`).
- Контроллер `Admin\MainPageController` с валидацией, JSON-кодированием массивов и `redirect()->back()->with('success', ...)`.
- Страница Inertia `Admin/MainPage/Index.vue` — единая форма со всеми блоками, drag-and-drop для переупорядочивания блоков, переключатель видимости блоков.
- Пункт «Главная страница» в `AdminLayout.vue` (секция «Сайт»).
- Проброс `pageData` из `HomeController::buildHomePageProps()` — загрузка группы `main_page`, слияние с дефолтами (текущие захардкоженные данные как fallback).
- Обновление `MainPage.vue` — отображение данных из props вместо хардкода; порядок блоков из `block_order`.

## Редактируемые блоки

| # | Блок | Ключи settings | Тип |
|---|------|---------------|-----|
| 1 | Hero | `hero_title`, `hero_description`, `hero_bg_image` | скаляры |
| 2 | Этапы программы | `program_stages` | JSON `[{step, title, description, image, buttonLabel, href}]` |
| 3 | Города программы | `program_cities` | JSON `[{year, cities: [{name, region, image}]}]` |
| 4 | Результаты программы | `program_results`, `program_results_image` | JSON `[{year, results: [{value, description}]}]` + скаляр |
| 5 | Что получает город | `city_benefits` | JSON `[{title, image}]` |
| 6 | Допинициативы | `additional_initiatives` | JSON `[{title, image}]` |
| 7 | Видеоролики | `videos` | JSON `[{title, thumbnail, embedUrl, videoFile}]` |
| 8 | Переезжаем | `moving_title`, `moving_description` | скаляры |
| 9 | Статистика (гости) | `stats_guests_value`, `stats_guests_label` | скаляры |
| 10 | CTA | `cta_title`, `cta_description` | скаляры |
| 11 | Форма обратной связи | `contact_title`, `contact_description`, `contact_left_text`, `contact_bullets` | скаляры + JSON |
| 12 | Контакты | `contacts`, `socials` | JSON `[{label, value, href}]`, `[{label, href, icon}]` |
| 13 | Заголовки секций | `section_titles` | JSON `{sectionId: {title, subtitle}}` |
| — | Порядок блоков | `block_order` | JSON `[{id, enabled}]` |

Динамические блоки (Популярные туры, Города, Карта, Новости, Рецепты, Хронология) — данные из БД без изменений; редактируются только заголовки секций и видимость/порядок через `block_order`.

## Out-of-scope

- Редактирование сущностей (туры, города, посты, рецепты, хронология) — существующий CRUD.
- Изменение формы обратной связи и валидации `ContactController`.
- Старая страница `Home.vue` удалена; `MainPage.vue` рендерится на `/`.
- Произвольная HTML-вёрстка или WYSIWYG-редактор.

## Constraints

- Повторять паттерн `OpportunityToursPageController` + `Admin/OpportunityToursPage/Index.vue`.
- Реюзать `DynamicList`, `SectionHeader` из `Admin/OpportunityToursPage/`.
- Docker-only выполнение по правилу spec-continuation.
- Идентификаторы в коде — на английском.
- Max 5 файлов на шаг.

## Open questions

(нет — интерфейсы подтверждены существующим кодом)
