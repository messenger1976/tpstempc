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
class Report_Mortuary extends CI_Controller {

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

    function delete_report_mortuary($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table_mortuary', array('id' => $id));
            redirect(current_lang() . '/report_mortuary/contribution_report/' . $link);
        }
        redirect(current_lang() . '/report_mortuary/contribution_report/' . $link);
    }

    function contribution_report($link, $id = null) {

        if ($link == 1) {
            $this->data['title'] = lang('member_mortuary_balance');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_mortuary_statement');
        } else if ($link == 3) {
            $this->data['title'] = lang('member_mortuary_transactions');
        } else if ($link == 4) {
            $this->data['title'] = lang('member_mortuary_transactions_summary');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $this->data['reportlist'] = $this->report_model->report_mortuary(null, $link)->result();
        $this->data['content'] = 'report/mortuary/contribution_report_title';
        $this->load->view('template', $this->data);
    }

    function create_contribution_report_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_mortuary_balance');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_mortuary_statement');
        } else if ($link == 3) {
            $this->data['title'] = lang('member_mortuary_transactions');
        } else if ($link == 4) {
            $this->data['title'] = lang('member_mortuary_transactions_summary');
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
            $status = $this->input->post('status');
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            if ($from <= $to) {
                $array = array(
                    'fromdate' => $from,
                    'todate' => $to,
                    'status' => $status,
                    'description' => $description,
                    'link' => $link,
                    'page' => $page,
                     'PIN' => current_user()->PIN,
                    'user' =>  $this->session->userdata('user_id')
                );

                if (is_null($id)) {
                    $this->db->insert('report_table_mortuary', $array);
                } else {
                    $this->db->update('report_table_mortuary', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report_mortuary/contribution_report/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }


        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_mortuary($id)->row();
        }
        $this->data['mortuary_status_list'] = $this->mortuary_model->mortuary_status()->result();
        $this->data['content'] = 'report/mortuary/create_contribution_report_title';
        $this->load->view('template', $this->data);
    }

    function contribution_balance_view($link, $id) {
        $this->data['title'] = lang('member_mortuary_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        //if (!is_null($id)) {
        //    $id = decode_id($id);
        //}

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_mortuary_balance($reportinfo->fromdate, $reportinfo->todate, $reportinfo->status);

        $this->data['content'] = 'report/mortuary/contribution_list_balance';
        $this->load->view('template', $this->data);
    }

    function contribution_balance_print($link, $id) {
        $this->data['title'] = lang('member_mortuary_balance');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->account_mortuary_balance($reportinfo->fromdate, $reportinfo->todate);

        $html = $this->load->view('report/mortuary/print/contribution_list_balance_print', $this->data, true);
        $this->export_to_pdf($html, 'Contribution_balance', $reportinfo->page);
    }

    function contribution_statement_view($link, $id) {
        $this->data['title'] = lang('member_mortuary_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

        $this->data['content'] = 'report/mortuary/mortuary_statement';
        $this->load->view('template', $this->data);
    }

    function mortuary_statement_view($link, $id) {
        $this->data['title'] = lang('member_mortuary_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

        $this->data['content'] = 'report/mortuary/mortuary_statement';
        $this->load->view('template', $this->data);
    }
    function mortuary_ledger_view($link, $link_id, $id) {
        $this->data['title'] = lang('member_mortuary_statement');
        $this->data['link_cat'] = $link;
        $this->data['link_id'] = $link_id;
        
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $this->data['id'] = $id;

        $reportinfo = $this->report_model->report_mortuary($link_id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_ledger($reportinfo->fromdate, $reportinfo->todate, $id);

        $this->data['content'] = 'report/mortuary/mortuary_ledger';
        $this->load->view('template', $this->data);
    }
    function contribution_statement_print($link, $id) {
        $this->data['title'] = lang('member_mortuary_statement');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_statement($reportinfo->fromdate, $reportinfo->todate, $reportinfo->description);

        
        $html = $this->load->view('report/mortuary/print/contribution_statement_print', $this->data, true);
        $this->export_to_pdf($html, 'Mortuary_Statement', $reportinfo->page);
    }
    
    
    
    
    
    function contribution_transaction_view($link, $id) {
        $this->data['title'] = lang('member_mortuary_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_transactions($reportinfo->fromdate, $reportinfo->todate);
        
        $this->data['content'] = 'report/mortuary/contribution_transactions';
        $this->load->view('template', $this->data);
        
    }
    
    function contribution_transaction_print($link, $id) {
        $this->data['title'] = lang('member_mortuary_transactions');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_transactions($reportinfo->fromdate, $reportinfo->todate);
        
       
        $html = $this->load->view('report/mortuary/print/contribution_transactions_print', $this->data, true);
        $this->export_to_pdf($html, 'Contribution_Transactions', $reportinfo->page);
        
    }
    
    
    function contribution_transaction_summary_view($link, $id) {
        $this->data['title'] = lang('member_mortuary_transactions_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_transactions_summary($reportinfo->fromdate, $reportinfo->todate);
        
        $this->data['content'] = 'report/mortuary/contribution_transactions_summary';
        $this->load->view('template', $this->data);
    }
    function contribution_transaction_summary_print($link, $id) {
        $this->data['title'] = lang('member_mortuary_transactions_summary');
        $this->data['link_cat'] = $link;
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_mortuary($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->mortuary_transactions_summary($reportinfo->fromdate, $reportinfo->todate);
        
       
        $html = $this->load->view('report/mortuary/print/contribution_transactions_summary_print', $this->data, true);
        $this->export_to_pdf($html, 'mortuary_trans_summary', $reportinfo->page);
    }
            
    
    
    function export_to_pdf($html, $filename, $page_orientation = null) {
        //$html = "Tanzania";
        $this->load->library('pdf1');
        $pdf = $this->pdf1->load($page_orientation);
        $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td style="text-align:center;"><h2 style="padding: 0px; margin: 0px;font-size:18px; text-align:center;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px; text-align:center;"><strong> P.O.Box: ' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
        $pdf->SetHTMLHeader($header);
        $pdf->SetFooter(lang('app_name') . '|{PAGENO}|' . date('d-m-Y H:i:s')); // Add a footer for good measure
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
        $ifposted = $this->report_model->mortuary_edit_entry($id, $trans_date,$description,$paymentmethod, $trans_type, $amount, $comment);
        $status['success'] = 'Y';
        $status['id'] = $id;
        
        echo json_encode($status);
    }

}
