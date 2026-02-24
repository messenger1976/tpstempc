# Troubleshooting: Activity Log Model Not Updating on Server

## Common Issues and Solutions

### 1. **PHP OPcache is Caching the Old File**

PHP OPcache stores compiled PHP files in memory. Even after uploading a new file, the server may still use the cached version.

#### Solution A: Clear OPcache via PHP file (Recommended)
Create a file called `clear_cache.php` in your root directory:

```php
<?php
// clear_cache.php - Run this once after uploading files
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache is not enabled.";
}
```

**Steps:**
1. Upload `clear_cache.php` to your server root
2. Visit: `http://yoursite.com/clear_cache.php` in your browser
3. You should see "OPcache cleared successfully!"
4. **IMPORTANT:** Delete this file after use for security

#### Solution B: Clear OPcache via .htaccess (if you have server access)
Add this to your `.htaccess`:
```apache
php_value opcache.revalidate_freq 0
```

#### Solution C: Restart PHP-FPM/Apache (if you have server access)
```bash
# For PHP-FPM
sudo service php-fpm restart
# or
sudo systemctl restart php-fpm

# For Apache
sudo service apache2 restart
# or
sudo systemctl restart httpd
```

### 2. **Model Loading - Use Lowercase**

CodeIgniter expects model names in **lowercase** when loading. I've updated the dashboard controller to use lowercase:

```php
// ✅ Correct
$this->load->model('activity_log_model');

// ❌ Works but not recommended
$this->load->model('Activity_log_model');
```

### 3. **Verify File Upload**

After uploading, verify the file on the server:

1. **Check file exists:** Ensure `application/models/activity_log_model.php` exists on server
2. **Check file size:** Should match your local file size
3. **Check modification date:** Should be recent
4. **Check file permissions:** Should be readable (644 or 755)

### 4. **Verify the New Method Exists**

Add this temporary debug code to check if the method exists:

```php
// Add to dashboard controller temporarily
function index() {
    $this->load->model('activity_log_model');
    
    // Check if method exists
    if (method_exists($this->activity_log_model, 'get_recent_activities')) {
        echo "Method exists!<br>";
        $this->data['recent_activities'] = $this->activity_log_model->get_recent_activities(10);
    } else {
        echo "Method NOT found!<br>";
        // List all available methods
        echo "Available methods: ";
        print_r(get_class_methods($this->activity_log_model));
    }
    
    $this->data['content'] = 'dashboard';
    $this->load->view('dashboard', $this->data);
}
```

### 5. **Check CodeIgniter Cache**

CodeIgniter may cache model files. Delete these cache directories:

```
application/cache/*
system/cache/*
```

### 6. **File Permissions**

Ensure proper file permissions:
```bash
chmod 644 application/models/activity_log_model.php
chmod 755 application/models/
```

### 7. **Verify Database Table Exists**

Make sure the `activity_logs` table exists in your database. The model will fail silently if the table doesn't exist.

## Quick Checklist After Upload:

1. ✅ File uploaded: `application/models/activity_log_model.php`
2. ✅ Clear PHP OPcache (using clear_cache.php)
3. ✅ Verify file modification date is recent
4. ✅ Check file permissions (644)
5. ✅ Clear CodeIgniter cache folders
6. ✅ Test the dashboard page
7. ✅ Delete `clear_cache.php` after use

## Most Likely Solution:

**90% of the time, it's PHP OPcache.** Run the `clear_cache.php` script after uploading any PHP file changes.

