<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of setting_model
 *
 * @author miltone
 */
class Setting_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function global_contribution_info() {
       $this->db->where('PIN',  current_user()->PIN);
        $row = $this->db->get('contribution_global')->row();
        if (empty($row) || $row === NULL) {
            $fields = $this->db->list_fields('contribution_global');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = 0;
            }

            $row = (Object) $fieldschange;
        }

        return $row;
    }
    function global_mortuary_info() {
        $this->db->where('PIN',  current_user()->PIN);
        $row = $this->db->get('mortuary_global')->row();
        if (empty($row) || $row === NULL) {
            $fields = $this->db->list_fields('mortuary_global');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = 0;
            }
            
            $row = (Object) $fieldschange;
        }
        
        return $row;
    }
    
    function share_setting_info() {
        $this->db->where('PIN',  current_user()->PIN);
        $row = $this->db->get('share_setting')->row();
        if (empty($row) || $row === NULL) {
            $fields = $this->db->list_fields('share_setting');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = 0;
            }

            $row = (Object) $fieldschange;
        }

        return $row;
    }
    //added by Herald
    function mortuary_global_info() {
        $this->db->where('PIN',  current_user()->PIN);
        $row = $this->db->get('mortuary_global')->row();
        if (empty($row) || $row === NULL) {
            $fields = $this->db->list_fields('mortuary_global');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = 0;
            }
            $row = (Object) $fieldschange;
        }
        return $row;
    }
    
    function setting_global_contibution($data) {
        //check exist
        $this->db->where('PIN',  current_user()->PIN);
        $check = $this->db->get('contribution_global')->row();
        if (!empty($check)) {
            return $this->db->update('contribution_global', $data, array('id' => $check->id));
        } else {
            return $this->db->insert('contribution_global', $data);
        }
        return FALSE;
    }

    function setup_share($data) {
        //check exist
        $this->db->where('PIN',  current_user()->PIN);
        $check = $this->db->get('share_setting')->row();
        if (!empty($check)) {
            return $this->db->update('share_setting', $data, array('id' => $check->id));
        } else {
            return $this->db->insert('share_setting', $data);
        }
        return FALSE;
    }
    //Added by Herald 10/20/2021
    function setup_mortuary($data) {
        //check exist
        $this->db->where('PIN',  current_user()->PIN);
        $check = $this->db->get('mortuary_global')->row();
        if (!empty($check)) {
            return $this->db->update('mortuary_global', $data, array('id' => $check->id));
        } else {
            return $this->db->insert('mortuary_global', $data);
        }
        return FALSE;
    }
    
    /*  function create_client_account($data) {
      $pin = $this->db->get('auto_client')->row()->Number;

      // increatent 1 next PIN
      $this->db->set('Number', 'Number+1', FALSE);
      $this->db->update('auto_client');

      $data['createdby'] = $this->session->userdata('user_id');
      $data['PIN'] = $pin;

      $this->db->insert('client_account', $data);

      //create administrator group
      $this->db->insert ('groups',array('PIN'=>$pin,'name'=>'admin','description'=>'Administrator'));

      return $pin;
      } */

    //update client information
    function update_companyinfo($data, $pin) {
        return $this->db->update('companyinfo', $data, array('id' => $pin));
    }

    function company_infomation($id = null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        
        return $this->db->get('companyinfo');
    }

    function saving_account_typelist($id = null, $account = null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($account)) {
            $this->db->where('account', $account);
        }

        return $this->db->get('saving_account_type');
    }

    function saving_account_typecreate($data, $id = null) {
        if (is_null($id)) {
            //create new account
            $new_account = $this->db->get('auto_inc')->row()->saving_type;
            // increatent 1 next saving_type
            $this->db->set('saving_type', 'saving_type+1', FALSE);
            $this->db->update('auto_inc');
            // insert
            $data['account'] = $new_account;

            return $this->db->insert('saving_account_type', $data);
        } else {
            //update
            return $this->db->update('saving_account_type', $data, array('id' => $id));
        }
    }

    function is_tax_exist($taxcode) {
         $pin = current_user()->PIN;
        $this->db->where('code', $taxcode);
       $this->db->where('PIN',  $pin);
        $check = $this->db->get('taxcode')->row();

        if (!empty($check)) {
            return TRUE;
        }
        return FALSE;
    }

    function is_itemcode_exist($itemcode) {
        $pin = current_user()->PIN;
        $this->db->where('code', $itemcode);
        $this->db->where('PIN',  $pin);
        $check = $this->db->get('items')->row();

        if (!empty($check)) {
            return TRUE;
        }
        return FALSE;
    }

    function invoicetype($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('invoicetype');
    }

    function add_taxcode($data, $id = null) {
        if (is_null($id)) {
            return $this->db->insert('taxcode', $data);
        } else {
            return $this->db->update('taxcode', $data, array('id' => $id));
        }
    }

    function tax_info($id = null, $taxcode = null) {
$this->db->where('PIN',  current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($taxcode)) {
            $this->db->where('code', $taxcode);
        }

        return $this->db->get('taxcode');
    }

    function additems_invoice($data, $id = null) {
        if (is_null($id)) {
            return $this->db->insert('items', $data);
        } else {
            return $this->db->update('items', $data, array('id' => $id));
        }
    }

    function item_info($id = null, $code = null, $invoicetype = null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($invoicetype)) {
            $this->db->where('invoicetype', $invoicetype);
        }
        if (!is_null($code)) {
            $this->db->where('code', $code);
        }

        return $this->db->get('items');
    }

    
    function intervalinfo($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('loan_interval');
    }

    function interest_method($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get(' loan_interest_method');
    }

    function penalt_method($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get(' loan_penalt_method');
    }

    
    function addloan_product($data, $id = null) {
        if (!is_null($id)) {
            return $this->db->update('loan_product', $data, array('id' => $id));
        } else {
            return $this->db->insert('loan_product', $data);
        }
    }
    
    
    function loanproduct($id=null){
         $this->db->where('PIN',  current_user()->PIN);
        if(!is_null($id)){
            $this->db->where('id',$id);
        }
        return $this->db->get('loan_product');
    }

    function payment_method_list($id = null) {
        $pin = current_user()->PIN;
        $this->db->select('pm.*, ac.name as gl_account_name, IFNULL(pm.status, 1) as status', FALSE);
        $this->db->from('paymentmenthod pm');
        $this->db->join('account_chart ac', 'pm.gl_account_code = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
        $this->db->where('pm.PIN', $pin);
        if (!is_null($id)) {
            $this->db->where('pm.id', $id);
        }
        $this->db->order_by('pm.name', 'ASC');
        return $this->db->get();
    }

    function payment_method_create($data, $id = null) {
        if (is_null($id)) {
            return $this->db->insert('paymentmenthod', $data);
        } else {
            return $this->db->update('paymentmenthod', $data, array('id' => $id));
        }
    }

    function payment_method_delete($id) {
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $result = $this->db->delete('paymentmenthod');
        return $result;
    }

    function payment_method_toggle_status($id) {
        $pin = current_user()->PIN;
        // Get current status
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $current = $this->db->get('paymentmenthod')->row();
        
        if ($current) {
            $new_status = $current->status == 1 ? 0 : 1;
            $this->db->where('id', $id);
            $this->db->where('PIN', $pin);
            return $this->db->update('paymentmenthod', array('status' => $new_status));
        }
        return FALSE;
    }

    function is_payment_method_exist($name, $exclude_id = null) {
        $pin = current_user()->PIN;
        $this->db->where('name', $name);
        $this->db->where('PIN', $pin);
        if (!is_null($exclude_id)) {
            $this->db->where('id !=', $exclude_id);
        }
        $check = $this->db->get('paymentmenthod')->row();

        if (!empty($check)) {
            return TRUE;
        }
        return FALSE;
    }

}

?>
