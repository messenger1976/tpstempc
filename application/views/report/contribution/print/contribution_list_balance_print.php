<?php
$company = company_info();
$as_of = !empty($reportinfo->todate) ? strtoupper(date('F d, Y', strtotime($reportinfo->todate))) : '';
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
                <div style="font-weight:bold; font-size:13px; margin-top:4px;">MEMBER CBU BALANCE</div>
                <div style="font-size:10px;">As of <?php echo $as_of; ?></div>
            </td>
            <td style="width:70px; text-align:right; vertical-align:bottom; font-weight:bold; font-size:10px;">LENDING</td>
        </tr>
    </table>

    <table style="width:100%; border-collapse:collapse; font-size:9px;">
        <thead>
            <tr>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:35px;">S/No</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px; width:80px;">Member ID</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Member Name</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:80px;">Date Joined</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:95px;">CBU Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($transaction)) {
            ?>
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; font-style:italic;">
                    No members found as of <?php echo format_date($reportinfo->todate, false); ?>.
                </td>
            </tr>
            <?php
        } else {
            $i = 1;
            $balance = 0;
            foreach ($transaction as $value) {
                $row_balance = floatval($value->balance);
                $balance += $row_balance;
                $joined = '';
                if (!empty($value->joiningdate) && $value->joiningdate !== '0000-00-00' && $value->joiningdate !== '0000-00-00 00:00:00') {
                    $joined = format_date(substr($value->joiningdate, 0, 10), false);
                }
                $name = trim(preg_replace('/\s+/', ' ', isset($value->name) ? $value->name : ''));
                ?>
                <tr>
                    <td style="text-align:center; padding:2px 3px;"><?php echo $i++; ?>.</td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($value->member_id); ?></td>
                    <td style="padding:2px 3px;"><?php echo htmlspecialchars($name); ?></td>
                    <td style="text-align:center; padding:2px 3px;"><?php echo htmlspecialchars($joined); ?></td>
                    <td style="text-align:right; padding:2px 3px;"><?php echo number_format($row_balance, 2); ?></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="4"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($balance, 2); ?>
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
