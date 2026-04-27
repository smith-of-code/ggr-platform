# План — assignment-template-download

## Milestones

1. Добавить метод `downloadTemplate` в `Lms\AssignmentController` — proxy-download через `Storage::disk()->download()` с оригинальным именем
2. Добавить метод `downloadTaskTemplate` — аналогично для task-level шаблонов
3. Зарегистрировать маршруты в `routes/lms.php`
4. Обновить фронт: `Show.vue` — заменить прямые ссылки на route-based URL
5. Обновить фронт: `InlineAssignment.vue` — аналогично
6. Верификация: build + ручная проверка

## Verification

Проверка по Docker command pattern из spec-continuation:
- `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --path=assignments`
- `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
