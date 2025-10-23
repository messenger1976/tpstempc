<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>SACCOS PLUS | Login</title>
		<meta name="generator" content="Bootply" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<link href="<?php echo base_url() ?>login/css/bootstrap.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
                <link href="<?php echo base_url() ?>login/css/styles.css" rel="stylesheet">
                <style type="text/css">
                    div.error_message{
                        color: red;
                    }
                </style>
	</head>
	<body>
<!--login modal-->
<div id="loginModal" class="modal show" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
  <div class="modal-content">
      <div class="modal-header">
          
          <h1 class="text-center">Login</h1>
      </div>
      <div class="modal-body">
       <?php  if (isset($message) && !empty($message)) {
    echo '<div class="error_message">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="error_message">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="error_message">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="error_message">' . $this->session->flashdata('warning') . '</div>';
}?>
          <?php echo form_open('auth/login','class="form col-md-12 center-block"') ?>
          
          
            <div class="form-group">
                <input type="text" name="identity" class="form-control input-lg" placeholder="Username">
                <?php echo form_error('identity'); ?>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control input-lg" placeholder="Password">
              <?php echo form_error('password'); ?>
            </div>
            <div class="form-group">
              <button class="btn btn-primary btn-lg btn-block">Sign In</button>
              <span class="pull-right"><a href="#">Register</a></span><span><a href="#">Need help?</a></span>
            </div>
          </form>
      </div>
      <div class="modal-footer">
          <div class="col-md-12">
         
		  </div>	
      </div>
  </div>
  </div>
</div>
	<!-- script references -->
		<script src="<?php echo base_url() ?>media/js/jquery.min.js"></script>
		<script src="<?php echo base_url() ?>login/js/bootstrap.min.js"></script>
	</body>
</html>