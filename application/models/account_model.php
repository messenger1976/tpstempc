<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of account_model
 *
 * @author miltone
 */
class Account_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function client_list($id = null) {
        if (is_resseller()) {
            $reseller_id = current_user()->reffid;
            if (!is_null($id)) {
                $this->db->where('id', $id);
            }
            $this->db->where('reseller_id', $reseller_id);
            return $this->db->get('companyinfo')->result();
        } else {
            if (!is_null($id)) {
                $this->db->where('id', $id);
                return $this->db->get('companyinfo')->result();
            }
        }

        return array();
    }

    function create_reseller_account($data, $id = null) {
        //check
        if (!is_null($id)) {
            $this->db->update('reseller_account', $data, array('id' => $id));
            $additional_data = array(
                'first_name' => $data['firstname'],
                'last_name' => $data['lastname'],
                'company' => $data['company'],
                'reseller' => 1,
                'super_user' => $data['is_super'],
                'phone' => $data['mobile'],
            );

            return $this->db->update('users', $additional_data, array('reffid' => $id, 'refftable' => 'reseller_account'));
        } else {
            //create here
            $this->db->insert('reseller_account', $data);
            return $this->db->insert_id();
        }
    }

    function reseller_account_list($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('reseller_account');
    }

    function create_client_account($client_array, $login_info, $id = null) {
         $this->db->trans_start();
        if (!is_null($id)) {
            //editing issue
            $this->db->update('companyinfo', $client_array, array('id' => $id));

            $last_id = $id;
        } else {
            //create account
            $client_array['createdby'] = current_user()->id;
            $client_array['reseller_id'] = current_user()->reffid;

            $account_no = $this->db->get('client_inc')->row()->PIN;
            // increatent 1 next account
            $this->db->set('PIN', 'PIN+1', FALSE);
            $this->db->update('client_inc');
            $client_array['PIN'] = $account_no;

            $this->db->insert('companyinfo', $client_array);
            $last_id = $this->db->insert_id();

            //load default financial account
            $default_account = $this->db->get('account_chart_default')->result();
            foreach ($default_account as $keyp => $valuep) {
                $create_account1 = array(
                    'account' => $valuep->account,
                    'PIN' => $account_no,
                    'account_type' => $valuep->account_type,
                    'sub_account_type' => $valuep->sub_account_type,
                    'name' => $valuep->name,
                    'description' => $valuep->description,
                    'createdby' => current_user()->id,
                    'edit'=>0
                );
                $this->db->insert('account_chart', $create_account1);
            }

           
            //insert in account auto
             $last_account = array(
                'PIN' => $account_no
            );
             
            $main_account = $this->finance_model->account_type()->result();
            foreach ($main_account as $keyxx => $valuexx) {
                $last_account['accounttype'] = $valuexx->account;
                $sub_account = $this->finance_model->account_type_sub(null, $valuexx->account)->result();
                if ($sub_account) {
                    foreach ($sub_account as $keyyy => $valueyy) {
                        $last_account['sub_account'] = $valueyy->sub_account;
                        $last_account['last_account'] = 10;
                        $this->db->insert('account_inc', $last_account);
                    }
                } else {
                    $last_account['sub_account'] = 0;
                    $last_account['last_account'] = 10;
                    $this->db->insert('account_inc', $last_account);
                }
            }



            //load default global settings account
            $default_settings = $this->db->get('global_setting_default')->result();
            foreach ($default_settings as $keypk => $valuepk) {
                $create_account12 = array(
                    'key' => $valuepk->key,
                    'PIN' => $account_no,
                    'text' => $valuepk->text,
                    'is_number' => $valuepk->is_number,
                );
                $this->db->insert('global_setting', $create_account12);
            }



            $login_info['company'] = $client_array['name'];
            $login_info['phone'] = $client_array['mobile'];
            $login_info['PIN'] = $account_no;
            $login_info['reffid'] = $last_id;
            $login_info['refftable'] = 'client_account';
            $login_info['is_client_admin'] = 1;
            $username = $login_info['email'];
            $email = $login_info['email'];
            $password = alphaID($account_no, FALSE, 8);
            $login_info['defaultpass'] = $password;
            //$this->db->insert('groups',array('name'=>'member','description'=>'Member','PIN'=>$login_info['PIN'],'opt'=>2));
           // $insert_group = $this->db->insert('groups',array('name'=>'admin','description'=>'Administrator','PIN'=>$login_info['PIN'],'opt'=>1));
            //$group_id = $this->db->insert_id();
            if ($this->ion_auth->register($username, $password, $email, $login_info, array(1))) {
                $client_name = $login_info['first_name'] . ' ' . $login_info['last_name'] . ' - ' . $login_info['company'];
                $this->maildata->send_login_credential($client_name, $email, $password);
            }
        }

        $this->db->trans_complete();

        return $last_id;
    }

}
