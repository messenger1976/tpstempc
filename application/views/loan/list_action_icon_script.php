<style>
.action-icon-tooltip {
    position: fixed;
    z-index: 20000;
    display: none;
    max-width: 280px;
    padding: 6px 10px;
    border-radius: 4px;
    background: #2f4050;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
    pointer-events: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.action-icon-tooltip:after {
    content: '';
    position: absolute;
    left: 50%;
    top: 100%;
    margin-left: -5px;
    border: 5px solid transparent;
    border-top-color: #2f4050;
}
</style>
<script>
(function(){
    if (window.__tapstemcoActionIconTips) { return; }
    window.__tapstemcoActionIconTips = true;

    function buttonLabel(btn) {
        var label = (btn.getAttribute('data-tooltip') || btn.getAttribute('aria-label') || btn.getAttribute('title') || '').replace(/\s+/g, ' ').trim();
        if (label) { return label; }
        var clone = btn.cloneNode(true);
        var icons = clone.querySelectorAll('i, .fa, .glyphicon');
        for (var i = 0; i < icons.length; i++) {
            if (icons[i].parentNode) { icons[i].parentNode.removeChild(icons[i]); }
        }
        return (clone.textContent || '').replace(/\s+/g, ' ').trim();
    }

    function ensureTip() {
        var tip = document.getElementById('actionIconTooltip');
        if (tip) { return tip; }
        tip = document.createElement('div');
        tip.id = 'actionIconTooltip';
        tip.className = 'action-icon-tooltip';
        document.body.appendChild(tip);
        return tip;
    }

    function placeTip(tip, btn) {
        var rect = btn.getBoundingClientRect();
        var tipRect = tip.getBoundingClientRect();
        var left = rect.left + (rect.width / 2) - (tipRect.width / 2);
        var top = rect.top - tipRect.height - 8;
        if (left < 8) { left = 8; }
        if (left + tipRect.width > window.innerWidth - 8) {
            left = window.innerWidth - tipRect.width - 8;
        }
        if (top < 8) { top = rect.bottom + 8; }
        tip.style.left = Math.round(left) + 'px';
        tip.style.top = Math.round(top) + 'px';
        tip.style.display = 'block';
    }

    function hideTip() {
        var tip = document.getElementById('actionIconTooltip');
        if (tip) { tip.style.display = 'none'; }
    }

    function showTip(btn) {
        var label = buttonLabel(btn);
        if (!label) { return; }
        btn.setAttribute('data-tooltip', label);
        btn.setAttribute('aria-label', label);
        btn.removeAttribute('title');
        var tip = ensureTip();
        tip.textContent = label;
        tip.style.display = 'block';
        placeTip(tip, btn);
    }

    function enhance() {
        var buttons = document.querySelectorAll('.member-list-page .action-btns .btn');
        for (var i = 0; i < buttons.length; i++) {
            var btn = buttons[i];
            var label = buttonLabel(btn);
            if (!label) { continue; }
            btn.setAttribute('data-tooltip', label);
            btn.setAttribute('aria-label', label);
            btn.removeAttribute('title');
        }
    }

    function start() {
        enhance();
        document.addEventListener('mouseover', function(e) {
            var btn = e.target.closest ? e.target.closest('.member-list-page .action-btns .btn') : null;
            if (btn) { showTip(btn); }
        });
        document.addEventListener('mouseout', function(e) {
            var btn = e.target.closest ? e.target.closest('.member-list-page .action-btns .btn') : null;
            if (!btn) { return; }
            var next = e.relatedTarget;
            if (next && btn.contains(next)) { return; }
            hideTip();
        });
        document.addEventListener('scroll', hideTip, true);
        window.addEventListener('resize', hideTip);
        var $ = window.jQuery;
        if ($ && $.fn.dataTable) {
            $(document).on('draw.dt', function(){ enhance(); });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
