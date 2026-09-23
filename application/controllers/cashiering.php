<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cashiering extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        if (!can_access_cashiering()) {
            $this->session->set_flashdata('warning', 'Access denied. Cashiering is limited to Cashier and Admin roles.');
            redirect(current_lang() . '/dashboard', 'refresh');
        }

        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = 'Cashiering';
        $this->lang->load('finance');
        $this->load->model('cashiering_model');
        $this->load->model('cash_receipt_model');
        $this->load->model('cash_disbursement_model');
    }

    public function index() {
        redirect(current_lang() . '/cashiering/dashboard', 'refresh');
    }

    public function dashboard() {
        $date = $this->input->get('date') ?: date('Y-m-d');
        $this->data['title'] = lang('cashiering_dashboard');
        $this->data['report_date'] = $date;
        $this->data['summary'] = $this->cashiering_model->get_daily_summary($date);
        $this->data['recent_reports'] = $this->cashiering_model->get_recent_reports(10);
        $this->data['content'] = 'cashiering/dashboard';
        $this->load->view('template', $this->data);
    }

    public function cash_count_sheet($report_id = null) {
        $this->data['title'] = lang('cashiering_cash_count_sheet');
        $report_date = $this->input->get('date') ?: date('Y-m-d');
        $this->data['report_date'] = $report_date;

        if ($this->input->post()) {
            $denoms = cashiering_denominations();

            /* Rebuild the nested grid from the flat posted field names. */
            $grid = array('bills' => array(), 'coins' => array());
            foreach ($denoms['bills'] as $denom) {
                $grid['bills'][$denom['key']] = array(
                    'bundles' => $this->input->post('bundles_' . $denom['key']),
                    'loose' => $this->input->post('loose_' . $denom['key']),
                );
            }
            foreach ($denoms['coins'] as $denom) {
                $grid['coins'][$denom['key']] = array(
                    'rolls' => $this->input->post('rolls_' . $denom['key']),
                    'loose' => $this->input->post('loose_' . $denom['key']),
                );
            }
            $grid['other'] = array(
                'checks' => $this->input->post('checks'),
                'advances' => $this->input->post('advances'),
                'others' => $this->input->post('others'),
            );
            $grid['meta'] = array(
                'fund_name' => trim((string) $this->input->post('fund_name')),
                'accountable_person' => trim((string) $this->input->post('accountable_person')),
                'counted_by' => trim((string) $this->input->post('counted_by')),
                'other_funds' => trim((string) $this->input->post('other_funds')),
                'others_specify' => trim((string) $this->input->post('others_specify')),
            );

            $breakdown = $this->cashiering_model->normalize_breakdown($grid);
            $totals = $this->cashiering_model->breakdown_totals($breakdown);

            /*
             * Cash on hand per books is what the count is reconciled against. It
             * defaults to the system figure (opening + receipts - disbursements) but
             * the cashier may override it, so it is taken from the form when present.
             */
            $report_date = $this->input->post('report_date') ?: $report_date;
            $day = $this->cashiering_model->get_daily_summary($report_date);
            $posted_on_hand = $this->input->post('cash_on_hand_books');
            $cash_on_hand = ($posted_on_hand === NULL || $posted_on_hand === '')
                ? floatval($day['expected_cash'])
                : floatval(str_replace(',', '', $posted_on_hand));

            $payload = array(
                'report_date' => $report_date,
                'cashier_name' => $grid['meta']['accountable_person'] !== ''
                    ? $grid['meta']['accountable_person']
                    : trim((string) $this->input->post('cashier_name')),
                'beginning_cash' => floatval($day['beginning_cash']),
                'cash_in' => floatval($day['cash_in']),
                'cash_out' => floatval($day['cash_out']),
                'expected_cash' => $cash_on_hand,
                'cash_counted' => $totals['currency_total'],
                'other_items' => $totals['cash_items'],
                'grand_total' => $totals['grand_total'],
                'over_short' => $totals['grand_total'] - $cash_on_hand,
                'notes' => trim((string) $this->input->post('notes')),
                'cash_breakdown' => json_encode($breakdown),
                'created_by' => current_user()->id,
                'PIN' => current_user()->PIN,
                'created_at' => date('Y-m-d H:i:s'),
            );

            $report_id = $this->cashiering_model->save_report($payload);
            if ($report_id) {
                $this->session->set_flashdata('message', lang('cashiering_report_saved'));
                redirect(current_lang() . '/cashiering/cash_count_sheet/' . $report_id . '?date=' . urlencode($payload['report_date']), 'refresh');
            }

            $this->session->set_flashdata('warning', lang('cashiering_report_fail'));
        }

        if (!empty($report_id)) {
            $this->data['report'] = $this->cashiering_model->get_report($report_id);
        } else {
            $this->data['report'] = $this->cashiering_model->get_report_for_date($report_date);
        }

        if (!empty($this->data['report'])) {
            $report_date = $this->data['report']->report_date;
            $breakdown = $this->cashiering_model->normalize_breakdown($this->data['report']->cash_breakdown);
        } else {
            $breakdown = $this->cashiering_model->normalize_breakdown(array());
        }

        $this->data['report_date'] = $report_date;
        $this->data['breakdown'] = $breakdown;
        $this->data['totals'] = $this->cashiering_model->breakdown_totals($breakdown);
        $this->data['summary'] = $this->cashiering_model->get_daily_summary($report_date);
        $this->data['is_void'] = $this->cashiering_model->is_void($this->data['report']);
        $this->data['can_void'] = $this->cashiering_model->user_can_void_report($this->data['report']);
        $this->data['voided_by_name'] = $this->_voided_by_name($this->data['report']);
        $this->data['content'] = 'cashiering/cash_count_sheet';
        $this->load->view('template', $this->data);
    }

    /**
     * Void a submitted count sheet.
     *
     * The row is kept and marked void with a reason; nothing is deleted, because the
     * sheet is the evidence of what was in the drawer. An admin may void any sheet, a
     * cashier only their own report of the current day (see
     * cashiering_model::user_can_void_report()).
     */
    public function void_cash_count($report_id = null) {
        $report = !empty($report_id) ? $this->cashiering_model->get_report($report_id) : null;
        if (!$report) {
            $this->session->set_flashdata('warning', lang('cashiering_no_report'));
            redirect(current_lang() . '/cashiering/cash_count_sheet', 'refresh');
            return;
        }

        if (!$this->cashiering_model->user_can_void_report($report)) {
            $this->session->set_flashdata('warning', lang('cashiering_void_not_allowed'));
            redirect(current_lang() . '/cashiering/cash_count_sheet/' . (int) $report->id, 'refresh');
            return;
        }

        if ($this->input->post()) {
            $reason = trim((string) $this->input->post('void_reason'));

            if ($reason === '') {
                $this->data['warning'] = lang('cashiering_void_reason_required');
            } elseif ($this->cashiering_model->void_report($report->id, $reason)) {
                $this->session->set_flashdata('message', lang('cashiering_report_voided'));
                // The sheet for the day is now blank again, ready for a correct count.
                redirect(current_lang() . '/cashiering/cash_count_sheet?date=' . urlencode($report->report_date), 'refresh');
                return;
            } else {
                $this->data['warning'] = lang('cashiering_report_void_fail');
            }
        }

        $this->data['title'] = lang('cashiering_void_title');
        $this->data['report'] = $report;
        $this->data['breakdown'] = $this->cashiering_model->normalize_breakdown($report->cash_breakdown);
        $this->data['totals'] = $this->cashiering_model->breakdown_totals($this->data['breakdown']);
        $this->data['content'] = 'cashiering/void_cash_count';
        $this->load->view('template', $this->data);
    }

    /**
     * Name of the user who voided a sheet, for the void audit line. Empty when the
     * sheet is active or the user cannot be resolved.
     */
    private function _voided_by_name($report) {
        if (!$this->cashiering_model->is_void($report) || empty($report->voided_by)) {
            return '';
        }
        return $this->cashiering_model->user_name($report->voided_by);
    }

    /**
     * Printable HTML view of a submitted sheet. The Print button opens this in a
     * new tab and the PDF export renders it, so screen and PDF show one sheet.
     */
    public function cash_count_sheet_print($report_id = null, $autoprint = 0) {
        $report = !empty($report_id) ? $this->cashiering_model->get_report($report_id) : null;
        if (!$report) {
            $this->session->set_flashdata('warning', lang('cashiering_no_report'));
            redirect(current_lang() . '/cashiering/cash_count_sheet', 'refresh');
            return;
        }

        $this->data = array_merge($this->data, $this->_sheet_print_data(
            $report,
            $this->cashiering_model->normalize_breakdown($report->cash_breakdown)
        ));
        $this->data['autoprint'] = !empty($autoprint);
        $this->data['pdf_url'] = site_url(current_lang() . '/cashiering/export_cash_count_pdf/' . (int) $report->id);
        $this->load->view('cashiering/print_cash_count_sheet', $this->data);
    }

    /**
     * View data shared by the browser preview and the PDF export.
     *
     * $report is a cashiering_reports row or a draft object built by
     * export_cash_count_pdf_draft(); $breakdown is already normalised. Keeping the
     * two callers on one helper is what stops the preview and the PDF from drifting.
     */
    private function _sheet_print_data($report, $breakdown, $is_draft = FALSE) {
        return array(
            'report' => $report,
            'breakdown' => $breakdown,
            'totals' => $this->cashiering_model->breakdown_totals($breakdown),
            'voided_by_name' => $this->_voided_by_name($report),
            'is_draft' => $is_draft,
            'sheet_url' => site_url(current_lang() . '/cashiering/cash_count_sheet'),
            /* Read by both print views; only the Print tab turns it on. */
            'autoprint' => FALSE,
        );
    }

    /**
     * PDF download of a submitted sheet.
     */
    public function export_cash_count_pdf($report_id) {
        $report = $this->cashiering_model->get_report($report_id);
        if (!$report) {
            $this->session->set_flashdata('warning', lang('cashiering_no_report'));
            redirect(current_lang() . '/cashiering/cash_count_sheet', 'refresh');
            return;
        }

        $data = $this->_sheet_print_data(
            $report,
            $this->cashiering_model->normalize_breakdown($report->cash_breakdown)
        );
        $this->_cash_count_pdf_output(
            $data,
            'cash_count_sheet_' . date('Ymd', strtotime($report->report_date)) . '.pdf'
        );
    }

    /**
     * PDF of the sheet exactly as it stands on screen, WITHOUT saving it.
     *
     * The Export to PDF button on an unsubmitted sheet posts the form here rather
     * than to cash_count_sheet(), so a cashier can check the printed layout - or hand
     * it to the auditor - before committing the count. Nothing is written: the same
     * flat field names are read, totalled and printed, and the sheet is stamped
     * "Draft - not yet submitted" so it cannot be mistaken for the filed copy.
     */
    public function export_cash_count_pdf_draft() {
        if (!$this->input->post()) {
            redirect(current_lang() . '/cashiering/cash_count_sheet', 'refresh');
            return;
        }

        $denoms = cashiering_denominations();

        /* Rebuild the nested grid from the flat posted field names, exactly as the
           save path in cash_count_sheet() does. */
        $grid = array('bills' => array(), 'coins' => array());
        foreach ($denoms['bills'] as $denom) {
            $grid['bills'][$denom['key']] = array(
                'bundles' => $this->input->post('bundles_' . $denom['key']),
                'loose' => $this->input->post('loose_' . $denom['key']),
            );
        }
        foreach ($denoms['coins'] as $denom) {
            $grid['coins'][$denom['key']] = array(
                'rolls' => $this->input->post('rolls_' . $denom['key']),
                'loose' => $this->input->post('loose_' . $denom['key']),
            );
        }
        $grid['other'] = array(
            'checks' => $this->input->post('checks'),
            'advances' => $this->input->post('advances'),
            'others' => $this->input->post('others'),
        );
        $grid['meta'] = array(
            'fund_name' => trim((string) $this->input->post('fund_name')),
            'accountable_person' => trim((string) $this->input->post('accountable_person')),
            'counted_by' => trim((string) $this->input->post('counted_by')),
            'other_funds' => trim((string) $this->input->post('other_funds')),
            'others_specify' => trim((string) $this->input->post('others_specify')),
        );

        $breakdown = $this->cashiering_model->normalize_breakdown($grid);
        $totals = $this->cashiering_model->breakdown_totals($breakdown);

        $report_date = $this->input->post('report_date') ?: date('Y-m-d');

        /* Cash on hand per books defaults to the system figure but the cashier may
           override it, so an empty box falls back to the day's expectation. */
        $posted_on_hand = $this->input->post('cash_on_hand_books');
        if ($posted_on_hand === NULL || $posted_on_hand === '') {
            $day = $this->cashiering_model->get_daily_summary($report_date);
            $cash_on_hand = floatval($day['expected_cash']);
        } else {
            $cash_on_hand = floatval(str_replace(',', '', $posted_on_hand));
        }

        /* Shaped like a cashiering_reports row, so the print views need no special case. */
        $report = (object) array(
            'id' => NULL,
            'status' => 'draft',
            'report_date' => $report_date,
            'cashier_name' => $grid['meta']['accountable_person'] !== ''
                ? $grid['meta']['accountable_person']
                : trim((string) $this->input->post('cashier_name')),
            'expected_cash' => $cash_on_hand,
            'over_short' => $totals['grand_total'] - $cash_on_hand,
            'notes' => trim((string) $this->input->post('notes')),
        );

        $this->_cash_count_pdf_output(
            $this->_sheet_print_data($report, $breakdown, TRUE),
            'cash_count_sheet_draft_' . date('Ymd', strtotime($report_date)) . '.pdf'
        );
    }

    /**
     * Stream a PDF of the sheet.
     *
     * Preferred path: print the Tailwind sheet with a headless browser, so the
     * download keeps the exact layout of the sheet the cashier sees - the same
     * renderer the loan forms use.
     *
     * When no browser can be launched (typical on shared hosting) it falls back to
     * print_cash_count_sheet_tcpdf.php, a separate table-based view written for a
     * renderer that understands neither CSS nor Tailwind class names. That is a
     * plainer sheet by necessity - it is a legibility net, not a second design - so
     * the Tailwind view stays the one that defines the sheet's look.
     */
    private function _cash_count_pdf_output($data, $filename) {
        $html = $this->load->view('cashiering/print_cash_count_sheet', $data, TRUE);

        if ($this->_cashiering_pdf_renderer()) {
            $pdf_file = loan_form_html_to_pdf($html);
            if ($pdf_file !== FALSE) {
                $bytes = file_get_contents($pdf_file);
                @unlink($pdf_file);
                $this->output
                    ->set_content_type('application/pdf')
                    ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
                    ->set_header('Content-Length: ' . strlen($bytes))
                    ->set_output($bytes);
                return;
            }
        }

        log_message('error', 'Cashiering PDF: no headless browser; using the TCPDF fallback view.');
        $fallback = $this->load->view('cashiering/print_cash_count_sheet_tcpdf', $data, TRUE);
        $this->_cashiering_tcpdf_output($this->_html_body($fallback), $filename);
    }

    /**
     * Load the shared HTML-to-PDF helper. It ships with the loan form renderer, but
     * the functions themselves are generic.
     */
    private function _cashiering_pdf_renderer() {
        if (function_exists('loan_form_html_to_pdf')) {
            return TRUE;
        }
        $helper = dirname(__FILE__) . '/pdf/loan_form_common.php';
        if (!is_file($helper)) {
            return FALSE;
        }
        include_once $helper;
        return function_exists('loan_form_html_to_pdf');
    }

    /**
     * TCPDF cannot parse a full HTML document, a stylesheet or a doctype, so it is
     * handed the <body> markup only - see the header comment in the print view.
     */
    private function _html_body($html) {
        if (preg_match('~<body[^>]*>(.*)</body>~is', $html, $matches)) {
            return $matches[1];
        }
        return $html;
    }

    /**
     * TCPDF fallback for hosts where no headless browser is available.
     *
     * Output buffers are dropped first: TCPDF refuses to send a file once anything
     * sits in the buffer. The same guard the other exporters in this app use.
     */
    private function _cashiering_tcpdf_output($body, $filename) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());

        $this->load->library('pdf');
        $this->pdf->set_subtitle('');
        $this->pdf->hidefooter(FALSE);
        $this->pdf->start_pdf(FALSE);
        $this->pdf->SetSubject('Cash Count Sheet');
        $this->pdf->setPrintHeader(FALSE);
        $this->pdf->AddPage();
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->writeHTML($body, TRUE, FALSE, TRUE, FALSE, '');
        $this->pdf->Output($filename, 'D');
        exit;
    }
}
