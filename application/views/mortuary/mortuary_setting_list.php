<!-- Sweet Alert -->
<link href="<?php echo base_url(); ?>media/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<!-- Sweet alert -->
<script src="<?php echo base_url(); ?>media/js/plugins/sweetalert/sweetalert.min.js"></script>

<?php echo form_open(current_lang() . "/mortuary/mortuary_account_list", 'class="form-horizontal"'); ?>

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

<div class="form-group col-lg-10">

    <div class="col-lg-5">
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

</div>


<?php echo form_close(); ?>


<div class="table-responsive">
    <div style="width: 90%; margin: auto; text-align: right;">
    <?php echo anchor(current_lang().'/mortuary/mortuary_setting_create/',  lang('mortuary_setting_create'),'class="btn btn-primary"'); ?>
</div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width: 10px;"><?php echo lang('member_pid'); ?></th>
                <th style="width: 10px;"><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('mortuary_member_name'); ?></th>
                <th><?php echo lang('member_gender'); ?></th>
                <th><?php echo lang('member_dob'); ?></th>
                <th style="text-align: center;"><?php echo 'Age'; ?></th>
                <th><?php echo lang('mortuary_source'); ?></th>
                <th style="text-align: right;"><?php echo lang('mortuary_amount'); ?></th>
                <th style="text-align: center;"><?php echo lang('mortuary_status'); ?></th>
                <th style="text-align: center;"><?php echo lang('mortuary_posted_status'); ?></th>
                <th style="text-align: center;"><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($mortuary_setting as $key => $value) { ?>

                <tr>
                    <td><span><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    
                    <td><?php
                    $memberinfo = $this->member_model->member_basic_info(null,$value->PID,$value->member_id)->row();
                    $dob = new DateTime($memberinfo->dob);
                    $today   = new DateTime('today');
                    $year = $dob->diff($today)->y;
                    //echo $year;
                    echo $memberinfo->firstname.' '.$memberinfo->middlename.' '.$memberinfo->lastname; ?></td>
                    <td><?php echo ($memberinfo->gender=='F'?'Female':'Male'); ?></td>
                    <td><?php echo $memberinfo->dob; ?></td>
                    <td style="text-align: center;"><?php echo $year; ?></td>
                    <td><?php echo $this->mortuary_model->contribution_source($value->contribute_source)->row()->name; ?></td>
                    
                    <td style="text-align: right;"><?php echo number_format($value->amount,2,'.',','); ?></td>
                    <?php $mortstat = ($value->status_flag==1)?'class="btn btn-info btn-xs"': ($value->status_flag==2?'class="btn btn-warning btn-xs"':'class="btn btn-danger btn-xs"');?>
                    <td style="text-align: center;"><span <?php echo $mortstat;?>><?php echo $this->mortuary_model->mortuary_status($value->status_flag)->row()->description; ?></span></td>
                    <td style="text-align: center;"><a class="swtalert" id="posted<?php echo $value->id;?>" data-id="<?php echo $value->id; ?>" data-value="<?php echo $value->posted; ?>" data-pid="<?php echo $value->PID;?>" data-memberid="<?php echo trim($value->member_id);?>"><?php echo $value->posted?'Yes':'No'; ?></a></td>
                    <td style="text-align: center;"><?php echo anchor(current_lang() . "/mortuary/mortuary_setting_create/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'),' class="btn btn-success btn-xs btn-outline"'); ?>
                    <?php echo anchor('#', ' <i class="fa fa-edit"></i> Ledger',' class="btn btn-success btn-xs btn-outline"'); ?>
                    </td>
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

        $('.swtalert').click(function(e){
            e.preventDefault();
            id = $(this).data('id');
            postedvalue = $(this).data('value');
            textvalue = $(this).html();
            if(postedvalue==1) return true;

            swal({
                title: "GL Posting ",
                text: "Are you sure to post this to GL?",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, post it!',
                closeOnConfirm: false,
                closeOnCancel: false,
            },
            function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: '<?php echo site_url(current_lang() . '/mortuary/mortuary_post_to_gl/'); ?>',
                        type: 'POST',
                        data:{
                            id: id,
                            posted : postedvalue
                        },
                        success:function(data){
                            var json = JSON.parse(data);
                            if(json['posted'].toString() == '1'){
                                $('#posted'+id).html('Yes');
                            }else{
                                $('#posted'+id).html('No');
                            }
                            //$('#posted'+id).data('id',json['posted'].toString());
                            $('#posted'+id).data('value',json['posted'].toString());
                            swal('Posted!', json['message'],'success');
                        }
                    });
                    
                } else {
                    swal("Cancelled", "Your post to gl is cancelled", "error");
                }
            });
            
            
        });
    });
        
</script>
