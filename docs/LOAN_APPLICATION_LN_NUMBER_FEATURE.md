# Loan Application LN Number (LID) Feature

## Overview

This document describes the LN Number (LID) field added to the loan application page and the related member ID parsing fix. The LN Number field allows users to specify a custom loan number or leave it blank for auto-generation.

**Affected Page:** Loan Application (`/en/loan/loan_application`)

---

## Features

### LN Number (LID) Field

| Aspect | Description |
|--------|-------------|
| **Location** | Loan application form, under "Loan Basic Informations", first field |
| **Default** | Auto-generated next LN number (e.g. LN1, LN2, LN123) |
| **Editable** | Yes — user can type a custom value |
| **Auto-generation** | If left empty on submit, system generates next number from `auto_inc` table |
| **Storage** | Saved to `loan_contract.LID` column |
| **Uniqueness** | Custom LIDs are validated; duplicate LID returns error |

### Member ID / PID Parsing Fix

The PID and member_id fields use autocomplete that returns format `"2005-00173 - Member Name"`. The controller now correctly extracts the full ID (e.g. `2005-00173`) instead of splitting on hyphen and taking only the first segment.

---

## Modified Files

### application/views/loan/loan_application_step1.php

- Added LN Number input field with label and helper text
- Default value from `$next_ln_number` (passed from controller)
- Form field name: `lid`

### application/controllers/loan.php

**LN Number handling:**
- Passes `next_ln_number` to view for default display
- On submit: reads `lid` from POST; if empty, gets next LN from model
- Adds `LID` to `$createloan` array before calling `add_newloan()`

**Member ID parsing (replaced `explode('-')` with):**
```php
// Handle autocomplete format: "2005-00173 - Member Name" or plain "2005-00173"
if (strpos($pid_value, ' - ') !== false) {
    $parts = explode(' - ', $pid_value, 2);
    $pid = trim($parts[0]);
} else {
    $pid = $pid_value;
}
// Same logic for member_id
```

### application/models/loan_model.php

**New function:**
- `get_next_ln_number()` — Returns next LN number without incrementing `auto_inc` (for form default)

**Modified function:**
- `add_newloan($data, $processingfee)`:
  - If `$data['LID']` is provided and not empty: uses it, checks uniqueness via `is_loan_exist()`, returns FALSE if duplicate
  - If custom LID is numeric format (LN1234): updates `auto_inc` to stay ahead
  - If `$data['LID']` is empty: auto-generates from `auto_inc` (original behavior)

---

## Database

### loan_contract table

- **LID** (varchar) — Primary/unique identifier for the loan
- Stores the LN Number (e.g. LN1, LN2, LN-2025-001, or custom values)

### auto_inc table

- **loan** (bigint) — Used for auto-generating sequential LN numbers when form field is empty
- Incremented when a new auto-generated LID is created

---

## Behavior Summary

| Scenario | Result |
|----------|--------|
| User leaves LN Number empty | System generates next LN (e.g. LN5) and saves it |
| User enters custom LN (e.g. LN-2025-001) | Custom value saved to LID column |
| User enters LN that already exists | Insert fails; "Fail to save Loan information" shown |
| Autocomplete returns "2005-00173 - John Doe" | PID = "2005-00173", member_id = extracted value |

---

## Date Implemented

February 2026

---

## Related

- **LOAN_APPLICATION_BYPASSES.md** — Temporary validation bypasses on same page
- **Language:** `application/language/english/loan_lang.php` — `$lang['loan_LID']` = 'Loan Number'
- **Autocomplete:** `application/controllers/saving.php` — `autosuggest()`, `search_member()`
