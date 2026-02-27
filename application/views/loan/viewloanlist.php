<?php echo form_open(current_lang() . "/loan/loan_viewlist", 'class="form-horizontal"'); ?>

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

    <div class="col-lg-3">
        <input type="text" class="form-control" name="key" value="<?php echo (isset($_GET['key']) ? htmlspecialchars($_GET['key']) : (isset($_POST['key']) ? htmlspecialchars($_POST['key']) : '')); ?>"/> 
    </div>
    <div class="col-lg-3">
        <select name="status_filter" class="form-control">
            <?php 
            $status_list = isset($status_list) ? $status_list : array();
            $current_status = isset($status_filter) ? $status_filter : '';
            foreach ($status_list as $code => $label) { 
                $sel = ($current_status !== null && $current_status !== '' && (string)$code === (string)$current_status) ? ' selected="selected"' : '';
            ?>
                <option value="<?php echo htmlspecialchars($code); ?>"<?php echo $sel; ?>><?php echo htmlspecialchars($label); ?></option>
            <?php } ?>
        </select>
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
                <th><?php echo lang('loan_LID'); ?></th>
                <th><?php echo lang('member_name'); ?></th>
                <th><?php echo lang('loan_applied_amount'); ?></th>
                <th><?php echo lang('loan_installment'); ?></th>
                <th><?php echo lang('loan_installment_amount'); ?></th>
                <th><?php echo lang('loan_total_interest'); ?></th>
                <th><?php echo lang('loan_total'); ?></th>
                <th><?php echo lang('loan_status'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>

            </tr>

        </thead>
        <tbody>
            <?php if (count($loan_list) > 0) {
                foreach ($loan_list as $key => $value) {
                    ?>
            <tr>
                <td><?php echo $value->LID;?></td>
                <td><?php 
                $info = $this->member_model->member_basic_info(null,$value->PID)->row();
                echo $info->member_id.' : '.$info->firstname.' '.$info->middlename.' '.$info->lastname; ?></td>
                <td style="text-align: right;"><?php echo number_format($value->basic_amount,2) ?></td>
                <td style="text-align: center;"><?php 
                $interval = $this->setting_model->intervalinfo($value->interval)->row();
                echo $value->number_istallment.' '.$interval->description; ?></td>
                <td style="text-align: right;"><?php echo number_format($value->installment_amount,2) ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_interest_amount,2) ?></td>
                <td style="text-align: right;"><?php echo number_format($value->total_loan,2) ?></td>
                <td ><?php echo $value->name; ?></td>
                 <td><?php echo anchor(current_lang() . "/loan/view_indetail/" . encode_id($value->LID), ' <i class="fa fa-folder-open"></i> ' . lang('loan_view_detail'));
                 if($value->edit == 0){
                     echo anchor(current_lang().'/loan/loan_editing/'.encode_id($value->LID),' |  <i class="fa fa-edit"></i> ' . lang('button_edit'));
                 }
                 if (isset($value->status) && (string)$value->status === '4') {
                     $schedule_url = site_url(current_lang() . '/loan/view_repayment_schedule_popup/' . encode_id($value->LID));
                     echo ' | <a href="' . htmlspecialchars($schedule_url) . '" class="repayment-schedule-popup" data-schedule-url="' . htmlspecialchars($schedule_url) . '" title="' . htmlspecialchars(lang('loan_view_repayment_schedule')) . '"><i class="fa fa-calendar-check-o"></i> ' . lang('loan_view_repayment_schedule') . '</a>';
                     if (!empty($value->disburse)) {
                         $print_disburse_url = site_url(current_lang() . '/loan/loan_disbursement_print/' . encode_id($value->LID));
                         echo ' | <a href="' . htmlspecialchars($print_disburse_url) . '" target="_blank" title="' . htmlspecialchars(lang('loan_print_disbursement')) . '"><i class="fa fa-print"></i> ' . lang('loan_print_disbursement') . '</a>';
                     }
                 }
                 ?></td>
            </tr>
                <?php }
                ?>

<?php } ?>
        </tbody>

    </table>
       <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>

<!-- Repayment Schedule Modal -->
<div class="modal fade" id="repaymentScheduleModal" tabindex="-1" role="dialog" aria-labelledby="repaymentScheduleModalLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%; max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="repaymentScheduleModalLabel"><?php echo lang('loan_view_repayment_schedule'); ?></h4>
            </div>
            <div class="modal-body" style="padding: 0; min-height: 400px;">
                <iframe id="repaymentScheduleFrame" style="width: 100%; height: 70vh; min-height: 400px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function findPopupLink(el) {
        while (el) {
            if (el.nodeName === 'A' && el.className && el.className.indexOf('repayment-schedule-popup') !== -1) return el;
            el = el.parentNode;
        }
        return null;
    }
    document.addEventListener('click', function(e) {
        var link = (e.target && e.target.closest) ? e.target.closest('a.repayment-schedule-popup') : findPopupLink(e.target);
        if (!link) return;
        e.preventDefault();
        var url = link.getAttribute('data-schedule-url') || link.getAttribute('href');
        if (!url) return;
        var frame = document.getElementById('repaymentScheduleFrame');
        var modal = document.getElementById('repaymentScheduleModal');
        if (frame) frame.src = url;
        if (modal && typeof jQuery !== 'undefined' && jQuery(modal).modal) {
            jQuery(modal).modal('show');
        } else {
            window.open(url, 'repayment_schedule_win', 'width=950,height=700,scrollbars=yes,resizable=yes');
        }
    });
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            jQuery('#repaymentScheduleModal').on('hidden.bs.modal', function() {
                var frame = document.getElementById('repaymentScheduleFrame');
                if (frame) frame.src = 'about:blank';
            });
        });
    }
})();
</script>