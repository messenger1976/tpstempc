<?php

/**
 * Shared TCPDF helpers for the printable loan forms
 * (TPSTEMPC-12 Application for Loan, TPSTEMPC-13 Co-Makers Statement /
 *  Promissory Note, TPSTEMPC-13 Pledge & Authority).
 *
 * Plain functions (not a class) so the include files under
 * application/controllers/pdf/ stay framework free. The TCPDF instance is
 * always passed in as the first argument.
 *
 * Layout notes
 * ------------
 * - The letterhead is drawn with TCPDF primitives (start_pdf(FALSE) disables
 *   TCPDF's own header) so it can carry the badge, the cooperative block and
 *   the form number at fixed positions.
 * - Everything below the letterhead is one writeHTML() call. Sizes in CSS use
 *   "pt" because "px" values are scaled by the document scale factor (they are
 *   divided by image_scale * k), which makes px unpredictable.
 * - The source forms were built with flexbox/grid; TCPDF has no layout engine,
 *   so the same structure is expressed with tables, cell borders and inline
 *   text styles. Underlined fill-in fields inside a paragraph use <u>, because
 *   inline elements do not get borders.
 */

if (!function_exists('loan_form_pdf_start')) {

    /**
     * Standard bootstrap for the loan form documents.
     */
    function loan_form_pdf_start($pdf, $orientation = 'P') {
        $pdf->set_subtitle('');
        $pdf->hidefooter(FALSE);
        if (strtoupper($orientation) === 'L') {
            $pdf->changepageformat('L');
        }
        $pdf->start_pdf(FALSE);
        $pdf->SetSubject('TAPSTEMCO Loan Form');
        $pdf->SetKeywords('TAPSTEMCO, loan form, TPSTEMPC-12, TPSTEMPC-13');
        $pdf->setPrintHeader(FALSE);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8.5);
    }
}

if (!function_exists('loan_form_pdf_body_y')) {

    /**
     * First body position after a page break (top margin minus the offset used
     * for the hand drawn letterhead).
     */
    function loan_form_pdf_body_y($pdf) {
        $margins = $pdf->getMargins();
        return $margins['top'] - 12;
    }
}

/* ---------------------------------------------------------------------------
 * HTML helpers
 *
 * The forms are rendered with writeHTML() from table based HTML so the printed
 * sheet follows the layout of the source documents. Those were built with
 * flexbox/grid, which TCPDF does not support, so the same structure is
 * expressed with nested free tables, borders and inline text styles.
 * ------------------------------------------------------------------------- */

