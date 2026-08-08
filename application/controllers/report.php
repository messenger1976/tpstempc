<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report
 *
 * @author miltone
 */
class Report extends CI_Controller {

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
        $this->load->model('mortuary_model');
        $this->load->model('setting_model');
        $this->load->model('customer_model');
        $this->load->model('loan_model');
        $this->load->model('share_model');
        $this->load->model('report_model');
    }

    function index() {
        // One-time label heal for legacy journal names on Reports home
        if (function_exists('journal_display_type')) {
            foreach (array(3, 4) as $jid) {
                $row = $this->db->query('SELECT id, type FROM journal WHERE id = ? LIMIT 1', array($jid))->row();
                if ($row) {
                    $display = journal_display_type($row->type);
                    if ($display !== $row->type) {
                        $this->db->update('journal', array('type' => $display), array('id' => $jid));
                    }
                }
            }
        }
        $this->data['title'] = lang('page_report');
        $this->data['content'] = 'report/home';
        $this->load->view('template', $this->data);
    }

    function delete_report_ledger($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table', array('id' => $id));
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
        }
        redirect(current_lang() . '/report/general_leger_transaction/' . $link);
    }

    function delete_report_journal($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table_journal', array('id' => $id));
            redirect(current_lang() . '/report/journal_entry/' . $link);
        }
        redirect(current_lang() . '/report/journal_entry/' . $link);
    }

    function general_leger_transaction($link) {
        if ($link == 1) {
            $this->data['title'] = lang('ledger_transaction');
        } else if ($link == 2) {
            $this->data['title'] = lang('ledger_transaction_summary');
        } else if ($link == 3) {
            $this->data['title'] = lang('ledger_trial_balance');
        } else if ($link == 4) {
            $this->data['title'] = 'Income Statement';
        } else if ($link == 5) {
            $this->data['title'] = 'Balance Sheet';
        } else if ($link == 7) {
            $this->data['title'] = 'Consolidated Statement Of Financial Condition';
        } else if ($link == 8) {
            $this->data['title'] = 'Comparative Statement of Financial Operations - Lending';
        }
        $this->data['link_cat'] = $link;
        $this->data['reportlist'] = $this->report_model->report_list(null, $link)->result();
        $this->data['content'] = 'report/ledger/ledger_trans_title';
        $this->load->view('template', $this->data);
    }

    function journal_entry($link) {
        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();
        if (!$title) {
            $this->session->set_flashdata('error', 'Journal type not found.');
            redirect(current_lang() . '/report/index');
            return;
        }

        // Persist renamed journal labels (legacy Cash Receipts / Loan Disbursement names)
        if (in_array((int) $title->id, array(3, 4), true) && function_exists('journal_display_type')) {
            $display = journal_display_type($title->type);
            if ($display !== $title->type) {
                $this->db->update('journal', array('type' => $display), array('id' => (int) $title->id));
                $title->type = $display;
            }
        }

        $this->data['title'] = (function_exists('journal_display_type') ? journal_display_type($title->type) : $title->type) . ' Journal';
        $this->data['journalinfo'] = $title;

        $this->data['link_cat'] = $link;
        $this->data['reportlist'] = $this->report_model->report_list_journal(null, $link)->result();
        $this->data['content'] = 'report/journal/journal_trans_title';
        $this->load->view('template', $this->data);
    }

    function create_journal_trans_title($link, $id = null) {
        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();
        if (!$title) {
            $this->session->set_flashdata('error', 'Journal type not found.');
            redirect(current_lang() . '/report/index');
            return;
        }
        if (in_array((int) $title->id, array(3, 4), true) && function_exists('journal_display_type')) {
            $display = journal_display_type($title->type);
            if ($display !== $title->type) {
                $this->db->update('journal', array('type' => $display), array('id' => (int) $title->id));
                $title->type = $display;
            }
        }

        $this->data['title'] = (function_exists('journal_display_type') ? journal_display_type($title->type) : $title->type) . ' Journal';
        $this->data['journalinfo'] = $title;
        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->form_validation->set_rules('fromdate', 'From', 'required|valid_date');
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
                );
                if (is_null($id)) {
                    $this->db->insert('report_table_journal', $array);
                } else {
                    $this->db->update('report_table_journal', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report/journal_entry/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }


        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_list_journal($id)->row();
        }

        $this->data['content'] = 'report/journal/create_journal_trans_title';
        $this->load->view('template', $this->data);
    }

    function create_ledger_trans_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('ledger_transaction');
        } else if ($link == 2) {
            $this->data['title'] = lang('ledger_transaction_summary');
        } else if ($link == 3) {
            $this->data['title'] = lang('ledger_trial_balance');
        } else if ($link == 4) {
            $this->data['title'] = 'Income Statement';
        } else if ($link == 5) {
            $this->data['title'] = 'Balance Sheet';
        } else if ($link == 7) {
            $this->data['title'] = 'Consolidated Statement Of Financial Condition';
        } else if ($link == 8) {
            $this->data['title'] = 'Comparative Statement of Financial Operations - Lending';
        }
        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $as_of_report = ($link == 5 || $link == 7 || $link == 8);
        $this->form_validation->set_rules('fromdate', ($as_of_report ? 'Date' : 'From'), 'required|valid_date');
        if (!$as_of_report) {
            $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
        }
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == TRUE) {
            $from = format_date(trim($this->input->post('fromdate')));
            $to = format_date(trim($this->input->post('todate')));
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            $pass = false;
            if (!$as_of_report) {
                
                if (strtotime($from) > strtotime($to)) {
                   
                    $pass = FALSE;
                } else {
                    $pass = TRUE;
                }
            } else {
                // As-of reports use fromdate only; comparative ops derives YTD/month/prior from it
                $to = $from;
                $pass = TRUE;
            }
            if ($pass) {
                $array = array(
                    'fromdate' => $from,
                    'todate' => $to,
                    'description' => $description,
                    'link' => $link,
                    'page' => $page,
                    'PIN' => current_user()->PIN,
                );
                if ($link == 1) {
                    $account = trim($this->input->post('account'));
                    $array['account'] = ($account !== '' ? $account : null);
                }
                if (is_null($id)) {
                    $this->db->insert('report_table', $array);
                } else {
                    $this->db->update('report_table', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report/general_leger_transaction/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }


        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_list($id)->row();
        }

        if ($link == 1) {
            $this->data['account_list'] = $this->finance_model->account_chart(null, null, null)->result();
        }

        $this->data['content'] = 'report/ledger/create_ledger_trans_title';
        $this->load->view('template', $this->data);
    }

    function ledger_trans_view($link, $id) {
        $this->data['title'] = lang('ledger_transaction');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;
        $account = (!empty($reportinfo->account) ? $reportinfo->account : null);
        $this->data['transaction'] = $this->report_model->ledger_trans($reportinfo->fromdate, $reportinfo->todate, $account);
        foreach ($this->data['transaction'] as $t) {
            $ent = $this->report_model->get_gl_related_entity($t);
            $t->related_entity_name = $ent['name'];
            $t->related_entity_url = $ent['url'];
        }
        $this->data['account_name'] = null;
        if (!empty($reportinfo->account)) {
            $ac = $this->finance_model->account_chart(null, $reportinfo->account)->row();
            $this->data['account_name'] = $ac ? $ac->name : $reportinfo->account;
        }

        $this->data['content'] = 'report/ledger/ledger_transaction';
        $this->load->view('template', $this->data);
    }

    function journal_trans_view($link, $id) {

        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();
        
        // Validate journal exists
        if (!$title) {
            $this->session->set_flashdata('error', 'Journal type not found.');
            redirect(current_lang() . '/report/journal_entry/' . $link);
            return;
        }
        
        $this->data['journalinfo'] = $title;
        $this->data['title'] = (function_exists('journal_display_type') ? journal_display_type($title->type) : $title->type) . ' Journal';

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list_journal($id)->row();
        
        // Validate reportinfo exists
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/journal_entry/' . $link);
            return;
        }
        
        // Validate dates exist
        if (empty($reportinfo->fromdate) || empty($reportinfo->todate)) {
            $this->session->set_flashdata('error', 'Report dates are missing. Please edit the report and set valid dates.');
            redirect(current_lang() . '/report/create_journal_trans_title/' . $link . '/' . encode_id($id));
            return;
        }
        
        $this->data['reportinfo'] = $reportinfo;
        $transactions = $this->report_model->journal_trans($reportinfo->fromdate, $reportinfo->todate, $link);
        foreach ($transactions as $t) {
            $ent = $this->report_model->get_gl_related_entity($t);
            $t->related_entity_name = $ent['name'];
            $t->related_entity_url = $ent['url'];
            $t->related_ref_no = isset($ent['ref_no']) ? $ent['ref_no'] : '';
            $t->related_ref_url = isset($ent['ref_url']) ? $ent['ref_url'] : '';
        }
        $this->data['transaction'] = $transactions;

        $this->data['content'] = 'report/journal/journal_transaction';
        $this->load->view('template', $this->data);
    }

    function ledger_trans_summary_view($link, $id) {
        $this->data['title'] = lang('ledger_transaction_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;

        $this->data['content'] = 'report/ledger/ledger_transaction_summary';
        $this->load->view('template', $this->data);
    }

    function ledger_trans_print_summary($link, $id) {
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;


        $html = $this->load->view('report/ledger/print/ledger_transaction_summary', $this->data, true);

        $this->export_to_pdf($html, 'Ledger_transaction_summary', $reportinfo->page ? $reportinfo->page : 'A4-L', false);
    }

    function ledger_trial_balance_view($link, $id) {
        $this->data['title'] = lang('ledger_trial_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;

        $this->data['content'] = 'report/ledger/ledger_trial_balance';
        $this->load->view('template', $this->data);
    }

    function ledger_trial_balance_print($link, $id) {

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;


        $html = $this->load->view('report/ledger/print/ledger_trial_balance', $this->data, true);


        $this->export_to_pdf($html, 'Trial_balance', $reportinfo->page ? $reportinfo->page : 'A4', false);
    }

    function ledger_trans_print($link, $id) {

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $account = (!empty($reportinfo->account) ? $reportinfo->account : null);
        $this->data['transaction'] = $this->report_model->ledger_trans($reportinfo->fromdate, $reportinfo->todate, $account);
        foreach ($this->data['transaction'] as $t) {
            $ent = $this->report_model->get_gl_related_entity($t);
            $t->related_entity_name = $ent['name'];
            $t->related_entity_url = $ent['url'];
        }
        $this->data['account_name'] = null;
        if (!empty($reportinfo->account)) {
            $ac = $this->finance_model->account_chart(null, $reportinfo->account)->row();
            $this->data['account_name'] = $ac ? $ac->name : $reportinfo->account;
        }

        $html = $this->load->view('report/ledger/print/ledger_transaction', $this->data, true);
        $this->export_to_pdf($html, 'Ledger_transaction', $reportinfo->page ? $reportinfo->page : 'A4-L', false);
    }

    /**
     * Export General Ledger Transactions to Excel (link=1 only).
     */
    function ledger_trans_export($link, $id) {
        if ((int)$link !== 1) {
            $this->session->set_flashdata('error', 'Export is only available for General Ledger Transactions.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        $account = (!empty($reportinfo->account) ? $reportinfo->account : null);
        $transaction = $this->report_model->ledger_trans($reportinfo->fromdate, $reportinfo->todate, $account);
        foreach ($transaction as $t) {
            $ent = $this->report_model->get_gl_related_entity($t);
            $t->related_entity_name = $ent['name'];
        }
        $account_name = null;
        if (!empty($reportinfo->account)) {
            $ac = $this->finance_model->account_chart(null, $reportinfo->account)->row();
            $account_name = $ac ? $ac->name : $reportinfo->account;
        }
        if (ob_get_level()) { ob_end_clean(); }
        while (@ob_end_clean());
        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle('General Ledger Transactions')
            ->setSubject('GL Transactions');
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('GL Transactions');
        $row = 1;
        $sheet->setCellValue('A' . $row, company_info()->name);
        $row++;
        $sheet->setCellValue('A' . $row, 'General Ledger Transactions');
        $row++;
        $sheet->setCellValue('A' . $row, 'For the period from ' . format_date($reportinfo->fromdate, false) . ' to ' . format_date($reportinfo->todate, false));
        $row++;
        if ($account_name) {
            $sheet->setCellValue('A' . $row, 'Account: ' . $account_name);
            $row++;
        }
        $row++;
        $headers = array('Type', 'Date', '#', 'Account', 'Person/Member/Item', 'Debit', 'Credit', 'Remarks');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->getStartColor()->setRGB('E0E0E0');
        $row++;
        $debittotal = 0;
        $credittotal = 0;
        foreach ($transaction as $value) {
            $debittotal += $value->debit;
            $credittotal += $value->credit;
            $journal_type = isset($value->trans_comment) ? $value->trans_comment : '';
            $ref_no = (isset($value->invoiceid) && $value->invoiceid > 0) ? '#' . $value->invoiceid : (isset($value->refferenceID) ? '#' . $value->refferenceID : '');
            $account_display = $value->account . ' - ' . $value->name;
            $rel_name = isset($value->related_entity_name) ? $value->related_entity_name : '';
            $desc = isset($value->description) ? $value->description : '';
            $sheet->setCellValue('A' . $row, $journal_type);
            $sheet->setCellValue('B' . $row, format_date($value->date, false));
            $sheet->setCellValue('C' . $row, $ref_no);
            $sheet->setCellValue('D' . $row, $account_display);
            $sheet->setCellValue('E' . $row, $rel_name);
            $sheet->setCellValue('F' . $row, $value->debit > 0 ? number_format($value->debit, 2) : '');
            $sheet->setCellValue('G' . $row, $value->credit > 0 ? number_format($value->credit, 2) : '');
            $sheet->setCellValue('H' . $row, $desc);
            $sheet->getStyle('F' . $row . ':G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $row++;
        }
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, number_format($debittotal, 2));
        $sheet->setCellValue('G' . $row, number_format($credittotal, 2));
        $sheet->setCellValue('H' . $row, '');
        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F' . $row . ':G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        foreach (range('A', 'H') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $filename = 'General_Ledger_Transactions_' . date('Y-m-d_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    function journal_trans_print($link, $id) {

        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();
        if ($title && in_array((int) $title->id, array(3, 4), true) && function_exists('journal_display_type')) {
            $display = journal_display_type($title->type);
            if ($display !== $title->type) {
                $this->db->update('journal', array('type' => $display), array('id' => (int) $title->id));
                $title->type = $display;
            }
        }
        $this->data['journalinfo'] = $title;

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list_journal($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $transactions = $this->report_model->journal_trans($reportinfo->fromdate, $reportinfo->todate, $link);
        foreach ($transactions as $t) {
            $ent = $this->report_model->get_gl_related_entity($t);
            $t->related_entity_name = $ent['name'];
            $t->related_entity_url = $ent['url'];
            $t->related_ref_no = isset($ent['ref_no']) ? $ent['ref_no'] : '';
            $t->related_ref_url = isset($ent['ref_url']) ? $ent['ref_url'] : '';
        }
        $this->data['transaction'] = $transactions;

        $html = $this->load->view('report/journal/print/journal_transaction_print', $this->data, true);
        $pdf_name = 'Journal_Entries';
        if ($title && function_exists('journal_display_type')) {
            $pdf_name = preg_replace('/[^A-Za-z0-9_-]+/', '_', journal_display_type($title->type)) . '_Journal';
        } elseif ($title && !empty($title->type)) {
            $pdf_name = preg_replace('/[^A-Za-z0-9_-]+/', '_', $title->type) . '_Journal';
        }
        $this->export_to_pdf($html, $pdf_name, $reportinfo->page ? $reportinfo->page : 'A4-L', false);
    }

    function ledger_balance_sheet_view($link, $id) {
        $this->data['title'] = 'Balance Sheet';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        
        // Validate reportinfo exists
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        
        // Validate fromdate exists
        if (empty($reportinfo->fromdate)) {
            $this->session->set_flashdata('error', 'Report date is missing. Please edit the report and set a valid date.');
            redirect(current_lang() . '/report/create_ledger_trans_title/' . $link . '/' . encode_id($id));
            return;
        }
        
        $this->data['reportinfo'] = $reportinfo;
        $this->data['bs_data'] = $this->report_model->get_financial_condition_data($reportinfo->fromdate);

        $this->data['content'] = 'report/ledger/ledger_balance_sheet';
        $this->load->view('template', $this->data);
    }

    /**
     * Account ledger drill-down (from Balance Sheet / other FS reports).
     * URL: report/account_ledger/{encoded_account}/{back_link}/{encoded_report_id}
     */
    function account_ledger($account_enc = null, $back_link = null, $back_id = null) {
        $this->data['title'] = 'Account Ledger';
        $this->data['back_link'] = $back_link;
        $this->data['back_id'] = $back_id;

        $account = null;
        if (!is_null($account_enc) && $account_enc !== '') {
            $account = decode_id($account_enc);
        }
        if (empty($account)) {
            $this->session->set_flashdata('warning', 'Account not found.');
            redirect(current_lang() . '/report/index');
            return;
        }

        // Default date range from parent report context
        $default_until = date('Y-m-d');
        $default_from = date('Y-01-01');
        if (!is_null($back_id) && $back_id !== '' && !is_null($back_link)) {
            $report_id = decode_id($back_id);
            if ($report_id) {
                $back_link_str = (string) $back_link;
                $is_journal_back = (strlen($back_link_str) > 1 && $back_link_str[0] === 'j');
                $ri = $is_journal_back
                    ? $this->report_model->report_list_journal($report_id)->row()
                    : $this->report_model->report_list($report_id)->row();
                if ($ri && !empty($ri->fromdate)) {
                    // Period reports (Income Statement, journal, etc.): use from–until
                    // As-of reports (Balance Sheet / Financial Condition): year start → as-of
                    $as_of_links = array('5', '7');
                    if (!$is_journal_back && in_array($back_link_str, $as_of_links, true)) {
                        $default_until = $ri->fromdate;
                        $default_from = date('Y-01-01', strtotime($ri->fromdate));
                    } elseif (!empty($ri->todate) && $ri->todate != '0000-00-00') {
                        $default_from = $ri->fromdate;
                        $default_until = $ri->todate;
                    } else {
                        $default_until = $ri->fromdate;
                        $default_from = date('Y-01-01', strtotime($ri->fromdate));
                    }
                }
            }
        }

        $from_in = trim($this->input->get_post('fromdate'));
        $until_in = trim($this->input->get_post('todate'));
        if ($from_in !== '') {
            $from = format_date($from_in);
        } else {
            $from = $default_from;
        }
        if ($until_in !== '') {
            $until = format_date($until_in);
        } else {
            $until = $default_until;
        }

        if (strtotime($from) > strtotime($until)) {
            $this->data['warning'] = 'From date is greater than until date.';
            $tmp = $from;
            $from = $until;
            $until = $tmp;
        }

        $ledger = $this->report_model->get_account_ledger($account, $from, $until);
        if (!$ledger) {
            $this->session->set_flashdata('warning', 'Account not found in Chart of Accounts.');
            redirect(current_lang() . '/report/index');
            return;
        }

        $this->data['account_enc'] = $account_enc;
        $this->data['ledger'] = $ledger;
        $this->data['fromdate'] = $from;
        $this->data['todate'] = $until;
        $this->data['content'] = 'report/ledger/account_ledger';
        $this->load->view('template', $this->data);
    }

    function account_ledger_print($account_enc = null, $back_link = null, $back_id = null) {
        $account = decode_id($account_enc);
        $from_in = trim($this->input->get('fromdate'));
        $until_in = trim($this->input->get('todate'));
        $from = $from_in !== '' ? format_date($from_in) : date('Y-01-01');
        $until = $until_in !== '' ? format_date($until_in) : date('Y-m-d');

        if (!is_null($back_id) && $back_id !== '' && ($from_in === '' || $until_in === '')) {
            $report_id = decode_id($back_id);
            if ($report_id) {
                $back_link_str = (string) $back_link;
                $is_journal_back = (strlen($back_link_str) > 1 && $back_link_str[0] === 'j');
                $ri = $is_journal_back
                    ? $this->report_model->report_list_journal($report_id)->row()
                    : $this->report_model->report_list($report_id)->row();
                if ($ri && !empty($ri->fromdate)) {
                    $as_of_links = array('5', '7');
                    $is_as_of = !$is_journal_back && in_array($back_link_str, $as_of_links, true);
                    if (!$is_as_of && !empty($ri->todate) && $ri->todate != '0000-00-00') {
                        if ($from_in === '') {
                            $from = $ri->fromdate;
                        }
                        if ($until_in === '') {
                            $until = $ri->todate;
                        }
                    } else {
                        if ($until_in === '') {
                            $until = $ri->fromdate;
                        }
                        if ($from_in === '') {
                            $from = date('Y-01-01', strtotime($ri->fromdate));
                        }
                    }
                }
            }
        }

        $ledger = $this->report_model->get_account_ledger($account, $from, $until);
        if (!$ledger) {
            show_error('Account not found.');
            return;
        }
        $this->data['ledger'] = $ledger;
        $this->data['fromdate'] = $from;
        $this->data['todate'] = $until;
        $html = $this->load->view('report/ledger/print/account_ledger_print', $this->data, true);
        $this->export_to_pdf($html, 'Account_Ledger_' . $account, 'A4-L');
    }

    function ledger_balance_sheet_print($link, $id) {
        $this->data['title'] = 'Balance Sheet';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['bs_data'] = $this->report_model->get_financial_condition_data($reportinfo->fromdate);

        $html = $this->load->view('report/ledger/print/ledger_balance_sheet_print', $this->data, true);
        // Stream PDF inline for modal viewer (same pattern as loan disbursement print)
        $this->export_to_pdf($html, 'Balance_sheet', $reportinfo->page ? $reportinfo->page : 'A4', false);
    }

    function ledger_financial_condition_view($link, $id) {
        $this->data['title'] = 'Consolidated Statement Of Financial Condition';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();

        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }

        if (empty($reportinfo->fromdate)) {
            $this->session->set_flashdata('error', 'Report date is missing. Please edit the report and set a valid date.');
            redirect(current_lang() . '/report/create_ledger_trans_title/' . $link . '/' . encode_id($id));
            return;
        }

        $this->data['reportinfo'] = $reportinfo;
        $this->data['fc_data'] = $this->report_model->get_financial_condition_data($reportinfo->fromdate);
        $this->data['content'] = 'report/ledger/ledger_financial_condition';
        $this->load->view('template', $this->data);
    }

    function ledger_financial_condition_print($link, $id) {
        $this->data['title'] = 'Consolidated Statement Of Financial Condition';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['fc_data'] = $this->report_model->get_financial_condition_data($reportinfo->fromdate);

        $html = $this->load->view('report/ledger/print/ledger_financial_condition_print', $this->data, true);
        $this->export_to_pdf($html, 'Financial_Condition', $reportinfo->page);
    }

    function ledger_financial_operations_view($link, $id) {
        $this->data['title'] = 'Comparative Statement of Financial Operations - Lending';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();

        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }

        if (empty($reportinfo->fromdate)) {
            $this->session->set_flashdata('error', 'Report date is missing. Please edit the report and set a valid date.');
            redirect(current_lang() . '/report/create_ledger_trans_title/' . $link . '/' . encode_id($id));
            return;
        }

        $this->data['reportinfo'] = $reportinfo;
        $this->data['fo_data'] = $this->report_model->get_financial_operations_data($reportinfo->fromdate);
        $this->data['content'] = 'report/ledger/ledger_financial_operations';
        $this->load->view('template', $this->data);
    }

    function ledger_financial_operations_print($link, $id) {
        $this->data['title'] = 'Comparative Statement of Financial Operations - Lending';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['fo_data'] = $this->report_model->get_financial_operations_data($reportinfo->fromdate);

        $html = $this->load->view('report/ledger/print/ledger_financial_operations_print', $this->data, true);
        $this->export_to_pdf($html, 'Financial_Operations', $reportinfo->page ? $reportinfo->page : 'A4-L');
    }

  
     function ledger_income_statement_view($link, $id) {
        $this->data['title'] = 'Income Statement';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/general_leger_transaction/' . $link);
            return;
        }
        $this->data['reportinfo'] = $reportinfo;

        $this->data['content'] = 'report/ledger/ledger_income_statement';
        $this->load->view('template', $this->data);
    }
    
    
  
     function ledger_income_statement_print($link, $id) {
        $this->data['title'] = 'Income Statement';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;

         $html = $this->load->view('report/ledger/print/ledger_income_statement_print', $this->data, true);
        $this->export_to_pdf($html, 'Income_statement', $reportinfo->page ? $reportinfo->page : 'A4', false);
        
    }
    
    
    
    function cash_flow_report($id = null) {
        // Keep old edit URLs working: /cash_flow_report/{encoded_id} → filter form
        if (!is_null($id) && $id !== '') {
            redirect(current_lang() . '/report/create_cash_flow_report/' . $id, 'refresh');
            return;
        }

        $this->data['title'] = 'Statement of Cash Flows';
        $this->data['link_cat'] = 6;
        $this->data['id'] = null;
        $this->data['reportlist'] = $this->report_model->report_list(null, 6)->result();
        $this->data['content'] = 'report/cash_flow/cash_flow_report_title';
        $this->load->view('template', $this->data);
    }

    function create_cash_flow_report($id = null) {
        $this->data['title'] = 'Statement of Cash Flows';
        $this->data['link_cat'] = 6;
        $this->data['id'] = $id;

        $decoded_id = null;
        if (!is_null($id) && $id !== '') {
            $decoded_id = decode_id($id);
            if (!$decoded_id) {
                $this->session->set_flashdata('warning', 'Report not found.');
                redirect(current_lang() . '/report/cash_flow_report', 'refresh');
                return;
            }
        }

        $this->form_validation->set_rules('fromdate', 'From', 'required|valid_date');
        $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
        $this->form_validation->set_rules('description', 'Description', 'required');
        $this->form_validation->set_rules('page', 'Page Orientation', 'required');

        if ($this->form_validation->run() == TRUE) {
            $from = format_date(trim($this->input->post('fromdate')));
            $to = format_date(trim($this->input->post('todate')));
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            if ($page !== 'A4' && $page !== 'A4-L') {
                $page = 'A4';
            }

            if (strtotime($from) <= strtotime($to)) {
                $array = array(
                    'fromdate' => $from,
                    'todate' => $to,
                    'description' => $description,
                    'link' => 6,
                    'page' => $page,
                    'PIN' => current_user()->PIN,
                );
                if (is_null($decoded_id)) {
                    $this->db->insert('report_table', $array);
                    $new_id = $this->db->insert_id();
                    redirect(current_lang() . '/report/cash_flow_report_view/' . encode_id($new_id), 'refresh');
                    return;
                }

                $this->db->update('report_table', $array, array(
                    'id' => $decoded_id,
                    'link' => 6,
                    'PIN' => current_user()->PIN,
                ));
                redirect(current_lang() . '/report/cash_flow_report_view/' . encode_id($decoded_id), 'refresh');
                return;
            }

            $this->data['warning'] = 'From date is greater than until date';
        }

        if (!is_null($decoded_id)) {
            $reportinfo = $this->report_model->report_list($decoded_id, 6)->row();
            if (!$reportinfo) {
                $this->session->set_flashdata('warning', 'Report not found.');
                redirect(current_lang() . '/report/cash_flow_report', 'refresh');
                return;
            }
            $this->data['reportinfo'] = $reportinfo;
        }

        $this->data['content'] = 'report/cash_flow/create_cash_flow_report';
        $this->load->view('template', $this->data);
    }

    function cash_flow_report_view($id) {
        $this->data['title'] = 'Statement of Cash Flows';
        $this->data['link_cat'] = 6;
        $this->data['id'] = $id;
        
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        
        $reportinfo = $this->report_model->report_list($id)->row();
        if (!$reportinfo) {
            $this->session->set_flashdata('error', 'Report not found.');
            redirect(current_lang() . '/report/cash_flow_report');
            return;
        }
        $this->data['reportinfo'] = $reportinfo;
        $this->data['cash_flow_data'] = $this->report_model->get_cash_flow_data($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/cash_flow/cash_flow_report_view';
        $this->load->view('template', $this->data);
    }

    function cash_flow_report_print($id) {
        $this->data['title'] = 'Statement of Cash Flows';
        $this->data['link_cat'] = 6;
        $this->data['id'] = $id;
        
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['cash_flow_data'] = $this->report_model->get_cash_flow_data($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/cash_flow/print/cash_flow_report_print', $this->data, true);
        $this->export_to_pdf($html, 'Cash_Flow_Report', $reportinfo->page ? $reportinfo->page : 'A4', false);
    }

    function delete_cash_flow_report($id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table', array('id' => $id, 'link' => 6));
            redirect(current_lang() . '/report/cash_flow_report', 'refresh');
        }
        redirect(current_lang() . '/report/cash_flow_report', 'refresh');
    }
    
    function export_to_pdf($html, $filename, $page_orientation = null, $with_default_header = true) {
        //$html = "Tanzania";
        $this->load->library('pdf1');
        if ($page_orientation == NULL) {
            $page_orientation = 'A4';
        }
        if ($with_default_header) {
            $pdf = $this->pdf1->load($page_orientation);
            $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td><h2 style="padding: 0px; margin: 0px; font-size:23px;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px;"><strong> P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
            $pdf->SetHTMLHeader($header);
        } else {
            // Compact top margin when report HTML already includes its own header (e.g. Balance Sheet)
            include_once APPPATH . '/third_party/mpdf/mpdf.php';
            $pdf = new mPDF('', $page_orientation, '', '', 10, 10, 8, 12, 5, 5);
        }
        $pdf->SetFooter('|{PAGENO}|' . date('d-m-Y H:i:s'));
        // Avoid browsers / PDF.js keeping a stale preview after template changes
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        $pdf->WriteHTML($html);
        $output_mode = ($this->input->get('download') === '1') ? 'D' : 'I';
        $pdf->Output($filename . '.pdf', $output_mode);
    }

}
