<?php echo form_open(current_lang() . "/saving/savings_beginning_balance_list", 'class="form-horizontal"'); ?>

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
$status_filter = isset($status_filter) ? $status_filter : (isset($_GET['status_filter']) ? $_GET['status_filter'] : '1');
?>

<div class="form-group col-lg-12">
    <div class="col-lg-3">
        <input type="text" class="form-control" id="accountno" name="key" placeholder="<?php echo lang('search_account_member'); ?>" value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>"/> 
    </div>
    <div class="col-lg-2">
        <select name="account_type_filter" class="form-control">
            <option value="all" <?php echo ($account_type_filter == 'all' ? 'selected="selected"' : ''); ?>>All</option>
            <option value="special" <?php echo ($account_type_filter == 'special' ? 'selected="selected"' : ''); ?>>Special</option>
            <option value="mso" <?php echo ($account_type_filter == 'mso' ? 'selected="selected"' : ''); ?>>MSO</option>
        </select>
    </div>
    <div class="col-lg-2">
        <select name="status_filter" class="form-control">
            <option value="all" <?php echo ($status_filter == 'all' ? 'selected="selected"' : ''); ?>><?php echo lang('all_status'); ?></option>
            <option value="1" <?php echo ($status_filter == '1' ? 'selected="selected"' : ''); ?>><?php echo lang('account_status_active'); ?></option>
            <option value="0" <?php echo ($status_filter == '0' ? 'selected="selected"' : ''); ?>><?php echo lang('account_status_inactive'); ?></option>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
    <div class="col-lg-3" style="text-align: right;">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createBeginningBalanceModal">
            <?php echo lang('create_savings_beginning_balance'); ?>
        </button>
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
                <th style="text-align: center; width: 100px;"><?php echo lang('account_status'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($saving_accounts) && count($saving_accounts) > 0) { ?>
                <?php foreach ($saving_accounts as $key => $value) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($value->account, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
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
                        <td><?php echo htmlspecialchars($value->old_members_acct ? $value->old_members_acct : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($value->account_type_name_display ? $value->account_type_name_display : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->balance, 2, '.', ','); ?></td>
                        <td style="text-align: right;"><?php echo number_format($value->virtual_balance, 2, '.', ','); ?></td>
                        <td style="text-align: center;">
                            <?php 
                            $status_value = isset($value->status) ? $value->status : '1';
                            $status_class = ($status_value == '1' || $status_value === 1) ? 'btn-success' : 'btn-danger';
                            $status_text = ($status_value == '1' || $status_value === 1) ? lang('account_status_active') : lang('account_status_inactive');
                            ?>
                            <span class="btn <?php echo $status_class; ?> btn-xs" style="cursor: default;">
                                <?php echo htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="8" style="text-align: center;"><?php echo lang('no_records_found'); ?></td>
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

<!-- Create Beginning Balance Modal -->
<div class="modal fade" id="createBeginningBalanceModal" tabindex="-1" role="dialog" aria-labelledby="createBeginningBalanceModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="createBeginningBalanceModalLabel"><?php echo lang('create_savings_beginning_balance'); ?></h4>
            </div>
            <div class="modal-body">
                <div id="beginningBalanceFormContainer">
                    <?php 
                    // Pass variables to the form view - use data from controller
                    $form_data = array(
                        'account_list' => isset($account_list) ? $account_list : (isset($this->data['account_list']) ? $this->data['account_list'] : array()),
                        'paymenthod' => isset($paymenthod) ? $paymenthod : (isset($this->data['paymenthod']) ? $this->data['paymenthod'] : array())
                    );
                    $this->load->view('saving/beginning_balance_form', $form_data); 
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Wait for jQuery to be available
(function() {
    function waitForJQuery() {
        if (typeof jQuery === 'undefined') {
            setTimeout(waitForJQuery, 50);
            return;
        }
        
        jQuery(document).ready(function($) {
            // Handle form submission via AJAX
            $(document).on('submit', '#beginningBalanceForm', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                var submitBtn = $(this).find('input[type="submit"]');
                var originalText = submitBtn.val();
                
                submitBtn.prop('disabled', true).val('<?php echo lang("please_wait"); ?>...');
                
                $.ajax({
                    url: '<?php echo site_url(current_lang() . "/saving/create_savings_beginning_balance"); ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success == 'Y') {
                            alert(response.message);
                            $('#createBeginningBalanceModal').modal('hide');
                            location.reload();
                        } else {
                            alert(response.message || '<?php echo lang("error_occurred"); ?>');
                            submitBtn.prop('disabled', false).val(originalText);
                        }
                    },
                    error: function() {
                        alert('<?php echo lang("error_occurred"); ?>');
                        submitBtn.prop('disabled', false).val(originalText);
                    }
                });
            });
            
            // Load form content when modal is shown
            $('#createBeginningBalanceModal').on('show.bs.modal', function() {
                // Reset form
                var form = document.getElementById('beginningBalanceForm');
                if (form) {
                    form.reset();
                }
                $('#beginning_member_info').html('');
                // Reset datepicker to today
                var dateInput = $('#beginning_datepicker input');
                if (dateInput.length) {
                    dateInput.val('<?php echo date('d-m-Y'); ?>');
                }
            });
        });
    }
    
    waitForJQuery();
})();
</script>
