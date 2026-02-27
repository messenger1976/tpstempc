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
            // Handle autocomplete format: "2005-00173 - Member Name" or plain "2005-00173"
            $pid_value = trim($this->input->post('pid'));
            $member_id_value = trim($this->input->post('member_id'));
            if (strpos($pid_value, ' - ') !== false) {
                $parts = explode(' - ', $pid_value, 2);
                $pid = trim($parts[0]);
            } else {
                $pid = $pid_value;
            }
            if (strpos($member_id_value, ' - ') !== false) {
                $parts = explode(' - ', $member_id_value, 2);
                $member_id = trim($parts[0]);
            } else {
                $member_id = $member_id_value;
            }
            $PID = $pid;

            $product_id = $this->input->post('product');
            $product = $this->setting_model->loanproduct($product_id)->row();

            $date = format_date(trim($this->input->post('applicationdate')));
            $amount = $this->input->post('amount');
            $installment = trim($this->input->post('installment'));
            $source = trim($this->input->post('source'));
            $purpose = trim($this->input->post('purpose'));
            $processingfee = $this->input->post('procesingfee');
            $lid_input = trim($this->input->post('lid'));
            if (empty($lid_input)) {
                $lid_input = $this->loan_model->get_next_ln_number();
            }

            $createloan = array(
                'LID' => $lid_input,
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
        $this->data['next_ln_number'] = $this->loan_model->get_next_ln_number();
        $this->data['content'] = 'loan/loan_application_step1';
        $this->load->view('template', $this->data);
    }

    function pass_monthly_income($monthy_income, $pid, $newinstall = 0, $exclude_lid = null) {
        // 
         $pin = current_user()->PIN;
        $monthly_contribution = 0;
        $contr_setup = $this->db->get_where('contribution_settings', array('PID' => $pid,'PIN'=>$pin))->row();
        if ($contr_setup) {
            $monthly_contribution = $contr_setup->amount;
        } else {
            $this->db->where('PIN',$pin);
            $contr_setup = $this->db->get('contribution_global')->row();
            $monthly_contribution = $contr_setup ? $contr_setup->amount : 0;
        }
        //check open_loan (when editing, exclude current loan so its installment is only counted via $newinstall)
        $sql = "SELECT * FROM loan_contract WHERE PID='" . $this->db->escape_str($pid) . "' AND disburse=1 AND status=4";
        if ($exclude_lid !== null && $exclude_lid !== '') {
            $sql .= " AND LID != '" . $this->db->escape_str($exclude_lid) . "'";
        }
        $open_loan = $this->db->query($sql)->result();
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
        // TEMPORARILY DISABLED - share requirement check removed for loan application
        return TRUE;
        /*
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
        */
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
        // TEMPORARILY DISABLED - maximum loan amount check bypassed
        return TRUE;
        /*
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
        */
    }

    function maximum_contributions_times($product, $loan_amount, $contribution) {
        // TEMPORARILY DISABLED - contribution times check bypassed
        return TRUE;
        /*
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
        */
    }

    function pass_contribution_condition($product, $contribution) {
        // TEMPORARILY DISABLED - minimum contribution amount check bypassed
        return TRUE;
        /*
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
        */
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

                // TEMPORARILY DISABLED: one-third monthly income check — remove comment to re-enable pass_monthly_income()
                if ($this->maximum_loan_allowed($product, $amount, $contribution, $pid) == TRUE && $this->maximum_contributions_times($product, $amount, $contribution) == TRUE && $this->pass_share_condition($product, $share_info) == TRUE && $this->pass_contribution_condition($product, $contribution) == TRUE && $this->pass_saving_condition($product, $saving_account) == TRUE) {
                    // && $this->pass_monthly_income($createloan['monthly_income'], $pid, $installment_amount, $LID) == TRUE

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
                    /* TEMPORARILY DISABLED: one-third monthly income check — uncomment to re-enable
                    }else if (!$this->pass_monthly_income($createloan['monthly_income'], $pid, $installment_amount, $LID)) {
                        $this->data['warning'] = lang('loan_contribution_exceed_one_third');
                    */
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

    /**
     * New loan disbursement entry (Option A + B): payment method dropdown + editable accounting lines.
     * Credit account comes from selected payment method; user can edit/add/remove lines.
     */
    function loan_disburse_entry($loanid) {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_disburse_inaction');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo || $loaninfo->PIN != $pin) {
            $this->session->set_flashdata('warning', lang('loan_evaluation_error'));
            redirect(current_lang() . '/loan/loan_disbursement', 'refresh');
            return;
        }
        if ($loaninfo->status != 4 || $loaninfo->disburse != 0) {
            $this->session->set_flashdata('warning', 'Loan is not approved or already disbursed.');
            redirect(current_lang() . '/loan/loan_disbursement', 'refresh');
            return;
        }

        $this->form_validation->set_rules('disbursedate', lang('loan_disburse_date'), 'required|valid_date');
        $this->form_validation->set_rules('comment', lang('loan_comment'), 'required');
        $this->form_validation->set_rules('payment_method', lang('loan_disburse_payment_method'), 'required');
        if ($this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'disburse_no'")->row()) {
            $this->form_validation->set_rules('disburse_no', lang('loan_disburse_no'), 'required|callback_check_loan_disburse_no');
        }

        if ($this->form_validation->run() == TRUE) {
            $accounts = $this->input->post('account');
            $debits = $this->input->post('debit');
            $credits = $this->input->post('credit');
            $line_descriptions = $this->input->post('line_description');
            $line_items = array();
            $total_debit = 0;
            $total_credit = 0;
            if (is_array($accounts)) {
                foreach ($accounts as $key => $account) {
                    $debit = isset($debits[$key]) ? floatval(str_replace(',', '', $debits[$key])) : 0;
                    $credit = isset($credits[$key]) ? floatval(str_replace(',', '', $credits[$key])) : 0;
                    if (!empty($account) && ($debit > 0 || $credit > 0)) {
                        $line_items[] = array(
                            'account' => $account,
                            'debit' => $debit,
                            'credit' => $credit,
                            'description' => isset($line_descriptions[$key]) ? $line_descriptions[$key] : '',
                        );
                        $total_debit += $debit;
                        $total_credit += $credit;
                    }
                }
            }
            if (empty($line_items)) {
                $this->data['warning'] = lang('loan_disburse_entries_required');
            } elseif (abs($total_debit - $total_credit) > 0.01) {
                $this->data['warning'] = lang('debits_credits_not_balanced');
            } else {
                $payment_method_id = $this->input->post('payment_method');
                $payment_method_name = '';
                if (isset($this->data['payment_methods_by_id'][$payment_method_id])) {
                    $payment_method_name = $this->data['payment_methods_by_id'][$payment_method_id]->name;
                } else {
                    $this->load->model('payment_method_config_model');
                    $pm = $this->payment_method_config_model->get_payment_method_by_id($payment_method_id, $pin);
                    $payment_method_name = $pm ? $pm->name : 'Cash';
                }

                $disburse_date = format_date(trim($this->input->post('disbursedate')));
                $array_data = array(
                    'LID' => $LID,
                    'disbursedate' => $disburse_date,
                    'comment' => $this->input->post('comment'),
                    'createdby' => current_user()->id,
                    'PIN' => $pin,
                );
                if ($this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'disburse_no'")->row()) {
                    $array_data['disburse_no'] = trim($this->input->post('disburse_no'));
                }
                if ($this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'payment_method'")->row()) {
                    $array_data['payment_method'] = $payment_method_name;
                }

                $this->db->trans_start();
                $this->db->insert('loan_contract_disburse', $array_data);
                $this->db->update('loan_contract', array('disburse' => 1), array('LID' => $LID));
                $this->loan_model->save_disbursement_gl_items($LID, $pin, $line_items);
                $this->loan_model->post_loan_disbursement_to_gl($LID, $pin, $line_items, $disburse_date, $loaninfo);

                $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
                if (!$product) {
                    $this->db->trans_rollback();
                    $this->data['warning'] = 'Loan product not found. Cannot create repayment schedule.';
                } else {
                    $interest_method = (isset($product->interest_method) && ($product->interest_method == 1 || $product->interest_method == 2)) ? (int) $product->interest_method : 1;
                    $interval = isset($product->interval) ? (int) $product->interval : 1;
                    $schedule = $this->loanbase->create_repayment_schedule(
                        $loaninfo->installment_amount, $loaninfo->rate, $loaninfo->number_istallment,
                        $disburse_date, $loaninfo->basic_amount, $LID, $interest_method, $interval
                    );
                    if (!empty($schedule)) {
                        $this->db->insert_batch('loan_contract_repayment_schedule', $schedule);
                    }
                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $this->data['warning'] = lang('loan_evaluation_error') . ' Transaction was rolled back. Please try again or contact support.';
                    } else {
                        $this->session->set_flashdata('message', lang('loan_info_saved'));
                        redirect(current_lang() . '/loan/view_repayment_schedule/' . $loanid, 'refresh');
                        return;
                    }
                }
                $this->db->trans_complete();
            }
        }

        $this->data['loaninfo'] = $loaninfo;
        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
        $this->data['loan_principle_account'] = $product ? $product->loan_principle_account : '';
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();
        $this->load->model('payment_method_config_model');
        $payment_methods = $this->payment_method_config_model->get_all_payment_methods();
        $this->data['payment_methods'] = array();
        $this->data['payment_methods_by_id'] = array();
        $default_credit_account = null;
        $first_id = null;
        $cash_id = null;
        foreach ($payment_methods as $method) {
            $this->data['payment_methods'][$method->id] = $method->name;
            $this->data['payment_methods_by_id'][$method->id] = $method;
            if ($first_id === null) {
                $first_id = $method->id;
            }
            if ($cash_id === null && isset($method->name) && strcasecmp(trim($method->name), 'cash') === 0) {
                $cash_id = $method->id;
            }
        }
        $default_payment_method_id = $cash_id !== null ? $cash_id : $first_id;
        if ($default_payment_method_id !== null) {
            $default_credit_account = $this->loan_model->get_credit_account_for_payment_method($default_payment_method_id);
        }
        $this->data['default_credit_account'] = $default_credit_account;
        $this->data['default_payment_method_id'] = $default_payment_method_id;
        $this->data['show_disburse_no'] = (bool) $this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'disburse_no'")->row();
        $this->data['next_disburse_no'] = $this->loan_model->get_next_loan_disburse_no();
        $payment_method_credit_accounts = array();
        foreach ($payment_methods as $method) {
            $payment_method_credit_accounts[$method->id] = $this->loan_model->get_credit_account_for_payment_method($method->id);
        }
        $this->data['payment_method_credit_accounts'] = $payment_method_credit_accounts;
        $this->data['content'] = 'loan/loan_disburse_entry';
        $this->load->view('template', $this->data);
    }

    /**
     * Form validation callback: ensure loan disbursement number is unique for this PIN.
     */
    function check_loan_disburse_no($disburse_no) {
        if (empty(trim($disburse_no))) {
            return TRUE;
        }
        if ($this->loan_model->loan_disburse_no_exists(trim($disburse_no), null)) {
            $this->form_validation->set_message('check_loan_disburse_no', lang('loan_disburse_no_exists'));
            return FALSE;
        }
        return TRUE;
    }

    function loan_disburse_action($loanid) {
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_disburse_inaction');
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->form_validation->set_rules('disbursedate', lang('loan_disburse_date'), 'required|valid_date');
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

        $status_filter = null;
        if (isset($_POST['status_filter']) && $_POST['status_filter'] !== '') {
            $status_filter = $_POST['status_filter'];
        } else if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') {
            $status_filter = $_GET['status_filter'];
        }

        if (!is_null($key)) {
            $config['suffix'] = '?key=' . urlencode($key);
        }
        if ($status_filter !== null && $status_filter !== '') {
            $config['suffix'] = (isset($config['suffix']) ? $config['suffix'] . '&' : '?') . 'status_filter=' . urlencode($status_filter);
        }


        $config["base_url"] = site_url(current_lang() . '/loan/loan_viewlist/');
        $config["total_rows"] = $this->loan_model->count_loan($key, $status_filter);
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

        $this->data['loan_list'] = $this->loan_model->search_loan($key, $config["per_page"], $page, $status_filter);

        $this->data['status_filter'] = $status_filter;
        $this->data['status_list'] = loan_status();
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
        $this->load->library('pagination');
        $pin = current_user()->PIN;
        $this->data['title'] = lang('loan_repayment');

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
            $config['suffix'] = '?key=' . urlencode($key);
        }

        $config["base_url"] = site_url(current_lang() . '/loan/loan_repayment/');
        $config["total_rows"] = $this->loan_model->count_loan_repayment_list_released_with_balance($key);
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
        $this->data['loan_list'] = $this->loan_model->loan_repayment_list_released_with_balance($key, $config["per_page"], $page);

        $this->load->model('cash_receipt_model');
        $this->data['next_receipt_no'] = $this->cash_receipt_model->get_next_shared_receipt_no();
        $this->data['content'] = 'loan/loan_repayment';
        $this->load->view('template', $this->data);
    }

    /**
     * Full-page Process Payment form (like Cash Receipt create). Linked from Loan Repayment list.
     */
    function loan_repayment_entry($loanid) {
        $LID = decode_id($loanid);
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo) {
            show_404();
            return;
        }
        $pin = current_user()->PIN;
        if ((string) $loaninfo->PIN !== (string) $pin) {
            show_404();
            return;
        }
        $this->data['title'] = lang('loan_repayment') . ' - ' . lang('loan_repay_btn');
        $this->data['loaninfo'] = $loaninfo;
        $this->data['loanid'] = $loanid;
        $this->load->model('cash_receipt_model');
        $this->data['next_receipt_no'] = $this->cash_receipt_model->get_next_shared_receipt_no();
        $this->data['content'] = 'loan/loan_repayment_entry';
        $this->load->view('template', $this->data);
    }

    /**
     * Process form submit from loan_repayment_entry. Validates, saves, redirects to receipt or back to entry.
     */
    function loan_repayment_process() {
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            redirect(current_lang() . '/loan/loan_repayment', 'refresh');
            return;
        }
        $pin = current_user()->PIN;
        $amount_raw = $this->input->post('amount');
        if ($amount_raw !== null && $amount_raw !== '') {
            $_POST['amount'] = str_replace(',', '', $amount_raw);
        }
        $this->form_validation->set_rules('amount', lang('loan_repay_amount'), 'required|numeric');
        $this->form_validation->set_rules('loanid', lang('loan_LID'), 'required');
        $this->form_validation->set_rules('repaydate', lang('loan_repay_date'), 'required|valid_date');
        $this->form_validation->set_rules('receipt_no', lang('cash_receipt_no'), 'required');

        $LID = trim($this->input->post('loanid'));
        $loanid_encoded = encode_id($LID);
        $redirect_back = current_lang() . '/loan/loan_repayment_entry/' . $loanid_encoded;

        if ($this->form_validation->run() !== TRUE) {
            $this->session->set_flashdata('warning', validation_errors(' ', ' '));
            redirect($redirect_back, 'refresh');
            return;
        }

        $amount = trim($this->input->post('amount'));
        $paydate = format_date(trim($this->input->post('repaydate')));
        $receipt_no = trim($this->input->post('receipt_no'));

        $this->load->model('cash_receipt_model');
        if ($this->cash_receipt_model->receipt_no_exists($receipt_no)) {
            $this->session->set_flashdata('warning', lang('cash_receipt_no_exists'));
            redirect($redirect_back, 'refresh');
            return;
        }
        if ($this->loan_model->receipt_no_exists_loan_repayment($receipt_no)) {
            $this->session->set_flashdata('warning', lang('cash_receipt_no_exists'));
            redirect($redirect_back, 'refresh');
            return;
        }

        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo) {
            $this->session->set_flashdata('warning', 'Loan not found.');
            redirect($redirect_back, 'refresh');
            return;
        }
        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
        $open_repayment = $this->loan_model->open_repayment_installment($LID);
        $previous_remain_balance = $this->loan_model->get_previous_remain_balance($LID);
        $amount_tmp = ($amount + $previous_remain_balance);

        if ($amount <= 0) {
            $this->session->set_flashdata('warning', 'Amount should be greater than 0');
            redirect($redirect_back, 'refresh');
            return;
        }
        if ($loaninfo->status != 4) {
            $this->session->set_flashdata('warning', 'Invalid Operation, Loan Status does not allow Repayment process');
            redirect($redirect_back, 'refresh');
            return;
        }
        if (count($open_repayment) < 1) {
            $open_repayment_check = $this->loan_model->open_repayment_installment($LID);
            if (count($open_repayment_check) < 1) {
                $this->db->update('loan_contract', array('status' => 5), array('LID' => $LID, 'status' => 4, 'disburse' => 1, 'PIN' => $pin));
            }
            $this->session->set_flashdata('warning', 'No open installment available for new payment');
            redirect($redirect_back, 'refresh');
            return;
        }

        $this->db->trans_start();
        $receipt = $this->loan_model->loan_repay_receipt($LID, $amount, $paydate, $receipt_no);
        foreach ($open_repayment as $key => $value) {
            $repay_amount_install = $loaninfo->installment_amount;
            if ($amount_tmp >= $repay_amount_install) {
                $max_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($value->repaydate)) . " +" . MAX_NUMBER_DAYS_OVERDUE_PENALT . " days"));
                if ($paydate <= $max_date) {
                    $repay_amount_install_to_pay_all_loan = round($value->repayamount + $value->balance, 2);
                    if ($amount_tmp >= $repay_amount_install_to_pay_all_loan) {
                        $new_principle = round($repay_amount_install_to_pay_all_loan - $value->interest, 2);
                        $amount_tmp -= $repay_amount_install_to_pay_all_loan;
                        $array_data = array(
                            'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                            'amount' => $repay_amount_install_to_pay_all_loan, 'paydate' => $paydate,
                            'interest' => $value->interest, 'principle' => $new_principle, 'duedate' => $value->repaydate,
                            'balance' => 0, 'iliyobaki' => round($amount_tmp, 2), 'createdby' => current_user()->id, 'PIN' => $pin,
                        );
                        $this->loan_model->record_loan_repayment_all($array_data, $value->id, $value->LID);
                        break;
                    } else {
                        $amount_tmp -= $repay_amount_install;
                        $array_data = array(
                            'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                            'amount' => $repay_amount_install, 'paydate' => $paydate,
                            'interest' => $value->interest, 'principle' => $value->principle, 'duedate' => $value->repaydate,
                            'balance' => $value->balance, 'iliyobaki' => round($amount_tmp, 2), 'createdby' => current_user()->id, 'PIN' => $pin,
                        );
                        $this->loan_model->record_loan_repayment($array_data, $value->id);
                    }
                } else {
                    $d1 = new DateTime($max_date);
                    $d2 = new DateTime($paydate);
                    $number_months = ($d1->diff($d2)->m + ($d1->diff($d2)->y * 12)) + 1;
                    $penalt_method = $product->penalt_method;
                    $penalt_percentage = $product->penalt_percentage;
                    $penalt = 0;
                    $principle = $value->principle;
                    $interest_val = $value->interest;
                    if ($penalt_method == 1) $penalt = (($penalt_percentage / 100) * $principle);
                    else if ($penalt_method == 2) $penalt = (($penalt_percentage / 100) * ($principle + $interest_val));
                    $penalt_avail = round($penalt, 2);
                    $test_remain = ($repay_amount_install + ($penalt_avail * $number_months));
                    if ($amount_tmp >= $test_remain) {
                        $repay_amount_install_to_pay_all_loan = round($value->repayamount + $value->balance + ($penalt_avail * $number_months), 2);
                        if ($amount_tmp >= $repay_amount_install_to_pay_all_loan) {
                            $new_principle = round($value->repayamount + $value->balance - $value->interest, 2);
                            $amount_tmp -= $repay_amount_install_to_pay_all_loan;
                            $array_data = array(
                                'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                                'amount' => $repay_amount_install_to_pay_all_loan, 'paydate' => $paydate,
                                'interest' => $value->interest, 'principle' => $new_principle, 'balance' => 0, 'duedate' => $value->repaydate,
                                'iliyobaki' => round($amount_tmp, 2), 'penalt' => ($penalt_avail * $number_months), 'penalty_months' => $number_months,
                                'createdby' => current_user()->id, 'PIN' => $pin,
                            );
                            $this->loan_model->record_loan_repayment_all($array_data, $value->id, $value->LID);
                            break;
                        } else {
                            $amount_tmp -= $repay_amount_install;
                            $array_data = array(
                                'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                                'amount' => $repay_amount_install, 'paydate' => $paydate,
                                'interest' => $value->interest, 'principle' => $value->principle, 'balance' => $value->balance, 'duedate' => $value->repaydate,
                                'iliyobaki' => round($amount_tmp, 2), 'penalt' => ($penalt_avail * $number_months), 'penalty_months' => $number_months,
                                'createdby' => current_user()->id, 'PIN' => $pin,
                            );
                            $this->loan_model->record_loan_repayment($array_data, $value->id);
                        }
                    } else {
                        $this->loan_model->add_remain_balance($LID, round($amount_tmp, 2));
                        break;
                    }
                }
            } else {
                if ($amount_tmp > 0) {
                    $this->loan_model->add_remain_balance($LID, round($amount_tmp, 2));
                }
                break;
            }
        }
        $open_repayment_check = $this->loan_model->open_repayment_installment($LID);
        if (count($open_repayment_check) < 1) {
            $this->db->update('loan_contract', array('status' => 5), array('LID' => $LID, 'status' => 4, 'disburse' => 1, 'PIN' => $pin));
        }
        $this->db->trans_complete();
        redirect(site_url(current_lang() . '/loan/view_loanreceipt/' . $receipt), 'refresh');
    }

    /**
     * Popup window form for adding a loan payment. Opens from loan_repayment list via window.open().
     */
    function loan_repayment_form($loanid) {
        $LID = decode_id($loanid);
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo) {
            show_404();
            return;
        }
        $this->load->model('cash_receipt_model');
        $data['loan_LID'] = $loaninfo->LID;
        $data['next_receipt_no'] = $this->cash_receipt_model->get_next_shared_receipt_no();
        $this->load->view('loan/loan_repayment_form_popup', $data);
    }

    /**
     * AJAX: Save loan repayment from modal (Receipt No., Payment Date, Amount). Returns JSON.
     */
    function loan_repayment_save() {
        $this->output->set_content_type('application/json');
        if (strtoupper($this->input->server('REQUEST_METHOD')) !== 'POST') {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => 'Invalid request.')));
            return;
        }
        $pin = current_user()->PIN;
        $amount_raw = $this->input->post('amount');
        if ($amount_raw !== null && $amount_raw !== '') {
            $_POST['amount'] = str_replace(',', '', $amount_raw);
        }
        $this->form_validation->set_rules('amount', lang('loan_repay_amount'), 'required|numeric');
        $this->form_validation->set_rules('loanid', lang('loan_LID'), 'required');
        $this->form_validation->set_rules('repaydate', lang('loan_repay_date'), 'required|valid_date');
        $this->form_validation->set_rules('receipt_no', lang('cash_receipt_no'), 'required');

        if ($this->form_validation->run() !== TRUE) {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => validation_errors(' ', ' '), 'validation_errors' => $this->form_validation->error_array())));
            return;
        }

        $amount = trim($this->input->post('amount'));
        $LID = trim($this->input->post('loanid'));
        $paydate = format_date(trim($this->input->post('repaydate')));
        $receipt_no = trim($this->input->post('receipt_no'));

        $this->load->model('cash_receipt_model');
        if ($this->cash_receipt_model->receipt_no_exists($receipt_no)) {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => lang('cash_receipt_no_exists'))));
            return;
        }
        if ($this->loan_model->receipt_no_exists_loan_repayment($receipt_no)) {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => lang('cash_receipt_no_exists'))));
            return;
        }

        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo) {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => 'Loan not found.')));
            return;
        }
        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
        $open_repayment = $this->loan_model->open_repayment_installment($LID);
        $previous_remain_balance = $this->loan_model->get_previous_remain_balance($LID);
        $amount_tmp = ($amount + $previous_remain_balance);

        if ($amount <= 0) {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => 'Amount should be greater than 0')));
            return;
        }
        if ($loaninfo->status != 4) {
            $this->output->set_output(json_encode(array('success' => false, 'warning' => 'Invalid Operation, Loan Status does not allow Repayment process')));
            return;
        }
        if (count($open_repayment) < 1) {
            $open_repayment_check = $this->loan_model->open_repayment_installment($LID);
            if (count($open_repayment_check) < 1) {
                $this->db->update('loan_contract', array('status' => 5), array('LID' => $LID, 'status' => 4, 'disburse' => 1, 'PIN' => $pin));
            }
            $this->output->set_output(json_encode(array('success' => false, 'warning' => 'No open installment available for new payment')));
            return;
        }

        $this->db->trans_start();
        $receipt = $this->loan_model->loan_repay_receipt($LID, $amount, $paydate, $receipt_no);
        foreach ($open_repayment as $key => $value) {
            $repay_amount_install = $loaninfo->installment_amount;
            if ($amount_tmp >= $repay_amount_install) {
                $max_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($value->repaydate)) . " +" . MAX_NUMBER_DAYS_OVERDUE_PENALT . " days"));
                if ($paydate <= $max_date) {
                    $repay_amount_install_to_pay_all_loan = round($value->repayamount + $value->balance, 2);
                    if ($amount_tmp >= $repay_amount_install_to_pay_all_loan) {
                        $new_principle = round($repay_amount_install_to_pay_all_loan - $value->interest, 2);
                        $amount_tmp -= $repay_amount_install_to_pay_all_loan;
                        $array_data = array(
                            'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                            'amount' => $repay_amount_install_to_pay_all_loan, 'paydate' => $paydate,
                            'interest' => $value->interest, 'principle' => $new_principle, 'duedate' => $value->repaydate,
                            'balance' => 0, 'iliyobaki' => round($amount_tmp, 2), 'createdby' => current_user()->id, 'PIN' => $pin,
                        );
                        $this->loan_model->record_loan_repayment_all($array_data, $value->id, $value->LID);
                        break;
                    } else {
                        $amount_tmp -= $repay_amount_install;
                        $array_data = array(
                            'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                            'amount' => $repay_amount_install, 'paydate' => $paydate,
                            'interest' => $value->interest, 'principle' => $value->principle, 'duedate' => $value->repaydate,
                            'balance' => $value->balance, 'iliyobaki' => round($amount_tmp, 2), 'createdby' => current_user()->id, 'PIN' => $pin,
                        );
                        $this->loan_model->record_loan_repayment($array_data, $value->id);
                    }
                } else {
                    $d1 = new DateTime($max_date);
                    $d2 = new DateTime($paydate);
                    $number_months = ($d1->diff($d2)->m + ($d1->diff($d2)->y * 12)) + 1;
                    $penalt_method = $product->penalt_method;
                    $penalt_percentage = $product->penalt_percentage;
                    $penalt = 0;
                    $principle = $value->principle;
                    $interest_val = $value->interest;
                    if ($penalt_method == 1) $penalt = (($penalt_percentage / 100) * $principle);
                    else if ($penalt_method == 2) $penalt = (($penalt_percentage / 100) * ($principle + $interest_val));
                    $penalt_avail = round($penalt, 2);
                    $test_remain = ($repay_amount_install + ($penalt_avail * $number_months));
                    if ($amount_tmp >= $test_remain) {
                        $repay_amount_install_to_pay_all_loan = round($value->repayamount + $value->balance + ($penalt_avail * $number_months), 2);
                        if ($amount_tmp >= $repay_amount_install_to_pay_all_loan) {
                            $new_principle = round($value->repayamount + $value->balance - $value->interest, 2);
                            $amount_tmp -= $repay_amount_install_to_pay_all_loan;
                            $array_data = array(
                                'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                                'amount' => $repay_amount_install_to_pay_all_loan, 'paydate' => $paydate,
                                'interest' => $value->interest, 'principle' => $new_principle, 'balance' => 0, 'duedate' => $value->repaydate,
                                'iliyobaki' => round($amount_tmp, 2), 'penalt' => ($penalt_avail * $number_months), 'penalty_months' => $number_months,
                                'createdby' => current_user()->id, 'PIN' => $pin,
                            );
                            $this->loan_model->record_loan_repayment_all($array_data, $value->id, $value->LID);
                            break;
                        } else {
                            $amount_tmp -= $repay_amount_install;
                            $array_data = array(
                                'LID' => $LID, 'receipt' => $receipt, 'installment' => $value->installment_number,
                                'amount' => $repay_amount_install, 'paydate' => $paydate,
                                'interest' => $value->interest, 'principle' => $value->principle, 'balance' => $value->balance, 'duedate' => $value->repaydate,
                                'iliyobaki' => round($amount_tmp, 2), 'penalt' => ($penalt_avail * $number_months), 'penalty_months' => $number_months,
                                'createdby' => current_user()->id, 'PIN' => $pin,
                            );
                            $this->loan_model->record_loan_repayment($array_data, $value->id);
                        }
                    } else {
                        $this->loan_model->add_remain_balance($LID, round($amount_tmp, 2));
                        break;
                    }
                }
            } else {
                if ($amount_tmp > 0) {
                    $this->loan_model->add_remain_balance($LID, round($amount_tmp, 2));
                }
                break;
            }
        }
        $open_repayment_check = $this->loan_model->open_repayment_installment($LID);
        if (count($open_repayment_check) < 1) {
            $this->db->update('loan_contract', array('status' => 5), array('LID' => $LID, 'status' => 4, 'disburse' => 1, 'PIN' => $pin));
        }
        $this->db->trans_complete();
        $redirect = site_url(current_lang() . '/loan/view_loanreceipt/' . $receipt);
        $this->output->set_output(json_encode(array('success' => true, 'redirect' => $redirect)));
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

    /**
     * Repayment schedule for popup window - only the schedule table, no full page template
     */
    function view_repayment_schedule_popup($loanid) {
        $LID = decode_id($loanid);
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo) {
            show_404();
            return;
        }
        $this->db->order_by('installment_number', 'ASC');
        $data['schedule'] = $this->db->get_where('loan_contract_repayment_schedule', array('LID' => $LID))->result();
        $data['loaninfo'] = $loaninfo;
        $data['loanid'] = $loanid;
        $this->load->view('loan/loan_repayment_schedule_popup', $data);
    }

    /**
     * Print loan disbursement voucher (for cashier release).
     * Shows latest disbursement header + accounting entries.
     */
    function loan_disbursement_print($loanid) {
        $pin = current_user()->PIN;
        $LID = decode_id($loanid);
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo || (string) $loaninfo->PIN !== (string) $pin) {
            show_404();
            return;
        }

        // Latest disbursement header for this loan
        $this->db->where('LID', $LID);
        $this->db->where('PIN', $pin);
        $this->db->order_by('createdon', 'DESC');
        $this->db->limit(1);
        $disburse = $this->db->get('loan_contract_disburse')->row();
        if (!$disburse) {
            return show_error('Loan disbursement not found.', 404);
        }

        // Prefer saved editable lines, otherwise fall back to GL lines
        $line_items = array();
        $ledger_entry_id = null;

        $saved_items = $this->loan_model->get_disbursement_gl_items($LID, $pin);
        if (!empty($saved_items)) {
            foreach ($saved_items as $it) {
                $row = new stdClass();
                $row->account = isset($it['account']) ? $it['account'] : '';
                $row->description = isset($it['description']) ? $it['description'] : '';
                $row->debit = isset($it['debit']) ? floatval($it['debit']) : 0;
                $row->credit = isset($it['credit']) ? floatval($it['credit']) : 0;
                $row->account_name = '';
                if (!empty($row->account)) {
                    $acc = $this->db->query('SELECT name FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($row->account, $pin))->row();
                    $row->account_name = $acc ? $acc->name : '';
                }
                $line_items[] = $row;
            }
        } else {
            $entry = $this->db->query(
                'SELECT entryid FROM general_ledger WHERE PIN = ? AND journalID = 4 AND LID = ? ORDER BY entryid DESC LIMIT 1',
                array($pin, $LID)
            )->row();
            if ($entry && !empty($entry->entryid)) {
                $ledger_entry_id = $entry->entryid;
                $rows = $this->db->query(
                    'SELECT gl.account, gl.debit, gl.credit, gl.description, ac.name as account_name
                     FROM general_ledger gl
                     LEFT JOIN account_chart ac ON ac.account = gl.account AND ac.PIN = gl.PIN
                     WHERE gl.PIN = ? AND gl.entryid = ?
                     ORDER BY gl.debit DESC, gl.id ASC',
                    array($pin, $ledger_entry_id)
                )->result();
                foreach ($rows as $r) {
                    $row = new stdClass();
                    $row->account = $r->account;
                    $row->account_name = $r->account_name;
                    $row->description = $r->description;
                    $row->debit = floatval($r->debit);
                    $row->credit = floatval($r->credit);
                    $line_items[] = $row;
                }
            }
        }

        $data = array(
            'loanid' => $loanid,
            'loaninfo' => $loaninfo,
            'disburse' => $disburse,
            'line_items' => $line_items,
            'ledger_entry_id' => $ledger_entry_id,
        );
        $this->load->view('loan/print/loan_disbursement_print', $data);
    }

    function print_repayment_schedule($loanid) {
        $this->data['loanid'] = $loanid;
        $LID = decode_id($loanid);
        $this->db->order_by('installment_number', 'ASC');
        $schedule = $this->db->get_where('loan_contract_repayment_schedule', array('LID' => $LID))->result();
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        include 'pdf/repayment_schedule.php';
    }

    /**
     * Export loan repayment schedule to Excel
     */
    function export_repayment_schedule($loanid) {
        $LID = decode_id($loanid);
        $loaninfo = $this->loan_model->loan_info($LID)->row();
        if (!$loaninfo) {
            $this->session->set_flashdata('warning', 'Loan not found.');
            redirect(current_lang() . '/loan/view_repayment_schedule/' . $loanid, 'refresh');
            return;
        }
        $this->db->order_by('installment_number', 'ASC');
        $schedule = $this->db->get_where('loan_contract_repayment_schedule', array('LID' => $LID))->result();

        $this->load->library('excel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
            ->setTitle(lang('loan_view_repayment_schedule') . ' - ' . $loaninfo->LID)
            ->setSubject('Loan Repayment Schedule Export');

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Repayment Schedule');

        $row = 1;
        $sheet->setCellValue('A' . $row, lang('loan_view_repayment_schedule') . ' - ' . lang('loan_LID') . ' ' . $loaninfo->LID);
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A' . $row, lang('loan_applied_amount') . ': ' . number_format($loaninfo->basic_amount, 2));
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $row += 2;

        $sheet->setCellValue('A' . $row, lang('sno'));
        $sheet->setCellValue('B' . $row, lang('due_date'));
        $sheet->setCellValue('C' . $row, lang('amount'));
        $sheet->setCellValue('D' . $row, 'Interest');
        $sheet->setCellValue('E' . $row, 'Principle');
        $sheet->setCellValue('F' . $row, lang('balance'));
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, number_format($loaninfo->basic_amount, 2));
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $row++;

        $s = 1;
        foreach ($schedule as $value) {
            $sheet->setCellValue('A' . $row, $s++);
            $sheet->setCellValue('B' . $row, date('d M, Y', strtotime($value->repaydate)));
            $sheet->setCellValue('C' . $row, number_format($value->repayamount, 2));
            $sheet->setCellValue('D' . $row, number_format($value->interest, 2));
            $sheet->setCellValue('E' . $row, number_format($value->principle, 2));
            $sheet->setCellValue('F' . $row, number_format($value->balance, 2));
            $sheet->getStyle('C' . $row . ':F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Loan_Repayment_Schedule_' . $loaninfo->LID . '_' . date('Y-m-d_His') . '.xls';
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
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

    // Loan Beginning Balances Methods
    function loan_beginning_balance_list() {
        $this->data['title'] = lang('loan_beginning_balance_list');
        
        // Get all fiscal years
        $this->data['fiscal_years'] = $this->setting_model->fiscal_year_list()->result();
        
        // Get all loan products for filter
        $this->data['loan_products'] = $this->setting_model->loanproduct()->result();
       
        // Handle fiscal year selection with session + active fiscal year logic
        // Initialize variable
        $selected_fiscal_year_id = null;
        
        // 1. First, check POST/GET (user explicitly selected a fiscal year)
        $post_fiscal_year_id = $this->input->post('fiscal_year_id');
        $get_fiscal_year_id = $this->input->get('fiscal_year_id');
        
        if ($post_fiscal_year_id) {
            $selected_fiscal_year_id = $post_fiscal_year_id;
        } elseif ($get_fiscal_year_id) {
            $selected_fiscal_year_id = $get_fiscal_year_id;
        }

        // 2. If a fiscal year was selected via POST/GET, store it in session
        if ($selected_fiscal_year_id) {
            $this->session->set_userdata('loan_beginning_balance_fiscal_year_id', $selected_fiscal_year_id);
        } else {
            // 3. If nothing was selected in this request, try session value
            $session_fiscal_year_id = $this->session->userdata('loan_beginning_balance_fiscal_year_id');

            if ($session_fiscal_year_id) {
                $selected_fiscal_year_id = $session_fiscal_year_id;
            } else {
                // 4. If session is empty, fall back to the active fiscal year
                $active_fiscal_year = $this->setting_model->get_active_fiscal_year();
                if ($active_fiscal_year) {
                    $selected_fiscal_year_id = $active_fiscal_year->id;
                    // Store active fiscal year in session for future use
                    $this->session->set_userdata('loan_beginning_balance_fiscal_year_id', $selected_fiscal_year_id);
                }
            }
        }
        
        // Ensure selected_fiscal_year_id is an integer for proper comparison
        if ($selected_fiscal_year_id) {
            $selected_fiscal_year_id = (int)$selected_fiscal_year_id;
        }
        
        // Handle loan product filter
        $selected_loan_product_id = $this->input->post('loan_product_id');
        if (!$selected_loan_product_id && $this->input->get('loan_product_id')) {
            $selected_loan_product_id = $this->input->get('loan_product_id');
        }
        if (!$selected_loan_product_id) {
            $selected_loan_product_id = 'all'; // Default to 'all'
        }
        $this->data['selected_loan_product_id'] = $selected_loan_product_id;
        
        // Always set selected_fiscal_year_id in data array so view can use it for dropdown selection
        $this->data['selected_fiscal_year_id'] = $selected_fiscal_year_id;
        
        if ($selected_fiscal_year_id) {
            $this->data['fiscal_year'] = $this->setting_model->fiscal_year_list($selected_fiscal_year_id)->row();
            $balances = $this->loan_model->loan_beginning_balance_list($selected_fiscal_year_id, null, $selected_loan_product_id)->result();
            
            // Pre-fetch member names and product info to avoid N+1 queries
            $member_names = array();
            $product_info = array();
            foreach ($balances as $balance) {
                if (!isset($member_names[$balance->member_id])) {
                    try {
                        $member_names[$balance->member_id] = $this->member_model->member_name($balance->member_id);
                    } catch (Exception $e) {
                        $member_names[$balance->member_id] = 'Unknown';
                    }
                }
                if (!isset($product_info[$balance->loan_product_id])) {
                    $product = $this->setting_model->loanproduct($balance->loan_product_id)->row();
                    $product_info[$balance->loan_product_id] = $product ? $product->name : '-';
                }
            }
            
            $this->data['loan_beginning_balances'] = $balances;
            $this->data['member_names'] = $member_names;
            $this->data['product_info'] = $product_info;
        } else {
            $this->data['loan_beginning_balances'] = array();
            $this->data['member_names'] = array();
            $this->data['product_info'] = array();
        }
        
        $this->data['content'] = 'loan/loan_beginning_balance_list';
        $this->load->view('template', $this->data);
    }

    function loan_beginning_balance_create($id = null) {
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        
        if ($id) {
            $this->data['title'] = lang('loan_beginning_balance_edit');
            $this->data['balance'] = $this->loan_model->loan_beginning_balance_list(null, $id)->row();
            
            if (!$this->data['balance']) {
                $this->session->set_flashdata('warning', lang('loan_beginning_balance_not_found'));
                redirect(current_lang() . '/loan/loan_beginning_balance_list', 'refresh');
                return;
            }
            
            // Check if already posted
            if ($this->data['balance']->posted == 1) {
                $this->session->set_flashdata('warning', lang('loan_beginning_balance_already_posted'));
                redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $this->data['balance']->fiscal_year_id, 'refresh');
                return;
            }
        } else {
            $this->data['title'] = lang('loan_beginning_balance_create');
        }
        
        // Get fiscal years and loan products
        $this->data['fiscal_years'] = $this->setting_model->fiscal_year_list()->result();
        $this->data['loan_products'] = $this->setting_model->loanproduct()->result();
        
        // Form validation
        $this->form_validation->set_rules('fiscal_year_id', lang('fiscal_year'), 'required|numeric');
        $this->form_validation->set_rules('member_id', lang('loan_beginning_balance_member_id'), 'required');
        $this->form_validation->set_rules('loan_product_id', lang('loan_beginning_balance_loan_product'), 'required|numeric');
        $this->form_validation->set_rules('principal_balance', lang('loan_beginning_balance_principal'), 'numeric');
        $this->form_validation->set_rules('interest_balance', lang('loan_beginning_balance_interest'), 'numeric');
        $this->form_validation->set_rules('penalty_balance', lang('loan_beginning_balance_penalty'), 'numeric');
        
        if ($this->form_validation->run() == TRUE) {
            $fiscal_year_id = trim($this->input->post('fiscal_year_id'));
            $member_id_raw = trim($this->input->post('member_id'));
            $loan_product_id = trim($this->input->post('loan_product_id'));
            $loan_id = trim($this->input->post('loan_id'));
            $principal_balance = str_replace(',', '', trim($this->input->post('principal_balance')));
            $interest_balance = str_replace(',', '', trim($this->input->post('interest_balance')));
            $penalty_balance = str_replace(',', '', trim($this->input->post('penalty_balance')));
            $disbursement_date = format_date(trim($this->input->post('disbursement_date')));
            $loan_amount = str_replace(',', '', trim($this->input->post('loan_amount')));
            $monthly_amort = str_replace(',', '', trim($this->input->post('monthly_amort')));
            $last_date_paid = format_date(trim($this->input->post('last_date_paid')));
            $term = trim($this->input->post('term'));
            $description = trim($this->input->post('description'));
            
            // Clean member_id - extract just the ID if autocomplete format is used (e.g., "12345 - John Doe")
            $member_id = $member_id_raw;
            if (strpos($member_id_raw, ' - ') !== false) {
                $explode = explode(' - ', $member_id_raw);
                $member_id = trim($explode[0]);
            } else {
                // Extract numeric/alphanumeric part if there are any extra characters
                if (preg_match('/^[\w\-]+/', $member_id_raw, $matches)) {
                    $member_id = trim($matches[0]);
                }
            }
            
            // Ensure member_id is not empty after cleaning
            if (empty($member_id)) {
                $member_id = $member_id_raw; // Fallback to original if cleaning failed
            }
            
            // Validate amounts
            $principal_balance = $principal_balance ? floatval($principal_balance) : 0;
            $interest_balance = $interest_balance ? floatval($interest_balance) : 0;
            $penalty_balance = $penalty_balance ? floatval($penalty_balance) : 0;
            $loan_amount = $loan_amount ? floatval($loan_amount) : null;
            $monthly_amort = $monthly_amort ? floatval($monthly_amort) : null;
            $term = $term ? intval($term) : null;
            $total_balance = $principal_balance + $interest_balance + $penalty_balance;
            
            // Check if at least one amount is greater than zero
            if ($total_balance <= 0) {
                $this->data['warning'] = lang('loan_beginning_balance_amount_required');
            } else if (empty($member_id)) {
                $this->data['warning'] = lang('loan_beginning_balance_member_not_found');
            } else {
                // Check if member exists - ensure member_id is trimmed and not empty
                $member_id = trim($member_id);
                if (empty($member_id)) {
                    $this->data['warning'] = lang('loan_beginning_balance_member_not_found');
                } else {
                    $member_query = $this->member_model->member_basic_info(null, null, $member_id);
                    $member = $member_query->row();
                    if ($member_query->num_rows() == 0 || !$member) {
                        $this->data['warning'] = lang('loan_beginning_balance_member_not_found');
                    } else {
                        // Check if loan product exists
                        if (!$this->loan_model->is_loan_product_exist($loan_product_id)) {
                            $this->data['warning'] = lang('loan_beginning_balance_product_not_found');
                        } else {
                            $data = array(
                                'fiscal_year_id' => $fiscal_year_id,
                                'member_id' => $member_id,
                                'loan_id' => $loan_id ? $loan_id : null,
                                'loan_product_id' => $loan_product_id,
                                'principal_balance' => $principal_balance,
                                'interest_balance' => $interest_balance,
                                'penalty_balance' => $penalty_balance,
                                'total_balance' => $total_balance,
                                'disbursement_date' => $disbursement_date ? $disbursement_date : null,
                                'loan_amount' => $loan_amount,
                                'monthly_amort' => $monthly_amort,
                                'last_date_paid' => $last_date_paid ? $last_date_paid : null,
                                'term' => $term,
                                'description' => $description
                            );
                            
                            if (!$id) {
                                // Check if already exists
                                if ($this->loan_model->check_loan_beginning_balance_exists($fiscal_year_id, $member_id, $loan_product_id)) {
                                    $this->data['warning'] = lang('loan_beginning_balance_already_exists');
                                } else {
                                    $result = $this->loan_model->loan_beginning_balance_create($data);
                                    if ($result) {
                                        $this->session->set_flashdata('message', lang('loan_beginning_balance_create_success'));
                                        redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $fiscal_year_id, 'refresh');
                                    } else {
                                        $this->data['warning'] = lang('loan_beginning_balance_create_fail');
                                    }
                                }
                            } else {
                                // Check if another record exists with same fiscal year, member and product
                                $existing = $this->loan_model->loan_beginning_balance_list($fiscal_year_id)->result();
                                $duplicate = false;
                                foreach ($existing as $ex) {
                                    if ($ex->id != $id && $ex->member_id == $member_id && $ex->loan_product_id == $loan_product_id) {
                                        $duplicate = true;
                                        break;
                                    }
                                }
                                
                                if ($duplicate) {
                                    $this->data['warning'] = lang('loan_beginning_balance_already_exists');
                                } else {
                                    $result = $this->loan_model->loan_beginning_balance_update($data, $id);
                                    if ($result) {
                                        $this->session->set_flashdata('message', lang('loan_beginning_balance_update_success'));
                                        redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $fiscal_year_id, 'refresh');
                                    } else {
                                        $this->data['warning'] = lang('loan_beginning_balance_update_fail');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $this->data['content'] = 'loan/loan_beginning_balance_form';
        $this->load->view('template', $this->data);
    }

    function loan_beginning_balance_delete($id) {
        $id = decode_id($id);
        
        $balance = $this->loan_model->loan_beginning_balance_list(null, $id)->row();
        
        if (!$balance) {
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_not_found'));
            redirect(current_lang() . '/loan/loan_beginning_balance_list', 'refresh');
            return;
        }
        
        // Check if already posted
        if ($balance->posted == 1) {
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_cannot_delete_posted'));
            redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
            return;
        }
        
        $result = $this->loan_model->loan_beginning_balance_delete($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('loan_beginning_balance_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_delete_fail'));
        }
        
        redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
    }

    function loan_beginning_balance_export() {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Disable CodeIgniter's output completely
        $this->output->enable_profiler(FALSE);
        // Prevent CodeIgniter from sending output
        $this->output->set_output('');
        
        // Check permission
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        
        if (!has_role(5, 'Loan_beginning_balances')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }
        
        // Load Excel library
        $this->load->library('excel');
        
        // Get fiscal year ID from GET or POST
        $selected_fiscal_year_id = $this->input->get('fiscal_year_id');
        if (!$selected_fiscal_year_id) {
            $selected_fiscal_year_id = $this->input->post('fiscal_year_id');
        }
        
        if (!$selected_fiscal_year_id) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_select_fiscal_year'));
            redirect(current_lang() . '/loan/loan_beginning_balance_list', 'refresh');
            exit();
        }
        
        // Get loan product filter from GET or POST
        $selected_loan_product_id = $this->input->get('loan_product_id');
        if (!$selected_loan_product_id) {
            $selected_loan_product_id = $this->input->post('loan_product_id');
        }
        if (!$selected_loan_product_id) {
            $selected_loan_product_id = 'all'; // Default to 'all'
        }
        
        // Get fiscal year info
        $fiscal_year = $this->setting_model->fiscal_year_list($selected_fiscal_year_id)->row();
        if (!$fiscal_year) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'Fiscal year not found');
            redirect(current_lang() . '/loan/loan_beginning_balance_list', 'refresh');
            exit();
        }
        
        // Get balances with loan product filter
        $balances = $this->loan_model->loan_beginning_balance_list($selected_fiscal_year_id, null, $selected_loan_product_id)->result();
        
        // Pre-fetch member names and product info
        $member_names = array();
        $product_info = array();
        foreach ($balances as $balance) {
            if (!isset($member_names[$balance->member_id])) {
                try {
                    $member_names[$balance->member_id] = $this->member_model->member_name($balance->member_id);
                } catch (Exception $e) {
                    $member_names[$balance->member_id] = 'Unknown';
                }
            }
            if (!isset($product_info[$balance->loan_product_id])) {
                $product = $this->setting_model->loanproduct($balance->loan_product_id)->row();
                $product_info[$balance->loan_product_id] = $product ? $product->name : '-';
            }
        }
        
        // Check if we have data
        if (empty($balances) || !is_array($balances) || count($balances) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $selected_fiscal_year_id, 'refresh');
            exit();
        }
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Loan Beginning Balances")
                                     ->setSubject("Loan Beginning Balances Export")
                                     ->setDescription("Loan Beginning Balances exported from " . company_info()->name);
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Loan Beginning Balances');
        
        // Add header info
        $sheet->setCellValue('A1', 'Fiscal Year: ' . $fiscal_year->name);
        $sheet->setCellValue('A2', 'Period: ' . date('M d, Y', strtotime($fiscal_year->start_date)) . ' - ' . date('M d, Y', strtotime($fiscal_year->end_date)));
        $sheet->setCellValue('A3', 'Export Date: ' . date('Y-m-d H:i:s'));
        
        // Set column headers (starting from row 5)
        $row = 5;
        $sheet->setCellValue('A' . $row, lang('sno'));
        $sheet->setCellValue('B' . $row, lang('loan_beginning_balance_member_id'));
        $sheet->setCellValue('C' . $row, lang('member_name'));
        $sheet->setCellValue('D' . $row, lang('loan_beginning_balance_loan_product'));
        $sheet->setCellValue('E' . $row, lang('loan_beginning_balance_loan_id'));
        $sheet->setCellValue('F' . $row, lang('loan_beginning_balance_loan_amount'));
        $sheet->setCellValue('G' . $row, lang('loan_beginning_balance_monthly_amort'));
        $sheet->setCellValue('H' . $row, lang('loan_beginning_balance_term'));
        $sheet->setCellValue('I' . $row, lang('loan_beginning_balance_last_date_paid'));
        $sheet->setCellValue('J' . $row, lang('loan_beginning_balance_disbursement_date'));
        $sheet->setCellValue('K' . $row, lang('loan_beginning_balance_principal'));
        $sheet->setCellValue('L' . $row, lang('loan_beginning_balance_interest'));
        $sheet->setCellValue('M' . $row, lang('loan_beginning_balance_penalty'));
        $sheet->setCellValue('N' . $row, lang('loan_beginning_balance_total'));
        $sheet->setCellValue('O' . $row, lang('status'));
        
        // Style header row
        $sheet->getStyle('A' . $row . ':O' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':O' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A' . $row . ':O' . $row)->getFill()->getStartColor()->setARGB('FFCCCCCC');
        $sheet->getStyle('A' . $row . ':O' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Add data rows
        $row = 6;
        $i = 1;
        foreach ($balances as $balance) {
            $member_info = isset($member_names[$balance->member_id]) ? $member_names[$balance->member_id] : 'Unknown';
            $product_name = isset($product_info[$balance->loan_product_id]) ? $product_info[$balance->loan_product_id] : '-';
            $status = ($balance->posted == 1) ? lang('loan_beginning_balance_posted') : lang('loan_beginning_balance_not_posted');
            
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $balance->member_id);
            $sheet->setCellValue('C' . $row, $member_info);
            $sheet->setCellValue('D' . $row, $product_name);
            $sheet->setCellValue('E' . $row, $balance->loan_id ? $balance->loan_id : '-');
            $sheet->setCellValue('F' . $row, $balance->loan_amount ? number_format($balance->loan_amount, 2) : '-');
            $sheet->setCellValue('G' . $row, $balance->monthly_amort ? number_format($balance->monthly_amort, 2) : '-');
            $sheet->setCellValue('H' . $row, $balance->term ? $balance->term . ' months' : '-');
            $sheet->setCellValue('I' . $row, $balance->last_date_paid ? date('d-m-Y', strtotime($balance->last_date_paid)) : '-');
            $sheet->setCellValue('J' . $row, $balance->disbursement_date ? date('d-m-Y', strtotime($balance->disbursement_date)) : '-');
            $sheet->setCellValue('K' . $row, number_format($balance->principal_balance, 2));
            $sheet->setCellValue('L' . $row, number_format($balance->interest_balance, 2));
            $sheet->setCellValue('M' . $row, number_format($balance->penalty_balance, 2));
            $sheet->setCellValue('N' . $row, number_format($balance->total_balance, 2));
            $sheet->setCellValue('O' . $row, $status);
            
            // Right align numeric columns
            $sheet->getStyle('F' . $row . ':N' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set filename - ensure .xls extension
        $fiscal_year_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fiscal_year->name);
        $filename = 'Loan_Beginning_Balances_' . $fiscal_year_name . '_' . date('Y-m-d_His') . '.xls';
        
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

    function loan_beginning_balance_post($id) {
        $id = decode_id($id);
        
        $balance = $this->loan_model->loan_beginning_balance_list(null, $id)->row();
        
        if (!$balance) {
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_not_found'));
            redirect(current_lang() . '/loan/loan_beginning_balance_list', 'refresh');
            return;
        }
        
        if ($balance->posted == 1) {
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_already_posted'));
            redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
            return;
        }
        
        $result = $this->loan_model->loan_beginning_balance_post_to_ledger($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('loan_beginning_balance_post_success'));
        } else {
            $this->session->set_flashdata('warning', lang('loan_beginning_balance_post_fail'));
        }
        
        redirect(current_lang() . '/loan/loan_beginning_balance_list?fiscal_year_id=' . $balance->fiscal_year_id, 'refresh');
    }

    // Member Autosuggest Methods for Loan Beginning Balance
    function autosuggest_member($id) {
        $pin = current_user()->PIN;
        $q = strtolower($_GET["q"]);
        if (!$q)
            return;
        
        $auto = $this->db->query("SELECT PID, firstname, middlename, lastname, member_id FROM members WHERE PIN='$pin' AND (PID LIKE '$q%' OR member_id LIKE '$q%' OR firstname LIKE '$q%' OR lastname LIKE '$q%')")->result();

        foreach ($auto as $key => $value) {
            if ($id == 'pid') {
                echo $value->PID . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
            } else if ($id == 'mid') {
                echo $value->member_id . ' - ' . $value->firstname . ' ' . $value->middlename . ' ' . $value->lastname . "\n";
            }
        }
    }

    function search_member() {
        $value = trim($this->input->post('value'));
        $column = trim($this->input->post('column'));
        
        // Validate input
        if (empty($value) || empty($column)) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        // Handle autocomplete format: "2005-00173 - BRENDALOU SALES" or just "2005-00173"
        // Check if value contains " - " (space-dash-space) which separates ID from name
        if (strpos($value, ' - ') !== false) {
            // Extract everything before " - " as the ID
            $explode = explode(' - ', $value);
            $value = trim($explode[0]);
        } else {
            // If no " - " separator, the value might be just the ID or formatted differently
            // Try to extract just the ID part if it contains dashes
            if (preg_match('/^[\d\-]+/', $value, $matches)) {
                $value = trim($matches[0]);
            }
        }
        
        if (empty($value)) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        $pid = null;
        $member_id = null;
        $error = '';
        if ($column == 'PID') {
            $pid = trim($value);
            $error = lang('invalid_PID');
        } else if ($column == 'MID') {
            $member_id = trim($value);
            $error = lang('invalid_member_id');
        } else {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = lang('invalid_member_id');
            echo json_encode($status);
            return;
        }
        
        // Ensure values are not empty after trimming
        if (($column == 'PID' && empty($pid)) || ($column == 'MID' && empty($member_id))) {
            $status = array();
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
            return;
        }
        
        // Query member using member_model method
        $member_query = $this->member_model->member_basic_info(null, $pid, $member_id);
        $member = $member_query->row();

        $status = array();
        // Check if member exists and has valid PID
        if ($member_query->num_rows() > 0 && $member && isset($member->PID) && !empty($member->PID)) {
            $contact = $this->member_model->member_contact($member->PID);
            $status['success'] = 'Y';
            $status['data'] = $member;
            $status['contact'] = $contact;
            echo json_encode($status);
        } else {
            $status['success'] = 'N';
            $status['error'] = $error;
            echo json_encode($status);
        }
    }

}

?>
