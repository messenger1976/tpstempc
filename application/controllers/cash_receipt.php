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
     * Defaults date_from/date_to to current date. Persists selected dates in session when navigating away and back.
     */
    function cash_receipt_list() {
        $this->data['title'] = lang('cash_receipt_list');
        
        $today = date('Y-m-d');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $clear = $this->input->get('clear') === '1' || $this->input->get('clear') === 1;

        if ($clear) {
            $this->session->unset_userdata(array('cash_receipt_list_date_from', 'cash_receipt_list_date_to'));
            redirect(current_lang() . '/cash_receipt/cash_receipt_list?date_from=' . $today . '&date_to=' . $today, 'refresh');
            return;
        } elseif (!empty($date_from) || !empty($date_to)) {
            $date_from = !empty($date_from) ? $date_from : $today;
            $date_to = !empty($date_to) ? $date_to : $today;
            $this->session->set_userdata('cash_receipt_list_date_from', $date_from);
            $this->session->set_userdata('cash_receipt_list_date_to', $date_to);
        } else {
            $stored_from = $this->session->userdata('cash_receipt_list_date_from');
            $stored_to = $this->session->userdata('cash_receipt_list_date_to');
            if (!empty($stored_from) || !empty($stored_to)) {
                $date_from = !empty($stored_from) ? $stored_from : $today;
                $date_to = !empty($stored_to) ? $stored_to : $today;
            } else {
                $date_from = $today;
                $date_to = $today;
            }
        }
        
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        
        $receipts = $this->cash_receipt_model->get_cash_receipts(null, null, $date_from, $date_to)->result();
        
        // Load payment methods lookup for ID to name conversion
        $this->load->model('payment_method_config_model');
        $payment_methods_list = $this->payment_method_config_model->get_all_payment_methods();
        $payment_methods_by_id = array();
        foreach ($payment_methods_list as $pm) {
            $payment_methods_by_id[$pm->id] = $pm->name;
        }
        
        foreach ($receipts as $receipt) {
            $receipt->is_posted = $this->cash_receipt_model->is_receipt_posted_to_gl($receipt->id);
            
            // If payment_method is an integer (ID), lookup the name from paymentmenthod table
            if (is_numeric($receipt->payment_method) && isset($payment_methods_by_id[(int)$receipt->payment_method])) {
                $receipt->payment_method_display = $payment_methods_by_id[(int)$receipt->payment_method];
            } else {
                $receipt->payment_method_display = $receipt->payment_method;
            }
        }
        $this->data['cash_receipts'] = $receipts;
        
        $this->data['content'] = 'cash_receipt/cash_receipt_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Create new cash receipt
     */
    function cash_receipt_create() {
        $this->data['title'] = lang('cash_receipt_create');
        
        // Form validation rules
        $cancelled = $this->input->post('cancelled') == '1';
        $this->form_validation->set_rules('receipt_date', lang('cash_receipt_date'), 'required');
        $this->form_validation->set_rules('receipt_no', lang('cash_receipt_no'), 'required|callback_check_receipt_no');
        $this->form_validation->set_rules('received_from', lang('cash_receipt_received_from'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_receipt_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_receipt_description'), 'required');
        if (!$cancelled) {
            $this->form_validation->set_rules('account[]', lang('cash_receipt_account'), 'required');
        }

        if ($this->form_validation->run() == TRUE) {
            // Convert payment method ID to name
            $payment_method_id = $this->input->post('payment_method');
            $payment_method_name = '';
            if (!empty($payment_method_id) && isset($this->data['payment_methods_by_id'][$payment_method_id])) {
                $payment_method_name = $this->data['payment_methods_by_id'][$payment_method_id]->name;
            } else {
                // Fallback: use posted value if ID lookup fails (for backward compatibility)
                $payment_method_name = $payment_method_id;
            }
            
            // Prepare receipt data
            $receipt_data = array(
                'receipt_no' => $this->input->post('receipt_no'),
                'receipt_date' => date('Y-m-d', strtotime($this->input->post('receipt_date'))),
                'received_from' => $this->input->post('received_from'),
                'payment_method' => $payment_method_name,
                'cheque_no' => $this->input->post('cheque_no'),
                'bank_name' => $this->input->post('bank_name'),
                'description' => $this->input->post('description'),
                'total_amount' => 0,
                'cancelled' => $cancelled ? 1 : 0,
                'createdby' => current_user()->id,
                'PIN' => current_user()->PIN,
                'created_at' => date('Y-m-d H:i:s')
            );

            // Get line items (skip when cancelled - just record document reference)
            $line_items = array();
            $total_debit = 0;
            $total_credit = 0;
            if (!$cancelled) {
                $accounts = $this->input->post('account');
                $debits = $this->input->post('debit');
                $credits = $this->input->post('credit');
                $line_descriptions = $this->input->post('line_description');
                if (is_array($accounts)) {
                    foreach ($accounts as $key => $account) {
                        $debit = isset($debits[$key]) ? floatval(str_replace(',', '', $debits[$key])) : 0;
                        $credit = isset($credits[$key]) ? floatval(str_replace(',', '', $credits[$key])) : 0;
                        if (!empty($account) && ($debit > 0 || $credit > 0)) {
                            $line_items[] = array(
                                'account' => $account,
                                'debit' => $debit,
                                'credit' => $credit,
                                'amount' => $debit + $credit,
                                'description' => isset($line_descriptions[$key]) ? $line_descriptions[$key] : ''
                            );
                            $total_debit += $debit;
                            $total_credit += $credit;
                        }
                    }
                }
            }
            $receipt_data['total_amount'] = max($total_debit, $total_credit);

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
        $this->data['payment_methods_by_id'] = array();
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->id] = $method->name;
            $this->data['payment_methods_by_id'][$method->id] = $method;
            // Find Cash ID for default selection
            if (strtolower(trim($method->name)) === 'cash') {
                $this->data['default_cash_id'] = $method->id;
            }
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
        
        if ($this->cash_receipt_model->is_receipt_posted_to_gl($id)) {
            $this->session->set_flashdata('warning', lang('cash_receipt_cannot_edit_posted'));
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
        }
        
        // Form validation rules
        $this->form_validation->set_rules('receipt_date', lang('cash_receipt_date'), 'required');
        $this->form_validation->set_rules('receipt_no', lang('cash_receipt_no'), 'required');
        $this->form_validation->set_rules('received_from', lang('cash_receipt_received_from'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_receipt_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_receipt_description'), 'required');
        
        // Check if cancelled
        $cancelled = $this->input->post('cancelled') == '1';
        if (!$cancelled) {
            $this->form_validation->set_rules('account[]', lang('cash_receipt_account'), 'required');
        }

        if ($this->form_validation->run() == TRUE) {
            // Convert payment method ID to name
            $payment_method_id = $this->input->post('payment_method');
            $payment_method_name = '';
            if (!empty($payment_method_id) && isset($this->data['payment_methods_by_id'][$payment_method_id])) {
                $payment_method_name = $this->data['payment_methods_by_id'][$payment_method_id]->name;
            } elseif (!empty($payment_method_id) && $payment_method_id < 0) {
                // Temporary entry (removed from config) - use as-is
                $payment_method_name = isset($this->data['payment_methods'][$payment_method_id]) ? $this->data['payment_methods'][$payment_method_id] : '';
            }
            // Fallback to existing value or 'Cash'
            if (empty($payment_method_name) && isset($receipt->payment_method)) {
                $payment_method_name = trim($receipt->payment_method);
            }
            if (empty($payment_method_name)) {
                $payment_method_name = 'Cash';
            }
            // Prepare receipt data
            $receipt_data = array(
                'receipt_no' => $this->input->post('receipt_no'),
                'receipt_date' => date('Y-m-d', strtotime($this->input->post('receipt_date'))),
                'received_from' => $this->input->post('received_from'),
                'payment_method' => $payment_method_name,
                'cheque_no' => $this->input->post('cheque_no'),
                'bank_name' => $this->input->post('bank_name'),
                'description' => $this->input->post('description'),
                'total_amount' => 0,
                'cancelled' => $cancelled ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            );

            // Get line items (skip when cancelled - just record document reference)
            $line_items = array();
            $total_debit = 0;
            $total_credit = 0;
            
            if (!$cancelled) {
                $accounts = $this->input->post('account');
                $debits = $this->input->post('debit');
                $credits = $this->input->post('credit');
                $line_descriptions = $this->input->post('line_description');
                
                if (is_array($accounts)) {
                    foreach ($accounts as $key => $account) {
                        $debit = isset($debits[$key]) ? floatval(str_replace(',', '', $debits[$key])) : 0;
                        $credit = isset($credits[$key]) ? floatval(str_replace(',', '', $credits[$key])) : 0;
                        if (!empty($account) && ($debit > 0 || $credit > 0)) {
                            $line_items[] = array(
                                'account' => $account,
                                'debit' => $debit,
                                'credit' => $credit,
                                'amount' => $debit + $credit,
                                'description' => isset($line_descriptions[$key]) ? $line_descriptions[$key] : ''
                            );
                            $total_debit += $debit;
                            $total_credit += $credit;
                        }
                    }
                }
            }
            
            $receipt_data['total_amount'] = max($total_debit, $total_credit);

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
        $this->data['line_items'] = $this->cash_receipt_model->get_line_items_for_edit($id);
        
        // Get account list for dropdown
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        // Get payment methods from paymentmenthod table
        $this->load->model('payment_method_config_model');
        $payment_methods = $this->payment_method_config_model->get_all_payment_methods();
        $this->data['payment_methods'] = array();
        $this->data['payment_methods_by_id'] = array();
        $this->data['payment_method_id_by_name'] = array();
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->id] = $method->name;
            $this->data['payment_methods_by_id'][$method->id] = $method;
            $this->data['payment_method_id_by_name'][strtolower(trim($method->name))] = $method->id;
        }
        // If saved payment method is not in the table (e.g. was removed from config), still show it so it can be selected
        if (!empty($receipt->payment_method)) {
            $saved_method_lower = strtolower(trim($receipt->payment_method));
            if (!isset($this->data['payment_method_id_by_name'][$saved_method_lower])) {
                // Add as a temporary entry with a fake ID (negative to avoid conflicts)
                $temp_id = -999;
                $this->data['payment_methods'][$temp_id] = $receipt->payment_method;
            }
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
        
        // Lookup payment method name if stored as ID
        if (!empty($receipt->payment_method) && is_numeric($receipt->payment_method)) {
            $this->load->model('payment_method_config_model');
            $pm = $this->db->query('SELECT name FROM paymentmenthod WHERE id = ? AND PIN = ? LIMIT 1', array((int)$receipt->payment_method, current_user()->PIN))->row();
            if ($pm) {
                $receipt->payment_method_display = $pm->name;
            } else {
                $receipt->payment_method_display = $receipt->payment_method;
            }
        } else {
            $receipt->payment_method_display = $receipt->payment_method;
        }
        
        $this->data['receipt'] = $receipt;
        $this->data['line_items'] = $this->cash_receipt_model->get_receipt_items($id);
        
        // Get accounting entries (built from current receipt data, not from stored journal)
        $this->data['accounting_entries'] = $this->cash_receipt_model->get_journal_entries_by_receipt($id);
        
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
        
        if ($this->cash_receipt_model->is_receipt_posted_to_gl($id)) {
            $this->session->set_flashdata('warning', lang('cash_receipt_cannot_delete_posted'));
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
            return;
        }
        
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
     * Report Summary: Trial Balance format - accounts used in cash receipt module with totals
     */
    function cash_receipt_report_summary() {
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['summary'] = $this->cash_receipt_model->get_account_summary($date_from, $date_to);
        $this->data['title'] = lang('cash_receipt_report_summary');
        $this->load->view('cash_receipt/cash_receipt_report_summary', $this->data);
    }

    /**
     * Export Report Summary to Excel (Trial Balance format)
     */
    function cash_receipt_report_summary_export() {
        if (ob_get_level()) { ob_end_clean(); }
        while (@ob_end_clean());
        $this->load->library('excel');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $summary = $this->cash_receipt_model->get_account_summary($date_from, $date_to);
        if (empty($summary)) {
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
            exit();
        }
        $grand_debit = 0;
        $grand_credit = 0;
        foreach ($summary as $r) {
            $grand_debit += isset($r->total_debit) ? floatval($r->total_debit) : 0;
            $grand_credit += isset($r->total_credit) ? floatval($r->total_credit) : 0;
        }
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle(lang('cash_receipt_report_summary'))
            ->setSubject('Cash Receipt Report Summary');
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Report Summary');
        $sheet->setCellValue('A1', lang('account_code'));
        $sheet->setCellValue('B1', lang('account_name'));
        $sheet->setCellValue('C1', lang('journalentry_debit'));
        $sheet->setCellValue('D1', lang('journalentry_credit'));
        $headerStyle = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E0E0E0')));
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($summary as $r) {
            $debit = isset($r->total_debit) ? floatval($r->total_debit) : 0;
            $credit = isset($r->total_credit) ? floatval($r->total_credit) : 0;
            if ($debit <= 0 && $credit <= 0) continue;
            $sheet->setCellValue('A' . $row, $r->account);
            $sheet->setCellValue('B' . $row, $r->account_name);
            $sheet->setCellValue('C' . $row, $debit);
            $sheet->setCellValue('D' . $row, $credit);
            $row++;
        }
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, lang('total'));
        $sheet->setCellValue('C' . $row, $grand_debit);
        $sheet->setCellValue('D' . $row, $grand_credit);
        $sheet->getStyle('C2:D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', 'D') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="cash_receipt_report_summary_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Report Details: Line-by-line detail of all accounts used in cash receipt module
     */
    function cash_receipt_report_details() {
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['details'] = $this->cash_receipt_model->get_account_details($date_from, $date_to);
        $this->data['title'] = lang('cash_receipt_report_details');
        $this->load->view('cash_receipt/cash_receipt_report_details', $this->data);
    }

    /**
     * Export Report Details to Excel (grouped by transaction, trial balance layout)
     */
    function cash_receipt_report_details_export() {
        if (ob_get_level()) { ob_end_clean(); }
        while (@ob_end_clean());
        $this->load->library('excel');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $details = $this->cash_receipt_model->get_account_details($date_from, $date_to);
        if (empty($details)) {
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/cash_receipt/cash_receipt_list', 'refresh');
            exit();
        }
        $grouped = array();
        foreach ($details as $row) {
            $key = $row->receipt_no;
            if (!isset($grouped[$key])) {
                $grouped[$key] = array('receipt_no' => $row->receipt_no, 'receipt_date' => $row->receipt_date, 'received_from' => $row->received_from, 'payment_method' => $row->payment_method, 'receipt_description' => isset($row->receipt_description) ? $row->receipt_description : '', 'lines' => array());
            }
            $grouped[$key]['lines'][] = $row;
        }
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle(lang('cash_receipt_report_details'))
            ->setSubject('Cash Receipt Report Details');
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Report Details');
        $sheet->setCellValue('A1', lang('cash_receipt_no'));
        $sheet->setCellValue('B1', lang('cash_receipt_date'));
        $sheet->setCellValue('C1', lang('cash_receipt_received_from'));
        $sheet->setCellValue('D1', lang('cash_receipt_payment_method'));
        $sheet->setCellValue('E1', lang('account_code'));
        $sheet->setCellValue('F1', lang('account_name'));
        $sheet->setCellValue('G1', lang('journalentry_debit'));
        $sheet->setCellValue('H1', lang('journalentry_credit'));
        $headerStyle = array('font' => array('bold' => true), 'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E0E0E0')));
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $row = 2;
        foreach ($grouped as $txn) {
            $lines = $txn['lines'];
            foreach ($lines as $l) {
                $debit = isset($l->debit) ? floatval($l->debit) : 0;
                $credit = isset($l->credit) ? floatval($l->credit) : 0;
                $sheet->setCellValue('A' . $row, $txn['receipt_no']);
                $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($txn['receipt_date'])));
                $sheet->setCellValue('C' . $row, $txn['received_from']);
                $sheet->setCellValue('D' . $row, $txn['payment_method']);
                $sheet->setCellValue('E' . $row, $l->account);
                $sheet->setCellValue('F' . $row, $l->account_name . (!empty($l->line_description) ? ' — ' . $l->line_description : ''));
                $sheet->setCellValue('G' . $row, $debit);
                $sheet->setCellValue('H' . $row, $credit);
                $row++;
            }
        }
        $sheet->getStyle('G2:H' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (range('A', 'H') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="cash_receipt_report_details_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
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
