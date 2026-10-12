<?php
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$val = function ($k, $d = '') use ($s) { return isset($s[$k]) && $s[$k] !== null ? $s[$k] : $d; };
$this->load->view('member_app/_flash');
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-7">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>PayMongo (QR Ph) for the Member Loan App</h5></div>
                <div class="ibox-content">
                    <?php if ($readiness['ok']) { ?>
                        <div class="alert alert-success">Members can pay loans online<?php echo $test_mode ? ' &mdash; <strong>TEST MODE</strong> (no real money)' : ''; ?>.</div>
                    <?php } else { ?>
                        <div class="alert alert-warning"><strong>Not live yet:</strong> <?php echo $e($readiness['message']); ?></div>
                    <?php } ?>

                    <form method="post" class="form-horizontal" autocomplete="off">
                        <?php echo $csrf_field; ?>
                        <input type="hidden" name="action" value="gateway">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Online payments</label>
                            <div class="col-sm-8 checkbox"><label><input type="checkbox" name="enabled" value="1" <?php echo $val('enabled') == 1 ? 'checked' : ''; ?>> Enabled for members</label></div>
                        </div>
                        <?php foreach (array('public_key' => array('Public key', 'pk_test_… / pk_live_…'), 'secret_key' => array('Secret key', 'sk_test_… / sk_live_…'), 'webhook_secret' => array('Webhook secret', 'whsk_…')) as $field => $meta) { ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo $e($meta[0]); ?></label>
                                <div class="col-sm-8">
                                    <input type="password" name="<?php echo $field; ?>" class="form-control" placeholder="<?php echo $masked[$field] !== '' ? $e($masked[$field]) . ' (leave blank to keep)' : $e($meta[1]); ?>" autocomplete="new-password">
                                    <?php if ($masked[$field] !== '') { ?>
                                        <label class="small text-muted"><input type="checkbox" name="clear_<?php echo $field; ?>" value="1"> Remove saved value</label>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Minimum payment</label>
                            <div class="col-sm-8"><input type="number" step="0.01" min="20" name="min_amount" class="form-control" value="<?php echo $e($val('min_amount', '20.00')); ?>"><span class="help-block">PayMongo's floor is PHP 20.00.</span></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Convenience fee</label>
                            <div class="col-sm-8">
                                <div class="checkbox"><label><input type="checkbox" name="fee_enabled" value="1" <?php echo $val('fee_enabled') == 1 ? 'checked' : ''; ?>> Charge the member a fee on top</label></div>
                                <div class="row">
                                    <div class="col-xs-6"><div class="input-group"><input type="number" step="0.001" min="0" max="10" name="fee_percent" class="form-control" value="<?php echo $e($val('fee_percent', '0')); ?>"><span class="input-group-addon">%</span></div></div>
                                    <div class="col-xs-6"><div class="input-group"><span class="input-group-addon">+ PHP</span><input type="number" step="0.01" min="0" name="fee_fixed" class="form-control" value="<?php echo $e($val('fee_fixed', '0')); ?>"></div></div>
                                </div>
                                <span class="help-block">The fee is never applied to the loan.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Fee GL account</label>
                            <div class="col-sm-8">
                                <select name="fee_gl_account" class="form-control">
                                    <option value="">-- Not set (fee stays off) --</option>
                                    <?php foreach ($account_list as $group) {
                                        if (empty($group['data'])) {
                                            continue;
                                        }
                                        $group_name = isset($group['info']->name) ? $group['info']->name : '';
                                        ?>
                                        <optgroup label="<?php echo $e($group_name); ?>">
                                            <?php foreach ($group['data'] as $acc) { ?>
                                                <option value="<?php echo $e($acc->account); ?>" <?php echo (string) $val('fee_gl_account') === (string) $acc->account ? 'selected' : ''; ?>><?php echo $e($acc->account . ' - ' . $acc->name); ?></option>
                                            <?php } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                                <span class="help-block">Income account credited with the fee (e.g. service / processing fee income). Each fee gets its own unposted Cash Receipt: debit the "<?php echo $e($payment_method ? $payment_method->name : 'Online - PayMongo'); ?>" account, credit this account.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Statement description</label>
                            <div class="col-sm-8"><input type="text" name="description_prefix" maxlength="100" class="form-control" value="<?php echo $e($val('description_prefix', 'Loan Payment')); ?>"></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Note shown to members</label>
                            <div class="col-sm-8"><input type="text" name="receipt_note" maxlength="255" class="form-control" value="<?php echo $e($val('receipt_note')); ?>" placeholder="e.g. Payments made after 3 PM are posted the next business day."></div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">Save settings</button>
                            </div>
                        </div>
                    </form>
                    <form method="post" style="display:inline">
                        <?php echo $csrf_field; ?>
                        <input type="hidden" name="action" value="test">
                        <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-plug"></i> Test saved secret key</button>
                    </form>
                </div>
            </div>

            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Loan products members can apply for in the app</h5></div>
                <div class="ibox-content">
                    <p class="text-muted small">Tick the products to offer. If none are ticked, every product is offered. A maximum amount limits app applications only; staff can still approve more at the office.</p>
                    <form method="post">
                        <?php echo $csrf_field; ?>
                        <input type="hidden" name="action" value="products">
                        <table class="table table-condensed">
                            <thead><tr><th style="width:40px"></th><th>Product</th><th>Rate</th><th>Max term</th><th style="width:170px">App max amount</th></tr></thead>
                            <tbody>
                            <?php foreach ($products as $p) { ?>
                                <tr>
                                    <td><input type="checkbox" name="product_ids[]" value="<?php echo (int) $p->id; ?>" <?php echo array_key_exists((int) $p->id, $chosen) ? 'checked' : ''; ?>></td>
                                    <td><strong><?php echo $e($p->name); ?></strong><br><small class="text-muted"><?php echo $e($p->description); ?></small></td>
                                    <td><?php echo $e($p->interest_rate); ?>%</td>
                                    <td><?php echo (int) $p->maxmum_time; ?></td>
                                    <td><input type="number" step="0.01" min="0" class="form-control input-sm" name="max_amount[<?php echo (int) $p->id; ?>]" value="<?php echo isset($chosen[(int) $p->id]) ? $e($chosen[(int) $p->id]) : ''; ?>" placeholder="No limit"></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <button type="submit" class="btn btn-primary">Save products</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="ibox float-e-margins">
                <div class="ibox-title"><h5>Setup checklist</h5></div>
                <div class="ibox-content">
                    <ol style="padding-left:18px">
                        <li style="margin-bottom:8px">Member app address: <br><code><?php echo $e($app_url); ?></code></li>
                        <li style="margin-bottom:8px">In the PayMongo dashboard, add a webhook for <strong>payment.paid</strong> and <strong>payment.failed</strong> to:<br><code style="word-break:break-all"><?php echo $e($webhook_url); ?></code><br>then paste its secret above. The site must be on <strong>HTTPS</strong>.</li>
                        <li style="margin-bottom:8px">Payment method <strong><?php echo $e($payment_method ? $payment_method->name : 'Online - PayMongo'); ?></strong> GL account:
                            <?php if ($payment_method && trim((string) $payment_method->gl_account_code) !== '') { ?>
                                <span class="label label-success"><?php echo $e($payment_method->gl_account_code); ?></span>
                            <?php } else { ?>
                                <span class="label label-warning">Not set</span> &mdash; set it in <a href="<?php echo site_url('payment_method_config'); ?>">Payment Method Configuration</a> (usually the PayMongo clearing / bank account).
                            <?php } ?>
                        </li>
                        <li style="margin-bottom:8px">Test with <code>sk_test_</code> keys first; the app shows a "Simulate payment" link in test mode.</li>
                        <li>Give staff the <em>Online_loan_payments</em>, <em>Paymongo_settings</em> and <em>Member_app_access</em> rights in User Group access as needed.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
