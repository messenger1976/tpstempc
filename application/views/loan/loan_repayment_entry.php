<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
/* Bootstrap datepicker dropdown - ensure visible and styled */
.datepicker.dropdown-menu { position: absolute; z-index: 9999 !important; }
.datepicker { border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.datepicker table { margin: 0; }
.datepicker td, .datepicker th { padding: 6px 8px; text-align: center; }
.datepicker td.active, .datepicker td.active:hover { background: #337ab7; color: #fff; }
.datepicker td.today { background: #f0f0f0; }
.input-group.date .input-group-addon { cursor: pointer; }
</style>

<?php echo form_open(current_lang() . '/loan/loan_repayment_process', 'class="form-horizontal" id="loanRepaymentForm"'); ?>

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
?>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><?php echo lang('loan_repayment'); ?> - <?php echo lang('loan_repay_btn'); ?></h5>
                    <div class="ibox-tools">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-white btn-xs">
                            <i class="fa fa-arrow-left"></i> <?php echo lang('back'); ?>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">

                    <input type="hidden" name="loanid" value="<?php echo htmlspecialchars(isset($loaninfo) ? $loaninfo->LID : ''); ?>"/>

                    <!-- Loan / Member (read-only) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('loan_LID'); ?> :</label>
                                <div class="col-lg-8">
                                    <p class="form-control-static"><strong><?php echo htmlspecialchars(isset($loaninfo) ? $loaninfo->LID : ''); ?></strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('member_name'); ?> :</label>
                                <div class="col-lg-8">
                                    <?php
                                    $member_display = '';
                                    if (isset($loaninfo) && !empty($loaninfo->PID)) {
                                        $info = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
                                        $member_display = $info ? ($info->member_id . ' : ' . $info->firstname . ' ' . $info->middlename . ' ' . $info->lastname) : ($loaninfo->firstname . ' ' . $loaninfo->middlename . ' ' . $loaninfo->lastname);
                                    }
                                    ?>
                                    <p class="form-control-static"><?php echo htmlspecialchars($member_display); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receipt No & Payment Date -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_no'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="receipt_no" value="<?php echo set_value('receipt_no', isset($next_receipt_no) ? $next_receipt_no : 'CR-00001'); ?>" class="form-control" required/>
                                    <?php echo form_error('receipt_no'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('loan_repay_date'); ?> : <span class="required">*</span></label>
                                    <div class="col-lg-8">
                                    <div class="input-group date" id="repaydatepicker">
                                        <input type="text" name="repaydate" placeholder="DD-MM-YYYY" readonly
                                            value="<?php echo set_value('repaydate', date('d-m-Y')); ?>"
                                            class="form-control" required/>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    <?php echo form_error('repaydate'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amount & Payment Method -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('loan_repay_amount'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" name="amount" value="<?php echo set_value('amount'); ?>" class="form-control amountformat" required/>
                                    <?php echo form_error('amount'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-lg-4 control-label"><?php echo lang('cash_receipt_payment_method'); ?> : <span class="required">*</span></label>
                                <div class="col-lg-8">
                                    <select name="payment_method" id="payment_method" class="form-control" required>
                                        <option value=""><?php echo lang('select_default_text'); ?></option>
                                        <?php
                                        $selected_pm = set_value('payment_method', isset($default_payment_method_id) ? $default_payment_method_id : '');
                                        if (isset($payment_methods) && is_array($payment_methods)) {
                                            foreach ($payment_methods as $pm_id => $pm_name) {
                                                $sel = ((string)$selected_pm !== '' && (string)$selected_pm === (string)$pm_id) ? 'selected="selected"' : '';
                                                echo '<option value="' . htmlspecialchars($pm_id) . '" ' . $sel . '>' . htmlspecialchars($pm_name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <?php echo form_error('payment_method'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr/>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> <?php echo lang('save'); ?>
                            </button>
                            <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-white">
                                <?php echo lang('cancel'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script>
(function(){
    var REPAY_CONTAINER_ID = 'repaydatepicker';
    var CDN_DP = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js';

    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        s.onerror = function() { if (cb) cb(); };
        (document.head || document.documentElement).appendChild(s);
    }

    function initDatepicker() {
        if (!window.jQuery) {
            setTimeout(initDatepicker, 100);
            return;
        }
        var $ = window.jQuery;
        var $container = $('#' + REPAY_CONTAINER_ID);
        if (!$container.length) return;

        // Template loads Eonasdan datetimepicker (different plugin). Always load standard
        // bootstrap-datepicker from CDN so the calendar works on live and uses Bootstrap style.
        loadScript(CDN_DP, function() {
            if (!$.fn.datepicker) return;
            var $c = $('#' + REPAY_CONTAINER_ID);
            var $input = $c.find('input');
            if (!$c.length || !$input.length) return;
            $c.datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom auto',
                todayBtn: 'linked',
                clearBtn: false,
                container: 'body',
                keyboardNavigation: true
            });
            $c.find('.input-group-addon').on('click', function() {
                $input.focus();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { setTimeout(initDatepicker, 150); });
    } else {
        setTimeout(initDatepicker, 150);
    }

    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('loanRepaymentForm');
        if (form) {
            form.addEventListener('submit', function() {
                var inp = form.querySelector('input[name="amount"]');
                if (inp && inp.value) inp.value = String(inp.value).replace(/,/g, '');
            });
        }
    });
})();
</script>
