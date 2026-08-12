<?php $this->load->view('loan/list_page_styles'); ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet"/>
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet"/>

<?php
$selected_pid = isset($selected_pid) ? $selected_pid : '';
$selected_product_id = isset($selected_product_id) ? $selected_product_id : 'all';
$selected_member_text = isset($selected_member_text) ? $selected_member_text : '';
$date_from = isset($date_from) ? $date_from : '';
$date_to = isset($date_to) ? $date_to : '';
$loan_products = isset($loan_products) ? $loan_products : array();
?>

<style type="text/css">
.member-list-page .filter-field.date-field { flex: 0 1 150px; min-width: 140px; }
.member-list-page .filter-field.product-field { flex: 1 1 180px; min-width: 160px; }
.member-list-page .filter-field.member-field { flex: 1 1 240px; min-width: 200px; }
.member-list-page .filter-field .select2-container { width: 100% !important; }
.member-list-page .filter-field .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.member-list-page .filter-field .select2-container .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
}
.member-list-page .filter-field .select2-container .select2-selection__arrow { height: 34px; }
.member-list-page .input-group.date .input-group-addon {
    background: #fafbfc;
    border-color: #e5e6e7;
    color: #1ab394;
    cursor: pointer;
}
.bootstrap-datetimepicker-widget {
    z-index: 1060 !important;
    min-width: 280px;
    max-width: 320px;
    padding: 4px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 4px;
    box-shadow: 0 6px 12px rgba(0,0,0,.175);
}
</style>

