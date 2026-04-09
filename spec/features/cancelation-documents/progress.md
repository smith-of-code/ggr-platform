# cancelation-documents — прогресс

## Completed

1. Task 1: Аудит текущего потока документов — см. `plan.md` § «Аудит потока»
2. Task 2: Миграция статусов и метаданных — `database/migrations/2026_04_09_120000_add_review_fields_to_lms_profile_documents.php`
3. Task 3: Сервис подтверждения и аннулирования — `app/Services/Lms/LmsProfileDocumentReviewService.php`, модель `LmsProfileDocument`
4. Task 4: Email-уведомление — `app/Mail/LmsProfileDocumentAnnulledMail.php`, `resources/views/emails/lms-profile-document-annulled.blade.php`, очередь `SendMailJob`
5. Task 5: HTTP API / actions для админки — `UserController::approveProfileDocument`, `annulProfileDocument`, маршруты `lms.admin.users.documents.*`
6. Task 6: Админский UI — `resources/js/Pages/Lms/Admin/Users/Show.vue`
7. Task 7: UI участника и правила загрузки — `ProfileController`, `resources/js/Pages/Lms/Profile/Edit.vue`, `LmsProfile::getMissingFields()`
8. Task 8: Регрессия — миграции PostgreSQL-специфичного SQL обёрнуты для SQLite (`2026_03_17`, `2026_03_31`, `2026_03_27_400000`, `2026_04_02`, `2026_03_31_120000`, `2026_03_19`); `php artisan test --filter=ProfileDocumentReviewTest` — 6 тестов; `npm run build` OK
9. Task 9: Заявка «Хочу заменить документ» — таблица `lms_profile_document_replace_requests`, модель, `LmsProfileDocumentReplaceRequestService`, маршруты participant + admin, UI в `Edit.vue` / `Show.vue`, тесты в `ProfileDocumentReviewTest`

## Partially completed

(пусто)

## Not started

(пусто)

## Open issues

(пусто)
