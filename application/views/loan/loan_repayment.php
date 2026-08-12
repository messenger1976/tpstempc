<?php $this->load->view('loan/list_page_styles'); ?>

<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet"/>

<?php
$search_key = isset($search_key) ? $search_key : '';
$date_from = isset($date_from) ? $date_from : '';
$date_to = isset($date_to) ? $date_to : '';
$selected_product_id = isset($selected_product_id) ? $selected_product_id : 'all';
$loan_products = isset($loan_products) ? $loan_products : array();
$loan_list = isset($loan_list) ? $loan_list : array();
?>

<style type="text/css">
.member-list-page .filter-field.date-field { flex: 0 1 150px; min-width: 140px; }
.member-list-page .filter-field.product-field { flex: 1 1 180px; min-width: 160px; }
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
                <h4><?php echo lang('loan_repayment'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo site_url(current_lang() . "/loan/loan_repayment"); ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label><?php echo lang('loan_LID'); ?> / <?php echo lang('member_name'); ?></label>
                        <input type="text" class="form-control" name="key" id="loan_search_key" placeholder="Loan No, Member ID or Name..." value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                        <div id="loan-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
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
                        <select name="product_id" class="form-control">
                            <option value="all"<?php echo ($selected_product_id === 'all' || $selected_product_id === '') ? ' selected="selected"' : ''; ?>>All Products</option>
                            <?php foreach ($loan_products as $product) { ?>
                                <option value="<?php echo (int) $product->id; ?>"<?php echo ((string) $selected_product_id === (string) $product->id) ? ' selected="selected"' : ''; ?>>
                                    <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment'); ?>" class="btn btn-default btn-clear-filter">
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
                <h4><?php echo lang('loan_repayment'); ?></h4>
            </div>
            <?php
            $total_rows = isset($total_rows) ? (int) $total_rows : count($loan_list);
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
                        <th style="text-align:right;"><?php echo lang('loan_total'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($loan_list)) { ?>
                        <?php foreach ($loan_list as $value) {
                            $member_id_disp = isset($value->member_id) ? $value->member_id : '';
                            $full_name = trim((isset($value->firstname) ? $value->firstname : '') . ' ' . (isset($value->middlename) ? $value->middlename : '') . ' ' . (isset($value->lastname) ? $value->lastname : ''));
                            if ($member_id_disp === '' && !empty($value->PID)) {
                                $info = $this->member_model->member_basic_info(null, $value->PID)->row();
                                if ($info) {
                                    $member_id_disp = $info->member_id;
                                    $full_name = trim($info->firstname . ' ' . $info->middlename . ' ' . $info->lastname);
                                }
                            }
                            $interval = $this->setting_model->intervalinfo(isset($value->interval) ? $value->interval : 1)->row();
                            $interval_desc = $interval ? $interval->description : '';
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
                                <td><?php echo htmlspecialchars(!empty($value->product_name) ? $value->product_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($app_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->basic_amount, 2); ?></td>
                                <td style="text-align:center;"><?php echo htmlspecialchars((int) $value->number_istallment . ($interval_desc !== '' ? ' ' . $interval_desc : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->installment_amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->total_interest_amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->total_loan, 2); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?php echo site_url(current_lang() . '/loan/loan_repayment_entry/' . encode_id($value->LID)); ?>" class="btn btn-primary btn-xs" title="<?php echo htmlspecialchars(lang('loan_repay_btn'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa fa-plus"></i> <?php echo lang('loan_repay_btn'); ?>
                                        </a>
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

<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script>
(function() {
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

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

    function initMemberSuggest() {
        var input = document.getElementById('loan_search_key');
        var box = document.getElementById('loan-suggest-box');
        var form = input ? input.form : null;
        if (!input || !box || !form) {
            return;
        }

        var suggestUrl = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';
        var timer = null;
        var xhr = null;
        var items = [];
        var activeIndex = -1;
        var suppressBlur = false;

        function hideBox() {
            box.classList.remove('open');
            box.innerHTML = '';
            items = [];
            activeIndex = -1;
        }

        function setActive(index) {
            var nodes = box.querySelectorAll('.member-suggest-item');
            activeIndex = index;
            for (var i = 0; i < nodes.length; i++) {
                if (i === activeIndex) {
                    nodes[i].classList.add('active');
                    if (nodes[i].scrollIntoView) {
                        nodes[i].scrollIntoView({ block: 'nearest' });
                    }
                } else {
                    nodes[i].classList.remove('active');
                }
            }
        }

        function choose(item) {
            if (!item) {
                return;
            }
            input.value = item.member_id || item.pid || '';
            hideBox();
            form.submit();
        }

        function render(list) {
            items = list || [];
            activeIndex = items.length ? 0 : -1;
            if (!items.length) {
                box.innerHTML = '<div class="member-suggest-empty">No matching members</div>';
                box.classList.add('open');
                return;
            }
            var html = '';
            for (var i = 0; i < items.length; i++) {
                var row = items[i];
                html += '<button type="button" class="member-suggest-item' + (i === 0 ? ' active' : '') + '" data-index="' + i + '" role="option">' +
                    '<span class="suggest-id">' + escapeHtml(row.member_id) + '</span>' +
                    '<span class="suggest-name">' + escapeHtml(row.name) + '</span>' +
                    '<span class="suggest-status">' + escapeHtml(row.pid) + '</span>' +
                    '</button>';
            }
            box.innerHTML = html;
            box.classList.add('open');
        }

        function fetchSuggestions() {
            var q = input.value.replace(/^\s+|\s+$/g, '');
            if (q.length < 2) {
                hideBox();
                return;
            }
            if (xhr && xhr.abort) {
                xhr.abort();
            }
            xhr = new XMLHttpRequest();
            xhr.open('GET', suggestUrl + '?q=' + encodeURIComponent(q), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) {
                    return;
                }
                if (xhr.status < 200 || xhr.status >= 300) {
                    hideBox();
                    return;
                }
                try {
                    var json = JSON.parse(xhr.responseText || '{}');
                    render(json.items || []);
                } catch (e) {
                    hideBox();
                }
            };
            xhr.send();
        }

        input.addEventListener('input', function() {
            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(fetchSuggestions, 250);
        });

        input.addEventListener('keydown', function(e) {
            if (!box.classList.contains('open')) {
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                choose(items[activeIndex]);
            } else if (e.key === 'Escape') {
                hideBox();
            }
        });

        box.addEventListener('mousedown', function() {
            suppressBlur = true;
        });
        box.addEventListener('click', function(e) {
            var btn = e.target.closest ? e.target.closest('.member-suggest-item') : null;
            if (!btn) {
                return;
            }
            var idx = parseInt(btn.getAttribute('data-index'), 10);
            choose(items[idx]);
        });
        input.addEventListener('blur', function() {
            setTimeout(function() {
                if (!suppressBlur) {
                    hideBox();
                }
                suppressBlur = false;
            }, 150);
        });
    }

    function boot() {
        initDatePickers();
        initMemberSuggest();
    }

    if (document.readyState === 'complete') {
        boot();
    } else {
        window.addEventListener('load', boot);
        setTimeout(boot, 300);
    }
})();
</script>
