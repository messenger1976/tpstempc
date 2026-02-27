
<?php echo form_open(current_lang() . "/member/member_list", 'class="form-horizontal" method="GET"'); ?>

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

<div class="form-group col-lg-12">

    <div class="col-lg-5">
        <label class="col-sm-2">Search:</label>
        <div class="col-sm-10"><input type="text" class="form-control" name="key" value="<?php echo $key; ?>"/> </div>
    </div>
    
    <div class="col-lg-2">
        <label class="col-sm-3">Status:</label>
        <div class="col-sm-9">
        <select class="form-control" name="searchstatus">
            <option value="" <?php echo $searchstatus==''?'selected':'';?>>All</option>
            <option value="1" <?php echo $searchstatus=='1'?'selected':'';?>>Active</option>
            <option value="0" <?php echo $searchstatus=='0'?'selected':'';?>>In-Active</option>
        </select>    
        </div>
    </div>
    <div class="col-lg-2">
        <label class="col-sm-3">Type:</label>
        <div class="col-sm-9">
            <select class="form-control" name="searchmember">
                <option value="" <?php echo $searchmember==''?'selected':'';?>>All</option>
                <option value="1" <?php echo $searchmember=='1'?'selected':'';?>>Member</option>
                <option value="0" <?php echo $searchmember=='0'?'selected':'';?>>Non-member</option>
            </select>    
        </div>
        
    </div>
    
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>


<?php echo form_close(); ?>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <!--<th><?php echo lang('member_pid'); ?></th>-->
                <th><?php echo 'Mem ID'; ?></th>
                <th style="max-width: 40px;"><?php echo ''; ?></th>
                <th><?php echo lang('member_firstname'); ?></th>
                <th><?php echo lang('member_middlename'); ?></th>
                <th><?php echo lang('member_lastname'); ?></th>
                <th><?php echo lang('member_gender'); ?></th>
                <th><?php echo lang('member_contact_phone1'); ?></th>
                <th><?php echo lang('member_status'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($member_list as $key => $value) { ?>

                <tr>
                    <!--<td><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>-->
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><img src="<?php echo htmlspecialchars(base_url().'uploads/memberphoto/'.$value->photo, ENT_QUOTES, 'UTF-8'); ?>" width="40"/></td>
                    <td><?php echo htmlspecialchars($value->firstname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->middlename, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->lastname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->gender, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php
                        echo $this->member_model->member_contact($value->PID)->phone1;
                        ?>
                    </td>
                    
                    <td><?php echo ($value->status == 1? anchor(current_lang().'/member/deactivate/'.  encode_id($value->id),  lang('member_active')): anchor(current_lang().'/member/activate/'.  encode_id($value->id),  lang('member_inactive'))); ?></td>
                    
                    <td>
                        <?php echo anchor(current_lang() . "/member/memberinfo/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'),' class="btn btn-primary btn-xs"'); ?>
                        <?php if ($this->ion_auth->is_admin()) { ?>
                        <a href="#" class="btn btn-danger btn-xs btn-delete-member" data-id="<?php echo encode_id($value->id); ?>" data-name="<?php echo htmlspecialchars($value->firstname . ' ' . $value->lastname, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?></a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>

<script>
(function() {
    function initDeleteMembers() {
        var buttons = document.querySelectorAll('.btn-delete-member');
        var deleteBaseUrl = '<?php echo site_url(current_lang() . '/member/soft_delete_member'); ?>';
        var deleteTitle = "<?php echo addslashes(lang('button_delete')); ?>?";
        var deleteText = "<?php echo addslashes(lang('member_delete_confirm_text')); ?> ";
        var confirmText = "<?php echo addslashes(lang('yes')); ?>";
        var cancelText = "<?php echo addslashes(lang('button_cancel')); ?>";
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].addEventListener('click', function(e) {
                e.preventDefault();
                var memberId = this.getAttribute('data-id');
                var memberName = this.getAttribute('data-name');
                var deleteUrl = deleteBaseUrl + '/' + memberId;
                if (typeof swal !== 'undefined') {
                    swal({
                        title: deleteTitle,
                        text: deleteText + memberName,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = deleteUrl;
                        }
                    });
                } else {
                    if (confirm(deleteText + memberName)) {
                        window.location.href = deleteUrl;
                    }
                }
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeleteMembers);
    } else {
        initDeleteMembers();
    }
})();
</script>
