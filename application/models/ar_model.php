<?php

/**
 * Accounts Receivable (AR) Model
 * AR balances, AR ledger, and AR aging report data
 */
class Ar_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * Get the Accounts Receivable GL account code for current PIN.
     * Tries chart of accounts by name, then falls back to 1010002.
     */
    function get_ar_account() {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where("(name LIKE '%Accounts Receivable%' OR name LIKE '%Account Receivable%' OR name LIKE '%Receivable%')", null, false);
        $this->db->where_in('account_type', array(1, 10000));
        $this->db->limit(1);
        $row = $this->db->get('account_chart')->row();
        if ($row) {
            return (int) $row->account;
        }
        return 1010002;
    }

    /**
     * AR balances by customer as of a given date.
     * Sum of debit - credit from general_ledger for AR account, grouped by customerid.
     */
    function get_ar_balances($as_of_date = null) {
        $pin = current_user()->PIN;
        if (empty($as_of_date)) {
            $as_of_date = date('Y-m-d');
        } else {
            $as_of_date = date('Y-m-d', strtotime($as_of_date));
        }
        $ar_account = $this->get_ar_account();
        $ar_esc = (int) $ar_account;
        $pin_esc = $this->db->escape($pin);
        $date_esc = $this->db->escape($as_of_date);
        $sql = "SELECT 
                    gl.customerid,
                    MAX(c.name) AS customer_name,
                    MAX(c.customerid) AS customer_number,
                    SUM(COALESCE(gl.debit, 0) - COALESCE(gl.credit, 0)) AS balance
                FROM general_ledger gl
                LEFT JOIN customer c ON c.customerid = gl.customerid AND c.PIN = gl.PIN
                WHERE gl.PIN = $pin_esc AND gl.account = $ar_esc AND gl.date <= $date_esc
                GROUP BY gl.customerid
                HAVING balance != 0
                ORDER BY customer_name ASC, gl.customerid ASC";
        return $this->db->query($sql)->result();
    }

    /**
     * Total AR balance as of date (single number).
     */
    function get_ar_total_balance($as_of_date = null) {
        $pin = current_user()->PIN;
        if (empty($as_of_date)) {
            $as_of_date = date('Y-m-d');
        } else {
            $as_of_date = date('Y-m-d', strtotime($as_of_date));
        }
        $ar_account = $this->get_ar_account();
        $this->db->select('SUM(COALESCE(debit, 0) - COALESCE(credit, 0)) AS total', false);
        $this->db->where('PIN', $pin);
        $this->db->where('account', $ar_account);
        $this->db->where('date <=', $as_of_date);
        $row = $this->db->get('general_ledger')->row();
        return $row && $row->total !== null ? floatval($row->total) : 0;
    }

    /**
     * AR ledger: transactions for AR account with running balance.
     * Optional customer_id and date range.
     */
    function get_ar_ledger($customer_id = null, $date_from = null, $date_to = null) {
        $pin = current_user()->PIN;
        $ar_account = $this->get_ar_account();
        $this->db->select('gl.id, gl.date, gl.description, gl.debit, gl.credit, gl.customerid, gl.refferenceID, gl.fromtable, gl.invoiceid, ac.name AS account_name');
        $this->db->from('general_ledger gl');
        $this->db->join('account_chart ac', 'ac.account = gl.account AND ac.PIN = gl.PIN', 'left');
        $this->db->where('gl.PIN', $pin);
        $this->db->where('gl.account', $ar_account);
        if (!empty($customer_id)) {
            $this->db->where('gl.customerid', $customer_id);
        }
        if (!empty($date_from)) {
            $this->db->where('gl.date >=', date('Y-m-d', strtotime($date_from)));
        }
        if (!empty($date_to)) {
            $this->db->where('gl.date <=', date('Y-m-d', strtotime($date_to)));
        }
        $this->db->order_by('gl.date', 'ASC');
        $this->db->order_by('gl.id', 'ASC');
        $rows = $this->db->get()->result();
        $running = 0;
        foreach ($rows as $r) {
            $d = isset($r->debit) ? floatval($r->debit) : 0;
            $c = isset($r->credit) ? floatval($r->credit) : 0;
            $running += $d - $c;
            $r->running_balance = $running;
        }
        return $rows;
    }

    /**
     * AR aging report: outstanding invoices grouped by days overdue (due_date vs as_of_date).
     * Uses sales_invoice where balance > 0.
     */
    function get_ar_aging($as_of_date = null) {
        $pin = current_user()->PIN;
        if (empty($as_of_date)) {
            $as_of_date = date('Y-m-d');
        } else {
            $as_of_date = date('Y-m-d', strtotime($as_of_date));
        }
        $this->db->select('si.id, si.customerid, si.issue_date, si.due_date, si.balance, c.name AS customer_name, c.customerid AS customer_number');
        $this->db->from('sales_invoice si');
        $this->db->join('customer c', 'c.customerid = si.customerid AND c.PIN = si.PIN', 'left');
        $this->db->where('si.PIN', $pin);
        $this->db->where('si.balance >', 0);
        $this->db->order_by('si.due_date', 'ASC');
        $invoices = $this->db->get()->result();
        $as_of = new DateTime($as_of_date);
        $buckets = array(
            'current'   => array('label' => 'Current (0-30 days)',  'min' => 0,   'max' => 30,  'invoices' => array(), 'total' => 0),
            '31_60'     => array('label' => '31-60 days',          'min' => 31,  'max' => 60,  'invoices' => array(), 'total' => 0),
            '61_90'     => array('label' => '61-90 days',          'min' => 61,  'max' => 90,  'invoices' => array(), 'total' => 0),
            '91_180'    => array('label' => '91-180 days',         'min' => 91,  'max' => 180, 'invoices' => array(), 'total' => 0),
            'over_180'  => array('label' => 'Over 180 days',       'min' => 181, 'max' => 9999, 'invoices' => array(), 'total' => 0),
        );
        foreach ($invoices as $inv) {
            $balance = floatval($inv->balance);
            $due = !empty($inv->due_date) ? new DateTime($inv->due_date) : null;
            $days_overdue = 0;
            if ($due) {
                $diff = $as_of->diff($due);
                $days_overdue = $due > $as_of ? 0 : (int) $diff->days;
            }
            $bucket_key = 'current';
            if ($days_overdue > 180) {
                $bucket_key = 'over_180';
            } elseif ($days_overdue > 90) {
                $bucket_key = '91_180';
            } elseif ($days_overdue > 60) {
                $bucket_key = '61_90';
            } elseif ($days_overdue > 30) {
                $bucket_key = '31_60';
            }
            $item = array(
                'invoice_id'    => $inv->id,
                'customerid'   => $inv->customerid,
                'customer_name'=> $inv->customer_name,
                'customer_number' => isset($inv->customer_number) ? $inv->customer_number : $inv->customerid,
                'issue_date'    => $inv->issue_date,
                'due_date'      => $inv->due_date,
                'balance'       => $balance,
                'days_overdue'  => $days_overdue,
            );
            $buckets[$bucket_key]['invoices'][] = $item;
            $buckets[$bucket_key]['total'] += $balance;
        }
        return $buckets;
    }

    /**
     * Get customer list for dropdown (for AR ledger filter).
     */
    function get_customers_list() {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->order_by('name', 'ASC');
        return $this->db->get('customer')->result();
    }
}
