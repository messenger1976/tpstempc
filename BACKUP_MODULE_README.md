# Database Backup Module

## Overview
The Database Backup Module provides a comprehensive solution for backing up, restoring, and managing your database backups directly from the application interface.

## Features

### 1. **Create Backups**
- One-click database backup creation
- Automatic timestamped filenames
- Stores backups in the `backups/` directory

### 2. **Manage Backups**
- View list of all available backups
- See backup file size and creation date
- Organized display with newest backups first

### 3. **Download Backups**
- Download backup files to your local computer
- Secure download with authentication check
- All downloads are logged for security

### 4. **Delete Backups**
- Remove old or unnecessary backups
- Confirmation dialog prevents accidental deletion
- Deletion is logged for audit trail

### 5. **Restore Database**
- Restore database from any backup file
- Double confirmation to prevent accidents
- Complete database restoration

### 6. **Auto Backup (Scheduled)**
- Can be configured with cron jobs for automatic backups
- Automatic cleanup of old backups (configurable)
- Keeps only recent backups to save space

## Installation

The module has been installed with the following components:

### Files Created:
1. **Controller**: `application/controllers/backup.php`
2. **View**: `application/views/backup/index.php`
3. **Backup Directory**: `backups/` (with security files)

### Menu Integration:
- Added to Settings menu (Admin only access)
- Menu item: "Database Backup" with database icon

## Access Control

- **Admin Only**: Only users with administrator privileges can access this module
- **Authentication Required**: All operations require active login session
- **Activity Logging**: All backup operations are logged to the activity_log table

## Usage

### Creating a Backup

1. Navigate to **Settings > Database Backup**
2. Click **"Create New Backup"** button
3. Confirm the action in the dialog
4. Wait for the backup to complete
5. The new backup will appear in the list

### Downloading a Backup

1. Locate the backup in the list
2. Click the **"Download"** button
3. The backup file will be downloaded to your computer

### Restoring a Backup

⚠️ **WARNING**: Restoring will overwrite ALL current data!

1. **Create a current backup first** (highly recommended)
2. Locate the backup you want to restore
3. Click the **"Restore"** button
4. Confirm twice (double confirmation for safety)
5. Wait for the restoration to complete

### Deleting a Backup

1. Locate the backup you want to delete
2. Click the **"Delete"** button
3. Confirm the deletion
4. The backup file will be permanently removed

## Automated Backups (Optional)

### Setting Up Cron Job

To schedule automatic daily backups, add this to your crontab:

```bash
# Daily backup at 2:00 AM
0 2 * * * /usr/bin/php /path/to/your/application/index.php backup auto_backup
```

For Windows Task Scheduler:
```
Program: C:\xampp\php\php.exe
Arguments: C:\xampp3\htdocs\tapstemco\index.php backup auto_backup
```

### Auto Cleanup

The `auto_backup()` function automatically removes backups older than 30 days. You can modify this in the controller:

```php
$this->cleanup_old_backups(30); // Change 30 to desired number of days
```

## Security Features

### 1. Directory Protection
- `.htaccess` file prevents direct web access to SQL files
- `index.html` prevents directory browsing

### 2. Access Control
- Admin-only access
- Session authentication required
- Path traversal protection

### 3. Activity Logging
All operations are logged with:
- User ID
- Action performed
- Filename
- IP address
- Timestamp

### 4. File Validation
- Filename validation prevents directory traversal attacks
- Extension checking ensures only SQL files are processed

## Best Practices

### Regular Backups
1. **Daily**: For active production systems
2. **Weekly**: For moderate-use systems
3. **Before Updates**: Always backup before system updates
4. **Before Major Changes**: Backup before significant data operations

### Backup Storage
1. **Download Important Backups**: Store copies locally or in cloud storage
2. **Multiple Locations**: Keep backups in different physical locations
3. **Test Restores**: Periodically test backup restoration
4. **Retention Policy**: Keep multiple versions (daily, weekly, monthly)

### Safety Tips
1. ✅ Always create a fresh backup before restoring
2. ✅ Test backups on a development environment first
3. ✅ Verify backup file integrity before restoring
4. ✅ Keep backup files secure and encrypted
5. ⚠️ Never restore untested backups on production
6. ⚠️ Be aware that restore operations overwrite all data

## Troubleshooting

### Backup Creation Fails
**Problem**: "Failed to create backup file"
**Solution**: 
- Check that `backups/` directory exists
- Verify write permissions on the directory
- Ensure sufficient disk space

### Cannot Download Backup
**Problem**: Download fails or shows 404
**Solution**:
- Verify the backup file exists in `backups/` directory
- Check file permissions
- Ensure no special characters in filename

### Restore Fails
**Problem**: Database restore fails
**Solution**:
- Check database connection settings
- Verify backup file is not corrupted
- Ensure database user has necessary privileges
- Check for SQL syntax compatibility

### Permission Denied
**Problem**: "You do not have permission to access this module"
**Solution**:
- Verify you are logged in as an administrator
- Check `ion_auth` user permissions
- Contact system administrator

## File Locations

```
tapstemco/
├── application/
│   ├── controllers/
│   │   └── backup.php          # Backup controller
│   └── views/
│       └── backup/
│           └── index.php        # Backup management interface
├── backups/                     # Backup storage directory
│   ├── .htaccess               # Security configuration
│   └── index.html              # Prevents directory browsing
└── BACKUP_MODULE_README.md     # This file
```

## Technical Details

### Database Utility
Uses CodeIgniter's `dbutil` library for database operations:
- `$this->dbutil->backup()` - Creates SQL dump
- Exports complete database structure and data

### File Naming Convention
```
backup_YYYY-MM-DD_HH-MM-SS.sql
auto_backup_YYYY-MM-DD_HH-MM-SS.sql
```

### Supported Features
- ✅ Full database backup
- ✅ Complete data export (structure + data)
- ✅ MySQL/MySQLi support
- ✅ Compression-ready (can be added)
- ✅ Incremental backups (future enhancement)

## Future Enhancements

Potential improvements for future versions:
- [ ] Compression (ZIP/GZIP) for smaller file sizes
- [ ] Email notifications on backup completion
- [ ] Selective table backup
- [ ] Backup encryption
- [ ] Remote backup storage (FTP, S3, Google Drive)
- [ ] Backup verification/integrity checks
- [ ] Backup scheduling from web interface
- [ ] Differential/incremental backups

## Support

For issues or questions:
1. Check the activity log for error details
2. Verify file permissions on `backups/` directory
3. Check PHP error logs
4. Ensure database credentials are correct
5. Contact your system administrator

## Version History

- **v1.0** (December 2025) - Initial release
  - Basic backup/restore functionality
  - Web-based management interface
  - Admin-only access control
  - Activity logging integration

---

**Created**: December 22, 2025  
**Application**: TAPSTEMCO  
**Module**: Database Backup  
**Access Level**: Administrator Only
