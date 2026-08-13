<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <?php
    $company = function_exists('company_info_detail') ? company_info_detail() : null;
    $company_name = ($company && isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative';
    $display_name = isset($name) && $name !== '' ? $name : $company_name;
    $display_logo = isset($logo) ? $logo : '';
    ?>
    <title><?php echo htmlspecialchars($company_name); ?> | Login</title>

    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

    <style type="text/css">
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body.login-page {
            font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            background:
                radial-gradient(ellipse at top left, rgba(26,179,148,0.35) 0%, transparent 45%),
                radial-gradient(ellipse at bottom right, rgba(23,160,133,0.28) 0%, transparent 40%),
                linear-gradient(160deg, #0f7665 0%, #1ab394 45%, #147a6a 100%);
        }

        body.login-page::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: fadeInDown 0.55s ease-out;
        }

        .login-card {
            background: #fff;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(8, 60, 50, 0.28);
            overflow: hidden;
            padding: 36px 32px 28px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 26px;
            padding-bottom: 22px;
            border-bottom: 1px solid #eef1f2;
        }

        .logo-section img {
            max-width: 112px;
            height: auto;
            margin-bottom: 12px;
        }

        .company-name {
            color: #2f4050;
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: -0.3px;
        }

        .login-subtitle {
            margin: 0;
            font-size: 13px;
            color: #888;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 11px 14px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 600;
        }

        .alert-success {
            background: #e8f8f5;
            color: #0e7c69;
            border: 1px solid #c9ebe3;
        }

        .alert-danger {
            background: #fdeceb;
            color: #c0392b;
            border: 1px solid #f5c6cb;
        }

        div.error_message {
            color: #c0392b;
            margin: 6px 0 0;
            padding: 8px 12px;
            background: #fdeceb;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            font-size: 13px;
            display: block;
        }

        .form-group { margin-bottom: 16px; }

        .input-group-addon {
            background: #fafbfc;
            border: 1px solid #e5e6e7;
            border-right: none;
            border-radius: 8px 0 0 8px;
            padding: 11px 14px;
            color: #1ab394;
            font-size: 15px;
        }

        .input-group input.form-control {
            border: 1px solid #e5e6e7;
            border-left: none;
            border-radius: 0 8px 8px 0;
            padding: 11px 14px;
            font-size: 14px;
            height: auto;
            box-shadow: none;
        }

        .input-group input.form-control:focus {
            border-color: #1ab394;
            box-shadow: none;
            outline: none;
        }

        .input-group:focus-within .input-group-addon {
            border-color: #1ab394;
            background: #e8f8f5;
        }

        .input-group:focus-within input.form-control {
            border-color: #1ab394;
        }

        .remember-me-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember-me-section .i-checks { margin: 0; }

        .remember-me-section label {
            font-size: 13px;
            color: #676a6c;
            font-weight: 500;
            cursor: pointer;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .remember-me-section .i-checks input { margin-right: 8px; }

        .forgot-password-link {
            color: #1ab394;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .forgot-password-link:hover {
            color: #147a6a;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            font-size: 15px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            background: #1ab394;
            color: #fff;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background .15s ease, box-shadow .15s ease;
            box-shadow: 0 4px 14px rgba(26, 179, 148, 0.35);
        }

        .btn-login:hover {
            background: #18a689;
            color: #fff;
            box-shadow: 0 6px 18px rgba(26, 179, 148, 0.4);
        }

        .btn-login:active { background: #147a6a; }

        .register-section {
            text-align: center;
            margin-top: 22px;
            padding-top: 20px;
            border-top: 1px solid #eef1f2;
        }

        .register-section p {
            color: #888;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .btn-register {
            display: inline-block;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid #1ab394;
            background: transparent;
            color: #1ab394;
            text-decoration: none;
            width: 100%;
            transition: background .15s ease, color .15s ease;
        }

        .btn-register:hover {
            background: #e8f8f5;
            color: #147a6a;
            text-decoration: none;
        }

        .copyright {
            text-align: center;
            margin-top: 22px;
            color: rgba(255, 255, 255, 0.88);
            font-size: 12px;
            position: relative;
            z-index: 1;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-card { padding: 28px 20px 22px; }
            .company-name { font-size: 18px; }
            .logo-section img { max-width: 96px; }
            .remember-me-section {
                flex-direction: column;
                align-items: flex-start;
            }
            .forgot-password-link { align-self: flex-end; }
        }
    </style>
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <?php if (!empty($display_logo)) { ?>
                    <img src="<?php echo base_url(); ?>logo/<?php echo htmlspecialchars($display_logo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?>"/>
                <?php } ?>
                <h1 class="company-name"><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="login-subtitle">Sign in to continue</p>
            </div>

            <?php
            if (isset($message) && !empty($message)) {
                echo '<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $message . '</div>';
            } else if ($this->session->flashdata('message') != '') {
                echo '<div class="alert alert-success alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $this->session->flashdata('message') . '</div>';
            } else if (isset($warning) && !empty($warning)) {
                echo '<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $warning . '</div>';
            } else if ($this->session->flashdata('warning') != '') {
                echo '<div class="alert alert-danger alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $this->session->flashdata('warning') . '</div>';
            }
            ?>

            <?php echo form_open('auth/login', 'role="form"'); ?>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user" aria-hidden="true"></i></span>
                    <input type="text" name="identity" class="form-control" placeholder="Username" autocomplete="username"
                           value="<?php echo htmlspecialchars(set_value('identity') ? set_value('identity') : (isset($identity_cookie_name) ? get_cookie($identity_cookie_name) : ''), ENT_QUOTES, 'UTF-8'); ?>"
                           required>
                </div>
                <?php echo form_error('identity'); ?>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
                </div>
                <?php echo form_error('password'); ?>
            </div>

            <div class="remember-me-section">
                <div class="i-checks">
                    <label>
                        <input type="checkbox" name="remember" value="1" <?php echo (isset($identity_cookie_name) && isset($remember_cookie_name) && get_cookie($identity_cookie_name) && get_cookie($remember_cookie_name)) ? 'checked' : ''; ?>>
                        <i></i> Remember me
                    </label>
                </div>
                <a href="<?php echo base_url(); ?>auth/forgot_password" class="forgot-password-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">Login</button>

            <div class="register-section">
                <p>Do not have an account?</p>
                <a class="btn-register" href="<?php echo base_url(); ?>auth/register">Create an account</a>
            </div>

            <?php echo form_close(); ?>
        </div>

        <p class="copyright">Copyright &copy; <?php echo date('Y'); ?> - Bohollander IT Solutions</p>
    </div>

    <script src="<?php echo base_url(); ?>assets/js/jquery-3.1.1.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/iCheck/icheck.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });
        });
    </script>
</body>

</html>
