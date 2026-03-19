You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
slug: <feature-slug>
issue: <expected/actual + steps>
(optional) mode: normal|fast (default: normal)
(optional) max_steps: 3 (applies only in fast mode)

Read ONLY:
- /spec/features/<slug>/progress.md
- /spec/features/<slug>/tasks.md
- /spec/features/<slug>/spec.md
- /spec/90-open-questions.md

If issue unclear and cannot be verified from repo -> record 1 question in /spec/90-open-questions.md and STOP.

Record bug:
- Append to /spec/features/<slug>/spec.md section "## Bugfixes" (short):
  - Issue
  - Expected
  - Actual
  - Repro (bullets)
  - Acceptance (1-2 bullets)

Add tasks:
- Append 1–3 BUG tasks to tasks.md (each max 3 bullets).
- Add them to progress.md Not started (top, keep order of existing tasks if already mid-flight).
- If fix touches logic with existing tests -> include "update/add test" as a task bullet.

Pick Active:
- If Partial has a task -> keep it.
- Else move first Not started -> Partial and init Left with 3–7 steps (short).

Execute:
- normal: do exactly 1 Left step.
- fast: do up to max_steps Left steps.
Rules:
- Minimal diff.
- Verify fix in Docker using pattern from spec-continuation (no examples).
- After code changes: run linter on changed files; if errors introduced -> fix before proceeding.
- Run existing tests for affected modules in Docker; if failures introduced -> fix before proceeding.
- If bug is logic-related -> add or update a test case that covers the bug scenario (regression test).
- Update progress.md after each step (move Left->Done, list files touched).

When task completes (all Left steps done):
- Move task from Partial -> Completed in progress.md.

If uncertainty -> record in /spec/90-open-questions.md + add Open issue in progress.md, then STOP.

Output ONLY:
Bug: <BUG-ID>
Active: <TASK-ID>
Done: <1..N steps>
Files: <list>
Lint: clean | <count> fixed
Tests: pass | skip | <failures>
Regression test: added | updated | n/a
Next: <one short line>
Questions: <if any>
