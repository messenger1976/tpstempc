<!-- Sweet Alert -->
<link href="<?php echo base_url(); ?>media/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>
<!-- Sweet alert -->
<script src="<?php echo base_url(); ?>media/js/plugins/sweetalert/sweetalert.min.js"></script>

<?php echo form_open(current_lang() . "/contribution/contribute_setting", 'class="form-horizontal"'); ?>

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
?>

<div class="form-group col-lg-10">

    <div class="col-lg-5">
        <input type="text" class="form-control" id="accountno" name="key" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>


<?php echo form_close(); ?>


<div class="table-responsive">
    <div style="width: 100%; margin: auto; text-align: right;">
    <?php echo anchor(current_lang().'/contribution/contribute_setting_create/',  lang('contribution_setting_create'),'class="btn btn-primary"'); ?>
</div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('member_pid'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('contribution_member_name'); ?></th>
                <th><?php echo lang('member_gender'); ?></th>
                <th><?php echo lang('contribution_source'); ?></th>
                <th><?php echo lang('contribution_amount'); ?></th>
                <th style="text-align:center;"><?php echo lang('contribution_posted'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($contribution_setting as $key => $value) { ?>

                <tr>
                    <td style="width:100px;"><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    
                    <td><?php
                    $memberinfo = $this->member_model->member_basic_info(null,$value->PID,$value->member_id)->row();
                    echo $memberinfo->firstname.' '.$memberinfo->middlename.' '.$memberinfo->lastname; ?></td>
                    <td><?php echo $memberinfo->gender=='M'?'Male':'Female'; ?></td>
                    <td><?php echo $this->contribution_model->contribution_source($value->contribute_source)->row()->name; ?></td>
                    
                    <td style="text-align: right;"><?php echo number_format($value->amount,2); ?></td>
                    <?php 
                    $css_posted = 'color:white;';
                    $badge_success = 'badge-warning';
                    if($value->posted){
                        $css_posted = 'color:white;';
                        $badge_success = 'badge-success';
                    }
                    ?>
                    <td style="text-align: center;"><a style="<?php echo $css_posted;?>" class="swtalert badge <?php echo $badge_success;?>" id="posted<?php echo $value->id;?>" data-id="<?php echo $value->id; ?>" data-value="<?php echo $value->posted; ?>" data-pid="<?php echo $value->PID;?>" data-memberid="<?php echo trim($value->member_id);?>"><?php echo $value->posted?'Yes':'No'; ?></a></td>
                    <td><?php echo anchor(current_lang() . "/contribution/contribute_setting_create/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'),'class="label label-primary p-xxs"'); ?></td>
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
                        url: '<?php echo site_url(current_lang() . '/contribution/contribution_post_to_gl/'); ?>',
                        type: 'POST',
                        data:{
                            id: id,
                            posted : postedvalue
                        },
                        success:function(data){
                            var json = JSON.parse(data);
                            if(json['posted'].toString() == '1'){
                                $('#posted'+id).html('Yes').css({'color':'white'});
                                $('#posted'+id).removeClass('badge-warning').addClass('badge-success');
                                
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
