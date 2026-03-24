# Фича: Авторизация через ВКонтакте и Яндекс

**Статус**: implemented

## Цель

Дать участникам возможность привязать аккаунты ВКонтакте и Яндекс ID к своей учётной записи в личном кабинете, а затем использовать SSO для входа на странице логина LMS-события.

## In-scope

- Установка и настройка пакета `laravel/socialite` + кастомный VK ID провайдер + `socialiteproviders/yandex`
- Новая таблица `social_accounts` (user_id, provider, provider_id, …) для хранения привязок
- Раздел «Привязанные аккаунты» на странице профиля участника (`Lms/Profile/Edit.vue`) с кнопками «Привязать ВК» / «Привязать Яндекс» и возможностью отвязать
- Backend-маршруты привязки: redirect → callback → сохранение в `social_accounts`
- Кнопки «Войти через ВК» / «Войти через Яндекс» на странице логина LMS (`Lms/Auth/Login.vue`)
- Backend-маршруты SSO-логина: redirect → callback → поиск по `social_accounts` → `Auth::login()`
- Если при SSO-логине `social_accounts` не найден — редирект обратно на логин с ошибкой «Аккаунт не привязан»
- Конфигурация OAuth credentials через `config/services.php` + `.env`

## Out-of-scope

- Регистрация нового пользователя через SSO (только привязка существующего)
- Другие провайдеры (Google, Apple, Telegram и т.д.) — можно добавить позже через ту же архитектуру
- Модификация `auth_method` поля в `LmsEvent` — SSO работает глобально, не per-event
- Административный UI для управления SSO-настройками

## Constraints

- Привязка доступна только авторизованному пользователю из личного кабинета LMS
- Один провайдер — один аккаунт на пользователя (unique: user_id + provider)
- Один `provider_id` — один пользователь (unique: provider + provider_id) — нельзя привязать один ВК к двум аккаунтам
- Пакет: `laravel/socialite` + `socialiteproviders/yandex`
- VK ID: кастомный Socialite-провайдер `App\Socialite\VkIdProvider` (endpoint'ы `id.vk.ru`, обязательный PKCE S256, формат callback `payload`)
- Все команды — только через Docker

## Технические решения

### Таблица `social_accounts`

| Поле | Тип | Примечание |
|------|-----|------------|
| id | bigint PK | |
| user_id | FK → users | cascade |
| provider | string | `vkontakte`, `yandex` |
| provider_id | string | ID пользователя у провайдера |
| token | text | nullable, encrypted |
| refresh_token | text | nullable, encrypted |
| expires_at | datetime | nullable |
| timestamps | | |
| **unique** | (provider, provider_id) | |
| **unique** | (user_id, provider) | |

### Маршруты

OAuth-провайдеры требуют фиксированный callback URL. Поэтому callback — глобальный, а event slug и тип операции (login/link) сохраняются в сессии перед redirect.

#### Инициация (per-event)

| Метод | URI | Action | Name |
|-------|-----|--------|------|
| GET | `/lms/{event:slug}/social/{provider}/login` | сохранение event+flow=login в сессию → redirect к провайдеру | `lms.social.login` |
| GET | `/lms/{event:slug}/social/{provider}/link` | сохранение event+flow=link в сессию → redirect к провайдеру (auth required) | `lms.social.link` |
| DELETE | `/lms/{event:slug}/social/{provider}/unlink` | отвязка (auth required) | `lms.social.unlink` |

#### Глобальный callback

| Метод | URI | Action | Name |
|-------|-----|--------|------|
| GET | `/auth/social/{provider}/callback` | чтение flow из сессии → обработка login или link | `social.callback` |

### VK ID API (кастомный провайдер)

Пакет `socialiteproviders/vkontakte` использует **старый** VK OAuth API (`oauth.vk.ru`), несовместимый с VK ID (`id.vk.ru`). Поэтому реализован кастомный Socialite-провайдер `App\Socialite\VkIdProvider`.

| Аспект | Старый VK OAuth (`socialiteproviders/vkontakte`) | VK ID (кастомный `VkIdProvider`) |
|--------|--------------------------------------------------|----------------------------------|
| Auth URL | `oauth.vk.ru/authorize` | `id.vk.ru/authorize` |
| Token URL | `oauth.vk.ru/access_token` | `id.vk.ru/oauth2/auth` (POST) |
| User Info | `api.vk.ru/method/users.get` (GET) | `id.vk.ru/oauth2/user_info` (POST) |
| PKCE | Не поддерживается | **Обязателен** (S256) |
| `device_id` | Нет | Обязателен при обмене кода |
| `client_secret` | Используется | Не используется (PKCE вместо него) |
| Формат callback | Стандартные query params | JSON в `payload` query param |

Провайдер регистрируется через `Socialite::extend('vkontakte', ...)` в `AppServiceProvider`. Для Yandex используется стандартный `socialiteproviders/yandex`.

### Контроллер

`App\Http\Controllers\Lms\SocialAuthController` — единый контроллер для link/unlink/login.

### UI

- **Login.vue**: добавить разделитель «или» и кнопки SSO под формой email/пароль
- **Profile/Edit.vue**: добавить секцию «Привязанные аккаунты» с кнопками привязки/отвязки и статусом

## Open questions

(нет)
