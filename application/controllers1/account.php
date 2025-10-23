<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of account
 *
 * @author miltone
 */
class Account extends CI_Controller{
    //put your code here
    
     //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->data['current_title'] = lang('page_account');
        $this->data['title'] = lang('page_account');
        $this->data['path_title'][] = lang('page_account');
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->load->model('account_model');
        $this->load->library('maildata');
        $this->lang->load('account');
    }
    
    function reseller_account() {
       $this->data['title'] = lang('reseller_account_list');
      $this->data['path_title'][] = lang('reseller_account_list');
       $this->data['resellers'] = $this->account_model->reseller_account_list()->result();
       
        $this->data['content'] = 'account/reseller_account_list';
        $this->load->view('template', $this->data);
    }

    function create_resseller_account($id = null) {
        $this->data['id'] = $id;
        $this->data['title'] = is_null($id) ? lang('setting_reseller_account_create') : lang('setting_reseller_account_create_edit');
        $this->data['path_title'][] = lang('setting_reseller_account');

        $this->form_validation->set_rules('fname', lang('index_fname_th'), 'required');
        $this->form_validation->set_rules('lname', lang('index_lname_th'), 'required');
        if (is_null($id)) {
            $this->form_validation->set_rules('email', lang('index_email_th'), 'required|valid_email');
        }
        $this->form_validation->set_rules('phone', lang('mobile'), 'required|valid_phone');
        $this->form_validation->set_rules('company', rtrim(lang('create_user_company_label'), ':'), '');
        $this->form_validation->set_rules('address', lang('address'), '');
        $this->form_validation->set_rules('code', 'Code', '');
        $this->form_validation->set_rules('reseller', 'is super user?', '');

        if ($this->form_validation->run() == true) {
            $firstname = ucwords(strtolower(trim($this->input->post('fname'))));
            $lastname = ucwords(strtolower(trim($this->input->post('lname'))));
            $email = strtolower(trim($this->input->post('email')));
            $company = strtoupper(trim($this->input->post('company')));
            $address = trim($this->input->post('address'));
            $reseller = trim($this->input->post('reseller'));
            $phone = $this->input->post('code') . str_replace(' ', '', $this->input->post('phone'));

            $array = array(
                'is_super' => $reseller,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'mobile' => $phone,
                'email' => $email,
                'company' => $company,
                'address' => $address,
                'createdby' => current_user()->id
            );



            //first insert
            if (is_null($id)) {
                if (!$this->ion_auth_model->username_check($email)) {

                    $create = $this->account_model->create_reseller_account($array, $id);
                    if (is_null($id)) {
                        if ($create) {

                            $username = $email;
                            $email = $email;
                            $password = alphaID($create, FALSE, 8);

                            // create account for login
                            $additional_data = array(
                                'first_name' => $firstname,
                                'last_name' => $lastname,
                                'company' => $company,
                                'reseller' => 1,
                                'super_user' => $reseller,
                                'phone' => $phone,
                                'address' => $address,
                                'reffid' => $create,
                                'refftable' => 'reseller_account',
                                'defaultpass' => $password
                            );

                            if ($this->ion_auth->register($username, $password, $email, $additional_data, array(3))) {


                                $name_reseller = $firstname . ' ' . $lastname;

                                $this->maildata->send_login_credential($name_reseller, $email, $password);


                                $this->session->set_flashdata('message', lang('account_creation_successful'));
                                redirect('account/create_resseller_account', 'refresh');
                            }
                        } else {
                            $this->data['warning'] = lang('account_creation_unsuccessful');
                        }
                    } else {
                        $this->session->set_flashdata('message', lang('update_successful'));
                        redirect('account/create_resseller_account', 'refresh');
                    }
                } else {

                    $this->data['warning'] = lang('account_creation_duplicate_email');
                }
            } else {

                $array = array(
                    'is_super' => $reseller,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'mobile' => $phone,
                    'company' => $company,
                    'address' => $address,
                );
                $create = $this->account_model->create_reseller_account($array, $id);
                $this->session->set_flashdata('message', lang('update_successful'));
                redirect('account/reseller_account', 'refresh');
            }
        }

        if (!is_null($id)) {
           $this->data['resellerinfo'] = reseller_info($id);
        }

        $this->data['content'] = 'account/new_reseller';
        $this->load->view('template', $this->data);
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

    /*function select_client_religion() {
        $this->data['title'] = lang('myclient_account_create');
        $this->data['path_title'][] = lang('myclient_account_create');
        $this->data['religions'] = $this->setting_model->religion_list()->result();
        $this->data['content'] = 'client_account/select_religion';
        $this->load->view('template', $this->data);
    }

    function create_client_account($religion_id) {
        if (is_null($religion_id)) {
            redirect(current_lang() . '/dashboard', 'refresh');
        }
        $this->data['religion_id'] = $religion_id;
        $this->data['title'] = lang('myclient_account_create');
        $this->data['path_title'][] = lang('myclient_account_create');
        $this->data['religioninfo'] = $this->setting_model->religion_list($religion_id)->row();
        $this->data['content'] = "../../tree/index";
        $this->load->view('template', $this->data);
    }
*/
    function client_account_create($id = null) {
        $this->data['id'] = $id;
        $this->data['title'] = is_null($id) ? lang('myclient_account_create') : lang('myclient_account_edit');
        $this->data['path_title'][] = is_null($id) ? lang('myclient_account_create') : lang('myclient_account_edit');

        $this->form_validation->set_rules('name', lang('client_account_name'), 'required');
        $this->form_validation->set_rules('address', lang('client_account_address'), 'required');
        $this->form_validation->set_rules('landline', lang('client_account_landline'), '');
        $this->form_validation->set_rules('mobile', lang('client_account_mobile'), 'required|valid_phone');
        $this->form_validation->set_rules('code', 'Code', '');
        $this->form_validation->set_rules('email', lang('create_user_email_label'), 'valid_email');
        $this->form_validation->set_rules('website', lang('client_account_website'), '');
        $this->form_validation->set_rules('currency', lang('client_account_currency'), 'required');
        $this->form_validation->set_rules('sms_token', lang('client_account_sms_token'), '');

        if (is_null($id)) {
            $this->form_validation->set_rules('fname', lang('create_user_fname_label'), 'required');
            $this->form_validation->set_rules('lname', lang('create_user_lname_label'), 'required');
            $this->form_validation->set_rules('admin_email', lang('create_user_email_label'), 'required|valid_email|is_unique[users.username]');
        }


        // upload files
        $upload_photo = true;
        $file_name = 0;
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
                $this->data['logo_error'] = lang('client_account_logo_error');
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'logo');
                $upload_photo = TRUE;
            }
        }



        if ($this->form_validation->run() == true && $upload_photo == TRUE) {
          
            $client_array = array(
                'name' => trim($this->input->post('name')),
                'address' => trim($this->input->post('address')),
                'landline' => trim($this->input->post('landline')),
                'mobile' => trim($this->input->post('code')) . trim(str_replace(' ', '', $this->input->post('mobile'))),
                'website' => trim($this->input->post('website')),
                'email' => trim($this->input->post('email')),
                'currency' => trim($this->input->post('currency')),
                'sms_token' => trim($this->input->post('sms_token')),
                'sms_token' => trim($this->input->post('sms_token')),
            );
            
            if($file_name != 0){
                $client_array['logo'] = $file_name;
            }
            if(is_null($id)){
            $login_info=array(
                'first_name' => trim($this->input->post('fname')),
                'last_name' => trim($this->input->post('lname')),
                'email' => trim($this->input->post('admin_email')),
                'company' => trim($this->input->post('name')),
                'phone' => $client_array['mobile'],
            );
            }else{
             $login_info=array();   
            }
            
            if($this->account_model->create_client_account($client_array,$login_info,$id)){
                if(is_null($id)){
                $this->session->set_flashdata('message',  lang('account_creation_successful'));
                }else{
                 $this->session->set_flashdata('message',  lang('update_successful'));   
                }
                redirect(current_lang().'/account/myclients_list','refresh');
                
            }else{
                $this->data['warning'] = lang('account_creation_unsuccessful');
            }
            
        }
        
        if(!is_null($id)){
           $accountinfo = $this->account_model->client_list($id);
          $this->data['accountinfo'] = $accountinfo[0];
        }

        $this->data['content'] = "account/create_account";
        $this->load->view('template', $this->data);
    }
    
    
   
    
    
    
    function myclients_list(){
         $this->data['title'] = lang('myclients_list');
        $this->data['path_title'][] = lang('myclients_list');

       
       $this->data['client_list'] = $this->account_model->client_list();
        $this->data['content'] = "account/myclient_list";
        $this->load->view('template', $this->data);
    }
    
    function client_account_view($element_id,$id){
         $this->data['title'] = lang('myclients_account_view');
        $this->data['path_title'][] = lang('myclients_account_view');

        $this->data['accountinfo'] = $this->setting_model->client_list($id,null,null,$element_id)->row();
        $this->data['content'] = "client_account/myclient_account_view";
        $this->load->view('template', $this->data);
    }
    
    
    
    
    
}
