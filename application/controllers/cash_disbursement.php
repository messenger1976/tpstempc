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
     * Defaults date_from/date_to to current date. Persists selected dates in session when navigating away and back.
     */
    function cash_disbursement_list() {
        $this->data['title'] = lang('cash_disbursement_list');
        
        $today = date('Y-m-d');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $clear = $this->input->get('clear') === '1' || $this->input->get('clear') === 1;

        if ($clear) {
            $this->session->unset_userdata(array('cash_disbursement_list_date_from', 'cash_disbursement_list_date_to'));
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list?date_from=' . $today . '&date_to=' . $today, 'refresh');
            return;
        } elseif (!empty($date_from) || !empty($date_to)) {
            $date_from = !empty($date_from) ? $date_from : $today;
            $date_to = !empty($date_to) ? $date_to : $today;
            $this->session->set_userdata('cash_disbursement_list_date_from', $date_from);
            $this->session->set_userdata('cash_disbursement_list_date_to', $date_to);
        } else {
            $stored_from = $this->session->userdata('cash_disbursement_list_date_from');
            $stored_to = $this->session->userdata('cash_disbursement_list_date_to');
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
        
        $disbursements = $this->cash_disbursement_model->get_cash_disbursements(null, null, $date_from, $date_to)->result();
        
        // Load payment methods lookup for ID to name conversion
        $this->load->model('payment_method_config_model');
        $payment_methods_list = $this->payment_method_config_model->get_all_payment_methods();
        $payment_methods_by_id = array();
        foreach ($payment_methods_list as $pm) {
            $payment_methods_by_id[$pm->id] = $pm->name;
        }
        
        foreach ($disbursements as $disburse) {
            $disburse->is_posted = $this->cash_disbursement_model->is_disbursement_posted_to_gl($disburse->id);
            
            // If payment_method is an integer (ID), lookup the name from paymentmenthod table
            if (is_numeric($disburse->payment_method) && isset($payment_methods_by_id[(int)$disburse->payment_method])) {
                $disburse->payment_method_display = $payment_methods_by_id[(int)$disburse->payment_method];
            } else {
                $disburse->payment_method_display = $disburse->payment_method;
            }
        }
        $this->data['cash_disbursements'] = $disbursements;
        
        $this->data['content'] = 'cash_disbursement/cash_disbursement_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Report Summary: Trial Balance format - accounts used in cash disbursement module with totals
     */
    function cash_disbursement_report_summary() {
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['summary'] = $this->cash_disbursement_model->get_account_summary($date_from, $date_to);
        $this->data['title'] = lang('cash_disbursement_report_summary');
        $this->load->view('cash_disbursement/cash_disbursement_report_summary', $this->data);
    }

    /**
     * Export Report Summary to Excel (Trial Balance format)
     */
    function cash_disbursement_report_summary_export() {
        if (ob_get_level()) { ob_end_clean(); }
        while (@ob_end_clean());
        $this->load->library('excel');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $summary = $this->cash_disbursement_model->get_account_summary($date_from, $date_to);
        if (empty($summary)) {
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
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
            ->setTitle(lang('cash_disbursement_report_summary'))
            ->setSubject('Cash Disbursement Report Summary');
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
        header('Content-Disposition: attachment;filename="cash_disbursement_report_summary_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Report Details: Line-by-line detail grouped by transaction, trial balance layout
     */
    function cash_disbursement_report_details() {
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['details'] = $this->cash_disbursement_model->get_account_details($date_from, $date_to);
        $this->data['title'] = lang('cash_disbursement_report_details');
        $this->load->view('cash_disbursement/cash_disbursement_report_details', $this->data);
    }

    /**
     * Export Report Details to Excel (grouped by transaction, trial balance layout)
     */
    function cash_disbursement_report_details_export() {
        if (ob_get_level()) { ob_end_clean(); }
        while (@ob_end_clean());
        $this->load->library('excel');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $details = $this->cash_disbursement_model->get_account_details($date_from, $date_to);
        if (empty($details)) {
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
            exit();
        }
        $grouped = array();
        foreach ($details as $row) {
            $key = $row->disburse_no;
            if (!isset($grouped[$key])) {
                $grouped[$key] = array('disburse_no' => $row->disburse_no, 'disburse_date' => $row->disburse_date, 'paid_to' => $row->paid_to, 'payment_method' => $row->payment_method, 'disburse_description' => isset($row->disburse_description) ? $row->disburse_description : '', 'lines' => array());
            }
            $grouped[$key]['lines'][] = $row;
        }
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle(lang('cash_disbursement_report_details'))
            ->setSubject('Cash Disbursement Report Details');
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Report Details');
        $sheet->setCellValue('A1', lang('cash_disbursement_no'));
        $sheet->setCellValue('B1', lang('cash_disbursement_date'));
        $sheet->setCellValue('C1', lang('cash_disbursement_paid_to'));
        $sheet->setCellValue('D1', lang('cash_disbursement_payment_method'));
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
                $sheet->setCellValue('A' . $row, $txn['disburse_no']);
                $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($txn['disburse_date'])));
                $sheet->setCellValue('C' . $row, $txn['paid_to']);
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
        header('Content-Disposition: attachment;filename="cash_disbursement_report_details_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
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
        $cancelled = $this->input->post('cancelled') == '1';
        $this->form_validation->set_rules('payment_method', lang('cash_disbursement_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_disbursement_description'), 'required');
        if (!$cancelled) {
            $this->form_validation->set_rules('account[]', lang('cash_disbursement_account'), 'required');
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
            
            // Prepare disbursement data
            $disburse_data = array(
                'disburse_no' => $this->input->post('disburse_no'),
                'disburse_date' => date('Y-m-d', strtotime($this->input->post('disburse_date'))),
                'paid_to' => $this->input->post('paid_to'),
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
            $disburse_data['total_amount'] = max($total_debit, $total_credit);

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
        
        if ($this->cash_disbursement_model->is_disbursement_posted_to_gl($id)) {
            $this->session->set_flashdata('warning', lang('cash_disbursement_cannot_edit_posted'));
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
        }
        
        // Form validation rules
        $this->form_validation->set_rules('disburse_date', lang('cash_disbursement_date'), 'required');
        $this->form_validation->set_rules('disburse_no', lang('cash_disbursement_no'), 'required');
        $this->form_validation->set_rules('paid_to', lang('cash_disbursement_paid_to'), 'required');
        $this->form_validation->set_rules('payment_method', lang('cash_disbursement_payment_method'), 'required');
        $this->form_validation->set_rules('description', lang('cash_disbursement_description'), 'required');
        
        // Check if cancelled
        $cancelled = $this->input->post('cancelled') == '1';
        if (!$cancelled) {
            $this->form_validation->set_rules('account[]', lang('cash_disbursement_account'), 'required');
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
            if (empty($payment_method_name) && !empty($disburse->payment_method)) {
                $payment_method_name = trim((string) $disburse->payment_method);
            }
            if (empty($payment_method_name)) {
                $payment_method_name = 'Cash';
            }
            // Prepare disbursement data
            $disburse_data = array(
                'disburse_no' => $this->input->post('disburse_no'),
                'disburse_date' => date('Y-m-d', strtotime($this->input->post('disburse_date'))),
                'paid_to' => $this->input->post('paid_to'),
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
            
            $disburse_data['total_amount'] = max($total_debit, $total_credit);

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
        $this->data['line_items'] = $this->cash_disbursement_model->get_line_items_for_edit($id);
        
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
        if (!empty($disburse->payment_method)) {
            $saved_method_lower = strtolower(trim($disburse->payment_method));
            if (!isset($this->data['payment_method_id_by_name'][$saved_method_lower])) {
                // Add as a temporary entry with a fake ID (negative to avoid conflicts)
                $temp_id = -999;
                $this->data['payment_methods'][$temp_id] = $disburse->payment_method;
            }
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
        
        // Lookup payment method name if stored as ID
        if (!empty($disburse->payment_method) && is_numeric($disburse->payment_method)) {
            $this->load->model('payment_method_config_model');
            $pm = $this->db->query('SELECT name FROM paymentmenthod WHERE id = ? AND PIN = ? LIMIT 1', array((int)$disburse->payment_method, current_user()->PIN))->row();
            if ($pm) {
                $disburse->payment_method_display = $pm->name;
            } else {
                $disburse->payment_method_display = $disburse->payment_method;
            }
        } else {
            $disburse->payment_method_display = $disburse->payment_method;
        }
        
        $journal_id = $this->cash_disbursement_model->get_journal_entry_id_for_disbursement($id);
        $disburse->journal_entry_id = $journal_id;
        $disburse->is_posted_to_gl = $journal_id ? $this->finance_model->is_journal_entry_posted_to_gl($journal_id) : false;
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
        
        if ($this->cash_disbursement_model->is_disbursement_posted_to_gl($id)) {
            $this->session->set_flashdata('warning', lang('cash_disbursement_cannot_delete_posted'));
            redirect(current_lang() . '/cash_disbursement/cash_disbursement_list', 'refresh');
            return;
        }
        
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
        
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $cash_disbursements = $this->cash_disbursement_model->get_cash_disbursements(null, null, $date_from, $date_to)->result();
        
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
