<style>
.member-list-page { margin-top: 4px; }
.member-list-page .member-alert {
    display: block;
    margin: 0 0 16px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}
.member-list-page .member-alert.success {
    background: #e8f8f5;
    color: #0e7c69;
    border: 1px solid #c9ebe3;
}
.member-list-page .member-alert.danger {
    background: #fdeceb;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
.member-list-page .member-filter-panel,
.member-list-page .member-table-panel {
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 10px;
    margin-bottom: 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    overflow: visible;
}
.member-list-page .member-table-panel {
    overflow: hidden;
}
.member-list-page .member-filter-panel .panel-body {
    overflow: visible;
}
.member-list-page .panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 18px;
    background: #fafbfc;
    border-bottom: 1px solid #e7eaec;
}
.member-list-page .panel-head-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.member-list-page .panel-head i.icon-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e8f8f5;
    color: #1ab394;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.member-list-page .panel-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #2f4050;
}
.member-list-page .result-meta {
    color: #888;
    font-size: 12px;
    font-weight: 600;
}
.member-list-page .result-meta strong {
    color: #1ab394;
}
.member-list-page .panel-body { padding: 18px; }
.member-list-page .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.member-list-page .filter-field {
    flex: 1 1 180px;
    min-width: 160px;
}
.member-list-page .filter-field.search-field { flex: 2 1 260px; position: relative; }
.member-list-page .member-suggest-box {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 4px;
    background: #fff;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-height: 300px;
    overflow-y: auto;
    z-index: 10050;
}
.member-list-page .member-suggest-box.open { display: block; }
.member-list-page .member-suggest-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border: 0;
    border-bottom: 1px solid #f0f2f3;
    background: #fff;
    text-align: left;
    cursor: pointer;
}
.member-list-page .member-suggest-item:last-child { border-bottom: 0; }
.member-list-page .member-suggest-item:hover,
.member-list-page .member-suggest-item.active {
    background: #e8f8f5;
}
.member-list-page .member-suggest-item .suggest-id {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 11px;
    white-space: nowrap;
}
.member-list-page .member-suggest-item .suggest-name {
    flex: 1;
    font-weight: 600;
    color: #2f4050;
    font-size: 13px;
}
.member-list-page .member-suggest-item .suggest-status {
    color: #999;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.member-list-page .member-suggest-empty {
    padding: 12px;
    color: #999;
    font-size: 12px;
}
.member-list-page .filter-field label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #888;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.member-list-page .filter-field .form-control {
    border-radius: 6px;
    border-color: #e5e6e7;
    box-shadow: none;
    height: 36px;
}
.member-list-page .filter-field .form-control:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-list-page .filter-actions {
    flex: 0 0 auto;
    display: flex;
    gap: 8px;
    align-items: center;
}
.member-list-page .filter-actions .btn {
    border-radius: 6px;
    font-weight: 600;
    padding: 8px 16px;
    height: 36px;
}
.member-list-page .filter-actions .btn-default {
    background: #fff;
    border: 1px solid #e1e5e8;
    color: #676a6c;
    box-shadow: none;
}
.member-list-page .filter-actions .btn-default:hover {
    background: #f8fafb;
    border-color: #c5c9cc;
    color: #2f4050;
}
.member-list-page .filter-actions .btn-primary {
    box-shadow: 0 2px 6px rgba(26,179,148,0.25);
}
.member-list-page .member-table {
    margin: 0;
    background: #fff;
    border-collapse: separate;
    border-spacing: 0;
}
.member-list-page .table-responsive {
    border-top: 0;
    max-height: 68vh;
    overflow: auto;
}
.member-list-page .member-table > thead > tr > th {
    background: linear-gradient(180deg, #fbfcfd 0%, #f4f7f8 100%);
    border: 1px solid #e7eaec !important;
    border-top: 0 !important;
    color: #5a5e63;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    vertical-align: middle;
    white-space: nowrap;
    padding: 13px 14px;
    position: sticky;
    top: 0;
    z-index: 2;
}
.member-list-page .member-table > tbody > tr > td {
    vertical-align: middle;
    border: 1px solid #e7eaec !important;
    padding: 12px 14px;
    color: #2f4050;
    font-size: 13px;
}
.member-list-page .member-table > thead > tr > th:not(:first-child),
.member-list-page .member-table > tbody > tr > td:not(:first-child) {
    border-left: 0 !important;
}
.member-list-page .member-table > tbody > tr:nth-child(even) {
    background: #fcfdfd;
}
.member-list-page .member-table > tbody > tr:hover {
    background: #f3fbf8 !important;
}
.member-list-page .member-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e8f8f5;
    display: block;
    box-shadow: 0 2px 6px rgba(26,179,148,0.15);
}
.member-list-page .member-id-chip {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    background: #e8f8f5;
    color: #0e7c69;
    font-weight: 700;
    font-size: 12px;
}
.member-list-page .status-pill {
    display: inline-block;
    padding: 5px 11px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none !important;
}
.member-list-page .status-pill.active {
    background: #e8f8f5;
    color: #0e7c69;
}
.member-list-page .status-pill.inactive {
    background: #fdeceb;
    color: #c0392b;
}
.member-list-page .status-pill:hover {
    opacity: .9;
}
.member-list-page .action-btns {
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
}
.member-list-page .action-btns .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    min-width: 30px;
    height: 30px;
    padding: 0;
    border-radius: 6px;
    font-size: 0;
    line-height: 1;
    overflow: hidden;
}
.member-list-page .action-btns .btn i,
.member-list-page .action-btns .btn .fa {
    font-size: 13px;
    line-height: 1;
    margin: 0;
    width: auto;
}
.member-list-page .list-footer {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-top: 1px solid #eef1f2;
    background: linear-gradient(180deg, #fbfcfd 0%, #f6f8f9 100%);
}
.member-list-page .list-footer .pagination,
.member-list-page .member-pagination {
    display: flex !important;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    border: 0 !important;
    border-top: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
}
.member-list-page .member-pagination .link-pagination {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    margin: 0 !important;
    border: 1px solid #e1e5e8 !important;
    border-radius: 8px !important;
    background: #fff;
    overflow: hidden;
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.member-list-page .member-pagination .link-pagination a {
    display: block;
    padding: 8px 12px !important;
    color: #676a6c !important;
    text-decoration: none !important;
    font-weight: 600;
    font-size: 13px;
    line-height: 1.2;
}
.member-list-page .member-pagination .link-pagination:hover {
    border-color: #1ab394 !important;
    box-shadow: 0 2px 8px rgba(26,179,148,0.15);
}
.member-list-page .member-pagination .link-pagination:hover a {
    color: #1ab394 !important;
    background: #e8f8f5;
}
.member-list-page .member-pagination .link-pagination.current {
    background: #1ab394 !important;
    border-color: #1ab394 !important;
    color: #fff !important;
    padding: 8px 12px !important;
    font-weight: 700;
    font-size: 13px;
    min-width: 36px;
    box-shadow: 0 2px 8px rgba(26,179,148,0.28);
}
.member-list-page .member-pagination .link-pagination.nav-btn a {
    color: #2f4050 !important;
}
.member-list-page .page-size-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border: 1px solid #e7eaec;
    border-radius: 8px;
    background: #fff;
    color: #676a6c;
    font-size: 12px;
    font-weight: 600;
}
.member-list-page .page-size-wrap #per_pg {
    width: 78px !important;
    height: 32px;
    padding: 4px 8px !important;
    border: 1px solid #e5e6e7;
    border-radius: 6px;
    background: #fafbfc !important;
    color: #2f4050;
    font-weight: 700;
    outline: none;
}
.member-list-page .page-size-wrap #per_pg:focus {
    border-color: #1ab394;
    box-shadow: 0 0 0 2px rgba(26,179,148,0.15);
}
.member-list-page .empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #999;
}
.member-list-page .empty-state i {
    font-size: 40px;
    color: #c9ebe3;
    margin-bottom: 12px;
    display: block;
}
@media (max-width: 767px) {
    .member-list-page .filter-actions {
        width: 100%;
    }
    .member-list-page .filter-actions .btn {
        flex: 1;
    }
}
</style>
<?php $this->load->view('loan/list_action_icon_script'); ?>

