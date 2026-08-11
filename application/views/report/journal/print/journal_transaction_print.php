<?php
$company = company_info();
$journal_label = !empty($journalinfo->type)
    ? strtoupper(function_exists('journal_display_type') ? journal_display_type($journalinfo->type) : $journalinfo->type)
    : 'JOURNAL';
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
                <div style="font-weight:bold; font-size:13px; margin-top:4px; line-height:1.2;">
                    <?php echo htmlspecialchars($journal_label); ?> JOURNAL ENTRIES
                </div>
                <div style="font-size:10px; line-height:1.2;">
                    For the period from <?php echo $period_from; ?> to <?php echo $period_to; ?>
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
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:55px;">Date</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:40px;">Entry</th>
                <th style="text-align:center; border-bottom:1px solid #000; padding:3px; width:50px;">Ref #</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Account</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Member / Person</th>
                <th style="text-align:left; border-bottom:1px solid #000; padding:3px;">Particulars</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:85px;">Debit</th>
                <th style="text-align:right; border-bottom:1px solid #000; padding:3px; width:85px;">Credit</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (empty($transaction)) {
            ?>
            <tr>
                <td colspan="8" style="text-align:center; padding:12px; color:#666; font-style:italic;">
                    No journal transactions found for the selected date range
                    (<?php echo format_date($reportinfo->fromdate, false); ?> to <?php echo format_date($reportinfo->todate, false); ?>).
                </td>
            </tr>
            <?php
        } else {
            $entry_id = null;
            $year = null;
            $total_credit = 0;
            $total_debit = 0;
            $show_date = true;

            foreach ($transaction as $key => $value) {
                $total_credit += floatval($value->credit);
                $total_debit += floatval($value->debit);
                $year_track = date('Y', strtotime($value->date));
                $border = '';

                if ($key === 0) {
                    $entry_id = $value->entryid;
                    $year = $year_track;
                    $show_date = true;
                    ?>
                    <tr>
                        <td colspan="8" style="padding:4px 3px 1px; font-weight:bold;"><?php echo $year_track; ?></td>
                    </tr>
                    <?php
                } else {
                    if ($year != $year_track) {
                        $year = $year_track;
                        ?>
                        <tr>
                            <td colspan="8" style="padding:8px 3px 1px; font-weight:bold;"><?php echo $year_track; ?></td>
                        </tr>
                        <?php
                    }
                    if ($entry_id != $value->entryid) {
                        $border = 'border-top:1px solid #000;';
                        $entry_id = $value->entryid;
                        $show_date = true;
                    } else {
                        $show_date = false;
                    }
                }

                $ref_no = '';
                if (!empty($value->related_ref_no)) {
                    $ref_no = $value->related_ref_no;
                } else {
                    $ref_no = (isset($value->invoiceid) && $value->invoiceid > 0)
                        ? $value->invoiceid
                        : (isset($value->refferenceID) ? $value->refferenceID : '');
                }
                $acct_code = isset($value->account) ? $value->account : '';
                $acct_label = htmlspecialchars($acct_code) . ' - ' . htmlspecialchars($value->name);
                $pad = (floatval($value->credit) > 0) ? 'padding-left:18px;' : '';
                $particulars = '';
                if (!empty($value->description)) {
                    $particulars = $value->description;
                } elseif (!empty($value->trans_comment)) {
                    $particulars = $value->trans_comment;
                }
                $rel_name = isset($value->related_entity_name) ? $value->related_entity_name : '';
                ?>
                <tr>
                    <td style="text-align:center; padding:2px 3px; white-space:nowrap; <?php echo $border; ?>">
                        <?php echo $show_date ? date('M d', strtotime($value->date)) : ''; ?>
                    </td>
                    <td style="text-align:center; padding:2px 3px; <?php echo $border; ?>">
                        <?php echo $show_date ? htmlspecialchars($value->entryid) : ''; ?>
                    </td>
                    <td style="text-align:center; padding:2px 3px; <?php echo $border; ?>">
                        <?php echo ($ref_no !== '' && $ref_no !== null) ? '#' . htmlspecialchars($ref_no) : '&mdash;'; ?>
                    </td>
                    <td style="padding:2px 3px; <?php echo $pad . $border; ?>"><?php echo $acct_label; ?></td>
                    <td style="padding:2px 3px; <?php echo $border; ?>">
                        <?php echo $rel_name !== '' ? htmlspecialchars($rel_name) : '&mdash;'; ?>
                    </td>
                    <td style="padding:2px 3px; <?php echo $border; ?>">
                        <?php echo $particulars !== '' ? htmlspecialchars($particulars) : '&mdash;'; ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px; <?php echo $border; ?>">
                        <?php echo (floatval($value->debit) > 0 ? number_format($value->debit, 2) : ''); ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap; padding:2px 3px; <?php echo $border; ?>">
                        <?php echo (floatval($value->credit) > 0 ? number_format($value->credit, 2) : ''); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000;" colspan="6"></td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($total_debit, 2); ?>
                </td>
                <td style="border-top:1px solid #000; border-bottom:1px solid #000; text-align:right; white-space:nowrap; padding:3px; font-weight:bold;">
                    <span style="float:left;">P</span><?php echo number_format($total_credit, 2); ?>
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
