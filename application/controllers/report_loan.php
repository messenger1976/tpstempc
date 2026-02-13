<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report_loan
 *
 * @author miltone
 */
class Report_Loan extends CI_Controller {

//put your code here
    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_report');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->lang->load('loan');
        $this->lang->load('setting');
        $this->lang->load('customer');
        $this->load->library('loanbase');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
        $this->load->model('customer_model');
        $this->load->model('loan_model');
        $this->load->model('share_model');
        $this->load->model('report_model');
    }

    function repayment_schedule() {
        $this->data['title'] = lang('repayment_schedule');
        $this->form_validation->set_rules('loan_id', lang('loan_id'), 'required');
        $this->data['loan_id'] = '';
        $loan_id = '';
        if ($this->form_validation->run() == TRUE) {
            $loan_id = $this->input->post('loan_id');
            $this->data['loan_id'] = $loan_id;
        }

        if (isset($_GET['loan_id'])) {
            $loan_id = $_GET['loan_id'];
            $this->data['loan_id'] = $loan_id;
        }
        $this->data['loan_list'] = $this->report_model->loan_delivery_list();
        $this->data['content'] = 'report/loan/repayment_schedule';
        $this->load->view('template', $this->data);
    }

    function loan_statement() {
        $this->data['title'] = lang('loan_statement');
        $this->form_validation->set_rules('loan_id', lang('loan_id'), 'required');
        $this->data['loan_id'] = '';
        $loan_id = '';
        if ($this->form_validation->run() == TRUE) {
            $loan_id = $this->input->post('loan_id');
            $this->data['loan_id'] = $loan_id;
        }

        if (isset($_GET['loan_id'])) {
            $loan_id = $_GET['loan_id'];
            $this->data['loan_id'] = $loan_id;
        }
        $this->data['loan_list'] = $this->report_model->loan_delivery_list();
        $this->data['content'] = 'report/loan/loan_statement';
        $this->load->view('template', $this->data);
    }

    function loan_statement_print() {
        $LID = $_GET['loan_id'];
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['trans'] = $this->report_model->loan_statement($LID);
        $html = $this->load->view('report/loan/print/loan_statement_print', $this->data, true);
        $this->export_to_pdf($html, $LID, 'A4');
    }

    function delete_report_loan($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table_loan', array('id' => $id));
            redirect(current_lang() . '/report_loan/loan_report/' . $link);
        }
        redirect(current_lang() . '/report_loan/loan_report/' . $link);
    }

    function loan_report($link, $id = null) {

        if ($link == 1) {
            $this->data['title'] = lang('report_loan_list');
        } else if ($link == 2) {
            $this->data['title'] = lang('report_loan_balance');
        } else if ($link == 3) {
            $this->data['title'] = lang('report_loan_interest_penalty');
        } else if ($link == 4) {
            $this->data['title'] = lang('report_loan_transaction');
        } else if ($link == 5) {
            $this->data['title'] = lang('report_loan_transaction_summary');
        }else if ($link == 6) {
            $this->data['title'] = lang('report_loan_processing_fee_collection');
        }


        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->data['reportlist'] = $this->report_model->report_loan(null, $link)->result();
        $this->data['content'] = 'report/loan/loan_report_title';
        $this->load->view('template', $this->data);
    }

    function create_loan_report_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('report_loan_list');
        } else if ($link == 2) {
            $this->data['title'] = lang('report_loan_balance');
        } else if ($link == 3) {
            $this->data['title'] = lang('report_loan_interest_penalty');
        } else if ($link == 4) {
            $this->data['title'] = lang('report_loan_transaction');
        } else if ($link == 5) {
            $this->data['title'] = lang('report_loan_transaction_summary');
        }else if ($link == 6) {
            $this->data['title'] = lang('report_loan_processing_fee_collection');
        }else if ($link == 7) {
            $this->data['title'] = lang('report_loan_aging');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        if ($link == 7) {
            // Aging report uses "as of date" instead of date range
            $this->form_validation->set_rules('fromdate', 'As of Date', 'required|valid_date');
            $this->form_validation->set_rules('description', 'Description', 'required');
        } else {
            $this->form_validation->set_rules('fromdate', ($link == 1 ? 'Loan applied From' : (($link == 2 || $link == 3) ? 'Disbursed From' : 'From')), 'required|valid_date');
            $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
            $this->form_validation->set_rules('description', 'Description', 'required');
        }

        if ($this->form_validation->run() == TRUE) {
            $from = format_date(trim($this->input->post('fromdate')));
            $to = ($link == 7) ? $from : format_date(trim($this->input->post('todate'))); // For aging report, use same date
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            
            if ($link == 7 || $from <= $to) {
                $array = array(
                    'fromdate' => $from,
                    'todate' => $to,
                    'description' => $description,
                    'link' => $link,
                    'page' => $page,
                    'PIN' => current_user()->PIN,
                );

                if ($link == 1) {
                    $array['custom'] = $this->input->post('custom');
                }

                if (is_null($id)) {
                    $this->db->insert('report_table_loan', $array);
                } else {
                    $this->db->update('report_table_loan', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report_loan/loan_report/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }


        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_loan($id)->row();
        }

        $this->data['content'] = 'report/loan/create_loan_report_title';
        $this->load->view('template', $this->data);
    }

    
     function loan_processing_fee_collection_view($link, $id = null) {
       
       $this->data['title'] = lang('report_loan_processing_fee_collection');
      

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;        
        $this->data['transaction'] = $this->report_model->loan_processing_fee_collection($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/loan/loan_processing_fee_collection';
        $this->load->view('template', $this->data);
    }
    
    
     
     function loan_processing_fee_collection_print($link, $id = null) {
       
       $this->data['title'] = lang('report_loan_processing_fee_collection');
      

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;        
        $this->data['transaction'] = $this->report_model->loan_processing_fee_collection($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/loan/print/loan_processing_fee_collection_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_processing_fee_collection', $reportinfo->page);
    }
    
    
    
    function loan_list_view($link, $id) {
        $this->data['title'] = lang('report_loan_list');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_list_report($reportinfo->fromdate, $reportinfo->todate, $reportinfo->custom);

        $this->data['content'] = 'report/loan/loan_list_view';
        $this->load->view('template', $this->data);
    }

    function loan_list_print($link, $id) {
        $this->data['title'] = lang('report_loan_list');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_list_report($reportinfo->fromdate, $reportinfo->todate, $reportinfo->custom);

        $html = $this->load->view('report/loan/print/loan_list_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_list', 'A4-L');
    }

    function loan_balance_view($link, $id) {
        $this->data['title'] = lang('report_loan_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_list_balance($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/loan/loan_balance';
        $this->load->view('template', $this->data);
    }

    function loan_balance_print($link, $id) {
        $this->data['title'] = lang('report_loan_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_list_balance($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/loan/print/loan_balance_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_balance', 'A4-L');
        ;
    }

    function loan_interest_penalty_view($link, $id) {
        $this->data['title'] = lang('report_loan_interest_penalty');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_list_balance($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/loan/loan_interest_balance';
        $this->load->view('template', $this->data);
    }

    function loan_interest_penalty_print($link, $id) {
        $this->data['title'] = lang('report_loan_interest_penalty');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_list_balance($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/loan/print/loan_interest_balance_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_balance_interest_penalty', $reportinfo->page);
    }

    function loan_transaction_view($link, $id) {
        $this->data['title'] = lang('report_loan_transaction');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_transactions($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/loan/loan_transaction';
        $this->load->view('template', $this->data);
    }

    function loan_transaction_print($link, $id) {
        $this->data['title'] = lang('report_loan_transaction');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_transactions($reportinfo->fromdate, $reportinfo->todate);
        $html = $this->load->view('report/loan/print/loan_transaction_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_transactions', $reportinfo->page);
    }

    function loan_transaction_summary_view($link, $id) {
        $this->data['title'] = lang('report_loan_transaction_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_transactions_summary($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/loan/loan_transaction_summary';
        $this->load->view('template', $this->data);
    }

    function loan_transaction_summary_print($link, $id) {
        $this->data['title'] = lang('report_loan_transaction_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->loan_transactions_summary($reportinfo->fromdate, $reportinfo->todate);


        $html = $this->load->view('report/loan/print/loan_transaction_summary_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_transactions_summary', $reportinfo->page);
    }

    function loan_aging_view($link, $id) {
        $this->data['title'] = lang('report_loan_aging');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        // Get view mode from GET parameter (default: grouped)
        $view_mode = $this->input->get('view_mode');
        if (empty($view_mode) || !in_array($view_mode, array('grouped', 'tabular', 'columnar'))) {
            $view_mode = 'grouped';
        }
        $this->data['view_mode'] = $view_mode;

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['aging_data'] = $this->report_model->loan_aging_report($reportinfo->fromdate);

        $this->data['content'] = 'report/loan/loan_aging_report';
        $this->load->view('template', $this->data);
    }

    function loan_aging_print($link, $id) {
        $this->data['title'] = lang('report_loan_aging');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        // Get view mode from GET parameter (default: grouped)
        $view_mode = $this->input->get('view_mode');
        if (empty($view_mode) || !in_array($view_mode, array('grouped', 'tabular', 'columnar'))) {
            $view_mode = 'grouped';
        }
        $this->data['view_mode'] = $view_mode;

        $reportinfo = $this->report_model->report_loan($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['aging_data'] = $this->report_model->loan_aging_report($reportinfo->fromdate);

        $html = $this->load->view('report/loan/print/loan_aging_report_print', $this->data, true);
        $this->export_to_pdf($html, 'Loan_aging_report', $reportinfo->page);
    }

    function loan_aging_export($link, $id) {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Load Excel library
        $this->load->library('excel');
        
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        // Get view mode from GET parameter (default: grouped)
        $view_mode = $this->input->get('view_mode');
        if (empty($view_mode) || !in_array($view_mode, array('grouped', 'tabular', 'columnar'))) {
            $view_mode = 'grouped';
        }

        $reportinfo = $this->report_model->report_loan($id)->row();
        $aging_data = $this->report_model->loan_aging_report($reportinfo->fromdate);
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Loan Aging Report")
                                     ->setSubject("Loan Aging Report Export")
                                     ->setDescription("Loan Aging Report exported from " . company_info()->name);
        
        // Set active sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Loan Aging Report');
        
        // Determine column range based on view mode
        if ($view_mode == 'columnar') {
            $last_col = 'N'; // S/No, Loan ID, Member ID, Member Name, Loan Type, Disbursed Date, Due Date, Days Overdue, Current, 31-60, 61-90, 91-180, Over 180, Total
        } elseif ($view_mode == 'tabular') {
            $last_col = 'M';
        } else {
            $last_col = 'L';
        }
        
        // Add header information
        $row = 1;
        $sheet->setCellValue('A' . $row, company_info()->name);
        $sheet->mergeCells('A' . $row . ':' . $last_col . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Loan Aging Report');
        $sheet->mergeCells('A' . $row . ':' . $last_col . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'As of ' . format_date($reportinfo->fromdate, false));
        $sheet->mergeCells('A' . $row . ':' . $last_col . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        if (!empty($reportinfo->description)) {
            $row++;
            $sheet->setCellValue('A' . $row, $reportinfo->description);
            $sheet->mergeCells('A' . $row . ':' . $last_col . $row);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        
        $row++;
        $row++; // Empty row
        
        // Add column headers
        $headerRow = $row;
        $col = 'A';
        if ($view_mode == 'columnar') {
            $sheet->setCellValue($col++ . $row, 'S/No');
            $sheet->setCellValue($col++ . $row, 'Loan ID');
            $sheet->setCellValue($col++ . $row, 'Member ID');
            $sheet->setCellValue($col++ . $row, 'Member Name');
            $sheet->setCellValue($col++ . $row, 'Loan Type');
            $sheet->setCellValue($col++ . $row, 'Disbursed Date');
            $sheet->setCellValue($col++ . $row, 'Due Date');
            $sheet->setCellValue($col++ . $row, 'Days Overdue');
            $sheet->setCellValue($col++ . $row, 'Current (0-30 days)');
            $sheet->setCellValue($col++ . $row, '31-60 days');
            $sheet->setCellValue($col++ . $row, '61-90 days');
            $sheet->setCellValue($col++ . $row, '91-180 days');
            $sheet->setCellValue($col++ . $row, 'Over 180 days');
            $sheet->setCellValue($col++ . $row, 'Total Outstanding');
        } else {
            $sheet->setCellValue($col++ . $row, 'S/No');
            if ($view_mode == 'tabular') {
                $sheet->setCellValue($col++ . $row, 'Aging Bucket');
            }
            $sheet->setCellValue($col++ . $row, 'Loan ID');
            $sheet->setCellValue($col++ . $row, 'Member ID');
            $sheet->setCellValue($col++ . $row, 'Member Name');
            $sheet->setCellValue($col++ . $row, 'Loan Type');
            $sheet->setCellValue($col++ . $row, 'Disbursed Date');
            $sheet->setCellValue($col++ . $row, 'Due Date');
            $sheet->setCellValue($col++ . $row, 'Days Overdue');
            $sheet->setCellValue($col++ . $row, 'Outstanding Principal');
            $sheet->setCellValue($col++ . $row, 'Outstanding Interest');
            $sheet->setCellValue($col++ . $row, 'Outstanding Penalty');
            $sheet->setCellValue($col++ . $row, 'Total Outstanding');
        }
        
        // Style header row
        $headerStyle = array(
            'font' => array('bold' => true),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'E0E0E0')
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $sheet->getStyle('A' . $headerRow . ':' . $last_col . $headerRow)->applyFromArray($headerStyle);
        
        // Set column alignments
        if ($view_mode == 'columnar') {
            $numeric_start_col = 'I'; // Starting from Current column
        } elseif ($view_mode == 'tabular') {
            $numeric_start_col = 'I';
        } else {
            $numeric_start_col = 'H';
        }
        $sheet->getStyle($numeric_start_col . $headerRow . ':' . $last_col . $headerRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
        $row++;
        $sno = 1;
        $grand_total_balance = 0;
        $grand_total_principal = 0;
        $grand_total_interest = 0;
        $grand_total_penalty = 0;
        $grand_total_loans = 0;
        
        if ($view_mode == 'columnar') {
            // Columnar format: aging buckets as columns
            $all_loans = array();
            
            // Collect all loans with their bucket info
            foreach ($aging_data as $bucket_key => $bucket) {
                foreach ($bucket['loans'] as $loan) {
                    $loan['bucket_key'] = $bucket_key;
                    $all_loans[] = $loan;
                }
            }
            
            // Sort by days overdue (descending)
            usort($all_loans, function($a, $b) {
                return $b['days_overdue'] - $a['days_overdue'];
            });
            
            // Add loan rows
            foreach ($all_loans as $loan) {
                $member_info = $this->member_model->member_basic_info(null, $loan['PID'])->row();
                $product_info = $this->setting_model->loanproduct($loan['product_type'])->row();
                
                $member_name = $member_info ? ($member_info->firstname . ' ' . $member_info->middlename . ' ' . $member_info->lastname) : 'N/A';
                $product_name = $product_info ? $product_info->name : 'N/A';
                
                // Determine which column to show the amount in
                $current_col = '';
                $col_31_60 = '';
                $col_61_90 = '';
                $col_91_180 = '';
                $col_over_180 = '';
                
                $bucket_key = $loan['bucket_key'];
                $amount = $loan['outstanding_balance'];
                
                if ($bucket_key == 'current') {
                    $current_col = number_format($amount, 2);
                } elseif ($bucket_key == '31_60') {
                    $col_31_60 = number_format($amount, 2);
                } elseif ($bucket_key == '61_90') {
                    $col_61_90 = number_format($amount, 2);
                } elseif ($bucket_key == '91_180') {
                    $col_91_180 = number_format($amount, 2);
                } elseif ($bucket_key == 'over_180') {
                    $col_over_180 = number_format($amount, 2);
                }
                
                $col = 'A';
                $sheet->setCellValue($col++ . $row, $sno++);
                $sheet->setCellValue($col++ . $row, $loan['LID']);
                $sheet->setCellValue($col++ . $row, $loan['member_id']);
                $sheet->setCellValue($col++ . $row, $member_name);
                $sheet->setCellValue($col++ . $row, $product_name);
                $sheet->setCellValue($col++ . $row, format_date($loan['disbursedate'], false));
                $sheet->setCellValue($col++ . $row, $loan['oldest_unpaid_due_date'] ? format_date($loan['oldest_unpaid_due_date'], false) : 'N/A');
                $sheet->setCellValue($col++ . $row, $loan['days_overdue']);
                $sheet->setCellValue($col++ . $row, $current_col);
                $sheet->setCellValue($col++ . $row, $col_31_60);
                $sheet->setCellValue($col++ . $row, $col_61_90);
                $sheet->setCellValue($col++ . $row, $col_91_180);
                $sheet->setCellValue($col++ . $row, $col_over_180);
                $sheet->setCellValue($col++ . $row, number_format($amount, 2));
                
                // Add borders
                $sheet->getStyle('A' . $row . ':' . $last_col . $row)->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                ));
                
                // Right align numeric columns
                $sheet->getStyle('I' . $row . ':' . $last_col . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                
                $row++;
            }
            
            // Totals row
            $col = 'A';
            $sheet->setCellValue($col++ . $row, 'TOTAL:');
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $col = 'I';
            $sheet->setCellValue($col++ . $row, number_format($aging_data['current']['total_balance'], 2));
            $sheet->setCellValue($col++ . $row, number_format($aging_data['31_60']['total_balance'], 2));
            $sheet->setCellValue($col++ . $row, number_format($aging_data['61_90']['total_balance'], 2));
            $sheet->setCellValue($col++ . $row, number_format($aging_data['91_180']['total_balance'], 2));
            $sheet->setCellValue($col++ . $row, number_format($aging_data['over_180']['total_balance'], 2));
            $sheet->setCellValue($col++ . $row, number_format($grand_total_balance, 2));
            
            $grandTotalStyle = array(
                'font' => array('bold' => true, 'size' => 12),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'D0D0D0')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $sheet->getStyle('A' . $row . ':' . $last_col . $row)->applyFromArray($grandTotalStyle);
            $sheet->getStyle('I' . $row . ':' . $last_col . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            
            // Calculate grand totals
            foreach ($aging_data as $bucket_key => $bucket) {
                if (count($bucket['loans']) > 0) {
                    $grand_total_balance += $bucket['total_balance'];
                    $grand_total_loans += count($bucket['loans']);
                }
            }
        } elseif ($view_mode == 'tabular') {
            // Tabular format: all loans in one table
            $all_loans = array();
            
            // Collect all loans with their bucket labels
            foreach ($aging_data as $bucket_key => $bucket) {
                foreach ($bucket['loans'] as $loan) {
                    $loan['bucket_label'] = $bucket['label'];
                    $loan['bucket_key'] = $bucket_key;
                    $all_loans[] = $loan;
                }
            }
            
            // Sort by days overdue (descending)
            usort($all_loans, function($a, $b) {
                return $b['days_overdue'] - $a['days_overdue'];
            });
            
            // Add loan rows
            foreach ($all_loans as $loan) {
                $member_info = $this->member_model->member_basic_info(null, $loan['PID'])->row();
                $product_info = $this->setting_model->loanproduct($loan['product_type'])->row();
                
                $member_name = $member_info ? ($member_info->firstname . ' ' . $member_info->middlename . ' ' . $member_info->lastname) : 'N/A';
                $product_name = $product_info ? $product_info->name : 'N/A';
                
                $col = 'A';
                $sheet->setCellValue($col++ . $row, $sno++);
                $sheet->setCellValue($col++ . $row, $loan['bucket_label']);
                $sheet->setCellValue($col++ . $row, $loan['LID']);
                $sheet->setCellValue($col++ . $row, $loan['member_id']);
                $sheet->setCellValue($col++ . $row, $member_name);
                $sheet->setCellValue($col++ . $row, $product_name);
                $sheet->setCellValue($col++ . $row, format_date($loan['disbursedate'], false));
                $sheet->setCellValue($col++ . $row, $loan['oldest_unpaid_due_date'] ? format_date($loan['oldest_unpaid_due_date'], false) : 'N/A');
                $sheet->setCellValue($col++ . $row, $loan['days_overdue']);
                $sheet->setCellValue($col++ . $row, number_format($loan['outstanding_principal'], 2));
                $sheet->setCellValue($col++ . $row, number_format($loan['outstanding_interest'], 2));
                $sheet->setCellValue($col++ . $row, number_format($loan['outstanding_penalty'], 2));
                $sheet->setCellValue($col++ . $row, number_format($loan['outstanding_balance'], 2));
                
                // Add borders
                $sheet->getStyle('A' . $row . ':' . $last_col . $row)->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                ));
                
                // Right align numeric columns
                $sheet->getStyle('I' . $row . ':' . $last_col . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                
                $row++;
            }
            
            // Add subtotals by bucket
            foreach ($aging_data as $bucket_key => $bucket) {
                if (count($bucket['loans']) > 0) {
                    $col = 'A';
                    $sheet->setCellValue($col++ . $row, 'Subtotal for ' . $bucket['label'] . ':');
                    $sheet->mergeCells('A' . $row . ':H' . $row);
                    $col = 'I';
                    $sheet->setCellValue($col++ . $row, count($bucket['loans']));
                    $sheet->setCellValue($col++ . $row, number_format($bucket['total_principal'], 2));
                    $sheet->setCellValue($col++ . $row, number_format($bucket['total_interest'], 2));
                    $sheet->setCellValue($col++ . $row, number_format($bucket['total_penalty'], 2));
                    $sheet->setCellValue($col++ . $row, number_format($bucket['total_balance'], 2));
                    
                    $subtotalStyle = array(
                        'font' => array('bold' => true),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'F9F9F9')
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $sheet->getStyle('A' . $row . ':' . $last_col . $row)->applyFromArray($subtotalStyle);
                    $sheet->getStyle('I' . $row . ':' . $last_col . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    
                    $grand_total_balance += $bucket['total_balance'];
                    $grand_total_principal += $bucket['total_principal'];
                    $grand_total_interest += $bucket['total_interest'];
                    $grand_total_penalty += $bucket['total_penalty'];
                    $grand_total_loans += count($bucket['loans']);
                    
                    $row++;
                }
            }
        } else {
            // Grouped format: original format with bucket headers
            foreach ($aging_data as $bucket_key => $bucket) {
                if (count($bucket['loans']) > 0) {
                    // Add bucket header row
                    $sheet->setCellValue('A' . $row, $bucket['label'] . ' (' . count($bucket['loans']) . ' loan(s))');
                    $sheet->mergeCells('A' . $row . ':L' . $row);
                    $bucketHeaderStyle = array(
                        'font' => array('bold' => true),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'E8E8E8')
                        )
                    );
                    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($bucketHeaderStyle);
                    $row++;
                    
                    // Add loan rows
                    foreach ($bucket['loans'] as $loan) {
                        $member_info = $this->member_model->member_basic_info(null, $loan['PID'])->row();
                        $product_info = $this->setting_model->loanproduct($loan['product_type'])->row();
                        
                        $member_name = $member_info ? ($member_info->firstname . ' ' . $member_info->middlename . ' ' . $member_info->lastname) : 'N/A';
                        $product_name = $product_info ? $product_info->name : 'N/A';
                        
                        $sheet->setCellValue('A' . $row, $sno++);
                        $sheet->setCellValue('B' . $row, $loan['LID']);
                        $sheet->setCellValue('C' . $row, $loan['member_id']);
                        $sheet->setCellValue('D' . $row, $member_name);
                        $sheet->setCellValue('E' . $row, $product_name);
                        $sheet->setCellValue('F' . $row, format_date($loan['disbursedate'], false));
                        $sheet->setCellValue('G' . $row, $loan['oldest_unpaid_due_date'] ? format_date($loan['oldest_unpaid_due_date'], false) : 'N/A');
                        $sheet->setCellValue('H' . $row, $loan['days_overdue']);
                        $sheet->setCellValue('I' . $row, number_format($loan['outstanding_principal'], 2));
                        $sheet->setCellValue('J' . $row, number_format($loan['outstanding_interest'], 2));
                        $sheet->setCellValue('K' . $row, number_format($loan['outstanding_penalty'], 2));
                        $sheet->setCellValue('L' . $row, number_format($loan['outstanding_balance'], 2));
                        
                        // Add borders
                        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        ));
                        
                        // Right align numeric columns
                        $sheet->getStyle('H' . $row . ':L' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        
                        $row++;
                    }
                    
                    // Add bucket subtotal row
                    $sheet->setCellValue('A' . $row, 'Subtotal for ' . $bucket['label'] . ':');
                    $sheet->mergeCells('A' . $row . ':G' . $row);
                    $sheet->setCellValue('H' . $row, count($bucket['loans']));
                    $sheet->setCellValue('I' . $row, number_format($bucket['total_principal'], 2));
                    $sheet->setCellValue('J' . $row, number_format($bucket['total_interest'], 2));
                    $sheet->setCellValue('K' . $row, number_format($bucket['total_penalty'], 2));
                    $sheet->setCellValue('L' . $row, number_format($bucket['total_balance'], 2));
                    
                    $subtotalStyle = array(
                        'font' => array('bold' => true),
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'F9F9F9')
                        ),
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    );
                    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($subtotalStyle);
                    $sheet->getStyle('H' . $row . ':L' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    
                    $grand_total_balance += $bucket['total_balance'];
                    $grand_total_principal += $bucket['total_principal'];
                    $grand_total_interest += $bucket['total_interest'];
                    $grand_total_penalty += $bucket['total_penalty'];
                    $grand_total_loans += count($bucket['loans']);
                    
                    $row++;
                    $row++; // Empty row
                }
            }
        }
        
        // Add grand total row if there are loans (skip for columnar as it's already added)
        if ($grand_total_loans > 0 && $view_mode != 'columnar') {
            $col = 'A';
            $sheet->setCellValue($col++ . $row, 'GRAND TOTAL:');
            $merge_end = ($view_mode == 'tabular') ? 'H' : 'G';
            $sheet->mergeCells('A' . $row . ':' . $merge_end . $row);
            $col = ($view_mode == 'tabular') ? 'I' : 'H';
            $sheet->setCellValue($col++ . $row, $grand_total_loans);
            $sheet->setCellValue($col++ . $row, number_format($grand_total_principal, 2));
            $sheet->setCellValue($col++ . $row, number_format($grand_total_interest, 2));
            $sheet->setCellValue($col++ . $row, number_format($grand_total_penalty, 2));
            $sheet->setCellValue($col++ . $row, number_format($grand_total_balance, 2));
            
            $grandTotalStyle = array(
                'font' => array('bold' => true, 'size' => 12),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'D0D0D0')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $sheet->getStyle('A' . $row . ':' . $last_col . $row)->applyFromArray($grandTotalStyle);
            $numeric_start_col = ($view_mode == 'tabular') ? 'I' : 'H';
            $sheet->getStyle($numeric_start_col . $row . ':' . $last_col . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }
        
        // Auto-size columns
        if ($view_mode == 'columnar') {
            $col_range = range('A', 'N');
        } elseif ($view_mode == 'tabular') {
            $col_range = range('A', 'M');
        } else {
            $col_range = range('A', 'L');
        }
        foreach ($col_range as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="loan_aging_report_' . date('Y-m-d', strtotime($reportinfo->fromdate)) . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Write file
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    function export_to_pdf($html, $filename, $page_orientation = null) {
        //$html = "Tanzania";
        $this->load->library('pdf1');
        $pdf = $this->pdf1->load($page_orientation);
        $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td style="text-align:center;"><h2 style="padding: 0px; margin: 0px;font-size:18px; text-align:center;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px; text-align:center;"><strong> P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
        $pdf->SetHTMLHeader($header);
        $pdf->SetFooter('SACCO PLUS' . '|{PAGENO}|' . date('d-m-Y H:i:s')); // Add a footer for good measure <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($filename, 'I'); // save to file because we can  
    }

}
