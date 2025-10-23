<style type="text/css">
    table#headertable tr td{
        font-size: 15px;
    }
    #invoiceheader{
        font-size: 25px;
        margin-bottom: 15px;
        display: inline-block;
    }

    #invoiceheaderlogo{
        height: 100px;
        width: 100px;
        display: inline-block
    }

</style>

<div style="text-align: center; border-bottom: 1px dashed #ccc; padding-bottom: 20px;" class="col-lg-10">
    <table style="width: 100%;" id="headertable">
        <tr><td colspan="3"><img id="invoiceheaderlogo" src="<?php echo base_url() ?>logo/<?php echo company_info()->logo; ?>"/>
                <div id="invoiceheader"><?php echo company_info()->name; ?></div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: left;" valign='top'>
                <?php echo lang('clientaccount_label_phone') . ' : ' . company_info()->mobile; ?><br/>
                <?php echo lang('clientaccount_label_email') . ' : ' . company_info()->email; ?><br/>
                <?php echo lang('clientaccount_label_fax') . ' : ' . company_info()->fax; ?><br/>
                <?php echo lang('clientaccount_label_website') . ' : ' . ''; ?><br/>
            </td>
            <td style="width: 50%; text-align: right;" valign='top'>
                <div style="text-align: left; display: inline-block;">
                    P.O.BOX <?php echo company_info()->box; ?><br/>
                    <?php echo company_info()->address; ?><br/>
                </div>
            </td>
        </tr>
    </table>

</div>

