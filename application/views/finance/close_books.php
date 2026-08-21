<link href="<?php echo base_url(); ?>assets/css/plugins/datapicker/datepicker3.css?v=20260801" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
<?php
$closed_as_of = isset($closed_as_of) ? $closed_as_of : null;
$fiscal_years = isset($fiscal_years) ? $fiscal_years : array();
$close_history = isset($close_history) ? $close_history : array();
$closed_display = $closed_as_of ? date('d-m-Y', strtotime($closed_as_of)) : '';
?>
<style>
.gl-close-page { margin-top: 4px; }
.gl-close-page .cbu-alert {
    display: block; margin: 0 0 16px; padding: 10px 14px; border-radius: 6px;
    font-size: 13px; font-weight: 600;
}
.gl-close-page .cbu-alert.success { background: #e8f8f5; color: #0e7c69; border: 1px solid #c9ebe3; }
.gl-close-page .cbu-alert.danger { background: #fdeceb; color: #c0392b; border: 1px solid #f5c6cb; }
.gl-close-page .cbu-alert.warning { background: #fef5e7; color: #9a6b12; border: 1px solid #f5e0b8; }
.gl-close-page .cbu-alert.info { background: #eef6fb; color: #1c6ea4; border: 1px solid #d4e8f5; }
.gl-close-page .cbu-panel {
    background: #fff; border: 1px solid #e7eaec; border-radius: 10px;
    margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); overflow: visible;
}
.gl-close-page .cbu-panel .panel-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 14px 20px; background: #fafbfc; border-bottom: 1px solid #e7eaec;
}
.gl-close-page .cbu-panel .panel-head h4 { margin: 0; font-size: 15px; font-weight: 700; color: #2f4050; }
.gl-close-page .cbu-panel .panel-body { padding: 20px; overflow: visible; }
.gl-close-page .status-pill {
    display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;
}
.gl-close-page .status-pill.closed { background: #f8ac59; color: #fff; }
.gl-close-page .status-pill.open { background: #1ab394; color: #fff; }
.gl-close-page .help { color: #676a6c; font-size: 13px; margin: 0 0 16px; }
.gl-close-page .fy-table td, .gl-close-page .fy-table th { vertical-align: middle; }
.gl-close-page .input-group-addon { cursor: pointer; }
.datepicker-dropdown,.datepicker{z-index:9999!important;width:auto;min-width:0;}
.datepicker-dropdown.dropdown-menu{background:#fff;border:1px solid #e7eaec;box-shadow:0 2px 8px rgba(0,0,0,0.12);padding:8px;width:auto;min-width:220px;max-width:280px;}
.datepicker table{width:auto;margin:0;table-layout:fixed;}
.datepicker td,.datepicker th{text-align:center;width:auto;}
</style>

<div class="col-lg-12 gl-close-page">
    <?php
    if ($this->session->flashdata('message') != '') {
        echo '<div class="cbu-alert success displaymessage">' . $this->session->flashdata('message') . '</div>';
    } else if ($this->session->flashdata('warning') != '') {
        echo '<div class="cbu-alert danger displaymessage">' . $this->session->flashdata('warning') . '</div>';
    }
    ?>

    <div class="cbu-panel">
        <div class="panel-head">
            <h4><i class="fa fa-lock"></i> <?php echo lang('gl_close_books'); ?></h4>
            <?php if ($closed_as_of) { ?>
                <span class="status-pill closed"><?php echo sprintf(lang('gl_close_books_status_closed'), date('M d, Y', strtotime($closed_as_of))); ?></span>
            <?php } else { ?>
                <span class="status-pill open"><?php echo lang('gl_close_books_status_open'); ?></span>
            <?php } ?>
        </div>
        <div class="panel-body">
            <p class="help"><?php echo lang('gl_close_books_help'); ?></p>

            <?php echo form_open(current_lang() . '/finance/close_books', 'class="form-horizontal"'); ?>
            <input type="hidden" name="close_action" value="set"/>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('gl_close_books_as_of'); ?></label>
                <div class="col-lg-4">
                    <div class="input-group date" id="closeBooksDate">
                        <input type="text" name="closed_as_of" id="closed_as_of"
                               value="<?php echo htmlspecialchars($closed_display, ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="<?php echo lang('hint_date'); ?>" class="form-control"/>
                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                    <span class="help-block"><?php echo lang('gl_close_books_as_of_hint'); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?php echo lang('gl_close_books_note'); ?></label>
                <div class="col-lg-6">
                    <input type="text" name="note" class="form-control" maxlength="255" placeholder="<?php echo lang('gl_close_books_note_hint'); ?>"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-6">
                    <button type="submit" name="save_close_books" value="1" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo lang('gl_close_books_save'); ?>
                    </button>
                    <?php if ($closed_as_of) { ?>
                        <button type="submit" name="save_close_books" value="1" class="btn btn-warning"
                                onclick="this.form.close_action.value='clear'; return confirm('<?php echo htmlspecialchars(lang('gl_close_books_clear_confirm'), ENT_QUOTES, 'UTF-8'); ?>');">
                            <i class="fa fa-unlock"></i> <?php echo lang('gl_close_books_clear'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="cbu-panel">
        <div class="panel-head">
            <h4><?php echo lang('gl_close_books_by_fiscal_year'); ?></h4>
        </div>
        <div class="panel-body">
            <p class="help"><?php echo lang('gl_close_books_by_fiscal_year_help'); ?></p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped fy-table">
                    <thead>
                        <tr>
                            <th><?php echo lang('fiscal_year_name'); ?></th>
                            <th><?php echo lang('fiscal_year_start_date'); ?></th>
                            <th><?php echo lang('fiscal_year_end_date'); ?></th>
                            <th><?php echo lang('status'); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($fiscal_years)) { ?>
                        <?php foreach ($fiscal_years as $fy) {
                            $end = date('Y-m-d', strtotime($fy->end_date));
                            $is_closed = ($closed_as_of && strcmp($end, $closed_as_of) <= 0);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fy->name, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($fy->start_date)); ?></td>
                                <td><?php echo date('M d, Y', strtotime($fy->end_date)); ?></td>
                                <td>
                                    <?php if ($is_closed) { ?>
                                        <span class="status-pill closed"><?php echo lang('gl_close_books_year_closed'); ?></span>
                                    <?php } else { ?>
                                        <span class="status-pill open"><?php echo lang('gl_close_books_year_open'); ?></span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php echo form_open(current_lang() . '/finance/close_books', 'style="display:inline"'); ?>
                                    <input type="hidden" name="close_action" value="close_fy"/>
                                    <input type="hidden" name="fiscal_year_id" value="<?php echo (int) $fy->id; ?>"/>
                                    <button type="submit" name="save_close_books" value="1" class="btn btn-sm btn-default"
                                            onclick="return confirm('<?php echo htmlspecialchars(sprintf(lang('gl_close_books_fy_confirm'), $fy->name, date('M d, Y', strtotime($fy->end_date))), ENT_QUOTES, 'UTF-8'); ?>');">
                                        <?php echo lang('gl_close_books_close_year'); ?>
                                    </button>
                                    <?php echo form_close(); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td colspan="5"><?php echo lang('fiscal_year_no_data'); ?></td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (!empty($close_history)) { ?>
    <div class="cbu-panel">
        <div class="panel-head">
            <h4><?php echo lang('gl_close_books_history'); ?></h4>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo lang('gl_close_books_as_of'); ?></th>
                            <th><?php echo lang('gl_close_books_action'); ?></th>
                            <th><?php echo lang('gl_close_books_note'); ?></th>
                            <th><?php echo lang('gl_close_books_when'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($close_history as $row) { ?>
                        <tr>
                            <td><?php echo !empty($row->closed_as_of) ? date('M d, Y', strtotime($row->closed_as_of)) : lang('gl_close_books_status_open'); ?></td>
                            <td><?php echo htmlspecialchars($row->action, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $row->note, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo !empty($row->created_at) ? date('M d, Y H:i', strtotime($row->created_at)) : ''; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<script>
(function() {
    function loadScript(src, cb, fallback) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        if (fallback) {
            s.onerror = function() { loadScript(fallback, cb); };
        }
        document.head.appendChild(s);
    }

    function initOnceReady() {
        if (!window.jQuery) {
            setTimeout(initOnceReady, 50);
            return;
        }
        var $ = window.jQuery;

        function ensureBootstrapDP(cb) {
            function wrapBootstrapDP() {
                if ($.fn.datepicker && $.fn.datepicker.DPGlobal) {
                    $.fn.bootstrapDP = $.fn.datepicker;
                    if ($.fn.datepicker.noConflict) {
                        $.fn.datepicker.noConflict();
                    }
                }
                cb();
            }
            if (!($.fn.datepicker && $.fn.datepicker.DPGlobal)) {
                loadScript(
                    '<?php echo base_url(); ?>assets/js/plugins/datapicker/bootstrap-datepicker.js',
                    wrapBootstrapDP,
                    'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js'
                );
            } else {
                wrapBootstrapDP();
            }
        }

        function initPicker() {
            var picker = $.fn.bootstrapDP || $.fn.datepicker;
            if (!picker) { return; }
            var $wrap = $('#closeBooksDate');
            if (!$wrap.length) { return; }
            picker.call($wrap, {
                todayBtn: 'linked',
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
                format: 'dd-mm-yyyy',
                orientation: 'bottom auto',
                todayHighlight: true,
                container: 'body'
            });
            $wrap.find('.input-group-addon').on('click', function() {
                picker.call($wrap, 'show');
            });
        }

        ensureBootstrapDP(initPicker);
    }

    initOnceReady();
})();
</script>