if (!function_exists('lf_h')) {

    function lf_h($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('lf_v')) {

    /**
     * Cell content: the value, or a blank space so an empty field still draws
     * its underline.
     */
    function lf_v($value) {
        $value = trim((string) $value);
        return ($value === '') ? '&nbsp;' : lf_h($value);
    }
}

if (!function_exists('lf_vu')) {

    /**
     * Inline (underlined) content. Spaces become non breaking spaces so the
     * underline is drawn as one continuous line.
     */
    function lf_vu($value, $blank = 10) {
        $value = trim((string) $value);
        if ($value === '') {
            return str_repeat('&nbsp;', $blank);
        }
        return str_replace(' ', '&nbsp;', lf_h($value));
    }
}

if (!function_exists('lf_underline')) {

    /**
     * Fill-in field for use inside a flowing paragraph.
     */
    function lf_underline($value, $blank = 10) {
        return '<u>' . lf_vu($value, $blank) . '</u>';
    }
}

if (!function_exists('lf_checkbox_cell')) {

    function lf_checkbox_cell($checked, $extra = '') {
        return '<td style="width:4mm; border:1px solid #374151; text-align:center; font-size:7pt;' . $extra . '">'
            . ($checked ? 'X' : '&nbsp;') . '</td>';
    }
}

if (!function_exists('lf_checkbox_row')) {

    /**
     * Table row of check boxes with their captions.
     * $items = array(array('label' => 'Salary Loan', 'checked' => TRUE), ...)
     */
    function lf_checkbox_row($items, $label_w = '30mm') {
        $html = '<tr>';
        foreach ($items as $item) {
            $html .= lf_checkbox_cell(!empty($item['checked']));
            $html .= '<td style="width:' . $label_w . '; padding-left:1.5mm;">' . lf_h($item['label']) . '</td>';
        }
        return $html . '</tr>';
    }
}

if (!function_exists('lf_signature_table')) {

    /**
     * Signature areas: a rule to sign on with the printed name above it and the
     * caption underneath. Columns without a caption get no rule, which is how a
     * single signature is centred with empty side columns.
     *
     * $columns = array(array('width' => '48%', 'caption' => '...', 'name' => '...'), ...)
     */
    function lf_signature_table($columns, $gap = '4%', $rule_height = '8mm', $font_size = '7pt') {
        $gap_cell = '<td style="width:' . $gap . '">&nbsp;</td>';
        $rules = '';
        $captions = '';
        $total = count($columns);
        $index = 0;

        foreach ($columns as $col) {
            $caption = isset($col['caption']) ? trim((string) $col['caption']) : '';
            $name = isset($col['name']) ? trim((string) $col['name']) : '';
            $width = isset($col['width']) ? $col['width'] : '';

            $rules .= '<td style="width:' . $width . '; height:' . $rule_height . '; text-align:center;'
                . ' font-size:' . $font_size . '; font-weight:bold;'
                . ($caption !== '' ? ' border-bottom:1px solid #374151;' : '') . '" valign="bottom">'
                . lf_v($name) . '</td>';
            $captions .= '<td style="text-align:center; font-size:' . $font_size . ';">' . lf_h($caption) . '</td>';

            $index++;
            if ($index < $total) {
                $rules .= $gap_cell;
                $captions .= $gap_cell;
            }
        }

        return '<table style="width:100%;">'
            . '<tr>' . $rules . '</tr>'
            . '<tr>' . $captions . '</tr>'
            . '</table>';
    }
}

if (!function_exists('loan_form_pdf_letterhead')) {

    /**
     * Cooperative letterhead, matching the header of the printed forms: badge on
     * the left, centred cooperative name/address/registration number, the form
     * number on the right and a light rule underneath. An optional reference
     * line links the sheet back to the loan record. The cursor is left at the
     * start of the body.
     */
    function loan_form_pdf_letterhead($pdf, $company, $form_no, $reference = '') {
        $margins = $pdf->getMargins();
        $page_w = $pdf->getPageWidth();
        $top = loan_form_pdf_body_y($pdf);

        $coop_name = (defined('TAPSTEMCO_FORM_COOP_NAME'))
            ? TAPSTEMCO_FORM_COOP_NAME
            : (($company && !empty($company->name)) ? $company->name : 'Multipurpose Cooperative');
        $coop_address = (defined('TAPSTEMCO_FORM_COOP_ADDRESS'))
            ? TAPSTEMCO_FORM_COOP_ADDRESS
            : (($company && !empty($company->address)) ? $company->address : '');
        $coop_reg = defined('TAPSTEMCO_FORM_REG_NO') ? TAPSTEMCO_FORM_REG_NO : '';

        /* ------------------------------------------------------------ badge */
        $cx = $margins['left'] + 11;
        $cy = $top + 11;

        $logo_file = (defined('TAPSTEMCO_FORM_LOGO') && TAPSTEMCO_FORM_LOGO !== '')
            ? TAPSTEMCO_FORM_LOGO
            : (($company && !empty($company->logo)) ? $company->logo : '');
        $logo_path = ($logo_file !== '' && defined('FCPATH')) ? FCPATH . 'logo/' . $logo_file : '';
        // TCPDF::Image() dies on an unreadable file, so the crest is used only when
        // getimagesize() confirms a real raster image.
        $logo_ok = ($logo_path !== '' && @getimagesize($logo_path));

        if ($logo_ok) {
            $pdf->Image($logo_path, $cx - 10.5, $cy - 10.5, 21, 21, '', '', '', TRUE, 300, '', FALSE, FALSE, 0, FALSE, FALSE, FALSE);
        } else {
            /* Fallback: the cooperative badge drawn with primitives. */
            $pdf->SetLineWidth(0.7);
            $pdf->SetDrawColor(22, 101, 52);
            $pdf->Ellipse($cx, $cy, 10.5, 10.5, 0, 0, 360, 'D');
            $pdf->SetLineWidth(0.2);
            $pdf->SetDrawColor(234, 179, 8);
            $pdf->Ellipse($cx, $cy, 9.1, 9.1, 0, 0, 360, 'D');

            $pdf->SetFont('helvetica', 'B', 5.4);
            $pdf->SetTextColor(220, 38, 38);
            $pdf->SetXY($cx - 8.6, $cy - 3.2);
            $pdf->Cell(17.2, 2.6, 'TAPSTEMCO', 0, 2, 'C');
            $pdf->SetFont('helvetica', '', 3.8);
            $pdf->SetTextColor(22, 101, 52);
            $pdf->SetX($cx - 8.6);
            $pdf->Cell(17.2, 2.6, 'TEACHERS & EMPLOYEES', 0, 2, 'C');
        }

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);

        /* ------------------------------------------ centred name and address */
        $title_x = $margins['left'] + 24;
        $title_w = $page_w - $margins['right'] - 34 - $title_x;

        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->SetXY($title_x, $top + 1.5);
        $pdf->MultiCell($title_w, 4.2, strtoupper($coop_name), 0, 'C', FALSE, 1);

        $pdf->SetFont('helvetica', '', 7.5);
        if ($coop_address !== '') {
            $pdf->SetX($title_x);
            $pdf->MultiCell($title_w, 3.3, $coop_address, 0, 'C', FALSE, 1);
        }
        if ($coop_reg !== '') {
            $pdf->SetX($title_x);
            $pdf->MultiCell($title_w, 3.3, 'CDA Registration/Confirmation No. ' . $coop_reg, 0, 'C', FALSE, 1);
        }

        /* -------------------------------------------------------- form number */
        if (trim((string) $form_no) !== '') {
            $pdf->SetFont('helvetica', 'B', 7.5);
            $pdf->SetXY($page_w - $margins['right'] - 32, $top + 2);
            $pdf->Cell(32, 4, 'Form No. ' . $form_no, 0, 0, 'R');
        }

        /* -------------------------------------------------- rule and options */
        $y = max($pdf->GetY(), $top + 21);
        $pdf->SetDrawColor(209, 213, 219);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($margins['left'], $y + 1.5, $page_w - $margins['right'], $y + 1.5);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(0, 0, 0);

        $y = $y + 3;
        if ($reference !== '') {
            $pdf->SetFont('helvetica', 'I', 6.5);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->SetXY($margins['left'], $y);
            $pdf->Cell($page_w - $margins['left'] - $margins['right'], 3, $reference, 0, 1, 'R');
            $pdf->SetTextColor(0, 0, 0);
            $y = $pdf->GetY();
        }

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetY($y + 3);
        $pdf->SetX($margins['left']);
    }
}

