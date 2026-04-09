# error-ui — прогресс

## Completed tasks

1. Аудит payload событий Inertia — зафиксировано в комментариях `app.js` (`invalid` → `detail.response`, `error` → `detail.errors`, `exception` → `detail.exception`).
2. Утилита `formatValidationErrors` / `formatInvalidInertiaResponse` — `resources/js/utils/inertiaErrors.js`.
3. Обработчик `invalid` — через утилиту.
4. Обработчик `error` — валидация через `formatValidationErrors`; добавлен `exception` для сетевых сбоев.
5. Ветка `finish` — только `flash.error`; валидация не дублируется (см. `inertia:error`).
6. `flash.error` без дубля с валидацией — политика в комментарии к `finish`.
7. Лимиты — `MAX_VALIDATION_MESSAGES`, `MAX_TOAST_CHARS` в утилите.
8. Регрессия — `npm run build` в контейнере `fpm` успешно.

## Partially completed

_(пусто)_

## Not started

_(пусто)_

## Open issues

_(пусто)_
