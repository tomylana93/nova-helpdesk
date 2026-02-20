# Serena-First Core Rules

## Scope

These rules apply to all repository tasks.

## Default Tooling Policy

Use Serena MCP tools as the default path for code exploration and code edits.
Only use non-Serena tools when Serena is unavailable or cannot perform the required operation.

## Mandatory Startup Flow

At the beginning of a task:

1. Run `activate_project`.
2. Run `check_onboarding_performed`.
3. If onboarding is not performed, run onboarding before continuing.

## Failure and Fallback Policy

If Serena MCP fails:

1. Retry one to two times.
2. If it still fails, continue with safe fallback tools.
3. Explicitly report the failure reason and expected impact in the final update.

## Enforcement

Do not skip Serena-first flow for convenience.
Any exception must be justified in the task report.
