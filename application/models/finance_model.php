<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of finance_model
 *
 * @author miltone
 */
class Finance_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function account_type($id = null, $account = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($account)) {
            $this->db->where('account', $account);
        }

        $this->db->order_by('account', 'ASC');
        return $this->db->get('account_type');
    }

    function account_type_sub($id = null, $accounttype = null, $sub_account = null) {

        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($accounttype)) {
            $this->db->where('accounttype', $accounttype);
        }
        if (!is_null($sub_account)) {
            $this->db->where('sub_account', $sub_account);
        }

        $this->db->order_by('sub_account', 'ASC');
        return $this->db->get('account_type_sub');
    }

    function member_saving_account_list($id = null, $account = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($account)) {
            $this->db->where('account', $account);
        }
        $this->db->order_by('account', 'ASC');
        return $this->db->get('members_account');
    }

    function saving_account_list($id = null, $account = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($account)) {
            $this->db->where('account', $account);
        }
        $this->db->order_by('account', 'ASC');
        return $this->db->get('saving_account_type');
    }

    function last_chart_account($pin, $accounttype, $sub_account) {
        $this->db->where('PIN', $pin);
        $this->db->where('accounttype', $accounttype);
        $this->db->where('sub_account', $sub_account);
        return $this->db->get('account_inc')->row();
    }

    function create_chart_account($data) {

        $pin = $data['PIN'];
        $accounttype = $data['account_type'];
        $sub_account = $data['sub_account_type'];

        $last_account = $this->last_chart_account($pin, $accounttype, $sub_account);

        // increment last account by 1
        $this->db->where('PIN', $pin);
        $this->db->where('accounttype', $accounttype);
        $this->db->where('sub_account', $sub_account);
        $this->db->set('last_account', "last_account+1", FALSE);
        $this->db->update('account_inc');

        $account_start = (string) $last_account->accounttype . $last_account->sub_account;
        $last_part = format_lastpart_account($last_account->last_account);
        $account_no = $account_start . $last_part;
        //Disabled auto increment of account number - 11/22/2025
        //$data['account'] = (int) $account_no;


        $this->db->insert('account_chart', $data);

        return $account_no;
    }

    function edit_chart_account($create_account, $id) {
        return $this->db->update('account_chart', $create_account, array('id' => $id));
    }

    function delete_chart_account($id) {
        // Get account number before deletion
        $account_info = $this->account_chart($id, null)->row();
        
        if (!$account_info) {
            return false;
        }
        
        $account_number = $account_info->account;
        $pin = current_user()->PIN;
        
        // Check if account has transactions in general_ledger
        $this->db->where('account', $account_number);
        $this->db->where('PIN', $pin);
        $ledger_count = $this->db->count_all_results('general_ledger');
        
        // Check if account has transactions in general_journal
        $this->db->where('account', $account_number);
        $journal_count = $this->db->count_all_results('general_journal');
        
        // If account has transactions, cannot delete
        if ($ledger_count > 0 || $journal_count > 0) {
            return false;
        }
        
        // Safe to delete
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        return $this->db->delete('account_chart');
    }
    
    function check_account_has_transactions($account_number) {
        $pin = current_user()->PIN;
        
        // Check general_ledger
        $this->db->where('account', $account_number);
        $this->db->where('PIN', $pin);
        $ledger_count = $this->db->count_all_results('general_ledger');
        
        // Check general_journal
        $this->db->where('account', $account_number);
        $journal_count = $this->db->count_all_results('general_journal');
        
        return ($ledger_count > 0 || $journal_count > 0);
    }

    /*
      //edit saccoss account,
      function edit_saccoss_account($data, $id) {
      return $this->db->update('saccos_accounts', $data, array('id' => $id));
      }
     */

    function account_typelist($id = null,$account_type=null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
if (!is_null($account_type)) {
            $this->db->where('account', $account_type);
        }

        return $this->db->get('account_type');
    }

    function account_chart_by_accounttype($account_type = null) {
        $return = array();

        $account_type_list = array();
        if (is_array($account_type)) {
            foreach ($account_type as $key => $value) {
                $account_type_list[] = $this->account_type(null,$value)->row();
            }
        } else if (!is_null($account_type)) {
            $account_type_list[] = $this->account_typelist(null,$account_type)->row();
        } else {
            $account_type_list = $this->account_typelist()->result();
        }
        foreach ($account_type_list as $key => $value) {
            $return[$value->id]['info'] = $value;
            $return[$value->id]['data'] = $this->account_chart(null, null, $value->account)->result();
        }
        return $return;
    }

    function account_chart($id = null, $account = null, $account_type = null, $parent_account = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($account)) {
            $this->db->where('account', $account);
        }
        if (!is_null($account_type)) {
            $this->db->where('account_type', $account_type);
        }
        if (!is_null($parent_account)) {
            $this->db->where('account_parent', $parent_account);
        }

        $this->db->order_by('account', 'ASC');
        return $this->db->get('account_chart');
    }

    function account_cash_received() {
$pin=current_user()->PIN;
        $sql = "SELECT * FROM account_chart where (account='1010001' OR account='1010003') AND PIN='$pin'";
        return $this->db->query($sql)->result();
    }

    function paymentmenthod($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        return $this->db->get('paymentmenthod')->result();
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM savings_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

    function create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment = '', $cheque_num = '', $posted_date='',$old_savings_account_no='') {

        $account = $this->db->get('auto_inc')->row()->saving;

        // increatent 1 next PIN
        $this->db->set('saving', 'saving+1', FALSE);
        $this->db->update('auto_inc');
        

        //create account now
        $new_account = array(
            'account' => $account,
            'RFID' => $PID,
            'member_id' => $member_id,
            'old_members_acct' => $old_savings_account_no,
            'account_cat' => $account_type,
            'virtual_balance' => $virtual_balance,
            'createdby' => current_user()->id,
            'tablename' => 'members',
            'PIN' => current_user()->PIN,
        );

        if($posted_date!=''){
            $new_account_date = array(
                'createdon' => $posted_date,
            );
            $new_account = array_merge($new_account, $new_account_date);
        }

        if ($comment == '' || is_null($comment)) {
            $comment = 'Opening account';
        }

        $create_new = $this->db->insert('members_account', $new_account);
        if ($create_new) {
            $amount = $balance + $virtual_balance;
            $systemcomment = 'OPEN ACCOUNT, NORMAL DEPOSIT';
            $customer_name = $this->saving_account_name($account);
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $PID, $systemcomment, $virtual_balance, $posted_date);
        }

        return FALSE;
    }

    function add_saving_transaction($trans_type = null, $account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $pid = null, $posted_date='') {
        if (is_null($trans_type) || is_null($account) || $amount == 0 || is_null($paymethod)) {
            return false;
        }

        if ($trans_type == 'CR') {
            //deposit
            $systemcomment = 'NORMAL DEPOSIT';
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $pid, $systemcomment,0, $posted_date);
        } else if ($trans_type == 'DR') {
            //with draw
            $systemcomment = 'NORMAL WITHDRAWAL';
            return $this->debit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $systemcomment, $pid,$posted_date);
        }


        return FALSE;
    }

    function saving_account_balance($account) {
        $this->db->where('account', $account);
        return $this->db->get('members_account')->row();
    }

    function saving_account_balance_PID($pid, $member_id) {
        $this->db->where('RFID', $pid);
        $this->db->where('member_id', $member_id);
        return $this->db->get('members_account')->row();
    }

    function count_transaction($key, $from, $upto) {
        $pin = current_user()->PIN;
        $and = " PIN ='$pin' AND trans_date >= '$from 00:00:00' AND trans_date <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND account = '$key'";
        }

        return count($this->db->query("SELECT * FROM savings_transaction WHERE $and ORDER BY trans_date DESC")->result());
    }

    function search_transaction($key, $from, $upto, $limit, $start) {
        $pin = current_user()->PIN;
        $and = " PIN ='$pin' AND trans_date >= '$from 00:00:00' AND trans_date <= '$upto 23:59:59'";
        if (!is_null($key)) {
            $and.=" AND account = '$key'";
        }

        return $this->db->query("SELECT * FROM savings_transaction WHERE $and ORDER BY trans_date DESC LIMIT $start,$limit")->result();
    }

    function credit($account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $pid = null, $systemcomment = '', $start_up = 0, $posted_date='') {
        $pin = current_user()->PIN;


        if ($amount == 0 || is_null($account) || is_null($paymethod)) {
            return FALSE;
        }

        //get previous balance

        $account_info = $this->saving_account_balance($account);


        //increaase balance
        $this->db->where("account", $account);
        if ($start_up != 0) {
            $amount = $amount - $start_up;
        }
        $this->db->set("balance", "balance+{$amount}", FALSE);
        $this->db->update('members_account');

        if ($start_up != 0) {
            $amount = $amount + $start_up;
        }

        //create transaction history
        $receipt = $this->receiptNo();
        $this->db->set('receipt', $receipt);
        $this->db->set('account', $account);
        $this->db->set('trans_type', 'CR');
        $this->db->set('paymethod', $paymethod);
        $this->db->set('cheque_num', $cheque_num);
        $this->db->set('amount', $amount);
        if ($start_up == 0) {
            $this->db->set('previous_balance', $account_info->balance);
        } else {
            $this->db->set('previous_balance', 0);
        }
        if($posted_date!=''){
            $this->db->set('trans_date', $posted_date);
        }
        $pid | $pid = $account_info->RFID;
        $this->db->set('PID', $pid);
        $this->db->set('account_cat', $account_info->account_cat);
        $this->db->set('comment', $comment);
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('customer_name', $customer_name);
        $this->db->set('PIN', $pin);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('savings_transaction');
        if ($insert) {
            return $receipt;
        }

        return FALSE;
    }

    function debit($account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $systemcomment = '', $pid = null, $posted_date='') {
        $pin = current_user()->PIN;
        if ($amount == 0 || is_null($account) || is_null($paymethod)) {
            return FALSE;
        }

        //get previous balance

        $account_info = $this->saving_account_balance($account);


        //increaase balance
        $this->db->where("account", $account);
        $this->db->set("balance", "balance-{$amount}", FALSE);
        $this->db->update('members_account');

        //create transaction history
        $receipt = $this->receiptNo();
        $this->db->set('receipt', $receipt);
        $this->db->set('account', $account);
        $this->db->set('trans_type', 'DR');
        $this->db->set('paymethod', $paymethod);
        $this->db->set('cheque_num', $cheque_num);
        $this->db->set('amount', $amount);
        $this->db->set('previous_balance', $account_info->balance);
        if($posted_date!=''){
            $this->db->set('trans_date', $posted_date);
        }
        $pid | $pid = $account_info->RFID;
        $this->db->set('PID', $pid);
        $this->db->set('account_cat', $account_info->account_cat);
        $this->db->set('customer_name', $customer_name);
        $this->db->set('comment', $comment);
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('PIN', $pin);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('savings_transaction');
        if ($insert) {
            return $receipt;
        }

        return FALSE;
    }

    function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        $data = $this->db->get('savings_transaction')->row();
        if ($data) {
            return $data;
        }

        return FALSE;
    }

    function saving_account_name($account) {
        $account_info = $this->saving_account_balance($account);
        if ($account_info->tablename == 'members_grouplist') {
            $this->db->where('GID', $account_info->RFID);
            $rowdata = $this->db->get('members_grouplist')->row();
            return $rowdata->name;
        } else if ($account_info->tablename == 'members') {
            $this->db->where('PID', $account_info->RFID);
            $rowdata = $this->db->get('members')->row();
            return $rowdata->firstname . ' ' . $rowdata->middlename . ' ' . $rowdata->lastname;
        }
    }

    function sales_quote_list() {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        return $this->db->get('sales_quote')->result();
    }

    function sales_invoice_list() {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->order_by('status', 'ASC');
        return $this->db->get('sales_invoice')->result();
    }

    function enter_journal($main_array, $array_items) {
        $pin = current_user()->PIN;
        $this->db->trans_start();

        //prepare journal entry
        $this->db->insert('general_journal_entry', $main_array);
        $jid = $this->db->insert_id();

        $ledger_entry = array('date' => $main_array['entrydate']);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $ledger = array(
            'journalID' => 5,
            'refferenceID' => $jid,
            'entryid' => $ledger_entry_id,
            'date' => $main_array['entrydate'],
            'linkto' => 'general_journal.entryid',
            'fromtable' => 'general_journal',
            'PIN' => $pin
        );


        foreach ($array_items as $key => $value) {
            $value['entryid'] = $jid;
            $this->db->insert('general_journal', $value);

            //
            $ledger['account'] = $value['account'];
            $ledger['credit'] = $value['credit'];
            $ledger['description'] = $value['description'];
            $ledger['debit'] = $value['debit'];
            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
            $this->db->insert('general_ledger', $ledger);
        }

        $this->db->trans_complete();

        return $jid;
    }
    function count_saving_setting($key=null, $status=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status!='') {
            $this->db->where('status_flag', $status);
        }
        return count($this->db->get('members_account')->result());
    }

    // Chart Type CRUD operations
    function create_chart_type($data) {
        return $this->db->insert('account_type', $data);
    }

    function update_chart_type($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('account_type', $data);
    }

    function delete_chart_type($id) {
        // Get chart type info
        $chart_type = $this->account_type($id)->row();
        if (!$chart_type) {
            return false; // Chart type doesn't exist
        }
        
        // Check if chart type is being used in account_chart
        $this->db->where('account_type', $chart_type->account);
        $count = $this->db->count_all_results('account_chart');
        
        if ($count > 0) {
            return false; // Cannot delete if in use
        }
        
        // Check if chart type has sub types
        $this->db->where('accounttype', $chart_type->account);
        $sub_count = $this->db->count_all_results('account_type_sub');
        
        if ($sub_count > 0) {
            return false; // Cannot delete if has sub types
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('account_type');
    }

    // Chart Sub Type CRUD operations
    function create_chart_sub_type($data) {
        return $this->db->insert('account_type_sub', $data);
    }

    function update_chart_sub_type($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('account_type_sub', $data);
    }

    function delete_chart_sub_type($id) {
        // Get chart sub type info
        $sub_type = $this->account_type_sub($id)->row();
        if (!$sub_type) {
            return false; // Chart sub type doesn't exist
        }
        
        // Check if chart sub type is being used in account_chart
        $this->db->where('account_type', $sub_type->accounttype);
        $this->db->where('sub_account_type', $sub_type->sub_account);
        $count = $this->db->count_all_results('account_chart');
        
        if ($count > 0) {
            return false; // Cannot delete if in use
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('account_type_sub');
    }

}

?>
