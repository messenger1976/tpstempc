<?php

/**
 * Test Logging Controller
 * 
 * Temporary controller to test automatic database logging
 * Delete after testing
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Test_logging extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * Test automatic logging
     */
    function index() {
        echo "<h2>Automatic Database Logging Test</h2>";
        echo "<hr>";
        
        // Check driver class
        echo "<h3>1. Database Driver Class</h3>";
        $driver_class = get_class($this->db);
        echo "Driver: <strong>" . $driver_class . "</strong><br>";
        
        if ($driver_class == 'MY_DB_mysqli_driver') {
            echo "<span style='color:green;'>✅ Extended driver is loaded!</span><br><br>";
        } else {
            echo "<span style='color:red;'>❌ Extended driver NOT loaded. Current: " . $driver_class . "</span><br>";
            echo "<p><strong>Problem:</strong> The extended driver is not being used. Check:</p>";
            echo "<ul>";
            echo "<li>File exists: application/core/MY_DB_mysqli_driver.php</li>";
            echo "<li>Run clear_cache.php</li>";
            echo "<li>Restart Apache/PHP-FPM</li>";
            echo "</ul>";
            echo "<hr>";
            return;
        }
        
        // Check activity_logs table
        echo "<h3>2. Activity Logs Table</h3>";
        if (!$this->db->table_exists('activity_logs')) {
            echo "<span style='color:red;'>❌ activity_logs table does not exist!</span><br>";
            return;
        }
        echo "<span style='color:green;'>✅ activity_logs table exists</span><br><br>";
        
        // Get count before
        $before_count = $this->db->count_all('activity_logs');
        echo "Current log count: <strong>" . $before_count . "</strong><br><br>";
        
        // Test INSERT
        echo "<h3>3. Testing INSERT Operation</h3>";
        echo "Performing test INSERT...<br>";
        
        // Create a test table if it doesn't exist, or use an existing one
        // Let's try to insert into a simple table or create a test entry
        $test_data = array(
            'test_field' => 'test_value_' . time(),
            'created_at' => date('Y-m-d H:i:s')
        );
        
        // Try inserting into a common table (adjust if needed)
        // If members table exists, use it, otherwise skip this test
        if ($this->db->table_exists('members')) {
            // We'll just check if logging triggers
            echo "Note: We'll check if logging happens when we perform a real operation.<br>";
        }
        
        // Check logs after (simulate)
        echo "<h3>4. Next Steps</h3>";
        echo "<p>To test automatic logging:</p>";
        echo "<ol>";
        echo "<li>Perform any INSERT/UPDATE/DELETE operation in your application</li>";
        echo "<li>Check the activity_logs table:</li>";
        echo "<pre>SELECT * FROM activity_logs ORDER BY id DESC LIMIT 10;</pre>";
        echo "<li>Or check the dashboard - Recent Activities widget</li>";
        echo "</ol>";
        
        // Show recent logs
        echo "<h3>5. Recent Activity Logs (Last 5)</h3>";
        $this->db->order_by('id', 'DESC');
        $this->db->limit(5);
        $recent = $this->db->get('activity_logs');
        
        if ($recent && $recent->num_rows() > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
            echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Action</th><th>Module</th><th>Description</th><th>User</th><th>Date</th></tr>";
            foreach ($recent->result() as $log) {
                echo "<tr>";
                echo "<td>" . $log->id . "</td>";
                echo "<td>" . htmlspecialchars($log->action) . "</td>";
                echo "<td>" . htmlspecialchars($log->module) . "</td>";
                echo "<td>" . htmlspecialchars(substr($log->description, 0, 40)) . "...</td>";
                echo "<td>" . htmlspecialchars($log->username) . "</td>";
                echo "<td>" . $log->created_at . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:orange;'>No logs found. Perform a database operation and check again.</p>";
        }
        
        echo "<hr>";
        echo "<p><strong>Security:</strong> Delete this controller file after testing!</p>";
        echo "<p>Location: application/controllers/Test_logging.php</p>";
    }
    
    /**
     * Perform actual test insert
     */
    function test_insert() {
        if (!$this->ion_auth->logged_in()) {
            echo "Please log in first.";
            return;
        }
        
        echo "<h2>Test Automatic Logging - INSERT Operation</h2>";
        
        // Check driver
        $driver = get_class($this->db);
        echo "Driver: " . $driver . "<br>";
        
        if ($driver != 'MY_DB_mysqli_driver') {
            echo "<span style='color:red;'>Extended driver not loaded!</span>";
            return;
        }
        
        // Get log count before
        $before = $this->db->count_all('activity_logs');
        echo "Logs before: " . $before . "<br>";
        
        // Perform test operation - create a temporary test table entry
        // Or use an existing table
        if ($this->db->table_exists('members')) {
            $test_data = array(
                'firstname' => 'Test',
                'lastname' => 'Auto Logging',
                'created_at' => date('Y-m-d H:i:s')
            );
            
            echo "Attempting INSERT into members table...<br>";
            $result = $this->db->insert('members', $test_data);
            
            if ($result) {
                echo "<span style='color:green;'>✅ INSERT successful</span><br>";
                
                // Get new ID
                $new_id = $this->db->insert_id();
                
                // Delete test record
                $this->db->delete('members', array('id' => $new_id));
                
                // Check logs
                $after = $this->db->count_all('activity_logs');
                echo "Logs after: " . $after . "<br>";
                
                if ($after > $before) {
                    echo "<span style='color:green;'>✅ Automatic logging is working! New log created.</span><br>";
                } else {
                    echo "<span style='color:red;'>❌ No new log was created. Check the troubleshooting guide.</span><br>";
                }
                
                // Show latest log
                $this->db->order_by('id', 'DESC');
                $this->db->limit(1);
                $latest = $this->db->get('activity_logs')->row();
                if ($latest) {
                    echo "<h3>Latest Log Entry:</h3>";
                    echo "Action: " . $latest->action . "<br>";
                    echo "Description: " . $latest->description . "<br>";
                    echo "Time: " . $latest->created_at . "<br>";
                }
            } else {
                echo "<span style='color:red;'>INSERT failed</span><br>";
            }
        } else {
            echo "No suitable test table found. Try performing a real database operation in your application.";
        }
    }
}

