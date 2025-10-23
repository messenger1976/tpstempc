

<div class="table-responsive">
     <div style="width: 90%; margin: auto; text-align: right;">
    <?php echo anchor(current_lang().'/setting/taxcode_registration/',  lang('taxcode_register'),'class="btn btn-primary"'); ?>
</div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('taxcode'); ?></th>
                <th><?php echo lang('taxdescription'); ?></th>
                <th><?php echo lang('taxpercent'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
                
            </tr>

        </thead>
        <tbody>
            <?php foreach ($taxlist as $key => $value) { ?>

                <tr>
                     
                    <td><?php echo $value->code; ?></td>
                    <td><?php echo $value->description; ?></td>
                    <td><?php echo $value->rate; ?></td>
                    <td><?php echo anchor(current_lang() . "/setting/taxcode_registration/" . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?></td>
                    
                    
                </tr>
            <?php } ?>
        </tbody>

    </table>
   
 
    
</div>
