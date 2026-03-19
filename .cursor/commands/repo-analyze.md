You MUST follow .cursor/rules/spec-continuation.mdc (alwaysApply).

Input:
questions: <список вопросов>
(optional) scope: <feature-slug | module | file path>

Goal:
Study repository structure and answer questions using:
1) Current code (priority)
2) /spec/*
3) Historical markdown (context only)

Rules:
- DO NOT modify any files (exception: /spec/90-open-questions.md for gaps).
- DO NOT create tasks.
- DO NOT implement code.
- DO NOT guess.
- Answers must be concise and structured.

Phase 1 — Targeted search
1) Check if /spec exists.
2) If scope provided -> narrow search to that scope.
3) Search strategy (in order, stop when answer found):
   a) /spec files matching question keywords (glob + read).
   b) Grep for relevant class/function/route/component names in code.
   c) File structure scan (glob) for relevant modules/directories.
   d) Read only files identified in steps a-c (no full codebase scan).
4) Use priority: code > /spec > historical markdown.

Phase 2 — Validation
If contradictions found:
- Code wins.
- Mention discrepancy briefly with file paths.

If question cannot be answered deterministically from repo:
- Record gap in /spec/90-open-questions.md.
- Mark answer as "UNRESOLVED" and continue with remaining questions.

Phase 3 — Cross-reference
For each answer, include:
- Source: <file path(s) that confirm the answer>
- Confidence: high (code confirms) | medium (spec only) | low (inferred)

Output ONLY:
Answers:
- Q1: <short answer> [source: <path>, confidence: <level>]
- Q2: <short answer> [source: <path>, confidence: <level>]
- ...

Conflicts:
- <file vs file, if any>

Gaps recorded:
- <if any>
