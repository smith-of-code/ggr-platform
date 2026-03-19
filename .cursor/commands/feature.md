You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
slug: <feature-slug>
intent: <1-2 sentences>
(optional) constraints:
(optional) out_of_scope:

Do:
1) Ensure /spec/features/<slug>/ exists.
2) Ensure files exist:
- /spec/features/<slug>/spec.md
- /spec/features/<slug>/plan.md
- /spec/features/<slug>/tasks.md
- /spec/features/<slug>/progress.md
Create if missing (minimal headers only).

3) Read ONLY:
- /spec/features/<slug>/spec.md (if exists)
- /spec/features/<slug>/progress.md (if exists)
- /spec/90-open-questions.md (if exists)
- minimal code lookup only for confirming interfaces/paths (no deep scan)

4) Safety check:
- If progress.md has ANY Completed or Partial tasks -> this is an existing feature.
  - Do NOT reset progress.md (skip step 8).
  - Merge new scope into existing spec.md/plan.md/tasks.md (append, do not overwrite completed work).
  - Warn in output: "Existing progress preserved. New tasks appended."
- If progress.md is empty or all tasks are Not started -> treat as fresh setup.

5) Update ONLY /spec/features/<slug>/spec.md with:
- Goal (1 line)
- In-scope (3-7 bullets)
- Out-of-scope (0-5 bullets)
- Constraints (0-5 bullets)
- Open questions (only if truly unverifiable)

If unverifiable gap -> append to /spec/90-open-questions.md and STOP.

6) If feature involves UI changes (pages, components, modals, forms):
- Perform UI Kit Lookup per ui-kit-gate rule.
- Record component decisions in plan.md under "## UI Components".

7) Update ONLY /spec/features/<slug>/plan.md with:
- Milestones (3-7 bullets)
- UI Components (if step 6 applied)
- Verification (reference "Command Execution Pattern" from spec-continuation; no examples)

8) Generate 7–10 sequential tasks in /spec/features/<slug>/tasks.md.
Each task max 4 bullets:
- Goal
- Scope (paths)
- DoD
- Verify (reference pattern; no examples)

9) Initialize /spec/features/<slug>/progress.md (only if fresh per step 4):
- Completed: empty
- Partial: empty
- Not started: list all tasks top-down
- Open issues: empty

10) Conflict scan:
- Check /spec/features/*/progress.md for other features with Partial tasks.
- If any touch overlapping paths -> warn in output.

Output ONLY:
Mode: fresh | update
Changed files: <list>
Tasks: <count> (new: <N>, existing: <N>)
Overlaps: <feature slugs if any>
Next: /feature-work slug=<slug>
