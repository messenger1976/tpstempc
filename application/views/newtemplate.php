<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Co-Operative+ |  <?php echo $current_title; ?></title>

    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css" rel="stylesheet">

    <link href="<?php echo base_url(); ?>assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet">

    
</head>

<body class="skin-1">
    <div id="wrapper">
    <nav class="navbar-default navbar-static-side" role="navigation">
        <?php $this->load->view('newmenu'); ?>
    </nav>

        <div id="page-wrapper" class="gray-bg dashbard-1">
        <?php $this->load->view('newheader'); ?>
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2><?php echo (isset ($title) ? $title : $current_title); ?></h2>
                    <?php if(!isset ($dashboard)){ ?>
                    <ol class="breadcrumb">
                        <li>
                            <a href="<?php echo site_url(current_lang()); ?>"><?php echo lang('home'); ?></a>
                        </li>
                        <li>
                            <a><?php echo $current_title; ?></a>
                        </li>
                        <li class="active">
                            <strong><?php echo (isset ($title) ? $title : $current_title); ?></strong>
                        </li>
                    </ol>
                    <?php } ?>
                </div>
                <div class="col-lg-2">

                </div>
            </div>
            <div class="wrapper wrapper-content">

                <div class="p-w-md m-t-sm">
                    

                    


                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox">
                                <div class="ibox-title"><h5><strong><?php echo (isset ($title) ? $title : $current_title). (isset ($subtitle) ? $subtitle:'');  ?></strong></h5></div>


                                <div class="ibox-content">

                                <?php
                                if (isset($content) && isset($data)) {
                                    $this->load->view($content, $data);
                                } else {
                                    $this->load->view($content);
                                }
                                ?>

                                    

                                </div>
                            </div>
                        </div>
                    </div>

                </div>


            </div>

        <div class="footer">
            <div class="pull-right">
                10GB of <strong>250GB</strong> Free.
            </div>
            <div>
                <strong>Copyright</strong> UnifiedAR &copy; 2014-2023
            </div>
        </div>
        </div>

    </div>

    <!-- Mainly scripts -->
    <script src="<?php echo base_url(); ?>assets/js/jquery-3.1.1.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Flot -->
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.spline.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.resize.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.pie.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.symbol.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/plugins/flot/jquery.flot.time.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="<?php echo base_url(); ?>assets/js/uarscript.js"></script>
    <!--<script src="js/plugins/pace/pace.min.js"></script>-->

    <!-- Sparkline -->
    <script src="<?php echo base_url(); ?>assets/js/plugins/sparkline/jquery.sparkline.min.js"></script>

    <!-- ChartJS -->
    <script src="<?php echo base_url(); ?>assets/js/plugins/chartJs/Chart.min.js"></script>

    <script>
        $(document).ready(function() {

            




           
            

        });
    </script>
</body>
</html>
