# Блок «Видеопрезентация программы» (video-presentation-block)

## Goal

Добавить на главную страницу новый блок с видеороликом-презентацией программы и информационными секциями (миссия, цели, ключевые ценности, организаторы, история, цифры и факты, аудитория). Весь контент редактируется через `/admin/main-page`.

## In-scope

- Новый ключ `video_presentation` в группе `main_page` (JSON) — хранит: `video_embed_url`, `video_file`, `video_thumbnail`, `video_title`, `mission`, `goals` (массив), `values` (массив), `organizers` (массив `{name, role, image}`), `history` (текст), `facts` (массив `{value, label}`), `audience` (текст).
- Добавить `video_presentation` в `JSON_KEYS`, валидацию `update()`, `defaults()` и `defaultBlockOrder()` в `MainPageController`.
- Добавить блок `video_presentation` в `block_order`, `blockLabels`, `sectionTitleKeys`, `defaultBlockOrder` и форму в `Admin/MainPage/Index.vue`.
- Отрисовать красивый блок `video_presentation` в `MainPage.vue` (публичная сторона) — видеоплеер/embed + информационные карточки с анимацией.
- Обновить `HomeController::buildMainPageData()` для проброса `video_presentation`.

## Out-of-scope

- Изменение существующего блока `videos` (карусель видеороликов) — он остаётся без изменений.
- Загрузка видео на сервер (используются ссылки URL / embed).
- Отдельная модель БД — всё через `settings`.

## Constraints

- Паттерн хранения: `SettingsService::setGroup('main_page', ...)` — аналогично другим блокам.
- Реюзать `DynamicList` из `Admin/OpportunityToursPage/` для массивных полей (goals, values, organizers, facts).
- Docker-only выполнение.
- Max 5 файлов на шаг.
- Идентификаторы в коде — на английском.

## Open questions

(нет — все интерфейсы подтверждены текущим кодом)
