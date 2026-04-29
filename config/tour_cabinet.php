<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LMS-событие, чьи формы показываются в ЛК туров
    |--------------------------------------------------------------------------
    |
    | Slug из таблицы lms_events (как в URL lms-admin/{slug}/forms).
    |
    */
    'lms_event_slug' => env('TOUR_CABINET_LMS_EVENT_SLUG', 'vshgr-2026'),

    /*
    |--------------------------------------------------------------------------
    | Формы этапа 1 конкурса в ЛК туров (LmsForm того же события)
    |--------------------------------------------------------------------------
    |
    | Две формы в LMS Admin: без пометки «Нужно больше данных» / с пометкой.
    | Публичное прохождение: GET/POST /forms/{slug}. Ответы — lms_form_submissions (user_id).
    | Переопределение из БД: админка /admin/tour-cabinet/forms (группа settings tour_cabinet).
    |
    */
    'contest_stage1_form_slug_standard' => env('TOUR_CABINET_CONTEST_STAGE1_FORM_SLUG_STANDARD'),

    'contest_stage1_form_slug_more_data' => env('TOUR_CABINET_CONTEST_STAGE1_FORM_SLUG_MORE_DATA'),

    /*
    |--------------------------------------------------------------------------
    | «Стандартная анкета» — отдельный блок на дашборде ЛК туров
    |--------------------------------------------------------------------------
    |
    | Slug любой LmsForm платформы (не обязан быть из события туров).
    | Блок отображается выше блока «Конкурс»; если slug пуст / форма неактивна — блок скрыт.
    | Переопределение из БД: админка /admin/tour-cabinet (группа settings tour_cabinet).
    |
    */
    'dashboard_standard_form_slug' => env('TOUR_CABINET_DASHBOARD_STANDARD_FORM_SLUG'),

    /*
    |--------------------------------------------------------------------------
    | Уведомление участника о завершении всех 3 этапов конкурса
    |--------------------------------------------------------------------------
    |
    | Письмо отправляется однократно после успешного сохранения ответа этапа 3
    | в TourCabinetContestController::storeStage3, если для направления участника
    | max_contest_stages = 3, этапы 1-2 завершены и `enabled = true`.
    | Тема и тело редактируются администратором: /admin/tour-cabinet → блок
    | «Уведомление о завершении конкурса». Значения из таблицы settings (группа
    | tour_cabinet) имеют приоритет над этими дефолтами.
    |
    */
    'contest_completion_notification' => [
        'enabled' => filter_var(
            env('TOUR_CABINET_CONTEST_COMPLETION_NOTIFICATION_ENABLED', false),
            FILTER_VALIDATE_BOOLEAN
        ),
        'subject' => env(
            'TOUR_CABINET_CONTEST_COMPLETION_NOTIFICATION_SUBJECT',
            'Конкурс пройден — ожидайте обратную связь'
        ),
        'body' => env(
            'TOUR_CABINET_CONTEST_COMPLETION_NOTIFICATION_BODY',
            'Спасибо за участие! Ожидайте — мы обязательно вернёмся с обратной связью и результатами конкурса.'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Блок «Коммерческие туры» в ЛК туров (под блоком «Конкурс»)
    |--------------------------------------------------------------------------
    |
    | enabled — глобальный выключатель отображения блока на дашборде. По умолчанию
    | false, чтобы блок не появился у пользователей до первичной настройки админом.
    | stage3_subject / stage3_body — заголовок и текст экрана этапа 3 «ожидайте
    | обратной связи»; редактируются в админке (settings группа tour_cabinet:
    | commerce_tours_enabled / commerce_tours_stage3_subject /
    | commerce_tours_stage3_body) и имеют приоритет над этими дефолтами.
    |
    */
    'commerce_tours' => [
        'enabled' => filter_var(
            env('TOUR_CABINET_COMMERCE_TOURS_ENABLED', false),
            FILTER_VALIDATE_BOOLEAN
        ),
        'stage3_subject' => env(
            'TOUR_CABINET_COMMERCE_TOURS_STAGE3_SUBJECT',
            'Заявка принята'
        ),
        'stage3_body' => env(
            'TOUR_CABINET_COMMERCE_TOURS_STAGE3_BODY',
            'Спасибо за проявленный интерес! Мы с вами свяжемся — ожидайте обратной связи.'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Контакт для альтернативного канала (email), показывается в ЛК поддержки
    |--------------------------------------------------------------------------
    */
    'support_contact_email' => env('TOUR_CABINET_SUPPORT_CONTACT_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Публичный URL портала (main), если ЛК туров открывают с поддомена LMS
    |--------------------------------------------------------------------------
    |
    | Для ссылки «ЛК Туров» из LmsLayout: полная перезагрузка на этот origin,
    | иначе Inertia-запрос на lms → 301 → main → login HTML даёт «некорректный ответ».
    | Пример: https://main.rosatom-travel.ru
    |
    */
    'portal_public_url' => env('PORTAL_PUBLIC_URL', ''),

];
