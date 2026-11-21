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
        $current_balance = $this->ensure_members_contribution($pid, $member_id, $pin);
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
        $current_balance = $this->ensure_members_contribution($pid, $member_id, $pin);
        $previous_balance = $current_balance ? $current_balance->balance : 0;

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
        if ($comment != 'CONTRIBUTION_MIGRATED') {
            if (stripos((string) $comment, 'BEGINNING BALANCE') !== FALSE) {
                $systemcomment = 'BEGINNING BALANCE';
            } else {
                $systemcomment = 'DEPOSIT';
            }
        } else {
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
     * Ensure members_contribution exists and carries the organisation PIN.
     * Legacy rows often have PIN=0 because credit/debit previously omitted it.
     *
     * @return object|null balance row
     */
    function ensure_members_contribution($pid, $member_id, $pin = null) {
        if ($pin === null) {
            $pin = current_user()->PIN;
        }
        $row = $this->contribution_balance($pid, $member_id);
        if (!$row) {
            $this->db->insert('members_contribution', array(
                'PID' => $pid,
                'member_id' => $member_id,
                'balance' => 0,
                'PIN' => $pin,
            ));
            return $this->contribution_balance($pid, $member_id);
        }
        if (!isset($row->PIN) || intval($row->PIN) === 0) {
            $this->db->where('PID', $pid);
            $this->db->where('member_id', $member_id);
            $this->db->update('members_contribution', array('PIN' => $pin));
            $row->PIN = $pin;
        }
        return $row;
    }

    /**
     * Backfill members_contribution.PIN from members / contribution_settings.
     *
     * @return int rows updated
     */
    function backfill_members_contribution_pin() {
        $sql = "UPDATE members_contribution mc
                LEFT JOIN members m ON m.PID = mc.PID AND TRIM(m.member_id) = TRIM(mc.member_id)
                LEFT JOIN contribution_settings cs ON cs.PID = mc.PID AND TRIM(cs.member_id) = TRIM(mc.member_id)
                SET mc.PIN = COALESCE(NULLIF(m.PIN, 0), NULLIF(cs.PIN, 0), mc.PIN)
                WHERE mc.PIN = 0 OR mc.PIN IS NULL";
        $this->db->query($sql);
        return $this->db->affected_rows();
    }

    /**
     * Repair a posted CBU setting that has no BEGINNING BALANCE sub-ledger row
     * and/or a mismatched Masterfile balance / GL amount.
     *
     * @param int $setting_id contribution_settings.id
     * @return array {success, message, receipt}
     */
    function repair_posted_cbu_beginning_balance($setting_id) {
        $pin = current_user()->PIN;
        $setting = $this->search_contribution_setting_id($setting_id);
        if (!$setting || (isset($setting->PIN) && intval($setting->PIN) !== intval($pin))) {
            return array('success' => false, 'message' => 'CBU setting not found.');
        }
        if (empty($setting->posted)) {
            return array('success' => false, 'message' => 'CBU setting is not posted.');
        }

        $pid = trim($setting->PID);
        $member_id = trim($setting->member_id);
        $amount = floatval($setting->amount);
        $trans_date = !empty($setting->posting_date) ? date('Y-m-d', strtotime($setting->posting_date)) : date('Y-m-d');
        if ($amount <= 0) {
            return array('success' => false, 'message' => 'Invalid CBU setting amount.');
        }

        $this->ensure_members_contribution($pid, $member_id, $pin);

        $pin_esc = $this->db->escape($pin);
        $pid_esc = $this->db->escape($pid);
        $mid_esc = $this->db->escape($member_id);
        $bb_sql = "SELECT * FROM contribution_transaction
                   WHERE PIN = {$pin_esc}
                     AND PID = {$pid_esc}
                     AND TRIM(member_id) = {$mid_esc}
                     AND (
                         UPPER(COALESCE(comment,'')) LIKE '%BEGINNING BALANCE%'
                         OR UPPER(COALESCE(system_comment,'')) LIKE '%BEGINNING BALANCE%'
                     )
                     AND UPPER(COALESCE(comment,'')) NOT LIKE '%VOID%'
                     AND UPPER(COALESCE(system_comment,'')) NOT LIKE '%VOID%'
                     AND trans_type = 'CR'
                   ORDER BY id ASC
                   LIMIT 1";
        $bb_tx = $this->db->query($bb_sql)->row();

        $receipt = null;
        $this->db->trans_start();

        if (!$bb_tx) {
            $balance_row = $this->contribution_balance($pid, $member_id);
            $current_balance = $balance_row ? floatval($balance_row->balance) : 0;
            // Set balance to the posted setting amount (orphan repair), then write the missing CR.
            $this->db->where('PID', $pid);
            $this->db->where('member_id', $member_id);
            $this->db->update('members_contribution', array('balance' => $amount, 'PIN' => $pin));

            $receipt = $this->receiptNo();
            $this->db->insert('contribution_transaction', array(
                'receipt' => $receipt,
                'member_id' => $member_id,
                'trans_type' => 'CR',
                'paymethod' => 'ADJUSTMENT',
                'cheque_num' => '',
                'amount' => $amount,
                'previous_balance' => $current_balance,
                'PID' => $pid,
                'comment' => 'BEGINNING BALANCE',
                'system_comment' => 'BEGINNING BALANCE',
                'month' => '',
                'auto' => 0,
                'PIN' => $pin,
                'createdon' => $trans_date . ' 08:00:00',
                'createdby' => $this->session->userdata('user_id'),
            ));
            if ($this->db->affected_rows() <= 0) {
                $this->db->_trans_status = FALSE;
                $this->db->trans_complete();
                return array('success' => false, 'message' => 'Failed to create missing BEGINNING BALANCE transaction.');
            }
        } else {
            $receipt = $bb_tx->receipt;
            if (abs(floatval($bb_tx->amount) - $amount) > 0.009) {
                $delta = $amount - floatval($bb_tx->amount);
                $this->db->where('id', $bb_tx->id);
                $this->db->update('contribution_transaction', array('amount' => $amount));
                $this->db->where('PID', $pid);
                $this->db->where('member_id', $member_id);
                $this->db->set('balance', 'balance+' . $delta, FALSE);
                $this->db->update('members_contribution');
            }
        }

        // Sync GL CBU line to the setting amount (CBU-only Journal 8).
        $gl_ok = $this->post_contribution_to_gl($setting_id, 1, $pid, $member_id, $amount, $trans_date);
        if (!$gl_ok) {
            $this->db->_trans_status = FALSE;
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Sub-ledger repaired but GL sync failed. Check Capital Build Up Account.');
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Repair failed.');
        }

        $balance_row = $this->contribution_balance($pid, $member_id);
        return array(
            'success' => true,
            'message' => 'CBU beginning balance repaired. Masterfile balance is now '
                . number_format($balance_row ? $balance_row->balance : $amount, 2),
            'receipt' => $receipt,
        );
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

    /**
     * Source Type filter options for CBU transaction search.
     */
    function contribution_source_filter_options() {
        return array(
            'all' => 'All',
            'cbu_transaction' => 'CBU Transaction',
            'beginning_balance' => 'Beginning Balance',
            'cash_receipt' => 'Cash Receipt',
            'cash_disbursement' => 'Cash Disbursement',
            'journal_entry' => 'Journal Entry',
            'auto_contribution' => 'Auto Contribution',
            'loan_disbursement' => 'Loan Disbursement',
            'migration' => 'Migration',
            'void' => 'Void',
        );
    }

    /**
     * SQL fragment for Source Type filter on contribution_transaction.
     */
    function contribution_source_filter_sql($source) {
        $source = strtolower(trim((string) $source));
        if ($source === '' || $source === 'all') {
            return '';
        }

        $c = "UPPER(IFNULL(comment,''))";
        $s = "UPPER(IFNULL(system_comment,''))";
        $p = "UPPER(IFNULL(paymethod,''))";
        $is_void = "($c LIKE 'VOID%' OR $s LIKE 'VOID%')";
        $is_bb = "($c LIKE '%BEGINNING BALANCE%')";
        $is_mig = "($s LIKE '%CONTRIBUTION_MIGRATED%' OR $c LIKE '%CONTRIBUTION_MIGRATED%')";
        // MySQL REGEXP: keep '-' at end of the class. '[\s#\-/]' becomes an invalid
        // range (\ to /) and raises error 1139. Prefer POSIX [[:space:]] over \s.
        $is_cr = "($p LIKE '%CASH RECEIPT%' OR $c LIKE '%CASH RECEIPT%' OR $c REGEXP '^(CV|OR|CR)[[:space:]#/-]')";
        $is_cd = "($p LIKE '%CASH DISBURSEMENT%' OR $c LIKE '%CASH DISBURSEMENT%' OR $c REGEXP '^(CDS|CD)[[:space:]#/-]')";
        $is_jv = "($p LIKE '%JOURNAL%' OR $c LIKE '%JOURNAL%' OR $c REGEXP '^JV[[:space:]#/-]')";
        $is_loan = "($c LIKE '%LOAN%' OR $c REGEXP '^LN[[:space:]#/-]')";
        $is_auto = "(IFNULL(auto,0) = 1)";

        switch ($source) {
            case 'void':
                return " AND $is_void";
            case 'beginning_balance':
                return " AND $is_bb";
            case 'migration':
                return " AND $is_mig";
            case 'cash_receipt':
                return " AND NOT $is_void AND NOT $is_bb AND NOT $is_mig AND $is_cr";
            case 'cash_disbursement':
                return " AND NOT $is_void AND NOT $is_bb AND NOT $is_mig AND NOT $is_cr AND $is_cd";
            case 'journal_entry':
                return " AND NOT $is_void AND NOT $is_bb AND NOT $is_mig AND NOT $is_cr AND NOT $is_cd AND $is_jv";
            case 'auto_contribution':
                return " AND NOT $is_void AND NOT $is_bb AND NOT $is_mig AND NOT $is_cr AND NOT $is_cd AND NOT $is_jv AND $is_auto";
            case 'loan_disbursement':
                return " AND NOT $is_void AND NOT $is_bb AND NOT $is_mig AND NOT $is_cr AND NOT $is_cd AND NOT $is_jv AND NOT $is_auto AND $is_loan";
            case 'cbu_transaction':
                return " AND NOT $is_void AND NOT $is_bb AND NOT $is_mig AND NOT $is_cr AND NOT $is_cd AND NOT $is_jv AND NOT $is_auto AND NOT $is_loan";
            default:
                return '';
        }
    }

    /**
     * SQL fragment for GL Posted filter on contribution_transaction.
     * Beginning Balance uses contribution_settings.posted; others use general_ledger.
     */
    function contribution_posted_filter_sql($posted) {
        $posted = strtolower(trim((string) $posted));
        if ($posted === '' || $posted === 'all') {
            return '';
        }

        $is_bb = "UPPER(IFNULL(comment,'')) LIKE '%BEGINNING BALANCE%'";
        $bb_posted = "EXISTS (
            SELECT 1 FROM contribution_settings cs
            WHERE cs.PID = contribution_transaction.PID
              AND cs.member_id = contribution_transaction.member_id
              AND cs.PIN = contribution_transaction.PIN
              AND cs.posted = 1
        )";
        $rcpt_posted = "EXISTS (
            SELECT 1 FROM general_ledger gl
            WHERE gl.refferenceID = contribution_transaction.receipt
              AND gl.fromtable = 'contribution_transaction'
              AND gl.PIN = contribution_transaction.PIN
        )";
        $is_posted = "(($is_bb AND $bb_posted) OR (NOT ($is_bb) AND $rcpt_posted))";

        if ($posted === 'posted') {
            return " AND $is_posted";
        }
        if ($posted === 'unposted' || $posted === 'not_posted') {
            return " AND NOT ($is_posted)";
        }
        return '';
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
    
    
     function count_transaction($key, $from, $upto, $source = 'all', $posted = 'all') {
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

        $and .= $this->contribution_source_filter_sql($source);
        $and .= $this->contribution_posted_filter_sql($posted);

        return count($this->db->query("SELECT * FROM contribution_transaction WHERE $and ORDER BY createdon DESC")->result());
    }

    function search_transaction($key, $from, $upto, $limit, $start, $source = 'all', $posted = 'all') {
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

        $and .= $this->contribution_source_filter_sql($source);
        $and .= $this->contribution_posted_filter_sql($posted);

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
            // Posting to GL - CBU side only (Journal 8). Offset belongs in Finance → Beginning Balances.
            $capital_account_info = account_row_info($capital_build_up_account);
            if (!$capital_account_info) {
                return FALSE;
            }

            $amount = floatval($amount);
            $is_equity = $this->is_equity_account_type($capital_account_info->account_type);
            $debit = $is_equity ? 0 : $amount;
            $credit = $is_equity ? $amount : 0;

            if (!empty($existing_entry)) {
                // Keep one CBU line; sync amount if setting was corrected after a partial post.
                $existing_amount = floatval($existing_entry->credit) + floatval($existing_entry->debit);
                if (abs($existing_amount - $amount) > 0.009
                    || trim((string) $existing_entry->account) !== trim((string) $capital_build_up_account)
                    || intval($existing_entry->journalID) !== 8) {
                    $this->db->where('id', $existing_entry->id);
                    $this->db->where('PIN', $pin);
                    $this->db->update('general_ledger', array(
                        'journalID' => 8,
                        'date' => $trans_date,
                        'description' => 'CBU Beginning Balance - ' . $member_id,
                        'account' => $capital_build_up_account,
                        'debit' => $debit,
                        'credit' => $credit,
                        'account_type' => $capital_account_info->account_type,
                        'sub_account_type' => isset($capital_account_info->sub_account_type) ? $capital_account_info->sub_account_type : null,
                        'PID' => $pid,
                        'member_id' => $member_id,
                    ));
                }
                return TRUE;
            }

            if (function_exists('gl_reject_closed_date')) {
                $lock_msg = gl_reject_closed_date($trans_date);
                if ($lock_msg) {
                    log_message('error', 'CBU BB GL blocked by books close: ' . $lock_msg);
                    return FALSE;
                }
            }

            $ledger_entry = array(
                'date' => $trans_date,
                'PIN' => $pin
            );
            $this->db->insert('general_ledger_entry', $ledger_entry);
            $ledger_entry_id = $this->db->insert_id();
            if (!$ledger_entry_id) {
                return FALSE;
            }

            $ledger = array(
                'journalID' => 8, // Beginning Balance journal (same as Savings/Finance BB)
                'refferenceID' => $id,
                'entryid' => $ledger_entry_id,
                'date' => $trans_date,
                'description' => 'CBU Beginning Balance - ' . $member_id,
                'linkto' => 'contribution_settings.id',
                'fromtable' => 'contribution_settings',
                'paid' => 0,
                'PID' => $pid,
                'member_id' => $member_id,
                'PIN' => $pin,
                'account' => $capital_build_up_account,
                // Equity CBU: credit increases capital. Legacy asset CBU: debit increases asset.
                'debit' => $debit,
                'credit' => $credit,
                'account_type' => $capital_account_info->account_type,
                'sub_account_type' => isset($capital_account_info->sub_account_type) ? $capital_account_info->sub_account_type : null,
            );
            $this->db->insert('general_ledger', $ledger);
            if ($this->db->affected_rows() <= 0) {
                return FALSE;
            }

            return TRUE;
        } else {
            // Unposting - reverse GL with reversing entry (keep original for audit)
            if (!empty($existing_entry)) {
                $this->load->model('finance_model');
                $gl_void = $this->finance_model->void_gl_lines_with_reversal(
                    'contribution_settings',
                    $id,
                    'Unpost CBU beginning balance',
                    array('use_source_date' => true)
                );
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
     * Whether a chart account_type is equity (normal credit balance).
     * Supports legacy codes 30/40 and standard 30000/40000 ranges used in COA.
     *
     * @param mixed $account_type
     * @return bool
     */
    function is_equity_account_type($account_type) {
        $type = (int) $account_type;
        if (in_array($type, array(30, 40, 30000, 40000), true)) {
            return true;
        }
        if ($type >= 30000 && $type < 50000) {
            return true;
        }
        return false;
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
        if (strpos($comment, 'BEGINNING BALANCE') !== FALSE
            || strpos($system, 'BEGINNING BALANCE') !== FALSE) {
            return $this->post_contribution_bb_receipt_to_gl($receipt);
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
        $gl_void = $this->finance_model->void_gl_lines_with_reversal(
            'contribution_settings',
            $id,
            $reason !== '' ? $reason : 'Void CBU beginning balance',
            array('use_source_date' => true)
        );
        if (empty($gl_void['success'])) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => !empty($gl_void['message']) ? $gl_void['message'] : 'GL reverse failed.');
        }

        $pid = $settings->PID;
        $member_id = $settings->member_id;
        $amount = floatval($settings->amount);
        $comment = 'VOID BEGINNING BALANCE' . ($reason !== '' ? (' — ' . $reason) : '');

        // Only reverse sub-ledger when a non-void BEGINNING BALANCE CR still exists
        // (orphan Posted rows may have GL/flag without an SL credit).
        $pin_esc = $this->db->escape($pin);
        $pid_esc = $this->db->escape($pid);
        $mid_esc = $this->db->escape(trim($member_id));
        $bb_tx = $this->db->query(
            "SELECT id FROM contribution_transaction
             WHERE PIN = {$pin_esc}
               AND PID = {$pid_esc}
               AND TRIM(member_id) = {$mid_esc}
               AND trans_type = 'CR'
               AND (
                   UPPER(COALESCE(comment,'')) LIKE '%BEGINNING BALANCE%'
                   OR UPPER(COALESCE(system_comment,'')) LIKE '%BEGINNING BALANCE%'
               )
               AND UPPER(COALESCE(comment,'')) NOT LIKE '%VOID%'
               AND UPPER(COALESCE(system_comment,'')) NOT LIKE '%VOID%'
             LIMIT 1"
        )->row();

        $receipt = null;
        if ($bb_tx) {
            $receipt = $this->contribution_transaction('DR', $pid, $member_id, $amount, 'JOURNAL', $comment, '', '', 0, date('Y-m-d H:i:s'));
            if (!$receipt) {
                $this->db->_trans_status = FALSE;
                $this->db->trans_complete();
                return array('success' => false, 'message' => 'Failed to reverse CBU sub-ledger balance.');
            }
        } else {
            // Orphan posted flag: ensure masterfile balance is not left negative.
            $this->ensure_members_contribution($pid, $member_id, $pin);
        }

        $this->post_to_gl($id, 0);
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Void failed.');
        }
        return array('success' => true, 'message' => 'CBU beginning balance voided with reversing GL and sub-ledger entry.', 'receipt' => $receipt);
    }

    /**
     * Default migration as-of date for CBU beginning balances.
     */
    function cbu_bb_default_as_of_date() {
        return '2024-12-31';
    }

    /**
     * Resolve configured Capital Build Up COA + account_chart row.
     *
     * @return object|null {account, info} or null
     */
    function get_cbu_capital_account() {
        $this->load->model('setting_model');
        $global_contribution = $this->setting_model->global_contribution_info();
        $coa = isset($global_contribution->capital_build_up_account) ? trim((string) $global_contribution->capital_build_up_account) : '';
        if ($coa === '') {
            return null;
        }
        $info = account_row_info($coa);
        if (!$info) {
            return null;
        }
        return (object) array('account' => $coa, 'info' => $info);
    }

    /**
     * Post a contribution_transaction BEGINNING BALANCE receipt as CBU-only GL (Journal 8).
     *
     * @param string $receipt
     * @return array {success, message}
     */
    function post_contribution_bb_receipt_to_gl($receipt) {
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

        $cbu = $this->get_cbu_capital_account();
        if (!$cbu) {
            return array('success' => false, 'message' => 'Capital Build Up Account is not configured or missing from chart of accounts');
        }

        $amount = floatval($trans->amount);
        if ($amount <= 0) {
            return array('success' => false, 'message' => 'Invalid amount');
        }

        $trans_date = !empty($trans->createdon) ? date('Y-m-d', strtotime($trans->createdon)) : date('Y-m-d');
        if (function_exists('gl_reject_closed_date')) {
            $lock_msg = gl_reject_closed_date($trans_date);
            if ($lock_msg) {
                return array('success' => false, 'message' => $lock_msg);
            }
        }

        $trans_type = strtoupper(trim($trans->trans_type));
        $is_equity = $this->is_equity_account_type($cbu->info->account_type);
        // Equity: CR → credit CBU; DR → debit CBU. Asset CBU flips those sides.
        $credit_cbu = ($trans_type !== 'DR');
        if (!$is_equity) {
            $credit_cbu = !$credit_cbu;
        }

        $member_id = isset($trans->member_id) ? $trans->member_id : '';
        $pid = isset($trans->PID) ? $trans->PID : '';

        $this->db->trans_start();
        $this->db->insert('general_ledger_entry', array('date' => $trans_date, 'PIN' => $pin));
        $ledger_entry_id = $this->db->insert_id();
        if (!$ledger_entry_id) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Failed to create GL entry header');
        }

        $ledger = array(
            'journalID' => 8,
            'refferenceID' => $receipt,
            'entryid' => $ledger_entry_id,
            'date' => $trans_date,
            'description' => 'CBU Beginning Balance - ' . $member_id . ' (Receipt: ' . $receipt . ')',
            'linkto' => 'contribution_transaction.receipt',
            'fromtable' => 'contribution_transaction',
            'paid' => 0,
            'PID' => $pid,
            'member_id' => $member_id,
            'PIN' => $pin,
            'account' => $cbu->account,
            'debit' => $credit_cbu ? 0 : $amount,
            'credit' => $credit_cbu ? $amount : 0,
            'account_type' => $cbu->info->account_type,
            'sub_account_type' => isset($cbu->info->sub_account_type) ? $cbu->info->sub_account_type : null,
        );
        $this->db->insert('general_ledger', $ledger);
        if ($this->db->affected_rows() <= 0) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => 'Failed to insert CBU BB GL line');
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'GL posting failed');
        }
        return array('success' => true, 'message' => 'CBU beginning balance posted to GL (CBU account only)');
    }

    /**
     * Tag contribution_transaction rows on a given date as BEGINNING BALANCE / ADJUSTMENT.
     *
     * @param string $as_of_date Y-m-d
     * @return array
     */
    function tag_contribution_transactions_as_beginning_balance($as_of_date) {
        $pin = current_user()->PIN;
        $as_of_date = date('Y-m-d', strtotime($as_of_date));
        $result = array(
            'tagged' => 0,
            'already_bb' => 0,
            'skipped_void' => 0,
            'skipped_journal' => 0,
            'total' => 0,
            'errors' => array()
        );

        $this->db->where('PIN', $pin);
        $this->db->where('DATE(createdon) = ' . $this->db->escape($as_of_date), NULL, FALSE);
        $rows = $this->db->get('contribution_transaction')->result();
        $result['total'] = count($rows);

        foreach ($rows as $row) {
            $comment = strtoupper(trim(isset($row->comment) ? $row->comment : ''));
            $system = strtoupper(trim(isset($row->system_comment) ? $row->system_comment : ''));
            $paymethod = strtoupper(trim(isset($row->paymethod) ? $row->paymethod : ''));

            if (strpos($comment, 'VOID') !== FALSE || strpos($system, 'VOID') !== FALSE) {
                $result['skipped_void']++;
                continue;
            }
            if ($paymethod === 'JOURNAL' || strpos($paymethod, 'JOURNAL') !== FALSE
                || strpos($paymethod, 'CASH RECEIPT') !== FALSE
                || strpos($paymethod, 'CASH DISBURSEMENT') !== FALSE) {
                $result['skipped_journal']++;
                continue;
            }
            if (strpos($comment, 'BEGINNING BALANCE') !== FALSE
                && $paymethod === 'ADJUSTMENT'
                && strpos($system, 'BEGINNING BALANCE') !== FALSE) {
                $result['already_bb']++;
                continue;
            }

            $this->db->where('id', $row->id);
            $this->db->where('PIN', $pin);
            $ok = $this->db->update('contribution_transaction', array(
                'comment' => 'BEGINNING BALANCE',
                'system_comment' => 'BEGINNING BALANCE',
                'paymethod' => 'ADJUSTMENT'
            ));
            if ($ok) {
                $result['tagged']++;
            } else {
                $result['errors'][] = 'Failed tagging receipt ' . $row->receipt;
            }
        }

        return $result;
    }

    /**
     * Summarise tagged CBU BB amounts for an as-of date (single CBU COA).
     *
     * @param string $as_of_date Y-m-d
     * @return array
     */
    function get_cbu_bb_coa_summary($as_of_date) {
        $pin = current_user()->PIN;
        $as_of_date = date('Y-m-d', strtotime($as_of_date));
        $cbu = $this->get_cbu_capital_account();
        if (!$cbu) {
            return array();
        }

        $pin_esc = $this->db->escape($pin);
        $date_esc = $this->db->escape($as_of_date);
        $sql = "SELECT
                    SUM(CASE WHEN UPPER(TRIM(ct.trans_type)) = 'DR' THEN 0 ELSE CAST(ct.amount AS DECIMAL(18,2)) END) AS credit_total,
                    SUM(CASE WHEN UPPER(TRIM(ct.trans_type)) = 'DR' THEN CAST(ct.amount AS DECIMAL(18,2)) ELSE 0 END) AS debit_total,
                    COUNT(DISTINCT ct.receipt) AS tx_count,
                    SUM(CASE WHEN posted.receipt IS NULL THEN 1 ELSE 0 END) AS unposted_count
                FROM contribution_transaction ct
                LEFT JOIN (
                    SELECT DISTINCT refferenceID AS receipt, PIN
                    FROM general_ledger
                    WHERE fromtable = 'contribution_transaction' AND PIN = {$pin_esc}
                ) posted ON posted.receipt = ct.receipt AND posted.PIN = ct.PIN
                WHERE ct.PIN = {$pin_esc}
                  AND DATE(ct.createdon) = {$date_esc}
                  AND (
                      UPPER(COALESCE(ct.comment, '')) LIKE '%BEGINNING BALANCE%'
                      OR UPPER(COALESCE(ct.system_comment, '')) LIKE '%BEGINNING BALANCE%'
                  )
                  AND UPPER(COALESCE(ct.comment, '')) NOT LIKE '%VOID%'
                  AND UPPER(COALESCE(ct.system_comment, '')) NOT LIKE '%VOID%'";
        $row = $this->db->query($sql)->row();
        if (!$row || intval($row->tx_count) === 0) {
            return array();
        }

        $credit = floatval($row->credit_total);
        $debit = floatval($row->debit_total);
        return array((object) array(
            'coa_account' => $cbu->account,
            'coa_name' => isset($cbu->info->name) ? $cbu->info->name : $cbu->account,
            'credit_total' => $credit,
            'debit_total' => $debit,
            'net_credit' => $credit - $debit,
            'tx_count' => intval($row->tx_count),
            'unposted_count' => intval($row->unposted_count),
        ));
    }

    /**
     * Remove non-CBU GL lines from BB receipts (contribution_transaction) on as-of date.
     * Also strips offset sides from contribution_settings BB GL for the same date.
     *
     * @param string $as_of_date Y-m-d
     * @return array
     */
    function strip_cbu_bb_gl_offset_sides($as_of_date) {
        $pin = current_user()->PIN;
        $as_of_date = date('Y-m-d', strtotime($as_of_date));
        $result = array(
            'stripped_receipts' => 0,
            'lines_removed' => 0,
            'skipped' => 0,
            'errors' => array()
        );

        $cbu = $this->get_cbu_capital_account();
        if (!$cbu) {
            $result['errors'][] = 'Capital Build Up Account is not configured';
            return $result;
        }
        $liability = trim((string) $cbu->account);

        if (function_exists('gl_reject_closed_date')) {
            $lock_msg = gl_reject_closed_date($as_of_date);
            if ($lock_msg) {
                $result['errors'][] = $lock_msg;
                return $result;
            }
        }

        $pin_esc = $this->db->escape($pin);
        $date_esc = $this->db->escape($as_of_date);

        // Transaction-based BB receipts
        $sql = "SELECT ct.receipt
                FROM contribution_transaction ct
                WHERE ct.PIN = {$pin_esc}
                  AND DATE(ct.createdon) = {$date_esc}
                  AND (
                      UPPER(COALESCE(ct.comment, '')) LIKE '%BEGINNING BALANCE%'
                      OR UPPER(COALESCE(ct.system_comment, '')) LIKE '%BEGINNING BALANCE%'
                  )
                  AND UPPER(COALESCE(ct.comment, '')) NOT LIKE '%VOID%'";
        $txs = $this->db->query($sql)->result();
        foreach ($txs as $tx) {
            $removed = $this->_strip_non_cbu_gl_lines('contribution_transaction', $tx->receipt, $liability);
            if ($removed > 0) {
                $result['stripped_receipts']++;
                $result['lines_removed'] += $removed;
            } else {
                $result['skipped']++;
            }
        }

        // Masterfile BB rows posted on this date
        $sql2 = "SELECT cs.id
                 FROM contribution_settings cs
                 WHERE cs.PIN = {$pin_esc}
                   AND cs.posted = 1
                   AND DATE(cs.posting_date) = {$date_esc}";
        $settings = $this->db->query($sql2)->result();
        foreach ($settings as $setting) {
            $removed = $this->_strip_non_cbu_gl_lines('contribution_settings', $setting->id, $liability);
            if ($removed > 0) {
                $result['stripped_receipts']++;
                $result['lines_removed'] += $removed;
            } else {
                $result['skipped']++;
            }
        }

        return $result;
    }

    /**
     * Delete GL lines that are not the CBU COA for a given fromtable/refferenceID.
     *
     * @return int lines removed
     */
    function _strip_non_cbu_gl_lines($fromtable, $refference_id, $cbu_account) {
        $pin = current_user()->PIN;
        $this->db->where('refferenceID', $refference_id);
        $this->db->where('fromtable', $fromtable);
        $this->db->where('PIN', $pin);
        $gl_lines = $this->db->get('general_ledger')->result();
        if (empty($gl_lines)) {
            return 0;
        }

        $removed = 0;
        foreach ($gl_lines as $line) {
            if (trim((string) $line->account) === trim((string) $cbu_account)) {
                $this->db->where('id', $line->id);
                $this->db->update('general_ledger', array(
                    'journalID' => 8,
                    'description' => 'CBU Beginning Balance [' . $fromtable . ':' . $refference_id . ']'
                ));
                continue;
            }
            $this->db->where('id', $line->id);
            $this->db->where('PIN', $pin);
            if ($this->db->delete('general_ledger')) {
                $removed++;
            }
        }
        return $removed;
    }

    /**
     * Post all unposted BEGINNING BALANCE contribution_transaction rows for an as-of date.
     *
     * @param string $as_of_date Y-m-d
     * @return array
     */
    function post_unposted_cbu_beginning_balances_to_gl($as_of_date) {
        $pin = current_user()->PIN;
        $as_of_date = date('Y-m-d', strtotime($as_of_date));
        $result = array(
            'posted' => 0,
            'failed' => 0,
            'errors' => array(),
            'coa_totals' => array()
        );

        $pin_esc = $this->db->escape($pin);
        $date_esc = $this->db->escape($as_of_date);
        $sql = "SELECT ct.*
                FROM contribution_transaction ct
                LEFT JOIN (
                    SELECT DISTINCT refferenceID AS receipt, PIN
                    FROM general_ledger
                    WHERE fromtable = 'contribution_transaction' AND PIN = {$pin_esc}
                ) posted ON posted.receipt = ct.receipt AND posted.PIN = ct.PIN
                WHERE ct.PIN = {$pin_esc}
                  AND DATE(ct.createdon) = {$date_esc}
                  AND (
                      UPPER(COALESCE(ct.comment, '')) LIKE '%BEGINNING BALANCE%'
                      OR UPPER(COALESCE(ct.system_comment, '')) LIKE '%BEGINNING BALANCE%'
                  )
                  AND UPPER(COALESCE(ct.comment, '')) NOT LIKE '%VOID%'
                  AND posted.receipt IS NULL
                ORDER BY ct.createdon ASC, ct.id ASC";
        $unposted = $this->db->query($sql)->result();

        foreach ($unposted as $ct) {
            $post = $this->post_contribution_bb_receipt_to_gl($ct->receipt);
            if (!empty($post['success']) && $this->is_contribution_receipt_posted_to_gl($ct->receipt)) {
                $result['posted']++;
                $cbu = $this->get_cbu_capital_account();
                $coa = $cbu ? $cbu->account : 'UNKNOWN';
                if (!isset($result['coa_totals'][$coa])) {
                    $result['coa_totals'][$coa] = 0;
                }
                $is_dr = (strtoupper(trim($ct->trans_type)) === 'DR');
                $result['coa_totals'][$coa] += $is_dr ? -floatval($ct->amount) : floatval($ct->amount);
            } else {
                $result['failed']++;
                $result['errors'][] = 'Receipt ' . $ct->receipt . ': ' . (isset($post['message']) ? $post['message'] : 'post failed');
            }
        }

        return $result;
    }

    /**
     * Members with BEGINNING BALANCE contribution txs (for list page).
     */
    function search_cbu_with_beginning_balance($key = null, $limit = 40, $start = 0) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $sql = "SELECT mc.PID, mc.member_id, mc.balance,
                       m.firstname, m.middlename, m.lastname,
                       (SELECT MAX(ct2.createdon) FROM contribution_transaction ct2
                        WHERE ct2.PID = mc.PID AND TRIM(ct2.member_id) = TRIM(mc.member_id) AND ct2.PIN = {$pin_esc}
                          AND (UPPER(COALESCE(ct2.comment,'')) LIKE '%BEGINNING BALANCE%'
                               OR UPPER(COALESCE(ct2.system_comment,'')) LIKE '%BEGINNING BALANCE%')
                       ) AS bb_date
                FROM members_contribution mc
                INNER JOIN contribution_transaction ct
                    ON ct.PID = mc.PID AND TRIM(ct.member_id) = TRIM(mc.member_id) AND ct.PIN = {$pin_esc}
                    AND (UPPER(COALESCE(ct.comment,'')) LIKE '%BEGINNING BALANCE%'
                         OR UPPER(COALESCE(ct.system_comment,'')) LIKE '%BEGINNING BALANCE%')
                LEFT JOIN members m ON m.PID = mc.PID AND m.PIN = {$pin_esc}
                WHERE (mc.PIN = {$pin_esc} OR mc.PIN = 0 OR mc.PIN IS NULL)";
        if (!is_null($key) && $key !== '') {
            $key_esc = $this->db->escape_like_str($key);
            $sql .= " AND (mc.member_id LIKE '%{$key_esc}%' OR mc.PID LIKE '%{$key_esc}%'
                        OR m.firstname LIKE '%{$key_esc}%' OR m.lastname LIKE '%{$key_esc}%')";
        }
        $sql .= " GROUP BY mc.PID, mc.member_id, mc.balance, m.firstname, m.middlename, m.lastname
                  ORDER BY mc.member_id ASC
                  LIMIT " . intval($start) . ", " . intval($limit);
        return $this->db->query($sql)->result();
    }

    function count_cbu_with_beginning_balance($key = null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $sql = "SELECT COUNT(DISTINCT CONCAT(mc.PID, '|', mc.member_id)) AS cnt
                FROM members_contribution mc
                INNER JOIN contribution_transaction ct
                    ON ct.PID = mc.PID AND TRIM(ct.member_id) = TRIM(mc.member_id) AND ct.PIN = {$pin_esc}
                    AND (UPPER(COALESCE(ct.comment,'')) LIKE '%BEGINNING BALANCE%'
                         OR UPPER(COALESCE(ct.system_comment,'')) LIKE '%BEGINNING BALANCE%')
                LEFT JOIN members m ON m.PID = mc.PID AND m.PIN = {$pin_esc}
                WHERE (mc.PIN = {$pin_esc} OR mc.PIN = 0 OR mc.PIN IS NULL)";
        if (!is_null($key) && $key !== '') {
            $key_esc = $this->db->escape_like_str($key);
            $sql .= " AND (mc.member_id LIKE '%{$key_esc}%' OR mc.PID LIKE '%{$key_esc}%'
                        OR m.firstname LIKE '%{$key_esc}%' OR m.lastname LIKE '%{$key_esc}%')";
        }
        $row = $this->db->query($sql)->row();
        return $row ? intval($row->cnt) : 0;
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

        // Deleting a BEGINNING BALANCE CR must clear the setting Posted flag
        // (otherwise Masterfile stays Posted Yes with Balance 0.00).
        $comment_u = strtoupper(trim(isset($transaction->comment) ? $transaction->comment : ''));
        $system_u = strtoupper(trim(isset($transaction->system_comment) ? $transaction->system_comment : ''));
        $is_bb_cr = ($trans_type === 'CR'
            && (strpos($comment_u, 'BEGINNING BALANCE') !== FALSE || strpos($system_u, 'BEGINNING BALANCE') !== FALSE)
            && strpos($comment_u, 'VOID') === FALSE
            && strpos($system_u, 'VOID') === FALSE);
        if ($is_bb_cr) {
            $this->db->where('PID', $pid);
            $this->db->where('member_id', $member_id);
            $this->db->where('PIN', $pin);
            $this->db->where('posted', 1);
            $setting = $this->db->get('contribution_settings')->row();
            if ($setting) {
                $this->post_to_gl($setting->id, 0);
                $this->load->model('finance_model');
                $this->finance_model->void_gl_lines_with_reversal(
                    'contribution_settings',
                    $setting->id,
                    'CBU BB transaction deleted',
                    array('use_source_date' => true)
                );
            }
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return FALSE;
        }
        
        return TRUE;
    }

}

?>
