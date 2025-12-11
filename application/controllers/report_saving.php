<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report_saving
 *
 * @author miltone
 */
class Report_Saving extends CI_Controller {

    //put your code here
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

    function saving_account_report($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('saving_account_list');
        } else if ($link == 2) {
            $this->data['title'] = lang('saving_account_statement');
        
        } else if ($link == 3) {
            $this->data['title'] = lang('saving_account_transactions');
        } else if ($link == 4) {
            $this->data['title'] = lang('saving_account_transactions_summary');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->data['reportlist'] = $this->report_model->report_saving(null, $link)->result();
        $this->data['content'] = 'report/saving/saving_report_title';
        $this->load->view('template', $this->data);
    }

    function saving_account_report_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('saving_account_list');
        } else if ($link == 2) {
            $this->data['title'] = lang('saving_account_statement');
       
        } else if ($link == 3) {
            $this->data['title'] = lang('saving_account_transactions');
        }else if ($link == 4) {
            $this->data['title'] = lang('saving_account_transactions_summary');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->form_validation->set_rules('fromdate', 'From', 'required|valid_date');
        $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
        $this->form_validation->set_rules('description', ($link != 2 ? 'Description' : 'Account'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $from = format_date(trim($this->input->post('fromdate')));
            $to = format_date(trim($this->input->post('todate')));
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            if ($from <= $to) {
                $array = array(
                    'fromdate' => $from,
                    'todate' => $to,
                    'description' => $description,
                    'link' => $link,
                    'page' => $page,
                     'PIN' => current_user()->PIN,
                );
                if ($link == 1) {

                    $account_type = $this->input->post('account_type');
                    $array['account_type'] = $account_type;
                }
                if (is_null($id)) {
                    $this->db->insert('report_table_saving', $array);
                } else {
                    $this->db->update('report_table_saving', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report_saving/saving_account_report/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }


        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_saving($id)->row();
        }

        $this->data['content'] = 'report/saving/saving_account_report_title';
        $this->load->view('template', $this->data);
    }

    function delete_report_saving_account($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table_saving', array('id' => $id));
            redirect(current_lang() . '/report_saving/saving_account_report/' . $link);
        }
        redirect(current_lang() . '/report_saving/saving_account_report/' . $link);
    }

    function saving_account_accountlist_view($link, $id) {
        $this->data['title'] = lang('saving_account_list');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_balance($reportinfo->fromdate, $reportinfo->todate, $reportinfo->account_type);

        $this->data['content'] = 'report/saving/account_list_balance';
        $this->load->view('template', $this->data);
    }
  

    function saving_account_accountlist_print($link, $id) {
        $this->data['title'] = lang('saving_account_list');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_balance($reportinfo->fromdate, $reportinfo->todate, $reportinfo->account_type);

        $html = $this->load->view('report/saving/print/account_list_balance_print', $this->data, true);
        $this->export_to_pdf($html, 'Saving_account_list', $reportinfo->page);
    }

    function saving_account_accountlist_export($link, $id) {
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
        
        // Store original encoded ID for redirect
        $encoded_id = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $transaction = $this->report_model->account_saving_balance($reportinfo->fromdate, $reportinfo->todate, $reportinfo->account_type);
        
        // Check if we have data
        if (empty($transaction) || !is_array($transaction) || count($transaction) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/report_saving/saving_account_accountlist_view/' . $link . '/' . $encoded_id, 'refresh');
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
        
        // Add company name and report title
        $sheet->setCellValue('A1', company_info()->name);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Saving Account List');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'Account created from ' . format_date($reportinfo->fromdate, false) . ' to ' . format_date($reportinfo->todate, false));
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Set column headers
        $sheet->setCellValue('A5', 'S/No');
        $sheet->setCellValue('B5', 'Account No');
        $sheet->setCellValue('C5', 'Member ID');
        $sheet->setCellValue('D5', 'Account Name');
        $sheet->setCellValue('E5', 'Account Type');
        $sheet->setCellValue('F5', 'Available Balance');
        $sheet->setCellValue('G5', 'Actual Balance');
        
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
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
        );
        
        $sheet->getStyle('A5:G5')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        
        // Populate data
        $row = 6;
        $i = 1;
        $balance = 0;
        $actual = 0;
        foreach ($transaction as $value) {
            $account = $this->finance_model->saving_account_list(null, $value->account_cat)->row();
            $account_name = $this->report_model->saving_account_name($value->RFID, $value->tablename);
            
            $balance += $value->balance;
            $actual += $value->balance;
            $actual += $value->virtual_balance;
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, !empty($value->old_members_acct) ? $value->old_members_acct : $value->account);
            $sheet->setCellValue('C' . $row, !empty($value->members_member_id) ? $value->members_member_id : $value->member_id);
            $sheet->setCellValue('D' . $row, $account_name);
            $sheet->setCellValue('E' . $row, $account->name);
            $sheet->setCellValue('F' . $row, number_format($value->balance, 2));
            $sheet->setCellValue('G' . $row, number_format(($value->balance + $value->virtual_balance), 2));
            
            // Set alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            
            // Add borders to cells
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            
            $row++;
        }
        
        // Add total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, number_format($balance, 2));
        $sheet->setCellValue('G' . $row, number_format($actual, 2));
        
        // Style total row
        $totalStyle = array(
            'font' => array(
                'bold' => true,
            ),
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'left' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'right' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
            ),
        );
        
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
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
        
        // Exit immediately to prevent any further output
        exit();
    }
    
    
      function saving_account_statement_view($link, $id) {
        $this->data['title'] = lang('saving_account_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

        $this->data['content'] = 'report/saving/account_saving_statement';
        $this->load->view('template', $this->data);
    }
    function new_saving_account_statement_view($link, $id, $account) {
        $this->data['title'] = lang('saving_account_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($account)) {
            $account = decode_id($account);
        }
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $this->data['account'] = $account;
        
        // Get account info to retrieve old_members_acct
        $account_info = $this->finance_model->saving_account_balance($account);
        $this->data['account_info'] = $account_info;
        
        $reportinfo = $this->report_model->report_saving($id,$link)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_statement($reportinfo->fromdate, $reportinfo->todate, $account);

        $this->data['content'] = 'report/saving/account_saving_statement_ledger';
        $this->load->view('template', $this->data);
    }
    
    function new_saving_account_statement_print($link, $id, $account) {
        $this->data['title'] = lang('saving_account_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($account)) {
            $account = decode_id($account);
        }
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $this->data['account'] = $account;
        
        // Get account info to retrieve old_members_acct
        $account_info = $this->finance_model->saving_account_balance($account);
        $this->data['account_info'] = $account_info;
        
        $reportinfo = $this->report_model->report_saving($id,$link)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_statement($reportinfo->fromdate, $reportinfo->todate, $account);

        $html = $this->load->view('report/saving/print/account_saving_statement_ledger_print', $this->data, true);
        $this->export_to_pdf($html, 'Account_Statement', $reportinfo->page);
    }
    
    function new_saving_account_statement_export($link, $id, $account) {
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
        
        // Store original encoded IDs for redirect
        $encoded_id = $id;
        $encoded_account = $account;
        if (!is_null($account)) {
            $account = decode_id($account);
        }
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        
        // Get account info to retrieve old_members_acct
        $account_info = $this->finance_model->saving_account_balance($account);
        
        $reportinfo = $this->report_model->report_saving($id,$link)->row();
        $transaction = $this->report_model->account_saving_statement($reportinfo->fromdate, $reportinfo->todate, $account);
        
        // Check if we have data
        if (empty($transaction) || !is_array($transaction) || count($transaction) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/report_saving/new_saving_account_statement_view/' . $link . '/' . $encoded_id . '/' . $encoded_account, 'refresh');
            exit();
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Account Statement")
                                     ->setSubject("Account Statement Export")
                                     ->setDescription("Account Statement exported from " . company_info()->name);
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Account Statement');
        
        // Add company name and report title
        $sheet->setCellValue('A1', company_info()->name);
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Account Statement');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'For the period from ' . format_date($reportinfo->fromdate, false) . ' to ' . format_date($reportinfo->todate, false));
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Account details
        $account_number = !empty($account_info) && !empty($account_info->old_members_acct) ? $account_info->old_members_acct : $account;
        $account_name = $this->finance_model->saving_account_name($account);
        $sheet->setCellValue('A4', 'Account Number: ' . $account_number);
        $sheet->setCellValue('A5', 'Account Name: ' . $account_name);
        
        // Set column headers
        $sheet->setCellValue('A7', 'Date');
        $sheet->setCellValue('B7', 'Description');
        $sheet->setCellValue('C7', 'Debit [DR]');
        $sheet->setCellValue('D7', 'Credit [CR]');
        $sheet->setCellValue('E7', 'Balance');
        
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
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
        );
        
        $sheet->getStyle('A7:E7')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        
        // Populate data
        $row = 8;
        $balance = 0;
        $credit = 0;
        $debit = 0;
        
        if (count($transaction) > 0) {
            $balance = $transaction[0]->credit_total - $transaction[0]->debit_total;
            // Write brought forward balance
            $sheet->setCellValue('B' . $row, 'BROUGHT FORWARD BALANCE');
            $sheet->setCellValue('E' . $row, number_format($balance, 2));
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            $row++;
        }
        
        foreach ($transaction as $value) {
            $dt = explode(' ', $value->trans_date);
            if ($value->debit > 0) {
                $balance -= $value->debit;
                $debit += $value->debit;
            } else if ($value->credit > 0) {
                $balance += $value->credit;
                $credit += $value->credit;
            }
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, format_date($dt[0], FALSE));
            $sheet->setCellValue('B' . $row, $value->system_comment . ' [' . $value->paymethod . '] ' . $value->comment);
            $sheet->setCellValue('C' . $row, $value->debit > 0 ? number_format($value->debit, 2) : '');
            $sheet->setCellValue('D' . $row, $value->credit > 0 ? number_format($value->credit, 2) : '');
            $sheet->setCellValue('E' . $row, number_format($balance, 2));
            
            // Set alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            
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
        
        // Add totals row
        $sheet->setCellValue('C' . $row, number_format($debit, 2));
        $sheet->setCellValue('D' . $row, number_format($credit, 2));
        $sheet->setCellValue('E' . $row, number_format($balance, 2));
        
        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'font' => array(
                'bold' => true
            )
        ));
        
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
        // Generate filename
        $filename = 'Account_Statement_' . date('Y-m-d_His') . '.xls';
        
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
   
      function saving_account_statement_print($link, $id) {
        $this->data['title'] = lang('saving_account_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

         $html = $this->load->view('report/saving/print/account_saving_statement_print', $this->data, true);
        $this->export_to_pdf($html, 'Account_Statement', $reportinfo->page);
        
    }
    
    
       function saving_account_transaction_view($link, $id) {
        $this->data['title'] = lang('saving_account_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_transactions($reportinfo->fromdate, $reportinfo->todate);
        
        $this->data['content'] = 'report/saving/account_saving_transactions';
        $this->load->view('template', $this->data);
    }
    
       function saving_account_transaction_print($link, $id) {
        $this->data['title'] = lang('saving_account_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_transactions($reportinfo->fromdate, $reportinfo->todate);
        
         $html = $this->load->view('report/saving/print/account_saving_transactions_print', $this->data, true);
        $this->export_to_pdf($html, 'Saving_transactions', $reportinfo->page);
    }
    
    
    
       function saving_account_transaction_summary_view($link, $id) {
        $this->data['title'] = lang('saving_account_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_transactions_summary($reportinfo->fromdate, $reportinfo->todate);
        
        $this->data['content'] = 'report/saving/account_saving_transactions_summary';
        $this->load->view('template', $this->data);
    }
    
       function saving_account_transaction_summary_print($link, $id) {
        $this->data['title'] = lang('saving_account_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_saving($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_saving_transactions_summary($reportinfo->fromdate, $reportinfo->todate);
        
       
         $html = $this->load->view('report/saving/print/account_saving_transactions_summary_print', $this->data, true);
        $this->export_to_pdf($html, 'Saving_transactions', $reportinfo->page);
    }
    
    
    
    

    function export_to_pdf($html, $filename, $page_orientation = null) {
        //$html = "Tanzania";
        $this->load->library('pdf1');
        $pdf = $this->pdf1->load($page_orientation);
        $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td style="text-align:center;"><h2 style="padding: 0px; margin: 0px;font-size:18px;text-align:center;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px;text-align:center;"><strong> P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
        $pdf->SetHTMLHeader($header);
        $pdf->SetFooter('SACCO PLUS' . '|{PAGENO}|' . date('d-m-Y H:i:s')); // Add a footer for good measure <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($filename, 'I'); // save to file because we can  
    }

    function saving_edit_entry(){
        $this->load->model('report_model');
        $id = $this->input->post('id');
        $trans_date = $this->input->post('trans_date');
        $description = $this->input->post('description');
        $paymentmethod = $this->input->post('paymentmethod');
        $trans_type = $this->input->post('trans_type');
        $amount = $this->input->post('amount');
        $comment = $this->input->post('comment');
        
        
        
        
        $ifposted = $this->report_model->save_edit_entry($id, $trans_date,$description,$paymentmethod, $trans_type, $amount, $comment);
        $status['success'] = 'Y';
                
            



        
       // $ifposted = $this->mortuary_model->post_to_gl($id, $posted);
        //$status['success'] = 'Y';
        $status['id'] = $id;
        //$status['posted'] = $posted;
        
        echo json_encode($status);
    }

    function recomputebalancesindividual($pid,$balance){
        $pin = current_user()->PIN;
        $this->db->select('*');
		$this->db->from('members_account');	
		$this->db->where('account', $pid);
		//$this->db->where('member_id', $mid);	
		$membermortuarycount  = $this->db->get()->num_rows();
		
		if($membermortuarycount == 1){
			
			$dataUpdate = array( 
			'balance' => $balance, //
			'PIN' => $pin, //
			);
			
			$this->db->where('account', $pid);
		    //$this->db->where('member_id', $mid);
		    //$this->db->where('PIN', $pin);
		    $this->db->update('members_account', $dataUpdate);
			echo json_encode(array('success'=>1,'message'=>'Process Successfully','balance'=>$balance));
		}else{
            echo json_encode(array('success'=>1,'message'=>'Process Failed','balance'=>$balance));
        }
        
    }

}
