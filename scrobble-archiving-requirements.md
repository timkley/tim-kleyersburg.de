# Last.fm Scrobble Archiving Feature Requirements

## 1. Core Objective
The primary goal is to create a robust system for regularly archiving a user's Last.fm scrobbles into the application's database. This will serve as a personal backup and data source for potential future features.

## 2. Key Requirements

### 2.1. High-Volume Data Handling
- The system must be capable of handling a large number of scrobbles (e.g., 200,000+).
- Initial import and subsequent updates should be processed efficiently without causing timeouts or excessive memory usage.
- This will be achieved by processing the scrobbles in chunks using a queued job.

### 2.2. Incremental Archiving
- The system must be "smart" and only fetch new scrobbles that have not yet been archived.
- It should not re-import the entire scrobble history on every run.
- This will be implemented by storing the timestamp of the last archived track and using it as a starting point (`from` parameter) for the next API request to Last.fm's `user.getRecentTracks` endpoint.

### 2.3. Scheduled & Automated Execution
- The archiving process must run automatically at regular intervals.
- A scheduled job will be configured to run once every hour.

### 2.4. Data Storage
- A new database table (`scrobbles`) will be created to store the archived data.
- The table will store essential information for each scrobble, including:
    - Artist
    - Track name
    - Album
    - Play timestamp (`played_at`)
    - Full API response data (as JSON) for future-proofing.

### 2.5. Code Refactoring & Service Abstraction
- Existing Last.fm API calls within the `app/Livewire/Pages/Components/LastScrobble.php` component must be refactored.
- All Last.fm API interactions will be centralized into a new dedicated service class: `app/Services/LastFM.php`. This will improve maintainability and reusability.

## 3. Technical Implementation Details

- **Model:** A `Scrobble` Eloquent model will be created to interact with the `scrobbles` table.
- **Migration:** A database migration will be created to define the schema for the `scrobbles` table.
- **Service Class:** `app/Services/LastFM.php` will handle all communication with the Last.fm API.
- **Job:** A queued job (e.g., `ArchiveScrobbles`) will contain the logic for fetching and storing the scrobbles.
- **Scheduler:** The new job will be registered in Laravel's console kernel to run hourly.
- **API Endpoint:** The `user.getRecentTracks` method from the Last.fm API will be used.
- **Configuration:** Last.fm API key and username will be stored in the `.env` file.
