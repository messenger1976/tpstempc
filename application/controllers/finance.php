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
            $description = $this->input->post('accountdescription');
            $tmp1 = explode('_', $tmp);
            $accounttype = $tmp1[0];
            $accounttype_sub = $tmp1[1];
            
            
            $create_account = array(
                'account_type' => $accounttype,
                'sub_account_type' => $accounttype_sub,
                'name' => trim($name),
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
        $this->form_validation->set_rules('account_type', lang('member_group_description'), 'required');
        $this->form_validation->set_rules('accountname', lang('finance_account_name'), 'required');
        $this->form_validation->set_rules('accountdescription', lang('finance_account_description'), '');
        $this->form_validation->set_rules('is_header', lang('finance_account_is_header'), '');


        if ($this->form_validation->run() == TRUE) {

            
             $tmp = $this->input->post('account_type');
            $name = $this->input->post('accountname');
            $description = $this->input->post('accountdescription');
            $tmp1 = explode('_', $tmp);
            $accounttype = $tmp1[0];
            $accounttype_sub = $tmp1[1];
            
            
            $create_account = array(
                'account_type' => $accounttype,
                'sub_account_type' => $accounttype_sub,
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
        $this->data['account_chart'] = $this->finance_model->account_chart()->result();
        $this->data['content'] = 'finance/account_chart_list';
        $this->load->view('template', $this->data);
    }

    function journalentry() {
        $this->data['title'] = lang('journalentry');
        $this->form_validation->set_rules('issue_date', lang('journalentry_description'), 'required|valid_date');
        $this->form_validation->set_rules('description11', lang('description'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $array_items = array();
            $account = $this->input->post('account');
            $description = $this->input->post('description');
            $credit = $this->input->post('credit');
            $debit = $this->input->post('debit');
            $act = count($account);
            $date = format_date(trim($this->input->post('issue_date')));
            $out_description = trim($this->input->post('description11'));
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

                        $array_items[] = $tmp_array;
                    }
                }
                
                $main_array = array(
                    'entrydate' => $date,
                    'description' => $out_description
                );
                
                $insert = $this->finance_model->enter_journal($main_array,$array_items);
                if($insert){
                    $this->session->set_flashdata('message','Journal Recorded');
                    redirect(current_lang().'/finance/journalentry','refresh');
                }
                
            } else {
                $this->data['warning'] = 'Make sure summmation of credit and debit are equal';
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();





        $this->data['content'] = 'finance/journalentry';
        $this->load->view('template', $this->data);
    }

}

?>
