<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of report_member
 *
 * @author miltone
 */
class Report_Member extends CI_Controller {

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

    function member_fields($column = null) {
        $colum_array = array(
            'members.PID' => lang('member_pid'),
            'members.member_id' => lang('member_member_id'),
            'members.firstname' => lang('member_firstname'),
            'members.middlename' => lang('member_middlename'),
            'members.lastname' => lang('member_lastname'),
            'members.gender' => lang('member_gender'),
            'members.maritalstatus' => lang('member_maritalstatus'),
            'members.dob' => lang('member_dob'),
            'members.joiningdate' => lang('member_join_date')
        );
        $colum_array1 = array(
            'members_contact.phone1' => lang('member_contact_phone1'),
            'members_contact.phone2' => lang('member_contact_phone2'),
            'members_contact.email' => lang('member_contact_email'),
            'members_contact.postaladdress' => lang('member_contact_box'),
            'members_contact.physicaladdress' => lang('member_contact_physical'),
        );
        $colum_array2 = array(
            'members_nextkin.name' => lang('nextkin_name'),
            'members_nextkin.relationship' => lang('nextkin_relationship'),
            'members_nextkin.phone' => lang('member_contact_phone1'),
            'members_nextkin.email' => lang('member_contact_email'),
            'members_nextkin.postaladdress' => lang('member_contact_box'),
            'members_nextkin.physicaladdress' => lang('member_contact_physical')
        );



        if (!is_null($column)) {

            //return (array_key_exists($column, $colum_array) ? $colum_array[$column] : '');
        }

        return array($colum_array, $colum_array1, $colum_array2);
    }