<div class="col-lg-12 member-list-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="member-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4><?php echo lang('loan_disbursement'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field member-field">
                        <label><?php echo lang('member_name'); ?></label>
                        <select name="pid" id="pid" class="form-control">
                            <option value=""><?php echo lang('loan_search_name'); ?></option>
                            <?php if ($selected_pid !== '' && $selected_member_text !== '') { ?>
                                <option value="<?php echo htmlspecialchars($selected_pid, ENT_QUOTES, 'UTF-8'); ?>" selected="selected">
                                    <?php echo htmlspecialchars($selected_member_text, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-field date-field">
                        <label>From Date</label>
                        <div class="input-group date" id="date_from_picker">
                            <input type="text" name="date_from" id="date_from" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars($date_from, ENT_QUOTES, 'UTF-8'); ?>" data-date-format="DD-MM-YYYY" class="form-control" autocomplete="off"/>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <div class="filter-field date-field">
                        <label>To Date</label>
                        <div class="input-group date" id="date_to_picker">
                            <input type="text" name="date_to" id="date_to" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars($date_to, ENT_QUOTES, 'UTF-8'); ?>" data-date-format="DD-MM-YYYY" class="form-control" autocomplete="off"/>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <div class="filter-field product-field">
                        <label><?php echo lang('loan_product'); ?></label>
                        <select name="product_id" id="product_id" class="form-control">
                            <option value="all"<?php echo ($selected_product_id === 'all' || $selected_product_id === '') ? ' selected="selected"' : ''; ?>>
                                <?php echo lang('loan_products'); ?>
                            </option>
                            <?php foreach ($loan_products as $product) { ?>
                                <option value="<?php echo (int) $product->id; ?>"<?php echo ((string) $selected_product_id === (string) $product->id) ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_disbursement'); ?>" class="btn btn-default btn-clear-filter">
                            <i class="fa fa-undo"></i> Clear
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search"></i> <?php echo lang('button_search'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-money icon-badge"></i>
                <h4><?php echo lang('loan_disbursement'); ?></h4>
            </div>
            <?php
            $total_rows = isset($total_rows) ? (int) $total_rows : count($loan_wait);
            $page_start = isset($page_start) ? (int) $page_start : 0;
            $per_page = isset($per_page) ? (int) $per_page : $total_rows;
            $from_n = $total_rows > 0 ? ($page_start + 1) : 0;
            $to_n = $total_rows > 0 ? min($page_start + $per_page, $total_rows) : 0;
            ?>
            <div class="result-meta">
                Showing <strong><?php echo number_format($from_n); ?>-<?php echo number_format($to_n); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped member-table">
                <thead>
                    <tr>
                        <th><?php echo lang('loan_LID'); ?></th>
                        <th><?php echo lang('member_name'); ?></th>
                        <th><?php echo lang('loan_product'); ?></th>
                        <th><?php echo lang('loan_applicationdate'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_applied_amount'); ?></th>
                        <th style="text-align:center;"><?php echo lang('loan_installment'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_installment_amount'); ?></th>
                        <th style="text-align:right;"><?php echo lang('loan_total_interest'); ?></th>
                        <th><?php echo lang('loan_status'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($loan_wait)) { ?>
                        <?php foreach ($loan_wait as $value) {
                            $member_id_disp = isset($value->member_id) ? $value->member_id : '';
                            $full_name = trim((isset($value->firstname) ? $value->firstname : '') . ' ' . (isset($value->middlename) ? $value->middlename : '') . ' ' . (isset($value->lastname) ? $value->lastname : ''));
                            if ($member_id_disp === '' && !empty($value->PID)) {
                                $info = $this->member_model->member_basic_info(null, $value->PID)->row();
                                if ($info) {
                                    $member_id_disp = $info->member_id;
                                    $full_name = trim($info->firstname . ' ' . $info->middlename . ' ' . $info->lastname);
                                }
                            }
                            $product_name = !empty($value->loan_product_name) ? $value->loan_product_name : (!empty($value->product_name) ? $value->product_name : '-');
                            $interval = $this->setting_model->intervalinfo($value->interval)->row();
                            $interval_desc = $interval ? $interval->description : '';
                            $status_name = !empty($value->status_name) ? $value->status_name : 'Approved & Accepted';
                            $app_date = !empty($value->applicationdate) ? format_date($value->applicationdate, false) : '';
                            ?>
                            <tr>
                                <td><span class="member-id-chip"><?php echo htmlspecialchars($value->LID, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <?php if ($member_id_disp !== '') { ?>
                                        <span class="member-id-chip"><?php echo htmlspecialchars($member_id_disp, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php } ?>
                                    <?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($app_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->basic_amount, 2); ?></td>
                                <td style="text-align:center;"><?php echo htmlspecialchars($value->number_istallment . ($interval_desc !== '' ? ' ' . $interval_desc : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->installment_amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->total_interest_amount, 2); ?></td>
                                <td><span class="status-pill accepted"><?php echo htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <?php echo anchor(current_lang() . "/loan/loan_disburse_entry/" . encode_id($value->LID), ' <i class="fa fa-money"></i> ' . lang('loan_disburse_link'), 'class="btn btn-primary btn-xs"'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa fa-money"></i>
                                    <?php echo lang('no_records_found'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="list-footer">
            <div class="pagination-wrap"><?php echo isset($links) ? $links : ''; ?></div>
            <div class="page-size-wrap"><?php page_selector(); ?></div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script>
(function() {
    function initDatePickers() {
        if (typeof jQuery === 'undefined' || typeof moment === 'undefined') {
            setTimeout(initDatePickers, 50);
            return;
        }
        function bindPickers() {
            if (typeof jQuery.fn.datetimepicker === 'undefined') {
                return false;
            }
            var opts = { pickTime: false, format: 'DD-MM-YYYY' };
            var $from = jQuery('#date_from_picker');
            var $to = jQuery('#date_to_picker');
            if ($from.length && !$from.data('DateTimePicker')) {
                $from.datetimepicker(opts);
            }
            if ($to.length && !$to.data('DateTimePicker')) {
                $to.datetimepicker(opts);
            }
            return true;
        }
        if (!bindPickers()) {
            var script = document.createElement('script');
            script.src = '<?php echo base_url(); ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
            script.onload = function() { bindPickers(); };
            document.head.appendChild(script);
        }
    }

    function initSelect2() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            setTimeout(initSelect2, 50);
            return;
        }
        jQuery('#pid').select2({
            placeholder: <?php echo json_encode(lang('loan_search_name')); ?>,
            allowClear: true,
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: <?php echo json_encode(site_url(current_lang() . '/loan/search_member_select2')); ?>,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term || '' };
                },
                processResults: function(data) {
                    return { results: (data && data.results) ? data.results : [] };
                },
                cache: true
            }
        });
    }

    function boot() {
        initDatePickers();
        initSelect2();
    }

    if (document.readyState === 'complete') {
        boot();
    } else {
        window.addEventListener('load', boot);
        setTimeout(boot, 300);
    }
})();
</script>
