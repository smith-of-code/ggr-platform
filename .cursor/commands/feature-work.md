You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
slug: <feature-slug>
(optional) mode: normal|fast (default: normal)
(optional) max_steps: 99 (applies only in fast mode)

Read ONLY:
- /spec/features/<slug>/progress.md
- /spec/features/<slug>/tasks.md
- /spec/features/<slug>/spec.md
- /spec/features/<slug>/plan.md
- /spec/90-open-questions.md

Gate:
- If any missing -> record gap in /spec/90-open-questions.md and STOP.
- If there is a blocking question for this slug and cannot be verified from repo -> STOP.
- If all tasks in Completed and none in Not started -> output "Feature complete. Next: /feature-close slug=<slug>" and STOP.

Pick Active:
- If Partial has a task -> keep it.
- Else move first Not started -> Partial and init Left with 3–7 steps (short).

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
- Pick next Not started task automatically (do NOT execute it, just report).

If uncertainty -> record in /spec/90-open-questions.md + add Open issue in progress.md, then STOP.

Output ONLY:
Active: <TASK-ID>
Done: <1..N steps>
Files: <list>
Lint: clean | <count> fixed
Tests: pass | skip | <failures>
Next: <one short line>
Questions: <if any>
