# Harmonize Protein Tracking

## Problem

Protein is tracked in two independent systems with no data flow between them:

- **Goals system** (dashboard): Manual daily input, streaks, goal = weight x 2g
- **Nutrition system** (grind): Meal-based logging, sums protein from meals, targets in user_settings per day-type

Users must log protein in both places separately.

## Decision

Nutrition becomes the single source of truth for protein. The goals widget displays protein progress read-only, driven entirely by meal logging.

## Data Flow

```
User logs/deletes meal (Livewire UI or LogMeal AI tool)
  -> NutritionDay::recalculateTotals()
    -> Sums meals into total_kcal, total_protein, total_fat, total_carbs
    -> Syncs protein to DailyGoal:
       - Reads day type from NutritionDay.type
       - Reads protein target from UserSetting.nutrition_daily_targets[day_type].protein
       - Gets or creates DailyGoal::for(GoalType::Protein, date)
       - Sets goal = target, amount = total_protein
       - Saves (streak logic fires if goal reached)
```

Day type changes on the nutrition page also trigger `recalculateTotals()`, updating the target.

## Changes by File

### `NutritionDay::recalculateTotals()`

Add protein goal sync after saving totals. Fetches user settings target for the day type, updates DailyGoal protein row with both `goal` and `amount`.

### `protein.blade.php` (goals widget)

Remove manual input form. Show read-only progress display. Optionally show a nudge to log meals in nutrition if no data yet.

### `Goals.php` (Livewire component)

Skip manual tracking for protein type.

### `CreateDailyGoals` job

Still creates a protein DailyGoal row daily. Amount starts at 0, goal from GoalType default. First meal sync overwrites both.

## Edge Cases

- **No meals logged**: DailyGoal protein row exists with amount=0, goal from GoalType default.
- **Day type changes mid-day**: `recalculateTotals()` fires, updating protein target for the new day type. Amount stays the same, goal shifts.
- **Deleting all meals**: Amount goes to 0. Today's streak credit removed, previous days unaffected.
- **Streak tracking**: Amount is set directly (not incremented via `track()`). Streak check must trigger on direct amount set when goal transitions from not-reached to reached.

## What Stays the Same

- `CreateDailyGoals` job still creates protein row daily
- Protein appears in goals widget with streak display
- All other goal types unchanged
- Nutrition UI unchanged
- LogMeal AI tool works automatically (already calls `recalculateTotals()`)

## What Gets Removed

- Manual protein input in goals widget
- Nothing else
