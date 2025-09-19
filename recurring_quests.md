# Recurring Quests Feature Requirements

## 1. Introduction

This document outlines the feature requirements for implementing recurring quests in the Quest module. The goal is to allow users to create quests that repeat on a schedule using a master quest as a template.

## 2. High-Level Requirements

- Users should be able to create quests that recur daily, weekly, or monthly.
- The system should automatically create new "instance" quests based on the recurrence rules of a "master" quest.
- The UI should be updated to allow users to create and manage these recurrence rules.

## 3. Detailed Requirements

### 3.1. Database Schema

A new `quest_recurrences` table will be created to store the recurrence rules. This keeps the main `quests` table clean.

**`quest_recurrences` table:**

- `id`: Primary Key
- `quest_id`: Foreign Key to `quests.id` (the "master" quest template)
- `type`: `string` (e.g., `daily`, `weekly`, `monthly`), should be managed with an Enum class
- `value`: `integer` (e.g., `2` for Tuesday, `15` for the 15th of the month, `2` for every 2 weeks)
- `last_recurred_at`: `timestamp`
- `ends_at`: `timestamp` (nullable)

The `quests` table will be extended with one nullable column to link instances back to their recurrence rule:

- `created_from_recurrence_id`: Foreign Key to `quest_recurrences.id` (nullable)

### 3.2. Models

- A new `QuestRecurrence` model will be created for the `quest_recurrences` table.
- The `Quest` model will have a relationship to `QuestRecurrence`.

### 3.3. Recurrence Logic

A new Job will be created to handle the recurrence of quests. This job will be scheduled to run daily.

The job will perform the following steps:

1. Find all `quest_recurrences` that are due to recur.
2. For each recurrence rule, find the corresponding "master" quest via `quest_id`.
3. Create a new quest "instance" by copying the columns `name`, `description`, `quest_id`, `images`, `should_be_printed`
4. The new quest instance will have its `created_from_recurrence_id` set to the ID of the recurrence rule.
5. Update the `last_recurred_at` timestamp on the `quest_recurrences` record.
6. If the `ends_at` timestamp has been reached, the rule should no longer be processed.
7. The job will only create a new quest instance, if the previous instance was completed.

### 3.4. User Interface

The UI will be updated to include the following changes:

- **Quest Creation/Edit Form:**
    - A new section will be added to allow users to define recurrence rules for a quest.
    - Users should be able to select from the following recurrence options:
        - Every day / every x days (e.g., every 2 days, `type` = `daily`, `value` = 2)
        - Every week on a specific day (e.g., every Tuesday, `type` = `weekly`, `value` = 2)
        - Every month on a specific day (e.g., every 15th of the month, `type` = `monthly`, `value` = 15)
- **Quest View Page:**
    - If a quest is a master quest (so a matching recurrence model is found), its recurrence information will be displayed.
    - If a quest is an instance, a link to the master quest should be displayed.
