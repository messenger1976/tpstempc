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

    /**
     * Post journal_entry (cash receipt/disbursement) to GL.
     * Moved to top of class to avoid scope/parse issues.
     */
    function post_journal_entry_to_general_ledger($journal_entry_id, $journal_id = 5) {
        $pin = current_user()->PIN;
        $journal_entry_id = (int) $journal_entry_id;
        if ($journal_entry_id <= 0) {
            return false;
        }
        if ($this->is_journal_entry_posted_to_gl($journal_entry_id)) {
            return true;
        }
        $entry = $this->db->query(
            'SELECT id, entry_date, description, PIN FROM journal_entry WHERE id = ? AND PIN = ? LIMIT 1',
            array($journal_entry_id, $pin)
        )->row();
        if (!$entry) {
            log_message('error', 'post_journal_entry_to_general_ledger: journal_entry not found id=' . $journal_entry_id);
            return false;
        }
        $has_journal_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'journal_id'")->row();
        $has_entry_id = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'entry_id'")->row();
        $link_col = $has_journal_id ? 'journal_id' : ($has_entry_id ? 'entry_id' : null);
        if (!$link_col) {
            log_message('error', 'post_journal_entry_to_general_ledger: journal_entry_items has no journal_id or entry_id');
            return false;
        }
        $has_pin_col = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'PIN'")->row();
        $has_desc_col = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'description'")->row();
        $select_fields = 'account, debit, credit';
        if ($has_desc_col) {
            $select_fields .= ', description';
        }
        $line_items = $this->db->query(
            'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? ORDER BY id ASC',
            array($journal_entry_id)
        )->result();
        if (empty($line_items) && $has_pin_col) {
            $line_items = $this->db->query(
                'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? AND PIN = ? ORDER BY id ASC',
                array($journal_entry_id, $pin)
            )->result();
        }
        if (empty($line_items) && $has_journal_id && $has_entry_id) {
            $other_link_col = ($link_col == 'journal_id') ? 'entry_id' : 'journal_id';
            $line_items = $this->db->query(
                'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $other_link_col . ' = ? ORDER BY id ASC',
                array($journal_entry_id)
            )->result();
            if (!empty($line_items)) {
                $link_col = $other_link_col;
            } elseif ($has_pin_col) {
                $line_items = $this->db->query(
                    'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $other_link_col . ' = ? AND PIN = ? ORDER BY id ASC',
                    array($journal_entry_id, $pin)
                )->result();
                if (!empty($line_items)) {
                    $link_col = $other_link_col;
                }
            }
        }
        if (empty($line_items)) {
            $entry_check = $this->db->query('SELECT id, reference_type, reference_id FROM journal_entry WHERE id = ?', array($journal_entry_id))->row();
            if ($entry_check && $entry_check->reference_type == 'cash_receipt' && isset($entry_check->reference_id)) {
                $this->load->model('cash_receipt_model');
                $receipt = $this->cash_receipt_model->get_cash_receipt($entry_check->reference_id);
                if ($receipt) {
                    $receipt_items = $this->cash_receipt_model->get_receipt_items($entry_check->reference_id);
                    $payment_method = isset($receipt->payment_method) ? trim($receipt->payment_method) : 'Cash';
                    if (empty($payment_method)) $payment_method = 'Cash';
                    $this->load->model('payment_method_config_model');
                    $pm_config = $this->payment_method_config_model->get_account_for_payment_method($payment_method);
                    $cash_account = null;
                    if ($pm_config && !empty($pm_config->gl_account_code)) {
                        $acct = $this->db->query('SELECT account FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($pm_config->gl_account_code, $pin))->row();
                        if ($acct) $cash_account = $acct->account;
                    }
                    if (!$cash_account) {
                        $acct = $this->db->query('SELECT account FROM account_chart WHERE PIN = ? AND account_type IN (1, 10000) AND (name LIKE ? OR name LIKE ?) LIMIT 1', array($pin, '%Cash%', '%Bank%'))->row();
                        if ($acct) $cash_account = $acct->account;
                    }
                    if ($cash_account) {
                        $has_desc = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'description'")->row();
                        $debit_desc = 'Receipt from: ' . (isset($receipt->received_from) ? $receipt->received_from : '');
                        if ($has_desc) {
                            $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, $receipt->total_amount, 0, $debit_desc, $pin));
                        } else {
                            $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, $receipt->total_amount, 0, $pin));
                        }
                        foreach ($receipt_items as $item) {
                            if ($has_desc) {
                                $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $item->account, 0, $item->amount, isset($item->description) ? $item->description : '', $pin));
                            } else {
                                $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $item->account, 0, $item->amount, $pin));
                            }
                        }
                        $line_items = $this->db->query('SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? ORDER BY id ASC', array($journal_entry_id))->result();
                        if (empty($line_items) && $has_pin_col) {
                            $line_items = $this->db->query('SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? AND PIN = ? ORDER BY id ASC', array($journal_entry_id, $pin))->result();
                        }
                    }
                }
            }
            if (empty($line_items)) {
                log_message('error', 'post_journal_entry_to_general_ledger: no line items for journal_entry id=' . $journal_entry_id);
                return false;
            }
        }
        $total_debit = 0;
        $total_credit = 0;
        foreach ($line_items as $item) {
            $total_debit += floatval($item->debit);
            $total_credit += floatval($item->credit);
        }
        if (abs($total_debit - $total_credit) > 0.01) {
            log_message('error', 'post_journal_entry_to_general_ledger: entry ' . $journal_entry_id . ' does not balance');
            return false;
        }
        $this->db->trans_start();
        $entry_date = isset($entry->entry_date) ? $entry->entry_date : date('Y-m-d');
        $ledger_entry = array('date' => $entry_date, 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();
        if (!$ledger_entry_id) {
            $this->db->trans_complete();
            return false;
        }
        $ledger = array(
            'journalID' => $journal_id,
            'refferenceID' => $journal_entry_id,
            'entryid' => $ledger_entry_id,
            'date' => $entry_date,
            'linkto' => 'journal_entry.id',
            'fromtable' => 'journal_entry',
            'PIN' => $pin
        );
        $inserted_count = 0;
        foreach ($line_items as $item) {
            $account_info = account_row_info($item->account);
            if (!$account_info) continue;
            $ledger['account'] = $item->account;
            $ledger['debit'] = floatval($item->debit);
            $ledger['credit'] = floatval($item->credit);
            $ledger['description'] = isset($item->description) ? $item->description : (isset($entry->description) ? $entry->description : '');
            $ledger['account_type'] = isset($account_info->account_type) ? $account_info->account_type : 0;
            $ledger['sub_account_type'] = isset($account_info->sub_account_type) ? $account_info->sub_account_type : 0;
            if ($this->db->insert('general_ledger', $ledger)) {
                $inserted_count++;
            }
        }
        $this->db->trans_complete();
        return ($inserted_count > 0 && $this->db->trans_status());
    }

    /**
     * Check if a journal entry (general_journal) has been posted to general ledger.
     * Placed near top of class to avoid scope issues.
     */
    function is_journal_posted($journal_entry_id) {
        $pin = current_user()->PIN;
        $this->db->where('refferenceID', $journal_entry_id);
        $this->db->where('fromtable', 'general_journal');
        $this->db->where('PIN', $pin);
        $count = $this->db->count_all_results('general_ledger');
        return $count > 0;
    }

    /**
     * Post journal entries from general_journal to general_ledger. Placed near top to avoid scope issues.
     */
    function post_journal_to_general_ledger($journal_entry_id = null, $journal_id = 5) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $this->db->select('gje.*');
        $this->db->from('general_journal_entry gje');
        $this->db->join('general_ledger gl', 'gl.refferenceID = gje.id AND gl.fromtable = "general_journal"', 'left');
        $this->db->where('gl.id IS NULL');
        $this->db->where('gje.PIN', $pin);
        if ($journal_entry_id) {
            $this->db->where('gje.id', $journal_entry_id);
        }
        $unposted_entries = $this->db->get()->result();
        if (empty($unposted_entries)) {
            $this->db->trans_complete();
            return false;
        }
        foreach ($unposted_entries as $entry) {
            if (!isset($entry->entryid)) {
                $entry->entryid = $entry->id;
            }
            $line_items = $this->db->where('entryid', $entry->id)->get('general_journal')->result();
            if (empty($line_items)) {
                $line_items = $this->db->where('entryid', $entry->id)->where('PIN', $pin)->get('general_journal')->result();
            }
            if (empty($line_items)) {
                continue;
            }
            $total_debit = 0;
            $total_credit = 0;
            foreach ($line_items as $item) {
                $total_debit += floatval($item->debit);
                $total_credit += floatval($item->credit);
            }
            if (abs($total_debit - $total_credit) > 0.01) {
                log_message('error', 'Journal entry ' . $entry->entryid . ' does not balance.');
                continue;
            }
            $ledger_entry = array('date' => $entry->entrydate, 'PIN' => $pin);
            $this->db->insert('general_ledger_entry', $ledger_entry);
            $ledger_entry_id = $this->db->insert_id();
            if (!$ledger_entry_id) {
                continue;
            }
            $ledger = array(
                'journalID' => $journal_id,
                'refferenceID' => $entry->id,
                'entryid' => $ledger_entry_id,
                'date' => $entry->entrydate,
                'linkto' => 'general_journal.entryid',
                'fromtable' => 'general_journal',
                'PIN' => $pin
            );
            foreach ($line_items as $item) {
                $account_info = account_row_info($item->account);
                if (!$account_info) {
                    continue;
                }
                $ledger['account'] = $item->account;
                $ledger['debit'] = floatval($item->debit);
                $ledger['credit'] = floatval($item->credit);
                $ledger['description'] = isset($item->description) ? $item->description : '';
                $ledger['account_type'] = $account_info->account_type;
                $ledger['sub_account_type'] = isset($account_info->sub_account_type) ? $account_info->sub_account_type : null;
                $this->db->insert('general_ledger', $ledger);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Get journal entry details with line items (general_journal_entry).
     * Placed near top of class to avoid scope issues.
     */
    function get_journal_entry_details($entry_id) {
        $pin = current_user()->PIN;
        $entry = $this->db->where('id', $entry_id)->where('PIN', $pin)->get('general_journal_entry')->row();
        if (!$entry) {
            $entry = $this->db->where('id', $entry_id)->get('general_journal_entry')->row();
        }
        if (!$entry) {
            return FALSE;
        }
        $actual_id = isset($entry->id) ? $entry->id : (isset($entry->entryid) ? $entry->entryid : $entry_id);
        $entry->entryid = $actual_id;
        $sql = "SELECT * FROM general_journal WHERE entryid = ? ORDER BY id ASC";
        $entry->line_items = $this->db->query($sql, array(intval($actual_id)))->result();
        if (empty($entry->line_items) && intval($actual_id) != intval($entry_id)) {
            $entry->line_items = $this->db->query($sql, array(intval($entry_id)))->result();
        }
        if (empty($entry->line_items)) {
            $entry->line_items = $this->db->query($sql, array($actual_id))->result();
        }
        if (empty($entry->line_items) && $actual_id != $entry_id) {
            $entry->line_items = $this->db->query($sql, array($entry_id))->result();
        }
        if (!isset($entry->line_items) || !is_array($entry->line_items)) {
            $entry->line_items = array();
        }
        $entry->is_posted = $this->is_journal_posted($actual_id);
        $entry->total_debit = 0;
        $entry->total_credit = 0;
        foreach ($entry->line_items as $item) {
            $entry->total_debit += floatval($item->debit);
            $entry->total_credit += floatval($item->credit);
        }
        foreach ($entry->line_items as $item) {
            $account_info = account_row_info($item->account);
            $item->account_name = $account_info ? $account_info->name : 'Account not found';
        }
        return $entry;
    }

    /**
     * Get list of unposted journal entries from general_journal_entry
     * @return array List of unposted journal entries with details
     */
    function get_unposted_journal_entries() {
        $pin = current_user()->PIN;
        $this->db->select('gje.*, COUNT(gj.id) as line_count');
        $this->db->from('general_journal_entry gje');
        $this->db->join('general_journal gj', 'gj.entryid = gje.id', 'left');
        $this->db->join('general_ledger gl', 'gl.refferenceID = gje.id AND gl.fromtable = "general_journal"', 'left');
        $this->db->where('gl.id IS NULL');
        $this->db->where('gje.PIN', $pin);
        $this->db->group_by('gje.id');
        $this->db->order_by('gje.entrydate', 'DESC');
        $this->db->order_by('gje.id', 'DESC');
        $results = $this->db->get()->result();
        foreach ($results as $entry) {
            $line_items = $this->db->where('entryid', $entry->id)->get('general_journal')->result();
            if (empty($line_items)) {
                $line_items = $this->db->where('entryid', $entry->id)->where('PIN', $pin)->get('general_journal')->result();
            }
            $entry->entryid = $entry->id;
            $entry->line_count = count($line_items);
            $entry->total_debit = 0.00;
            $entry->total_credit = 0.00;
            $entry->createdby = null;
            foreach ($line_items as $item) {
                $entry->total_debit += floatval($item->debit);
                $entry->total_credit += floatval($item->credit);
                if (empty($entry->createdby) && !empty($item->createdby)) {
                    $entry->createdby = $item->createdby;
                }
            }
            $entry->line_count = isset($entry->line_count) ? intval($entry->line_count) : 0;
            $entry->total_debit = isset($entry->total_debit) ? floatval($entry->total_debit) : 0.00;
            $entry->total_credit = isset($entry->total_credit) ? floatval($entry->total_credit) : 0.00;
            if (!empty($entry->createdby)) {
                $user = $this->db->where('id', $entry->createdby)->get('users')->row();
                $entry->created_by_name = $user ? $user->username : 'Unknown';
            } else {
                $entry->created_by_name = 'Unknown';
            }
        }
        return $results;
    }

    function get_total_savings_amount($key=null, $account_type_filter=null, $status_filter=null) {
        $pin = current_user()->PIN;
        $this->db->select_sum('ma.balance');
        $this->db->from('members_account ma');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
        $this->db->where('ma.PIN', $pin);
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '40' OR ac.account_type = 40 OR ac.account_type = '40')) OR LOWER(sat.name) LIKE '%mso%' OR LOWER(sat.description) LIKE '%mso%')", NULL, FALSE);
            }
        }
        if (!is_null($status_filter) && $status_filter != '') {
            if ($status_filter != 'all') {
                if ($status_filter == '1') {
                    $this->db->where("(ma.status = '1' OR ma.status IS NULL)", NULL, FALSE);
                } else {
                    $this->db->where('ma.status', $status_filter);
                }
            }
        }
        if (!is_null($key) && $key != '') {
            $key_escaped = $this->db->escape_like_str($key);
            $this->db->where("(ma.account LIKE '%{$key_escaped}%' OR ma.member_id LIKE '%{$key_escaped}%' OR ma.RFID = " . $this->db->escape($key) . ")", NULL, FALSE);
        }
        $result = $this->db->get()->row();
        return $result->balance ? $result->balance : 0;
    }

    function is_journal_entry_posted_to_gl($journal_entry_id) {
        $pin = current_user()->PIN;
        $this->db->where('refferenceID', $journal_entry_id);
        $this->db->where('fromtable', 'journal_entry');
        $this->db->where('PIN', $pin);
        $count = $this->db->count_all_results('general_ledger');
        return $count > 0;
    }

    function get_receipt_disbursement_journal_entries() {
        $pin = current_user()->PIN;
        $entries = array();
        $col = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row();
        if (!$col) {
            return $entries;
        }
        $has_pin_col = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'PIN'")->row();
        $sql = "SELECT je.id, je.entry_date, je.description, je.reference_type, je.reference_id, je.createdby, je.PIN, je.created_at 
                FROM journal_entry je
                LEFT JOIN general_ledger gl ON gl.refferenceID = je.id AND gl.fromtable = 'journal_entry' AND gl.PIN = je.PIN
                WHERE je.PIN = ? AND je.reference_type IN ('cash_receipt', 'cash_disbursement') AND gl.id IS NULL
                ORDER BY je.entry_date DESC, je.id DESC";
        $rows = $this->db->query($sql, array($pin))->result();
        if (empty($rows)) {
            return $entries;
        }
        foreach ($rows as $row) {
            $line_items = $this->db->query(
                "SELECT debit, credit FROM journal_entry_items WHERE journal_id = ?",
                array($row->id)
            )->result();
            if (empty($line_items) && $has_pin_col) {
                $line_items = $this->db->query(
                    "SELECT debit, credit FROM journal_entry_items WHERE journal_id = ? AND PIN = ?",
                    array($row->id, $pin)
                )->result();
            }
            $total_debit = 0.00;
            $total_credit = 0.00;
            $line_count = 0;
            if (!empty($line_items)) {
                $line_count = count($line_items);
                foreach ($line_items as $item) {
                    $total_debit += floatval($item->debit);
                    $total_credit += floatval($item->credit);
                }
            }
            $total_debit = floatval($total_debit);
            $total_credit = floatval($total_credit);
            $line_count = intval($line_count);
            $user = $this->db->where('id', $row->createdby)->get('users')->row();
            $entries[] = (object) array(
                'entryid' => $row->id,
                'entrydate' => $row->entry_date,
                'description' => $row->description,
                'total_debit' => $total_debit,
                'total_credit' => $total_credit,
                'line_count' => $line_count,
                'createdby' => $row->createdby,
                'created_by_name' => $user ? $user->username : 'Unknown',
                'entry_source' => $row->reference_type,
                'reference_id' => isset($row->reference_id) ? $row->reference_id : null,
                'is_posted' => $this->is_journal_entry_posted_to_gl($row->id),
            );
        }
        return $entries;
    }

    function account_type($id = null, $account = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($account)) {
            $this->db->where('account', $account);
        }

        $this->db->order_by('account', 'ASC');
        return $this->db->get('account_type');
    }

    function account_type_sub($id = null, $accounttype = null, $sub_account = null) {

        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($accounttype)) {
            $this->db->where('accounttype', $accounttype);
        }
        if (!is_null($sub_account)) {
            $this->db->where('sub_account', $sub_account);
        }

        $this->db->order_by('sub_account', 'ASC');
        return $this->db->get('account_type_sub');
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

    function last_chart_account($pin, $accounttype, $sub_account) {
        $this->db->where('PIN', $pin);
        $this->db->where('accounttype', $accounttype);
        $this->db->where('sub_account', $sub_account);
        return $this->db->get('account_inc')->row();
    }

    function create_chart_account($data) {

        $pin = $data['PIN'];
        $accounttype = $data['account_type'];
        $sub_account = $data['sub_account_type'];

        $last_account = $this->last_chart_account($pin, $accounttype, $sub_account);

        // increment last account by 1
        $this->db->where('PIN', $pin);
        $this->db->where('accounttype', $accounttype);
        $this->db->where('sub_account', $sub_account);
        $this->db->set('last_account', "last_account+1", FALSE);
        $this->db->update('account_inc');

        $account_start = (string) $last_account->accounttype . $last_account->sub_account;
        $last_part = format_lastpart_account($last_account->last_account);
        $account_no = $account_start . $last_part;
        //Disabled auto increment of account number - 11/22/2025
        //$data['account'] = (int) $account_no;


        $this->db->insert('account_chart', $data);

        return $account_no;
    }

    function edit_chart_account($create_account, $id) {
        return $this->db->update('account_chart', $create_account, array('id' => $id));
    }

    function delete_chart_account($id) {
        // Get account number before deletion
        $account_info = $this->account_chart($id, null)->row();
        
        if (!$account_info) {
            return false;
        }
        
        $account_number = $account_info->account;
        $pin = current_user()->PIN;
        
        // Check if account has transactions in general_ledger
        $this->db->where('account', $account_number);
        $this->db->where('PIN', $pin);
        $ledger_count = $this->db->count_all_results('general_ledger');
        
        // Check if account has transactions in general_journal
        $this->db->where('account', $account_number);
        $journal_count = $this->db->count_all_results('general_journal');
        
        // If account has transactions, cannot delete
        if ($ledger_count > 0 || $journal_count > 0) {
            return false;
        }
        
        // Safe to delete
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        return $this->db->delete('account_chart');
    }
    
    function check_account_has_transactions($account_number) {
        $pin = current_user()->PIN;
        
        // Check general_ledger
        $this->db->where('account', $account_number);
        $this->db->where('PIN', $pin);
        $ledger_count = $this->db->count_all_results('general_ledger');
        
        // Check general_journal
        $this->db->where('account', $account_number);
        $journal_count = $this->db->count_all_results('general_journal');
        
        return ($ledger_count > 0 || $journal_count > 0);
    }

    /*
      //edit saccoss account,
      function edit_saccoss_account($data, $id) {
      return $this->db->update('saccos_accounts', $data, array('id' => $id));
      }
     */

    function account_typelist($id = null,$account_type=null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
if (!is_null($account_type)) {
            $this->db->where('account', $account_type);
        }

        return $this->db->get('account_type');
    }

    function account_chart_by_accounttype($account_type = null) {
        $return = array();

        $account_type_list = array();
        if (is_array($account_type)) {
            foreach ($account_type as $key => $value) {
                $account_type_list[] = $this->account_type(null,$value)->row();
            }
        } else if (!is_null($account_type)) {
            $account_type_list[] = $this->account_typelist(null,$account_type)->row();
        } else {
            $account_type_list = $this->account_typelist()->result();
        }
        foreach ($account_type_list as $key => $value) {
            $return[$value->id]['info'] = $value;
            $return[$value->id]['data'] = $this->account_chart(null, null, $value->account)->result();
        }
        return $return;
    }

    function account_chart($id = null, $account = null, $account_type = null, $parent_account = null) {
        $this->db->where('PIN', current_user()->PIN);
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

        $this->db->order_by('account', 'ASC');
        return $this->db->get('account_chart');
    }

    function account_cash_received() {
$pin=current_user()->PIN;
        $sql = "SELECT * FROM account_chart where (account='1010001' OR account='1010003') AND PIN='$pin'";
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

    function create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment = '', $cheque_num = '', $posted_date='',$old_savings_account_no='') {

        $account = $this->db->get('auto_inc')->row()->saving;

        // increatent 1 next PIN
        $this->db->set('saving', 'saving+1', FALSE);
        $this->db->update('auto_inc');
        

        //create account now
        $new_account = array(
            'account' => $account,
            'RFID' => $PID,
            'member_id' => $member_id,
            'old_members_acct' => $old_savings_account_no,
            'account_cat' => $account_type,
            'virtual_balance' => $virtual_balance,
            'createdby' => current_user()->id,
            'tablename' => 'members',
            'PIN' => current_user()->PIN,
        );

        if($posted_date!=''){
            $new_account_date = array(
                'createdon' => $posted_date,
            );
            $new_account = array_merge($new_account, $new_account_date);
        }

        if ($comment == '' || is_null($comment)) {
            $comment = 'Opening account';
        }

        $create_new = $this->db->insert('members_account', $new_account);
        if ($create_new) {
            $amount = $balance + $virtual_balance;
            $systemcomment = 'OPEN ACCOUNT, NORMAL DEPOSIT';
            $customer_name = $this->saving_account_name($account);
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $PID, $systemcomment, $virtual_balance, $posted_date);
        }

        return FALSE;
    }

    /**
     * Create account with beginning balance (for admin only)
     * Similar to create_account but uses BEGINNING BALANCE system comment
     */
    function create_account_with_beginning_balance($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment = '', $cheque_num = '', $posted_date='',$old_savings_account_no='') {

        $account = $this->db->get('auto_inc')->row()->saving;

        // increment 1 next PIN
        $this->db->set('saving', 'saving+1', FALSE);
        $this->db->update('auto_inc');
        

        //create account now
        $new_account = array(
            'account' => $account,
            'RFID' => $PID,
            'member_id' => $member_id,
            'old_members_acct' => $old_savings_account_no,
            'account_cat' => $account_type,
            'virtual_balance' => $virtual_balance,
            'createdby' => current_user()->id,
            'tablename' => 'members',
            'PIN' => current_user()->PIN,
        );

        if($posted_date!=''){
            $new_account_date = array(
                'createdon' => $posted_date,
            );
            $new_account = array_merge($new_account, $new_account_date);
        }

        if ($comment == '' || is_null($comment)) {
            $comment = 'Beginning Balance';
        }

        $create_new = $this->db->insert('members_account', $new_account);
        if ($create_new) {
            $amount = $balance + $virtual_balance;
            $systemcomment = 'BEGINNING BALANCE'; // Changed from OPEN ACCOUNT
            $customer_name = $this->saving_account_name($account);
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $PID, $systemcomment, $virtual_balance, $posted_date);
        }

        return FALSE;
    }

    /**
     * Get savings account balance by member
     */
    function saving_account_balance_by_member($pid, $member_id, $account_type) {
        $pin = current_user()->PIN;
        $this->db->where('RFID', $pid);
        $this->db->where('member_id', $member_id);
        $this->db->where('account_cat', $account_type);
        $this->db->where('PIN', $pin);
        return $this->db->get('members_account')->row();
    }

    function add_saving_transaction($trans_type = null, $account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $pid = null, $posted_date='', $refno = '') {
        if (is_null($trans_type) || is_null($account) || $amount == 0 || is_null($paymethod)) {
            return false;
        }

        if ($trans_type == 'CR') {
            //deposit
            $systemcomment = 'NORMAL DEPOSIT';
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $pid, $systemcomment,0, $posted_date, $refno);
        } else if ($trans_type == 'INT') {
            //interest
            $systemcomment = 'INTEREST';
            return $this->credit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $pid, $systemcomment,0, $posted_date, $refno);
        } else if ($trans_type == 'DR') {
            //with draw
            $systemcomment = 'NORMAL WITHDRAWAL';
            return $this->debit($account, $amount, $paymethod, $comment, $cheque_num, $customer_name, $systemcomment, $pid,$posted_date, $refno);
        }


        return FALSE;
    }

    function saving_account_balance($account) {
        // Ensure account is treated as string for consistent comparison
        // This handles cases where account might be numeric like "18" or string like "Account#1"
        $account_str = trim((string)$account);
        
        // Compare account - use string comparison to handle both numeric and string accounts
        // CodeIgniter will properly escape this value
        $this->db->where('account', $account_str);
        return $this->db->get('members_account')->row();
    }

    /**
     * Get cash/bank account or adjustment account based on payment method for savings transactions
     * 
     * @param string $payment_method Payment method (Cash, CHEQUE, Bank Transfer, ADJUSTMENT, etc.)
     * @return string|null Account code or null if not found
     */
    private function get_cash_account_for_savings($payment_method) {
        $pin = current_user()->PIN;
        $payment_method_upper = strtoupper(trim($payment_method));
        
        // For ADJUSTMENT (beginning balances), use an adjustment/equity account instead of cash
        if ($payment_method_upper == 'ADJUSTMENT') {
            // Try to find an adjustment account or opening balance equity account
            $this->db->where('PIN', $pin);
            
            // Search for adjustment-related accounts (Equity or Asset type for adjustments)
            $adjustment_names = array('Adjustment', 'Opening Balance', 'Beginning Balance', 'Equity', 'Retained Earnings');
            $where_clause = "(";
            foreach ($adjustment_names as $index => $name) {
                if ($index > 0) {
                    $where_clause .= " OR ";
                }
                $escaped_name = $this->db->escape_like_str($name);
                $where_clause .= "name LIKE '%" . $escaped_name . "%'";
            }
            $where_clause .= ")";
            
            $this->db->where($where_clause, NULL, FALSE);
            // Equity accounts (type 30 or 40) or Adjustment asset accounts
            $this->db->where_in('account_type', array(30, 40, 1));
            $this->db->order_by('account', 'ASC');
            $this->db->limit(1);
            
            $account = $this->db->get('account_chart')->row();
            
            if (!$account) {
                // Fallback: try to find any equity account
                $this->db->where('PIN', $pin);
                $this->db->where_in('account_type', array(30, 40)); // Equity accounts
                $this->db->order_by('account', 'ASC');
                $this->db->limit(1);
                $account = $this->db->get('account_chart')->row();
            }
            
            if (!$account) {
                log_message('error', 'No adjustment/equity account found for ADJUSTMENT payment method');
                return null;
            }
            
            log_message('debug', 'Using adjustment account: ' . $account->account . ' (' . $account->name . ') for ADJUSTMENT payment method');
            return $account->account;
        }
        
        // Map payment methods to account names (case-insensitive matching) for cash transactions
        $account_mapping = array(
            'CASH' => 'Cash',
            'CHEQUE' => 'Bank',
            'BANK TRANSFER' => 'Bank',
            'BANK' => 'Bank',
            'MOBILE MONEY' => 'Mobile Money',
            'MOBILE' => 'Mobile Money',
            'MPESA' => 'Mobile Money',
            'AIRTEL MONEY' => 'Mobile Money',
            'TIGO PESA' => 'Mobile Money'
        );
        
        $account_name = 'Cash'; // Default
        foreach ($account_mapping as $key => $value) {
            if (strpos($payment_method_upper, $key) !== FALSE) {
                $account_name = $value;
                break;
            }
        }
        
        // Find the account - check for asset type 1 or 10000 (Asset accounts)
        // Build WHERE clause manually for compatibility with older CodeIgniter versions
        $this->db->where('PIN', $pin);
        
        // Build the OR conditions manually with proper escaping
        $escaped_account_name = $this->db->escape_like_str($account_name);
        $where_clause = "(name LIKE '%" . $escaped_account_name . "%'";
        
        // Also try to find accounts starting with 10 (Asset accounts in chart of accounts)
        // Note: escape() adds quotes, so we use it directly
        if ($account_name == 'Cash') {
            $where_clause .= " OR account = " . $this->db->escape('1010001'); // Default cash account
            $where_clause .= " OR account LIKE " . $this->db->escape('10100%'); // Cash accounts typically start with 10100
        } elseif ($account_name == 'Bank') {
            $where_clause .= " OR account = " . $this->db->escape('1010003'); // Default bank account
            $where_clause .= " OR account LIKE " . $this->db->escape('10100%'); // Bank accounts typically start with 10100
        }
        
        $where_clause .= ")";
        $this->db->where($where_clause, NULL, FALSE);
        $this->db->where_in('account_type', array(1, 10000)); // Asset type
        $this->db->order_by('account', 'ASC');
        $this->db->limit(1);
        
        $account = $this->db->get('account_chart')->row();
        
        if (!$account) {
            log_message('error', 'No cash/bank account found for payment method: ' . $payment_method . ', searched for: ' . $account_name);
            return null;
        }
        
        return $account->account;
    }

    /**
     * Get interest expense account for savings interest GL posting
     * Searches account_chart for expense account (account_type 50 or 70000) with "Interest" in name
     *
     * @return string|null GL account code or null if not found
     */
    private function get_interest_expense_account_for_savings() {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $escaped = $this->db->escape_like_str('Interest');
        $this->db->where("(name LIKE '%" . $escaped . "%' OR description LIKE '%" . $escaped . "%')", NULL, FALSE);
        $this->db->where_in('account_type', array(50, 70000)); // Expense account types
        $this->db->order_by('account', 'ASC');
        $this->db->limit(1);
        $account = $this->db->get('account_chart')->row();
        if (!$account) {
            log_message('error', 'No interest expense account found in chart of accounts. Create an account with "Interest" in name and expense type (50/70000).');
            return null;
        }
        return $account->account;
    }

    /**
     * Post savings transaction to General Ledger
     * Handles: account opening, beginning balance, deposit, withdrawal, interest
     *
     * @param string $account Savings account number
     * @param float $amount Transaction amount
     * @param string $paymethod Payment method
     * @param string $account_cat Savings account type (from saving_account_type.account)
     * @param string $receipt Receipt number
     * @param string $trans_date Transaction date
     * @param string $pid Member PID
     * @param string $member_id Member ID
     * @param string $customer_name Customer name
     * @param string $systemcomment Transaction type: OPEN ACCOUNT, BEGINNING BALANCE, NORMAL DEPOSIT, NORMAL WITHDRAWAL, INTEREST
     * @return bool True if successful, False otherwise
     */
    function post_savings_to_gl($account, $amount, $paymethod, $account_cat, $receipt, $trans_date, $pid, $member_id, $customer_name = '', $systemcomment = '') {
        $pin = current_user()->PIN;
        
        // Skip if amount is zero
        if (floatval($amount) == 0) {
            log_message('debug', 'Skipping GL posting for savings account ' . $account . ': Zero amount');
            return true; // Not an error, just nothing to post
        }
        
        // Check if already posted to avoid duplicates
        $this->db->where('refferenceID', $receipt);
        $this->db->where('fromtable', 'savings_transaction');
        $this->db->where('PIN', $pin);
        $existing_entry = $this->db->get('general_ledger')->row();
        
        if (!empty($existing_entry)) {
            log_message('debug', 'Savings account GL entry already exists for receipt: ' . $receipt);
            return true; // Already posted
        }
        
        // Get savings account type to retrieve account_setup (GL liability account)
        $savings_account_type = $this->saving_account_list(null, $account_cat)->row();
        
        if (!$savings_account_type || empty($savings_account_type->account_setup)) {
            log_message('error', 'Savings account type not found or account_setup not configured for account_cat: ' . $account_cat);
            return false; // Cannot post without account_setup
        }
        
        $savings_liability_account = $savings_account_type->account_setup;
        $savings_account_info = account_row_info($savings_liability_account);
        
        if (!$savings_account_info) {
            log_message('error', 'Savings liability account not found in chart of accounts: ' . $savings_liability_account);
            return false;
        }
        
        $sys_upper = strtoupper(trim($systemcomment));
        $is_withdrawal = (strpos($sys_upper, 'NORMAL WITHDRAWAL') !== FALSE);
        $is_interest = (strpos($sys_upper, 'INTEREST') !== FALSE);
        
        // For INTEREST: debit = interest expense account; for deposit/withdrawal: debit/credit = cash or liability
        if ($is_interest) {
            $debit_account = $this->get_interest_expense_account_for_savings();
        } else {
            $debit_account = $this->get_cash_account_for_savings($paymethod);
        }
        
        if (!$debit_account) {
            log_message('error', 'Debit account not found for payment method: ' . $paymethod . ', type: ' . $systemcomment);
            return false;
        }
        
        $debit_account_info = account_row_info($debit_account);
        
        if (!$debit_account_info) {
            log_message('error', 'Debit account not found in chart of accounts: ' . $debit_account);
            return false;
        }
        
        // Determine description prefix based on transaction type
        $is_adjustment = (strtoupper(trim($paymethod)) == 'ADJUSTMENT');
        if ($is_withdrawal) {
            $description_prefix = 'Savings Withdrawal';
        } elseif ($is_interest) {
            $description_prefix = 'Savings Interest';
        } elseif ($is_adjustment) {
            $description_prefix = 'Savings Beginning Balance Adjustment';
        } elseif (strpos($sys_upper, 'OPEN ACCOUNT') !== FALSE) {
            $description_prefix = 'Savings Account Opening';
        } elseif (strpos($sys_upper, 'BEGINNING BALANCE') !== FALSE) {
            $description_prefix = 'Savings Beginning Balance';
        } else {
            $description_prefix = 'Savings Deposit';
        }
        
        // Check if Journal ID 9 exists, if not use 5 as fallback
        $this->db->where('id', 9);
        $journal_check = $this->db->get('journal')->row();
        $journal_id = ($journal_check) ? 9 : 5; // Use 9 for Savings Journal, fallback to 5 for Manual Journal
        
        // Start transaction
        $this->db->trans_start();
        
        try {
            // Create general ledger entry header
            $ledger_entry = array(
                'date' => $trans_date,
                'PIN' => $pin
            );
            $this->db->insert('general_ledger_entry', $ledger_entry);
            $ledger_entry_id = $this->db->insert_id();
            
            if (!$ledger_entry_id) {
                log_message('error', 'Failed to create general_ledger_entry for savings account: ' . $account);
                $this->db->trans_complete();
                return false;
            }
            
            // Prepare base ledger data with appropriate description (is_adjustment already defined above)
            $description = $description_prefix . ' - ' . ($customer_name ? $customer_name : 'Member ' . $member_id) . ' (Account: ' . $account . ', Receipt: ' . $receipt . ')';
            
            $ledger_base = array(
                'journalID' => $journal_id,
                'refferenceID' => $receipt, // Reference to savings_transaction.receipt
                'entryid' => $ledger_entry_id,
                'date' => $trans_date,
                'description' => $description,
                'linkto' => 'savings_transaction.receipt',
                'fromtable' => 'savings_transaction',
                'PID' => $pid,
                'member_id' => $member_id,
                'PIN' => $pin,
            );
            
            // Deposit/Interest: Debit cash/expense, Credit liability
            // Withdrawal: Debit liability, Credit cash
            if ($is_withdrawal) {
                // Entry 1: Debit Savings Liability (decrease liability)
                $ledger_debit = $ledger_base;
                $ledger_debit['account'] = $savings_liability_account;
                $ledger_debit['debit'] = floatval($amount);
                $ledger_debit['credit'] = 0;
                $ledger_debit['account_type'] = $savings_account_info->account_type;
                $ledger_debit['sub_account_type'] = isset($savings_account_info->sub_account_type) ? $savings_account_info->sub_account_type : null;
                
                $this->db->insert('general_ledger', $ledger_debit);
                
                if ($this->db->affected_rows() <= 0) {
                    $db_error = $this->db->error();
                    log_message('error', 'Failed to insert debit entry to general_ledger: ' . json_encode($db_error));
                    $this->db->trans_complete();
                    return false;
                }
                
                // Entry 2: Credit Cash/Bank (cash goes out)
                $ledger_credit = $ledger_base;
                $ledger_credit['account'] = $debit_account;
                $ledger_credit['debit'] = 0;
                $ledger_credit['credit'] = floatval($amount);
                $ledger_credit['account_type'] = $debit_account_info->account_type;
                $ledger_credit['sub_account_type'] = isset($debit_account_info->sub_account_type) ? $debit_account_info->sub_account_type : null;
            } else {
                // Deposit/Interest: Debit cash or expense, Credit liability
                $ledger_debit = $ledger_base;
                $ledger_debit['account'] = $debit_account;
                $ledger_debit['debit'] = floatval($amount);
                $ledger_debit['credit'] = 0;
                $ledger_debit['account_type'] = $debit_account_info->account_type;
                $ledger_debit['sub_account_type'] = isset($debit_account_info->sub_account_type) ? $debit_account_info->sub_account_type : null;
                
                $this->db->insert('general_ledger', $ledger_debit);
                
                if ($this->db->affected_rows() <= 0) {
                    $db_error = $this->db->error();
                    log_message('error', 'Failed to insert debit entry to general_ledger: ' . json_encode($db_error));
                    $this->db->trans_complete();
                    return false;
                }
                
                $ledger_credit = $ledger_base;
                $ledger_credit['account'] = $savings_liability_account;
                $ledger_credit['debit'] = 0;
                $ledger_credit['credit'] = floatval($amount);
                $ledger_credit['account_type'] = $savings_account_info->account_type;
                $ledger_credit['sub_account_type'] = isset($savings_account_info->sub_account_type) ? $savings_account_info->sub_account_type : null;
            }
            
            $this->db->insert('general_ledger', $ledger_credit);
            
            if ($this->db->affected_rows() <= 0) {
                $db_error = $this->db->error();
                log_message('error', 'Failed to insert credit entry to general_ledger: ' . json_encode($db_error));
                $this->db->trans_complete();
                return false;
            }
            
            // Complete transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Transaction failed while posting savings account to GL: ' . $account);
                return false;
            }
            
            $type_label = $is_withdrawal ? 'Withdrawal' : ($is_interest ? 'Interest' : ($is_adjustment ? 'Adjustment' : 'Deposit/Opening'));
            log_message('info', 'Savings posted to GL: Account ' . $account . ', Receipt ' . $receipt . ', Amount ' . $amount . ', Type: ' . $type_label);
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Exception while posting savings account to GL: ' . $e->getMessage());
            $this->db->trans_complete();
            return false;
        }
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

    function credit($account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $pid = null, $systemcomment = '', $start_up = 0, $posted_date='', $refno = '') {
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
        if($posted_date!=''){
            $this->db->set('trans_date', $posted_date);
        }
        $pid | $pid = $account_info->RFID;
        $this->db->set('PID', $pid);
        $this->db->set('account_cat', $account_info->account_cat);
        $this->db->set('comment', $comment);
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('customer_name', $customer_name);
        $this->db->set('refno', $refno);
        $this->db->set('PIN', $pin);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('savings_transaction');
        if ($insert) {
            // Auto-post to GL for: opening balance, beginning balance, normal deposit, interest
            $post_to_gl = (strpos($systemcomment, 'OPEN ACCOUNT') !== FALSE || strpos($systemcomment, 'BEGINNING BALANCE') !== FALSE
                || strpos($systemcomment, 'NORMAL DEPOSIT') !== FALSE || strpos($systemcomment, 'INTEREST') !== FALSE);
            if ($post_to_gl) {
                // Get member_id for GL posting
                $member_id = isset($account_info->member_id) ? $account_info->member_id : '';
                
                // Post to General Ledger
                $gl_post_result = $this->post_savings_to_gl($account, $amount, $paymethod, $account_info->account_cat, $receipt, $posted_date ? $posted_date : date('Y-m-d'), $pid, $member_id, $customer_name, $systemcomment);
                
                if (!$gl_post_result) {
                    log_message('error', 'Savings account GL posting failed for account: ' . $account . ', receipt: ' . $receipt . ', type: ' . $systemcomment);
            }
            
            return $receipt;
        }

        return FALSE;
    }

    function debit($account = null, $amount = 0, $paymethod = null, $comment = '', $cheque_num = '', $customer_name = '', $systemcomment = '', $pid = null, $posted_date='', $refno = '') {
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
        if($posted_date!=''){
            $this->db->set('trans_date', $posted_date);
        }
        $pid | $pid = $account_info->RFID;
        $this->db->set('PID', $pid);
        $this->db->set('account_cat', $account_info->account_cat);
        $this->db->set('customer_name', $customer_name);
        $this->db->set('comment', $comment);
        $this->db->set('system_comment', $systemcomment);
        $this->db->set('refno', $refno);
        $this->db->set('PIN', $pin);
        $this->db->set('createdby', $this->session->userdata('user_id'));
        $insert = $this->db->insert('savings_transaction');
        if ($insert) {
            // Auto-post withdrawals to GL
            if (strpos($systemcomment, 'NORMAL WITHDRAWAL') !== FALSE) {
                $member_id = isset($account_info->member_id) ? $account_info->member_id : '';
                $pid_val = !empty($pid) ? $pid : $account_info->RFID;
                $gl_post_result = $this->post_savings_to_gl($account, $amount, $paymethod, $account_info->account_cat, $receipt, $posted_date ? $posted_date : date('Y-m-d'), $pid_val, $member_id, $customer_name, $systemcomment);
                if (!$gl_post_result) {
                    log_message('error', 'Savings withdrawal GL posting failed: account ' . $account . ', receipt ' . $receipt);
                }
            }
            return $receipt;
        }

        return FALSE;
    }

    function get_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        $data = $this->db->get('savings_transaction')->row();
        if ($data) {
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
        $this->db->where('PIN', $pin);
        return $this->db->get('sales_quote')->result();
    }

    function sales_invoice_list() {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->order_by('status', 'ASC');
        return $this->db->get('sales_invoice')->result();
    }

    function enter_journal($main_array, $array_items, $auto_post = false) {
        $pin = current_user()->PIN;
        log_message('debug', 'enter_journal called with ' . count($array_items) . ' line items. PIN=' . $pin);
        
        // Journal entry tables are now excluded from activity logging (see MY_DB_mysqli_driver.php)
        // So we don't need to disable logging - it won't interfere
        
        $this->db->trans_start();

        // Add PIN to main_array if not present
        if (!isset($main_array['PIN'])) {
            $main_array['PIN'] = $pin;
        }

        //prepare journal entry (saved as draft/unposted)
        log_message('debug', 'Inserting journal entry header with PIN=' . (isset($main_array['PIN']) ? $main_array['PIN'] : 'NOT SET'));
        
        // Use raw SQL INSERT like test script (more reliable in transactions)
        $entrydate = $this->db->escape($main_array['entrydate']);
        $description = $this->db->escape($main_array['description']);
        $pin_value = isset($main_array['PIN']) ? $this->db->escape($main_array['PIN']) : 'NULL';
        
        $insert_sql = "INSERT INTO general_journal_entry (entrydate, description, PIN) VALUES ($entrydate, $description, $pin_value)";
        
        $header_insert_result = $this->db->query($insert_sql);
        $header_affected = $this->db->affected_rows();
        log_message('debug', 'Header insert result: ' . ($header_insert_result ? 'SUCCESS' : 'FAILED') . ', affected_rows=' . $header_affected);
        
        // Check for MySQL error message if available
        if (isset($this->db->conn_id) && method_exists($this->db->conn_id, 'error') && !empty($this->db->conn_id->error)) {
            log_message('error', 'MySQL error after header insert: ' . $this->db->conn_id->error);
            $this->db->trans_complete();
            return FALSE;
        }
        
        // Check if header insert succeeded
        if (!$header_insert_result || $header_affected != 1) {
            log_message('error', 'Header insert failed: result=' . ($header_insert_result ? 'TRUE' : 'FALSE') . ', affected_rows=' . $header_affected);
            $this->db->trans_complete();
            return FALSE;
        }
        
        // Check transaction status after header insert
        if ($this->db->_trans_status === FALSE) {
            log_message('error', 'Transaction status is FALSE after header insert');
            $this->db->trans_complete();
            return FALSE;
        }
        
        // Get insert ID - use LAST_INSERT_ID() directly like test script (more reliable in transactions)
        $jid = $this->db->insert_id();
        log_message('debug', 'insert_id() returned: ' . $jid);
        if (!$jid || $jid == 0) {
            $last_id_result = $this->db->query("SELECT LAST_INSERT_ID() as id")->row();
            if ($last_id_result && $last_id_result->id > 0) {
                $jid = $last_id_result->id;
                log_message('debug', 'LAST_INSERT_ID() returned: ' . $jid);
            }
        }
        
        // Verify header exists INSIDE transaction before proceeding (critical check)
        // Use the same connection and raw SQL to ensure we're querying the same transaction
        if ($jid && $jid > 0) {
            // Try multiple verification methods
            $verify_header_inside_trans = $this->db->query("SELECT id, description, PIN FROM general_journal_entry WHERE id = ?", array(intval($jid)))->row();
            
            // Try querying by description and date (what we just inserted)
            $verify_by_desc = $this->db->query("SELECT id, description, PIN FROM general_journal_entry WHERE PIN = ? AND description = ? AND entrydate = ? ORDER BY id DESC LIMIT 1", 
                array($pin, $main_array['description'], $main_array['entrydate']))->row();
            
            if ($verify_header_inside_trans) {
                log_message('debug', 'Header verification INSIDE transaction: Found id=' . $verify_header_inside_trans->id);
            } else if ($verify_by_desc && $verify_by_desc->id == $jid) {
                log_message('debug', 'Header verification INSIDE transaction (by description): Found id=' . $verify_by_desc->id);
                // Update jid to match what we found
                $jid = $verify_by_desc->id;
            } else {
                log_message('error', 'CRITICAL: Header with id=' . $jid . ' does NOT exist INSIDE transaction!');
                log_message('error', '  - Direct query by ID: ' . ($verify_header_inside_trans ? 'FOUND' : 'NOT FOUND'));
                log_message('error', '  - Query by description: ' . ($verify_by_desc ? 'FOUND id=' . $verify_by_desc->id : 'NOT FOUND'));
                
                // Check if there's a trigger or constraint issue
                $check_triggers = $this->db->query("SHOW TRIGGERS LIKE 'general_journal_entry'")->result();
                if ($check_triggers) {
                    log_message('error', '  - Found triggers on general_journal_entry table: ' . count($check_triggers));
                }
                
                $this->db->trans_complete();
                return FALSE;
            }
        }
        
        log_message('debug', 'After header insert, journal entry ID: ' . $jid);
        
        // If we still don't have an ID, the insert likely failed - check error and rollback
        if (!$jid || $jid == 0) {
            log_message('error', 'CRITICAL: Failed to get journal entry ID after insert. Transaction will be rolled back.');
            $this->db->trans_complete();
            return FALSE;
        }
        
        log_message('debug', 'Using journal entry ID: ' . $jid . ' for line items');

        // Check if PIN column exists in general_journal table (once, before the loop)
        // Use raw query but cache result to avoid repeated queries in transaction
        $has_pin_column = false;
        static $pin_column_cache = null;
        if ($pin_column_cache === null) {
            try {
                $column_check = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'");
                $has_pin_column = ($column_check && $column_check->num_rows() > 0);
                $pin_column_cache = $has_pin_column;
                log_message('debug', 'PIN column check: ' . ($has_pin_column ? 'EXISTS' : 'DOES NOT EXIST'));
            } catch (Exception $e) {
                log_message('error', 'Could not check for PIN column: ' . $e->getMessage());
                $pin_column_cache = false;
            }
        } else {
            $has_pin_column = $pin_column_cache;
        }
        
        log_message('debug', 'Starting to insert ' . count($array_items) . ' line items for journal entry ID: ' . $jid);

        // Save journal line items
        $items_inserted = 0;
        foreach ($array_items as $key => $value) {
            // Ensure entryid is set and is an integer - CRITICAL: must match the header's id exactly
            $value['entryid'] = intval($jid);
            
            // Only add PIN if column exists and value not already set
            if ($has_pin_column) {
                if (!isset($value['PIN']) || empty($value['PIN'])) {
                    $value['PIN'] = $pin;
                }
            } else {
                // Remove PIN from array if column doesn't exist
                unset($value['PIN']);
            }
            
            // Ensure required fields are present
            if (!isset($value['entrydate']) && isset($main_array['entrydate'])) {
                $value['entrydate'] = $main_array['entrydate'];
            }
            
            $insert_result = $this->db->insert('general_journal', $value);
            $affected_rows = $this->db->affected_rows();
            
            // Check for errors or if insert failed
            if (!$insert_result || $affected_rows != 1) {
                $mysql_error = '';
                if (isset($this->db->conn_id) && method_exists($this->db->conn_id, 'error')) {
                    $mysql_error = $this->db->conn_id->error;
                }
                log_message('error', 'Failed to insert journal line item for entryid: ' . $jid . '. MySQL error: ' . $mysql_error . '. Affected rows: ' . $affected_rows . '. Data: ' . json_encode($value));
                
                // Check transaction status after failed insert
                if ($this->db->_trans_status === FALSE) {
                    log_message('error', 'Transaction status set to FALSE after failed line item insert');
                    break;
                }
                
                // Try again without PIN if error occurred and we were using PIN
                if ($has_pin_column && isset($value['PIN'])) {
                    unset($value['PIN']);
                    log_message('debug', 'Retrying insert without PIN column');
                    $insert_result = $this->db->insert('general_journal', $value);
                    $affected_rows = $this->db->affected_rows();
                    if ($insert_result && $affected_rows == 1) {
                        $items_inserted++;
                        log_message('debug', 'Inserted journal line item (without PIN): entryid=' . $jid . ', account=' . (isset($value['account']) ? $value['account'] : 'N/A'));
                        continue;
                    } else {
                        $mysql_error = '';
                        if (isset($this->db->conn_id) && method_exists($this->db->conn_id, 'error')) {
                            $mysql_error = $this->db->conn_id->error;
                        }
                        log_message('error', 'Retry without PIN also failed. MySQL error: ' . $mysql_error . ', Affected rows: ' . $affected_rows);
                    }
                }
                
                // Still failed - break out of loop, transaction will rollback on trans_complete()
                log_message('error', 'Failed to insert line item after retry. Will rollback transaction.');
                break; // Exit loop - transaction will be rolled back when we check below
            } else {
                $items_inserted++;
                log_message('debug', 'Inserted journal line item: entryid=' . $jid . ', account=' . (isset($value['account']) ? $value['account'] : 'N/A') . ', affected_rows=' . $affected_rows);
            }
        }
        
        // Check if all items were inserted successfully before completing transaction
        if ($items_inserted != count($array_items)) {
            log_message('error', 'Journal entry ' . $jid . ': Failed to insert all line items. Expected: ' . count($array_items) . ', Inserted: ' . $items_inserted);
            // Force rollback by marking transaction as failed
            $this->db->_trans_status = FALSE;
            $this->db->trans_complete();
            return FALSE;
        }
        
        // Log insertion success - verification will happen AFTER commit (like test script)
        log_message('debug', 'Journal entry ' . $jid . ': Successfully inserted ' . $items_inserted . ' line items. Committing transaction...');

        // Only auto-post if explicitly requested (for backward compatibility)
        if ($auto_post) {
            $post_result = $this->post_journal_to_general_ledger($jid, 5);
            if (!$post_result) {
                $this->db->trans_complete();
                log_message('error', 'Journal entry ' . $jid . ' created but auto-posting failed');
                return FALSE;
            }
        }

        // Check transaction status BEFORE completing (CodeIgniter checks this internally)
        $status_before_complete = $this->db->_trans_status;
        
        // Check for any MySQL errors before completing
        if (isset($this->db->conn_id) && method_exists($this->db->conn_id, 'error') && !empty($this->db->conn_id->error)) {
            log_message('error', 'MySQL error before trans_complete(): ' . $this->db->conn_id->error);
        }
        
        $complete_result = $this->db->trans_complete();
        $transaction_status = $this->db->trans_status();
        
        if ($transaction_status === FALSE) {
            log_message('error', 'Transaction FAILED and was rolled back');
        } else {
            log_message('info', 'Journal entry ' . $jid . ' transaction completed successfully');
        }

        if ($transaction_status === FALSE) {
            log_message('error', 'Journal entry creation failed - transaction rolled back. Entry ID ' . $jid . ' and its line items were NOT saved.');
            // Check MySQL error if available
            if (isset($this->db->conn_id) && method_exists($this->db->conn_id, 'error') && !empty($this->db->conn_id->error)) {
                log_message('error', 'MySQL error: ' . $this->db->conn_id->error);
            }
            return FALSE;
        }
        
        // Verify entry header exists after commit (like test script does)
        $header_check_sql = "SELECT * FROM general_journal_entry WHERE id = ? LIMIT 1";
        $header_check = $this->db->query($header_check_sql, array(intval($jid)))->row();
        if (!$header_check) {
            log_message('error', 'CRITICAL: Entry header with id=' . $jid . ' does not exist after transaction commit! Transaction may have rolled back silently.');
            return FALSE;
        }
        
        // Verify items were actually inserted after transaction commits (simple query like test script)
        $verify_sql = "SELECT COUNT(*) as cnt FROM general_journal WHERE entryid = ?";
        $verify_result = $this->db->query($verify_sql, array(intval($jid)))->row();
        $verify_count = $verify_result ? intval($verify_result->cnt) : 0;
        
        if ($verify_count != $items_inserted) {
            log_message('error', 'Journal entry ' . $jid . ' verification failed: Expected ' . $items_inserted . ' items, found ' . $verify_count . ' after commit');
            // Still return the ID - entry was created, but item count mismatch
        } else {
            log_message('info', 'Journal entry ' . $jid . ' created successfully with ' . $verify_count . ' line items (posted: ' . ($auto_post ? 'yes' : 'no - requires approval') . ')');
        }

        return $jid;
    }
    function count_saving_setting($key=null, $status=null) {
        $this->db->where('PIN',  current_user()->PIN);
        if (!is_null($key)) {
            $this->db->where('PID', $key);
        }
        if (!is_null($status) && $status!='') {
            $this->db->where('status_flag', $status);
        }
        return count($this->db->get('members_account')->result());
    }

    function count_saving_account($key=null, $account_type_filter=null, $status_filter=null) {
        $pin = current_user()->PIN;
        $this->db->from('members_account ma');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
        $this->db->where('ma.PIN', $pin);
        
        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                // Special accounts: check account_setup prefix, account_type, or name/description contains "special"
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                // MSO accounts: check account_setup prefix, account_type, or name/description contains "mso"
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '40' OR ac.account_type = 40 OR ac.account_type = '40')) OR LOWER(sat.name) LIKE '%mso%' OR LOWER(sat.description) LIKE '%mso%')", NULL, FALSE);
            }
        }
        
        // Filter by status
        if (!is_null($status_filter) && $status_filter != '') {
            if ($status_filter == 'all') {
                // Show all statuses - no filter
            } else {
                // Handle NULL status as Active (1) by default
                if ($status_filter == '1') {
                    $this->db->where("(ma.status = '1' OR ma.status IS NULL)", NULL, FALSE);
                } else {
                    $this->db->where('ma.status', $status_filter);
                }
            }
        }
        
        if (!is_null($key) && $key != '') {
            $key_escaped = $this->db->escape_like_str($key);
            $this->db->where("(ma.account LIKE '%{$key_escaped}%' OR ma.member_id LIKE '%{$key_escaped}%' OR ma.RFID = " . $this->db->escape($key) . ")", NULL, FALSE);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Count savings accounts with beginning balance transactions
     */
    function count_saving_account_with_beginning_balance($key=null, $account_type_filter=null, $status_filter=null) {
        $pin = current_user()->PIN;
        $this->db->from('members_account ma');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
        // Join with savings_transaction to filter by BEGINNING BALANCE
        $this->db->join('savings_transaction st', 'st.account = ma.account AND st.PIN = ma.PIN AND st.system_comment = ' . $this->db->escape('BEGINNING BALANCE'), 'inner');
        $this->db->where('ma.PIN', $pin);
        $this->db->group_by('ma.account'); // Group to avoid duplicates
        
        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '40' OR ac.account_type = 40 OR ac.account_type = '40')) OR LOWER(sat.name) LIKE '%mso%' OR LOWER(sat.description) LIKE '%mso%')", NULL, FALSE);
            }
        }
        
        // Filter by status
        if (!is_null($status_filter) && $status_filter != '') {
            if ($status_filter == 'all') {
                // Show all statuses - no filter
            } else {
                if ($status_filter == '1') {
                    $this->db->where("(ma.status = '1' OR ma.status IS NULL)", NULL, FALSE);
                } else {
                    $this->db->where('ma.status', $status_filter);
                }
            }
        }
        
        if (!is_null($key) && $key != '') {
            $key_escaped = $this->db->escape_like_str($key);
            $this->db->where("(ma.account LIKE '%{$key_escaped}%' OR ma.member_id LIKE '%{$key_escaped}%' OR ma.RFID = " . $this->db->escape($key) . ")", NULL, FALSE);
        }
        
        return $this->db->count_all_results();
    }

    function search_saving_account($key=null, $limit=40, $start=0, $account_type_filter=null, $status_filter=null) {
        $pin = current_user()->PIN;
        $this->db->select('ma.*, m.firstname, m.middlename, m.lastname, m.member_id as member_id_display, mg.name as group_name, sat.description as account_type_name, sat.account as account_type_code, sat.name as account_type_name_display');
        $this->db->from('members_account ma');
        $this->db->join('members m', 'ma.RFID = m.PID AND m.PIN = ma.PIN AND ma.tablename = \'members\'', 'left');
        $this->db->join('members_grouplist mg', 'ma.RFID = mg.GID AND mg.PIN = ma.PIN AND ma.tablename = \'members_grouplist\'', 'left');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
        $this->db->where('ma.PIN', $pin);
        
        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                // Special accounts: check account_setup prefix, account_type, or name/description contains "special"
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                // MSO accounts: check account_setup prefix, account_type, or name/description contains "mso"
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '40' OR ac.account_type = 40 OR ac.account_type = '40')) OR LOWER(sat.name) LIKE '%mso%' OR LOWER(sat.description) LIKE '%mso%')", NULL, FALSE);
            }
        }
        
        // Filter by status
        if (!is_null($status_filter) && $status_filter != '') {
            if ($status_filter != 'all') {
                // Handle NULL status as Active (1) by default
                if ($status_filter == '1') {
                    $this->db->where("(ma.status = '1' OR ma.status IS NULL)", NULL, FALSE);
                } else {
                    $this->db->where('ma.status', $status_filter);
                }
            }
        }
        
        if (!is_null($key) && $key != '') {
            $key_escaped = $this->db->escape_like_str($key);
            $this->db->where("(ma.account LIKE '%{$key_escaped}%' OR ma.member_id LIKE '%{$key_escaped}%' OR ma.RFID = " . $this->db->escape($key) . " OR m.firstname LIKE '%{$key_escaped}%' OR m.lastname LIKE '%{$key_escaped}%')", NULL, FALSE);
        }
        
        $this->db->order_by('ma.account', 'ASC');
        $this->db->limit($limit, $start);
        return $this->db->get()->result();
    }

    /**
     * Search savings accounts with beginning balance transactions
     * Filters accounts that have at least one BEGINNING BALANCE transaction
     */
    function search_saving_account_with_beginning_balance($key=null, $limit=40, $start=0, $account_type_filter=null, $status_filter=null) {
        $pin = current_user()->PIN;
        $this->db->select('ma.*, m.firstname, m.middlename, m.lastname, m.member_id as member_id_display, mg.name as group_name, sat.description as account_type_name, sat.account as account_type_code, sat.name as account_type_name_display');
        $this->db->from('members_account ma');
        $this->db->join('members m', 'ma.RFID = m.PID AND m.PIN = ma.PIN AND ma.tablename = \'members\'', 'left');
        $this->db->join('members_grouplist mg', 'ma.RFID = mg.GID AND mg.PIN = ma.PIN AND ma.tablename = \'members_grouplist\'', 'left');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
        // Join with savings_transaction to filter by BEGINNING BALANCE
        $this->db->join('savings_transaction st', 'st.account = ma.account AND st.PIN = ma.PIN AND st.system_comment = ' . $this->db->escape('BEGINNING BALANCE'), 'inner');
        $this->db->where('ma.PIN', $pin);
        $this->db->group_by('ma.account'); // Group to avoid duplicates if multiple beginning balance transactions exist
        
        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $this->db->escape($pin), 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '40' OR ac.account_type = 40 OR ac.account_type = '40')) OR LOWER(sat.name) LIKE '%mso%' OR LOWER(sat.description) LIKE '%mso%')", NULL, FALSE);
            }
        }
        
        // Filter by status
        if (!is_null($status_filter) && $status_filter != '') {
            if ($status_filter != 'all') {
                if ($status_filter == '1') {
                    $this->db->where("(ma.status = '1' OR ma.status IS NULL)", NULL, FALSE);
                } else {
                    $this->db->where('ma.status', $status_filter);
                }
            }
        }
        
        if (!is_null($key) && $key != '') {
            $key_escaped = $this->db->escape_like_str($key);
            $this->db->where("(ma.account LIKE '%{$key_escaped}%' OR ma.member_id LIKE '%{$key_escaped}%' OR ma.RFID = " . $this->db->escape($key) . " OR m.firstname LIKE '%{$key_escaped}%' OR m.lastname LIKE '%{$key_escaped}%')", NULL, FALSE);
        }
        
        $this->db->order_by('ma.account', 'ASC');
        $this->db->limit($limit, $start);
        return $this->db->get()->result();
    }

    function get_saving_account_info($id) {
        $pin = current_user()->PIN;
        $this->db->select('ma.*, m.member_id as member_member_id, m.firstname, m.middlename, m.lastname, sat.description as account_type_name');
        $this->db->from('members_account ma');
        $this->db->join('members m', 'ma.RFID = m.PID AND m.PIN = ' . $this->db->escape($pin), 'left');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account', 'left');
        $this->db->where('ma.id', $id);
        $this->db->where('ma.PIN', $pin);
        return $this->db->get()->row();
    }

    function update_saving_account($data, $id) {
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        return $this->db->update('members_account', $data);
    }

    // Chart Type CRUD operations
    function create_chart_type($data) {
        return $this->db->insert('account_type', $data);
    }

    function update_chart_type($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('account_type', $data);
    }

    function delete_chart_type($id) {
        // Get chart type info
        $chart_type = $this->account_type($id)->row();
        if (!$chart_type) {
            return false; // Chart type doesn't exist
        }
        
        // Check if chart type is being used in account_chart
        $this->db->where('account_type', $chart_type->account);
        $count = $this->db->count_all_results('account_chart');
        
        if ($count > 0) {
            return false; // Cannot delete if in use
        }
        
        // Check if chart type has sub types
        $this->db->where('accounttype', $chart_type->account);
        $sub_count = $this->db->count_all_results('account_type_sub');
        
        if ($sub_count > 0) {
            return false; // Cannot delete if has sub types
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('account_type');
    }

    // Chart Sub Type CRUD operations
    function create_chart_sub_type($data) {
        return $this->db->insert('account_type_sub', $data);
    }

    function update_chart_sub_type($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('account_type_sub', $data);
    }

    function delete_chart_sub_type($id) {
        // Get chart sub type info
        $sub_type = $this->account_type_sub($id)->row();
        if (!$sub_type) {
            return false; // Chart sub type doesn't exist
        }
        
        // Check if chart sub type is being used in account_chart
        $this->db->where('account_type', $sub_type->accounttype);
        $this->db->where('sub_account_type', $sub_type->sub_account);
        $count = $this->db->count_all_results('account_chart');
        
        if ($count > 0) {
            return false; // Cannot delete if in use
        }
        
        $this->db->where('id', $id);
        return $this->db->delete('account_type_sub');
    }

    // Beginning Balances Methods
    function beginning_balance_list($fiscal_year_id = null, $id = null) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        
        if (!is_null($fiscal_year_id)) {
            $this->db->where('fiscal_year_id', $fiscal_year_id);
        }
        
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        
        $this->db->order_by('account', 'ASC');
        return $this->db->get('beginning_balances');
    }

    function beginning_balance_create($data) {
        $pin = current_user()->PIN;
        $data['PIN'] = $pin;
        $data['created_by'] = current_user()->id;
        return $this->db->insert('beginning_balances', $data);
    }

    function beginning_balance_update($data, $id) {
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        return $this->db->update('beginning_balances', $data);
    }

    function beginning_balance_delete($id) {
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        
        // Check if already posted
        $balance = $this->beginning_balance_list(null, $id)->row();
        if ($balance && $balance->posted == 1) {
            return false; // Cannot delete if already posted
        }
        
        return $this->db->delete('beginning_balances');
    }

    function beginning_balance_post_to_ledger($id) {
        $pin = current_user()->PIN;
        $balance = $this->beginning_balance_list(null, $id)->row();
        
        if (!$balance || $balance->posted == 1) {
            return false; // Already posted or doesn't exist
        }
        
        // Get fiscal year info
        $fiscal_year = $this->db->where('id', $balance->fiscal_year_id)->get('fiscal_year')->row();
        if (!$fiscal_year) {
            return false;
        }
        
        // Get account info
        $account_info = account_row_info($balance->account);
        if (!$account_info) {
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
            log_message('error', 'Failed to create general_ledger_entry header for beginning balance ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        // Use LAST_INSERT_ID() as fallback if needed
        if (!$ledger_entry_id || $ledger_entry_id == 0) {
            $last_id_result = $this->db->query("SELECT LAST_INSERT_ID() as id")->row();
            if ($last_id_result && $last_id_result->id > 0) {
                $ledger_entry_id = $last_id_result->id;
            } else {
                log_message('error', 'Failed to get ledger_entry_id for beginning balance ID: ' . $id);
                $this->db->trans_complete();
                return false;
            }
        }
        
        // Validate that at least one of debit or credit is greater than zero
        if (floatval($balance->debit) == 0 && floatval($balance->credit) == 0) {
            log_message('error', 'Beginning balance ID ' . $id . ' has both debit and credit as zero');
            $this->db->trans_complete();
            return false;
        }
        
        // Create general ledger entry
        $ledger = array(
            'journalID' => 8, // Journal ID for Beginning Balance
            'refferenceID' => $id,
            'entryid' => $ledger_entry_id,
            'date' => $fiscal_year->start_date,
            'description' => 'Beginning Balance - ' . ($balance->description ? $balance->description : $account_info->name),
            'linkto' => 'beginning_balances.id',
            'fromtable' => 'beginning_balances',
            'account' => $balance->account,
            'debit' => floatval($balance->debit),
            'credit' => floatval($balance->credit),
            'account_type' => $account_info->account_type,
            'sub_account_type' => isset($account_info->sub_account_type) ? $account_info->sub_account_type : null,
            'PIN' => $pin
        );
        
        $insert_result = $this->db->insert('general_ledger', $ledger);
        $insert_affected = $this->db->affected_rows();
        
        if (!$insert_result || $insert_affected != 1) {
            log_message('error', 'Failed to insert general_ledger entry for beginning balance ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        // Check transaction status before updating posted status
        if ($this->db->_trans_status === FALSE) {
            log_message('error', 'Transaction status is FALSE before updating posted status for beginning balance ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        // Update beginning balance as posted
        $update_data = array(
            'posted' => 1,
            'posted_date' => date('Y-m-d H:i:s'),
            'posted_by' => current_user()->id
        );
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $update_result = $this->db->update('beginning_balances', $update_data);
        $update_affected = $this->db->affected_rows();
        
        if (!$update_result || $update_affected != 1) {
            log_message('error', 'Failed to update beginning balance as posted for ID: ' . $id);
            $this->db->trans_complete();
            return false;
        }
        
        $this->db->trans_complete();
        
        $transaction_status = $this->db->trans_status();
        
        if ($transaction_status === FALSE) {
            log_message('error', 'Beginning balance post to ledger failed - transaction rolled back for ID: ' . $id);
            return false;
        }
        
        log_message('info', 'Beginning balance ID ' . $id . ' posted to general ledger successfully (Account: ' . $balance->account . ', Debit: ' . $balance->debit . ', Credit: ' . $balance->credit . ')');
        
        return true;
    }

    function check_beginning_balance_exists($fiscal_year_id, $account) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('fiscal_year_id', $fiscal_year_id);
        $this->db->where('account', $account);
        $result = $this->db->get('beginning_balances');
        return $result->num_rows() > 0;
    }

}
}
