# Задачи — lms-events

## BUG-LMS-RUTUBE-PRIVATE-001 — Embed приватного RuTube на странице LMS Video

- **Goal:** Корректно строить `iframe src` для `video/private/...?p=...` и обычных `video/{id}` в `Lms/Videos/Show.vue`.
- **Scope:** `resources/js/Pages/Lms/Videos/Show.vue` (парсинг URL через `URL`, приватный путь + query `p`).
- **Verify:** Сборка фронта в Docker; ручной сценарий из `spec.md` Bugfixes.

## BUG-LMS-RUTUBE-PRIVATE-002 — Превью и API thumbnail для private URL

- **Goal:** Не использовать слово `private` как id превью / запроса к API RuTube.
- **Scope:** `resources/js/Pages/Lms/Videos/Index.vue` (`getThumbnail`), `app/Http/Controllers/Lms/Admin/VideoController.php` (`fetchThumbnail`).
- **Verify:** Список видео: превью по hex-id для private URL (если доступно с CDN); сохранение видео в админке без ложного API id.
