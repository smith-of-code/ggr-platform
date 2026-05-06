# Tasks: tour-cabinet-profile-required-gate

Задачи последовательны: следующая стартует только после полного завершения предыдущей (см. `progress.md`). Verify шаги — по «Command Execution Pattern» из `spec-continuation` (Docker, без host-команд).

## 1. Сервис `TourCabinetProfileCompleteness`

- **Goal**: единая SSoT для проверки полноты профиля + согласия на ОПД.
- **Scope (paths)**: `app/Services/TourCabinetProfileCompleteness.php` (новый).
- **DoD**: метод `isComplete(User $user): bool` возвращает `true` ⇔ заполнены `last_name`, `first_name`, `gender`, `birth_date`, `phone`, `email` И существует `tour_cabinet_documents` `(user_id, type=personal_data_consent)` со `status ∈ {pending_review, approved}` И непустым `file_path`. Метод `missingFields(User $user): array` возвращает массив ключей пустых полей (`last_name`/.../`personal_data_consent`).
- **Verify**: `php artisan tinker --execute=...` — таблица из 5 кейсов (профиль пустой, профиль полный без согласия, профиль полный + согласие `pending_review`, профиль полный + согласие `approved`, профиль полный + согласие `annulled`); `ReadLints` по сервису — чисто.

## 2. Расширить `TourCabinetDocument` типом `personal_data_consent`

- **Goal**: добавить новый тип документа без миграции (значение колонки `type` — строка с whitelist в модели).
- **Scope (paths)**: `app/Models/TourCabinetDocument.php`, `app/Http/Controllers/TourCabinetController.php` (метод `uploadProfileDocument` — extend whitelist в валидации).
- **DoD**: `TourCabinetDocument::TYPE_PERSONAL_DATA_CONSENT = 'personal_data_consent'`; константа в `allowedTypes()`; `typeLabel('personal_data_consent') === 'Согласие на обработку персональных данных'`; `uploadProfileDocument` принимает новый тип (`Rule::in(TourCabinetDocument::allowedTypes())`).
- **Verify**: `php artisan tinker` — `TourCabinetDocument::allowedTypes()` содержит новый тип, `typeLabel` возвращает корректную метку; `ReadLints` по обоим файлам — чисто.

## 3. Inertia-проп `profileGate` в дашборде

- **Goal**: пробросить статус полноты профиля на фронт.
- **Scope (paths)**: `app/Http/Controllers/TourCabinetController.php` (метод `dashboard`).
- **DoD**: в Inertia-render добавлен ключ `profileGate` = `{ complete: bool, missing: string[], message: string }`. Используется `TourCabinetProfileCompleteness` из задачи 1. `message` — статичная строка «Сначала заполните профиль и загрузите согласие на обработку персональных данных, чтобы получить доступ к остальным разделам.» (или эквивалент).
- **Verify**: `php artisan tinker --execute=...` — для пользователя с пустым профилем `profileGate.complete === false` и `missing` содержит `last_name`, `first_name`, `gender`, `birth_date`, `phone`, `personal_data_consent`; для полного профиля + согласия — `complete === true`.

## 4. Middleware `tour-cabinet.profile-complete` + навешивание на маршруты

- **Goal**: серверная блокировка mutating-эндпоинтов участия при незаполненном профиле.
- **Scope (paths)**: `app/Http/Middleware/EnsureTourCabinetProfileComplete.php` (новый), `bootstrap/app.php` (регистрация alias `tour-cabinet.profile-complete`), `routes/web.php` (навесить middleware на участвующие маршруты — конкурс кроме `show`, коммерческие туры, стандартная анкета, прочие mutating; **исключить** `tour-cabinet.profile.update`, `tour-cabinet.profile.documents.*`, `tour-cabinet.support.*`, `tour-cabinet.logout`).
- **DoD**: при `! TourCabinetProfileCompleteness::isComplete($user)` middleware возвращает `back()->withErrors(['profile' => '...'])->withFragment('tour-cabinet-profile')`. На GET-маршрутах (например, `tour-cabinet.contest.city-form`) — `redirect()->route('tour-cabinet.dashboard')->withErrors(...)`.
- **Verify**: `php artisan route:list --name=tour-cabinet` (Docker) — middleware `tour-cabinet.profile-complete` присутствует на нужных маршрутах и отсутствует на исключённых.

## 5. Плашка-уведомление на дашборде

