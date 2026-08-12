<?php
$lang = current_lang();
$can_view_ar = function_exists('has_role') && has_role(6, 'View_AR');
$journals = $this->db->get('journal')->result();
?>

<style type="text/css">
.report-home-page { margin-top: 4px; }
.report-home-page .page-intro {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.report-home-page .page-intro .icon-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.report-home-page .page-intro h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #2f4050;
}
.report-home-page .page-intro p {
    margin: 2px 0 0;
    font-size: 13px;
    color: #888;
}
.report-home-page .report-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: hidden;
}
.report-home-page .report-panel .panel-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.report-home-page .report-panel .panel-head .icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.report-home-page .report-panel .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.report-home-page .report-links {
    list-style: none;
    margin: 0;
    padding: 6px 0;
}
.report-home-page .report-links li { margin: 0; }
.report-home-page .report-links a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    color: #2f4050;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    border-bottom: 1px solid #f0f2f3;
    transition: background .15s ease, color .15s ease;
}
.report-home-page .report-links li:last-child a {
    border-bottom: 0;
}
.report-home-page .report-links a:hover {
    background: #e8f8f5;
    color: #1ab394;
}
.report-home-page .report-links a i.fa-angle-right {
    margin-left: auto;
    color: #c5c9ce;
    font-size: 12px;
}
.report-home-page .report-links a:hover i.fa-angle-right {
    color: #1ab394;
}
.report-home-page .report-links a .link-icon {
    width: 22px;
    text-align: center;
    color: #1ab394;
    opacity: .75;
}
@media (max-width: 991px) {
    .report-home-page .report-col-right {
        margin-top: 0;
    }
}
</style>

