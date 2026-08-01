<link href="<?php echo base_url(); ?>media/css/jquery.autocomplete.css" rel="stylesheet"/>

<!-- CBU Ledger popup: plain overlay so it never depends on the Bootstrap modal plugin -->
<div id="cbuLedgerOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10500; overflow:auto;">
    <div style="background:#fff; max-width:900px; margin:40px auto; border-radius:4px; box-shadow:0 5px 20px rgba(0,0,0,0.4);">
        <div class="modal-content" style="box-shadow:none; border:0;">
            <div class="modal-header">
                <button type="button" class="close cbu-ledger-close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title"><i class="fa fa-book modal-icon"></i> <?php echo lang('cbu_ledger_title'); ?></h2>
            </div>
            <div class="modal-body">
                <div id="cbu-ledger-loading" style="text-align: center; padding: 20px;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p><?php echo lang('cbu_ledger_loading'); ?></p>
                </div>
                <div id="cbu-ledger-content" style="display: none;">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-6">
                            <p><strong><?php echo lang('member_pid'); ?>:</strong> <span id="cbu-ledger-pid"></span></p>
                            <p><strong><?php echo lang('member_member_id'); ?>:</strong> <span id="cbu-ledger-member-id"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><?php echo lang('contribution_member_name'); ?>:</strong> <span id="cbu-ledger-name"></span></p>
                            <p><strong><?php echo lang('balance'); ?>:</strong> <span id="cbu-ledger-balance"></span></p>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 420px; overflow: auto;">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="text-align: center; width: 110px;"><?php echo lang('index_trans_date'); ?></th>
                                    <th><?php echo lang('comment'); ?></th>
                                    <th style="text-align: right; width: 120px;"><?php echo lang('debit'); ?></th>
                                    <th style="text-align: right; width: 120px;"><?php echo lang('credit'); ?></th>
                                    <th style="text-align: right; width: 130px;"><?php echo lang('balance'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="cbu-ledger-rows">
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold;">
                                    <td colspan="2" style="text-align: right;"><?php echo lang('total'); ?></td>
                                    <td style="text-align: right;" id="cbu-ledger-total-debit">0.00</td>
                                    <td style="text-align: right;" id="cbu-ledger-total-credit">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="cbu-ledger-empty" style="display: none; padding: 20px; text-align: center;">
                        <p class="text-muted"><?php echo lang('cbu_ledger_empty'); ?></p>
                    </div>
                </div>
                <div id="cbu-ledger-error" style="display: none; padding: 20px; text-align: center;">
                    <p class="text-danger"><i class="fa fa-exclamation-triangle"></i> <span id="cbu-ledger-error-message"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default cbu-ledger-close"><?php echo lang('button_close'); ?></button>
            </div>
        </div>
    </div>
</div>

<form action="<?php echo site_url(current_lang() . "/contribution/masterfile_list"); ?>" method="get" class="form-horizontal">

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

<div class="form-group col-lg-10">

    <div class="col-lg-4">
        <input type="text" class="form-control" id="accountno" name="key" value="<?php echo $this->input->get('key') ? htmlspecialchars($this->input->get('key'), ENT_QUOTES, 'UTF-8') : ''; ?>"/>
    </div>
    <div class="col-lg-3">
        <select name="status" class="form-control">
            <option value=""><?php echo lang('all'); ?> <?php echo lang('member_status'); ?></option>
            <option value="1" <?php echo ($this->input->get('status') === '1') ? 'selected' : ''; ?>><?php echo lang('member_active'); ?></option>
            <option value="0" <?php echo ($this->input->get('status') === '0') ? 'selected' : ''; ?>><?php echo lang('member_inactive'); ?></option>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="submit" value="<?php echo lang('button_search'); ?>" class="btn btn-primary"/>
    </div>

</div>

</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="text-align: center; width: 60px;"><?php echo lang('sno'); ?></th>
                <th><?php echo lang('member_pid'); ?></th>
                <th><?php echo lang('member_member_id'); ?></th>
                <th><?php echo lang('contribution_member_name'); ?></th>
                <th style="text-align: right;"><?php echo lang('balance'); ?></th>
                <th style="text-align: center;"><?php echo lang('member_status'); ?></th>
                <th><?php echo lang('index_action_th'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $index = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
            $index++;
            foreach ($masterfile_list as $key => $value) {
                $name = trim($value->firstname . ' ' . $value->middlename . ' ' . $value->lastname);
                $is_active = ((string) $value->member_status === '1');
                ?>
                <tr>
                    <td style="text-align: center;"><?php echo $index++; ?></td>
                    <td style="width:100px;"><?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($value->member_id, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align: right;"><?php echo number_format((float) $value->balance, 2); ?></td>
                    <td style="text-align: center;">
                        <?php if ($is_active) { ?>
                            <span class="badge badge-success" style="color:white;"><?php echo lang('member_active'); ?></span>
                        <?php } else { ?>
                            <span class="badge badge-warning" style="color:white;"><?php echo lang('member_inactive'); ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-info btn-xs btn-outline btn-cbu-ledger"
                           data-pid="<?php echo htmlspecialchars($value->PID, ENT_QUOTES, 'UTF-8'); ?>"
                           data-memberid="<?php echo htmlspecialchars(trim($value->member_id), ENT_QUOTES, 'UTF-8'); ?>"
                           data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fa fa-th-list"></i> <?php echo lang('cbu_ledger'); ?>
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php echo $links; ?>
    <div style="margin-right: 20px; text-align: right;"> <?php page_selector(); ?></div>
</div>

<script type="text/javascript">
(function() {
    var LEDGER_URL = '<?php echo site_url(current_lang() . '/contribution/cbu_ledger_ajax'); ?>';

    function el(id) {
        return document.getElementById(id);
    }

    function show(id, visible) {
        var node = el(id);
        if (node) {
            node.style.display = visible ? 'block' : 'none';
        }
    }

    function setText(id, text) {
        var node = el(id);
        if (node) {
            node.textContent = text;
        }
    }

    function escapeHtml(text) {
        return String(text === null || typeof text === 'undefined' ? '' : text)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function closeLedger() {
        show('cbuLedgerOverlay', false);
    }

    function findLedgerButton(node) {
        while (node && node !== document) {
            if (node.className && String(node.className).indexOf('btn-cbu-ledger') !== -1) {
                return node;
            }
            node = node.parentNode;
        }
        return null;
    }

    function renderLedger(response, pid, memberId, name) {
        show('cbu-ledger-loading', false);

        if (!response || !response.success) {
            show('cbu-ledger-error', true);
            setText('cbu-ledger-error-message', (response && response.message) ? response.message : 'Failed to load ledger.');
            return;
        }

        setText('cbu-ledger-pid', response.pid || pid);
        setText('cbu-ledger-member-id', response.member_id || memberId);
        setText('cbu-ledger-name', response.member_name || name);
        setText('cbu-ledger-balance', response.current_balance || '0.00');
        setText('cbu-ledger-total-debit', response.total_debit || '0.00');
        setText('cbu-ledger-total-credit', response.total_credit || '0.00');

        var rows = response.rows || [];
        if (rows.length > 0) {
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                html += '<tr>' +
                    '<td style="text-align:center;">' + escapeHtml(rows[i].date) + '</td>' +
                    '<td>' + escapeHtml(rows[i].description) + '</td>' +
                    '<td style="text-align:right;">' + escapeHtml(rows[i].debit) + '</td>' +
                    '<td style="text-align:right;">' + escapeHtml(rows[i].credit) + '</td>' +
                    '<td style="text-align:right;">' + escapeHtml(rows[i].balance) + '</td>' +
                    '</tr>';
            }
            el('cbu-ledger-rows').innerHTML = html;
        } else {
            show('cbu-ledger-empty', true);
        }
        show('cbu-ledger-content', true);
    }

    function openLedger(button) {
        if (!el('cbuLedgerOverlay') || !el('cbu-ledger-rows')) {
            alert('CBU ledger popup could not be loaded on this page.');
            return;
        }

        var pid = button.getAttribute('data-pid') || '';
        var memberId = button.getAttribute('data-memberid') || '';
        var name = button.getAttribute('data-name') || '';

        show('cbuLedgerOverlay', true);
        show('cbu-ledger-loading', true);
        show('cbu-ledger-content', false);
        show('cbu-ledger-error', false);
        show('cbu-ledger-empty', false);
        el('cbu-ledger-rows').innerHTML = '';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', LEDGER_URL, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) {
                return;
            }
            if (xhr.status !== 200) {
                show('cbu-ledger-loading', false);
                show('cbu-ledger-error', true);
                setText('cbu-ledger-error-message', 'Failed to load ledger (HTTP ' + xhr.status + ').');
                return;
            }
            var response = null;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (err) {
                show('cbu-ledger-loading', false);
                show('cbu-ledger-error', true);
                setText('cbu-ledger-error-message', 'Server returned an unexpected response.');
                return;
            }
            renderLedger(response, pid, memberId, name);
        };
        xhr.send('pid=' + encodeURIComponent(pid) + '&member_id=' + encodeURIComponent(memberId));
    }

    // The overlay must sit directly under <body>, otherwise the ibox wrapper clips it.
    var overlay = el('cbuLedgerOverlay');
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }

    document.addEventListener('click', function(e) {
        var target = e.target || e.srcElement;

        if (findLedgerButton(target)) {
            e.preventDefault();
            openLedger(findLedgerButton(target));
            return;
        }

        if (target.id === 'cbuLedgerOverlay') {
            closeLedger();
            return;
        }

        var node = target;
        while (node && node !== document) {
            if (node.className && String(node.className).indexOf('cbu-ledger-close') !== -1) {
                e.preventDefault();
                closeLedger();
                return;
            }
            node = node.parentNode;
        }
    }, false);

    document.addEventListener('keydown', function(e) {
        if (e.keyCode === 27) {
            closeLedger();
        }
    }, false);

    function initScripts() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initScripts, 50);
            return;
        }

        function initAutocomplete() {
            try {
                if ($("#accountno").data('ui-autocomplete')) {
                    $("#accountno").autocomplete('destroy');
                }
            } catch (e) {}

            setTimeout(function() {
                try {
                    $("#accountno").autocomplete("<?php echo site_url(current_lang() . '/saving/autosuggest_member_id_all/'); ?>", {
                        matchContains: true
                    });
                } catch (e) {
                    console.error('Autocomplete initialization error:', e);
                }
            }, 150);
        }

        var existingScript = document.querySelector('script[src*="jquery.autocomplete_origin.js"]');
        if (existingScript) {
            setTimeout(initAutocomplete, 200);
        } else {
            var autocompleteScript = document.createElement('script');
            autocompleteScript.src = '<?php echo base_url(); ?>media/js/jquery.autocomplete_origin.js';
            autocompleteScript.onload = function() {
                setTimeout(initAutocomplete, 300);
            };
            document.head.appendChild(autocompleteScript);
        }
    }

    initScripts();
})();
</script>
