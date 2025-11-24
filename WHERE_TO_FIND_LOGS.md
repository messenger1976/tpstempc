# Where to Find PHP Error Logs

## Quick Access

**View all logs at once:**
```
http://your-domain.com/view_logs.php
```

## Manual Locations

### 1. CodeIgniter Application Logs
- **Path:** `application/logs/`
- **Files:** `log-YYYY-MM-DD.php` (one file per day)
- **Example:** `application/logs/log-2024-12-19.php`
- **Status:** ✅ Enabled (threshold = 1)

### 2. XAMPP Apache Error Log
- **Path:** `C:\xampp\apache\logs\error.log`
- **Contains:** Apache and PHP errors
- **Open with:** Any text editor (Notepad++, VS Code, etc.)

### 3. XAMPP PHP Error Log
- **Path:** `C:\xampp\php\logs\php_error_log`
- **Contains:** PHP-specific errors
- **Open with:** Any text editor

### 4. Windows Event Viewer
- **How to access:**
  1. Press `Win + R`
  2. Type: `eventvwr.msc`
  3. Go to: Windows Logs → Application
  4. Look for PHP/Apache errors

## Enable Error Display (Temporary)

Currently set to **development mode** to show errors on screen.

To change back to production mode (hide errors), edit `index.php`:
```php
define('ENVIRONMENT', 'production'); // Hide errors
```

Or to show errors in production:
```php
define('ENVIRONMENT', 'production');
// Add after this:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Enable CodeIgniter Logging

Already enabled! Check `application/config/config.php`:
```php
$config['log_threshold'] = 1; // 1 = errors only, 4 = all messages
```

## After Debugging

**Remember to:**
1. ✅ Delete `view_logs.php` (security)
2. ✅ Set `ENVIRONMENT` back to `production`
3. ✅ Set `$config['log_threshold']` back to `0` if you want to disable logging

