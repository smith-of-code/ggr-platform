You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
target: <file path | directory | class name | module>
goal: <what to improve — e.g. "extract service", "reduce duplication", "apply SRP">
(optional) max_files: 5

Rules:
- Zero behavior change. Refactoring MUST NOT alter external behavior.
- Minimal diff — touch only what is necessary for the stated goal.
- Follow SOLID / KISS principles.
- If target involves UI -> perform UI Kit Lookup per ui-kit-gate rule before changes.
- DO NOT add new features or fix bugs during refactoring.

Phase 1 — Analyze

1) Read target file(s) and their direct dependents (imports, usages).
2) Identify the smell / issue to address (name it: Long Method, God Class, Feature Envy, Duplication, etc.).
3) If target unclear or too broad (> max_files affected) -> record gap in /spec/90-open-questions.md and STOP.

Phase 2 — Baseline tests

1) Identify existing tests covering the target:
   - PHP: tests/Feature/*, tests/Unit/* (grep for class/method references).
   - JS: resources/js/**/__tests__/*.spec.js (grep for component/composable references).
2) Run baseline tests in Docker using pattern from spec-continuation.
3) Record result: <N> tests, <pass/fail>.
4) If no tests exist for the target -> warn in output, proceed with extra caution.

Phase 3 — Plan

List planned changes as a numbered checklist (max 7 items):
- What moves where.
- What gets renamed.
- What gets extracted.
- What gets deleted.

DO NOT execute yet. Present plan in progress output (visible to agent only).

Phase 4 — Execute

Apply changes one at a time:
- After each change: run linter on changed files, fix if errors introduced.
- After all changes: re-run baseline tests in Docker.
- If any test fails -> revert last change, report failure, STOP.

Phase 5 — Spec update

- If refactoring changed file paths, class names, or interfaces documented in /spec -> update spec.
- If refactoring is within a feature scope -> update /spec/features/<slug>/spec.md if paths changed.

Output ONLY:
Target: <what was refactored>
Smell: <identified issue>
Changes: <file list with action: moved | renamed | extracted | deleted | modified>
Tests: baseline <N> pass -> after <N> pass | <failures>
Lint: clean | <count> fixed
Spec updated: <list of spec files | "no spec impact">
