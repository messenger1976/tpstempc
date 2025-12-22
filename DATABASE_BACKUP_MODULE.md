# Database Backup Module Documentation

## Overview
The Database Backup Module provides comprehensive database backup and restore functionality for the TAPSTEMCO system. This module allows administrators to create, download, restore, and manage database backups.

## Features
- **Create Backups**: Generate compressed ZIP backups of the entire database
- **Download Backups**: Download backup files for offline storage
- **Restore Database**: Restore the database from a backup file
- **Delete Backups**: Remove old or unnecessary backup files
- **Security**: Admin-only access with protected backup directory

## Access Requirements
- **User Role**: Administrator only
- **Authentication**: User must be logged in
- **Authorization**: Checked via `$this->ion_auth->is_admin()`

## File Structure
```
application/
├── controllers/
│   └── backup.php                          # Backup controller
├── models/
│   └── Backup_model.php                    # Backup model with DB operations
└── views/
    └── backup/
        ├── backup_list.php                 # Main backup management page
        └── backup_restore_confirm.php      # Restore confirmation page

backups/                                    # Backup storage directory
├── .htaccess                               # Access protection
└── index.html                              # Directory listing protection
```

## Usage

### Access the Backup Module
Navigate to: `Settings > Database Backup` from the main menu

Or access directly: `/[language]/backup/index`

### Creating a Backup
1. Click the "Create New Backup" button
2. The system will create a timestamped backup file in ZIP format
3. The backup file will be saved in the `/backups/` directory

### Downloading a Backup
1. Click the "Download" button next to the desired backup
2. The file will be downloaded to your local machine

### Restoring a Backup
1. Click the "Restore" button next to the desired backup
2. Confirm the restoration (warning: this will overwrite current data)
3. The database will be restored from the selected backup

### Deleting a Backup
1. Click the "Delete" button next to the desired backup
2. Confirm the deletion
3. The backup file will be permanently removed

## Security Features

### Access Control
- Only administrators can access the backup module
- Non-admin users receive a 403 Forbidden error
- All backup operations require authentication

### Directory Protection
- `/backups/` directory is protected with `.htaccess`
- Direct web access to backup files is denied
- Directory listing is disabled with `index.html`

### Backup File Naming
Backup files are named with timestamps:
```
backup_YYYY-MM-DD_HH-ii-ss.zip
```
Example: `backup_2024-12-22_14-30-45.zip`

## Technical Details

### Backup Format
- **Format**: ZIP compressed archive
- **Contents**: SQL dump of entire database
- **Compression**: Automatic via CodeIgniter's DBUtil library

### Model Methods
- `create_backup()`: Creates a new database backup
- `get_backup_list()`: Returns array of all backup files
- `delete_backup($filename)`: Deletes specified backup file
- `download_backup($filename)`: Initiates file download
- `restore_backup($filename)`: Restores database from backup

### Controller Actions
- `index()`: Display backup list
- `create_backup()`: Create new backup
- `download($filename)`: Download backup file
- `delete($filename)`: Delete backup file
- `restore($filename)`: Restore from backup

## Best Practices

### Regular Backups
- Create backups before major system changes
- Schedule regular backups (daily, weekly, etc.)
- Keep multiple backup versions

### Storage Management
- Download important backups to offline storage
- Regularly clean up old backup files
- Monitor backup directory disk space

### Before Restore
- **Always create a current backup** before restoring an old one
- Verify the backup file is not corrupted
- Confirm the backup date matches your requirements

## Troubleshooting

### Backup Creation Fails
- Check write permissions on `/backups/` directory
- Ensure sufficient disk space
- Verify database connection

### Restore Fails
- Check the backup file is not corrupted
- Verify backup file is in correct format
- Ensure sufficient database permissions

### Cannot Access Backup Module
- Verify you are logged in as administrator
- Check ion_auth configuration
- Verify menu permissions

## Menu Integration
The backup module is integrated into the Settings menu:
```
Settings
  └── Database Backup  (Admin only)
```

The menu item appears only for administrators and is located in the Settings submenu after Activity Logs.
