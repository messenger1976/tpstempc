<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of finance
 *
 * @author miltone
 */
class Finance extends CI_Controller {

    //put your code here


    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_finance');
        $this->lang->load('setting');
        $this->lang->load('finance');
        $this->load->model('member_model');
        $this->load->model('finance_model');
        $this->load->model('setting_model');
    }

    function index() {
        
    }

    function finance_account_create($parent_account = null) {

        $this->data['parent'] = $parent_account;
        $this->data['title'] = lang('finance_account_create');
        if (!is_null($parent_account)) {
            $this->data['parent_info'] = $this->finance_model->account_chart(null, $parent_account)->row();
        }
        $this->form_validation->set_rules('accountcode', lang('finance_account_code'), 'required');
        $this->form_validation->set_rules('account_type', lang('member_group_description'), 'required');
        $this->form_validation->set_rules('accountname', lang('finance_account_name'), 'required');
        $this->form_validation->set_rules('accountdescription', lang('finance_account_description'), '');
      

        if ($this->form_validation->run() == TRUE) {

            /*$parent_acc = '';
            $parent_path = '';
            if (!is_null($parent_account)) {
                $parent_account_info = $this->finance_model->account_chart(null, $parent_account)->row();
                $parent_acc = $parent_account_info->account;
                $parent_path = $parent_account_info->path . '/' . $parent_acc;
            }
            $is_header = 0;
            if ($this->input->post('is_header')) {
                $is_header = 1;
            }
            */
            
            $tmp = $this->input->post('account_type');
            $name = $this->input->post('accountname');
            $accountcode = $this->input->post('accountcode');
            $description = $this->input->post('accountdescription');
            $tmp1 = explode('_', $tmp);
            $accounttype = $tmp1[0];
            $accounttype_sub = $tmp1[1];
            
            
            $create_account = array(
                'account_type' => $accounttype,
                'sub_account_type' => $accounttype_sub,
                'name' => trim($name),
                'account' => $accountcode,
                'description' => trim($description),
                'createdby' => current_user()->id,
                'PIN' =>  current_user()->PIN
            );
            $return = $this->finance_model->create_chart_account($create_account);
            if ($return) {
                $this->session->set_flashdata('message', lang('finance_account_create_success'));
                redirect(current_lang() . '/finance/finance_account_create/' . $parent_account, 'refresh');
            } else {
                $this->data['warning'] = lang('finance_account_create_fail');
            }
        }

        $this->data['account_typelist'] = $this->finance_model->account_typelist()->result();

        $this->data['content'] = 'finance/create_account';
        $this->load->view('template', $this->data);
    }

    function finance_account_edit($id) {
        $this->data['id'] = $id;
        $id = decode_id($id);
        $accountinfo = $this->finance_model->account_chart($id, null)->row();

        $this->data['parent'] = null;

        $this->data['title'] = lang('finance_account_edit');
        if ($accountinfo->account_parent != 0) {
            $this->data['parent_info'] = $this->finance_model->account_chart(null, $accountinfo->account_parent)->row();
        }
        $this->form_validation->set_rules('accountcode', lang('finance_account_code'), 'required');
        $this->form_validation->set_rules('account_type', lang('member_group_description'), 'required');
        $this->form_validation->set_rules('accountname', lang('finance_account_name'), 'required');
        $this->form_validation->set_rules('accountdescription', lang('finance_account_description'), '');
        $this->form_validation->set_rules('is_header', lang('finance_account_is_header'), '');


        if ($this->form_validation->run() == TRUE) {

            
             $tmp = $this->input->post('account_type');
            $name = $this->input->post('accountname');
            $accountcode = $this->input->post('accountcode');
            $description = $this->input->post('accountdescription');
            $tmp1 = explode('_', $tmp);
            $accounttype = $tmp1[0];
            $accounttype_sub = $tmp1[1];
            
            
            $create_account = array(
                'account_type' => $accounttype,
                'sub_account_type' => $accounttype_sub,
                'account' => trim($accountcode),
                'name' => trim($name),
                'description' => trim($description),
                'PIN' =>  current_user()->PIN
            );
           
            $return = $this->finance_model->edit_chart_account($create_account, $id);
            if ($return) {
                $this->session->set_flashdata('message', lang('finance_account_create_success'));
                redirect(current_lang() . '/finance/finance_account_edit/' . $this->data['id'], 'refresh');
            } else {
                $this->data['warning'] = lang('finance_account_create_fail');
            }
        }

        $this->data['accountinfo'] = $this->finance_model->account_chart($id, null)->row();
        $this->data['account_typelist'] = $this->finance_model->account_typelist()->result();
        $this->data['content'] = 'finance/edit_account_chart';
        $this->load->view('template', $this->data);
    }

    function finance_account_list() {
        $this->data['title'] = lang('finance_account_list');
        $account_chart = $this->finance_model->account_chart()->result();
        
        // Sort by account field (ASC)
        usort($account_chart, function($a, $b) {
            return (int)$a->account - (int)$b->account;
        });
        
        $this->data['account_chart'] = $account_chart;
        $this->data['content'] = 'finance/account_chart_list';
        $this->load->view('template', $this->data);
    }

    function finance_account_list_print() {
        $this->data['title'] = 'Chart of Accounts';
        // Get accounts organized by account type for ladder display
        $account_chart_by_type = $this->finance_model->account_chart_by_accounttype();
        
        // Sort account types by account code (ASC)
        uasort($account_chart_by_type, function($a, $b) {
            $account_a = isset($a['info']->account) ? (int)$a['info']->account : 0;
            $account_b = isset($b['info']->account) ? (int)$b['info']->account : 0;
            return $account_a - $account_b;
        });
        
        // Sort accounts within each type by account field (ASC)
        foreach ($account_chart_by_type as $type_id => $type_data) {
            if (isset($type_data['data']) && is_array($type_data['data'])) {
                usort($type_data['data'], function($a, $b) {
                    return (int)$a->account - (int)$b->account;
                });
                $account_chart_by_type[$type_id]['data'] = $type_data['data'];
            }
        }
        
        $this->data['account_chart_by_type'] = $account_chart_by_type;
        
        // Sort the flat list by account field (ASC)
        $account_chart = $this->finance_model->account_chart()->result();
        usort($account_chart, function($a, $b) {
            return (int)$a->account - (int)$b->account;
        });
        $this->data['account_chart'] = $account_chart;
        
        $this->load->view('finance/print/account_chart_list_print', $this->data);
    }

    function finance_account_list_export() {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Disable CodeIgniter's output completely
        $this->output->enable_profiler(FALSE);
        // Prevent CodeIgniter from sending output
        $this->output->set_output('');
        
        // Load Excel library
        $this->load->library('excel');
        
        // Get account chart data - same as list function
        $account_chart = $this->finance_model->account_chart()->result();
        
        // Check if we have data
        if (empty($account_chart) || !is_array($account_chart) || count($account_chart) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            exit();
        }
        
        // Sort by account field (ASC)
        usort($account_chart, function($a, $b) {
            return (int)$a->account - (int)$b->account;
        });
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Chart of Accounts")
                                     ->setSubject("Chart of Accounts Export")
                                     ->setDescription("Chart of Accounts exported from " . company_info()->name);
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Chart of Accounts');
        
        // Set column headers
        $sheet->setCellValue('A1', lang('sno'));
        $sheet->setCellValue('B1', lang('account_no'));
        $sheet->setCellValue('C1', lang('finance_account_type'));
        $sheet->setCellValue('D1', lang('finance_account_name'));
        $sheet->setCellValue('E1', lang('finance_account_description'));
        
        // Style the header row
        $headerStyle = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FFFFFF'),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '4472C4')
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );
        
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(40);
        
        // Populate data
        $row = 2;
        $i = 1;
        foreach ($account_chart as $account) {
            // Get account type name - exactly as in view
            $account_type_name = '';
            if (isset($account->account_type)) {
                $account_type_result = $this->finance_model->account_type(null, $account->account_type);
                if ($account_type_result && $account_type_result->num_rows() > 0) {
                    $account_type = $account_type_result->row();
                    $account_type_name = isset($account_type->name) ? $account_type->name : '';
                }
            }
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $account->account);
            $sheet->setCellValue('C' . $row, $account_type_name);
            $sheet->setCellValue('D' . $row, $account->name);
            $sheet->setCellValue('E' . $row, $account->description);
            
            // Add borders to cells
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            
            $row++;
        }
        
        // Set filename
        $filename = 'Chart_of_Accounts_' . date('Y-m-d_His') . '.xls';
        
        // Clear any remaining output buffers before sending headers
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Set headers - MUST be before any output
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        
        // Create writer and output directly
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        
        // Exit immediately to prevent any further output
        exit();
    }

    function finance_account_delete($id) {
        $id = decode_id($id);
        
        // Check if account exists and belongs to current user's PIN
        $account_info = $this->finance_model->account_chart($id, null)->row();
        
        if (!$account_info) {
            $this->session->set_flashdata('warning', lang('finance_account_not_found'));
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            return;
        }
        
        if ($account_info->PIN != current_user()->PIN) {
            $this->session->set_flashdata('warning', lang('finance_account_not_found'));
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            return;
        }
        
        // Check if account has transactions
        $has_transactions = $this->finance_model->check_account_has_transactions($account_info->account);
        
        if ($has_transactions) {
            $this->session->set_flashdata('warning', lang('finance_account_has_transactions'));
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            return;
        }
        
        // Proceed with deletion
        $result = $this->finance_model->delete_chart_account($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('finance_account_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('finance_account_delete_fail'));
        }
        
        redirect(current_lang() . '/finance/finance_account_list', 'refresh');
    }

    function journalentry() {
        $this->data['title'] = lang('journalentry');
        $this->load->model('customer_model');
        $this->load->model('supplier_model');
        $this->load->model('loan_model');
        $this->form_validation->set_rules('issue_date', lang('journalentry_date'), 'required|valid_date');
        $this->form_validation->set_rules('document_no', lang('journalentry_document_no'), 'required|trim|max_length[100]');
        $this->form_validation->set_rules('description11', lang('description'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $array_items = array();
            $account = $this->input->post('account');
            $description = $this->input->post('description');
            $credit = $this->input->post('credit');
            $debit = $this->input->post('debit');
            $link_type = $this->input->post('link_type');
            $link_entity = $this->input->post('link_entity');
            $act = count($account);
            $date = format_date(trim($this->input->post('issue_date')));
            $out_description = trim($this->input->post('description11'));
            $out_document_no = trim($this->input->post('document_no'));
            // Auto Reference #: JV-{YYYY}{######} based on entry date year
            $entry_year = date('Y', strtotime($date));
            $out_reference_no = $this->finance_model->get_next_journal_voucher_no($entry_year);
            $summ_credit = $this->input->post('summation_credit');
            $summ_debit = $this->input->post('summation_debit');

            if ($summ_credit == $summ_debit) {
                for ($i = 0; $i < $act; $i++) {
                    $account_code = $account[$i];
                    $credit_amount = str_replace(',','',$credit[$i]);
                    $debit_amount = str_replace(',','',$debit[$i]);
                    $description_data = $description[$i];

                    $tmp_array = array();
                    if (empty($account_code) || (empty($credit_amount) && empty($debit_amount))) {
                        
                    } else {
                        $tmp_array['account'] = $account_code;
                        $tmp_array['description'] = $description_data;
                        $tmp_array['credit'] = ($credit_amount > 0 ? $credit_amount : 0);
                        $tmp_array['debit'] = ($debit_amount > 0 ? $debit_amount : 0);
                        $tmp_array['entrydate'] = $date;
                        $tmp_array['createdby'] = current_user()->id;

                        $lt = (is_array($link_type) && isset($link_type[$i])) ? $link_type[$i] : '';
                        $le = (is_array($link_entity) && isset($link_entity[$i])) ? $link_entity[$i] : '';
                        $link_fields = $this->finance_model->resolve_journal_line_link($lt, $le);
                        foreach ($link_fields as $lk => $lv) {
                            $tmp_array[$lk] = $lv;
                        }

                        $array_items[] = $tmp_array;
                    }
                }
                
                $main_array = array(
                    'entrydate' => $date,
                    'description' => $out_description,
                    'reference_no' => $out_reference_no,
                    'document_no' => $out_document_no
                );
                
                // Create journal entry (NOT auto-posted - requires approval)
                $insert = $this->finance_model->enter_journal($main_array,$array_items, false);
                if($insert){
                    $this->session->set_flashdata('message','Journal Entry Created Successfully. It will be posted to General Ledger after approval.');
                    redirect(current_lang().'/finance/journalentry','refresh');
                } else {
                    $this->data['warning'] = 'Failed to create journal entry. Please check the error logs for details.';
                    log_message('error', 'Journal entry creation failed from controller. Array items count: ' . count($array_items));
                }
                
            } else {
                $this->data['warning'] = 'Make sure summmation of credit and debit are equal';
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        $this->data['customerlist'] = $this->customer_model->customer_info()->result();
        $this->data['supplierlist'] = $this->supplier_model->supplier_info()->result();
        $this->data['loanlist'] = $this->loan_model->loan_repay_list();
        $this->data['next_reference_no'] = $this->finance_model->get_next_journal_voucher_no(date('Y'));
        
        // Get count of unposted entries for display
        $this->data['unposted_count'] = count($this->finance_model->get_unposted_journal_entries());

        $this->data['content'] = 'finance/journalentry';
        $this->load->view('template', $this->data);
    }

    /**
     * Display list of manual journal entries (general_journal_entry).
     * Defaults date_from/date_to to current date. Persists selected dates in session when navigating away and back.
     */
    function journal_entry_list() {
        $this->load->helper('text');
        $this->data['title'] = lang('journal_entry_list');

        $today = date('Y-m-d');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $clear = $this->input->get('clear') === '1' || $this->input->get('clear') === 1;

        if ($clear) {
            $this->session->unset_userdata(array('journal_entry_list_date_from', 'journal_entry_list_date_to'));
            redirect(current_lang() . '/finance/journal_entry_list?date_from=' . $today . '&date_to=' . $today, 'refresh');
            return;
        } elseif (!empty($date_from) || !empty($date_to)) {
            $date_from = !empty($date_from) ? $date_from : $today;
            $date_to = !empty($date_to) ? $date_to : $today;
            $this->session->set_userdata('journal_entry_list_date_from', $date_from);
            $this->session->set_userdata('journal_entry_list_date_to', $date_to);
        } else {
            $stored_from = $this->session->userdata('journal_entry_list_date_from');
            $stored_to = $this->session->userdata('journal_entry_list_date_to');
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
        $this->data['journal_entries'] = $this->finance_model->get_journal_entries($date_from, $date_to);

        $this->data['content'] = 'finance/journal_entry_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Edit an unposted (draft) manual journal entry.
     */
    function journal_entry_edit($id) {
        $encoded_id = $id;
        $id = decode_id($id);
        $this->data['title'] = lang('journal_entry_edit');
        $this->data['id'] = $encoded_id;

        if (!has_role(6, 'Edit_journal_entry') && !has_role(6, 'Journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to edit journal entries.');
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            return;
        }

        $entry = $this->finance_model->get_journal_entry_details($id);
        if (!$entry) {
            $this->session->set_flashdata('warning', lang('journal_entry_not_found'));
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            return;
        }
        if (!empty($entry->is_posted)) {
            $this->session->set_flashdata('warning', lang('journal_entry_cannot_edit_posted'));
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            return;
        }

        $this->load->model('customer_model');
        $this->load->model('supplier_model');
        $this->load->model('loan_model');
        $this->form_validation->set_rules('issue_date', lang('journalentry_date'), 'required|valid_date');
        $this->form_validation->set_rules('document_no', lang('journalentry_document_no'), 'required|trim|max_length[100]');
        $this->form_validation->set_rules('description11', lang('description'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $array_items = array();
            $account = $this->input->post('account');
            $description = $this->input->post('description');
            $credit = $this->input->post('credit');
            $debit = $this->input->post('debit');
            $link_type = $this->input->post('link_type');
            $link_entity = $this->input->post('link_entity');
            $act = is_array($account) ? count($account) : 0;
            $date = format_date(trim($this->input->post('issue_date')));
            $out_description = trim($this->input->post('description11'));
            $out_document_no = trim($this->input->post('document_no'));
            $summ_credit = $this->input->post('summation_credit');
            $summ_debit = $this->input->post('summation_debit');

            if ($summ_credit == $summ_debit && $act > 0) {
                for ($i = 0; $i < $act; $i++) {
                    $account_code = $account[$i];
                    $credit_amount = str_replace(',', '', $credit[$i]);
                    $debit_amount = str_replace(',', '', $debit[$i]);
                    $description_data = $description[$i];

                    if (empty($account_code) || (empty($credit_amount) && empty($debit_amount))) {
                        continue;
                    }

                    $tmp_array = array(
                        'account' => $account_code,
                        'description' => $description_data,
                        'credit' => ($credit_amount > 0 ? $credit_amount : 0),
                        'debit' => ($debit_amount > 0 ? $debit_amount : 0),
                        'entrydate' => $date,
                        'createdby' => current_user()->id,
                    );

                    $lt = (is_array($link_type) && isset($link_type[$i])) ? $link_type[$i] : '';
                    $le = (is_array($link_entity) && isset($link_entity[$i])) ? $link_entity[$i] : '';
                    $link_fields = $this->finance_model->resolve_journal_line_link($lt, $le);
                    foreach ($link_fields as $lk => $lv) {
                        $tmp_array[$lk] = $lv;
                    }
                    $array_items[] = $tmp_array;
                }

                if (!empty($array_items)) {
                    $main_array = array(
                        'entrydate' => $date,
                        'description' => $out_description,
                        'document_no' => $out_document_no,
                    );
                    $updated = $this->finance_model->update_journal_entry($id, $main_array, $array_items);
                    if ($updated) {
                        $this->session->set_flashdata('message', lang('journal_entry_update_success'));
                        redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
                        return;
                    }
                    $this->data['warning'] = lang('journal_entry_update_fail');
                } else {
                    $this->data['warning'] = lang('journal_entry_no_items');
                }
            } else {
                $this->data['warning'] = 'Make sure summmation of credit and debit are equal';
            }
        }

        $this->data['entry'] = $entry;
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        $this->data['customerlist'] = $this->customer_model->customer_info()->result();
        $this->data['supplierlist'] = $this->supplier_model->supplier_info()->result();
        $this->data['loanlist'] = $this->loan_model->loan_repay_list();
        $this->data['content'] = 'finance/journal_entry_edit';
        $this->load->view('template', $this->data);
    }

    /**
     * Delete an unposted manual journal entry.
     */
    function journal_entry_delete($id) {
        $id = decode_id($id);

        if ($this->finance_model->is_journal_posted($id)) {
            $this->session->set_flashdata('warning', lang('journal_entry_cannot_delete_posted'));
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            return;
        }

        $result = $this->finance_model->delete_journal_entry($id);

        if ($result) {
            $this->session->set_flashdata('message', lang('journal_entry_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('journal_entry_delete_fail'));
        }

        redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
    }

    /**
     * Export journal entries to Excel.
     */
    function journal_entry_export() {
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());

        $this->load->library('excel');

        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $entries = $this->finance_model->get_journal_entries($date_from, $date_to);

        if (empty($entries)) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', lang('no_records_found'));
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            exit();
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle('Journal Entries')
                                     ->setSubject('Journal Entries Export')
                                     ->setDescription('Journal Entries exported from ' . company_info()->name);

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Journal Entries');

        $sheet->setCellValue('A1', lang('journal_entry_no'));
        $sheet->setCellValue('B1', lang('journalentry_date'));
        $sheet->setCellValue('C1', lang('journalentry_reference_no'));
        $sheet->setCellValue('D1', lang('journalentry_document_no'));
        $sheet->setCellValue('E1', lang('journalentry_description'));
        $sheet->setCellValue('F1', lang('journalentry_debit'));
        $sheet->setCellValue('G1', lang('journalentry_credit'));
        $sheet->setCellValue('H1', lang('status'));

        $row = 2;
        foreach ($entries as $entry) {
            $status = !empty($entry->is_posted) ? lang('journal_entry_status_posted') : lang('journal_entry_status_draft');
            $sheet->setCellValue('A' . $row, $entry->entryid);
            $sheet->setCellValue('B' . $row, $entry->entrydate);
            $sheet->setCellValue('C' . $row, isset($entry->reference_no) ? $entry->reference_no : '');
            $sheet->setCellValue('D' . $row, isset($entry->document_no) ? $entry->document_no : '');
            $sheet->setCellValue('E' . $row, $entry->description);
            $sheet->setCellValue('F' . $row, number_format($entry->total_debit, 2, '.', ''));
            $sheet->setCellValue('G' . $row, number_format($entry->total_credit, 2, '.', ''));
            $sheet->setCellValue('H' . $row, $status);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Journal_Entries_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Print a manual journal entry.
     */
    function journal_entry_print($id) {
        $id = decode_id($id);
        $entry = $this->finance_model->get_journal_entry_details($id);

        if (!$entry) {
            $this->session->set_flashdata('warning', lang('journal_entry_not_found'));
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            return;
        }

        $this->data['entry'] = $entry;
        $this->data['id'] = encode_id($id);
        $this->load->view('finance/print/journal_entry_print', $this->data);
    }

    // Journal Entry Review and Approval
    function journal_entry_review() {
        $this->data['title'] = 'Journal Entry Review & Approval';
        
        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to access this page.');
            redirect(current_lang() . '/dashboard', 'refresh');
            return;
        }

        // Posted to GL entries (so user can void and repost)
        $posted_general = $this->finance_model->get_posted_general_journal_entries();
        foreach ($posted_general as $e) {
            $e->entry_source = 'general_journal';
            $e->reference_id = null;
        }
        $posted_receipt_disburse = $this->finance_model->get_posted_receipt_disbursement_journal_entries();
        $this->data['posted_entries'] = array_merge($posted_general, $posted_receipt_disburse);
        usort($this->data['posted_entries'], function ($a, $b) {
            $da = strtotime($a->entrydate);
            $db = strtotime($b->entrydate);
            if ($da !== $db) return $db - $da;
            return $b->entryid - $a->entryid;
        });
        
        $this->data['content'] = 'finance/journal_entry_review';
        $this->load->view('template', $this->data);
    }

    /**
     * DataTables server-side JSON feed for unposted journal entries on the review page.
     */
    function journal_entry_review_unposted_data() {
        if (!has_role(6, 'Review_journal_entry')) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'draw' => (int) $this->input->post('draw'),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => array(),
                    'error' => 'Access denied',
                )));
            return;
        }

        $draw = (int) $this->input->post('draw');
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');
        if ($length <= 0) {
            $length = 25;
        }
        $search_post = $this->input->post('search');
        $search = (is_array($search_post) && isset($search_post['value'])) ? trim((string) $search_post['value']) : '';
        $source_filter = trim((string) $this->input->post('source_filter'));
        if ($source_filter === '') {
            $source_filter = 'all';
        }
        $order_column_index = 3;
        $order_dir = 'desc';
        $order = $this->input->post('order');
        if (is_array($order) && isset($order[0]['column'])) {
            $order_column_index = (int) $order[0]['column'];
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';
        }

        try {
            $result = $this->finance_model->get_unposted_journal_review_datatable(
                $start,
                $length,
                $search,
                $order_column_index,
                $order_dir,
                $source_filter
            );

            $data = array();
            foreach ($result['entries'] as $entry) {
                $data[] = $this->_journal_review_unposted_row($entry);
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'draw' => $draw,
                    'recordsTotal' => (int) $result['recordsTotal'],
                    'recordsFiltered' => (int) $result['recordsFiltered'],
                    'data' => $data,
                    'grand_total_debit' => number_format($result['grand_total_debit'], 2, '.', ''),
                    'grand_total_credit' => number_format($result['grand_total_credit'], 2, '.', ''),
                )));
        } catch (Exception $e) {
            log_message('error', 'journal_entry_review_unposted_data: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => array(),
                    'error' => 'Failed to load journal entries',
                )));
        }
    }

    /**
     * Build one DataTables row (HTML cells) for an unposted journal review entry.
     */
    private function _journal_review_unposted_row($entry) {
        $entry_source = isset($entry->entry_source) ? $entry->entry_source : 'general_journal';
        $is_general = ($entry_source === 'general_journal');
        $lang = current_lang();

        $view_url = $lang . '/finance/journal_entry_view/' . encode_id($entry->entryid);
        if ($entry_source === 'cash_disbursement' && isset($entry->reference_id)) {
            $view_url = $lang . '/cash_disbursement/cash_disbursement_view/' . encode_id($entry->reference_id);
        } elseif ($entry_source === 'cash_receipt' && isset($entry->reference_id)) {
            $view_url = $lang . '/cash_receipt/cash_receipt_view/' . encode_id($entry->reference_id);
        }

        $source_label = function_exists('journal_source_label') ? journal_source_label($entry_source) : $entry_source;
        $entry_debit = isset($entry->total_debit) ? floatval($entry->total_debit) : 0;
        $entry_credit = isset($entry->total_credit) ? floatval($entry->total_credit) : 0;
        $balanced = abs($entry_debit - $entry_credit) <= 0.01;

        $checkbox = $is_general
            ? '<input type="checkbox" name="entry_ids[]" value="' . htmlspecialchars(encode_id($entry->entryid), ENT_QUOTES, 'UTF-8') . '" class="entry-checkbox"/>'
            : '&mdash;';

        $status_html = $balanced
            ? '<span class="label label-success">Balanced</span>'
            : '<span class="label label-danger">Unbalanced</span>';

        $actions = '<a href="' . site_url($view_url) . '" class="btn btn-info btn-xs" title="View Details">'
            . '<i class="fa fa-eye"></i> View</a>';

        if ($is_general && $balanced) {
            $actions .= ' <a href="' . site_url($lang . '/finance/journal_entry_approve/' . encode_id($entry->entryid)) . '"'
                . ' onclick="return confirm(\'Are you sure you want to approve and post this journal entry?\');"'
                . ' class="btn btn-success btn-xs" title="Approve & Post">'
                . '<i class="fa fa-check"></i> Approve</a>';
        }

        $is_receipt_disburse = in_array($entry_source, array('cash_receipt', 'cash_disbursement'), true);
        $is_posted = !empty($entry->is_posted);
        $can_post_to_gl = $is_receipt_disburse && !$is_posted && $balanced;
        if ($can_post_to_gl) {
            $actions .= ' <a href="' . site_url($lang . '/finance/journal_entry_post_to_gl/' . encode_id($entry->entryid)) . '"'
                . ' onclick="return confirm(\'Post this entry to the General Ledger?\');"'
                . ' class="btn btn-success btn-xs" title="Post to GL">'
                . '<i class="fa fa-book"></i> Post to GL</a>';
        } elseif ($is_receipt_disburse && $is_posted) {
            $actions .= ' <span class="label label-default">Posted to GL</span>';
        }

        return array(
            $checkbox,
            (int) $entry->entryid,
            '<span class="label label-default">' . htmlspecialchars($source_label, ENT_QUOTES, 'UTF-8') . '</span>',
            date('M d, Y', strtotime($entry->entrydate)),
            htmlspecialchars($entry->description, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($entry->created_by_name, ENT_QUOTES, 'UTF-8'),
            isset($entry->line_count) ? (int) $entry->line_count : 0,
            number_format($entry_debit, 2),
            number_format($entry_credit, 2),
            $status_html,
            $actions,
        );
    }

    function journal_entry_view($id) {
        $encoded_id = $id; // Store original encoded ID for logging
        $id = decode_id($id);
        log_message('debug', 'journal_entry_view: Encoded ID=' . $encoded_id . ', Decoded ID=' . $id);
        $this->data['title'] = 'Journal Entry Details';
        
        if (!has_role(6, 'Journal_entry') && !has_role(6, 'View_journal_entry') && !has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to access this page.');
            redirect(current_lang() . '/dashboard', 'refresh');
            return;
        }

        $entry = $this->finance_model->get_journal_entry_details($id);

        if (!$entry) {
            $this->session->set_flashdata('warning', lang('journal_entry_not_found'));
            redirect(current_lang() . '/finance/journal_entry_list', 'refresh');
            return;
        }

        $this->data['entry'] = $entry;
        $this->data['id'] = encode_id($id);

        if ($this->input->get('popup')) {
            $this->data['is_popup'] = true;
            $this->load->view('finance/journal_entry_view_popup', $this->data);
            return;
        }

        $this->data['content'] = 'finance/journal_entry_view';
        $this->load->view('template', $this->data);
    }

    function journal_entry_approve($id) {
        $id = decode_id($id);
        
        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to approve journal entries.');
            redirect(current_lang() . '/dashboard', 'refresh');
            return;
        }
        
        // Check if entry exists and is not posted
        $entry = $this->finance_model->get_journal_entry_details($id);
        
        if (!$entry) {
            $this->session->set_flashdata('warning', 'Journal entry not found.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        
        if ($entry->is_posted) {
            $this->session->set_flashdata('warning', 'This journal entry has already been posted.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        
        // Verify debits equal credits
        if (abs($entry->total_debit - $entry->total_credit) > 0.01) {
            $this->session->set_flashdata('warning', 'Journal entry is not balanced. Debits: ' . number_format($entry->total_debit, 2) . ', Credits: ' . number_format($entry->total_credit, 2));
            redirect(current_lang() . '/finance/journal_entry_view/' . encode_id($id), 'refresh');
            return;
        }
        
        // Post to general ledger
        $result = $this->finance_model->post_journal_to_general_ledger($id, 5);
        
        if ($result) {
            $this->session->set_flashdata('message', 'Journal Entry #' . $id . ' has been approved and posted to General Ledger successfully.');
        } else {
            $this->session->set_flashdata('warning', 'Failed to post journal entry. Please check error logs.');
        }
        
        redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
    }

    /**
     * Post a single cash receipt or cash disbursement journal entry to General Ledger.
     * Used for entries from journal_entry table (not general_journal_entry).
     */
    function journal_entry_post_to_gl($id) {
        $id = decode_id($id);

        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to post journal entries to GL.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }

        if ($this->finance_model->is_journal_entry_posted_to_gl($id)) {
            $this->session->set_flashdata('warning', 'This entry has already been posted to the General Ledger.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }

        $result = $this->finance_model->post_journal_entry_to_general_ledger($id, 5);

        if ($result) {
            $this->session->set_flashdata('message', 'Journal entry has been posted to General Ledger successfully.');
        } else {
            $this->session->set_flashdata('warning', 'Failed to post to General Ledger. Entry may be unbalanced or not found. Check error logs.');
        }
        redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
    }

    /**
     * Void GL posting for a manual journal entry (general_journal). Journal entry stays active; can repost later.
     */
    function void_gl_posting_general($id) {
        $id = decode_id($id);
        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to void GL postings.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        if (!$this->finance_model->is_journal_posted($id)) {
            $this->session->set_flashdata('warning', 'This journal entry is not posted to the General Ledger.');
            redirect(current_lang() . '/finance/journal_entry_view/' . encode_id($id), 'refresh');
            return;
        }
        $this->finance_model->void_journal_posting_to_gl($id, 'general_journal');
        $this->session->set_flashdata('message', 'GL posting has been voided. You can repost this journal entry to the General Ledger when ready.');
        redirect(current_lang() . '/finance/journal_entry_view/' . encode_id($id), 'refresh');
    }

    /**
     * Void GL posting for a journal entry (cash receipt / cash disbursement). Journal stays active; can repost later.
     */
    function void_gl_posting_journal_entry($id) {
        $id = decode_id($id);
        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to void GL postings.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        if (!$this->finance_model->is_journal_entry_posted_to_gl($id)) {
            $this->session->set_flashdata('warning', 'This entry is not posted to the General Ledger.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        $this->finance_model->void_journal_posting_to_gl($id, 'journal_entry');
        $this->session->set_flashdata('message', 'GL posting has been voided. You can repost this entry to the General Ledger from Journal Entry Review when ready.');
        redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
    }

    /**
     * Void GL posting for multiple selected entries (batch). Expects void_ids[] = "source::encoded_id" (e.g. general_journal::xxx or journal_entry::xxx).
     */
    function void_gl_posting_batch() {
        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to void GL postings.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        $void_ids = $this->input->post('void_ids', FALSE);
        if (empty($void_ids)) {
            $this->session->set_flashdata('warning', 'No entries selected. Please select at least one posted entry to void.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        if (!is_array($void_ids)) {
            $void_ids = array($void_ids);
        }
        $success_count = 0;
        $skip_count = 0;
        $invalid_count = 0;
        foreach ($void_ids as $composite) {
            $composite = trim((string) $composite);
            if ($composite === '') continue;
            $parts = explode('::', $composite, 2);
            if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
                $invalid_count++;
                continue;
            }
            list($source, $encoded_id) = $parts;
            // Map source to GL fromtable: only general_journal vs journal_entry (cash_receipt/cash_disbursement use journal_entry table)
            $from_table = ($source === 'general_journal') ? 'general_journal' : 'journal_entry';
            if ($source !== 'general_journal' && !in_array($source, array('journal_entry', 'cash_receipt', 'cash_disbursement'), true)) {
                $invalid_count++;
                continue;
            }
            $id = decode_id($encoded_id);
            if ($id === null || $id === '' || (is_numeric($id) && (int) $id <= 0)) {
                $invalid_count++;
                continue;
            }
            if ($from_table === 'general_journal' && !$this->finance_model->is_journal_posted($id)) {
                $skip_count++;
                continue;
            }
            if ($from_table === 'journal_entry' && !$this->finance_model->is_journal_entry_posted_to_gl($id)) {
                $skip_count++;
                continue;
            }
            $this->finance_model->void_journal_posting_to_gl($id, $from_table);
            $success_count++;
        }
        if ($success_count > 0) {
            $this->session->set_flashdata('message', $success_count . ' GL posting(s) voided. You can repost from Journal Entry Review when ready.');
        }
        if ($skip_count > 0) {
            $this->session->set_flashdata('warning', $skip_count . ' selected entry/entries were not posted to GL and were skipped.');
        }
        if ($invalid_count > 0) {
            $this->session->set_flashdata('warning', $invalid_count . ' selected value(s) were invalid and skipped.');
        }
        if ($success_count === 0 && $skip_count === 0 && $invalid_count === 0) {
            $this->session->set_flashdata('warning', 'No entries were voided. Please select at least one posted entry and try again.');
        }
        redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
    }

    function journal_entry_batch_approve() {
        if (!has_role(6, 'Review_journal_entry')) {
            $this->session->set_flashdata('warning', 'You do not have permission to approve journal entries.');
            redirect(current_lang() . '/dashboard', 'refresh');
            return;
        }
        
        $entry_ids = $this->input->post('entry_ids');
        
        if (empty($entry_ids) || !is_array($entry_ids)) {
            $this->session->set_flashdata('warning', 'No journal entries selected.');
            redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
            return;
        }
        
        $success_count = 0;
        $failed_count = 0;
        
        foreach ($entry_ids as $entry_id) {
            $id = decode_id($entry_id);
            
            // Check if already posted
            if ($this->finance_model->is_journal_posted($id)) {
                continue;
            }
            
            // Get entry details
            $entry = $this->finance_model->get_journal_entry_details($id);
            
            if (!$entry) {
                $failed_count++;
                continue;
            }
            
            // Verify balance
            if (abs($entry->total_debit - $entry->total_credit) > 0.01) {
                $failed_count++;
                continue;
            }
            
            // Post to general ledger
            $result = $this->finance_model->post_journal_to_general_ledger($id, 5);
            
            if ($result) {
                $success_count++;
            } else {
                $failed_count++;
            }
        }
        
        if ($success_count > 0) {
            $this->session->set_flashdata('message', $success_count . ' journal entry/entries approved and posted successfully.');
        }
        
        if ($failed_count > 0) {
            $this->session->set_flashdata('warning', $failed_count . ' journal entry/entries failed to post. Please check for errors.');
        }
        
        redirect(current_lang() . '/finance/journal_entry_review', 'refresh');
    }

    // Chart Type Management
    function chart_type_list() {
        $this->data['title'] = lang('chart_type_list');
        $this->data['chart_types'] = $this->finance_model->account_type()->result();
        $this->data['content'] = 'finance/chart_type_list';
        $this->load->view('template', $this->data);
    }

    function chart_type_create($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->data['title'] = lang('chart_type_edit');
            $this->data['chart_type'] = $this->finance_model->account_type($id)->row();
        } else {
            $this->data['title'] = lang('chart_type_create');
        }

        $this->form_validation->set_rules('name', lang('chart_type_name'), 'required');
        $this->form_validation->set_rules('account', lang('chart_type_account'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'name' => trim($this->input->post('name')),
                'account' => trim($this->input->post('account'))
            );

            if (is_null($id)) {
                // Check if account number already exists
                $existing = $this->finance_model->account_type(null, $data['account'])->row();
                if ($existing) {
                    $this->data['warning'] = lang('chart_type_account_exists');
                } else {
                    $result = $this->finance_model->create_chart_type($data);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_type_create_success'));
                        redirect(current_lang() . '/finance/chart_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_type_create_fail');
                    }
                }
            } else {
                // Check if account number already exists for another record
                $existing = $this->finance_model->account_type(null, $data['account'])->row();
                if ($existing && $existing->id != $id) {
                    $this->data['warning'] = lang('chart_type_account_exists');
                } else {
                    $result = $this->finance_model->update_chart_type($data, $id);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_type_update_success'));
                        redirect(current_lang() . '/finance/chart_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_type_update_fail');
                    }
                }
            }
        }

        $this->data['content'] = 'finance/chart_type_form';
        $this->load->view('template', $this->data);
    }

    function chart_type_edit($id) {
        $this->chart_type_create($id);
    }

    function chart_type_delete($id) {
        $id = decode_id($id);
        $result = $this->finance_model->delete_chart_type($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('chart_type_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('chart_type_delete_fail_in_use'));
        }
        
        redirect(current_lang() . '/finance/chart_type_list', 'refresh');
    }

    // Chart Sub Type Management
    function chart_sub_type_list() {
        $this->data['title'] = lang('chart_sub_type_list');
        $this->data['chart_sub_types'] = $this->finance_model->account_type_sub()->result();
        $this->data['chart_types'] = $this->finance_model->account_type()->result();
        $this->data['content'] = 'finance/chart_sub_type_list';
        $this->load->view('template', $this->data);
    }

    function chart_sub_type_create($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->data['title'] = lang('chart_sub_type_edit');
            $this->data['chart_sub_type'] = $this->finance_model->account_type_sub($id)->row();
        } else {
            $this->data['title'] = lang('chart_sub_type_create');
        }

        $this->form_validation->set_rules('name', lang('chart_sub_type_name'), 'required');
        $this->form_validation->set_rules('accounttype', lang('chart_type'), 'required');
        $this->form_validation->set_rules('sub_account', lang('chart_sub_type_account'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'name' => trim($this->input->post('name')),
                'accounttype' => trim($this->input->post('accounttype')),
                'sub_account' => trim($this->input->post('sub_account'))
            );

            if (is_null($id)) {
                // Check if sub account number already exists for this account type
                $existing = $this->finance_model->account_type_sub(null, $data['accounttype'], $data['sub_account'])->row();
                if ($existing) {
                    $this->data['warning'] = lang('chart_sub_type_account_exists');
                } else {
                    $result = $this->finance_model->create_chart_sub_type($data);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_sub_type_create_success'));
                        redirect(current_lang() . '/finance/chart_sub_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_sub_type_create_fail');
                    }
                }
            } else {
                // Check if sub account number already exists for another record
                $existing = $this->finance_model->account_type_sub(null, $data['accounttype'], $data['sub_account'])->row();
                if ($existing && $existing->id != $id) {
                    $this->data['warning'] = lang('chart_sub_type_account_exists');
                } else {
                    $result = $this->finance_model->update_chart_sub_type($data, $id);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_sub_type_update_success'));
                        redirect(current_lang() . '/finance/chart_sub_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_sub_type_update_fail');
                    }
                }
            }
        }

        $this->data['chart_types'] = $this->finance_model->account_type()->result();
        $this->data['content'] = 'finance/chart_sub_type_form';
        $this->load->view('template', $this->data);
    }

    function chart_sub_type_edit($id) {
        $this->chart_sub_type_create($id);
    }

    function chart_sub_type_delete($id) {
        $id = decode_id($id);
        $result = $this->finance_model->delete_chart_sub_type($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('chart_sub_type_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('chart_sub_type_delete_fail_in_use'));
        }
        
        redirect(current_lang() . '/finance/chart_sub_type_list', 'refresh');
    }

    // Beginning Balances Management
    function beginning_balance_list() {
        $this->data['title'] = lang('beginning_balance_list');
        
        // Get selected fiscal year from POST or GET
        $selected_fiscal_year_id = $this->input->post('fiscal_year_id') ? $this->input->post('fiscal_year_id') : $this->input->get('fiscal_year_id');
        
        // Get all fiscal years
        $this->data['fiscal_years'] = $this->setting_model->fiscal_year_list()->result();
        
        // Get beginning balances for selected fiscal year
        if ($selected_fiscal_year_id) {
            $this->data['selected_fiscal_year_id'] = $selected_fiscal_year_id;
            $this->data['beginning_balances'] = $this->finance_model->beginning_balance_list($selected_fiscal_year_id)->result();
            
            // Get fiscal year info
            $fiscal_year = $this->setting_model->fiscal_year_list($selected_fiscal_year_id)->row();
            $this->data['fiscal_year'] = $fiscal_year;
        } else {
            $this->data['selected_fiscal_year_id'] = null;
            $this->data['beginning_balances'] = array();
        }
        
        $this->data['content'] = 'finance/beginning_balance_list';
        $this->load->view('template', $this->data);
    }

    function beginning_balance_create($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        if (is_null($id)) {
            $this->data['title'] = lang('beginning_balance_create');
        } else {
            $this->data['title'] = lang('beginning_balance_edit');
            $this->data['balance'] = $this->finance_model->beginning_balance_list(null, $id)->row();
            
            if (!$this->data['balance']) {
                $this->session->set_flashdata('warning', lang('beginning_balance_not_found'));
                redirect(current_lang() . '/finance/beginning_balance_list', 'refresh');
                return;
            }
            
            // Check if already posted
            if ($this->data['balance']->posted == 1) {
                $this->session->set_flashdata('warning', lang('beginning_balance_already_posted'));
                redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $this->data['balance']->fiscal_year_id, 'refresh');
                return;
            }
        }

        $this->form_validation->set_rules('fiscal_year_id', lang('fiscal_year'), 'required');
        $this->form_validation->set_rules('account', lang('finance_account_code'), 'required');
        $this->form_validation->set_rules('debit', lang('beginning_balance_debit'), 'numeric');
        $this->form_validation->set_rules('credit', lang('beginning_balance_credit'), 'numeric');
        $this->form_validation->set_rules('description', lang('description'), '');

        if ($this->form_validation->run() == TRUE) {
            $fiscal_year_id = $this->input->post('fiscal_year_id');
            $account = trim($this->input->post('account'));
            $debit = floatval(str_replace(',', '', $this->input->post('debit')));
            $credit = floatval(str_replace(',', '', $this->input->post('credit')));
            $description = trim($this->input->post('description'));

            // Validate that account exists
            $account_info = account_row_info($account);
            if (!$account_info) {
                $this->data['warning'] = lang('beginning_balance_account_not_found');
            } else {
                // Check if debit and credit are both zero
                if ($debit == 0 && $credit == 0) {
                    $this->data['warning'] = lang('beginning_balance_amount_required');
                } else {
                    $data = array(
                        'fiscal_year_id' => $fiscal_year_id,
                        'account' => $account,
                        'debit' => $debit,
                        'credit' => $credit,
                        'description' => $description
                    );

                    if (is_null($id)) {
                        // Check if beginning balance already exists for this fiscal year and account
                        if ($this->finance_model->check_beginning_balance_exists($fiscal_year_id, $account)) {
                            $this->data['warning'] = lang('beginning_balance_already_exists');
                        } else {
                            $result = $this->finance_model->beginning_balance_create($data);
                            if ($result) {
                                $this->session->set_flashdata('message', lang('beginning_balance_create_success'));
                                redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $fiscal_year_id, 'refresh');
                            } else {
                                $this->data['warning'] = lang('beginning_balance_create_fail');
                            }
                        }
                    } else {
                        // Check if beginning balance already exists for another record
                        $existing = $this->finance_model->beginning_balance_list($fiscal_year_id)->result();
                        $exists = false;
                        foreach ($existing as $existing_balance) {
                            if ($existing_balance->account == $account && $existing_balance->id != $id) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        if ($exists) {
                            $this->data['warning'] = lang('beginning_balance_already_exists');
                        } else {
                            $result = $this->finance_model->beginning_balance_update($data, $id);
                            if ($result) {
                                $this->session->set_flashdata('message', lang('beginning_balance_update_success'));
                                redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $fiscal_year_id, 'refresh');
                            } else {
                                $this->data['warning'] = lang('beginning_balance_update_fail');
                            }
                        }
                    }
                }
            }
        }

        // Get fiscal years
        $this->data['fiscal_years'] = $this->setting_model->fiscal_year_list()->result();
        
        // Get account list
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        
        $this->data['content'] = 'finance/beginning_balance_form';
        $this->load->view('template', $this->data);
    }

    function beginning_balance_edit($id) {
        $this->beginning_balance_create($id);
    }

    function beginning_balance_delete($id) {
        $id = decode_id($id);
        
        $balance = $this->finance_model->beginning_balance_list(null, $id)->row();
        
        if (!$balance) {
            $this->session->set_flashdata('warning', lang('beginning_balance_not_found'));
            redirect(current_lang() . '/finance/beginning_balance_list', 'refresh');
            return;
        }
        
        // Check if already posted
        if ($balance->posted == 1) {
            $this->session->set_flashdata('warning', lang('beginning_balance_cannot_delete_posted'));
            redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
            return;
        }
        
        $result = $this->finance_model->beginning_balance_delete($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('beginning_balance_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('beginning_balance_delete_fail'));
        }
        
        redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
    }

    function beginning_balance_post($id) {
        $id = decode_id($id);
        
        $balance = $this->finance_model->beginning_balance_list(null, $id)->row();
        
        if (!$balance) {
            $this->session->set_flashdata('warning', lang('beginning_balance_not_found'));
            redirect(current_lang() . '/finance/beginning_balance_list', 'refresh');
            return;
        }
        
        if ($balance->posted == 1) {
            $this->session->set_flashdata('warning', lang('beginning_balance_already_posted'));
            redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
            return;
        }
        
        $result = $this->finance_model->beginning_balance_post_to_ledger($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('beginning_balance_post_success'));
        } else {
            $this->session->set_flashdata('warning', lang('beginning_balance_post_fail'));
        }
        
        redirect(current_lang() . '/finance/beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
    }

}

?>
