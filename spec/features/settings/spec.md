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

### Страницы
- `Pages/Admin/Settings/Index.vue`, `Mail.vue`

## Ключевые workflow

- Настройки почты хранятся в группе mail_settings
- ENV-значения используются как fallback
- Применение настроек через config() в runtime (applyMailConfig)
- Тестовая отправка письма (testMail)
- Кеширование настроек из БД
