# Consent — прогресс

## Completed

1. Task 1: Миграция таблицы consents
   - ✅ `database/migrations/2026_04_07_200000_create_consents_table.php`
   - Миграция применена, таблица создана

2. Task 2: Модель Consent
   - ✅ `app/Models/Consent.php`
   - Модель с fillable, casts, 6 констант event_type

3. Task 3: ConsentService
   - ✅ `app/Services/ConsentService.php`
   - Статический метод `log()` с автосбором IP, UA, URL, session_id

4. Task 4: Интеграция — регистрация портала
   - ✅ `app/Http/Controllers/Auth/RegisteredUserController.php`
   - Логирование после создания пользователя, event_type = 'registration'

5. Task 5: Интеграция — LMS auth (invite + activate)
   - ✅ `app/Http/Controllers/Lms/AuthController.php`
   - Логирование в registerByInvite (lms_invite) и activate (lms_activate)
   - Маршрут lms.register не зарегистрирован — отдельный метод register не используется

6. Task 6: Интеграция — форма заявок (4 формы)
   - ✅ `app/Http/Controllers/ApplicationController.php` — валидация consent + логирование
   - ✅ `resources/js/Pages/Education/Partials/ApplicationForm.vue` — чекбокс + consent в form
   - ✅ `resources/js/Pages/Education/Index.vue` — чекбокс + consent в form
   - ✅ `resources/js/Pages/Tours/Show.vue` — чекбокс в модалке + consent в payload
   - ✅ `resources/js/Pages/Directions/ShowAtomsVkusa.vue` — чекбокс + consent в payload

7. Task 7: Интеграция — контактная форма
   - ✅ `app/Http/Controllers/HomeController.php` — валидация consent + логирование
   - ✅ `resources/js/Pages/Home.vue` — чекбокс + consent в contactForm

8. Task 8: Smoke-тест и финальная проверка
   - ✅ Модель создаёт/читает записи
   - ✅ PHP lint — ошибок нет
   - ✅ Frontend build — успешно
   - ✅ Миграция применена

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
