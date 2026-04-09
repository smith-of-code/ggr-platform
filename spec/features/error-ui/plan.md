# error-ui — план

## Milestones

1. Зафиксировать форматы ответов (422 validation, прочие JSON с `message`) на уровне фронта и границы fallback.
2. Реализовать извлечение и нормализацию текста ошибки из payload события `invalid` (и при необходимости `error`).
3. Заменить общее сообщение в `finish` при `props.errors` на человекочитаемую сводку валидации.
4. Ручная проверка сценариев: невалидный Inertia-ответ, форма с ошибками, `flash.error`.
5. Верификация сборки фронта по правилам Docker.

## UI Components

- Повторное использование существующего `useToast` (`resources/js/composables/useToast.js`) — новые компоненты UI-kit не требуются; меняется только текст сообщений.

## Verification

По правилу **Docker-only** из `spec-continuation`: загрузить `source docker/.env.<env>`, команды через `docker exec ${APP_NAME}_fpm` (или `docker compose ... run --rm fpm`). Проверить `npm run build` в контейнере `fpm` и при наличии тестов — затронутые проверки.
