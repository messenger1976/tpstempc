<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(lang('loan_repayment')); ?> - <?php echo htmlspecialchars(isset($loan_LID) ? $loan_LID : ''); ?></title>
    <link href="<?php echo base_url(); ?>media/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
        body { padding: 20px; margin: 0; max-width: 480px; }
        .form-actions { margin-top: 20px; }
    </style>
</head>
<body>
    <h4 style="margin-top:0;"><?php echo htmlspecialchars(lang('loan_repayment') . ' - ' . lang('loan_repay_btn')); ?></h4>
    <p class="text-muted"><?php echo lang('loan_LID'); ?>: <strong><?php echo htmlspecialchars(isset($loan_LID) ? $loan_LID : ''); ?></strong></p>
    <div id="formMessage" class="alert" style="display:none;"></div>
    <form id="repayForm" method="post" action="">
        <input type="hidden" name="loanid" value="<?php echo htmlspecialchars(isset($loan_LID) ? $loan_LID : ''); ?>"/>
        <div class="form-group">
            <label><?php echo lang('cash_receipt_no'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="receipt_no" class="form-control" value="<?php echo htmlspecialchars(isset($next_receipt_no) ? $next_receipt_no : 'CR-00001'); ?>" required/>
        </div>
        <div class="form-group">
            <label><?php echo lang('loan_repay_date'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="repaydate" class="form-control" placeholder="DD-MM-YYYY" value="<?php echo date('d-m-Y'); ?>" required/>
        </div>
        <div class="form-group">
            <label><?php echo lang('loan_repay_amount'); ?> <span class="text-danger">*</span></label>
            <input type="text" name="amount" id="amountInput" class="form-control" required/>
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
