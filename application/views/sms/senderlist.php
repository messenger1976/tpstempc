<div class="table-responsive" style="overflow: auto;">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/sms/create_senderid/'); ?>"><?php echo 'New Sender ID'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
           
            <tr>
                <th style="width: 50px;"><?php echo lang('sno'); ?></th>
                <th>Sender ID</th>
                <th style="width: 100px;"><?php echo lang('index_action_th'); ?></th>
            </tr>

        </thead>
        <tbody>
            <?php
            $i=1;
            if(count($senderlist) > 0){
                foreach ($senderlist as $key => $value) { ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo $value->name; ?></td>
                <td><a   href="<?php echo site_url(current_lang() . '/sms/create_senderid/'.$value->id); ?>"><span class="fa fa-edit">Edit</span></a></td>
            </tr>
               <?php }
            }else{
            ?>
            <tr>
                <td colspan="3">No data found !</td>
            </tr>
            <?php } ?>
        </tbody>
        
    </table>
</div>