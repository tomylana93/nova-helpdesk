# Serena MCP

The Serena MCP server provides semantic code tools (symbol search, references, precise editing) and a per-project instruction manual.

## Session Start — Mandatory First Actions

**Before any Bash, Read, grep, or file operation**, call these two in order:

1. `activate_project` — activate the project in Serena.
2. `initial_instructions` — read the project instruction manual.

Any tool call that is not `activate_project` before these two completes is a rule violation, even if the task looks trivial. There are no exceptions.

## Code Navigation — Serena First, grep Never

Use Serena's semantic tools for all code exploration and refactoring. Do **not** use `grep`, `Bash cat`, or `Read` on large files when a symbolic tool covers the same need.

| Need | Use |
|------|-----|
| Understand a class/file structure | `get_symbols_overview` or `find_symbol` with `depth` |
| Find all callers of a function | `find_referencing_symbols` |
| Find a specific symbol | `find_symbol` |
| Search by pattern | `search_for_pattern` |
| Edit a method body | `replace_symbol_body` |
| Insert code near a symbol | `insert_before_symbol` / `insert_after_symbol` |

**Concrete examples:**

```
❌ grep -rn "AppearanceTabs" resources/js
✅ mcp__serena__find_referencing_symbols("AppearanceTabs")

❌ cat -n FrontendLocaleExporter.php
✅ mcp__serena__find_symbol("FrontendLocaleExporter", depth=2)

❌ grep -rEln "lang:export|LangExport" app
✅ mcp__serena__search_for_pattern("lang:export", path="app")

❌ Read full file to find one method
✅ mcp__serena__find_symbol("methodName") → read only the body
```

**Exceptions where full `Read` is acceptable:**
- File is ≤ 100 lines.
- File is non-code (config, docs, `.env.example`).
- You genuinely need the entire file (e.g. reading a test to understand full flow).
- Never re-read a file already read in full in the same session.

## Fallback (Serena MCP not connected)

Say so explicitly in the evidence block, then fall back to ripgrep/Read — but still read only the symbols you need. Do not slurp whole files or rewrite files wholesale.

> **Model note:** Claude Code (Opus 4.8) and Codex CLI (GPT-5.5) have a `serena-hooks` SessionStart/PreToolUse reminder, but a reminder is **not a guarantee** — prior sessions ran Bash before `activate_project` even with hooks active. Follow these rules from the text, not from the hook. **Antigravity (Gemini 3.5 Flash) has no compatible session-start hook**, so it must comply purely from this text; its sessions warrant the closest developer review.