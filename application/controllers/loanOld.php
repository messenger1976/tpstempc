<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of loan
 *
 * @author miltone
 */
class Loan extends CI_Controller {

    //put your code here
    public $loan_allowed = 0;

    //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_loan');
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
    }

    /*
     * Function to uploads files to server
     * @author Miltone Urassa
     * @Contact miltoneurassa@yahoo.com
     */

    function upload_file($array, $name, $folder) {
        $filename = time() . $array[$name]['name'];

        $path = './' . $folder . '/';
        $path1 = './' . $folder . '/';
        $path = $path . basename($filename);

        if (move_uploaded_file($_FILES[$name]['tmp_name'], $path)) {
            // chmod($path1.$filename, 777);
            return $filename;
        } else {
            return 0;
        }
    }

    /*
     *  @author Miltone Urassa
     *  @Contact : miltoneurassa@yahoo.com
     *  function Name :  getExtension
     *  Description : File extension
     *  @parm filename
     *  @return file extension in lower case
     * 
     */

    function getExtension($str) {
        $i = strrpos($str, ".");
        if (!$i) {
            return "";
        }
        $l = strlen($str) - $i;
        $ext = substr($str, $i + 1, $l);
        return strtolower($ext);
    }
    
    
    
    function automatic_repayment_process(){
        
        $this->data['title'] = 'Automatic Loan Repayment';
        $this->form_validation->set_rules('date_month', 'Months', 'required|valid_month');

        if ($this->form_validation->run() == TRUE) {
            $month = trim($this->input->post('date_month'));
            $exp = explode('-', $month);
            $selected = $exp[1] . $exp[0];
            $current = date('Ym');
            if ($selected <= $current) {
                
                
                
                
            }else {
                $this->data['warning'] = 'Invalid month';
            }
        }

        $this->data['content'] = 'loan/loan_autmatic';
        $this->load->view('template', $this->data);
        
    }
            
    

    function loan_application() {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_create_new');

        if ($this->input->post('amount')) {
            $_POST['amount'] = str_replace(',', '', $_POST['amount']);
            $_POST['income'] = str_replace(',', '', $_POST['income']);
            $_POST['procesingfee'] = str_replace(',', '', $_POST['procesingfee']);
        }
        $this->form_validation->set_rules('applicationdate', lang('loan_applicationdate'), 'required|valid_dae');
        $this->form_validation->set_rules('product', lang('loan_product'), 'required');
        $this->form_validation->set_rules('amount', lang('loan_applied_amount'), 'required|numeric');
        $this->form_validation->set_rules('installment', lang('loan_installment'), 'required|numeric');
        $this->form_validation->set_rules('source', lang('loan_paysource'), 'required');
        $this->form_validation->set_rules('purpose', lang('loan_purpose'), 'required');
        $this->form_validation->set_rules('pid', lang('member_pid'), 'required');
        $this->form_validation->set_rules('member_id', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('income', 'Monthly Income', 'required|numeric');
        $this->form_validation->set_rules('procesingfee', 'Loan Processing Fee', 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $PID_initial = explode('-', trim($this->input->post('pid')));
            $member_id_initial = explode('-', trim($this->input->post('member_id')));
            $pid = $PID = $PID_initial[0];
            $member_id = $member_id_initial[0];

            $product_id = $this->input->post('product');
            $product = $this->setting_model->loanproduct($product_id)->row();

            $date = format_date(trim($this->input->post('applicationdate')));
            $amount = $this->input->post('amount');
            $installment = trim($this->input->post('installment'));
            $source = trim($this->input->post('source'));
            $purpose = trim($this->input->post('purpose'));
            $processingfee = $this->input->post('procesingfee');

            $createloan = array(
                'PID' => $pid,
                'member_id' => $member_id,
                'product_type' => $product_id,
                'rate' => $product->interest_rate,
                'interval' => $product->interval,
                'basic_amount' => $amount,
                'number_istallment' => $installment,
                'pay_source' => $source,
                'applicationdate' => $date,
                'loan_purpose' => $purpose,
                'monthly_income' => trim($this->input->post('income')),
                'createdby' => current_user()->id,
                'PIN' => $pin,
            );



            if ($product->maxmum_time >= $installment) {
                //start validating

                $share_info = $this->share_model->share_member_info($pid, $member_id);

                $saving_account = $this->finance_model->saving_account_balance_PID($pid, $member_id);
                $contribution = $this->contribution_model->contribution_balance($pid, $member_id);

                $installment_amount = $this->loanbase->get_installment($product->interest_rate, $amount, $installment, $product->interest_method, $product->interval);

                if ($this->maximum_loan_allowed($product, $amount, $contribution, $pid) == TRUE  && $this->maximum_contributions_times($product, $amount, $contribution) == TRUE && $this->pass_share_condition($product, $share_info) == TRUE && $this->pass_contribution_condition($product, $contribution) == TRUE && $this->pass_saving_condition($product, $saving_account) == TRUE) {

                    $installment_amount = $this->loanbase->get_installment($product->interest_rate, $amount, $installment, $product->interest_method, $product->interval);

                    $total_interest_amount = $this->loanbase->totalInterest($product->interest_rate, $amount, $installment, $installment_amount, $product->interest_method, $product->interval);
                    $createloan['installment_amount'] = $installment_amount;
                    $createloan['total_interest_amount'] = $total_interest_amount;
                    $createloan['total_loan'] = ($total_interest_amount + $amount);
                    $insert = $this->loan_model->add_newloan($createloan,$processingfee);
                    if ($insert) {
                        //notify user
                        $recipient = array();
                        $expl = explode(',', NEW_LOAN);
                        foreach ($expl as $key => $value) {
                            $std = new stdClass();
                            $std->mobile = $value;
                            $recipient[] = $std;
                        }

                        $member_name = $this->member_model->member_name(null, $pid);
                        $message = 'Notification, New loan ' . $insert . ' created for ' . $member_name;
                        if (count($recipient) > 0) {
                            //send SMS=====================================================
                            //$this->smssending->send_sms(SENDER, $message, $recipient);
                        }

                        $this->session->set_flashdata('message', lang('loan_saved_success'));
                        redirect(current_lang() . '/loan/loan_editing/' . encode_id($insert), 'refresh');
                    } else {
                        $this->data['warning'] = lang('loan_add_fail');
                    }
                } else {
                    if (!$this->pass_contribution_condition($product, $contribution)) {
                        $this->data['warning'] = lang('loan_contribution_insufficient');
                    }
                    if (!$this->pass_saving_condition($product, $saving_account)) {
                        $this->data['warning'] = lang('loan_saving_insufficient');
                    }else if (!$this->pass_share_condition($product, $share_info)) {
                        $this->data['warning'] = lang('loan_share_insufficient');
                    }else if (!$this->maximum_contributions_times($product, $amount, $contribution)) {
                        $this->data['warning'] = lang('loan_contribution_times_exceed');
                    }
                    /*else if (!$this->pass_monthly_income($createloan['monthly_income'], $pid, $installment_amount)) {
                        $this->data['warning'] = lang('loan_contribution_exceed_one_third');
                    }*/
                    else if (!$this->maximum_loan_allowed($product, $amount, $contribution, $pid)) {
                        $this->data['warning'] = lang('loan_not_allowed', number_format($this->loan_allowed, 2));
                    }
                }
            } else {
                $this->data['warning'] = lang('loan_maximum_duration');
            }
        }


        $this->data['paysource_list'] = $this->contribution_model->contribution_source()->result();
        $this->data['loan_product_list'] = $this->setting_model->loanproduct()->result();
        $this->data['content'] = 'loan/loan_application_step1';
        $this->load->view('template', $this->data);
    }

    function pass_monthly_income($monthy_income, $pid, $newinstall = 0) {
        // 
         $pin = current_user()->PIN;
        $monthly_contribution = 0;
        $contr_setup = $this->db->get_where('contribution_settings', array('PID' => $pid,'PIN'=>$pin))->row();
        if (count($contr_setup) > 0) {
            $monthly_contribution = $contr_setup->amount;
        } else {
            $this->db->where('PIN',$pin);
            $contr_setup = $this->db->get('contribution_global')->row();
            $monthly_contribution = $contr_setup->amount;
        }
        //check open_loan
        $open_loan = $this->db->query("SELECT * FROM loan_contract WHERE PID='$pid' AND disburse=1 AND status=4")->result();
        $repay_installment = 0;
        foreach ($open_loan as $key => $value) {

            $repay_installment += $value->installment_amount;
        }

        $total_contr_from_salary = $repay_installment + $monthly_contribution + $newinstall;
        $remain_theluth = ((1 / 3) * $monthy_income);
        //$remain = $monthy_income - $total_contr_from_salary;
        if ($total_contr_from_salary <= $remain_theluth) {
            return TRUE;
        }
        return FALSE;
    }

    function pass_share_condition($product, $share) {

        if ($product->loan_security_share_min > 0) {
            if ($share) {
                if ($share->totalshare >= $product->loan_security_share_min) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        return TRUE;
    }

    function pass_saving_condition($product, $saving) {

        if ($product->loan_security_saving_minimum > 0) {
            if ($saving) {
                if ($saving->balance >= $product->loan_security_saving_minimum) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        return TRUE;
    }

    function maximum_loan_allowed($product, $loan_amount, $contribution, $pid) {
        $total_amount = $contribution->balance * $product->loan_security_contribution_times;

        $open_loan = $this->db->query("SELECT * FROM loan_contract WHERE PID='$pid' AND approval=4")->result();

        $principles = 0;
        $amount_paid = 0;
        foreach ($open_loan as $key => $value) {
            $amount_paid += $this->db->query("SELECT SUM('amount') as amount FROM loan_repayment_receipt WHERE LID='$value->LID'")->row()->amount;
            $principles += $value->basic_amount;
        }
        $open_principle = $principles - $amount_paid;
        $allowed = $total_amount - $open_principle;
        $this->loan_allowed = $allowed;
        if ($allowed >= $loan_amount) {
            return TRUE;
        }
        return FALSE;
    }

    function maximum_contributions_times($product, $loan_amount, $contribution) {
        $total_amount = $contribution->balance * $product->loan_security_contribution_times;
        if ($total_amount == 0) {
            return TRUE;
        } else {
            if (count($contribution) > 0) {
                if ($loan_amount <= $total_amount) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }
        return TRUE;
    }

    function pass_contribution_condition($product, $contribution) {

        if ($product->loan_security_contribution_min > 0) {
            if ($contribution) {
                if ($contribution->balance >= $product->loan_security_contribution_min) {

                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        return TRUE;
    }

    function loan_editing($loanid) {
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);

        $info = $this->loan_model->loan_info($LID)->row();

        if ($this->input->post('amount')) {
            $_POST['amount'] = str_replace(',', '', $_POST['amount']);

            $_POST['income'] = str_replace(',', '', $_POST['income']);
        }
        $this->form_validation->set_rules('applicationdate', lang('loan_applicationdate'), 'required|valid_dae');
        $this->form_validation->set_rules('product', lang('loan_product'), 'required');
        $this->form_validation->set_rules('amount', lang('loan_applied_amount'), 'required|numeric');
        $this->form_validation->set_rules('installment', lang('loan_installment'), 'required|numeric');
        $this->form_validation->set_rules('source', lang('loan_paysource'), 'required');
        $this->form_validation->set_rules('purpose', lang('loan_purpose'), 'required');
        $this->form_validation->set_rules('income', 'Monthly Income', 'required|numeric');

        if ($this->form_validation->run() == TRUE) {


            $pid = $PID = $info->PID;
            $member_id = $info->member_id;

            $product_id = $this->input->post('product');
            $product = $this->setting_model->loanproduct($product_id)->row();

            $date = format_date(trim($this->input->post('applicationdate')));
            $amount = $this->input->post('amount');
            $installment = trim($this->input->post('installment'));
            $source = trim($this->input->post('source'));
            $purpose = trim($this->input->post('purpose'));
            $pin = current_user()->PIN;
            $createloan = array(
                'product_type' => $product_id,
                'rate' => $product->interest_rate,
                'interval' => $product->interval,
                'basic_amount' => $amount,
                'number_istallment' => $installment,
                'pay_source' => $source,
                'applicationdate' => $date,
                'monthly_income' => trim($this->input->post('income')),
                'loan_purpose' => $purpose,
                'PIN' => $pin,
            );



            if ($product->maxmum_time >= $installment) {
                //start validating

                $share_info = $this->share_model->share_member_info($pid, $member_id);

                $saving_account = $this->finance_model->saving_account_balance_PID($pid, $member_id);
                $contribution = $this->contribution_model->contribution_balance($pid, $member_id);

                $installment_amount = $this->loanbase->get_installment($product->interest_rate, $amount, $installment, $product->interest_method, $product->interval);

                if ($this->maximum_loan_allowed($product, $amount, $contribution, $pid) == TRUE && $this->pass_monthly_income($createloan['monthly_income'], $pid, $installment_amount) == TRUE && $this->maximum_contributions_times($product, $amount, $contribution) == TRUE && $this->pass_share_condition($product, $share_info) == TRUE && $this->pass_contribution_condition($product, $contribution) == TRUE && $this->pass_saving_condition($product, $saving_account) == TRUE) {

                    $total_interest_amount = $this->loanbase->totalInterest($product->interest_rate, $amount, $installment, $installment_amount, $product->interest_method, $product->interval);
                    $createloan['installment_amount'] = $installment_amount;
                    $createloan['total_interest_amount	'] = $total_interest_amount;
                    $createloan['total_loan	'] = ($total_interest_amount + $amount);


                    $insert = $this->loan_model->edit_loan_info($createloan, $LID);
                    if ($insert) {
                        $this->session->set_flashdata('message', lang('loan_saved_success'));
                        redirect(current_lang() . '/loan/loan_editing/' . encode_id($insert), 'refresh');
                    } else {
                        $this->data['warning'] = lang('loan_add_fail');
                    }
                } else {
                    if (!$this->pass_contribution_condition($product, $contribution)) {
                        $this->data['warning'] = lang('loan_contribution_insufficient');
                    }else if (!$this->pass_saving_condition($product, $saving_account)) {
                        $this->data['warning'] = lang('loan_saving_insufficient');
                    }else if (!$this->pass_share_condition($product, $share_info)) {
                        $this->data['warning'] = lang('loan_share_insufficient');
                    }else if (!$this->maximum_contributions_times($product, $amount, $contribution)) {
                        $this->data['warning'] = lang('loan_contribution_times_exceed');
                    }else if (!$this->pass_monthly_income($createloan['monthly_income'], $pid, $installment_amount)) {
                        $this->data['warning'] = lang('loan_contribution_exceed_one_third');
                    }else if (!$this->maximum_loan_allowed($product, $amount, $contribution, $pid)) {
                        $this->data['warning'] = lang('loan_not_allowed', number_format($this->loan_allowed, 2));
                    }
                }
            } else {
                $this->data['warning'] = lang('loan_maximum_duration');
            }
        }


        $this->data['basicinfo'] = $this->member_model->member_basic_info(null, $info->PID, $info->member_id)->row();
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['paysource_list'] = $this->contribution_model->contribution_source()->result();
        $this->data['loan_product_list'] = $this->setting_model->loanproduct()->result();
        $this->data['content'] = 'loan/loan_editing';
        $this->load->view('template', $this->data);
    }

    function loan_security($loanid) {
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);

        $info = $this->loan_model->loan_info($LID)->row();
        $this->form_validation->set_rules('declaration', lang('loan_security_declaration'), 'required');
        $this->form_validation->set_rules('comment', lang('loan_supporting_document_comment'), '');
        $upload_photo = TRUE;
        $file_name = 0;
        if ($this->input->post('comment')) {

            if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
                $extension = $this->getExtension($_FILES['file']['name']);
                $file_name = $this->upload_file($_FILES, 'file', 'uploads/document');
                $upload_photo = TRUE;
            } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
                $this->data['logo_error'] = 'The ' . lang('loan_supporting_document_attach') . ' field is required';
                $upload_photo = FALSE;
            }
        }
$pin = current_user()->PIN;
        if ($this->form_validation->run() == TRUE && $upload_photo == true) {
            $declaration = array(
                'declaration' => trim($this->input->post('declaration')),
                'LID' => $LID,
                'PIN' => $pin
            );
            $this->loan_model->loan_declaration($declaration);
            if ($this->input->post('comment')) {
                if ($file_name != 0) {
                    $doc = array(
                        'comment' => trim($this->input->post('comment')),
                        'file' => $file_name,
                        'LID' => $LID,
                        'PIN' => $pin
                    );
                    $this->loan_model->loan_supporting_doc($doc);
                }
            }



            $this->data['message'] = lang('loan_info_saved');
        }

        $this->data['basicinfo'] = $this->member_model->member_basic_info(null, $info->PID, $info->member_id)->row();
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['declaration'] = $this->loan_model->get_declaration($LID);
        $this->data['supporting_doc'] = $this->loan_model->get_supporting_doc($LID);

        $this->data['content'] = 'loan/loan_security';
        $this->load->view('template', $this->data);
    }

    function deletedoc($loanid, $id) {
        $this->db->delete('loan_contract_supportdoc', array('id' => $id));
        redirect(current_lang() . '/loan/loan_security/' . $loanid, 'refresh');
    }

    function remove_guarantor($loanid, $id) {
        $this->db->delete('loan_contract_guarantor', array('id' => $id));
        redirect(current_lang() . '/loan/loan_guarantor/' . $loanid, 'refresh');
    }

    function loan_guarantor($loanid, $edit = null) {
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);

        $info = $this->loan_model->loan_info($LID)->row();
        $this->form_validation->set_rules('customerid', lang('loan_quarantor_name'), 'required');
        if (!$this->ion_auth->in_group('Members')) {
            $this->form_validation->set_rules('relationship', lang('loan_quarantor_relationship'), 'required');
            $this->form_validation->set_rules('asset', lang('loan_quarantor_asset'), 'required');
        }
        $upload_photo = TRUE;
        $file_name = 0;

        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            $file_name = $this->upload_file($_FILES, 'file', 'uploads/document');
            $upload_photo = TRUE;
        }

$pin = current_user()->PIN;
        if ($this->form_validation->run() == TRUE && $upload_photo == true) {
            $guarantor = array(
                'LID' => $LID,
                'PID' => trim($this->input->post('customerid')),
                'relationship' => trim($this->input->post('relationship')),
                'declaration' => trim($this->input->post('asset')),
                'PIN' => $pin
            );
            if ($file_name != 0) {
                $guarantor['file'] = $file_name;
            }
            if ($this->ion_auth->in_group('Members')) {
                $guarantor['request'] = 1;
                //================================SMS NOTIFICATION=====================================
                //notify guarantor requested
                $contact = $this->member_model->member_contact($guarantor['PID']);
                if ($contact->phone1 <> '') {
                    $loaninfo = $this->loan_model->loan_info($guarantor['LID'])->row();
                    $memberinfo = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
                    $message = str_replace('LOAN_NUMBER', $guarantor['LID'], REQUEST_GUARANTOR);
                    $message1 = str_replace('MEMBER_NAME', $memberinfo->firstname . ' ' . $memberinfo->lastname, $message);
                   // $this->smssending->send_sms_single(SENDER, $message1, $contact->phone1);
                }

                //=====================================================================================
            }

            $this->loan_model->add_guarantor($guarantor, $edit);
            $this->session->set_flashdata('message', lang('loan_info_saved'));
            redirect(current_lang() . '/loan/loan_guarantor/' . $loanid . '/' . $edit, 'refresh');
        }

        $this->data['basicinfo'] = $this->member_model->member_basic_info(null, $info->PID, $info->member_id)->row();
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['guarantor_list'] = $this->loan_model->get_guarantor(null, $LID)->result();
        $this->data['member_list'] = $this->member_model->member_basic_info()->result();

        $this->data['content'] = 'loan/loan_guarantor';
        $this->load->view('template', $this->data);
    }

    function loan_evaluation() {
        $this->data['title'] = lang('loan_evaluation_list');
        $this->data['loan_wait'] = $this->loan_model->loan_wait_evaluation();
        $this->data['content'] = 'loan/loan_evaluationlist';
        $this->load->view('template', $this->data);
    }

    function loan_guarantor_request() {
        $this->data['title'] = 'Loan Guarantor Request';
        $member = $this->member_model->member_basic_info(null, null, current_user()->member_id)->row();
        $this->data['request'] = $this->db->query("SELECT * FROM loan_contract_guarantor WHERE PID='$member->PID' AND request_status=0")->result();
        $this->data['content'] = 'loan/loan_guarantor_request';
        $this->load->view('template', $this->data);
    }

    function loan_guarantor_respond($id) {
        if (isset($_GET['s'])) {
            if ($_GET['s'] == 'reject') {
                $id = decode_id($id);
                $this->db->update('loan_contract_guarantor', array('request_status' => 2), array('id' => $id));
                $g = $this->db->get_where('loan_contract_guarantor', array('id' => $id))->row();

                $memberinfo = $this->member_model->member_basic_info(null, $g->PID)->row();

                //==================================REJECT NOTFICATION=================================================
                $loaninfo = $this->loan_model->loan_info($g->LID)->row();
                $contact = $this->member_model->member_contact($loaninfo->PID);

                if ($contact->phone1 <> '') {

                    $message = str_replace('LOAN_NUMBER', $g->LID, REQUEST_GUARANTOR_RESPOND);
                    $message1 = str_replace('MEMBER_NAME', $memberinfo->firstname . ' ' . $memberinfo->lastname, $message);
                    $message2 = str_replace('ACTION', 'reject', $message1);
                   // $this->smssending->send_sms_single(SENDER, $message2, $contact->phone1);
                }
                //========================================================================================
                redirect(current_lang() . '/loan/loan_guarantor_request', 'refresh');
            } else if ($_GET['s'] == 'accept') {
                $this->data['id'] = $id;
                $this->data['title'] = 'Loan guarantor responding';
                $id = decode_id($id);

                $this->form_validation->set_rules('relationship', lang('loan_quarantor_relationship'), 'required');
                $this->form_validation->set_rules('asset', lang('loan_quarantor_asset'), 'required');

                $upload_photo = TRUE;
                $file_name = 0;

                if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
                    $extension = $this->getExtension($_FILES['file']['name']);
                    $file_name = $this->upload_file($_FILES, 'file', 'uploads/document');
                    $upload_photo = TRUE;
                }

$pin = current_user()->PIN;
                if ($this->form_validation->run() == TRUE && $upload_photo == true) {
                    $guarantor = array(
                        'relationship' => trim($this->input->post('relationship')),
                        'declaration' => trim($this->input->post('asset')),
                        'PIN' => $pin
                    );
                    if ($file_name != 0) {
                        $guarantor['file'] = $file_name;
                    }
                    $guarantor['request_status'] = 1;

                    $g = $this->db->get_where('loan_contract_guarantor', array('id' => $id))->row();
                    $memberinfo = $this->member_model->member_basic_info(null, $g->PID)->row();

                    //==================================REJECT NOTFICATION=================================================
                    $loaninfo = $this->loan_model->loan_info($g->LID)->row();
                    $contact = $this->member_model->member_contact($loaninfo->PID);

                    if ($contact->phone1 <> '') {

                        $message = str_replace('LOAN_NUMBER', $g->LID, REQUEST_GUARANTOR_RESPOND);
                        $message1 = str_replace('MEMBER_NAME', $memberinfo->firstname . ' ' . $memberinfo->lastname, $message);
                        $message2 = str_replace('ACTION', 'accept', $message1);
                        //$this->smssending->send_sms_single(SENDER, $message2, $contact->phone1);
                    }


                    $this->loan_model->add_guarantor($guarantor, $id);
                    $this->session->set_flashdata('message', lang('loan_info_saved'));
                    redirect(current_lang() . '/loan/loan_guarantor_request/', 'refresh');
                }

                $g = $this->db->get_where('loan_contract_guarantor', array('id' => $id))->row();
                $loaninfo = $this->loan_model->loan_info($g->LID)->row();
                $this->data['loaninfo'] = $loaninfo;
                $this->data['basicinfo'] = $this->member_model->member_basic_info(null, $loaninfo->PID)->row();
                $this->data['content'] = 'loan/guarantor_respond_accept';
                $this->load->view('template', $this->data);
            }
        } else {
            redirect(base_url(), 'refresh');
        }
    }

    function member_loan_list() {
        $pin = current_user()->PIN;
        $this->data['title'] = 'Loan List';
        $member_id = current_user()->member_id;
        $sql = "SELECT loan_contract.*,loan_status.name FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
        $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status WHERE loan_contract.member_id='$member_id' AND loan_contract.PIN='$pin'";

        $sql.= " ORDER BY loan_contract.applicationdate ASC";

        $this->data['loan_list'] = $this->db->query($sql)->result();
        $this->data['content'] = 'loan/member_loan_list';
        $this->load->view('template', $this->data);
    }

    function loan_evaluation_action($loanid) {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_evaluation_inaction');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->form_validation->set_rules('status', lang('loan_status'), 'required');
        $this->form_validation->set_rules('comment', lang('loan_comment'), 'required');
        if ($this->form_validation->run() == TRUE) {
            $array_data = array(
                'LID' => $LID,
                'status' => $this->input->post('status'),
                'comment' => $this->input->post('comment'),
                'createdby' => current_user()->id,
                'PIN' => $pin,
                
            );

            $create = $this->db->insert('loan_contract_evaluation', $array_data);
            if ($create) {
                //load data
                $evst = $this->input->post('status');
                $up = array('evaluated' => $evst, 'status' => $evst);
                if ($evst == 1) {
                    $up['edit'] = 1;
                }


                //notify user
                $recipient = array();
                if ($evst == 1) {
                    $expl = explode(',', APROVE_LOAN);
                    foreach ($expl as $key => $value) {
                        $std = new stdClass();
                        $std->mobile = $value;
                        $recipient[] = $std;
                    }
                }
                $loaninfo = $this->loan_model->loan_info($LID)->row();
                $member_contact = $this->member_model->member_contact($loaninfo->PID);
                if ($member_contact->phone1 <> '') {
                    $std = new stdClass();
                    $std->mobile = $member_contact->phone1;
                    $recipient[] = $std;
                }
                $member_name = $this->member_model->member_name(null, $loaninfo->PID);
                $message = 'Notification, Loan ' . $loaninfo->PID . '=>' . $member_name . ' Evaluated, STATUS:' . str_replace('&', ' and', $this->db->get_where('loan_status', array('code' => $evst))->row()->name);
                if (count($recipient) > 0) {
                    //$this->smssending->send_sms(SENDER, $message, $recipient);
                }



                $this->db->update('loan_contract', $up, array('LID' => $LID));
                $this->session->set_flashdata('message', lang('loan_info_saved'));
                redirect(current_lang() . '/loan/loan_evaluation_action/' . $loanid, 'refresh');
            }
        }
        if (validation_errors()) {
            $this->data['warning'] = lang('loan_evaluation_error');
        }
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['content'] = 'loan/evaluation_acction';
        $this->load->view('template', $this->data);
    }

    function loan_approval() {
        $this->data['title'] = lang('loan_evaluation_list');
        $this->data['loan_wait'] = $this->loan_model->loan_wait_approval();
        $this->data['content'] = 'loan/loan_wait_toapprove';
        $this->load->view('template', $this->data);
    }

    function loan_approval_action($loanid) {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_approval_inaction');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->form_validation->set_rules('status', lang('loan_status'), 'required');
        $this->form_validation->set_rules('comment', lang('loan_comment'), 'required');
        if ($this->form_validation->run() == TRUE) {
            $array_data = array(
                'LID' => $LID,
                'status' => $this->input->post('status'),
                'comment' => $this->input->post('comment'),
                'createdby' => current_user()->id,
                'PIN' => $pin,
            );

            $create = $this->db->insert('loan_contract_approve', $array_data);
            if ($create) {
                //load data
                $evst = $this->input->post('status');
                $up = array('approval' => $evst, 'status' => $evst);

                $recipient = array();
                if ($evst == 4) {
                    $expl = explode(',', DISBURSE_LOAN);
                    foreach ($expl as $key => $value) {
                        $std = new stdClass();
                        $std->mobile = $value;
                        $recipient[] = $std;
                    }
                }
                
                $loaninfo = $this->loan_model->loan_info($LID)->row();
                $member_contact = $this->member_model->member_contact($loaninfo->PID);
                if ($member_contact->phone1 <> '') {
                    $std = new stdClass();
                    $std->mobile = $member_contact->phone1;
                    $recipient[] = $std;
                }
                $member_name = $this->member_model->member_name(null, $loaninfo->PID);
                $message = 'Notification, Loan ' . $loaninfo->PID . '=>' . $member_name . ' Approved, STATUS:' . str_replace('&', 'and', $this->db->get_where('loan_status', array('code' => $evst))->row()->name);
                if (count($recipient) > 0) {
                   // $this->smssending->send_sms(SENDER, $message, $recipient);
                }

                $this->db->update('loan_contract', $up, array('LID' => $LID));
                $this->session->set_flashdata('message', lang('loan_info_saved'));
                redirect(current_lang() . '/loan/loan_approval_action/' . $loanid, 'refresh');
            }
        }
        if (validation_errors()) {
            $this->data['warning'] = lang('loan_evaluation_error');
        }
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['content'] = 'loan/loan_approval_action';
        $this->load->view('template', $this->data);
    }

    function loan_disbursement() {
        $this->data['title'] = lang('loan_disburseme_list');
        $this->data['loan_wait'] = $this->loan_model->loan_wait_disburse();
        $this->data['content'] = 'loan/loan_wait_disburse';
        $this->load->view('template', $this->data);
    }

    function loan_disburse_action($loanid) {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_disburse_inaction');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->form_validation->set_rules('disbursedate', lang('loan_startrepay_date'), 'required|valid_date');
        $this->form_validation->set_rules('comment', lang('loan_comment'), 'required');
        if ($this->form_validation->run() == TRUE) {
            $array_data = array(
                'LID' => $LID,
                'disbursedate' => format_date(trim($this->input->post('disbursedate'))),
                'comment' => $this->input->post('comment'),
                'createdby' => current_user()->id,
                'PIN' => $pin,
            );

            $this->db->trans_start();
            $create = $this->db->insert('loan_contract_disburse', $array_data);
            if ($create) {
                //load data

                $up = array('disburse' => 1);
                $this->db->update('loan_contract', $up, array('LID' => $LID));

                // insert repay schedule
                $infodata = $this->loan_model->loan_info($LID)->row();

                //loan disbursed now credit Bank account and debit Loan account
                $product = $this->setting_model->loanproduct($infodata->product_type)->row();


                //bank account
                $credit_account = 1010001;
                //ledger entry ID
                $ledger_entry = array('date' => $array_data['disbursedate'],'PIN'=>$pin);
                $this->db->insert('general_ledger_entry', $ledger_entry);
                $ledger_entry_id = $this->db->insert_id();

                //ledger data
                $ledger = array(
                    'journalID' => 4,
                    'entryid' => $ledger_entry_id,
                    'LID' => $LID,
                    'date' => $array_data['disbursedate'],
                    'description' => 'Loan Disbursed',
                    'linkto' => 'loan_contract.LID',
                    'fromtable' => 'loan_contract',
                    'paid' => 0,
                    'PID' => $infodata->PID,
                    'member_id' => $infodata->member_id,
                    'PIN' => $pin,
                );

                $ledger['account'] = $credit_account;
                $ledger['credit'] = $infodata->basic_amount;
                $accountinfo = account_row_info($ledger['account']);
                $ledger['account_type'] = $accountinfo->account_type;
                $ledger['sub_account_type'] = $accountinfo->sub_account_type;
                $this->db->insert('general_ledger', $ledger);

                $ledger['credit'] = 0;
                $ledger['debit'] = 0;

                //debit account
                $debit_account = $product->loan_principle_account;
                $ledger['debit'] = $infodata->basic_amount;
                $ledger['account'] = $debit_account;
               // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                 $accountinfo = account_row_info($ledger['account']);
                $ledger['account_type'] = $accountinfo->account_type;
                $ledger['sub_account_type'] = $accountinfo->sub_account_type;
                $this->db->insert('general_ledger', $ledger);


                $schedule = $this->loanbase->create_repayment_schedule($infodata->installment_amount, $infodata->rate, $infodata->number_istallment, $array_data['disbursedate'], $infodata->basic_amount, $LID, $product->interest_method, $product->interval);

                // foreach ($schedule as $key => $value) {
                //   $value['LID'] = $LID;
                $this->db->insert_batch('loan_contract_repayment_schedule', $schedule);
                // }

                $this->db->trans_complete();

                $this->session->set_flashdata('message', lang('loan_info_saved'));
                redirect(current_lang() . '/loan/view_repayment_schedule/' . $loanid, 'refresh');
            }
            $this->db->trans_complete();
        }
        if (validation_errors()) {
            $this->data['warning'] = lang('loan_evaluation_error');
        }
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['content'] = 'loan/loan_disburse_action';
        $this->load->view('template', $this->data);
    }

    function loan_viewlist() {
        $this->load->library('pagination');
        $this->data['title'] = lang('member_list');

        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }


        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }


        $config["base_url"] = site_url(current_lang() . '/loan/loan_viewlist/');
        $config["total_rows"] = $this->loan_model->count_loan($key);
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

        $this->data['loan_list'] = $this->loan_model->search_loan($key, $config["per_page"], $page);



        $this->data['content'] = 'loan/viewloanlist';
        $this->load->view('template', $this->data);
    }

    function view_indetail($loanid) {
        $this->data['title'] = lang('loan_viewdetails');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);


        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['content'] = 'loan/loan_view_details';
        $this->load->view('template', $this->data);
    }

    function loan_repayment() {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_repayment');
        $this->data['loanlist'] = $this->loan_model->loan_repay_list();
        if ($this->input->post('amount')) {
            $_POST['amount'] = str_replace(',', '', $_POST['amount']);
        }
        $this->form_validation->set_rules('amount', lang('loan_repay_amount'), 'required|numeric');
        $this->form_validation->set_rules('loanid', lang('loan_LID'), 'required');
        $this->form_validation->set_rules('repaydate', lang('loan_repay_date'), 'required|valid_date');


        if ($this->form_validation->run() == TRUE) {
            $amount = trim($this->input->post('amount'));
            $repaid_amount = $amount;
            $LID = trim($this->input->post('loanid'));
            $paydate = format_date(trim($this->input->post('repaydate')));


            $loaninfo = $this->loan_model->loan_info($LID)->row();
            $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();

            $open_repayment = $this->loan_model->open_repayment_installment($LID);
            $previous_remain_balance = $this->loan_model->get_previous_remain_balance($LID);

            //current money in hand
             $amount_tmp = ($amount + $previous_remain_balance);

            $error_array = array();
            $success_array = array();
            if ($amount > 0) {
                if ($loaninfo->status == 4) {
                    $this->db->trans_start();
                    if (count($open_repayment) > 0) {
                        $receipt = $this->loan_model->loan_repay_receipt($LID, $amount, $paydate);
                        foreach ($open_repayment as $key => $value) {
                            $repay_amount_install = $loaninfo->installment_amount;
                            if ($amount_tmp >= $repay_amount_install) {
                                //there is at least one installment in this stage
                                //check due date
                                $max_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($value->repaydate)) . " +" . MAX_NUMBER_DAYS_OVERDUE_PENALT . " days"));

                                if ($paydate <= $max_date) {
                                    //amewah kulipa
                                    //insert data
                                    $amount_tmp -= $repay_amount_install;
                                    $array_data = array(
                                        'LID' => $LID,
                                        'receipt' => $receipt,
                                        'installment' => $value->installment_number,
                                        'amount' => $repay_amount_install,
                                        'paydate' => $paydate,
                                        'interest' => $value->interest,
                                        'principle' => $value->principle,
                                        'duedate' => $value->repaydate,
                                        'balance' => $value->balance,
                                        'iliyobaki' => round($amount_tmp, 2),
                                        'createdby' => current_user()->id,
                                        'PIN' => $pin,
                                    );
                                    $this->loan_model->record_loan_repayment($array_data, $value->id);
                                } else {
                                    //kachelewa kulipa
                                    //get_number of months
                                    $d1 = new DateTime($max_date);
                                    $d2 = new DateTime($paydate);
                                    $number_months = ($d1->diff($d2)->m + ($d1->diff($d2)->y * 12));
                                     $number_months += 1;

                                    $penalt_method = $product->penalt_method;
                                    $penalt_percentage = $product->penalt_percentage;
                                    $penalt = 0;
                                    $principle = $value->principle;
                                    $interest = $value->interest;
                                    if ($penalt_method == 1) {
                                        //only on principle
                                        $penalt = (($penalt_percentage / 100) * $principle);
                                    } else if ($penalt_method == 2) {
                                        $tmp2 = $principle + $interest;
                                        $penalt = (($penalt_percentage / 100) * $tmp2);
                                    }

                                    $penalt_avail = round($penalt, 2);
                                    $test_remain = ($repay_amount_install + ($penalt_avail * $number_months));

                                    if ($amount_tmp >= $test_remain) {
                                        //good

                                        $amount_tmp -= $repay_amount_install;
                                        $array_data = array(
                                            'LID' => $LID,
                                            'receipt' => $receipt,
                                            'installment' => $value->installment_number,
                                            'amount' => $repay_amount_install,
                                            'paydate' => $paydate,
                                            'interest' => $value->interest,
                                            'principle' => $value->principle,
                                            'balance' => $value->balance,
                                            'duedate' => $value->repaydate,
                                            'iliyobaki' => round($amount_tmp, 2),
                                            'penalt' => ($penalt_avail * $number_months),
                                            'penalty_months' => $number_months,
                                            'createdby' => current_user()->id,
                                            'PIN' => $pin,
                                        );
//echo '<pre>';
//print_r($array_data);
//echo '</pre>';
//exit;
                                        $this->loan_model->record_loan_repayment($array_data, $value->id);
                                    } else {
                                        // insert to balance

                                        $this->loan_model->add_remain_balance($LID, round($amount_tmp, 2));
break;
                                    }
                                }
                            } else {
                                if ($amount_tmp > 0) {
                                    // insert as balance for next installment
                                    $this->loan_model->add_remain_balance($LID, round($amount_tmp, 2));
                                } else {

                                    break;
                                }
                            }
                        }


                        $this->db->trans_complete();
                        $this->session->set_flashdata('next_customer', site_url(current_lang() . '/loan/loan_repayment'));
                        $this->session->set_flashdata('next_customer_label', 'Process New Loan Repayment');

                        redirect(current_lang() . '/loan/view_loanreceipt/' . $receipt, 'refresh');
                    } else {
                        $this->data['warning'] = 'No open installment available for new payment';
                    }
                } else {
                    $this->data['warning'] = 'Invalid Operation, Loan Status does not allow Repayment process';
                }
            } else {
                $this->data['warning'] = 'Amount should be greater than 0';
            }
        }

        $this->data['content'] = 'loan/loan_repayment';
        $this->load->view('template', $this->data);
    }

    function view_repayment_schedule($loanid) {
        $this->data['title'] = lang('loan_view_repayment_schedule');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->db->order_by('installment_number', 'ASC');
        $this->data['schedule'] = $this->db->get_where('loan_contract_repayment_schedule', array('LID' => $LID))->result();
        $this->data['loaninfo'] = $this->loan_model->loan_info($LID)->row();
        $this->data['content'] = 'loan/loan_repayment_schedule';
        $this->load->view('template', $this->data);
    }

    function print_repayment_schedule($loanid) {
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->db->order_by('installment_number', 'ASC');
        $schedule = $this->db->get_where('loan_contract_repayment_schedule', array('LID' => $LID))->result();
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        include 'pdf/repayment_schedule.php';
    }

    function view_loanreceipt($receipt) {

        $this->lang->load('setting');
        $trans = $this->loan_model->get_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'loan/loan_receipt';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->loan_model->get_transaction($receipt);
        if ($trans) {
            include 'include/loan_receipt.php';
            exit;
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

}

?>
