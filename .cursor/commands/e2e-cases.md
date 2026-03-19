You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
scope: <feature-slug | module-name | "global">
(optional) focus: <конкретная область — e.g. "авторизация", "CRUD курсов", "геймификация">
(optional) role: <роль пользователя — e.g. "участник", "админ", "лидер", "гость">

Goal:
Создать человекочитаемые E2E тесткейсы — текстовое описание сценариев без кода.
Результат сохраняется в /spec/e2e/<scope>.md.

Rules:
- Тесткейсы пишутся СЛОВАМИ, не кодом.
- Каждый кейс описывает: что делает пользователь и что он должен увидеть.
- Язык кейсов — русский.
- DO NOT generate test code (no Playwright, no Cypress, no PHP).
- DO NOT modify any code files.
- DO NOT guess behavior — if unclear, check code, then spec, then record gap.

Phase 1 — Сбор контекста

If scope = feature slug:
- Read /spec/features/<slug>/spec.md, plan.md.
- Read related routes from /spec/05-flows.md.
- Read Vue pages mentioned in spec (skim for user-visible behavior: forms, buttons, lists, modals, toasts, redirects).
- Read controllers (skim for validation rules, redirects, error responses).

If scope = module name (from /spec/02-modules.md):
- Read module section from /spec/02-modules.md.
- Read matching routes from /spec/05-flows.md.
- Skim key pages and controllers.

If scope = "global":
- Read /spec/02-modules.md and /spec/05-flows.md.
- Generate кейсы по каждому модулю (краткие, 3-5 кейсов на модуль).

If role provided — фильтровать кейсы только для этой роли.
If focus provided — сузить область до указанной.

Phase 2 — Генерация тесткейсов

Для каждого функционального блока создать тесткейсы в формате:

```
## <Название блока>

### TC-<NNN>: <Краткое название>
**Роль:** <гость | участник | лидер | админ>
**Предусловия:** <что должно быть настроено перед тестом>

**Шаги:**
1. <Действие пользователя — что кликает, куда переходит, что вводит>
2. <Следующее действие>
3. ...

**Ожидаемый результат:**
- <Что видит пользователь>
- <Куда перенаправлен>
- <Что изменилось в данных / на странице>

---
```

Правила генерации:
- Нумерация сквозная: TC-001, TC-002, ...
- Покрывать: happy path, валидация (пустые/невалидные данные), граничные случаи, права доступа.
- Для CRUD: create, read (список + детали), update, delete.
- Для форм: обязательные поля, ошибки валидации, успешная отправка.
- Для списков: пустой список, пагинация (если есть), фильтрация/поиск (если есть).
- Для авторизации: доступ без логина, доступ с неверной ролью.
- Шаги должны быть конкретными: "Нажать кнопку «Создать курс»", а не "Создать курс".
- Ожидаемые результаты должны быть наблюдаемыми: "Появляется toast «Курс создан»", а не "Курс создаётся".

Phase 3 — Сохранение

- Ensure /spec/e2e/ directory exists.
- If /spec/e2e/<scope>.md does NOT exist — write new file with all generated cases.
- If /spec/e2e/<scope>.md already exists — merge:
  1. Read existing file. Parse all `### TC-<NNN>: <Title>` headings.
  2. For each generated case — check duplicates by TITLE match:
     - Exact title match (case-insensitive) -> SKIP (already exists).
     - No match -> this is a NEW case.
  3. Find max existing TC number (e.g. TC-042 -> max=42).
  4. Assign new cases sequential numbers starting from max+1.
  5. Append new cases at the end of matching `## <Block>` section (or create new section).
  6. NEVER modify or renumber existing cases.
  7. Report in output: which cases were skipped as duplicates, which are new.

Phase 4 — Краткая статистика

Output ONLY:
Scope: <what was covered>
Role filter: <role | all>
Focus: <focus | all>
File: /spec/e2e/<scope>.md
Total cases: <count>
By category:
- Happy path: <count>
- Validation: <count>
- Access control: <count>
- Edge cases: <count>
New cases added: <count> (if merge)
Gaps: <if any behavior unclear>
