# Фича: Динамические настройки

**Статус**: auto-detected, needs review

## Описание

Система хранения настроек в БД (таблица settings) с кешированием и возможностью переопределения конфигов Laravel (mail) через UI.

## Связанные сущности

### Модели
- `App\Models\Setting`

### Сервисы
- `App\Services\SettingsService` — getMailSettings, getGroup, setGroup, applyMailConfig

### Контроллеры
- `App\Http\Controllers\Admin\SettingsController` — index, mail, updateMail, testMail
- `App\Http\Controllers\Admin\PageVisibilityController` — управление видимостью разделов сайта (`admin.settings.page-visibility.*`)
- `App\Http\Controllers\Admin\LmsFormTrashController` — корзина soft-deleted форм (`admin.settings.forms-trash.*`); см. фичу `tour-cabinet-forms-delete-copy`
- `App\Http\Controllers\Admin\ContestProgressResetController` — сброс прогресса конкурса конкретного участника (`admin.settings.contest-reset.*`); см. фичу `admin-settings-reset-contest-progress`

### Страницы
- `Pages/Admin/Settings/Index.vue` — хаб с карточками всех разделов настроек
- `Pages/Admin/Settings/Mail.vue` — настройки SMTP
- `Pages/Admin/Settings/FormsTrash.vue` — корзина форм
- `Pages/Admin/Settings/ContestReset/Index.vue` — поиск участника и сброс его прогресса конкурса

## Ключевые workflow

- Настройки почты хранятся в группе mail_settings
- ENV-значения используются как fallback
- Применение настроек через config() в runtime (applyMailConfig)
- Тестовая отправка письма (testMail)
- Кеширование настроек из БД
