<?php
$loan_beginning_balances = isset($loan_beginning_balances) ? $loan_beginning_balances : array();
$member_names = isset($member_names) ? $member_names : array();
$product_info = isset($product_info) ? $product_info : array();
$activated_map = isset($activated_map) ? $activated_map : array();
$row_count = is_array($loan_beginning_balances) ? count($loan_beginning_balances) : 0;
?>
<?php if ($row_count > 0) {
    $i = 1;
    foreach ($loan_beginning_balances as $balance) {
        $member_info = isset($member_names[$balance->member_id]) ? $member_names[$balance->member_id] : 'Unknown';
        $product_name = isset($product_info[$balance->loan_product_id]) ? $product_info[$balance->loan_product_id] : '-';
        $is_activated = !empty($activated_map[$balance->id]);
        ?>
        <tr>
            <td style="text-align:center;"><?php echo $i++; ?></td>
            <td><span class="member-id-chip"><?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?></span></td>
            <td><?php echo htmlspecialchars($member_info, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo $balance->loan_id ? htmlspecialchars($balance->loan_id, ENT_QUOTES, 'UTF-8') : '-'; ?></td>
            <td class="amount-cell"><?php echo $balance->loan_amount ? number_format($balance->loan_amount, 2) : '-'; ?></td>
            <td class="amount-cell"><?php echo $balance->monthly_amort ? number_format($balance->monthly_amort, 2) : '-'; ?></td>
            <td><?php echo $balance->term ? htmlspecialchars($balance->term . ' months', ENT_QUOTES, 'UTF-8') : '-'; ?></td>
            <td><?php echo !empty($balance->disbursement_date) ? date('d-m-Y', strtotime($balance->disbursement_date)) : '-'; ?></td>
            <td><?php echo $balance->last_date_paid ? date('d-m-Y', strtotime($balance->last_date_paid)) : '-'; ?></td>
            <td class="amount-cell"><?php echo number_format($balance->principal_balance, 2); ?></td>
            <td class="amount-cell"><?php echo number_format($balance->interest_balance, 2); ?></td>
            <td class="amount-cell"><?php echo number_format($balance->penalty_balance, 2); ?></td>
            <td class="amount-cell"><?php echo number_format($balance->total_balance, 2); ?></td>
            <td>
                <?php if ($is_activated) { ?>
                    <span class="status-pill activated"><?php echo lang('loan_beginning_balance_activated'); ?></span>
                    <?php if ($balance->loan_id) { ?>
                        <span class="status-sub">
                            <a href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($balance->loan_id)); ?>">
                                <?php echo htmlspecialchars($balance->loan_id, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </span>
                    <?php } ?>
                <?php } else if ((int) $balance->posted === 1) { ?>
                    <span class="status-pill posted"><?php echo lang('loan_beginning_balance_posted'); ?></span>
                    <?php if ($balance->posted_date) { ?>
                        <span class="status-sub"><?php echo date('M d, Y H:i', strtotime($balance->posted_date)); ?></span>
                    <?php } ?>
                <?php } else { ?>
                    <span class="status-pill not-posted"><?php echo lang('loan_beginning_balance_not_posted'); ?></span>
                <?php } ?>
            </td>
            <td>
                <div class="action-btns">
                    <?php if ((int) $balance->posted === 0) { ?>
                        <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_create/' . encode_id($balance->id)); ?>">
                            <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-success btn-xs btn-post-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-check"></i> <?php echo lang('loan_beginning_balance_post'); ?>
                        </a>
                    <?php } else if ($is_activated) { ?>
                        <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($balance->loan_id)); ?>">
                            <i class="fa fa-folder-open"></i> <?php echo lang('loan_view_detail'); ?>
                        </a>
                        <a class="btn btn-default btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_ledger/' . encode_id($balance->loan_id)); ?>">
                            <i class="fa fa-book"></i> <?php echo lang('loan_ledger'); ?>
                        </a>
                    <?php } else { ?>
                        <a class="btn btn-default btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_create/' . encode_id($balance->id)); ?>">
                            <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-info btn-xs btn-activate-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-play-circle"></i> <?php echo lang('loan_beginning_balance_activate'); ?>
                        </a>
                        <?php if (has_role(5, 'void_transaction')) { ?>
                        <a class="btn btn-danger btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_void/' . encode_id($balance->id)); ?>" onclick="return confirm('Void this loan beginning balance with a reversing GL entry?');">
                            <i class="fa fa-undo"></i> Void
                        </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </td>
        </tr>
    <?php }
} else { ?>
    <tr>
        <td colspan="16">
            <div class="empty-state">
                <i class="fa fa-list"></i>
                <?php echo lang('data_not_found'); ?>
            </div>
        </td>
    </tr>
<?php } ?>
