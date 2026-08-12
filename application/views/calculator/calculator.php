<style>
.calc-page { margin-top: 4px; }
.calc-page .calc-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.calc-page .calc-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.calc-page .calc-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.calc-page .calc-side-card {
    background: linear-gradient(165deg, #f7fcfa 0%, #ffffff 48%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 22px 18px 20px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.calc-page .calc-icon-wrap {
    width: 110px;
    height: 110px;
    margin: 0 auto 16px;
    border-radius: 50%;
    border: 3px solid #1ab394;
    background: #fff;
    box-shadow: 0 4px 14px rgba(26,179,148,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
}
.calc-page .calc-icon-wrap i {
    font-size: 42px;
    color: #1ab394;
}
.calc-page .calc-side-card h3 {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 700;
    color: #2f4050;
}
.calc-page .calc-side-card p {
    margin: 0 0 16px;
    color: #888;
    font-size: 12px;
    line-height: 1.5;
}
.calc-page .calc-tips {
    text-align: left;
    margin: 0;
    padding: 0;
    list-style: none;
    border-top: 1px dashed #e7eaec;
}
.calc-page .calc-tips li {
    display: flex;
    gap: 10px;
    padding: 11px 2px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
    color: #676a6c;
}
.calc-page .calc-tips li:last-child { border-bottom: 0; }
.calc-page .calc-tips i {
    color: #1ab394;
    width: 16px;
    margin-top: 2px;
}
.calc-page .calc-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.calc-page .calc-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.calc-page .calc-panel .panel-head i {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.calc-page .calc-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.calc-page .calc-panel .panel-body { padding: 22px 20px 12px; }
.calc-page .form-horizontal .form-group { margin-bottom: 16px; }
.calc-page .form-horizontal .control-label {
    color: #676a6c;
    font-weight: 600;
    padding-top: 9px;
}
.calc-page .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.calc-page .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.calc-page .required { color: #ed5565; }
.calc-page .calc-actions {
    margin-top: 8px;
    margin-bottom: 8px;
    padding-top: 12px;
    border-top: 1px solid #f0f2f3;
}
.calc-page .calc-actions .btn {
    min-width: 140px;
    padding: 9px 20px;
    border-radius: 6px;
    font-weight: 600;
    margin-right: 8px;
}
.calc-page .calc-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
}
.calc-page .calc-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.calc-page .calc-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.calc-page .result-hero {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}
.calc-page .result-stat {
    background: linear-gradient(165deg, #f7fcfa 0%, #fff 70%);
    border: 1px solid #e7eaec;
    border-radius: 10px;
    padding: 14px 16px;
}
.calc-page .result-stat .lbl {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #888;
    margin-bottom: 6px;
}
.calc-page .result-stat .val {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #2f4050;
    font-variant-numeric: tabular-nums;
}
.calc-page .result-stat.accent {
    background: linear-gradient(165deg, #1ab394 0%, #18a689 100%);
    border-color: #1ab394;
}
.calc-page .result-stat.accent .lbl { color: rgba(255,255,255,0.85); }
.calc-page .result-stat.accent .val { color: #fff; }
.calc-page .result-table {
    width: 100%;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}
.calc-page .result-table td {
    padding: 12px 14px;
    border-bottom: 1px dashed #eef1f2;
    font-size: 13px;
    vertical-align: middle;
}
.calc-page .result-table tr:last-child td { border-bottom: 0; }
.calc-page .result-table .lbl {
    width: 38%;
    color: #888;
    font-weight: 600;
}
.calc-page .result-table .val {
    color: #2f4050;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.calc-page .product-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 12px;
}
@media (max-width: 991px) {
    .calc-page .calc-side-card {
        max-width: 360px;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>

<div class="col-lg-12 calc-page">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <aside class="calc-side-card">
                <div class="calc-icon-wrap">
                    <i class="fa fa-calculator"></i>
                </div>
                <h3>Loan Calculator</h3>
                <p>Estimate installment amount, interest, and total loan based on product rate and term.</p>
                <ul class="calc-tips">
                    <li><i class="fa fa-check-circle"></i><span>Enter the principal (base amount)</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Set the number of installments</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Choose a loan product</span></li>
                    <li><i class="fa fa-check-circle"></i><span>Review the calculated schedule totals</span></li>
                </ul>
            </aside>
        </div>

        <div class="col-lg-9 col-md-8">
            <?php echo form_open_multipart(current_lang() . "/calculator/index", 'class="form-horizontal"'); ?>

            <?php
            if (isset($message) && !empty($message)) {
                echo '<div class="calc-alert success displaymessage">' . $message . '</div>';
            } else if ($this->session->flashdata('message') != '') {
                echo '<div class="calc-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
            } else if (isset($warning) && !empty($warning)) {
                echo '<div class="calc-alert danger displaymessage">' . $warning . '</div>';
            } else if ($this->session->flashdata('warning') != '') {
                echo '<div class="calc-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
            }
            echo validation_errors();
            ?>

            <div class="calc-panel">
                <div class="panel-head">
                    <i class="fa fa-sliders"></i>
                    <h4>Calculation Inputs</h4>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Base Amount : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="base_amount" value="<?php echo set_value('base_amount'); ?>" class="form-control" placeholder="0.00"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label">Installment Number : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <input type="text" name="install_no" value="<?php echo set_value('install_no'); ?>" class="form-control" placeholder="e.g. 12"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 col-md-4 control-label"><?php echo lang('loan_product'); ?> : <span class="required">*</span></label>
                        <div class="col-lg-7 col-md-8">
                            <select name="product" class="form-control">
                                <option value=""><?php echo lang('select_default_text'); ?></option>
                                <?php
                                $selected = set_value('product');
                                foreach ($loan_product_list as $key => $value) {
                                    ?>
                                    <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                                <?php } ?>
                            </select>
                            <?php echo form_error('product'); ?>
                        </div>
                    </div>
                    <div class="form-group calc-actions">
                        <label class="col-lg-3 col-md-4 control-label">&nbsp;</label>
                        <div class="col-lg-7 col-md-8">
                            <a href="<?php echo site_url(current_lang() . '/calculator/index'); ?>" class="btn btn-default">
                                <i class="fa fa-undo"></i> Clear
                            </a>
                            <button type="submit" name="submit" value="Calculate" class="btn btn-primary">
                                <i class="fa fa-calculator"></i> Calculate
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo form_close(); ?>

            <?php if (isset($return_data)) { ?>
                <div class="calc-panel">
                    <div class="panel-head">
                        <i class="fa fa-file-text-o"></i>
                        <h4>Calculator Report</h4>
                    </div>
                    <div class="panel-body">
                        <div class="result-hero">
                            <div class="result-stat">
                                <span class="lbl">Installment Amount</span>
                                <span class="val"><?php echo number_format($return_data['installment_amount'], 2); ?></span>
                            </div>
                            <div class="result-stat">
                                <span class="lbl">Total Interest</span>
                                <span class="val"><?php echo number_format($return_data['interest_amount'], 2); ?></span>
                            </div>
                            <div class="result-stat accent">
                                <span class="lbl">Total Loan</span>
                                <span class="val"><?php echo number_format(($return_data['base_amount'] + $return_data['interest_amount']), 2); ?></span>
                            </div>
                        </div>

                        <table class="result-table">
                            <tr>
                                <td class="lbl">Base Amount</td>
                                <td class="val"><?php echo number_format($return_data['base_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="lbl">Installment Number</td>
                                <td class="val"><?php echo number_format($return_data['installment_no'], 0); ?></td>
                            </tr>
                            <tr>
                                <td class="lbl">Product Name</td>
                                <td class="val"><span class="product-badge"><?php echo htmlspecialchars($return_data['product']->name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                            </tr>
                            <tr>
                                <td class="lbl">Rate</td>
                                <td class="val"><?php echo htmlspecialchars($return_data['product']->interest_rate, ENT_QUOTES, 'UTF-8'); ?>%</td>
                            </tr>
                            <tr>
                                <td class="lbl">Installment Amount</td>
                                <td class="val"><?php echo number_format($return_data['installment_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="lbl">Total Interest</td>
                                <td class="val"><?php echo number_format($return_data['interest_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="lbl">Total Loan</td>
                                <td class="val"><?php echo number_format(($return_data['base_amount'] + $return_data['interest_amount']), 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
