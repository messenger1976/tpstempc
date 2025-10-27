<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TAPSTEMCO | Login</title>

    <link href="<?php echo base_url() ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/css/style.css" rel="stylesheet">
    <link href="<?php echo base_url() ?>assets/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">
    <style type="text/css">
            div.error_message{
                color: red;
                margin: 0px;
                padding: 0px;
                border: 1px solid black;
                border-radius: 10px;
            }
            
            
        </style>
</head>

<body class="gray-bg">

<div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>

                <h1 class="logo-name"><img src="<?php echo base_url() ?>logo/bmpc-login-logo.png" style="max-width:150px;"/></h1>
                
            </div>
            <h3>Welcome to Talibon Public School Teachers and Employees Multi-Purpose Cooperative</h3>
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
            
            <?php echo form_open('auth/login','class="m-t" role="form"'); ?>              

                <div class="form-group">
                <div class="input-group m-b"><span class="input-group-addon"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span><input type="text"  name="identity" class="form-control" placeholder="Username" required></div>
                    <?php echo form_error('identity'); ?>
                </div>
                <div class="form-group">
                <div class="input-group m-b"><span class="input-group-addon"><i class="fa fa-unlock-alt" aria-hidden="true"></i></span><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                    <?php echo form_error('password'); ?>
                </div>
                <div class="form-group">
                    <div class="pull-left">
                        <div class="i-checks"><label> <input type="checkbox" value="remember-me"><i></i> Remember me </label></div>
                    </div>
                </div>
                
                
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>

                <a href="<?php echo base_url() ?>auth/forgot_password"><small>Forgot password?</small></a>
                <p class="text-muted text-center"><small>Do not have an account?</small></p>
                <a class="btn btn-sm btn-white btn-block" href="register.html">Create an account</a>
            </form>
            <p class="m-t"> <small>Copyright &copy; 2025 - Bohollander IT Solutions</small> </p>
        </div>
    </div>
    <!-- Mainly scripts -->
    <script src="<?php echo base_url() ?>assets/js/jquery-3.1.1.min.js"></script>
    <script src="<?php echo base_url() ?>assets/js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="<?php echo base_url() ?>assets/js/plugins/iCheck/icheck.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.i-checks').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green',
                });
            });
        </script>
</body>

</html>
