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
    function get_cash_disbursements($id = null, $disburse_no = null) {
        $this->db->where('PIN', current_user()->PIN);
        
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        
        if (!is_null($disburse_no)) {
            $this->db->where('disburse_no', $disburse_no);
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
     * Get disbursement items/line items
     */
    function get_disburse_items($disburse_id) {
        $this->db->where('disburse_id', $disburse_id);
        $this->db->order_by('id', 'ASC');
        
        $items = $this->db->get('cash_disbursement_items')->result();
        
        // Get account names for each item
        foreach ($items as $item) {
            $account = $this->db->where('account', $item->account)
                                ->where('PIN', current_user()->PIN)
                                ->get('account_chart')
                                ->row();
            $item->account_name = $account ? $account->name : 'Unknown Account';
        }
        
        return $items;
    }

    /**
     * Create new cash disbursement
     */
    function create_cash_disbursement($disburse_data, $line_items) {
        // Start transaction
        $this->db->trans_start();
        
        // Insert disbursement header
        $this->db->insert('cash_disbursements', $disburse_data);
        $disburse_id = $this->db->insert_id();
        
        // Insert disbursement items
        if ($disburse_id && !empty($line_items)) {
            foreach ($line_items as $item) {
                $item['disburse_id'] = $disburse_id;
                $item['PIN'] = current_user()->PIN;
                $this->db->insert('cash_disbursement_items', $item);
            }
        }
        
        // Create journal entry
        $this->create_journal_entry($disburse_id, $disburse_data, $line_items);
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $disburse_id;
    }

    /**
     * Update existing cash disbursement
     */
    function update_cash_disbursement($id, $disburse_data, $line_items) {
        // Start transaction
        $this->db->trans_start();
        
        // Update disbursement header
        $this->db->where('id', $id);
        $this->db->where('PIN', current_user()->PIN);
        $this->db->update('cash_disbursements', $disburse_data);
        
        // Delete existing items
        $this->db->where('disburse_id', $id);
        $this->db->delete('cash_disbursement_items');
        
        // Insert new items
        if (!empty($line_items)) {
            foreach ($line_items as $item) {
                $item['disburse_id'] = $id;
                $item['PIN'] = current_user()->PIN;
                $this->db->insert('cash_disbursement_items', $item);
            }
        }
        
        // Delete old journal entries
        $this->db->where('reference_type', 'cash_disbursement');
        $this->db->where('reference_id', $id);
        $this->db->delete('journal_entry');
        
        // Create new journal entry
        $this->create_journal_entry($id, $disburse_data, $line_items);
        
        // Complete transaction
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    /**
     * Delete cash disbursement
     */
    function delete_cash_disbursement($id) {
        // Start transaction
        $this->db->trans_start();
        
        // Delete disbursement items
        $this->db->where('disburse_id', $id);
        $this->db->delete('cash_disbursement_items');
        
        // Delete journal entries
        $this->db->where('reference_type', 'cash_disbursement');
        $this->db->where('reference_id', $id);
        $this->db->delete('journal_entry');
        
        // Delete disbursement header
        $this->db->where('id', $id);
        $this->db->where('PIN', current_user()->PIN);
        $this->db->delete('cash_disbursements');
        
        // Complete transaction
        $this->db->trans_complete();
        
        return $this->db->trans_status();
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
     * Create journal entry for cash disbursement
     */
    private function create_journal_entry($disburse_id, $disburse_data, $line_items) {
        // Get cash/bank account based on payment method
        $cash_account = $this->get_cash_account($disburse_data['payment_method']);
        
        if (!$cash_account) {
            return false;
        }
        
        $entry_date = isset($disburse_data['disburse_date']) ? $disburse_data['disburse_date'] : date('Y-m-d');
        
        // Create journal entry header
        $journal_data = array(
            'entry_date' => $entry_date,
            'description' => 'Cash Disbursement: ' . $disburse_data['disburse_no'] . ' - ' . $disburse_data['description'],
            'reference_type' => 'cash_disbursement',
            'reference_id' => $disburse_id,
            'createdby' => current_user()->id,
            'PIN' => current_user()->PIN,
            'created_at' => date('Y-m-d H:i:s')
        );
        
        $this->db->insert('journal_entry', $journal_data);
        $journal_id = $this->db->insert_id();
        
        if (!$journal_id) {
            return false;
        }
        
        // Credit Cash/Bank Account (decrease asset)
        $this->db->insert('journal_entry_items', array(
            'journal_id' => $journal_id,
            'account' => $cash_account,
            'debit' => 0,
            'credit' => $disburse_data['total_amount'],
            'description' => 'Disbursement to: ' . $disburse_data['paid_to'],
            'PIN' => current_user()->PIN
        ));
        
        // Debit Expense/Other Accounts (based on line items)
        foreach ($line_items as $item) {
            $this->db->insert('journal_entry_items', array(
                'journal_id' => $journal_id,
                'account' => $item['account'],
                'debit' => $item['amount'],
                'credit' => 0,
                'description' => $item['description'],
                'PIN' => current_user()->PIN
            ));
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
        
        // Find the account
        $this->db->where('PIN', current_user()->PIN);
        $this->db->like('name', $account_name);
        $this->db->where('account_type', 1); // Asset type
        $this->db->limit(1);
        
        $account = $this->db->get('account_chart')->row();
        
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
}
