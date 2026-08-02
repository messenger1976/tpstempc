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
            return $this->debit($pid, $member_id, $paymethod, $amount, $comment, $cheque_num, $date);
        }

        return false;
    }
    
    

    function debit($pid, $member_id, $paymethod, $amount, $comment='', $cheque_num='', $date='') {
        $pin = current_user()->PIN;
        $current_balance = $this->contribution_balance($pid, $member_id);
        if (!$current_balance) {
            $this->db->insert('members_contribution', array('PID' => $pid, 'member_id' => $member_id));
            $current_balance = $this->contribution_balance($pid, $member_id);
        }
        $previous_balance = $current_balance ? $current_balance->balance : 0;


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
        if ($date !== '') {
            $this->db->set('createdon', $date);
        }
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

    /**
     * Apply a manual journal CBU line to the member CBU sub-ledger.
     * Credit on CBU COA → CR (increase balance); Debit on CBU COA → DR (decrease).
     *
     * @param int|string $pid
     * @param string $member_id
     * @param float $debit
     * @param float $credit
     * @param string $comment
     * @param string $date Y-m-d or datetime
     * @param string $source Origin module key: journal_entry|cash_receipt|cash_disbursement|general_journal|journal_voucher
     * @return string|false receipt number
     */
    function journal_cbu_subledger($pid, $member_id, $debit, $credit, $comment = '', $date = '', $source = 'journal_entry') {
        $debit = floatval($debit);
        $credit = floatval($credit);
        if ($credit <= 0 && $debit <= 0) {
            return false;
        }
        // Equity CBU: credit increases member capital; debit decreases it
        if ($credit > 0) {
            $trans_type = 'CR';
            $amount = $credit;
        } else {
            $trans_type = 'DR';
            $amount = $debit;
        }
        if ($date === '') {
            $date = date('Y-m-d H:i:s');
        } elseif (strlen($date) <= 10) {
            $date = $date . ' 00:00:00';
        }
        $source = strtolower(trim((string) $source));
        $paymethod_map = array(
            'cash_receipt' => 'CASH RECEIPT',
            'cash_disbursement' => 'CASH DISBURSEMENT',
            'journal_entry' => 'JOURNAL',
            'general_journal' => 'JOURNAL',
            'journal_voucher' => 'JOURNAL',
        );
        $paymethod = isset($paymethod_map[$source]) ? $paymethod_map[$source] : 'JOURNAL';
        $default_comments = array(
            'cash_receipt' => 'Cash Receipt',
            'cash_disbursement' => 'Cash Disbursement',
            'journal_entry' => 'Journal Entry',
            'general_journal' => 'Journal Entry',
            'journal_voucher' => 'Journal Entry',
        );
        $comment = trim($comment) !== '' ? $comment : (isset($default_comments[$source]) ? $default_comments[$source] : 'Journal Entry');
        return $this->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, '', '', 0, $date);
    }

    /**
     * Human-readable origin of a contribution_transaction row.
     */
    function contribution_transaction_source($transaction) {
        if (is_array($transaction)) {
            $transaction = (object) $transaction;
        }
        if (!is_object($transaction)) {
            return 'CBU Transaction';
        }

        $system = strtoupper(trim(isset($transaction->system_comment) ? (string) $transaction->system_comment : ''));
        $comment = strtoupper(trim(isset($transaction->comment) ? (string) $transaction->comment : ''));
        $paymethod = strtoupper(trim(isset($transaction->paymethod) ? (string) $transaction->paymethod : ''));
        $auto = isset($transaction->auto) ? intval($transaction->auto) : 0;

        if (strpos($comment, 'VOID') === 0 || strpos($system, 'VOID') === 0) {
            return 'Void';
        }
        if (strpos($comment, 'BEGINNING BALANCE') !== FALSE) {
            return 'Beginning Balance';
        }
        if (strpos($system, 'CONTRIBUTION_MIGRATED') !== FALSE || strpos($comment, 'CONTRIBUTION_MIGRATED') !== FALSE) {
            return 'Migration';
        }
        if ($paymethod === 'CASH RECEIPT' || strpos($paymethod, 'CASH RECEIPT') !== FALSE) {
            return 'Cash Receipt';
        }
        if ($paymethod === 'CASH DISBURSEMENT' || strpos($paymethod, 'CASH DISBURSEMENT') !== FALSE) {
            return 'Cash Disbursement';
        }
        if ($paymethod === 'JOURNAL' || strpos($paymethod, 'JOURNAL') !== FALSE) {
            return 'Journal Entry';
        }
        if ($auto === 1) {
            return 'Auto Contribution';
        }

        if ($comment !== '') {
            if (preg_match('/^(CV|OR|CR)[\s#\-\/]/i', $comment) || strpos($comment, 'CASH RECEIPT') !== FALSE) {
                return 'Cash Receipt';
            }
            if (preg_match('/^(CDS|CD)[\s#\-\/]/i', $comment) || strpos($comment, 'CASH DISBURSEMENT') !== FALSE) {
                return 'Cash Disbursement';
            }
            if (preg_match('/^JV[\s#\-\/]/i', $comment) || strpos($comment, 'JOURNAL') !== FALSE) {
                return 'Journal Entry';
            }
            if (preg_match('/^LN[\s#\-\/]/i', $comment) || strpos($comment, 'LOAN') !== FALSE) {
                return 'Loan Disbursement';
            }
        }

        return 'CBU Transaction';
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

    function count_contribution_setting($key=null, $status=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status !== '') {
            $this->db->where('posted', $status);
        }

        return count($this->db->get('contribution_settings')->result());
    }

    function search_contribution_setting($key, $limit, $start, $status=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status !== '') {
            $this->db->where('posted', $status);
        }

        $this->db->limit($limit, $start);
        return $this->db->get('contribution_settings')->result();
    }

    /**
     * Count CBU masterfile list rows (settings joined with member + balance).
     *
     * @param string|null $key PID or member_id
     * @param string|null $status Member status (0/1)
     * @return int
     */
    function count_masterfile_list($key = null, $status = null) {
        $pin = $this->db->escape(current_user()->PIN);
        $and = " cs.PIN = $pin AND m.status != 2 ";
        if (!is_null($key) && $key !== '') {
            $key_esc = $this->db->escape($key);
            $and .= " AND (cs.PID = $key_esc OR cs.member_id = $key_esc) ";
        }
        if (!is_null($status) && $status !== '' && ($status === '0' || $status === '1')) {
            $and .= " AND m.status = " . $this->db->escape($status) . " ";
        }
        $sql = "SELECT COUNT(*) AS total
                FROM contribution_settings cs
                INNER JOIN members m ON m.PID = cs.PID AND m.PIN = cs.PIN
                WHERE $and";
        $row = $this->db->query($sql)->row();
        return $row ? (int) $row->total : 0;
    }

    /**
     * Search CBU masterfile list with balance and member status.
     *
     * @param string|null $key PID or member_id
     * @param int $limit
     * @param int $start
     * @param string|null $status Member status (0/1)
     * @return array
     */
    function search_masterfile_list($key, $limit, $start, $status = null) {
        $pin = $this->db->escape(current_user()->PIN);
        $and = " cs.PIN = $pin AND m.status != 2 ";
        if (!is_null($key) && $key !== '') {
            $key_esc = $this->db->escape($key);
            $and .= " AND (cs.PID = $key_esc OR cs.member_id = $key_esc) ";
        }
        if (!is_null($status) && $status !== '' && ($status === '0' || $status === '1')) {
            $and .= " AND m.status = " . $this->db->escape($status) . " ";
        }
        $limit = (int) $limit;
        $start = (int) $start;
        $sql = "SELECT cs.id, cs.PID, cs.member_id, cs.PIN,
                       m.firstname, m.middlename, m.lastname, m.status AS member_status,
                       COALESCE(mc.balance, 0) AS balance
                FROM contribution_settings cs
                INNER JOIN members m ON m.PID = cs.PID AND m.PIN = cs.PIN
                LEFT JOIN members_contribution mc ON mc.PID = cs.PID AND TRIM(mc.member_id) = TRIM(cs.member_id)
                WHERE $and
                ORDER BY cs.PID ASC
                LIMIT $start, $limit";
        return $this->db->query($sql)->result();
    }

    /**
     * Full CBU ledger (statement lines) for a member.
     *
     * @param string $member_id
     * @param int|string|null $pid
     * @return array
     */
    function cbu_ledger_transactions($member_id, $pid = null) {
        $pin = $this->db->escape(current_user()->PIN);
        $member_id_esc = $this->db->escape(trim($member_id));
        $and = " PIN = $pin AND TRIM(member_id) = $member_id_esc ";
        if (!is_null($pid) && $pid !== '') {
            $and .= " AND PID = " . $this->db->escape($pid) . " ";
        }
        $sql = "SELECT PID, member_id, createdon, comment, system_comment, trans_type, paymethod,
                       CASE WHEN trans_type = 'CR' THEN amount ELSE 0 END AS credit,
                       CASE WHEN trans_type = 'DR' THEN amount ELSE 0 END AS debit,
                       previous_balance
                FROM contribution_transaction
                WHERE $and
                ORDER BY createdon ASC, id ASC";
        return $this->db->query($sql)->result();
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
            // Unposting - reverse GL with reversing entry (keep original for audit)
            if (!empty($existing_entry)) {
                $this->load->model('finance_model');
                $gl_void = $this->finance_model->void_gl_lines_with_reversal('contribution_settings', $id, 'Unpost CBU beginning balance');
                return !empty($gl_void['success']);
            }
        }
        
        return FALSE;
    }

    /**
     * Whether a contribution_transaction receipt already has GL lines.
     */
    function is_contribution_receipt_posted_to_gl($receipt) {
        $pin = current_user()->PIN;
        if ($receipt === null || $receipt === '') {
            return false;
        }
        $this->db->where('refferenceID', $receipt);
        $this->db->where('fromtable', 'contribution_transaction');
        $this->db->where('PIN', $pin);
        return $this->db->count_all_results('general_ledger') > 0;
    }

    /**
     * Beginning-balance CBU is posted via contribution_settings.id, not the receipt.
     */
    function is_contribution_beginning_balance_posted($pid, $member_id) {
        $pin = current_user()->PIN;
        if ($pid === null || $pid === '' || $member_id === null || $member_id === '') {
            return false;
        }
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $member_id);
        $this->db->where('PIN', $pin);
        $this->db->where('posted', 1);
        return $this->db->count_all_results('contribution_settings') > 0;
    }

    /**
     * Resolve cash/bank (or adjustment) GL account for a CBU payment method.
     */
    function get_cash_account_for_contribution($payment_method) {
        $pin = current_user()->PIN;
        $payment_method = trim((string) $payment_method);
        if ($payment_method === '') {
            $payment_method = 'CASH';
        }

        $this->load->model('payment_method_config_model');
        $pm_config = $this->payment_method_config_model->get_account_for_payment_method($payment_method, $pin);
        if ($pm_config && !empty($pm_config->gl_account_code)) {
            $acct = $this->db->query(
                'SELECT account FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1',
                array($pm_config->gl_account_code, $pin)
            )->row();
            if ($acct) {
                return $acct->account;
            }
        }

        $payment_upper = strtoupper($payment_method);
        if (strpos($payment_upper, 'ADJUSTMENT') !== FALSE) {
            $acct = $this->db->query(
                "SELECT account FROM account_chart
                 WHERE PIN = ?
                   AND (name LIKE '%Opening Balance%' OR name LIKE '%Beginning Balance%' OR name LIKE '%Adjustment%' OR name LIKE '%Equity%')
                   AND (account_type IN (30, 40, 30000, 40000) OR account_type BETWEEN 30000 AND 39999)
                 ORDER BY account ASC LIMIT 1",
                array($pin)
            )->row();
            if ($acct) {
                return $acct->account;
            }
        }

        $acct = $this->db->query(
            "SELECT account FROM account_chart
             WHERE PIN = ?
               AND account_type IN (1, 10000)
               AND (name LIKE '%Cash%' OR name LIKE '%Bank%')
             ORDER BY account ASC LIMIT 1",
            array($pin)
        )->row();
        return $acct ? $acct->account : null;
    }

    /**
     * Post a manual Contribute (CR/DR) receipt to general_ledger.
     * Skips beginning balance (posted via contribution_settings), journal-sourced
     * rows (already in GL), voids, and already-posted receipts.
     *
     * @param string $receipt
     * @return array {success:bool, message:string}
     */
    function post_contribution_receipt_to_gl($receipt) {
        $pin = current_user()->PIN;
        if ($receipt === null || $receipt === '') {
            return array('success' => false, 'message' => 'Invalid receipt');
        }

        $this->db->where('receipt', $receipt);
        $this->db->where('PIN', $pin);
        $trans = $this->db->get('contribution_transaction')->row();
        if (!$trans) {
            return array('success' => false, 'message' => 'Transaction not found');
        }

        if ($this->is_contribution_receipt_posted_to_gl($receipt)) {
            return array('success' => true, 'message' => 'Transaction already posted to GL');
        }

        $comment = strtoupper(trim(isset($trans->comment) ? (string) $trans->comment : ''));
        $system = strtoupper(trim(isset($trans->system_comment) ? (string) $trans->system_comment : ''));
        $paymethod = strtoupper(trim(isset($trans->paymethod) ? (string) $trans->paymethod : ''));

        if (strpos($comment, 'VOID') === 0 || strpos($system, 'VOID') === 0) {
            return array('success' => false, 'message' => 'Void entries cannot be posted to GL from this screen');
        }
        if (strpos($comment, 'BEGINNING BALANCE') !== FALSE) {
            return array('success' => false, 'message' => 'Beginning Balance is posted from the CBU Setting List, not this screen');
        }
        if ($paymethod === 'JOURNAL' || strpos($paymethod, 'JOURNAL') !== FALSE
            || $paymethod === 'CASH RECEIPT' || strpos($paymethod, 'CASH RECEIPT') !== FALSE
            || $paymethod === 'CASH DISBURSEMENT' || strpos($paymethod, 'CASH DISBURSEMENT') !== FALSE) {
            return array('success' => false, 'message' => 'This transaction already comes from a journal/cash document - post that document to GL instead');
        }

        $this->load->model('setting_model');
        $global_contribution = $this->setting_model->global_contribution_info();
        $capital_build_up_account = isset($global_contribution->capital_build_up_account) ? $global_contribution->capital_build_up_account : null;
        if (empty($capital_build_up_account)) {
            return array('success' => false, 'message' => 'Capital Build Up Account is not configured in Contribution Minimum settings');
        }

        $capital_account_info = account_row_info($capital_build_up_account);
        if (!$capital_account_info) {
            return array('success' => false, 'message' => 'Capital Build Up Account not found in chart of accounts');
        }

        $cash_account = $this->get_cash_account_for_contribution(isset($trans->paymethod) ? $trans->paymethod : 'CASH');
        if (empty($cash_account)) {
            return array('success' => false, 'message' => 'No cash/bank GL account mapped for payment method "' . (isset($trans->paymethod) ? $trans->paymethod : '') . '"');
        }
        $cash_account_info = account_row_info($cash_account);
        if (!$cash_account_info) {
            return array('success' => false, 'message' => 'Cash/bank account not found in chart of accounts');
        }

        $amount = floatval($trans->amount);
        if ($amount <= 0) {
            return array('success' => false, 'message' => 'Invalid amount');
        }

        $trans_type = strtoupper(trim($trans->trans_type));
        if (!in_array($trans_type, array('CR', 'DR'), true)) {
            return array('success' => false, 'message' => 'Only deposit (CR) and withdrawal (DR) can be posted');
        }

        $trans_date = !empty($trans->createdon) ? date('Y-m-d', strtotime($trans->createdon)) : date('Y-m-d');
        $member_id = isset($trans->member_id) ? $trans->member_id : '';
        $pid = isset($trans->PID) ? $trans->PID : '';
        $label = ($trans_type === 'CR') ? 'CBU Deposit' : 'CBU Withdrawal';
        $description = $label . ' - ' . $member_id . ' [' . $receipt . ']';
        if (!empty($trans->comment)) {
            $description .= ' ' . trim($trans->comment);
        }

        $this->db->trans_start();

        $ledger_entry = array(
            'date' => $trans_date,
            'PIN' => $pin
        );
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();
        if (!$ledger_entry_id) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Failed to create GL entry header');
        }

        $ledger = array(
            'journalID' => 7,
            'refferenceID' => $receipt,
            'entryid' => $ledger_entry_id,
            'date' => $trans_date,
            'description' => $description,
            'linkto' => 'contribution_transaction.receipt',
            'fromtable' => 'contribution_transaction',
            'paid' => 0,
            'PID' => $pid,
            'member_id' => $member_id,
            'PIN' => $pin,
        );

        // Deposit CR: Dr Cash, Cr CBU. Withdrawal DR: Dr CBU, Cr Cash.
        if ($trans_type === 'CR') {
            $ledger['account'] = $cash_account;
            $ledger['debit'] = $amount;
            $ledger['credit'] = 0;
            $ledger['account_type'] = $cash_account_info->account_type;
            $ledger['sub_account_type'] = isset($cash_account_info->sub_account_type) ? $cash_account_info->sub_account_type : null;
            $this->db->insert('general_ledger', $ledger);

            $ledger['account'] = $capital_build_up_account;
            $ledger['debit'] = 0;
            $ledger['credit'] = $amount;
            $ledger['account_type'] = $capital_account_info->account_type;
            $ledger['sub_account_type'] = isset($capital_account_info->sub_account_type) ? $capital_account_info->sub_account_type : null;
            $this->db->insert('general_ledger', $ledger);
        } else {
            $ledger['account'] = $capital_build_up_account;
            $ledger['debit'] = $amount;
            $ledger['credit'] = 0;
            $ledger['account_type'] = $capital_account_info->account_type;
            $ledger['sub_account_type'] = isset($capital_account_info->sub_account_type) ? $capital_account_info->sub_account_type : null;
            $this->db->insert('general_ledger', $ledger);

            $ledger['account'] = $cash_account;
            $ledger['debit'] = 0;
            $ledger['credit'] = $amount;
            $ledger['account_type'] = $cash_account_info->account_type;
            $ledger['sub_account_type'] = isset($cash_account_info->sub_account_type) ? $cash_account_info->sub_account_type : null;
            $this->db->insert('general_ledger', $ledger);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'GL posting failed');
        }

        if (!$this->is_contribution_receipt_posted_to_gl($receipt)) {
            return array('success' => false, 'message' => 'GL posting pending. Please try again');
        }

        return array('success' => true, 'message' => 'Transaction posted to GL successfully');
    }

    /**
     * Void a posted CBU beginning balance: reverse GL + reverse ops CR with DR + set posted=0.
     */
    function void_contribution_beginning_balance($id, $reason = '') {
        $pin = current_user()->PIN;
        $id = (int) $id;
        $reason = trim((string) $reason);
        $settings = $this->search_contribution_setting_id($id);
        if (!$settings || (isset($settings->PIN) && $settings->PIN != $pin)) {
            return array('success' => false, 'message' => 'CBU setting not found.');
        }
        if (empty($settings->posted)) {
            return array('success' => false, 'message' => 'This CBU beginning balance is not posted.');
        }

        $this->db->trans_start();
        $this->load->model('finance_model');
        $gl_void = $this->finance_model->void_gl_lines_with_reversal('contribution_settings', $id, $reason !== '' ? $reason : 'Void CBU beginning balance');
        if (empty($gl_void['success'])) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => !empty($gl_void['message']) ? $gl_void['message'] : 'GL reverse failed.');
        }

        $pid = $settings->PID;
        $member_id = $settings->member_id;
        $amount = floatval($settings->amount);
        $comment = 'VOID BEGINNING BALANCE' . ($reason !== '' ? (' — ' . $reason) : '');
        $receipt = $this->contribution_transaction('DR', $pid, $member_id, $amount, 'JOURNAL', $comment, '', '', 0, date('Y-m-d H:i:s'));
        if (!$receipt) {
            $this->db->_trans_status = FALSE;
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Failed to reverse CBU sub-ledger balance.');
        }

        $this->post_to_gl($id, 0);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Void failed.');
        }
        return array('success' => true, 'message' => 'CBU beginning balance voided with reversing GL and sub-ledger entry.', 'receipt' => $receipt);
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
