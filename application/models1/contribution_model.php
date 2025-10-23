<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contribution_model
 *
 * @author miltone
 */
class Contribution_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM contribution_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

     function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        $data = $this->db->get('contribution_transaction')->row();
        if (count($data) == 1) {
            return $data;
        }

        return FALSE;
    }
    function contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $cheque_num='',$month='',$auto=0,$date='') {
        if ($trans_type == 'CR') {
           
            return $this->credit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num,$month,$auto,$date);
        } else if ($trans_type == 'DR') {
            return $this->debit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num);
        }

        return false;
    }
    
    

    function debit($pid, $member_id, $paymethod, $amount, $comment='', $cheque_num='') {
        $pin = current_user()->PIN;
        $current_balance = $this->contribution_balance($pid, $member_id);
        $previous_balance = $current_balance->balance;


        //increaase balance
        $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->set("balance", "balance-{$amount}", FALSE);
        $this->db->update('members_contribution');

        //create transaction history
        $receipt = $this->receiptNo();
        $this->db->set('receipt', $receipt);
        $this->db->set('member_id', $member_id);
        $this->db->set('trans_type', 'DR');
        $this->db->set('paymethod', $paymethod);
        $this->db->set('cheque_num', $cheque_num);
        $this->db->set('amount', $amount);
        $this->db->set('previous_balance', $previous_balance);
        $this->db->set('PID', $pid);
        $this->db->set('comment', $comment);
        $this->db->set('PIN', $pin);
        $systemcomment = 'REFUND';
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('contribution_transaction');
        if ($insert) {
            return $receipt;
        }

        return FALSE;
    }

    function credit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num,$month,$auto=0,$date='') {
       $pin = current_user()->PIN;
        $current_balance = $this->contribution_balance($pid, $member_id);
        $previous_balance = 0;
        if ($current_balance) {
            $previous_balance = $current_balance->balance;
        }else{
            //insert for the first time
            $this->db->insert('members_contribution',array('PID'=>$pid,'member_id'=>$member_id));   
        }

        //check if available
        $check1 = $this->db->get_where('contribution_transaction',array('month'=>$month,'PID'=>$pid))->row();
        if($month<> '' && count($check1) ==1){
            //echo $check1->amount.'|';
             //increaase balance
       $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->set("balance", "balance-{$check1->amount}", FALSE);
        $this->db->update('members_contribution');
        $current_balance = $this->contribution_balance($pid, $member_id);
             //increaase balance
        $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->set("balance", "balance+{$amount}", FALSE);
        $this->db->update('members_contribution');

        //create transaction history
        $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->where("month", $month);
        $this->db->set('amount', $amount);
        $this->db->set('previous_balance', $previous_balance);
        $this->db->set('month', $month);
        $this->db->set('auto', $auto);
        $this->db->set('PIN', $pin);
        if($date<>''){
        $this->db->set('createdon', $date);    
        }
        $this->db->update('contribution_transaction');   
            
        }else {
        //increaase balance
        $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->set("balance", "balance+{$amount}", FALSE);
        $this->db->update('members_contribution');

        //create transaction history
        $receipt = $this->receiptNo();
        $this->db->set('receipt', $receipt);
        $this->db->set('member_id', $member_id);
        $this->db->set('trans_type', 'CR');
        $this->db->set('paymethod', $paymethod);
        $this->db->set('cheque_num', $cheque_num);
        $this->db->set('amount', $amount);
        $this->db->set('previous_balance', $previous_balance);
        $this->db->set('PID', $pid);
        $this->db->set('comment', $comment);
        $this->db->set('month', $month);
        $this->db->set('auto', $auto);
        $this->db->set('PIN', $pin);
        if($date<>''){
        $this->db->set('createdon', $date);    
        }
        if($comment != 'CONTRIBUTION_MIGRATED'){
        $systemcomment = 'CONTRIBUTE';
        }else{
            $systemcomment = 'CONTRIBUTION_MIGRATED';
        }
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('contribution_transaction');

        if ($insert) {
            return $receipt;
        }
        }

        return FALSE;
    }

    function contribution_balance($pid, $member_id) {
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $member_id);
        return $this->db->get('members_contribution')->row();
    }

    function contribution_setting($data, $id=null) {
        $check = $this->member_model->member_basic_info(null, $data['PID'], $data['member_id'])->row();
        if (!is_null($id)) {
            //update
            if (count($check) > 0) {
                return $this->db->update('contribution_settings', $data, array('id' => $id));
            } else {
                return FALSE;
            }
        } else {
            // insert
            if (count($check) == 0) {
                return FALSE;
            } else {
                //check if data exist
                $check2 = $this->db->get_where('contribution_settings', array('PID' => $data['PID']))->row();
                if (count($check2) > 0) {
                    return FALSE;
                } else {
                    return $this->db->insert('contribution_settings', $data);
                }
            }
        }
    }

    function contribution_source($id=null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('contribution_source');
    }

    function contribution_setting_info($id=null, $pid=null, $member_id=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($pid)) {
            $this->db->where('PID', $pid);
        }
        if (!is_null($member_id)) {
            $this->db->where('member_id', $member_id);
        }

        return $this->db->get('contribution_settings');
    }

    function count_contribution_setting($key=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }

        return count($this->db->get('contribution_settings')->result());
    }

    function search_contribution_setting($key, $limit, $start) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }

        $this->db->limit($limit, $start);
        return $this->db->get('contribution_settings')->result();
    }
    
    
    
    
    
     function count_transaction($key, $from, $upto) {
      $pin = current_user()->PIN;
        $and = " PIN ='$pin' AND createdon >= '$from 00:00:00' AND createdon <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND PID = '$key'";
        }

        return count($this->db->query("SELECT * FROM contribution_transaction WHERE $and ORDER BY createdon DESC")->result());
    }

    function search_transaction($key, $from, $upto, $limit, $start) {
         $pin = current_user()->PIN;
       
        $and = " PIN ='$pin' AND  createdon >= '$from 00:00:00' AND createdon <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND PID = '$key'";
        }

        return $this->db->query("SELECT * FROM contribution_transaction WHERE $and ORDER BY createdon DESC LIMIT $start,$limit")->result();
    }
    
    
    

}

?>
