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
        $this->export_to_pdf($html, 'Contribution_balance', $reportinfo->page);
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
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Member CBU balance');
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'Member Joined As of ' . format_date($reportinfo->todate, false));
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Set column headers
        $sheet->setCellValue('A5', 'S/No');
        $sheet->setCellValue('B5', 'Member ID');
        $sheet->setCellValue('C5', 'Name');
        $sheet->setCellValue('D5', 'Balance');
        
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
        
        $sheet->getStyle('A5:D5')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(20);
        
        // Populate data
        $row = 6;
        $i = 1;
        $total_balance = 0;
        foreach ($transaction as $value) {
            $total_balance += $value->balance;
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $value->member_id);
            $sheet->setCellValue('C' . $row, $value->name);
            $sheet->setCellValue('D' . $row, number_format($value->balance, 2));
            
            // Set alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            
            // Add borders to cells
            $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray(array(
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
        $sheet->setCellValue('D' . $row, number_format($total_balance, 2));
        
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
        
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($totalStyle);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
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
        $this->export_to_pdf($html, 'Contribution_Statement', $reportinfo->page);
    }
    
    
    
    
    
    function contribution_transaction_view($link, $id) {
        $this->data['title'] = lang('member_contribution_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
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
        $this->export_to_pdf($html, 'Contribution_Transactions', $reportinfo->page);
        
    }
    
    
    function contribution_transaction_summary_view($link, $id) {
        $this->data['title'] = lang('member_contribution_transactions_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_contribution($id)->row();
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
        $this->export_to_pdf($html, 'contribution_trans_summary', $reportinfo->page);
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
        $pdf->SetFooter('Bohollander COOP System' . '|{PAGENO}|' . date('d-m-Y H:i:s')); // Add a footer for good measure <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($filename, 'I'); // save to file because we can  
    }

}
