You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
slug: <feature-slug>
feedback: <правки от заказчика — текст, список замечаний, цитата из письма/чата>
(optional) mode: normal|fast (default: normal)
(optional) max_steps: 5 (applies only in fast mode)

Read ONLY:
- /spec/features/<slug>/spec.md
- /spec/features/<slug>/plan.md
- /spec/features/<slug>/tasks.md
- /spec/features/<slug>/progress.md
- /spec/90-open-questions.md

Gate:
- If feature slug not found -> STOP with message "Feature not found. Run /feature first."
- If spec.md missing -> STOP with message "Spec missing. Run /spec-sync first."

Phase 1 — Анализ правок

1) Parse feedback into discrete items (one item = one замечание / пожелание / правка).
2) For each item, read relevant code files mentioned in spec (controllers, pages, components, models).
3) Classify each item:
   - UI_TWEAK: визуальная правка (текст, стили, расположение, иконки, отступы).
   - BEHAVIOR_CHANGE: изменение логики, валидации, потока, прав доступа.
   - NEW_REQUIREMENT: функционал, которого нет в текущем scope.
   - CLARIFICATION: неясная формулировка, нужно уточнение у заказчика.
   - OUT_OF_SCOPE: выходит за рамки фичи, требует отдельной фичи.
   - ALREADY_DONE: уже реализовано, заказчик мог не заметить.
4) If any item classified as CLARIFICATION -> record in /spec/90-open-questions.md with context.

Phase 2 — Обновление спецификации

For items classified as UI_TWEAK, BEHAVIOR_CHANGE, NEW_REQUIREMENT:
1) Update /spec/features/<slug>/spec.md:
   - Add section "## Правки заказчика (<дата>)" (append, do not overwrite existing).
   - For each actionable item: short description + classification.
   - If item contradicts current spec -> mark spec as updated, note what changed.
2) For NEW_REQUIREMENT items that fit the feature scope:
   - Add to "In-scope" list in spec.md.
   - If too large for current scope -> recommend a separate /feature call in output.

Phase 3 — Генерация задач

1) Create REVIEW tasks in /spec/features/<slug>/tasks.md:
   - Prefix: REV-<NNN> (sequential within feature).
   - Each task max 4 bullets: Goal, Scope (paths), DoD, Verify.
   - Group related small UI tweaks into a single task (max 3 tweaks per task).
   - BEHAVIOR_CHANGE items get individual tasks.
   - NEW_REQUIREMENT items get individual tasks.
2) Add new tasks to /spec/features/<slug>/progress.md under "Not started".
   - Place AFTER any existing Not started tasks (do NOT reorder existing queue).

Phase 4 — Выполнение

Pick Active:
- If Partial has a task -> keep it (do NOT interrupt in-flight work).
- Else move first REV task from Not started -> Partial and init Left with 3–7 steps.

Execute:
- normal: do exactly 1 Left step.
- fast: do up to max_steps Left steps.
Rules:
- Minimal diff.
- If task involves UI changes -> perform UI Kit Lookup per ui-kit-gate rule before coding.
- Verify in Docker using pattern from spec-continuation (no examples).
- After code changes: run linter on changed files; if errors introduced -> fix before proceeding.
- If existing tests cover modified modules -> run them in Docker; if failures introduced -> fix before proceeding.
- Update progress.md after each step (move Left->Done, list files touched).

When task completes (all Left steps done):
- Move task from Partial -> Completed in progress.md.

If uncertainty -> record in /spec/90-open-questions.md + add Open issue in progress.md, then STOP.

Output ONLY:
Feedback items: <count>
Classified:
- UI_TWEAK: <count>
- BEHAVIOR_CHANGE: <count>
- NEW_REQUIREMENT: <count>
- CLARIFICATION: <count>
- OUT_OF_SCOPE: <count>
- ALREADY_DONE: <count>
Tasks created: <count> (REV-<first>..REV-<last>)
Active: <TASK-ID> (or "none — existing task in progress")
Done: <1..N steps>
Files: <list>
Lint: clean | <count> fixed
Tests: pass | skip | <failures>
Next: <one short line>
Questions: <if any — list items needing clarification from client>
