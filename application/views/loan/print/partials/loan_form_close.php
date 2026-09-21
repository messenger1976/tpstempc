<?php
/**
 * Closing part of a printable loan form. Counterpart of
 * loan/print/partials/loan_form_open.php.
 *
 * Adds the "one sheet" fit routine: the form is scaled as little as possible so
 * its content fits the printable area of the long bond sheet, then export mode
 * (?autoprint=1) sends the page to the browser's print dialog. "Download PDF"
 * renders this same page headlessly, so the file matches the preview.
 */
$autoprint = $this->input->get('autoprint');
?>
</div><!-- /form-container -->

<script>
/**
 * Fit one form on one sheet.
 *
 * The form is never re-flowed or trimmed - only the root font size is reduced
 * (every Tailwind size is rem based, so the layout scales proportionally) until
 * the content fits the printable box of the sheet. Forms that already fit (the
 * disclosure, the pledge) keep their design size.
 *
 * The sheet metrics come from the CSS variables declared in loan_form_open.php,
 * and the container is pinned to the printable width with no card padding, so the
 * on-screen preview, the print dialog and the exported PDF are the same page.
 */
function tapstemcoFitSheet() {
    var MM_PER_IN = 25.4;
    var PX_PER_IN = 96;
    var MIN_ROOT = 8;

    var root = document.documentElement;
    var container = document.querySelector('.form-container');
    if (!root || !container) {
        return false;
    }

    var styles = window.getComputedStyle(root);
    var sheetWIn = parseFloat(styles.getPropertyValue('--sheet-w-in')) || 8.5;
    var sheetHIn = parseFloat(styles.getPropertyValue('--sheet-h-in')) || 13;
    var marginXMm = parseFloat(styles.getPropertyValue('--sheet-mx-mm')) || 8;
    var marginYMm = parseFloat(styles.getPropertyValue('--sheet-my-mm')) || 6;

    var printableW = Math.floor(sheetWIn * PX_PER_IN - 2 * (marginXMm / MM_PER_IN) * PX_PER_IN);
    var printableH = Math.floor(sheetHIn * PX_PER_IN - 2 * (marginYMm / MM_PER_IN) * PX_PER_IN);
    var baseRoot = parseFloat(styles.fontSize) || 16;

    function applySheet(px) {
        root.style.fontSize = px + 'px';
        container.style.width = printableW + 'px';
        container.style.maxWidth = printableW + 'px';
        container.style.padding = '0';
        container.style.boxShadow = 'none';
        container.style.border = '0';
        return container.getBoundingClientRect().height;
    }

    var height = applySheet(baseRoot);
    if (height > printableH) {
        // Largest root size whose content still fits the sheet height.
        var lo = MIN_ROOT;
        var hi = baseRoot;
        var mid;
        for (var i = 0; i < 14; i++) {
            mid = (lo + hi) / 2;
            if (applySheet(mid) <= printableH) {
                lo = mid;
            } else {
                hi = mid;
            }
        }
        height = applySheet(Math.floor(lo * 100) / 100);
    }

    window.__sheet = { height: height, printable: printableH, fits: height <= printableH };
    return true;
}

(function () {
    var attempts = 0;

    function imagesReady() {
        var imgs = document.images;
        for (var i = 0; i < imgs.length; i++) {
            if (!imgs[i].complete) { return false; }
        }
        return true;
    }

    function runFit() {
        try {
            tapstemcoFitSheet();
        } catch (e) {
            // Never let the fit routine break the form.
        }
        window.__sheetFitted = true;
    }

    function fire() {
        // The document title becomes the default file name in the save dialog.
        window.print();
    }

    function waitThenPrint() {
        attempts++;
        // Wait for the crest and the Tailwind CDN styles, then fit once more so a
        // late style pass cannot undo the measurement.
        if (document.readyState === 'complete' && imagesReady() && attempts > 2) {
            runFit();
            setTimeout(fire, 150);
            return;
        }
        if (attempts > 40) {
            runFit();
            fire();
            return;
        }
        setTimeout(waitThenPrint, 150);
    }

    function start() {
        runFit();
        // Tailwind's CDN build can still be applying classes; settle, then redo.
        setTimeout(runFit, 400);
<?php if ($autoprint): ?>
        waitThenPrint();
<?php endif; ?>
    }

    if (document.readyState === 'complete') {
        start();
    } else {
        window.addEventListener('load', start);
    }
})();
</script>

</body>
</html>
