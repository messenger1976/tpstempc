(function() {
    var TIMER_MS = 2500;

    function labels() {
        return window.TAPSTEMCO_MEMBER_SEARCH || {};
    }

    function findSearchControl(el) {
        while (el && el !== document && el !== document.body) {
            if (el.id === 'search_pid' || el.id === 'search_mid') {
                return el;
            }
            el = el.parentNode;
        }
        return null;
    }

    function trimValue(value) {
        return String(value == null ? '' : value).replace(/^\s+|\s+$/g, '');
    }

    function showTimedDialog(title, message) {
        if (typeof swal !== 'function') {
            window.alert(message);
            return;
        }
        swal({
            title: title,
            text: message,
            type: 'warning',
            timer: TIMER_MS,
            showConfirmButton: false
        });
    }

    document.addEventListener('click', function(e) {
        var control = findSearchControl(e.target);
        if (!control) {
            return;
        }

        var isPid = control.id === 'search_pid';
        var i18n = labels();
        var input = document.getElementById(isPid ? 'pid' : 'member_id');
        var value = input ? trimValue(input.value) : '';
        if (value !== '') {
            return;
        }

        if (typeof e.preventDefault === 'function') {
            e.preventDefault();
        }
        if (typeof e.stopPropagation === 'function') {
            e.stopPropagation();
        }
        if (typeof e.stopImmediatePropagation === 'function') {
            e.stopImmediatePropagation();
        }

        showTimedDialog(
            isPid ? (i18n.pidTitle || 'System Member ID') : (i18n.midTitle || 'Member ID'),
            isPid ? (i18n.pidEmpty || 'Please, Fill System Member ID') : (i18n.midEmpty || 'Please, Fill Member ID')
        );
    }, true);
})();
