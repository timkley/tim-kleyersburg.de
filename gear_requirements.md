### **Product Requirements Document: Gear Module**

### 1. Overview

This document specifies the requirements for the "Gear" module, a smart packing list generator. The module's primary goal is to eliminate the mental overhead of packing by automatically creating tailored packing lists for users based on their journey's destination, duration, weather conditions, and participants.

**Target Audience:** Travelers who want a streamlined, automated, and context-aware packing experience.

### 2. User Stories

*   **As a traveler, I want to** create a new journey by providing a destination, dates, and a list of participants, **so that** the system has the context needed to build my packing list.
*   **As a traveler, I want to** view a dynamically generated packing list for my journey, **so that** I know exactly what to pack without having to think about it.
*   **As a traveler, I want to** check items off my packing list as I pack them for departure and for my return trip, **so that** I can track my progress and ensure I don't forget anything.
*   **As a traveler, I want to** manage my personal repository of packable items and their properties, **so that** the generated lists are perfectly customized to my gear.
*   **As a traveler, I want to** update my journey details, **so that** my packing list automatically adjusts to the new plans.

### 3. Functional Requirements

#### FR-1: Journey Management

*   **FR-1.1: Create Journey:**
    *   Users must be able to create a journey by providing the following information:
        *   `destination` (string, required): A user-friendly name. A geocoding autocomplete service will be used to aid input.
        *   `latitude` & `longitude` (decimal, required): Auto-populated by the geocoding service.
        *   `starts_at` & `ends_at` (date, required): Start date must be before or on the end date.
        *   `participants` (array, required): A multi-select input for participant types (e.g., 'Adult', 'Child', 'Infant').
    *   **Validation:** All fields are required. Dates must be valid. Latitude/Longitude must be within their valid ranges.
*   **FR-1.2: View Journeys:**
    *   The module's main page will display a list of all user-created journeys, sorted with upcoming journeys first.
*   **FR-1.3: Update Journey:**
    *   Users must be able to edit the details of an existing journey.
    *   **Crucially**, updating a journey's dates, destination, or participants **must** trigger a full regeneration of its associated packing list (`GearJourneyItem` records). The user should be warned that any existing packing progress (checkboxes) will be reset.
*   **FR-1.4: Delete Journey:**
    *   Users must be able to delete a journey. This action will also delete all associated packing list items (`GearJourneyItem` records).

#### FR-2: Gear Locker (Item & Category Management)

*   **FR-2.1: CRUD for Categories:** Users can create, read, update, and delete item categories (e.g., "Clothing", "Electronics").
*   **FR-2.2: CRUD for Items:** Users can create, read, update, and delete their personal gear items.
*   **FR-2.3: Item Properties:** When creating/editing an item, users must provide:
    *   `name` (string, required).
    *   `category_id` (foreign key, optional).
    *   **Quantity Logic:**
        *   `quantity_per_day` (float, default 0): The quantity needed per day of the journey. A value of `0` signifies a "fixed quantity" item.
        *   **Fixed Quantity:** An integer field `quantity` (default 1) will be used if `quantity_per_day` is 0.
    *   `properties` (array, optional): A multi-select input allowing the user to assign one or more packing conditions (from `GearProperty` Enum) to the item.

#### FR-3: Packing List Generation

*   **FR-3.1: Trigger:** The packing list generation service is triggered automatically upon the successful creation of a `GearJourney`. It is also triggered upon an update (per FR-1.3).
*   **FR-3.2: Idempotency:** Before generating a new list, the service must delete all existing `GearJourneyItem` records for the given journey to prevent duplication and ensure the list is always fresh.
*   **FR-3.3: Logic:** The service will:
    1.  Fetch all `GearItem` records belonging to the user. Processing should be done in chunks to ensure performance.
    2.  For each `GearItem`, determine if it should be included:
        *   If the item has no `properties`, it is always included.
        *   If the item has `properties`, **all** conditions associated with those properties must pass for the item to be included (AND logic).
    3.  If an item is included, calculate its final quantity:
        *   If `quantity_per_day` > 0, `quantity = ceil(item.quantity_per_day * journey.duration_in_days)`.
        *   If `quantity_per_day` == 0, `quantity = item.quantity`.
    4.  Create a `GearJourneyItem` record in the database for each included item with its calculated quantity.

