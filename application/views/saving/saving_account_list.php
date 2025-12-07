<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/saving/saving_account_list", 'class="form-horizontal"'); ?>

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

$sp = isset($jxy) ? $jxy : array();
$search_key = isset($jxy['key']) ? $jxy['key'] : (isset($_GET['key']) ? $_GET['key'] : '');
$account_type_filter = isset($account_type_filter) ? $account_type_filter : (isset($_GET['account_type_filter']) ? $_GET['account_type_filter'] : 'all');
?>

<div class="form-group col-lg-12">
    <div class="col-lg-3">
        <input type="text" class="form-control" id="accountno" name="key" placeholder="<?php echo lang('search_account_member'); ?>" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/> 
    </div>
    <div class="col-lg-3">
        <select name="account_type_filter" class="form-control">
            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
    <div class="col-lg-4" style="text-align: right;">
        <?php echo anchor(current_lang().'/saving/create_saving_account/', lang('create_saving_account'), 'class="btn btn-primary"'); ?>
    </div>
</div>

<?php echo form_close(); ?>

<!-- Total Amount Display -->
<div class="form-group col-lg-12" style="margin-top: 20px;">
    <div class="col-lg-12">
        <div class="alert alert-info" style="font-size: 16px; font-weight: bold;">
            <strong><?php echo lang('total_savings_amount'); ?>: </strong>
            <?php echo number_format($total_savings_amount, 2, '.', ','); ?>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('account_number'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('member_fullname'); ?></th>
                <th><?php echo lang('member_old_account_no'); ?></th>
                <th><?php echo lang('account_type_name'); ?></th>
                <th style="text-align: right; width: 120px;"><?php echo lang('balance'); ?></th>
                <th style="text-align: right; width: 120px;"><?php echo lang('virtual_balance'); ?></th>
                <th style="text-align: center; width: 100px;"><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($saving_accounts) && count($saving_accounts) > 0) { ?>
                <?php foreach ($saving_accounts as $key => $value) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($value->account, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->member_id_display, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php 
                            if ($value->tablename == 'members_grouplist' && $value->group_name) {
                                echo htmlspecialchars($value->group_name, ENT_QUOTES, 'UTF-8');
                            } else if (($value->firstname || $value->lastname)) {
                                echo htmlspecialchars(trim($value->lastname . ', ' . $value->firstname . ' ' . $value->middlename), ENT_QUOTES, 'UTF-8');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <!--<td>
                            <?php 
                            if ($value->tablename == 'members_grouplist' && $value->group_name) {
                                echo htmlspecialchars($value->group_name, ENT_QUOTES, 'UTF-8');
                            } else if (($value->firstname || $value->lastname)) {
                                echo htmlspecialchars(trim($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname), ENT_QUOTES, 'UTF-8');
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>-->
                        <td><?php echo htmlspecialchars($value->old_members_acct ? $value->old_members_acct : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->account_type_name_display ? $value->account_type_name_display : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->balance, 2, '.', ','); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->virtual_balance, 2, '.', ','); ?></td>
                        <td style="text-align: center;">
                            <?php echo anchor(current_lang() . "/saving/edit_saving_account/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), 'class="btn btn-success btn-xs btn-outline"'); ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="9" style="text-align: center;"><?php echo lang('no_records_found'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js" ></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
            matchContains:true
        });
    });
</script>
