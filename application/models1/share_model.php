<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of share_model
 *
 * @author miltone
 */
class Share_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function is_share_max_reached($PID, $member_id) {
        $pin = current_user()->PIN;
        $share_info = $this->setting_model->share_setting_info();
$this->db->where('PIN', $pin);
        $this->db->where('PID', $PID);
        $this->db->where('member_id', $member_id);
        $row = $this->db->get('members_share')->row();
        if (count($row) > 0) {
            if ($row->totalshare >= $share_info->max_share) {
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;
    }

    function share_member_info($pid, $member_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $member_id);
        $row = $this->db->get('members_share')->row();
        if (count($row) == 0) {
            return FALSE;
        }

        return $row;
    }

    function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        $data = $this->db->get('share_transaction')->row();
        if (count($data) == 1) {
            return $data;
        }

        return FALSE;
    }

    function add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $cheque_num,$date='') {
        //test
        
        $prev_share_info = $this->share_member_info($pid, $member_id);
        if ($prev_share_info) {
            $previous_share = $prev_share_info->totalshare;
             $previous_amount = $prev_share_info->amount + $prev_share_info->remainbalance;
        } else {
            $previous_share = 0;
            $previous_amount = 0;
        }

        $check = $this->db->get_where('members_share', array('PID' => $pid, 'member_id' => $member_id))->row();
        if (count($check) == 0) {
            //insert
            $array_insert = array(
                'PID' => $pid,
                'member_id' => $member_id,
                'amount' => $amountshare,
                'totalshare' => $share_number,
                'remainbalance' => $remain_amount,
                'PIN' => current_user()->PIN,
            );
            $this->db->insert('members_share', $array_insert);
        } else {
            //update
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("amount", "amount+{$amountshare}", FALSE);
            $this->db->set("totalshare", "totalshare+{$share_number}", FALSE);
            $this->db->set("remainbalance", $remain_amount, FALSE);
            $this->db->update('members_share');
        }
        if($comment != 'BUY_SHARE_MIGRATE'){
            
       
        $systemcomment = 'BUY SHARE';
        }else{
            $systemcomment = 'BUY_SHARE_MIGRATE';
        }
        $amount = $real_amount;
       
        $trans = $this->credit_share($pid, $member_id, $paymethod, $cost_per_share, $amount, $previous_share, $systemcomment, $comment, $cheque_num,$share_number,$previous_amount,0,$date);
        if ($trans) {
            return $trans;
        }

        return FALSE;
    }

    function refund_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $cheque_num) {
        //test
        $prev_share_info = $this->share_member_info($pid, $member_id);
        if ($prev_share_info) {
            $previous_share = $prev_share_info->totalshare;
             $previous_amount = $prev_share_info->amount + $prev_share_info->remainbalance;
        } else {
            $previous_share = 0;
            $previous_amount  = 0;
        }


        //update
        $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->set("amount", "amount-{$amountshare}", FALSE);
        $this->db->set("totalshare", "totalshare-{$share_number}", FALSE);
        // $this->db->set("remainbalance", $remain_amount, FALSE);
        $this->db->update('members_share');
        if ($remain_amount <= $prev_share_info->remainbalance) {
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("remainbalance", "remainbalance-{$remain_amount}", FALSE);
            $this->db->update('members_share');
        }else{
            $deduct_moja = 1;
            $new_remaining_balance = $cost_per_share - $remain_amount;
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("amount", "amount-{$cost_per_share}", FALSE);
            $this->db->set("totalshare", "totalshare-{$deduct_moja}", FALSE);
            $this->db->set("remainbalance", "remainbalance+{$new_remaining_balance}", FALSE);
            $this->db->update('members_share');
        }



        $systemcomment = 'REFUND SHARE';
        $amount = $real_amount;
        $trans = $this->debit_share($pid, $member_id, $paymethod, $cost_per_share, $amount, $previous_share, $systemcomment, $comment, $cheque_num,$share_number,$previous_amount);
        if ($trans) {
            return $trans;
        }

        return FALSE;
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM share_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

    function debit_share($pid, $member_id, $paymethod, $cost_per_share, $amount, $previous_share, $systemcomment='', $comment='', $cheque_num='',$share_number=0,$previous_amount=0, $transfer_deposit_to_PID=0) {
       $pin = current_user()->PIN;
        //create transaction history
        $receipt = $this->receiptNo();
        $this->db->set('receipt', $receipt);
        $this->db->set('member_id', $member_id);
        $this->db->set('trans_type', 'DR');
        $this->db->set('paymethod', $paymethod);
        $this->db->set('cheque_num', $cheque_num);
        $this->db->set('amount', $amount);
        $this->db->set('previous_share', $previous_share);
        $this->db->set('PID', $pid);
        $this->db->set('comment', $comment);
        $this->db->set('PIN', $pin);
        $this->db->set('share_no', $share_number);
        $this->db->set('previous_balance', $previous_amount);
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('cost_per_share', $cost_per_share);
        $this->db->set('transfer_from_to_PID', $transfer_deposit_to_PID);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('share_transaction');
        if ($insert) {
            return $receipt;
        }

        return FALSE;
    }
    function credit_share($pid, $member_id, $paymethod, $cost_per_share, $amount, $previous_share, $systemcomment='', $comment='', $cheque_num='',$share_number=0,$previous_amount=0,$transfer_deposit_to_PID=0,$date='') {
$pin = current_user()->PIN;
        //create transaction history
        $receipt = $this->receiptNo();
        $this->db->set('receipt', $receipt);
        $this->db->set('member_id', $member_id);
        $this->db->set('trans_type', 'CR');
        $this->db->set('paymethod', $paymethod);
        $this->db->set('cheque_num', $cheque_num);
        $this->db->set('amount', $amount);
        $this->db->set('previous_share', $previous_share);
        $this->db->set('PID', $pid);
        $this->db->set('comment', $comment);
        if($date<>''){
         $this->db->set('createdon', $date);   
        }
        $this->db->set('PIN', $pin);
        $this->db->set('share_no', $share_number);
        $this->db->set('previous_balance', $previous_amount);
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('cost_per_share', $cost_per_share);
        $this->db->set('transfer_from_to_PID', $transfer_deposit_to_PID);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('share_transaction');
        if ($insert) {
            return $receipt;
        }

        return FALSE;
    }

    function count_transaction($key, $from, $upto) {
        $pin = current_user()->PIN;
        $and = " PIN ='$pin' AND createdon >= '$from 00:00:00' AND createdon <= '$upto 23:59:59' ";
        if (!is_null($key)) {
            $and.=" AND PID = '$key'";
        }

        return count($this->db->query("SELECT * FROM share_transaction WHERE $and ORDER BY createdon DESC")->result());
    }

    function search_transaction($key, $from, $upto, $limit, $start) {
         $pin = current_user()->PIN;
        $and = " PIN ='$pin' AND createdon >= '$from 00:00:00' AND createdon <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND PID = '$key'";
        }

        return $this->db->query("SELECT * FROM share_transaction WHERE $and ORDER BY createdon DESC LIMIT $start,$limit")->result();
    }

}

?>
