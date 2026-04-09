# Прогресс — lms-events

## Completed

### BUG-LMS-RUTUBE-PRIVATE-001 — Embed приватного RuTube

- `resources/js/Pages/Lms/Videos/Show.vue` — `rutubeEmbedFromStoredUrl`: `video/private/{id}` + query `p` → `play/embed/.../?p=...`; публичный `video/{id}`.
- `spec/features/lms-events/spec.md` — Bugfixes BUG-LMS-RUTUBE-PRIVATE-001.

## Partially completed

(пусто)

## Not started

- BUG-LMS-RUTUBE-PRIVATE-002 — превью и `VideoController::fetchThumbnail` для private URL

## Open issues

(пусто)
