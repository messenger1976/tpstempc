<?php
$this->load->view('member/topmenu');
?>

<div class="col-lg-12 member-info-page">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <?php $this->load->view('member/member_profile_sidebar'); ?>
        </div>

        <div class="col-lg-9 col-md-8">
            <link href="<?php echo base_url(); ?>media/css/move_selected.css" rel="stylesheet">

            <?php echo form_open(current_lang() . "/member/membergroup/" . encode_id($basicinfo->id), 'class="form-horizontal"'); ?>

            <?php
            if (isset($message) && !empty($message)) {
                echo '<div class="member-alert success displaymessage">' . $message . '</div>';
            } else if ($this->session->flashdata('message') != '') {
                echo '<div class="member-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
            } else if (isset($warning) && !empty($warning)) {
                echo '<div class="member-alert danger displaymessage">' . $warning . '</div>';
            } else if ($this->session->flashdata('warning') != '') {
                echo '<div class="member-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
            }
            ?>

            <div class="member-form-panel">
                <div class="panel-head">
                    <i class="fa fa-object-group"></i>
                    <h4><?php echo lang('member_addgroup'); ?></h4>
                </div>
                <div class="panel-body">
                    <input type="hidden" id="view" name="selectedgp"/>

                    <div class="group-transfer">
                        <div class="group-column">
                            <div class="group-column-title">Available Groups</div>
                            <div id="all_users">
                                <?php
                                foreach ($allgroup as $key => $value) {
                                    if (!in_array($value->id, $selected_gp_array)) {
                                        ?>
                                        <div id="user<?php echo $value->id; ?>" userid="<?php echo $value->id; ?>" class="innertxt">
                                            <ul>
                                                <li style="color: blue;"><?php echo $value->name; ?></li>
                                                <li><?php echo $value->description; ?></li>
                                                <li style="padding-top:5px;">
                                                    <input type="checkbox" id="select<?php echo $value->id; ?>" value="<?php echo $value->id; ?>" class="selectit"/>
                                                    <label for="select<?php echo $value->id; ?>">&nbsp;&nbsp;<?php echo lang('select_it'); ?>.</label>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <div class="float_break"></div>
                            </div>
                        </div>

                        <div class="group-actions">
                            <a href="javascript:void(0);" id="move_right"><?php echo lang('right'); ?> &raquo;</a>
                            <a href="javascript:void(0);" id="move_left">&laquo; <?php echo lang('left'); ?></a>
                        </div>

                        <div class="group-column">
                            <div class="group-column-title">Selected Groups</div>
                            <div id="selected_users">
                                <?php
                                foreach ($allgroup as $key => $value) {
                                    if (in_array($value->id, $selected_gp_array)) {
                                        ?>
                                        <div id="user<?php echo $value->id; ?>" userid="<?php echo $value->id; ?>" class="innertxt2">
                                            <ul>
                                                <li style="color: blue;"><?php echo $value->name; ?></li>
                                                <li><?php echo $value->description; ?></li>
                                                <li style="padding-top:5px;">
                                                    <input type="checkbox" id="select<?php echo $value->id; ?>" value="<?php echo $value->id; ?>" class="selectit"/>
                                                    <label for="select<?php echo $value->id; ?>">&nbsp;&nbsp;<?php echo lang('select_it'); ?>.</label>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group member-form-actions">
                        <div class="col-lg-12">
                            <button class="btn btn-primary" name="SAVEGRP" type="submit">
                                <i class="fa fa-save"></i> <?php echo lang('member_memberbtnsave'); ?>
                            </button>
                        </div>
                    </div>
                </div>
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

            <?php echo form_close(); ?>
        </div>
    </div>
</div>
