<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$this->load->view('member_app/_flash');
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-6">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Member Loan App access</h5></div>
                <div class="ibox-content">
                    <p class="text-muted small">Use this when a member cannot receive the activation code by email or SMS, or is locked out. Check the member's ID card first.</p>
                    <form method="get" class="form-inline" style="margin-bottom:15px">
                        <input type="text" name="member_id" class="form-control" value="<?php echo $e($member_id); ?>" placeholder="Member ID" required>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Find</button>
                    </form>

                    <?php if ($member_id !== '' && !$member) { ?>
                        <div class="alert alert-warning">No active member with Member ID <?php echo $e($member_id); ?>.</div>
                    <?php } ?>

                    <?php if ($member) { ?>
                        <table class="table table-condensed">
                            <tr><th style="width:40%">Member</th><td><?php echo $e(trim($member->firstname . ' ' . $member->lastname)); ?> (<?php echo $e($member->member_id); ?>)</td></tr>
                            <tr><th>App account</th><td>
                                <?php if (!$account) { ?>
                                    <span class="label label-default">Not activated</span>
                                <?php } elseif ((string) $account->active === '0') { ?>
                                    <span class="label label-danger">Inactive</span>
                                <?php } else { ?>
                                    <span class="label label-success">Active</span>
                                <?php } ?>
                            </td></tr>
                            <?php if ($account && !empty($account->last_login)) { ?>
                                <tr><th>Last sign-in</th><td><?php echo $e(date('d-m-Y H:i', (int) $account->last_login)); ?></td></tr>
                            <?php } ?>
                        </table>

                        <?php if ($issued_code) { ?>
                            <div class="alert alert-success text-center">
                                <div>One-time code for <?php echo $e($member->member_id); ?> (valid 10 minutes):</div>
                                <div style="font-size:32px;font-weight:bold;letter-spacing:8px"><?php echo $e($issued_code); ?></div>
                                <div class="small">The member opens the app &rarr; "Got a code from the cooperative office?" and enters it with their birthdate, then chooses a password. Do not write it down or send it to anyone else.</div>
                            </div>
                        <?php } ?>

                        <form method="post" style="display:inline">
                            <?php echo $csrf_field; ?>
                            <input type="hidden" name="member_id" value="<?php echo $e($member->member_id); ?>">
                            <input type="hidden" name="action" value="code">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-key"></i> Issue office code</button>
                        </form>
                        <form method="post" style="display:inline">
                            <?php echo $csrf_field; ?>
                            <input type="hidden" name="member_id" value="<?php echo $e($member->member_id); ?>">
                            <input type="hidden" name="action" value="unlock">
                            <button type="submit" class="btn btn-default"><i class="fa fa-unlock"></i> Clear sign-in limits</button>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
