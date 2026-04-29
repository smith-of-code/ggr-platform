# Прогресс: commerce-tours

## Completed tasks

### Task 1. Миграции: `tour_cabinet_commerce_progress` + `tour_cabinet_commerce_city_forms` ✓

- Files:
  - `database/migrations/2026_04_29_140000_create_tour_cabinet_commerce_progress_table.php`
  - `database/migrations/2026_04_29_140100_create_tour_cabinet_commerce_city_forms_table.php`
- Verify:
  - `php artisan migrate --pretend` (Docker) — корректный SQL `create table` для обеих таблиц с FK на `users`/`cities`/`tours`/`lms_form_submissions`, unique на `user_id` и `city_id`, индекс по `lms_form_slug`.
  - `php artisan migrate` (Docker) — DONE 23.52ms / 8.63ms.

### Task 2. Модели + связи (`TourCabinetCommerceProgress`, `TourCabinetCommerceCityForm`, `User`) ✓

- Files:
  - `app/Models/TourCabinetCommerceProgress.php` (fillable, casts `completed_at => datetime`, связи user/city/tour/lmsFormSubmission)
  - `app/Models/TourCabinetCommerceCityForm.php` (fillable, связь city)
  - `app/Models/User.php` (новый метод `tourCabinetCommerceProgress(): HasOne` рядом с `tourCabinetContestProgress`)
- Verify: php-однострочник через Docker — таблицы/fillable/casts/связи отвечают спецификации (`progress.user/city/tour/lmsFormSubmission` = BelongsTo, `cityForm.city` = BelongsTo, `user.tourCabinetCommerceProgress` = HasOne).

### Task 3. Конфиг: дефолты `commerce_tours.*` в `config/tour_cabinet.php` ✓

- Files: `config/tour_cabinet.php` (новый блок `commerce_tours` с тремя ключами и env-переопределением)
- Verify: `php artisan config:show tour_cabinet.commerce_tours` (Docker) — `enabled=false`, `stage3_subject='Заявка принята'`, `stage3_body='Спасибо за проявленный интерес! …'`.

### Task 4. SettingsService: чтение настроек коммерческих туров ✓

- Files: `app/Services/SettingsService.php` (новый метод `getTourCabinetCommerceToursStage3Notification()`, ключи `commerce_tours_enabled|stage3_subject|stage3_body`).
- Verify: php-однострочник через Docker — три сценария (default → custom → cleared) корректны: `enabled=false` + дефолтные строки → custom после `setGroup` → пустые строки fallback к дефолтам. Cache forget работает через `setGroup`.

### Task 5. Сервис данных дашборда: `TourCabinetCommerceToursDashboardData` ✓

- Files:
  - `app/Services/TourCabinetCommerceToursDashboardData.php` (метод `buildPayload(User): array` с ключами `enabled, currentStage, cityId, tourId, completedAt, availableCities[], availableTours[], hasCityForm, stage3{subject,body}`)
  - `app/Http/Controllers/TourCabinetController.php` (`dashboard(...)` принимает новый сервис, добавлен ключ `commerceTours` в Inertia payload)
- Verify: php-однострочник через Docker — без прогресса `currentStage=1, cityId=NULL, tourId=NULL, availableCities.count=7`; после `updateOrCreate` с `city_id` — `cityId=20, currentStage=2, availableTours.count=1`; ключ `stage3.subject` равен дефолту «Заявка принята».

### Task 6. Пользовательские маршруты + контроллер `TourCabinetCommerceToursController` ✓

- Files:
  - `app/Http/Controllers/TourCabinetCommerceToursController.php` (методы `storeCity`, `storeTour`, `completeStage1`, `startCityForm`, `reopenSelection`)
  - `routes/web.php` (5 маршрутов под middleware `tour-cabinet`)
- Verify: `php artisan route:list --path=tour-cabinet/commerce-tours` (Docker) — `Showing [5] routes`: `POST commerce-tours/city`, `POST commerce-tours/tour`, `POST commerce-tours/complete-stage-1`, `GET commerce-tours/stage-2/form`, `POST commerce-tours/reopen-selection`.

### Task 7. Связка сабмита формы этапа 2 → переход на этап 3 (`TourCabinetCommerceToursFormLinker`) ✓

- Files:
  - `app/Services/TourCabinetCommerceToursFormLinker.php` (метод `tryLinkAfterSubmission(LmsForm, LmsFormSubmission)` — гарды auth/access, session-key, city↔form match, прогресс, идемпотентность через `current_stage >= 3`)
  - `app/Http/Controllers/Lms/FormPublicController.php` (импорт + вызов рядом с `TourCabinetContestFormLinker` в обоих методах сабмита: JSON и Inertia)
- Verify: php-однострочник через Docker — после `tryLinkAfterSubmission` `current_stage=3`, `lms_form_submission_id` заполнен, `completed_at` установлен, сессионный ключ очищен.

### Task 8. Админ-маршруты + контроллер `Admin\TourCabinetCommerceToursController` ✓

- Files:
  - `app/Http/Controllers/Admin/TourCabinetCommerceToursController.php` (методы `index`, `storeCityForm`, `updateCityForm`, `destroyCityForm`, `updateStage3Notification`; валидация slug — активная LmsForm; redirect-fragment `tour-cabinet-admin-commerce-tours`)
  - `routes/web.php` (5 маршрутов: `GET /admin/tour-cabinet/commerce-tours`, `POST/PATCH/DELETE /admin/tour-cabinet/commerce-tours/city-forms[/{cityForm}]`, `PUT /admin/tour-cabinet/commerce-tours/stage3-notification`)
