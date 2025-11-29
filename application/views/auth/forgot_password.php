<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>TAPSTEMCO | Forgot Password</title>

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

        .forgot-password-container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            animation: fadeInDown 0.6s ease-out;
        }

        .forgot-password-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 40px 35px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .forgot-password-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25), 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .icon-section {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
        }

        .icon-section .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            animation: pulse 2s infinite;
        }

        .icon-section .icon-wrapper i {
            font-size: 36px;
            color: #fff;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .page-title {
            color: #333;
            font-size: 26px;
            font-weight: 600;
            margin: 0 0 10px 0;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .page-description {
            color: #666;
            font-size: 14px;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 30px;
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

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
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
            margin-bottom: 20px;
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

        .input-group input {
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            height: auto;
            width: 100%;
        }

        .input-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .input-group:focus-within .input-group-addon {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            cursor: pointer;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .back-to-login-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
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
            .forgot-password-card {
                padding: 30px 25px;
                border-radius: 15px;
            }

            .page-title {
                font-size: 22px;
            }

            .icon-section .icon-wrapper {
                width: 70px;
                height: 70px;
            }

            .icon-section .icon-wrapper i {
                font-size: 30px;
            }

            .input-group input,
            .input-group-addon {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn-submit {
                padding: 12px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .forgot-password-card {
                padding: 25px 20px;
            }

            .icon-section {
                margin-bottom: 20px;
                padding-bottom: 20px;
            }

            .icon-section .icon-wrapper {
                width: 60px;
                height: 60px;
                margin-bottom: 15px;
            }

            .icon-section .icon-wrapper i {
                font-size: 26px;
            }

            .page-title {
                font-size: 20px;
            }

            .page-description {
                font-size: 13px;
            }
        }

        @media (max-width: 320px) {
            .forgot-password-card {
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
    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="icon-section">
                <div class="icon-wrapper">
                    <i class="fa fa-key" aria-hidden="true"></i>
                </div>
                <h2 class="page-title">Forgot Password</h2>
                <p class="page-description">
                    <?php if (isset($identity_label)): ?>
                        Enter your <?php echo strtolower($identity_label); ?> and we'll send you a link to reset your password.
                    <?php else: ?>
                        Enter your email address and your password will be reset and emailed to you.
                    <?php endif; ?>
                </p>
            </div>

            <?php
            if (isset($message) && !empty($message)) {
                if (strpos($message, 'success') !== false || strpos($message, 'sent') !== false) {
                    echo '<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $message . '</div>';
                } else {
                    echo '<div class="alert alert-info alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $message . '</div>';
                }
            }
            if ($this->session->flashdata('message') != '') {
                $flash_msg = $this->session->flashdata('message');
                if (strpos($flash_msg, 'success') !== false || strpos($flash_msg, 'sent') !== false) {
                    echo '<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $flash_msg . '</div>';
                } else {
                    echo '<div class="alert alert-info alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $flash_msg . '</div>';
                }
            }
            ?>
            
            <?php echo form_open('auth/forgot_password', 'role="form"'); ?>              

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-<?php echo (isset($identity_label) && strpos(strtolower($identity_label), 'username') !== false) ? 'user' : 'envelope'; ?>" aria-hidden="true"></i>
                    </span>
                    <?php 
                    $input_attrs = array(
                        'type' => (isset($identity_label) && strpos(strtolower($identity_label), 'username') !== false) ? 'text' : 'email',
                        'name' => 'email',
                        'id' => 'email',
                        'class' => 'form-control',
                        'placeholder' => isset($identity_label) ? $identity_label : 'Email address',
                        'value' => set_value('email'),
                        'required' => 'required'
                    );
                    echo form_input($input_attrs);
                    ?>
                </div>
                <?php echo form_error('email'); ?>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa fa-paper-plane" aria-hidden="true"></i> Send Reset Link
            </button>

            <div class="back-to-login-section">
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
