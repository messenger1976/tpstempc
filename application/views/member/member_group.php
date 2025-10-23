<?php
$this->load->view('member/topmenu');
?>

<div style="margin-top: 20px;" class="col-lg-12">
    <div class="col-lg-3">
        <img src="<?php echo base_url() ?>uploads/memberphoto/<?php echo $basicinfo->photo; ?>" style="width: 150px; height: 170px; border: 1px solid #ccc;"/>
        <div style="display: block;  margin-top: 20px; font-size: 15px;">
            <?php echo lang('member_pid') ?> : <?php echo $basicinfo->PID; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_member_id') ?> : <?php echo $basicinfo->member_id; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_firstname') ?> : <?php echo $basicinfo->firstname; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_middlename') ?> : <?php echo $basicinfo->middlename; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_lastname') ?> : <?php echo $basicinfo->lastname; ?>
        </div>
        <div style="display: block;  margin-top: 5px; font-size: 15px;">
            <?php echo lang('member_gender') ?> : <?php echo $basicinfo->gender; ?>
        </div>
    </div>

    <div class="col-lg-8">

        <link href="<?php echo base_url(); ?>media/css/move_selected.css" rel="stylesheet">

        <?php echo form_open(current_lang() . "/member/membergroup/" . encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

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

        <div>
            <input type="hidden" id="view" name="selectedgp"/>
            <div class="stack">
                <div class="form_top"> </div>
                <div class="float_break"></div>
                <div class="form">
                    <br />
                    <div class="formbox">     

                        <div id="all_users" class="col-lg-4" style="padding-top: 15px;">
                            <?php
                            foreach ($allgroup as $key => $value) {
                                if (!in_array($value->id, $selected_gp_array)) {
                                    ?>

                                    <div id="user<?php echo $value->id; ?>" userid="<?php echo $value->id; ?>" class="innertxt">

                                        <ul>
                                            <li style="color: blue;"><?php echo $value->name; ?></li>
                                            <li><?php echo $value->description; ?></li>
                                            <li style="padding-top:5px;"><input type="checkbox" id="select<?php echo $value->id; ?>" value="<?php echo $value->id; ?>" class="selectit" /><label for="select<?php echo $value->id; ?>">&nbsp;&nbsp;<?php echo lang('select_it'); ?>.</label></li>
                                        </ul>
                                    </div>


                                    <?php
                                }
                            }
                            ?>

                            <div class="float_break"></div> 
                        </div>
                        <div style="width:100px; text-align:center; margin-left:20px; padding-top: 100px; width:75px; float:left;">
                            <a href="javascript:void(0);" id="move_right"><?php echo lang('right'); ?> &raquo;</a><br /><br />
                            <a href="javascript:void(0);" id="move_left">&laquo; <?php echo lang('left'); ?></a>
                            <div class="float_break"></div>   
                        </div>
                        <div id="selected_users" class="col-lg-4">
                            
                                  <?php
                            foreach ($allgroup as $key => $value) {
                                if (in_array($value->id, $selected_gp_array)) {
                                    ?>

                                    <div id="user<?php echo $value->id; ?>" userid="<?php echo $value->id; ?>" class="innertxt2">

                                        <ul>
                                            <li style="color: blue;"><?php echo $value->name; ?></li>
                                            <li><?php echo $value->description; ?></li>
                                            <li style="padding-top:5px;"><input type="checkbox" id="select<?php echo $value->id; ?>" value="<?php echo $value->id; ?>" class="selectit" /><label for="select<?php echo $value->id; ?>">&nbsp;&nbsp;<?php echo lang('select_it'); ?>.</label></li>
                                        </ul>
                                    </div>


                                    <?php
                                }
                            }
                            ?>
                            
                            
                        </div>
                        <div class="float_break"></div> 
                       
                    </div>
                    <div class="float_break"></div>
                    <div class="formbox">
                       
                        <div class="float_break"></div>
                    </div>
                </div>
                <div class="form_bot"></div>
            </div>

            <script type="text/javascript">
                $(document).ready(function () {
                    
                       var users = '';
                        $('#selected_users .innertxt2').each(function() {
                            
                            var user_id = $(this).attr('userid');
                            if (users == '') 
                                users += user_id;
                            else
                                users += ',' + user_id;
                                    
                        });
                        
                        $("#view").val(users);
                        
                       
                    
                    
                    // Uncheck each checkbox on body load
                    $('#all_users .selectit').each(function() {this.checked = false;});
                    $('#selected_users .selectit').each(function() {this.checked = false;});
		
                    $('#all_users .selectit').click(function() {
                        var userid = $(this).val();
                        $('#user' + userid).toggleClass('innertxt_bg');
                    });
		
                    $('#selected_users .selectit').click(function() {
                        var userid = $(this).val();
                        $('#user' + userid).toggleClass('innertxt_bg');
                    });
		
                    $("#move_right").click(function() {
                        var users = $('#selected_users .innertxt2').size();
                        var selected_users = $('#all_users .innertxt_bg').size();
			
                        //if (users + selected_users > 5) {
                         //   alert('You can only chose maximum 5 users.');
                           // return;
                        //}
			
                        $('#all_users .innertxt_bg').each(function() {
                            var user_id = $(this).attr('userid');
                            $('#select' + user_id).each(function() {this.checked = false;});
				
                            var user_clone = $(this).clone(true);
                            $(user_clone).removeClass('innertxt');
                            $(user_clone).removeClass('innertxt_bg');
                            $(user_clone).addClass('innertxt2');
				
                            $('#selected_users').append(user_clone);
                            $(this).remove();
                        });
                        
                        
                         var users = '';
                        $('#selected_users .innertxt2').each(function() {
                            var user_id = $(this).attr('userid');
                            if (users == '') 
                                users += user_id;
                            else
                                users += ',' + user_id;
                        });
                        
                        $("#view").val(users);
                        
                        
                    });
		
                    $("#move_left").click(function() {
                        $('#selected_users .innertxt_bg').each(function() {
                            var user_id = $(this).attr('userid');
                            $('#select' + user_id).each(function() {this.checked = false;});
				
                            var user_clone = $(this).clone(true);
                            $(user_clone).removeClass('innertxt2');
                            $(user_clone).removeClass('innertxt_bg');
                            $(user_clone).addClass('innertxt');
				
                            $('#all_users').append(user_clone);
                            $(this).remove();
                        });
                        
                        
                         var users = '';
                        $('#selected_users .innertxt2').each(function() {
                            var user_id = $(this).attr('userid');
                            if (users == '') 
                                users += user_id;
                            else
                                users += ',' + user_id;
                        });
                        
                        $("#view").val(users);
                        
                        
                    });
		
                   /* $('#view').click(function() {
                        var users = '';
                        $('#selected_users .innertxt2').each(function() {
                            var user_id = $(this).attr('userid');
                            if (users == '') 
                                users += user_id;
                            else
                                users += ',' + user_id;
                        });
                        alert(users);
                        $("#view").val(users);
                    });*/
                });
            </script>


        </div>





        <div class="form-group">
            <label class="col-lg-3 control-label">&nbsp;</label>
            <div class="col-lg-6">
                <input class="btn btn-primary" value="<?php echo lang('member_memberbtnsave'); ?>" name="SAVEGRP" type="submit"/>
            </div>
        </div>

        <?php echo form_close(); ?>



    </div>
</div>