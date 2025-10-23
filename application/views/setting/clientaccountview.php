<style type="text/css">
    .td_label{
        font-weight: bold;
    }
</style>
<div class="table-responsive">
<div style="width: 70%; margin: auto; text-align: right;">
    <?php echo anchor(current_lang().'/setting/companyinfo_edit/'.  encode_id($account->id),  lang('clientaccount_label_btnedit'),'class="btn btn-primary"'); ?>
</div>
<table class="table table-bordered" style="width: 80%; margin: auto;">

    <tbody>
        
        <tr>
            <td class="td_label"><?php echo lang('clientaccount_label_name'); ?></td>
            <td><?php echo $account->name; ?></td>
        </tr>
        <tr>
            <td class="td_label"><?php echo lang('clientaccount_label_postal_address'); ?></td>
            <td><?php echo $account->box; ?></td>
        </tr>
        <tr>
            <td class="td_label"><?php echo lang('clientaccount_label_physical_address'); ?></td>
            <td><?php echo $account->address; ?></td>
        </tr>
        <tr>
            <td class="td_label"><?php echo lang('clientaccount_label_email'); ?></td>
            <td><?php echo $account->email; ?></td>
        </tr>
        <tr>
            <td class="td_label"><?php echo lang('clientaccount_label_phone'); ?></td>
            <td><?php echo $account->mobile; ?></td>
        </tr>
        <tr>
            <td class="td_label"><?php echo lang('clientaccount_label_fax'); ?></td>
            <td><?php echo $account->fax; ?></td>
        </tr>
     

    </tbody>
</table>
    
</div>