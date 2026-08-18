<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<?php
$balance = isset($balance) ? $balance : null;
$encoded_id = isset($encoded_id) ? $encoded_id : '';
$fiscal_years = isset($fiscal_years) ? $fiscal_years : array();
$loan_products = isset($loan_products) ? $loan_products : array();
$posted_unlocked = !empty($posted_unlocked);
$is_edit = !empty($balance);
$form_action = current_lang() . '/loan/loan_beginning_balance_create' . ($encoded_id !== '' ? '/' . $encoded_id : '');

$member_id_value = set_value('member_id', $is_edit ? $balance->member_id : '');
$fiscal_year_value = set_value('fiscal_year_id', $is_edit ? $balance->fiscal_year_id : '');
$product_value = set_value('loan_product_id', $is_edit ? $balance->loan_product_id : '');
$loan_id_value = set_value('loan_id', $is_edit ? $balance->loan_id : '');
$principal_value = set_value('principal_balance', $is_edit ? number_format((float) $balance->principal_balance, 2, '.', '') : '0.00');
$interest_value = set_value('interest_balance', $is_edit ? number_format((float) $balance->interest_balance, 2, '.', '') : '0.00');
$penalty_value = set_value('penalty_balance', $is_edit ? number_format((float) $balance->penalty_balance, 2, '.', '') : '0.00');
$total_value = $is_edit
    ? number_format((float) $balance->principal_balance + (float) $balance->interest_balance + (float) $balance->penalty_balance, 2, '.', '')
    : '0.00';
if ($this->input->post('principal_balance') !== FALSE) {
    $total_value = number_format(
        (float) str_replace(',', '', (string) set_value('principal_balance', 0))
        + (float) str_replace(',', '', (string) set_value('interest_balance', 0))
        + (float) str_replace(',', '', (string) set_value('penalty_balance', 0)),
        2,
        '.',
        ''
    );
}
$disbursement_value = set_value('disbursement_date', ($is_edit && $balance->disbursement_date) ? date('d-m-Y', strtotime($balance->disbursement_date)) : '');
$loan_amount_value = set_value('loan_amount', ($is_edit && $balance->loan_amount) ? number_format((float) $balance->loan_amount, 2, '.', '') : '');
$monthly_amort_value = set_value('monthly_amort', ($is_edit && $balance->monthly_amort) ? number_format((float) $balance->monthly_amort, 2, '.', '') : '');
$last_date_paid_value = set_value('last_date_paid', ($is_edit && $balance->last_date_paid) ? date('d-m-Y', strtotime($balance->last_date_paid)) : '');
$term_value = set_value('term', $is_edit ? $balance->term : '');
$description_value = set_value('description', $is_edit ? $balance->description : '');
$list_url = site_url(current_lang() . '/loan/loan_beginning_balance_list' . ($is_edit && !empty($balance->fiscal_year_id) ? '?fiscal_year_id=' . (int) $balance->fiscal_year_id : ''));
?>

