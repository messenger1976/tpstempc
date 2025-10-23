
<?php echo form_open(current_lang() . "/supplier/supplier_list", 'class="form-horizontal"'); ?>

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
        <input type="text" class="form-control" name="key" value="<?php echo (isset($_GET['key']) ? $_GET['key'] : ''); ?>"/> 
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>


<?php echo form_close(); ?>


<div class="table-responsive">
      <div style="text-align: right; margin-right: 20px;">
          <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/supplier/supplier_register'); ?>"><?php echo lang('supplier_addnew'); ?></a>
      </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
               
                <th><?php echo lang('supplier_id'); ?></th>
                <th><?php echo lang('supplier_name'); ?></th>
                <th><?php echo lang('supplier_phone'); ?></th>
                <th><?php echo lang('supplier_email'); ?></th>
                 <th><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($supplier_list as $key => $value) { ?>

                <tr>
                    
                    <td><?php echo $value->supplierid; ?></td>
                    <td><?php echo $value->name; ?></td>
                    <td><?php echo $value->phone; ?></td>
                    <td><?php echo $value->email; ?></td>
           <td><?php echo anchor(current_lang() . "/supplier/supplier_register/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?></td>
                    
                    
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div> 
   
    
</div>
