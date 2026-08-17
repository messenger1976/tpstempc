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
        if ($come) {
            return TRUE;
        }
        return FALSE;
    }

    function is_loan_exist($loan_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('LID', $loan_id);
        $come = $this->db->get('loan_contract')->row();
        if ($come) {
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
        if ($check) {
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

    function list_member_loans($pid) {
        $pin = current_user()->PIN;
        if ($pid === null || $pid === '') {
            return array();
        }

        $this->db->select('lc.LID, lc.PID, lc.member_id, lc.basic_amount, lc.total_loan, lc.status, lc.disburse, lc.product_type, lc.applicationdate, ls.name as status_name, lp.name as product_name', FALSE);
        $this->db->select('0 as is_beginning_balance, 0 as penalty, 0 as past_due_interest', FALSE);
        $this->db->select('(SELECT MAX(e.createdon) FROM loan_contract_evaluation e WHERE e.LID = lc.LID AND e.PIN = lc.PIN) AS evaluation_date', FALSE);
        $this->db->select('(SELECT MAX(a.createdon) FROM loan_contract_approve a WHERE a.LID = lc.LID AND a.PIN = lc.PIN) AS approval_date', FALSE);
        $this->db->select('(SELECT MAX(d.disbursedate) FROM loan_contract_disburse d WHERE d.LID = lc.LID AND d.PIN = lc.PIN) AS disbursement_date', FALSE);
        $this->db->select('(SELECT MAX(r.paydate) FROM loan_contract_repayment r WHERE r.LID = lc.LID AND r.PIN = lc.PIN) AS last_repay_date', FALSE);
        $this->db->from('loan_contract lc');
        $this->db->join('loan_status ls', 'ls.code = lc.status', 'left');
        $this->db->join('loan_product lp', 'lp.id = lc.product_type AND lp.PIN = lc.PIN', 'left');
        $this->db->where('lc.PIN', $pin);
        $this->db->where('lc.PID', $pid);
        $this->db->order_by('lc.applicationdate', 'DESC');
        $contracts = $this->db->get()->result();

        $sql_bb = "SELECT
                COALESCE(lbb.loan_id, CONCAT('BB-', lbb.id)) AS LID,
                COALESCE(lbb.loan_amount, lbb.principal_balance) AS basic_amount,
                lbb.total_balance AS total_loan,
                'bb' AS status,
                0 AS disburse,
                lbb.loan_product_id AS product_type,
                lbb.disbursement_date AS applicationdate,
                'Beginning Balance' AS status_name,
                lp.name AS product_name,
                1 AS is_beginning_balance,
                lbb.id AS bb_id,
                lbb.posted AS bb_posted,
                lbb.fiscal_year_id AS bb_fiscal_year_id,
                lbb.member_id AS member_id,
                COALESCE(lbb.penalty_balance, 0) AS penalty,
                COALESCE(lbb.interest_balance, 0) AS past_due_interest,
                NULL AS evaluation_date,
                NULL AS approval_date,
                lbb.disbursement_date AS disbursement_date,
                NULL AS last_repay_date
            FROM loan_beginning_balances lbb
            INNER JOIN members m ON m.member_id = lbb.member_id AND m.PIN = lbb.PIN
            LEFT JOIN loan_product lp ON lp.id = lbb.loan_product_id AND lp.PIN = lbb.PIN
            WHERE lbb.PIN = ? AND m.PID = ?
              AND (lbb.loan_id IS NULL OR lbb.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN = ?))
            ORDER BY lbb.disbursement_date DESC";
        $beginning = $this->db->query($sql_bb, array($pin, $pid, $pin))->result();

        return array_merge($contracts, $beginning);
    }

    /**
     * Disbursed, not-closed loans for a member that still have an outstanding balance.
     * Used by Cash Receipt → Received From → Loan Repayment.
     */
    function get_member_repayable_loans($pid, $exclude_receipt_id = null) {
        $pid = trim((string) $pid);
        if ($pid === '') {
            return array();
        }
        $this->load->model('setting_model');
        $this->load->model('cash_receipt_model');

        $rows = $this->list_member_loans($pid);
        $today = date('Y-m-d');
        $out = array();
        foreach ($rows as $row) {
            if (!empty($row->is_beginning_balance)) {
                continue;
            }
            $status = isset($row->status) ? (string) $row->status : '';
            if ($status === '5') {
                continue;
            }
            $disbursed = isset($row->disburse) && ((string) $row->disburse === '1' || $row->disburse === 1);
            if (!$disbursed) {
                continue;
            }
            $lid = isset($row->LID) ? $row->LID : '';
            if ($lid === '') {
                continue;
            }
            if ($this->cash_receipt_model->get_unposted_loan_repayment_for_loan($lid, $exclude_receipt_id)) {
                continue;
            }
            $breakdown = $this->get_loan_outstanding_for_offset($lid);
            $outstanding = $breakdown ? floatval($breakdown['total']) : 0;
            if ($outstanding <= 0.009) {
                continue;
            }
            $due = $this->calculate_repayment_due($lid, $today);
            $status_name = !empty($row->status_name) ? $row->status_name : '';
            if ($status_name === '' && $status !== '' && function_exists('loan_status')) {
                $mapped = loan_status($status);
                if (!empty($mapped)) {
                    $status_name = $mapped;
                }
            }
            $out[] = array(
                'LID' => $lid,
                'PID' => isset($row->PID) && $row->PID !== '' ? $row->PID : $pid,
                'member_id' => isset($row->member_id) ? $row->member_id : '',
                'product_name' => !empty($row->product_name) ? $row->product_name : ($breakdown ? $breakdown['product_name'] : ''),
                'status_name' => $status_name,
                'status' => $status,
                'basic_amount' => isset($row->basic_amount) ? floatval($row->basic_amount) : 0,
                'outstanding' => round($outstanding, 2),
                'principal_outstanding' => $breakdown ? round(floatval($breakdown['principal']), 2) : 0,
                'interest_outstanding' => $breakdown ? round(floatval($breakdown['interest']), 2) : 0,
                'amount_due' => isset($due->suggested_amount) ? round(floatval($due->suggested_amount), 2) : 0,
                'net_due' => isset($due->net_due) ? round(floatval($due->net_due), 2) : 0,
                'has_overdue' => !empty($due->has_overdue) ? 1 : 0,
            );
        }
        return $out;
    }

    /**
     * Suggested Cash Receipt line items for a loan repayment (editable on the form).
     * Debit: payment-method cash/bank. Credit: product principal / interest / penalty.
     */
    function get_cash_receipt_repayment_worksheet($lid, $payment_method_id = null, $paydate = null, $amount = null) {
        $lid = trim((string) $lid);
        $pin = current_user()->PIN;
        if ($lid === '') {
            return null;
        }
        $this->load->model('setting_model');
        $loan = $this->loan_info($lid)->row();
        if (!$loan || (string) $loan->PIN !== (string) $pin) {
            return null;
        }
        if (empty($paydate)) {
            $paydate = date('Y-m-d');
        }
        $product = $this->setting_model->loanproduct($loan->product_type)->row();
        $due = $this->calculate_repayment_due($lid, $paydate);
        $suggested = isset($due->suggested_amount) ? round((float) $due->suggested_amount, 2) : 0;
        $payment_amount = ($amount !== null && $amount !== '') ? round((float) $amount, 2) : $suggested;
        if ($payment_amount <= 0 && $suggested > 0) {
            $payment_amount = $suggested;
        }
        $preview = $this->preview_loan_repayment_application($lid, $payment_amount, $paydate);
        $principal = 0.0;
        $interest = 0.0;
        $penalty = 0.0;
        if (!empty($preview['success'])) {
            $principal = isset($preview['principle']) ? round((float) $preview['principle'], 2) : 0;
            $interest = isset($preview['interest']) ? round((float) $preview['interest'], 2) : 0;
            $penalty = isset($preview['penalt']) ? round((float) $preview['penalt'], 2) : 0;
        } else if ($suggested > 0 && abs($payment_amount - $suggested) > 0.009) {
            $fallback = $this->preview_loan_repayment_application($lid, $suggested, $paydate);
            if (!empty($fallback['success'])) {
                $payment_amount = $suggested;
                $principal = isset($fallback['principle']) ? round((float) $fallback['principle'], 2) : 0;
                $interest = isset($fallback['interest']) ? round((float) $fallback['interest'], 2) : 0;
                $penalty = isset($fallback['penalt']) ? round((float) $fallback['penalt'], 2) : 0;
                $preview = $fallback;
            }
        }
        $cash_total = round($principal + $interest + $penalty, 2);
        if ($cash_total <= 0 && $payment_amount > 0) {
            $cash_total = $payment_amount;
            $principal = $payment_amount;
        }

        $cash_account = null;
        $payment_method_id = (int) $payment_method_id;
        if ($payment_method_id > 0) {
            $cash_account = $this->get_credit_account_for_payment_method($payment_method_id);
        }

        $lines = array();
        if (!empty($cash_account)) {
            $lines[] = array(
                'account' => $cash_account,
                'debit' => $cash_total > 0 ? $cash_total : '',
                'credit' => '',
                'description' => 'Loan Repayment ' . $lid,
                'role' => 'cash',
            );
        }
        if ($product && !empty($product->loan_principle_account)) {
            $lines[] = array(
                'account' => $product->loan_principle_account,
                'debit' => '',
                'credit' => $principal > 0 ? $principal : '',
                'description' => 'Loan principal ' . $lid,
                'role' => 'principal',
            );
        }
        if ($product && !empty($product->loan_interest_account)) {
            $lines[] = array(
                'account' => $product->loan_interest_account,
                'debit' => '',
                'credit' => $interest > 0 ? $interest : '',
                'description' => 'Loan interest ' . $lid,
                'role' => 'interest',
            );
        }
        if ($product && !empty($product->loan_penalt_account) && $penalty > 0) {
            $lines[] = array(
                'account' => $product->loan_penalt_account,
                'debit' => '',
                'credit' => $penalty,
                'description' => 'Loan penalty ' . $lid,
                'role' => 'penalty',
            );
        }

        $status_name = '';
        if (isset($loan->status) && function_exists('loan_status')) {
            $mapped = loan_status((string) $loan->status);
            if (!empty($mapped)) {
                $status_name = $mapped;
            }
        }
        $breakdown = $this->get_loan_outstanding_for_offset($lid);

        return array(
            'LID' => $lid,
            'PID' => isset($loan->PID) ? $loan->PID : '',
            'member_id' => isset($loan->member_id) ? $loan->member_id : '',
            'product_name' => $product ? $product->name : '',
            'status_name' => $status_name,
            'outstanding' => $breakdown ? round(floatval($breakdown['total']), 2) : 0,
            'amount_due' => isset($due->suggested_amount) ? round(floatval($due->suggested_amount), 2) : $cash_total,
            'suggested_amount' => isset($due->suggested_amount) ? round(floatval($due->suggested_amount), 2) : $cash_total,
            'minimum_to_apply' => isset($due->minimum_to_apply) ? round(floatval($due->minimum_to_apply), 2) : 0,
            'payment_amount' => $payment_amount,
            'due' => $due,
            'preview_ok' => !empty($preview['success']),
            'preview_message' => !empty($preview['message']) ? $preview['message'] : '',
            'cash_account' => $cash_account,
            'line_items' => $lines,
        );
    }

    function member_loan_status_date($row) {
        $status = isset($row->status) ? (string) $row->status : '';
        $disburse = isset($row->disburse) && ((string) $row->disburse === '1' || $row->disburse === 1);
        $is_bb = !empty($row->is_beginning_balance);
        $application = isset($row->applicationdate) ? $row->applicationdate : '';
        $evaluation = isset($row->evaluation_date) ? $row->evaluation_date : '';
        $approval = isset($row->approval_date) ? $row->approval_date : '';
        $disbursement = isset($row->disbursement_date) ? $row->disbursement_date : '';
        $closed = isset($row->last_repay_date) ? $row->last_repay_date : '';

        if ($is_bb) {
            return array('label' => lang('loan_disburse_date'), 'date' => $disbursement ? $disbursement : $application);
        }
        if ($status === '5') {
            $date = $closed ? $closed : ($disbursement ? $disbursement : $approval);
            return array('label' => 'Closed Date', 'date' => $date);
        }
        if ($disburse || $status === '9') {
            return array('label' => lang('loan_disburse_date'), 'date' => $disbursement ? $disbursement : $application);
        }
        if ($status === '6') {
            if ($disbursement) {
                return array('label' => lang('loan_disburse_date'), 'date' => $disbursement);
            }
            return array('label' => 'Accepted Date', 'date' => $approval ? $approval : $application);
        }
        if ($status === '4') {
            return array('label' => 'Accepted Date', 'date' => $approval ? $approval : $application);
        }
        if ($status === '8') {
            return array('label' => 'Rejected Date', 'date' => $approval ? $approval : $evaluation);
        }
        if ($status === '1') {
            return array('label' => 'Evaluation Date', 'date' => $evaluation ? $evaluation : $application);
        }
        if ($status === '7' || $status === '2') {
            return array('label' => 'Rejected Date', 'date' => $evaluation ? $evaluation : ($approval ? $approval : $application));
        }
        return array('label' => lang('loan_applicationdate'), 'date' => $application);
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

    function loan_wait_evaluation($key = null, $limit = null, $start = 0) {
        $pin = current_user()->PIN;
        $sql = "SELECT lc.*, lp.name AS product_name, ls.name AS status_name,
                       m.member_id, m.firstname, m.middlename, m.lastname
                FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
                LEFT JOIN loan_status ls ON ls.code = lc.status
                WHERE lc.PIN = " . $this->db->escape($pin) . "
                  AND (lc.status = 0 OR lc.status = 3)";
        if (!is_null($key) && $key !== '') {
            $like = $this->db->escape('%' . $key . '%');
            $sql .= " AND (lc.LID LIKE $like OR m.member_id LIKE $like OR m.firstname LIKE $like
                      OR m.middlename LIKE $like OR m.lastname LIKE $like
                      OR CONCAT(m.firstname, ' ', m.lastname) LIKE $like
                      OR CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) LIKE $like)";
        }
        $sql .= " ORDER BY lc.applicationdate DESC";
        if (!is_null($limit)) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $start;
        }
        return $this->db->query($sql)->result();
    }

    function count_loan_wait_evaluation($key = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT COUNT(lc.LID) AS total
                FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                WHERE lc.PIN = " . $this->db->escape($pin) . "
                  AND (lc.status = 0 OR lc.status = 3)";
        if (!is_null($key) && $key !== '') {
            $like = $this->db->escape('%' . $key . '%');
            $sql .= " AND (lc.LID LIKE $like OR m.member_id LIKE $like OR m.firstname LIKE $like
                      OR m.middlename LIKE $like OR m.lastname LIKE $like
                      OR CONCAT(m.firstname, ' ', m.lastname) LIKE $like
                      OR CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) LIKE $like)";
        }
        $row = $this->db->query($sql)->row();
        return $row ? (int) $row->total : 0;
    }

    function loan_wait_approval($key = null, $date_from = null, $date_to = null, $product_id = null, $limit = null, $start = 0) {
        $pin = current_user()->PIN;
        $sql = "SELECT lc.*, lp.name AS product_name, ls.name AS status_name,
                       m.member_id, m.firstname, m.middlename, m.lastname
                FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
                LEFT JOIN loan_status ls ON ls.code = lc.status
                WHERE lc.PIN = " . $this->db->escape($pin) . "
                  AND lc.status = 1";
        if (!is_null($key) && $key !== '') {
            $like = $this->db->escape('%' . $key . '%');
            $sql .= " AND (lc.LID LIKE $like OR m.member_id LIKE $like OR m.firstname LIKE $like
                      OR m.middlename LIKE $like OR m.lastname LIKE $like
                      OR CONCAT(m.firstname, ' ', m.lastname) LIKE $like
                      OR CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) LIKE $like)";
        }
        if (!empty($date_from)) {
            $sql .= " AND DATE(lc.applicationdate) >= " . $this->db->escape($date_from);
        }
        if (!empty($date_to)) {
            $sql .= " AND DATE(lc.applicationdate) <= " . $this->db->escape($date_to);
        }
        if (!empty($product_id) && $product_id !== 'all') {
            $sql .= " AND lc.product_type = " . (int) $product_id;
        }
        $sql .= " ORDER BY lc.applicationdate DESC";
        if (!is_null($limit)) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $start;
        }
        return $this->db->query($sql)->result();
    }

    function count_loan_wait_approval($key = null, $date_from = null, $date_to = null, $product_id = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT COUNT(lc.LID) AS total
                FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                WHERE lc.PIN = " . $this->db->escape($pin) . "
                  AND lc.status = 1";
        if (!is_null($key) && $key !== '') {
            $like = $this->db->escape('%' . $key . '%');
            $sql .= " AND (lc.LID LIKE $like OR m.member_id LIKE $like OR m.firstname LIKE $like
                      OR m.middlename LIKE $like OR m.lastname LIKE $like
                      OR CONCAT(m.firstname, ' ', m.lastname) LIKE $like
                      OR CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) LIKE $like)";
        }
        if (!empty($date_from)) {
            $sql .= " AND DATE(lc.applicationdate) >= " . $this->db->escape($date_from);
        }
        if (!empty($date_to)) {
            $sql .= " AND DATE(lc.applicationdate) <= " . $this->db->escape($date_to);
        }
        if (!empty($product_id) && $product_id !== 'all') {
            $sql .= " AND lc.product_type = " . (int) $product_id;
        }
        $row = $this->db->query($sql)->row();
        return $row ? (int) $row->total : 0;
    }

    function loan_wait_disburse($pid = null, $product_id = null, $date_from = null, $date_to = null, $limit = null, $start = 0) {
        $this->ensure_release_workflow_columns();
        $pin = current_user()->PIN;
        $sql = "SELECT lc.*, lp.name AS loan_product_name, lp.name AS product_name,
                       ls.name AS status_name,
                       m.member_id, m.firstname, m.middlename, m.lastname
                FROM loan_contract lc
                LEFT JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
                LEFT JOIN loan_status ls ON ls.code = lc.status
                WHERE lc.PIN = " . $this->db->escape($pin) . "
                  AND lc.status = 4
                  AND lc.disburse = 0
                  AND NOT EXISTS (
                      SELECT 1
                      FROM loan_contract_disburse lcd
                      WHERE lcd.LID = lc.LID
                        AND lcd.PIN = lc.PIN
                        AND (
                            (lcd.release_status = 'pending')
                            OR (lcd.release_status = 'draft')
                        )
                  )";
        if (!empty($pid)) {
            $sql .= " AND lc.PID = " . $this->db->escape($pid);
        }
        if (!empty($product_id) && $product_id !== 'all') {
            $sql .= " AND lc.product_type = " . (int) $product_id;
        }
        if (!empty($date_from)) {
            $sql .= " AND DATE(lc.applicationdate) >= " . $this->db->escape($date_from);
        }
        if (!empty($date_to)) {
            $sql .= " AND DATE(lc.applicationdate) <= " . $this->db->escape($date_to);
        }
        $sql .= " ORDER BY lc.applicationdate DESC";
        if (!is_null($limit)) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $start;
        }
        return $this->db->query($sql)->result();
    }

    function count_loan_wait_disburse($pid = null, $product_id = null, $date_from = null, $date_to = null) {
        $this->ensure_release_workflow_columns();
        $pin = current_user()->PIN;
        $sql = "SELECT COUNT(lc.LID) AS total
                FROM loan_contract lc
                WHERE lc.PIN = " . $this->db->escape($pin) . "
                  AND lc.status = 4
                  AND lc.disburse = 0
                  AND NOT EXISTS (
                      SELECT 1
                      FROM loan_contract_disburse lcd
                      WHERE lcd.LID = lc.LID
                        AND lcd.PIN = lc.PIN
                        AND (
                            (lcd.release_status = 'pending')
                            OR (lcd.release_status = 'draft')
                        )
                  )";
        if (!empty($pid)) {
            $sql .= " AND lc.PID = " . $this->db->escape($pid);
        }
        if (!empty($product_id) && $product_id !== 'all') {
            $sql .= " AND lc.product_type = " . (int) $product_id;
        }
        if (!empty($date_from)) {
            $sql .= " AND DATE(lc.applicationdate) >= " . $this->db->escape($date_from);
        }
        if (!empty($date_to)) {
            $sql .= " AND DATE(lc.applicationdate) <= " . $this->db->escape($date_to);
        }
        $row = $this->db->query($sql)->row();
        return $row ? (int) $row->total : 0;
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

    function ensure_release_workflow_columns() {
        if ($this->db->table_exists('loan_contract_disburse')) {
            $columns = array(
                'release_status' => "VARCHAR(20) NULL DEFAULT NULL",
                'cash_disbursement_id' => "INT NULL DEFAULT NULL",
                'payout_journal_entry_id' => "INT NULL DEFAULT NULL",
                'offset_loan_ids' => "TEXT NULL DEFAULT NULL",
                'payout_completed_at' => "DATETIME NULL DEFAULT NULL",
            );
            foreach ($columns as $col => $definition) {
                if (!$this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE '" . $this->db->escape_str($col) . "'")->row()) {
                    $this->db->query("ALTER TABLE loan_contract_disburse ADD COLUMN `$col` $definition");
                }
            }
        }
    }

    function get_pending_release($LID, $pin = null) {
        $pin = $pin ? $pin : current_user()->PIN;
        $this->ensure_release_workflow_columns();
        // Use where_in (not or_where) so LID/PIN stay required — or_where without
        // grouping used to match any draft row in the table and block every Release click.
        $this->db->where('LID', $LID);
        $this->db->where('PIN', $pin);
        $this->db->where_in('release_status', array('pending', 'draft'));
        $this->db->order_by('disbursedate', 'DESC');
        $this->db->limit(1);
        return $this->db->get('loan_contract_disburse')->row();
    }

    function save_pending_release($LID, $row_data, $line_items) {
        $pin = current_user()->PIN;
        $this->ensure_release_workflow_columns();
        $existing = $this->get_pending_release($LID, $pin);
        if ($existing) {
            $this->db->where('LID', $LID);
            $this->db->where('PIN', $pin);
            $this->db->where_in('release_status', array('pending', 'draft'));
            $this->db->delete('loan_contract_disburse');
        }
        $row_data['release_status'] = 'pending';
        $row_data['cash_disbursement_id'] = null;
        $row_data['payout_journal_entry_id'] = null;
        $row_data['payout_completed_at'] = null;
        $this->db->insert('loan_contract_disburse', $row_data);
        $this->save_disbursement_gl_items($LID, $pin, $line_items);
        return $this->db->affected_rows() >= 0;
    }

    function link_pending_release_to_cash_disbursement($LID, $cash_disbursement_id) {
        $pin = current_user()->PIN;
        $this->ensure_release_workflow_columns();
        $this->db->where('LID', $LID)
            ->where('PIN', $pin)
            ->where_in('release_status', array('pending', 'draft'))
            ->update('loan_contract_disburse', array(
                'release_status' => 'draft',
                'cash_disbursement_id' => (int) $cash_disbursement_id,
            ));
        return $this->db->affected_rows() >= 0;
    }

    function unlink_pending_release_cash_disbursement($cash_disbursement_id) {
        $pin = current_user()->PIN;
        $this->ensure_release_workflow_columns();
        $this->db->where('PIN', $pin)
            ->where('cash_disbursement_id', (int) $cash_disbursement_id)
            ->where('release_status', 'draft')
            ->update('loan_contract_disburse', array(
                'release_status' => 'pending',
                'cash_disbursement_id' => null,
            ));
        return $this->db->affected_rows() >= 0;
    }

    /**
     * Ensure loan_status rows used by cancel / mixed rejection labels exist.
     * Local DB historically had 0–6 only; helper already maps 7/8/9.
     */
    function ensure_loan_cancel_status_codes() {
        if (!$this->db->table_exists('loan_status')) {
            return;
        }
        $needed = array(
            7 => 'Evaluated && Rejected',
            8 => 'Accepted && Rejected',
            9 => 'Accepted && Disbursed',
        );
        foreach ($needed as $code => $name) {
            $exists = $this->db->where('code', $code)->limit(1)->get('loan_status')->row();
            if (!$exists) {
                $this->db->insert('loan_status', array('code' => $code, 'name' => $name));
            }
        }
    }

    /**
     * Cancel an Accepted loan that is not yet disbursed (Pending Release / Released not posted).
     * Sets status/approval to 8 (Accepted && Rejected). Deletes pending release worksheet if any.
     * Blocks when release is linked to a Cash Disbursement (draft).
     *
     * @return array{success:bool,message:string}
     */
    function cancel_pending_release_loan($LID, $comment) {
        $this->lang->load('loan');
        $pin = current_user()->PIN;
        $LID = trim((string) $LID);
        $comment = trim((string) $comment);

        if ($LID === '') {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message('loan_evaluation_error', 'Invalid loan.'),
            );
        }
        if ($comment === '') {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_cancel_comment_required',
                    'A comment is required to cancel this loan.'
                ),
            );
        }

        $loan = $this->db->where('LID', $LID)->where('PIN', $pin)->get('loan_contract')->row();
        if (!$loan) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_cancel_not_eligible',
                    'This loan cannot be cancelled. Only accepted, undisbursed loans can be cancelled.'
                ),
            );
        }

        $status = (string) $loan->status;
        $disburse_zero = ((string) $loan->disburse === '0' || (int) $loan->disburse === 0);
        if (!in_array($status, array('4', '6', '9'), true) || !$disburse_zero) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_cancel_not_eligible',
                    'This loan cannot be cancelled. Only accepted, undisbursed loans can be cancelled.'
                ),
            );
        }

        $this->ensure_release_workflow_columns();
        $open_release = $this->get_pending_release($LID, $pin);
        if ($open_release && isset($open_release->release_status) && $open_release->release_status === 'draft') {
            $cd_note = !empty($open_release->cash_disbursement_id)
                ? (' (Cash Disbursement ID ' . (int) $open_release->cash_disbursement_id . ')')
                : '';
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_cancel_cd_linked',
                    'This loan release is linked to a Cash Disbursement. Delete the unposted Cash Disbursement in Finance first, then cancel the loan.'
                ) . $cd_note,
            );
        }

        $this->ensure_loan_cancel_status_codes();
        $this->db->trans_start();

        if ($open_release && isset($open_release->release_status) && $open_release->release_status === 'pending') {
            if ($this->db->table_exists('loan_disbursement_gl_items')) {
                $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_disbursement_gl_items');
            }
            $this->db->where('LID', $LID)
                ->where('PIN', $pin)
                ->where('release_status', 'pending')
                ->delete('loan_contract_disburse');
        }

        $this->db->insert('loan_contract_approve', array(
            'LID' => $LID,
            'status' => 8,
            'comment' => $comment,
            'createdby' => current_user()->id,
            'PIN' => $pin,
        ));

        $this->db->where('LID', $LID)->where('PIN', $pin)->update('loan_contract', array(
            'status' => 8,
            'approval' => 8,
        ));

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_cancel_fail',
                    'Failed to cancel the loan. Please try again.'
                ),
            );
        }

        return array(
            'success' => true,
            'message' => $this->_loan_lang_message(
                'loan_cancel_success',
                'Loan cancelled. Status is now Accepted && Rejected.'
            ),
        );
    }

    function get_member_pending_releases($pid) {
        $pin = current_user()->PIN;
        $this->ensure_release_workflow_columns();
        if ($pid === null || $pid === '') {
            return array();
        }
        $sql = "SELECT lcd.LID, lcd.disbursedate, lcd.comment, lcd.disburse_no, lcd.payment_method,
                       lcd.release_status, lcd.cash_disbursement_id,
                       lcd.offset_loan_ids,
                       lc.basic_amount, lc.member_id, lc.PID, lc.product_type, lc.installment_amount,
                       lp.name AS product_name,
                       m.firstname, m.middlename, m.lastname
                FROM loan_contract_disburse lcd
                INNER JOIN loan_contract lc ON lc.LID = lcd.LID AND lc.PIN = lcd.PIN
                LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
                LEFT JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                WHERE lcd.PIN = ?
                  AND lc.PID = ?
                  AND lcd.release_status IN ('pending', 'draft')
                ORDER BY lcd.disbursedate DESC, lcd.LID DESC";
        $rows = $this->db->query($sql, array($pin, $pid))->result();
        return $this->_enrich_pending_release_rows($rows, $pin);
    }

    /**
     * Search / list loan releases waiting for Cash Disbursement (release_status=pending).
     * Empty $key returns the pending list (for display). Optionally include the draft
     * linked to $include_draft_for_cd_id (edit of the same CD only).
     */
    function search_pending_releases($key, $limit = 20, $include_draft_for_cd_id = null) {
        $pin = current_user()->PIN;
        $this->ensure_release_workflow_columns();
        $key = trim((string) $key);
        $limit = max(1, min(50, (int) $limit));
        $params = array($pin);
        $status_sql = "lcd.release_status = 'pending'";
        if ($include_draft_for_cd_id !== null && (int) $include_draft_for_cd_id > 0) {
            $status_sql = "(lcd.release_status = 'pending' OR (lcd.release_status = 'draft' AND lcd.cash_disbursement_id = ?))";
            $params[] = (int) $include_draft_for_cd_id;
        }
        $key_sql = '';
        if ($key !== '') {
            $like = '%' . $this->db->escape_like_str($key) . '%';
            $key_sql = " AND (
                    lcd.LID LIKE ?
                    OR IFNULL(lcd.disburse_no, '') LIKE ?
                    OR IFNULL(lc.member_id, '') LIKE ?
                    OR IFNULL(lc.PID, '') LIKE ?
                    OR IFNULL(m.firstname, '') LIKE ?
                    OR IFNULL(m.middlename, '') LIKE ?
                    OR IFNULL(m.lastname, '') LIKE ?
                    OR CONCAT(IFNULL(m.firstname,''), ' ', IFNULL(m.middlename,''), ' ', IFNULL(m.lastname,'')) LIKE ?
                  )";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        $sql = "SELECT lcd.LID, lcd.disbursedate, lcd.comment, lcd.disburse_no, lcd.payment_method,
                       lcd.release_status, lcd.cash_disbursement_id,
                       lcd.offset_loan_ids,
                       lc.basic_amount, lc.member_id, lc.PID, lc.product_type, lc.installment_amount,
                       lp.name AS product_name,
                       m.firstname, m.middlename, m.lastname
                FROM loan_contract_disburse lcd
                INNER JOIN loan_contract lc ON lc.LID = lcd.LID AND lc.PIN = lcd.PIN
                LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
                LEFT JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                WHERE lcd.PIN = ?
                  AND {$status_sql}
                  {$key_sql}
                ORDER BY lcd.disbursedate DESC, lcd.LID DESC
                LIMIT {$limit}";
        $rows = $this->db->query($sql, $params)->result();
        return $this->_enrich_pending_release_rows($rows, $pin);
    }

    private function _enrich_pending_release_rows($rows, $pin) {
        foreach ($rows as $row) {
            $line_items = $this->get_disbursement_gl_items($row->LID, $pin);
            $net_cash = 0.0;
            $offset_total = 0.0;
            $offset_ids = array();
            $row->line_items = $line_items;
            if (!empty($row->offset_loan_ids)) {
                $decoded = json_decode($row->offset_loan_ids, true);
                if (is_array($decoded)) {
                    $offset_ids = $decoded;
                }
            }
            foreach ($offset_ids as $old_lid) {
                $bd = $this->get_loan_outstanding_for_offset($old_lid);
                if ($bd && !empty($bd['total'])) {
                    $offset_total += floatval($bd['total']);
                }
            }
            $net_cash = max(0, floatval($row->basic_amount) - $offset_total);
            foreach ($line_items as $item) {
                $credit = isset($item['credit']) ? floatval($item['credit']) : 0;
                $account = isset($item['account']) ? (string) $item['account'] : '';
                if ($credit > 0.009 && $account !== '21110' && $account !== '30130' && !in_array($account, $offset_ids, true)) {
                    $net_cash = $credit;
                }
            }
            $row->offset_total = round($offset_total, 2);
            $row->net_cash = round($net_cash, 2);
        }
        return $rows;
    }

    function finalize_release_payout_by_cash_disbursement($cash_disbursement_id, $journal_entry_id = null, $entry_date = null) {
        $pin = current_user()->PIN;
        $this->ensure_release_workflow_columns();
        $this->load->model('setting_model');
        $this->load->model('cash_disbursement_model');
        $this->load->library('loanbase');
        $release = $this->db->where('PIN', $pin)
            ->where('cash_disbursement_id', (int) $cash_disbursement_id)
            ->where('release_status', 'draft')
            ->get('loan_contract_disburse')
            ->row();
        if (!$release) {
            // Not a linked loan-release payout (or already finalized).
            return array('success' => true);
        }

        $LID = $release->LID;
        $loaninfo = $this->loan_info($LID)->row();
        if (!$loaninfo || (string) $loaninfo->PIN !== (string) $pin) {
            return array('success' => false, 'message' => 'Linked loan release not found for payout finalization.');
        }
        $line_items = $this->get_disbursement_gl_items($LID, $pin);
        if (empty($line_items)) {
            // Fallback to the cash disbursement journal lines used for GL posting.
            $cd_items = $this->cash_disbursement_model->get_disburse_items((int) $cash_disbursement_id);
            foreach ($cd_items as $it) {
                $debit = isset($it->debit) ? floatval($it->debit) : (isset($it->amount) ? floatval($it->amount) : 0);
                $credit = isset($it->credit) ? floatval($it->credit) : 0;
                if (empty($it->account) || ($debit <= 0 && $credit <= 0)) {
                    continue;
                }
                $line_items[] = array(
                    'account' => $it->account,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => isset($it->description) ? $it->description : '',
                );
            }
        }
        if (empty($line_items)) {
            return array('success' => false, 'message' => 'No accounting lines found to finalize the linked loan release.');
        }

        $pay_date = !empty($entry_date) ? $entry_date : $release->disbursedate;
        $payment_method = !empty($release->payment_method) ? $release->payment_method : 'Cash';

        $offset_ids = array();
        if (!empty($release->offset_loan_ids)) {
            $decoded = json_decode($release->offset_loan_ids, true);
            if (is_array($decoded)) {
                $offset_ids = $decoded;
            }
        }
        foreach ($offset_ids as $old_lid) {
            $settle = $this->settle_loan_by_offset($old_lid, $LID, $pay_date);
            if (empty($settle['success'])) {
                return array(
                    'success' => false,
                    'message' => !empty($settle['message']) ? $settle['message'] : ('Failed to settle offset loan ' . $old_lid),
                );
            }
        }

        $subledger = $this->post_disbursement_deduction_subledgers($LID, $loaninfo, $line_items, $pay_date, $payment_method);
        if (empty($subledger['success'])) {
            $detail = !empty($subledger['message'])
                ? $subledger['message']
                : 'Failed to post Savings/Share deduction sub-ledgers for the loan release.';
            return array(
                'success' => false,
                'message' => $detail,
            );
        }

        $schedule_exists = $this->db->where('LID', $LID)->where('PIN', $pin)->count_all_results('loan_contract_repayment_schedule');
        if (!$schedule_exists) {
            $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
            if (!$product) {
                return array('success' => false, 'message' => 'Loan product not found. Cannot create repayment schedule.');
            }
            $interest_method = (isset($product->interest_method) && ($product->interest_method == 1 || $product->interest_method == 2)) ? (int) $product->interest_method : 1;
            $interval = isset($product->interval) ? (int) $product->interval : 1;
            $schedule = $this->loanbase->create_repayment_schedule(
                $loaninfo->installment_amount,
                $loaninfo->rate,
                $loaninfo->number_istallment,
                $pay_date,
                $loaninfo->basic_amount,
                $LID,
                $interest_method,
                $interval
            );
            if (!empty($schedule)) {
                foreach ($schedule as $sk => $srow) {
                    if (!isset($schedule[$sk]['status'])) {
                        $schedule[$sk]['status'] = 0;
                    }
                    if (!isset($schedule[$sk]['sms_sent'])) {
                        $schedule[$sk]['sms_sent'] = 0;
                    }
                }
                $this->db->insert_batch('loan_contract_repayment_schedule', $schedule);
            }
        }

        $this->db->where('LID', $LID)->where('PIN', $pin)->update('loan_contract', array('disburse' => 1));
        $this->db->where('LID', $LID)
            ->where('PIN', $pin)
            ->where('cash_disbursement_id', (int) $cash_disbursement_id)
            ->where('release_status', 'draft')
            ->update('loan_contract_disburse', array(
                'release_status' => 'paid',
                'payout_journal_entry_id' => $journal_entry_id ? (int) $journal_entry_id : null,
                'payout_completed_at' => date('Y-m-d H:i:s'),
            ));
        return array('success' => true);
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

    /**
     * Post Savings (21110) and Paid-up Share (30130) credits from disbursement
     * accounting lines into member sub-ledgers. GL is already posted by
     * post_loan_disbursement_to_gl — savings credit uses a system_comment that
     * does not trigger a second GL post.
     *
     * @return array{success:bool,message?:string}
     */
    function post_disbursement_deduction_subledgers($LID, $loan_info, $line_items, $disburse_date, $paymethod = 'Cash') {
        if (empty($loan_info) || empty($line_items)) {
            return array('success' => true);
        }

        // Finance JE Review does not load loan_lang by default.
        $this->lang->load('loan');

        $savings_gl = '21110';
        $share_gl = '30130';
        if (function_exists('loan_disbursement_default_deductions')) {
            foreach (loan_disbursement_default_deductions() as $ded) {
                if (isset($ded['key']) && $ded['key'] === 'savings_deposit' && !empty($ded['account'])) {
                    $savings_gl = (string) $ded['account'];
                }
                if (isset($ded['key']) && $ded['key'] === 'paid_up_share' && !empty($ded['account'])) {
                    $share_gl = (string) $ded['account'];
                }
            }
        }

        $savings_amount = 0.0;
        $share_amount = 0.0;
        foreach ($line_items as $item) {
            $account = isset($item['account']) ? (string) $item['account'] : '';
            $credit = isset($item['credit']) ? floatval($item['credit']) : 0;
            if ($credit <= 0.009) {
                continue;
            }
            if ($account === $savings_gl) {
                $savings_amount += $credit;
            } elseif ($account === $share_gl) {
                $share_amount += $credit;
            }
        }
        $savings_amount = round($savings_amount, 2);
        $share_amount = round($share_amount, 2);

        if ($savings_amount <= 0.009 && $share_amount <= 0.009) {
            return array('success' => true);
        }

        $pid = $loan_info->PID;
        $member_id = $loan_info->member_id;
        $paymethod = $paymethod !== '' && $paymethod !== null ? $paymethod : 'Cash';
        $comment = 'Loan Disbursement ' . $LID;

        if ($savings_amount > 0.009) {
            $savings_result = $this->_post_disbursement_savings_subledger(
                $pid, $member_id, $savings_gl, $savings_amount, $paymethod, $comment, $disburse_date, $LID
            );
            if (empty($savings_result['success'])) {
                return $savings_result;
            }
        }

        if ($share_amount > 0.009) {
            $share_result = $this->_post_disbursement_share_subledger(
                $pid, $member_id, $share_amount, $paymethod, $comment, $disburse_date
            );
            if (empty($share_result['success'])) {
                return $share_result;
            }
        }

        return array('success' => true);
    }

    function _loan_lang_message($key, $fallback, $sprintf_args = array()) {
        $msg = lang($key);
        if ($msg === false || $msg === '' || $msg === $key) {
            $msg = $fallback;
        }
        if (!empty($sprintf_args)) {
            return vsprintf($msg, $sprintf_args);
        }
        return $msg;
    }

    /**
     * Credit member savings balance/transaction without posting GL again.
     */
    function _post_disbursement_savings_subledger($pid, $member_id, $gl_account, $amount, $paymethod, $comment, $disburse_date, $LID) {
        $this->lang->load('loan');
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('account_setup', $gl_account);
        $this->db->order_by('id', 'ASC');
        $account_type = $this->db->get('saving_account_type')->row();
        if (!$account_type || empty($account_type->account)) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_disburse_savings_type_missing',
                    'No savings product is mapped to GL account %s. Map a savings account type (account_setup) before releasing with a Savings deduction.',
                    array($gl_account)
                ),
            );
        }

        $this->load->model('finance_model');
        $member_account = $this->finance_model->saving_account_balance_by_member($pid, $member_id, $account_type->account);
        if (!$member_account || empty($member_account->account)) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_disburse_savings_account_missing',
                    'This member has no savings account for the Savings Deposit product. Open a savings account first, then post the payout.'
                ),
            );
        }

        // System comment must NOT match NORMAL DEPOSIT / INTEREST / etc. or GL would double-post.
        $receipt = $this->finance_model->credit(
            $member_account->account,
            $amount,
            $paymethod,
            $comment,
            '',
            '',
            $pid,
            'LOAN DISBURSEMENT DEDUCTION',
            0,
            $disburse_date,
            $LID
        );
        if (!$receipt) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_disburse_savings_post_fail',
                    'Failed to credit the member savings account for the Savings Deposit deduction.'
                ),
            );
        }
        return array('success' => true, 'receipt' => $receipt);
    }

    /**
     * Credit member share sub-ledger (members_share + share_transaction). No GL.
     */
    function _post_disbursement_share_subledger($pid, $member_id, $real_amount, $paymethod, $comment, $disburse_date) {
        $this->lang->load('loan');
        $this->load->model('setting_model');
        $this->load->model('share_model');

        $share_setup = $this->setting_model->share_setting_info();
        if (!$share_setup || empty($share_setup->amount) || floatval($share_setup->amount) <= 0) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_disburse_share_setup_missing',
                    'Share settings (cost per share) are not configured. Configure Shares before releasing with a Paid-up Capital Share deduction.'
                ),
            );
        }

        $cost_per_share = floatval($share_setup->amount);
        $max_share = isset($share_setup->max_share) ? floatval($share_setup->max_share) : 0;
        $share_info = $this->share_model->share_member_info($pid, $member_id);
        $previous_share = $share_info ? floatval($share_info->totalshare) : 0;
        $combined = $real_amount;
        if ($share_info) {
            $combined = $real_amount + floatval($share_info->remainbalance);
        }

        // Match share_buy: whole shares and remainder from combined amount.
        $share_number = intval($combined / $cost_per_share);
        $remain_amount = fmod($combined, $cost_per_share);
        // Avoid float noise for money remainders.
        $remain_amount = round($remain_amount, 2);
        if (abs($remain_amount - $cost_per_share) < 0.009) {
            $share_number += 1;
            $remain_amount = 0;
        }

        if ($max_share > 0 && ($previous_share + $share_number) > $max_share) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_disburse_share_max_reached',
                    'Paid-up Capital Share deduction would exceed the member maximum shares. Reduce the share deduction or raise the max.'
                ),
            );
        }

        $amountshare = round($share_number * $cost_per_share, 2);
        $add_share = $this->share_model->add_share(
            $pid,
            $member_id,
            $paymethod,
            $cost_per_share,
            $share_number,
            $amountshare,
            $remain_amount,
            $real_amount,
            $comment,
            '',
            $disburse_date
        );
        if (!$add_share) {
            return array(
                'success' => false,
                'message' => $this->_loan_lang_message(
                    'loan_disburse_share_post_fail',
                    'Failed to credit the member share sub-ledger for the Paid-up Capital Share deduction.'
                ),
            );
        }
        return array('success' => true, 'receipt' => $add_share);
    }

    /**
     * Active disbursed loans for a member that can be offset by a new loan.
     * Excludes the new loan LID being disbursed.
     */
    function get_offsetable_loans($PID, $exclude_LID = null) {
        $pin = current_user()->PIN;
        $this->db->select('loan_contract.*');
        $this->db->from('loan_contract');
        $this->db->where('loan_contract.PIN', $pin);
        $this->db->where('loan_contract.PID', $PID);
        $this->db->where('loan_contract.status', 4);
        $this->db->where('loan_contract.disburse', 1);
        if ($exclude_LID !== null && $exclude_LID !== '') {
            $this->db->where('loan_contract.LID !=', $exclude_LID);
        }
        $this->db->order_by('loan_contract.applicationdate', 'ASC');
        $loans = $this->db->get()->result();
        $out = array();
        foreach ($loans as $loan) {
            $breakdown = $this->get_loan_outstanding_for_offset($loan->LID);
            if ($breakdown && $breakdown['total'] > 0.009) {
                $loan->principal_outstanding = $breakdown['principal'];
                $loan->interest_outstanding = $breakdown['interest'];
                $loan->total_outstanding = $breakdown['total'];
                $loan->principle_account = $breakdown['principle_account'];
                $loan->interest_account = $breakdown['interest_account'];
                $loan->product_name = $breakdown['product_name'];
                $out[] = $loan;
            }
        }
        return $out;
    }

    /**
     * Outstanding principal + unpaid schedule interest for offset calculation.
     */
    function get_loan_outstanding_for_offset($LID) {
        $pin = current_user()->PIN;
        $loan = $this->loan_info($LID)->row();
        if (!$loan || (string) $loan->PIN !== (string) $pin) {
            return null;
        }
        $this->load->model('setting_model');
        $product = $this->setting_model->loanproduct($loan->product_type)->row();

        $open = $this->open_repayment_installment($LID);
        $principal = 0.0;
        $interest = 0.0;
        if (!empty($open)) {
            foreach ($open as $row) {
                $principal += isset($row->principle) ? floatval($row->principle) : 0;
                $interest += isset($row->interest) ? floatval($row->interest) : 0;
            }
        } else {
            $paid_sql = "SELECT COALESCE(SUM(principle),0) AS paid FROM loan_contract_repayment WHERE LID=" . $this->db->escape($LID) . " AND PIN=" . $this->db->escape($pin);
            if ($this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row()) {
                $paid_sql .= " AND (is_voided IS NULL OR is_voided = 0)";
            }
            $paid = floatval($this->db->query($paid_sql)->row()->paid);
            $principal = max(0, floatval($loan->basic_amount) - $paid);
        }

        return array(
            'LID' => $LID,
            'principal' => round($principal, 2),
            'interest' => round($interest, 2),
            'total' => round($principal + $interest, 2),
            'principle_account' => $product ? $product->loan_principle_account : '',
            'interest_account' => $product ? $product->loan_interest_account : '',
            'product_name' => $product ? $product->name : '',
            'basic_amount' => floatval($loan->basic_amount),
        );
    }

    /**
     * Ensure loan_contract has offset_loans column (comma-separated LIDs closed by this new loan).
     */
    function ensure_offset_loans_column() {
        if (!$this->db->query("SHOW COLUMNS FROM loan_contract LIKE 'offset_loans'")->row()) {
            $this->db->query("ALTER TABLE loan_contract ADD COLUMN offset_loans VARCHAR(255) NULL DEFAULT NULL COMMENT 'LIDs settled by offset reloan at disbursement'");
        }
    }

    /**
     * Operationally close an old loan as offset by a new loan.
     * Does NOT post cash GL (settlement is in the new loan disbursement journal).
     *
     * @return array{success:bool,message:string}
     */
    function settle_loan_by_offset($old_LID, $new_LID, $paydate, $createdby = null) {
        $pin = current_user()->PIN;
        $createdby = $createdby ? $createdby : current_user()->id;
        $loan = $this->loan_info($old_LID)->row();
        if (!$loan || (string) $loan->PIN !== (string) $pin) {
            return array('success' => false, 'message' => 'Offset loan not found: ' . $old_LID);
        }
        if ((int) $loan->status !== 4 || (int) $loan->disburse !== 1) {
            return array('success' => false, 'message' => 'Loan ' . $old_LID . ' is not an active disbursed loan.');
        }

        $breakdown = $this->get_loan_outstanding_for_offset($old_LID);
        if (!$breakdown || $breakdown['total'] <= 0) {
            // Already clear — just force closed if needed
            $this->db->update('loan_contract', array('status' => 5), array('LID' => $old_LID, 'PIN' => $pin, 'status' => 4));
            return array('success' => true, 'message' => 'Loan already settled.');
        }

        $open = $this->open_repayment_installment($old_LID);
        $receipt = $this->loan_repay_receipt($old_LID, $breakdown['total'], $paydate, substr('OFFSET-' . $new_LID, 0, 20));

        if (!empty($open)) {
            foreach ($open as $sched) {
                $repay = array(
                    'LID' => $old_LID,
                    'receipt' => $receipt,
                    'installment' => $sched->installment_number,
                    'amount' => floatval($sched->repayamount),
                    'penalt' => 0,
                    'paydate' => $paydate,
                    'interest' => floatval($sched->interest),
                    'duedate' => $sched->repaydate,
                    'principle' => floatval($sched->principle),
                    'balance' => 0,
                    'penalty_months' => 0,
                    'iliyobaki' => 0,
                    'createdby' => $createdby,
                    'month' => isset($sched->month) ? $sched->month : date('Ym', strtotime($paydate)),
                    'PIN' => $pin,
                );
                $this->db->insert('loan_contract_repayment', $repay);
            }
            $this->db->where('LID', $old_LID);
            $this->db->where('PIN', $pin);
            $this->db->where('status', 0);
            $this->db->update('loan_contract_repayment_schedule', array('status' => 2));
        } else {
            $repay = array(
                'LID' => $old_LID,
                'receipt' => $receipt,
                'installment' => 0,
                'amount' => $breakdown['total'],
                'penalt' => 0,
                'paydate' => $paydate,
                'interest' => $breakdown['interest'],
                'duedate' => $paydate,
                'principle' => $breakdown['principal'],
                'balance' => 0,
                'penalty_months' => 0,
                'iliyobaki' => 0,
                'createdby' => $createdby,
                'month' => date('Ym', strtotime($paydate)),
                'PIN' => $pin,
            );
            $this->db->insert('loan_contract_repayment', $repay);
        }

        $this->db->update('loan_contract', array('status' => 5), array(
            'LID' => $old_LID,
            'PIN' => $pin,
            'status' => 4,
            'disburse' => 1,
        ));

        return array(
            'success' => true,
            'message' => 'Offset settled ' . $old_LID,
            'receipt' => $receipt,
            'amount' => $breakdown['total'],
        );
    }

    function loan_repay_list() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT loan_contract.*,members.firstname,members.middlename,members.lastname  FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin' AND loan_contract.status=4 AND loan_contract.disburse=1 ORDER BY loan_contract.LID ASC")->result();
    }

    /**
     * Count released loans (status=4, disburse=1) that still have outstanding balance (open installments).
     */
    function count_loan_repayment_list_released_with_balance($key = null, $date_from = null, $date_to = null, $product_id = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT COUNT(DISTINCT lc.LID) AS cnt FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID
                INNER JOIN loan_contract_repayment_schedule rs ON rs.LID = lc.LID AND rs.status = 0 AND rs.PIN = " . (int)$pin . "
                WHERE lc.PIN = " . (int)$pin . " AND lc.status = 4 AND lc.disburse = 1";
        if (!is_null($key) && trim($key) !== '') {
            $key_esc = $this->db->escape_like_str($key);
            $sql .= " AND (lc.LID LIKE " . $this->db->escape($key_esc . '%') . " OR lc.member_id LIKE " . $this->db->escape($key_esc . '%') . " OR m.member_id LIKE " . $this->db->escape($key_esc . '%') . " OR m.firstname LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR m.middlename LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR m.lastname LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR CONCAT(m.firstname, ' ', m.lastname) LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) LIKE " . $this->db->escape('%' . $key_esc . '%') . ")";
        }
        if (!empty($date_from)) {
            $sql .= " AND DATE(lc.applicationdate) >= " . $this->db->escape($date_from);
        }
        if (!empty($date_to)) {
            $sql .= " AND DATE(lc.applicationdate) <= " . $this->db->escape($date_to);
        }
        if (!empty($product_id) && $product_id !== 'all') {
            $sql .= " AND lc.product_type = " . (int) $product_id;
        }
        $row = $this->db->query($sql)->row();
        return $row ? (int)$row->cnt : 0;
    }

    /**
     * Get released loans (status=4, disburse=1) that still have outstanding balance, for repayment list page (pagination).
     */
    function loan_repayment_list_released_with_balance($key = null, $limit = null, $start = 0, $date_from = null, $date_to = null, $product_id = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT lc.*, m.member_id, m.firstname, m.middlename, m.lastname,
                       lp.name AS product_name
                FROM loan_contract lc
                INNER JOIN members m ON m.PID = lc.PID
                LEFT JOIN loan_product lp ON lp.id = lc.product_type AND lp.PIN = lc.PIN
                INNER JOIN loan_contract_repayment_schedule rs ON rs.LID = lc.LID AND rs.status = 0 AND rs.PIN = " . (int)$pin . "
                WHERE lc.PIN = " . (int)$pin . " AND lc.status = 4 AND lc.disburse = 1";
        if (!is_null($key) && trim($key) !== '') {
            $key_esc = $this->db->escape_like_str($key);
            $sql .= " AND (lc.LID LIKE " . $this->db->escape($key_esc . '%') . " OR lc.member_id LIKE " . $this->db->escape($key_esc . '%') . " OR m.member_id LIKE " . $this->db->escape($key_esc . '%') . " OR m.firstname LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR m.middlename LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR m.lastname LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR CONCAT(m.firstname, ' ', m.lastname) LIKE " . $this->db->escape('%' . $key_esc . '%') . " OR CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) LIKE " . $this->db->escape('%' . $key_esc . '%') . ")";
        }
        if (!empty($date_from)) {
            $sql .= " AND DATE(lc.applicationdate) >= " . $this->db->escape($date_from);
        }
        if (!empty($date_to)) {
            $sql .= " AND DATE(lc.applicationdate) <= " . $this->db->escape($date_to);
        }
        if (!empty($product_id) && $product_id !== 'all') {
            $sql .= " AND lc.product_type = " . (int) $product_id;
        }
        $sql .= " GROUP BY lc.LID ORDER BY lc.applicationdate DESC";
        if (!is_null($limit)) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$start;
        }
        return $this->db->query($sql)->result();
    }

    /**
     * Lifecycle filter keys used on Loan List (derived; does not change loan_contract.status).
     */
    function loan_lifecycle_filter_keys() {
        return array('pending_release', 'released_unposted', 'active', 'past_due');
    }

    function is_loan_lifecycle_filter($status) {
        return $status !== null && $status !== '' && in_array((string) $status, $this->loan_lifecycle_filter_keys(), true);
    }

    function _loan_list_grace_days() {
        return defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0;
    }

    function _sql_loan_has_open_release($lc_alias = 'loan_contract') {
        return "EXISTS (
            SELECT 1 FROM loan_contract_disburse lcd
            WHERE lcd.LID = {$lc_alias}.LID
              AND lcd.PIN = {$lc_alias}.PIN
              AND lcd.release_status IN ('pending', 'draft')
        )";
    }

    function _sql_loan_is_past_due($lc_alias = 'loan_contract') {
        $grace = $this->_loan_list_grace_days();
        return "EXISTS (
            SELECT 1 FROM loan_contract_repayment_schedule rs
            WHERE rs.LID = {$lc_alias}.LID
              AND rs.PIN = {$lc_alias}.PIN
              AND rs.status = 0
              AND DATE_ADD(rs.repaydate, INTERVAL {$grace} DAY) < CURDATE()
        )";
    }

    function _lifecycle_filter_sql($lifecycle, $lc_alias = 'loan_contract') {
        $lifecycle = (string) $lifecycle;
        $open_rel = $this->_sql_loan_has_open_release($lc_alias);
        $past_due = $this->_sql_loan_is_past_due($lc_alias);
        if ($lifecycle === 'pending_release') {
            return "{$lc_alias}.status IN (4,6,9) AND {$lc_alias}.disburse = 0 AND NOT ({$open_rel})";
        }
        if ($lifecycle === 'released_unposted') {
            return "{$lc_alias}.status IN (4,6,9) AND {$lc_alias}.disburse = 0 AND ({$open_rel})";
        }
        if ($lifecycle === 'active') {
            return "{$lc_alias}.status IN (4,6,9) AND {$lc_alias}.disburse = 1 AND NOT ({$past_due})";
        }
        if ($lifecycle === 'past_due') {
            return "{$lc_alias}.status IN (4,6,9) AND {$lc_alias}.disburse = 1 AND ({$past_due})";
        }
        return '1=0';
    }

    function _lifecycle_select_sql($lc_alias = 'loan_contract') {
        $grace = $this->_loan_list_grace_days();
        return ",
            (SELECT lcd.release_status FROM loan_contract_disburse lcd
             WHERE lcd.LID = {$lc_alias}.LID AND lcd.PIN = {$lc_alias}.PIN
               AND lcd.release_status IN ('pending','draft')
             ORDER BY lcd.id DESC LIMIT 1) AS open_release_status,
            EXISTS (
                SELECT 1 FROM loan_contract_repayment_schedule rs
                WHERE rs.LID = {$lc_alias}.LID AND rs.PIN = {$lc_alias}.PIN
                  AND rs.status = 0
                  AND DATE_ADD(rs.repaydate, INTERVAL {$grace} DAY) < CURDATE()
            ) AS is_past_due_flag";
    }

    /**
     * Resolve display lifecycle for a loan_contract list row.
     * Approval statuses keep loan_status.name; Accepted/disbursed family show lifecycle labels.
     */
    function resolve_loan_lifecycle($row) {
        $this->lang->load('loan');
        $status = isset($row->status) ? (string) $row->status : '';
        $base_name = isset($row->name) ? $row->name : '';

        if ($status === 'bb' || !empty($row->is_beginning_balance)) {
            return array(
                'code' => 'bb',
                'name' => ($base_name !== '' ? $base_name : 'Beginning Balance'),
                'pill' => 'bb',
                'override_name' => false,
            );
        }

        if (in_array($status, array('0', '1', '2', '3', '5', '7', '8'), true)) {
            $pill = 'other';
            if ($status === '0') {
                $pill = 'new';
            } else if ($status === '1') {
                $pill = 'eval';
            } else if ($status === '2') {
                $pill = 'rejected';
            } else if ($status === '5') {
                $pill = 'closed';
            } else if ($status === '7' || $status === '8') {
                $pill = 'mixed';
            }
            return array(
                'code' => $status,
                'name' => $base_name,
                'pill' => $pill,
                'override_name' => false,
            );
        }

        $disburse = isset($row->disburse) && ((string) $row->disburse === '1' || $row->disburse === 1 || $row->disburse === true);
        $past_due = !empty($row->is_past_due_flag) && (string) $row->is_past_due_flag !== '0';
        $open_release = !empty($row->open_release_status);

        if ($disburse) {
            if ($past_due) {
                return array(
                    'code' => 'past_due',
                    'name' => $this->_loan_lang_message('loan_lifecycle_past_due', 'Past Due'),
                    'pill' => 'past-due',
                    'override_name' => true,
                );
            }
            return array(
                'code' => 'active',
                'name' => $this->_loan_lang_message('loan_lifecycle_active', 'Active'),
                'pill' => 'active',
                'override_name' => true,
            );
        }

        if ($open_release) {
            return array(
                'code' => 'released_unposted',
                'name' => $this->_loan_lang_message('loan_lifecycle_released_unposted', 'Released not posted'),
                'pill' => 'released-unposted',
                'override_name' => true,
            );
        }

        if (in_array($status, array('4', '6', '9'), true)) {
            return array(
                'code' => 'pending_release',
                'name' => $this->_loan_lang_message('loan_lifecycle_pending_release', 'Pending Release'),
                'pill' => 'pending-release',
                'override_name' => true,
            );
        }

        return array(
            'code' => $status,
            'name' => $base_name,
            'pill' => 'other',
            'override_name' => false,
        );
    }

    function enrich_loan_list_lifecycle($rows) {
        if (empty($rows) || !is_array($rows)) {
            return $rows;
        }
        foreach ($rows as $row) {
            $info = $this->resolve_loan_lifecycle($row);
            $row->lifecycle_code = $info['code'];
            $row->lifecycle_name = $info['name'];
            $row->lifecycle_pill = $info['pill'];
            if (!empty($info['override_name'])) {
                $row->name = $info['name'];
            }
        }
        return $rows;
    }

    function count_loan($key = null, $status = null) {
        $pin = current_user()->PIN;
        
        // Filter: Beginning Balance only (from loan_beginning_balances)
        if ($status !== null && $status !== '' && (string)$status === 'bb') {
            $sql_bb = "SELECT loan_beginning_balances.id FROM loan_beginning_balances INNER JOIN members ON members.member_id=loan_beginning_balances.member_id WHERE loan_beginning_balances.PIN='$pin' AND members.PIN='$pin'";
            $sql_bb .= " AND (loan_beginning_balances.loan_id IS NULL OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='$pin'))";
            if (!is_null($key)) {
                $sql_bb .= " AND (loan_beginning_balances.loan_id LIKE " . $this->db->escape($key . '%') . " OR loan_beginning_balances.member_id LIKE " . $this->db->escape($key . '%') . " OR members.firstname LIKE " . $this->db->escape($key . '%') . " OR members.lastname LIKE " . $this->db->escape($key . '%') . ")";
            }
            return $this->db->query($sql_bb)->num_rows();
        }

        // Lifecycle filters (derived from disburse / release / schedule)
        if ($this->is_loan_lifecycle_filter($status)) {
            $sql = "SELECT loan_contract.LID FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin' AND (" . $this->_lifecycle_filter_sql($status, 'loan_contract') . ")";
            if (!is_null($key)) {
                $sql .= " AND (loan_contract.LID LIKE " . $this->db->escape($key . '%') . " OR loan_contract.member_id LIKE " . $this->db->escape($key . '%') . " OR members.firstname LIKE " . $this->db->escape($key . '%') . " OR members.lastname LIKE " . $this->db->escape($key . '%') . ")";
            }
            return $this->db->query($sql)->num_rows();
        }

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
        $life_select = $this->_lifecycle_select_sql('loan_contract');
        
        // Filter: Beginning Balance only
        if ($status !== null && $status !== '' && (string)$status === 'bb') {
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
                        'bb' as status,
                        1 as edit,
                        1 as is_beginning_balance,
                        loan_beginning_balances.id as bb_id,
                        loan_beginning_balances.posted as bb_posted,
                        loan_beginning_balances.fiscal_year_id as bb_fiscal_year_id,
                        COALESCE(loan_product.`interval`, 1) as `interval`,
                        loan_beginning_balances.disbursement_date as applicationdate,
                        loan_product.name as product_name
                    FROM loan_beginning_balances 
                    INNER JOIN members ON members.member_id=loan_beginning_balances.member_id 
                    LEFT JOIN loan_product ON loan_product.id=loan_beginning_balances.loan_product_id AND loan_product.PIN='$pin'
                    WHERE loan_beginning_balances.PIN='$pin' AND members.PIN='$pin'";
            $sql_bb .= " AND (loan_beginning_balances.loan_id IS NULL OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='$pin'))";
            if (!is_null($key)) {
                $sql_bb .= " AND (loan_beginning_balances.loan_id LIKE '$key%' OR loan_beginning_balances.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
            }
            $sql_bb .= " ORDER BY loan_beginning_balances.disbursement_date DESC, loan_beginning_balances.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$start;
            return $this->enrich_loan_list_lifecycle($this->db->query($sql_bb)->result());
        }

        // Lifecycle filters
        if ($this->is_loan_lifecycle_filter($status)) {
            $sql = "SELECT loan_contract.*,loan_status.name,loan_product.name AS product_name" . $life_select . " FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
            $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status ";
            $sql .= " LEFT JOIN loan_product ON loan_product.id=loan_contract.product_type AND loan_product.PIN='$pin' ";
            $sql .= " WHERE loan_contract.PIN='$pin' AND (" . $this->_lifecycle_filter_sql($status, 'loan_contract') . ")";
            if (!is_null($key)) {
                $sql .= " AND ( loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
            }
            $sql .= " ORDER BY loan_contract.applicationdate DESC LIMIT " . (int)$limit . " OFFSET " . (int)$start;
            return $this->enrich_loan_list_lifecycle($this->db->query($sql)->result());
        }

        // When status filter is set, get only loan_contract with that status (no beginning balances)
        if ($status !== null && $status !== '') {
            $sql = "SELECT loan_contract.*,loan_status.name,loan_product.name AS product_name" . $life_select . " FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
            $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status ";
            $sql .= " LEFT JOIN loan_product ON loan_product.id=loan_contract.product_type AND loan_product.PIN='$pin' ";
            $sql .= " WHERE loan_contract.PIN='$pin' AND loan_contract.status=" . $this->db->escape($status);
            if (!is_null($key)) {
                $sql .= " AND ( loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
            }
            $sql .= " ORDER BY loan_contract.applicationdate DESC LIMIT " . (int)$limit . " OFFSET " . (int)$start;
            return $this->enrich_loan_list_lifecycle($this->db->query($sql)->result());
        }
        
        // Get regular loans from loan_contract
        $sql = "SELECT loan_contract.*,loan_status.name,loan_product.name AS product_name" . $life_select . " FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
        $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status ";
        $sql .= " LEFT JOIN loan_product ON loan_product.id=loan_contract.product_type AND loan_product.PIN='$pin' ";
        $sql .= " WHERE loan_contract.PIN='$pin'";

        if (!is_null($key)) {
            $sql .= "  AND ( loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }

        $sql.= " ORDER BY loan_contract.applicationdate DESC";
        
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
                        'bb' as status,
                        1 as edit,
                        1 as is_beginning_balance,
                        loan_beginning_balances.id as bb_id,
                        loan_beginning_balances.posted as bb_posted,
                        loan_beginning_balances.fiscal_year_id as bb_fiscal_year_id,
                        COALESCE(loan_product.`interval`, 1) as `interval`,
                        loan_beginning_balances.disbursement_date as applicationdate,
                        loan_product.name as product_name
                    FROM loan_beginning_balances 
                    INNER JOIN members ON members.member_id=loan_beginning_balances.member_id 
                    LEFT JOIN loan_product ON loan_product.id=loan_beginning_balances.loan_product_id AND loan_product.PIN='$pin'
                    WHERE loan_beginning_balances.PIN='$pin' AND members.PIN='$pin'";
        
        // Exclude beginning balances that already have corresponding loan_contract entries
        $sql_bb .= " AND (loan_beginning_balances.loan_id IS NULL OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN='$pin'))";
        
        if (!is_null($key)) {
            $sql_bb .= " AND (loan_beginning_balances.loan_id LIKE '$key%' OR loan_beginning_balances.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }
        
        $sql_bb .= " ORDER BY loan_beginning_balances.disbursement_date DESC, loan_beginning_balances.created_at DESC";
        
        $beginning_balances = $this->db->query($sql_bb)->result();
        
        // Combine results
        $all_results = array_merge($regular_loans, $beginning_balances);
        
        // Sort by applicationdate DESC
        usort($all_results, function($a, $b) {
            $dateA = isset($a->applicationdate) ? strtotime($a->applicationdate) : 0;
            $dateB = isset($b->applicationdate) ? strtotime($b->applicationdate) : 0;
            return $dateB - $dateA;
        });
        
        // Apply pagination
        return $this->enrich_loan_list_lifecycle(array_slice($all_results, $start, $limit));
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

    /**
     * Resolve overdue grace days for a loan product.
     * Product penalt_grace_days (optional) overrides MAX_NUMBER_DAYS_OVERDUE_PENALT when set (>= 0).
     *
     * @param object|null $product loan_product row
     * @return int
     */
    function get_penalt_grace_days($product = null) {
        $default = defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0;
        if (!$product) {
            return $default;
        }
        if (!isset($product->penalt_grace_days) || $product->penalt_grace_days === null || $product->penalt_grace_days === '') {
            return $default;
        }
        if (!is_numeric($product->penalt_grace_days)) {
            return $default;
        }
        $days = (int) $product->penalt_grace_days;
        return ($days >= 0) ? $days : $default;
    }

    /**
     * Calculate what is due as of $paydate for open schedule installments.
     * Matches loan_repayment_process / loan_repayment_save overdue rules:
     * grace = product penalt_grace_days if set, else MAX_NUMBER_DAYS_OVERDUE_PENALT; after grace, penalty months apply.
     *
     * @param string $LID
     * @param string $paydate Y-m-d
     * @return object
     */
    function calculate_repayment_due($LID, $paydate) {
        $loaninfo = $this->loan_info($LID)->row();
        $empty = (object) array(
            'items' => array(),
            'total_installments' => 0,
            'total_penalty' => 0,
            'total_due' => 0,
            'carry_balance' => 0,
            'net_due' => 0,
            'minimum_to_apply' => 0,
            'suggested_amount' => 0,
            'grace_days' => defined('MAX_NUMBER_DAYS_OVERDUE_PENALT') ? (int) MAX_NUMBER_DAYS_OVERDUE_PENALT : 0,
            'penalt_percentage' => 0,
            'penalt_method' => 0,
            'installment_amount' => 0,
            'paydate' => $paydate,
            'has_overdue' => false,
            'overdue_count' => 0,
        );
        if (!$loaninfo || empty($paydate)) {
            return $empty;
        }

        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
        $penalt_method = $product ? (int) $product->penalt_method : 0;
        $penalt_percentage = $product && $product->penalt_percentage !== '' && $product->penalt_percentage !== null
            ? (float) $product->penalt_percentage : 0;
        $grace_days = $this->get_penalt_grace_days($product);
        $installment_amount = round((float) $loaninfo->installment_amount, 2);
        $carry = round((float) $this->get_previous_remain_balance($LID), 2);
        $open = $this->open_repayment_installment($LID);

        $items = array();
        $total_installments = 0;
        $total_penalty = 0;
        $has_overdue = false;
        $overdue_count = 0;

        foreach ($open as $row) {
            $due_date = $row->repaydate;
            // Only installments whose due date has arrived as of payment date
            if (strtotime($due_date) > strtotime($paydate)) {
                break;
            }

            $grace_end = date('Y-m-d', strtotime($due_date . ' +' . $grace_days . ' days'));
            $is_overdue = (strtotime($paydate) > strtotime($grace_end));
            $penalty = 0;
            $penalty_months = 0;

            if ($is_overdue) {
                $d1 = new DateTime($grace_end);
                $d2 = new DateTime($paydate);
                $penalty_months = ($d1->diff($d2)->m + ($d1->diff($d2)->y * 12)) + 1;
                $penalt_unit = 0;
                if ($penalt_method == 1) {
                    $penalt_unit = ($penalt_percentage / 100) * (float) $row->principle;
                } else if ($penalt_method == 2) {
                    $penalt_unit = ($penalt_percentage / 100) * ((float) $row->principle + (float) $row->interest);
                }
                $penalty = round($penalt_unit * $penalty_months, 2);
                $has_overdue = true;
                $overdue_count++;
            }

            $line_total = round($installment_amount + $penalty, 2);
            $items[] = (object) array(
                'installment' => (int) $row->installment_number,
                'due_date' => $due_date,
                'grace_end' => $grace_end,
                'status' => $is_overdue ? 'overdue' : 'due',
                'installment_amount' => $installment_amount,
                'principle' => round((float) $row->principle, 2),
                'interest' => round((float) $row->interest, 2),
                'penalty' => $penalty,
                'penalty_months' => $penalty_months,
                'total' => $line_total,
            );
            $total_installments = round($total_installments + $installment_amount, 2);
            $total_penalty = round($total_penalty + $penalty, 2);
        }

        $total_due = round($total_installments + $total_penalty, 2);
        $net_due = round(max(0, $total_due - $carry), 2);
        $minimum_to_apply = 0;
        if (!empty($items)) {
            $minimum_to_apply = round(max(0, (float) $items[0]->total - $carry), 2);
            // If carry alone covers first installment, still need a positive payment to process
            // when net of all due is > 0; minimum is first line after carry.
            if ($minimum_to_apply <= 0 && $net_due > 0) {
                // Carry covers first installment; next cash needed is remaining net due
                // but applying still requires paying at least the next uncovered portion.
                $covered = $carry;
                foreach ($items as $it) {
                    if ($covered >= $it->total) {
                        $covered = round($covered - $it->total, 2);
                        continue;
                    }
                    $minimum_to_apply = round($it->total - $covered, 2);
                    break;
                }
                if ($minimum_to_apply <= 0) {
                    $minimum_to_apply = $net_due;
                }
            } else if ($minimum_to_apply <= 0 && $total_due > 0 && $carry >= $total_due) {
                // Carry already covers everything due — no new cash required to clear due items
                $minimum_to_apply = 0;
            }
        }

        return (object) array(
            'items' => $items,
            'total_installments' => $total_installments,
            'total_penalty' => $total_penalty,
            'total_due' => $total_due,
            'carry_balance' => $carry,
            'net_due' => $net_due,
            'minimum_to_apply' => $minimum_to_apply,
            'suggested_amount' => ($net_due > 0 ? $net_due : $installment_amount),
            'grace_days' => $grace_days,
            'penalt_percentage' => $penalt_percentage,
            'penalt_method' => $penalt_method,
            'installment_amount' => $installment_amount,
            'paydate' => $paydate,
            'has_overdue' => $has_overdue,
            'overdue_count' => $overdue_count,
        );
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
        // Do not use count($check): PHP 8 throws TypeError on null/object.
        if ($check) {
            $this->db->where('LID', $LID);
            $this->db->where('PIN', $pin);
            $this->db->set('balance', $amount, FALSE);
            return $this->db->update('loan_balance_carry');
        }
        return $this->db->insert('loan_balance_carry', array('LID' => $LID, 'PIN' => $pin, 'balance' => $amount));
    }

    /**
     * Ensure loan_contract_repayment insert has NOT NULL columns (strict SQL).
     */
    function _normalize_repayment_row($array_data) {
        if (!is_array($array_data)) {
            return $array_data;
        }
        if (!array_key_exists('penalt', $array_data) || $array_data['penalt'] === null || $array_data['penalt'] === '') {
            $array_data['penalt'] = 0;
        }
        if (!array_key_exists('penalty_months', $array_data) || $array_data['penalty_months'] === null || $array_data['penalty_months'] === '') {
            $array_data['penalty_months'] = 0;
        }
        if (!array_key_exists('month', $array_data) || $array_data['month'] === null) {
            $array_data['month'] = '';
        }
        return $array_data;
    }

    /**
     * Plan installment applications for a payment (no writes).
     * Same carry / grace / penalty / payoff rules as Loan Management → Loan Repayment.
     *
     * @return array
     */
    function plan_loan_repayment_applications($LID, $amount, $paydate) {
        $pin = current_user()->PIN;
        $amount = round((float) $amount, 2);
        $loaninfo = $this->loan_info($LID)->row();
        if (!$loaninfo || (string) $loaninfo->PIN !== (string) $pin) {
            return array('success' => false, 'message' => 'Loan not found.');
        }
        if ((int) $loaninfo->status !== 4) {
            return array('success' => false, 'message' => 'Invalid Operation, Loan Status does not allow Repayment process');
        }
        $this->load->model('setting_model');
        $product = $this->setting_model->loanproduct($loaninfo->product_type)->row();
        $open_repayment = $this->open_repayment_installment($LID);
        if (count($open_repayment) < 1) {
            return array(
                'success' => false,
                'message' => 'No open installment available for new payment',
                'close_if_empty' => true,
                'loaninfo' => $loaninfo,
            );
        }
        if ($amount <= 0) {
            return array('success' => false, 'message' => 'Amount should be greater than 0', 'loaninfo' => $loaninfo);
        }

        $due_preview = $this->calculate_repayment_due($LID, $paydate);
        $minimum = isset($due_preview->minimum_to_apply) ? (float) $due_preview->minimum_to_apply : 0;
        if (!empty($due_preview->items) && $minimum > 0 && round($amount, 2) + 0.00001 < $minimum) {
            return array(
                'success' => false,
                'message' => sprintf(lang('loan_repay_amount_insufficient'), number_format($minimum, 2)),
                'minimum_to_apply' => $minimum,
                'due' => $due_preview,
                'loaninfo' => $loaninfo,
                'product' => $product,
            );
        }

        $previous_remain_balance = $this->get_previous_remain_balance($LID);
        $amount_tmp = ($amount + $previous_remain_balance);
        $applications = array();
        $applied_any = false;
        $createdby = current_user()->id;

        foreach ($open_repayment as $value) {
            $repay_amount_install = $loaninfo->installment_amount;
            if ($amount_tmp >= $repay_amount_install) {
                $grace_days = $this->get_penalt_grace_days($product);
                $max_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($value->repaydate)) . " +" . $grace_days . " days"));
                if ($paydate <= $max_date) {
                    $repay_amount_install_to_pay_all_loan = round($value->repayamount + $value->balance, 2);
                    if ($amount_tmp >= $repay_amount_install_to_pay_all_loan) {
                        $new_principle = round($repay_amount_install_to_pay_all_loan - $value->interest, 2);
                        $amount_tmp -= $repay_amount_install_to_pay_all_loan;
                        $applications[] = array(
                            'mode' => 'all',
                            'schedule_id' => $value->id,
                            'data' => array(
                                'LID' => $LID,
                                'installment' => $value->installment_number,
                                'amount' => $repay_amount_install_to_pay_all_loan,
                                'paydate' => $paydate,
                                'interest' => $value->interest,
                                'principle' => $new_principle,
                                'duedate' => $value->repaydate,
                                'balance' => 0,
                                'iliyobaki' => round($amount_tmp, 2),
                                'createdby' => $createdby,
                                'PIN' => $pin,
                            ),
                        );
                        $applied_any = true;
                        break;
                    } else {
                        $amount_tmp -= $repay_amount_install;
                        $applications[] = array(
                            'mode' => 'partial',
                            'schedule_id' => $value->id,
                            'data' => array(
                                'LID' => $LID,
                                'installment' => $value->installment_number,
                                'amount' => $repay_amount_install,
                                'paydate' => $paydate,
                                'interest' => $value->interest,
                                'principle' => $value->principle,
                                'duedate' => $value->repaydate,
                                'balance' => $value->balance,
                                'iliyobaki' => round($amount_tmp, 2),
                                'createdby' => $createdby,
                                'PIN' => $pin,
                            ),
                        );
                        $applied_any = true;
                    }
                } else {
                    $d1 = new DateTime($max_date);
                    $d2 = new DateTime($paydate);
                    $number_months = ($d1->diff($d2)->m + ($d1->diff($d2)->y * 12)) + 1;
                    $penalt_method = $product->penalt_method;
                    $penalt_percentage = $product->penalt_percentage;
                    $penalt = 0;
                    $principle = $value->principle;
                    $interest_val = $value->interest;
                    if ($penalt_method == 1) {
                        $penalt = (($penalt_percentage / 100) * $principle);
                    } else if ($penalt_method == 2) {
                        $penalt = (($penalt_percentage / 100) * ($principle + $interest_val));
                    }
                    $penalt_avail = round($penalt, 2);
                    $penalt_total = round($penalt_avail * $number_months, 2);
                    $test_remain = round($repay_amount_install + $penalt_total, 2);
                    if ($amount_tmp >= $test_remain) {
                        $repay_amount_install_to_pay_all_loan = round($value->repayamount + $value->balance + $penalt_total, 2);
                        if ($amount_tmp >= $repay_amount_install_to_pay_all_loan) {
                            $new_principle = round($value->repayamount + $value->balance - $value->interest, 2);
                            $amount_tmp -= $repay_amount_install_to_pay_all_loan;
                            $applications[] = array(
                                'mode' => 'all',
                                'schedule_id' => $value->id,
                                'data' => array(
                                    'LID' => $LID,
                                    'installment' => $value->installment_number,
                                    'amount' => $repay_amount_install_to_pay_all_loan,
                                    'paydate' => $paydate,
                                    'interest' => $value->interest,
                                    'principle' => $new_principle,
                                    'balance' => 0,
                                    'duedate' => $value->repaydate,
                                    'iliyobaki' => round($amount_tmp, 2),
                                    'penalt' => $penalt_total,
                                    'penalty_months' => $number_months,
                                    'createdby' => $createdby,
                                    'PIN' => $pin,
                                ),
                            );
                            $applied_any = true;
                            break;
                        } else {
                            $amount_tmp -= $test_remain;
                            $applications[] = array(
                                'mode' => 'partial',
                                'schedule_id' => $value->id,
                                'data' => array(
                                    'LID' => $LID,
                                    'installment' => $value->installment_number,
                                    'amount' => $repay_amount_install,
                                    'paydate' => $paydate,
                                    'interest' => $value->interest,
                                    'principle' => $value->principle,
                                    'balance' => $value->balance,
                                    'duedate' => $value->repaydate,
                                    'iliyobaki' => round($amount_tmp, 2),
                                    'penalt' => $penalt_total,
                                    'penalty_months' => $number_months,
                                    'createdby' => $createdby,
                                    'PIN' => $pin,
                                ),
                            );
                            $applied_any = true;
                        }
                    } else {
                        break;
                    }
                }
            } else {
                break;
            }
        }

        if (!$applied_any) {
            return array(
                'success' => false,
                'message' => sprintf(lang('loan_repay_amount_insufficient'), number_format($minimum, 2)),
                'minimum_to_apply' => $minimum,
                'due' => $due_preview,
                'loaninfo' => $loaninfo,
                'product' => $product,
            );
        }

        $principle_total = 0;
        $interest_total = 0;
        $penalt_total_sum = 0;
        foreach ($applications as $app) {
            $principle_total += isset($app['data']['principle']) ? (float) $app['data']['principle'] : 0;
            $interest_total += isset($app['data']['interest']) ? (float) $app['data']['interest'] : 0;
            $penalt_total_sum += isset($app['data']['penalt']) ? (float) $app['data']['penalt'] : 0;
        }

        return array(
            'success' => true,
            'applications' => $applications,
            'amount_tmp' => round($amount_tmp, 2),
            'loaninfo' => $loaninfo,
            'product' => $product,
            'due' => $due_preview,
            'minimum_to_apply' => $minimum,
            'principle' => round($principle_total, 2),
            'interest' => round($interest_total, 2),
            'penalt' => round($penalt_total_sum, 2),
            'amount' => $amount,
        );
    }

    /**
     * Apply a loan repayment to the schedule (receipt, installments, carry, close).
     * When post_gl is false, sub-ledger only — Cash Receipt journal is the GL.
     *
     * @return array
     */
    function apply_loan_repayment($LID, $amount, $paydate, $receipt_no, $cash_account = null, $options = array()) {
        if (!is_array($options)) {
            $options = array();
        }
        $post_gl = !array_key_exists('post_gl', $options) || !empty($options['post_gl']);
        $manage_transaction = !array_key_exists('manage_transaction', $options) || !empty($options['manage_transaction']);
        $pin = current_user()->PIN;
        $amount = round((float) $amount, 2);

        $plan = $this->plan_loan_repayment_applications($LID, $amount, $paydate);
        if (empty($plan['success'])) {
            if (!empty($plan['close_if_empty'])) {
                $this->db->update('loan_contract', array('status' => 5), array('LID' => $LID, 'status' => 4, 'disburse' => 1, 'PIN' => $pin));
            }
            return array('success' => false, 'message' => isset($plan['message']) ? $plan['message'] : 'Loan repayment could not be applied.', 'plan' => $plan);
        }

        if ($receipt_no !== null && $receipt_no !== '' && $this->receipt_no_exists_loan_repayment($receipt_no)) {
            return array('success' => false, 'message' => lang('cash_receipt_no_exists'));
        }

        if ($manage_transaction) {
            $this->db->trans_start();
        }
        $receipt = $this->loan_repay_receipt($LID, $amount, $paydate, $receipt_no);
        foreach ($plan['applications'] as $app) {
            $array_data = $app['data'];
            $array_data['receipt'] = $receipt;
            if ($app['mode'] === 'all') {
                $ok = $this->record_loan_repayment_all($array_data, $app['schedule_id'], $array_data['LID'], $cash_account, $post_gl);
            } else {
                $ok = $this->record_loan_repayment($array_data, $app['schedule_id'], $cash_account, $post_gl);
            }
            if ($ok === false) {
                if ($manage_transaction) {
                    $this->db->trans_rollback();
                } else {
                    $this->db->_trans_status = FALSE;
                }
                return array('success' => false, 'message' => 'Loan repayment GL posting failed. Check payment method and loan product GL accounts.');
            }
        }
        if ($plan['amount_tmp'] > 0) {
            $this->add_remain_balance($LID, round($plan['amount_tmp'], 2));
        } else {
            $this->add_remain_balance($LID, 0);
        }
        $open_repayment_check = $this->open_repayment_installment($LID);
        if (count($open_repayment_check) < 1) {
            $this->db->update('loan_contract', array('status' => 5), array('LID' => $LID, 'status' => 4, 'disburse' => 1, 'PIN' => $pin));
        }
        if ($manage_transaction) {
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                return array('success' => false, 'message' => 'Loan repayment save failed. Please try again.');
            }
        }
        return array('success' => true, 'receipt' => $receipt, 'plan' => $plan);
    }

    /**
     * Dry-run apply totals for Cash Receipt journal lines.
     */
    function preview_loan_repayment_application($LID, $amount, $paydate) {
        return $this->plan_loan_repayment_applications($LID, $amount, $paydate);
    }

    /**
     * After Cash Receipt journal is posted to GL: apply schedule without a second cash posting.
     */
    function finalize_loan_repayment_by_cash_receipt($cash_receipt_id, $journal_entry_id = null, $entry_date = null) {
        $this->load->model('cash_receipt_model');
        $this->cash_receipt_model->ensure_received_from_columns();
        $pin = current_user()->PIN;
        $cash_receipt_id = (int) $cash_receipt_id;
        $receipt = $this->cash_receipt_model->get_cash_receipt($cash_receipt_id);
        if (!$receipt) {
            return array('success' => true);
        }
        $type = isset($receipt->received_from_type) ? strtolower(trim((string) $receipt->received_from_type)) : '';
        $lid = isset($receipt->loan_repayment_lid) ? trim((string) $receipt->loan_repayment_lid) : '';
        if ($type !== 'loan_repayment' || $lid === '') {
            return array('success' => true);
        }
        if (!empty($receipt->loan_repayment_applied) && !empty($receipt->loan_repayment_receipt)) {
            return array('success' => true);
        }

        $amount = isset($receipt->total_amount) ? round((float) $receipt->total_amount, 2) : 0;
        $paydate = !empty($receipt->receipt_date) ? $receipt->receipt_date : $entry_date;
        if (empty($paydate)) {
            $paydate = date('Y-m-d');
        }
        $receipt_no = isset($receipt->receipt_no) ? $receipt->receipt_no : null;
        $cash_account = null;
        if (!empty($receipt->payment_method)) {
            $this->load->model('payment_method_config_model');
            $pm = $this->payment_method_config_model->get_account_for_payment_method($receipt->payment_method);
            if ($pm && !empty($pm->gl_account_code)) {
                $cash_account = $pm->gl_account_code;
            }
            if (!$cash_account) {
                $methods = $this->payment_method_config_model->get_all_payment_methods();
                foreach ($methods as $method) {
                    if (isset($method->name) && strcasecmp(trim($method->name), trim($receipt->payment_method)) === 0) {
                        $cash_account = $this->get_credit_account_for_payment_method($method->id);
                        break;
                    }
                }
            }
        }

        $applied = $this->apply_loan_repayment($lid, $amount, $paydate, $receipt_no, $cash_account, array(
            'post_gl' => false,
            'manage_transaction' => false,
        ));
        if (empty($applied['success'])) {
            return array(
                'success' => false,
                'message' => !empty($applied['message']) ? $applied['message'] : 'Failed to apply loan repayment after GL posting.',
            );
        }

        $this->cash_receipt_model->mark_loan_repayment_applied($cash_receipt_id, $applied['receipt']);
        return array('success' => true, 'receipt' => $applied['receipt']);
    }

    function record_loan_repayment($array_data, $repay_schedule_ref, $cash_account = null, $post_gl = true) {
        $pin = current_user()->PIN;
        $array_data = $this->_normalize_repayment_row($array_data);
        $this->db->trans_start();
        $insert = $this->db->insert('loan_contract_repayment', $array_data);
        $referenceID = $this->_loan_repayment_insert_id($array_data);
        if ($post_gl) {
        $this->load->model('setting_model');
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

        $accounts_needed = array(
            $debit_account,
            $product ? $product->loan_principle_account : null,
            $product ? $product->loan_interest_account : null,
        );
        $equity_account_setting = function_exists('default_text_value') ? default_text_value('RETAINED_EARNINGS_ACCOUNT') : '';
        $equity_account = (is_numeric($equity_account_setting) && (int)$equity_account_setting > 0) ? (int)$equity_account_setting : 3000002;
        $accounts_needed[] = $equity_account;
        if (array_key_exists('penalt', $array_data) && floatval($array_data['penalt']) > 0) {
            $accounts_needed[] = $product ? $product->loan_penalt_account : null;
        }
        foreach ($accounts_needed as $acct) {
            if ($acct === null || $acct === '' || !account_row_info($acct)) {
                $this->db->_trans_status = FALSE;
                $this->db->trans_complete();
                return false;
            }
        }

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
        if (array_key_exists('penalt', $array_data) && floatval($array_data['penalt']) > 0) {
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

        }


        $this->db->update('loan_contract_repayment_schedule', array('status' => 1), array('id' => $repay_schedule_ref));
        $this->db->update('loan_repayment_receipt', array('affect_loan' => 1, 'installment' => $array_data['installment']), array('receipt' => $array_data['receipt']));
        $this->db->trans_complete();
        return $insert;
    }
    
    
    //paying all loan before the end of the given duration
    function record_loan_repayment_all($array_data, $repay_schedule_ref, $loan_id, $cash_account = null, $post_gl = true) {
        $pin = current_user()->PIN;
        $array_data = $this->_normalize_repayment_row($array_data);
        $this->db->trans_start();
        $insert = $this->db->insert('loan_contract_repayment', $array_data);
        $referenceID = $this->_loan_repayment_insert_id($array_data);
        if ($post_gl) {
        $this->load->model('setting_model');
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
        if (array_key_exists('penalt', $array_data) && floatval($array_data['penalt']) > 0) {
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

    /**
     * Get loan ledger transactions for a single loan (disbursement + repayments) in date order.
     * Returns array of objects: date, description, debit, credit, type ('disbursement'|'repayment'),
     * and for repayments: schedule_installment, duedate, interest, penalt, amount_paid.
     */
    function get_loan_ledger_transactions($LID) {
        $pin = current_user()->PIN;
        $LID = $this->db->escape_str($LID);
        $rows = array();

        // Disbursement row(s) from loan_contract_disburse
        if ($this->db->table_exists('loan_contract_disburse')) {
            $this->db->select('lcd.disbursedate as date, lc.basic_amount, lcd.comment');
            $this->db->from('loan_contract_disburse lcd');
            $this->db->join('loan_contract lc', 'lc.LID = lcd.LID AND lc.PIN = lcd.PIN');
            $this->db->where('lcd.LID', $LID);
            $this->db->where('lcd.PIN', $pin);
            // Use a single AND (...) — CI2 group_start/or_where can become
            // "LID = X AND … OR release_status = paid" and pull other loans' paid releases.
            if ($this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'release_status'")->row()) {
                $this->db->where('(lcd.release_status IS NULL OR lcd.release_status = \'paid\')', null, false);
            }
            $this->db->order_by('lcd.disbursedate', 'ASC');
            $this->db->order_by('lcd.id', 'ASC');
            $disburse = $this->db->get()->result();
            foreach ($disburse as $d) {
                $desc = lang('loan_ledger_disbursement');
                if (!empty($d->comment) && stripos($d->comment, 'Beginning Balance') !== FALSE) {
                    $desc = lang('loan_ledger_beginning_balance');
                }
                $rows[] = (object)array(
                    'date' => $d->date,
                    'description' => $desc,
                    'debit' => 0,
                    'credit' => isset($d->basic_amount) ? floatval($d->basic_amount) : 0,
                    'type' => 'disbursement',
                    'schedule_installment' => null,
                    'duedate' => null,
                    'interest' => null,
                    'penalt' => null,
                    'amount_paid' => null
                );
            }
        }

        // Repayment rows from loan_contract_repayment (with full detail: schedule, interest, penalty, amount)
        $this->db->select('paydate as date, installment as schedule_installment, duedate, interest, penalt, amount, receipt');
        $this->db->where('LID', $LID);
        $this->db->where('PIN', $pin);
        if ($this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row()) {
            $this->db->where('(is_voided IS NULL OR is_voided = 0)', null, false);
        }
        $this->db->order_by('paydate', 'ASC');
        $repays = $this->db->get('loan_contract_repayment')->result();
        foreach ($repays as $r) {
            $amount = isset($r->amount) ? floatval($r->amount) : 0;
            $rows[] = (object)array(
                'date' => $r->date,
                'description' => lang('loan_ledger_repayment') . ' #' . (isset($r->schedule_installment) ? $r->schedule_installment : ''),
                'debit' => $amount,
                'credit' => 0,
                'type' => 'repayment',
                'schedule_installment' => isset($r->schedule_installment) ? $r->schedule_installment : null,
                'duedate' => isset($r->duedate) ? $r->duedate : null,
                'interest' => isset($r->interest) ? floatval($r->interest) : 0,
                'penalt' => isset($r->penalt) ? floatval($r->penalt) : 0,
                'amount_paid' => $amount,
                'receipt' => isset($r->receipt) ? $r->receipt : null,
            );
        }

        // Sort all by date
        usort($rows, function ($a, $b) {
            return strcmp($a->date, $b->date);
        });

        return $rows;
    }

    // Loan Beginning Balances Methods
    /**
     * Find a beginning-balance row by its displayed loan_id (not yet activated as loan_contract).
     */
    function get_beginning_balance_by_loan_id($loan_id) {
        if ($loan_id === null || $loan_id === '') {
            return null;
        }
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('loan_id', $loan_id);
        $this->db->limit(1);
        return $this->db->get('loan_beginning_balances')->row();
    }

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
        $id = (int) $id;
        // Confirm row exists for this PIN (CI update() can return TRUE with 0 matches).
        $exists = $this->db->where('id', $id)->where('PIN', $pin)->count_all_results('loan_beginning_balances');
        if ($exists < 1) {
            return false;
        }
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

    /**
     * Post loan beginning balance to GL (product receivable Drs) and offset
     * matching Finance chart beginning_balances when present.
     *
     * @return array{success:bool,message:string}
     */
    function loan_beginning_balance_post_to_ledger($id) {
        $pin = current_user()->PIN;
        $balance = $this->loan_beginning_balance_list(null, $id)->row();
        
        if (!$balance) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_not_found'));
        }
        if ($balance->posted == 1) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_already_posted'));
        }
        
        // Get fiscal year info
        $fiscal_year = $this->db->where('id', $balance->fiscal_year_id)->get('fiscal_year')->row();
        if (!$fiscal_year) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
        }
        
        // Get loan product info
        $product = $this->db->where('id', $balance->loan_product_id)->where('PIN', $pin)->get('loan_product')->row();
        if (!$product) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_product_not_found'));
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
            return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
        }
        
        // Use LAST_INSERT_ID() as fallback if needed
        if (!$ledger_entry_id || $ledger_entry_id == 0) {
            $last_id_result = $this->db->query("SELECT LAST_INSERT_ID() as id")->row();
            if ($last_id_result && $last_id_result->id > 0) {
                $ledger_entry_id = $last_id_result->id;
            } else {
                log_message('error', 'Failed to get ledger_entry_id for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
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
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
            }
            
            $insert_result = $this->db->insert('general_ledger', $ledger);
            $insert_affected = $this->db->affected_rows();
            
            if (!$insert_result || $insert_affected != 1) {
                log_message('error', 'Failed to insert principal ledger entry for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
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
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
            }
            
            $insert_result = $this->db->insert('general_ledger', $ledger);
            $insert_affected = $this->db->affected_rows();
            
            if (!$insert_result || $insert_affected != 1) {
                log_message('error', 'Failed to insert interest ledger entry for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
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
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
            }
            
            $insert_result = $this->db->insert('general_ledger', $ledger);
            $insert_affected = $this->db->affected_rows();
            
            if (!$insert_result || $insert_affected != 1) {
                log_message('error', 'Failed to insert penalty ledger entry for loan beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
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
            return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
        }
        
        // Check transaction status before chart BB offset / posted update
        if ($this->db->_trans_status === FALSE) {
            log_message('error', 'Transaction status is FALSE before updating posted status for loan beginning balance ID: ' . $id);
            $this->db->trans_complete();
            return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
        }

        // Offset Finance chart beginning balances (same product GLs) when present
        $this->load->model('finance_model');
        $offset = $this->finance_model->deduct_chart_bb_for_loan_bb($balance, $product);
        if (empty($offset['success'])) {
            log_message('error', 'Loan BB #' . $id . ' chart BB offset failed: ' . (!empty($offset['message']) ? $offset['message'] : ''));
            $offset_message = lang('loan_beginning_balance_chart_bb_offset_fail');
            if (!empty($offset['code']) && $offset['code'] === 'insufficient') {
                $offset_message = sprintf(
                    lang('loan_beginning_balance_chart_bb_insufficient'),
                    $offset['account'],
                    number_format($offset['available'], 2),
                    number_format($offset['required'], 2)
                );
            } elseif (!empty($offset['message'])) {
                $offset_message = $offset['message'];
            }
            // Force rollback of receivable Drs already inserted in this transaction
            $this->db->_trans_status = FALSE;
            $this->db->trans_complete();
            return array('success' => false, 'message' => $offset_message);
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
            return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
        }
        
        $this->db->trans_complete();
        
        $transaction_status = $this->db->trans_status();
        
        if ($transaction_status === FALSE) {
            log_message('error', 'Loan beginning balance post to ledger failed - transaction rolled back for ID: ' . $id);
            return array('success' => false, 'message' => lang('loan_beginning_balance_post_fail'));
        }
        
        log_message('info', 'Loan beginning balance ID ' . $id . ' posted to general ledger successfully with ' . $ledger_items_inserted . ' ledger entries'
            . (!empty($offset['offsets']) ? (' and ' . $offset['offsets'] . ' chart BB offset(s)') : ''));
        
        return array('success' => true, 'message' => lang('loan_beginning_balance_post_success'));
    }

    /**
     * Whether a beginning balance has already been activated into a loan_contract.
     */
    function is_loan_beginning_balance_activated($balance) {
        if (!$balance || empty($balance->loan_id)) {
            return false;
        }
        return $this->is_loan_exist($balance->loan_id);
    }

    /**
     * Activate (promote) a loan beginning balance into Loan Management:
     * creates loan_contract (Accepted + disbursed), synthetic disburse row,
     * remaining repayment schedule, and links loan_beginning_balances.loan_id.
     * Does NOT post disbursement GL (opening receivable is already from BB post).
     *
     * @return array{success:bool,message:string,LID?:string}
     */
    function activate_loan_beginning_balance($id) {
        $pin = current_user()->PIN;
        $id = (int) $id;
        $balance = $this->loan_beginning_balance_list(null, $id)->row();

        if (!$balance) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_not_found'));
        }
        if (empty($balance->posted)) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_must_post'));
        }
        if ($this->is_loan_beginning_balance_activated($balance)) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_already_activated'), 'LID' => $balance->loan_id);
        }

        $principal = floatval($balance->principal_balance);
        if ($principal <= 0) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_no_principal'));
        }

        $this->load->model('member_model');
        $this->load->model('setting_model');
        $CI = &get_instance();
        $CI->load->library('loanbase');

        $member = $this->member_model->member_basic_info(null, null, $balance->member_id)->row();
        if (!$member) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_member_not_found'));
        }

        $product = $this->setting_model->loanproduct($balance->loan_product_id)->row();
        if (!$product) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_product_not_found'));
        }

        $interest_method = ((int) $product->interest_method === 1 || (int) $product->interest_method === 2)
            ? (int) $product->interest_method : 1;
        $interval = !empty($product->interval) ? (int) $product->interval : 1;
        if ($interval !== 1 && $interval !== 2) {
            $interval = 1;
        }
        $rate = floatval($product->interest_rate);

        $term = !empty($balance->term) ? (int) $balance->term : 0;
        $monthly_amort = !empty($balance->monthly_amort) ? floatval($balance->monthly_amort) : 0;

        if ($term < 1 && $monthly_amort > 0) {
            // Approximate remaining installments from principal / amort (min 1)
            $term = max(1, (int) ceil($principal / $monthly_amort));
        }
        if ($term < 1) {
            $term = !empty($product->maxmum_time) ? (int) $product->maxmum_time : 12;
        }
        if ($monthly_amort <= 0) {
            $monthly_amort = $CI->loanbase->get_installment($rate, $principal, $term, $interest_method, $interval);
        }
        if ($term < 1 || $monthly_amort <= 0) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_incomplete_terms'));
        }

        // Resolve LID: use BB loan_id if free, otherwise auto-generate
        $LID = trim((string) $balance->loan_id);
        if ($LID !== '') {
            if ($this->is_loan_exist($LID)) {
                return array('success' => false, 'message' => lang('loan_beginning_balance_already_activated'), 'LID' => $LID);
            }
        } else {
            $LID = $this->get_next_ln_number();
            // Reserve the auto_inc number
            if (preg_match('/^LN(\d+)$/', $LID, $m)) {
                $num = (int) $m[1];
                $current = $this->db->get('auto_inc')->row();
                if ($current && $num >= (int) $current->loan) {
                    $this->db->set('loan', $num + 1, FALSE);
                    $this->db->update('auto_inc');
                }
            }
        }

        $interest_balance = floatval($balance->interest_balance);
        $total_interest = $CI->loanbase->totalInterest($rate, $principal, $term, $monthly_amort, $interest_method, $interval);
        if ($total_interest <= 0 && $interest_balance > 0) {
            $total_interest = $interest_balance;
        }

        // Application / opening date must come from BB disbursement_date (never "today").
        // Without it, activate would stamp wrong dates on the loan and schedule.
        if (empty($balance->disbursement_date) || strtotime($balance->disbursement_date) === FALSE) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_need_disbursement_date'));
        }
        $disburse_date = $balance->disbursement_date;

        // First installment due date
        if (!empty($balance->last_date_paid) && strtotime($balance->last_date_paid) !== FALSE) {
            $startdate = date('Y-m-d', strtotime($balance->last_date_paid . ($interval == 2 ? ' +7 days' : ' +1 month')));
        } else {
            $startdate = date('Y-m-d', strtotime($disburse_date . ($interval == 2 ? ' +7 days' : ' +1 month')));
        }

        $purpose = trim((string) $balance->description);
        if ($purpose === '') {
            $purpose = 'Beginning Balance - Migrated Loan';
        }

        $contract = array(
            'LID' => $LID,
            'PID' => $member->PID,
            'member_id' => $balance->member_id,
            'product_type' => $balance->loan_product_id,
            'rate' => $rate,
            'interval' => $interval,
            'basic_amount' => $principal,
            'number_istallment' => $term,
            'pay_source' => 'CASH',
            'applicationdate' => $disburse_date,
            'monthly_income' => 0,
            'loan_purpose' => $purpose,
            'installment_amount' => $monthly_amort,
            'total_interest_amount' => $total_interest,
            'total_loan' => ($principal + $total_interest),
            'createdby' => current_user()->id,
            'edit' => 1,
            'status' => 4, // Accepted
            'evaluated' => 'BEGINNING_BALANCE',
            'approval' => 1,
            'disburse' => 1,
            'PIN' => $pin,
        );

        $schedule = $CI->loanbase->create_repayment_schedule(
            $monthly_amort, $rate, $term, $startdate, $principal, $LID, $interest_method, $interval
        );
        if (empty($schedule)) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_fail'));
        }
        foreach ($schedule as $key => $row) {
            $schedule[$key]['status'] = 0;
            $schedule[$key]['sms_sent'] = 0;
        }

        $disburse_row = array(
            'LID' => $LID,
            'disbursedate' => $disburse_date,
            'comment' => 'Beginning Balance - Opening (no cash disbursement)',
            'createdby' => current_user()->id,
            'PIN' => $pin,
        );
        if ($this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'disburse_no'")->row()) {
            $disburse_row['disburse_no'] = 'BB-' . $id;
        }
        if ($this->db->query("SHOW COLUMNS FROM loan_contract_disburse LIKE 'payment_method'")->row()) {
            $disburse_row['payment_method'] = 'BEGINNING_BALANCE';
        }

        $this->db->trans_start();

        if (!$this->db->insert('loan_contract', $contract)) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_fail'));
        }

        if (!$this->db->insert('loan_contract_disburse', $disburse_row)) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_fail'));
        }

        if (!$this->db->insert_batch('loan_contract_repayment_schedule', $schedule)) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_fail'));
        }

        $bb_update = array(
            'loan_id' => $LID,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        if (!$this->db->update('loan_beginning_balances', $bb_update)) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_fail'));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => lang('loan_beginning_balance_activate_fail'));
        }

        return array(
            'success' => true,
            'message' => sprintf(lang('loan_beginning_balance_activate_success'), $LID),
            'LID' => $LID,
        );
    }

    /**
     * Void a posted loan beginning balance with reversing GL entry.
     */
    function void_loan_beginning_balance($id, $reason = '') {
        $pin = current_user()->PIN;
        $id = (int) $id;
        $balance = $this->loan_beginning_balance_list(null, $id)->row();
        if (!$balance || empty($balance->posted)) {
            return array('success' => false, 'message' => 'Loan beginning balance not found or not posted.');
        }
        if ($this->is_loan_beginning_balance_activated($balance)) {
            return array(
                'success' => false,
                'message' => lang('loan_beginning_balance_cannot_void_activated'),
            );
        }
        $this->load->model('finance_model');
        $this->db->trans_start();
        // Use original BB GL date so FY reports net to zero (void dated "today" leaves opening balance in prior FY).
        $gl = $this->finance_model->void_gl_lines_with_reversal(
            'loan_beginning_balances',
            $id,
            $reason !== '' ? $reason : 'Void loan beginning balance',
            array('use_source_date' => true)
        );
        if (empty($gl['success'])) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => !empty($gl['message']) ? $gl['message'] : 'GL reverse failed.');
        }
        $restore = $this->finance_model->restore_chart_bb_for_loan_bb($id);
        if (empty($restore['success'])) {
            $this->db->_trans_status = FALSE;
            $this->db->trans_complete();
            return array(
                'success' => false,
                'message' => !empty($restore['message']) ? $restore['message'] : 'Failed to restore chart beginning balance offsets.',
            );
        }
        $this->db->where('id', $id)->where('PIN', $pin)->update('loan_beginning_balances', array(
            'posted' => 0,
            'posted_date' => null,
            'posted_by' => null,
        ));
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Void failed.');
        }
        return array('success' => true, 'message' => 'Loan beginning balance voided with reversing GL entry.');
    }

    /**
     * Resolve general_ledger.refferenceID for a loan_contract_repayment row.
     * Correct posts use repayment.id; older/polluted posts used a wrong insert_id
     * (often general_ledger_entry.id). Fall back by LID + pay date + description.
     */
    function resolve_repayment_gl_reference_id($row) {
        $pin = current_user()->PIN;
        $row_id = isset($row->id) ? (int) $row->id : 0;
        if ($row_id > 0) {
            $hit = $this->db->query(
                "SELECT id FROM general_ledger
                 WHERE PIN = ? AND fromtable = 'loan_contract_repayment' AND refferenceID = ?
                 LIMIT 1",
                array($pin, $row_id)
            )->row();
            if ($hit) {
                return $row_id;
            }
        }

        $lid = isset($row->LID) ? $row->LID : '';
        $paydate = isset($row->paydate) ? $row->paydate : '';
        if ($lid === '' || $paydate === '') {
            return $row_id > 0 ? $row_id : '';
        }

        $refs = $this->db->query(
            "SELECT DISTINCT refferenceID
             FROM general_ledger
             WHERE PIN = ?
               AND fromtable = 'loan_contract_repayment'
               AND LID = ?
               AND date = ?
               AND description = 'Loan Repayment'
               AND (refferenceID IS NOT NULL AND refferenceID != '' AND refferenceID != '0')
             ORDER BY refferenceID ASC",
            array($pin, $lid, $paydate)
        )->result();

        if (count($refs) === 1) {
            return $refs[0]->refferenceID;
        }

        // Multiple same-day repayments: prefer ref whose debit total matches this row amount.
        $target = isset($row->amount) ? round(floatval($row->amount), 2) : 0;
        if ($target > 0 && !empty($refs)) {
            foreach ($refs as $ref) {
                $sum = $this->db->query(
                    "SELECT ROUND(COALESCE(SUM(debit),0), 2) AS total_debit
                     FROM general_ledger
                     WHERE PIN = ? AND fromtable = 'loan_contract_repayment'
                       AND refferenceID = ? AND LID = ? AND date = ?",
                    array($pin, $ref->refferenceID, $lid, $paydate)
                )->row();
                if ($sum && abs(floatval($sum->total_debit) - $target) < 0.02) {
                    return $ref->refferenceID;
                }
            }
        }

        if (!empty($refs)) {
            return $refs[0]->refferenceID;
        }
        return $row_id > 0 ? $row_id : '';
    }

    /**
     * Reliably get loan_contract_repayment.id after insert (insert_id can be polluted
     * by activity_logs / other inserts on the same connection).
     */
    function _loan_repayment_insert_id($array_data) {
        $pin = current_user()->PIN;
        $insert_id = (int) $this->db->insert_id();
        if ($insert_id > 0) {
            $check = $this->db->query(
                "SELECT id FROM loan_contract_repayment WHERE id = ? AND PIN = ? AND LID = ? LIMIT 1",
                array($insert_id, $pin, isset($array_data['LID']) ? $array_data['LID'] : '')
            )->row();
            if ($check) {
                return $insert_id;
            }
        }
        $found = $this->db->query(
            "SELECT id FROM loan_contract_repayment
             WHERE PIN = ? AND LID = ? AND receipt = ?
             ORDER BY id DESC LIMIT 1",
            array(
                $pin,
                isset($array_data['LID']) ? $array_data['LID'] : '',
                isset($array_data['receipt']) ? $array_data['receipt'] : '',
            )
        )->row();
        return $found ? (int) $found->id : $insert_id;
    }

    /**
     * Whether a disbursed loan was created via Beginning Balance Activate (no cash GL).
     */
    function is_beginning_balance_activated_loan($loan, $release = null) {
        if ($loan && isset($loan->evaluated) && (string) $loan->evaluated === 'BEGINNING_BALANCE') {
            return true;
        }
        if ($release) {
            if (isset($release->payment_method) && (string) $release->payment_method === 'BEGINNING_BALANCE') {
                return true;
            }
            if (!empty($release->comment) && stripos($release->comment, 'Beginning Balance - Opening') !== false) {
                return true;
            }
            if (!empty($release->disburse_no) && strpos((string) $release->disburse_no, 'BB-') === 0) {
                return true;
            }
        }
        if ($loan && !empty($loan->LID)) {
            $bb = $this->get_beginning_balance_by_loan_id($loan->LID);
            if ($bb && !empty($bb->posted) && $this->is_loan_beginning_balance_activated($bb)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Undo Activate as Loan: remove contract/schedule/disburse, clear BB.loan_id.
     * Does not reverse BB opening GL (row stays Posted so it can be re-activated
     * after correcting dates/terms). Use BB list Void to reverse GL if needed.
     *
     * @return array{success:bool,message:string,fiscal_year_id?:int}
     */
    function deactivate_loan_beginning_balance_activation($LID, $reason = '') {
        $pin = current_user()->PIN;
        $LID = trim((string) $LID);
        $reason = trim((string) $reason);
        if ($reason === '') {
            $reason = 'Undo beginning balance activation';
        }

        $loan = $this->db->where('LID', $LID)->where('PIN', $pin)->get('loan_contract')->row();
        if (!$loan || empty($loan->disburse)) {
            return array('success' => false, 'message' => 'Loan not found or not disbursed.');
        }

        $release = $this->db->where('LID', $LID)->where('PIN', $pin)
            ->order_by('id', 'DESC')->limit(1)
            ->get('loan_contract_disburse')->row();
        if (!$this->is_beginning_balance_activated_loan($loan, $release)) {
            return array('success' => false, 'message' => 'This loan is not a beginning-balance activation.');
        }

        $has_voided_col = $this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row();
        if ($has_voided_col) {
            $paid = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM loan_contract_repayment WHERE LID = ? AND PIN = ? AND (is_voided IS NULL OR is_voided = 0)",
                array($LID, $pin)
            )->row();
        } else {
            $paid = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM loan_contract_repayment WHERE LID = ? AND PIN = ?",
                array($LID, $pin)
            )->row();
        }
        if ($paid && intval($paid->cnt) > 0) {
            return array('success' => false, 'message' => 'Cannot undo activation while repayments exist. Void repayments first.');
        }

        $bb = $this->get_beginning_balance_by_loan_id($LID);
        $bb_needs_link = false;
        if (!$bb && $loan) {
            $this->db->where('PIN', $pin);
            $this->db->where('member_id', $loan->member_id);
            $this->db->where('loan_product_id', $loan->product_type);
            $this->db->where('posted', 1);
            $this->db->limit(1);
            $bb = $this->db->get('loan_beginning_balances')->row();
            $bb_needs_link = (bool) $bb;
        }
        $fiscal_year_id = $bb ? (int) $bb->fiscal_year_id : null;

        $this->db->trans_start();

        $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract_repayment_schedule');
        if ($this->db->table_exists('loan_disbursement_gl_items')) {
            $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_disbursement_gl_items');
        }
        $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract_disburse');
        if ($this->db->table_exists('loan_contract_evaluation')) {
            $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract_evaluation');
        }
        if ($this->db->table_exists('loan_contract_approve')) {
            $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract_approve');
        }
        $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract');

        // Keep/restore BB.loan_id so re-activate can reuse the same LID. Not-activated = loan_contract gone.
        if ($bb) {
            $bb_upd = array('updated_at' => date('Y-m-d H:i:s'));
            if ($bb_needs_link || empty($bb->loan_id)) {
                $bb_upd['loan_id'] = $LID;
            }
            $this->db->where('id', (int) $bb->id)->where('PIN', $pin)->update('loan_beginning_balances', $bb_upd);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Failed to undo beginning balance activation.');
        }

        return array(
            'success' => true,
            'message' => lang('loan_beginning_balance_deactivate_success'),
            'fiscal_year_id' => $fiscal_year_id,
            'bb_id' => $bb ? (int) $bb->id : null,
        );
    }

    /**
     * Void a loan repayment receipt: reverse GL for each repayment row and reopen schedule(s).
     */
    function void_loan_repayment_receipt($receipt, $reason = '', $options = array()) {
        $pin = current_user()->PIN;
        $receipt = trim((string) $receipt);
        $reason = trim((string) $reason);
        if (!is_array($options)) {
            $options = array();
        }
        $reverse_gl = !array_key_exists('reverse_gl', $options) || !empty($options['reverse_gl']);
        $from_cash_receipt_void = !empty($options['from_cash_receipt_void']);
        if ($receipt === '') {
            return array('success' => false, 'message' => 'Invalid receipt.');
        }

        $this->load->model('cash_receipt_model');
        $linked_cr = $this->cash_receipt_model->get_cash_receipt_by_loan_repayment_receipt($receipt);
        if ($linked_cr && !$from_cash_receipt_void) {
            return array(
                'success' => false,
                'message' => 'This repayment was posted from Cash Receipt. Void it from Journal Entry Review so the cash voucher and loan schedule reverse together.',
            );
        }

        // Ensure void column
        if (!$this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row()) {
            $this->db->query("ALTER TABLE loan_contract_repayment ADD COLUMN is_voided TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!$this->db->query("SHOW COLUMNS FROM loan_repayment_receipt LIKE 'is_voided'")->row()) {
            $this->db->query("ALTER TABLE loan_repayment_receipt ADD COLUMN is_voided TINYINT(1) NOT NULL DEFAULT 0");
        }

        $rcpt = $this->db->where('receipt', $receipt)->get('loan_repayment_receipt')->row();
        if ($rcpt && !empty($rcpt->is_voided)) {
            if ($from_cash_receipt_void) {
                return array('success' => true, 'message' => 'This repayment receipt is already voided.', 'LID' => isset($rcpt->LID) ? $rcpt->LID : null);
            }
            return array('success' => false, 'message' => 'This repayment receipt is already voided.');
        }

        $rows = $this->db->where('receipt', $receipt)->where('(is_voided IS NULL OR is_voided = 0)', null, false)->get('loan_contract_repayment')->result();
        if (empty($rows)) {
            // legacy rows without is_voided filter
            $rows = $this->db->where('receipt', $receipt)->get('loan_contract_repayment')->result();
        }
        if (empty($rows)) {
            return array('success' => false, 'message' => 'No repayment lines found for this receipt.');
        }

        $this->load->model('finance_model');
        $this->db->trans_start();
        $LID = null;
        foreach ($rows as $row) {
            if (!empty($row->is_voided)) {
                continue;
            }
            $LID = $row->LID;
            if ($reverse_gl) {
                $gl_ref = $this->resolve_repayment_gl_reference_id($row);
                $gl = $this->finance_model->void_gl_lines_with_reversal(
                    'loan_contract_repayment',
                    $gl_ref,
                    $reason !== '' ? $reason : ('Void repayment ' . $receipt)
                );
                if (empty($gl['success']) && (string) $gl_ref !== (string) $row->id) {
                    // Retry with repayment row id in case some installs stored it correctly.
                    $gl = $this->finance_model->void_gl_lines_with_reversal(
                        'loan_contract_repayment',
                        $row->id,
                        $reason !== '' ? $reason : ('Void repayment ' . $receipt)
                    );
                }
                if (empty($gl['success'])) {
                    $no_lines = !empty($gl['message']) && stripos($gl['message'], 'No GL lines found') !== false;
                    if (!$no_lines) {
                        $this->db->_trans_status = FALSE;
                        $this->db->trans_complete();
                        return array('success' => false, 'message' => !empty($gl['message']) ? $gl['message'] : ('GL reverse failed for repayment #' . $row->id));
                    }
                }
            }
            $this->db->where('id', $row->id)->update('loan_contract_repayment', array('is_voided' => 1));
            // Reopen matching schedule installment
            if (!empty($row->installment)) {
                $this->db->where('LID', $row->LID);
                $this->db->where('installment_number', $row->installment);
                $this->db->where_in('status', array(1, 2));
                $this->db->update('loan_contract_repayment_schedule', array('status' => 0));
            }
        }
        if ($LID) {
            // Reopen any status=2 leftovers if no active (non-void) repayments remain after this void for later installments
            $active = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM loan_contract_repayment WHERE LID = ? AND (is_voided IS NULL OR is_voided = 0)",
                array($LID)
            )->row();
            if ($active && intval($active->cnt) === 0) {
                $this->db->where('LID', $LID)->where('status', 2)->update('loan_contract_repayment_schedule', array('status' => 0));
            }
            // If loan was marked completed (5), reopen to released (4)
            $loan = $this->db->where('LID', $LID)->get('loan_contract')->row();
            if ($loan && isset($loan->status) && intval($loan->status) === 5) {
                $this->db->where('LID', $LID)->update('loan_contract', array('status' => 4));
            }
        }
        $this->db->where('receipt', $receipt)->update('loan_repayment_receipt', array('is_voided' => 1, 'affect_loan' => 0));
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Void failed.');
        }
        return array('success' => true, 'message' => 'Loan repayment voided with reversing GL entry.', 'LID' => $LID);
    }

    /**
     * Void loan disbursement GL (by LID) when no repayments exist.
     * Also reverses Savings/Share sub-ledgers created by loan disbursement deductions
     * (without a second GL post — loan GL reversal already covers 21110/30130).
     * Old-style posts only (loan_contract / "Loan Disbursed"). New-flow paid/draft
     * releases linked to Cash Disbursement are blocked.
     */
    function void_loan_disbursement($LID, $reason = '') {
        $pin = current_user()->PIN;
        $LID = trim((string) $LID);
        $reason = trim((string) $reason);
        if ($reason === '') {
            $reason = 'Void loan disbursement';
        }
        $loan = $this->db->where('LID', $LID)->where('PIN', $pin)->get('loan_contract')->row();
        if (!$loan || empty($loan->disburse)) {
            return array('success' => false, 'message' => 'Loan not found or not disbursed.');
        }

        $this->ensure_release_workflow_columns();
        $release = $this->db->where('LID', $LID)->where('PIN', $pin)
            ->order_by('id', 'DESC')->limit(1)
            ->get('loan_contract_disburse')->row();
        if ($release && isset($release->release_status)
            && in_array($release->release_status, array('draft', 'paid'), true)) {
            return array(
                'success' => false,
                'message' => 'This loan was paid through Cash Disbursement / Journal Entry Review. Automatic void is not available; create a manual reversing journal instead.',
            );
        }

        $has_voided_col = $this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row();
        if ($has_voided_col) {
            $paid = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM loan_contract_repayment WHERE LID = ? AND PIN = ? AND (is_voided IS NULL OR is_voided = 0)",
                array($LID, $pin)
            )->row();
        } else {
            $paid = $this->db->query(
                "SELECT COUNT(*) AS cnt FROM loan_contract_repayment WHERE LID = ? AND PIN = ?",
                array($LID, $pin)
            )->row();
        }
        if ($paid && intval($paid->cnt) > 0) {
            return array('success' => false, 'message' => 'Cannot void disbursement while repayments exist. Void repayments first.');
        }

        // Beginning-balance activate posts no cash "Loan Disbursed" GL — undo activation instead.
        if ($this->is_beginning_balance_activated_loan($loan, $release)) {
            return $this->deactivate_loan_beginning_balance_activation($LID, $reason);
        }

        $this->load->model('finance_model');
        $this->load->model('share_model');
        $this->db->trans_start();

        $savings_rev = $this->_reverse_disbursement_savings_subledgers($LID, $reason);
        if (empty($savings_rev['success'])) {
            $this->db->trans_rollback();
            return $savings_rev;
        }

        $share_rev = $this->_reverse_disbursement_share_subledgers($LID, $reason);
        if (empty($share_rev['success'])) {
            $this->db->trans_rollback();
            return $share_rev;
        }

        // Disbursement GL often keyed by LID without refferenceID — filter by LID + description
        $gl = $this->finance_model->void_gl_lines_with_reversal('loan_contract', $LID, $reason, array(
            'LID' => $LID,
            'description' => 'Loan Disbursed',
            'ignore_refferenceID' => 1,
        ));
        if (empty($gl['success'])) {
            // Try with refferenceID = LID if that was stored
            $gl = $this->finance_model->void_gl_lines_with_reversal('loan_contract', $LID, $reason);
        }
        if (empty($gl['success'])) {
            $this->db->trans_rollback();
            return array('success' => false, 'message' => !empty($gl['message']) ? $gl['message'] : 'No disbursement GL found to reverse.');
        }

        $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract_repayment_schedule');
        if ($this->db->table_exists('loan_disbursement_gl_items')) {
            $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_disbursement_gl_items');
        }
        $this->db->where('LID', $LID)->where('PIN', $pin)->delete('loan_contract_disburse');
        $upd = array('disburse' => 0);
        if ($this->db->query("SHOW COLUMNS FROM loan_contract LIKE 'offset_loans'")->row()) {
            $upd['offset_loans'] = null;
        }
        $this->db->where('LID', $LID)->where('PIN', $pin)->update('loan_contract', $upd);

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Void failed.');
        }

        $extra = array();
        if (!empty($savings_rev['count'])) {
            $extra[] = $savings_rev['count'] . ' savings';
        }
        if (!empty($share_rev['count'])) {
            $extra[] = $share_rev['count'] . ' share';
        }
        $msg = 'Loan disbursement voided with reversing GL entry.';
        if (!empty($extra)) {
            $msg .= ' Also reversed ' . implode(' and ', $extra) . ' deduction sub-ledger(s).';
        }
        $msg .= ' Loan is ready for Loan Release → Cash Disbursement.';
        return array('success' => true, 'message' => $msg);
    }

    /**
     * Reverse savings credits posted as loan disbursement deductions (no second GL).
     */
    function _reverse_disbursement_savings_subledgers($LID, $reason = '') {
        $pin = current_user()->PIN;
        $this->load->model('finance_model');
        $comment = 'Loan Disbursement ' . $LID;
        $rows = $this->db->where('PIN', $pin)
            ->where('comment', $comment)
            ->where('system_comment', 'LOAN DISBURSEMENT DEDUCTION')
            ->where('trans_type', 'CR')
            ->order_by('id', 'ASC')
            ->get('savings_transaction')
            ->result();
        $count = 0;
        foreach ($rows as $trans) {
            if ($this->finance_model->is_savings_transaction_voided($trans->receipt)) {
                continue;
            }
            $acct = $this->finance_model->saving_account_balance($trans->account);
            if (!$acct) {
                return array(
                    'success' => false,
                    'message' => 'Savings account for disbursement deduction not found (receipt ' . $trans->receipt . ').',
                );
            }
            if (floatval($acct->balance) + 0.009 < floatval($trans->amount)) {
                return array(
                    'success' => false,
                    'message' => 'Cannot reverse Savings deduction: account balance is less than the disbursement credit of '
                        . number_format(floatval($trans->amount), 2) . '. Adjust savings first.',
                );
            }
            // System comment must not trigger NORMAL WITHDRAWAL GL (loan GL reverse already covers 21110).
            $void_receipt = $this->finance_model->debit(
                $trans->account,
                floatval($trans->amount),
                !empty($trans->paymethod) ? $trans->paymethod : 'Cash',
                'VOID-' . $trans->receipt . ' - ' . $reason,
                isset($trans->cheque_num) ? $trans->cheque_num : '',
                isset($trans->customer_name) ? $trans->customer_name : '',
                'LOAN DISBURSEMENT VOID',
                $trans->PID,
                date('Y-m-d'),
                $LID
            );
            if (!$void_receipt) {
                return array(
                    'success' => false,
                    'message' => 'Failed to reverse savings deduction receipt ' . $trans->receipt . '.',
                );
            }
            $count++;
        }
        return array('success' => true, 'count' => $count);
    }

    /**
     * Reverse share buys posted as loan disbursement deductions.
     */
    function _reverse_disbursement_share_subledgers($LID, $reason = '') {
        $pin = current_user()->PIN;
        $this->load->model('share_model');
        $comment = 'Loan Disbursement ' . $LID;
        $rows = $this->db->where('PIN', $pin)
            ->where('comment', $comment)
            ->where('trans_type', 'CR')
            ->order_by('id', 'DESC')
            ->get('share_transaction')
            ->result();
        $count = 0;
        foreach ($rows as $trans) {
            if (method_exists($this->share_model, 'is_share_transaction_voided')
                && $this->share_model->is_share_transaction_voided($trans->receipt)) {
                continue;
            }
            if (method_exists($this->share_model, 'is_void_entry')
                && $this->share_model->is_void_entry($trans)) {
                continue;
            }
            $result = $this->share_model->void_share_transaction($trans->receipt, $reason);
            if (empty($result['success'])) {
                return array(
                    'success' => false,
                    'message' => !empty($result['message'])
                        ? ('Share deduction reverse failed: ' . $result['message'])
                        : ('Failed to reverse share receipt ' . $trans->receipt),
                );
            }
            $count++;
        }
        return array('success' => true, 'count' => $count);
    }

    /**
     * Void loan processing fee GL.
     */
    function void_loan_processing_fee($fee_id, $reason = '') {
        $pin = current_user()->PIN;
        $fee_id = (int) $fee_id;
        $fee = $this->db->where('id', $fee_id)->where('PIN', $pin)->get('loanprocessing_fee')->row();
        if (!$fee) {
            return array('success' => false, 'message' => 'Processing fee not found.');
        }
        if (!$this->db->query("SHOW COLUMNS FROM loanprocessing_fee LIKE 'is_voided'")->row()) {
            $this->db->query("ALTER TABLE loanprocessing_fee ADD COLUMN is_voided TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!empty($fee->is_voided)) {
            return array('success' => false, 'message' => 'Processing fee already voided.');
        }
        $this->load->model('finance_model');
        $this->db->trans_start();
        $gl = $this->finance_model->void_gl_lines_with_reversal('loanprocessing_fee', $fee_id, $reason !== '' ? $reason : 'Void loan processing fee');
        if (empty($gl['success'])) {
            $this->db->trans_complete();
            return array('success' => false, 'message' => !empty($gl['message']) ? $gl['message'] : 'GL reverse failed.');
        }
        $this->db->where('id', $fee_id)->update('loanprocessing_fee', array('is_voided' => 1));
        $this->db->trans_complete();
        return array('success' => true, 'message' => 'Loan processing fee voided with reversing GL entry.');
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
