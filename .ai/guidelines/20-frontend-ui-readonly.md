# Frontend UI Component Rules

## Reuse Policy

For frontend work, always check existing components in:

- `resources/js/components/ui`

## Read-Only Policy

The following path is read-only for manual edits:

- `resources/js/components/ui/**`

Do not modify existing UI components in this folder.

## Adding New UI Components

If a new component is needed:

1. Use Shadcn Vue MCP tools to search and inspect available components/examples.
2. Install or add the component through the official Shadcn workflow.
3. Treat installed UI source as read-only unless the user explicitly instructs otherwise.

## Usage Pattern

Compose UI behavior from consumer-level files (pages/layouts/feature components), not by patching UI primitives.
