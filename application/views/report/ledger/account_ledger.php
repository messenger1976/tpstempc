<?php
if (!function_exists('al_fmt')) {
    function al_fmt($amount, $show_zero = false) {
        $v = floatval($amount);
        if (!$show_zero && abs($v) < 0.005) {
            return '';
        }
        if (abs($v) < 0.005) {
            return number_format(0, 2);
        }
        if ($v < 0) {
            return '(' . number_format(abs($v), 2) . ')';
        }
        return number_format($v, 2);
    }
}
$acc = $ledger['account'];
$transactions = $ledger['transactions'];
$back_url = site_url(current_lang() . '/report/index');
if (!empty($back_link) && !empty($back_id)) {
    $bl = (string) $back_link;
    if (strlen($bl) > 1 && $bl[0] === 'j') {
        $journal_type_id = substr($bl, 1);
        $back_url = site_url(current_lang() . '/report/journal_trans_view/' . $journal_type_id . '/' . $back_id);
    } elseif ($bl === '5') {
        $back_url = site_url(current_lang() . '/report/ledger_balance_sheet_view/' . $back_link . '/' . $back_id);
    } elseif ($bl === '7') {
        $back_url = site_url(current_lang() . '/report/ledger_financial_condition_view/' . $back_link . '/' . $back_id);
    } elseif ($bl === '4') {
        $back_url = site_url(current_lang() . '/report/ledger_income_statement_view/' . $back_link . '/' . $back_id);
    } elseif ($bl === '3') {
        $back_url = site_url(current_lang() . '/report/ledger_trial_balance_view/' . $back_link . '/' . $back_id);
    } elseif ($bl === '2') {
        $back_url = site_url(current_lang() . '/report/ledger_trans_summary_view/' . $back_link . '/' . $back_id);
    } elseif ($bl === '1') {
        $back_url = site_url(current_lang() . '/report/ledger_trans_view/' . $back_link . '/' . $back_id);
    } elseif ($bl === '6') {
        $back_url = site_url(current_lang() . '/report/cash_flow_report_view/' . $back_id);
    } elseif ($bl === '8') {
        $back_url = site_url(current_lang() . '/report/ledger_financial_operations_view/' . $back_link . '/' . $back_id);
    } else {
        $back_url = site_url(current_lang() . '/report/general_leger_transaction/' . $back_link);
    }
}
$form_action = current_lang() . '/report/account_ledger/' . $account_enc
    . (!empty($back_link) ? '/' . $back_link : '')
    . (!empty($back_id) ? '/' . $back_id : '');
$print_url = site_url(current_lang() . '/report/account_ledger_print/' . $account_enc
    . (!empty($back_link) ? '/' . $back_link : '/0')
    . (!empty($back_id) ? '/' . $back_id : '/0')
    . '?fromdate=' . rawurlencode(format_date($fromdate, false))
    . '&todate=' . rawurlencode(format_date($todate, false)));
?>
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">

