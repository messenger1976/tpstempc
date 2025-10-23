<div class="table-responsive">
    <div style="text-align: right; margin-right: 20px;">
        <a  class="btn btn-primary" href="<?php echo site_url(current_lang() . '/report_member/create_member_list_title/' . $link_cat); ?>"><?php echo 'New Report'; ?></a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th><?php echo lang('index_action_th'); ?></th>
                <th><?php echo 'From'; ?></th>
                <th><?php echo 'Until'; ?></th>
                <th><?php echo 'Description'; ?></th>
                <?php if($link_cat == 1){ ?>
                <th><?php echo 'Column'; ?></th>
                
                <?php } ?>
<th><?php echo 'Page Orientation'; ?></th>
            </tr>

        </thead>
        <tbody>
            <?php foreach ($reportlist as $key => $value) {  ?>
                <tr>
                    <td style="width: 300px;"><?php echo anchor(current_lang() . "/report_member/create_member_list_title/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit')); ?>   &nbsp; | &nbsp;
                        <?php echo anchor(current_lang() . "/report_member/delete_report_member_list/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-times"></i> ' . lang('button_delete')); ?>   &nbsp; | &nbsp; 
                        <?php
                        if ($link_cat == 1) {
                            echo anchor(current_lang() . "/report_member/member_list_view/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));
                        } elseif ($link_cat == 2) {
                            echo anchor(current_lang() . "/report_member/registration_fee_collection/" . $link_cat . '/' . encode_id($value->id), ' <i class="fa fa-eye"></i> ' . lang('button_view'));
                        } 
                        ?></td>
                    <td><?php echo format_date($value->fromdate, false); ?></td>
                    <td><?php echo format_date($value->todate, false); ?></td>
                    <td><?php echo $value->description; ?></td>
                     <?php if($link_cat == 1){ ?>
                    <td><?php if($value->viewall == 1){ echo 'All';}else{
                        $array_column = explode(',', $value->column);
                        echo ' <div style="font-weight: bold; border-bottom: 1px solid #ccc;">Basic Information</div>';
                        foreach ($column_list[0] as $key1 => $value1) {
                            if(in_array($key1, $array_column)){
                           echo $value1.', &nbsp;'; 
                        }
                        }
                       echo ' <div style="font-weight: bold; border-bottom: 1px solid #ccc;">Contact Information</div>';
                        foreach ($column_list[1] as $key1 => $value1) {
                            if(in_array($key1, $array_column)){
                           echo $value1.', &nbsp;'; 
                        }
                        }
                        echo ' <div style="font-weight: bold; border-bottom: 1px solid #ccc;">Next of Kin Information</div>';
                        foreach ($column_list[2] as $key1 => $value1) {
                            if(in_array($key1, $array_column)){
                           echo $value1.', &nbsp;'; 
                        }
                        }
                        
                        
                        
                        
                    } ?></td>
                     <?php } ?>
                     <td><?php echo ($value->page == 'A4' ? 'Portait' : 'Landscape'); ?></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>
</div>