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
     * Update existing cash receipt
     */
    function update_cash_receipt($id, $receipt_data, $line_items) {
        // Start transaction
        $this->db->trans_start();
        
        // Update receipt header
        $this->db->where('id', $id);
        $this->db->where('PIN', current_user()->PIN);
        $this->db->update('cash_receipts', $receipt_data);
        
        // Delete existing items
        $this->db->where('receipt_id', $id);
        $this->db->delete('cash_receipt_items');
        
        // Insert new items
        if (!empty($line_items)) {
            foreach ($line_items as $item) {
                $item['receipt_id'] = $id;
                $item['PIN'] = current_user()->PIN;
                $this->db->insert('cash_receipt_items', $item);
            }
        }
        
        // Delete old journal entries
        $this->db->where('reference_type', 'cash_receipt');
        $this->db->where('reference_id', $id);
        $this->db->delete('journal_entry');
        
        // Create new journal entry
        $this->create_journal_entry($id, $receipt_data, $line_items);
        
        // Complete transaction
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    /**
     * Delete cash receipt
     */
    function delete_cash_receipt($id) {
        // Start transaction
        $this->db->trans_start();
        
        // Resolve PIN safely
        $pin = (is_object(current_user()) && isset(current_user()->PIN)) ? current_user()->PIN : $this->session->userdata('PIN');
        if (!$pin) {
            log_message('error', 'delete_cash_receipt: missing PIN, aborting delete for id ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        // Delete receipt items
        $this->db->where('receipt_id', $id);
        $this->db->delete('cash_receipt_items');
        
        // Delete journal entries
        $this->db->where('reference_type', 'cash_receipt');
        $this->db->where('reference_id', $id);
        $this->db->delete('journal_entry');
        
        // Delete receipt header
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $this->db->delete('cash_receipts');
        
        // Complete transaction
        $this->db->trans_complete();
        
        return $this->db->trans_status();
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
     * Create journal entry for cash receipt
     */
    private function create_journal_entry($receipt_id, $receipt_data, $line_items) {
        // Get cash/bank account based on payment method
        $cash_account = $this->get_cash_account($receipt_data['payment_method']);
        
        if (!$cash_account) {
            log_message('error', 'Journal entry failed: No cash account found for payment method: ' . $receipt_data['payment_method']);
            return false;
        }
        
        log_message('debug', 'Creating journal entry for receipt_id: ' . $receipt_id . ', cash_account: ' . $cash_account);
        
        $entry_date = isset($receipt_data['receipt_date']) ? $receipt_data['receipt_date'] : date('Y-m-d');
        
        // Create journal entry header
        $journal_data = array(
            'entry_date' => $entry_date,
            'description' => 'Cash Receipt: ' . $receipt_data['receipt_no'] . ' - ' . $receipt_data['description'],
            'reference_type' => 'cash_receipt',
            'reference_id' => $receipt_id,
            'createdby' => current_user()->id,
            'PIN' => current_user()->PIN,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        $this->db->insert('journal_entry', $journal_data);
        $journal_id = $this->db->insert_id();
        
        if (!$journal_id) {
            $db_error = $this->db->error();
            log_message('error', 'Journal entry header insert failed: ' . json_encode($db_error));
            return false;
        }
        
        log_message('debug', 'Journal entry created with ID: ' . $journal_id);
        
        // Debit Cash/Bank Account (increase asset)
        $this->db->insert('journal_entry_items', array(
            'journal_id' => $journal_id,
            'account' => $cash_account,
            'debit' => $receipt_data['total_amount'],
            'credit' => 0,
            'description' => 'Receipt from: ' . $receipt_data['received_from'],
            'PIN' => current_user()->PIN
        ));
        
        if ($this->db->affected_rows() <= 0) {
            $db_error = $this->db->error();
            log_message('error', 'Journal entry debit item insert failed: ' . json_encode($db_error));
        }
        
        // Credit Revenue/Other Accounts (based on line items)
        foreach ($line_items as $item) {
            $this->db->insert('journal_entry_items', array(
                'journal_id' => $journal_id,
                'account' => $item['account'],
                'debit' => 0,
                'credit' => $item['amount'],
                'description' => $item['description'],
                'PIN' => current_user()->PIN
            ));
            
            if ($this->db->affected_rows() <= 0) {
                $db_error = $this->db->error();
                log_message('error', 'Journal entry credit item insert failed for account ' . $item['account'] . ': ' . json_encode($db_error));
            }
        }
        
        return true;
    }

    /**
     * Get cash/bank account based on payment method
     */
    private function get_cash_account($payment_method) {
        // Map payment methods to account types
        $account_mapping = array(
            'Cash' => 'Cash',
            'Cheque' => 'Bank',
            'Bank Transfer' => 'Bank',
            'Mobile Money' => 'Mobile Money'
        );
        
        $account_name = isset($account_mapping[$payment_method]) ? $account_mapping[$payment_method] : 'Cash';
        
        // Find the account - check for asset type 10000 (or 1 for legacy)
        $this->db->where('PIN', current_user()->PIN);
        $this->db->like('name', $account_name);
        $this->db->where_in('account_type', array(1, 10000)); // Asset type
        $this->db->limit(1);
        
        $account = $this->db->get('account_chart')->row();
        
        if (!$account) {
            log_message('error', 'No cash/bank account found for payment method: ' . $payment_method . ', searched for name like: ' . $account_name);
        }
        
        return $account ? $account->account : null;
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
}
