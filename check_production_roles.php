<?php
/**
 * Check if roles exist in production database
 * Run this on production server to verify roles are present
 * SECURITY: Delete after use!
 */

// Database configuration - UPDATE for production
$db_config = array(
    'hostname' => 'localhost', // Update if production DB is on different host
    'username' => 'your_db_user', // Update with production DB username
    'password' => 'your_db_pass', // Update with production DB password
    'database' => 'tapstemco' // Update if production DB name is different
);

// Try to get config from CodeIgniter config file
$config_file = __DIR__ . '/application/config/database.php';
if (file_exists($config_file)) {
    // Read config without loading CodeIgniter
    $config_content = file_get_contents($config_file);
    // Extract database config (simple parsing)
    if (preg_match("/\['default'\]\['hostname'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_config['hostname'] = $matches[1];
    }
    if (preg_match("/\['default'\]\['username'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_config['username'] = $matches[1];
    }
    if (preg_match("/\['default'\]\['password'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_config['password'] = $matches[1];
    }
    if (preg_match("/\['default'\]\['database'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $db_config['database'] = $matches[1];
    }
}

// Connect to database
try {
    $conn = new mysqli($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>Production Database Role Check</h2>";
    echo "<style>
        body{font-family:Arial;margin:20px;background:#f5f5f5;}
        .success{color:green;font-weight:bold;}
        .error{color:red;font-weight:bold;}
        .warning{color:orange;font-weight:bold;}
        table{border-collapse:collapse;width:100%;margin:10px 0;background:white;}
        th,td{border:1px solid #ddd;padding:8px;text-align:left;}
        th{background-color:#4CAF50;color:white;}
        .section{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    </style>";
    
    // Step 1: Check Module 3
    echo "<div class='section'>";
    echo "<h3>Step 1: Check Module 3 (Savings)</h3>";
    $result = $conn->query("SELECT * FROM module WHERE id = 3");
    if ($result && $result->num_rows > 0) {
        $module = $result->fetch_assoc();
        echo "<p class='success'>✅ Module 3 exists: {$module['Name']}</p>";
    } else {
        echo "<p class='error'>❌ Module 3 does NOT exist!</p>";
    }
    echo "</div>";
    
    // Step 2: Check existing roles
    echo "<div class='section'>";
    echo "<h3>Step 2: Check Existing Roles for Module 3</h3>";
    $result = $conn->query("SELECT * FROM role WHERE Module_id = 3 ORDER BY Name");
    echo "<table><tr><th>ID</th><th>Name</th></tr>";
    
    $saving_list_exists = false;
    $edit_exists = false;
    
    if ($result && $result->num_rows > 0) {
        while ($role = $result->fetch_assoc()) {
            $highlight = ($role['Name'] == 'saving_account_list' || $role['Name'] == 'Edit_saving_account') ? "style='background:#fff3cd;'" : "";
            echo "<tr $highlight><td>{$role['id']}</td><td>{$role['Name']}</td></tr>";
            if ($role['Name'] == 'saving_account_list') $saving_list_exists = true;
            if ($role['Name'] == 'Edit_saving_account') $edit_exists = true;
        }
    } else {
        echo "<tr><td colspan='2'>No roles found for Module 3</td></tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Step 3: Add missing roles
    echo "<div class='section'>";
    echo "<h3>Step 3: Add Missing Roles</h3>";
    
    if (!$saving_list_exists) {
        $result = $conn->query("INSERT INTO role (Module_id, Name) VALUES (3, 'saving_account_list')");
        if ($result) {
            echo "<p class='success'>✅ Added 'saving_account_list' role</p>";
        } else {
            echo "<p class='error'>❌ Failed to add 'saving_account_list': " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='success'>✅ 'saving_account_list' already exists</p>";
    }
    
    if (!$edit_exists) {
        $result = $conn->query("INSERT INTO role (Module_id, Name) VALUES (3, 'Edit_saving_account')");
        if ($result) {
            echo "<p class='success'>✅ Added 'Edit_saving_account' role</p>";
        } else {
            echo "<p class='error'>❌ Failed to add 'Edit_saving_account': " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='success'>✅ 'Edit_saving_account' already exists</p>";
    }
    echo "</div>";
    
    // Step 4: Verify
    echo "<div class='section'>";
    echo "<h3>Step 4: Verification</h3>";
    $result = $conn->query("SELECT * FROM role WHERE Module_id = 3 AND Name IN ('saving_account_list', 'Edit_saving_account')");
    if ($result && $result->num_rows >= 2) {
        echo "<p class='success'>✅ Both roles verified in database!</p>";
        echo "<table><tr><th>ID</th><th>Module_id</th><th>Name</th></tr>";
        while ($role = $result->fetch_assoc()) {
            echo "<tr><td>{$role['id']}</td><td>{$role['Module_id']}</td><td class='success'>{$role['Name']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Roles not found after insert attempt</p>";
    }
    echo "</div>";
    
    echo "<hr>";
    echo "<p><strong>⚠️ SECURITY: Delete this file (check_production_roles.php) after use!</strong></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check database connection settings.</p>";
}
?>

