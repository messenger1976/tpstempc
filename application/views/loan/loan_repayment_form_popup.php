<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(lang('loan_repayment')); ?> - <?php echo htmlspecialchars(isset($loan_LID) ? $loan_LID : ''); ?></title>
    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body { padding: 20px; margin: 0; max-width: 520px; }
        .form-actions { margin-top: 20px; }
        #repaymentDuePanel { margin: 12px 0 16px; }
        #repaymentDuePanel table { font-size: 12px; }
    </style>
</head>
<body>
    <?php
    $due = isset($repayment_due) ? $repayment_due : null;
    $suggested = $due && isset($due->suggested_amount) ? (float) $due->suggested_amount : 0;
    ?>
    <h4 style="margin-top:0;"><?php echo htmlspecialchars(lang('loan_repayment') . ' - ' . lang('loan_repay_btn')); ?></h4>
    <p class="text-muted"><?php echo lang('loan_LID'); ?>: <strong><?php echo htmlspecialchars(isset($loan_LID) ? $loan_LID : ''); ?></strong></p>
    <div id="formMessage" class="alert" style="display:none;"></div>

    <div id="repaymentDuePanel" class="panel panel-info">
        <div class="panel-heading"><strong><?php echo lang('loan_repay_due_title'); ?></strong></div>
        <div class="panel-body" style="padding:10px;">
            <p class="text-muted small" id="repaymentDueExplanation" style="margin-bottom:8px;">
                <?php
                echo sprintf(
                    lang('loan_repay_due_explanation'),
                    ($due && isset($due->grace_days)) ? (int) $due->grace_days : (defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0),
                    ($due && isset($due->penalt_percentage)) ? rtrim(rtrim(number_format((float) $due->penalt_percentage, 2), '0'), '.') : '0'
                );
                ?>
            </p>
            <div class="table-responsive">
                <table class="table table-condensed table-bordered" style="margin-bottom:8px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo lang('due_date'); ?></th>
                            <th><?php echo lang('index_status_th'); ?></th>
                            <th class="text-right"><?php echo lang('loan_installment_amount'); ?></th>
                            <th class="text-right"><?php echo lang('loan_ledger_penalty'); ?></th>
                            <th class="text-right"><?php echo lang('total'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="repaymentDueBody">
                        <?php if ($due && !empty($due->items)): ?>
                            <?php foreach ($due->items as $item): ?>
                                <tr class="<?php echo $item->status === 'overdue' ? 'warning' : ''; ?>">
                                    <td><?php echo (int) $item->installment; ?></td>
                                    <td><?php echo htmlspecialchars(format_date($item->due_date, FALSE)); ?></td>
                                    <td><?php echo $item->status === 'overdue' ? lang('loan_repay_status_overdue') : lang('loan_repay_status_due'); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $item->installment_amount, 2); ?></td>
                                    <td class="text-right"><?php echo number_format((float) $item->penalty, 2); ?></td>
                                    <td class="text-right"><strong><?php echo number_format((float) $item->total, 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-muted"><?php echo lang('loan_repay_nothing_due'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right"><?php echo lang('loan_repay_total_due'); ?></th>
                            <th class="text-right" id="dueTotalInstallments"><?php echo number_format($due ? (float) $due->total_installments : 0, 2); ?></th>
                            <th class="text-right" id="dueTotalPenalty"><?php echo number_format($due ? (float) $due->total_penalty : 0, 2); ?></th>
                            <th class="text-right" id="dueTotalDue"><?php echo number_format($due ? (float) $due->total_due : 0, 2); ?></th>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><?php echo lang('loan_repay_carry_balance'); ?></td>
                            <td class="text-right" id="dueCarry"><?php echo number_format($due ? (float) $due->carry_balance : 0, 2); ?></td>
                        </tr>
                        <tr class="info">
                            <th colspan="5" class="text-right"><?php echo lang('loan_repay_net_due'); ?></th>
                            <th class="text-right" id="dueNetDue"><?php echo number_format($suggested, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-default btn-sm" id="btnUseSuggestedAmount"><?php echo lang('loan_repay_use_suggested'); ?></button>
        </div>
    </div>

    <form id="repayForm" method="post" action="">
        <input type="hidden" name="loanid" value="<?php echo htmlspecialchars(isset($loan_LID) ? $loan_LID : ''); ?>"/>
        <div class="form-group">
            <label><?php echo lang('cash_receipt_no'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="receipt_no" class="form-control" value="<?php echo htmlspecialchars(isset($next_receipt_no) ? $next_receipt_no : 'CR-00001'); ?>" required/>
        </div>
        <div class="form-group">
            <label><?php echo lang('loan_repay_date'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="repaydate" id="repaydateInput" class="form-control" placeholder="DD-MM-YYYY" value="<?php echo date('d-m-Y'); ?>" required/>
        </div>
        <div class="form-group">
            <label><?php echo lang('loan_repay_amount'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="amount" id="amountInput" class="form-control" value="<?php echo $suggested > 0 ? number_format($suggested, 2, '.', '') : ''; ?>" required/>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-default" onclick="window.close();"><?php echo lang('button_cancel'); ?></button>
            <button type="submit" class="btn btn-primary" id="btnSave"><i class="fa fa-save"></i> Save</button>
        </div>
    </form>
    <script>
    (function() {
        var form = document.getElementById('repayForm');
        var msgEl = document.getElementById('formMessage');
        var saveUrl = '<?php echo site_url(current_lang() . '/loan/loan_repayment_save'); ?>';
        var dueUrl = <?php echo json_encode(isset($repayment_due_url) ? $repayment_due_url : ''); ?>;
        var suggested = <?php echo json_encode(round($suggested, 2)); ?>;
        var labels = {
            status_due: <?php echo json_encode(lang('loan_repay_status_due')); ?>,
            status_overdue: <?php echo json_encode(lang('loan_repay_status_overdue')); ?>,
            nothing_due: <?php echo json_encode(lang('loan_repay_nothing_due')); ?>,
            explanation: <?php echo json_encode(lang('loan_repay_due_explanation')); ?>
        };

        function formatMoney(n) {
            var x = parseFloat(n); if (isNaN(x)) x = 0;
            return x.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
        function formatDateDisplay(ymd) {
            if (!ymd || ymd.indexOf('-') < 0) return ymd || '';
            var p = ymd.split('-');
            return p.length === 3 ? (p[2] + '-' + p[1] + '-' + p[0]) : ymd;
        }
        function renderDue(due) {
            if (!due) return;
            suggested = parseFloat(due.suggested_amount) || 0;
            var expl = labels.explanation || '';
            document.getElementById('repaymentDueExplanation').textContent =
                expl.replace('%s', due.grace_days || 0).replace('%s', due.penalt_percentage || 0);
            var body = document.getElementById('repaymentDueBody');
            body.innerHTML = '';
            if (!due.items || !due.items.length) {
                body.innerHTML = '<tr><td colspan="6" class="text-muted">' + labels.nothing_due + '</td></tr>';
            } else {
                due.items.forEach(function(item) {
                    var status = item.status === 'overdue' ? labels.status_overdue : labels.status_due;
                    var tr = document.createElement('tr');
                    if (item.status === 'overdue') tr.className = 'warning';
                    tr.innerHTML =
                        '<td>' + item.installment + '</td>' +
                        '<td>' + formatDateDisplay(item.due_date) + '</td>' +
                        '<td>' + status + '</td>' +
                        '<td class="text-right">' + formatMoney(item.installment_amount) + '</td>' +
                        '<td class="text-right">' + formatMoney(item.penalty) + '</td>' +
                        '<td class="text-right"><strong>' + formatMoney(item.total) + '</strong></td>';
                    body.appendChild(tr);
                });
            }
            document.getElementById('dueTotalInstallments').textContent = formatMoney(due.total_installments);
            document.getElementById('dueTotalPenalty').textContent = formatMoney(due.total_penalty);
            document.getElementById('dueTotalDue').textContent = formatMoney(due.total_due);
            document.getElementById('dueCarry').textContent = formatMoney(due.carry_balance);
            document.getElementById('dueNetDue').textContent = formatMoney(due.suggested_amount);
        }
        function refreshDue(autofill) {
            if (!dueUrl) return;
            var repaydate = document.getElementById('repaydateInput').value;
            fetch(dueUrl + (dueUrl.indexOf('?') >= 0 ? '&' : '?') + 'repaydate=' + encodeURIComponent(repaydate))
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res && res.success && res.due) {
                        renderDue(res.due);
                        if (autofill) {
                            document.getElementById('amountInput').value = (parseFloat(res.due.suggested_amount) || 0).toFixed(2);
                        }
                    }
                })
                .catch(function() {});
        }

        document.getElementById('btnUseSuggestedAmount').addEventListener('click', function() {
            document.getElementById('amountInput').value = (suggested || 0).toFixed(2);
        });
        document.getElementById('repaydateInput').addEventListener('change', function() { refreshDue(true); });
        document.getElementById('repaydateInput').addEventListener('blur', function() { refreshDue(true); });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var amt = document.getElementById('amountInput').value;
            if (amt) amt = amt.replace(/,/g, '');
            var fd = new FormData(form);
            fd.set('amount', amt || fd.get('amount'));
            var btn = document.getElementById('btnSave');
            if (btn) { btn.disabled = true; btn.innerHTML = 'Saving...'; }
            fetch(saveUrl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res && res.success && res.redirect) {
                        if (window.opener) window.opener.location.href = res.redirect;
                        window.location.href = res.redirect;
                    } else {
                        if (msgEl) {
                            msgEl.className = 'alert alert-danger';
                            msgEl.textContent = (res && res.warning) ? res.warning : 'Error';
                            msgEl.style.display = 'block';
                        }
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save'; }
                    }
                })
                .catch(function() {
                    if (msgEl) {
                        msgEl.className = 'alert alert-danger';
                        msgEl.textContent = 'Request failed.';
                        msgEl.style.display = 'block';
                    }
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save'; }
                });
        });
    })();
    </script>
</body>
</html>
