# Фича: LMS — Инвайты и онбординг

**Статус**: auto-detected, needs review

## Описание

Система приглашений участников через токены, массовый импорт пользователей, активация профилей, отправка приглашений по email.

## Связанные сущности

### Модели
- `App\Models\Lms\LmsInvitation`
- `App\Models\Lms\LmsProfile`

### Mail
- `App\Mail\InvitationMail`

### Jobs
- `App\Jobs\SendMailJob`

### Контроллеры
- `App\Http\Controllers\Lms\AuthController` — showInvite, registerByInvite, showActivate, activate
- `App\Http\Controllers\Lms\Admin\InvitationController` — store, toggle, destroy
- `App\Http\Controllers\Lms\Admin\UserController` — import, sendInvitations, bulkEnroll, downloadTemplate

### Страницы
- `Pages/Lms/Auth/Invite.vue`, `Pages/Lms/Auth/Activate.vue`
- `Pages/Lms/Admin/Users/Index.vue`, `Create.vue`

## Ключевые workflow

- Админ создаёт инвайт-ссылку (LmsInvitation) с ролью, сроком действия, лимитом использований
- Участник переходит по ссылке → регистрируется через registerByInvite
- Массовый импорт пользователей из шаблона (Excel/CSV)
- Массовая отправка приглашений по email через SendMailJob (queue: emails)
- Активация профиля через invite_token в LmsProfile
- Массовая запись на курсы (bulkEnroll)
