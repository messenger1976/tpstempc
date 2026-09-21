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
