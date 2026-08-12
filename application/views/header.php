<?php
$user = function_exists('current_user') ? current_user() : null;
$user_label = 'User';
$user_role = 'Signed in';
if ($user) {
    $full = trim((isset($user->first_name) ? $user->first_name : '') . ' ' . (isset($user->last_name) ? $user->last_name : ''));
    if ($full !== '') {
        $user_label = $full;
    } else if (!empty($user->username)) {
        $user_label = $user->username;
    }
}
?>
<div class="row border-bottom">
    <nav class="navbar navbar-static-top white-bg" role="navigation">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#" title="Toggle navigation">
                <i class="fa fa-bars"></i>
            </a>
        </div>
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <span class="topbar-user">
                    <span class="user-avatar"><i class="fa fa-user"></i></span>
                    <span class="user-meta">
                        <strong><?php echo htmlspecialchars($user_label, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <span><?php echo htmlspecialchars($user_role, ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </span>
            </li>
            <li>
                <a class="topbar-logout" href="<?php echo site_url('auth/logout'); ?>">
                    <i class="fa fa-sign-out"></i> <?php echo lang('logout'); ?>
                </a>
            </li>
        </ul>
    </nav>
</div>
