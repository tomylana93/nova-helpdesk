# Serena Memory Management Rules

## Read Memory Before Work

Before implementation:

1. Run `list_memories`.
2. Read only memories relevant to the current task.

## Write or Update Memory After Work

After implementation:

1. Persist only stable and reusable knowledge.
2. Prefer updating existing memory over creating duplicates.

## Prohibited Behavior

- Do not blindly overwrite memory files.
- Do not create duplicate memory files for the same topic.
- Do not write irrelevant task noise into memory.

## Minimum Quality Bar

Memory entries should be concise, factual, and reusable for future tasks.
