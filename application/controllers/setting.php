<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of clientaccount
 *
 * @author miltone
 */
class Setting extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_setting');
        $this->lang->load('setting');
        $this->load->model('setting_model');
        $this->load->model('finance_model');
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
     *  @author Helma Technologies Tanzania
     *  @Contact : info@helmatechnologies.com
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

    function index() {
        
    }

    
    function mobile_notification_delete($id){
        $this->db->delete('mobile_notification',array('id'=>$id));
         $this->session->set_flashdata('message','Information deleted successfully');
         redirect(current_lang().'/setting/mobile_notification','refresh');
    }
            
    function mobile_notification($id=null){
        $this->data['title'] = 'Mobile Notifications'; 
        $this->data['id'] = $id;
        $this->form_validation->set_rules('group', 'Group', 'xss_clean|required');
        $this->form_validation->set_rules('mobile', 'Mobile', 'xss_clean|required|valid_phone');
        $this->form_validation->set_rules('pre_phone1', '', '');
         if ($this->form_validation->run() == TRUE) {
           $group = $this->input->post('group');  
           $mobile = $this->input->post('pre_phone1'). trim($this->input->post('mobile')); 
           if(is_null($id)){
               $this->db->insert('mobile_notification',array('group'=>$group,'mobile'=>$mobile));
               $this->session->set_flashdata('message','Information saved successfully');
           }else if(!is_null($id)){
              $this->db->update('mobile_notification',array('group'=>$group,'mobile'=>$mobile),array('id'=>$id)); 
              $this->session->set_flashdata('message','Information updated successfully');
           }
           
           redirect(current_lang().'/setting/mobile_notification','refresh');
         }
        
        if(!is_null($id)){
            $this->data['smscontact'] = $this->db->get_where('mobile_notification',array('id'=>$id))->row();
        }

        $this->db->order_by('group','ASC');
        $this->data['list'] = $this->db->get('mobile_notification')->result();
        $this->data['content'] = 'setting/mobile_notification';
        $this->load->view('template', $this->data);
    }
            
    function contribution_minimum() {
        $this->data['title'] = lang('contribution_minimum_setting1');
        if ($this->input->post('amount')) {
            $_POST['amount'] = str_replace(',', '', $_POST['amount']);
            $_POST['charge'] = str_replace(',', '', $_POST['charge']);
        }


        $this->form_validation->set_rules('amount', lang('contribution_minimum_amount'), 'xss_clean|required|numeric');
        $this->form_validation->set_rules('charge', lang('contribution_minimum_overdueamount'), 'xss_clean|required|numeric');
        $this->form_validation->set_rules('capital_build_up_account', 'Capital Build Up Account', 'xss_clean|required');

        if ($this->form_validation->run() == TRUE) {
            $global_info = array(
                'amount' => trim($this->input->post('amount')),
                'overdue_amount' => trim($this->input->post('charge')),
                'capital_build_up_account' => trim($this->input->post('capital_build_up_account')),
                'PIN' =>  current_user()->PIN
            );

            $process = $this->setting_model->setting_global_contibution($global_info);
            if ($process) {
                $this->session->set_flashdata('message', lang('contribution_minimum_success'));
                redirect(current_lang() . '/setting/contribution_minimum/', 'refresh');
            } else {
                $this->data['warning'] = lang('contribution_minimum_fail');
            }
        }

        // Get account list and build hierarchical structure with indentation
        $account_list_raw = $this->finance_model->account_chart_by_accounttype(array(10, 40));
        
        // Build hierarchical tree structure with indentation based on account number
        $account_tree = array();
        foreach ($account_list_raw as $type_key => $type_data) {
            $accounts = $type_data['data'];
            
            // Determine level based on account number structure (ladderize)
            foreach ($accounts as $account) {
                // Convert account to string and pad if needed
                $account_str = str_pad((string)$account->account, 7, '0', STR_PAD_LEFT);
                $level = 0;
                
                // Level detection based on trailing zeros or account structure
                // Accounts ending in 0000 = Level 0 (Main)
                // Accounts ending in 00 = Level 1 (Sub)
                // Accounts ending in 0 = Level 2 (Detail)
                // Other accounts = Level 3 (Sub-detail)
                if (strlen($account_str) >= 4) {
                    $last_4 = substr($account_str, -4);
                    $last_2 = substr($account_str, -2);
                    $last_1 = substr($account_str, -1);
                    
                    if ($last_4 == '0000') {
                        $level = 0; // Main account
                    } else if ($last_2 == '00') {
                        $level = 1; // Sub-account
                    } else if ($last_1 == '0') {
                        $level = 2; // Detail account
                    } else {
                        $level = 3; // Sub-detail account
                    }
                }
                // Ensure display_level is set
                $account->display_level = (int)$level;
            }
            
            // Sort by account number to maintain hierarchy
            usort($accounts, function($a, $b) {
                return (int)$a->account - (int)$b->account;
            });
            
            $account_tree[$type_key] = array(
                'info' => $type_data['info'],
                'data' => $accounts
            );
        }
        
        $this->data['account_list'] = $account_tree;
        $this->data['content'] = 'setting/minimum_contribution';
        $this->load->view('template', $this->data);
    }

    function saving_account_typecreate($id = null) {
        if (is_null($id)) {
            $this->data['title'] = lang('create_saving_account');
        } else {
            $this->data['title'] = lang('saving_account_edit');
        }
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }

        // Handle comma-formatted monetary values before validation
        // Remove commas from all monetary fields if they exist in POST
        if ($this->input->post('minimum_amount') !== FALSE) {
            $_POST['minimum_amount'] = str_replace(',', '', $this->input->post('minimum_amount'));
        }
        if ($this->input->post('max_withdrawal') !== FALSE) {
            $_POST['max_withdrawal'] = str_replace(',', '', $this->input->post('max_withdrawal'));
        }
        if ($this->input->post('interest_rate') !== FALSE) {
            $_POST['interest_rate'] = str_replace(',', '', $this->input->post('interest_rate'));
        }
        if ($this->input->post('min_deposit') !== FALSE) {
            $_POST['min_deposit'] = str_replace(',', '', $this->input->post('min_deposit'));
        }
        $this->form_validation->set_rules('account_name', lang('account_name'), 'xss_clean|required');
        $this->form_validation->set_rules('account_description', lang('account_description'), 'xss_clean|required');
        $this->form_validation->set_rules('minimum_amount', lang('account_min_amount'), 'xss_clean|required|numeric');
        $this->form_validation->set_rules('max_withdrawal', lang('account_max_withdrawal'), 'xss_clean|required|numeric');
        $this->form_validation->set_rules('interest_rate', lang('account_interest_rate'), 'xss_clean|required|numeric');
        $this->form_validation->set_rules('min_deposit', lang('account_min_deposit'), 'xss_clean|numeric');
        $this->form_validation->set_rules('account_setup', 'Account Setup', 'xss_clean|required');
        if ($this->form_validation->run() == TRUE) {
            $account_info = array(
                'name' => trim($this->input->post('account_name')),
                'description' => trim($this->input->post('account_description')),
                'min_amount' => trim($this->input->post('minimum_amount')),
                'max_withdrawal' => trim($this->input->post('max_withdrawal')),
                'interest_rate' => trim($this->input->post('interest_rate')),
                'min_deposit' => trim($this->input->post('min_deposit')),
                'account_setup' => trim($this->input->post('account_setup')),
                'PIN' =>  current_user()->PIN
            );

            $process = $this->setting_model->saving_account_typecreate($account_info, $id);
            if ($process) {
                $this->session->set_flashdata('message', lang('saving_account_process_success'));
                redirect(current_lang() . '/setting/saving_account_typecreate/' . $this->data['id'], 'refresh');
            } else {
                $this->data['warning'] = lang('saving_account_process_fail');
            }
        }

        if (!is_null($id)) {
            $this->data['account'] = $this->setting_model->saving_account_typelist($id)->row();
        }
        
        // Get account list and build hierarchical structure with indentation
        $account_list_raw = $this->finance_model->account_chart_by_accounttype(array(10, 40));
        
        // Build hierarchical tree structure with indentation based on account number
        $account_tree = array();
        foreach ($account_list_raw as $type_key => $type_data) {
            $accounts = $type_data['data'];
            
            // Determine level based on account number structure (ladderize)
            foreach ($accounts as $account) {
                // Convert account to string and pad if needed
                $account_str = str_pad((string)$account->account, 7, '0', STR_PAD_LEFT);
                $level = 0;
                
                // Level detection based on trailing zeros or account structure
                // Accounts ending in 0000 = Level 0 (Main)
                // Accounts ending in 00 = Level 1 (Sub)
                // Accounts ending in 0 = Level 2 (Detail)
                // Other accounts = Level 3 (Sub-detail)
                if (strlen($account_str) >= 4) {
                    $last_4 = substr($account_str, -4);
                    $last_2 = substr($account_str, -2);
                    $last_1 = substr($account_str, -1);
                    
                    if ($last_4 == '0000') {
                        $level = 0; // Main account
                    } else if ($last_2 == '00') {
                        $level = 1; // Sub-account
                    } else if ($last_1 == '0') {
                        $level = 2; // Detail account
                    } else {
                        $level = 3; // Sub-detail account
                    }
                }
                // Ensure display_level is set
                $account->display_level = (int)$level;
            }
            
            // Sort by account number to maintain hierarchy
            usort($accounts, function($a, $b) {
                return (int)$a->account - (int)$b->account;
            });
            
            $account_tree[$type_key] = array(
                'info' => $type_data['info'],
                'data' => $accounts
            );
        }
        
        $this->data['account_list'] = $account_tree;
        $this->data['content'] = 'setting/saving_account_typecreate';
        $this->load->view('template', $this->data);
    }

    function saving_account_typelist() {
        $this->data['title'] = lang('saving_account_typelist');

        $this->data['saving_acc_list'] = $this->setting_model->saving_account_typelist()->result();
        $this->data['content'] = 'setting/saving_account_typelist';
        $this->load->view('template', $this->data);
    }

    // function to create/ edit client (saccoss account)
    /* function client_account() {
      $this->data['title'] = lang('setting_create_clientaccount');

      $this->form_validation->set_rules('pre_phone', '', 'xss_clean');
      $this->form_validation->set_rules('companyname', lang('clientaccount_label_name'), 'required');
      $this->form_validation->set_rules('postaladdress', lang('clientaccount_label_postal_address'), 'xss_clean');
      $this->form_validation->set_rules('physicaladdress', lang('clientaccount_label_physical_address'), 'xss_clean|required');
      $this->form_validation->set_rules('phone', lang('clientaccount_label_phone'), 'xss_clean|required|valid_phone');
      $this->form_validation->set_rules('fax', lang('clientaccount_label_fax'), 'xss_clean');
      $this->form_validation->set_rules('email', lang('clientaccount_label_email'), 'xss_clean|required|valid_email');


      // upload files
      $upload_photo = false;
      $file_name = 0;
      if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
      $extension = $this->getExtension($_FILES['file']['name']);
      if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
      $this->data['logo_error'] = 'Invalid format, only jpg,jpeg,png and gif is supported';
      $upload_photo = FALSE;
      } else {
      $file_name = $this->upload_file($_FILES, 'file', 'logo');
      $upload_photo = TRUE;
      }
      } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
      $this->data['logo_error'] = 'The ' . lang('clientaccount_label_logo') . ' field is required';
      $upload_photo = FALSE;
      }

      if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
      if ($file_name != 0) {
      //insert data here

      $clientdata = array(
      'name' => trim($this->input->post('companyname')),
      'box' => trim($this->input->post('postaladdress')),
      'address' => trim($this->input->post('physicaladdress')),
      'mobile' => trim($this->input->post('pre_phone')) . trim($this->input->post('phone')),
      'fax' => trim($this->input->post('fax')),
      'email' => trim($this->input->post('email')),
      'logo' => $file_name,
      );

      $create_account = $this->setting_model->create_client_account($clientdata);
      if ($create_account) {
      $this->session->set_flashdata('message', lang('clientaccount_create_succes'));
      redirect(current_lang() . '/setting/client_account', 'refresh');
      } else {
      $this->data['warning'] = lang('clientaccount_create_fail');
      }
      } else {
      $this->data['warning'] = lang('clientaccount_create_fail');
      }
      }

      $this->data['content'] = 'setting/newclient';
      $this->load->view('template', $this->data);
      }

     */

    function companyinfo_edit($id) {
        $this->data['PIN'] = $id;
        //decode it first
        $id = decode_id($id);

        $this->data['title'] = lang('setting_create_clientaccount_edit');
        $this->form_validation->set_rules('pre_phone', '', 'xss_clean');
        $this->form_validation->set_rules('companyname', lang('clientaccount_label_name'), 'required');
        $this->form_validation->set_rules('postaladdress', lang('clientaccount_label_postal_address'), 'xss_clean');
        $this->form_validation->set_rules('physicaladdress', lang('clientaccount_label_physical_address'), 'xss_clean|required');
        $this->form_validation->set_rules('phone', lang('clientaccount_label_phone'), 'xss_clean|required|valid_phone');
        $this->form_validation->set_rules('fax', lang('clientaccount_label_fax'), 'xss_clean');
        $this->form_validation->set_rules('email', lang('clientaccount_label_email'), 'xss_clean|required|valid_email');


        // upload files
        $upload_photo = true;
        $file_name = 0;
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
                $this->data['logo_error'] = lang('client_logo_error');
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'logo');
                $upload_photo = TRUE;
            }
        }

        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {

            //update data here

            $clientdata = array(
                'name' => trim($this->input->post('companyname')),
                'box' => trim($this->input->post('postaladdress')),
                'address' => trim($this->input->post('physicaladdress')),
                'mobile' => trim($this->input->post('pre_phone')) . trim($this->input->post('phone')),
                'fax' => trim($this->input->post('fax')),
                'email' => trim($this->input->post('email')),
            );

            if ($file_name != 0) {
                $clientdata['logo'] = $file_name;
            }

            $create_account = $this->setting_model->update_companyinfo($clientdata, $id);
            if ($create_account) {
                $this->session->set_flashdata('message', lang('clientaccount_edit_succes'));
                redirect(current_lang() . '/setting/companyinfo_edit/' . encode_id($id), 'refresh');
            } else {
                $this->data['warning'] = lang('clientaccount_edit_fail');
            }
        }

        $this->data['account'] = $this->setting_model->company_infomation()->row();
        $this->data['content'] = 'setting/editclientaccount';
        $this->load->view('template', $this->data);
    }

    function companyinfo_view($id = null) {

        $this->data['title'] = lang('seting_accountinfo');

        $this->data['account'] = $this->setting_model->company_infomation()->row();
        $this->data['content'] = 'setting/clientaccountview';
        $this->load->view('template', $this->data);
    }

    /*  function clientaccount_list() {

      $this->data['title'] = lang('seting_clientaccountlist');

      $this->data['account'] = $this->setting_model->client_accounts()->result();
      $this->data['content'] = 'setting/clientaccountlist';
      $this->load->view('template', $this->data);
      } */

    function share_setup() {
        $this->data['title'] = lang('setting_share_setup');
        if (isset($_POST)) {
            foreach ($_POST as $key => $value) {
                $_POST[$key] = str_replace(',', '', $value);
            }
        }
        $this->form_validation->set_rules('share_value', lang('share_current_value'), 'xss_clean|required|numeric');
        $this->form_validation->set_rules('share_minimum', lang('share_minimum'), 'xss_clean|required|integer');
        $this->form_validation->set_rules('share_maximum', lang('share_maximum'), 'xss_clean|required|integer');

        if ($this->form_validation->run() == TRUE) {
            $minumum = trim($this->input->post('share_minimum'));
            $maxmum = trim($this->input->post('share_maximum'));
            $share_value = trim($this->input->post('share_value'));

            if ($maxmum > $minumum) {
                //now add share
                $share = array(
                    'amount' => trim($this->input->post('share_value')),
                    'min_share' => trim($this->input->post('share_minimum')),
                    'max_share' => trim($this->input->post('share_maximum')),
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );

                $setup = $this->setting_model->setup_share($share);
                if ($setup) {
                    $this->session->set_flashdata('message', lang('share_setup_success'));
                    redirect(current_lang() . '/setting/share_setup', 'refresh');
                } else {
                    $this->data['warning'] = lang('share_setup_fail');
                }
            } else {
                $this->data['warning'] = lang('share_max_less_min');
            }
        }

        $this->data['content'] = 'setting/share_setup';
        $this->load->view('template', $this->data);
    }
