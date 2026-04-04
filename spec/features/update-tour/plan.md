# update-tour — План

## Milestones

1. **Admin UI**: переименовать label «Город старта» → «Логистические точки» в форме и таблице туров
2. **Backend**: обеспечить eager-loading `cities` в `DirectionController` и `FavoriteController`
3. **Каталог туров**: чипсы городов проведения + логистические точки на карточке `Tours/Index.vue`
4. **Детальная страница**: чипсы городов + логистические точки на `Tours/Show.vue`
5. **Главная страница**: чипсы городов + логистические точки на `Home.vue`
6. **Направления**: чипсы городов + логистические точки на `Directions/Show.vue` и `ShowAtomsVkusa.vue`
7. **Возможности + Избранное**: чипсы городов + логистические точки на `OpportunityTours/Index.vue` и `Favorites/Index.vue`

## UI Components

- `RBadge` — уже используется на карточках (variant="primary", "info"), используем для чипсов городов
- Чипсы городов — `<RBadge variant="info" size="sm">{{ city.name }}</RBadge>` в `flex flex-wrap gap-1`
- «Логистические точки» — текст `text-sm text-gray-500` с иконкой геолокации (как сейчас start_city)

## Verification

Проверка по паттерну Docker exec из spec-continuation.
