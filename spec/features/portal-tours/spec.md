# Модуль 4. Туры возможностей

## Статус: Реализован

## Описание
Каталог туров с фильтрацией, детальные страницы с эмоциями, заявками и расширенным контентом.

## Каталог туров (Tours/Index)
- Сетка карточек
- Фильтры: проект, сезон, тип участия, город, для детей, для иностранцев
- Пагинация (12 на странице)

## Детальная страница тура (Tours/Show)

### Секции
1. **Hero** — изображение, название
2. **Бейджи** — город, продолжительность, проект, закрытый город
3. **Счётчик эмоций** — кнопки ❤️ 😮 🔥 😎 ⭐ с count
4. **Описание** — HTML контент
5. **Для кого этот тур** — target_audience
6. **Организатор** — organizer_info
7. **Города** — карточки связанных городов
8. **Даты заездов** — список с ценами и кнопкой «Оставить заявку»
9. **Sidebar** — детали тура, стоимость, кнопка заявки, избранное

### Счётчик эмоций
- 5 типов: love, wow, fire, cool, star
- Авторизованные: по user_id (один голос, можно менять/снять)
- Гости: по IP
- Кэшированный count в JSON поле `reactions_count`

### Форма заявки
- Модальное окно с полями: имя, email, телефон, сообщение
- POST `/applications` с типом `tour`
- Привязка к заезду (tour_departure_id)

## Модели
- `Tour` — расширен полями target_audience, organizer_info, reactions_count
- `TourReaction` — реакции (emoji, user_id/ip_address)
- `Application` — заявки

## Маршруты
- `GET /tours` — каталог (публичный)
- `GET /tours/{slug}` — детальная (публичный)
- `POST /tours/{tour}/react` — эмоция (публичный)
- `POST /applications` — заявка (публичный)
- `POST /favorites/tour/{id}` — избранное (auth)

## Файлы
- `resources/js/Pages/Tours/Index.vue`
- `resources/js/Pages/Tours/Show.vue`
- `app/Http/Controllers/TourController.php`
- `app/Models/Tour.php`
- `app/Models/TourReaction.php`
