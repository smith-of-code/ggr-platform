# Прогресс — video-presentation-block

## Completed tasks

- T1. Бэкенд: MainPageController — модель данных
  - `app/Http/Controllers/Admin/MainPageController.php` — JSON_KEYS, валидация, defaults(), defaultBlockOrder()
- T2. Бэкенд: HomeController — проброс данных
  - `app/Http/Controllers/HomeController.php` — video_presentation в jsonKeys, исправлено декодирование дефолтов
- T3. Админка: форма — blockLabels, sectionTitleKeys, defaultBlockOrder
  - `resources/js/Pages/Admin/MainPage/Index.vue` — blockLabels, sectionTitleKeys, defaultBlockOrder, useForm
- T4. Админка: форма — поля video_presentation
  - `resources/js/Pages/Admin/MainPage/Index.vue` — шаблон редактирования: видео, миссия, цели, ценности, организаторы, история, факты, аудитория
- T5. Публичная страница: computed и данные
  - `resources/js/Pages/MainPage.vue` — computed: vp, vpGoals, vpValues, vpOrganizers, vpFacts; функции vpShowVideo, openPresentationVideo, closePresentationVideo
- T6. Публичная страница: шаблон блока (видео + миссия)
  - `resources/js/Pages/MainPage.vue` — секция video_presentation: видеоплеер с обложкой + миссия
- T7. Публичная страница: шаблон блока (цели, ценности, организаторы)
  - `resources/js/Pages/MainPage.vue` — карточки целей, ценностей, организаторов с фото
- T8. Публичная страница: шаблон блока (история, факты, аудитория)
  - `resources/js/Pages/MainPage.vue` — секции истории, цифр/фактов, аудитории
- T9. Финализация: линтер + сборка
  - Линтер: 0 ошибок
  - `npm run build` — OK (4.79s)

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