//Added by Herald
    function mortuary_setup() {
        $this->data['title'] = lang('setting_mortuary_setup');
        if (isset($_POST)) {
            foreach ($_POST as $key => $value) {
                $_POST[$key] = str_replace(',', '', $value);
            }
        }
        $this->form_validation->set_rules('amount', lang('mortuary_amount_deduction'), 'xss_clean|required|double');
        $this->form_validation->set_rules('maintaining_balance', lang('maintaining_balance_amount'), 'xss_clean|required|double');
        $this->form_validation->set_rules('endangered_amount', lang('endangered_amount'), 'xss_clean|required|double');
        $this->form_validation->set_rules('dismember_amount', lang('dismember_amount'), 'xss_clean|required|double');
        
        if ($this->form_validation->run() == TRUE) {
            //$amount = trim($this->input->post('amount'));
            //$maintaining_balance = trim($this->input->post('maintaining_balance'));
            //$share_value = trim($this->input->post('share_value'));
            
            
            $mortuary = array(
                'amount' => trim($this->input->post('amount')),
                'maintaining_balance' => trim($this->input->post('maintaining_balance')),
                'endangered_amount' => trim($this->input->post('endangered_amount')),
                'dismember_amount' => trim($this->input->post('dismember_amount')),
                'PIN' => current_user()->PIN,
            );
            
            $setup = $this->setting_model->setup_mortuary($mortuary);
            if ($setup) {
                $this->session->set_flashdata('message', lang('mortuary_setup_success'));
                redirect(current_lang() . '/setting/mortuary_setup', 'refresh');
            } else {
                $this->data['warning'] = lang('mortuary_setup_fail');
            }
            
        }
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(10, 40));
        
        $this->data['product'] = $this->setting_model->loanproduct()->row();
        $this->data['content'] = 'setting/mortuary_setup';
        $this->load->view('template', $this->data);
    }
    
    function taxcode_registration($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        if (is_null($id)) {
            $this->data['title'] = lang('taxcode_register');
        } else {
            $this->data['title'] = lang('taxcode_edit');
        }

        if (is_null($id)) {
            $this->form_validation->set_rules('code', lang('taxcode'), 'required');
        }

        $this->form_validation->set_rules('description', lang('taxdescription'), '');
        $this->form_validation->set_rules('percent', lang('taxpercent'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $taxcode = trim($this->input->post('code'));
            $tax_info = array(
                'description' => trim($this->input->post('description')),
                'rate' => trim($this->input->post('percent')),
                'PIN' => current_user()->PIN
            );
            $error = 0;
            if (is_null($id)) {
                if (!$this->setting_model->is_tax_exist($taxcode)) {
                    $tax_info['code'] = $taxcode;
                } else {
                    $error = 1;
                }
            }
            if ($error == 0) {
                $create = $this->setting_model->add_taxcode($tax_info, $id);
                if ($create) {
                    $this->session->set_flashdata('message', lang('share_setup_success'));
                    redirect(current_lang() . '/setting/taxcode_registration/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('share_setup_fail');
                }
            } else {
                $this->data['warning'] = lang('tax_code_exist');
            }
        }

        if (!is_null($id)) {
            $this->data['taxinfo'] = $this->setting_model->tax_info($id)->row();
        }

        $this->data['content'] = 'setting/taxcode_registration';
        $this->load->view('template', $this->data);
    }

    function tax_code_list() {
        $this->data['title'] = lang('taxcode_list');
        $this->data['taxlist'] = $this->setting_model->tax_info()->result();
        $this->data['content'] = 'setting/taxcode_list';
        $this->load->view('template', $this->data);
    }

    function additems_invoice($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        if (is_null($id)) {
            $this->data['title'] = lang('salesinvoice_item');
        } else {
            $this->data['title'] = lang('salesinvoice_item_edit');
        }

        if (is_null($id)) {
            $this->form_validation->set_rules('code', lang('salesinvoiceitem_code'), 'required');
        }
        if ($this->input->post('price')) {
            $_POST['price'] = str_replace(',', '', $_POST['price']);
        }
        $this->form_validation->set_rules('description', lang('salesinvoiceitem_description'), 'required');
        $this->form_validation->set_rules('name', lang('salesinvoiceitem_name'), 'required');
        $this->form_validation->set_rules('price', lang('salesinvoiceitem_price'), 'required|numeric');
        $this->form_validation->set_rules('account', lang('salesinvoiceitem_account'), 'required');
        $this->form_validation->set_rules('taxcode', lang('salesinvoiceitem_taxcode'), '');
        $this->form_validation->set_rules('invoicetype', lang('salesinvoiceitem_type'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $itemcode = trim($this->input->post('code'));
            $item_info = array(
                'description' => trim($this->input->post('description')),
                'price' => trim($this->input->post('price')),
                'name' => trim($this->input->post('name')),
                'account' => trim($this->input->post('account')),
                'taxcode' => trim($this->input->post('taxcode')),
                'invoicetype' => trim($this->input->post('invoicetype')),
                'PIN' => current_user()->PIN
            );
            $error = 0;
            if (is_null($id)) {
                if (!$this->setting_model->is_itemcode_exist($itemcode)) {
                    $item_info['code'] = $itemcode;
                } else {
                    $error = 1;
                }
            }
            if ($error == 0) {
                $create = $this->setting_model->additems_invoice($item_info, $id);
                if ($create) {
                    $this->session->set_flashdata('message', lang('share_setup_success'));
                    redirect(current_lang() . '/setting/additems_invoice/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('share_setup_fail');
                }
            } else {
                $this->data['warning'] = lang('salesinvoiceitem_code_exist');
            }
        }

        if (!is_null($id)) {
            $this->data['iteminfo'] = $this->setting_model->item_info($id)->row();
        }
        $this->data['invoicetype_list'] = $this->setting_model->invoicetype()->result();
        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(40, 50));
        $this->data['content'] = 'setting/items_registration';
        $this->load->view('template', $this->data);
    }

    function items_invoice() {
        $this->data['title'] = lang('salesinvoiceitem_itemslist');
        $this->data['item_list'] = $this->setting_model->item_info()->result();
        $this->data['content'] = 'setting/itemslist';
        $this->load->view('template', $this->data);
    }

    function global_setting() {
        $this->data['title'] = lang('global_setting');
        $defaulttext = $this->db->get_where('global_setting',array('PIN'=>current_user()->PIN))->result();
        //if ($this->input->post('SAVEDATA')) {
        foreach ($defaulttext as $key => $value) {

            if ($value->is_number == 1) {
                $this->form_validation->set_rules('field_' . $value->id, str_replace('_', ' ', $value->key), 'numeric');
            } else {
                $this->form_validation->set_rules('field_' . $value->id, str_replace('_', ' ', $value->key), '');
            }
            // $note = $this->input->post('field_' . $value->id);
            //  $this->db->update('company_defaulttext', array('text' => $note), array('id' => $value->id));
        }
        //$this->data['message'] = lang('share_trans_success');
        // }
        if ($this->form_validation->run() == TRUE) {
            foreach ($defaulttext as $key => $value) {
                $note = $this->input->post('field_' . $value->id);
                $this->db->update('global_setting', array('text' => $note), array('id' => $value->id));
            }
            $this->data['message'] = lang('share_trans_success');
        }

        $this->data['default_list'] = $this->db->get_where('global_setting',array('PIN'=>current_user()->PIN))->result();
        $this->data['content'] = 'setting/global_setting';
        $this->load->view('template', $this->data);
    }

    function addloan_product($id = null) {
        $this->data['id'] = $id;

        if (is_null($id)) {
            $this->data['title'] = lang('loanproduct_add');
        } else {
            $this->data['title'] = lang('loanproduct_edit');
            $id = decode_id($id);
        }

        $this->form_validation->set_rules('name', lang('loanproduct_name'), 'required');
        $this->form_validation->set_rules('description', lang('loanproduct_description'), 'required');
        $this->form_validation->set_rules('interval', lang('loanproduct_interval'), 'required');
        $this->form_validation->set_rules('interest_rate', lang('loanproduct_interest'), 'required|numeric');
        $this->form_validation->set_rules('loanproduct_contribution_times', lang('loanproduct_contribution_times'), 'required|integer');
        $this->form_validation->set_rules('interest_method', lang('loanproduct_interest_method'), 'required');
        $this->form_validation->set_rules('penalt_method', lang('loanproduct_penalt_method'), 'required');
        $this->form_validation->set_rules('loan_security_share_min', lang('loanproduct_share'), 'required|integer');
        $this->form_validation->set_rules('loan_security_contribution_min', lang('loanproduct_contribution'), 'required|numeric');
        $this->form_validation->set_rules('loan_security_saving_minimum', lang('loanproduct_saving'), 'required|numeric');
        $this->form_validation->set_rules('maxmum_time', lang('loanproduct_maxmum_time'), 'required|integer');
        $this->form_validation->set_rules('loan_principle_account', lang('loanproduct_account_principle'), 'required');
        $this->form_validation->set_rules('loan_interest_account', lang('loanproduct_account_interest'), 'required');
        $this->form_validation->set_rules('loan_penalt_account', lang('loanproduct_account_penalt'), 'required');
        $this->form_validation->set_rules('penalt_percentage', lang('loanproduct_penalt_percentage'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $productinfo = array(
                'name' => trim($this->input->post('name')),
                'description' => trim($this->input->post('description')),
                'interval' => trim($this->input->post('interval')),
                'interest_rate' => trim($this->input->post('interest_rate')),
                'interest_method' => trim($this->input->post('interest_method')),
                'penalt_method' => trim($this->input->post('penalt_method')),
                'loan_security_share_min' => trim($this->input->post('loan_security_share_min')),
                'loan_security_contribution_min' => trim($this->input->post('loan_security_contribution_min')),
                'loan_security_saving_minimum' => trim($this->input->post('loan_security_saving_minimum')),
                'loan_security_contribution_times' => trim($this->input->post('loanproduct_contribution_times')),
                'maxmum_time' => trim($this->input->post('maxmum_time')),
                'loan_principle_account' => trim($this->input->post('loan_principle_account')),
                'loan_interest_account' => trim($this->input->post('loan_interest_account')),
                'loan_penalt_account' => trim($this->input->post('loan_penalt_account')),
                'penalt_percentage' => trim($this->input->post('penalt_percentage')),
                'PIN' => current_user()->PIN
            );


            $create = $this->setting_model->addloan_product($productinfo, $id);
            if ($create) {
                $this->session->set_flashdata('message', lang('loanproduct_add_success'));
                redirect(current_lang() . '/setting/addloan_product/' . $this->data['id'], 'refresh');
            } else {
                $this->data['warning'] = lang('loanproduct_add_fail');
            }
        }

        if (!is_null($id)) {
            $this->data['product'] = $this->setting_model->loanproduct($id)->row();
        }


        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(10, 40));
        $this->data['penalt_method_list'] = $this->setting_model->penalt_method()->result();
        $this->data['interest_method_list'] = $this->setting_model->interest_method()->result();
        $this->data['interval_list'] = $this->setting_model->intervalinfo()->result();
        $this->data['content'] = 'setting/addloanproduct';
        $this->load->view('template', $this->data);
    }

    function loan_product_list() {
        $this->data['title'] = lang('loan_product_list');
        $this->data['productlist'] = $this->setting_model->loanproduct()->result();
        $this->data['content'] = 'setting/loanproductlist';
        $this->load->view('template', $this->data);
    }

}

?>
