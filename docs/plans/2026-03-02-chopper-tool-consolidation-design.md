# Chopper Tool Consolidation Design

## Problem

Chopper currently has 17 specialized tools. Every time we want to give him access to new data, we need to build a new tool. Since Chopper primarily manages the database, we can replace most tools with a small number of general-purpose tools and let the LLM write queries itself.

## Architecture

**Before:** 17 specialized tools
**After:** 3 general-purpose tools + schema normalization + Eloquent observers

| Tool | Purpose | Replaces |
|------|---------|----------|
| **DatabaseTool** | Raw SQL (SELECT, INSERT, UPDATE) | 11 database tools |
| **EvalTool** | Allowlisted PHP execution | Scout search, calculations |
| **FilesystemTool** | Knowledge base file operations | 4 notes tools |

### Tools Removed

**Quest domain (7):** SearchQuests, ListQuests, GetQuest, CreateQuest, CompleteQuest, AddNoteToQuest, SearchQuestComments
**Nutrition domain (4):** LogMeal, EditMeal, QueryNutrition
**Body measurements (2):** LogBodyMeasurement, QueryBodyMeasurements
**Conversation (1):** SearchConversationHistory

### Tools Retained (none — all replaced)

## Tool Designs

### 1. DatabaseTool

**Parameters:**
- `query` (string, required) — Raw SQL statement

**Allowed statements:** SELECT, INSERT, UPDATE, SHOW, DESCRIBE, EXPLAIN
**Blocked statements:** DELETE, DROP, ALTER, TRUNCATE, CREATE

**Validation:** Parse the first keyword of the query to determine statement type. Reject disallowed types with a descriptive error.

**Execution:**
- SELECT/SHOW/DESCRIBE/EXPLAIN → `DB::select()` → return JSON array of results
- INSERT → `DB::insert()` → return `true` + last insert ID
- UPDATE → `DB::update()` → return affected row count

**Tool description for LLM:**
> Execute SQL queries against the database. Supports SELECT, INSERT, UPDATE, SHOW, DESCRIBE, EXPLAIN. Returns JSON for reads, affected row count for writes. Schema summary is in your system prompt — use DESCRIBE for full details.

### 2. EvalTool

**Parameters:**
- `code` (string, required) — PHP code without `<?php` tags

**Execution:** Runs in Laravel application context (like `artisan tinker`). Returns output + last `return` value.

**Allowlisted classes/functions:**
- **Models:** Quest, NutritionDay, Meal (new), BodyMeasurement, AgentConversationMessage, User
- **Laravel utilities:** Carbon, Collection, Str, Arr, Http
- **PHP functions:** round, abs, min, max, array_sum, array_map, array_filter, count, json_encode, json_decode
- **Scout:** Model::search() (via model allowlist)

**Blocked (everything else, including):**
- Filesystem: File, Storage, file_get_contents, file_put_contents, unlink, rmdir
- System: exec, shell_exec, system, proc_open, passthru
- Eval: eval, assert
- Process: Process

**Enforcement:** Static analysis / regex scan of code string before execution. Reject with descriptive error if disallowed class or function is detected.

**Tool description for LLM:**
> Execute PHP code in the Laravel app context. Use for Scout semantic searches (e.g., `Quest::search('term')->get()`), complex calculations, or HTTP requests. Only allowlisted classes are available — models, Carbon, Collection, Str, Http, math functions.

### 3. FilesystemTool

**Parameters:**
- `action` (enum: browse, read, write, search)
- `path` (string, relative to knowledge base root) — required for browse, read, write
- `content` (string) — required for write
- `query` (string) — required for search

**Sandboxing:** All paths resolved relative to configured PARA notes directory. Path traversal (`../`) blocked.

**Behavior by action:**
- **browse** — List directory contents (files and subdirectories)
- **read** — Return file content as string
- **write** — Create or overwrite file, then git commit + push
- **search** — Full-text search across all notes, return matching file paths with line excerpts

**Tool description for LLM:**
> Manage knowledge base files (PARA-organized markdown notes). Actions: browse (list directory), read (get file content), write (create/update file with auto git sync), search (full-text search across all notes).

## Schema Changes

### Normalize meals into separate table

**Create `grind_meals` table:**

| Column | Type | Notes |
|--------|------|-------|
| id | bigint | primary key |
| nutrition_day_id | bigint FK | references grind_nutrition_days.id, cascade delete |
| name | string | e.g., "Lunch", "Protein Shake" |
| time | string, nullable | e.g., "12:30" |
| kcal | unsigned int | |
| protein | unsigned int | |
| fat | unsigned int | |
| carbs | unsigned int | |
| timestamps | | |

**Remove from `grind_nutrition_days`:**
- `meals` (json) — data migrated to grind_meals rows
- `total_kcal`, `total_protein`, `total_fat`, `total_carbs` — computed via SUM queries on grind_meals

**Migration:** Create grind_meals table, migrate existing JSON meals data to rows, then drop the removed columns.

### Meal model

Create `Modules\Holocron\Grind\Models\Meal` Eloquent model with `belongsTo(NutritionDay::class)` relationship. NutritionDay gets `hasMany(Meal::class)` relationship.

## Eloquent Observers

### NutritionDayObserver

The `syncProteinGoalProjection()` logic needs to be preserved. Since totals are no longer stored on the days table, the observer should:
- On NutritionDay save: query the related meals' protein sum and sync with DailyGoal model
- Alternatively, this could move to a `MealObserver` that fires when meals are created/updated/deleted

**Note:** Observers only fire on Eloquent operations, not raw SQL. The system prompt should instruct Chopper to prefer the EvalTool (Eloquent) for meal writes when protein goal syncing matters. For simple queries and reads, DatabaseTool is preferred.

## System Prompt Changes

### Remove
- All tool-specific invocation instructions (search-first for quests, etc.)
- Tool-by-tool usage guides

### Add
1. **Auto-generated schema summary** — compact table/column listing injected at boot via a `SchemaProvider`
2. **Tool selection guidance:**
   - DatabaseTool → reading and writing data (SELECT, INSERT, UPDATE)
   - EvalTool → Scout semantic search, complex calculations, HTTP requests
   - FilesystemTool → knowledge base notes
3. **Domain knowledge** previously embedded in tool descriptions:
   - Quest structure (parent/child, notes flag, completed_at)
   - Meal structure (belongs to nutrition day, macros)
   - Body measurement fields and delta calculation
   - Nutrition day types and their meaning
4. **Schema context instructions:** "A compact schema summary is provided below. Use DESCRIBE queries for full column details when needed."

### SchemaProvider

A new class that:
1. Runs at agent boot time
2. Queries `SHOW TABLES` + `DESCRIBE` for relevant tables
3. Generates a compact schema summary string
4. Injects it into Chopper's system prompt via the Promptable trait

Only includes Chopper-relevant tables (grind_*, holocron_*, agent_conversation_messages, users), not framework tables.

## Design Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Tool count | 3 (down from 17) | General-purpose tools let the LLM adapt without code changes |
| SQL restrictions | SELECT/INSERT/UPDATE only | Chopper manages data, not schema. No DELETE for safety. |
| Eval allowlist | Class/function allowlist | Prevents filesystem/system access while enabling Scout + HTTP |
| Schema context | Prompt injection + on-demand DESCRIBE | Compact summary always available, full details queryable |
| Meals storage | Normalized table | Makes SQL writes natural, eliminates JSON manipulation, removes need for total recalculation logic |
| Business logic | Observers + prompt guidance | Observers handle Eloquent writes; prompt guides tool selection for writes that need side effects |
