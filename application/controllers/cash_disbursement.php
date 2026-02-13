<?php

/**
 * Cash Disbursement Controller
 * Manages cash disbursement transactions for the accounting system
 */
class Cash_disbursement extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = lang('page_cash_disbursement');
        $this->lang->load('setting');
        $this->lang->load('finance');
        $this->load->helper('text');
        $this->load->model('cash_disbursement_model');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('setting_model');
    }

    /**
     * Default index - redirects to cash disbursement list
     */
    function index() {
        redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
    }

    /**
     * Display list of all cash disbursements
     */
    function cash_disbursement_list() {
        $this->data['title'] = lang('cash_disbursement_list');
        
        // Get all cash disbursements
        $this->data['cash_disbursements'] = $this->cash_disbursement_model->get_cash_disbursements()->result();
        
        $this->data['content'] = 'cash_disbursement/cash_disbursement_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Create new cash disbursement
     */
    function cash_disbursement_create() {
        $this->data['title'] = lang('cash_disbursement_create');
        
        // Form validation rules
        $this->form_validation->set_rules('disburse_date', lang('cash_disbursement_date'), 'required');
        $this->form_validation->set_rules('disburse_no', lang('cash_disbursement_no'), 'required|callback_check_disburse_no');
        $this->form_validation->set_rules('paid_to', lang('cash_disbursement_paid_to'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_disbursement_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_disbursement_description'), 'required');
        $this->form_validation->set_rules('account[]', lang('cash_disbursement_account'), 'required');
        $this->form_validation->set_rules('amount[]', lang('cash_disbursement_amount'), 'required');

        if ($this->form_validation->run() == TRUE) {
            // Prepare disbursement data
            $disburse_data = array(
                'disburse_no' => $this->input->post('disburse_no'),
                'disburse_date' => date('Y-m-d', strtotime($this->input->post('disburse_date'))),
                'paid_to' => $this->input->post('paid_to'),
                'payment_method' => $this->input->post('payment_method'),
                'cheque_no' => $this->input->post('cheque_no'),
                'bank_name' => $this->input->post('bank_name'),
                'description' => $this->input->post('description'),
                'total_amount' => 0,
                'createdby' => current_user()->id,
                'PIN' => current_user()->PIN,
                'created_at' => date('Y-m-d H:i:s')
            );

            // Get line items
            $accounts = $this->input->post('account');
            $amounts = $this->input->post('amount');
            $line_descriptions = $this->input->post('line_description');
            
            $line_items = array();
            $total = 0;
            
            if (is_array($accounts)) {
                foreach ($accounts as $key => $account) {
                    if (!empty($account) && !empty($amounts[$key]) && $amounts[$key] > 0) {
                        $line_items[] = array(
                            'account' => $account,
                            'amount' => $amounts[$key],
                            'description' => isset($line_descriptions[$key]) ? $line_descriptions[$key] : ''
                        );
                        $total += $amounts[$key];
                    }
                }
            }
            
            $disburse_data['total_amount'] = $total;

            // Create cash disbursement
            $disburse_id = $this->cash_disbursement_model->create_cash_disbursement($disburse_data, $line_items);
            
            if ($disburse_id) {
                $this->session->set_flashdata('message', lang('cash_disbursement_create_success'));
                redirect(current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($disburse_id), 'refresh');
            } else {
                $this->data['warning'] = lang('cash_disbursement_create_fail');
            }
        }

        // Get next disbursement number
        $this->data['next_disburse_no'] = $this->cash_disbursement_model->get_next_disburse_no();
        
        // Get account list for dropdown
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        // Get payment methods from paymentmenthod table only
        $this->load->model('payment_method_config_model');
        $payment_methods = $this->payment_method_config_model->get_all_payment_methods();
        $this->data['payment_methods'] = array();
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->name] = $method->name;
        }

        $this->data['content'] = 'cash_disbursement/cash_disbursement_form';
        $this->load->view('template', $this->data);
    }

    /**
     * Edit existing cash disbursement
     */
    function cash_disbursement_edit($id) {
        $this->data['id'] = $id;
        $id = decode_id($id);
        
        $this->data['title'] = lang('cash_disbursement_edit');
        
        // Get disbursement data
        $disburse = $this->cash_disbursement_model->get_cash_disbursement($id);
        
        if (!$disburse) {
            $this->session->set_flashdata('warning', lang('cash_disbursement_not_found'));
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
        }
        
        // Form validation rules
        $this->form_validation->set_rules('disburse_date', lang('cash_disbursement_date'), 'required');
        $this->form_validation->set_rules('disburse_no', lang('cash_disbursement_no'), 'required');
        $this->form_validation->set_rules('paid_to', lang('cash_disbursement_paid_to'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_disbursement_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_disbursement_description'), 'required');
        $this->form_validation->set_rules('account[]', lang('cash_disbursement_account'), 'required');
        $this->form_validation->set_rules('amount[]', lang('cash_disbursement_amount'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $posted_payment = trim((string) $this->input->post('payment_method'));
            if ($posted_payment === '' && !empty($disburse->payment_method)) {
                $posted_payment = trim((string) $disburse->payment_method);
            }
            if ($posted_payment === '') {
                $posted_payment = 'Cash';
            }
            // Prepare disbursement data
            $disburse_data = array(
                'disburse_no' => $this->input->post('disburse_no'),
                'disburse_date' => date('Y-m-d', strtotime($this->input->post('disburse_date'))),
                'paid_to' => $this->input->post('paid_to'),
                'payment_method' => $posted_payment,
                'cheque_no' => $this->input->post('cheque_no'),
                'bank_name' => $this->input->post('bank_name'),
                'description' => $this->input->post('description'),
                'total_amount' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            );

            // Get line items
            $accounts = $this->input->post('account');
            $amounts = $this->input->post('amount');
            $line_descriptions = $this->input->post('line_description');
            
            $line_items = array();
            $total = 0;
            
            if (is_array($accounts)) {
                foreach ($accounts as $key => $account) {
                    if (!empty($account) && !empty($amounts[$key]) && $amounts[$key] > 0) {
                        $line_items[] = array(
                            'account' => $account,
                            'amount' => $amounts[$key],
                            'description' => isset($line_descriptions[$key]) ? $line_descriptions[$key] : ''
                        );
                        $total += $amounts[$key];
                    }
                }
            }
            
            $disburse_data['total_amount'] = $total;

            // Update cash disbursement
            $result = $this->cash_disbursement_model->update_cash_disbursement($id, $disburse_data, $line_items);
            
            if ($result) {
                $this->session->set_flashdata('message', lang('cash_disbursement_update_success'));
                redirect(current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id($id), 'refresh');
            } else {
                $this->data['warning'] = lang('cash_disbursement_update_fail');
            }
        }

        $this->data['disburse'] = $disburse;
        $this->data['line_items'] = $this->cash_disbursement_model->get_disburse_items($id);
        
        // Get account list for dropdown
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        // Get payment methods from paymentmenthod table only
        $this->load->model('payment_method_config_model');
        $payment_methods = $this->payment_method_config_model->get_all_payment_methods();
        $this->data['payment_methods'] = array();
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->name] = $method->name;
        }
        // If saved payment method is not in the table (e.g. was removed from config), still show it so it can be selected
        if (!empty($disburse->payment_method) && !isset($this->data['payment_methods'][$disburse->payment_method])) {
            $this->data['payment_methods'][$disburse->payment_method] = $disburse->payment_method;
        }

        $this->data['content'] = 'cash_disbursement/cash_disbursement_edit';
        $this->load->view('template', $this->data);
    }

    /**
     * View cash disbursement details
     */
    function cash_disbursement_view($id) {
        $this->data['id'] = $id;
        $id = decode_id($id);
        
        $this->data['title'] = lang('cash_disbursement_view');
        
        // Get disbursement data
        $disburse = $this->cash_disbursement_model->get_cash_disbursement($id);
        
        if (!$disburse) {
            $this->session->set_flashdata('warning', lang('cash_disbursement_not_found'));
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
        }
        
        $this->data['disburse'] = $disburse;
        $this->data['line_items'] = $this->cash_disbursement_model->get_disburse_items($id);
        $this->data['accounting_entries'] = $this->cash_disbursement_model->get_journal_entries_by_disbursement($id);
        
        $this->data['content'] = 'cash_disbursement/cash_disbursement_view';
        $this->load->view('template', $this->data);
    }

    /**
     * Print cash disbursement
     */
    function cash_disbursement_print($id) {
        $id = decode_id($id);
        
        $this->data['title'] = lang('cash_disbursement');
        
        // Get disbursement data
        $disburse = $this->cash_disbursement_model->get_cash_disbursement($id);
        
        if (!$disburse) {
            $this->session->set_flashdata('warning', lang('cash_disbursement_not_found'));
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
        }
        
        $this->data['disburse'] = $disburse;
        $this->data['line_items'] = $this->cash_disbursement_model->get_disburse_items($id);
        
        $this->load->view('cash_disbursement/print/cash_disbursement_print', $this->data);
    }

    /**
     * Delete cash disbursement
     */
    function cash_disbursement_delete($id) {
        $id = decode_id($id);
        
        // Delete disbursement
        $result = $this->cash_disbursement_model->delete_cash_disbursement($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('cash_disbursement_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('cash_disbursement_delete_fail'));
        }
        
        redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
    }

    /**
     * Export cash disbursements to Excel
     */
    function cash_disbursement_export() {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Load Excel library
        $this->load->library('excel');
        
        // Get cash disbursements data
        $cash_disbursements = $this->cash_disbursement_model->get_cash_disbursements()->result();
        
        if (empty($cash_disbursements)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
            exit();
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Cash Disbursements")
                                     ->setSubject("Cash Disbursements Export")
                                     ->setDescription("Cash Disbursements exported from " . company_info()->name);
        
        // Set active sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Cash Disbursements');
        
        // Add header row
        $sheet->setCellValue('A1', 'Disbursement No');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Paid To');
        $sheet->setCellValue('D1', 'Payment Method');
        $sheet->setCellValue('E1', 'Description');
        $sheet->setCellValue('F1', 'Total Amount');
        
        // Style header row
        $headerStyle = array(
            'font' => array('bold' => true),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'E0E0E0')
            )
        );
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Add data rows
        $row = 2;
        foreach ($cash_disbursements as $disburse) {
            $sheet->setCellValue('A' . $row, $disburse->disburse_no);
            $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($disburse->disburse_date)));
            $sheet->setCellValue('C' . $row, $disburse->paid_to);
            $sheet->setCellValue('D' . $row, $disburse->payment_method);
            $sheet->setCellValue('E' . $row, $disburse->description);
            $sheet->setCellValue('F' . $row, number_format($disburse->total_amount, 2));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="cash_disbursements_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Write file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Search members for cash disbursement form (paid_to)
     * Returns JSON response with member data
     */
    function search_member() {
        $key = trim($this->input->get('key'));
        $limit = 20;
        $start = 0;
        
        $status = array();
        
        if (empty($key)) {
            $status['success'] = 'N';
            $status['error'] = 'Please enter search keyword';
            echo json_encode($status);
            return;
        }
        
        $members = $this->member_model->search_member($key, 1, 1, $limit, $start);
        
        if (!empty($members)) {
            $status['success'] = 'Y';
            $status['data'] = array();
            
            foreach ($members as $member) {
                $status['data'][] = array(
                    'PID' => $member->PID,
                    'member_id' => $member->member_id,
                    'fullname' => trim($member->firstname . ' ' . $member->middlename . ' ' . $member->lastname),
                    'firstname' => $member->firstname,
                    'middlename' => $member->middlename,
                    'lastname' => $member->lastname
                );
            }
        } else {
            $status['success'] = 'N';
            $status['error'] = 'No members found';
        }
        
        echo json_encode($status);
    }

    /**
     * Check if disbursement number already exists
     */
    function check_disburse_no($disburse_no) {
        $id = $this->input->post('id');
        
        if ($this->cash_disbursement_model->disburse_no_exists($disburse_no, $id)) {
            $this->form_validation->set_message('check_disburse_no', lang('cash_disbursement_no_exists'));
            return FALSE;
        }
        return TRUE;
    }
}
