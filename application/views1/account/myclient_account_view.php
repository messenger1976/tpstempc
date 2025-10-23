<div class="row">
    <div class="col-xs-12 col-sm-3 center">

        <span class="profile-picture">
            <img id="avatar" style="height: 150px; width: 150px;" class="editable img-responsive editable-click editable-empty" alt="<?php echo $accountinfo->name; ?>" src="<?php echo base_url() . '/logo/' . $accountinfo->logo; ?>"></img>
        </span>


    </div>
    <div class="col-xs-12 col-sm-9">
        <div class="profile-user-info profile-user-info-striped ">
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('account_no') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->account_no; ?></span>
                </div>
            </div>
           
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_name') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->name; ?></span>
                </div>
            </div>
             <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_religion') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $this->setting_model->religion_list($accountinfo->religion_id)->row()->name; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('level') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $this->setting_model->religion_structure($accountinfo->religion_id,$accountinfo->religion_level)->row()->name; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_address') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->address; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_landline') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->landline; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_mobile') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->mobile; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_website') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->website; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_currency') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->currency; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_sms_token') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->sms_token; ?></span>
                </div>
            </div>
            <div class="profile-info-row">
                <div class="profile-info-name"> <?php echo lang('client_account_currency') ?> </div>

                <div class="profile-info-value">
                    <span  id="username"><?php echo $accountinfo->currency; ?></span>
                </div>
            </div>

            
        </div>
        
        <div style="text-align: center; padding-top:  10px;"><a href="<?php echo site_url(current_lang().'/client_account/client_account_create/'.$accountinfo->element_id.'/'.$accountinfo->id); ?>"><button class="btn btn-lg btn-sm btn-primary">
            <i class="fa fa-edit"></i>
            <?php echo lang('edit_account'); ?></button></a></div>

    </div>
</div>