<style type="text/css">
    .cat_outer{
        display: block;
        font-weight: bold;
        padding-top: 10px;
    }
    div.cat_outer a{
        float: right;
    }
</style>
<?php
if (isset($message) && !empty($message)) {
    echo '<div class="label label-info displaymessage">' . $message . '</div>';
} else if ($this->session->flashdata('message') != '') {
    echo '<div class="label label-info displaymessage">' . $this->session->flashdata('message') . '</div>';
} else if (isset($warning) && !empty($warning)) {
    echo '<div class="label label-danger displaymessage">' . $warning . '</div>';
} else if ($this->session->flashdata('warning') != '') {
    echo '<div class="label label-danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
}
$asset = $this->db->get_where('balance_sheet_group',array('account_type'=>1))->result();
?>
<div class="row">
    
    
    
    <div class="col-xs-5 ">
        <!-- ==================Assets===============================   -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 style="display: inline-block;">Assets</h4> <a href="<?php echo site_url(current_lang().'/setting/balance_sheet_addgroup/1/'); ?>" style="display: inline-block; float: right;" class="btn btn-primary">Add Group</a>
               
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <table style="width: 100%;">
                    <?php
                    foreach ($asset as $key => $value) { ?>
                        <tr><td style="width: 20%;"><a><span class="fa fa-edit">Edit</span></a></td><td>
                    <div class="cat_outer"><?php echo $value->name; ?><a class="ryt" href="<?php echo site_url(current_lang().'/setting/balance_sheet_addaccount/'.$value->id); ?>">Add Account</a></div>
                    </td></tr>
                  <?php  }  ?>
                    
                    </table>
                </div>

            </div>
        </div>
        
        
         <!-- ==================Assets===============================   -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Liabilities</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/5'); ?>">Balance Sheet</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Income Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Trial Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/2'); ?>">General Ledger Summary</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/1'); ?>">General Ledger Transactions</a>
                </div>

            </div>
        </div>
         
          <!-- ==================Assets===============================   -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Equity</h4>
            </div>
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/5'); ?>">Balance Sheet</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Income Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Trial Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/2'); ?>">General Ledger Summary</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/1'); ?>">General Ledger Transactions</a>
                </div>

            </div>
        </div>
        
    </div>
    
    
        <div class="col-xs-7 ">
        <!-- ==================Template===============================   -->
        <div class="panel panel-default">
           
            <div class="panel-body">
                <div class="inside_content">
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/5'); ?>">Balance Sheet</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Income Statement</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/3'); ?>">Trial Balance</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/2'); ?>">General Ledger Summary</a>
                    <a href="<?php echo site_url(current_lang().'/report/general_leger_transaction/1'); ?>">General Ledger Transactions</a>
                </div>

            </div>
        </div>
        
    </div>
</div>