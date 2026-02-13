# Automatic Database Activity Logging

## Overview

The system now automatically logs all database INSERT, UPDATE, and DELETE operations to the activity log without requiring manual logging in your code.

## How It Works

A custom database driver (`MY_DB_mysqli_driver`) extends CodeIgniter's MySQLi driver and intercepts all write operations (INSERT, UPDATE, DELETE) to automatically log them.

## Features

✅ **Automatic Logging** - All database changes are logged automatically  
✅ **No Code Changes Required** - Works with existing code  
✅ **User Tracking** - Automatically captures user who made the change  
✅ **Table Tracking** - Records which table was modified  
✅ **Action Detection** - Automatically determines if it's a create, update, or delete  
✅ **Smart Exclusions** - System tables (like activity_logs itself) are excluded to prevent loops

## Excluded Tables

The following tables are automatically excluded from logging to prevent infinite loops:

- `activity_logs` - The logging table itself
- `sessions` / `ci_sessions` - Session storage
- `login_attempts` - Login attempt tracking

## What Gets Logged

### INSERT Operations
- **Action:** `create`
- **Description:** "Create operation on [table_name] table"
- **Table:** Automatically detected from query

### UPDATE Operations
- **Action:** `update`
- **Description:** "Update operation on [table_name] table"
- **Table:** Automatically detected from query
- **Record ID:** Attempts to extract ID from WHERE clause

### DELETE Operations
- **Action:** `delete`
- **Description:** "Delete operation on [table_name] table"
- **Table:** Automatically detected from query
- **Record ID:** Attempts to extract ID from WHERE clause

## User Information

The system automatically captures:
- **User ID** - From Ion Auth or session
- **Username** - From Ion Auth or session
- **IP Address** - From CodeIgniter input class
- **User Agent** - Browser information
- **Module/Controller** - Which controller made the change
- **Timestamp** - When the change occurred

## Example Logs

When a user performs these operations:

```php
// This INSERT will be automatically logged
$this->db->insert('members', $data);

// This UPDATE will be automatically logged
$this->db->update('members', $data, array('id' => 123));

// This DELETE will be automatically logged
$this->db->delete('members', array('id' => 123));
```

The activity log will automatically record:
- Who performed the action (user)
- What action was performed (create/update/delete)
- Which table was affected
- When it happened
- From which controller/module

## Manual Control (Optional)

If you need to disable automatic logging for specific operations:

```php
// Disable logging temporarily
$this->db->set_logging_enabled(FALSE);
$this->db->delete('temp_data', array('id' => 1));
$this->db->set_logging_enabled(TRUE); // Re-enable
```

## Excluding Additional Tables

To exclude more tables from automatic logging:

```php
// Exclude a table from logging
$this->db->exclude_table_from_logging('temp_cache');

// Include it again
$this->db->include_table_in_logging('temp_cache');
```

## Viewing Logs

All automatically logged database changes appear in:
- **Dashboard** - Recent Activities widget
- **Activity Log Page** - Full activity log with filters (`/activity_log`)

## Notes

1. **Performance**: Logging adds minimal overhead as it uses direct database inserts
2. **Error Handling**: If logging fails, the main database operation still succeeds
3. **Prevents Loops**: Activity log inserts are excluded to prevent infinite loops
4. **Works with Active Record**: All CodeIgniter Active Record methods are supported
5. **Works with Raw Queries**: Raw SQL INSERT/UPDATE/DELETE queries are also logged

## Migration

If you have existing manual logging code, you can:

1. **Keep it** - Manual logs provide more detailed descriptions
2. **Remove it** - Automatic logging handles everything
3. **Combine** - Use manual logs for important events, automatic for everything else

The automatic system works alongside manual logging without conflicts.

## Troubleshooting

### Logs not appearing?

1. Check that `activity_logs` table exists
2. Verify user is logged in (for user tracking)
3. Check if table is in exclusion list
4. Verify database connection is working

### Too many logs?

1. Add frequently updated tables to exclusion list
2. Use `set_logging_enabled(FALSE)` for bulk operations
3. Review and adjust exclusion list as needed

## Implementation Details

The automatic logging is implemented through:
- `application/core/MY_DB_mysqli_driver.php` - Extended database driver
- Automatically loaded by CodeIgniter (no configuration needed)
- Works transparently with existing code

No additional setup required! 🎉

