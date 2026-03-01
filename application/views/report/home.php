<style type="text/css">

    .panel-heading {
        padding: 5px 0px 5px 15px;
    }
    .panel-body {
        padding: 0px;
    }

    div.inside_content{
        display: block;
    }


    div.inside_content a{
        display: block;
        border-bottom: 1px solid #ccc;
        line-height: 30px;
        text-indent: 20px;
        font-size: 13px;
        font-family: "open sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    div.inside_content a:last-child{
        border-bottom: 0px;  
    }
</style>


<!-- ============================================================================ -->
<div class="row">
    <div class="col-xs-6 ">
        <!-- ==================General Ledger===============================   -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>General Ledger & Financial Statement</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/5'); ?>">Balance Sheet</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/4'); ?>">Income Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report/cash_flow_report'); ?>">Cash Flow Report</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Trial Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/2'); ?>">General Ledger Summary</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/1'); ?>">General Ledger Transactions</a>
                    <?php if (function_exists('has_role') && has_role(6, 'View_AR')) { ?>
                    <a href="<?php echo site_url(current_lang().'/ar/ar_balances'); ?>">AR Balances</a>
                    <a href="<?php echo site_url(current_lang().'/ar/ar_ledger'); ?>">AR Ledger</a>
                    <a href="<?php echo site_url(current_lang().'/ar/ar_aging'); ?>">AR Aging Report</a>
                    <?php } ?>
                </div>

            </div>
        </div>
        
        <!-- ===========================Member Reports=============================== -->
        
          <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Members</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report_member/member_report_title/1'); ?>">Member List</a>
                    <a href="<?php echo site_url(current_lang().'/report_member/member_profile/'); ?>">Member Profile</a>
                    <a href="<?php echo site_url(current_lang().'/report_member/member_report_title/2'); ?>">Registration Fee Collection</a>
                    
                </div>

            </div>
        </div>
                <!-- ===================== Mortuary Report===================================== -->
 <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Members Mortuary Fund</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report_mortuary/contribution_report/1'); ?>">Mortuary Fund Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report_mortuary/contribution_report/2'); ?>">Member Mortuary Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report_mortuary/contribution_report/3'); ?>">Mortuary Transactions</a>
                    <a href="<?php echo site_url(current_lang().'/report_mortuary/contribution_report/4'); ?>">Mortuary Transactions Summary</a>
                    
                </div>

            </div>
        </div>


                <!-- ===================== Contributions===================================== -->
 <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Capital Build Up</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report_contribution/contribution_report/1'); ?>">CBU Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report_contribution/contribution_report/2'); ?>">Member CBU Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report_contribution/contribution_report/3'); ?>">CBU Transactions</a>
                    <a href="<?php echo site_url(current_lang().'/report_contribution/contribution_report/4'); ?>">CBU Transactions Summary</a>
                    
                </div>

            </div>
        </div>
                
                                <!-- ===================== Contributions===================================== -->
 <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Loans</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/6'); ?>">Loan Processing Fee Collection</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/1'); ?>">Loan List</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/7'); ?>">Loan Aging Report</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/repayment_schedule'); ?>">Loan Repayment Schedule</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_statement'); ?>">Loan Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/3'); ?>">Interest & Penalty</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/2'); ?>">Loan Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/4'); ?>">Loan Transaction</a>
                    <a href="<?php echo site_url(current_lang().'/report_loan/loan_report/5'); ?>">Loan Transaction Summary</a>
                    
                    
                </div>

            </div>
        </div>
                
                
                
        
    </div>
    
    
    
    
    <!-- =================================================RIGHT SIDE -============================ -->
    
    <div class="col-xs-5 col-xs-offset-1">
        
        <!-- =======================Journal Transactions============================== -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Journals Transactions</h4>
            </div>
            <div class="panel-body">
                <?php $journal = $this->db->get('journal')->result(); ?>
                <div class="inside_content">
                    <?php foreach ($journal as $key => $value) { ?>
                        <a href="<?php echo site_url(current_lang().'/report/journal_entry/'.$value->id); ?>"><?php echo $value->type; ?></a>
                  <?php  } ?>
                </div>
            </div>
        </div>
        
        <!-- ========================Savings Account=============================== -->
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Savings Accounts</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report_saving/saving_account_report/1'); ?>">Saving Account List</a>
                    <a href="<?php echo site_url(current_lang().'/report_saving/saving_account_report/2'); ?>">Saving Account Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report_saving/saving_account_report/3'); ?>">Saving Transactions</a>
                    <a href="<?php echo site_url(current_lang().'/report_saving/saving_account_report/4'); ?>">Saving Transactions Summary</a>
                    
                </div>

            </div>
        </div>
        
         <!-- ========================Share Reports=============================== -->
        
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Shares</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report_share/share_report/1'); ?>">Shares Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report_share/share_report/2'); ?>">Member Shares Statement</a>
                    <a  href="<?php echo site_url(current_lang().'/report_share/share_report/3'); ?>">Shares Transactions</a>
                    <a href="<?php echo site_url(current_lang().'/report_share/share_report/4'); ?>">Shares Transactions Summary</a>
                    
                </div>

            </div>
        </div>
        
        
        
        
        
    </div>
</div>