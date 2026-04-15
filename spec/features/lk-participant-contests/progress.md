# Прогресс: ЛК участника + конкурсные этапы

## Текущее состояние

- **Спека**: `spec.md`.
- **Этап 1**: миграции, модели, `TourCabinetContestController`, `TourCabinet/Contest.vue`, привязка сабмита — `TourCabinetContestFormLinker`.
- **Админка**: `/admin/tour-cabinet/forms` (slug форм + settings), `/admin/tour-cabinet/direction-cities`, `/admin/tour-cabinet/stage2-questions`.
- **Этапы 2–3 в ЛК**: вкладки на дашборде `/tour-cabinet`; `POST` `/tour-cabinet/contest/stage-2`, `/tour-cabinet/contest/stage-3`, `POST /tour-cabinet/contest/complete-stage-1`; `GET` `/tour-cabinet/contest*` → редирект на дашборд `#tour-cabinet-contest`. Панели `Contest/ContestStage{1,2,3}Panel.vue`.

## Не начато

- Excel.

## Следующий шаг

1. Экспорт заявок / ответов в Excel (колонки и права по спеке).
