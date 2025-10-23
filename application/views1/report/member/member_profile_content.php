<style type="text/css">
    #photo{
        display: inline-block;
        width: 200px;
        float: left;
        text-align: left;
    }
    #photo img{
        width: 200px;
        height: 200px;
    }
    #basic_info{
        display: inline-block;
        width: 200px;
        float: left;
        text-align: left;
        margin-left: 15px;
        width: 450px;
        line-height: 19px;
        font-size: 15px;
    }

    #basic_info label{
        width: 200px;
    }
    #wrapper_outer{
        margin: 20px 10px; 
        text-align: left;
    }
</style>
<center>
    <div style="width: 700px;">
        <div id="wrapper_outer">

            <div style="display: block; font-size: 20px; border-bottom: 1px solid #ccc; margin-bottom: 10px;"><strong><?php echo lang('member_basic_info'); ?></strong></div>
            <div id="photo"><img src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $memberinfo->photo; ?>"/></div>
            <div id="basic_info">
                <label><?php echo lang('member_pid'); ?> : </label><?php echo $memberinfo->PID ?><br/>
                <label><?php echo lang('member_member_id'); ?> : </label><?php echo $memberinfo->member_id ?><br/>
                <label><?php echo lang('member_firstname'); ?> : </label><?php echo $memberinfo->firstname ?><br/>
                <label><?php echo lang('member_middlename'); ?> : </label><?php echo $memberinfo->middlename ?><br/>
                <label><?php echo lang('member_lastname'); ?> : </label><?php echo $memberinfo->lastname ?><br/>
                <label><?php echo lang('member_gender'); ?> : </label><?php echo $memberinfo->gender ?><br/>
                <label><?php echo lang('member_maritalstatus'); ?> : </label><?php echo $memberinfo->maritalstatus ?><br/>
                <label><?php echo lang('member_dob'); ?> : </label><?php echo format_date($memberinfo->dob, false); ?><br/>
                <label><?php echo lang('member_join_date'); ?> : </label><?php echo format_date($memberinfo->joiningdate, false); ?><br/>

            </div>
            <div style="clear: both;"></div>

            <br/>
            <br/>
            <div style="display: block; font-size: 20px; border-bottom: 1px solid #ccc; margin-bottom: 10px;"><strong><?php echo lang('member_contact_info'); ?></strong></div>
            <div id="basic_info" style="width: 650px;">
                <label><?php echo lang('member_contact_phone1'); ?> : </label><?php echo $contactinfo->phone1 ?><br/>
                <label><?php echo lang('member_contact_phone2'); ?> : </label><?php echo $contactinfo->phone2 ?><br/>
                <label><?php echo lang('member_contact_email'); ?> : </label><?php echo $contactinfo->email ?><br/>
                <label><?php echo lang('member_contact_box'); ?> : </label><?php echo $contactinfo->postaladdress ?><br/>
                <label><?php echo lang('member_contact_physical'); ?> : </label><?php echo $contactinfo->physicaladdress ?><br/>

            </div>
            <div style="clear: both;"></div>
            <br/>
            <br/>
            <div style="display: block; font-size: 20px; border-bottom: 1px solid #ccc; margin-bottom: 10px;"><strong><?php echo lang('nextkin_title'); ?></strong></div>
            <div id="basic_info" style="width: 650px;">
                <label><?php echo lang('nextkin_name'); ?> : </label><?php echo $nextkininfo->name ?><br/>
                <label><?php echo lang('nextkin_relationship'); ?> : </label><?php echo $nextkininfo->relationship ?><br/>
                <label><?php echo lang('member_contact_phone1'); ?> : </label><?php echo $nextkininfo->phone ?><br/>
                <label><?php echo lang('member_contact_email'); ?> : </label><?php echo $nextkininfo->email ?><br/>
                <label><?php echo lang('member_contact_box'); ?> : </label><?php echo $nextkininfo->postaladdress ?><br/>
                <label><?php echo lang('member_contact_physical'); ?> : </label><?php echo $nextkininfo->physicaladdress ?><br/>

            </div>
            <div style="clear: both;"></div>
        </div>
        


    </div>

</center>
<div style="text-align: center; border-top: 1px solid #000; padding-top: 10px;">
            <a href="<?php echo site_url(current_lang() . '/report_member/member_profile_print/?member_id=' . $member_id); ?>" class="btn btn-primary">Print</a>

        </div>