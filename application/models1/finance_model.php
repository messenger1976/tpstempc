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

    function create_chart_account($data) {
       // $this->db->where('id', $data['account_type']);
       $account = $this->db->get('account_inc')->row()->last_account;

        // increatent 1 next PIN
       // $this->db->where('id', $data['account_type']);
        $this->db->set('last_account ', 'last_account +1', FALSE);
        $this->db->update('account_inc');


        $data['account'] = $account;
        $this->db->insert('account_chart', $data);

        return $account;
    }

    function edit_chart_account($create_account, $id) {
        return $this->db->update('account_chart', $create_account, array('id' => $id));
    }

    /*
      //edit saccoss account,
      function edit_saccoss_account($data, $id) {
      return $this->db->update('saccos_accounts', $data, array('id' => $id));
      }
     */

    function account_typelist($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('account_type');
    }

    function account_chart_by_accounttype($account_type = null) {
        $return = array();
        $account_type_list = array();
        if (is_array($account_type)) {
            foreach ($account_type as $key => $value) {
                $account_type_list[] = $this->account_typelist($value)->row();
            }
        } else if (!is_null($account_type)) {
            $account_type_list[] = $this->account_typelist($account_type)->row();
        } else {
            $account_type_list = $this->account_typelist()->result();
        }

        foreach ($account_type_list as $key => $value) {
            $return[$value->id]['info'] = $value;
            $return[$value->id]['data'] = $this->account_chart(null, null, $value->id)->result();
        }
        return $return;
    }

    function account_chart($id = null, $account = null, $account_type = null, $parent_account = null) {
        $this->db->where('PIN',  current_user()->PIN);
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

        $this->db->order_by('account_type', 'ASC');
        return $this->db->get('account_chart');
    }

    function account_cash_received() {
        $sql = "SELECT * FROM account_chart where account='1000001' OR account='1000003'";
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

    function create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment = '', $cheque_num = '') {



        $account = $this->db->get('auto_inc')->row()->saving;

        // increatent 1 next PIN
        $this->db->set('saving', 'saving+1', FALSE);
        $this->db->update('auto_inc');


        //create account now
        $new_account = array(
            'account' => $account,
            'RFID' => $PID,
            'member_id' => $member_id,
            'account_cat' => $account_type,
            'virtual_balance' => $virtual_balance,
            'createdby' => current_user()->id,
            'tablename' => 'members',
            'PIN' => current_user()->PIN,
        );

        if ($comment == '' || is_null($comment)) {
            $comment = 'Opening account';
        }

        $create_new = $this->db->insert('members_account', $new_account);
        if ($create_new) {
            $amount = $balance + $virtual_balance;
            $systemcomment = 'OPEN ACCOUNT, NORMAL DEPOSIT';
            $customer_name = $this->saving_account_name($account);
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $PID, $systemcomment, $virtual_balance);
        }

        return FALSE;
    }

    function add_saving_transaction($trans_type = null, $account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $pid = null) {
        if (is_null($trans_type) || is_null($account) || $amount == 0 || is_null($paymethod)) {
            return false;
        }

        if ($trans_type == 'CR') {
            //deposit
            $systemcomment = 'NORMAL DEPOSIT';
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $pid, $systemcomment);
        } else if ($trans_type == 'DR') {
            //with draw
            $systemcomment = 'NORMAL WITHDRAWAL';
            return $this->debit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $systemcomment, $pid);
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

    function credit($account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $pid = null, $systemcomment = '', $start_up = 0) {
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

    function debit($account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $systemcomment = '', $pid = null) {
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
        if (count($data) == 1) {
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
 $this->db->where('PIN',$pin);
        return $this->db->get('sales_quote')->result();
    }

    function sales_invoice_list() {
        $pin = current_user()->PIN;
 $this->db->where('PIN',$pin);
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
            'PIN' =>$pin
        );


        foreach ($array_items as $key => $value) {
            $value['entryid'] = $jid;
            $this->db->insert('general_journal',$value);
            
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

}

?>