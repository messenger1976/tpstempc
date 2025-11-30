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
        if (!empty($data) && isset($data->receipt)) {
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
        $systemcomment = 'WITHDRAWAL';
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
        $systemcomment = 'DEPOSIT';
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
            if (!empty($check) && isset($check->PID)) {
                return $this->db->update('contribution_settings', $data, array('id' => $id));
            } else {
                return FALSE;
            }
        } else {
            // insert
            if (empty($check) || !isset($check->PID)) {
                return FALSE;
            } else {
                //check if data exist
                $check2 = $this->db->get_where('contribution_settings', array('PID' => $data['PID']))->row();
                if (!empty($check2) && isset($check2->id)) {
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
    
    
    function search_contribution_setting_id($key) {
        $this->db->where('id',  $key);
        return $this->db->get('contribution_settings')->row();
    } 
    
    
     function count_transaction($key, $from, $upto) {
      $pin = current_user()->PIN;
        $and = " PIN ='$pin'";
        
        // If Member ID is provided, ignore date filters
        // Otherwise, use date filters
        if (empty($key) || $key == '0' || is_null($key)) {
            // Member ID is blank/empty, use date filters
            if (!is_null($from) && !is_null($upto) && $from != '' && $upto != '') {
                $and.=" AND createdon >= '$from 00:00:00' AND createdon <= '$upto 23:59:59'";
            }
        } else {
            // Member ID is provided, ignore date filters and filter by PID or member_id
            $and.=" AND (PID = '$key' OR member_id = '$key')";
        }

        return count($this->db->query("SELECT * FROM contribution_transaction WHERE $and ORDER BY createdon DESC")->result());
    }

    function search_transaction($key, $from, $upto, $limit, $start) {
         $pin = current_user()->PIN;
       
        $and = " PIN ='$pin'";
        
        // If Member ID is provided, ignore date filters
        // Otherwise, use date filters
        if (empty($key) || $key == '0' || is_null($key)) {
            // Member ID is blank/empty, use date filters
            if (!is_null($from) && !is_null($upto) && $from != '' && $upto != '') {
                $and.=" AND createdon >= '$from 00:00:00' AND createdon <= '$upto 23:59:59'";
            }
        } else {
            // Member ID is provided, ignore date filters and filter by PID or member_id
            $and.=" AND (PID = '$key' OR member_id = '$key')";
        }

        return $this->db->query("SELECT * FROM contribution_transaction WHERE $and ORDER BY createdon DESC LIMIT $start,$limit")->result();
    }
    
    function post_to_gl($id,$posted){
        $this->db->where("id", $id);
        $this->db->set("posted", $posted);
        $this->db->update('contribution_settings');
        return TRUE;
    }
    
    function post_contribution_to_gl($id, $posted, $pid, $member_id, $amount, $trans_date) {
        $pin = current_user()->PIN;
        $this->load->model('setting_model');
        
        // Get Capital Build Up Account from settings
        $global_contribution = $this->setting_model->global_contribution_info();
        $capital_build_up_account = isset($global_contribution->capital_build_up_account) ? $global_contribution->capital_build_up_account : null;
        
        if (empty($capital_build_up_account)) {
            return FALSE; // Cannot post without Capital Build Up Account configured
        }
        
        // Check if GL entry already exists for this contribution setting
        $this->db->where('refferenceID', $id);
        $this->db->where('fromtable', 'contribution_settings');
        $this->db->where('PIN', $pin);
        $existing_entry = $this->db->get('general_ledger')->row();
        
        if ($posted == 1) {
            // Posting to GL - Create entries
            if (empty($existing_entry)) {
                // Create ledger entry
                $ledger_entry = array(
                    'date' => $trans_date,
                    'PIN' => $pin
                );
                $this->db->insert('general_ledger_entry', $ledger_entry);
                $ledger_entry_id = $this->db->insert_id();
                
                // Get account info for Capital Build Up Account
                $capital_account_info = account_row_info($capital_build_up_account);
                if (!$capital_account_info) {
                    return FALSE; // Account not found
                }
                
                // Prepare ledger data
                $ledger = array(
                    'journalID' => 7, // Journal ID for Contribution (adjust if your system uses different ID)
                    'refferenceID' => $id,
                    'entryid' => $ledger_entry_id,
                    'date' => $trans_date,
                    'description' => 'Contribution Beginning Balance - ' . $member_id,
                    'linkto' => 'contribution_settings.id',
                    'fromtable' => 'contribution_settings',
                    'paid' => 0,
                    'PID' => $pid,
                    'member_id' => $member_id,
                    'PIN' => $pin,
                );
                
                // Determine accounting entries based on Capital Build Up account type
                // If Capital Build Up is Equity (account_type 30 or 40): Debit Cash, Credit Capital Build Up
                // If Capital Build Up is Asset: Debit Capital Build Up, Credit Member Payable
                $capital_account_type = $capital_account_info->account_type;
                
                if ($capital_account_type == 30 || $capital_account_type == 40) {
                    // Capital Build Up is Equity - Standard entry: Debit Cash, Credit Capital Build Up
                    // Use standard cash account (1010001) or check if exists
                    $cash_account = 1010001; // Default cash account - adjust if needed
                    $cash_account_info = account_row_info($cash_account);
                    
                    if (!$cash_account_info) {
                        return FALSE; // Cash account not found
                    }
                    
                    // Debit: Cash Account
                    $ledger['account'] = $cash_account;
                    $ledger['debit'] = $amount;
                    $ledger['credit'] = 0;
                    $ledger['account_type'] = $cash_account_info->account_type;
                    $ledger['sub_account_type'] = isset($cash_account_info->sub_account_type) ? $cash_account_info->sub_account_type : null;
                    $this->db->insert('general_ledger', $ledger);
                    
                    // Credit: Capital Build Up Account
                    $ledger['account'] = $capital_build_up_account;
                    $ledger['debit'] = 0;
                    $ledger['credit'] = $amount;
                    $ledger['account_type'] = $capital_account_info->account_type;
                    $ledger['sub_account_type'] = isset($capital_account_info->sub_account_type) ? $capital_account_info->sub_account_type : null;
                    $this->db->insert('general_ledger', $ledger);
                } else {
                    // Capital Build Up is Asset - Debit Capital Build Up, Credit Member Contribution Payable
                    // Debit: Capital Build Up Account
                    $ledger['account'] = $capital_build_up_account;
                    $ledger['debit'] = $amount;
                    $ledger['credit'] = 0;
                    $ledger['account_type'] = $capital_account_info->account_type;
                    $ledger['sub_account_type'] = isset($capital_account_info->sub_account_type) ? $capital_account_info->sub_account_type : null;
                    $this->db->insert('general_ledger', $ledger);
                    
                    // Credit: Member Contribution Payable (Liability) - using default account
                    // You may want to add this as a setting
                    $member_payable_account = 2000002; // Default member contribution payable - adjust as needed
                    $payable_account_info = account_row_info($member_payable_account);
                    
                    if (!$payable_account_info) {
                        // If payable account doesn't exist, use cash account as credit
                        $cash_account = 1010001;
                        $payable_account_info = account_row_info($cash_account);
                        if (!$payable_account_info) {
                            return FALSE;
                        }
                        $member_payable_account = $cash_account;
                    }
                    
                    $ledger['account'] = $member_payable_account;
                    $ledger['debit'] = 0;
                    $ledger['credit'] = $amount;
                    $ledger['account_type'] = $payable_account_info->account_type;
                    $ledger['sub_account_type'] = isset($payable_account_info->sub_account_type) ? $payable_account_info->sub_account_type : null;
                    $this->db->insert('general_ledger', $ledger);
                }
                
                return TRUE;
            }
        } else {
            // Unposting - Delete GL entries
            if (!empty($existing_entry)) {
                $this->db->where('refferenceID', $id);
                $this->db->where('fromtable', 'contribution_settings');
                $this->db->where('PIN', $pin);
                $this->db->delete('general_ledger');
                
                // Also delete ledger entry if no other records reference it
                $this->db->where('entryid', $existing_entry->entryid);
                $remaining = $this->db->count_all_results('general_ledger');
                if ($remaining == 0) {
                    $this->db->where('id', $existing_entry->entryid);
                    $this->db->delete('general_ledger_entry');
                }
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    function total_cbu_balance() {
        $pin = current_user()->PIN;
        // Sum all CBU balances from active members
        $this->db->select_sum('members_contribution.balance');
        $this->db->from('members_contribution');
        $this->db->join('members', 'members.PID = members_contribution.PID', 'inner');
        $this->db->where('members.PIN', $pin);
        $this->db->where('members.status', 1); // Only active members
        $result = $this->db->get()->row();
        
        return isset($result->balance) && is_numeric($result->balance) ? $result->balance : 0;
    }
    
    function delete_transaction($receipt) {
        $pin = current_user()->PIN;
        
        // Get transaction details
        $this->db->where('receipt', $receipt);
        $this->db->where('PIN', $pin); // Ensure transaction belongs to current user's organization
        $transaction = $this->db->get('contribution_transaction')->row();
        
        if (!$transaction) {
            return FALSE; // Transaction not found or doesn't belong to this organization
        }
        
        $pid = $transaction->PID;
        $member_id = $transaction->member_id;
        $amount = $transaction->amount;
        $trans_type = $transaction->trans_type;
        
        // Start transaction
        $this->db->trans_start();
        
        // Reverse the balance adjustment based on transaction type
        // CR (Credit/Deposit) added to balance, so we subtract it
        // DR (Debit/Withdrawal) subtracted from balance, so we add it back
        if ($trans_type == 'CR') {
            // Reverse credit: subtract the amount from balance
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("balance", "balance-{$amount}", FALSE);
            $this->db->update('members_contribution');
        } else if ($trans_type == 'DR') {
            // Reverse debit: add the amount back to balance
            $this->db->where("PID", $pid);
            $this->db->where("member_id", $member_id);
            $this->db->set("balance", "balance+{$amount}", FALSE);
            $this->db->update('members_contribution');
        }
        
        // Delete the transaction record
        $this->db->where('receipt', $receipt);
        $this->db->where('PIN', $pin);
        $this->db->delete('contribution_transaction');
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return FALSE;
        }
        
        return TRUE;
    }

}

?>
