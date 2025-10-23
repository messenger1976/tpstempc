<div style="margin-left: 50px; margin-bottom: 20px; border-bottom: 1px dashed #ccc; margin-right: 50px;">
    <a class="btn btn-primary" href="<?php echo site_url(current_lang().'/member/memberinfo/'.  encode_id($basicinfo->id)); ?>"><?php echo lang('member_basic_info'); ?></a>
    <a class="btn btn-primary" href="<?php echo site_url(current_lang().'/member/membercontact/'.  encode_id($basicinfo->id)); ?>"  ><?php echo lang('member_contact_info'); ?></a>
    <a class="btn btn-primary" href="<?php echo site_url(current_lang().'/member/membernextkin/'.  encode_id($basicinfo->id)); ?>"   ><?php echo lang('member_nextkin_info'); ?></a>
    <a class="btn btn-primary" href="<?php echo site_url(current_lang().'/member/membergroup/'.  encode_id($basicinfo->id)); ?>"   ><?php echo lang('member_addgroup'); ?></a>
</div>