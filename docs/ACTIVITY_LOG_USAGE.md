# Activity Log Module - Usage Guide

This module logs all user activities including login, logout, and CRUD operations (create, update, delete, view).

## Installation

1. **Run the SQL migration:**
   ```sql
   -- Execute the SQL file to create the activity_logs table
   sql/activity_logs.sql
   ```

2. **The module is already integrated and ready to use!**

## Features

- ✅ Automatic login/logout logging
- ✅ Manual logging for CRUD operations
- ✅ View activity logs with filters
- ✅ Export logs to CSV
- ✅ Statistics dashboard
- ✅ Delete old logs (admin only)

## Automatic Logging

### Login/Logout
Login and logout activities are automatically logged. No additional code needed.

## Manual Logging in Controllers

To log activities in your controllers, use the helper functions:

### 1. Load the Helper
```php
$this->load->helper('activity_log');
```

### 2. Log Create Operations
```php
// After successfully creating a record
$member_id = $this->member_model->add_member($data);
if ($member_id) {
    log_create('member', $member_id, 'member', 'Created new member: ' . $data['firstname']);
    // ... rest of your code
}
```

### 3. Log Update Operations
```php
// After successfully updating a record
if ($this->member_model->edit_member($data, $id)) {
    log_update('member', $id, 'member', 'Updated member information');
    // ... rest of your code
}
```

### 4. Log Delete Operations
```php
// After successfully deleting a record
if ($this->db->delete('members', array('id' => $id))) {
    log_delete('member', $id, 'member', 'Deleted member');
    // ... rest of your code
}
```

### 5. Log View Operations
```php
// When viewing a record detail page
function view_member($id) {
    log_view('member', $id, 'member', 'Viewed member details');
    // ... rest of your code
}
```

### 6. Generic Log Function
```php
// For custom activities
log_activity('action_name', 'module_name', 'Description', $record_id, 'record_type');
```

## Example Integration

Here's an example of how to integrate logging into the member controller:

```php
function new_member() {
    // ... validation code ...
    
    if ($this->form_validation->run() == TRUE) {
        $this->load->helper('activity_log');
        
        $return = $this->member_model->add_member($new_member, $registrationfee);
        if ($return) {
            // Log the creation
            log_create('member', $return, 'member', 
                'Created new member: ' . $new_member['firstname'] . ' ' . $new_member['lastname']);
            
            $this->session->set_flashdata('message', lang('member_create_success'));
            redirect(current_lang() . '/member/memberinfo/' . encode_id($return), 'refresh');
        }
    }
}

function edit_memberinfo($id) {
    $id = decode_id($id);
    $this->load->helper('activity_log');
    
    if ($this->form_validation->run() == TRUE) {
        $return = $this->member_model->edit_member($edit_member, $id);
        if ($return) {
            // Log the update
            log_update('member', $id, 'member', 'Updated member information');
            
            $this->session->set_flashdata('message', lang('member_edited_success'));
            redirect(current_lang() . '/member/memberinfo/' . encode_id($id), 'refresh');
    }
}

function deactivate($id) {
    $id = decode_id($id);
    $this->load->helper('activity_log');
    
    $this->db->update('members', array('status' => 0), array('id' => $id));
    
    // Log the action
    log_activity('deactivate', 'member', 'Deactivated member', $id, 'member');
    
    $this->session->set_flashdata('message', lang('member_deactivated'));
    redirect(current_lang() . '/member/member_list', 'refresh');
}
```

## Viewing Activity Logs

1. Navigate to: `http://yoursite.com/activity_log` or `http://yoursite.com/en/activity_log`
2. Use filters to search by:
   - User
   - Module
   - Action type
   - Date range
   - Search text
3. Click "Export CSV" to download logs
4. Click "View" on any log entry to see details

## Available Helper Functions

- `log_activity($action, $module, $description, $record_id, $record_type)` - Generic logging
- `log_login($user_id, $username)` - Log user login (automatic)
- `log_logout($user_id, $username)` - Log user logout (automatic)
- `log_create($module, $record_id, $record_type, $description)` - Log record creation
- `log_update($module, $record_id, $record_type, $description)` - Log record update
- `log_delete($module, $record_id, $record_type, $description)` - Log record deletion
- `log_view($module, $record_id, $record_type, $description)` - Log record view

## Database Table Structure

The `activity_logs` table contains:
- `id` - Primary key
- `user_id` - User who performed the action
- `username` - Username
- `module` - Module/Controller name
- `action` - Action type (login, logout, create, update, delete, view)
- `description` - Description of the activity
- `record_id` - ID of the record being acted upon
- `record_type` - Type of record
- `ip_address` - User's IP address
- `user_agent` - Browser user agent
- `created_at` - Timestamp

## Notes

- All logs are automatically timestamped
- IP address and user agent are automatically captured
- The helper functions handle user detection automatically
- Module name is auto-detected if not provided
- Logs can be filtered and exported for reporting

