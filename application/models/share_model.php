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
        if ($row !== null) {
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
        if ($row === null) {
            return FALSE;
        }

        return $row;
    }

    function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        $data = $this->db->get('share_transaction')->row();
        if ($data !== null) {
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
        if ($check === null) {
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

    function refund_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $cheque_num, $date = '') {
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
        $trans = $this->debit_share($pid, $member_id, $paymethod, $cost_per_share, $amount, $previous_share, $systemcomment, $comment, $cheque_num,$share_number,$previous_amount, 0, $date);
        if ($trans) {
            return $trans;
        }

        return FALSE;
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM share_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

    function debit_share($pid, $member_id, $paymethod, $cost_per_share, $amount, $previous_share, $systemcomment='', $comment='', $cheque_num='',$share_number=0,$previous_amount=0, $transfer_deposit_to_PID=0, $date='') {
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
        if ($date <> '') {
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

    function is_void_entry($transaction) {
        $comment = isset($transaction->comment) ? (string) $transaction->comment : '';
        return (strpos($comment, 'VOID-') === 0);
    }

    function get_voided_receipt($transaction) {
        if ($this->is_void_entry($transaction)) {
            $comment = isset($transaction->comment) ? (string) $transaction->comment : '';
            if (preg_match('/VOID-([^ ]+)/', $comment, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    function is_share_transaction_voided($receipt) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->like('comment', 'VOID-' . $receipt, 'after');
        return $this->db->get('share_transaction')->num_rows() > 0;
    }

    /**
     * True when a later non-void share transaction exists for the same member.
     * Voiding out of order would leave members_share incorrect.
     */
    function has_later_share_transactions($trans) {
        if (!$trans || empty($trans->id)) {
            return true;
        }
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('PID', $trans->PID);
        $this->db->where('member_id', $trans->member_id);
        $this->db->where('id >', (int) $trans->id);
        $rows = $this->db->get('share_transaction')->result();
        foreach ($rows as $row) {
            if ($this->is_void_entry($row)) {
                continue;
            }
            // Already-reversed originals do not block voiding older entries
            if ($this->is_share_transaction_voided($row->receipt)) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * Void a share buy/refund by creating a reversing share_transaction
     * and restoring members_share balances.
     */
    function void_share_transaction($receipt, $reason = '') {
        $pin = current_user()->PIN;
        $reason = trim((string) $reason);
        if ($reason === '') {
            $reason = 'Transaction voided by user';
        }

        $this->db->where('receipt', $receipt);
        $this->db->where('PIN', $pin);
        $trans = $this->db->get('share_transaction')->row();
        if (!$trans) {
            return array('success' => false, 'message' => 'Transaction not found');
        }
        if ($this->is_void_entry($trans)) {
            return array('success' => false, 'message' => 'Cannot void a reversing entry');
        }
        if ($this->is_share_transaction_voided($receipt)) {
            return array('success' => false, 'message' => 'Transaction already voided');
        }
        if (!in_array($trans->trans_type, array('CR', 'DR'), true)) {
            return array('success' => false, 'message' => 'Only buy/refund transactions can be voided');
        }
        if ($this->has_later_share_transactions($trans)) {
            return array(
                'success' => false,
                'message' => 'Void newer share transactions for this member first.',
            );
        }

        $current = $this->share_member_info($trans->PID, $trans->member_id);
        if (!$current) {
            return array('success' => false, 'message' => 'Member share balance not found');
        }

        $share_no = floatval($trans->share_no);
        $cost = floatval($trans->cost_per_share);
        $amountshare = $share_no * $cost;
        $real = floatval($trans->amount);
        $previous_share = floatval($trans->previous_share);
        $previous_balance = floatval($trans->previous_balance);
        $pre_void_share = floatval($current->totalshare);
        $pre_void_balance = floatval($current->amount) + floatval($current->remainbalance);

        $this->db->trans_start();

        if ($trans->trans_type === 'CR') {
            // Reverse buy: restore amount / remain / share count from previous_*
            $old_amount = floatval($current->amount) - $amountshare;
            $old_remain = $previous_balance - $old_amount;
            if ($old_amount < -0.01 || $previous_share < -0.01) {
                $this->db->_trans_status = FALSE;
                $this->db->trans_complete();
                return array('success' => false, 'message' => 'Cannot reverse share buy: balance mismatch');
            }
            $this->db->where('PID', $trans->PID);
            $this->db->where('member_id', $trans->member_id);
            $this->db->where('PIN', $pin);
            $this->db->update('members_share', array(
                'amount' => max(0, round($old_amount, 2)),
                'remainbalance' => max(0, round($old_remain, 2)),
                'totalshare' => max(0, $previous_share),
            ));
            $void_type = 'DR';
            $void_system = 'VOID BUY SHARE';
        } else {
            // Reverse refund
            $shares_removed = $previous_share - floatval($current->totalshare);
            $remain_amount = round($real - $amountshare, 2);
            if (abs($shares_removed - $share_no) < 0.001) {
                $old_amount = floatval($current->amount) + $amountshare;
                $old_remain = floatval($current->remainbalance) + $remain_amount;
            } else {
                // refund_share branch that also consumes one extra share from remain
                $old_amount = floatval($current->amount) + $amountshare + $cost;
                $old_remain = floatval($current->remainbalance) - ($cost - $remain_amount);
            }
            $sum = $old_amount + $old_remain;
            if (abs($sum - $previous_balance) > 0.05) {
                $old_remain = $previous_balance - $old_amount;
            }
            $this->db->where('PID', $trans->PID);
            $this->db->where('member_id', $trans->member_id);
            $this->db->where('PIN', $pin);
            $this->db->update('members_share', array(
                'amount' => max(0, round($old_amount, 2)),
                'remainbalance' => max(0, round($old_remain, 2)),
                'totalshare' => max(0, $previous_share),
            ));
            $void_type = 'CR';
            $void_system = 'VOID REFUND SHARE';
        }

        $void_comment = 'VOID-' . $receipt . ' - ' . $reason;
        $original_method = !empty($trans->paymethod) ? $trans->paymethod : 'N/A';
        $void_system_comment = $void_system . ' | ORIG_TYPE:' . $trans->trans_type . ' | ORIG_METHOD:' . $original_method;

        if ($void_type === 'DR') {
            $void_receipt = $this->debit_share(
                $trans->PID,
                $trans->member_id,
                $trans->paymethod,
                $cost,
                $real,
                $pre_void_share,
                $void_system_comment,
                $void_comment,
                isset($trans->cheque_num) ? $trans->cheque_num : '',
                $share_no,
                $pre_void_balance
            );
        } else {
            $void_receipt = $this->credit_share(
                $trans->PID,
                $trans->member_id,
                $trans->paymethod,
                $cost,
                $real,
                $pre_void_share,
                $void_system_comment,
                $void_comment,
                isset($trans->cheque_num) ? $trans->cheque_num : '',
                $share_no,
                $pre_void_balance
            );
        }

        if (!$void_receipt) {
            $this->db->_trans_status = FALSE;
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Failed to create reversing share entry');
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Database error occurred');
        }

        return array(
            'success' => true,
            'message' => 'Share transaction voided with reversing entry',
            'void_receipt' => $void_receipt,
        );
    }

}

?>
