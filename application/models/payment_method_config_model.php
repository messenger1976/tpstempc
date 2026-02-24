<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_method_config_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get account code for a specific payment method using existing paymentmenthod table
     */
    public function get_account_for_payment_method($payment_method, $PIN = null) {
        if ($PIN === null) {
            $PIN = current_user()->PIN;
        }
        
        $this->db->where('LOWER(name)', strtolower($payment_method));
        $this->db->where('PIN', $PIN);
        $this->db->where('status', 1);
        $config = $this->db->get('paymentmenthod')->row();
        
        return $config;
    }

    /**
     * Get all payment methods for current user
     */
    public function get_all_payment_methods($PIN = null) {
        if ($PIN === null) {
            $PIN = current_user()->PIN;
        }
        
        $this->db->where('PIN', $PIN);
        $this->db->where('status', 1);
        $this->db->order_by('name', 'ASC');
        
        return $this->db->get('paymentmenthod')->result();
    }

    /**
     * Update payment method GL account mapping
     */
    public function update_gl_account($payment_method_id, $gl_account_code, $PIN = null) {
        if ($PIN === null) {
            $PIN = current_user()->PIN;
        }
        
        $data = array(
            'gl_account_code' => $gl_account_code
        );
        
        $this->db->where('id', $payment_method_id);
        $this->db->where('PIN', $PIN);
        
        return $this->db->update('paymentmenthod', $data);
    }

    /**
     * Get payment method by name
     */
    public function get_payment_method($name, $PIN = null) {
        if ($PIN === null) {
            $PIN = current_user()->PIN;
        }
        
        $this->db->where('LOWER(name)', strtolower($name));
        $this->db->where('PIN', $PIN);
        
        return $this->db->get('paymentmenthod')->row();
    }

}
?>