if (!function_exists('lf_render')) {

    /**
     * Write the form body and stream the PDF to the browser.
     */
    function lf_render($pdf, $html, $filename) {
        $margins = $pdf->getMargins();
        $pdf->SetX($margins['left']);
        $pdf->writeHTML($html, TRUE, FALSE, TRUE, FALSE, '');
        $pdf->Output($filename, 'I');
    }
}

if (!function_exists('loan_form_pdf_binary')) {

    /**
     * Locate an HTML-to-PDF renderer: a Chromium/Edge binary or wkhtmltopdf.
     *
     * @return string|null executable path, or null when none is installed
     */
    function loan_form_pdf_binary() {
        static $binary = null;
        static $checked = false;
        if ($checked) {
            return $binary;
        }
        $checked = true;

        $candidates = (DIRECTORY_SEPARATOR === '\\')
            ? array(
                'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
                'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
                'C:\Program Files\Google\Chrome\Application\chrome.exe',
                'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
            )
            : array(
                '/usr/bin/google-chrome',
                '/usr/bin/google-chrome-stable',
                '/usr/bin/chromium',
                '/usr/bin/chromium-browser',
                '/usr/bin/microsoft-edge',
                '/snap/bin/chromium',
                '/usr/local/bin/wkhtmltopdf',
                '/usr/bin/wkhtmltopdf',
            );

        foreach ($candidates as $path) {
            if (is_file($path)) {
                $binary = $path;
                return $binary;
            }
        }
        return null;
    }
}