    function member_report_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_report_member');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_report_fee');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $this->data['column_list'] = $this->member_fields();
        $this->data['reportlist'] = $this->report_model->report_memberlist(null, $link)->result();
        $this->data['content'] = 'report/member/member_list_title';
        $this->load->view('template', $this->data);
    }

    function delete_report_member_list($link, $id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->db->delete('report_table_member', array('id' => $id));
            redirect(current_lang() . '/report_member/member_report_title/' . $link);
        }
        redirect(current_lang() . '/report_member/member_report_title/' . $link);
    }

    function create_member_list_title($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_report_member');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_report_fee');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        $this->form_validation->set_rules('fromdate', 'Joined From', 'required|valid_date');
        $this->form_validation->set_rules('todate', 'Until', 'required|valid_date');
        $this->form_validation->set_rules('description', 'Description', 'required');
        if ($link == 1) {

            $this->form_validation->set_rules('column[]', 'Column', 'required');
        }
        if ($this->form_validation->run() == TRUE) {
            $from = format_date(trim($this->input->post('fromdate')));
            $to = format_date(trim($this->input->post('todate')));
            $description = trim($this->input->post('description'));
            $page = trim($this->input->post('page'));
            if ($link == 1) {
                $column = implode(',', $this->input->post('column'));
            }
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
                    $array['column'] = $column;
                }
                if (is_null($id)) {
                    $this->db->insert('report_table_member', $array);
                } else {
                    $this->db->update('report_table_member', $array, array('id' => $id));
                }

                redirect(current_lang() . '/report_member/member_report_title/' . $link, 'refresh');
            } else {
                $this->data['warning'] = 'From date is greater than until date';
            }
        }



        if (!is_null($id)) {
            $this->data['reportinfo'] = $this->report_model->report_memberlist($id)->row();
        }
        $this->data['column_list'] = $this->member_fields();
        $this->data['content'] = 'report/member/create_member_list_title';
        $this->load->view('template', $this->data);
    }

    function registration_fee_collection($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_report_member');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_report_fee');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_memberlist($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->registration_fee_collection($reportinfo->fromdate, $reportinfo->todate);

        $this->data['content'] = 'report/member/registration_fee_collection';
        $this->load->view('template', $this->data);
    }

    function member_list_view($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_report_member');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_report_fee');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_memberlist($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $columns = explode(',', $reportinfo->column);
        $this->data['column'] = $columns;
        $tmpcolumn = $this->member_fields();
        $this->data['all_column'] = array_merge($tmpcolumn[0], array_merge($tmpcolumn[1], $tmpcolumn[2]));
        $this->data['transaction'] = $this->report_model->member_list_data($reportinfo->fromdate, $reportinfo->todate, $columns);


        $this->data['content'] = 'report/member/member_list_view';
        $this->load->view('template', $this->data);
    }

    function member_list_print($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_report_member');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_report_fee');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_memberlist($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $columns = explode(',', $reportinfo->column);
        $this->data['column'] = $columns;
        $tmpcolumn = $this->member_fields();
        $this->data['all_column'] = array_merge($tmpcolumn[0], array_merge($tmpcolumn[1], $tmpcolumn[2]));
        $this->data['transaction'] = $this->report_model->member_list_data($reportinfo->fromdate, $reportinfo->todate, $columns);
        $html = $this->load->view('report/member/print/member_list_print', $this->data, true);

        $this->export_to_pdf($html, 'Member_List', $reportinfo->page);
    }

    function registration_fee_collection_print($link, $id = null) {
        if ($link == 1) {
            $this->data['title'] = lang('member_report_member');
        } else if ($link == 2) {
            $this->data['title'] = lang('member_report_fee');
        }

        $this->data['id'] = $id;
        $this->data['link_cat'] = $link;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        $reportinfo = $this->report_model->report_memberlist($id)->row();
        $this->data['reportinfo'] = $reportinfo;
        $this->data['transaction'] = $this->report_model->registration_fee_collection($reportinfo->fromdate, $reportinfo->todate);
        $html = $this->load->view('report/member/print/registration_fee_collection_print', $this->data, true);

        $this->export_to_pdf($html, 'Registration_fee_collection', $reportinfo->page);
    }

    function member_profile() {
        $this->data['title'] = lang('member_profile');
        if (isset($_GET['member'])) {
            $_POST['member_id'] = $_GET['member'];
        }
        $this->form_validation->set_rules('member_id', lang('member_select_member'), 'required');
        $this->data['member_id'] = '';
        $member_id = '';
        if ($this->form_validation->run() == TRUE) {
            $member_id = $this->input->post('member_id');
            $this->data['member_id'] = $member_id;
        }

        if (isset($_GET['member_id'])) {
            $member_id = $_GET['member_id'];
            $this->data['member_id'] = $member_id;
        }

        if ($member_id != '') {

            $this->data['memberinfo'] = $this->member_model->member_basic_info(null, null, $member_id)->row();
            $this->data['contactinfo'] = $this->member_model->member_contact($this->data['memberinfo']->PID);
            $this->data['nextkininfo'] = $this->member_model->member_nextkin($this->data['memberinfo']->PID);
        }


        $this->data['memberlist'] = $this->member_model->member_basic_info()->result();
        $this->data['content'] = 'report/member/member_profile';
        $this->load->view('template', $this->data);
    }

    function member_profile_print() {
        $member_id = '';


        if (isset($_GET['member_id'])) {
            $member_id = $_GET['member_id'];
            $this->data['member_id'] = $member_id;
        }

        if ($member_id != '') {

            $this->data['memberinfo'] = $this->member_model->member_basic_info(null, null, $member_id)->row();
            $this->data['contactinfo'] = $this->member_model->member_contact($this->data['memberinfo']->PID);
            $this->data['nextkininfo'] = $this->member_model->member_nextkin($this->data['memberinfo']->PID);
            $html = $this->load->view('report/member/print/member_profile_content', $this->data, true);

            $this->export_to_pdf($html, 'Member_profile');
        }
        redirect(current_lang() . '/report_member/member_profile', 'refresh');
    }

    function export_to_pdf($html, $filename, $page_orientation = null) {
        //$html = "Tanzania";
        //echo $var; exit;
        $this->load->library('pdf1');
        $pdf = $this->pdf1->load($page_orientation);
        $header = '<div style="border-bottom:1px solid #000; text-align:center;">
                <table style="display:inline-block;"><tr><td valign="top"><img style="height:50px; display:inline-block;" src="' . base_url() . 'logo/' . company_info()->logo . '"/></td>
                    <td style="text-align:center;"><h2 style="padding: 0px; margin: 0px; font-size:18px; text-align:center;"><strong>' . company_info()->name . '</strong></h2>
                        <h5 style="padding: 0px; margin: 0px; font-size:15px;text-align:center;"><strong> P.O.Box' . strtoupper(company_info()->box) . ' , ' . strtoupper(lang('clientaccount_label_phone')) . ':' . company_info()->mobile . '</strong></h5></td></tr></table> 
                </div>';
        $pdf->SetHTMLHeader($header, 'E', true);
        $pdf->SetHTMLHeader($header, 'O', true);
        $pdf->SetFooter('SACCO PLUS' . '|{PAGENO}|' . date('d-m-Y H:i:s')); // Add a footer for good measure <img src="https://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF   
        $pdf->Output($filename, 'I'); // save to file because we can
        exit;
    }

}
