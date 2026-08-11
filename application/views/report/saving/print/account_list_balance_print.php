<?php
$company = company_info();
$period_from = !empty($reportinfo->fromdate) ? strtoupper(date('F d, Y', strtotime($reportinfo->fromdate))) : '';
$period_to = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
$account_type_names = isset($account_type_names) ? $account_type_names : array();
$account_type_label = isset($account_type_label) ? $account_type_label : 'All Account Types';
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
                <div style="font-weight:bold; font-size:13px; margin-top:4px; line-height:1.2;">SAVINGS ACCOUNT LIST</div>
                <div style="font-size:10px; line-height:1.2;">
                    Accounts opened from <?php echo $period_from; ?> to <?php echo $period_to; ?>
                </div>
                <div style="font-size:10px; line-height:1.2;">
                    Account Type: <?php echo htmlspecialchars($account_type_label); ?>
                </div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px; padding:0;">
                LENDING
            </td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:9px;">
        <thead>
            <tr>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:35px;">S/No</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account No</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Member ID</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account Name</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account Type</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:70px;">Date Opened</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:85px;">Available Balance</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:85px;">Maintaining Balance</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:85px;">Total Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($transaction)) {
            ?>
            <tr>
                <td colspan="9" style="text-align:center; padding:12px; color:#666; font-style:italic;">
                    No savings accounts found for accounts opened
                    <?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?>.
                </td>
            </tr>
            <?php
        } else {
            $i = 1;
            $balance = 0;
            $maintaining = 0;
            $actual = 0;
            foreach ($transaction as $value) {
                $avail = floatval($value->balance);
                $maint = floatval($value->virtual_balance);
                $act = $avail + $maint;
                $balance += $avail;
                $maintaining += $maint;
                $actual += $act;

                $acct_no = !empty($value->old_members_acct) ? $value->old_members_acct : $value->account;
                $member_id = !empty($value->members_member_id) ? $value->members_member_id : $value->member_id;
                $acct_name = $this->report_model->saving_account_name($value->RFID, $value->tablename);
                $type_name = isset($account_type_names[$value->account_cat])
                    ? $account_type_names[$value->account_cat]
                    : $value->account_cat;
                $opened = !empty($value->createdon) ? date('M d, Y', strtotime($value->createdon)) : '&mdash;';
                ?>
                <tr>
                    <td style="text-align:center; padding:2px 3px;"><?php echo $i++; ?>.</td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($acct_no); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($member_id); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($acct_name); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($type_name); ?></td>
                    <td style="text-align:center; padding:2px 3px; white-space:nowrap;"><?php echo $opened; ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo number_format($avail, 2); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo number_format($maint, 2); ?></td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px;"><?php echo number_format($act, 2); ?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="6"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($balance, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($maintaining, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($actual, 2); ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

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
