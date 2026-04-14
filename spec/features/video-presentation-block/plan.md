# План — video-presentation-block

## Milestones

1. **Бэкенд: данные и валидация** — Расширить `MainPageController` (JSON_KEYS, defaults, defaultBlockOrder, валидация), обновить `HomeController::buildMainPageData()`.
2. **Админка: форма редактирования** — Добавить секцию `video_presentation` в `Admin/MainPage/Index.vue` (blockLabels, sectionTitleKeys, defaultBlockOrder, форма через DynamicList + RInput).
3. **Публичная страница: рендер блока** — Реализовать красивый блок в `MainPage.vue` с видеоплеером, информационными карточками, анимациями.
4. **Финализация** — Проверка линтера, сборка, регрессия.

## UI Components

- `DynamicList` (`@/Pages/Admin/OpportunityToursPage/DynamicList.vue`) — для goals, values, organizers, facts в админке.
- `RInput`, `RButton`, `RCheckbox` — из `@rosatom-ggr/ui-kit` (глобально зарегистрированы).
- Видео: `<video>` / `<iframe>` — inline, аналогично существующему блоку `videos`.
- Анимации: `useScrollReveal` composable + CSS-классы `reveal`, `reveal-delay-*`.

## Verification

Все команды выполняются по паттерну `source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`.