- **Goal**: контрастный заметный блок над профилем «Сначала заполните профиль».
- **Scope (paths)**: `resources/js/Pages/TourCabinet/Dashboard.vue` (template + props).
- **DoD**: добавлен дефолт пропа `profileGate: { complete: true, missing: [], message: '' }` (безопасный fallback). Computed `profileGateActive = !profileGate.complete`. Над секцией `#tour-cabinet-profile` рендерится плашка `bg-amber-100/80 border-2 border-amber-300 text-amber-900` с `ExclamationTriangleIcon`, текстом `profileGate.message` и подсветкой списка `missing` (через словарь meta-меток на фронте, без зависимости от бэкенда). Плашка закрывает / скрывает себя при `profileGate.complete === true`.
- **Verify**: `npm run build` (Docker) — без ошибок; `ReadLints` по `Dashboard.vue` — чисто.

## 6. Блокировка секций дашборда

- **Goal**: при незаполненном профиле UI этапов недоступен.
- **Scope (paths)**: `resources/js/Pages/TourCabinet/Dashboard.vue` (template).
- **DoD**: секции `#tour-cabinet-favorites`, `#tour-cabinet-standard-form`, `#tour-cabinet-atomic-ticket`, `#tour-cabinet-contest`, `#tour-cabinet-commerce-tours` оборачиваются обёрткой с `:class="profileGateActive ? 'pointer-events-none select-none opacity-50 grayscale' : ''"` + `aria-hidden="profileGateActive"` (или равноценное `v-show="!profileGateActive"`). Блок «Поддержка» (если виден на дашборде) и `#tour-cabinet-profile` / `#tour-cabinet-documents` не блокируются. Под каждой заблокированной секцией — мини-плашка-якорь `Сначала заполните профиль ↑` со ссылкой `#tour-cabinet-profile` (опционально).
- **Verify**: `npm run build` (Docker) — без ошибок; smoke-тест по странице (если возможно): для пустого профиля разделы визуально неактивны и не реагируют на клики.

## 7. Блок согласия на ОПД в `#tour-cabinet-documents`

- **Goal**: участник может загрузить согласие на обработку персональных данных как обычный документ.
- **Scope (paths)**: `resources/js/Pages/TourCabinet/Dashboard.vue` (`docConfig` массив + рендер блока «Обязательный документ»).
- **DoD**: запись `{ type: 'personal_data_consent', label: 'Согласие на обработку персональных данных', required: true }` добавлена первой в `docConfig`. Визуально этот пункт выделен (`border-amber-300 bg-amber-50` либо аналогичный акцент) и подписан «обязательно для участия». Кнопка «Загрузить» постится на `tour-cabinet.profile.documents.upload` с `type=personal_data_consent` (существующий эндпоинт).
- **Verify**: `npm run build` (Docker) — без ошибок; ручная проверка: загрузка PDF приводит к появлению согласия в списке `profileDocuments`, плашка-уведомление исчезает после следующего рендера дашборда (`profileGate.complete === true`).

## 8. Обновить общий спек `spec/features/tour-cabinet/spec.md`

- **Goal**: задокументировать новый тип документа и поведение гейта в спеке родительской фичи.
- **Scope (paths)**: `spec/features/tour-cabinet/spec.md` (раздел «Документы участника и модерация», раздел «Модель пользователя» / «Защита дашборда»).
- **DoD**: в `TourCabinetDocument::allowedTypes()` упомянут `personal_data_consent` с человеческой меткой; в раздел «Защита дашборда» добавлен абзац про middleware `tour-cabinet.profile-complete` и про `profileGate`; добавлена ссылка на текущую фичу.
- **Verify**: `git diff spec/features/tour-cabinet/spec.md` — точечные правки без переписывания истории.

## 9. Финальный verify + «Реализация (как сделано)»

- **Goal**: закрыть фичу, заполнить раздел «Реализация (как сделано)» в `spec.md`, занести задачи в «Выполнено» в `progress.md`.
- **Scope (paths)**: `spec/features/tour-cabinet-profile-required-gate/spec.md`, `progress.md`.
- **DoD**: 1) `php artisan tinker` end-to-end для пустого / полного профиля — `profileGate` корректен; 2) `npm run build` (Docker) — успешно; 3) `ReadLints` по всем затронутым файлам — чисто; 4) `progress.md` — все задачи 1–8 в «Выполнено», открытые вопросы синхронизированы со `spec.md`.
- **Verify**: повторный прогон `npm run build` + `ReadLints`, ручная проверка дашборда (если поднят dev): пустой профиль → плашка + блокировки; полный профиль → секции разблокированы.
