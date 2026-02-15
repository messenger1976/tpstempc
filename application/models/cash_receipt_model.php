<?php

/**
 * Cash Receipt Model
 * Handles database operations for cash receipt module
 */
class Cash_receipt_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('payment_method_config_model');
    }

    /**
     * Get all cash receipts
     */
    function get_cash_receipts($id = null, $receipt_no = null, $date_from = null, $date_to = null) {
        $this->db->where('PIN', current_user()->PIN);
        
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        
        if (!is_null($receipt_no)) {
            $this->db->where('receipt_no', $receipt_no);
        }
        
        // Apply date range filter if provided
        if (!empty($date_from)) {
            $this->db->where('receipt_date >=', $date_from);
        }
        
        if (!empty($date_to)) {
            $this->db->where('receipt_date <=', $date_to);
        }
        
        $this->db->order_by('receipt_date', 'DESC');
        $this->db->order_by('id', 'DESC');
        
        return $this->db->get('cash_receipts');
    }

    /**
     * Get single cash receipt
     */
    function get_cash_receipt($id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('id', $id);
        
        return $this->db->get('cash_receipts')->row();
    }

    /**
     * Get receipt items/line items
     */
    function get_receipt_items($receipt_id) {
        // Use raw query to avoid query builder state issues
        $sql = "SELECT cri.*, ac.name as account_name 
                FROM cash_receipt_items cri
                LEFT JOIN account_chart ac ON ac.account = cri.account AND ac.PIN = ?
                WHERE cri.receipt_id = ?
                ORDER BY cri.id ASC";
        
        return $this->db->query($sql, array(current_user()->PIN, $receipt_id))->result();
    }

    /**
     * Create new cash receipt
     */
    function create_cash_receipt($receipt_data, $line_items) {
        // Insert receipt header
        $this->db->insert('cash_receipts', $receipt_data);
        
        // Get actual insert ID by querying the last inserted record for this PIN
        // (Workaround for persistent connection stale insert_id issue)
        $receipt = $this->db->where('PIN', current_user()->PIN)
                            ->order_by('id', 'DESC')
                            ->limit(1)
                            ->get('cash_receipts')
                            ->row();
        
        if (!$receipt) {
            return false;
        }
        
        $receipt_id = $receipt->id;
        $receipt_data['id'] = $receipt_id;
        
        // Insert receipt items
        if (!empty($line_items)) {
            foreach ($line_items as $item) {
                $item['receipt_id'] = $receipt_id;
                $item['PIN'] = current_user()->PIN;
                $this->db->insert('cash_receipt_items', $item);
            }
        }
        
        // Create journal entry (journal_entry table only, not GL)
        $this->create_journal_entry($receipt_id, $receipt_data, $line_items);
        
        return $receipt_id;
    }

    /**
     * Update existing cash receipt (header, line items, and journal entry).
     * Changing payment method or line items replaces the accounting entry with a new one.
     */
    function update_cash_receipt($id, $receipt_data, $line_items) {
        $id = (int) $id;
        $pin = current_user()->PIN;
        if ($id <= 0) {
            return false;
        }

        // Start transaction
        $this->db->trans_start();

        // Remove old journal entry FIRST (while header still has current receipt_no so description lookup works)
        $this->_delete_journal_entries_for_receipt($id);

        // Update payment_method in its own query so it always saves (avoids being skipped or lost in combined UPDATE)
        $payment_val = isset($receipt_data['payment_method']) ? trim((string) $receipt_data['payment_method']) : '';
        if ($payment_val !== '') {
            $this->db->query(
                'UPDATE cash_receipts SET payment_method = ?, updated_at = ? WHERE id = ? AND PIN = ?',
                array($payment_val, isset($receipt_data['updated_at']) ? $receipt_data['updated_at'] : date('Y-m-d H:i:s'), $id, $pin)
            );
        }

        // Update remaining receipt header fields (payment_method already done above)
        $allowed = array('receipt_no', 'receipt_date', 'received_from', 'cheque_no', 'bank_name', 'description', 'total_amount', 'updated_at');
        $set_parts = array();
        $params = array();
        foreach ($allowed as $col) {
            if (!array_key_exists($col, $receipt_data)) {
                continue;
            }
            $val = $receipt_data[$col];
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
                'UPDATE cash_receipts SET ' . implode(', ', $set_parts) . ' WHERE id = ? AND PIN = ?',
                $params
            );
        }

        // Delete existing line items (direct query)
        $this->db->query('DELETE FROM cash_receipt_items WHERE receipt_id = ?', array($id));

        // Insert new line items
        if (!empty($line_items)) {
            foreach ($line_items as $item) {
                $item['receipt_id'] = $id;
                $item['PIN'] = $pin;
                $this->db->insert('cash_receipt_items', $item);
            }
        }

        // Complete transaction so receipt + items are committed even if journal creation fails
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        // Create journal entry after commit so a journal failure does not roll back the edit
        $this->create_journal_entry($id, $receipt_data, $line_items);

        return true;
    }

    /**
     * Delete cash receipt (header, line items, and linked journal entry).
     */
    function delete_cash_receipt($id) {
        $id = (int) $id;
        $pin = current_user()->PIN;
        if ($id <= 0) {
            return false;
        }

        // Start transaction
        $this->db->trans_start();

        // Delete linked journal entry and its items first (may block FK or leave orphans)
        $this->_delete_journal_entries_for_receipt($id);

        // Delete receipt line items (direct query to avoid builder state)
        $this->db->query('DELETE FROM cash_receipt_items WHERE receipt_id = ?', array($id));

        // Delete receipt header (direct query so builder state from earlier deletes cannot affect it)
        $this->db->query('DELETE FROM cash_receipts WHERE id = ? AND PIN = ?', array($id, $pin));

        // Complete transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Delete journal entry (and its items) for a receipt. Works whether journal_entry
     * has reference_type/reference_id or only description.
     */
    private function _delete_journal_entries_for_receipt($receipt_id) {
        $pin = current_user()->PIN;
        $receipt_id = (int) $receipt_id;
        $entry_ids = array();

        $has_ref_type = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row();
        $has_ref_id = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_id'")->row();
        if ($has_ref_type && $has_ref_id) {
            $rows = $this->db->query(
                'SELECT id FROM journal_entry WHERE reference_type = ? AND reference_id = ? AND PIN = ?',
                array('cash_receipt', $receipt_id, $pin)
            )->result();
            foreach ($rows as $r) {
                $entry_ids[] = $r->id;
            }
        }
        if (empty($entry_ids)) {
            $r = $this->db->query('SELECT receipt_no FROM cash_receipts WHERE id = ? AND PIN = ? LIMIT 1', array($receipt_id, $pin))->row();
            if ($r) {
                $like = 'Cash Receipt: ' . $this->db->escape_like_str($r->receipt_no) . '%';
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
                array($receipt_id, $pin)
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
                array('cash_receipt', $receipt_id, $pin)
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
     * Check if receipt number already exists
     */
    function receipt_no_exists($receipt_no, $exclude_id = null) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('receipt_no', $receipt_no);
        
        if (!is_null($exclude_id)) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $count = $this->db->count_all_results('cash_receipts');
        
        return ($count > 0);
    }

    /**
     * Get next receipt number
     */
    function get_next_receipt_no() {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        
        $last_receipt = $this->db->get('cash_receipts')->row();
        
        if ($last_receipt && !empty($last_receipt->receipt_no)) {
            // Extract number from receipt number
            preg_match('/\d+/', $last_receipt->receipt_no, $matches);
            if (!empty($matches)) {
                $next_num = intval($matches[0]) + 1;
                return 'CR-' . str_pad($next_num, 5, '0', STR_PAD_LEFT);
            }
        }
        
        // Default first receipt number
        return 'CR-00001';
    }

    /**
     * Create journal entry for cash receipt (journal_entry table only, not GL).
     * Handles both journal_id and entry_id columns in journal_entry_items.
     */
    private function create_journal_entry($receipt_id, $receipt_data, $line_items) {
        $pin = current_user()->PIN;
        $cash_account = $this->get_cash_account($receipt_data['payment_method']);

        // Final fallback if get_cash_account still returns null
        if (!$cash_account) {
            $fallback = $this->db->query(
                'SELECT account FROM account_chart WHERE PIN = ? AND account_type IN (1, 10000) AND (name LIKE ? OR name LIKE ?) LIMIT 1',
                array($pin, '%Cash%', '%Bank%')
            )->row();
            if ($fallback) {
                $cash_account = $fallback->account;
            } else {
                log_message('error', 'Journal entry failed: No cash account found for payment method: ' . $receipt_data['payment_method']);
                return false;
            }
        }

        log_message('debug', 'Creating journal entry for receipt_id: ' . $receipt_id . ', cash_account: ' . $cash_account);

        $entry_date = isset($receipt_data['receipt_date']) ? $receipt_data['receipt_date'] : date('Y-m-d');
        $desc = 'Cash Receipt: ' . $receipt_data['receipt_no'] . ' - ' . (isset($receipt_data['description']) ? $receipt_data['description'] : '');

        // Check if reference_type/reference_id columns exist
        $has_ref_type = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row();
        $has_ref_id = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_id'")->row();

        // Build INSERT for journal_entry header
        if ($has_ref_type && $has_ref_id) {
            $this->db->query(
                'INSERT INTO journal_entry (entry_date, description, reference_type, reference_id, createdby, PIN, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                array($entry_date, $desc, 'cash_receipt', $receipt_id, current_user()->id, $pin, date('Y-m-d H:i:s'))
            );
        } else {
            $this->db->query(
                'INSERT INTO journal_entry (entry_date, description, createdby, PIN, created_at) VALUES (?, ?, ?, ?, ?)',
                array($entry_date, $desc, current_user()->id, $pin, date('Y-m-d H:i:s'))
            );
        }

        $journal_id = $this->db->insert_id();
        if (!$journal_id) {
            $db_error = $this->db->error();
            log_message('error', 'Journal entry header insert failed: ' . json_encode($db_error));
            return false;
        }

        log_message('debug', 'Journal entry created with ID: ' . $journal_id);

        // Check which column journal_entry_items uses
        $has_journal_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'journal_id'")->row();
        $has_entry_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'entry_id'")->row();
        $link_col = $has_journal_id ? 'journal_id' : ($has_entry_id ? 'entry_id' : 'journal_id'); // default to journal_id

        // Check if description column exists
        $has_desc = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'description'")->row();

        // Debit Cash/Bank Account (increase asset)
        $debit_desc = 'Receipt from: ' . (isset($receipt_data['received_from']) ? $receipt_data['received_from'] : '');
        if ($has_desc) {
            $debit_result = $this->db->query(
                'INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)',
                array($journal_id, $cash_account, $receipt_data['total_amount'], 0, $debit_desc, $pin)
            );
        } else {
            $debit_result = $this->db->query(
                'INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)',
                array($journal_id, $cash_account, $receipt_data['total_amount'], 0, $pin)
            );
        }
        
        if (!$debit_result) {
            $error = $this->db->error();
            log_message('error', 'Failed to insert debit item for cash receipt journal entry: ' . json_encode($error) . ' | journal_id: ' . $journal_id);
            return false;
        }

        // Credit Revenue/Other Accounts (based on line items)
        foreach ($line_items as $item) {
            if ($has_desc) {
                $credit_result = $this->db->query(
                    'INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)',
                    array($journal_id, $item['account'], 0, $item['amount'], isset($item['description']) ? $item['description'] : '', $pin)
                );
            } else {
                $credit_result = $this->db->query(
                    'INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)',
                    array($journal_id, $item['account'], 0, $item['amount'], $pin)
                );
            }
            
            if (!$credit_result) {
                $error = $this->db->error();
                log_message('error', 'Failed to insert credit item for cash receipt journal entry: ' . json_encode($error) . ' | journal_id: ' . $journal_id . ' | item: ' . json_encode($item));
                return false;
            }
        }

        log_message('debug', 'Journal entry items created successfully for receipt ID: ' . $receipt_id . ', journal_id: ' . $journal_id . ', items count: ' . (count($line_items) + 1));
        return true;
    }

    /**
     * Get cash/bank account based on payment method.
     * Priority: 1) paymentmenthod.gl_account_code, 2) account_chart by payment method name, 3) standard mapping, 4) generic Cash/Bank asset.
     */
    private function get_cash_account($payment_method) {
        $pin = current_user()->PIN;
        $payment_method = trim((string) $payment_method);
        if (empty($payment_method)) {
            $payment_method = 'Cash';
        }

        // 1) Check paymentmenthod table for configured GL account
        $pm_config = $this->payment_method_config_model->get_account_for_payment_method($payment_method);
        if ($pm_config && !empty($pm_config->gl_account_code)) {
            $acct = $this->db->query(
                'SELECT account FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1',
                array($pm_config->gl_account_code, $pin)
            )->row();
            if ($acct) {
                return $acct->account;
            }
        }

        // 2) Try account_chart by payment method name (LIKE)
        $acct = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND name LIKE ? AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%' . $this->db->escape_like_str($payment_method) . '%')
        )->row();
        if ($acct) {
            return $acct->account;
        }

        // 3) Standard mapping fallback
        $mapping = array(
            'Cash' => 'Cash',
            'Cheque' => 'Bank',
            'Bank Transfer' => 'Bank',
            'BANK DEPOSIT' => 'Bank',
            'Mobile Money' => 'Mobile Money'
        );
        $search_name = isset($mapping[$payment_method]) ? $mapping[$payment_method] : 'Cash';
        $acct = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND name LIKE ? AND account_type IN (1, 10000) LIMIT 1',
            array($pin, '%' . $this->db->escape_like_str($search_name) . '%')
        )->row();
        if ($acct) {
            return $acct->account;
        }

        // 4) Generic Cash or Bank asset account
        $acct = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND account_type IN (1, 10000) AND (name LIKE ? OR name LIKE ?) LIMIT 1',
            array($pin, '%Cash%', '%Bank%')
        )->row();
        if ($acct) {
            return $acct->account;
        }

        log_message('error', 'No cash/bank account found for payment method: ' . $payment_method);
        return null;
    }

    /**
     * Get journal/accounting entries for a cash receipt (for display on view page).
     * Always built from current receipt + line items + current payment method so the
     * displayed entries match the current payment method and accounts (they update when edited).
     */
    function get_journal_entries_by_receipt($receipt_id) {
        $pin = current_user()->PIN;
        $receipt_id = (int) $receipt_id;

        $receipt = $this->get_cash_receipt($receipt_id);
        if (!$receipt) {
            return array('journal' => null, 'items' => array());
        }

        $line_items = $this->get_receipt_items($receipt_id);
        $cash_account_code = $this->get_cash_account($receipt->payment_method);
        $display_items = array();
        
        // Debit entry: Cash/Bank Account
        if ($cash_account_code) {
            $cash_name = $this->db->query('SELECT name FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($cash_account_code, $pin))->row();
            $display_items[] = (object) array(
                'account' => $cash_account_code,
                'account_name' => $cash_name ? $cash_name->name : $cash_account_code,
                'debit' => floatval($receipt->total_amount),
                'credit' => 0,
                'description' => 'Receipt from: ' . (isset($receipt->received_from) ? $receipt->received_from : '')
            );
        }
        
        // Credit entries: Revenue/Other Accounts (from line items)
        foreach ($line_items as $item) {
            $display_items[] = (object) array(
                'account' => $item->account,
                'account_name' => isset($item->account_name) ? $item->account_name : $item->account,
                'debit' => 0,
                'credit' => floatval($item->amount),
                'description' => isset($item->description) ? $item->description : ''
            );
        }
        
        $journal_display = (object) array(
            'id' => 0,
            'description' => 'Cash Receipt: ' . $receipt->receipt_no . ' - ' . (isset($receipt->description) ? $receipt->description : ''),
            'reference_type' => 'cash_receipt',
            'reference_id' => $receipt_id
        );
        
        return array('journal' => $journal_display, 'items' => $display_items);
    }

    /**
     * Get cash receipts by date range
     */
    function get_receipts_by_date($start_date, $end_date) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('receipt_date >=', $start_date);
        $this->db->where('receipt_date <=', $end_date);
        $this->db->order_by('receipt_date', 'DESC');
        
        return $this->db->get('cash_receipts')->result();
    }

    /**
     * Get total receipts for a period
     */
    function get_total_receipts($start_date = null, $end_date = null) {
        $this->db->select_sum('total_amount');
        $this->db->where('PIN', current_user()->PIN);
        
        if (!is_null($start_date)) {
            $this->db->where('receipt_date >=', $start_date);
        }
        
        if (!is_null($end_date)) {
            $this->db->where('receipt_date <=', $end_date);
        }
        
        $result = $this->db->get('cash_receipts')->row();
        
        return $result ? $result->total_amount : 0;
    }

    /**
     * Get account summary for cash receipt module (Trial Balance style).
     * Returns each account used in cash_receipt_items with total amount, optionally filtered by date.
     */
    function get_account_summary($date_from = null, $date_to = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT cri.account,
                COALESCE(ac.name, 'Unknown Account') AS account_name,
                SUM(cri.amount) AS total_amount,
                COUNT(cri.id) AS line_count
                FROM cash_receipt_items cri
                INNER JOIN cash_receipts cr ON cr.id = cri.receipt_id AND cr.PIN = ?
                LEFT JOIN account_chart ac ON ac.account = cri.account AND ac.PIN = ?
                WHERE cri.PIN = ?";
        $params = array($pin, $pin, $pin);
        if (!empty($date_from)) {
            $sql .= " AND cr.receipt_date >= ?";
            $params[] = $date_from;
        }
        if (!empty($date_to)) {
            $sql .= " AND cr.receipt_date <= ?";
            $params[] = $date_to;
        }
        $sql .= " GROUP BY cri.account, ac.name ORDER BY cri.account ASC";
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get detailed lines for cash receipt report (all receipt items with receipt header info).
     */
    function get_account_details($date_from = null, $date_to = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT cr.receipt_no, cr.receipt_date, cr.received_from, cr.payment_method, cr.description AS receipt_description,
                cri.account, COALESCE(ac.name, 'Unknown Account') AS account_name, cri.description AS line_description, cri.amount
                FROM cash_receipt_items cri
                INNER JOIN cash_receipts cr ON cr.id = cri.receipt_id AND cr.PIN = ?
                LEFT JOIN account_chart ac ON ac.account = cri.account AND ac.PIN = ?
                WHERE cri.PIN = ?";
        $params = array($pin, $pin, $pin);
        if (!empty($date_from)) {
            $sql .= " AND cr.receipt_date >= ?";
            $params[] = $date_from;
        }
        if (!empty($date_to)) {
            $sql .= " AND cr.receipt_date <= ?";
            $params[] = $date_to;
        }
        $sql .= " ORDER BY cr.receipt_date ASC, cr.id ASC, cri.id ASC";
        return $this->db->query($sql, $params)->result();
    }
}
