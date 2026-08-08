<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report_contribution
 *
 * @author miltone
 */
class Report_Contribution extends CI_Controller {

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

    function delete_report_contribution($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table_contribution', array('id' => $id));
            redirect(current_lang() . '/report_contribution/contribution_report/' . $link);
        }
        redirect(current_lang() . '/report_contribution/contribution_report/' . $link);
    }

    function contribution_report($link, $id = null) {

        if ($link == 1) {
            $this->data['title'] = lang('member_contribution_balance');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_contribution_statement');
        } else if ($link == 3) {
            $this->data['title'] = lang('member_contribution_transactions');
        } else if ($link == 4) {
            $this->data['title'] = lang('member_contribution_transactions_summary');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->data['reportlist'] = $this->report_model->report_contribution(null, $link)->result();
        $this->data['content'] = 'report/contribution/contribution_report_title';
        $this->load->view('template', $this->data);
    }

    function create_contribution_report_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_contribution_balance');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_contribution_statement');
        } else if ($link == 3) {
            $this->data['title'] = lang('member_contribution_transactions');
        } else if ($link == 4) {
            $this->data['title'] = lang('member_contribution_transactions_summary');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->form_validation->set_rules('fromdate', ($link == 1 ? 'Member Joined From' : 'From'), 'required|valid_date');
        $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
        $this->form_validation->set_rules('description', 'Description', 'required');

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
                    'user' =>  $this->session->userdata('user_id')
                );

                if (is_null($id)) {
                    $this->db->insert('report_table_contribution', $array);
                } else {
                    $this->db->update('report_table_contribution', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report_contribution/contribution_report/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }


        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_contribution($id)->row();
        }

        $this->data['content'] = 'report/contribution/create_contribution_report_title';
        $this->load->view('template', $this->data);
    }

    function contribution_balance_view($link, $id) {
        $this->data['title'] = lang('member_contribution_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report_contribution/contribution_report/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_contribution_balance($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/contribution/contribution_list_balance';
        $this->load->view('template', $this->data);
    }

    function contribution_balance_print($link, $id) {
        $this->data['title'] = lang('member_contribution_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_contribution_balance($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/contribution/print/contribution_list_balance_print', $this->data, true);
        $this->export_to_pdf($html, 'Member_CBU_Balance', $reportinfo->page ? $reportinfo->page : 'A4', false);
    }

    function contribution_balance_export($link, $id) {
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

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $transaction = $this->report_model->account_contribution_balance($reportinfo->fromdate, $reportinfo->todate);
        
        // Check if we have data
        if (empty($transaction) || !is_array($transaction) || count($transaction) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/report_contribution/contribution_balance_view/' . $link . '/' . $encoded_id, 'refresh');
            exit();
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Member CBU Balance")
                                     ->setSubject("Member CBU Balance Export")
                                     ->setDescription("Member CBU Balance exported from " . company_info()->name);
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Member CBU Balance');
        
        // Add company name and report title
        $sheet->setCellValue('A1', company_info()->name);
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'MEMBER CBU BALANCE');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'As of ' . format_date($reportinfo->todate, false));
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Set column headers
        $sheet->setCellValue('A5', 'S/No');
        $sheet->setCellValue('B5', 'Member ID');
        $sheet->setCellValue('C5', 'Member Name');
        $sheet->setCellValue('D5', 'Date Joined');
        $sheet->setCellValue('E5', 'CBU Balance');
        
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
        
        $sheet->getStyle('A5:E5')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(18);
        
        // Populate data
        $row = 6;
        $i = 1;
        $total_balance = 0;
        foreach ($transaction as $value) {
            $row_balance = floatval($value->balance);
            $total_balance += $row_balance;
            $joined = '';
            if (!empty($value->joiningdate) && $value->joiningdate !== '0000-00-00' && $value->joiningdate !== '0000-00-00 00:00:00') {
                $joined = format_date(substr($value->joiningdate, 0, 10), false);
            }
            $name = trim(preg_replace('/\s+/', ' ', isset($value->name) ? $value->name : ''));
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $value->member_id);
            $sheet->setCellValue('C' . $row, $name);
            $sheet->setCellValue('D' . $row, $joined);
            $sheet->setCellValue('E' . $row, number_format($row_balance, 2));
            
            // Set alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
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
        
        // Add total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, number_format($total_balance, 2));
        
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
        
        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
        // Set filename
        $filename = 'Member_CBU_Balance_' . date('Y-m-d_His') . '.xls';
        
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

    function contribution_statement_view($link, $id) {
        $this->data['title'] = lang('member_contribution_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report_contribution/contribution_report/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->contribution_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

        $this->data['content'] = 'report/contribution/contribution_statement';
        $this->load->view('template', $this->data);
    }

    function contribution_statement_print($link, $id) {
        $this->data['title'] = lang('member_contribution_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->contribution_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

        $html = $this->load->view('report/contribution/print/contribution_statement_print', $this->data, true);
        $this->export_to_pdf($html, 'Member_CBU_Statement', $reportinfo->page ? $reportinfo->page : 'A4', false);
    }
    
    
    
    
    
    function contribution_transaction_view($link, $id) {
        $this->data['title'] = lang('member_contribution_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report_contribution/contribution_report/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->contribution_transactions($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/contribution/contribution_transactions';
        $this->load->view('template', $this->data);
    }

    function contribution_transaction_print($link, $id) {
        $this->data['title'] = lang('member_contribution_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->contribution_transactions($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/contribution/print/contribution_transactions_print', $this->data, true);
        $this->export_to_pdf($html, 'Member_CBU_Transactions', $reportinfo->page ? $reportinfo->page : 'A4-L', false);
    }

    function contribution_transaction_export($link, $id) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());

        $this->output->enable_profiler(FALSE);
        $this->output->set_output('');

        $this->load->library('excel');

        $encoded_id = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $transaction = $this->report_model->contribution_transactions($reportinfo->fromdate, $reportinfo->todate);

        if (empty($transaction) || !is_array($transaction) || count($transaction) == 0) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/report_contribution/contribution_transaction_view/' . $link . '/' . $encoded_id, 'refresh');
            exit();
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle('Member CBU Transactions')
            ->setSubject('Member CBU Transactions Export')
            ->setDescription('Member CBU Transactions exported from ' . company_info()->name);

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('CBU Transactions');

        $sheet->setCellValue('A1', company_info()->name);
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'MEMBER CBU TRANSACTIONS');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'For the period from ' . format_date($reportinfo->fromdate, false) . ' to ' . format_date($reportinfo->todate, false));
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A5', 'S/No');
        $sheet->setCellValue('B5', 'Date');
        $sheet->setCellValue('C5', 'Member ID');
        $sheet->setCellValue('D5', 'Member Name');
        $sheet->setCellValue('E5', 'Particulars');
        $sheet->setCellValue('F5', 'Method');
        $sheet->setCellValue('G5', 'Debit');
        $sheet->setCellValue('H5', 'Credit');

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
        $sheet->getStyle('A5:H5')->applyFromArray($headerStyle);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(32);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(14);

        $row = 6;
        $i = 1;
        $total_debit = 0;
        $total_credit = 0;
        foreach ($transaction as $value) {
            $dt = explode(' ', $value->createdon);
            $trans_date = isset($dt[0]) ? format_date($dt[0], false) : '';
            $member_name = trim(preg_replace('/\s+/', ' ', !empty($value->member_name) ? $value->member_name : ''));
            $particulars = trim(isset($value->system_comment) ? $value->system_comment : '');
            if (!empty($value->comment)) {
                $particulars .= ($particulars !== '' ? ' — ' : '') . $value->comment;
            }
            $row_debit = floatval($value->debit);
            $row_credit = floatval($value->credit);

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $trans_date);
            $sheet->setCellValue('C' . $row, $value->member_id);
            $sheet->setCellValue('D' . $row, $member_name);
            $sheet->setCellValue('E' . $row, $particulars);
            $sheet->setCellValue('F' . $row, $value->paymethod);
            $sheet->setCellValue('G' . $row, $row_debit > 0 ? number_format($row_debit, 2) : '');
            $sheet->setCellValue('H' . $row, $row_credit > 0 ? number_format($row_credit, 2) : '');

            $total_debit += $row_debit;
            $total_credit += $row_credit;

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            $row++;
        }

        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, '');
        $sheet->setCellValue('G' . $row, number_format($total_debit, 2));
        $sheet->setCellValue('H' . $row, number_format($total_credit, 2));
        $totalStyle = array(
            'font' => array('bold' => true),
            'borders' => array(
                'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            ),
        );
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());

        $filename = 'CBU_Transactions_' . date('Y-m-d_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }
    
    function contribution_transaction_summary_view($link, $id) {
        $this->data['title'] = lang('member_contribution_transactions_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report_contribution/contribution_report/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->contribution_transactions_summary($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/contribution/contribution_transactions_summary';
        $this->load->view('template', $this->data);
    }

    function contribution_transaction_summary_print($link, $id) {
        $this->data['title'] = lang('member_contribution_transactions_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->contribution_transactions_summary($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/contribution/print/contribution_transactions_summary_print', $this->data, true);
        $this->export_to_pdf($html, 'Member_CBU_Transactions_Summary', $reportinfo->page ? $reportinfo->page : 'A4-L', false);
    }

    function contribution_transaction_summary_export($link, $id) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());

        $this->output->enable_profiler(FALSE);
        $this->output->set_output('');

        $this->load->library('excel');

        $encoded_id = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
        $transaction = $this->report_model->contribution_transactions_summary($reportinfo->fromdate, $reportinfo->todate);

        if (empty($transaction) || !is_array($transaction) || count($transaction) == 0) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/report_contribution/contribution_transaction_summary_view/' . $link . '/' . $encoded_id, 'refresh');
            exit();
        }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle('Member CBU Transactions Summary')
            ->setSubject('Member CBU Transactions Summary Export')
            ->setDescription('Member CBU Transactions Summary exported from ' . company_info()->name);

        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('CBU Transactions Summary');

        $sheet->setCellValue('A1', company_info()->name);
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'MEMBER CBU TRANSACTIONS SUMMARY');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'For the period from ' . format_date($reportinfo->fromdate, false) . ' to ' . format_date($reportinfo->todate, false));
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A5', 'S/No');
        $sheet->setCellValue('B5', 'Member ID');
        $sheet->setCellValue('C5', 'Member Name');
        $sheet->setCellValue('D5', 'Opening Balance');
        $sheet->setCellValue('E5', 'Debit');
        $sheet->setCellValue('F5', 'Credit');
        $sheet->setCellValue('G5', 'Closing Balance');

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

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(18);

        $row = 6;
        $i = 1;
        $total_debit = 0;
        $total_credit = 0;
        foreach ($transaction as $value) {
            $balance_open = $this->report_model->contribution_transactions_summary_previous($reportinfo->fromdate, $value->member_id);
            $balance_tmp = 0;
            if ($balance_open) {
                $balance_tmp = $balance_open->credit - $balance_open->debit;
            }
            $close_balance = $balance_tmp - $value->debit + $value->credit;

            $opening_label = number_format($balance_tmp, 2);
            if ($balance_tmp > 0) {
                $opening_label = number_format($balance_tmp, 2) . ' Cr';
            } elseif ($balance_tmp < 0) {
                $opening_label = number_format((-1 * $balance_tmp), 2) . ' Dr';
            }
            $closing_label = number_format($close_balance, 2);
            if ($close_balance > 0) {
                $closing_label = number_format($close_balance, 2) . ' Cr';
            } elseif ($close_balance < 0) {
                $closing_label = number_format((-1 * $close_balance), 2) . ' Dr';
            }

            $member_name = trim(preg_replace('/\s+/', ' ', !empty($value->member_name) ? $value->member_name : ''));

            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $value->member_id);
            $sheet->setCellValue('C' . $row, $member_name);
            $sheet->setCellValue('D' . $row, $opening_label);
            $sheet->setCellValue('E' . $row, $value->debit > 0 ? number_format($value->debit, 2) : '');
            $sheet->setCellValue('F' . $row, $value->credit > 0 ? number_format($value->credit, 2) : '');
            $sheet->setCellValue('G' . $row, $closing_label);

            $total_debit += $value->debit;
            $total_credit += $value->credit;

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            $row++;
        }

        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, number_format($total_debit, 2));
        $sheet->setCellValue('F' . $row, number_format($total_credit, 2));
        $sheet->setCellValue('G' . $row, '');
        $totalStyle = array(
            'font' => array('bold' => true),
            'borders' => array(
                'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
            ),
        );
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('E' . $row . ':F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());

        $filename = 'CBU_Transactions_Summary_' . date('Y-m-d_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }
            
    
    
    function export_to_pdf($html, $filename, $page_orientation = null, $with_default_header = true) {
        $this->load->library('pdf1');
        if ($page_orientation == NULL) {
            $page_orientation = 'A4';
        }
        if ($with_default_header) {
            $pdf = $this->pdf1->load($page_orientation);
            $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td style="text-align:center;"><h2 style="padding: 0px; margin: 0px;font-size:18px; text-align:center;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px; text-align:center;"><strong> P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
            $pdf->SetHTMLHeader($header);
        } else {
            include_once APPPATH . '/third_party/mpdf/mpdf.php';
            $pdf = new mPDF('', $page_orientation, '', '', 10, 10, 8, 12, 5, 5);
        }
        $pdf->SetFooter('|{PAGENO}|' . date('d-m-Y H:i:s'));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        $pdf->WriteHTML($html);
        $output_mode = ($this->input->get('download') === '1') ? 'D' : 'I';
        $pdf->Output($filename . '.pdf', $output_mode);
    }

}
