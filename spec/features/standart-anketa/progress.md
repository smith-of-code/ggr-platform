# Прогресс: standart-anketa

## Completed tasks

### Task 1: SettingsService — ключ `tour_cabinet_dashboard_standard_form_slug` ✓
- Files: `config/tour_cabinet.php`, `app/Services/SettingsService.php`
- Verify: `app(SettingsService::class)->getTourCabinetDashboardStandardFormSlug()` возвращает `null` при отсутствии настройки (через `php artisan tinker` в Docker).

### Task 2: Бэкенд дашборда — проп `dashboardStandardForm` ✓
- Files: `app/Http/Controllers/TourCabinetController.php`
- Verify: маршруты `/tour-cabinet` грузятся (`route:list`); приватный `dashboardStandardFormForUser` возвращает `null`, либо `{slug,title,submitted}` для активной формы текущего slug.

### Task 3: Админ-маршрут + контроллер — `PUT admin.tour-cabinet.dashboard-form.update` ✓
- Files: `routes/web.php`, `app/Http/Controllers/Admin/TourCabinetFormsController.php`
- Verify: `php artisan route:list --path=tour-cabinet/dashboard-form` показывает новый PUT-маршрут.

### Task 4: Payload админки — `dashboardStandardFormSlug` + `allFormsOptions` ✓
- Files: `app/Services/Admin/TourCabinetHubPageData.php`
- Verify: `formsPayload()` отдаёт оба ключа; контроллеры `TourCabinetHubController` и `Admin\TourCabinetFormsController::index` пробрасывают весь payload.

### Task 5: Админ-фронт — карточка «Дашборд: Стандартная анкета» ✓
- Files: `resources/js/Pages/Admin/TourCabinet/TourCabinetAdminFormsPanel.vue`, `resources/js/Pages/Admin/TourCabinet/Forms/Index.vue`
- Verify: `npm run build` (Docker) — успех; `SearchSelect` по `allFormsOptions` (фильтр `is_active`); submit → `admin.tour-cabinet.dashboard-form.update`.

### Task 6: Клиентский блок «Стандартная анкета» на дашборде ✓
- Files: `resources/js/Pages/TourCabinet/Dashboard.vue`
- Verify: `npm run build` (Docker) — успех; новая `<section id="tour-cabinet-standard-form">` помещена непосредственно перед `#tour-cabinet-contest`; `v-if="dashboardStandardForm"` скрывает блок при пустой настройке.

### Task 7: Контрастная плашка-уведомление под блоком ✓
- Files: `resources/js/Pages/TourCabinet/Dashboard.vue` (внутри новой секции)
- Verify: плашка `bg-amber-100/80` + `border-amber-300` + `text-amber-900`; скрывается при `submitted === true`; визуально выделена против общего фона дашборда.

### Task 8: Финализация — spec/progress ✓
- Files: `spec/features/standart-anketa/spec.md`, `spec/features/standart-anketa/progress.md`, `spec/features/tour-cabinet/spec.md`
- Verify: spec-файлы отражают финальное состояние; Open questions сведены к двум потенциальным улучшениям (формулировка текста / выбор иконки).

## Partially completed

(пусто)

## Not started

(пусто) — фича реализована полностью.

## Verification summary

- PHP-lint: чисто.
- Frontend build (Docker, `npm run build`): успешно (две сборки на Tasks 5 и 6/7).
- `php artisan route:list` (Docker): новый маршрут `PUT admin/tour-cabinet/dashboard-form admin.tour-cabinet.dashboard-form.update` присутствует.
- Tinker (Docker): `getTourCabinetDashboardStandardFormSlug()` возвращает `null` без настройки — корректное поведение «блок скрыт».

## Open issues

(пусто)
