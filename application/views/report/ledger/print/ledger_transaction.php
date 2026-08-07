<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';

$logo_src = '';
$logo_w = 50;
$logo_h = 50;
if (!empty($company->logo)) {
    $logo_file = FCPATH . 'logo' . DIRECTORY_SEPARATOR . $company->logo;
    if (is_file($logo_file)) {
        $logo_src = str_replace('\\', '/', $logo_file);
        $size = @getimagesize($logo_file);
        if ($size && !empty($size[0]) && !empty($size[1])) {
            $max = 50;
            if ($size[0] >= $size[1]) {
                $logo_w = $max;
                $logo_h = max(1, (int) round($max * ($size[1] / $size[0])));
            } else {
                $logo_h = $max;
                $logo_w = max(1, (int) round($max * ($size[0] / $size[1])));
            }
        }
    } else {
        $logo_src = base_url() . 'logo/' . $company->logo;
    }
}

$transaction = isset($transaction) ? $transaction : array();
?>
<div style="padding: 0; margin: 0;">
    <table style="width:100%; margin:0 0 6px 0; border-collapse:collapse;">
        <tr>
            <td style="width:<?php echo ($logo_w + 10); ?>px; vertical-align:middle; padding:0; text-align:left;">
                <?php if ($logo_src !== '') { ?>
                    <img src="<?php echo $logo_src; ?>" width="<?php echo (int) $logo_w; ?>" height="<?php echo (int) $logo_h; ?>" alt="logo"/>
                <?php } ?>
            </td>
            <td style="text-align:center; vertical-align:middle; padding:0 6px;">
                <div style="font-weight:bold; font-size:12px; text-transform:uppercase; line-height:1.25;">
                    <?php echo htmlspecialchars($company->name); ?>
                </div>
                <div style="font-size:10px; line-height:1.2;">
                    <?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?>
                </div>
                <div style="font-weight:bold; font-size:13px; margin-top:4px; line-height:1.2;">GENERAL LEDGER TRANSACTIONS</div>
                <div style="font-size:10px; line-height:1.2;">
                    For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                </div>
                <?php if (!empty($account_name)) { ?>
                    <div style="font-size:10px; line-height:1.2;">
                        Account: <?php echo htmlspecialchars($account_name); ?>
                    </div>
                <?php } ?>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px; padding:0;">
                LENDING
            </td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <thead>
            <tr>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Type</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:70px;">Date</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:45px;">#</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Person/Member/Item</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:90px;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:90px;">Credit</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Remarks</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $credittotal = 0;
        $debittotal = 0;
        foreach ($transaction as $value) {
            $credittotal += floatval($value->credit);
            $debittotal += floatval($value->debit);
            $journal_type = isset($value->trans_comment) ? $value->trans_comment : '';
            $ref_no = (isset($value->invoiceid) && $value->invoiceid > 0) ? $value->invoiceid : '';
            $pad = (floatval($value->credit) > 0) ? 'padding-left:18px;' : '';
            ?>
            <tr>
                <td style="padding:2px 3px;"><?php echo $journal_type !== '' ? htmlspecialchars($journal_type) : '&mdash;'; ?></td>
                <td style="text-align:center; padding:2px 3px; white-space:nowrap;"><?php echo format_date($value->date, false); ?></td>
                <td style="text-align:center; padding:2px 3px;"><?php echo $ref_no !== '' ? '#' . $ref_no : ''; ?></td>
                <td style="padding:2px 3px; <?php echo $pad; ?>">
                    <?php echo htmlspecialchars($value->account); ?> - <?php echo htmlspecialchars($value->name); ?>
                </td>
                <td style="padding:2px 3px;">
                    <?php echo (isset($value->related_entity_name) && $value->related_entity_name !== '') ? htmlspecialchars($value->related_entity_name) : '&mdash;'; ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px;">
                    <?php echo (floatval($value->debit) > 0 ? number_format($value->debit, 2) : ''); ?>
                </td>
                <td style="text-align:right; white-space:nowrap; padding:2px 3px;">
                    <?php echo (floatval($value->credit) > 0 ? number_format($value->credit, 2) : ''); ?>
                </td>
                <td style="padding:2px 3px;"><?php echo isset($value->description) && $value->description !== '' ? htmlspecialchars($value->description) : '&mdash;'; ?></td>
            </tr>
        <?php } ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="5"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($debittotal, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($credittotal, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;"></td>
            </tr>
        </tbody>
    </table>

    <table style="width:100%; margin-top:36px; font-size:11px; border-collapse:collapse;">
        <tr>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Certified Correct:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">ANTONINA P. PATUNGAN</span><br/>
                Bookkeeper
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Checked by:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">ANA MARIE F. VALMORIA</span><br/>
                AICOM
            </td>
            <td style="width:33%; vertical-align:top; text-align:center;">
                Noted by:<br/>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr><td height="95" style="height:95px; font-size:1px; line-height:95px;">&nbsp;</td></tr></table>
                <span style="font-weight:bold; text-decoration:underline;">REMEDIOS T. AUXTERO</span><br/>
                Manager
            </td>
        </tr>
    </table>
</div>
