<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$member_id = !empty($reportinfo->description) ? $reportinfo->description : '';
$member_name = $member_id !== '' ? $this->member_model->member_name($member_id) : '';
$member_name = trim(preg_replace('/\s+/', ' ', $member_name));
$cbu_balance = 0;
if ($member_id !== '') {
    $bal_row = $this->db->query(
        "SELECT IFNULL(mc.balance, 0) AS balance
         FROM members m
         LEFT JOIN members_contribution mc ON mc.PID = m.PID
         WHERE m.member_id = ? AND m.PIN = ?
         LIMIT 1",
        array($member_id, current_user()->PIN)
    )->row();
    if ($bal_row) {
        $cbu_balance = floatval($bal_row->balance);
    }
}
$transaction = isset($transaction) ? $transaction : array();

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
?>
<div style="padding:0; margin:0;">
    <table style="width:100%; margin:0 0 6px 0; border-collapse:collapse;">
        <tr>
            <td style="width:<?php echo ($logo_w + 10); ?>px; vertical-align:middle; padding:0;">
                <?php if ($logo_src !== '') { ?>
                    <img src="<?php echo $logo_src; ?>" width="<?php echo (int) $logo_w; ?>" height="<?php echo (int) $logo_h; ?>" alt="logo"/>
                <?php } ?>
            </td>
            <td style="text-align:center; vertical-align:middle; padding:0 6px;">
                <div style="font-weight:bold; font-size:12px; text-transform:uppercase;"><?php echo htmlspecialchars($company->name); ?></div>
                <div style="font-size:10px;"><?php echo htmlspecialchars($company->address ? $company->address : $company->box); ?></div>
                <div style="font-weight:bold; font-size:13px; margin-top:4px;">MEMBER CBU STATEMENT</div>
                <div style="font-size:10px;">For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?></div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px;">LENDING</td>
        </tr>
    </table>

    <table style="width:100%; margin-bottom:8px; font-size:9px; border-collapse:collapse;">
        <tr>
            <td style="padding:2px 0; width:50%;"><strong>Member ID:</strong> <?php echo htmlspecialchars($member_id); ?></td>
            <td style="padding:2px 0;"><strong>Current CBU Balance:</strong> <?php echo number_format($cbu_balance, 2); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="padding:2px 0;"><strong>Member Name:</strong> <?php echo htmlspecialchars($member_name); ?></td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:9px;">
        <thead>
            <tr>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:70px;">Date</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Particulars</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px; width:70px;">Method</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:80px;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:80px;">Credit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:85px;">Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $balance = 0;
        $credit = 0;
        $debit = 0;
        $previous_trans = $this->report_model->contribution_statement_previous($reportinfo->fromdate, $member_id);
        if ($previous_trans) {
            $balance = floatval($previous_trans->credit) - floatval($previous_trans->debit);
        }
        ?>
            <tr>
                <td style="padding:2px 3px;"></td>
                <td style="padding:2px 3px; font-weight:bold;">BROUGHT FORWARD BALANCE</td>
                <td style="padding:2px 3px;"></td>
                <td style="padding:2px 3px;"></td>
                <td style="padding:2px 3px;"></td>
                <td style="text-align:right; padding:2px 3px; font-weight:bold;"><?php echo number_format($balance, 2); ?></td>
            </tr>
        <?php
        if (empty($transaction)) {
            ?>
            <tr>
                <td colspan="6" style="text-align:center; padding:12px; font-style:italic;">
                    No CBU transactions for this period.
                </td>
            </tr>
            <?php
        } else {
            foreach ($transaction as $value) {
                $dt = explode(' ', $value->createdon);
                $row_debit = floatval($value->debit);
                $row_credit = floatval($value->credit);
                if ($row_debit > 0) {
                    $balance -= $row_debit;
                    $debit += $row_debit;
                } elseif ($row_credit > 0) {
                    $balance += $row_credit;
                    $credit += $row_credit;
                }
                $particulars = trim(isset($value->system_comment) ? $value->system_comment : '');
                if (!empty($value->comment)) {
                    $particulars .= ($particulars !== '' ? ' — ' : '') . $value->comment;
                }
                ?>
                <tr>
                    <td style="text-align:center; padding:2px 3px;"><?php echo format_date($dt[0], false); ?></td>
                    <td style="padding:2px 3px;"><?php echo $particulars !== '' ? htmlspecialchars($particulars) : '&mdash;'; ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars(isset($value->paymethod) ? $value->paymethod : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo ($row_debit > 0 ? number_format($row_debit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo ($row_credit > 0 ? number_format($row_credit, 2) : ''); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo number_format($balance, 2); ?></td>
                </tr>
                <?php
            }
        }
        ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="3"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($debit, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($credit, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <?php echo number_format($balance, 2); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="text-align:right; margin-top:8px; font-size:11px; font-weight:bold;">
        Ending Balance: <?php echo number_format($balance, 2); ?>
    </div>

    <table style="width:100%; margin-top:28px; font-size:10px;">
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
