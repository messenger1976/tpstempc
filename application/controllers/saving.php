<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of saving
 *
 * @author miltone
 */
class Saving extends CI_Controller {

    //put your code here



    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_saving');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->load->model('member_model');
        $this->load->model('finance_model');
    }
 //Added by Herald
    function saving_account_list() {
        $this->load->library('pagination');
        $this->data['title'] = lang('saving_account_list');
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        
        // Check permission
        if (!has_role(3, 'saving_account_list')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        
        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }
        
        $config["per_page"] = $this->session->userdata('PER_PAGE');
        
        $key = null;
        
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $explode = explode('-', $_POST['key']);
            $key = trim($explode[0]);
        } else if (isset($_GET['key']) && $_GET['key'] != '') {
            $key = trim($_GET['key']);
        }
        
        $account_type_filter = null;
        if (isset($_POST['account_type_filter']) && $_POST['account_type_filter'] != '') {
            $account_type_filter = $_POST['account_type_filter'];
        } else if (isset($_GET['account_type_filter']) && $_GET['account_type_filter'] != '') {
            $account_type_filter = $_GET['account_type_filter'];
        }
        
        // GL posted filter (posted / not posted to General Ledger)
        $gl_posted_filter = null;
        if (isset($_POST['gl_posted_filter']) && $_POST['gl_posted_filter'] != '') {
            $gl_posted_filter = $_POST['gl_posted_filter'];
        } else if (isset($_GET['gl_posted_filter']) && $_GET['gl_posted_filter'] != '') {
            $gl_posted_filter = $_GET['gl_posted_filter'];
        }
        
        // Status filter - default to '1' (Active)
        $status_filter = '1'; // Default to Active
        if (isset($_POST['status_filter']) && $_POST['status_filter'] != '') {
            $status_filter = $_POST['status_filter'];
        } else if (isset($_GET['status_filter']) && $_GET['status_filter'] != '') {
            $status_filter = $_GET['status_filter'];
        }
        
        $suffix_array = array();

        if (!is_null($key) && $key != '') {
            $suffix_array['key'] = $key;
        }
        
        if (!is_null($account_type_filter) && $account_type_filter != '') {
            $suffix_array['account_type_filter'] = $account_type_filter;
        }
        
        if (!is_null($gl_posted_filter) && $gl_posted_filter != '' && $gl_posted_filter != 'all') {
            $suffix_array['gl_posted_filter'] = $gl_posted_filter;
        }
        
        if ($status_filter != '') {
            $suffix_array['status_filter'] = $status_filter;
        }
        
        $this->data['jxy'] = $suffix_array;
        $this->data['account_type_filter'] = $account_type_filter;
        $this->data['gl_posted_filter'] = $gl_posted_filter;
        $this->data['status_filter'] = $status_filter;
        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }
        
        
        $config["base_url"] = site_url(current_lang() . '/saving/saving_account_list');
        $config["total_rows"] = $this->finance_model->count_saving_account($key, $account_type_filter, $status_filter, $gl_posted_filter);
        $config["uri_segment"] = 4;
        
        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';
        
        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';
        
        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';
        
        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';
        
        $config['last_tag_open'] = '<div class="link-pagination">';
        $config['last_tag_close'] = '</div>';
        
        $config['first_tag_open'] = '<div class="link-pagination">';
        $config['first_tag_close'] = '</div>';
        
        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';
        
        
        $config["num_links"] = 10;
        
        
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();
        
        $this->data['saving_accounts'] = $this->finance_model->search_saving_account($key, $config["per_page"], $page, $account_type_filter, $status_filter, $gl_posted_filter);
        $this->data['total_savings_amount'] = $this->finance_model->get_total_savings_amount($key, $account_type_filter, $status_filter, $gl_posted_filter);
        
        $this->data['content'] = 'saving/saving_account_list';
        $this->load->view('template', $this->data);
    }
    
    function saving_account_list_export() {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Disable CodeIgniter's output completely
        $this->output->enable_profiler(FALSE);
        // Prevent CodeIgniter from sending output
        $this->output->set_output('');
        
        // Check permission
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        
        if (!has_role(3, 'saving_account_list')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        // Load Excel library
        $this->load->library('excel');
        
        // Get search parameters (same as saving_account_list)
        $key = null;
        if (isset($_GET['key']) && $_GET['key'] != '') {
            // Handle key extraction same as list function (in case it comes with dash separator)
            $explode = explode('-', $_GET['key']);
            $key = trim($explode[0]);
        }
        
        $account_type_filter = null;
        if (isset($_GET['account_type_filter']) && $_GET['account_type_filter'] != '' && $_GET['account_type_filter'] != 'all') {
            $account_type_filter = $_GET['account_type_filter'];
        }
        
        // GL posted filter for export
        $gl_posted_filter = null;
        if (isset($_GET['gl_posted_filter']) && $_GET['gl_posted_filter'] != '' && $_GET['gl_posted_filter'] != 'all') {
            $gl_posted_filter = $_GET['gl_posted_filter'];
        }
        
        // Status filter - must match saving_account_list logic exactly
        // Default to '1' (Active) if not provided
        $status_filter = '1'; // Default to Active
        if (isset($_GET['status_filter']) && $_GET['status_filter'] != '') {
            $status_filter = $_GET['status_filter'];
        }
        
        // Get total count first to use as limit for export (get all records)
        $total_count = $this->finance_model->count_saving_account($key, $account_type_filter, $status_filter, $gl_posted_filter);
        
        // Get all accounts (use total count + 1000 as limit to ensure we get all records)
        // If total_count is 0, use a reasonable default limit
        $limit = ($total_count > 0) ? $total_count + 1000 : 10000;
        $saving_accounts = $this->finance_model->search_saving_account($key, $limit, 0, $account_type_filter, $status_filter, $gl_posted_filter);
        $total_savings_amount = $this->finance_model->get_total_savings_amount($key, $account_type_filter, $status_filter, $gl_posted_filter);
        
        // Check if we have data
        if (!$saving_accounts || !is_array($saving_accounts) || count($saving_accounts) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            exit();
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Saving Account List")
                                     ->setSubject("Saving Account List Export")
                                     ->setDescription("Saving Account List exported from " . company_info()->name);
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Saving Account List');
        
        // Set column headers
        $sheet->setCellValue('A1', 'S/No');
        $sheet->setCellValue('B1', lang('account_number'));
        $sheet->setCellValue('C1', lang('member_member_id'));
        $sheet->setCellValue('D1', lang('member_fullname'));
        $sheet->setCellValue('E1', lang('member_old_account_no'));
        $sheet->setCellValue('F1', lang('account_type_name'));
        $sheet->setCellValue('G1', lang('balance'));
        $sheet->setCellValue('H1', lang('virtual_balance'));
        $sheet->setCellValue('I1', lang('account_status'));
        $sheet->setCellValue('J1', lang('saving_account_gl_status'));
        
        // Style header row
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1:J1')->getFill()->getStartColor()->setARGB('FFCCCCCC');
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:J1')->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        
        // Populate data
        $row = 2;
        $i = 1;
        foreach ($saving_accounts as $value) {
            // Get account holder name
            $account_holder_name = '-';
            if ($value->tablename == 'members_grouplist' && $value->group_name) {
                $account_holder_name = $value->group_name;
            } else if (($value->firstname || $value->lastname)) {
                $account_holder_name = trim($value->lastname . ', ' . $value->firstname . ' ' . $value->middlename);
            }
            
            // Get status text
            $status_value = isset($value->status) ? $value->status : '1';
            $status_text = ($status_value == '1' || $status_value === 1) ? lang('account_status_active') : lang('account_status_inactive');
            
            // Get GL posted status
            $gl_posted_count = isset($value->gl_posted_count) ? intval($value->gl_posted_count) : 0;
            $gl_status_text = $gl_posted_count > 0 ? lang('saving_account_gl_posted') : lang('saving_account_gl_not_posted');
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $value->account);
            $sheet->setCellValue('C' . $row, $value->member_id_display);
            $sheet->setCellValue('D' . $row, $account_holder_name);
            $sheet->setCellValue('E' . $row, $value->old_members_acct ? $value->old_members_acct : '-');
            $sheet->setCellValue('F' . $row, $value->account_type_name_display ? $value->account_type_name_display : '-');
            $sheet->setCellValue('G' . $row, number_format($value->balance, 2, '.', ''));
            $sheet->setCellValue('H' . $row, number_format($value->virtual_balance, 2, '.', ''));
            $sheet->setCellValue('I' . $row, $status_text);
            $sheet->setCellValue('J' . $row, $gl_status_text);
            
            // Set alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
            // Add borders to cells
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            
            $row++;
        }
        
        // Add total row
        $sheet->setCellValue('F' . $row, 'Total:');
        $sheet->setCellValue('G' . $row, number_format($total_savings_amount, 2, '.', ''));
        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row . ':G' . $row)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // Set filename
        $filename = 'Saving_Account_List_' . date('Y-m-d_His') . '.xls';
        
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
        exit();
    }
    
    /**
     * Post all unposted savings transactions for an account to the General Ledger.
     * Expects encoded members_account id.
     */
    function post_to_gl($id = null) {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!has_role(3, 'saving_account_list')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        if (empty($id)) {
            $this->session->set_flashdata('warning', lang('invalid_account'));
            redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            return;
        }
        $decoded_id = decode_id($id);
        $account_info = $this->finance_model->get_saving_account_info($decoded_id);
        if (!$account_info || empty($account_info->account)) {
            $this->session->set_flashdata('warning', lang('invalid_account'));
            redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            return;
        }
        $result = $this->finance_model->post_savings_account_to_gl($account_info->account);
        if ($result['posted'] > 0) {
            $msg = $result['posted'] . ' ' . ($result['posted'] == 1 ? lang('saving_account_post_to_gl_success_one') : lang('saving_account_post_to_gl_success_many'));
            if ($result['failed'] > 0) {
                $msg .= '. ' . $result['failed'] . ' ' . lang('saving_account_post_to_gl_partial_fail');
            }
            $this->session->set_flashdata('message', $msg);
        } elseif ($result['failed'] > 0) {
            $this->session->set_flashdata('warning', lang('saving_account_post_to_gl_fail'));
        } else {
            $this->session->set_flashdata('message', lang('saving_account_post_to_gl_none'));
        }
        redirect(current_lang() . '/saving/saving_account_list', 'refresh');
    }

    /**
     * Post all unposted savings transactions for multiple selected accounts to the General Ledger.
     * Expects POST ids[] = array of encoded members_account ids.
     */
    function post_selected_to_gl() {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!has_role(3, 'saving_account_list')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        $ids = $this->input->post('ids');
        if (!is_array($ids) || count($ids) == 0) {
            $this->session->set_flashdata('warning', lang('saving_account_post_to_gl_none'));
            $this->_redirect_saving_list();
            return;
        }
        $total_posted = 0;
        $total_failed = 0;
        $accounts_processed = 0;
        $errors = array();
        foreach ($ids as $encoded_id) {
            $encoded_id = trim($encoded_id);
            if (empty($encoded_id)) continue;
            $decoded_id = decode_id($encoded_id);
            $account_info = $this->finance_model->get_saving_account_info($decoded_id);
            if (!$account_info || empty($account_info->account)) continue;
            $result = $this->finance_model->post_savings_account_to_gl($account_info->account);
            $accounts_processed++;
            $total_posted += $result['posted'];
            $total_failed += $result['failed'];
            if (!empty($result['errors'])) {
                $errors = array_merge($errors, $result['errors']);
            }
        }
        if ($accounts_processed == 0) {
            $this->session->set_flashdata('warning', lang('invalid_account'));
        } elseif ($total_posted > 0) {
            $msg = $total_posted . ' ' . ($total_posted == 1 ? lang('saving_account_post_to_gl_success_one') : lang('saving_account_post_to_gl_success_many'));
            if ($total_failed > 0) {
                $msg .= '. ' . $total_failed . ' ' . lang('saving_account_post_to_gl_partial_fail');
            }
            $this->session->set_flashdata('message', $msg);
        } elseif ($total_failed > 0) {
            $this->session->set_flashdata('warning', lang('saving_account_post_to_gl_fail'));
        } else {
            $this->session->set_flashdata('message', lang('saving_account_post_to_gl_none'));
        }
        $this->_redirect_saving_list();
    }

    private function _redirect_saving_list() {
        $query = array();
        if ($this->input->post('redirect_key') !== false && $this->input->post('redirect_key') !== '') $query['key'] = $this->input->post('redirect_key');
        if ($this->input->post('redirect_account_type_filter') !== false && $this->input->post('redirect_account_type_filter') !== '') $query['account_type_filter'] = $this->input->post('redirect_account_type_filter');
        if ($this->input->post('redirect_gl_posted_filter') !== false && $this->input->post('redirect_gl_posted_filter') !== '') $query['gl_posted_filter'] = $this->input->post('redirect_gl_posted_filter');
        if ($this->input->post('redirect_status_filter') !== false && $this->input->post('redirect_status_filter') !== '') $query['status_filter'] = $this->input->post('redirect_status_filter');
        $url = current_lang() . '/saving/saving_account_list';
        if (!empty($query)) $url .= '?' . http_build_query($query);
        redirect($url, 'refresh');
    }
    
    function edit_saving_account($id = null) {
        $this->data['title'] = lang('edit_saving_account');
        $this->data['id'] = $id;
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        
        // Check permission - allow if user has either saving_account_list or Edit_saving_account permission
        if (!has_role(3, 'saving_account_list') && !has_role(3, 'Edit_saving_account')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        
        if (!is_null($id)) {
            $this->data['account_info'] = $this->finance_model->get_saving_account_info($id);
            if (!$this->data['account_info']) {
                $this->session->set_flashdata('warning', lang('invalid_account'));
                redirect(current_lang() . '/saving/saving_account_list', 'refresh');
                return;
            }
        } else {
            $this->session->set_flashdata('warning', lang('invalid_account'));
            redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            return;
        }
        
        // Handle comma-formatted numbers
        if ($this->input->post('balance')) {
            $initial = $this->input->post('balance');
            $_POST['balance'] = str_replace(',', '', $initial);
        }
        if ($this->input->post('virtual_balance')) {
            $initial = $this->input->post('virtual_balance');
            $_POST['virtual_balance'] = str_replace(',', '', $initial);
        }
        
        $this->form_validation->set_rules('account', lang('account_number'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('account_cat', lang('account_type'), 'required');
        $this->form_validation->set_rules('balance', lang('balance'), 'required|numeric');
        $this->form_validation->set_rules('virtual_balance', lang('virtual_balance'), 'numeric');
        $this->form_validation->set_rules('status', lang('account_status'), 'required|in_list[0,1]');
        
        if ($this->form_validation->run() == TRUE) {
            $update_data = array(
                //'account' => trim($this->input->post('account')),
                'old_members_acct' => trim($this->input->post('account')),
                'member_id' => trim($this->input->post('member_id')),
                'account_cat' => trim($this->input->post('account_cat')),
                'balance' => trim($this->input->post('balance')),
                'virtual_balance' => trim($this->input->post('virtual_balance')) ? trim($this->input->post('virtual_balance')) : 0,
                'status' => trim($this->input->post('status')),
            );
            
            $result = $this->finance_model->update_saving_account($update_data, $id);
            if ($result) {
                $this->session->set_flashdata('message', lang('account_updated_successfully'));
                redirect(current_lang() . '/saving/saving_account_list', 'refresh');
            } else {
                $this->data['warning'] = lang('account_update_failed');
            }
        }
        
        $this->data['account_list'] = $this->finance_model->saving_account_list()->result();
        $this->data['content'] = 'saving/edit_account';
        $this->load->view('template', $this->data);
    }

    function create_saving_account() {
        $this->data['title'] = lang('create_saving_account');
        $this->data['account_list'] = $this->finance_model->saving_account_list()->result();
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        if ($this->input->post('open_balance')) {
            $initial = $this->input->post('open_balance');
            $_POST['open_balance'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('saving_account', lang('member_saccos_saving_account_type'), 'required');
        $this->form_validation->set_rules('open_balance', lang('account_balance_open'), 'required|numeric');
        $this->form_validation->set_rules('paymenthod', lang('paymentmethod'), 'required');
        $this->form_validation->set_rules('comment', lang('comment'), '');
        $this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');
        $check_number_received = '';
        if ($this->input->post('paymenthod')) {
            $is_cheque = $this->input->post('paymenthod');
            if ($is_cheque == 'CHEQUE') {
                $this->form_validation->set_rules('cheque', lang('cheque_no'), 'required');
                $check_number_received = trim($this->input->post('cheque'));
            }
        }
        if ($this->form_validation->run() == TRUE) {

            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $PID = $PID_initial[0];
            $member_id = $member_id_initial[0].'-'.$member_id_initial[1];
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));

            $account_type = $this->input->post('saving_account');
            $old_member_id = $this->input->post('old_member_id');
            $account_selected = $this->finance_model->saving_account_list(null, $account_type)->row();
            $opening_balance = trim($this->input->post('open_balance'));
            $account_selected->min_amount;
            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));


            if ($account_selected->min_amount <= $opening_balance) {

                $balance = $opening_balance - $account_selected->min_amount;
                $virtual_balance = $account_selected->min_amount;

                $accountdata = $this->finance_model->create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment, $check_number_received, $posting_date, $old_member_id);
                if ($accountdata) {
                    $this->session->set_flashdata('next_customer', site_url(current_lang() . '/saving/create_saving_account'));
                    $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                    redirect(current_lang() . '/saving/receipt_view/' . $accountdata, 'refresh');
                } else {
                    $this->data['warning'] = lang('create_saving_account_error');
                }
            } else {
                $this->data['warning'] = lang('opening_balance_error') . ' ' . number_format($account_selected->min_amount, 2);
            }
        }
        $this->data['content'] = 'saving/create_account';
        $this->load->view('template', $this->data);
    }

    function receipt_view($receipt) {
        $this->lang->load('setting');
        $trans = $this->finance_model->get_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'saving/receipt';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_receipt($receipt) {
        // Suppress PHP warnings/errors for TCPDF compatibility (PHP 7.3+ issues)
        $old_error_reporting = error_reporting(0);
        $old_display_errors = ini_set('display_errors', 0);
        
        // Start output buffering to prevent any output before PDF
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        
        $this->lang->load('setting');
        $trans = $this->finance_model->get_transaction($receipt);
        if ($trans) {
            // Clear any output that might have been generated
            ob_clean();
            
            // Suppress warnings for TCPDF library
            @include 'include/receipt.php';
            
            // Restore settings
            error_reporting($old_error_reporting);
            if ($old_display_errors !== false) {
                ini_set('display_errors', $old_display_errors);
            }
            exit;
        } else {
            // Clean output buffer before showing error
            ob_end_clean();
            // Restore settings
            error_reporting($old_error_reporting);
            if ($old_display_errors !== false) {
                ini_set('display_errors', $old_display_errors);
            }
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function credit_debit() {
        $this->data['title'] = lang('saving_account_credit_debit');
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        if ($this->input->post('amount')) {
            $initial = $this->input->post('amount');
            $_POST['amount'] = str_replace(',', '', $initial);
        }
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('trans_type', lang('transaction_type'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('paymenthod', lang('paymentmethod'), 'required');
        $this->form_validation->set_rules('comment', lang('comment'), '');
        $this->form_validation->set_rules('customer_name', lang('customer_name'), 'required');
        $this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');

        $check_number_received = '';
        if ($this->input->post('paymenthod')) {
            $is_cheque = $this->input->post('paymenthod');
            if ($is_cheque == 'CHEQUE') {
                $this->form_validation->set_rules('cheque', lang('cheque_no'), 'required');
                $check_number_received = trim($this->input->post('cheque'));
            }
        }
        if ($this->form_validation->run() == TRUE) {

            $account_initial = explode('-', trim($this->input->post('pid')));

            $account_number = $account_initial[0];
            $trans_type = $this->input->post('trans_type');

            $comment = trim($this->input->post('comment'));
            $paymethod = trim($this->input->post('paymenthod'));
            $amount = trim($this->input->post('amount'));
            $customer_name = trim($this->input->post('customer_name'));
            $posting_date = date("Y-m-d",strtotime($this->input->post('posting_date')));
            $refno = trim($this->input->post('refno'));

            $continue = true;
            if ($trans_type == 'DR') {
                $account_balance = $this->finance_model->saving_account_balance($account_number);
                if ($account_balance) {
                    $remaining = $account_balance->balance - $amount;
                    if ($remaining < 0) {
                        $continue = FALSE;
                    }
                } else {
                    $continue = FALSE;
                }
            }

            if ($continue) {
                //now finalize
                $receipt = $this->finance_model->add_saving_transaction($trans_type, $account_number, $amount, $paymethod, $comment, $check_number_received, $customer_name, $pid = null, $posting_date, $refno);
                if ($receipt) {
                    $this->session->set_flashdata('next_customer', site_url(current_lang() . '/saving/credit_debit'));
                    $this->session->set_flashdata('next_customer_label', lang('next_deposit_withdrawal'));
                    redirect(current_lang() . '/saving/receipt_view/' . $receipt, 'refresh');
                } else {
                    $this->data['warning'] = lang('transaction_fail');
                }
            } else {
                //insufficient balance
                $this->data['warning'] = lang('insufficient_balance');
            }
        }
        $this->data['content'] = 'saving/credit_debit';
        $this->load->view('template', $this->data);
    }

    function transaction_search() {
        $this->load->library('pagination');
        $this->data['title'] = lang('saving_transaction_search');

        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 30);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        $from = null;
        $to = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
            $expl = explode('-', $key);
            $key = $expl[0];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (isset($_POST['from']) && $_POST['from'] != '') {
            $from = format_date($_POST['from']);
        } else if (isset($_GET['from'])) {
            $from = format_date($_GET['from']);
        } else {
            $from = date('Y-m-d');
        }

        if (isset($_POST['upto']) && $_POST['upto'] != '') {
            $upto = format_date($_POST['upto']);
        } else if (isset($_GET['upto'])) {
            $upto = format_date($_GET['upto']);
        } else {
            $upto = date('Y-m-d');
        }


        $suffix_array = array();

        if (!is_null($key)) {
            $suffix_array['key'] = $key;
        }

        if (!is_null($from)) {
            $suffix_array['from'] = $from;
        }

        if (!is_null($upto)) {
            $suffix_array['upto'] = $upto;
        }
        $this->data['jxy'] = $suffix_array;

        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }

        $config["base_url"] = site_url(current_lang() . '/saving/transaction_search/');
        $config["total_rows"] = $this->finance_model->count_transaction($key, $from, $upto);
        $config["uri_segment"] = 4;

        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';

        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';

        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';

        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';

        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';


        $config["num_links"] = 10;


        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();

        $this->data['transactionlist'] = $this->finance_model->search_transaction($key, $from, $upto, $config["per_page"], $page);


        $this->data['content'] = 'saving/transaction_history';
        $this->load->view('template', $this->data);
    }

    
    
    function autosuggest($id) {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;
        

        $auto = $this->db->query("SELECT PID,firstname, middlename, lastname,member_id FROM members WHERE PIN='$pin' AND ( PID LIKE '$q%' OR  member_id LIKE '$q%' OR firstname LIKE '$q%' OR lastname LIKE '$q%')")->result();
        //$auto = $this->db->query("SELECT PIDFROM members WHERE  PID LIKE '$q%' or member_id LIKE '$q%' AND PIN='$PIN'")->result();

        foreach ($auto as $key => $value) {
            if ($id == 'pid') {
                echo $value->PID . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
            } else if ($id == 'mid') {
                echo $value->member_id . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
            }
        }
    }

    function autosuggest_account($id) {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT a.account,a.old_members_acct,b.firstname, b.middlename, b.lastname FROM members_account as a INNER JOIN members as b ON a.RFID=b.PID   WHERE b.PIN='$pin' AND ( a.account LIKE '$q%'  OR b.firstname LIKE '$q%' OR b.lastname LIKE '$q%') ")->result();


        foreach ($auto as $key => $value) {

            echo $value->account . ' - [' . $value->old_members_acct . '] ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
        }
    }

    function autosuggest_account_all() {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT a.account,b.firstname, b.middlename, b.lastname FROM members_account as a INNER JOIN members as b ON a.RFID=b.PID   WHERE b.PIN='$pin' AND ( a.account LIKE '$q%'  OR b.firstname LIKE '$q%' OR b.lastname LIKE '$q%')")->result();
        $auto1 = $this->db->query("SELECT a.account,b.name FROM members_account as a INNER JOIN members_grouplist as b ON a.RFID=b.GID   WHERE a.PIN='$pin' AND ( a.account LIKE '$q%'  OR b.name LIKE '$q%' ) ")->result();


        foreach ($auto as $key => $value) {

            echo $value->account . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
        }
        foreach ($auto1 as $key => $value) {

            echo $value->account . ' - ' . $value->name . "\n";
        }
    }
    function autosuggest_member_id_all() {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;

        $auto = $this->db->query("SELECT * FROM members WHERE PIN='$pin' AND  (PID LIKE '$q%'  OR firstname LIKE '$q%' OR lastname LIKE '$q%' OR member_id LIKE '$q%')")->result();
      

        foreach ($auto as $key => $value) {

            echo $value->PID . ' - '.$value->member_id.'  ==> ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
        }
      
    }
    
    
    

    function search_account() {
        // Set JSON header to ensure proper content type
        header('Content-Type: application/json');
        
        $value = $this->input->post('value');
        $column = $this->input->post('column');
        
        // Initialize status array
        $status = array();
        
        // Validate input
        if (empty($value) || empty($column)) {
            $status['success'] = 'N';
            $status['error'] = lang('invalid_account');
            echo json_encode($status);
            return;
        }
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        // Also handles formats like "Account#1 - NAME" or "18 - NAME"
        if (strpos($value, ' - ') !== false) {
            // Split on " - " (space-dash-space) to separate account number from name
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else {
            // If no " - " separator, try splitting on dash (for formats like "18-NAME")
            // But preserve account numbers that contain dashes (like "2005-00173")
            $explode = explode('-', $value, 2); // Limit to 2 parts to preserve multi-dash account numbers
            $value = trim($explode[0]);
        }
        
        // Ensure we have a valid account number after extraction
        if (empty($value)) {
            $status['success'] = 'N';
            $status['error'] = lang('invalid_account');
            echo json_encode($status);
            return;
        }
        
        $account_pin = null;
        $error = '';
        if ($column == 'PID') {
            // Use the extracted value as the account number
            // Trim and ensure it's treated as a string to match database storage
            $account_pin = trim((string)$value);
            $error = lang('invalid_account');
        }
        
        // Ensure we have an account number to search for
        if (empty($account_pin)) {
            $status['success'] = 'N';
            $status['error'] = lang('invalid_account');
            echo json_encode($status);
            return;
        }
        
        // Query account info - try to match autocomplete query behavior
        // The autocomplete filters by member PIN, so we'll do the same
        $pin = current_user()->PIN;
        $this->db->select('ma.*');
        $this->db->from('members_account ma');
        $this->db->join('members m', 'ma.RFID = m.PID', 'inner');
        $this->db->where('ma.account', $account_pin);
        $this->db->where('m.PIN', $pin);
        $account_info = $this->db->get()->row();
        
        // If still not found with member PIN join, try the original method as fallback
        if (empty($account_info)) {
            $account_info = $this->finance_model->saving_account_balance($account_pin);
        }
        
        // Check if account_info is valid (returns object or null)
        if (!empty($account_info) && is_object($account_info)) {
            $status['accountinfo'] = $account_info;
            $member = $this->member_model->member_basic_info(null, $account_info->RFID, $account_info->member_id)->row();

            if (!empty($member) && isset($member->PID)) {
                $contact = $this->member_model->member_contact($member->PID);
                $status['success'] = 'Y';
                $status['data'] = $member;
                $status['contact'] = $contact;
                echo json_encode($status);
            } else {
                $status['success'] = 'N';
                $status['error'] = $error;
                echo json_encode($status);
            }
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

    function search_member() {

        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Validate input
        if (empty($value) || empty($column)) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        // Check if value contains " - " (space-dash-space) which separates ID from name
        if (strpos($value, ' - ') !== false) {
            // Extract everything before " - " as the ID
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else {
            // If no " - " separator, the value might be just the ID or formatted differently
            // Try to extract ID by splitting on first dash, but preserve member IDs with dashes
            // For member IDs like "2005-00173", we need to be smarter
            // Check if it looks like a member ID format (contains dash and has numbers)
            if (preg_match('/^[\d\-]+/', $value, $matches)) {
                // If it starts with digits and dashes, use the matched portion
                $value = trim($matches[0]);
            } else {
                // Fallback: split on first dash (old behavior for backward compatibility)
                $explode = explode('-', $value);
                $value = trim($explode[0]);
            }
        }
        
        if (empty($value)) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = trim($value);
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = trim($value);
            $error = lang('invalid_member_id');
        } else {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        // Ensure values are not empty after trimming
        if (($column == 'PID' && empty($pid)) || ($column == 'MID' && empty($member_id))) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
            return;
        }
        
        // Query member with proper null handling
        $member_query = $this->member_model->member_basic_info(null, $pid, $member_id);
        $member = $member_query->row();

        $status = array();
        // Check if member exists and has valid PID
        // Use num_rows() to check if query returned results, then check object properties
        if ($member_query->num_rows() > 0 && $member && isset($member->PID) && !empty($member->PID)){
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            echo json_encode($status);
        }else{
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }
    
    function search_member_share() {
        $this->load->model('share_model');
        $this->load->model('setting_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        if (strpos($value, ' - ') !== false) {
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else if (preg_match('/^[\d\-]+/', $value, $matches)) {
            $value = trim($matches[0]);
        } else {
            $explode = explode('-', $value);
            $value = trim($explode[0]);
        }
        
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = $value;
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = $value;
            $error = lang('invalid_member_id');
        }
        $member = $this->member_model->member_basic_info(null, $pid, $member_id)->row();

        $status = array();
        $share_array = array('amount' => 0, 'share' => 0, 'max_share' => $share_setting->max_share, 'min_share' => $share_setting->min_share);

        if (!empty($member) && isset($member->PID)) {
            $current_share = $this->share_model->share_member_info($member->PID, $member->member_id);
            if ($current_share) {
                $share_array['share'] = $current_share->totalshare;
                $share_array['amount'] = ($current_share->amount + $current_share->remainbalance);
            }
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            $status['share'] = $share_array;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }
    
    function search_member_contribution() {
        $this->load->model('setting_model');
        $this->load->model('contribution_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        if (strpos($value, ' - ') !== false) {
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else if (preg_match('/^[\d\-]+/', $value, $matches)) {
            $value = trim($matches[0]);
        } else {
            $explode = explode('-', $value);
            $value = trim($explode[0]);
        }
        
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = $value;
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = $value;
            $error = lang('invalid_member_id');
        }

        $member = $this->member_model->member_basic_info(null, $pid, $member_id)->row();


        $status = array();
        

        if (!empty($member) && isset($member->PID)) {
            $balance = 0;
            $current_share = $this->contribution_model->contribution_balance($member->PID, $member->member_id);
            if ($current_share) {
                $balance = $current_share->balance;
            }
            
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            $status['balance'] = $balance;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }
    
    function search_member_mortuary() {
        $this->load->model('setting_model');
        $this->load->model('contribution_model');
        $this->load->model('mortuary_model');
        $share_setting = $this->setting_model->share_setting_info();
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        if (strpos($value, ' - ') !== false) {
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else if (preg_match('/^[\d\-]+/', $value, $matches)) {
            $value = trim($matches[0]);
        } else {
            $explode = explode('-', $value);
            $value = trim($explode[0]);
        }
        
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = $value;
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = $value;
            $error = lang('invalid_member_id');
        }
        
        $member = $this->member_model->member_basic_info(null, $pid, $member_id,1)->row();
        
        
        $status = array();
        
        
        if (!empty($member) && isset($member->PID)) {
            $balance = 0;
            $current_share = $this->contribution_model->contribution_balance($member->PID, $member->member_id);
            if ($current_share) {
                $balance = $current_share->balance;
            }
            
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            $status['balance'] = $balance;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

    /**
     * Savings Beginning Balance List
     * Only accessible by admin users
     */
    function savings_beginning_balance_list() {
        $this->load->library('pagination');
        $this->data['title'] = lang('savings_beginning_balance_list');
        
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        
        // Check if user is admin - only admin can access this
        if (!$this->ion_auth->is_admin()) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }
        
        $config["per_page"] = $this->session->userdata('PER_PAGE');
        
        $key = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $explode = explode('-', $_POST['key']);
            $key = trim($explode[0]);
        } else if (isset($_GET['key']) && $_GET['key'] != '') {
            $key = trim($_GET['key']);
        }
        
        $account_type_filter = null;
        if (isset($_POST['account_type_filter']) && $_POST['account_type_filter'] != '') {
            $account_type_filter = $_POST['account_type_filter'];
        } else if (isset($_GET['account_type_filter']) && $_GET['account_type_filter'] != '') {
            $account_type_filter = $_GET['account_type_filter'];
        }
        
        $status_filter = '1'; // Default to Active
        if (isset($_POST['status_filter']) && $_POST['status_filter'] != '') {
            $status_filter = $_POST['status_filter'];
        } else if (isset($_GET['status_filter']) && $_GET['status_filter'] != '') {
            $status_filter = $_GET['status_filter'];
        }
        
        $suffix_array = array();
        if (!is_null($key) && $key != '') {
            $suffix_array['key'] = $key;
        }
        if (!is_null($account_type_filter) && $account_type_filter != '') {
            $suffix_array['account_type_filter'] = $account_type_filter;
        }
        if ($status_filter != '') {
            $suffix_array['status_filter'] = $status_filter;
        }
        
        $this->data['jxy'] = $suffix_array;
        $this->data['account_type_filter'] = $account_type_filter;
        $this->data['status_filter'] = $status_filter;
        
        $config["base_url"] = site_url(current_lang() . '/saving/savings_beginning_balance_list');
        // Count accounts with beginning balance transactions
        $config["total_rows"] = $this->finance_model->count_saving_account_with_beginning_balance($key, $account_type_filter, $status_filter);
        $config["per_page"] = $this->session->userdata('PER_PAGE');
        $config["uri_segment"] = 4;
        
        $config['full_tag_open'] = '<div class="pagination">';
        $config['full_tag_close'] = '</div>';
        
        $config['first_link'] = 'First';
        $config['first_tag_open'] = '<div class="link-pagination">';
        $config['first_tag_close'] = '</div>';
        
        $config['last_link'] = 'Last';
        $config['last_tag_open'] = '<div class="link-pagination">';
        $config['last_tag_close'] = '</div>';
        
        $config['next_link'] = 'Next';
        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';
        
        $config['prev_link'] = 'Previous';
        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';
        
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';
        
        $config["num_links"] = 10;
        
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();
        
        // Filter for beginning balance transactions only (system_comment contains 'BEGINNING BALANCE')
        $this->data['saving_accounts'] = $this->finance_model->search_saving_account_with_beginning_balance($key, $config["per_page"], $page, $account_type_filter, $status_filter);
        $this->data['total_savings_amount'] = $this->finance_model->get_total_savings_amount($key, $account_type_filter, $status_filter);
        
        // Load data for popup form
        $this->data['account_list'] = $this->finance_model->saving_account_list()->result();
        $this->data['paymenthod'] = $this->finance_model->paymentmenthod();
        
        $this->data['content'] = 'saving/savings_beginning_balance_list';
        $this->load->view('template', $this->data);
    }

    /**
     * Create Savings Beginning Balance (Popup)
     * Only accessible by admin users
     */
    function create_savings_beginning_balance() {
        // Check if user is admin - only admin can access this
        if (!$this->ion_auth->is_admin()) {
            // For AJAX requests, return JSON error
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json');
                echo json_encode(array('success' => 'N', 'message' => lang('access_denied')));
                return;
            }
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        // Set output content type for JSON response
        $this->output->set_content_type('application/json');
        
        if ($this->input->post('beginning_balance')) {
            $initial = $this->input->post('beginning_balance');
            $_POST['beginning_balance'] = str_replace(',', '', $initial);
        }
        
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('saving_account', lang('member_saccos_saving_account_type'), 'required');
        $this->form_validation->set_rules('beginning_balance', lang('beginning_balance'), 'required|numeric');
        $this->form_validation->set_rules('comment', lang('comment'), '');
        $this->form_validation->set_rules('posting_date', lang('mortuary_transaction_date'), 'required|valid_date');
        
        // Beginning balances are adjustments, no payment method needed
        $paymethod = 'ADJUSTMENT';
        $check_number_received = '';
        
        if ($this->form_validation->run() == TRUE) {
            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $PID = $PID_initial[0];
            $member_id = $member_id_initial[0].'-'.$member_id_initial[1];
            $posting_date = date("Y-m-d", strtotime($this->input->post('posting_date')));
            
            $account_type = $this->input->post('saving_account');
            $old_member_id = $this->input->post('old_member_id');
            $account_selected = $this->finance_model->saving_account_list(null, $account_type)->row();
            
            if (!$account_selected) {
                echo json_encode(array('success' => 'N', 'message' => lang('savings_beginning_balance_error')));
                return;
            }
            
            $beginning_balance = trim($this->input->post('beginning_balance'));
            $comment = trim($this->input->post('comment'));
            // Payment method is always 'ADJUSTMENT' for beginning balances (already set above)
            
            if ($account_selected->min_amount <= $beginning_balance) {
                $balance = $beginning_balance - $account_selected->min_amount;
                $virtual_balance = $account_selected->min_amount;
                
                // Use existing account if it exists, otherwise create new account
                $existing_account = $this->finance_model->saving_account_balance_by_member($PID, $member_id, $account_type);
                
                if ($existing_account) {
                    // Account exists - add beginning balance to existing account using credit directly with BEGINNING BALANCE comment
                    $customer_name = $this->finance_model->saving_account_name($existing_account->account);
                    $receipt = $this->finance_model->credit($existing_account->account, $beginning_balance, $paymethod, $comment ? $comment : 'Beginning Balance', $check_number_received, $customer_name, $PID, 'BEGINNING BALANCE', 0, $posting_date);
                    
                    if ($receipt) {
                        $this->session->set_flashdata('message', lang('savings_beginning_balance_success'));
                        echo json_encode(array('success' => 'Y', 'message' => lang('savings_beginning_balance_success'), 'receipt' => $receipt));
                        return;
                    } else {
                        echo json_encode(array('success' => 'N', 'message' => lang('savings_beginning_balance_error')));
                        return;
                    }
                } else {
                    // Create new account with beginning balance
                    $accountdata = $this->finance_model->create_account_with_beginning_balance($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment ? $comment : 'Beginning Balance', $check_number_received, $posting_date, $old_member_id);
                    
                    if ($accountdata) {
                        $this->session->set_flashdata('message', lang('savings_beginning_balance_success'));
                        echo json_encode(array('success' => 'Y', 'message' => lang('savings_beginning_balance_success'), 'receipt' => $accountdata));
                        return;
                    } else {
                        echo json_encode(array('success' => 'N', 'message' => lang('savings_beginning_balance_error')));
                        return;
                    }
                }
            } else {
                echo json_encode(array('success' => 'N', 'message' => lang('opening_balance_error') . ' ' . number_format($account_selected->min_amount, 2)));
                return;
            }
        } else {
            // Return validation errors
            $errors = validation_errors();
            echo json_encode(array('success' => 'N', 'message' => $errors));
            return;
        }
    }

    /**
     * Convert existing OPEN ACCOUNT records to BEGINNING BALANCE
     * This method converts savings_transaction records with 'OPEN ACCOUNT, NORMAL DEPOSIT'
     * to 'BEGINNING BALANCE' and updates GL entries to use adjustment account instead of cash
     * 
     * Access: Admin only
     * URL: /saving/convert_open_account_to_beginning_balance
     */
    function convert_open_account_to_beginning_balance() {
        // Check if user is admin
        if (!$this->ion_auth->is_admin()) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        $pin = current_user()->PIN;
        $results = array(
            'total_found' => 0,
            'converted' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_messages' => array(),
            'converted_receipts' => array(),
            'skipped_receipts' => array()
        );
        
        // Find adjustment/equity account for ADJUSTMENT payment method
        $this->db->where('PIN', $pin);
        $adjustment_names = array('Adjustment', 'Opening Balance', 'Beginning Balance', 'Equity', 'Retained Earnings');
        $where_clause = "(";
        foreach ($adjustment_names as $index => $name) {
            if ($index > 0) {
                $where_clause .= " OR ";
            }
            $escaped_name = $this->db->escape_like_str($name);
            $where_clause .= "name LIKE '%" . $escaped_name . "%'";
        }
        $where_clause .= ")";
        
        $this->db->where($where_clause, NULL, FALSE);
        $this->db->where_in('account_type', array(30, 40, 1)); // Equity (30, 40) or Asset (1)
        $this->db->order_by('account', 'ASC');
        $this->db->limit(1);
        
        $adjustment_account_obj = $this->db->get('account_chart')->row();
        
        if (!$adjustment_account_obj) {
            // Fallback: try to find any equity account
            $this->db->where('PIN', $pin);
            $this->db->where_in('account_type', array(30, 40)); // Equity accounts
            $this->db->order_by('account', 'ASC');
            $this->db->limit(1);
            $adjustment_account_obj = $this->db->get('account_chart')->row();
        }
        
        if (!$adjustment_account_obj) {
            $this->session->set_flashdata('warning', 'No adjustment/equity account found. Please create an adjustment or equity account in Chart of Accounts first.');
            redirect('saving/savings_beginning_balance_list', 'refresh');
            return;
        }
        
        $adjustment_account = $adjustment_account_obj->account;
        $adjustment_account_info = account_row_info($adjustment_account);
        
        if (!$adjustment_account_info) {
            $this->session->set_flashdata('warning', 'Adjustment account not found in chart of accounts.');
            redirect('saving/savings_beginning_balance_list', 'refresh');
            return;
        }
        
        // Find all savings_transaction records with 'OPEN ACCOUNT, NORMAL DEPOSIT'
        $this->db->where('PIN', $pin);
        $this->db->where('system_comment', 'OPEN ACCOUNT, NORMAL DEPOSIT');
        $transactions = $this->db->get('savings_transaction')->result();
        
        $results['total_found'] = count($transactions);
        
        if ($results['total_found'] == 0) {
            $this->session->set_flashdata('message', 'No OPEN ACCOUNT records found to convert.');
            redirect('saving/savings_beginning_balance_list', 'refresh');
            return;
        }
        
        // Process each transaction
        foreach ($transactions as $transaction) {
            $this->db->trans_start();
            
            try {
                $receipt = $transaction->receipt;
                
                // Find GL entries for this receipt
                $this->db->where('refferenceID', $receipt);
                $this->db->where('fromtable', 'savings_transaction');
                $this->db->where('PIN', $pin);
                $gl_entries = $this->db->get('general_ledger')->result();
                
                if (empty($gl_entries)) {
                    // No GL entries found - skip this transaction
                    $results['skipped']++;
                    $results['skipped_receipts'][] = $receipt;
                    $this->db->trans_complete();
                    continue;
                }
                
                // Find the debit entry (should have debit > 0)
                $debit_entry = null;
                foreach ($gl_entries as $entry) {
                    if (floatval($entry->debit) > 0) {
                        $debit_entry = $entry;
                        break;
                    }
                }
                
                if (!$debit_entry) {
                    // No debit entry found - skip
                    $results['skipped']++;
                    $results['skipped_receipts'][] = $receipt . ' (no debit entry)';
                    $this->db->trans_complete();
                    continue;
                }
                
                // Get savings account info to get account_cat
                $savings_account = $this->finance_model->saving_account_balance($transaction->account);
                if (!$savings_account) {
                    $results['errors']++;
                    $results['error_messages'][] = "Receipt $receipt: Savings account not found";
                    $this->db->trans_complete();
                    continue;
                }
                
                // Get savings account type for liability account
                $savings_account_type = $this->finance_model->saving_account_list(null, $savings_account->account_cat)->row();
                if (!$savings_account_type || empty($savings_account_type->account_setup)) {
                    $results['errors']++;
                    $results['error_messages'][] = "Receipt $receipt: Savings account type not configured";
                    $this->db->trans_complete();
                    continue;
                }
                
                // Update GL debit entry: change account to adjustment account
                $update_data = array(
                    'account' => $adjustment_account,
                    'account_type' => $adjustment_account_info->account_type,
                    'sub_account_type' => isset($adjustment_account_info->sub_account_type) ? $adjustment_account_info->sub_account_type : null
                );
                
                $this->db->where('id', $debit_entry->id);
                $this->db->update('general_ledger', $update_data);
                
                // Update GL descriptions for all entries (both debit and credit)
                $new_description = 'Savings Beginning Balance Adjustment - ' . ($transaction->customer_name ? $transaction->customer_name : 'Member ' . $transaction->member_id) . ' (Account: ' . $transaction->account . ', Receipt: ' . $receipt . ')';
                
                $this->db->where('refferenceID', $receipt);
                $this->db->where('fromtable', 'savings_transaction');
                $this->db->where('PIN', $pin);
                $this->db->update('general_ledger', array('description' => $new_description));
                
                // Update savings_transaction record
                $transaction_update = array(
                    'system_comment' => 'BEGINNING BALANCE',
                    'paymenthod' => 'ADJUSTMENT'
                );
                
                $this->db->where('receipt', $receipt);
                $this->db->where('PIN', $pin);
                $this->db->update('savings_transaction', $transaction_update);
                
                if ($this->db->trans_status() === FALSE) {
                    $results['errors']++;
                    $results['error_messages'][] = "Receipt $receipt: Transaction failed";
                    $this->db->trans_complete();
                } else {
                    $this->db->trans_complete();
                    $results['converted']++;
                    $results['converted_receipts'][] = $receipt;
                }
                
            } catch (Exception $e) {
                $results['errors']++;
                $results['error_messages'][] = "Receipt " . (isset($receipt) ? $receipt : 'unknown') . ": " . $e->getMessage();
                $this->db->trans_complete();
            }
        }
        
        // Set flash message with results
        $message = "Conversion completed. ";
        $message .= "Total found: {$results['total_found']}, ";
        $message .= "Converted: {$results['converted']}, ";
        $message .= "Skipped: {$results['skipped']}, ";
        $message .= "Errors: {$results['errors']}";
        
        if ($results['errors'] > 0 && count($results['error_messages']) > 0) {
            $message .= "<br>Errors: " . implode("<br>", array_slice($results['error_messages'], 0, 10));
            if (count($results['error_messages']) > 10) {
                $message .= "<br>... and " . (count($results['error_messages']) - 10) . " more errors";
            }
        }
        
        $this->session->set_flashdata('message', $message);
        redirect('saving/savings_beginning_balance_list', 'refresh');
    }

}


?>