<style type="text/css">
.loan-bb-page { margin-top: 4px; }
.loan-bb-page .cbu-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.loan-bb-page .cbu-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.loan-bb-page .cbu-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.loan-bb-page .cbu-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.loan-bb-page .cbu-panel .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.loan-bb-page .cbu-panel .panel-head .head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.loan-bb-page .cbu-panel .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.loan-bb-page .cbu-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.loan-bb-page .cbu-panel .panel-head .btn {
    border-radius: 6px;
    font-weight: 600;
}
.loan-bb-page .cbu-actions .btn {
    margin-right: 8px;
    padding: 9px 20px;
}
.loan-bb-page .cbu-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.loan-bb-page .cbu-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.loan-bb-page .cbu-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.loan-bb-page .cbu-panel .panel-body { padding: 22px 20px 12px; overflow: visible; }
.loan-bb-page .form-horizontal .form-group { margin-bottom: 16px; }
.loan-bb-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.loan-bb-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.loan-bb-page textarea.form-control { height: auto; min-height: 80px; }
.loan-bb-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.loan-bb-page .form-control[readonly] {
    background: #f8fafb;
    font-weight: 700;
    color: #1ab394;
}
.loan-bb-page .help-block {
    font-size: 12px;
    color: #888;
    margin-top: 6px;
    margin-bottom: 0;
}
.loan-bb-page .input-group-addon {
    background: #f8fafb;
    cursor: pointer;
    color: #1ab394;
}
.loan-bb-page .input-group-addon:hover { background: #e8f8f5; }
.loan-bb-page .required { color: #ed5565; }
.loan-bb-page .section-divider {
    margin: 8px 0 18px;
    padding: 10px 0 8px;
    border-bottom: 1px solid #eef1f2;
    color: #1ab394;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .02em;
}
.loan-bb-page .section-divider i { margin-right: 6px; }
.loan-bb-page .cbu-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.loan-bb-page .cbu-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    min-width: 110px;
}
.loan-bb-page .btn-primary {
    background: #1ab394;
    border-color: #1ab394;
}
.loan-bb-page .cbu-lookup { position: relative; }
.loan-bb-page .member-suggest-box {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    z-index: 1050;
    margin-top: 4px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-height: 260px;
    overflow-y: auto;
}
.loan-bb-page .member-suggest-box.open { display: block; }
.loan-bb-page .member-suggest-item {
    display: flex;
    width: 100%;
    border: 0;
    background: #fff;
    text-align: left;
    padding: 10px 12px;
    border-bottom: 1px solid #f0f2f3;
    cursor: pointer;
}
.loan-bb-page .member-suggest-item:hover,
.loan-bb-page .member-suggest-item.active { background: #f7fcfa; }
.loan-bb-page .member-suggest-item .suggest-id {
    flex: 0 0 auto;
    min-width: 90px;
    font-weight: 700;
    color: #1ab394;
    font-size: 12px;
}
.loan-bb-page .member-suggest-item .suggest-name {
    flex: 1 1 auto;
    color: #2f4050;
    font-size: 13px;
    font-weight: 600;
}
.loan-bb-page .member-suggest-item .suggest-status {
    flex: 0 0 auto;
    color: #999;
    font-size: 11px;
    margin-left: 8px;
}
.loan-bb-page .member-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
.loan-bb-page .cbu-preview {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    min-height: 280px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    margin-bottom: 20px;
}
.loan-bb-page .cbu-preview-empty {
    text-align: center;
    color: #999;
    padding: 40px 12px;
}
.loan-bb-page .cbu-preview-empty i {
    font-size: 42px;
    color: #c9ebe3;
    display: block;
    margin-bottom: 12px;
}
.loan-bb-page .cbu-member-card { text-align: center; }
.loan-bb-page .cbu-member-photo {
    width: 120px;
    height: 120px;
    margin: 0 auto 14px;
    border-radius: 50%;
    padding: 4px;
    background: #fff;
    border: 3px solid #1ab394;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    overflow: hidden;
}
.loan-bb-page .cbu-member-photo img {
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    object-fit: cover;
    object-position: center top;
    border-radius: 50%;
    display: block;
}
.loan-bb-page .cbu-member-photo.avatar-fallback img {
    object-fit: contain;
    object-position: center center;
    background: #e8f8f5;
}
.loan-bb-page .cbu-member-photo-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f8f5;
}
.loan-bb-page .cbu-member-photo-empty i {
    font-size: 42px;
    color: #1ab394;
}
.loan-bb-page .cbu-member-name {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #2f4050;
    line-height: 1.3;
}
.loan-bb-page .cbu-member-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
    margin-bottom: 14px;
}
.loan-bb-page .cbu-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #e8f8f5;
    color: #1ab394;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}
