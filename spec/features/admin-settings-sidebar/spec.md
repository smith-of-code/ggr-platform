# Пункт «Настройки» в левом меню AdminLayout

## Цель

Добавить в левое меню админ-панели портала единый пункт «Настройки»,
ведущий на хаб `/admin/settings`. Подразделы открываются из карточек
хаба (`Pages/Admin/Settings/Index.vue`).

## Текущее состояние

`resources/js/Layouts/AdminLayout.vue` содержит секции:

- Дашборд / Заявки
- Контент портала (Города, Каталог туров, Промокоды, ЛК туров, Клиенты,
  Обращения ЛК туров, Отзывы, Направления, Атомы вкуса, Блог, Подписчики,
  Продукты ВШГР, Рецепты)
- Сайт (Главная, Туры возможностей, Страница ВШГР, Исследования, Хронология)
- HR (Вакансии)

Раздел `/admin/settings` ранее был доступен только через прямой URL;
ссылка из основного меню отсутствовала. Хаб содержит карточки:

- `admin.settings.mail` — Почта (SMTP)
- `admin.settings.page-visibility` — Видимость страниц
- `admin.settings.forms-trash.index` — Корзина форм
- `admin.settings.contest-reset.index` — Сброс прогресса конкурса

## Целевое состояние

В `AdminLayout.vue` после пункта «Вакансии» (секция HR) добавляется один
пункт верхнего уровня без отдельного заголовка-секции:

| ID меню          | Лейбл       | Маршрут                |
|------------------|-------------|------------------------|
| `admin.settings` | Настройки   | `admin.settings.index` |

Иконка — шестерёнка (cog, heroicons outline). Внутри хаба
`Pages/Admin/Settings/Index.vue` пользователь выбирает один из 4
подразделов через карточки.

### `activeItemId`

Пункт подсвечивается для любого URL, начинающегося с `/admin/settings`
(сам хаб, `/mail`, `/page-visibility`, `/forms-trash`, `/contest-reset`):

```js
if (url.startsWith('/admin/settings')) return 'admin.settings'
```

### `ROUTE_MAP`

Один ключ:

```js
'admin.settings': 'admin.settings.index',
```

### Видимость

Маршруты группы `/admin/*` защищены middleware `['auth', 'portal.admin']`
(флаг `users.is_admin`), поэтому пункт «Настройки» в `AdminLayout`
автоматически видим только администраторам. Отдельная проверка в Vue
не требуется.

## Не входит в скоп

- Изменение хаба `Pages/Admin/Settings/Index.vue` и его карточек.
- Раскрытие подразделов настроек прямыми пунктами в сайдбаре.
- Изменение маршрутов и контроллеров `Admin\SettingsController`,
  `Admin\PageVisibilityController`, `Admin\LmsFormTrashController`,
  `Admin\ContestProgressResetController`.
- Изменение левого меню других лейаутов (`LmsLayout`, `LmsAdminLayout`).
