# Задачи — video-presentation-block

## T1. Бэкенд: MainPageController — модель данных

- **Цель**: Добавить `video_presentation` в JSON_KEYS, defaults, defaultBlockOrder, валидацию.
- **Scope**: `app/Http/Controllers/Admin/MainPageController.php`
- **DoD**: Ключ `video_presentation` в JSON_KEYS; правила валидации для всех вложенных полей; дефолтные значения; блок в defaultBlockOrder.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan route:list --name=admin.main-page`

## T2. Бэкенд: HomeController — проброс данных

- **Цель**: Убедиться что `video_presentation` корректно декодируется и передаётся на публичную страницу.
- **Scope**: `app/Http/Controllers/HomeController.php`
- **DoD**: `video_presentation` присутствует в `pageData` на публичной странице.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm php artisan tinker --execute="..."`

## T3. Админка: форма — blockLabels, sectionTitleKeys, defaultBlockOrder

- **Цель**: Зарегистрировать блок `video_presentation` в метаданных формы.
- **Scope**: `resources/js/Pages/Admin/MainPage/Index.vue`
- **DoD**: Блок видим в drag-and-drop списке; заголовок секции редактируется.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T4. Админка: форма — поля video_presentation

- **Цель**: Добавить поля редактирования блока: видео (embed/file/thumbnail/title), миссия, цели, ценности, организаторы, история, факты, аудитория.
- **Scope**: `resources/js/Pages/Admin/MainPage/Index.vue`
- **DoD**: Все поля отображаются при раскрытии блока; DynamicList для массивных полей.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T5. Публичная страница: computed и данные

- **Цель**: Добавить computed-свойства для данных блока video_presentation.
- **Scope**: `resources/js/Pages/MainPage.vue` (script section)
- **DoD**: Computed-ы: `videoPresentation`, `vpGoals`, `vpValues`, `vpOrganizers`, `vpFacts`.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T6. Публичная страница: шаблон блока (видео + миссия)

- **Цель**: Реализовать верхнюю часть блока — видеоплеер и секция миссии.
- **Scope**: `resources/js/Pages/MainPage.vue` (template section)
- **DoD**: Видео (embed/file) отображается с обложкой и кнопкой play; миссия отображается рядом.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T7. Публичная страница: шаблон блока (цели, ценности, организаторы)

- **Цель**: Реализовать информационные секции — цели, ценности, организаторы.
- **Scope**: `resources/js/Pages/MainPage.vue` (template section)
- **DoD**: Карточки целей, ценностей с иконками; карточки организаторов с фото.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T8. Публичная страница: шаблон блока (история, факты, аудитория)

- **Цель**: Реализовать секции истории, цифр/фактов, аудитории.
- **Scope**: `resources/js/Pages/MainPage.vue` (template section)
- **DoD**: Текст истории; карточки фактов с анимацией; описание аудитории.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`

## T9. Финализация: линтер + сборка

- **Цель**: Проверить отсутствие ошибок линтера и успешную сборку.
- **Scope**: все изменённые файлы
- **DoD**: 0 ошибок линтера; `npm run build` — OK.
- **Verify**: `source docker/.env.local && docker exec ${APP_NAME}_fpm npm run build`
