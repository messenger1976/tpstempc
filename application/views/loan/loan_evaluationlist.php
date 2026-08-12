<?php $this->load->view('loan/list_page_styles'); ?>

<?php
$search_key = isset($search_key) ? $search_key : (isset($_GET['key']) ? $_GET['key'] : (isset($_POST['key']) ? $_POST['key'] : ''));
?>

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
                <h4><?php echo lang('loan_evaluation_list'); ?></h4>
            </div>
        </div>
        <div class="panel-body">
            <form action="<?php echo site_url(current_lang() . "/loan/loan_evaluation"); ?>" method="get" class="form-horizontal">
                <div class="filter-row">
                    <div class="filter-field search-field">
                        <label><?php echo lang('loan_LID'); ?> / <?php echo lang('member_name'); ?></label>
                        <input type="text" class="form-control" name="key" id="loan_search_key" placeholder="Loan No, Member ID or Name..." value="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"/>
                        <div id="loan-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                    </div>
                    <div class="filter-actions">
                        <a href="<?php echo site_url(current_lang() . '/loan/loan_evaluation'); ?>" class="btn btn-default btn-clear-filter">
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
                <i class="fa fa-check-square-o icon-badge"></i>
                <h4><?php echo lang('loan_evaluation_list'); ?></h4>
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
                            $interval = $this->setting_model->intervalinfo($value->interval)->row();
                            $interval_desc = $interval ? $interval->description : '';
                            $status_code = isset($value->status) ? (string) $value->status : '';
                            $status_name = !empty($value->status_name) ? $value->status_name : '';
                            if ($status_name === '' && $status_code === '0') {
                                $status_name = 'New Loan';
                            } else if ($status_name === '' && $status_code === '3') {
                                $status_name = 'Need Information';
                            }
                            $pill = ($status_code === '3') ? 'mixed' : 'new';
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
                                <td style="text-align:center;"><?php echo htmlspecialchars($value->number_istallment . ($interval_desc !== '' ? ' ' . $interval_desc : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->installment_amount, 2); ?></td>
                                <td class="amount-cell"><?php echo number_format($value->total_interest_amount, 2); ?></td>
                                <td><span class="status-pill <?php echo $pill; ?>"><?php echo htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <?php echo anchor(current_lang() . "/loan/loan_evaluation_action/" . encode_id($value->LID), ' <i class="fa fa-file"></i> ' . lang('loan_evaluation_link'), 'class="btn btn-primary btn-xs"'); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa fa-check-square-o"></i>
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
            if (q.length < 1) {
                hideBox();
                return;
            }
            if (xhr && typeof xhr.abort === 'function') {
                xhr.abort();
            }
            if (window.jQuery) {
                xhr = jQuery.getJSON(suggestUrl + '?q=' + encodeURIComponent(q))
                    .done(function(data) { render(data || []); })
                    .fail(function(jqXHR, textStatus) {
                        if (textStatus !== 'abort') hideBox();
                    });
            }
        }

        input.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(fetchSuggestions, 220);
        });
        input.addEventListener('keydown', function(e) {
            if (!box.classList.contains('open') || !items.length) return;
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
        input.addEventListener('blur', function() {
            setTimeout(function() {
                if (!suppressBlur) hideBox();
                suppressBlur = false;
            }, 150);
        });
        box.addEventListener('mousedown', function() { suppressBlur = true; });
        box.addEventListener('click', function(e) {
            var btn = e.target;
            while (btn && btn !== box && !btn.classList.contains('member-suggest-item')) {
                btn = btn.parentNode;
            }
            if (!btn || !btn.classList.contains('member-suggest-item')) return;
            choose(items[parseInt(btn.getAttribute('data-index'), 10)]);
        });
    }

    if (document.readyState === 'complete') {
        initMemberSuggest();
    } else {
        window.addEventListener('load', initMemberSuggest);
        setTimeout(initMemberSuggest, 300);
    }
})();
</script>
