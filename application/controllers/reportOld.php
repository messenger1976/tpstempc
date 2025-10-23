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
        $this->load->model('setting_model');
        $this->load->model('customer_model');
        $this->load->model('loan_model');
        $this->load->model('share_model');
        $this->load->model('report_model');
    }

    function index() {

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
        }
        $this->data['link_cat'] = $link;
        $this->data['reportlist'] = $this->report_model->report_list(null, $link)->result();
        $this->data['content'] = 'report/ledger/ledger_trans_title';
        $this->load->view('template', $this->data);
    }

    function journal_entry($link) {
        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();

        $this->data['title'] = $title->type . ' Journal';

        $this->data['link_cat'] = $link;
        $this->data['reportlist'] = $this->report_model->report_list_journal(null, $link)->result();
        $this->data['content'] = 'report/journal/journal_trans_title';
        $this->load->view('template', $this->data);
    }

    function create_journal_trans_title($link, $id = null) {
        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();

        $this->data['title'] = $title->type . ' Journal';

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
        }
        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->form_validation->set_rules('fromdate', ($link != 5 ? 'From' : 'Date'), 'required|valid_date');
        if ($link != 5) {
            $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
        }
        $this->form_validation->set_rules('description', 'Description', 'required');

        if ($this->form_validation->run() == TRUE) {
            $from = format_date(trim($this->input->post('fromdate')));
            $to = format_date(trim($this->input->post('todate')));
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            $pass = false;
            if ($link != 5) {
                
                if (strtotime($from) > strtotime($to)) {
                   
                    $pass = FALSE;
                } else {
                    $pass = TRUE;
                }
            } else {
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
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->ledger_trans($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/ledger/ledger_transaction';
        $this->load->view('template', $this->data);
    }

    function journal_trans_view($link, $id) {

        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();
        $this->data['journalinfo'] = $title;

        $this->data['title'] = $title->type . ' Journal';

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list_journal($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->journal_trans($reportinfo->fromdate, $reportinfo->todate, $link);

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

        $this->export_to_pdf($html, 'Ledger_transaction_summary', $reportinfo->page);
    }

    function ledger_trial_balance_view($link, $id) {
        $this->data['title'] = lang('ledger_trial_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
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


        $html = $this->load->view('report/ledger/print//ledger_trial_balance', $this->data, true);


        $this->export_to_pdf($html, 'Trial_balance', $reportinfo->page);
    }

    function ledger_trans_print($link, $id) {

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->ledger_trans($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/ledger/print/ledger_transaction', $this->data, true);
        $this->export_to_pdf($html, 'Ledger_transaction', $reportinfo->page);
    }

    function journal_trans_print($link, $id) {

        $this->db->where('id', $link);
        $title = $this->db->get('journal')->row();
        $this->data['journalinfo'] = $title;

        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list_journal($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->journal_trans($reportinfo->fromdate, $reportinfo->todate, $link);

        $html = $this->load->view('report/journal/print/journal_transaction_print', $this->data, true);
        $this->export_to_pdf($html, 'Journal_Entries', $reportinfo->page);
    }

    function ledger_balance_sheet_view($link, $id) {
        $this->data['title'] = 'Balance Sheet';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;

        $this->data['content'] = 'report/ledger/ledger_balance_sheet';
        $this->load->view('template', $this->data);
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

        $html = $this->load->view('report/ledger/print/ledger_balance_sheet_print', $this->data, true);
        $this->export_to_pdf($html, 'Balance_sheet', $reportinfo->page);
    }

  
     function ledger_income_statement_view($link, $id) {
        $this->data['title'] = 'Balance Sheet';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;

        $this->data['content'] = 'report/ledger/ledger_income_statement';
        $this->load->view('template', $this->data);
    }
    
    
  
     function ledger_income_statement_print($link, $id) {
        $this->data['title'] = 'Balance Sheet';
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $reportinfo = $this->report_model->report_list($id)->row();
        $this->data['reportinfo'] = $reportinfo;

         $html = $this->load->view('report/ledger/print/ledger_income_statement_print', $this->data, true);
        $this->export_to_pdf($html, 'Income_statement', $reportinfo->page);
        
    }
    
    
    
    function export_to_pdf($html, $filename, $page_orientation = null) {
        //$html = "Tanzania";
        $this->load->library('pdf1');
        $pdf = $this->pdf1->load($page_orientation);
        $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td><h2 style="padding: 0px; margin: 0px; font-size:23px;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px;"><strong> P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
        $pdf->SetHTMLHeader($header);
        $pdf->SetFooter('SACCO PLUS' . '|{PAGENO}|' . date('d-m-Y H:i:s')); // Add a footer for good measure <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF
        $pdf->Output($filename, 'I'); // save to file because we can  
    }

}
