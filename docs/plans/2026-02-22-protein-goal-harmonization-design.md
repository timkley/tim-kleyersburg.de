# Protein Goal Harmonization Design

## Overview

Unify protein tracking so nutrition data is the canonical source for both target and progress, while preserving existing Dashboard goal/streak presentation.

## Problem

Protein is currently tracked in two places:

- Dashboard `daily_goals` (`GoalType::Protein`) with manual entry.
- Grind nutrition (`grind_nutrition_days.total_protein`) with per-day-type targets from `user_settings.nutrition_daily_targets`.

This creates drift between displayed progress/targets and actual logged meals.

## Goals

- Use nutrition targets as the canonical protein goal source.
- Use nutrition totals as the canonical protein amount source.
- Keep Dashboard protein streak/progress UI, but make it derived/read-only.
- Ensure all nutrition write paths stay consistent (UI, AI tool, future paths).

## Non-Goals

- Historical backfill of old protein `daily_goals` rows.
- Refactor/removal of non-protein goals.
- Replacing `daily_goals` as the streak/progress storage model.

## Decisions

- Canonical target: `user_settings.nutrition_daily_targets[dayType].protein`.
- Canonical amount: `grind_nutrition_days.total_protein` for a date.
- Dashboard day-type resolution: use `NutritionDay->type`; if missing, default to `rest`.
- If day-type protein target is missing: fallback to formula `round(weight * 2)`.
- Sync behavior: absolute overwrite (`amount = total_protein`), not incremental.
- Sync scope: all nutrition write paths via domain/model-level hook.
- Historical behavior: no backfill; sync from rollout onward when days are updated.

## Approaches Considered

### 1. Derived projection in `daily_goals` (selected)

- Keep `daily_goals` for Dashboard rendering and streak logic.
- Treat protein `daily_goals` rows as projection synced from nutrition.

Trade-offs:

- Pros: small refactor, keeps existing UI/streak queries intact.
- Cons: duplicated data remains by design.

### 2. Lazy sync on Dashboard render

- Update protein goal only when Dashboard is viewed.

Trade-offs:

- Pros: minimal write-path changes.
- Cons: stale values in other flows; hidden inconsistency windows.

### 3. Remove protein from `daily_goals`

- Compute protein status/streak directly from nutrition tables.

Trade-offs:

- Pros: cleanest single-model long-term design.
- Cons: large refactor across streak/calendar/dashboard behaviors.

## Architecture

`NutritionDay` remains the write authority for protein intake. After nutrition totals are recalculated (and when day type changes), inline sync logic updates the corresponding protein `daily_goals` row for the same date.

`daily_goals` for protein becomes a read model:

- `goal` derives from nutrition target for that day type.
- `amount` derives from nutrition total protein.

Dashboard reads this projected row as before but no longer allows manual protein entry.

## Component-Level Design

### `modules/Holocron/Grind/Models/NutritionDay.php`

- Extend `recalculateTotals()` to sync protein `DailyGoal` inline after totals are saved.
- Inline sync algorithm:
  - Resolve Tim user + settings.
  - Resolve protein target for current day type.
  - If missing, fallback to `round(weight * 2)`.
  - Upsert/find `DailyGoal` for `GoalType::Protein` and same date.
  - Overwrite `goal` and `amount`.

### `modules/Holocron/Grind/Livewire/Nutrition/Index.php`

- In `updatedDayType()`, trigger the same sync path after updating day type.
- Rationale: changing day type can change target while amount stays the same.

### `app/Ai/Tools/LogMeal.php`

- No direct sync changes expected if sync is in `NutritionDay::recalculateTotals()`.
- AI write path is automatically harmonized because it already calls `recalculateTotals()`.

### `modules/Holocron/Dashboard/Views/components/goals/protein.blade.php`

- Remove manual input form for protein tracking.
- Keep read-only progress/streak display.
- Optional UX note/link to nutrition page can be added later.

## Data Flow

1. Meal added/removed (Nutrition UI or AI tool).
2. `NutritionDay::recalculateTotals()` updates cached totals.
3. Inline sync resolves target and upserts protein `DailyGoal` for same date.
4. Dashboard renders updated protein goal progress and streak without manual edits.

On day type change:

1. `day.type` updated.
2. Sync reruns for that day.
3. `DailyGoal.goal` updates immediately to new day-type target.
4. `DailyGoal.amount` stays aligned to `total_protein`.

## Error Handling and Edge Cases

- Missing `nutrition_daily_targets`/missing day-type target:
  - fallback to `round(weight * 2)`.
- Missing weight and missing target:
  - fallback to `0` to avoid runtime failures.
- Missing nutrition day for a date:
  - no backfill; existing historical rows untouched until date is updated through nutrition.
- Repeated sync calls:
  - idempotent due to absolute assignment.

## Testing Strategy

### Unit

- `NutritionDay` totals sync protein goal amount exactly.
- Day-type target is reflected in protein goal after sync.
- Fallback target (`round(weight * 2)`) applies when day-type target missing.

### Feature

- Nutrition add/delete meal updates protein `DailyGoal` via recalc path.
- Day type change updates protein goal target.
- AI `LogMeal` path updates protein `DailyGoal` consistency.
- Dashboard protein component no longer provides manual submit UI.

## Rollout Notes

- No migration required.
- No historical data backfill required.
- Existing historical protein `daily_goals` can remain until touched by new nutrition writes.

## Success Criteria

- For any date touched through nutrition flows, Dashboard protein amount equals `total_protein`.
- For any touched date, Dashboard protein goal equals day-type nutrition target (or fallback formula).
- Protein progress on Dashboard is read-only and cannot drift from nutrition totals.
