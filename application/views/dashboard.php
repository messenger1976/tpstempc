<!--<center>
    <br/>
    <div style="width: 700px;">
        <img style="width:500px" src="<?php echo base_url() ?>uploads/final.png"/>
    </div>
    <br/><br/><br/><br/><br/><br/><br/><br/>
</center>-->

<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Talibon Public School Teachers and Employees Multi-Purpose Cooperative  | <?php echo $current_title; ?></title>

    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css?v=4.7.0" rel="stylesheet">

    <!-- Morris -->
    <link href="<?php echo base_url(); ?>media/css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">

    <!-- Gritter -->
    <link href="<?php echo base_url(); ?>media/js/plugins/gritter/jquery.gritter.css" rel="stylesheet">

    <link href="<?php echo base_url(); ?>media/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>media/css/style.css" rel="stylesheet">
</head>

<body>
    <div id="wrapper">
        <nav class="navbar-default navbar-static-side" role="navigation">
            <?php $this->load->view('menu'); ?>
        </nav>

        <div id="page-wrapper" class="gray-bg">
        <?php $this->load->view('header'); ?>
        <!--<div class="row border-bottom">
        <nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                <form role="search" class="navbar-form-custom" method="post" action="search_results.html">
                    <div class="form-group">
                        <input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search">
                    </div>
                </form>
            </div>
            <ul class="nav navbar-top-links navbar-right">
                <li>
                    <span class="m-r-sm text-muted welcome-message">Welcome to INSPINIA+ Admin Theme.</span>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-envelope"></i>  <span class="label label-warning">16</span>
                    </a>
                    <ul class="dropdown-menu dropdown-messages">
                        <li>
                            <div class="dropdown-messages-box">
                                <a href="profile.html" class="pull-left">
                                    <img alt="image" class="img-circle" src="img/a7.jpg">
                                </a>
                                <div>
                                    <small class="pull-right">46h ago</small>
                                    <strong>Mike Loreipsum</strong> started following <strong>Monica Smith</strong>. <br>
                                    <small class="text-muted">3 days ago at 7:58 pm - 10.06.2014</small>
                                </div>
                            </div>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="dropdown-messages-box">
                                <a href="profile.html" class="pull-left">
                                    <img alt="image" class="img-circle" src="img/a4.jpg">
                                </a>
                                <div>
                                    <small class="pull-right text-navy">5h ago</small>
                                    <strong>Chris Johnatan Overtunk</strong> started following <strong>Monica Smith</strong>. <br>
                                    <small class="text-muted">Yesterday 1:21 pm - 11.06.2014</small>
                                </div>
                            </div>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="dropdown-messages-box">
                                <a href="profile.html" class="pull-left">
                                    <img alt="image" class="img-circle" src="img/profile.jpg">
                                </a>
                                <div>
                                    <small class="pull-right">23h ago</small>
                                    <strong>Monica Smith</strong> love <strong>Kim Smith</strong>. <br>
                                    <small class="text-muted">2 days ago at 2:30 am - 11.06.2014</small>
                                </div>
                            </div>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="text-center link-block">
                                <a href="mailbox.html">
                                    <i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell"></i>  <span class="label label-primary">8</span>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                        <li>
                            <a href="mailbox.html">
                                <div>
                                    <i class="fa fa-envelope fa-fw"></i> You have 16 messages
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="profile.html">
                                <div>
                                    <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                    <span class="pull-right text-muted small">12 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="grid_options.html">
                                <div>
                                    <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <div class="text-center link-block">
                                <a href="notifications.html">
                                    <strong>See All Alerts</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>


                <li>
                    <a href="login.html">
                        <i class="fa fa-sign-out"></i> Log out
                    </a>
                </li>
            </ul>

        </nav>
        </div>-->



        <div class="row wrapper border-bottom white-bg page-heading" style="background: linear-gradient(135deg, #1ab394 0%, #17a085 100%); border-bottom: none !important;">
                    <div class="col-lg-10">
                        <h2 style="color: white; margin: 0; padding: 15px 0;">
                            <i class="fa fa-dashboard"></i> <?php echo (isset ($title) ? $title : $current_title); ?>
                            <small style="color: rgba(255,255,255,0.8);">Cooperative Management Dashboard</small>
                        </h2>
                        <?php if(!isset ($dashboard)){ ?>
                        <ol class="breadcrumb" style="background: transparent; margin: 0; padding: 10px 0;">
                            <li>
                                <a href="<?php echo site_url(current_lang()); ?>" style="color: rgba(255,255,255,0.8);"><?php echo lang('home'); ?></a>
                            </li>
                            <li>
                                <a style="color: rgba(255,255,255,0.8);"><?php echo $current_title; ?></a>
                            </li>
                            <li class="active">
                                <strong style="color: white;"><?php echo (isset ($title) ? $title : $current_title); ?></strong>
                            </li>
                        </ol>
                        <?php } ?>
                    </div>
                    <div class="col-lg-2 text-right" style="padding-top: 20px;">
                        <span style="color: white; font-size: 14px;">
                            <i class="fa fa-calendar"></i> <?php echo date('F d, Y'); ?>
                        </span>
                    </div>
                </div>


            <div class="wrapper wrapper-content">
                <!-- Cooperative Dashboard Statistics -->
                <div class="row">
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #1ab394 0%, #17a085 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-users fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Total Members</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #1ab394; font-weight: bold;"><?php echo isset($total_members) ? number_format($total_members) : '0'; ?></h1>
                                <small style="color: #777;">Active Cooperative Members</small>
                                <div class="stat-percent font-bold text-success" style="margin-top: 10px;">
                                    <i class="fa fa-arrow-up"></i> Active
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #1c84c6 0%, #155d8b 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-money fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Share Capital</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #1c84c6; font-weight: bold;"><?php echo isset($total_share_capital) ? number_format($total_share_capital, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Total Share Capital</small>
                                <div class="stat-percent font-bold text-info" style="margin-top: 10px;">
                                    <i class="fa fa-handshake-o"></i> Member Equity
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #23c6c8 0%, #1a9b9d 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-bank fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Total Savings</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #23c6c8; font-weight: bold;"><?php echo isset($total_savings) ? number_format($total_savings, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Member Savings Deposits</small>
                                <div class="stat-percent font-bold" style="margin-top: 10px; color: #23c6c8;">
                                    <i class="fa fa-piggy-bank"></i> Deposits
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #f8ac59 0%, #d68910 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-credit-card fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Active Loans</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #f8ac59; font-weight: bold;"><?php echo isset($total_active_loans) ? number_format($total_active_loans, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Outstanding Loan Balance</small>
                                <div class="stat-percent font-bold" style="margin-top: 10px; color: #f8ac59;">
                                    <i class="fa fa-file-text-o"></i> Loans
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row Statistics -->
                <div class="row" style="margin-top: 20px;">
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #ed5565 0%, #da4453 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-calculator fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Contributions (CBU)</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #ed5565; font-weight: bold;"><?php echo isset($total_contributions) ? number_format($total_contributions, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Capital Build-Up Funds</small>
                                <div class="stat-percent font-bold text-danger" style="margin-top: 10px;">
                                    <i class="fa fa-line-chart"></i> CBU Balance
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-money fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Loan Collections</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #9b59b6; font-weight: bold;"><?php echo isset($total_collections) ? number_format($total_collections, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Monthly Collections</small>
                                <div class="stat-percent font-bold" style="margin-top: 10px; color: #9b59b6;">
                                    <i class="fa fa-arrow-circle-down"></i> This Month
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-cubes fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Mortuary Fund</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #34495e; font-weight: bold;"><?php echo isset($total_mortuary) ? number_format($total_mortuary, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Mortuary Contributions</small>
                                <div class="stat-percent font-bold" style="margin-top: 10px; color: #34495e;">
                                    <i class="fa fa-heart"></i> Benefit Fund
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="background: linear-gradient(135deg, #16a085 0%, #138d75 100%); color: white;">
                                <span class="pull-right"><i class="fa fa-pie-chart fa-2x"></i></span>
                                <h5 style="color: white; margin: 0;">Net Assets</h5>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa;">
                                <h1 class="no-margins" style="color: #16a085; font-weight: bold;"><?php echo isset($net_assets) ? number_format($net_assets, 2) : '0.00'; ?></h1>
                                <small style="color: #777;">Cooperative Net Worth</small>
                                <div class="stat-percent font-bold text-success" style="margin-top: 10px;">
                                    <i class="fa fa-check-circle"></i> Total Assets
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Loan Aging Summary -->
                <?php if (isset($loan_aging_data) && !empty($loan_aging_data)) { ?>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="border-bottom: 2px solid #ed5565;">
                                <h5 style="color: #ed5565; font-weight: bold;"><i class="fa fa-exclamation-triangle"></i> Loan Aging Summary (As of <?php echo date('F d, Y'); ?>)</h5>
                            </div>
                            <div class="ibox-content" style="background: white;">
                                <div class="row">
                                    <?php 
                                    $aging_buckets = array(
                                        'current' => array('label' => 'Current (0-30 days)', 'color' => '#1ab394', 'icon' => 'fa-check-circle'),
                                        '31_60' => array('label' => '31-60 days', 'color' => '#f8ac59', 'icon' => 'fa-clock-o'),
                                        '61_90' => array('label' => '61-90 days', 'color' => '#ed5565', 'icon' => 'fa-exclamation-circle'),
                                        '91_180' => array('label' => '91-180 days', 'color' => '#d9534f', 'icon' => 'fa-warning'),
                                        'over_180' => array('label' => 'Over 180 days', 'color' => '#a94442', 'icon' => 'fa-times-circle')
                                    );
                                    foreach ($aging_buckets as $key => $bucket_info) {
                                        $bucket = isset($loan_aging_data[$key]) ? $loan_aging_data[$key] : null;
                                        $total_balance = $bucket ? $bucket['total_balance'] : 0;
                                        $loan_count = $bucket ? count($bucket['loans']) : 0;
                                    ?>
                                    <div class="col-lg-2 col-md-4 col-sm-6" style="margin-bottom: 15px;">
                                        <div style="border-left: 4px solid <?php echo $bucket_info['color']; ?>; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                                            <div style="text-align: center;">
                                                <i class="fa <?php echo $bucket_info['icon']; ?> fa-2x" style="color: <?php echo $bucket_info['color']; ?>;"></i>
                                                <h4 style="color: <?php echo $bucket_info['color']; ?>; margin: 10px 0 5px 0; font-weight: bold;">
                                                    <?php echo number_format($total_balance, 2); ?>
                                                </h4>
                                                <small style="color: #777; display: block; margin-bottom: 5px;">
                                                    <?php echo $bucket_info['label']; ?>
                                                </small>
                                                <span style="color: #555; font-size: 12px;">
                                                    <?php echo $loan_count; ?> loan(s)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <!-- Loan Collections and Performance Chart -->
                <div class="row" style="margin-top: 20px;">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="border-bottom: 2px solid #1ab394;">
                                <h5 style="color: #1ab394; font-weight: bold;"><i class="fa fa-line-chart"></i> Loan Collections & Performance</h5>
                                <div class="pull-right">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-primary active">Monthly</button>
                                        <button type="button" class="btn btn-xs btn-default">Quarterly</button>
                                        <button type="button" class="btn btn-xs btn-default">Annual</button>
                                    </div>
                                </div>
                            </div>
                            <div class="ibox-content" style="background: white;">
                                <div class="row">
                                    <div class="col-lg-9">
                                        <div class="flot-chart">
                                            <div class="flot-chart-content" id="flot-dashboard-chart" style="height: 320px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <ul class="stat-list">
                                            <li style="border-left: 3px solid #1ab394; padding-left: 15px;">
                                                <h2 class="no-margins" style="color: #1ab394;"><?php echo isset($loan_releases) ? number_format($loan_releases) : '0'; ?></h2>
                                                <small style="color: #777;">Loan Releases (This Month)</small>
                                                <div class="stat-percent" style="color: #1ab394;">Active Loans <i class="fa fa-arrow-up text-navy"></i></div>
                                                <div class="progress progress-mini" style="margin-top: 8px;">
                                                    <div style="width: 75%;" class="progress-bar progress-bar-success"></div>
                                                </div>
                                            </li>
                                            <li style="border-left: 3px solid #1c84c6; padding-left: 15px; margin-top: 20px;">
                                                <h2 class="no-margins" style="color: #1c84c6;"><?php echo isset($collections_monthly) ? number_format($collections_monthly, 2) : '0.00'; ?></h2>
                                                <small style="color: #777;">Collections (Last Month)</small>
                                                <div class="stat-percent" style="color: #1c84c6;">Collection Rate <i class="fa fa-check-circle text-navy"></i></div>
                                                <div class="progress progress-mini" style="margin-top: 8px;">
                                                    <div style="width: <?php echo isset($collection_rate) ? min($collection_rate, 100) : 0; ?>%;" class="progress-bar progress-bar-info"></div>
                                                </div>
                                            </li>
                                            <li style="border-left: 3px solid #f8ac59; padding-left: 15px; margin-top: 20px;">
                                                <h2 class="no-margins" style="color: #f8ac59;"><?php echo isset($payment_rate) ? number_format($payment_rate, 1) : '0'; ?>%</h2>
                                                <small style="color: #777;">On-Time Payment Rate</small>
                                                <div class="stat-percent" style="color: #f8ac59;">Performance <i class="fa fa-trophy text-navy"></i></div>
                                                <div class="progress progress-mini" style="margin-top: 8px;">
                                                    <div style="width: <?php echo isset($payment_rate) ? min($payment_rate, 100) : 0; ?>%;" class="progress-bar progress-bar-warning"></div>
                                                </div>
                                            </li>
                                            <?php if (isset($loan_aging_data)) { ?>
                                            <li style="border-left: 3px solid #ed5565; padding-left: 15px; margin-top: 20px;">
                                                <h2 class="no-margins" style="color: #ed5565;"><?php echo isset($loan_aging_data['over_180']['total_balance']) ? number_format($loan_aging_data['over_180']['total_balance'], 2) : '0.00'; ?></h2>
                                                <small style="color: #777;">Overdue > 180 Days</small>
                                                <div class="stat-percent" style="color: #ed5565;">High Risk <i class="fa fa-exclamation-triangle text-navy"></i></div>
                                                <div class="progress progress-mini" style="margin-top: 8px;">
                                                    <?php 
                                                    $total_outstanding = isset($total_active_loans) ? $total_active_loans : 1;
                                                    $overdue_pct = $total_outstanding > 0 ? ($loan_aging_data['over_180']['total_balance'] / $total_outstanding * 100) : 0;
                                                    ?>
                                                    <div style="width: <?php echo min($overdue_pct, 100); ?>%;" class="progress-bar progress-bar-danger"></div>
                                                </div>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Recent Activities and Quick Access -->
                <div class="row" style="margin-top: 20px;">
                    <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title" style="border-bottom: 2px solid #1c84c6;">
                                <h5 style="color: #1c84c6; font-weight: bold;"><i class="fa fa-bell-o"></i> Recent Activities</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content ibox-heading" style="background: linear-gradient(135deg, #1c84c6 0%, #155d8b 100%); color: white;">
                                <h3><i class="fa fa-clock-o"></i> System Activities</h3>
                                <small><i class="fa fa-info-circle"></i> Latest cooperative transactions and updates.</small>
                            </div>
                            <div class="ibox-content" style="background: #f8f9fa; max-height: 400px; overflow-y: auto;">
                                <div class="feed-activity-list">
                                    <?php 
                                    // Helper function to get icon and color based on action type
                                    function get_activity_style($action) {
                                        $styles = array(
                                            'login' => array('icon' => 'fa-sign-in', 'color' => '#1ab394'),
                                            'logout' => array('icon' => 'fa-sign-out', 'color' => '#ed5565'),
                                            'create' => array('icon' => 'fa-plus', 'color' => '#1ab394'),
                                            'update' => array('icon' => 'fa-edit', 'color' => '#f8ac59'),
                                            'delete' => array('icon' => 'fa-trash', 'color' => '#ed5565'),
                                            'view' => array('icon' => 'fa-eye', 'color' => '#23c6c8'),
                                            'approve' => array('icon' => 'fa-check', 'color' => '#1ab394'),
                                            'reject' => array('icon' => 'fa-times', 'color' => '#ed5565'),
                                        );
                                        
                                        // Default style
                                        $default = array('icon' => 'fa-circle', 'color' => '#1c84c6');
                                        
                                        return isset($styles[strtolower($action)]) ? $styles[strtolower($action)] : $default;
                                    }
                                    
                                    // Helper function to format time ago
                                    function time_ago($datetime) {
                                        $timestamp = strtotime($datetime);
                                        $diff = time() - $timestamp;
                                        
                                        if ($diff < 3600) {
                                            $mins = floor($diff / 60);
                                            return $mins <= 1 ? 'Just now' : $mins . ' minutes ago';
                                        } elseif ($diff < 86400) {
                                            $hours = floor($diff / 3600);
                                            return $hours == 1 ? '1 hour ago' : $hours . ' hours ago';
                                        } elseif ($diff < 604800) {
                                            $days = floor($diff / 86400);
                                            if ($days == 1) return 'Yesterday';
                                            return $days . ' days ago';
                                        } else {
                                            return date('M d, Y', $timestamp);
                                        }
                                    }
                                    
                                    if (isset($recent_activities) && !empty($recent_activities)): 
                                        foreach ($recent_activities as $activity): 
                                            $style = get_activity_style($activity->action);
                                            $user_name = trim(($activity->first_name ?: '') . ' ' . ($activity->last_name ?: ''));
                                            if (empty($user_name)) {
                                                $user_name = $activity->username ?: 'System';
                                            }
                                            $time_ago = time_ago($activity->created_at);
                                            $formatted_date = date('M d, Y - h:i A', strtotime($activity->created_at));
                                    ?>
                                    <div class="feed-element">
                                        <div style="border-left: 3px solid <?php echo $style['color']; ?>; padding-left: 10px;">
                                            <small class="pull-right text-navy"><?php echo $time_ago; ?></small>
                                            <strong style="color: <?php echo $style['color']; ?>;">
                                                <i class="fa <?php echo $style['icon']; ?>"></i> 
                                                <?php echo ucfirst($activity->action); ?> 
                                                <?php echo $activity->module ? ucfirst($activity->module) : 'Activity'; ?>
                                            </strong>
                                            <div style="color: #777;">
                                                <?php echo $activity->description ?: ucfirst($activity->action) . ' ' . ($activity->module ?: 'activity'); ?>
                                                <?php if ($activity->first_name || $activity->username): ?>
                                                    <br><small>by <?php echo $user_name; ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><i class="fa fa-clock-o"></i> <?php echo $formatted_date; ?></small>
                                        </div>
                                    </div>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                    <div class="feed-element">
                                        <div style="padding: 20px; text-align: center; color: #777;">
                                            <i class="fa fa-info-circle fa-2x"></i>
                                            <p style="margin-top: 10px;">No recent activities found.</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div style="text-align: center; padding-top: 15px; border-top: 1px solid #e5e5e5; margin-top: 10px;">
                                    <a href="<?php echo site_url(current_lang() . '/activity_log'); ?>" class="btn btn-sm btn-primary" style="background: #1c84c6; border-color: #1c84c6;">
                                        <i class="fa fa-list"></i> View All Activities
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <!-- Pending Loan Applications -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title" style="border-bottom: 2px solid #f8ac59;">
                                        <h5 style="color: #f8ac59; font-weight: bold;"><i class="fa fa-file-text-o"></i> Pending Loan Applications</h5>
                                        <div class="ibox-tools">
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content" style="background: white;">
                                        <table class="table table-hover no-margins">
                                            <thead style="background: #f8f9fa;">
                                            <tr>
                                                <th>Status</th>
                                                <th>Member ID</th>
                                                <th>Member Name</th>
                                                <th>Loan Amount</th>
                                                <th>Application Date</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><span class="label label-warning">Pending</span></td>
                                                <td>M-00123</td>
                                                <td>Sample Member</td>
                                                <td class="text-navy">₱ 50,000.00</td>
                                                <td><i class="fa fa-clock-o"></i> <?php echo date('M d, Y'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><span class="label label-info">Under Review</span></td>
                                                <td>M-00124</td>
                                                <td>Sample Member 2</td>
                                                <td class="text-navy">₱ 75,000.00</td>
                                                <td><i class="fa fa-clock-o"></i> <?php echo date('M d, Y', strtotime('-1 day')); ?></td>
                                            </tr>
                                            <tr>
                                                <td><span class="label label-warning">Pending</span></td>
                                                <td>M-00125</td>
                                                <td>Sample Member 3</td>
                                                <td class="text-navy">₱ 30,000.00</td>
                                                <td><i class="fa fa-clock-o"></i> <?php echo date('M d, Y', strtotime('-2 days')); ?></td>
                                            </tr>
                                            <tr>
                                                <td><span class="label label-primary">Approved</span></td>
                                                <td>M-00120</td>
                                                <td>Sample Member 4</td>
                                                <td class="text-success">₱ 100,000.00</td>
                                                <td><i class="fa fa-check-circle"></i> <?php echo date('M d, Y', strtotime('-3 days')); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Stats and Member Summary -->
                        <div class="row" style="margin-top: 20px;">
                            <div class="col-lg-6">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title" style="border-bottom: 2px solid #1ab394;">
                                        <h5 style="color: #1ab394; font-weight: bold;"><i class="fa fa-tasks"></i> Quick Actions</h5>
                                        <div class="ibox-tools">
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content" style="background: #f8f9fa;">
                                        <div class="row text-center">
                                            <div class="col-md-6" style="margin-bottom: 15px;">
                                                <div style="padding: 20px; background: white; border-radius: 5px; border-left: 4px solid #1ab394;">
                                                    <h3 style="color: #1ab394; margin: 0;"><i class="fa fa-user-plus fa-2x"></i></h3>
                                                    <p style="margin: 10px 0 0 0; color: #777;">Register New Member</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6" style="margin-bottom: 15px;">
                                                <div style="padding: 20px; background: white; border-radius: 5px; border-left: 4px solid #f8ac59;">
                                                    <h3 style="color: #f8ac59; margin: 0;"><i class="fa fa-credit-card fa-2x"></i></h3>
                                                    <p style="margin: 10px 0 0 0; color: #777;">Process Loan Application</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6" style="margin-bottom: 15px;">
                                                <div style="padding: 20px; background: white; border-radius: 5px; border-left: 4px solid #23c6c8;">
                                                    <h3 style="color: #23c6c8; margin: 0;"><i class="fa fa-bank fa-2x"></i></h3>
                                                    <p style="margin: 10px 0 0 0; color: #777;">Savings Deposit</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6" style="margin-bottom: 15px;">
                                                <div style="padding: 20px; background: white; border-radius: 5px; border-left: 4px solid #ed5565;">
                                                    <h3 style="color: #ed5565; margin: 0;"><i class="fa fa-file-text fa-2x"></i></h3>
                                                    <p style="margin: 10px 0 0 0; color: #777;">Generate Reports</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title" style="border-bottom: 2px solid #1c84c6;">
                                        <h5 style="color: #1c84c6; font-weight: bold;"><i class="fa fa-bar-chart"></i> Member Growth Summary</h5>
                                        <div class="ibox-tools">
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content" style="background: white;">
                                        <div style="padding: 15px;">
                                            <div style="margin-bottom: 20px;">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h4 style="color: #1c84c6; margin: 0;">New Members</h4>
                                                        <p style="color: #777; margin: 5px 0;">This Month</p>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        <h2 style="color: #1c84c6; margin: 0;"><?php echo isset($new_members_month) ? number_format($new_members_month) : '0'; ?></h2>
                                                    </div>
                                                </div>
                                                <div class="progress" style="height: 8px; margin-top: 10px;">
                                                    <div class="progress-bar progress-bar-info" style="width: 65%;"></div>
                                                </div>
                                            </div>
                                            <div style="margin-bottom: 20px;">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h4 style="color: #1ab394; margin: 0;">Active Loans</h4>
                                                        <p style="color: #777; margin: 5px 0;">Currently Active</p>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        <h2 style="color: #1ab394; margin: 0;"><?php echo isset($active_loans_count) ? number_format($active_loans_count) : '0'; ?></h2>
                                                    </div>
                                                </div>
                                                <div class="progress" style="height: 8px; margin-top: 10px;">
                                                    <div class="progress-bar progress-bar-success" style="width: 80%;"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h4 style="color: #f8ac59; margin: 0;">Collection Rate</h4>
                                                        <p style="color: #777; margin: 5px 0;">This Month</p>
                                                    </div>
                                                    <div class="col-md-6 text-right">
                                                        <h2 style="color: #f8ac59; margin: 0;"><?php echo isset($collection_rate) ? number_format($collection_rate, 1) : '0'; ?>%</h2>
                                                    </div>
                                                </div>
                                                <div class="progress" style="height: 8px; margin-top: 10px;">
                                                    <div class="progress-bar progress-bar-warning" style="width: <?php echo isset($collection_rate) ? $collection_rate : 0; ?>%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <div class="footer">
            <div class="pull-right">
                <strong>Talibon Public School Teachers and Employees Multi-Purpose Cooperative</strong>
            </div>
            <div>
                <strong>Copyright</strong> Cooperative Management System &copy; <?php echo date('Y'); ?>
            </div>
        </div>
        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="<?php echo base_url(); ?>media/js/jquery-1.10.2.js"></script>
    <script src="<?php echo base_url(); ?>media/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url(); ?>media/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="<?php echo base_url(); ?>media/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Flot -->
    <script src="<?php echo base_url(); ?>media/js/plugins/flot/jquery.flot.js"></script>
    <script src="<?php echo base_url(); ?>media/js/plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script src="<?php echo base_url(); ?>media/js/plugins/flot/jquery.flot.spline.js"></script>
    <script src="<?php echo base_url(); ?>media/js/plugins/flot/jquery.flot.resize.js"></script>
    <script src="<?php echo base_url(); ?>media/js/plugins/flot/jquery.flot.pie.js"></script>

    <!-- Peity -->
    <script src="<?php echo base_url(); ?>media/js/plugins/peity/jquery.peity.min.js"></script>
    <script src="<?php echo base_url(); ?>media/js/demo/peity-demo.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="<?php echo base_url(); ?>media/js/inspinia.js"></script>
    <!--<script src="<?php echo base_url(); ?>media/js/plugins/pace/pace.min.js"></script>-->

    <!-- jQuery UI -->
    <script src="<?php echo base_url(); ?>media/js/plugins/jquery-ui/jquery-ui.min.js"></script>

    <!-- GITTER -->
    <script src="<?php echo base_url(); ?>media/js/plugins/gritter/jquery.gritter.min.js"></script>

    <!-- EayPIE -->
    <script src="<?php echo base_url(); ?>media/js/plugins/easypiechart/jquery.easypiechart.js"></script>

    <!-- Sparkline -->
    <script src="<?php echo base_url(); ?>media/js/plugins/sparkline/jquery.sparkline.min.js"></script>

    <!-- ChartJS -->
    <script src="<?php echo base_url(); ?>media/js/plugins/chartJs/Chart.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.chart').easyPieChart({
                barColor: '#f8ac59',
//                scaleColor: false,
                scaleLength: 5,
                lineWidth: 4,
                size: 80
            });

            $('.chart2').easyPieChart({
                barColor: '#1c84c6',
//                scaleColor: false,
                scaleLength: 5,
                lineWidth: 4,
                size: 80
            });

            // Prepare monthly collections data
            var monthlyCollections = <?php echo json_encode(isset($monthly_collections) ? $monthly_collections : array()); ?>;
            var collectionsData = [];
            var maxCollection = 0;
            
            // Convert monthly collections to chart data format
            for (var month in monthlyCollections) {
                if (monthlyCollections.hasOwnProperty(month)) {
                    var dateParts = month.split('-');
                    var year = parseInt(dateParts[0]);
                    var monthNum = parseInt(dateParts[1]);
                    var day = 15; // Use middle of month for display
                    var timestamp = gd(year, monthNum, day);
                    var amount = parseFloat(monthlyCollections[month]) || 0;
                    collectionsData.push([timestamp, amount]);
                    if (amount > maxCollection) {
                        maxCollection = amount;
                    }
                }
            }
            
            // Sort by timestamp
            collectionsData.sort(function(a, b) {
                return a[0] - b[0];
            });
            
            // Prepare loan aging data for display
            var loanAgingData = <?php echo json_encode(isset($loan_aging_data) ? $loan_aging_data : array()); ?>;
            var agingSeries = [];
            
            if (loanAgingData && Object.keys(loanAgingData).length > 0) {
                // Create series for each aging bucket
                var agingBuckets = [
                    {key: 'current', label: 'Current (0-30 days)', color: '#1ab394'},
                    {key: '31_60', label: '31-60 days', color: '#f8ac59'},
                    {key: '61_90', label: '61-90 days', color: '#ed5565'},
                    {key: '91_180', label: '91-180 days', color: '#d9534f'},
                    {key: 'over_180', label: 'Over 180 days', color: '#a94442'}
                ];
                
                // Get current date for display
                var now = new Date();
                var currentTimestamp = gd(now.getFullYear(), now.getMonth() + 1, now.getDate());
                
                agingBuckets.forEach(function(bucket) {
                    if (loanAgingData[bucket.key] && loanAgingData[bucket.key].total_balance > 0) {
                        agingSeries.push({
                            label: bucket.label,
                            data: [[currentTimestamp, parseFloat(loanAgingData[bucket.key].total_balance)]],
                            color: bucket.color,
                            bars: {
                                show: true,
                                align: "center",
                                barWidth: 7 * 24 * 60 * 60 * 1000, // 7 days width
                                lineWidth: 0,
                                fill: true,
                                fillColor: bucket.color
                            }
                        });
                    }
                });
            }
            
            // Build dataset
            var dataset = [];
            
            // Add monthly collections as bars
            if (collectionsData.length > 0) {
                dataset.push({
                    label: "Monthly Collections",
                    data: collectionsData,
                    color: "#1ab394",
                    bars: {
                        show: true,
                        align: "center",
                        barWidth: 20 * 24 * 60 * 60 * 1000, // ~20 days width for monthly bars
                        lineWidth: 0,
                        fill: true,
                        fillColor: "#1ab394"
                    }
                });
            }
            
            // Add aging buckets as separate series (stacked or side-by-side)
            if (agingSeries.length > 0) {
                // Add aging data as a line/area chart overlay
                var agingTotalData = [];
                if (loanAgingData) {
                    var totalAging = 0;
                    if (loanAgingData.current) totalAging += parseFloat(loanAgingData.current.total_balance || 0);
                    if (loanAgingData['31_60']) totalAging += parseFloat(loanAgingData['31_60'].total_balance || 0);
                    if (loanAgingData['61_90']) totalAging += parseFloat(loanAgingData['61_90'].total_balance || 0);
                    if (loanAgingData['91_180']) totalAging += parseFloat(loanAgingData['91_180'].total_balance || 0);
                    if (loanAgingData.over_180) totalAging += parseFloat(loanAgingData.over_180.total_balance || 0);
                    
                    if (totalAging > 0) {
                        var now = new Date();
                        var currentTimestamp = gd(now.getFullYear(), now.getMonth() + 1, now.getDate());
                        agingTotalData.push([currentTimestamp, totalAging]);
                        
                        dataset.push({
                            label: "Total Outstanding Loans",
                            data: agingTotalData,
                            yaxis: 2,
                            color: "#464f88",
                            points: {
                                show: true,
                                radius: 5,
                                fill: true
                            },
                            lines: {
                                show: false
                            }
                        });
                    }
                }
            }
            
            // Calculate max value for y-axis
            var maxY = Math.max(maxCollection, 1000);
            if (loanAgingData) {
                var totalAging = 0;
                if (loanAgingData.current) totalAging += parseFloat(loanAgingData.current.total_balance || 0);
                if (loanAgingData['31_60']) totalAging += parseFloat(loanAgingData['31_60'].total_balance || 0);
                if (loanAgingData['61_90']) totalAging += parseFloat(loanAgingData['61_90'].total_balance || 0);
                if (loanAgingData['91_180']) totalAging += parseFloat(loanAgingData['91_180'].total_balance || 0);
                if (loanAgingData.over_180) totalAging += parseFloat(loanAgingData.over_180.total_balance || 0);
                maxY = Math.max(maxY, totalAging * 1.1); // Add 10% padding
            }
            
            var options = {
                xaxis: {
                    mode: "time",
                    tickSize: [1, "month"],
                    tickLength: 0,
                    axisLabel: "Month",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Arial',
                    axisLabelPadding: 10,
                    color: "#838383",
                    timeformat: "%b %Y"
                },
                yaxes: [{
                    position: "left",
                    max: maxY,
                    color: "#838383",
                    axisLabel: "Amount",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Arial',
                    axisLabelPadding: 3,
                    tickFormatter: function(val) {
                        return val.toLocaleString();
                    }
                }, {
                    position: "right",
                    color: "#838383",
                    axisLabel: "Outstanding Balance",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 12,
                    axisLabelFontFamily: 'Arial',
                    axisLabelPadding: 67,
                    tickFormatter: function(val) {
                        return val.toLocaleString();
                    }
                }],
                legend: {
                    noColumns: 2,
                    labelBoxBorderColor: "#000000",
                    position: "nw",
                    show: true
                },
                grid: {
                    hoverable: true,
                    borderWidth: 1,
                    color: '#838383',
                    clickable: true
                },
                tooltip: true,
                tooltipOpts: {
                    content: "%s: %y",
                    shifts: {
                        x: -60,
                        y: 25
                    }
                }
            };

            function gd(year, month, day) {
                return new Date(year, month - 1, day).getTime();
            }

            var previousPoint = null, previousLabel = null;

            $.plot($("#flot-dashboard-chart"), dataset, options);
            
            // Add tooltip functionality
            $("#flot-dashboard-chart").bind("plothover", function (event, pos, item) {
                if (item) {
                    if (previousPoint != item.dataIndex || previousLabel != item.series.label) {
                        previousPoint = item.dataIndex;
                        previousLabel = item.series.label;
                        
                        $("#tooltip").remove();
                        var x = item.datapoint[0],
                            y = item.datapoint[1];
                        
                        showTooltip(item.pageX, item.pageY,
                                    item.series.label + ": " + y.toLocaleString());
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
            
            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 5,
                    border: '1px solid #fdd',
                    padding: '2px',
                    'background-color': '#fee',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }

            // World map removed - not needed for cooperative dashboard
        });
    </script>
</body>
</html>