- Verify: `php artisan route:list --path=admin/tour-cabinet/commerce-tours` (Docker) — `Showing [5] routes` со всеми именами `admin.tour-cabinet.commerce-tours.*`.

### Task 9. Hub payload + админ-фронт (`TourCabinetAdminCommerceToursPanel.vue` + `CommerceTours/Index.vue`) ✓

- Files:
  - `app/Services/Admin/TourCabinetHubPageData.php` (новый метод `commerceToursPayload()`: `enabled`, `stage3{subject,body}`, `cityForms[]`, `availableCities[]`, `availableForms[]`)
  - `app/Http/Controllers/Admin/TourCabinetHubController.php` (проп `commerceToursSection`)
  - `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminCommerceToursPanel.vue` (RCard с настройками этапа 3 — чекбокс активности, тема, тело + RCard со списком привязок «город → slug формы»: inline-редактирование, добавление, удаление через `window.confirm` — соответствует прецеденту `TourCabinetAdminFormsPanel.vue`, нативный стиль input-ов)
  - `resources/js/Pages/Admin/TourCabinet/CommerceTours/Index.vue` (отдельная страница для маршрута `admin.tour-cabinet.commerce-tours.index`, проброс пропов в панель)
  - `resources/js/Pages/Admin/TourCabinet/Hub.vue` (новая секция `<section>` с подключением панели + проп `commerceToursSection`)
- UI Kit Lookup: подтверждён реюз — `RCard`, `RButton` (глобальные), нативные `<input type="checkbox">`/`<input type="text">`/`<select>`/`<textarea>` (стиль как в `TourCabinetAdminFormsPanel.vue` — единая визуальная парадигма панели; `RInput`/`RCheckbox`/`RSelect` намеренно не используются, чтобы не ломать существующий стиль), `useForm` + `router.patch/delete` Inertia, `window.confirm` для удаления (как у других CRUD-таблиц фичи).
- Verify: `npm run build` (Docker) — `built in 5.43s` без ошибок.

### Task 10. Пользовательский фронт: блок «Коммерческие туры» в `Dashboard.vue` ✓

- Files:
  - `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage1Panel.vue` (двухшаговый выбор: `<select>` город → `<select>` тур, кнопка «Перейти к этапу 2»; три `useForm` для трёх POST-маршрутов; блокировка по `locked`)
  - `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage2Panel.vue` (карточка-резюме «Город / Тур» + кнопка «Открыть анкету» (ссылка на `tour-cabinet.commerce-tours.stage-2.form`) или плашка-предупреждение при отсутствии маппинга + «Изменить выбор» через POST `reopen-selection`)
  - `resources/js/Pages/TourCabinet/CommerceTours/CommerceToursStage3Panel.vue` (статичный экран subject + body с `whitespace-pre-line`)
  - `resources/js/Pages/TourCabinet/Dashboard.vue` (импорты трёх панелей + проп `commerceTours` + новый `<section id="tour-cabinet-commerce-tours">` под блоком конкурса с табами этапов; computed: `commerceToursLocked`, `commerceToursCityName`, `commerceToursTourName`, `commerceToursStageSummary`; `activeCommerceTab` синхронизируется с `commerceTours.currentStage`; блок скрыт при `commerceTours.enabled === false`)
- UI Kit Lookup: повторно использованы `RButton` (глобальный), нативные `<select>`, `<button>` с rosatom-стилями (как в `Contest/ContestStage*Panel.vue`); иконки `IdentificationIcon`, `ChatBubbleLeftRightIcon`, `CheckCircleIcon` из `@heroicons/vue/24/outline` (уже импортированы).
- Verify: `npm run build` (Docker) — `built in 5.01s` без ошибок; `route:list --path=tour-cabinet/commerce-tours` — 5 маршрутов на месте (см. Task 6).

### Task 11. Финализация: spec/progress + полная верификация ✓

- Files: `spec/features/commerce-tours/spec.md` (раздел «Реализация (как сделано)» с финальными именами роутов/полей и переченем затронутых модулей), `spec/features/commerce-tours/progress.md` (все 11 задач в Completed, Not started/Open issues пусто).
- Verify summary:
  - PHP-lint (`ReadLints`): чисто на 13 затронутых `.php` (модели, сервисы, контроллеры, конфиг, routes).
  - ESLint/JS-lint (`ReadLints`): чисто на 8 затронутых `.vue`.
  - БД: миграции применены (`php artisan migrate` Docker — DONE 23.52ms / 8.63ms).
  - Конфиг: `php artisan config:show tour_cabinet.commerce_tours` (Docker) — три ключа с ожидаемыми дефолтами.
  - Settings/Service: php-однострочник — три сценария (default → custom → cleared) корректны.
  - Сервис дашборда: php-однострочник — payload содержит ожидаемые ключи и реагирует на `city_id` (`availableTours.count` обновляется).
  - Linker: php-однострочник — `current_stage=3`, `lms_form_submission_id` заполнен, `completed_at` установлен, сессионный ключ очищен.
  - Маршруты: `php artisan route:list --path=tour-cabinet/commerce-tours` — 5 пользовательских + 5 админских; финальный комбинированный список — `Showing [10] routes`.
  - Frontend: `npm run build` (Docker) — успех (`built in 5.01s`).
- Git diff stat (трекаемые файлы): 10 файлов изменено, +291 строк. Новые файлы (не трекаемые до коммита) — 9 в `app/`, `resources/js/Pages/TourCabinet/CommerceTours/`, `resources/js/Pages/Admin/TourCabinet/CommerceTours/`, 2 миграции.

## Partially completed

(пусто)

## Not started

(пусто) — фича реализована полностью.

## Open issues

(пусто)
