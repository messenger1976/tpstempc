<style type="text/css">
    .photo{
        display: inline-block;
        width: 200px;
        float: left;
        text-align: left;
    }
    .photo img{
        width: 200px;
        height: 200px;
    }
    .basic_info{
        display: inline-block;
        width: 200px;
        float: left;
        text-align: left;
        margin-left: 15px;
        width: 450px;
        line-height: 19px;
        font-size: 15px;
    }

    .label{
        width: 200px;
        display: inline-block;
    }
    table.table tr td{
        padding-top: 3px;
        padding-bottom: 3px;
    }
    #wrapper_outer{
        margin: 20px 10px; 
        text-align: left;
    }
</style>
<center style="text-align: center">
    <div>
        <div id="wrapper_outer">

            <div style="display: block; font-size: 20px; border-bottom: 1px solid #ccc; margin-bottom: 10px;"><strong><?php echo lang('member_basic_info'); ?></strong></div>
            <div class="photo"><img src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $memberinfo->photo; ?>"/></div>
            <div class="basic_info">
                <table class="table">
                    <tr>
                        <td class="label"><?php echo lang('member_pid'); ?> :</td>
                        <td><?php echo $memberinfo->PID ?></td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo lang('member_member_id'); ?> :</td>
                        <td><?php echo $memberinfo->member_id ?></td>
                    </tr>
                    <tr>
                        <td class="label"><?php echo lang('member_firstname'); ?>: </td>
                        <td><?php echo $memberinfo->firstname ?></td>
                    </tr>




                    <tr>                        <td class="label"><?php echo lang('member_middlename'); ?> : </td><td><?php echo $memberinfo->middlename ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_lastname'); ?> : </td><td><?php echo $memberinfo->lastname ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_gender'); ?> : </td><td><?php echo $memberinfo->gender ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_maritalstatus'); ?> : </td><td><?php echo $memberinfo->maritalstatus ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_dob'); ?> : </td><td><?php echo format_date($memberinfo->dob, false); ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_join_date'); ?> : </td><td><?php echo format_date($memberinfo->joiningdate, false); ?></td></tr>
                </table>
            </div>
            <div style="clear: both;"></div>

            <br/>
            <br/>
            <div style="display: block; font-size: 20px; border-bottom: 1px solid #ccc; margin-bottom: 10px;"><strong><?php echo lang('member_contact_info'); ?></strong></div>
            <div id="basic_info" style="width: 800px;">
                <table class="table">
                    <tr>                        <td class="label"><?php echo lang('member_contact_phone1'); ?> : </td><td><?php echo $contactinfo->phone1 ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_phone2'); ?> : </td><td><?php echo $contactinfo->phone2 ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_email'); ?> : </td><td><?php echo $contactinfo->email ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_box'); ?> : </td><td><?php echo $contactinfo->postaladdress ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_physical'); ?> : </td><td><?php echo $contactinfo->physicaladdress ?></td></tr>
                </table>
            </div>
            <div style="clear: both;"></div>
            <br/>
            <br/>
            <div style="display: block; font-size: 20px; border-bottom: 1px solid #ccc; margin-bottom: 10px;"><strong><?php echo lang('nextkin_title'); ?></strong></div>
            <div id="basic_info" style="width: 800px;">
                <table class="table">
                    <tr>                        <td class="label"><?php echo lang('nextkin_name'); ?> : </td><td><?php echo $nextkininfo->name ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('nextkin_relationship'); ?> : </td><td><?php echo $nextkininfo->relationship ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_phone1'); ?> : </td><td><?php echo $nextkininfo->phone ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_email'); ?> : </td><td><?php echo $nextkininfo->email ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_box'); ?> : </td><td><?php echo $nextkininfo->postaladdress ?></td></tr>
                    <tr>                        <td class="label"><?php echo lang('member_contact_physical'); ?> : </td><td><?php echo $nextkininfo->physicaladdress ?></td></tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>



    </div>

</center>
