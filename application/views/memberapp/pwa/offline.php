<?php $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $this->load->view('memberapp/layout/head', array('title' => 'Offline')); ?>
</head>
<body class="ma-auth-body">
<main class="ma-offline-page">
    <img src="<?php echo base_url('media/memberapp/icons/icon-192.png'); ?>" alt="" width="88" height="88">
    <h1>You are offline</h1>
    <p>This page needs an internet connection. If you have signed in on this phone before, your loans are still available.</p>
    <a class="ma-btn ma-btn-lg" href="<?php echo $e($base); ?>customer_login/app">Open my loans</a>
    <button type="button" class="ma-btn ma-btn-outline ma-btn-lg" onclick="location.reload()">Try again</button>
</main>
</body>
</html>
