<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$v = $asset_version;
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0f6b3a">
<meta name="color-scheme" content="light">
<meta name="robots" content="noindex, nofollow">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="TAP Loans">
<meta name="format-detection" content="telephone=no">
<title><?php echo $e(isset($title) ? $title . ' · ' : ''); ?><?php echo $e($app_name); ?></title>
<link rel="manifest" href="<?php echo site_url('customer_login/manifest.webmanifest'); ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url('media/memberapp/icons/favicon-32.png'); ?>">
<link rel="apple-touch-icon" href="<?php echo base_url('media/memberapp/icons/apple-touch-icon.png'); ?>">
<link rel="stylesheet" href="<?php echo base_url('media/memberapp/css/app.css'); ?>?v=<?php echo $e($v); ?>">
