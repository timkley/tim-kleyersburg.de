# Nutrition Tracking Design

## Overview

Add nutrition tracking to the Grind fitness module. Includes a dedicated UI for daily meal logging with 7-day rolling averages, body measurement tracking with charts, and Chopper integration for logging meals and querying data via chat.

## Database Schema

### `user_settings` (add column)

| Column | Type | Notes |
|--------|------|-------|
| nutrition_daily_targets | json, nullable | `{"training": {kcal, protein, fat, carbs}, "rest": {...}, "sick": {...}}` |

### `grind_nutrition_days` (new table)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| date | date, unique | |
| type | enum: training/rest/sick | |
| training_label | string, nullable | e.g. "upper", "lower" |
| meals | json | `[{name, time, kcal, protein, fat, carbs}, ...]` |
| notes | text, nullable | |
| total_kcal | int | Cached from meals |
| total_protein | int | |
| total_fat | int | |
| total_carbs | int | |
| timestamps | | |

### `grind_body_measurements` (new table)

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| date | date, unique | |
| weight | decimal(5,2) | |
| body_fat | decimal(4,1), nullable | |
| muscle_mass | decimal(4,1), nullable | |
| visceral_fat | int, nullable | |
| bmi | decimal(4,1), nullable | |
| body_water | decimal(4,1), nullable | |
| timestamps | | |

## UI

### Day View (Ernährung)

- Date picker defaulting to today, navigate forward/back
- Day type selector (Training/Rest/Sick) + optional training label
- 7-day rolling average bar: 4 cards showing avg kcal/protein/fat/carbs vs targets
- Today's macro totals below the averages
- Meals list: editable cards (name, time, macros), inline add/edit/delete
- Optional notes field

### Measurements (Körper)

- Form to log a new measurement (date, weight, body fat, muscle mass, etc.)
- Table of past measurements, most recent first
- Weight over time chart
- Muscle mass over time chart

### Navigation

Add to Grind navbar: `Ernährung | Körper`

## Chopper Tools

### LogMeal

Log a meal to a nutrition day. Creates the day if it doesn't exist.

Parameters:
- `date` (string, required)
- `name` (string, required)
- `kcal` (int, required)
- `protein` (int, required)
- `fat` (int, required)
- `carbs` (int, required)
- `time` (string, optional)
- `day_type` (string, optional — training/rest/sick, used when creating a new day)

Returns confirmation with updated day totals.

### QueryNutrition

Query nutrition data and summaries.

Parameters:
- `query_type` (enum: today/date/week/average, required)
- `date` (string, optional)

Modes:
- `today` — today's meals, totals, and targets for the day type
- `date` — specific day's data
- `week` — last 7 days with daily totals
- `average` — 7-day rolling averages vs targets

## Data Import

One-time migration seeder reads `nutrition.json` and imports:

- Daily targets → `user_settings.nutrition_daily_targets`
- 21 days of logs → `grind_nutrition_days` rows
- Body measurements → `grind_body_measurements` rows

Delete `nutrition.json` after import.
