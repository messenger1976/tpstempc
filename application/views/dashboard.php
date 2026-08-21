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

    <?php $company = company_info_detail(); $company_name = (isset($company->name) && $company->name !== '') ? $company->name : 'Cooperative'; ?>
    <title><?php echo htmlspecialchars($company_name); ?> | <?php echo $current_title; ?></title>

    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>assets/font-awesome/css/font-awesome.css?v=4.7.0" rel="stylesheet">

    <!-- Morris -->
    <link href="<?php echo base_url(); ?>media/css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">

    <!-- Gritter -->
    <link href="<?php echo base_url(); ?>media/js/plugins/gritter/jquery.gritter.css" rel="stylesheet">

    <link href="<?php echo base_url(); ?>media/css/animate.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>media/css/style.css" rel="stylesheet">
    <link href="<?php echo base_url(); ?>media/css/app-shell.css?v=20260813" rel="stylesheet">
    <!-- Leaflet / OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <style>
        #members-osm-map { height: 480px; width: 100%; border-radius: 8px; z-index: 1; }
        .member-map-legend { margin-top: 10px; color: #676a6c; font-size: 12px; }
        .leaflet-popup-content { font-size: 13px; line-height: 1.4; }
        .leaflet-popup-content ul { margin: 6px 0 0 16px; padding: 0; }
        .leaflet-routing-container { max-height: 220px; overflow-y: auto; width: 280px; font-size: 12px; }
        .office-map-marker {
            background: #ed5565;
            color: #fff;
            border: 2px solid #fff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            line-height: 24px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.4);
            font-size: 14px;
        }

        .dash-home { margin-top: 0; }
        .dash-home .dash-intro {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 16px 20px;
            background: #fff;
            border: 1px solid #e7eaec;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .dash-home .dash-intro .intro-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .dash-home .dash-intro .icon-badge {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #e8f8f5;
            color: #1ab394;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .dash-home .dash-intro h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #2f4050;
        }
        .dash-home .dash-intro p {
            margin: 2px 0 0;
            font-size: 13px;
            color: #888;
        }
        .dash-home .dash-intro .intro-date {
            font-size: 13px;
            font-weight: 600;
            color: #1ab394;
            background: #e8f8f5;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .dash-home .kpi-card {
            background: #fff;
            border: 1px solid #e7eaec;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            overflow: hidden;
            min-height: 128px;
        }
        .dash-home .kpi-card .kpi-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f2f3;
            background: #fafbfc;
        }
        .dash-home .kpi-card .kpi-head h5 {
            margin: 0;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #676a6c;
        }
        .dash-home .kpi-card .kpi-head .kpi-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e8f8f5;
            color: #1ab394;
        }
        .dash-home .kpi-card .kpi-body { padding: 14px 16px 16px; }
        .dash-home .kpi-card .kpi-value {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #2f4050;
            font-variant-numeric: tabular-nums;
            line-height: 1.2;
        }
        .dash-home .kpi-card .kpi-sub {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #888;
        }
        .dash-home .kpi-card .kpi-tag {
            display: inline-block;
            margin-top: 10px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            background: #e8f8f5;
            color: #1ab394;
        }
        .dash-home .kpi-card.tone-teal .kpi-icon { background: #e8f8f5; color: #1ab394; }
        .dash-home .kpi-card.tone-teal .kpi-value { color: #1ab394; }
        .dash-home .kpi-card.tone-blue .kpi-icon { background: #eef3fb; color: #1c84c6; }
        .dash-home .kpi-card.tone-blue .kpi-value { color: #1c84c6; }
        .dash-home .kpi-card.tone-blue .kpi-tag { background: #eef3fb; color: #1c84c6; }
        .dash-home .kpi-card.tone-cyan .kpi-icon { background: #e8f9fa; color: #23c6c8; }
        .dash-home .kpi-card.tone-cyan .kpi-value { color: #23c6c8; }
        .dash-home .kpi-card.tone-cyan .kpi-tag { background: #e8f9fa; color: #23c6c8; }
        .dash-home .kpi-card.tone-amber .kpi-icon { background: #fef6eb; color: #f8ac59; }
        .dash-home .kpi-card.tone-amber .kpi-value { color: #f8ac59; }
        .dash-home .kpi-card.tone-amber .kpi-tag { background: #fef6eb; color: #d68910; }
        .dash-home .kpi-card.tone-red .kpi-icon { background: #fdeceb; color: #ed5565; }
        .dash-home .kpi-card.tone-red .kpi-value { color: #ed5565; }
        .dash-home .kpi-card.tone-red .kpi-tag { background: #fdeceb; color: #c0392b; }
        .dash-home .kpi-card.tone-slate .kpi-icon { background: #eef1f2; color: #34495e; }
        .dash-home .kpi-card.tone-slate .kpi-value { color: #34495e; }
        .dash-home .kpi-card.tone-slate .kpi-tag { background: #eef1f2; color: #34495e; }
        .dash-home .kpi-card.tone-navy .kpi-icon { background: #eef3fb; color: #3c6eae; }
        .dash-home .kpi-card.tone-navy .kpi-value { color: #3c6eae; }
        .dash-home .kpi-card.tone-navy .kpi-tag { background: #eef3fb; color: #3c6eae; }

        .dash-home .ibox {
            background: #fff;
            border: 1px solid #e7eaec;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .dash-home .ibox.float-e-margins { margin: 0 0 20px; }
        .dash-home .ibox-title {
            background: #fafbfc !important;
            border-bottom: 1px solid #e7eaec !important;
            border-width: 0 0 1px !important;
            min-height: 48px;
            padding: 14px 18px;
        }
        .dash-home .ibox-title h5 {
            color: #2f4050 !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            margin: 0 !important;
        }
        .dash-home .ibox-title h5 i { color: #1ab394; margin-right: 6px; }
        .dash-home .ibox-content {
            background: #fff !important;
            border-width: 0 !important;
            padding: 18px;
        }
        .dash-home .ibox-content.ibox-heading {
            background: #e8f8f5 !important;
            color: #0e7c69 !important;
            border-bottom: 1px solid #c9ebe3 !important;
        }
        .dash-home .ibox-content.ibox-heading h3 {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: 700;
            color: #0e7c69;
        }
        .dash-home .ibox-content.ibox-heading small { color: #3a8f7f; }
        .dash-home .aging-tile {
            border: 1px solid #e7eaec;
            border-left-width: 4px;
            border-radius: 8px;
            padding: 14px 10px;
            background: #fafbfc;
            text-align: center;
            margin-bottom: 12px;
            min-height: 120px;
        }
        .dash-home .aging-tile h4 {
            margin: 8px 0 4px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        .dash-home .quick-action {
            display: block;
            padding: 18px 14px;
            background: #fff;
            border: 1px solid #e7eaec;
            border-radius: 8px;
            border-left: 4px solid #1ab394;
            text-decoration: none !important;
            color: inherit;
            transition: background .15s ease, border-color .15s ease;
            margin-bottom: 12px;
            min-height: 96px;
        }
        .dash-home .quick-action:hover {
            background: #e8f8f5;
            border-color: #c9ebe3;
        }
        .dash-home .quick-action h3 { margin: 0; }
        .dash-home .quick-action p {
            margin: 8px 0 0;
            color: #676a6c;
            font-size: 13px;
            font-weight: 600;
        }
        .dash-home .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }
        .dash-home .status-pill.pending { background: #fef6eb; color: #d68910; }
        .dash-home .status-pill.review { background: #eef3fb; color: #1c84c6; }
        .dash-home .status-pill.approved { background: #e8f8f5; color: #1ab394; }
        .dash-home .pipeline-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 16px;
        }
        .dash-home .pipeline-chip {
            flex: 1 1 140px;
            min-width: 130px;
            border: 1px solid #e7eaec;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fafbfc;
            text-decoration: none !important;
            color: inherit;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .dash-home .pipeline-chip:hover {
            border-color: #cfd6dc;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .dash-home .pipeline-chip .chip-label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #888;
            font-weight: 700;
        }
        .dash-home .pipeline-chip .chip-value {
            display: block;
            margin-top: 4px;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.1;
        }
        .dash-home .pipeline-chip.tone-pending .chip-value { color: #d68910; }
        .dash-home .pipeline-chip.tone-review .chip-value { color: #1c84c6; }
        .dash-home .pipeline-chip.tone-approved .chip-value { color: #1ab394; }
        .dash-home .pipeline-chip.tone-cash .chip-value { color: #ed5565; }
        .dash-home .loan-pipeline-table td a.loan-link {
            color: #1c84c6;
            font-weight: 600;
        }
        .dash-home .loan-pipeline-table tr.pipeline-row {
            cursor: pointer;
        }
        .dash-home .loan-pipeline-table tr.pipeline-row:hover td {
            background: #f8fafb;
        }
        .dash-home .pipeline-empty {
            text-align: center;
            padding: 28px 12px;
            color: #777;
        }
        .dash-home .pipeline-empty i {
            font-size: 28px;
            color: #c5ccd1;
            margin-bottom: 8px;
        }
        .dash-home .pipeline-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding-top: 12px;
            margin-top: 8px;
            border-top: 1px solid #e7eaec;
        }
        .dash-home .pipeline-footer .listed-amount {
            color: #676a6c;
            font-size: 13px;
        }
        .dash-home .btn-primary {
            background: #1ab394;
            border-color: #1ab394;
            border-radius: 6px;
            font-weight: 600;
        }
        .dash-home .table > thead > tr > th {
            background: #fafbfc;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #676a6c;
            border-bottom: 1px solid #e7eaec;
        }
    </style>
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



        <div class="wrapper wrapper-content dash-home">
                <div class="dash-intro">
                    <div class="intro-left">
                        <i class="fa fa-dashboard icon-badge"></i>
                        <div>
                            <h2><?php echo (isset($title) ? $title : $current_title); ?></h2>
                            <p>Cooperative Management Dashboard</p>
                        </div>
                    </div>
                    <div class="intro-date">
                        <i class="fa fa-calendar"></i> <?php echo date('F d, Y'); ?>
                    </div>
                </div>

                <!-- Cooperative Dashboard Statistics -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-teal">
                            <div class="kpi-head">
                                <h5>Total Members</h5>
                                <span class="kpi-icon"><i class="fa fa-users"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_members) ? number_format($total_members) : '0'; ?></h1>
                                <span class="kpi-sub">Active Cooperative Members</span>
                                <span class="kpi-tag"><i class="fa fa-check"></i> Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-blue">
                            <div class="kpi-head">
                                <h5>Share Capital</h5>
                                <span class="kpi-icon"><i class="fa fa-money"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_share_capital) ? number_format($total_share_capital, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Total Share Capital</span>
                                <span class="kpi-tag"><i class="fa fa-handshake-o"></i> Member Equity</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-cyan">
                            <div class="kpi-head">
                                <h5>Total Savings</h5>
                                <span class="kpi-icon"><i class="fa fa-bank"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_savings) ? number_format($total_savings, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Member Savings Deposits</span>
                                <span class="kpi-tag"><i class="fa fa-database"></i> Deposits</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-amber">
                            <div class="kpi-head">
                                <h5>Active Loans</h5>
                                <span class="kpi-icon"><i class="fa fa-credit-card"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_active_loans) ? number_format($total_active_loans, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Outstanding Loan Balance</span>
                                <span class="kpi-tag"><i class="fa fa-file-text-o"></i> Loans</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row Statistics -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-red">
                            <div class="kpi-head">
                                <h5>Contributions (CBU)</h5>
                                <span class="kpi-icon"><i class="fa fa-calculator"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_contributions) ? number_format($total_contributions, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Capital Build-Up Funds</span>
                                <span class="kpi-tag"><i class="fa fa-line-chart"></i> CBU Balance</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-navy">
                            <div class="kpi-head">
                                <h5>Loan Collections</h5>
                                <span class="kpi-icon"><i class="fa fa-money"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_collections) ? number_format($total_collections, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Monthly Collections</span>
                                <span class="kpi-tag"><i class="fa fa-arrow-circle-down"></i> This Month</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-slate">
                            <div class="kpi-head">
                                <h5>Mortuary Fund</h5>
                                <span class="kpi-icon"><i class="fa fa-heart"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($total_mortuary) ? number_format($total_mortuary, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Mortuary Contributions</span>
                                <span class="kpi-tag"><i class="fa fa-heart"></i> Benefit Fund</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="kpi-card tone-teal">
                            <div class="kpi-head">
                                <h5>Net Assets</h5>
                                <span class="kpi-icon"><i class="fa fa-pie-chart"></i></span>
                            </div>
                            <div class="kpi-body">
                                <h1 class="kpi-value"><?php echo isset($net_assets) ? number_format($net_assets, 2) : '0.00'; ?></h1>
                                <span class="kpi-sub">Cooperative Net Worth</span>
                                <span class="kpi-tag"><i class="fa fa-check-circle"></i> Total Assets</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Members Address Map (OpenStreetMap) -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5><i class="fa fa-map-marker"></i> Members Address Map</h5>
                            </div>
                            <div class="ibox-content">
                                <?php
                                $map_stats = isset($member_map_stats) ? $member_map_stats : array('with_address' => 0, 'plotted' => 0, 'locations' => 0, 'table_ready' => false);
                                $map_locations = isset($member_map_locations) ? $member_map_locations : array();
                                ?>
                                <?php if (empty($map_stats['table_ready'])) { ?>
                                    <div class="alert alert-warning" style="margin-bottom: 0;">
                                        Map cache is not installed yet. Run
                                        <code>php tools/install_member_address_geocode.php</code>
                                        (or open <code>tools/install_member_address_geocode.php</code> in the browser) to geocode member addresses.
                                    </div>
                                <?php } elseif (empty($map_locations)) { ?>
                                    <div class="alert alert-info" style="margin-bottom: 0;">
                                        No geocoded member addresses to display yet.
                                        <?php if (!empty($map_stats['with_address'])) { ?>
                                            <?php echo intval($map_stats['with_address']); ?> member(s) have addresses — re-run <code>tools/install_member_address_geocode.php</code> to plot them.
                                        <?php } else { ?>
                                            Add physical addresses in member contact info to see them here.
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div id="members-osm-map"></div>
                                    <div class="member-map-legend">
                                        <i class="fa fa-map-marker" style="color:#ed5565;"></i> Office &nbsp;
                                        <i class="fa fa-circle" style="color:#1c84c6;"></i> Members &nbsp;|&nbsp;
                                        Click a member pin to show driving directions from the office.
                                        Showing <strong><?php echo intval($map_stats['plotted']); ?></strong> of
                                        <strong><?php echo intval($map_stats['with_address']); ?></strong> active members across
                                        <strong><?php echo intval($map_stats['locations']); ?></strong> location(s).
                                        Map data &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>
                                        · Routing via <a href="http://project-osrm.org/" target="_blank" rel="noopener">OSRM</a>.
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Aging Summary -->
                <?php if (isset($loan_aging_data) && !empty($loan_aging_data)) { ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5><i class="fa fa-exclamation-triangle"></i> Loan Aging Summary (As of <?php echo date('F d, Y'); ?>)</h5>
                            </div>
                            <div class="ibox-content">
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
                                    <div class="col-lg-2 col-md-4 col-sm-6">
                                        <div class="aging-tile" style="border-left-color: <?php echo $bucket_info['color']; ?>;">
                                            <i class="fa <?php echo $bucket_info['icon']; ?> fa-2x" style="color: <?php echo $bucket_info['color']; ?>;"></i>
                                            <h4 style="color: <?php echo $bucket_info['color']; ?>;">
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
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <!-- Loan Collections and Performance Chart -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5><i class="fa fa-line-chart"></i> Loan Collections &amp; Performance</h5>
                                <div class="pull-right">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-primary active">Monthly</button>
                                        <button type="button" class="btn btn-xs btn-default">Quarterly</button>
                                        <button type="button" class="btn btn-xs btn-default">Annual</button>
                                    </div>
                                </div>
                            </div>
                            <div class="ibox-content">
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
                <div class="row">
                    <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5><i class="fa fa-bell-o"></i> Recent Activities</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="ibox-content ibox-heading">
                                <h3><i class="fa fa-clock-o"></i> System Activities</h3>
                                <small><i class="fa fa-info-circle"></i> Latest cooperative transactions and updates.</small>
                            </div>
                            <div class="ibox-content" style="max-height: 400px; overflow-y: auto;">
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
                                    <a href="<?php echo site_url(current_lang() . '/activity_log'); ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-list"></i> View All Activities
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <!-- Pending Loan Applications (live pipeline) -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5><i class="fa fa-file-text-o"></i> Pending Loan Applications</h5>
                                        <div class="ibox-tools">
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
                                        <?php
                                        $pipeline_summary = isset($loan_pipeline_summary) && is_array($loan_pipeline_summary) ? $loan_pipeline_summary : array();
                                        $pipeline_items = isset($loan_pipeline_items) && is_array($loan_pipeline_items) ? $loan_pipeline_items : array();
                                        $cnt_eval = isset($pipeline_summary['awaiting_evaluation']) ? (int) $pipeline_summary['awaiting_evaluation'] : 0;
                                        $cnt_approval = isset($pipeline_summary['awaiting_approval']) ? (int) $pipeline_summary['awaiting_approval'] : 0;
                                        $cnt_release = isset($pipeline_summary['ready_to_release']) ? (int) $pipeline_summary['ready_to_release'] : 0;
                                        $cnt_cash = isset($pipeline_summary['pending_cash_release']) ? (int) $pipeline_summary['pending_cash_release'] : 0;
                                        $listed_amount = isset($pipeline_summary['listed_amount']) ? floatval($pipeline_summary['listed_amount']) : 0;
                                        ?>
                                        <div class="pipeline-summary">
                                            <a class="pipeline-chip tone-pending" href="<?php echo site_url(current_lang() . '/loan/loan_evaluation'); ?>">
                                                <span class="chip-label">Awaiting Evaluation</span>
                                                <span class="chip-value"><?php echo number_format($cnt_eval); ?></span>
                                            </a>
                                            <a class="pipeline-chip tone-review" href="<?php echo site_url(current_lang() . '/loan/loan_approval'); ?>">
                                                <span class="chip-label">Awaiting Approval</span>
                                                <span class="chip-value"><?php echo number_format($cnt_approval); ?></span>
                                            </a>
                                            <a class="pipeline-chip tone-approved" href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>">
                                                <span class="chip-label">Ready to Release</span>
                                                <span class="chip-value"><?php echo number_format($cnt_release); ?></span>
                                            </a>
                                            <a class="pipeline-chip tone-cash" href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>">
                                                <span class="chip-label">Pending Cash Release</span>
                                                <span class="chip-value"><?php echo number_format($cnt_cash); ?></span>
                                            </a>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover no-margins loan-pipeline-table">
                                                <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Loan ID</th>
                                                    <th>Member</th>
                                                    <th>Product</th>
                                                    <th class="text-right">Amount</th>
                                                    <th>Applied</th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($pipeline_items)): ?>
                                                    <?php foreach ($pipeline_items as $item): ?>
                                                        <?php
                                                        $action_url = isset($item['action_url']) ? $item['action_url'] : '#';
                                                        $pill = isset($item['pill']) ? $item['pill'] : 'pending';
                                                        $status_label = isset($item['status_label']) ? $item['status_label'] : 'Pending';
                                                        $applied = !empty($item['applicationdate']) ? date('M d, Y', strtotime($item['applicationdate'])) : '—';
                                                        $member_label = trim((isset($item['member_id']) ? $item['member_id'] : '') . ' · ' . (isset($item['member_name']) ? $item['member_name'] : ''), ' ·');
                                                        ?>
                                                        <tr class="pipeline-row" onclick="window.location='<?php echo htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8'); ?>';">
                                                            <td><span class="status-pill <?php echo htmlspecialchars($pill, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status_label, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                            <td><a class="loan-link" href="<?php echo htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8'); ?>" onclick="event.stopPropagation();"><?php echo htmlspecialchars(isset($item['LID']) ? $item['LID'] : '', ENT_QUOTES, 'UTF-8'); ?></a></td>
                                                            <td><?php echo htmlspecialchars($member_label !== '' ? $member_label : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo htmlspecialchars(!empty($item['product_name']) ? $item['product_name'] : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right text-navy">₱ <?php echo number_format(isset($item['amount']) ? $item['amount'] : 0, 2); ?></td>
                                                            <td><i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($applied, ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-right">
                                                                <a href="<?php echo htmlspecialchars($action_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-xs btn-primary" onclick="event.stopPropagation();">Open</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7">
                                                            <div class="pipeline-empty">
                                                                <div><i class="fa fa-check-circle"></i></div>
                                                                <p style="margin:0;">No loans waiting for evaluation or approval right now.</p>
                                                                <small>Accepted loans ready for release appear above when available.</small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="pipeline-footer">
                                            <span class="listed-amount">
                                                <?php if (!empty($pipeline_items)): ?>
                                                    Showing <?php echo count($pipeline_items); ?> actionable loan(s) · ₱ <?php echo number_format($listed_amount, 2); ?>
                                                <?php else: ?>
                                                    Queue clear for evaluation / approval
                                                <?php endif; ?>
                                            </span>
                                            <span>
                                                <a href="<?php echo site_url(current_lang() . '/loan/loan_evaluation'); ?>" class="btn btn-sm btn-default">Evaluation</a>
                                                <a href="<?php echo site_url(current_lang() . '/loan/loan_approval'); ?>" class="btn btn-sm btn-default">Approval</a>
                                                <a href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" class="btn btn-sm btn-primary">Release Queue</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Stats and Member Summary -->
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5><i class="fa fa-tasks"></i> Quick Actions</h5>
                                        <div class="ibox-tools">
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
                                        <div class="row text-center">
                                            <div class="col-md-6">
                                                <a class="quick-action" href="<?php echo site_url(current_lang() . '/member/new_member'); ?>" style="border-left-color:#1ab394;">
                                                    <h3 style="color: #1ab394;"><i class="fa fa-user-plus fa-2x"></i></h3>
                                                    <p>Register New Member</p>
                                                </a>
                                            </div>
                                            <div class="col-md-6">
                                                <a class="quick-action" href="<?php echo site_url(current_lang() . '/loan/loan_application'); ?>" style="border-left-color:#f8ac59;">
                                                    <h3 style="color: #f8ac59;"><i class="fa fa-credit-card fa-2x"></i></h3>
                                                    <p>Process Loan Application</p>
                                                </a>
                                            </div>
                                            <div class="col-md-6">
                                                <a class="quick-action" href="<?php echo site_url(current_lang() . '/saving/credit_debit'); ?>" style="border-left-color:#23c6c8;">
                                                    <h3 style="color: #23c6c8;"><i class="fa fa-bank fa-2x"></i></h3>
                                                    <p>Savings Deposit</p>
                                                </a>
                                            </div>
                                            <div class="col-md-6">
                                                <a class="quick-action" href="<?php echo site_url(current_lang() . '/report/index'); ?>" style="border-left-color:#ed5565;">
                                                    <h3 style="color: #ed5565;"><i class="fa fa-file-text fa-2x"></i></h3>
                                                    <p>Generate Reports</p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5><i class="fa fa-bar-chart"></i> Member Growth Summary</h5>
                                        <div class="ibox-tools">
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
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
                <strong><?php echo htmlspecialchars($company_name); ?></strong>
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

    <!-- Leaflet / OpenStreetMap -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <script>
        $(document).ready(function() {
            // Members Address Map + office-to-member directions
            var memberMapLocations = <?php echo json_encode(isset($member_map_locations) ? $member_map_locations : array()); ?>;
            var officeMapLocation = <?php echo json_encode(isset($office_map_location) ? $office_map_location : null); ?>;
            if (typeof L !== 'undefined' && $('#members-osm-map').length && memberMapLocations && memberMapLocations.length) {
                var map = L.map('members-osm-map', {
                    scrollWheelZoom: false
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                var officeLat = officeMapLocation ? parseFloat(officeMapLocation.lat) : 10.1365976;
                var officeLng = officeMapLocation ? parseFloat(officeMapLocation.lng) : 124.3132049;
                var officeLabel = (officeMapLocation && officeMapLocation.label) ? officeMapLocation.label : 'Office';
                var officeAddress = (officeMapLocation && officeMapLocation.address) ? officeMapLocation.address : '';
                var routingControl = null;

                var officeIcon = L.divIcon({
                    className: '',
                    html: '<div class="office-map-marker" title="Office"><i class="fa fa-building"></i></div>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                L.marker([officeLat, officeLng], { icon: officeIcon, zIndexOffset: 1000 })
                    .bindPopup('<strong>' + $('<div/>').text(officeLabel).html() + '</strong><br>' +
                        $('<div/>').text(officeAddress || 'Office location').html() +
                        '<br><small>Directions start from here</small>')
                    .addTo(map);

                function showRouteTo(lat, lng, addressLabel) {
                    if (typeof L.Routing === 'undefined') {
                        return;
                    }
                    if (routingControl) {
                        map.removeControl(routingControl);
                        routingControl = null;
                    }
                    routingControl = L.Routing.control({
                        waypoints: [
                            L.latLng(officeLat, officeLng),
                            L.latLng(lat, lng)
                        ],
                        routeWhileDragging: false,
                        addWaypoints: false,
                        draggableWaypoints: false,
                        fitSelectedRoutes: true,
                        showAlternatives: false,
                        createMarker: function() { return null; },
                        lineOptions: {
                            styles: [
                                { color: '#1c84c6', opacity: 0.85, weight: 6 }
                            ]
                        },
                        router: L.Routing.osrmv1({
                            serviceUrl: 'https://router.project-osrm.org/route/v1',
                            profile: 'driving'
                        })
                    }).addTo(map);

                    routingControl.on('routesfound', function(e) {
                        if (!e.routes || !e.routes.length) {
                            return;
                        }
                        var summary = e.routes[0].summary;
                        var km = (summary.totalDistance / 1000).toFixed(1);
                        var mins = Math.round(summary.totalTime / 60);
                        var panel = $('.leaflet-routing-container .leaflet-routing-alt h2').first();
                        if (panel.length) {
                            panel.text('Office → ' + (addressLabel || 'Member') + ' (' + km + ' km, ~' + mins + ' min)');
                        }
                    });
                }

                var bounds = [[officeLat, officeLng]];
                $.each(memberMapLocations, function(i, loc) {
                    var lat = parseFloat(loc.lat);
                    var lng = parseFloat(loc.lng);
                    if (isNaN(lat) || isNaN(lng)) {
                        return;
                    }

                    var count = parseInt(loc.member_count, 10) || 1;
                    var radius = Math.min(18, Math.max(8, 6 + Math.sqrt(count) * 2.5));
                    var addressText = loc.address || 'Address';
                    var namesHtml = '';
                    if (loc.members && loc.members.length) {
                        namesHtml = '<ul>';
                        for (var n = 0; n < loc.members.length; n++) {
                            var m = loc.members[n];
                            namesHtml += '<li>' + $('<div/>').text((m.member_id ? m.member_id + ' — ' : '') + (m.name || '')).html() + '</li>';
                        }
                        if (count > loc.members.length) {
                            namesHtml += '<li><em>+' + (count - loc.members.length) + ' more</em></li>';
                        }
                        namesHtml += '</ul>';
                    }

                    var popup = '<strong>' + $('<div/>').text(addressText).html() + '</strong><br>' +
                        count + ' member' + (count === 1 ? '' : 's') + namesHtml +
                        '<br><small style="color:#1c84c6;"><i class="fa fa-road"></i> Click pin for directions from office</small>';

                    var marker = L.circleMarker([lat, lng], {
                        radius: radius,
                        color: '#1c84c6',
                        weight: 2,
                        fillColor: '#1c84c6',
                        fillOpacity: 0.65
                    }).bindPopup(popup).addTo(map);

                    marker.on('click', function() {
                        showRouteTo(lat, lng, addressText);
                    });

                    bounds.push([lat, lng]);
                });

                if (bounds.length === 1) {
                    map.setView(bounds[0], 13);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
                } else {
                    map.setView([officeLat, officeLng], 12);
                }

                setTimeout(function() { map.invalidateSize(); }, 200);
            }

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


