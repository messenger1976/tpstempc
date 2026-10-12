/*
 * Suggest-as-you-type for the Cash Receipt "Received From" payer search.
 * Suggestion rows reuse the result-table button classes (.select-payer-member,
 * .select-loan-member, .select-payer-name) so the page's existing delegated
 * click handlers perform the selection.
 */
(function(window) {
    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function init(options) {
        var $ = window.jQuery;
        if (!$) {
            return;
        }
        var opts = $.extend({
            input: '#receivedFromSearchKey',
            box: '#receivedFromSuggestBox',
            typeSelect: '#received_from_type',
            searchButton: '#doReceivedFromSearch',
            results: '#receivedFromSearchResults',
            urls: {},
            minChars: 2,
            delay: 250,
            emptyText: {}
        }, options || {});

        var $input = $(opts.input);
        var $box = $(opts.box);
        if (!$input.length || !$box.length) {
            return;
        }

        var timer = null;
        var xhr = null;
        var activeIndex = -1;
        var lastQuery = '';

        function currentType() {
            return $(opts.typeSelect).val() || '';
        }

        function hideBox() {
            $box.removeClass('open').empty();
            activeIndex = -1;
        }

        function items() {
            return $box.find('.payer-suggest-item');
        }

        function setActive(index) {
            var $items = items();
            if (!$items.length) {
                activeIndex = -1;
                return;
            }
            activeIndex = Math.max(0, Math.min(index, $items.length - 1));
            $items.removeClass('active');
            var node = $items.eq(activeIndex).addClass('active').get(0);
            if (node && node.scrollIntoView) {
                node.scrollIntoView({ block: 'nearest' });
            }
        }

        function rowButton(type, row) {
            var id = '';
            var name = '';
            var attrs = '';
            var cls = '';
            if (type === 'supplier' || type === 'customer') {
                id = type === 'supplier' ? row.supplierid : row.customerid;
                name = row.name || '';
                cls = 'select-payer-name';
                attrs = ' data-kind="' + type + '"';
            } else {
                id = row.member_id || row.PID || '';
                name = row.fullname || '';
                cls = type === 'loan_repayment' ? 'select-loan-member' : 'select-payer-member';
                attrs = ' data-pid="' + escapeHtml(row.PID) + '" data-member-id="' + escapeHtml(row.member_id) + '"';
            }
            var sub = '';
            if (type === 'member' || type === 'loan_repayment') {
                sub = row.PID ? 'PID ' + row.PID : '';
            }
            return '<button type="button" class="payer-suggest-item ' + cls + '" role="option"' + attrs +
                ' data-fullname="' + escapeHtml(name) + '">' +
                '<span class="suggest-id">' + escapeHtml(id) + '</span>' +
                '<span class="suggest-name">' + escapeHtml(name) + '</span>' +
                (sub ? '<span class="suggest-sub">' + escapeHtml(sub) + '</span>' : '') +
                '</button>';
        }

        function render(type, rows, errorText) {
            if (!rows || !rows.length) {
                $box.html('<div class="payer-suggest-empty">' + escapeHtml(errorText || opts.emptyText[type] || 'No matches') + '</div>').addClass('open');
                activeIndex = -1;
                return;
            }
            var html = '';
            $.each(rows, function(i, row) {
                html += rowButton(type, row);
            });
            $box.html(html).addClass('open');
            setActive(0);
        }

        function fetchSuggestions() {
            var type = currentType();
            var url = opts.urls[type];
            var q = $.trim($input.val() || '');
            if (!url || q.length < opts.minChars) {
                hideBox();
                return;
            }
            lastQuery = q;
            if (xhr && typeof xhr.abort === 'function') {
                xhr.abort();
            }
            xhr = $.ajax({ url: url, type: 'GET', data: { key: q }, dataType: 'json' })
                .done(function(response) {
                    if (q !== lastQuery || type !== currentType()) {
                        return;
                    }
                    if (response && response.success === 'Y') {
                        render(type, response.data || []);
                    } else {
                        render(type, [], null);
                    }
                })
                .fail(function(jqXHR, textStatus) {
                    if (textStatus !== 'abort') {
                        hideBox();
                    }
                });
        }

        function choose($item) {
            if (!$item || !$item.length) {
                return;
            }
            $input.val($item.data('fullname') || $input.val());
            $item.trigger('click');
        }

        $input.on('input', function() {
            clearTimeout(timer);
            timer = setTimeout(fetchSuggestions, opts.delay);
        });

        $input.on('focus', function() {
            if (!$box.hasClass('open') && $.trim($input.val() || '').length >= opts.minChars && !$(opts.results).children().length) {
                fetchSuggestions();
            }
        });

        $input.on('keydown', function(e) {
            if (!$box.hasClass('open')) {
                return;
            }
            var key = e.key || '';
            if (key === 'ArrowDown' || e.which === 40) {
                e.preventDefault();
                setActive(activeIndex + 1);
            } else if (key === 'ArrowUp' || e.which === 38) {
                e.preventDefault();
                setActive(activeIndex - 1);
            } else if ((key === 'Enter' || e.which === 13) && activeIndex >= 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                choose(items().eq(activeIndex));
            } else if (key === 'Escape' || e.which === 27) {
                hideBox();
            }
        });

        $input.on('blur', function() {
            setTimeout(hideBox, 180);
        });

        $box.on('mousedown', function(e) {
            e.preventDefault();
        });

        $box.on('click', '.payer-suggest-item', function() {
            $input.val($(this).data('fullname') || $input.val());
            setTimeout(hideBox, 0);
        });

        $(opts.searchButton).on('click', function() {
            clearTimeout(timer);
            hideBox();
        });

        $(opts.typeSelect).on('change', function() {
            clearTimeout(timer);
            hideBox();
        });
    }

    window.CashReceiptPayerSuggest = { init: init };
})(window);
