# Chopper Directives Design

## Problem

Teaching Chopper new behavioral rules (e.g., "always show protein targets when logging meals") requires code changes to the system prompt. We need a way to add persistent instructions at runtime via conversation.

## Solution

A `chopper_directives` table that stores behavioral rules as plain text. Active directives are loaded at boot and appended to Chopper's system prompt. Chopper manages directives himself via DatabaseTool — no new tools, no Eloquent model.

## Table: `chopper_directives`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | primary key |
| content | text | The directive text |
| deactivated_at | datetime, nullable | NULL = active, set = soft-deactivated |
| timestamps | | |

## Prompt Integration

In `ChopperAgent::instructions()`, load active directives with `DB::select('SELECT content FROM chopper_directives WHERE deactivated_at IS NULL')` and append as a dedicated section:

```
## Deine gelernten Regeln

Die folgenden Regeln hast du dir gemerkt. Befolge sie immer:
- Zeige immer Protein-Ziele beim Loggen von Mahlzeiten
- Wenn ich "Einwaage" sage, logge mein Gewicht
```

If no active directives exist, the section is omitted entirely (no empty header).

## System Prompt Instructions

Add to Chopper's rules section:

```
- Wenn der Benutzer dir sagt, dass du dir etwas merken sollst, speichere es als Direktive
  in der Tabelle `chopper_directives` (INSERT via DatabaseTool).
- Zum Deaktivieren einer Direktive: UPDATE chopper_directives SET deactivated_at = datetime('now') WHERE id = ?
- Zum Auflisten deiner Regeln: SELECT * FROM chopper_directives WHERE deactivated_at IS NULL
```

## Management Flow

**Teaching:** User says "Merke dir: zeige immer Protein-Ziele beim Loggen" → Chopper INSERTs into `chopper_directives` via DatabaseTool.

**Listing:** User says "Was hast du dir gemerkt?" → Chopper SELECTs active directives via DatabaseTool.

**Deactivating:** User says "Vergiss die Regel mit den Protein-Zielen" → Chopper finds the directive by content/ID, then UPDATEs `deactivated_at` via DatabaseTool.

**Reactivating:** User says "Aktiviere die Regel wieder" → Chopper UPDATEs `deactivated_at = NULL` via DatabaseTool.

## Design Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Storage | Dedicated table | Simple, queryable, no JSON manipulation |
| Deletion | Soft-delete via `deactivated_at` | Directives are never lost, can be reactivated |
| Management | Via existing DatabaseTool | No new tools needed, aligns with consolidation |
| Model | None | Raw DB::select in prompt loader. YAGNI. |
| Content format | Plain text | LLM writes natural language rules. No structure needed. |
