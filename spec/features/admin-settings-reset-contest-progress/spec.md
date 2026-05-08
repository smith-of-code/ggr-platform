# Сброс прогресса конкурса участника из `/admin/settings/` (admin-settings-reset-contest-progress)

## Goal

Дать админу портала возможность из раздела `/admin/settings/` найти конкретного участника и одной кнопкой сбросить его прогресс по конкурсу (этапы 1–3) до состояния «ничего не выбрано», в т.ч. удалить отправленные анкеты этапа 1, ответы этапа 2 и материалы этапа 3.

## In-scope

- Новая карточка-вход на странице `Admin/Settings/Index.vue` («Сброс прогресса конкурса») со ссылкой на отдельный экран.
- Отдельная страница `/admin/settings/contest-reset` (`admin.settings.contest-reset.index`) с поиском участника (по `email`, `id`, ФИО) и пагинированной таблицей кандидатов: ID, email, ФИО, направление, текущий этап, дата отправки этапа 2, кнопка «Сбросить» с подтверждением. Список ограничен пользователями с записью в `tour_cabinet_contest_progress` ИЛИ `tour_cabinet_contest_city_submissions` ИЛИ `tour_cabinet_contest_stage2_answers` (т.е. только реально начавшие конкурс) — иначе нет смысла сбрасывать.
- Сервис `App\Services\Admin\TourCabinetContestProgressResetter` с методом `reset(User $user): void` (атомарная транзакция):
  - удаляет файл `stage3_attachment_path` со storage (`config('filesystems.upload_disk')`), если он есть;
  - удаляет все записи `tour_cabinet_contest_city_submissions` для `user_id` (LMS-`lms_form_submissions` НЕ трогаем — см. Constraints);
  - удаляет все записи `tour_cabinet_contest_stage2_answers` для `user_id`;
  - удаляет запись `tour_cabinet_contest_progress` для `user_id` (направление, города, current_stage, stage3_text/video/attachment, completion_notified_at и т.д.);
  - возвращает участника к стартовому состоянию: на дашборде ЛК он снова увидит выбор направления.
- Маршрут `POST /admin/settings/contest-reset/{user}` (`admin.settings.contest-reset.reset`, `whereNumber('user')`) — вызывает сервис, редиректит на `index` с flash `success` и упомянутым email участника.
- Регистрация всех новых маршрутов в существующей admin-группе (`auth + admin`-доступ через текущее middleware-окружение раздела `/admin/settings`).
- Обновление `spec/features/lk-participant-contests/spec.md` (или `tour-cabinet/spec.md`) разделом-ссылкой на эту фичу.

## Out-of-scope

- Сброс прогресса коммерческих туров (`tour_cabinet_commerce_progress`) — другая сущность; при необходимости — отдельная фича.
- Массовый сброс по фильтру (направление/город/диапазон дат) — только по одному участнику за раз.
- Email-уведомление участнику о сбросе — это администраторское действие, авто-письма не отправляем.
- История/аудит сбросов в БД (отдельная таблица `contest_progress_resets`) — фиксируем факт через стандартный лог `Log::info('contest_progress_reset', [...])`, отдельной UI-истории нет.
- Частичный сброс (только этап 3, только этап 2) — всегда сброс целиком.
- Удаление LMS-`lms_form_submissions`, на которые ссылается `tour_cabinet_contest_city_submissions.lms_form_submission_id` — оставляем как есть (см. Constraints).

## Constraints

- Все команды — в Docker по правилу `spec-continuation` (`source docker/.env.local && docker exec ${APP_NAME}_fpm <cmd>`).
- Сервис `TourCabinetContestProgressResetter` работает в одной `DB::transaction`. Удаление файла со storage — после `commit` через `register_shutdown` или внутри транзакции до сохранения (последовательно: сначала зачитать `stage3_attachment_path`, потом транзакция, потом `Storage::delete`); ошибки `Storage::delete` логируются как `tour_cabinet_contest_progress_reset_storage_failed`, но не валят саму операцию (запись в БД уже стёрта).
- LMS-`lms_form_submissions` (и связанные `lms_form_responses`), на которые ссылались удалённые `tour_cabinet_contest_city_submissions`, **остаются в БД** — это первичные данные LMS-модуля, они могут использоваться в отчётах и для аналитики. Участнику будет показан выбор городов с нуля: повторная отправка той же анкеты создаст новую запись `lms_form_submissions` (контракт LMS не нарушается).
- Идемпотентность: если у пользователя уже нет записей в любой из трёх таблиц, `reset` отрабатывает без ошибки (по сути no-op + storage cleanup, если файл всё-таки лежал отдельно).
- Доступ к маршрутам — внутри уже существующей admin-группы `routes/web.php` (`prefix=admin`, middleware того же раздела, что и `admin.settings.{index,mail,page-visibility,forms-trash}`).
- Поиск участников: `email LIKE %q%`, `last_name/first_name/patronymic LIKE %q%`, либо точное совпадение `id = (int) $q`. Без q — список последних 25 по `updated_at` записей `tour_cabinet_contest_progress`.

## Open questions

(пусто — все решения зафиксированы выше; отдельная in-flight фича по коммерческим турам — out-of-scope.)
