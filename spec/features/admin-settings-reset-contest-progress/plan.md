# План: Сброс прогресса конкурса участника из `/admin/settings/`

## Milestones

- M1. Сервис сброса (`App\Services\Admin\TourCabinetContestProgressResetter`) с атомарной транзакцией и cleanup файла этапа 3.
- M2. Контроллер `App\Http\Controllers\Admin\ContestProgressResetController` (`index`, `reset`) + admin-маршруты `admin.settings.contest-reset.{index,reset}` в `routes/web.php`.
- M3. Inertia-страница `Admin/Settings/ContestReset/Index.vue`: поиск, таблица с пагинацией, модалка-confirm на сброс.
- M4. Карточка-вход в `Admin/Settings/Index.vue` («Сброс прогресса конкурса»).
- M5. Документация: ссылка из `spec/features/lk-participant-contests/spec.md` (или `tour-cabinet/spec.md`) на эту фичу.
- M6. Verify (Docker): tinker-смок сервиса (создать прогресс/сабмишены/ответы → reset → проверить что строки удалены, файл стёрт), `php artisan route:list`, `npm run build`, `ReadLints`.

## UI Components

Используем существующий UI Kit `resources/js/Components/`:

- Layout: `@/Layouts/AdminLayout.vue` (как в `Admin/Settings/FormsTrash.vue`).
- Inputs: `TextInput.vue`, `InputLabel.vue`, `InputError.vue`.
- Buttons: `PrimaryButton.vue` (поиск), `DangerButton.vue` (сброс), `SecondaryButton.vue` (отмена в модалке).
- Modal: `Modal.vue` (паттерн confirm-диалога из других admin-страниц).
- Inertia: `Head`, `Link`, `useForm`, `router`.
- Toasts: композабл `useToast` (`resources/js/composables/useToast.js`) для опционального доп. фидбэка; основной фидбэк — flash через `page.props.flash.{success,error}` (как в `FormsTrash.vue`).
- Таблица/пагинация: пагинация по `users.links` стандартным паттерном (как в `Admin/Settings/FormsTrash.vue`); отдельного компонента-таблицы нет, разметка inline на Tailwind.

## Verification

Командные шаблоны — см. `Command Execution Pattern` в `.cursor/rules/spec-continuation.mdc` (раздел «Docker-only execution»). Конкретные шаги verify фиксируются в каждой задаче `tasks.md` и в `progress.md` после выполнения.
