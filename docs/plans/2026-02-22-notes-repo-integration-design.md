# Notes Repo Integration Design

## Goal

Integrate an external PARA-structured markdown notes repo into the project so Chopper can browse, read, write, and search notes — while the repo stays independently managed via git.

## Architecture

### Git Submodule

Mount the external notes repo as a git submodule at `storage/notes/`.

- Tracked in the main project's `.gitmodules`
- Full git checkout — editable from IDE, terminal, or Chopper
- Independent commit history from the main project

### NotesService

A service class at `app/Ai/Services/NotesService.php` encapsulating all filesystem and git operations.

**Filesystem operations:**
- `list(string $path = '/')` — list directories and files at a path
- `read(string $path)` — read a markdown file's content
- `write(string $path, string $content)` — create or overwrite a file, creating parent dirs as needed
- `search(string $query)` — full-text grep across all `.md` files, returns matches with context

**Git sync operations:**
- `pull()` — runs `git pull --rebase` in the submodule; debounced to once per 60 seconds
- `commitAndPush(string $path)` — runs `git add <file>`, `git commit -m "Update <path>"`, `git push`

**Auto-sync behavior:**
- **Before reads** (list, read, search): calls `pull()` (debounced)
- **After writes**: calls `commitAndPush()` for the written file

**Conflict handling:** If `pull --rebase` fails due to conflicts, abort the rebase and return an error message describing the conflict. No silent data corruption — user resolves manually.

### AI Tools

Four new tools in `app/Ai/Tools/`, following the existing `Tool` interface pattern:

#### BrowseNotes
- **Description:** Browse the notes directory structure. Lists folders and files at a given path.
- **Schema:** `path` (string, optional, defaults to `/`)
- **Returns:** Formatted directory listing with file/folder indicators

#### ReadNote
- **Description:** Read the content of a markdown note file.
- **Schema:** `path` (string, required)
- **Returns:** The file content as a string

#### WriteNote
- **Description:** Create or update a markdown note file. Auto-commits and pushes the change.
- **Schema:** `path` (string, required), `content` (string, required)
- **Returns:** Confirmation message

#### SearchNotes (new — replaces existing)
- **Description:** Full-text search across all markdown notes in the knowledge base.
- **Schema:** `query` (string, required), `limit` (integer, optional)
- **Returns:** Matching file paths with surrounding context lines

### Rename: SearchNotes -> SearchQuestComments

The existing `SearchNotes` tool (semantic search over quest comments) is renamed to `SearchQuestComments` to avoid confusion. The new `SearchNotes` tool searches the markdown knowledge base.

Files affected:
- `app/Ai/Tools/SearchNotes.php` -> `app/Ai/Tools/SearchQuestComments.php`
- `app/Ai/Agents/ChopperAgent.php` — update import and registration

### Chopper Agent Updates

Register the 4 new tools in `ChopperAgent::tools()` and update the system instructions to mention the knowledge base capabilities.

## What This Does NOT Include

- No web UI — notes are edited via filesystem or Chopper
- No database models or migrations — notes live as files
- No MCP integration — tools are Laravel AI tools only
