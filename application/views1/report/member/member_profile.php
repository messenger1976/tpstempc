<link href="<?php echo base_url(); ?>media/css/choosen/chosen.css" rel="stylesheet">

<style type="text/css">

    .chosen-container-single .chosen-single{
        height: 34px !important; 
        padding-top: 8px;
    }


    div#searchboxdata{
        border-bottom:  1px solid #000; 
    }
    div.ibox-content{
        min-height: 300px;
    }
    .bgm{
        background-color: #f3f3f4;
    }

</style>
<?php if (!$this->ion_auth->in_group('Members')) { ?>
<div id="searchboxdata">
    <?php echo form_open_multipart(current_lang() . "/report_member/member_profile", 'class="form-horizontal"'); ?>

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

    <div class="form-group"><label class="col-lg-4 control-label"><?php echo lang('member_select_member'); ?>  : <span class="required">*</span></label>
        <div class="col-lg-7">
            <div class="input-group" >
                <select name="member_id" class="form-control" id="member_id">
                    <option value=""> <?php echo lang('select_default_text'); ?></option>
                    <?php
                    $selected = $member_id;
                    foreach ($memberlist as $key => $value) {
                        ?>
                        <option <?php echo ($selected ? ($selected == $value->member_id ? 'selected="selected"' : '') : ''); ?> value="<?php echo $value->member_id; ?>"> <?php echo $value->member_id . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname; ?></option>
                    <?php }
                    ?>
                </select>
                <span class="input-group-btn">
                    <input type="submit" value="Load" name="Load" class="btn btn-primary" />
                </span>
            </div>
            <?php echo form_error('member_id'); ?>
        </div>
    </div>

    <?php echo form_close(); ?>
</div>


<?php
}
if ($member_id != '') {
    $this->data['memberinfo'] = $memberinfo;
    $this->data['contactinfo'] = $contactinfo;
    $this->data['nextkininfo'] = $nextkininfo;

    $this->load->view('report/member/member_profile_content',$this->data);
}
?>



<script src="<?php echo base_url() ?>media/js/chosen.jquery.js"></script>
<script type="text/javascript">

    $(function() {

        var config = {
            no_results_text: 'Oops, nothing found!'
        }
        $("#member_id").chosen(config);
    });

</script>