<div class="col-lg-12 report-home-page">
    <div class="page-intro">
        <i class="fa fa-bar-chart icon-badge"></i>
        <div>
            <h3><?php echo lang('page_report'); ?></h3>
            <p>Choose a category, then open a report to set dates and filters.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-book icon-badge"></i>
                    <h4>General Ledger &amp; Financial Statement</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/5'); ?>"><i class="fa fa-file-text-o link-icon"></i> Balance Sheet <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/4'); ?>"><i class="fa fa-line-chart link-icon"></i> Income Statement <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/cash_flow_report'); ?>"><i class="fa fa-exchange link-icon"></i> Cash Flow Report <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/3'); ?>"><i class="fa fa-balance-scale link-icon"></i> Trial Balance <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/2'); ?>"><i class="fa fa-list-alt link-icon"></i> General Ledger Summary <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/1'); ?>"><i class="fa fa-list link-icon"></i> General Ledger Transactions <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/7'); ?>"><i class="fa fa-sitemap link-icon"></i> Consolidated Statement Of Financial Condition <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report/general_leger_transaction/8'); ?>"><i class="fa fa-bank link-icon"></i> Comparative Statement of Financial Operations - Lending <i class="fa fa-angle-right"></i></a></li>
                    <?php if ($can_view_ar) { ?>
                        <li><a href="<?php echo site_url($lang . '/ar/ar_balances'); ?>"><i class="fa fa-users link-icon"></i> AR Balances <i class="fa fa-angle-right"></i></a></li>
                        <li><a href="<?php echo site_url($lang . '/ar/ar_ledger'); ?>"><i class="fa fa-book link-icon"></i> AR Ledger <i class="fa fa-angle-right"></i></a></li>
                        <li><a href="<?php echo site_url($lang . '/ar/ar_aging'); ?>"><i class="fa fa-clock-o link-icon"></i> AR Aging Report <i class="fa fa-angle-right"></i></a></li>
                    <?php } ?>
                </ul>
            </div>

            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-user icon-badge"></i>
                    <h4>Members</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report_member/member_report_title/1'); ?>"><i class="fa fa-list link-icon"></i> Member List <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_member/member_profile/'); ?>"><i class="fa fa-id-card-o link-icon"></i> Member Profile <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_member/member_report_title/2'); ?>"><i class="fa fa-money link-icon"></i> Registration Fee Collection <i class="fa fa-angle-right"></i></a></li>
                </ul>
            </div>

            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-heart icon-badge"></i>
                    <h4>Members Mortuary Fund</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report_mortuary/contribution_report/1'); ?>"><i class="fa fa-pie-chart link-icon"></i> Mortuary Fund Balance <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_mortuary/contribution_report/2'); ?>"><i class="fa fa-file-text-o link-icon"></i> Member Mortuary Statement <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_mortuary/contribution_report/3'); ?>"><i class="fa fa-list link-icon"></i> Mortuary Transactions <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_mortuary/contribution_report/4'); ?>"><i class="fa fa-list-alt link-icon"></i> Mortuary Transactions Summary <i class="fa fa-angle-right"></i></a></li>
                </ul>
            </div>

            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-university icon-badge"></i>
                    <h4>Capital Build Up</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report_contribution/contribution_report/1'); ?>"><i class="fa fa-pie-chart link-icon"></i> CBU Balance <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_contribution/contribution_report/2'); ?>"><i class="fa fa-file-text-o link-icon"></i> Member CBU Statement <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_contribution/contribution_report/3'); ?>"><i class="fa fa-list link-icon"></i> CBU Transactions <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_contribution/contribution_report/4'); ?>"><i class="fa fa-list-alt link-icon"></i> CBU Transactions Summary <i class="fa fa-angle-right"></i></a></li>
                </ul>
            </div>

            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-handshake-o icon-badge"></i>
                    <h4>Loans</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/6'); ?>"><i class="fa fa-money link-icon"></i> Loan Processing Fee Collection <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/1'); ?>"><i class="fa fa-list link-icon"></i> Loan List <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/7'); ?>"><i class="fa fa-clock-o link-icon"></i> Loan Aging Report <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/repayment_schedule'); ?>"><i class="fa fa-calendar link-icon"></i> Loan Repayment Schedule <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_statement'); ?>"><i class="fa fa-file-text-o link-icon"></i> Loan Statement <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/3'); ?>"><i class="fa fa-percent link-icon"></i> Interest &amp; Penalty <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/2'); ?>"><i class="fa fa-pie-chart link-icon"></i> Loan Balance <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/4'); ?>"><i class="fa fa-list link-icon"></i> Loan Transaction <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_loan/loan_report/5'); ?>"><i class="fa fa-list-alt link-icon"></i> Loan Transaction Summary <i class="fa fa-angle-right"></i></a></li>
                </ul>
            </div>
        </div>

        <div class="col-md-6 report-col-right">
            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-pencil-square-o icon-badge"></i>
                    <h4>Journals Transactions</h4>
                </div>
                <ul class="report-links">
                    <?php if (!empty($journals)) { ?>
                        <?php foreach ($journals as $value) {
                            $label = function_exists('journal_display_type') ? journal_display_type($value->type) : $value->type;
                            ?>
                            <li>
                                <a href="<?php echo site_url($lang . '/report/journal_entry/' . $value->id); ?>">
                                    <i class="fa fa-book link-icon"></i>
                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        <?php } ?>
                    <?php } else { ?>
                        <li><a href="javascript:void(0)" style="color:#999;cursor:default;"><i class="fa fa-info-circle link-icon"></i> No journal types found</a></li>
                    <?php } ?>
                </ul>
            </div>

            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-money icon-badge"></i>
                    <h4>Savings Accounts</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report_saving/saving_account_report/1'); ?>"><i class="fa fa-list link-icon"></i> Saving Account List <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_saving/saving_account_report/2'); ?>"><i class="fa fa-file-text-o link-icon"></i> Saving Account Statement <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_saving/saving_account_report/3'); ?>"><i class="fa fa-list link-icon"></i> Saving Transactions <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_saving/saving_account_report/4'); ?>"><i class="fa fa-list-alt link-icon"></i> Saving Transactions Summary <i class="fa fa-angle-right"></i></a></li>
                </ul>
            </div>

            <div class="report-panel">
                <div class="panel-head">
                    <i class="fa fa-share-alt icon-badge"></i>
                    <h4>Shares</h4>
                </div>
                <ul class="report-links">
                    <li><a href="<?php echo site_url($lang . '/report_share/share_report/1'); ?>"><i class="fa fa-pie-chart link-icon"></i> Shares Balance <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_share/share_report/2'); ?>"><i class="fa fa-file-text-o link-icon"></i> Member Shares Statement <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_share/share_report/3'); ?>"><i class="fa fa-list link-icon"></i> Shares Transactions <i class="fa fa-angle-right"></i></a></li>
                    <li><a href="<?php echo site_url($lang . '/report_share/share_report/4'); ?>"><i class="fa fa-list-alt link-icon"></i> Shares Transactions Summary <i class="fa fa-angle-right"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