<div class="col-lg-12 member-list-page">
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

    <div class="member-filter-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-search icon-badge"></i>
                <h4>Find Members</h4>
            </div>
            <?php echo anchor(current_lang() . '/member/new_member', '<i class="fa fa-user-plus"></i> ' . lang('member_registration'), 'class="btn btn-primary btn-sm"'); ?>
        </div>
        <div class="panel-body">
            <?php echo form_open(current_lang() . "/member/member_list", 'class="form-horizontal" method="GET"'); ?>
            <div class="filter-row">
                <div class="filter-field search-field">
                    <label>Search</label>
                    <input type="text" id="member-list-search" class="form-control" name="key" value="<?php echo htmlspecialchars(isset($key) ? $key : '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Name, Member ID..." autocomplete="off"/>
                    <div id="member-suggest-box" class="member-suggest-box" role="listbox" aria-label="Member suggestions"></div>
                </div>
                <div class="filter-field">
                    <label>Status</label>
                    <select class="form-control" name="searchstatus">
                        <option value="" <?php echo $searchstatus == '' ? 'selected' : ''; ?>>All</option>
                        <option value="1" <?php echo $searchstatus == '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $searchstatus == '0' ? 'selected' : ''; ?>>In-Active</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label>Type</label>
                    <select class="form-control" name="searchmember">
                        <option value="" <?php echo $searchmember == '' ? 'selected' : ''; ?>>All</option>
                        <option value="1" <?php echo $searchmember == '1' ? 'selected' : ''; ?>>Member</option>
                        <option value="0" <?php echo $searchmember == '0' ? 'selected' : ''; ?>>Non-member</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <a href="<?php echo site_url(current_lang() . '/member/member_list'); ?>" class="btn btn-default btn-clear-filter">
                        <i class="fa fa-undo"></i> Clear
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> <?php echo lang('button_search'); ?>
                    </button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>

    <div class="member-table-panel">
        <div class="panel-head">
            <div class="panel-head-left">
                <i class="fa fa-users icon-badge"></i>
                <h4><?php echo lang('member_list'); ?></h4>
            </div>
            <?php
            $total_rows = isset($total_rows) ? (int) $total_rows : 0;
            $page_start = isset($page_start) ? (int) $page_start : 0;
            $per_page = isset($per_page) ? (int) $per_page : 0;
            $from = $total_rows > 0 ? ($page_start + 1) : 0;
            $to = $total_rows > 0 ? min($page_start + $per_page, $total_rows) : 0;
            ?>
            <div class="result-meta">
                Showing <strong><?php echo number_format($from); ?>-<?php echo number_format($to); ?></strong>
                of <strong><?php echo number_format($total_rows); ?></strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered member-table">
                <thead>
                    <tr>
                        <th><?php echo 'Mem ID'; ?></th>
                        <th style="width: 56px;"></th>
                        <th><?php echo lang('member_firstname'); ?></th>
                        <th><?php echo lang('member_middlename'); ?></th>
                        <th><?php echo lang('member_lastname'); ?></th>
                        <th><?php echo lang('member_gender'); ?></th>
                        <th><?php echo lang('member_contact_phone1'); ?></th>
                        <th><?php echo lang('member_status'); ?></th>
                        <th><?php echo lang('index_action_th'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($member_list)) { ?>
                        <?php foreach ($member_list as $row) { ?>
                            <tr>
                                <td>
                                    <span class="member-id-chip"><?php echo htmlspecialchars($row->member_id, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td>
                                    <img class="member-avatar" src="<?php echo htmlspecialchars(base_url() . 'uploads/memberphoto/' . $row->photo, ENT_QUOTES, 'UTF-8'); ?>" alt=""/>
                                </td>
                                <td><?php echo htmlspecialchars($row->firstname, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row->middlename, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row->lastname, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row->gender, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php echo $this->member_model->member_contact($row->PID)->phone1; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($row->status == 1) {
                                        echo anchor(current_lang() . '/member/deactivate/' . encode_id($row->id), lang('member_active'), 'class="status-pill active" title="Click to deactivate"');
                                    } else {
                                        echo anchor(current_lang() . '/member/activate/' . encode_id($row->id), lang('member_inactive'), 'class="status-pill inactive" title="Click to activate"');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php echo anchor(current_lang() . "/member/memberinfo/" . encode_id($row->id), ' <i class="fa fa-edit"></i> ' . lang('button_edit'), ' class="btn btn-primary btn-xs"'); ?>
                                        <?php if ($this->ion_auth->is_admin()) { ?>
                                            <a href="#" class="btn btn-danger btn-xs btn-delete-member" data-id="<?php echo encode_id($row->id); ?>" data-name="<?php echo htmlspecialchars($row->firstname . ' ' . $row->lastname, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-trash"></i> <?php echo lang('button_delete'); ?></a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa fa-users"></i>
                                    No members found for the current filters.
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="list-footer">
            <div class="pagination-wrap"><?php echo $links; ?></div>
            <div class="page-size-wrap"><?php page_selector(); ?></div>
        </div>
    </div>
</div>

<script>
(function() {
    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function initMemberSearchSuggest() {
        var input = document.getElementById('member-list-search');
        var box = document.getElementById('member-suggest-box');
        var form = input ? input.form : null;
        if (!input || !box || !form) {
            return;
        }

        var suggestUrl = '<?php echo site_url(current_lang() . '/member/autosuggest_member_list'); ?>';
        var timer = null;
        var xhr = null;
        var items = [];
        var activeIndex = -1;
        var suppressBlur = false;

        function hideBox() {
            box.classList.remove('open');
            box.innerHTML = '';
            items = [];
            activeIndex = -1;
        }

        function setActive(index) {
            var nodes = box.querySelectorAll('.member-suggest-item');
            activeIndex = index;
            for (var i = 0; i < nodes.length; i++) {
                if (i === activeIndex) {
                    nodes[i].classList.add('active');
                    if (nodes[i].scrollIntoView) {
                        nodes[i].scrollIntoView({ block: 'nearest' });
                    }
                } else {
                    nodes[i].classList.remove('active');
                }
            }
        }

        function choose(item) {
            if (!item) {
                return;
            }
            input.value = item.member_id || '';
            hideBox();
            form.submit();
        }

        function render(list) {
            items = list || [];
            activeIndex = items.length ? 0 : -1;
            if (!items.length) {
                box.innerHTML = '<div class="member-suggest-empty">No matching members</div>';
                box.classList.add('open');
                return;
            }

            var html = '';
            for (var i = 0; i < items.length; i++) {
                var row = items[i];
                html += '<button type="button" class="member-suggest-item' + (i === 0 ? ' active' : '') + '" data-index="' + i + '" role="option">' +
                    '<span class="suggest-id">' + escapeHtml(row.member_id) + '</span>' +
                    '<span class="suggest-name">' + escapeHtml(row.name) + '</span>' +
                    '<span class="suggest-status">' + escapeHtml(row.status) + '</span>' +
                    '</button>';
            }
            box.innerHTML = html;
            box.classList.add('open');
        }

        function fetchSuggestions() {
            var q = input.value.replace(/^\s+|\s+$/g, '');
            if (q.length < 1) {
                hideBox();
                return;
            }

            if (xhr && typeof xhr.abort === 'function') {
                xhr.abort();
            }

            var status = form.querySelector('[name="searchstatus"]');
            var type = form.querySelector('[name="searchmember"]');
            var url = suggestUrl +
                '?q=' + encodeURIComponent(q) +
                '&searchstatus=' + encodeURIComponent(status ? status.value : '') +
                '&searchmember=' + encodeURIComponent(type ? type.value : '');

            if (window.jQuery) {
                xhr = jQuery.getJSON(url)
                    .done(function(data) {
                        render(data || []);
                    })
                    .fail(function(jqXHR, textStatus) {
                        if (textStatus !== 'abort') {
                            hideBox();
                        }
                    });
                return;
            }

            xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) {
                    return;
                }
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        render(JSON.parse(xhr.responseText || '[]'));
                    } catch (e) {
                        hideBox();
                    }
                } else if (xhr.status !== 0) {
                    hideBox();
                }
            };
            xhr.send();
        }

        input.addEventListener('input', function() {
            clearTimeout(timer);
            timer = setTimeout(fetchSuggestions, 220);
        });

        input.addEventListener('keydown', function(e) {
            if (!box.classList.contains('open') || !items.length) {
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                choose(items[activeIndex]);
            } else if (e.key === 'Escape') {
                hideBox();
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(function() {
                if (!suppressBlur) {
                    hideBox();
                }
                suppressBlur = false;
            }, 150);
        });

        box.addEventListener('mousedown', function() {
            suppressBlur = true;
        });

        box.addEventListener('click', function(e) {
            var btn = e.target;
            while (btn && btn !== box && !btn.classList.contains('member-suggest-item')) {
                btn = btn.parentNode;
            }
            if (!btn || !btn.classList.contains('member-suggest-item')) {
                return;
            }
            var idx = parseInt(btn.getAttribute('data-index'), 10);
            choose(items[idx]);
        });

        form.addEventListener('change', function(e) {
            if (e.target && (e.target.name === 'searchstatus' || e.target.name === 'searchmember')) {
                if (input.value.replace(/^\s+|\s+$/g, '').length) {
                    fetchSuggestions();
                }
            }
        });
    }

    function initDeleteMembers() {
        var buttons = document.querySelectorAll('.btn-delete-member');
        var deleteBaseUrl = '<?php echo site_url(current_lang() . '/member/soft_delete_member'); ?>';
        var deleteTitle = "<?php echo addslashes(lang('button_delete')); ?>?";
        var deleteText = "<?php echo addslashes(lang('member_delete_confirm_text')); ?> ";
        var confirmText = "<?php echo addslashes(lang('yes')); ?>";
        var cancelText = "<?php echo addslashes(lang('button_cancel')); ?>";
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].addEventListener('click', function(e) {
                e.preventDefault();
                var memberId = this.getAttribute('data-id');
                var memberName = this.getAttribute('data-name');
                var deleteUrl = deleteBaseUrl + '/' + memberId;
                if (typeof swal !== 'undefined') {
                    swal({
                        title: deleteTitle,
                        text: deleteText + memberName,
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = deleteUrl;
                        }
                    });
                } else {
                    if (confirm(deleteText + memberName)) {
                        window.location.href = deleteUrl;
                    }
                }
            });
        }
    }

    function boot() {
        initMemberSearchSuggest();
        initDeleteMembers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
