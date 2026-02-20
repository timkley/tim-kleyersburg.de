# Quest API OpenAPI Spec — Design

## Goal

Create a static `openapi.json` file documenting all 26 Quest API endpoints for reference purposes.

## Approach

Hand-crafted OpenAPI 3.1.0 JSON file derived directly from the existing controllers, action validators, and API resources. No dependencies or build steps.

## File Location

`modules/Holocron/Quest/openapi.json`

## Auth

Bearer token via `securitySchemes` (`http/bearer`), applied globally.

## Schemas

| Schema | Source | Key Fields |
|--------|--------|------------|
| Quest | QuestResource | id, quest_id, name, description, date, daily, is_note, accepted, completed_at, should_be_printed, attachments, created_at, updated_at + optional relations |
| Note | NoteResource | id, quest_id, content, role, created_at, updated_at |
| Webpage | WebpageResource | id, url, title, pivot_id |
| Reminder | ReminderResource | id, quest_id, type, remind_at, recurrence_pattern, last_processed_at, created_at, updated_at |
| QuestRecurrence | QuestRecurrenceResource | id, quest_id, every_x_days, recurrence_type, last_recurred_at, ends_at, created_at, updated_at |

## Endpoints

All under `/api/holocron/quests`:

### Quests (9 endpoints)
- `GET /` → 200, array of Quest
- `POST /` → 201, Quest (body: name*, quest_id?, date?, daily?, is_note?, description?)
- `GET /{quest}` → 200, Quest (query: include)
- `PATCH /{quest}` → 200, Quest (body: name?, description?, date?, daily?, is_note?)
- `DELETE /{quest}` → 204
- `POST /{quest}/complete` → 200, Quest
- `POST /{quest}/move` → 200, Quest (body: quest_id?)
- `POST /{quest}/print` → 200, Quest
- `POST /{quest}/accept` → 200, Quest

### Attachments (2 endpoints)
- `POST /{quest}/attachments` → 200, Quest (multipart: file*)
- `DELETE /{quest}/attachments` → 204 (body: path*)

### Notes (3 endpoints)
- `GET /{quest}/notes` → 200, array of Note
- `POST /{quest}/notes` → 201, Note (body: content*, role?)
- `DELETE /{quest}/notes/{note}` → 204

### Links (3 endpoints)
- `GET /{quest}/links` → 200, array of Webpage
- `POST /{quest}/links` → 201, Quest (body: url*, title?)
- `DELETE /{quest}/links/{pivotId}` → 204

### Reminders (3 endpoints)
- `GET /{quest}/reminders` → 200, array of Reminder
- `POST /{quest}/reminders` → 201, Reminder (body: remind_at*, type*, id?, recurrence_pattern?)
- `DELETE /{quest}/reminders/{reminder}` → 204

### Recurrence (3 endpoints)
- `GET /{quest}/recurrence` → 200, QuestRecurrence (nullable)
- `POST /{quest}/recurrence` → 201, QuestRecurrence (body: every_x_days*, recurrence_type*, ends_at?)
- `DELETE /{quest}/recurrence` → 204

## Response Wrapping

All JSON responses use Laravel's `{"data": ...}` envelope. Collections return `{"data": [...]}`.

## Implementation

Single task: write the complete `openapi.json` file with all schemas, endpoints, request bodies, and response definitions.
