<?php

/**
 * Cash Disbursement Model
 * Handles database operations for cash disbursement module
 */
class Cash_disbursement_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * Get all cash disbursements
     */
    function get_cash_disbursements($id = null, $disburse_no = null, $date_from = null, $date_to = null) {
        $this->db->where('PIN', current_user()->PIN);
        
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        
        if (!is_null($disburse_no)) {
            $this->db->where('disburse_no', $disburse_no);
        }
        
        if (!empty($date_from)) {
            $this->db->where('disburse_date >=', $date_from);
        }
        
        if (!empty($date_to)) {
            $this->db->where('disburse_date <=', $date_to);
        }
        
        $this->db->order_by('disburse_date', 'DESC');
        $this->db->order_by('id', 'DESC');
        
        return $this->db->get('cash_disbursements');
    }

    /**
     * Get single cash disbursement
     */
    function get_cash_disbursement($id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('id', $id);
        
        return $this->db->get('cash_disbursements')->row();
    }

    /**
     * Get disbursement items/line items (with debit/credit like journal entry)
     */
    function get_disburse_items($disburse_id) {
        $pin = current_user()->PIN;
        $has_debit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'debit'")->row();
        $has_credit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'credit'")->row();
        if ($has_debit && $has_credit) {
            $sql = "SELECT cdi.*, COALESCE(cdi.debit, cdi.amount) as debit, COALESCE(cdi.credit, 0) as credit
                    FROM cash_disbursement_items cdi
                    WHERE cdi.disbursement_id = ?
                    ORDER BY cdi.id ASC";
        } else {
            $sql = "SELECT cdi.*, COALESCE(cdi.amount, 0) as debit, 0 as credit
                    FROM cash_disbursement_items cdi
                    WHERE cdi.disbursement_id = ?
                    ORDER BY cdi.id ASC";
        }
        $items = $this->db->query($sql, array($disburse_id))->result();
        foreach ($items as $item) {
            $row = $this->db->query(
                'SELECT name FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1',
                array($item->account, $pin)
            )->row();
            $item->account_name = $row ? $row->name : 'Unknown Account';
        }
        return $items;
    }

    /**
     * Get line items for edit form. For legacy disbursements (debits-only), prepends Cash/Bank credit to balance.
     */
    function get_line_items_for_edit($disburse_id) {
        $disburse = $this->get_cash_disbursement($disburse_id);
        if (!$disburse) {
            return array();
        }
        $line_items = $this->get_disburse_items($disburse_id);
        $total_debit = 0;
        $total_credit = 0;
        foreach ($line_items as $item) {
            $total_debit += isset($item->debit) ? floatval($item->debit) : (isset($item->amount) ? floatval($item->amount) : 0);
            $total_credit += isset($item->credit) ? floatval($item->credit) : 0;
        }
        // Legacy disbursements: add Cash/Bank credit to balance when debits exceed credits
        if ($total_debit > $total_credit && abs($total_debit - $total_credit) > 0.001) {
            $cash_account = $this->get_cash_account($disburse->payment_method);
            if ($cash_account) {
                $acct_row = $this->db->query('SELECT name FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($cash_account, current_user()->PIN))->row();
                $credit_amount = $total_debit - $total_credit;
                $cash_line = (object) array(
                    'account' => $cash_account,
                    'account_name' => $acct_row ? $acct_row->name : $cash_account,
                    'description' => 'Disbursement to: ' . (isset($disburse->paid_to) ? $disburse->paid_to : ''),
                    'debit' => 0,
                    'credit' => $credit_amount,
                    'amount' => $credit_amount
                );
                array_unshift($line_items, $cash_line);
            }
        }
        return $line_items;
    }

    /**
     * Create new cash disbursement
     */
    function create_cash_disbursement($disburse_data, $line_items) {
        // Start transaction
        $this->db->trans_start();
        
        // Insert disbursement header
        $this->db->insert('cash_disbursements', $disburse_data);
        if ($this->db->affected_rows() != 1) {
            $this->db->trans_complete();
            return false;
        }
        // Get actual insert ID by querying the last inserted record for this PIN
        // (Workaround for persistent connection stale insert_id issue - same as cash_receipt_model)
        $disburse = $this->db->where('PIN', current_user()->PIN)
                             ->order_by('id', 'DESC')
                             ->limit(1)
                             ->get('cash_disbursements')
                             ->row();
        if (!$disburse) {
            $this->db->trans_complete();
            return false;
        }
        $disburse_id = $disburse->id;
        
        // Insert disbursement items (with debit/credit)
        if ($disburse_id && !empty($line_items)) {
            foreach ($line_items as $item) {
                $row = array(
                    'disbursement_id' => $disburse_id,
                    'account' => $item['account'],
                    'description' => isset($item['description']) ? $item['description'] : '',
                    'amount' => isset($item['amount']) ? $item['amount'] : (isset($item['debit']) ? $item['debit'] : 0) + (isset($item['credit']) ? $item['credit'] : 0),
                    'PIN' => current_user()->PIN
                );
                if (isset($item['debit'])) $row['debit'] = $item['debit'];
                if (isset($item['credit'])) $row['credit'] = $item['credit'];
                $this->db->insert('cash_disbursement_items', $row);
            }
        }
        
        // Create journal entry only when not cancelled (cancelled disbursements are document references only, no GL)
        if (empty($disburse_data['cancelled'])) {
            $this->create_journal_entry($disburse_id, $disburse_data, $line_items);
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $disburse_id;
    }

    /**
     * Update existing cash disbursement (header, line items, and journal entry).
     * Changing payment method or line items replaces the accounting entry with a new one.
     */
    function update_cash_disbursement($id, $disburse_data, $line_items) {
        $id = (int) $id;
        $pin = current_user()->PIN;
        if ($id <= 0) {
            return false;
        }

        // Start transaction
        $this->db->trans_start();

        // Remove old journal entry FIRST (while header still has current disburse_no so description lookup works)
        $this->_delete_journal_entries_for_disbursement($id);

        // Update payment_method in its own query so it always saves (avoids being skipped or lost in combined UPDATE)
        $payment_val = isset($disburse_data['payment_method']) ? trim((string) $disburse_data['payment_method']) : '';
        if ($payment_val !== '') {
            $this->db->query(
                'UPDATE cash_disbursements SET payment_method = ?, updated_at = ? WHERE id = ? AND PIN = ?',
                array($payment_val, isset($disburse_data['updated_at']) ? $disburse_data['updated_at'] : date('Y-m-d H:i:s'), $id, $pin)
            );
        }

        // Update remaining disbursement header fields (payment_method already done above)
        $allowed = array('disburse_no', 'disburse_date', 'paid_to', 'cheque_no', 'bank_name', 'description', 'total_amount', 'updated_at');
        $set_parts = array();
        $params = array();
        foreach ($allowed as $col) {
            if (!array_key_exists($col, $disburse_data)) {
                continue;
            }
            $val = $disburse_data[$col];
            if (is_string($val)) {
                $val = trim($val);
            }
            $set_parts[] = '`' . $col . '` = ?';
            $params[] = $val;
        }
        if (!empty($set_parts)) {
            $params[] = $id;
            $params[] = $pin;
            $this->db->query(
                'UPDATE cash_disbursements SET ' . implode(', ', $set_parts) . ' WHERE id = ? AND PIN = ?',
                $params
            );
        }

        // Delete existing line items (direct query)
        $this->db->query('DELETE FROM cash_disbursement_items WHERE disbursement_id = ?', array($id));

        // Insert new line items (with debit/credit)
        if (!empty($line_items)) {
            foreach ($line_items as $item) {
                $row = array(
                    'disbursement_id' => $id,
                    'account' => $item['account'],
                    'description' => isset($item['description']) ? $item['description'] : '',
                    'amount' => isset($item['amount']) ? $item['amount'] : (isset($item['debit']) ? $item['debit'] : 0) + (isset($item['credit']) ? $item['credit'] : 0),
                    'PIN' => $pin
                );
                if (isset($item['debit'])) $row['debit'] = $item['debit'];
                if (isset($item['credit'])) $row['credit'] = $item['credit'];
                $this->db->insert('cash_disbursement_items', $row);
            }
        }

        // Complete transaction so disbursement + items are committed even if journal creation fails
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        // Create journal entry after commit so a journal failure does not roll back the edit
        $this->create_journal_entry($id, $disburse_data, $line_items);

        return true;
    }

    /**
     * Delete cash disbursement (header, line items, and linked journal entry).
     */
    function delete_cash_disbursement($id) {
        $id = (int) $id;
        $pin = current_user()->PIN;
        if ($id <= 0) {
            return false;
        }

        // Start transaction
        $this->db->trans_start();

        // Delete linked journal entry and its items first (may block FK or leave orphans)
        $this->_delete_journal_entries_for_disbursement($id);

        // Delete disbursement line items (direct query to avoid builder state)
        $this->db->query('DELETE FROM cash_disbursement_items WHERE disbursement_id = ?', array($id));

        // Delete disbursement header (direct query so builder state from earlier deletes cannot affect it)
        $this->db->query('DELETE FROM cash_disbursements WHERE id = ? AND PIN = ?', array($id, $pin));

        // Complete transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Delete journal entry (and its items) for a disbursement. Works whether journal_entry
     * has reference_type/reference_id or only description.
     */
    private function _delete_journal_entries_for_disbursement($disburse_id) {
        $pin = current_user()->PIN;
        $disburse_id = (int) $disburse_id;
        $entry_ids = array();

        $has_ref_type = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row();
        $has_ref_id = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_id'")->row();
        if ($has_ref_type && $has_ref_id) {
            $rows = $this->db->query(
                'SELECT id FROM journal_entry WHERE reference_type = ? AND reference_id = ? AND PIN = ?',
                array('cash_disbursement', $disburse_id, $pin)
            )->result();
            foreach ($rows as $r) {
                $entry_ids[] = $r->id;
            }
        }
        if (empty($entry_ids)) {
            $d = $this->db->query('SELECT disburse_no FROM cash_disbursements WHERE id = ? AND PIN = ? LIMIT 1', array($disburse_id, $pin))->row();
            if ($d) {
                $like = 'Cash Disbursement: ' . $this->db->escape_like_str($d->disburse_no) . '%';
                $rows = $this->db->query(
                    'SELECT id FROM journal_entry WHERE description LIKE ? AND PIN = ?',
                    array($like, $pin)
                )->result();
                foreach ($rows as $r) {
                    $entry_ids[] = $r->id;
                }
            }
        }
        if (empty($entry_ids) && $has_ref_id) {
            $rows = $this->db->query(
                'SELECT id FROM journal_entry WHERE reference_id = ? AND PIN = ?',
                array($disburse_id, $pin)
            )->result();
            foreach ($rows as $r) {
                $entry_ids[] = $r->id;
            }
        }

        if (empty($entry_ids)) {
            return;
        }

        $has_journal_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'journal_id'")->row();
        $has_entry_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'entry_id'")->row();
        $link_col = $has_journal_id ? 'journal_id' : ($has_entry_id ? 'entry_id' : null);
        if ($link_col && !empty($entry_ids)) {
            $placeholders = implode(',', array_fill(0, count($entry_ids), '?'));
            $this->db->query(
                'DELETE FROM journal_entry_items WHERE ' . $link_col . ' IN (' . $placeholders . ')',
                $entry_ids
            );
        }

        if ($has_ref_type && $has_ref_id) {
            $this->db->query(
                'DELETE FROM journal_entry WHERE reference_type = ? AND reference_id = ? AND PIN = ?',
                array('cash_disbursement', $disburse_id, $pin)
            );
        } else {
            if (!empty($entry_ids)) {
                $placeholders = implode(',', array_fill(0, count($entry_ids), '?'));
                $params = array_merge($entry_ids, array($pin));
                $this->db->query(
                    'DELETE FROM journal_entry WHERE id IN (' . $placeholders . ') AND PIN = ?',
                    $params
                );
            }
        }
    }

    /**
     * Check if disbursement number already exists
     */
    function disburse_no_exists($disburse_no, $exclude_id = null) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('disburse_no', $disburse_no);
        
        if (!is_null($exclude_id)) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $count = $this->db->count_all_results('cash_disbursements');
        
        return ($count > 0);
    }

    /**
     * Get next disbursement number
     */
    function get_next_disburse_no() {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        
        $last_disburse = $this->db->get('cash_disbursements')->row();
        
        if ($last_disburse && !empty($last_disburse->disburse_no)) {
            // Extract number from disbursement number
            preg_match('/\d+/', $last_disburse->disburse_no, $matches);
            if (!empty($matches)) {
                $next_num = intval($matches[0]) + 1;
                return 'CD-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
            }
        }
        
        // Default first disbursement number
        return 'CD-00001';
    }

    /**
     * Create journal entry for cash disbursement (uses line items directly - each has account, debit, credit).
     */
    private function create_journal_entry($disburse_id, $disburse_data, $line_items) {
        log_message('debug', 'create_journal_entry for disbursement ID: ' . $disburse_id . ', items: ' . count($line_items));
        
        $entry_date = isset($disburse_data['disburse_date']) ? $disburse_data['disburse_date'] : date('Y-m-d');
        $journal_data = array(
            'entry_date' => $entry_date,
            'description' => 'Cash Disbursement: ' . $disburse_data['disburse_no'] . ' - ' . (isset($disburse_data['description']) ? $disburse_data['description'] : ''),
            'createdby' => current_user()->id,
            'PIN' => current_user()->PIN,
            'created_at' => date('Y-m-d H:i:s')
        );
        if ($this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row()) {
            $journal_data['reference_type'] = 'cash_disbursement';
            $journal_data['reference_id'] = $disburse_id;
        }
        
        $this->db->insert('journal_entry', $journal_data);
        $journal_id = $this->db->insert_id();
        if (!$journal_id) {
            log_message('error', 'Failed to create journal_entry header for disbursement: ' . $disburse_id);
            return false;
        }
        
        $items_table_has_journal_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'journal_id'")->row();
        $items_table_has_entry_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'entry_id'")->row();
        $items_table_has_description = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'description'")->row();
        $link_key = $items_table_has_journal_id ? 'journal_id' : ($items_table_has_entry_id ? 'entry_id' : 'journal_id');
        
        foreach ($line_items as $item) {
            $debit = isset($item['debit']) ? floatval($item['debit']) : 0;
            $credit = isset($item['credit']) ? floatval($item['credit']) : 0;
            if (empty($item['account']) || ($debit <= 0 && $credit <= 0)) continue;
            $line_desc = isset($item['description']) ? $item['description'] : '';
            $insert_item = array(
                $link_key => $journal_id,
                'account' => $item['account'],
                'debit' => $debit,
                'credit' => $credit,
                'PIN' => current_user()->PIN
            );
            if ($items_table_has_description) $insert_item['description'] = $line_desc;
            $this->db->insert('journal_entry_items', $insert_item);
        }
        
        log_message('debug', 'Journal entry created for disbursement ID: ' . $disburse_id . ', journal_id: ' . $journal_id);
        return true;
    }

    /**
     * Get journal/accounting entries for a cash disbursement (for display on view page).
     * Built from line items. For legacy disbursements (debits-only), adds Cash/Bank credit to balance.
     */
    function get_journal_entries_by_disbursement($disburse_id) {
        $pin = current_user()->PIN;
        $disburse_id = (int) $disburse_id;

        $disburse = $this->get_cash_disbursement($disburse_id);
        if (!$disburse) {
            return array('journal' => null, 'items' => array());
        }

        $line_items = $this->get_disburse_items($disburse_id);
        $display_items = array();
        $total_debit = 0;
        $total_credit = 0;
        
        foreach ($line_items as $item) {
            $debit = isset($item->debit) ? floatval($item->debit) : (isset($item->amount) ? floatval($item->amount) : 0);
            $credit = isset($item->credit) ? floatval($item->credit) : 0;
            $display_items[] = (object) array(
                'account' => $item->account,
                'account_name' => isset($item->account_name) ? $item->account_name : $item->account,
                'debit' => $debit,
                'credit' => $credit,
                'description' => isset($item->description) ? $item->description : ''
            );
            $total_debit += $debit;
            $total_credit += $credit;
        }
        
        // Legacy disbursements: line items had debits only; Cash/Bank credit was auto-generated, not in line items
        if ($total_debit > $total_credit && abs($total_debit - $total_credit) > 0.001) {
            $cash_account = $this->get_cash_account($disburse->payment_method);
            if ($cash_account) {
                $cash_name_row = $this->db->query('SELECT name FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($cash_account, $pin))->row();
                $credit_amount = $total_debit - $total_credit;
                array_unshift($display_items, (object) array(
                    'account' => $cash_account,
                    'account_name' => $cash_name_row ? $cash_name_row->name : $cash_account,
                    'debit' => 0,
                    'credit' => $credit_amount,
                    'description' => 'Disbursement to: ' . (isset($disburse->paid_to) ? $disburse->paid_to : '')
                ));
            }
        }
        
        $journal_display = (object) array(
            'id' => 0,
            'description' => 'Cash Disbursement: ' . $disburse->disburse_no . ' - ' . (isset($disburse->description) ? $disburse->description : ''),
            'reference_type' => 'cash_disbursement',
            'reference_id' => $disburse_id
        );
        return array('journal' => $journal_display, 'items' => $display_items);
    }

    /**
     * Get cash/bank account based on payment method.
     * 1) Try payment method config (paymentmenthod.gl_account_code) if set.
     * 2) Try account_chart by payment method name (for any method from paymentmenthod).
     * 3) Fallback: known method names (Cash, Cheque, etc.) then generic Cash/Bank asset.
     */
    private function get_cash_account($payment_method) {
        $pin = current_user()->PIN;
        $payment_method = trim((string) $payment_method);
        if ($payment_method === '') {
            $payment_method = 'Cash';
        }

        // 1) Prefer GL account from paymentmenthod (case-insensitive match in model)
        if ($this->db->table_exists('paymentmenthod')) {
            $this->load->model('payment_method_config_model');
            $config = $this->payment_method_config_model->get_account_for_payment_method($payment_method, $pin);
            if ($config && !empty($config->gl_account_code)) {
                $ac = $this->db->query('SELECT account FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($config->gl_account_code, $pin))->row();
                if ($ac) {
                    return $ac->account;
                }
            }
        }

        // 2) Find in account_chart by payment method name (e.g. "BANK DEPOSIT" -> name LIKE %BANK DEPOSIT%)
        $account = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND name LIKE ? AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%' . $this->db->escape_like_str($payment_method) . '%')
        )->row();
        if ($account) {
            return $account->account;
        }

        // 3) Known method name mapping
        $account_mapping = array(
            'Cash' => 'Cash',
            'Cheque' => 'Bank',
            'Bank Transfer' => 'Bank',
            'Mobile Money' => 'Mobile Money',
            'BANK DEPOSIT' => 'Bank',
            'Bank Deposit' => 'Bank'
        );
        $account_name = isset($account_mapping[$payment_method]) ? $account_mapping[$payment_method] : 'Cash';
        $account = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND name LIKE ? AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%' . $this->db->escape_like_str($account_name) . '%')
        )->row();

        if ($account) {
            return $account->account;
        }

        // 4) Last resort: any asset account with Cash or Bank in name
        $account = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND (name LIKE ? OR name LIKE ?) AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%Cash%', '%Bank%')
        )->row();

        if (!$account) {
            log_message('error', 'No cash/bank account in account_chart for payment method: ' . $payment_method . ', PIN: ' . $pin);
        }
        return $account ? $account->account : null;
    }

    /**
     * Get cash disbursements by date range
     */
    function get_disbursements_by_date($start_date, $end_date) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('disburse_date >=', $start_date);
        $this->db->where('disburse_date <=', $end_date);
        $this->db->order_by('disburse_date', 'DESC');
        
        return $this->db->get('cash_disbursements')->result();
    }

    /**
     * Get total disbursements for a period
     */
    function get_total_disbursements($start_date = null, $end_date = null) {
        $this->db->select_sum('total_amount');
        $this->db->where('PIN', current_user()->PIN);
        
        if (!is_null($start_date)) {
            $this->db->where('disburse_date >=', $start_date);
        }
        
        if (!is_null($end_date)) {
            $this->db->where('disburse_date <=', $end_date);
        }
        
        $result = $this->db->get('cash_disbursements')->row();
        
        return $result ? $result->total_amount : 0;
    }

    /**
     * Get account summary for cash disbursement module (Trial Balance style).
     * Returns each account used in cash_disbursement_items with total amount (debit side), optionally filtered by date.
     */
    function get_account_summary($date_from = null, $date_to = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT cdi.account,
                COALESCE(ac.name, 'Unknown Account') AS account_name,
                SUM(cdi.amount) AS total_amount,
                COUNT(cdi.id) AS line_count
                FROM cash_disbursement_items cdi
                INNER JOIN cash_disbursements cd ON cd.id = cdi.disbursement_id AND cd.PIN = ?
                LEFT JOIN account_chart ac ON ac.account = cdi.account AND ac.PIN = ?
                WHERE cdi.PIN = ?";
        $params = array($pin, $pin, $pin);
        if (!empty($date_from)) {
            $sql .= " AND cd.disburse_date >= ?";
            $params[] = $date_from;
        }
        if (!empty($date_to)) {
            $sql .= " AND cd.disburse_date <= ?";
            $params[] = $date_to;
        }
        $sql .= " GROUP BY cdi.account, ac.name ORDER BY cdi.account ASC";
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get detailed lines for cash disbursement report (all disbursement items with header info).
     */
    function get_account_details($date_from = null, $date_to = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT cd.disburse_no, cd.disburse_date, cd.paid_to, cd.payment_method, cd.description AS disburse_description,
                cdi.account, COALESCE(ac.name, 'Unknown Account') AS account_name, cdi.description AS line_description, cdi.amount
                FROM cash_disbursement_items cdi
                INNER JOIN cash_disbursements cd ON cd.id = cdi.disbursement_id AND cd.PIN = ?
                LEFT JOIN account_chart ac ON ac.account = cdi.account AND ac.PIN = ?
                WHERE cdi.PIN = ?";
        $params = array($pin, $pin, $pin);
        if (!empty($date_from)) {
            $sql .= " AND cd.disburse_date >= ?";
            $params[] = $date_from;
        }
        if (!empty($date_to)) {
            $sql .= " AND cd.disburse_date <= ?";
            $params[] = $date_to;
        }
        $sql .= " ORDER BY cd.disburse_date ASC, cd.id ASC, cdi.id ASC";
        return $this->db->query($sql, $params)->result();
    }
}
