<?php
$activefunction = ($this->uri->segment(3)) ? $this->uri->segment(3) : 'X';
?>
<link href="<?php echo base_url(); ?>media/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<div class="row">
    <div class="col-lg-12">
        <div class="panel blank-panel">

            <div class="panel-heading">
                <div class="panel-options">

                    <ul class="nav nav-tabs">
                        <li <?php echo ($activefunction == 'member_report' ? 'class="active"' : ''); ?>><a data-toggle="tab" href="#tab-1"><?php echo lang('member_report_list') ?></a></li>
                        <li <?php echo ($activefunction == 'contribution_report' ? 'class="active"' : ''); ?>><a data-toggle="tab" href="#tab-2">Members Contribution</a></li>
                        <li <?php echo ($activefunction == 'contribution_statement' ? 'class="active"' : ''); ?>><a data-toggle="tab" href="#tab-3">Contribution Statement</a></li>
                    </ul>
                </div>
            </div>

            <div class="panel-body">

                <div class="tab-content">

                    <div id="tab-1" class="tab-pane <?php echo ($activefunction == 'member_report' ? 'active' : ''); ?>">
                        <?php echo form_open_multipart(current_lang() . "/report/member_report", 'class="form-horizontal"'); ?>

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
                        <div style="text-align: center;"><?php echo lang('member_memberlist_default'); ?></div>
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_join_date_from'); ?>  : </label>
                            <div class=" col-lg-6">
                                <div class="input-group date" id="datetimepicker2" >
                                    <input type="text" name="joindate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar "></span>
                                    </span>
                                </div>
                                <?php echo form_error('joindate'); ?>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo lang('member_join_date_to'); ?>  : </label>
                            <div class=" col-lg-6">
                                <div class="input-group date" id="datetimepicker" >
                                    <input type="text" name="joindate1" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate1'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar "></span>
                                    </span>
                                </div>
                                <?php echo form_error('joindate1'); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 control-label">&nbsp;</label>
                            <div class="col-lg-6">
                                <input class="btn btn-primary" value="<?php echo lang('member_report_btn'); ?>" type="submit"/>
                            </div>
                        </div>
                        <input name="member" value="1" type="hidden"/>
                        <?php echo form_close(); ?>
                    </div>


                    <div id="tab-2" class="tab-pane <?php echo ($activefunction == 'contribution_report' ? "active" : ''); ?>">
                        <?php echo form_open_multipart(current_lang() . "/report/contribution_report", 'class="form-horizontal"'); ?>

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

                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'FROM'; ?>  : </label>
                            <div class=" col-lg-6">
                                <div class="input-group date" id="datetimepicker3" >
                                    <input type="text" name="joindate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar "></span>
                                    </span>
                                </div>
                                <?php echo form_error('joindate'); ?>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'UP TO'; ?>  : </label>
                            <div class=" col-lg-6">
                                <div class="input-group date" id="datetimepicker4" >
                                    <input type="text" name="joindate1" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate1'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar "></span>
                                    </span>
                                </div>
                                <?php echo form_error('joindate1'); ?>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Details'; ?> : </label>
                            <div class=" col-lg-6">

                                <input type="radio" name="grouping" value="1"/> GROUP BY MEMBER
                                <input type="radio" checked="checked" name="grouping" value="2"/> WITHOUT GROUPING
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 control-label">&nbsp;</label>
                            <div class="col-lg-6">
                                <input class="btn btn-primary" value="<?php echo lang('member_report_btn'); ?>" type="submit"/>
                            </div>
                        </div>
                        <input name="member" value="1" type="hidden"/>
                        <?php echo form_close(); ?>
                    </div>



                    <div id="tab-3" class="tab-pane <?php echo ($activefunction == 'contribution_statement' ? 'active' : ''); ?>">
                        <?php echo form_open_multipart(current_lang() . "/report/contribution_statement", 'class="form-horizontal"'); ?>

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
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'Member ID'; ?>  : <span class="required">*</span></label>
                            <div class="col-lg-6">
                                <select name="member_id" class="form-control" id="loanid">
                                    <option value=""> <?php echo lang('select_default_text'); ?></option>
                                    <?php
                                    $selected = set_value('member_id');
                                    $member = $this->member_model->member_basic_info()->result();
                                    foreach ($member as $key => $value) {
                                        ?>
                                        <option <?php echo ($selected ? ($selected == $value->LID ? 'selected="selected' : '') : ''); ?> value="<?php echo $value->PID; ?>"> <?php echo $value->member_id . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname; ?></option>
                                    <?php }
                                    ?>
                                </select>
                                <?php echo form_error('member_id'); ?>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'FROM'; ?>  : </label>
                            <div class=" col-lg-6">
                                <div class="input-group date" id="datetimepicker5" >
                                    <input type="text" name="joindate" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar "></span>
                                    </span>
                                </div>
                                <?php echo form_error('joindate'); ?>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label"><?php echo 'UP TO'; ?>  : </label>
                            <div class=" col-lg-6">
                                <div class="input-group date" id="datetimepicker6" >
                                    <input type="text" name="joindate1" placeholder="<?php echo lang('hint_date'); ?>" value="<?php echo set_value('joindate1'); ?>"  data-date-format="DD-MM-YYYY" class="form-control"/> 
                                    <span class="input-group-addon">
                                        <span class="fa fa-calendar "></span>
                                    </span>
                                </div>
                                <?php echo form_error('joindate1'); ?>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-lg-3 control-label">&nbsp;</label>
                            <div class="col-lg-6">
                                <input class="btn btn-primary" value="<?php echo lang('member_report_btn'); ?>" type="submit"/>
                            </div>
                        </div>
                        <input name="member" value="1" type="hidden"/>
                        <?php echo form_close(); ?>
                    </div>



                </div>

            </div>

        </div>
    </div>

</div>


<script src="<?php echo base_url() ?>media/js/script/moment.js"></script>
<script src="<?php echo base_url() ?>media/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
    $(function() {
        $('#datetimepicker').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker2').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker3').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker4').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker5').datetimepicker({
            pickTime: false
        });
        $('#datetimepicker6').datetimepicker({
            pickTime: false
        });
    });
</script>