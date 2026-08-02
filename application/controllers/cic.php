<?php

/**
 * CIC Credit Data Export — prepare borrower/loan CSV for CISA / CIC submission.
 * Uses the plain-PHP package under FCPATH/cic_export/.
 */
class Cic extends CI_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        $this->lang->load('cic');
        if (!has_role(5, 'Export_CIC')) {
            $this->session->set_flashdata('warning', lang('cic_no_permission'));
            redirect(current_lang(), 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->load->model('cic_model');
        $this->data['current_title'] = lang('cic_module_title');
        $this->_load_cic_package();
    }

    /**
     * Preview / filter screen
     */
    function index() {
        $as_of = $this->input->get('as_of') ?: $this->input->post('as_of');
        if (empty($as_of)) {
            // Default: last day of previous month (typical CIC monthly cut-off)
            $as_of = date('Y-m-t', strtotime('first day of last month'));
        } else {
            $as_of = date('Y-m-d', strtotime($as_of));
        }

        $include_closed = ($this->input->get('include_closed') || $this->input->post('include_closed')) ? 1 : 0;
        $export_invalid = ($this->input->get('export_invalid') || $this->input->post('export_invalid')) ? 1 : 0;

        $rows = $this->cic_model->get_cic_prep_rows($as_of, (bool) $include_closed);

        $validator = new CicValidator();
        $valid_count = 0;
        $invalid_count = 0;
        $preview = array();
        $preview_limit = 50;
        $row_num = 0;

        foreach ($rows as $row) {
            $row_num++;
            $result = $validator->validateRow($row);
            if ($result['valid']) {
                $valid_count++;
            } else {
                $invalid_count++;
            }
            if (count($preview) < $preview_limit) {
                $preview[] = array(
                    'row_number' => $row_num,
                    'valid'      => $result['valid'],
                    'row'        => $result['row'],
                    'errors'     => $result['errors'],
                );
            }
        }

        $this->data['title'] = lang('cic_module_title');
        $this->data['as_of'] = $as_of;
        $this->data['include_closed'] = $include_closed;
        $this->data['export_invalid'] = $export_invalid;
        $this->data['total_rows'] = count($rows);
        $this->data['valid_count'] = $valid_count;
        $this->data['invalid_count'] = $invalid_count;
        $this->data['preview'] = $preview;
        $this->data['preview_limit'] = $preview_limit;
        $this->data['content'] = 'cic/index';
        $this->load->view('template', $this->data);
    }

    /**
     * Validate, write UTF-8 CSV under cic_export/output, then download.
     */
    function export() {
        $as_of = $this->input->get('as_of') ?: $this->input->post('as_of');
        if (empty($as_of)) {
            $as_of = date('Y-m-t', strtotime('first day of last month'));
        } else {
            $as_of = date('Y-m-d', strtotime($as_of));
        }

        $include_closed = ($this->input->get('include_closed') || $this->input->post('include_closed')) ? true : false;
        $export_invalid = ($this->input->get('export_invalid') || $this->input->post('export_invalid')) ? true : false;

        $rows = $this->cic_model->get_cic_prep_rows($as_of, $include_closed);

        $period = date('Ym', strtotime($as_of));
        $coop = 'TAPSTEMCO';
        if (function_exists('company_info')) {
            $info = company_info();
            if ($info && !empty($info->name)) {
                $coop = preg_replace('/[^A-Za-z0-9_-]+/', '', strtoupper(substr($info->name, 0, 32)));
                if ($coop === '') {
                    $coop = 'TAPSTEMCO';
                }
            }
        }

        $period_dir = FCPATH . 'cic_export/output/' . $period;
        if (!is_dir($period_dir)) {
            @mkdir($period_dir, 0750, true);
        }

        $stamp = date('Ymd_His');
        $base_name = 'CIC_PREP_' . $coop . '_' . $period . '_' . $stamp;
        $output_file = $period_dir . '/' . $base_name . '.csv';
        $error_file = $period_dir . '/' . $base_name . '_errors.csv';
        $log_file = FCPATH . 'cic_export/logs/validation_' . $period . '.log';

        $field_map = require FCPATH . 'cic_export/config/field_map.php';
        $logger = new CicErrorLogger($log_file, true);

        $exporter = new CicCsvExporter(array(
            'output_path'         => $output_file,
            'error_output_path'   => $export_invalid ? $error_file : null,
            'export_invalid_rows' => $export_invalid,
            'field_map'           => $field_map,
            'logger'              => $logger,
        ));

        $summary = $exporter->export($rows);

        if (!is_file($output_file)) {
            $this->session->set_flashdata('warning', lang('cic_export_failed'));
            redirect(current_lang() . '/cic/index?as_of=' . urlencode($as_of), 'refresh');
            return;
        }

        $download_name = 'CIC_PREP_' . $coop . '_' . $period . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($output_file));
        header('Pragma: no-cache');
        header('Expires: 0');
        // Do not add UTF-8 BOM — CIC expects UTF-8 without BOM
        readfile($output_file);
        exit;
    }

    /**
     * Require the standalone cic_export package classes once.
     */
    protected function _load_cic_package() {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $base = FCPATH . 'cic_export/src/';
        require_once $base . 'CicValidator.php';
        require_once $base . 'CicErrorLogger.php';
        require_once $base . 'CicCsvExporter.php';
        $loaded = true;
    }
}
