# Patch 1.0.9-A — Task Assignee Fix

## Bug

Web `TaskController::store()` and `::update()` called `Task::create($validated)` / `$task->update($validated)` directly. Since `assignee_ids` is **not** in `Task::$fillable`, the data was silently discarded — assignees were never saved/updated from the web UI.

## Root Cause

- `TaskService` already handles `assignee_ids` correctly (extracts before create, then `attach()` / `sync()` via the `task_user` pivot table).
- The API `TaskController` uses `TaskService`, so API requests worked.
- The Web `TaskController` bypassed `TaskService`, duplicating the logic incorrectly.

## Changes

### `app/Http/Controllers/Web/TaskController.php`
- Added `TaskService` import and constructor injection.
- `store()`: replaced `Task::create($validated)` with `$this->taskService->create($validated)`.
- `update()`: replaced `$task->update($validated)` with `$this->taskService->update($task, $validated)`.
- `create()`: passes `$users` view variable.
- `edit()`: passes `$users` view variable.

### `resources/views/tasks/create.blade.php`
- Added multi-select `<select name="assignee_ids[]" multiple>` with all users.
- Pre-selects from `old('assignee_ids', [])`.

### `resources/views/tasks/edit.blade.php`
- Added multi-select `<select name="assignee_ids[]" multiple>` with all users.
- Pre-selects from `old('assignee_ids', $task->assignees->pluck('id')->toArray())`.

### `tests/Feature/TaskTest.php` — 6 new tests:
1. `test_web_create_task_with_assignees_saves_assignees`
2. `test_web_update_task_with_assignees_updates_assignees`
3. `test_web_update_without_assignee_ids_preserves_existing_assignees`
4. `test_web_update_with_empty_assignee_ids_clears_assignees`
5. `test_web_create_page_shows_assignee_selector`
6. `test_web_edit_page_shows_assignee_selector_with_selected`

## Files Not Changed (No Change Needed)

- `app/Services/TaskService.php` — already correct, reused.
- `app/Models/Task.php` — `$fillable` intentionally excludes `assignee_ids` (handled via `task_user` pivot).
- `app/Http/Requests/StoreTaskRequest.php` — already validates `assignee_ids` as `nullable|array|exists:users,id`.
- `app/Http/Requests/UpdateTaskRequest.php` — same.
- `app/Http/Controllers/Api/TaskController.php` — already uses `TaskService`.
- Routes — no changes needed, existing `POST/GET` routes remain.

## Test Results

| Metric | Before | After |
|---|---|---|
| Tests | 1884 | 1890 |
| Assertions | 4753 | 4778 |
| Failures | 0 | 0 |
| Task tests | 144 | 150 |
| Task assertions | — | +6 |