<div class="row">
    <div class="col-lg-12">
        <?php if (!empty($warning)) { ?>
            <div class="label label-danger displaymessage"><?php echo $warning; ?></div>
        <?php } ?>

        <div class="ibox" style="margin-bottom: 15px;">
            <div class="ibox-title" style="padding: 10px 15px;">
                <h5 style="margin:0;">Filter Account Ledger</h5>
            </div>
            <div class="ibox-content" style="padding: 15px;">
                <?php echo form_open($form_action, 'class="form-horizontal" method="get"'); ?>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="col-lg-2 control-label">From :</label>
                    <div class="col-lg-3">
                        <div class="input-group date" id="datetimepicker">
                            <input type="text" name="fromdate" value="<?php echo format_date($fromdate, false); ?>" data-date-format="DD-MM-YYYY" class="form-control" placeholder="DD-MM-YYYY"/>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <label class="col-lg-1 control-label">Until :</label>
                    <div class="col-lg-3">
                        <div class="input-group date" id="datetimepicker2">
                            <input type="text" name="todate" value="<?php echo format_date($todate, false); ?>" data-date-format="DD-MM-YYYY" class="form-control" placeholder="DD-MM-YYYY"/>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>

        <div style="padding: 10px; margin: auto;">
            <div style="text-align: center; margin-bottom: 15px;">
                <h3 style="margin:0;"><strong><?php echo htmlspecialchars(company_info()->name); ?></strong></h3>
                <h2 style="margin:8px 0 4px;"><strong>Account Ledger</strong></h2>
                <h4 style="margin:0;">
                    <strong><?php echo htmlspecialchars($acc->account . ' — ' . $acc->name); ?></strong>
                </h4>
                <div style="margin-top:4px; color:#555;">
                    For the period from <?php echo format_date($fromdate, false); ?>
                    to <?php echo format_date($todate, false); ?>
                </div>
            </div>

            <div class="row" style="margin-bottom: 15px;">
                <div class="col-sm-4">
                    <div style="border:1px solid #ddd; border-radius:4px; padding:12px; background:#f9f9f9;">
                        <div style="font-size:11px; text-transform:uppercase; color:#777;">Balance Forwarded</div>
                        <div style="font-size:20px; font-weight:bold; margin-top:4px;"><?php echo al_fmt($ledger['opening_balance'], true); ?></div>
                        <div style="font-size:11px; color:#888; margin-top:4px;">As of before <?php echo format_date($fromdate, false); ?></div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="border:1px solid #ddd; border-radius:4px; padding:12px; background:#f9f9f9;">
                        <div style="font-size:11px; text-transform:uppercase; color:#777;">Period Movement</div>
                        <div style="font-size:14px; margin-top:6px;">
                            Debit: <strong><?php echo number_format($ledger['period_debit'], 2); ?></strong>
                            &nbsp;|&nbsp;
                            Credit: <strong><?php echo number_format($ledger['period_credit'], 2); ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div style="border:1px solid #1ab394; border-radius:4px; padding:12px; background:#f3fffb;">
                        <div style="font-size:11px; text-transform:uppercase; color:#777;">Ending Balance</div>
                        <div style="font-size:20px; font-weight:bold; margin-top:4px; color:#1ab394;"><?php echo al_fmt($ledger['ending_balance'], true); ?></div>
                        <div style="font-size:11px; color:#888; margin-top:4px;">As of <?php echo format_date($todate, false); ?></div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th style="width:90px;">Date</th>
                            <th style="width:110px;">Type</th>
                            <th style="width:70px;">Ref #</th>
                            <th>Description / Person</th>
                            <th style="text-align:right; width:110px;">Debit</th>
                            <th style="text-align:right; width:110px;">Credit</th>
                            <th style="text-align:right; width:120px;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#eef7f4; font-weight:bold;">
                            <td colspan="4">Balance Forwarded</td>
                            <td style="text-align:right;"><?php echo ($ledger['opening_debit'] > 0 ? number_format($ledger['opening_debit'], 2) : ''); ?></td>
                            <td style="text-align:right;"><?php echo ($ledger['opening_credit'] > 0 ? number_format($ledger['opening_credit'], 2) : ''); ?></td>
                            <td style="text-align:right;"><?php echo al_fmt($ledger['opening_balance'], true); ?></td>
                        </tr>
                        <?php if (empty($transactions)) { ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:#999; font-style:italic; padding:20px;">
                                    No transactions found for this account in the selected date range.
                                </td>
                            </tr>
                        <?php } else {
                            foreach ($transactions as $t) {
                                $journal_type = isset($t->trans_comment) ? $t->trans_comment : '';
                                $ref_no = (isset($t->invoiceid) && $t->invoiceid > 0) ? $t->invoiceid : (isset($t->refferenceID) ? $t->refferenceID : '');
                                $ref_url = function_exists('get_gl_reference_url')
                                    ? get_gl_reference_url(isset($t->fromtable) ? $t->fromtable : '', isset($t->refferenceID) ? $t->refferenceID : null)
                                    : '';
                                $desc = isset($t->description) ? $t->description : '';
                                $rel = isset($t->related_entity_name) ? $t->related_entity_name : '';
                                ?>
                                <tr>
                                    <td><?php echo format_date($t->date, false); ?></td>
                                    <td><?php echo $journal_type !== '' ? htmlspecialchars($journal_type) : '&mdash;'; ?></td>
                                    <td><?php
                                        if ($ref_no !== '' && $ref_no !== null) {
                                            if ($ref_url !== '') {
                                                echo anchor($ref_url, '#' . $ref_no, array('title' => 'View reference'));
                                            } else {
                                                echo '#' . htmlspecialchars($ref_no);
                                            }
                                        } else {
                                            echo '&mdash;';
                                        }
                                    ?></td>
                                    <td><?php
                                        $parts = array();
                                        if ($desc !== '') {
                                            $parts[] = htmlspecialchars($desc);
                                        }
                                        if ($rel !== '') {
                                            if (!empty($t->related_entity_url)) {
                                                $parts[] = anchor($t->related_entity_url, htmlspecialchars($rel));
                                            } else {
                                                $parts[] = htmlspecialchars($rel);
                                            }
                                        }
                                        echo !empty($parts) ? implode(' — ', $parts) : '&mdash;';
                                    ?></td>
                                    <td style="text-align:right;"><?php echo ($t->debit > 0 ? number_format($t->debit, 2) : ''); ?></td>
                                    <td style="text-align:right;"><?php echo ($t->credit > 0 ? number_format($t->credit, 2) : ''); ?></td>
                                    <td style="text-align:right; font-weight:600;"><?php echo al_fmt($t->running_balance, true); ?></td>
                                </tr>
                            <?php }
                        } ?>
                        <tr style="font-weight:bold; background:#f5f5f5;">
                            <td colspan="4" style="text-align:right; border-top:2px solid #000;">Period Totals</td>
                            <td style="text-align:right; border-top:2px solid #000;"><?php echo number_format($ledger['period_debit'], 2); ?></td>
                            <td style="text-align:right; border-top:2px solid #000;"><?php echo number_format($ledger['period_credit'], 2); ?></td>
                            <td style="text-align:right; border-top:2px solid #000;"></td>
                        </tr>
                        <tr style="font-weight:bold; background:#e8f8f5;">
                            <td colspan="6" style="text-align:right; border-bottom:3px double #000;">Ending Balance</td>
                            <td style="text-align:right; border-bottom:3px double #000;"><?php echo al_fmt($ledger['ending_balance'], true); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="text-align:center; margin-top: 20px;">
                <button type="button" class="btn btn-success js-download-report-pdf"
                        data-pdf-url="<?php echo htmlspecialchars($print_url); ?>"
                        data-pdf-name="Account_Ledger.pdf">
                    <i class="fa fa-download"></i> Download PDF
                </button>
                &nbsp;&nbsp;
                <a href="<?php echo $print_url; ?>" class="btn btn-primary">Print</a>
                &nbsp;&nbsp;
                <a href="<?php echo $back_url; ?>" class="btn btn-default">Back</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script type="text/javascript">
(function() {
    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }
        if (typeof $.fn.datetimepicker === 'undefined') {
            var script = document.createElement('script');
            script.src = '<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js';
            script.onload = function() {
                $(function () {
                    $('#datetimepicker, #datetimepicker2').datetimepicker({ pickTime: false });
                });
            };
            document.head.appendChild(script);
        } else {
            $(function () {
                $('#datetimepicker, #datetimepicker2').datetimepicker({ pickTime: false });
            });
        }
    }
    initScripts();
})();
</script>
