# Фича: Привязка LMS-формы к кнопке в направлении

**Статус**: implemented

## Цель

Дать возможность указать LMS-форму в блоке «Платное участие» направления и открывать её в модальном окне на публичной странице по клику на кнопку «Оставить заявку».

## В scope

- Миграция: добавить колонку `paid_form_slug` (string, nullable) в таблицу `directions`
- Обновить модель `Direction`: fillable, cast не нужен (строка)
- Админка `Directions/Form.vue`: селект выбора LMS-формы в блоке «Платное участие»
- `Admin\DirectionController`: передавать список активных форм на `edit`/`create`, валидировать `paid_form_slug`
- Публичный `DirectionController@show`: загружать форму + поля по `paid_form_slug`, передавать на фронт
- `Directions/Show.vue`: кнопка «Оставить заявку» открывает модалку с формой (используем `Modal.vue`)
- `Directions/ShowAtomsVkusa.vue`: аналогичная логика, если применимо
- Отправка формы из модалки через `POST /forms/{slug}/submit` (существующий endpoint)

## Вне scope

- Создание новых LMS-форм (уже реализовано)
- Изменение конструктора форм
- Привязка формы к другим сущностям (туры, города и т.д.)
- Авторизация / роли

## Ограничения

- Используем существующий `Modal.vue` (`resources/js/Components/Modal.vue`)
- Форма отправляется через `axios.post('/forms/{slug}/submit')` — существующий API `FormPublicController@submit`
- Если `paid_form_slug` не указан — кнопка работает как раньше (скролл к турам)
- Хранение по slug, а не по id — чтобы не создавать FK между разными доменами (directions ↔ lms_forms)

## Связанные сущности

### Существующие (без изменений)
- `App\Models\Lms\LmsForm` — форма
- `App\Models\Lms\LmsFormField` — поля формы
- `App\Http\Controllers\Lms\FormPublicController` — публичный submit
- `resources/js/Components/Modal.vue` — модальное окно

### Изменяемые
- `App\Models\Direction` — добавляется `paid_form_slug`
- `App\Http\Controllers\Admin\DirectionController` — передача форм, валидация
- `App\Http\Controllers\DirectionController` — загрузка формы для фронта
- `resources/js/Pages/Admin/Directions/Form.vue` — селект формы
- `resources/js/Pages/Directions/Show.vue` — модалка с формой
- `resources/js/Pages/Directions/ShowAtomsVkusa.vue` — модалка с формой
