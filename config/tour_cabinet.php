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

];
