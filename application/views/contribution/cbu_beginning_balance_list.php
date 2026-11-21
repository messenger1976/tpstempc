<?php echo form_open(current_lang() . "/contribution/cbu_beginning_balance_list", 'class="form-horizontal"'); ?>

<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}

$search_key = isset($jxy['key']) ? $jxy['key'] : (isset($_GET['key']) ? $_GET['key'] : '');
$bb_as_of = isset($bb_as_of) ? $bb_as_of : '2024-12-31';
$bb_coa_summary = isset($bb_coa_summary) ? $bb_coa_summary : array();
$bb_net_total = 0;
foreach ($bb_coa_summary as $bb_row) {
    $bb_net_total += isset($bb_row->net_credit) ? floatval($bb_row->net_credit) : 0;
}
?>

<div class="form-group col-lg-12">
    <div class="col-lg-4">
        <input type="text" class="form-control" name="key" placeholder="<?php echo lang('cash_receipt_search_placeholder_member'); ?>" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
</div>
<?php echo form_close(); ?>

<div class="form-group col-lg-12" style="margin-top: 10px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><?php echo lang('cbu_bb_gl_tools_title'); ?></strong>
        </div>
        <div class="panel-body">
            <p class="text-muted" style="margin-bottom: 12px;"><?php echo lang('cbu_bb_gl_tools_help'); ?></p>
            <?php echo form_open(current_lang() . '/contribution/tag_cbu_bb_as_of', array('class' => 'form-inline', 'style' => 'margin-bottom:10px;', 'onsubmit' => "return confirm('" . addslashes(lang('cbu_bb_tag_confirm')) . "');")); ?>
                <label><?php echo lang('savings_bb_as_of_date'); ?>:</label>
                <input type="date" name="bb_as_of" value="<?php echo htmlspecialchars($bb_as_of, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" style="margin:0 8px;"/>
                <button type="submit" class="btn btn-default btn-sm"><?php echo lang('cbu_bb_tag_button'); ?></button>
            <?php echo form_close(); ?>

            <?php echo form_open(current_lang() . '/contribution/strip_cbu_bb_gl_offsets', array('class' => 'form-inline', 'style' => 'margin-bottom:10px;', 'onsubmit' => "return confirm('" . addslashes(lang('cbu_bb_strip_confirm')) . "');")); ?>
                <input type="hidden" name="bb_as_of" value="<?php echo htmlspecialchars($bb_as_of, ENT_QUOTES, 'UTF-8'); ?>"/>
                <button type="submit" class="btn btn-warning btn-sm"><?php echo lang('cbu_bb_strip_button'); ?></button>
            <?php echo form_close(); ?>

            <?php echo form_open(current_lang() . '/contribution/post_cbu_bb_to_gl', array('class' => 'form-inline', 'style' => 'margin-bottom:10px;', 'onsubmit' => "return confirm('" . addslashes(lang('cbu_bb_post_confirm')) . "');")); ?>
                <input type="hidden" name="bb_as_of" value="<?php echo htmlspecialchars($bb_as_of, ENT_QUOTES, 'UTF-8'); ?>"/>
                <button type="submit" class="btn btn-success btn-sm"><?php echo lang('cbu_bb_post_button'); ?></button>
            <?php echo form_close(); ?>

            <div class="table-responsive" style="margin-top: 12px;">
                <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th><?php echo lang('cbu_bb_coa'); ?></th>
                            <th><?php echo lang('account_type_name'); ?></th>
                            <th style="text-align:right;"><?php echo lang('savings_bb_credit_total'); ?></th>
                            <th style="text-align:right;"><?php echo lang('savings_bb_debit_total'); ?></th>
                            <th style="text-align:right;"><?php echo lang('savings_bb_net_credit'); ?></th>
                            <th style="text-align:right;"><?php echo lang('savings_bb_tx_count'); ?></th>
                            <th style="text-align:right;"><?php echo lang('savings_bb_unposted'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bb_coa_summary)) { ?>
                            <?php foreach ($bb_coa_summary as $bb_row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bb_row->coa_account, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($bb_row->coa_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td style="text-align:right;"><?php echo number_format($bb_row->credit_total, 2, '.', ','); ?></td>
                                    <td style="text-align:right;"><?php echo number_format($bb_row->debit_total, 2, '.', ','); ?></td>
                                    <td style="text-align:right;"><?php echo number_format($bb_row->net_credit, 2, '.', ','); ?></td>
                                    <td style="text-align:right;"><?php echo intval($bb_row->tx_count); ?></td>
                                    <td style="text-align:right;"><?php echo intval($bb_row->unposted_count); ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="4" style="text-align:right;"><strong><?php echo lang('savings_bb_net_total'); ?></strong></td>
                                <td style="text-align:right;"><strong><?php echo number_format($bb_net_total, 2, '.', ','); ?></strong></td>
                                <td colspan="2"></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" style="text-align:center;"><?php echo lang('cbu_bb_no_summary'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="form-group col-lg-12" style="margin-top: 20px;">
    <div class="col-lg-12">
        <div class="alert alert-info" style="font-size: 16px; font-weight: bold;">
            <strong><?php echo lang('cbu_bb_total_balance'); ?>: </strong>
            <?php echo number_format(isset($total_cbu_amount) ? $total_cbu_amount : 0, 2, '.', ','); ?>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('member_pid'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('member_name'); ?></th>
                <th style="text-align: right;"><?php echo lang('balance'); ?></th>
                <th><?php echo lang('savings_bb_as_of_date'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($cbu_accounts) && count($cbu_accounts) > 0) { ?>
                <?php foreach ($cbu_accounts as $value) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php
                            if (!empty($value->firstname) || !empty($value->lastname)) {
                                echo htmlspecialchars(trim($value->lastname . ', ' . $value->firstname . ' ' . $value->middlename), ENT_QUOTES, 'UTF-8');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td style="text-align: right;"><?php echo number_format($value->balance, 2, '.', ','); ?></td>
                        <td><?php echo !empty($value->bb_date) ? htmlspecialchars(date('Y-m-d', strtotime($value->bb_date)), ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center;"><?php echo lang('no_records_found'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php if (isset($links)) { ?>
    <div class="form-group col-lg-12">
        <div class="col-lg-12">
            <?php echo $links; ?>
        </div>
    </div>
<?php } ?>
