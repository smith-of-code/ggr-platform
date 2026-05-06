# Plan: tour-cabinet-profile-required-gate

## Milestones

1. **M1. Сервис `TourCabinetProfileCompleteness`** — единая точка истины «полный профиль / не полный»; покрывает поля `last_name`, `first_name`, `gender`, `birth_date`, `phone`, `email` и наличие документа `personal_data_consent` (статус ∈ `{pending_review, approved}`). Юнит-проверка через `php artisan tinker`.
2. **M2. Новый тип документа `personal_data_consent`** — расширение `TourCabinetDocument::allowedTypes()` + `typeLabel()` + правил `uploadProfileDocument` без миграций. Запись `tour_cabinet_documents` со статусом `pending_review` после загрузки.
3. **M3. Серверный гард — middleware `tour-cabinet.profile-complete`** — навешивается на маршруты участия (контест, коммерческие туры, стандартная анкета, прочие mutating endpoints `/tour-cabinet/*` кроме профиля, документов, поддержки, выхода). Возвращает `back()->withErrors(['profile' => ...])`.
4. **M4. Inertia-проп `profileGate` + плашка на дашборде** — `TourCabinetController::dashboard` отдаёт `profileGate = { complete, missing, message }`; `Dashboard.vue` рендерит контрастную плашку над профилем.
5. **M5. Блокировка секций дашборда** — `Dashboard.vue` скрывает / накладывает `pointer-events-none + opacity-50` на секции `#tour-cabinet-favorites`, `#tour-cabinet-standard-form`, `#tour-cabinet-atomic-ticket`, `#tour-cabinet-contest`, `#tour-cabinet-commerce-tours`; «Поддержка» и «Профиль» / «Документы» остаются активны.
6. **M6. UI согласия на ОПД** — в `#tour-cabinet-documents` отдельный визуальный блок «Согласие на обработку персональных данных» с пометкой «обязательный документ». Реюз существующего паттерна `docConfig` / `uploadDoc`.
7. **M7. Документация и тесты** — обновить `spec/features/tour-cabinet/spec.md` (раздел «Документы»), `spec.md` фичи (раздел «Реализация (как сделано)»), пройти `npm run build` и `ReadLints` по затронутым PHP-/Vue-файлам.

## UI Components

- **Используем существующие**:
  - `RButton`, `RBadge` (`@/Components/UI/...`).
  - `RInput` для редактирования профиля.
  - `IdentificationIcon`, `ExclamationTriangleIcon`, `CheckCircleIcon`, `ArrowUpTrayIcon` из `@heroicons/vue/24/outline`.
  - Стилевой паттерн контрастной плашки — клонируем из `#tour-cabinet-standard-form` (`bg-amber-100/80` + `border-amber-300` + `text-amber-900`), усиливаем `border-2` и иконкой `ExclamationTriangleIcon`.
  - `docConfig` / `uploadedDoc` / `uploadDoc` / `deleteDoc` — существующая логика загрузки документов в `Dashboard.vue`. Расширяем `docConfig` записью для `personal_data_consent`, отдельный заголовок «Обязательный документ».
- **Новых композаблов / shared-компонентов не вводим.** Логика гейта целиком на стороне `Dashboard.vue` (computed `profileGateActive`) + сервер.

## Verification

- Пайплайн команд — по правилу «Command Execution Pattern» из `spec-continuation` (Docker).
- Покрытие:
  - `php artisan tinker` — таблица кейсов `TourCabinetProfileCompleteness::isComplete()` (полный/частичный/без согласия / `annulled`-согласие).
  - `php artisan route:list --name=tour-cabinet` — убедиться, что `tour-cabinet.profile-complete` middleware навешан на нужные маршруты.
  - `php artisan tinker` — end-to-end: `TourCabinetController::dashboard` отдаёт `profileGate` с правильными `complete` / `missing`.
  - `npm run build` — сборка без ошибок.
  - `ReadLints` по затронутым PHP-/Vue-файлам — чисто.
  - Smoke (опционально, если поднят dev): открыть `/tour-cabinet` пустым профилем, убедиться в плашке и блокировке секций; заполнить профиль + загрузить согласие — секции разблокировались.
