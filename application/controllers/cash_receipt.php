<?php

/**
 * Cash Receipt Controller
 * Manages cash receipt transactions for the accounting system
 */
class Cash_receipt extends CI_Controller {

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = lang('page_cash_receipt');
        $this->lang->load('setting');
        $this->lang->load('finance');
        $this->load->helper('text');
        $this->load->model('cash_receipt_model');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('setting_model');
    }

    /**
     * Default index - redirects to cash receipt list
     */
    function index() {
        redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
    }

    /**
     * Display list of all cash receipts
     */
    function cash_receipt_list() {
        $this->data['title'] = lang('cash_receipt_list');
        
        // Get date range filters
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        
        // Pass date filters to view for form persistence
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        
        // Get cash receipts with optional date range filter
        $this->data['cash_receipts'] = $this->cash_receipt_model->get_cash_receipts(null, null, $date_from, $date_to)->result();
        
        $this->data['content'] = 'cash_receipt/cash_receipt_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Create new cash receipt
     */
    function cash_receipt_create() {
        $this->data['title'] = lang('cash_receipt_create');
        
        // Form validation rules
        $this->form_validation->set_rules('receipt_date', lang('cash_receipt_date'), 'required');
        $this->form_validation->set_rules('receipt_no', lang('cash_receipt_no'), 'required|callback_check_receipt_no');
        $this->form_validation->set_rules('received_from', lang('cash_receipt_received_from'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_receipt_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_receipt_description'), 'required');
        $this->form_validation->set_rules('account[]', lang('cash_receipt_account'), 'required');
        $this->form_validation->set_rules('amount[]', lang('cash_receipt_amount'), 'required');

        if ($this->form_validation->run() == TRUE) {
            // Prepare receipt data
            $receipt_data = array(
                'receipt_no' => $this->input->post('receipt_no'),
                'receipt_date' => date('Y-m-d', strtotime($this->input->post('receipt_date'))),
                'received_from' => $this->input->post('received_from'),
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
            
            $receipt_data['total_amount'] = $total;

            // Create cash receipt
            $receipt_id = $this->cash_receipt_model->create_cash_receipt($receipt_data, $line_items);
            
            if ($receipt_id) {
                $this->session->set_flashdata('message', lang('cash_receipt_create_success'));
                redirect(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($receipt_id), 'refresh');
            } else {
                $this->data['warning'] = lang('cash_receipt_create_fail');
            }
        }

        // Get next receipt number
        $this->data['next_receipt_no'] = $this->cash_receipt_model->get_next_receipt_no();
        
        // Get account list for dropdown
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        // Get payment methods from paymentmenthod table
        $this->load->model('payment_method_config_model');
        $payment_methods = $this->payment_method_config_model->get_all_payment_methods();
        $this->data['payment_methods'] = array();
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->name] = $method->name;
        }

        $this->data['content'] = 'cash_receipt/cash_receipt_form';
        $this->load->view('template', $this->data);
    }

    /**
     * Edit existing cash receipt
     */
    function cash_receipt_edit($id) {
        $this->data['id'] = $id;
        $id = decode_id($id);
        
        $this->data['title'] = lang('cash_receipt_edit');
        
        // Get receipt data
        $receipt = $this->cash_receipt_model->get_cash_receipt($id);
        
        if (!$receipt) {
            $this->session->set_flashdata('warning', lang('cash_receipt_not_found'));
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
        }
        
        // Form validation rules
        $this->form_validation->set_rules('receipt_date', lang('cash_receipt_date'), 'required');
        $this->form_validation->set_rules('receipt_no', lang('cash_receipt_no'), 'required');
        $this->form_validation->set_rules('received_from', lang('cash_receipt_received_from'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_receipt_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_receipt_description'), 'required');
        $this->form_validation->set_rules('account[]', lang('cash_receipt_account'), 'required');
        $this->form_validation->set_rules('amount[]', lang('cash_receipt_amount'), 'required');

        if ($this->form_validation->run() == TRUE) {
            // Prepare receipt data
            $receipt_data = array(
                'receipt_no' => $this->input->post('receipt_no'),
                'receipt_date' => date('Y-m-d', strtotime($this->input->post('receipt_date'))),
                'received_from' => $this->input->post('received_from'),
                'payment_method' => $this->input->post('payment_method'),
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
            
            $receipt_data['total_amount'] = $total;

            // Update cash receipt
            $result = $this->cash_receipt_model->update_cash_receipt($id, $receipt_data, $line_items);
            
            if ($result) {
                $this->session->set_flashdata('message', lang('cash_receipt_update_success'));
                redirect(current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id($id), 'refresh');
            } else {
                $this->data['warning'] = lang('cash_receipt_update_fail');
            }
        }

        $this->data['receipt'] = $receipt;
        $this->data['line_items'] = $this->cash_receipt_model->get_receipt_items($id);
        
        // Get account list for dropdown
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        // Get payment methods from paymentmenthod table
        $this->load->model('payment_method_config_model');
        $payment_methods = $this->payment_method_config_model->get_all_payment_methods();
        $this->data['payment_methods'] = array();
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->name] = $method->name;
        }

        $this->data['content'] = 'cash_receipt/cash_receipt_edit';
        $this->load->view('template', $this->data);
    }

    /**
     * View cash receipt details
     */
    function cash_receipt_view($id) {
        $this->data['id'] = $id;
        $id = decode_id($id);
        
        $this->data['title'] = lang('cash_receipt_view');
        
        // Get receipt data
        $receipt = $this->cash_receipt_model->get_cash_receipt($id);
        
        if (!$receipt) {
            $this->session->set_flashdata('warning', lang('cash_receipt_not_found'));
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
        }
        
        $this->data['receipt'] = $receipt;
        $this->data['line_items'] = $this->cash_receipt_model->get_receipt_items($id);
        
        // Render a minimal layout when opened inside a popup iframe
        if ($this->input->get('popup')) {
            $this->data['is_popup'] = true;
            $this->load->view('cash_receipt/cash_receipt_view_popup', $this->data);
            return;
        }
        
        $this->data['content'] = 'cash_receipt/cash_receipt_view';
        $this->load->view('template', $this->data);
    }

    /**
     * Print cash receipt
     */
    function cash_receipt_print($id) {
        $id = decode_id($id);
        
        $this->data['title'] = lang('cash_receipt');
        
        // Get receipt data
        $receipt = $this->cash_receipt_model->get_cash_receipt($id);
        
        if (!$receipt) {
            $this->session->set_flashdata('warning', lang('cash_receipt_not_found'));
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
        }
        
        $this->data['receipt'] = $receipt;
        $this->data['line_items'] = $this->cash_receipt_model->get_receipt_items($id);
        
        $this->load->view('cash_receipt/print/cash_receipt_print', $this->data);
    }

    /**
     * Delete cash receipt
     */
    function cash_receipt_delete($id) {
        $id = decode_id($id);
        
        // Delete receipt
        $result = $this->cash_receipt_model->delete_cash_receipt($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('cash_receipt_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('cash_receipt_delete_fail'));
        }
        
        redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
    }

    /**
     * Export cash receipts to Excel
     */
    function cash_receipt_export() {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Load Excel library
        $this->load->library('excel');
        
        // Get date range filters from GET parameters
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        
        // Get cash receipts data with optional date range filter
        $cash_receipts = $this->cash_receipt_model->get_cash_receipts(null, null, $date_from, $date_to)->result();
        
        if (empty($cash_receipts)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
            exit();
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Cash Receipts")
                                     ->setSubject("Cash Receipts Export")
                                     ->setDescription("Cash Receipts exported from " . company_info()->name);
        
        // Set active sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Cash Receipts');
        
        // Add header row
        $sheet->setCellValue('A1', 'Receipt No');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Received From');
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
        foreach ($cash_receipts as $receipt) {
            $sheet->setCellValue('A' . $row, $receipt->receipt_no);
            $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($receipt->receipt_date)));
            $sheet->setCellValue('C' . $row, $receipt->received_from);
            $sheet->setCellValue('D' . $row, $receipt->payment_method);
            $sheet->setCellValue('E' . $row, $receipt->description);
            $sheet->setCellValue('F' . $row, number_format($receipt->total_amount, 2));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="cash_receipts_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Write file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Check if receipt number already exists
     */
    function check_receipt_no($receipt_no) {
        $id = $this->input->post('id');
        
        if ($this->cash_receipt_model->receipt_no_exists($receipt_no, $id)) {
            $this->form_validation->set_message('check_receipt_no', lang('cash_receipt_no_exists'));
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Search members for cash receipt form
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
        
        // Search members
        $members = $this->member_model->search_member($key, 1, 1, $limit, $start); // status=1 (active), member=1 (members only)
        
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
     * Get Accounts Receivable account code
     * Returns JSON response with AR account
     */
    function get_ar_account() {
        $this->load->model('finance_model');
        
        // Search for Accounts Receivable account
        $this->db->where('PIN', current_user()->PIN);
        $this->db->group_start();
        $this->db->like('name', 'Accounts Receivable', 'both');
        $this->db->or_like('name', 'Account Receivable', 'both');
        $this->db->or_like('name', 'AR', 'both');
        $this->db->or_like('name', 'Receivable', 'both');
        $this->db->group_end();
        $this->db->where_in('account_type', array(1, 10000)); // Asset type
        $this->db->limit(1);
        
        $account = $this->db->get('account_chart')->row();
        
        $status = array();
        if ($account) {
            $status['success'] = 'Y';
            $status['account'] = $account->account;
            $status['name'] = $account->name;
        } else {
            $status['success'] = 'N';
            $status['error'] = 'Accounts Receivable account not found in chart of accounts';
        }
        
        echo json_encode($status);
    }
}
