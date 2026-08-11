<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cash Disbursement Module Installer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .status-icon {
            display: inline-flex;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            color: white;
            font-size: 12px;
        }
        .status-icon.success {
            background-color: #28a745;
        }
        .status-icon.error {
            background-color: #dc3545;
        }
        .status-text {
            color: #333;
            font-size: 14px;
        }
        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 30px;
        }
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-install:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-back {
            background: #e9ecef;
            color: #333;
        }
        .btn-back:hover {
            background: #dee2e6;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Courier New', monospace;
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .loading {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .text-center {
            text-align: center;
        }
        .info-text {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 Cash Disbursement Module</h1>
        <p class="subtitle">Installer for TAPSTEMCO Accounting System</p>

        <div id="content">
            <?php
            // Set error reporting
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            // Prevent direct PHP execution in framework
            define('BASEPATH', dirname(__FILE__) . '/');

            // Configuration
            $base_path = dirname(dirname(__FILE__));
            $config_file = $base_path . '/application/config/database.php';

            // Initialize variables
            $db_host = '';
            $db_user = '';
            $db_password = '';
            $db_name = 'tapstemco';
            $sql_file = $base_path . '/sql/cash_disbursement_module.sql';

            // Auto-detect database credentials from config file
            if (file_exists($config_file)) {
                $config_content = file_get_contents($config_file);
                
                // Extract database configuration using regex
                preg_match("/\['hostname'\]\s*=>\s*['\"]([^'\"]+)['\"]/", $config_content, $host_match);
                preg_match("/\['username'\]\s*=>\s*['\"]([^'\"]+)['\"]/", $config_content, $user_match);
                preg_match("/\['password'\]\s*=>\s*['\"]([^'\"]+)['\"]/", $config_content, $pass_match);
                preg_match("/\['database'\]\s*=>\s*['\"]([^'\"]+)['\"]/", $config_content, $db_match);

                if (!empty($host_match[1])) $db_host = $host_match[1];
                if (!empty($user_match[1])) $db_user = $user_match[1];
                if (!empty($pass_match[1])) $db_password = $pass_match[1];
                if (!empty($db_match[1])) $db_name = $db_match[1];
            }

            // Default fallback
            if (empty($db_host)) $db_host = 'localhost';
            if (empty($db_user)) $db_user = 'root';

            // Check if form was submitted
            $installation_complete = false;
            $installation_message = '';
            $installation_status = 'info';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $db_host = isset($_POST['db_host']) ? trim($_POST['db_host']) : $db_host;
                $db_user = isset($_POST['db_user']) ? trim($_POST['db_user']) : $db_user;
                $db_password = isset($_POST['db_password']) ? trim($_POST['db_password']) : '';
                $db_name = isset($_POST['db_name']) ? trim($_POST['db_name']) : $db_name;

                try {
                    // Create connection
                    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);

                    // Check connection
                    if ($mysqli->connect_error) {
                        throw new Exception('Connection failed: ' . $mysqli->connect_error);
                    }

                    // Read SQL file
                    if (!file_exists($sql_file)) {
                        throw new Exception('SQL file not found: ' . $sql_file);
                    }

                    $sql_content = file_get_contents($sql_file);

                    // Split SQL statements
                    $statements = array_filter(array_map('trim', explode(';', $sql_content)));

                    $executed = 0;
                    $errors = array();

                    // Execute each statement
                    foreach ($statements as $statement) {
                        if (!empty($statement) && strpos(trim($statement), '--') !== 0) {
                            if ($mysqli->query($statement)) {
                                $executed++;
                            } else {
                                $errors[] = $mysqli->error;
                            }
                        }
                    }

                    $mysqli->close();

                    if (count($errors) === 0) {
                        $installation_complete = true;
                        $installation_status = 'success';
                        $installation_message = 'Cash Disbursement Module installed successfully! ' . $executed . ' database objects created.';
                    } else {
                        $installation_status = 'error';
                        $installation_message = 'Installation completed with warnings. ' . count($errors) . ' errors occurred.';
                    }

                } catch (Exception $e) {
                    $installation_status = 'error';
                    $installation_message = 'Installation failed: ' . $e->getMessage();
                }
            }
            ?>

            <?php if (!empty($installation_message)): ?>
            <div class="alert alert-<?php echo $installation_status; ?>">
                <?php echo htmlspecialchars($installation_message); ?>
            </div>
            <?php endif; ?>

            <?php if (!$installation_complete): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="db_host">Database Host:</label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
                    <p class="info-text">Usually "localhost" for local development</p>
                </div>

                <div class="form-group">
                    <label for="db_user">Database Username:</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
                    <p class="info-text">MySQL user with database privileges</p>
                </div>

                <div class="form-group">
                    <label for="db_password">Database Password:</label>
                    <input type="password" id="db_password" name="db_password" value="<?php echo htmlspecialchars($db_password); ?>">
                    <p class="info-text">Leave empty if no password is set</p>
                </div>

                <div class="form-group">
                    <label for="db_name">Database Name:</label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
                    <p class="info-text">The TAPSTEMCO database name</p>
                </div>

                <div class="button-group">
                    <button type="button" class="btn-back" onclick="history.back()">← Back</button>
                    <button type="submit" class="btn-install">Install Module</button>
                </div>
            </form>
            <?php else: ?>
            <div class="status-box">
                <div class="status-item">
                    <div class="status-icon success">✓</div>
                    <span class="status-text">Cash Disbursement Module tables created</span>
                </div>
                <div class="status-item">
                    <div class="status-icon success">✓</div>
                    <span class="status-text">Journal entry integration ready</span>
                </div>
                <div class="status-item">
                    <div class="status-icon success">✓</div>
                    <span class="status-text">Database setup completed successfully</span>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Next Steps:</strong><br>
                1. Assign permissions to user roles (View_cash_disbursement, Create_cash_disbursement, Edit_cash_disbursement, Delete_cash_disbursement)<br>
                2. Access the module from Finance → Cash Disbursement List<br>
                3. Create your first cash disbursement record
            </div>

            <div class="button-group">
                <button type="button" class="btn-back" onclick="window.location.href='/'">← Go to Dashboard</button>
                <button type="button" class="btn-back" onclick="location.reload()">↻ Install Another</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
