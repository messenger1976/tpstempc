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
class Mortuary_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM mortuary_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

     function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        $data = $this->db->get('mortuary_transaction')->row();
        if (count($data) == 1) {
            return $data;
        }

        return FALSE;
    }
    //Added by Herald 03/12/2023
    function get_mortuary_balances($pid,$mid){
        $pin = current_user()->PIN;
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $mid);
        $this->db->where('PIN', $pin);
        $data = $this->db->get('members_mortuary')->row();
        if (count($data) == 1) {
            return $data;
        }

        return FALSE;
    }
    function contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $cheque_num='', $month='', $auto=0, $date='') {
        if ($trans_type == 'CR') {
           
            return $this->credit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num,$month,$auto,$date);
        } else if ($trans_type == 'DR') {
            return $this->debit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num, $date);
        }

        return false;
    }
    //Added by Herald 03/12/2023 - for mortuary transaction
    function mortuary_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $cheque_num='', $month='', $auto=0, $date='',$reference='') {
        if ($trans_type == 'CR') {
           
            return $this->credit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num,$month,$auto,$date);
        } else if ($trans_type == 'DR') {
            return $this->debit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num, $date, $reference);
        }

        return false;
    }

    function debit($pid, $member_id, $paymethod, $amount, $comment='', $cheque_num='',$date, $reference) {
        $pin = current_user()->PIN;
        $current_balance = $this->contribution_balance($pid, $member_id);
        $previous_balance = $current_balance->balance;


        //increase balance
        $this->db->where("PID", $pid);
        $this->db->where("member_id", $member_id);
        $this->db->set("balance", "balance-{$amount}", FALSE);
        $this->db->update('members_mortuary');

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
        $systemcomment = 'WITHDRAWAL';
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        if($date<>''){
            $this->db->set('trans_date', $date);    
            $this->db->set('createdon', $date);
        }
        if($reference<>''){
            $this->db->set('reference', $reference);    
        }
        $insert = $this->db->insert('mortuary_transaction');
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
            $this->db->insert('members_mortuary',array('PID'=>$pid,'member_id'=>$member_id));   
        }

        //check if available
        $check1 = $this->db->get_where('mortuary_transaction',array('month'=>$month,'PID'=>$pid))->row();
        if($month<> '' && count($check1) ==1){
            //echo $check1->amount.'|';
             //increaase balance
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("balance", "balance-{$check1->amount}", FALSE);
            $this->db->update('members_mortuary');
            $current_balance = $this->contribution_balance($pid, $member_id);
                //increaase balance
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("balance", "balance+{$amount}", FALSE);
            $this->db->update('members_mortuary');

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
                $this->db->set('trans_date', $date); 
                $this->db->set('createdon', $date);   
            }
            $this->db->update('mortuary_transaction');   
            
        }else {
            //increase balance
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("balance", "balance+{$amount}", FALSE);
            $this->db->update('members_mortuary');

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
                $this->db->set('trans_date', $date);
                $this->db->set('createdon', $date);    
            }
            if($comment != 'CONTRIBUTION_MIGRATED'){
                $systemcomment = 'DEPOSIT';
            }else{
                $systemcomment = 'CONTRIBUTION_MIGRATED';
            }
            $this->db->set('system_comment', $systemcomment);
            $this->db->set('createdby', $this->session->userdata('user_id'));
            $insert = $this->db->insert('mortuary_transaction');

            if ($insert) {
                return $receipt;
            }
        }

        return FALSE;
    }

    function contribution_balance($pid, $member_id) {
        $pin = current_user()->PIN;
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $member_id);
        $this->db->where('PIN', $pin);
        return $this->db->get('members_mortuary')->row();
    }
    function mortuary_balance($pid, $member_id) {
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $member_id);
        return $this->db->get('members_mortuary')->row();
    }
    function contribution_setting($data, $id=null) {
        $check = $this->member_model->member_basic_info(null, $data['PID'], $data['member_id'])->row();
        if (!is_null($id)) {
            //update
            if (count($check) > 0) {
                return $this->db->update('mortuary_settings', $data, array('id' => $id));
            } else {
                return FALSE;
            }
        } else {
            // insert
            if (count($check) == 0) {
                return FALSE;
            } else {
                //check if data exist
                $check2 = $this->db->get_where('mortuary_settings', array('PID' => $data['PID']))->row();
                if (count($check2) > 0) {
                    return FALSE;
                } else {
                    return $this->db->insert('mortuary_settings', $data);
                }
            }
        }
    }

    function contribution_source($id=null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('mortuary_source');
    }
    function mortuary_status($id=null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('mortuary_status');
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

        return $this->db->get('mortuary_settings');
    }
    function mortuary_setting_info($id=null, $pid=null, $member_id=null) {
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

        return $this->db->get('mortuary_settings');
    }

    function count_contribution_setting($key=null, $status=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status!='') {
            $this->db->where('status_flag', $status);
        }
        return count($this->db->get('mortuary_settings')->result());
    }

    function search_contribution_setting($key, $limit, $start, $status=null) {
        //$this->db->select('mortuary_settings.*, members.id as id, members.member_id, members.lastname, members.firstname');
        //$this->db->from('mortuary_settings');
       // $this->db->join('members','mortuary_settings.member_id = members.member_id','left');
        
        /*if (!is_null($key)) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status!='') {
            $this->db->where('status_flag', $status);
        }
        $this->db->where('PIN',  current_user()->PIN);
        //$this->db->order_by('lastname asc, firstname asc');
        $this->db->limit($limit, $start);
        return $this->db->get('mortuary_settings')->result();*/

        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key) || $key!=0) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status!='') {
            $this->db->where('status_flag', $status);
        }
        $this->db->order_by('ABS(member_id)','asc');
        $this->db->limit($limit, $start);
        return $this->db->get('mortuary_settings')->result();
    }
    
    function search_mortuary_setting($key) {
        $this->db->where('id',  $key);
        //$this->db->limit(1);
        return $this->db->get('mortuary_settings')->row();
        //$check1 = $this->db->get_where('mortuary_transaction',array('month'=>$month,'PID'=>$pid))->row();
    }    
    
    
    
     function count_transaction($key, $from, $upto) {
      $pin = current_user()->PIN;
        $and = " PIN ='$pin' AND trans_date >= '$from 00:00:00' AND trans_date <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND PID = '$key'";
        }

        return count($this->db->query("SELECT * FROM mortuary_transaction WHERE $and ORDER BY trans_date DESC")->result());
    }

    function search_transaction($key, $from, $upto, $limit, $start) {
         $pin = current_user()->PIN;
       
        $and = " PIN ='$pin' AND  trans_date >= '$from 00:00:00' AND trans_date <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND PID = '$key'";
        }

        return $this->db->query("SELECT * FROM mortuary_transaction WHERE $and ORDER BY trans_date DESC LIMIT $start,$limit")->result();
    }
    
    function post_to_gl($id,$posted){
        $this->db->where("id", $id);
        $this->db->set("posted", $posted);
        $this->db->update('mortuary_settings');
        return TRUE;
    }
    //Herald 03/11/2023 - List all mortuary master list for recompute balances
    function masterlisting($status_flag=0, $id=null) {
        $pin = current_user()->PIN;
      
        $and = " PIN ='$pin'";
        if (!is_null($id)) {
           $and.=" AND member_id = '$id'";
        }

        return $this->db->query("SELECT * FROM mortuary_settings WHERE $and ORDER BY id ASC")->result();
    }
    function recomputebalances($pid,$mid,$balance){
        $pin = current_user()->PIN;
        $this->db->select('*');
		$this->db->from('members_mortuary');	
		$this->db->where('PID', $pid);
		$this->db->where('member_id', $mid);	
		$membermortuarycount  = $this->db->get()->num_rows();
		
		if($membermortuarycount == 1){
			
			$dataUpdate = array( 
			'balance' => $balance, //
			'PIN' => $pin, //
			);
			
			$this->db->where('PID', $pid);
		    $this->db->where('member_id', $mid);
		    //$this->db->where('PIN', $pin);
		    $this->db->update('members_mortuary', $dataUpdate);
			
		}else{
            $this->db->set("member_id", $mid);
            $this->db->set('PID', $pid);
            $this->db->set('PIN', $pin);
            $this->db->insert('members_mortuary');
        }
        return TRUE;
    }
    function update_mortuary_status($pid,$mid,$status_flag){
        $pin = current_user()->PIN;
			$dataUpdate = array( 
			'status_flag' => $status_flag, //
			);
			$this->db->where('PID', $pid);
		    $this->db->where('member_id', $mid);
		    $this->db->where('PIN', $pin);
		    $this->db->update('mortuary_settings', $dataUpdate);
        return TRUE;
    }

    function update_mortuary_autopayment($pid,$mid,$reference){
        $pin = current_user()->PIN;
			$dataUpdate = array( 
			    'reference' => $reference, //
			    'claim_status' => 1, //
			);
			$this->db->where('PID', $pid);
		    $this->db->where('member_id', $mid);
		    $this->db->where('PIN', $pin);
		    $this->db->update('mortuary_settings', $dataUpdate);
        return TRUE;
    }

}

?>
