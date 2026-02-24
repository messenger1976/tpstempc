<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <?php $company = function_exists('company_info_detail') ? company_info_detail() : null; $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative'; ?>
    <title><?php echo htmlspecialchars($company_name); ?> | Create Account</title>

    <link href="<?php echo base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/css/style.css" rel="stylesheet">
    
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.05"><circle cx="30" cy="30" r="4"/></g></svg>') repeat;
            opacity: 0.5;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            animation: fadeInDown 0.6s ease-out;
        }

        .register-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 40px 35px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25), 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .register-card::-webkit-scrollbar {
            width: 6px;
        }

        .register-card::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .register-card::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
        }

        .logo-section .icon-wrapper {
            width: 70px;
            height: 70px;
            margin: 0 auto 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .logo-section .icon-wrapper i {
            font-size: 32px;
            color: #fff;
        }

        .page-title {
            color: #333;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .page-description {
            color: #666;
            font-size: 13px;
            text-align: center;
            margin-top: 8px;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.4s ease-out;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger,
        .alert-warning {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        div.error_message {
            color: #dc3545;
            margin: 5px 0 0 0;
            padding: 8px 12px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            font-size: 13px;
            display: block;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 18px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .input-group {
            position: relative;
            margin-bottom: 0;
        }

        .input-group-addon {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 12px 15px;
            color: #667eea;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input,
        .input-group select {
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            height: auto;
            width: 100%;
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .input-group:focus-within .input-group-addon {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            margin-top: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            cursor: pointer;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .back-to-login-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .back-to-login-section p {
            color: #888;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .btn-back-login {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-back-login:hover {
            background: #667eea;
            color: #fff;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .copyright {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            position: relative;
            z-index: 1;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-card {
                padding: 30px 25px;
                border-radius: 15px;
            }

            .page-title {
                font-size: 22px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .input-group input,
            .input-group-addon {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn-register {
                padding: 12px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .register-card {
                padding: 25px 20px;
            }

            .logo-section {
                margin-bottom: 20px;
                padding-bottom: 20px;
            }

            .logo-section .icon-wrapper {
                width: 60px;
                height: 60px;
                margin-bottom: 12px;
            }

            .logo-section .icon-wrapper i {
                font-size: 28px;
            }

            .page-title {
                font-size: 20px;
            }

            .page-description {
                font-size: 12px;
            }
        }

        @media (max-width: 320px) {
            .register-card {
                padding: 20px 15px;
            }

            .input-group input,
            .input-group-addon {
                padding: 8px 10px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-card">
            <div class="logo-section">
                <div class="icon-wrapper">
                    <i class="fa fa-user-plus" aria-hidden="true"></i>
                </div>
                <h2 class="page-title">Create Account</h2>
                <p class="page-description">Fill in your information to get started</p>
            </div>

            <?php
            if (isset($message) && !empty($message)) {
                if (strpos($message, 'success') !== false || strpos($message, 'created') !== false) {
                    echo '<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $message . '</div>';
                } else {
                    echo '<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $message . '</div>';
                }
            }
            if ($this->session->flashdata('message') != '') {
                $flash_msg = $this->session->flashdata('message');
                if (strpos($flash_msg, 'success') !== false || strpos($flash_msg, 'created') !== false) {
                    echo '<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $flash_msg . '</div>';
                } else {
                    echo '<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $flash_msg . '</div>';
                }
            }
            if (isset($warning) && !empty($warning)) {
                echo '<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $warning . '</div>';
            } else if ($this->session->flashdata('warning') != '') {
                echo '<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $this->session->flashdata('warning') . '</div>';
            }
            ?>
            
            <?php echo form_open('auth/register', 'role="form"'); ?>              

            <div class="form-row">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?php echo set_value('first_name'); ?>" required>
                    </div>
                    <?php echo form_error('first_name'); ?>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?php echo set_value('last_name'); ?>" required>
                    </div>
                    <?php echo form_error('last_name'); ?>
                </div>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php echo set_value('email'); ?>" required>
                </div>
                <?php echo form_error('email'); ?>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user-circle" aria-hidden="true"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo set_value('username'); ?>" required>
                </div>
                <?php echo form_error('username'); ?>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password (min <?php echo $this->config->item('min_password_length', 'ion_auth'); ?> characters)" required>
                </div>
                <?php echo form_error('password'); ?>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                    <input type="password" name="password_confirm" class="form-control" placeholder="Confirm Password" required>
                </div>
                <?php echo form_error('password_confirm'); ?>
            </div>

            <button type="submit" class="btn-register">
                <i class="fa fa-user-plus" aria-hidden="true"></i> Create Account
            </button>

            <div class="back-to-login-section">
                <p>Already have an account?</p>
                <a href="<?php echo base_url() ?>auth/login" class="btn-back-login">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Back to Login
                </a>
            </div>

            <?php echo form_close(); ?>
        </div>

        <p class="copyright">Copyright &copy; 2025 - Bohollander IT Solutions</p>
    </div>

    <!-- Mainly scripts -->
    <script src="<?php echo base_url() ?>assets/js/jquery-3.1.1.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/bootstrap.min.js"></script>
</body>

</html>

