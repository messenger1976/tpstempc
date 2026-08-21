<?php
$loan_beginning_balances = isset($loan_beginning_balances) ? $loan_beginning_balances : array();
$member_names = isset($member_names) ? $member_names : array();
$product_info = isset($product_info) ? $product_info : array();
$activated_map = isset($activated_map) ? $activated_map : array();
$remaining_gl_map = isset($remaining_gl_map) ? $remaining_gl_map : array();
$has_gl_map = isset($has_gl_map) ? $has_gl_map : array();
$row_count = is_array($loan_beginning_balances) ? count($loan_beginning_balances) : 0;
?>
<?php if ($row_count > 0) {
    $i = 1;
    foreach ($loan_beginning_balances as $balance) {
        $member_info = isset($member_names[$balance->member_id]) ? $member_names[$balance->member_id] : 'Unknown';
        $product_name = isset($product_info[$balance->loan_product_id]) ? $product_info[$balance->loan_product_id] : '-';
        $is_activated = !empty($activated_map[$balance->id]);
        $has_remaining_gl = !empty($remaining_gl_map[$balance->id]);
        $can_print_journal = ((int) $balance->posted === 1) || $has_remaining_gl || !empty($has_gl_map[$balance->id]);
        // Do not allow Post while unreversed GL still exists (avoids duplicate Drs).
        $can_post = !$is_activated && (int) $balance->posted === 0 && !$has_remaining_gl;
        $can_activate = !$is_activated && (int) $balance->posted === 1;
        $can_void = !$is_activated && $has_remaining_gl && has_role(5, 'void_transaction');
        // Also allow Void on posted rows that still have GL (normal case).
        if (!$is_activated && (int) $balance->posted === 1 && has_role(5, 'void_transaction')) {
            $can_void = true;
        }
        ?>
        <tr>
            <td style="text-align:center;">
                <?php if ($can_post || $can_activate) { ?>
                    <input type="checkbox" class="bb-row-check"
                           value="<?php echo encode_id($balance->id); ?>"
                           data-can-post="<?php echo $can_post ? '1' : '0'; ?>"
                           data-can-activate="<?php echo $can_activate ? '1' : '0'; ?>" />
                <?php } else { ?>
                    <input type="checkbox" disabled="disabled" />
                <?php } ?>
            </td>
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
                <?php } else if ($has_remaining_gl) { ?>
                    <span class="status-pill not-posted"><?php echo lang('loan_beginning_balance_not_posted'); ?></span>
                    <span class="status-sub" style="color:#c0392b;">GL still open — void to clear</span>
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
                        <?php if (!$has_remaining_gl) { ?>
                        <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-delete-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?>
                        </a>
                        <?php } ?>
                        <?php if ($can_post) { ?>
                        <a href="javascript:void(0);" class="btn btn-success btn-xs btn-post-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-check"></i> <?php echo lang('loan_beginning_balance_post'); ?>
                        </a>
                        <?php } ?>
                        <?php if ($can_void) { ?>
                        <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-void-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-undo"></i> Void
                        </a>
                        <?php } ?>
                    <?php } else if ($is_activated) { ?>
                        <a class="btn btn-primary btn-xs" href="<?php echo site_url(current_lang() . '/loan/view_indetail/' . encode_id($balance->loan_id)); ?>">
                            <i class="fa fa-folder-open"></i> <?php echo lang('loan_view_detail'); ?>
                        </a>
                        <a class="btn btn-default btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_ledger/' . encode_id($balance->loan_id)); ?>">
                            <i class="fa fa-book"></i> <?php echo lang('loan_ledger'); ?>
                        </a>
                        <?php if (has_role(5, 'void_transaction')) { ?>
                        <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-deactivate-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-undo"></i> <?php echo lang('loan_void_bb_activation'); ?>
                        </a>
                        <?php } ?>
                    <?php } else { ?>
                        <a class="btn btn-default btn-xs" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_create/' . encode_id($balance->id)); ?>">
                            <i class="fa fa-edit"></i> <?php echo lang('button_edit'); ?>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-info btn-xs btn-activate-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-play-circle"></i> <?php echo lang('loan_beginning_balance_activate'); ?>
                        </a>
                        <?php if ($can_void) { ?>
                        <a href="javascript:void(0);" class="btn btn-danger btn-xs btn-void-balance" data-id="<?php echo encode_id($balance->id); ?>" data-member="<?php echo htmlspecialchars($balance->member_id, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-undo"></i> Void
                        </a>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($can_print_journal) { ?>
                        <a class="btn btn-default btn-xs" target="_blank" href="<?php echo site_url(current_lang() . '/loan/loan_beginning_balance_journal_print/' . encode_id($balance->id)); ?>" title="<?php echo htmlspecialchars(lang('loan_beginning_balance_journal'), ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-print"></i> <?php echo lang('loan_print_beginning_balance_journal'); ?>
                        </a>
                    <?php } ?>
                </div>
            </td>
        </tr>
    <?php }
} else { ?>
    <tr>
        <td colspan="17">
            <div class="empty-state">
                <i class="fa fa-list"></i>
                <?php echo lang('data_not_found'); ?>
            </div>
        </td>
    </tr>
<?php } ?>
