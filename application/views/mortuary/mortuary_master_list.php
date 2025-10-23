<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<?php echo form_open(current_lang() . "/mortuary/mortuary_master_list", 'class="form-horizontal"'); ?>

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
$_GET['searchstatus'] = $jxy['searchstatus'];
$_GET['key'] = $jxy['key'];
?>

<div class="form-group col-lg-12">

    <div class="col-lg-4">
        <input type="text" class="form-control" id="accountno" name="key" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
           
        <select name="searchstatus" class="form-control">
            <option value=""><?php echo lang('select_default_text'); ?></option>
            <?php
            $selected = (isset ($_GET['searchstatus'] ) ? $_GET['searchstatus']  : '');
            foreach ($mortuary_status_list as $key => $value) {
                ?>
                <option <?php echo ($value->id == $selected ? 'selected="selected"' : ''); ?> value="<?php echo $value->id ?>"><?php echo $value->description ; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>
    <div class="col-lg-4" style="text-align: right;">
        <?php echo anchor(current_lang().'/mortuary/mortuary_setting_create/',  lang('mortuary_setting_create'),'class="btn btn-primary"'); ?>
        <?php echo anchor('#',  'Process Balances','class="btn btn-warning" id="btnprocessbalances"'); ?>
    </div>
</div>


<?php echo form_close(); ?>


<div class="table-responsive">
    
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <!--<th style="width: 10px;"><?php echo lang('member_pid'); ?></th>-->
                <th style="width: 10px;"><?php echo lang('member_member_id'); ?></th>
                <th style="max-width: 40px;"><?php echo 'Pix'; ?></th>
                <th><?php echo lang('mortuary_member_name'); ?></th>
                <th><?php echo lang('member_gender'); ?></th>
                <th style="width: 20px;"><?php echo lang('member_dob'); ?></th>
                <th style="text-align: center;"><?php echo 'Age'; ?></th>
                <th style="text-align: right; width: 100px;"><?php echo lang('mortuary_balance_amount'); ?></th>
                <th style="text-align: right;  width: 100px;"><?php echo lang('mortuary_deduction_amount'); ?></th>
                <th style="text-align: center;"><?php echo lang('mortuary_status'); ?></th>
                <th style="text-align: center;"><?php echo lang('claim_mortuary_status'); ?></th>
                
                <th style="text-align: center;"><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($mortuary_setting as $key => $value) { ?>

                <tr>
                    <!--<td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>-->
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><img src="<?php echo htmlspecialchars(base_url().'uploads/memberphoto/'.get_photo($value->PIN, $value->PID, $value->member_id)->photo, ENT_QUOTES, 'UTF-8'); ?>" width="40"/></td>
                    
                    <td><?php
                    $memberinfo = $this->member_model->member_basic_info(null,$value->PID,$value->member_id)->row();
                    $dob = new DateTime($memberinfo->dob);
                    $today   = new DateTime('today');
                    $year = $dob->diff($today)->y;
                    //echo $year;
                    echo '<b>'.$memberinfo->lastname.'</b>, '.$memberinfo->firstname.' '.$memberinfo->middlename; ?></td>
                    <td><?php echo ($memberinfo->gender=='F'?'Female':'Male'); ?></td>
                    <td><?php echo date('m/d/Y',strtotime($memberinfo->dob)); ?></td>
                    <td style="text-align: center;"><?php echo $year; ?></td>
                    
                    <td style="text-align: right;"><?php echo number_format($this->mortuary_model->mortuary_balance($value->PID,$value->member_id)->balance,2,'.',','); ?></td>
                    <td style="text-align: right;"><?php echo number_format($deduction_amount*$year,2,'.',','); ?></td>
                    <?php $mortstat = ($value->status_flag==1)?'class="btn btn-info btn-xs"': ($value->status_flag==2?'class="btn btn-warning btn-xs"':'class="btn btn-danger btn-xs"');?>
                    <td style="text-align: center;"><span <?php echo $mortstat;?>><?php echo $this->mortuary_model->mortuary_status($value->status_flag)->row()->description; ?></span></td>
                    <?php
                    foreach ($mortuary_type_claim_array as $key => $value1) {
                        if($key==$value->claim_status){
                            $mortuary_type_claim = $value1;
                        }
                    }
                    ?>
                    <td style="text-align: center;"><?php echo $mortuary_type_claim;?></td>
                    <td style="text-align: center;"><?php echo anchor(current_lang() . "/mortuary/mortuary_setting_edit/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'),' class="btn btn-success btn-xs btn-outline"'); ?></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>

    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 


</div>
<script type="text/javascript" src="<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js" ></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>",{
            matchContains:true
        });
    });
    $(function(){
        $('#btnprocessbalances').on('click', function(e){
            e.preventDefault();
            $('#ibox-main').children('.ibox-content').addClass('sk-loading');
            $("body").css("cursor", "wait");
            recomputebalances();
        })
    })   
    async function recomputebalances() {
        let response = await fetch('<?php echo site_url(current_lang() . '/mortuary/recomputebalances/'); ?>');
		let totalrecdata = await response.json();
		success = totalrecdata.success;
        message = totalrecdata.message;
        await new Promise((resolve, reject) => setTimeout(resolve, 1000));
        $('#ibox-main').children('.ibox-content').removeClass('sk-loading');
        $("body").css("cursor", "default");
        swal({
            title: "Good job!",
            text: "All mortuary balances are successfully process!",
            icon: "success",
            button: "Close",
        });
        return true;
    } 
</script>
