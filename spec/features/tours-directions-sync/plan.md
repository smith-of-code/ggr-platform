# План: tours-directions-sync

## Milestones

1. **Инфраструктура моделей** — добавить helper-методы на `Direction`, relationship на `Tour` (без удаления старого кода, чтобы не сломать текущую работу)
2. **Миграции данных** — три миграции: `tours`, contest-таблицы, `directions.project_key`. Каждая добавляет `direction_id`, мигрирует данные через маппинг `project_key` → `directions.id`, удаляет старую колонку
3. **Удаление `Tour::PROJECTS`** — убрать константу, обновить Tour модель (fillable, relationship), обновить публичный `TourController`
4. **Backend-контроллеры и сервисы** — заменить все `Tour::PROJECTS` на `Direction`-запросы, обновить валидацию (`exists:directions,id`), обновить маршруты
5. **Frontend** — каталог (динамические options из props), админка туров/направлений (select из directions), TourCabinet Vue-компоненты
6. **Seeders + финализация** — обновить seeders (`direction_id` вместо `project`), верификация полного цикла

## Маппинг данных при миграции

| Старый `project` / `project_key` | Direction slug | Direction `id` |
|---|---|---|
| `start_atomgrad` | `start-v-atomgrad` | определяется по `project_key` в миграции |
| `atoms_vkusa` | `atomy-vkusa` | определяется по `project_key` в миграции |
| `llr` | `luchshie-lyudi-rosatoma` | определяется по `project_key` в миграции |

Миграция использует `directions.project_key` для маппинга (колонка ещё существует в момент выполнения). Порядок миграций:
1. tours.direction_id (маппинг через directions.project_key)
2. contest tables direction_id (маппинг через directions.project_key)
3. drop directions.project_key (после того как все ссылки мигрированы)

## Verification

Каждая задача верифицируется через Docker-команды:
```
source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>
```
- Миграции: `php artisan migrate`
- Маршруты: `php artisan route:list`
- Seed: `php artisan db:seed --class=...`
- Lint: `./vendor/bin/pint --test` (PHP) / `npx eslint` (JS)
