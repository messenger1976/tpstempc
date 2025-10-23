<script type="text/javascript" src="<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js" ></script>
<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>

<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/share/share_transaction_search", 'class="form-horizontal"'); ?>

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

$sp = $jxy;
$_GET['from'] = format_date($jxy['from'],FALSE);
$_GET['upto'] = format_date($jxy['upto'],FALSE);


?>

<div class="form-group col-lg-12">

    <div class="col-lg-4">
        <input type="text" class="form-control" name="key" id="accountno" placeholder="<?php echo lang('member_member_id').'/'.  lang('customer_name'); ?>" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-3">
        <input type="text" class="form-control" id="from" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="from" value="<?php echo (isset($_GET['from']) ? $_GET['from'] : ''); ?>"/> 
    </div>
    <div class="col-lg-3">
        <input type="text" class="form-control" id="upto" data-date-format="DD-MM-YYYY" placeholder="<?php echo lang('hint_date'); ?>" name="upto" value="<?php echo (isset($_GET['upto']) ? $_GET['upto'] : ''); ?>"/> 
    </div>
    <div class="col-lg-1">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>


<?php echo form_close(); ?>
</div>
<div class="table-responsive" style="overflow: auto;">
    <table class="table table-bordered table-striped" style="width: 1200px;">
        <thead>
           
            <tr>
                <th><?php echo lang('sno'); ?></th>
                <th><?php echo lang('index_receipt'); ?></th>
                <th><?php echo lang('member_pid'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('index_name'); ?></th>
                <th><?php echo lang('index_transtype'); ?></th>
                <th><?php echo lang('index_transmethod'); ?></th>
                <th><?php echo lang('index_chequeno'); ?></th>
                <th><?php echo lang('index_amount'); ?></th>
                <th><?php echo lang('index_trans_date'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php 
           $index = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
           $index++;
            foreach ($transactionlist as $key => $value) {
                
                ?>

                <tr>
                    
                    <td><?php echo $index++; ?></td>
                    <td><?php echo $value->receipt; ?></td>
                    <td><?php echo $value->PID; ?></td>
                    <td><?php echo $value->member_id; ?></td>
                    <td><?php
                    $account_name = $this->member_model->member_basic_info(null,$value->PID,$value->member_id)->row();
                    echo $account_name->firstname.' '.$account_name->middlename.' '.$account_name->lastname;; ?></td>
                    <td><?php echo $value->trans_type; ?></td>
                    <td><?php echo $value->paymethod; ?></td>
                    <td><?php echo $value->cheque_num; ?></td>
                    <td style="text-align: right;"><?php echo number_format($value->amount,2); ?></td>
                    <td><?php echo $value->createdon; ?></td>
                    

                    <td><?php echo anchor(current_lang() . "/share/receipt_view/" . $value->receipt, ' <i class="fa fa-edit"></i> ' . lang('view_link')); ?></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>
<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
            matchContains:true
        });
        });
        
    $(function () {
        $('#from').datetimepicker({
            pickTime: false
        });
        $('#upto').datetimepicker({
            pickTime: false
        });
    });
</script>