if (!function_exists('_loan_form_run_command')) {

    /**
     * Run a shell command with a wall-clock timeout. Output is written to a log
     * file rather than a pipe, so a chatty child process cannot block the parent.
     *
     * @return bool whether the command was launched at all
     */
    function _loan_form_run_command($cmd, $log_file, $timeout_seconds = 45) {
        // cmd.exe drops the first and last quote of a command line that starts with
        // a quote, which breaks a quoted executable path containing spaces
        // ("C:\Program Files (x86)\..."). One extra pair of quotes keeps the line
        // intact; it is harmless for commands that do not start with a quote.
        if (DIRECTORY_SEPARATOR === '\\' && substr($cmd, 0, 1) === '"') {
            $cmd = '"' . $cmd . '"';
        }

        if (function_exists('proc_open')) {
            $null_device = (DIRECTORY_SEPARATOR === '\\') ? 'NUL' : '/dev/null';
            $descriptors = array(
                0 => array('file', $null_device, 'r'),
                1 => array('file', $log_file, 'a'),
                2 => array('file', $log_file, 'a'),
            );
            $pipes = array();
            $process = @proc_open($cmd, $descriptors, $pipes);
            if (is_resource($process)) {
                $deadline = microtime(TRUE) + max(5, (int) $timeout_seconds);
                while (microtime(TRUE) < $deadline) {
                    $status = proc_get_status($process);
                    if (empty($status['running'])) {
                        break;
                    }
                    usleep(200000);
                }
                $status = proc_get_status($process);
                if (!empty($status['running'])) {
                    proc_terminate($process, 9);
                }
                proc_close($process);
                return TRUE;
            }
        }
        if (function_exists('exec')) {
            @exec($cmd . ' 2>&1', $unused_output, $unused_status);
            return TRUE;
        }
        return FALSE;
    }
}

if (!function_exists('_loan_form_remove_dir')) {

    /**
     * Remove a throw-away directory (e.g. a headless browser profile). Refuses to
     * touch anything outside the system temp directory.
     */
    function _loan_form_remove_dir($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $temp_root = realpath(sys_get_temp_dir());
        $target = realpath($dir);
        if ($temp_root === false || $target === false || strpos($target, $temp_root) !== 0) {
            return;
        }
        $items = @scandir($target);
        if (is_array($items)) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $path = $target . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                    _loan_form_remove_dir($path);
                } else {
                    @unlink($path);
                }
            }
        }
        @rmdir($target);
    }
}

if (!function_exists('loan_form_html_to_pdf')) {

    /**
     * Render an HTML string to a PDF file with the headless browser, so a
     * downloaded form keeps the exact layout of the HTML preview. The sheet size
     * comes from the form's own @page rule (long bond / Folio, 8.5in x 13in).
     *
     * @param string $html fully rendered form page
     * @return string|false absolute path of the PDF (the caller deletes it), or false
     */
    function loan_form_html_to_pdf($html) {
        if (!is_string($html) || trim($html) === '') {
            return false;
        }
        $binary = loan_form_pdf_binary();
        if ($binary === null) {
            return false;
        }

        $dir = sys_get_temp_dir();
        if ($dir === '' || !is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        $base = $dir . DIRECTORY_SEPARATOR . 'loanform_' . getmypid() . '_' . mt_rand(1000, 9999);
        $html_file = $base . '.html';
        $pdf_file = $base . '.pdf';
        $log_file = $base . '.log';
        $profile_dir = $base . '_profile';

        if (file_put_contents($html_file, $html) === false) {
            return false;
        }

        if (stripos($binary, 'wkhtmltopdf') !== false) {
            // Long bond (Folio): 8.5in x 13in = 216mm x 330mm.
            $cmd = escapeshellarg($binary) . ' --quiet --print-media-type --enable-local-file-access'
                . ' --page-width 216mm --page-height 330mm'
                . ' --margin-top 8mm --margin-bottom 8mm --margin-left 8mm --margin-right 8mm'
                . ' ' . escapeshellarg($html_file) . ' ' . escapeshellarg($pdf_file);
        } else {
            // --virtual-time-budget gives the Tailwind CDN pass time to finish
            // before the page is printed.
            $url = 'file:///' . str_replace('\\', '/', $html_file);
            $cmd = escapeshellarg($binary)
                . ' --headless=new --disable-gpu --no-sandbox --hide-scrollbars --disable-extensions'
                . ' --no-pdf-header-footer --run-all-compositor-stages-before-draw'
                . ' --virtual-time-budget=10000'
                . ' --user-data-dir=' . escapeshellarg($profile_dir)
                . ' --print-to-pdf=' . escapeshellarg($pdf_file)
                . ' ' . escapeshellarg($url);
        }

        _loan_form_run_command($cmd, $log_file, 45);

        @unlink($html_file);
        _loan_form_remove_dir($profile_dir);

        if (is_file($pdf_file) && filesize($pdf_file) > 0) {
            @unlink($log_file);
            return $pdf_file;
        }

        // Keep the browser's own message in the application log: without it a
        // failed launch (missing binary, quoting, sandbox) is invisible.
        if (function_exists('log_message') && is_file($log_file)) {
            log_message('error', 'Loan form PDF: headless render failed. ' . substr(trim((string) file_get_contents($log_file)), 0, 500));
        }
        @unlink($log_file);
        @unlink($pdf_file);
        return false;
    }
}
