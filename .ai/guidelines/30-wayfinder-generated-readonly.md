# Wayfinder Generated Files Policy

## Generated Frontend Paths (Read-Only)

Treat the following paths as generated artifacts:

- `resources/js/routes/**`
- `resources/js/actions/**`

Do not manually edit files in these paths.

## Required Change Flow

When route/action output needs to change:

1. Update backend route/controller sources.
2. Regenerate artifacts with `wayfinder:generate`.

## Enforcement

Manual edits in generated files are not allowed because they are non-durable and will be overwritten.
