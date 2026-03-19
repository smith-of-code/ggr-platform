You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
scope: <feature-slug | "global">
(optional) dry_run: true|false (default: false)

Goal:
Audit spec files against actual code and bring spec in sync (code wins per spec-continuation rule).
If /spec directory does not exist — bootstrap it from the codebase first.

Rules:
- Code is the source of truth. Spec adapts to code, NEVER the other way around.
- DO NOT change any code files.
- Only modify /spec files.
- DO NOT guess intent — if code behavior is ambiguous, record gap and skip that item.

Phase 0 — Bootstrap (only if /spec directory does NOT exist)

Check if /spec/ directory exists. If it does — skip to Phase 1.

Step 0.1 — Scan codebase structure
- routes/*.php — list all route files, extract route groups, prefixes, middleware.
- app/Models/*.php — list all models, their fillable/casts/relationships.
- app/Http/Controllers/**/*.php — list all controllers and public methods.
- app/Services/*.php, app/Helpers/*.php — list service/helper classes.
- resources/js/Pages/**/*.vue — list all pages, group by directory (module).
- resources/js/Components/**/*.vue — list shared/ui components.
- database/migrations/*.php — list migrations, extract table names and columns.
- app/Enums/*.php (if exists) — list enums and values.
- config/*.php — note non-default config files.

Step 0.2 — Create /spec directory structure
Create these files:

/spec/01-architecture.md
- Stack: PHP version, Laravel version (from composer.json), Vue version (from package.json), Inertia, DB engine, queue driver, etc.
- Directory layout: brief map of app/, resources/js/, database/, routes/.
- Auth approach (from auth config / middleware).
- Key packages (from composer.json require + package.json dependencies).

/spec/02-modules.md
- List each logical module (derived from route groups + controller namespaces + Pages directories).
- For each module: short purpose, controller(s), route prefix, page directory.

/spec/03-data-model.md
- For each Model: table name, key fields (from migration), relationships (from model), casts, enums used.
- Use actual migration columns — do NOT guess.

/spec/05-flows.md
- For each major route group: list endpoints (method + URI + controller@action + middleware).
- Group by module from 02-modules.

/spec/90-open-questions.md
- Empty template with header: `# Open questions`
- Add any ambiguities found during scan.

Step 0.3 — Detect features
- Scan for distinct functional areas that go beyond CRUD (e.g. custom workflows, multi-step processes, integrations).
- For each detected feature, create /spec/features/<slug>/ with a minimal spec.md containing:
  - Feature name.
  - Related models, controllers, pages (paths only).
  - Status: "auto-detected, needs review".
- Do NOT create plan.md / tasks.md / progress.md — those are created by feature commands.

Step 0.4 — Bootstrap report
Output a summary of what was created, then proceed to Phase 1 with scope="global".

Phase 1 — Collect scope

If scope = feature slug:
- Read /spec/features/<slug>/spec.md, plan.md, tasks.md, progress.md.
- Identify paths mentioned in spec (routes, controllers, models, components, migrations).
- Read those code files.

If scope = "global":
- Read /spec/01-architecture.md, /spec/02-modules.md, /spec/03-data-model.md, /spec/05-flows.md.
- Scan code structure: routes/*.php, app/Models/*.php, app/Http/Controllers/**/*.php, resources/js/Pages/**/*.vue.
- Compare declared modules/models/routes/pages with actual.

Phase 2 — Diff

For each spec claim, check against code:
- Route exists / method / middleware matches.
- Model fields / relationships / casts match migration + model file.
- Controller methods match declared API.
- Vue pages/components exist at declared paths.
- Status/enum values match code constants.

Categorize findings:
- MATCH: spec == code (no action).
- DRIFT: spec says X, code says Y (update spec).
- STALE: spec references something removed from code (remove from spec).
- MISSING: code has something not in spec (add to spec).

Phase 3 — Apply (skip if dry_run=true)

- Update spec files to match code for all DRIFT and STALE items.
- Add MISSING items to appropriate spec sections.
- If a finding is ambiguous -> record in /spec/90-open-questions.md, do NOT update.

Phase 4 — Report

Output ONLY:
Bootstrapped: yes|no
Scope: <what was checked>
Files scanned: <count code files> code, <count spec files> spec
Findings:
- MATCH: <count>
- DRIFT: <count> — <short list>
- STALE: <count> — <short list>
- MISSING: <count> — <short list>
Spec files updated: <list> (or "dry run, no changes")
Gaps recorded: <if any>
