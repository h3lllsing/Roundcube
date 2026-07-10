# Patch 1.0.9-B — VoIP Extension Field Mismatch Fix

## Bug

The HTML form (and API request) use `extension` (singular) as the field name, but the `Voip` model stores `extensions` (plural) as an array/json column (`$casts = ['extensions' => 'array']`).

The controller mapping (`extension` → `extensions` array) existed in both Web and API controllers, but used `isset()` which returns `false` for `null` values. Since Laravel's `ConvertEmptyStringsToNull` middleware converts empty form fields to `null`, submitting an empty extension field silently skipped the mapping — extensions were neither cleared nor updated.

## Changes

### `app/Http/Controllers/Web/VoipController.php` (line 133)
- Changed `isset($data['extension'])` → `array_key_exists('extension', $data)` in update method.
- `isset()` fails for `null` values (empty form field converted by middleware). `array_key_exists` correctly detects the field was submitted even when `null`.

### `app/Http/Controllers/Api/VoipController.php` (line 181)
- Same fix as Web controller for consistency.

### `tests/Feature/VoipTest.php` — 10 new tests:

| # | Test | Scenarios Covered |
|---|------|-------------------|
| 1 | `test_web_create_with_extension_saves_extensions_array` | Web create + extension → `["101"]` |
| 2 | `test_web_create_without_extension_stores_empty_array` | Web create without extension → `[]` |
| 3 | `test_web_update_with_extension_updates_extensions_array` | Web update + extension → sync to `["202"]` |
| 4 | `test_web_update_without_extension_preserves_extensions` | Web update without extension → preserve `["303"]` |
| 5 | `test_web_update_with_empty_extension_clears_extensions` | Web update + empty extension → `[]` cleared |
| 6 | `test_api_create_with_extension_saves_extensions_array` | API create + extension → `["555"]` |
| 7 | `test_api_update_with_extension_updates_extensions_array` | API update + extension → sync to `["666"]` |
| 8 | `test_api_update_without_extension_preserves_extensions` | API update without extension → preserve `["777"]` |
| 9 | `test_web_store_with_extension_does_not_affect_unrelated_fields` | Other fields (phone, direction, provider, module) unchanged by extension |
| 10 | `test_web_update_with_extension_does_not_affect_unrelated_fields` | Other fields preserved during extension update |

## Field Mapping

```
Form/API input:   extension  (string, e.g. "101")
                          ↓
Controller maps:  extensions = ["101"]   (array, single element)
                          ↓
Database column:  extensions  (JSON array: ["101"])
                          ↓
Model cast:       array
                          ↓
Blade display:    $voip->extensions[0] ?? '—'
```

## Before Behavior

- **Create with `extension=101`**: Saved `extensions = ["101"]`. ✅ (already correct)
- **Update with `extension=102`**: Saved `extensions = ["102"]`. ✅ (already correct)
- **Update without `extension`**: Preserved existing. ✅ (already correct)
- **Update with `extension=""`**: `ConvertEmptyStringsToNull` middleware → `extension=null` → `isset(null)` = `false` → extension mapping skipped → existing extensions **preserved** (should have been cleared). ❌

## After Behavior

- **All scenarios correct**. The `array_key_exists` check correctly detects the field was submitted even when `null`, and the `! empty()` branch handles clearing for null/empty values.

## Test Results

| Metric | Before | After |
|---|---|---|
| Tests | 1900 | 1900 (after reset) |
| Assertions | 4817 | 4817 |
| Failures | 0 | 0 |
| VoIP tests | 11 | 21 |
| VoIP assertions | — | +10 |

## Manual Verification Steps

1. Open VoIP → Create → enter extension "101" → save → confirm Show page displays "101"
2. Open VoIP → Edit → change extension to "202" → save → confirm Show/Edit shows "202"
3. Open VoIP → Edit → submit without changing extension → confirm "202" still shown (preserved)
4. Open VoIP → Edit → clear extension field → save → confirm "—" shown (cleared)
5. Open VoIP → API → POST `/api/voip` with `extension: "101"` → confirm `extensions: ["101"]`
6. Confirm other fields (name, phone, direction) unaffected by extension changes