#### FR-4: Packing List Interaction

*   **FR-4.1: Display:** The generated packing list for a journey will be displayed, grouped by `GearCategory`. Items without a category will be in a default "Uncategorized" group.
*   **FR-4.2: Check-off Functionality:** Each item on the list will have two checkboxes: `packed_for_departure` and `packed_for_return`.
*   **FR-4.3: State Persistence:** Toggling a checkbox must immediately and asynchronously persist its state to the database without a full page reload (via a Livewire action).

### 4. Conditional Logic System

The system's "smarts" are driven by a strategy-pattern-based conditional system.

#### 4.1. `GearProperty` Enum
A PHP enum defines the available conditions.
```php
// app/Enums/GearProperty.php
enum GearProperty: string
{
    case WARM_WEATHER = 'warm-weather';
    case COLD_WEATHER = 'cold-weather';
    case RAIN_EXPECTED = 'rain-expected';
    case CHILD_SPECIFIC = 'child-specific';
    case ADULT_SPECIFIC = 'adult-specific';
}
```

#### 4.2. `PackingConditionContract`
An interface ensures all condition checkers adhere to the same contract.
```php
interface PackingConditionContract {
    public function shouldBeIncluded(GearJourney $journey): bool;
}
```

#### 4.3. Condition Implementations
Each enum case will have a corresponding class that implements the contract.
*   `WarmWeatherCondition`: Returns `true` if `WeatherForecast->average_temp_celsius >= 20`.
*   `ColdWeatherCondition`: Returns `true` if `WeatherForecast->average_temp_celsius <= 10`.
*   `RainExpectedCondition`: Returns `true` if `WeatherForecast->max_precipitation_chance >= 40`.
*   `ChildSpecificCondition`: Returns `true` if the `$journey->participants` array contains the string `'child'`.
*   `AdultSpecificCondition`: Returns `true` if the `$journey->participants` array contains the string `'adult'`.

#### 4.4. Service Registration
Each enum also has an accompanying condition class which will be used to check the condition.

### 5. Data Model & Schema

*   **`gear_journeys`**
    *   `id` (PK), `user_id` (FK), `destination` (string), `latitude` (decimal), `longitude` (decimal), `starts_at` (date), `ends_at` (date), `participants` (json), timestamps.
*   **`gear_categories`**
    *   `id` (PK), `user_id` (FK), `name` (string), timestamps.
*   **`gear_items`**
    *   `id` (PK), `user_id` (FK), `category_id` (FK, nullable, `ON DELETE SET NULL`), `name` (string), `quantity_per_day` (float, default 0), `quantity` (integer, default 1), `properties` (json, nullable), timestamps.
*   **`gear_journey_items`**
    *   `id` (PK), `journey_id` (FK, `ON DELETE CASCADE`), `item_id` (FK, `ON DELETE CASCADE`), `quantity` (integer), `packed_for_departure` (boolean, default false), `packed_for_return` (boolean, default false), timestamps.

### 6. External Service Dependencies

*   **Geocoding Service:** Required for address autocomplete on the journey creation form to convert place names into `latitude` and `longitude`. Example: Algolia Places, Google Places API.
*   **Weather Service:** Required by the packing condition system.
  * Is already implemented, see if the weather service needs another method.
  *   **Data Structure:** The forecast must return a simple data object containing at least `average_temp_celsius` and `max_precipitation_chance`.

### 7. Out of Scope

*   Sharing packing lists between users.
*   Multi-user collaboration on a single journey.
*   AI-based item suggestions based on destination type (e.g., "Beach" suggests "Sunscreen").
*   Inventory management (tracking how many of an item a user owns).