.loan-bb-page .cbu-badge.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.loan-bb-page .cbu-member-details {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.loan-bb-page .cbu-member-details li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
}
.loan-bb-page .cbu-member-details li:last-child { border-bottom: 0; }
.loan-bb-page .cbu-member-details .lbl { color: #999; font-weight: 500; }
.loan-bb-page .cbu-member-details .val {
    color: #2f4050;
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}
.loan-bb-page .total-hint {
    margin-top: 4px;
    font-size: 12px;
    color: #888;
}
.bootstrap-datetimepicker-widget {
    z-index: 1060 !important;
}
</style>

<?php echo form_open($form_action, 'class="form-horizontal"'); ?>

<div class="col-lg-12 loan-bb-page">
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
    if (validation_errors()) {
        echo '<div class="cbu-alert danger displaymessage">' . validation_errors() . '</div>';
    }
    ?>

    <div class="row">
        <div class="col-lg-7">
            <div class="cbu-panel">
                <div class="panel-head">
                    <div class="head-left">
                        <i class="fa fa-<?php echo $is_edit ? 'edit' : 'plus'; ?> icon-badge"></i>
                        <h4><?php echo $is_edit ? lang('loan_beginning_balance_edit') : lang('loan_beginning_balance_create'); ?></h4>
                    </div>
                    <a href="<?php echo $list_url; ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> <?php echo lang('button_cancel'); ?>
                    </a>
                </div>
                <div class="panel-body">
                    <?php if ($posted_unlocked) { ?>
                        <div class="cbu-alert" style="background:#fff8e6;color:#8a6d3b;border:1px solid #faebcc;margin-bottom:16px;">
                            <?php echo lang('loan_beginning_balance_edit_posted_hint'); ?>
                        </div>
                    <?php } ?>

                    <div class="section-divider">
                        <i class="fa fa-user"></i>Member &amp; Product
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('fiscal_year'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select class="form-control" name="fiscal_year_id" id="fiscal_year_id" required>
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($fiscal_years as $fy) { ?>
                                    <option value="<?php echo (int) $fy->id; ?>"<?php echo ((string) $fiscal_year_value === (string) $fy->id) ? ' selected="selected"' : ''; ?>>
                                        <?php echo htmlspecialchars($fy->name . ' (' . date('M d, Y', strtotime($fy->start_date)) . ' - ' . date('M d, Y', strtotime($fy->end_date)) . ')', ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('fiscal_year_id'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_member_id'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <div class="cbu-lookup">
                                <div class="input-group">
                                    <input type="text" name="member_id" id="member_id" value="<?php echo htmlspecialchars((string) $member_id_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" autocomplete="off" required />
                                    <span class="input-group-addon" id="search_mid" title="Search member">
                                        <span class="fa fa-search"></span>
                                    </span>
                                </div>
                                <div id="mid-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                            </div>
                            <?php echo form_error('member_id'); ?>
                            <span class="help-block"><?php echo lang('member_id'); ?> — type to search, then press search or Enter</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_loan_product'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7">
                            <select class="form-control" name="loan_product_id" id="loan_product_id" required>
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php foreach ($loan_products as $product) { ?>
                                    <option value="<?php echo (int) $product->id; ?>"<?php echo ((string) $product_value === (string) $product->id) ? ' selected="selected"' : ''; ?>>
                                        <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('loan_product_id'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_loan_id'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="loan_id" id="loan_id" value="<?php echo htmlspecialchars((string) $loan_id_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
                            <?php echo form_error('loan_id'); ?>
                            <span class="help-block">Optional — reference to existing loan ID</span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-balance-scale"></i>Outstanding Balances
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_principal'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="principal_balance" id="principal_balance" value="<?php echo htmlspecialchars((string) $principal_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
                            <?php echo form_error('principal_balance'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_interest'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="interest_balance" id="interest_balance" value="<?php echo htmlspecialchars((string) $interest_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
                            <?php echo form_error('interest_balance'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_penalty'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="penalty_balance" id="penalty_balance" value="<?php echo htmlspecialchars((string) $penalty_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this); calculateTotal();" />
                            <?php echo form_error('penalty_balance'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_total'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" id="total_balance" value="<?php echo htmlspecialchars((string) $total_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly />
                            <div class="total-hint">At least one of principal, interest, or penalty must be greater than zero.</div>
                        </div>
                    </div>

                    <div class="section-divider">
                        <i class="fa fa-calendar"></i>Loan Terms <span style="color:#999;font-weight:500;">(optional)</span>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_disbursement_date'); ?> :</label>
                        <div class="col-lg-7">
                            <div class="input-group date" id="datetimepicker_disbursement">
                                <input type="text" name="disbursement_date" id="disbursement_date" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars((string) $disbursement_value, ENT_QUOTES, 'UTF-8'); ?>" data-date-format="DD-MM-YYYY" class="form-control" autocomplete="off"/>
                                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                            </div>
                            <?php echo form_error('disbursement_date'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_loan_amount'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="loan_amount" id="loan_amount" value="<?php echo htmlspecialchars((string) $loan_amount_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this);" />
                            <?php echo form_error('loan_amount'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_monthly_amort'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="text" name="monthly_amort" id="monthly_amort" value="<?php echo htmlspecialchars((string) $monthly_amort_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" onkeyup="formatNumber(this);" />
                            <?php echo form_error('monthly_amort'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_last_date_paid'); ?> :</label>
                        <div class="col-lg-7">
                            <div class="input-group date" id="datetimepicker_last_date_paid">
                                <input type="text" name="last_date_paid" id="last_date_paid" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo htmlspecialchars((string) $last_date_paid_value, ENT_QUOTES, 'UTF-8'); ?>" data-date-format="DD-MM-YYYY" class="form-control" autocomplete="off"/>
                                <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                            </div>
                            <?php echo form_error('last_date_paid'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('loan_beginning_balance_term'); ?> :</label>
                        <div class="col-lg-7">
                            <input type="number" name="term" id="term" value="<?php echo htmlspecialchars((string) $term_value, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" step="1" />
                            <?php echo form_error('term'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 control-label"><?php echo lang('description'); ?> :</label>
                        <div class="col-lg-7">
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars((string) $description_value, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <?php echo form_error('description'); ?>
                        </div>
                    </div>

                    <div class="form-group cbu-actions">
                        <label class="col-lg-4 control-label">&nbsp;</label>
                        <div class="col-lg-7">
                            <a href="<?php echo $list_url; ?>" class="btn btn-default">
                                <i class="fa fa-undo"></i> <?php echo lang('button_cancel'); ?>
                            </a>
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-save"></i>
                                <?php echo $is_edit ? lang('button_update') : lang('loan_beginning_balance_btncreate'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="cbu-preview" id="member_info">
                <div class="cbu-preview-empty">
                    <i class="fa fa-user"></i>
                    Search a member by Member ID to view details.
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script src="<?php echo base_url(); ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url(); ?>media/js/member_avatar.js"></script>
<script>
function formatNumber(input) {
    var value = input.value.replace(/[^\d.]/g, '');
    var parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    input.value = value;
}

function calculateTotal() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    var principal = parseFloat(jQuery('#principal_balance').val().replace(/,/g, '')) || 0;
    var interest = parseFloat(jQuery('#interest_balance').val().replace(/,/g, '')) || 0;
    var penalty = parseFloat(jQuery('#penalty_balance').val().replace(/,/g, '')) || 0;
    jQuery('#total_balance').val((principal + interest + penalty).toFixed(2));
}

(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        jQuery(document).ready(function($) {
            var SEARCH_URL = '<?php echo site_url(current_lang() . '/loan/search_member/'); ?>';
            var SUGGEST_URL = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';
            var PHOTO_BASE = '<?php echo base_url(); ?>uploads/memberphoto/';
            var initialMemberId = <?php echo json_encode((string) $member_id_value); ?>;

            function escapeHtml(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function displayValue(val) {
                if (val === null || val === undefined || String(val).replace(/^\s+|\s+$/g, '') === '') {
                    return '&mdash;';
                }
                return escapeHtml(val);
            }

            function genderLabel(code) {
                if (typeof memberGenderLabel === 'function') {
                    return memberGenderLabel(code);
                }
                return code || '';
            }

            function initDatePickers() {
                if (typeof moment === 'undefined') {
                    setTimeout(initDatePickers, 50);
                    return;
                }
                function bindPickers() {
                    if (typeof $.fn.datetimepicker === 'undefined') {
                        return false;
                    }
                    var opts = { pickTime: false, format: 'DD-MM-YYYY' };
                    var $from = $('#datetimepicker_disbursement');
                    var $to = $('#datetimepicker_last_date_paid');
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
            initDatePickers();

            $('#principal_balance, #interest_balance, #penalty_balance').on('blur', function() {
                var value = parseFloat($(this).val().replace(/,/g, ''));
                if (!isNaN(value)) {
                    $(this).val(value.toFixed(2));
                }
                calculateTotal();
            });
            calculateTotal();

            function renderMemberCard(userdata, contact) {
                contact = contact || {};
                var photo = userdata['photo'] ? userdata['photo'].toString() : '';
                var name = $.trim((userdata['firstname'] || '') + ' ' + (userdata['middlename'] || '') + ' ' + (userdata['lastname'] || ''));
                var gender = genderLabel(userdata['gender']);
                var accountStatus = (typeof memberAccountStatus === 'function') ? memberAccountStatus(userdata) : { label: '', active: true };
                var statusLabel = (window.TAPSTEMCO_MEMBER_STATUS && TAPSTEMCO_MEMBER_STATUS.label) ? TAPSTEMCO_MEMBER_STATUS.label : '<?php echo lang('member_status'); ?>';
                var output = '<div class="cbu-member-card">';
                if (typeof memberAvatarHtml === 'function') {
                    output += memberAvatarHtml(photo, userdata['gender']);
                } else if (photo) {
                    output += '<div class="cbu-member-photo"><img src="' + escapeHtml(PHOTO_BASE + photo) + '" alt=""/></div>';
                } else {
                    output += '<div class="cbu-member-photo cbu-member-photo-empty"><i class="fa fa-user"></i></div>';
                }
                output += '<h3 class="cbu-member-name">' + escapeHtml(name) + '</h3>';
                output += '<div class="cbu-member-badges">';
                if (userdata['member_id']) {
                    output += '<span class="cbu-badge">' + escapeHtml(userdata['member_id']) + '</span>';
                }
                if (userdata['PID'] !== undefined && userdata['PID'] !== null && String(userdata['PID']) !== '') {
                    output += '<span class="cbu-badge"><?php echo lang('member_pid'); ?> ' + escapeHtml(userdata['PID']) + '</span>';
                }
                if (gender) {
                    output += '<span class="cbu-badge">' + escapeHtml(gender) + '</span>';
                }
                if (accountStatus.label) {
                    output += '<span class="cbu-badge' + (accountStatus.active ? '' : ' inactive') + '">' + escapeHtml(statusLabel) + ': ' + escapeHtml(accountStatus.label) + '</span>';
                }
                output += '</div>';
                output += '<ul class="cbu-member-details">';
                output += '<li><span class="lbl"><?php echo lang('member_contact_address'); ?></span><span class="val">' + displayValue(contact['physicaladdress'] || contact['postaladdress'] || contact['officeaddress'] || '') + '</span></li>';
                output += '<li><span class="lbl"><?php echo lang('member_dob'); ?></span><span class="val">' + displayValue(userdata['dob']) + '</span></li>';
                output += '<li><span class="lbl"><?php echo lang('member_join_date'); ?></span><span class="val">' + displayValue(userdata['joiningdate']) + '</span></li>';
                output += '<li><span class="lbl"><?php echo lang('member_contact_phone1'); ?></span><span class="val">' + displayValue(contact['phone1']) + '</span></li>';
                output += '<li><span class="lbl"><?php echo lang('member_contact_position'); ?></span><span class="val">' + displayValue(contact['position'] || contact['occupation'] || '') + '</span></li>';
                output += '<li><span class="lbl"><?php echo lang('member_contact_email'); ?></span><span class="val">' + displayValue(contact['email']) + '</span></li>';
                output += '<li><span class="lbl"><?php echo lang('member_contact_salary_grade'); ?></span><span class="val">' + displayValue(contact['salary_grade'] || contact['salarygrade'] || userdata['salary_grade'] || '') + '</span></li>';
                output += '</ul></div>';
                $('#member_info').html(output);
            }

            function loadMember(value) {
                value = $.trim(String(value == null ? '' : value));
                if (value === '') {
                    return;
                }
                $('#member_info').html('<div class="cbu-preview-empty"><i class="fa fa-spinner fa-spin"></i><?php echo lang('please_wait'); ?></div>');
                $.ajax({
                    url: SEARCH_URL,
                    type: 'POST',
                    dataType: 'text',
                    data: { value: value, column: 'MID' },
                    success: function(data) {
                        try {
                            var json = JSON.parse(data);
                            if (!json['success'] || json['success'].toString() == 'N') {
                                $('#member_info').html('<div class="cbu-alert danger">' + escapeHtml(json['error'] || 'Member not found') + '</div>');
                                return;
                            }
                            var userdata = json['data'] || {};
                            var contact = json['contact'] || {};
                            if (userdata['member_id']) {
                                $('#member_id').val(userdata['member_id']);
                            }
                            renderMemberCard(userdata, contact);
                        } catch (e) {
                            $('#member_info').html('<div class="cbu-alert danger">Error parsing response</div>');
                        }
                    },
                    error: function() {
                        $('#member_info').html('<div class="cbu-alert danger">Error loading member information</div>');
                    }
                });
            }

            function initMemberSuggest() {
                var input = document.getElementById('member_id');
                var box = document.getElementById('mid-suggest-box');
                if (!input || !box) {
                    return;
                }
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
                        } else {
                            nodes[i].classList.remove('active');
                        }
                    }
                }

                function choose(item) {
                    if (!item) {
                        return;
                    }
                    input.value = item.member_id || '';
                    hideBox();
                    loadMember(input.value);
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
                    xhr.open('GET', SUGGEST_URL + '?q=' + encodeURIComponent(q), true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState !== 4) {
                            return;
                        }
                        if (xhr.status < 200 || xhr.status >= 300) {
                            hideBox();
                            return;
                        }
                        try {
                            var json = JSON.parse(xhr.responseText || '[]');
                            var list = Array.isArray(json) ? json : (json.items || []);
                            render(list);
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
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            loadMember(input.value);
                        }
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
                    choose(items[parseInt(btn.getAttribute('data-index'), 10)]);
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

            initMemberSuggest();

            $('#search_mid').on('click', function() {
                loadMember($('#member_id').val());
            });

            if (initialMemberId) {
                loadMember(initialMemberId);
            }
        });
    }
    initScripts();
})();
</script>
