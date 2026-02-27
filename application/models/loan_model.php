<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of loan_model
 *
 * @author miltone
 */
class Loan_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function is_loan_product_exist($product_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('id', $product_id);
        $come = $this->db->get('loan_product')->row();
        if (count($come) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function is_loan_exist($loan_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('LID', $loan_id);
        $come = $this->db->get('loan_contract')->row();
        if (count($come) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function get_declaration($loanid) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('LID', $loanid);
        $come = $this->db->get('loan_contract_declaration')->row();
        if ($come) {
            return $come;
        } else {
            $new = new stdClass();
            $new->declaration = '--------';
            return $new;
        }
    }

    function get_supporting_doc($loanid) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('LID', $loanid);
        return $this->db->get('loan_contract_supportdoc')->result();
    }

    function loan_declaration($data) {
        $pin = current_user()->PIN;
        $check = $this->db->get_where('loan_contract_declaration', array('LID' => $data['LID'], 'PIN' => $pin))->row();
        if (count($check) == 1) {
            return $this->db->update('loan_contract_declaration', $data, array('LID' => $data['LID'], 'PIN' => $pin));
        } else {
            return $this->db->insert('loan_contract_declaration', $data);
        }
    }

    function loan_evaluation_history($loanid) {
        $sql = "SELECT loan_contract_evaluation.*,loan_status.name,users.first_name,users.last_name FROM loan_contract_evaluation "
                . "INNER JOIN loan_status  ON loan_status.code = loan_contract_evaluation.status "
                . "INNER JOIN users  ON loan_contract_evaluation.createdby = users.id  WHERE loan_contract_evaluation.LID='$loanid'  order by loan_contract_evaluation.createdon desc";
        return $this->db->query($sql);
    }

    function loan_approval_history($loanid) {
        $sql = "SELECT loan_contract_approve.*,loan_status.name,users.first_name,users.last_name FROM loan_contract_approve "
                . "INNER JOIN loan_status  ON loan_status.code = loan_contract_approve.status "
                . "INNER JOIN users  ON loan_contract_approve.createdby = users.id  WHERE loan_contract_approve.LID='$loanid'  order by loan_contract_approve.createdon desc";
        return $this->db->query($sql);
    }

    function loan_disburse_history($loanid) {
        $sql = "SELECT loan_contract_disburse.*,users.first_name,users.last_name FROM loan_contract_disburse "
                . "INNER JOIN users  ON loan_contract_disburse.createdby = users.id  WHERE loan_contract_disburse.LID='$loanid'  order by loan_contract_disburse.createdon desc";
        return $this->db->query($sql);
    }

    function get_guarantor($id = null, $loanid = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($loanid)) {
            $this->db->where('LID', $loanid);
        }

        return $this->db->get('loan_contract_guarantor');
    }

    function add_guarantor($data, $edit = null) {
        $check = $this->db->get_where('loan_contract_declaration', array('LID' => $data['LID']))->row();
        if (!is_null($edit)) {
            return $this->db->update('loan_contract_guarantor', $data, array('id' => $edit));
        } else {
            return $this->db->insert('loan_contract_guarantor', $data);
        }
    }

    function loan_supporting_doc($data) {

        return $this->db->insert('loan_contract_supportdoc', $data);
    }

    function loan_info($loanid = null, $pin = null, $member_id = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($loanid)) {
            $this->db->where('LID', $loanid);
        }
        if (!is_null($pin)) {
            $this->db->where('PID', $pin);
        }
        if (!is_null($member_id)) {
            $this->db->where('member_id', $member_id);
        }

        return $this->db->get('loan_contract');
    }

    function edit_loan_info($data, $loanid) {
        $this->db->update('loan_contract', $data, array('LID' => $loanid));
        return $loanid;
    }

    /**
     * Get next LN number for display (does not increment auto_inc)
     */
    function get_next_ln_number() {
        $row = $this->db->get('auto_inc')->row();
        $next = $row ? ($row->loan + 1) : 1;
        return 'LN' . $next;
    }

    function add_newloan($data, $processingfee = 0) {
        // Use LID from $data if provided and not empty; otherwise auto-generate
        if (!empty($data['LID'])) {
            $lid = trim($data['LID']);
            // Check uniqueness
            if ($this->is_loan_exist($lid)) {
                return FALSE;
            }
            $data['LID'] = $lid;
            // Ensure auto_inc stays ahead if LID is numeric (e.g. LN1234)
            if (preg_match('/^LN(\d+)$/', $lid, $m)) {
                $num = (int)$m[1];
                $current = $this->db->get('auto_inc')->row()->loan;
                if ($num >= $current) {
                    $this->db->set('loan', $num + 1, FALSE);
                    $this->db->update('auto_inc');
                }
            }
        } else {
            $loanid = $this->db->get('auto_inc')->row()->loan;
            $this->db->set('loan', 'loan+1', FALSE);
            $this->db->update('auto_inc');
            $data['LID'] = 'LN' . $loanid;
        }

        $insert = $this->db->insert('loan_contract', $data);
        if ($insert) {




            $array_registration = array(
                'PID' => $data['PID'],
                'member_id' => $data['member_id'],
                'amount' => $processingfee,
                'createdby' => current_user()->id,
                'PIN' => $data['PIN'],
                'LID' => $data['LID']
            );

            $this->db->insert('loanprocessing_fee', $array_registration);
            $refferenceid = $this->db->insert_id();
            //now insert to income journal
            $credit_account = 4000002;
            $debit_account = 1010003;

            $ledger_entry = array('date' => date('Y-m-d'));
            $this->db->insert('general_ledger_entry', $ledger_entry);
            $ledger_entry_id = $this->db->insert_id();

            //update ledger book
            $ledgerbook = array(
                'journalID' => 2,
                'refferenceID' => $refferenceid,
                'entryid' => $ledger_entry_id,
                'date' => date('Y-m-d'),
                'description' => 'Loan Processing Fee ',
                'linkto' => 'loanprocessing_fee.id',
                'fromtable' => 'loanprocessing_fee',
                'PID' => $data['PID'],
                'member_id' => $data['member_id'],
                'PIN' => $data['PIN']
            );

            $ledgerbook['account'] = $credit_account;
            $ledgerbook['credit'] = $processingfee;
            $infoaccount = account_row_info($ledgerbook['account']);
            $ledgerbook['account_type'] = $infoaccount->account_type;
            $ledgerbook['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledgerbook);

            $ledgerbook['credit'] = 0;
            $ledgerbook['debit'] = 0;
            //retain earning
            $ledgerbook['account'] = 3000002;
            $ledgerbook['credit'] = $processingfee;
            $infoaccount = account_row_info($ledgerbook['account']);
            $ledgerbook['account_type'] = $infoaccount->account_type;
            $ledgerbook['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledgerbook);

            $ledgerbook['credit'] = 0;
            $ledgerbook['debit'] = 0;
            $ledgerbook['account'] = $debit_account;
            $infoaccount = account_row_info($ledgerbook['account']);
            $ledgerbook['account_type'] = $infoaccount->account_type;
            $ledgerbook['sub_account_type'] = $infoaccount->sub_account_type;
            $ledgerbook['debit'] = $processingfee;
            $this->db->insert('general_ledger', $ledgerbook);


            return $data['LID'];
        }

        return FALSE;
    }

    function loan_wait_evaluation() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT * FROM loan_contract WHERE PIN='$pin' AND (status=0 OR status=3) ORDER BY applicationdate DESC")->result();
    }

    function loan_wait_approval() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT * FROM loan_contract WHERE PIN='$pin' AND status=1 ORDER BY applicationdate DESC")->result();
    }

    function loan_wait_disburse() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT * FROM loan_contract WHERE PIN='$pin' AND status=4 AND disburse=0 ORDER BY applicationdate DESC")->result();
    }

    /**
     * Get GL account code for loan disbursement credit (source of funds) from payment method.
     * Uses paymentmenthod.gl_account_code if set, else fallback search by name in account_chart.
     */
    function get_credit_account_for_payment_method($payment_method_id) {
        $pin = current_user()->PIN;
        $payment_method_id = (int) $payment_method_id;
        if ($payment_method_id <= 0) {
            return null;
        }
        $this->load->model('payment_method_config_model');
        $pm = $this->payment_method_config_model->get_payment_method_by_id($payment_method_id, $pin);
        if (!$pm) {
            return null;
        }
        if (!empty($pm->gl_account_code)) {
            $ac = $this->db->query('SELECT account FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array(trim($pm->gl_account_code), $pin))->row();
            if ($ac) {
                return $ac->account;
            }
        }
        $payment_method_name = trim((string) $pm->name);
        if ($payment_method_name === '') {
            $payment_method_name = 'Cash';
        }
        $account = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND name LIKE ? AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%' . $this->db->escape_like_str($payment_method_name) . '%')
        )->row();
        if ($account) {
            return $account->account;
        }
        $mapping = array('Cash' => 'Cash', 'Cheque' => 'Bank', 'Bank Transfer' => 'Bank', 'BANK DEPOSIT' => 'Bank', 'Bank Deposit' => 'Bank', 'M-PESA' => 'Mobile Money', 'TIGO PESA' => 'Mobile Money');
        $name = isset($mapping[$payment_method_name]) ? $mapping[$payment_method_name] : 'Cash';
        $account = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND name LIKE ? AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%' . $this->db->escape_like_str($name) . '%')
        )->row();
        if ($account) {
            return $account->account;
        }
        $account = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND (name LIKE ? OR name LIKE ?) AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%Cash%', '%Bank%')
        )->row();
        return $account ? $account->account : null;
    }

    /**
     * Get next loan disbursement number (e.g. LD-00001, LD-00002). Same pattern as cash disbursement.
     */
    function get_next_loan_disburse_no() {
        $pin = current_user()->PIN;
        if (!$this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'disburse_no'")->row()) {
            return 'LD-00001';
        }
        $this->db->select('disburse_no');
        $this->db->where('PIN', $pin);
        $this->db->where('disburse_no IS NOT NULL');
        $this->db->where('disburse_no !=', '');
        $this->db->order_by('createdon', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get('loan_contract_disburse')->row();
        if ($last && !empty($last->disburse_no)) {
            preg_match('/\d+/', $last->disburse_no, $matches);
            if (!empty($matches)) {
                $next_num = (int) $matches[0] + 1;
                return 'LD-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
            }
        }
        return 'LD-00001';
    }

    /**
     * Check if loan disbursement number already exists for this PIN.
     * @param string $disburse_no
     * @param string|null $exclude_lid Optional LID to exclude (e.g. when editing)
     */
    function loan_disburse_no_exists($disburse_no, $exclude_lid = null) {
        $pin = current_user()->PIN;
        if (!$this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'disburse_no'")->row()) {
            return false;
        }
        $this->db->where('PIN', $pin);
        $this->db->where('disburse_no', $disburse_no);
        if ($exclude_lid !== null && $exclude_lid !== '') {
            $this->db->where('LID !=', $exclude_lid);
        }
        return $this->db->count_all_results('loan_contract_disburse') > 0;
    }

    /**
     * Save loan disbursement GL line items (for new disbursement entry UI).
     * $line_items = array of array('account' => ..., 'debit' => ..., 'credit' => ..., 'description' => ...)
     */
    function save_disbursement_gl_items($LID, $pin, $line_items) {
        if (!$this->db->table_exists('loan_disbursement_gl_items')) {
            return true;
        }
        $this->db->delete('loan_disbursement_gl_items', array('LID' => $LID, 'PIN' => $pin));
        foreach ($line_items as $item) {
            $debit = isset($item['debit']) ? floatval($item['debit']) : 0;
            $credit = isset($item['credit']) ? floatval($item['credit']) : 0;
            if (empty($item['account']) || ($debit <= 0 && $credit <= 0)) {
                continue;
            }
            $this->db->insert('loan_disbursement_gl_items', array(
                'LID' => $LID,
                'PIN' => $pin,
                'account' => $item['account'],
                'debit' => $debit,
                'credit' => $credit,
                'description' => isset($item['description']) ? $item['description'] : null,
            ));
        }
        return true;
    }

    /**
     * Get saved loan disbursement GL line items for a loan (by LID).
     */
    function get_disbursement_gl_items($LID, $pin) {
        if (!$this->db->table_exists('loan_disbursement_gl_items')) {
            return array();
        }
        $this->db->where('LID', $LID);
        $this->db->where('PIN', $pin);
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get('loan_disbursement_gl_items')->result();
        $items = array();
        foreach ($rows as $r) {
            $items[] = array(
                'account' => $r->account,
                'debit' => floatval($r->debit),
                'credit' => floatval($r->credit),
                'description' => $r->description,
            );
        }
        return $items;
    }

    /**
     * Post loan disbursement accounting lines to general ledger.
     * $line_items = array of array('account' => ..., 'debit' => ..., 'credit' => ...)
     * $loan_info = row from loan_info() for LID (must have PID, member_id).
     */
    function post_loan_disbursement_to_gl($LID, $pin, $line_items, $disburse_date, $loan_info) {
        if (empty($line_items)) {
            return false;
        }
        $ledger_entry = array('date' => $disburse_date, 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();
        if (!$ledger_entry_id) {
            return false;
        }
        $base_ledger = array(
            'journalID' => 4,
            'entryid' => $ledger_entry_id,
            'LID' => $LID,
            'date' => $disburse_date,
            'description' => 'Loan Disbursed',
            'linkto' => 'loan_contract.LID',
            'fromtable' => 'loan_contract',
            'paid' => 0,
            'PID' => $loan_info->PID,
            'member_id' => $loan_info->member_id,
            'PIN' => $pin,
        );
        foreach ($line_items as $item) {
            $debit = isset($item['debit']) ? floatval($item['debit']) : 0;
            $credit = isset($item['credit']) ? floatval($item['credit']) : 0;
            if (empty($item['account']) || ($debit <= 0 && $credit <= 0)) {
                continue;
            }
            $accountinfo = account_row_info($item['account']);
            if (!$accountinfo) {
                continue;
            }
            $ledger = $base_ledger;
            $ledger['account'] = $item['account'];
            $ledger['debit'] = $debit;
            $ledger['credit'] = $credit;
            $ledger['account_type'] = $accountinfo->account_type;
            $ledger['sub_account_type'] = isset($accountinfo->sub_account_type) ? $accountinfo->sub_account_type : null;
            $this->db->insert('general_ledger', $ledger);
        }
        return true;
    }

    function loan_repay_list() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT loan_contract.*,members.firstname,members.middlename,members.lastname  FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin' AND loan_contract.status=4 AND loan_contract.disburse=1 ORDER BY loan_contract.LID ASC")->result();
    }

    /**
     * Count released loans (status=4, disburse=1) that still have outstanding balance (open installments).
     */
    function count_loan_repayment_list_released_with_balance($key = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT COUNT(DISTINCT lc.LID) AS cnt FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID
                INNER JOIN loan_contract_repayment_schedule rs ON rs.LID = lc.LID AND rs.status = 0 AND rs.PIN = " . (int)$pin . "
                WHERE lc.PIN = " . (int)$pin . " AND lc.status = 4 AND lc.disburse = 1";
        if (!is_null($key) && trim($key) !== '') {
            $key_esc = $this->db->escape_like_str($key);
            $sql .= " AND (lc.LID LIKE " . $this->db->escape($key_esc . '%') . " OR lc.member_id LIKE " . $this->db->escape($key_esc . '%') . " OR m.firstname LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR m.lastname LIKE " . $this->db->escape('%' . $key_esc . '%') . ")";
        }
        $row = $this->db->query($sql)->row();
        return $row ? (int)$row->cnt : 0;
    }

    /**
     * Get released loans (status=4, disburse=1) that still have outstanding balance, for repayment list page (pagination).
     */
    function loan_repayment_list_released_with_balance($key, $limit, $start) {
        $pin = current_user()->PIN;
        $sql = "SELECT lc.*, m.firstname, m.middlename, m.lastname
                FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID
                INNER JOIN loan_contract_repayment_schedule rs ON rs.LID = lc.LID AND rs.status = 0 AND rs.PIN = " . (int)$pin . "
                WHERE lc.PIN = " . (int)$pin . " AND lc.status = 4 AND lc.disburse = 1";
        if (!is_null($key) && trim($key) !== '') {
            $key_esc = $this->db->escape_like_str($key);
            $sql .= " AND (lc.LID LIKE " . $this->db->escape($key_esc . '%') . " OR lc.member_id LIKE " . $this->db->escape($key_esc . '%') . " OR m.firstname LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR m.lastname LIKE " . $this->db->escape('%' . $key_esc . '%') . ")";
        }
        $sql .= " GROUP BY lc.LID ORDER BY lc.applicationdate ASC LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        return $this->db->query($sql)->result();
    }

    function count_loan($key = null, $status = null) {
        $pin = current_user()->PIN;
        
        // When status filter is set, count only loan_contract with that status
        if ($status !== null && $status !== '') {
            $sql = "SELECT loan_contract.LID FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin' AND loan_contract.status=" . $this->db->escape($status);
            if (!is_null($key)) {
                $sql .= " AND (loan_contract.LID LIKE " . $this->db->escape($key . '%') . " OR loan_contract.member_id LIKE " . $this->db->escape($key . '%') . " OR members.firstname LIKE " . $this->db->escape($key . '%') . " OR members.lastname LIKE " . $this->db->escape($key . '%') . ")";
            }
            return $this->db->query($sql)->num_rows();
        }
        
        // Count regular loans from loan_contract
        $sql = "SELECT loan_contract.* FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin'  ";

        if (!is_null($key)) {
            $sql .= "  AND (loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }
        
        $count = count($this->db->query($sql)->result());
        
        // Count loan beginning balances
        $sql_bb = "SELECT loan_beginning_balances.* FROM loan_beginning_balances INNER JOIN members ON members.member_id=loan_beginning_balances.member_id WHERE loan_beginning_balances.PIN='$pin' AND members.PIN='$pin'";
        
        // Exclude beginning balances that already have corresponding loan_contract entries
        $sql_bb .= " AND (loan_beginning_balances.loan_id IS NULL OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='$pin'))";
        
        if (!is_null($key)) {
            $sql_bb .= " AND (loan_beginning_balances.loan_id LIKE '$key%' OR loan_beginning_balances.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }
        
        $count_bb = count($this->db->query($sql_bb)->result());
        
        return $count + $count_bb;
    }

    function search_loan($key, $limit, $start, $status = null) {
        $pin = current_user()->PIN;
        $results = array();
        
        // When status filter is set, get only loan_contract with that status (no beginning balances)
        if ($status !== null && $status !== '') {
            $sql = "SELECT loan_contract.*,loan_status.name FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
            $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status WHERE loan_contract.PIN='$pin' AND loan_contract.status=" . $this->db->escape($status);
            if (!is_null($key)) {
                $sql .= " AND ( loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
            }
            $sql .= " ORDER BY loan_contract.applicationdate ASC LIMIT " . (int)$limit . " OFFSET " . (int)$start;
            return $this->db->query($sql)->result();
        }
        
        // Get regular loans from loan_contract
        $sql = "SELECT loan_contract.*,loan_status.name FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
        $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status WHERE loan_contract.PIN='$pin'";

        if (!is_null($key)) {
            $sql .= "  AND ( loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }

        $sql.= " ORDER BY loan_contract.applicationdate ASC";
        
        $regular_loans = $this->db->query($sql)->result();
        
        // Get loan beginning balances that don't have corresponding loan_contract entries
        $sql_bb = "SELECT 
                        COALESCE(loan_beginning_balances.loan_id, CONCAT('BB-', loan_beginning_balances.id)) as LID,
                        members.PID,
                        loan_beginning_balances.member_id,
                        COALESCE(loan_beginning_balances.loan_amount, loan_beginning_balances.principal_balance) as basic_amount,
                        loan_beginning_balances.term as number_istallment,
                        loan_beginning_balances.monthly_amort as installment_amount,
                        loan_beginning_balances.interest_balance as total_interest_amount,
                        loan_beginning_balances.total_balance as total_loan,
                        'Beginning Balance' as name,
                        1 as edit,
                        COALESCE(loan_product.`interval`, 1) as `interval`,
                        loan_beginning_balances.disbursement_date as applicationdate
                    FROM loan_beginning_balances 
                    INNER JOIN members ON members.member_id=loan_beginning_balances.member_id 
                    LEFT JOIN loan_product ON loan_product.id=loan_beginning_balances.loan_product_id AND loan_product.PIN='$pin'
                    WHERE loan_beginning_balances.PIN='$pin' AND members.PIN='$pin'";
        
        // Exclude beginning balances that already have corresponding loan_contract entries
        $sql_bb .= " AND (loan_beginning_balances.loan_id IS NULL OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='$pin'))";
        
        if (!is_null($key)) {
            $sql_bb .= " AND (loan_beginning_balances.loan_id LIKE '$key%' OR loan_beginning_balances.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }
        
        $sql_bb .= " ORDER BY loan_beginning_balances.disbursement_date ASC, loan_beginning_balances.created_at ASC";
        
        $beginning_balances = $this->db->query($sql_bb)->result();
        
        // Combine results
        $all_results = array_merge($regular_loans, $beginning_balances);
        
        // Sort by applicationdate
        usort($all_results, function($a, $b) {
            $dateA = isset($a->applicationdate) ? strtotime($a->applicationdate) : 0;
            $dateB = isset($b->applicationdate) ? strtotime($b->applicationdate) : 0;
            return $dateA - $dateB;
        });
        
        // Apply pagination
        return array_slice($all_results, $start, $limit);
    }

    function open_repayment_installment($LID) {
        $this->db->where('LID', $LID);
        $this->db->where('status', 0);
        $this->db->order_by('installment_number', 'ASC');
        return $this->db->get('loan_contract_repayment_schedule')->result();
    }

    function get_previous_remain_balance($LID) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('LID', $LID);
        $val = $this->db->get('loan_balance_carry')->row();
        if ($val) {
            return $val->balance;
        }

        return 0;
    }

    function loan_repay_receipt($LID, $amount, $paydate, $receipt_no = null) {
        $pin = current_user()->PIN;
        $receipt = $this->receiptNo();
        $array = array(
            'LID' => $LID,
            'receipt' => $receipt,
            'amount' => $amount,
            'paydate' => $paydate,
            'createdby' => current_user()->id,
            'PIN' => $pin,
        );
        if ($receipt_no !== null && $receipt_no !== '') {
            $array['receipt_no'] = $receipt_no;
        }

        $this->db->insert('loan_repayment_receipt', $array);
        return $receipt;
    }

    function add_remain_balance($LID, $amount) {
        $pin = current_user()->PIN;
        $check = $this->db->get_where('loan_balance_carry', array('LID' => $LID, 'PIN' => $pin))->row();
        if (count($check) > 0) {
            $this->db->where('LID', $LID);
            $this->db->where('PIN', $pin);
            $this->db->set('balance', $amount, FALSE);
            return $this->db->update('loan_balance_carry');
        } else {
            return $this->db->insert('loan_balance_carry', array('LID' => $LID, 'PIN' => $pin, 'balance' => $amount));
        }
    }

    function record_loan_repayment($array_data, $repay_schedule_ref, $cash_account = null) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $insert = $this->db->insert('loan_contract_repayment', $array_data);
        $referenceID = $this->db->insert_id();
        //general entry id
        $ledger_entry = array('date' => $array_data['paydate'], 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $LID = $array_data['LID'];
        $infodata = $this->loan_model->loan_info($LID)->row();
        $product = $this->setting_model->loanproduct($infodata->product_type)->row();
        // Determine cash/bank account for this repayment (fallback to 11110 for backward compatibility)
        $debit_account = $cash_account ? $cash_account : 11110;
        //prepare to enter ledger
        //ledger data
        $ledger = array(
            'journalID' => 4,
            'refferenceID' => $referenceID,
            'entryid' => $ledger_entry_id,
            'LID' => $LID,
            'date' => $array_data['paydate'],
            'description' => 'Loan Repayment',
            'linkto' => 'loan_contract_repayment.id',
            'fromtable' => 'loan_contract_repayment',
            'paid' => 0,
            'PIN' => $pin,
            'PID' => $infodata->PID,
            'member_id' => $infodata->member_id,
        );

        //bank account (cash/bank in from member)
        $ledger['account'] = $debit_account;
        $ledger['debit'] = $array_data['principle'];
        $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
        $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);


        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $product->loan_principle_account;
        $ledger['credit'] = $array_data['principle'];
         $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        //interest
        //debit account
        //bank account for interest cash-in
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $debit_account;
        $ledger['debit'] = $array_data['interest'];
        //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        //credit Income account
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $product->loan_interest_account;
        $ledger['credit'] = $array_data['interest'];
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        // Determine equity account (Retained Earnings) from global settings (fallback to 3000002)
        $equity_account_setting = function_exists('default_text_value') ? default_text_value('RETAINED_EARNINGS_ACCOUNT') : '';
        $equity_account = (is_numeric($equity_account_setting) && (int)$equity_account_setting > 0) ? (int)$equity_account_setting : 3000002;

        //credit equity
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $equity_account;
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $ledger['credit'] = $array_data['interest'];
        $this->db->insert('general_ledger', $ledger);


        //check if penalty exist
        if (array_key_exists('penalt', $array_data)) {
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = $debit_account;
            $ledger['debit'] = $array_data['penalt'];
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledger);

            //credit Income account
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = $product->loan_penalt_account;
            $ledger['credit'] = $array_data['penalt'];
           // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledger);


            //credit equity
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = $equity_account;
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['credit'] = $array_data['penalt'];
            $this->db->insert('general_ledger', $ledger);
        }


        $this->db->update('loan_contract_repayment_schedule', array('status' => 1), array('id' => $repay_schedule_ref));
        $this->db->update('loan_repayment_receipt', array('affect_loan' => 1, 'installment' => $array_data['installment']), array('receipt' => $array_data['receipt']));
        $this->db->trans_complete();
        return $insert;
    }
    
    
    //paying all loan before the end of the given duration
    function record_loan_repayment_all($array_data, $repay_schedule_ref, $loan_id, $cash_account = null) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $insert = $this->db->insert('loan_contract_repayment', $array_data);
        $referenceID = $this->db->insert_id();
        //general entry id
        $ledger_entry = array('date' => $array_data['paydate'], 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $LID = $array_data['LID'];
        $infodata = $this->loan_model->loan_info($LID)->row();
        $product = $this->setting_model->loanproduct($infodata->product_type)->row();
        // Determine cash/bank account for this repayment (fallback to 11110 for backward compatibility)
        $debit_account = $cash_account ? $cash_account : 11110;
        //prepare to enter ledger
        //ledger data
        $ledger = array(
            'journalID' => 4,
            'refferenceID' => $referenceID,
            'entryid' => $ledger_entry_id,
            'LID' => $LID,
            'date' => $array_data['paydate'],
            'description' => 'Loan Repayment',
            'linkto' => 'loan_contract_repayment.id',
            'fromtable' => 'loan_contract_repayment',
            'paid' => 0,
            'PIN' => $pin,
            'PID' => $infodata->PID,
            'member_id' => $infodata->member_id,
        );

        //bank account (cash/bank in from member)
        $ledger['account'] = $debit_account;
        $ledger['debit'] = $array_data['principle'];
        $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
        $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);


        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $product->loan_principle_account;
        $ledger['credit'] = $array_data['principle'];
         $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        //interest
        //debit account
        //bank account for interest cash-in
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $debit_account;
        $ledger['debit'] = $array_data['interest'];
        //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        //credit Income account
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $product->loan_interest_account;
        $ledger['credit'] = $array_data['interest'];
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        // Determine equity account (Retained Earnings) from global settings (fallback to 3000002)
        $equity_account_setting = function_exists('default_text_value') ? default_text_value('RETAINED_EARNINGS_ACCOUNT') : '';
        $equity_account = (is_numeric($equity_account_setting) && (int)$equity_account_setting > 0) ? (int)$equity_account_setting : 3000002;

        //credit equity
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = $equity_account;
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $ledger['credit'] = $array_data['interest'];
        $this->db->insert('general_ledger', $ledger);


        //check if penalty exist
        if (array_key_exists('penalt', $array_data)) {
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = $debit_account;
            $ledger['debit'] = $array_data['penalt'];
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledger);

            //credit Income account
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = $product->loan_penalt_account;
            $ledger['credit'] = $array_data['penalt'];
           // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledger);


            //credit equity
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = $equity_account;
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['credit'] = $array_data['penalt'];
            $this->db->insert('general_ledger', $ledger);
        }


        $this->db->update('loan_contract_repayment_schedule', array('status' => 1), array('id' => $repay_schedule_ref));       
        $this->db->update('loan_repayment_receipt', array('affect_loan' => 1, 'installment' => $array_data['installment']), array('receipt' => $array_data['receipt']));
        $this->db->update('loan_contract_repayment_schedule', array('status' => 2), array('LID' => $loan_id, 'status' => 0, 'pin' => $pin));
    
        $this->db->trans_complete();
        return $insert;
    }

    

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM loan_repayment_receipt")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

    function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        return $this->db->get('loan_repayment_receipt')->row();
    }

    /**
     * Check if receipt_no already exists in loan_repayment_receipt (for shared series with Cash Receipt).
     */
    function receipt_no_exists_loan_repayment($receipt_no) {
        if (empty($receipt_no)) return false;
        $has = $this->db->query("SHOW COLUMNS FROM loan_repayment_receipt LIKE 'receipt_no'")->row();
        if (!$has) return false;
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('receipt_no', $receipt_no);
        return $this->db->count_all_results('loan_repayment_receipt') > 0;
    }

    function loan_holder_name($LID) {
        $sql = "SELECT CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name FROM members INNER JOIN loan_contract ON members.PID=loan_contract.PID WHERE loan_contract.LID='$LID'";
        return $this->db->query($sql)->row()->name;
    }

    function installment_affected($receipt) {
        $min = $this->db->query("SELECT MIN(installment) as min  FROM loan_contract_repayment where receipt='$receipt'")->row();
        $max = $this->db->query("SELECT MAX(installment) as max  FROM loan_contract_repayment where receipt='$receipt'")->row();
        $installment = 0;
        if ($min->min == $max->max) {
            $installment = 'Installment No. ' . $max->max;
        } else {
            $installment = 'Installment No. ' . $min->min . ' - ' . $max->max;
        }

        return $installment;
    }

    // Loan Beginning Balances Methods
    function loan_beginning_balance_list($fiscal_year_id = null, $id = null, $loan_product_id = null) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);

        if (!is_null($fiscal_year_id)) {
            $this->db->where('fiscal_year_id', $fiscal_year_id);
        }

        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($loan_product_id) && $loan_product_id != '' && $loan_product_id != 'all') {
            $this->db->where('loan_product_id', $loan_product_id);
        }

        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('loan_beginning_balances');
    }

    function loan_beginning_balance_create($data) {
        $pin = current_user()->PIN;
        $data['PIN'] = $pin;
        $data['created_by'] = current_user()->id;
        return $this->db->insert('loan_beginning_balances', $data);
    }

    function loan_beginning_balance_update($data, $id) {
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        return $this->db->update('loan_beginning_balances', $data);
    }

    function loan_beginning_balance_delete($id) {
        if (empty($id)) {
            return false;
        }
        
        $pin = current_user()->PIN;
        
        // Check if already posted first
        $balance = $this->loan_beginning_balance_list(null, $id)->row();
        if ($balance && $balance->posted == 1) {
            return false; // Cannot delete if already posted
        }
        
        // Now set WHERE clauses and delete (must set WHERE before delete)
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        
        $result = $this->db->delete('loan_beginning_balances');
        return $result;
    }

    function loan_beginning_balance_post_to_ledger($id) {
        $pin = current_user()->PIN;
        $balance = $this->loan_beginning_balance_list(null, $id)->row();
        
        if (!$balance || $balance->posted == 1) {
            return false; // Already posted or doesn't exist
        }
        
        // Get fiscal year info
        $fiscal_year = $this->db->where('id', $balance->fiscal_year_id)->get('fiscal_year')->row();
        if (!$fiscal_year) {
            return false;
        }
        
        // Get loan product info
        $product = $this->db->where('id', $balance->loan_product_id)->where('PIN', $pin)->get('loan_product')->row();
        if (!$product) {
            return false;
        }
        
        $this->db->trans_start();
        
        // Create ledger entry header
        $ledger_entry = array(
            'date' => $fiscal_year->start_date,
            'PIN' => $pin
        );
        $ledger_entry_result = $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_affected = $this->db->affected_rows();
        $ledger_entry_id = $this->db->insert_id();
        
        // Verify ledger entry header was created
        if (!$ledger_entry_result || $ledger_entry_affected != 1 || !$ledger_entry_id || $ledger_entry_id == 0) {
            log_message('error', 'Failed to create general_ledger_entry header for loan beginning balance ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        // Use LAST_INSERT_ID() as fallback if needed
        if (!$ledger_entry_id || $ledger_entry_id == 0) {
            $last_id_result = $this->db->query("SELECT LAST_INSERT_ID() as id")->row();
            if ($last_id_result && $last_id_result->id > 0) {
                $ledger_entry_id = $last_id_result->id;
            } else {
                log_message('error', 'Failed to get ledger_entry_id for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return false;
            }
        }
        
        $ledger_items_inserted = 0;
        
        // Post principal balance if exists
        if ($balance->principal_balance > 0) {
            $ledger = array(
                'journalID' => 8, // Journal ID for Beginning Balance
                'refferenceID' => $id,
                'entryid' => $ledger_entry_id,
                'date' => $fiscal_year->start_date,
                'description' => 'Loan Beginning Balance - Principal - ' . $balance->member_id,
                'linkto' => 'loan_beginning_balances.id',
                'fromtable' => 'loan_beginning_balances',
                'account' => $product->loan_principle_account,
                'debit' => $balance->principal_balance,
                'credit' => 0,
                'member_id' => $balance->member_id,
                'PIN' => $pin
            );
            
            $infoaccount = account_row_info($product->loan_principle_account);
            if ($infoaccount) {
                $ledger['account_type'] = $infoaccount->account_type;
                $ledger['sub_account_type'] = isset($infoaccount->sub_account_type) ? $infoaccount->sub_account_type : null;
            } else {
                log_message('error', 'Account not found for principal: ' . $product->loan_principle_account);
                $this->db->trans_complete();
                return false;
            }
            
            $insert_result = $this->db->insert('general_ledger', $ledger);
            $insert_affected = $this->db->affected_rows();
            
            if (!$insert_result || $insert_affected != 1) {
                log_message('error', 'Failed to insert principal ledger entry for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return false;
            }
            $ledger_items_inserted++;
        }
        
        // Post interest balance if exists
        if ($balance->interest_balance > 0) {
            $ledger = array(
                'journalID' => 8,
                'refferenceID' => $id,
                'entryid' => $ledger_entry_id,
                'date' => $fiscal_year->start_date,
                'description' => 'Loan Beginning Balance - Interest - ' . $balance->member_id,
                'linkto' => 'loan_beginning_balances.id',
                'fromtable' => 'loan_beginning_balances',
                'account' => $product->loan_interest_account,
                'debit' => $balance->interest_balance,
                'credit' => 0,
                'member_id' => $balance->member_id,
                'PIN' => $pin
            );
            
            $infoaccount = account_row_info($product->loan_interest_account);
            if ($infoaccount) {
                $ledger['account_type'] = $infoaccount->account_type;
                $ledger['sub_account_type'] = isset($infoaccount->sub_account_type) ? $infoaccount->sub_account_type : null;
            } else {
                log_message('error', 'Account not found for interest: ' . $product->loan_interest_account);
                $this->db->trans_complete();
                return false;
            }
            
            $insert_result = $this->db->insert('general_ledger', $ledger);
            $insert_affected = $this->db->affected_rows();
            
            if (!$insert_result || $insert_affected != 1) {
                log_message('error', 'Failed to insert interest ledger entry for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return false;
            }
            $ledger_items_inserted++;
        }
        
        // Post penalty balance if exists
        if ($balance->penalty_balance > 0) {
            $ledger = array(
                'journalID' => 8,
                'refferenceID' => $id,
                'entryid' => $ledger_entry_id,
                'date' => $fiscal_year->start_date,
                'description' => 'Loan Beginning Balance - Penalty - ' . $balance->member_id,
                'linkto' => 'loan_beginning_balances.id',
                'fromtable' => 'loan_beginning_balances',
                'account' => $product->loan_penalt_account,
                'debit' => $balance->penalty_balance,
                'credit' => 0,
                'member_id' => $balance->member_id,
                'PIN' => $pin
            );
            
            $infoaccount = account_row_info($product->loan_penalt_account);
            if ($infoaccount) {
                $ledger['account_type'] = $infoaccount->account_type;
                $ledger['sub_account_type'] = isset($infoaccount->sub_account_type) ? $infoaccount->sub_account_type : null;
            } else {
                log_message('error', 'Account not found for penalty: ' . $product->loan_penalt_account);
                $this->db->trans_complete();
                return false;
            }
            
            $insert_result = $this->db->insert('general_ledger', $ledger);
            $insert_affected = $this->db->affected_rows();
            
            if (!$insert_result || $insert_affected != 1) {
                log_message('error', 'Failed to insert penalty ledger entry for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return false;
            }
            $ledger_items_inserted++;
        }
        
        // Verify at least one ledger item was inserted (if balances exist)
        $expected_items = 0;
        if ($balance->principal_balance > 0) $expected_items++;
        if ($balance->interest_balance > 0) $expected_items++;
        if ($balance->penalty_balance > 0) $expected_items++;
        
        if ($expected_items > 0 && $ledger_items_inserted != $expected_items) {
            log_message('error', 'Loan beginning balance ID ' . $id . ': Expected ' . $expected_items . ' ledger items, but inserted ' . $ledger_items_inserted);
            $this->db->trans_complete();
            return false;
        }
        
        // Check transaction status before updating posted status
        if ($this->db->_trans_status === FALSE) {
            log_message('error', 'Transaction status is FALSE before updating posted status for loan beginning balance ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        // Update loan beginning balance as posted
        $update_data = array(
            'posted' => 1,
            'posted_date' => date('Y-m-d H:i:s'),
            'posted_by' => current_user()->id
        );
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $update_result = $this->db->update('loan_beginning_balances', $update_data);
        $update_affected = $this->db->affected_rows();
        
        if (!$update_result || $update_affected != 1) {
            log_message('error', 'Failed to update loan beginning balance as posted for ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        $this->db->trans_complete();
        
        $transaction_status = $this->db->trans_status();
        
        if ($transaction_status === FALSE) {
            log_message('error', 'Loan beginning balance post to ledger failed - transaction rolled back for ID: ' . $id);
            return false;
        }
        
        log_message('info', 'Loan beginning balance ID ' . $id . ' posted to general ledger successfully with ' . $ledger_items_inserted . ' ledger entries');
        
        return true;
    }

    function check_loan_beginning_balance_exists($fiscal_year_id, $member_id, $loan_product_id) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('fiscal_year_id', $fiscal_year_id);
        $this->db->where('member_id', $member_id);
        $this->db->where('loan_product_id', $loan_product_id);
        $result = $this->db->get('loan_beginning_balances');
        return $result->num_rows() > 0;
    }

}
