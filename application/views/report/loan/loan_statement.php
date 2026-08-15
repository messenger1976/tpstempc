<link href="<?php echo base_url(); ?>assets/css/plugins/select2/select2.min.css" rel="stylesheet">
<?php $this->load->view('loan/list_page_styles'); ?>

<?php
$loan_id = isset($loan_id) ? $loan_id : '';
$loan_list = isset($loan_list) ? $loan_list : array();
?>

<style type="text/css">
.select2-container{width:100%!important;}
.ls-page { margin-top: 0; }
.ls-page .cbu-alert {
    display: block;
    margin: 0 0 14px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.ls-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.ls-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}

.ls-page .mp-finder {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px 14px;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.ls-page .mp-finder .finder-label {
    flex: 0 0 auto;
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #676a6c;
    white-space: nowrap;
}
.ls-page .mp-finder .finder-label .required { color: #ed5565; }
.ls-page .mp-finder .finder-select {
    flex: 1 1 220px;
    min-width: 180px;
    max-width: 560px;
}
.ls-page .mp-finder .btn-load {
    flex: 0 0 auto;
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-weight: 600;
    background: #1ab394;
    border-color: #1ab394;
    white-space: nowrap;
}
.ls-page .mp-finder .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.ls-page .mp-finder .select2-container .select2-selection--single {
    height: 36px;
    border-radius: 6px;
    border-color: #e5e6e7;
}
.ls-page .mp-finder .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
    padding-left: 12px;
    color: #2f4050;
}
.ls-page .mp-finder .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}
.ls-page .mp-finder .select2-container--default.select2-container--focus .select2-selection--single,
.ls-page .mp-finder .select2-container--default.select2-container--open .select2-selection--single {
    border-color: #1ab394;
}
.ls-page .mp-finder .finder-error {
    flex: 1 1 100%;
    margin: 0;
    color: #ed5565;
    font-size: 12px;
}

.ls-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.ls-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.ls-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ls-page .cbu-panel .panel-head i.icon-badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}
.ls-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #2f4050;
}
.ls-page .cbu-panel .panel-body { padding: 16px; }
.ls-page .cbu-panel .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.ls-page .cbu-panel .panel-head .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}

.ls-page .hint-empty {
    text-align: center;
    padding: 48px 20px;
    color: #888;
    background: #fff;
    border: 1px dashed #dfe4e8;
    border-radius: 10px;
}
.ls-page .hint-empty i {
    display: block;
    margin-bottom: 12px;
    opacity: 0.35;
}
.ls-page .hint-empty p {
    margin: 0;
    font-size: 14px;
}

#ibox-main > .ibox-content { padding-top: 14px; }
</style>

<div class="col-lg-12 ls-page member-list-page">
    <?php
    if (isset($message) && !empty($message)) {
        echo '<div class="cbu-alert success displaymessage">' . $message . '</div>';
    } else if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if (isset($warning) && !empty($warning)) {
        echo '<div class="cbu-alert danger displaymessage">' . $warning . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <?php echo form_open_multipart(current_lang() . '/report_loan/loan_statement', 'class="mp-finder-form"'); ?>
        <div class="mp-finder">
            <label class="finder-label" for="loan_id">
                <?php echo lang('loan_id'); ?> <span class="required">*</span>
            </label>
            <div class="finder-select">
                <select name="loan_id" class="form-control" id="loan_id">
                    <option value=""><?php echo lang('select_default_text'); ?></option>
                    <?php foreach ($loan_list as $value) {
                        $label = $value->LID . ' - ' . $value->name;
                        ?>
                        <option value="<?php echo htmlspecialchars($value->LID, ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo ((string) $loan_id === (string) $value->LID) ? 'selected="selected"' : ''; ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="Load" value="Load" class="btn btn-primary btn-load">
                <i class="fa fa-search"></i> Load
            </button>
            <?php
            $loan_err = form_error('loan_id');
            if ($loan_err) {
                echo '<div class="finder-error">' . $loan_err . '</div>';
            }
            ?>
        </div>
    <?php echo form_close(); ?>

    <?php if ($loan_id != '') {
        $this->data['loanid'] = encode_id($loan_id);
        $LID = $loan_id;
        $this->data['trans'] = $this->report_model->loan_statement($LID);
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->load->view('report/loan/loan_statement_content', $this->data);
    } else { ?>
        <div class="hint-empty">
            <i class="fa fa-file-text-o fa-3x"></i>
            <p>Select a loan and click Load to view the statement.</p>
        </div>
    <?php } ?>
</div>

<script src="<?php echo base_url(); ?>assets/js/plugins/select2/select2.full.min.js"></script>
<script>
(function(){
    function boot(){
        if (!window.jQuery || !jQuery.fn.select2) {
            setTimeout(boot, 50);
            return;
        }
        var $ = window.jQuery;
        if ($('#loan_id').length) {
            $('#loan_id').select2({
                width: '100%',
                placeholder: <?php echo json_encode(lang('select_default_text')); ?>,
                allowClear: true
            });
        }
    }
    boot();
})();
</script>
