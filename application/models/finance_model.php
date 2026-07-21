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
            'SELECT id, entry_date, description, PIN, reference_type FROM journal_entry WHERE id = ? AND PIN = ? LIMIT 1',
            array($journal_entry_id, $pin)
        )->row();
        if (!$entry) {
            log_message('error', 'post_journal_entry_to_general_ledger: journal_entry not found id=' . $journal_entry_id);
            return false;
        }
        // Use journal_id = 3 (Receive Money) for cash_receipt, 10 for cash_disbursement
        if (isset($entry->reference_type)) {
            if ($entry->reference_type == 'cash_receipt') {
                $journal_id = 3;
            } elseif ($entry->reference_type == 'cash_disbursement') {
                $journal_id = 10;
            }
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
        $has_amount_col = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'amount'")->row();
        $has_ref_type_col = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'reference_type'")->row();
        $select_fields = 'account, debit, credit';
        if ($has_desc_col) {
            $select_fields .= ', description';
        }
        if ($has_amount_col) {
            $select_fields .= ', amount';
        }
        $ref_type = isset($entry->reference_type) ? $entry->reference_type : null;
        $where_extra = ($has_ref_type_col && $ref_type) ? ' AND (reference_type = ? OR reference_type IS NULL)' : '';
        $params = array($journal_entry_id);
        if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
        $line_items = $this->db->query(
            'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ?' . $where_extra . ' ORDER BY id ASC',
            $params
        )->result();
        if (empty($line_items) && $has_pin_col) {
            $params = array($journal_entry_id, $pin);
            if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
            $line_items = $this->db->query(
                'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? AND PIN = ?' . $where_extra . ' ORDER BY id ASC',
                $params
            )->result();
        }
        if (empty($line_items) && $has_journal_id && $has_entry_id) {
            $other_link_col = ($link_col == 'journal_id') ? 'entry_id' : 'journal_id';
            $params = array($journal_entry_id);
            if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
            $line_items = $this->db->query(
                'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $other_link_col . ' = ?' . $where_extra . ' ORDER BY id ASC',
                $params
            )->result();
            if (!empty($line_items)) {
                $link_col = $other_link_col;
            } elseif ($has_pin_col) {
                $params = array($journal_entry_id, $pin);
                if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                $line_items = $this->db->query(
                    'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $other_link_col . ' = ? AND PIN = ?' . $where_extra . ' ORDER BY id ASC',
                    $params
                )->result();
                if (!empty($line_items)) {
                    $link_col = $other_link_col;
                }
            }
        }
        if (empty($line_items)) {
            $entry_check = $this->db->query('SELECT id, reference_type, reference_id FROM journal_entry WHERE id = ?', array($journal_entry_id))->row();
            if ($entry_check && isset($entry_check->reference_id)) {
                $has_desc = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'description'")->row();
                if ($entry_check->reference_type == 'cash_receipt') {
                    $this->load->model('cash_receipt_model');
                    $receipt = $this->cash_receipt_model->get_cash_receipt($entry_check->reference_id);
                    if ($receipt) {
                        $receipt_items = $this->cash_receipt_model->get_receipt_items($entry_check->reference_id);
                        $payment_method = isset($receipt->payment_method) ? trim($receipt->payment_method) : 'Cash';
                        if (empty($payment_method)) $payment_method = 'Cash';
                        $cash_account = $this->_get_cash_account_for_receipt($payment_method, $pin);
                        if ($cash_account) {
                            $debit_desc = 'Receipt from: ' . (isset($receipt->received_from) ? $receipt->received_from : '');
                            if ($has_ref_type_col && $has_desc) {
                                $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_receipt', $cash_account, $receipt->total_amount, 0, $debit_desc, $pin));
                            } elseif ($has_ref_type_col) {
                                $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_receipt', $cash_account, $receipt->total_amount, 0, $pin));
                            } elseif ($has_desc) {
                                $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, $receipt->total_amount, 0, $debit_desc, $pin));
                            } else {
                                $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, $receipt->total_amount, 0, $pin));
                            }
                            foreach ($receipt_items as $item) {
                                $amt = isset($item->amount) ? floatval($item->amount) : (isset($item->credit) ? floatval($item->credit) : 0);
                                if ($amt <= 0) continue;
                                if ($has_ref_type_col && $has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_receipt', $item->account, 0, $amt, isset($item->description) ? $item->description : '', $pin));
                                } elseif ($has_ref_type_col) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_receipt', $item->account, 0, $amt, $pin));
                                } elseif ($has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $item->account, 0, $amt, isset($item->description) ? $item->description : '', $pin));
                                } else {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $item->account, 0, $amt, $pin));
                                }
                            }
                            $params = array($journal_entry_id);
                            if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                            $line_items = $this->db->query('SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ?' . $where_extra . ' ORDER BY id ASC', $params)->result();
                            if (empty($line_items) && $has_pin_col) {
                                $params = array($journal_entry_id, $pin);
                                if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                                $line_items = $this->db->query('SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? AND PIN = ?' . $where_extra . ' ORDER BY id ASC', $params)->result();
                            }
                        }
                    }
                } elseif ($entry_check->reference_type == 'cash_disbursement') {
                    $this->load->model('cash_disbursement_model');
                    $disburse = $this->cash_disbursement_model->get_cash_disbursement($entry_check->reference_id);
                    if ($disburse) {
                        $disburse_items = $this->cash_disbursement_model->get_disburse_items($entry_check->reference_id);
                        $total_debit = 0;
                        $total_credit = 0;
                        foreach ($disburse_items as $it) {
                            $total_debit += isset($it->debit) ? floatval($it->debit) : (isset($it->amount) ? floatval($it->amount) : 0);
                            $total_credit += isset($it->credit) ? floatval($it->credit) : 0;
                        }
                        $cash_account = $this->_get_cash_account_for_receipt(isset($disburse->payment_method) ? trim($disburse->payment_method) : 'Cash', $pin);
                        if ($cash_account) {
                            if ($total_debit > $total_credit && ($total_debit - $total_credit) > 0.001) {
                                $credit_amt = $total_debit - $total_credit;
                                $bal_desc = 'Disbursement to: ' . (isset($disburse->paid_to) ? $disburse->paid_to : '');
                                if ($has_ref_type_col && $has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_disbursement', $cash_account, 0, $credit_amt, $bal_desc, $pin));
                                } elseif ($has_ref_type_col) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_disbursement', $cash_account, 0, $credit_amt, $pin));
                                } elseif ($has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, 0, $credit_amt, $bal_desc, $pin));
                                } else {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, 0, $credit_amt, $pin));
                                }
                            } elseif ($total_credit > $total_debit && ($total_credit - $total_debit) > 0.001) {
                                $debit_amt = $total_credit - $total_debit;
                                $bal_desc = 'Disbursement to: ' . (isset($disburse->paid_to) ? $disburse->paid_to : '');
                                if ($has_ref_type_col && $has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_disbursement', $cash_account, $debit_amt, 0, $bal_desc, $pin));
                                } elseif ($has_ref_type_col) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_disbursement', $cash_account, $debit_amt, 0, $pin));
                                } elseif ($has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, $debit_amt, 0, $bal_desc, $pin));
                                } else {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $cash_account, $debit_amt, 0, $pin));
                                }
                            }
                            foreach ($disburse_items as $item) {
                                $d = isset($item->debit) ? floatval($item->debit) : (isset($item->amount) ? floatval($item->amount) : 0);
                                $c = isset($item->credit) ? floatval($item->credit) : 0;
                                if (empty($item->account) || ($d <= 0 && $c <= 0)) continue;
                                if ($has_ref_type_col && $has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_disbursement', $item->account, $d, $c, isset($item->description) ? $item->description : '', $pin));
                                } elseif ($has_ref_type_col) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, 'cash_disbursement', $item->account, $d, $c, $pin));
                                } elseif ($has_desc) {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)', array($journal_entry_id, $item->account, $d, $c, isset($item->description) ? $item->description : '', $pin));
                                } else {
                                    $this->db->query('INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)', array($journal_entry_id, $item->account, $d, $c, $pin));
                                }
                            }
                            $params = array($journal_entry_id);
                            if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                            $line_items = $this->db->query('SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ?' . $where_extra . ' ORDER BY id ASC', $params)->result();
                            if (empty($line_items) && $has_pin_col) {
                                $params = array($journal_entry_id, $pin);
                                if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                                $line_items = $this->db->query('SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? AND PIN = ?' . $where_extra . ' ORDER BY id ASC', $params)->result();
                            }
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
            $d = isset($item->debit) ? floatval($item->debit) : 0;
            $c = isset($item->credit) ? floatval($item->credit) : 0;
            if ($d <= 0 && $c <= 0 && isset($item->amount)) {
                $c = floatval($item->amount);
            }
            $total_debit += $d;
            $total_credit += $c;
        }
        $balance_tolerance = 0.02;
        if (abs($total_debit - $total_credit) > $balance_tolerance) {
            // For cash_receipt entries, try to add a balancing Cash/Bank line so posting can succeed
            $entry_ref = $this->db->query(
                'SELECT reference_type, reference_id FROM journal_entry WHERE id = ? AND PIN = ? LIMIT 1',
                array($journal_entry_id, $pin)
            )->row();
            if ($entry_ref && $entry_ref->reference_type === 'cash_receipt' && !empty($entry_ref->reference_id)) {
                $this->load->model('cash_receipt_model');
                $receipt = $this->cash_receipt_model->get_cash_receipt($entry_ref->reference_id);
                if ($receipt) {
                    $payment_method = isset($receipt->payment_method) ? trim($receipt->payment_method) : 'Cash';
                    if (empty($payment_method)) {
                        $payment_method = 'Cash';
                    }
                    $cash_account = $this->_get_cash_account_for_receipt($payment_method, $pin);
                    if ($cash_account) {
                        $diff = $total_credit - $total_debit;
                        $debit_bal = ($diff > 0) ? $diff : 0;
                        $credit_bal = ($diff < 0) ? -$diff : 0;
                        $has_desc = $this->db->query("SHOW COLUMNS FROM journal_entry_items LIKE 'description'")->row();
                        $bal_desc = 'Receipt from: ' . (isset($receipt->received_from) ? $receipt->received_from : '');
                        if ($has_ref_type_col && $has_desc) {
                            $this->db->query(
                                'INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?, ?)',
                                array($journal_entry_id, 'cash_receipt', $cash_account, $debit_bal, $credit_bal, $bal_desc, $pin)
                            );
                        } elseif ($has_ref_type_col) {
                            $this->db->query(
                                'INSERT INTO journal_entry_items (' . $link_col . ', reference_type, account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?, ?)',
                                array($journal_entry_id, 'cash_receipt', $cash_account, $debit_bal, $credit_bal, $pin)
                            );
                        } elseif ($has_desc) {
                            $this->db->query(
                                'INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, description, PIN) VALUES (?, ?, ?, ?, ?, ?)',
                                array($journal_entry_id, $cash_account, $debit_bal, $credit_bal, $bal_desc, $pin)
                            );
                        } else {
                            $this->db->query(
                                'INSERT INTO journal_entry_items (' . $link_col . ', account, debit, credit, PIN) VALUES (?, ?, ?, ?, ?)',
                                array($journal_entry_id, $cash_account, $debit_bal, $credit_bal, $pin)
                            );
                        }
                        // Re-fetch line items and recompute totals
                        $params = array($journal_entry_id);
                        if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                        $line_items = $this->db->query(
                            'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ?' . $where_extra . ' ORDER BY id ASC',
                            $params
                        )->result();
                        if (empty($line_items) && $has_pin_col) {
                            $params = array($journal_entry_id, $pin);
                            if ($has_ref_type_col && $ref_type) $params[] = $ref_type;
                            $line_items = $this->db->query(
                                'SELECT ' . $select_fields . ' FROM journal_entry_items WHERE ' . $link_col . ' = ? AND PIN = ?' . $where_extra . ' ORDER BY id ASC',
                                $params
                            )->result();
                        }
                        $total_debit = 0;
                        $total_credit = 0;
                        foreach ($line_items as $item) {
                            $d = isset($item->debit) ? floatval($item->debit) : 0;
                            $c = isset($item->credit) ? floatval($item->credit) : 0;
                            if ($d <= 0 && $c <= 0 && isset($item->amount)) {
                                $c = floatval($item->amount);
                            }
                            $total_debit += $d;
                            $total_credit += $c;
                        }
                        log_message('info', 'post_journal_entry_to_general_ledger: auto-balanced cash_receipt entry ' . $journal_entry_id . ' (added Cash/Bank line). Debit: ' . $total_debit . ', Credit: ' . $total_credit);
                    }
                }
            }
            if (abs($total_debit - $total_credit) > $balance_tolerance) {
                log_message('error', 'post_journal_entry_to_general_ledger: entry ' . $journal_entry_id . ' does not balance. Debit: ' . $total_debit . ', Credit: ' . $total_credit);
                return false;
            }
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
            $d = isset($item->debit) ? floatval($item->debit) : 0;
            $c = isset($item->credit) ? floatval($item->credit) : 0;
            if ($d <= 0 && $c <= 0 && isset($item->amount)) {
                $c = floatval($item->amount);
            }
            $ledger['account'] = $item->account;
            $ledger['debit'] = $d;
            $ledger['credit'] = $c;
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
     * Get cash/bank account code for a payment method (used when auto-balancing cash_receipt journal entries).
     */
    private function _get_cash_account_for_receipt($payment_method, $pin) {
        $payment_method = trim((string) $payment_method);
        if (empty($payment_method)) {
            $payment_method = 'Cash';
        }
        $this->load->model('payment_method_config_model');
        $pm_config = $this->payment_method_config_model->get_account_for_payment_method($payment_method);
        if ($pm_config && !empty($pm_config->gl_account_code)) {
            $acct = $this->db->query('SELECT account FROM account_chart WHERE account = ? AND PIN = ? LIMIT 1', array($pm_config->gl_account_code, $pin))->row();
            if ($acct) {
                return $acct->account;
            }
        }
        $acct = $this->db->query(
            'SELECT account FROM account_chart WHERE PIN = ? AND account_type IN (1, 10000) AND (name LIKE ? OR name LIKE ?) LIMIT 1',
            array($pin, '%Cash%', '%Bank%')
        )->row();
        return $acct ? $acct->account : null;
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

                // Sub-ledger links from journal line (Customer AR / Supplier AP / Member Loan)
                $ledger['customerid'] = !empty($item->customerid) ? $item->customerid : null;
                $ledger['supplierid'] = !empty($item->supplierid) ? $item->supplierid : null;
                $ledger['LID'] = !empty($item->LID) ? $item->LID : null;
                $ledger['PID'] = !empty($item->PID) ? $item->PID : null;
                $ledger['member_id'] = !empty($item->member_id) ? $item->member_id : null;
                $ledger['invoiceid'] = !empty($item->invoiceid) ? intval($item->invoiceid) : null;

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
            $item->link_label = $this->format_journal_line_link_label($item);
        }
        return $entry;
    }

    /**
     * Ensure general_journal has sub-ledger link columns (Customer/Supplier/Loan).
     */
    function ensure_general_journal_link_columns() {
        static $done = false;
        if ($done) {
            return true;
        }
        $columns = array(
            'link_type' => "VARCHAR(20) NULL DEFAULT NULL",
            'customerid' => "VARCHAR(50) NULL DEFAULT NULL",
            'supplierid' => "VARCHAR(50) NULL DEFAULT NULL",
            'LID' => "VARCHAR(50) NULL DEFAULT NULL",
            'PID' => "BIGINT NULL DEFAULT NULL",
            'member_id' => "VARCHAR(50) NULL DEFAULT NULL",
            'invoiceid' => "INT NULL DEFAULT NULL",
        );
        foreach ($columns as $col => $definition) {
            $exists = $this->db->query("SHOW COLUMNS FROM general_journal LIKE '" . $this->db->escape_str($col) . "'")->row();
            if (!$exists) {
                $this->db->query("ALTER TABLE general_journal ADD COLUMN `$col` $definition");
            }
        }
        $done = true;
        return true;
    }

    /**
     * Resolve posted link_type + entity into general_journal link fields.
     * Does not update invoice balances or loan schedules.
     *
     * @param string $link_type customer|supplier|loan|empty
     * @param string $entity_id customerid, supplierid, or LID
     * @return array Fields to merge into journal line
     */
    function resolve_journal_line_link($link_type, $entity_id) {
        $pin = current_user()->PIN;
        $out = array(
            'link_type' => null,
            'customerid' => null,
            'supplierid' => null,
            'LID' => null,
            'PID' => null,
            'member_id' => null,
            'invoiceid' => null,
        );
        $link_type = strtolower(trim((string) $link_type));
        $entity_id = trim((string) $entity_id);
        if ($entity_id === '' || !in_array($link_type, array('customer', 'supplier', 'loan'), true)) {
            return $out;
        }

        if ($link_type === 'customer') {
            $row = $this->db->where('PIN', $pin)->where('customerid', $entity_id)->get('customer')->row();
            if ($row) {
                $out['link_type'] = 'customer';
                $out['customerid'] = $row->customerid;
            }
            return $out;
        }

        if ($link_type === 'supplier') {
            $row = $this->db->where('PIN', $pin)->where('supplierid', $entity_id)->get('supplier')->row();
            if ($row) {
                $out['link_type'] = 'supplier';
                $out['supplierid'] = $row->supplierid;
            }
            return $out;
        }

        // loan
        $loan = $this->db->where('PIN', $pin)->where('LID', $entity_id)->get('loan_contract')->row();
        if ($loan) {
            $out['link_type'] = 'loan';
            $out['LID'] = $loan->LID;
            $out['PID'] = isset($loan->PID) ? $loan->PID : null;
            $out['member_id'] = isset($loan->member_id) ? $loan->member_id : null;
        }
        return $out;
    }

    /**
     * Human-readable sub-ledger link for a journal line.
     */
    function format_journal_line_link_label($item) {
        if (empty($item)) {
            return '';
        }
        $pin = current_user()->PIN;
        $type = isset($item->link_type) ? strtolower(trim($item->link_type)) : '';

        if ($type === 'customer' || !empty($item->customerid)) {
            $cid = !empty($item->customerid) ? $item->customerid : '';
            if ($cid === '') {
                return '';
            }
            $c = $this->db->where('PIN', $pin)->where('customerid', $cid)->get('customer')->row();
            $name = $c ? $c->name : $cid;
            return 'Customer: ' . $cid . ' — ' . $name;
        }
        if ($type === 'supplier' || !empty($item->supplierid)) {
            $sid = !empty($item->supplierid) ? $item->supplierid : '';
            if ($sid === '') {
                return '';
            }
            $s = $this->db->where('PIN', $pin)->where('supplierid', $sid)->get('supplier')->row();
            $name = $s ? $s->name : $sid;
            return 'Supplier: ' . $sid . ' — ' . $name;
        }
        if ($type === 'loan' || !empty($item->LID)) {
            $lid = !empty($item->LID) ? $item->LID : '';
            if ($lid === '') {
                return '';
            }
            $loan = $this->db->where('PIN', $pin)->where('LID', $lid)->get('loan_contract')->row();
            $member = isset($item->member_id) ? $item->member_id : (isset($loan->member_id) ? $loan->member_id : '');
            $label = 'Loan: ' . $lid;
            if ($member !== '') {
                $label .= ' (Member ' . $member . ')';
            }
            return $label;
        }
        return '';
    }

    /**
     * Next Journal Voucher reference number: JV-{YYYY}{######}
     * Counter is per organization (PIN) and resets each calendar year.
     * Example: JV-2026000001
     *
     * @param int|string|null $year Year for the series (defaults to current year)
     * @return string
     */
    function get_next_journal_voucher_no($year = null) {
        $pin = current_user()->PIN;
        $year = $year !== null && $year !== '' ? (int) $year : (int) date('Y');
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $has_ref_no = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'reference_no'")->row();
        if (!$has_ref_no) {
            $this->db->query("ALTER TABLE general_journal_entry ADD COLUMN reference_no VARCHAR(100) NULL DEFAULT NULL AFTER description");
        }

        $prefix = 'JV-' . $year;
        $like = $prefix . '%';
        $row = $this->db->query(
            "SELECT reference_no FROM general_journal_entry
             WHERE PIN = ? AND reference_no LIKE ?
             ORDER BY reference_no DESC
             LIMIT 1",
            array($pin, $like)
        )->row();

        $next = 1;
        if ($row && !empty($row->reference_no)) {
            // Match JV-2026000001 (year already in prefix; take trailing 6+ digits)
            if (preg_match('/^JV-' . $year . '(\d+)$/', $row->reference_no, $m)) {
                $next = intval($m[1]) + 1;
            }
        }
        if ($next > 999999) {
            $next = 999999;
        }

        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get manual journal entries (general_journal_entry) with optional date range filter.
     */
    function get_journal_entries($date_from = null, $date_to = null) {
        $pin = current_user()->PIN;
        $has_pin_col = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'")->row();

        $this->db->where('PIN', $pin);
        if (!empty($date_from)) {
            $this->db->where('entrydate >=', $date_from);
        }
        if (!empty($date_to)) {
            $this->db->where('entrydate <=', $date_to);
        }
        $this->db->order_by('entrydate', 'DESC');
        $this->db->order_by('id', 'DESC');
        $results = $this->db->get('general_journal_entry')->result();

        foreach ($results as $entry) {
            if ($has_pin_col) {
                $totals = $this->db->query(
                    "SELECT COUNT(*) as line_count, COALESCE(SUM(COALESCE(gj.debit, 0)), 0) as total_debit, COALESCE(SUM(COALESCE(gj.credit, 0)), 0) as total_credit, MAX(gj.createdby) as createdby FROM general_journal gj WHERE gj.entryid = ? AND gj.PIN = ?",
                    array($entry->id, $pin)
                )->row();
            } else {
                $totals = $this->db->query(
                    "SELECT COUNT(*) as line_count, COALESCE(SUM(COALESCE(gj.debit, 0)), 0) as total_debit, COALESCE(SUM(COALESCE(gj.credit, 0)), 0) as total_credit, MAX(gj.createdby) as createdby FROM general_journal gj WHERE gj.entryid = ?",
                    array($entry->id)
                )->row();
            }

            $entry->entryid = $entry->id;
            $entry->line_count = isset($totals->line_count) ? intval($totals->line_count) : 0;
            $entry->total_debit = isset($totals->total_debit) ? floatval($totals->total_debit) : 0.00;
            $entry->total_credit = isset($totals->total_credit) ? floatval($totals->total_credit) : 0.00;
            $entry->total_amount = max($entry->total_debit, $entry->total_credit);
            $entry->createdby = isset($totals->createdby) ? $totals->createdby : null;
            $entry->is_posted = $this->is_journal_posted($entry->id);

            if (!empty($entry->createdby)) {
                $user = $this->db->where('id', $entry->createdby)->get('users')->row();
                $entry->created_by_name = $user ? $user->username : 'Unknown';
            } else {
                $entry->created_by_name = 'Unknown';
            }
        }

        return $results;
    }

    /**
     * Delete an unposted manual journal entry and its line items.
     */
    function delete_journal_entry($entry_id) {
        if ($this->is_journal_posted($entry_id)) {
            return false;
        }

        $pin = current_user()->PIN;
        $entry = $this->db->where('id', $entry_id)->where('PIN', $pin)->get('general_journal_entry')->row();
        if (!$entry) {
            return false;
        }

        $has_pin_col = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'")->row();

        $this->db->trans_start();
        $this->db->where('entryid', $entry_id);
        if ($has_pin_col) {
            $this->db->where('PIN', $pin);
        }
        $this->db->delete('general_journal');
        $this->db->where('id', $entry_id)->where('PIN', $pin);
        $this->db->delete('general_journal_entry');
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Update an unposted manual journal entry (header + replace line items).
     * Keeps existing reference_no; does not allow edit when already posted to GL.
     */
    function update_journal_entry($entry_id, $main_array, $array_items) {
        $entry_id = intval($entry_id);
        if ($entry_id < 1 || empty($array_items)) {
            return false;
        }
        if ($this->is_journal_posted($entry_id)) {
            return false;
        }

        $pin = current_user()->PIN;
        $entry = $this->db->where('id', $entry_id)->where('PIN', $pin)->get('general_journal_entry')->row();
        if (!$entry) {
            return false;
        }

        $this->ensure_general_journal_link_columns();

        $has_pin_col = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'")->row();
        $has_gj_ref_type = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'reference_type'")->row();
        $has_doc_no = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'document_no'")->row();

        $this->db->trans_start();

        $header_update = array(
            'entrydate' => $main_array['entrydate'],
            'description' => $main_array['description'],
        );
        if ($has_doc_no && array_key_exists('document_no', $main_array)) {
            $header_update['document_no'] = $main_array['document_no'];
        }
        $this->db->where('id', $entry_id)->where('PIN', $pin)->update('general_journal_entry', $header_update);

        $this->db->where('entryid', $entry_id);
        if ($has_pin_col) {
            $this->db->where('PIN', $pin);
        }
        $this->db->delete('general_journal');

        $items_inserted = 0;
        foreach ($array_items as $value) {
            $value['entryid'] = $entry_id;
            if ($has_pin_col) {
                $value['PIN'] = $pin;
            } else {
                unset($value['PIN']);
            }
            if ($has_gj_ref_type) {
                $value['reference_type'] = 'journal_voucher';
            }
            if (!isset($value['entrydate'])) {
                $value['entrydate'] = $main_array['entrydate'];
            }
            if (!isset($value['createdby'])) {
                $value['createdby'] = current_user()->id;
            }

            $link_keys = array('link_type', 'customerid', 'supplierid', 'LID', 'PID', 'member_id', 'invoiceid');
            foreach ($link_keys as $lk) {
                if (!array_key_exists($lk, $value) || $value[$lk] === null || $value[$lk] === '') {
                    unset($value[$lk]);
                }
            }

            if ($this->db->insert('general_journal', $value) && $this->db->affected_rows() == 1) {
                $items_inserted++;
            } else {
                $this->db->_trans_status = FALSE;
                break;
            }
        }

        if ($items_inserted != count($array_items)) {
            $this->db->_trans_status = FALSE;
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Get list of unposted journal entries from general_journal_entry
     * @return array List of unposted journal entries with details
     */
    function get_unposted_journal_entries() {
        $pin = current_user()->PIN;
        // Check if general_journal table has PIN column
        $has_pin_col = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'")->row();
        
        $this->db->select('gje.*');
        $this->db->from('general_journal_entry gje');
        $this->db->join('general_ledger gl', 'gl.refferenceID = gje.id AND gl.fromtable = "general_journal"', 'left');
        $this->db->where('gl.id IS NULL');
        $this->db->where('gje.PIN', $pin);
        $this->db->order_by('gje.entrydate', 'DESC');
        $this->db->order_by('gje.id', 'DESC');
        $results = $this->db->get()->result();
        
        foreach ($results as $entry) {
            // Calculate totals using SQL aggregation for accuracy
            if ($has_pin_col) {
                $totals_query = "SELECT 
                                    COUNT(*) as line_count,
                                    COALESCE(SUM(COALESCE(gj.debit, 0)), 0) as total_debit,
                                    COALESCE(SUM(COALESCE(gj.credit, 0)), 0) as total_credit,
                                    MAX(gj.createdby) as createdby
                                 FROM general_journal gj
                                 WHERE gj.entryid = ? AND gj.PIN = ?";
                $totals = $this->db->query($totals_query, array($entry->id, $pin))->row();
            } else {
                $totals_query = "SELECT 
                                    COUNT(*) as line_count,
                                    COALESCE(SUM(COALESCE(gj.debit, 0)), 0) as total_debit,
                                    COALESCE(SUM(COALESCE(gj.credit, 0)), 0) as total_credit,
                                    MAX(gj.createdby) as createdby
                                 FROM general_journal gj
                                 WHERE gj.entryid = ?";
                $totals = $this->db->query($totals_query, array($entry->id))->row();
            }
            
            $entry->entryid = $entry->id;
            $entry->line_count = isset($totals->line_count) ? intval($totals->line_count) : 0;
            $entry->total_debit = isset($totals->total_debit) ? floatval($totals->total_debit) : 0.00;
            $entry->total_credit = isset($totals->total_credit) ? floatval($totals->total_credit) : 0.00;
            $entry->createdby = isset($totals->createdby) ? $totals->createdby : null;
            
            if (!empty($entry->createdby)) {
                $user = $this->db->where('id', $entry->createdby)->get('users')->row();
                $entry->created_by_name = $user ? $user->username : 'Unknown';
            } else {
                $entry->created_by_name = 'Unknown';
            }
        }
        return $results;
    }

    /**
     * Get list of posted journal entries (general_journal_entry) that have been posted to GL.
     */
    function get_posted_general_journal_entries() {
        $pin = current_user()->PIN;
        $has_pin_col = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'")->row();
        $this->db->select('gje.*');
        $this->db->from('general_journal_entry gje');
        $this->db->join('general_ledger gl', 'gl.refferenceID = gje.id AND gl.fromtable = "general_journal" AND gl.PIN = gje.PIN', 'inner');
        $this->db->where('gje.PIN', $pin);
        $this->db->group_by('gje.id');
        $this->db->order_by('gje.entrydate', 'DESC');
        $this->db->order_by('gje.id', 'DESC');
        $results = $this->db->get()->result();
        foreach ($results as $entry) {
            if ($has_pin_col) {
                $totals = $this->db->query(
                    "SELECT COUNT(*) as line_count, COALESCE(SUM(COALESCE(gj.debit, 0)), 0) as total_debit, COALESCE(SUM(COALESCE(gj.credit, 0)), 0) as total_credit, MAX(gj.createdby) as createdby FROM general_journal gj WHERE gj.entryid = ? AND gj.PIN = ?",
                    array($entry->id, $pin)
                )->row();
            } else {
                $totals = $this->db->query(
                    "SELECT COUNT(*) as line_count, COALESCE(SUM(COALESCE(gj.debit, 0)), 0) as total_debit, COALESCE(SUM(COALESCE(gj.credit, 0)), 0) as total_credit, MAX(gj.createdby) as createdby FROM general_journal gj WHERE gj.entryid = ?",
                    array($entry->id)
                )->row();
            }
            $entry->entryid = $entry->id;
            $entry->line_count = isset($totals->line_count) ? intval($totals->line_count) : 0;
            $entry->total_debit = isset($totals->total_debit) ? floatval($totals->total_debit) : 0.00;
            $entry->total_credit = isset($totals->total_credit) ? floatval($totals->total_credit) : 0.00;
            $entry->createdby = isset($totals->createdby) ? $totals->createdby : null;
            $user = !empty($entry->createdby) ? $this->db->where('id', $entry->createdby)->get('users')->row() : null;
            $entry->created_by_name = $user ? $user->username : 'Unknown';
        }
        return $results;
    }

    /**
     * Get list of posted journal entries (cash_receipt / cash_disbursement) that have been posted to GL.
     */
    function get_posted_receipt_disbursement_journal_entries() {
        $pin = current_user()->PIN;
        if (!$this->db->table_exists('journal_entry')) {
            return array();
        }
        if (!$this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row()) {
            return array();
        }
        $sql = "SELECT je.id, je.entry_date, je.description, je.reference_type, je.reference_id, je.createdby, je.PIN, je.created_at
                FROM journal_entry je
                INNER JOIN general_ledger gl ON gl.refferenceID = je.id AND gl.fromtable = 'journal_entry' AND gl.PIN = je.PIN
                WHERE je.PIN = ? AND je.reference_type IN ('cash_receipt', 'cash_disbursement')
                GROUP BY je.id, je.entry_date, je.description, je.reference_type, je.reference_id, je.createdby, je.PIN, je.created_at
                ORDER BY je.entry_date DESC, je.id DESC";
        $rows = $this->db->query($sql, array($pin))->result();
        $entries = array();
        foreach ($rows as $row) {
            $total_debit = 0.00;
            $total_credit = 0.00;
            $line_count = 0;
            if ($row->reference_type == 'cash_receipt' && !empty($row->reference_id)) {
                $receipt_id = intval($row->reference_id);
                $pin_esc = $this->db->escape($pin);
                $has_d = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'debit'")->row();
                $has_c = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'credit'")->row();
                if ($has_d && $has_c) {
                    $t = $this->db->query("SELECT COUNT(*) as line_count, IFNULL(SUM(IFNULL(debit, 0)), 0) as total_debit, IFNULL(SUM(IFNULL(credit, 0)), 0) as total_credit FROM cash_receipt_items WHERE receipt_id = {$receipt_id} AND PIN = {$pin_esc}")->row();
                } else {
                    $t = $this->db->query("SELECT COUNT(*) as line_count, 0 as total_debit, IFNULL(SUM(IFNULL(amount, 0)), 0) as total_credit FROM cash_receipt_items WHERE receipt_id = {$receipt_id} AND PIN = {$pin_esc}")->row();
                }
                if ($t) {
                    $line_count = intval($t->line_count);
                    $total_debit = floatval($t->total_debit);
                    $total_credit = floatval($t->total_credit);
                    if ($total_debit == 0 && $row->reference_id) {
                        $r = $this->db->query("SELECT total_amount FROM cash_receipts WHERE id = ? AND PIN = ? LIMIT 1", array($row->reference_id, $pin))->row();
                        if ($r && isset($r->total_amount)) $total_debit = floatval($r->total_amount);
                    }
                }
            } elseif ($row->reference_type == 'cash_disbursement' && !empty($row->reference_id)) {
                $disb_id = intval($row->reference_id);
                $pin_esc = $this->db->escape($pin);
                $has_d = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'debit'")->row();
                $has_c = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'credit'")->row();
                if ($has_d && $has_c) {
                    $t = $this->db->query("SELECT COUNT(*) as line_count, IFNULL(SUM(IFNULL(debit, 0)), 0) as total_debit, IFNULL(SUM(IFNULL(credit, 0)), 0) as total_credit FROM cash_disbursement_items WHERE disbursement_id = {$disb_id} AND PIN = {$pin_esc}")->row();
                } else {
                    $t = $this->db->query("SELECT COUNT(*) as line_count, IFNULL(SUM(IFNULL(amount, 0)), 0) as total_debit, 0 as total_credit FROM cash_disbursement_items WHERE disbursement_id = {$disb_id} AND PIN = {$pin_esc}")->row();
                }
                if ($t) {
                    $line_count = intval($t->line_count);
                    $total_debit = floatval($t->total_debit);
                    $total_credit = floatval($t->total_credit);
                    if ($total_credit == 0 && $row->reference_id) {
                        $d = $this->db->query("SELECT total_amount FROM cash_disbursements WHERE id = ? AND PIN = ? LIMIT 1", array($row->reference_id, $pin))->row();
                        if ($d && isset($d->total_amount)) $total_credit = floatval($d->total_amount);
                    }
                }
            }
            $user = $this->db->where('id', $row->createdby)->get('users')->row();
            $entries[] = (object) array(
                'entryid' => $row->id,
                'entrydate' => $row->entry_date,
                'description' => $row->description,
                'total_debit' => $total_debit,
                'total_credit' => $total_credit,
                'line_count' => $line_count,
                'created_by_name' => $user ? $user->username : 'Unknown',
                'entry_source' => $row->reference_type,
                'reference_id' => isset($row->reference_id) ? $row->reference_id : null,
                'is_posted' => true,
            );
        }
        return $entries;
    }

    function get_total_savings_amount($key=null, $account_type_filter=null, $status_filter=null, $gl_posted_filter=null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $this->db->select_sum('ma.balance');
        $this->db->from('members_account ma');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $pin_esc, 'left');
        $this->db->where('ma.PIN', $pin);
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $pin_esc, 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $pin_esc, 'left');
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
        // Filter by GL posted status
        if (!is_null($gl_posted_filter) && $gl_posted_filter != '' && $gl_posted_filter != 'all') {
            if ($gl_posted_filter == 'posted') {
                $this->db->where("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") > 0", NULL, FALSE);
            } else if ($gl_posted_filter == 'not_posted') {
                $this->db->where("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") = 0", NULL, FALSE);
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

    function is_savings_receipt_posted_to_gl($receipt) {
        $pin = current_user()->PIN;
        $this->db->where('refferenceID', $receipt);
        $this->db->where('fromtable', 'savings_transaction');
        $this->db->where('PIN', $pin);
        $count = $this->db->count_all_results('general_ledger');
        return $count > 0;
    }

    /**
     * Void (remove) GL posting for a journal entry. Deletes general_ledger rows only;
     * journal entry stays active and can be reposted.
     * @param int $journal_entry_id ID from journal_entry or general_journal_entry
     * @param string $from_table 'journal_entry' or 'general_journal'
     * @return bool
     */
    function void_journal_posting_to_gl($journal_entry_id, $from_table = 'journal_entry') {
        $pin = current_user()->PIN;
        $journal_entry_id = (int) $journal_entry_id;
        if (!in_array($from_table, array('journal_entry', 'general_journal'), true)) {
            return false;
        }
        $this->db->where('refferenceID', $journal_entry_id);
        $this->db->where('fromtable', $from_table);
        $this->db->where('PIN', $pin);
        $existing = $this->db->get('general_ledger')->row();
        if (!$existing) {
            return true; // Nothing posted, consider success
        }
        $entryid = $existing->entryid;
        $this->db->where('refferenceID', $journal_entry_id);
        $this->db->where('fromtable', $from_table);
        $this->db->where('PIN', $pin);
        $this->db->delete('general_ledger');
        $this->db->where('entryid', $entryid);
        $remaining = $this->db->count_all_results('general_ledger');
        if ($remaining == 0) {
            $this->db->where('id', $entryid);
            $this->db->delete('general_ledger_entry');
        }
        return true;
    }

    function get_receipt_disbursement_journal_entries() {
        log_message('error', '=== get_receipt_disbursement_journal_entries START ===');
        $pin = current_user()->PIN;
        log_message('error', 'PIN: ' . $pin);
        $entries = array();
        
        // Check if table exists first
        if (!$this->db->table_exists('journal_entry')) {
            log_message('error', 'journal_entry table does not exist');
            return $entries;
        }
        log_message('error', 'journal_entry table exists');
        
        $col = $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row();
        if (!$col) {
            log_message('error', 'reference_type column does not exist');
            return $entries;
        }
        log_message('error', 'reference_type column exists');
        
        // Get journal entries first
        $sql = "SELECT 
                    je.id,
                    je.entry_date,
                    je.description,
                    je.reference_type,
                    je.reference_id,
                    je.createdby,
                    je.PIN,
                    je.created_at
                FROM journal_entry je
                LEFT JOIN general_ledger gl ON gl.refferenceID = je.id AND gl.fromtable = 'journal_entry' AND gl.PIN = je.PIN
                WHERE je.PIN = ? 
                    AND je.reference_type IN ('cash_receipt', 'cash_disbursement') 
                    AND gl.id IS NULL
                ORDER BY je.entry_date DESC, je.id DESC";
        
        log_message('error', 'Executing main query with PIN: ' . $pin);
        $rows = $this->db->query($sql, array($pin))->result();
        log_message('error', 'Main query returned ' . count($rows) . ' rows');
        
        if (empty($rows)) {
            log_message('error', 'No journal entries found, returning empty array');
            return $entries;
        }
        
        foreach ($rows as $row) {
            log_message('error', 'Processing entry ID: ' . $row->id . ', reference_type: ' . $row->reference_type . ', reference_id: ' . $row->reference_id);
            
            $total_debit = 0.00;
            $total_credit = 0.00;
            $line_count = 0;
            
            // Query items based on reference_type
            if ($row->reference_type == 'cash_receipt' && !empty($row->reference_id)) {
                // Query cash_receipt_items where receipt_id = journal_entry.reference_id
                $receipt_id = intval($row->reference_id);
                $pin_escaped = $this->db->escape($pin);
                
                // Check if debit/credit columns exist
                $has_debit = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'debit'")->row();
                $has_credit = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'credit'")->row();
                
                if ($has_debit && $has_credit) {
                    $items_query = "SELECT 
                                        COUNT(*) as line_count,
                                        IFNULL(SUM(IFNULL(debit, 0)), 0) as total_debit,
                                        IFNULL(SUM(IFNULL(credit, 0)), 0) as total_credit
                                    FROM cash_receipt_items
                                    WHERE receipt_id = {$receipt_id} AND PIN = {$pin_escaped}";
                } else {
                    // Legacy: use amount as credit
                    $items_query = "SELECT 
                                        COUNT(*) as line_count,
                                        0 as total_debit,
                                        IFNULL(SUM(IFNULL(amount, 0)), 0) as total_credit
                                    FROM cash_receipt_items
                                    WHERE receipt_id = {$receipt_id} AND PIN = {$pin_escaped}";
                }
                
                log_message('error', 'Cash Receipt Items query: ' . $items_query);
                $items_totals = $this->db->query($items_query)->row();
                
            } elseif ($row->reference_type == 'cash_disbursement' && !empty($row->reference_id)) {
                // Query cash_disbursement_items where disbursement_id = journal_entry.reference_id
                $disbursement_id = intval($row->reference_id);
                $pin_escaped = $this->db->escape($pin);
                
                // Check if debit/credit columns exist
                $has_debit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'debit'")->row();
                $has_credit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'credit'")->row();
                
                if ($has_debit && $has_credit) {
                    $items_query = "SELECT 
                                        COUNT(*) as line_count,
                                        IFNULL(SUM(IFNULL(debit, 0)), 0) as total_debit,
                                        IFNULL(SUM(IFNULL(credit, 0)), 0) as total_credit
                                    FROM cash_disbursement_items
                                    WHERE disbursement_id = {$disbursement_id} AND PIN = {$pin_escaped}";
                } else {
                    // Legacy: use amount as debit
                    $items_query = "SELECT 
                                        COUNT(*) as line_count,
                                        IFNULL(SUM(IFNULL(amount, 0)), 0) as total_debit,
                                        0 as total_credit
                                    FROM cash_disbursement_items
                                    WHERE disbursement_id = {$disbursement_id} AND PIN = {$pin_escaped}";
                }
                
                log_message('error', 'Cash Disbursement Items query: ' . $items_query);
                $items_totals = $this->db->query($items_query)->row();
            } else {
                log_message('error', 'Entry ' . $row->id . ': Unknown reference_type or missing reference_id');
                $items_totals = null;
            }
            
            if ($items_totals) {
                $line_count = isset($items_totals->line_count) ? intval($items_totals->line_count) : 0;
                $total_debit = isset($items_totals->total_debit) ? floatval($items_totals->total_debit) : 0.00;
                $total_credit = isset($items_totals->total_credit) ? floatval($items_totals->total_credit) : 0.00;
                
                // If total_debit is 0, use receipt/disbursement header total_amount as debit
                if ($total_debit == 0 && $row->reference_type == 'cash_receipt' && !empty($row->reference_id)) {
                    $receipt = $this->db->query("SELECT total_amount FROM cash_receipts WHERE id = ? AND PIN = ? LIMIT 1", array($row->reference_id, $pin))->row();
                    if ($receipt && isset($receipt->total_amount)) {
                        $total_debit = floatval($receipt->total_amount);
                        log_message('error', 'Entry ' . $row->id . ': Using receipt total_amount as debit: ' . $total_debit);
                    }
                } elseif ($total_credit == 0 && $row->reference_type == 'cash_disbursement' && !empty($row->reference_id)) {
                    $disbursement = $this->db->query("SELECT total_amount FROM cash_disbursements WHERE id = ? AND PIN = ? LIMIT 1", array($row->reference_id, $pin))->row();
                    if ($disbursement && isset($disbursement->total_amount)) {
                        $total_credit = floatval($disbursement->total_amount);
                        log_message('error', 'Entry ' . $row->id . ': Using disbursement total_amount as credit: ' . $total_credit);
                    }
                }
                
                log_message('error', 'Entry ' . $row->id . ' final results: line_count=' . $line_count . ', total_debit=' . $total_debit . ', total_credit=' . $total_credit);
            } else {
                log_message('error', 'Entry ' . $row->id . ': No items_totals returned');
            }
            
            $user = $this->db->where('id', $row->createdby)->get('users')->row();
            $entry_obj = (object) array(
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
            log_message('error', 'Adding entry to array: ID=' . $entry_obj->entryid . ', total_debit=' . $entry_obj->total_debit . ', total_credit=' . $entry_obj->total_credit . ', line_count=' . $entry_obj->line_count);
            $entries[] = $entry_obj;
        }
        log_message('error', 'Returning ' . count($entries) . ' entries');
        log_message('error', '=== get_receipt_disbursement_journal_entries END ===');
        return $entries;
    }

    /**
     * Server-side DataTables feed for unposted journal entries on the review page.
     *
     * @param int    $start
     * @param int    $length
     * @param string $search
     * @param int    $order_column_index DataTables column index
     * @param string $order_dir asc|desc
     * @param string $source_filter all|general_journal|cash_receipt|cash_disbursement
     * @return array draw, recordsTotal, recordsFiltered, data rows, grand totals
     */
    function get_unposted_journal_review_datatable($start, $length, $search, $order_column_index, $order_dir, $source_filter = 'all') {
        $pin = current_user()->PIN;
        $has_pin_col = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'PIN'")->row();
        $gj_pin_cond = $has_pin_col ? ' AND gj.PIN = gje.PIN' : '';

        $allowed_sources = array('general_journal', 'cash_receipt', 'cash_disbursement');
        $source_filter = strtolower(trim((string) $source_filter));
        if ($source_filter === '' || $source_filter === 'all' || !in_array($source_filter, $allowed_sources, true)) {
            $source_filter = 'all';
        }

        $union_parts = array();
        $bind = array();

        if ($source_filter === 'all' || $source_filter === 'general_journal') {
            $union_parts[] = "SELECT
                    gje.id AS entryid,
                    gje.entrydate AS entrydate,
                    gje.description AS description,
                    'general_journal' AS entry_source,
                    NULL AS reference_id,
                    (SELECT MAX(gj.createdby) FROM general_journal gj WHERE gj.entryid = gje.id{$gj_pin_cond}) AS createdby,
                    (SELECT COUNT(*) FROM general_journal gj WHERE gj.entryid = gje.id{$gj_pin_cond}) AS line_count,
                    (SELECT COALESCE(SUM(COALESCE(gj.debit, 0)), 0) FROM general_journal gj WHERE gj.entryid = gje.id{$gj_pin_cond}) AS total_debit,
                    (SELECT COALESCE(SUM(COALESCE(gj.credit, 0)), 0) FROM general_journal gj WHERE gj.entryid = gje.id{$gj_pin_cond}) AS total_credit,
                    0 AS is_posted
                FROM general_journal_entry gje
                LEFT JOIN general_ledger gl ON gl.refferenceID = gje.id AND gl.fromtable = 'general_journal' AND gl.PIN = gje.PIN
                WHERE gl.id IS NULL AND gje.PIN = ?";
            $bind[] = $pin;
        }

        if (($source_filter === 'all' || $source_filter === 'cash_receipt' || $source_filter === 'cash_disbursement')
            && $this->db->table_exists('journal_entry')
            && $this->db->query("SHOW COLUMNS FROM journal_entry LIKE 'reference_type'")->row()) {
            $receipt_line_sql = "0";
            $receipt_debit_sql = "0";
            $receipt_credit_sql = "0";
            $disburse_line_sql = "0";
            $disburse_debit_sql = "0";
            $disburse_credit_sql = "0";

            if ($this->db->table_exists('cash_receipt_items')) {
                $has_receipt_debit = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'debit'")->row();
                $has_receipt_credit = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'credit'")->row();
                $receipt_line_sql = "(SELECT COUNT(*) FROM cash_receipt_items cri WHERE cri.receipt_id = je.reference_id AND cri.PIN = je.PIN)";
                if ($has_receipt_debit && $has_receipt_credit) {
                    $receipt_debit_sql = "(SELECT COALESCE(SUM(COALESCE(cri.debit, 0)), 0) FROM cash_receipt_items cri WHERE cri.receipt_id = je.reference_id AND cri.PIN = je.PIN)";
                    $receipt_credit_sql = "(SELECT COALESCE(SUM(COALESCE(cri.credit, 0)), 0) FROM cash_receipt_items cri WHERE cri.receipt_id = je.reference_id AND cri.PIN = je.PIN)";
                } else {
                    $receipt_credit_sql = "(SELECT COALESCE(SUM(COALESCE(cri.amount, 0)), 0) FROM cash_receipt_items cri WHERE cri.receipt_id = je.reference_id AND cri.PIN = je.PIN)";
                }
            }

            if ($this->db->table_exists('cash_disbursement_items')) {
                $has_disburse_debit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'debit'")->row();
                $has_disburse_credit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'credit'")->row();
                $disburse_line_sql = "(SELECT COUNT(*) FROM cash_disbursement_items cdi WHERE cdi.disbursement_id = je.reference_id AND cdi.PIN = je.PIN)";
                if ($has_disburse_debit && $has_disburse_credit) {
                    $disburse_debit_sql = "(SELECT COALESCE(SUM(COALESCE(cdi.debit, 0)), 0) FROM cash_disbursement_items cdi WHERE cdi.disbursement_id = je.reference_id AND cdi.PIN = je.PIN)";
                    $disburse_credit_sql = "(SELECT COALESCE(SUM(COALESCE(cdi.credit, 0)), 0) FROM cash_disbursement_items cdi WHERE cdi.disbursement_id = je.reference_id AND cdi.PIN = je.PIN)";
                } else {
                    $disburse_debit_sql = "(SELECT COALESCE(SUM(COALESCE(cdi.amount, 0)), 0) FROM cash_disbursement_items cdi WHERE cdi.disbursement_id = je.reference_id AND cdi.PIN = je.PIN)";
                }
            }

            $ref_types = ($source_filter === 'all')
                ? array('cash_receipt', 'cash_disbursement')
                : array($source_filter);
            $ref_placeholders = implode(',', array_fill(0, count($ref_types), '?'));

            $union_parts[] = "SELECT
                    je.id AS entryid,
                    je.entry_date AS entrydate,
                    je.description AS description,
                    je.reference_type AS entry_source,
                    je.reference_id AS reference_id,
                    je.createdby AS createdby,
                    CASE je.reference_type
                        WHEN 'cash_receipt' THEN {$receipt_line_sql}
                        WHEN 'cash_disbursement' THEN {$disburse_line_sql}
                        ELSE 0
                    END AS line_count,
                    CASE je.reference_type
                        WHEN 'cash_receipt' THEN {$receipt_debit_sql}
                        WHEN 'cash_disbursement' THEN {$disburse_debit_sql}
                        ELSE 0
                    END AS total_debit,
                    CASE je.reference_type
                        WHEN 'cash_receipt' THEN {$receipt_credit_sql}
                        WHEN 'cash_disbursement' THEN {$disburse_credit_sql}
                        ELSE 0
                    END AS total_credit,
                    0 AS is_posted
                FROM journal_entry je
                LEFT JOIN general_ledger gl ON gl.refferenceID = je.id AND gl.fromtable = 'journal_entry' AND gl.PIN = je.PIN
                WHERE je.PIN = ?
                    AND je.reference_type IN ({$ref_placeholders})
                    AND gl.id IS NULL";
            $bind[] = $pin;
            foreach ($ref_types as $ref_type) {
                $bind[] = $ref_type;
            }
        }

        if (empty($union_parts)) {
            return array(
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'entries' => array(),
                'grand_total_debit' => 0.0,
                'grand_total_credit' => 0.0,
            );
        }

        $union_sql = '(' . implode(') UNION ALL (', $union_parts) . ')';

        $search_sql = '';
        $search_bind = array();
        if ($search !== '') {
            $like = '%' . $this->db->escape_like_str($search) . '%';
            $search_sql = " AND (
                CAST(entryid AS CHAR) LIKE ?
                OR description LIKE ?
                OR entry_source LIKE ?
                OR DATE_FORMAT(entrydate, '%b %d, %Y') LIKE ?
                OR DATE_FORMAT(entrydate, '%Y-%m-%d') LIKE ?
                OR createdby IN (SELECT id FROM users WHERE username LIKE ?)
            )";
            $search_bind = array($like, $like, $like, $like, $like, $like);
        }

        $order_map = array(
            1 => 'entryid',
            2 => 'entry_source',
            3 => 'entrydate',
            4 => 'description',
            5 => 'createdby',
            6 => 'line_count',
            7 => 'total_debit',
            8 => 'total_credit',
            9 => 'total_debit',
        );
        $order_col = isset($order_map[$order_column_index]) ? $order_map[$order_column_index] : 'entrydate';
        $allowed_order_cols = array('entryid', 'entry_source', 'entrydate', 'description', 'createdby', 'line_count', 'total_debit', 'total_credit');
        if (!in_array($order_col, $allowed_order_cols, true)) {
            $order_col = 'entrydate';
        }
        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        $count_total_q = $this->db->query("SELECT COUNT(*) AS cnt FROM ({$union_sql}) AS u", $bind);
        if ($count_total_q === false) {
            $err = method_exists($this->db, 'error') ? $this->db->error() : array();
            log_message('error', 'get_unposted_journal_review_datatable count_total failed: ' . $this->db->last_query() . ' | ' . json_encode($err));
            throw new Exception('Failed to count unposted journal entries');
        }
        $count_total_row = $count_total_q->row();
        $records_total = $count_total_row ? (int) $count_total_row->cnt : 0;

        $count_filtered_q = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM ({$union_sql}) AS u WHERE 1 = 1{$search_sql}",
            array_merge($bind, $search_bind)
        );
        if ($count_filtered_q === false) {
            $err = method_exists($this->db, 'error') ? $this->db->error() : array();
            log_message('error', 'get_unposted_journal_review_datatable count_filtered failed: ' . $this->db->last_query() . ' | ' . json_encode($err));
            throw new Exception('Failed to count filtered unposted journal entries');
        }
        $count_filtered_row = $count_filtered_q->row();
        $records_filtered = $count_filtered_row ? (int) $count_filtered_row->cnt : 0;

        $totals_q = $this->db->query(
            "SELECT COALESCE(SUM(total_debit), 0) AS grand_total_debit, COALESCE(SUM(total_credit), 0) AS grand_total_credit
             FROM ({$union_sql}) AS u WHERE 1 = 1{$search_sql}",
            array_merge($bind, $search_bind)
        );
        $totals_row = ($totals_q !== false) ? $totals_q->row() : null;
        $grand_total_debit = $totals_row ? floatval($totals_row->grand_total_debit) : 0.0;
        $grand_total_credit = $totals_row ? floatval($totals_row->grand_total_credit) : 0.0;

        $start = max(0, (int) $start);
        $length = (int) $length;
        if ($length < 0) {
            $length = 25;
        }
        if ($length === 0) {
            $length = 25;
        }

        $data_sql = "SELECT * FROM ({$union_sql}) AS u WHERE 1 = 1{$search_sql}
            ORDER BY {$order_col} {$order_dir}, entryid DESC
            LIMIT {$length} OFFSET {$start}";
        $data_q = $this->db->query($data_sql, array_merge($bind, $search_bind));
        if ($data_q === false) {
            $err = method_exists($this->db, 'error') ? $this->db->error() : array();
            log_message('error', 'get_unposted_journal_review_datatable data query failed: ' . $this->db->last_query() . ' | ' . json_encode($err));
            throw new Exception('Failed to load unposted journal entries');
        }
        $rows = $data_q->result();

        $entries = array();
        foreach ($rows as $row) {
            if (in_array($row->entry_source, array('cash_receipt', 'cash_disbursement'), true)) {
                $this->_apply_receipt_disbursement_totals_to_row($row, $pin);
            }
            $row->total_debit = floatval($row->total_debit);
            $row->total_credit = floatval($row->total_credit);
            $row->line_count = intval($row->line_count);
            if (!empty($row->createdby)) {
                $user = $this->db->where('id', $row->createdby)->get('users')->row();
                $row->created_by_name = $user ? $user->username : 'Unknown';
            } else {
                $row->created_by_name = 'Unknown';
            }
            $entries[] = $row;
        }

        return array(
            'recordsTotal' => $records_total,
            'recordsFiltered' => $records_filtered,
            'entries' => $entries,
            'grand_total_debit' => $grand_total_debit,
            'grand_total_credit' => $grand_total_credit,
        );
    }

    /**
     * Populate line_count, total_debit, total_credit on a cash receipt/disbursement journal row.
     */
    private function _apply_receipt_disbursement_totals_to_row($row, $pin) {
        $total_debit = 0.00;
        $total_credit = 0.00;
        $line_count = 0;

        if ($row->entry_source === 'cash_receipt' && !empty($row->reference_id)) {
            $receipt_id = intval($row->reference_id);
            $pin_esc = $this->db->escape($pin);
            $has_debit = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'debit'")->row();
            $has_credit = $this->db->query("SHOW COLUMNS FROM cash_receipt_items LIKE 'credit'")->row();
            if ($has_debit && $has_credit) {
                $items_totals = $this->db->query(
                    "SELECT COUNT(*) AS line_count,
                            IFNULL(SUM(IFNULL(debit, 0)), 0) AS total_debit,
                            IFNULL(SUM(IFNULL(credit, 0)), 0) AS total_credit
                     FROM cash_receipt_items
                     WHERE receipt_id = {$receipt_id} AND PIN = {$pin_esc}"
                )->row();
            } else {
                $items_totals = $this->db->query(
                    "SELECT COUNT(*) AS line_count, 0 AS total_debit,
                            IFNULL(SUM(IFNULL(amount, 0)), 0) AS total_credit
                     FROM cash_receipt_items
                     WHERE receipt_id = {$receipt_id} AND PIN = {$pin_esc}"
                )->row();
            }
            if ($items_totals) {
                $line_count = intval($items_totals->line_count);
                $total_debit = floatval($items_totals->total_debit);
                $total_credit = floatval($items_totals->total_credit);
                if ($total_debit == 0 && $row->reference_id) {
                    $receipt = $this->db->query(
                        "SELECT total_amount FROM cash_receipts WHERE id = ? AND PIN = ? LIMIT 1",
                        array($row->reference_id, $pin)
                    )->row();
                    if ($receipt && isset($receipt->total_amount)) {
                        $total_debit = floatval($receipt->total_amount);
                    }
                }
            }
        } elseif ($row->entry_source === 'cash_disbursement' && !empty($row->reference_id)) {
            $disbursement_id = intval($row->reference_id);
            $pin_esc = $this->db->escape($pin);
            $has_debit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'debit'")->row();
            $has_credit = $this->db->query("SHOW COLUMNS FROM cash_disbursement_items LIKE 'credit'")->row();
            if ($has_debit && $has_credit) {
                $items_totals = $this->db->query(
                    "SELECT COUNT(*) AS line_count,
                            IFNULL(SUM(IFNULL(debit, 0)), 0) AS total_debit,
                            IFNULL(SUM(IFNULL(credit, 0)), 0) AS total_credit
                     FROM cash_disbursement_items
                     WHERE disbursement_id = {$disbursement_id} AND PIN = {$pin_esc}"
                )->row();
            } else {
                $items_totals = $this->db->query(
                    "SELECT COUNT(*) AS line_count,
                            IFNULL(SUM(IFNULL(amount, 0)), 0) AS total_debit,
                            0 AS total_credit
                     FROM cash_disbursement_items
                     WHERE disbursement_id = {$disbursement_id} AND PIN = {$pin_esc}"
                )->row();
            }
            if ($items_totals) {
                $line_count = intval($items_totals->line_count);
                $total_debit = floatval($items_totals->total_debit);
                $total_credit = floatval($items_totals->total_credit);
                if ($total_credit == 0 && $row->reference_id) {
                    $disbursement = $this->db->query(
                        "SELECT total_amount FROM cash_disbursements WHERE id = ? AND PIN = ? LIMIT 1",
                        array($row->reference_id, $pin)
                    )->row();
                    if ($disbursement && isset($disbursement->total_amount)) {
                        $total_credit = floatval($disbursement->total_amount);
                    }
                }
            }
        }

        $row->line_count = $line_count;
        $row->total_debit = $total_debit;
        $row->total_credit = $total_credit;
        $row->is_posted = $this->is_journal_entry_posted_to_gl($row->entryid);
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
        $this->db->where('status', 1);
        return $this->db->get('paymentmenthod')->result();
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM savings_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 12);
    }

    function create_account($PID, $member_id, $account_type, $balance, $virtual_balance, $paymethod, $comment = '', $cheque_num = '', $posted_date='',$old_savings_account_no='', $interest_frequency = null) {

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

        if ($this->db->field_exists('interest_frequency', 'members_account')) {
            $new_account['interest_frequency'] = $this->normalize_interest_frequency_override($interest_frequency);
        }

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
        $pin = current_user()->PIN;
        // Ensure account is treated as string for consistent comparison
        // This handles cases where account might be numeric like "18" or string like "Account#1"
        $account_str = trim((string)$account);
        
        // Compare account - use string comparison to handle both numeric and string accounts
        // CodeIgniter will properly escape this value
        $this->db->where('account', $account_str);
        $this->db->where('PIN', $pin);
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
        // Also handles combinations like "OTHER - ADJUSTMENT" or "OTHERS - ADJUSTMENT"
        if (strpos($payment_method_upper, 'ADJUSTMENT') !== FALSE) {
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
                log_message('warning', 'No adjustment/equity account found for ADJUSTMENT payment method. Using cash account fallback.');
                // Ultimate fallback: use cash account
                $this->db->where('PIN', $pin);
                $escaped = $this->db->escape_like_str('Cash');
                $this->db->where("(name LIKE '%" . $escaped . "%' OR description LIKE '%" . $escaped . "%')", NULL, FALSE);
                $this->db->where_in('account_type', array(1, 10000)); // Asset: Cash account
                $this->db->order_by('account', 'ASC');
                $this->db->limit(1);
                $account = $this->db->get('account_chart')->row();
                
                if (!$account) {
                    log_message('error', 'No cash account found as fallback for ADJUSTMENT payment method');
                    return null;
                }
            }
            
            log_message('debug', 'Using adjustment account: ' . $account->account . ' (' . $account->name . ') for ADJUSTMENT payment method');
            return $account->account;
        }
        
        // For OTHER payment method, use a miscellaneous/suspense account
        // Also handles combinations like "OTHERS - ADJUSTMENT" or "OTHER SOURCES"
        if (strpos($payment_method_upper, 'OTHER') !== FALSE) {
            // Try to find Suspense, Miscellaneous, or Other account
            $this->db->where('PIN', $pin);
            
            // Search for other-related accounts (Suspense, Miscellaneous, etc.)
            $other_account_names = array('Suspense', 'Miscellaneous', 'Other', 'Pending Transactions', 'Clearing');
            $where_clause = "(";
            foreach ($other_account_names as $index => $name) {
                if ($index > 0) {
                    $where_clause .= " OR ";
                }
                $escaped_name = $this->db->escape_like_str($name);
                $where_clause .= "name LIKE '%" . $escaped_name . "%'";
            }
            $where_clause .= ")";
            
            $this->db->where($where_clause, NULL, FALSE);
            // Asset accounts (type 1 or 10000) or Liability (2, 20000) for suspense accounts
            $this->db->where_in('account_type', array(1, 2, 10000, 20000));
            $this->db->order_by('account', 'ASC');
            $this->db->limit(1);
            
            $account = $this->db->get('account_chart')->row();
            
            if ($account) {
                log_message('info', 'Using OTHER payment method account: ' . $account->account . ' (' . $account->name . ')');
                return $account->account;
            }
            
            // Fallback: use Cash account for OTHER if no Suspense/Miscellaneous account found
            log_message('warning', 'No Suspense/Miscellaneous account found for OTHER payment method. Falling back to Cash account.');
            $account_name = 'Cash';
        } else {
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
    function post_savings_to_gl($account, $amount, $paymethod, $account_cat, $receipt, $trans_date, $pid, $member_id, $customer_name = '', $systemcomment = '', $trans_type = '') {
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
        $type_upper = strtoupper(trim($trans_type));
        $is_withdrawal = ($type_upper === 'DR' || strpos($sys_upper, 'NORMAL WITHDRAWAL') !== FALSE);
        $is_interest = ($type_upper === 'INT' || strpos($sys_upper, 'INTEREST') !== FALSE);
        $is_void_interest = (strpos($sys_upper, 'VOID TRANSACTION') !== FALSE && strpos($sys_upper, 'ORIG_TYPE:INT') !== FALSE);
        
        // For INTEREST: debit = interest expense account; for deposit/withdrawal: debit/credit = cash or liability
        if ($is_interest || $is_void_interest) {
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
        // Check if payment method contains "ADJUSTMENT" (handles "ADJUSTMENT", "OTHER - ADJUSTMENT", etc.)
        $is_adjustment = (strpos(strtoupper(trim($paymethod)), 'ADJUSTMENT') !== FALSE);
        if ($is_void_interest) {
            $description_prefix = 'Savings Interest Void';
        } elseif ($is_withdrawal) {
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
        
        // Determine journal based on transaction type
        // Adjustments are Journal Voucher entries, use Manual Journal (ID 5)
        // Regular transactions use Savings Journal (ID 9)
        if ($is_adjustment) {
            $journal_id = 5; // Manual Journal for JV/Adjustment entries
            $description_prefix = '[JV] ' . $description_prefix; // Prefix with [JV] to indicate Journal Voucher
        } else {
            // Check if Journal ID 9 (Savings Journal) exists, if not use 5 as fallback
            $this->db->where('id', 9);
            $journal_check = $this->db->get('journal')->row();
            $journal_id = ($journal_check) ? 9 : 5; // Use 9 for Savings Journal, fallback to 5 for Manual Journal
        }
        
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
            
            // Log JV entries for audit trail
            if ($is_adjustment) {
                log_message('info', 'Creating Journal Voucher (JV) entry for savings account: ' . $account . ', receipt: ' . $receipt . ', amount: ' . $amount);
            }
            
            // Prepare base ledger data with appropriate description (is_adjustment already defined above)
            // Include payment method in description for audit trail (skip for pure ADJUSTMENT types)
            $paymethod_info = (!$is_adjustment) ? ', Payment Method: ' . $paymethod : '';
            $description = $description_prefix . ' - ' . ($customer_name ? $customer_name : 'Member ' . $member_id) . ' (Account: ' . $account . ', Receipt: ' . $receipt . $paymethod_info . ')';
            
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
            
            $type_label = $is_void_interest ? 'Void Interest' : ($is_withdrawal ? 'Withdrawal' : ($is_interest ? 'Interest' : ($is_adjustment ? 'Adjustment' : 'Deposit/Opening')));
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

    function count_transaction($key, $from, $upto, $trans_type = 'ALL', $account_type_filter = null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $interest_condition = "(UPPER(TRIM(st.trans_type)) IN ('INT','IN','INTEREST','IR') OR UPPER(COALESCE(st.system_comment, '')) LIKE '%INTEREST%' OR UPPER(COALESCE(st.comment, '')) LIKE '%INTEREST%')";
        $this->db->from('savings_transaction st');
        $this->db->join('members_account ma', 'ma.account = st.account AND ma.PIN = st.PIN', 'left');
        $this->db->join('members m', 'ma.RFID = m.PID AND m.PIN = st.PIN AND ma.tablename = "members"', 'left');
        $this->db->where('st.PIN', $pin);
        $this->db->where('st.trans_date >=', $from . ' 00:00:00');
        $this->db->where('st.trans_date <=', $upto . ' 23:59:59');

        $trans_type = strtoupper(trim((string) $trans_type));
        if ($trans_type === 'DEPOSIT') {
            $this->db->where("UPPER(TRIM(st.trans_type)) = 'CR'", NULL, FALSE);
            $this->db->where("NOT " . $interest_condition, NULL, FALSE);
        } elseif ($trans_type === 'WITHDRAWAL') {
            $this->db->where("UPPER(TRIM(st.trans_type)) = 'DR'", NULL, FALSE);
        } elseif ($trans_type === 'INTEREST') {
            $this->db->where($interest_condition, NULL, FALSE);
        }

        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
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

        // Search by receipt number, account number, old account number, or member name
        if (!is_null($key) && $key !== '') {
            $this->db->group_start();
            $this->db->like('st.receipt', $key);
            $this->db->or_like('st.account', $key);
            $this->db->or_like('ma.old_members_acct', $key);
            $this->db->or_like('m.firstname', $key);
            $this->db->or_like('m.middlename', $key);
            $this->db->or_like('m.lastname', $key);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    function count_deposit_withdrawal_transaction($key, $from, $upto) {
        // REMOVED - Void transaction module deleted
        return false;
    }

    function search_transaction($key, $from, $upto, $limit, $start, $trans_type = 'ALL', $account_type_filter = null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $interest_condition = "(UPPER(TRIM(st.trans_type)) IN ('INT','IN','INTEREST','IR') OR UPPER(COALESCE(st.system_comment, '')) LIKE '%INTEREST%' OR UPPER(COALESCE(st.comment, '')) LIKE '%INTEREST%')";
        $this->db->select("st.*, COALESCE(NULLIF(ma.old_members_acct, ''), st.account) AS account_no_display", FALSE);
        $this->db->select("CASE WHEN " . $interest_condition . " THEN 'INTEREST' WHEN UPPER(TRIM(st.trans_type)) = 'CR' THEN 'DEPOSIT' WHEN UPPER(TRIM(st.trans_type)) = 'DR' THEN 'WITHDRAWAL' ELSE UPPER(TRIM(st.trans_type)) END AS trans_type_display", FALSE);
        $this->db->from('savings_transaction st');
        $this->db->join('members_account ma', 'ma.account = st.account AND ma.PIN = st.PIN', 'left');
        $this->db->join('members m', 'ma.RFID = m.PID AND m.PIN = st.PIN AND ma.tablename = "members"', 'left');
        $this->db->where('st.PIN', $pin);
        $this->db->where('st.trans_date >=', $from . ' 00:00:00');
        $this->db->where('st.trans_date <=', $upto . ' 23:59:59');

        $trans_type = strtoupper(trim((string) $trans_type));
        if ($trans_type === 'DEPOSIT') {
            $this->db->where("UPPER(TRIM(st.trans_type)) = 'CR'", NULL, FALSE);
            $this->db->where("NOT " . $interest_condition, NULL, FALSE);
        } elseif ($trans_type === 'WITHDRAWAL') {
            $this->db->where("UPPER(TRIM(st.trans_type)) = 'DR'", NULL, FALSE);
        } elseif ($trans_type === 'INTEREST') {
            $this->db->where($interest_condition, NULL, FALSE);
        }

        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
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

        // Search by receipt number, account number, old account number, or member name
        if (!is_null($key) && $key !== '') {
            $this->db->group_start();
            $this->db->like('st.receipt', $key);
            $this->db->or_like('st.account', $key);
            $this->db->or_like('ma.old_members_acct', $key);
            $this->db->or_like('m.firstname', $key);
            $this->db->or_like('m.middlename', $key);
            $this->db->or_like('m.lastname', $key);
            $this->db->group_end();
        }

        $this->db->order_by('st.trans_date', 'DESC');
        $this->db->limit((int) $limit, (int) $start);

        return $this->db->get()->result();
    }

    function search_deposit_withdrawal_transaction($key, $from, $upto, $limit, $start) {
        // REMOVED - Void transaction module deleted
        return array();
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
                $gl_post_result = $this->post_savings_to_gl($account, $amount, $paymethod, $account_info->account_cat, $receipt, $posted_date ? $posted_date : date('Y-m-d'), $pid, $member_id, $customer_name, $systemcomment, 'CR');
                
                if (!$gl_post_result) {
                    log_message('error', 'Savings account GL posting failed for account: ' . $account . ', receipt: ' . $receipt . ', type: ' . $systemcomment);
                }
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
                $gl_post_result = $this->post_savings_to_gl($account, $amount, $paymethod, $account_info->account_cat, $receipt, $posted_date ? $posted_date : date('Y-m-d'), $pid_val, $member_id, $customer_name, $systemcomment, 'DR');
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

    function get_savings_transaction_by_receipt($receipt) {
        // REMOVED - Void transaction module deleted
        return null;
    }

    function is_savings_transaction_voided($receipt) {
        // Check if a reversing transaction exists for this receipt
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('comment LIKE', '%VOID-' . $receipt . '%');
        $query = $this->db->get('savings_transaction');
        return $query->num_rows() > 0;
    }

    function is_void_entry($transaction) {
        // Check if this transaction is a reversing entry (void transaction)
        $comment = isset($transaction->comment) ? (string)$transaction->comment : '';
        return (strpos($comment, 'VOID-') === 0);
    }

    function get_voided_receipt($transaction) {
        // Extract the original receipt number from a void entry comment
        // Comment format: VOID-[original_receipt] - [reason]
        if ($this->is_void_entry($transaction)) {
            $comment = isset($transaction->comment) ? (string)$transaction->comment : '';
            if (preg_match('/VOID-([^ ]+)/', $comment, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    function void_savings_deposit_withdrawal_transaction($receipt, $reason = '') {
        $pin = current_user()->PIN;
        
        // Get original transaction
        $this->db->where('receipt', $receipt);
        $this->db->where('PIN', $pin);
        $trans = $this->db->get('savings_transaction')->row();
        
        if (!$trans) {
            return array('success' => false, 'message' => 'Transaction not found');
        }
        
        // Check if already voided
        if ($this->is_savings_transaction_voided($receipt)) {
            return array('success' => false, 'message' => 'Transaction already voided');
        }
        
        // Only allow voiding of CR (deposit), DR (withdrawal), or INT (interest) transactions
        if (!in_array($trans->trans_type, array('CR', 'DR', 'INT'))) {
            return array('success' => false, 'message' => 'Only deposit/withdrawal/interest transactions can be voided');
        }
        
        // Begin transaction
        $this->db->trans_start();
        
        // Create a reversing entry
        // CR (deposit) and INT (interest) reverse to DR (withdrawal), DR reverses to CR
        $void_trans_type = ($trans->trans_type == 'CR' || $trans->trans_type == 'INT') ? 'DR' : 'CR';
        $void_comment = 'VOID-' . $receipt . ' - ' . $reason;
        $original_method = !empty($trans->paymethod) ? $trans->paymethod : 'N/A';
        $void_system_comment = 'VOID TRANSACTION | ORIG_TYPE:' . $trans->trans_type . ' | ORIG_METHOD:' . $original_method;
        
        // Get next receipt number
        $next_id = $this->get_next_savings_transaction_id();
        $void_receipt = 'SV' . str_pad($next_id, 8, '0', STR_PAD_LEFT);
        
        // Get account info for additional fields
        $account_info = $this->get_saving_account_info($trans->account);
        
        // Insert reversing transaction using db->set() method like other transactions
        $this->db->set('receipt', $void_receipt);
        $this->db->set('account', $trans->account);
        $this->db->set('trans_type', $void_trans_type);
        $this->db->set('amount', $trans->amount);
        $this->db->set('paymethod', $trans->paymethod);
        $this->db->set('cheque_num', $trans->cheque_num ? $trans->cheque_num : '');
        $this->db->set('trans_date', date('Y-m-d'));
        $this->db->set('comment', $void_comment);
        $this->db->set('system_comment', $void_system_comment);
        $this->db->set('PIN', $pin);
        $this->db->set('createdby', current_user()->id);
        
        // Add optional fields if they exist in original transaction
        if (isset($trans->PID)) {
            $this->db->set('PID', $trans->PID);
        } elseif ($account_info && isset($account_info->RFID)) {
            $this->db->set('PID', $account_info->RFID);
        }
        
        if (isset($trans->account_cat)) {
            $this->db->set('account_cat', $trans->account_cat);
        } elseif ($account_info && isset($account_info->account_cat)) {
            $this->db->set('account_cat', $account_info->account_cat);
        }
        
        if (isset($trans->customer_name)) {
            $this->db->set('customer_name', $trans->customer_name);
        }
        
        if (isset($trans->refno)) {
            $this->db->set('refno', $trans->refno);
        }
        
        // Set previous_balance (current balance before this void transaction)
        if ($account_info && isset($account_info->balance)) {
            $this->db->set('previous_balance', $account_info->balance);
        }
        
        $this->db->insert('savings_transaction');
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return array('success' => false, 'message' => 'Database error occurred');
        }

        // If this was an automated interest posting, mark its log row as VOIDED
        // so the period can be re-posted for this account (no-op otherwise).
        $this->void_interest_posting_by_receipt($receipt);

        // Auto-post the void reversing entry to GL so accounting remains adjusted
        $member_id = ($account_info && isset($account_info->member_id)) ? $account_info->member_id : '';
        $pid = isset($trans->PID) ? $trans->PID : (($account_info && isset($account_info->RFID)) ? $account_info->RFID : '');
        $customer_name = isset($trans->customer_name) ? $trans->customer_name : '';
        $account_cat = isset($trans->account_cat) ? $trans->account_cat : (($account_info && isset($account_info->account_cat)) ? $account_info->account_cat : '');
        $void_trans_date = date('Y-m-d');

        $gl_posted = $this->post_savings_to_gl(
            $trans->account,
            floatval($trans->amount),
            $trans->paymethod,
            $account_cat,
            $void_receipt,
            $void_trans_date,
            $pid,
            $member_id,
            $customer_name,
            $void_system_comment,
            $void_trans_type
        );

        // Verify if reversing receipt is posted; if not, fallback to posting all unposted savings transactions for this account
        $is_void_receipt_posted = $this->is_savings_receipt_posted_to_gl($void_receipt);
        if (!$gl_posted || !$is_void_receipt_posted) {
            $fallback_result = $this->post_savings_account_to_gl($trans->account);
            $is_void_receipt_posted = $this->is_savings_receipt_posted_to_gl($void_receipt);

            if (!$is_void_receipt_posted) {
                return array(
                    'success' => true,
                    'message' => 'Transaction voided but GL posting is still pending. Please use Post to GL.',
                    'void_receipt' => $void_receipt,
                    'gl_posted' => false,
                    'fallback_posted' => isset($fallback_result['posted']) ? $fallback_result['posted'] : 0,
                    'fallback_failed' => isset($fallback_result['failed']) ? $fallback_result['failed'] : 0
                );
            }
        }

        return array('success' => true, 'message' => 'Transaction voided and posted to GL successfully', 'void_receipt' => $void_receipt, 'gl_posted' => true);
    }
    
    function get_next_savings_transaction_id() {
        $query = $this->db->query("SELECT MAX(id) as id FROM savings_transaction")->row();
        return $query && $query->id ? ($query->id + 1) : 1;
    }

    /**
     * Get savings transactions for an account that have not been posted to GL.
     *
     * @param string $account Savings account number
     * @return array List of savings_transaction rows
     */
    function get_unposted_savings_transactions($account) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $account_esc = $this->db->escape($account);
        // Use NOT IN subquery to avoid join condition quoting issues with CodeIgniter
        $subquery = "SELECT gl.refferenceID FROM general_ledger gl WHERE gl.fromtable = 'savings_transaction' AND gl.PIN = " . $pin_esc;
        $sql = "SELECT st.* FROM savings_transaction st WHERE st.account = " . $account_esc . " AND st.PIN = " . $pin_esc
            . " AND st.receipt NOT IN (" . $subquery . ") ORDER BY st.trans_date ASC";
        return $this->db->query($sql)->result();
    }

    /**
     * Post all unposted savings transactions for an account to the General Ledger.
     *
     * @param string $account Savings account number
     * @return array ['posted' => int, 'failed' => int, 'errors' => array of strings]
     */
    function post_savings_account_to_gl($account) {
        $unposted = $this->get_unposted_savings_transactions($account);
        $posted = 0;
        $failed = 0;
        $errors = array();
        $account_info = $this->saving_account_balance($account);
        if (!$account_info) {
            return array('posted' => 0, 'failed' => 0, 'errors' => array('Account not found'));
        }
        $member_id = isset($account_info->member_id) ? $account_info->member_id : '';
        $pid = isset($account_info->RFID) ? $account_info->RFID : '';
        foreach ($unposted as $st) {
            $trans_date = isset($st->trans_date) ? date('Y-m-d', strtotime($st->trans_date)) : date('Y-m-d');
            $ok = $this->post_savings_to_gl(
                $st->account,
                floatval($st->amount),
                isset($st->paymethod) ? $st->paymethod : 'Cash',
                $st->account_cat,
                $st->receipt,
                $trans_date,
                $pid,
                $member_id,
                isset($st->customer_name) ? $st->customer_name : '',
                isset($st->system_comment) ? $st->system_comment : '',
                isset($st->trans_type) ? $st->trans_type : ''
            );
            if ($ok) {
                $posted++;
            } else {
                $failed++;
                $errors[] = 'Receipt ' . $st->receipt . ': post failed';
            }
        }
        return array('posted' => $posted, 'failed' => $failed, 'errors' => $errors);
    }

    /**
     * Post a single savings transaction receipt to GL.
     *
     * @param string $receipt Savings transaction receipt
     * @return array ['success' => bool, 'message' => string]
     */
    function post_savings_receipt_to_gl($receipt) {
        $pin = current_user()->PIN;

        if (empty($receipt)) {
            return array('success' => false, 'message' => 'Invalid receipt');
        }

        $this->db->where('receipt', $receipt);
        $this->db->where('PIN', $pin);
        $st = $this->db->get('savings_transaction')->row();

        if (!$st) {
            return array('success' => false, 'message' => 'Transaction not found');
        }

        if ($this->is_savings_receipt_posted_to_gl($receipt)) {
            return array('success' => true, 'message' => 'Transaction already posted to GL');
        }

        $account_info = $this->saving_account_balance($st->account);
        if (!$account_info) {
            return array('success' => false, 'message' => 'Account not found for GL posting');
        }

        $member_id = isset($account_info->member_id) ? $account_info->member_id : '';
        $pid = isset($st->PID) ? $st->PID : (isset($account_info->RFID) ? $account_info->RFID : '');
        $account_cat = isset($st->account_cat) ? $st->account_cat : (isset($account_info->account_cat) ? $account_info->account_cat : '');
        $trans_date = isset($st->trans_date) ? date('Y-m-d', strtotime($st->trans_date)) : date('Y-m-d');

        $ok = $this->post_savings_to_gl(
            $st->account,
            floatval($st->amount),
            isset($st->paymethod) ? $st->paymethod : 'Cash',
            $account_cat,
            $st->receipt,
            $trans_date,
            $pid,
            $member_id,
            isset($st->customer_name) ? $st->customer_name : '',
            isset($st->system_comment) ? $st->system_comment : '',
            isset($st->trans_type) ? $st->trans_type : ''
        );

        if (!$ok) {
            return array('success' => false, 'message' => 'Failed to post transaction to GL');
        }

        if (!$this->is_savings_receipt_posted_to_gl($receipt)) {
            return array('success' => false, 'message' => 'GL posting pending. Please try again');
        }

        return array('success' => true, 'message' => 'Transaction posted to GL successfully');
    }

    /**
     * Void/Reverse all GL postings for a savings account.
     * This deletes the general_ledger entries linked to the account's savings transactions.
     * 
     * @param string $account The savings account number
     * @return array Array with 'voided' count, 'failed' count, and 'errors' array
     */
    function void_savings_account_gl($account) {
        $pin = current_user()->PIN;
        
        // Get all receipts that have been posted to GL for this account
        // Query general_ledger directly and match with savings_transaction
        $this->db->distinct();
        $this->db->select('gl.refferenceID as receipt');
        $this->db->from('general_ledger gl');
        $this->db->where('gl.fromtable', 'savings_transaction');
        $this->db->where('gl.PIN', $pin);
        $this->db->where("gl.refferenceID IN (SELECT receipt FROM savings_transaction WHERE account = '" . $this->db->escape_str($account) . "' AND PIN = " . intval($pin) . ")", NULL, FALSE);
        $posted_transactions = $this->db->get()->result();
        
        if (empty($posted_transactions)) {
            return array('voided' => 0, 'failed' => 0, 'errors' => array());
        }
        
        $voided = 0;
        $failed = 0;
        $errors = array();
        
        // Start transaction
        $this->db->trans_start();
        
        foreach ($posted_transactions as $trans) {
            try {
                // Delete from general_ledger
                $this->db->where('refferenceID', $trans->receipt);
                $this->db->where('fromtable', 'savings_transaction');
                $this->db->where('PIN', $pin);
                $this->db->delete('general_ledger');
                
                $affected = $this->db->affected_rows();
                
                if ($affected > 0) {
                    $voided++;
                    log_message('info', 'Voided GL posting for savings receipt: ' . $trans->receipt);
                } else {
                    $failed++;
                    $errors[] = 'Receipt ' . $trans->receipt . ': no GL entries found to void';
                    log_message('warning', 'No GL entries found to void for receipt: ' . $trans->receipt);
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = 'Receipt ' . $trans->receipt . ': ' . $e->getMessage();
                log_message('error', 'Failed to void GL posting for receipt ' . $trans->receipt . ': ' . $e->getMessage());
            }
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed while voiding GL postings for account: ' . $account);
            return array('voided' => 0, 'failed' => count($posted_transactions), 'errors' => array('Database transaction failed'));
        }
        
        return array('voided' => $voided, 'failed' => $failed, 'errors' => $errors);
    }

    function saving_account_name($account) {
        $account_info = $this->saving_account_balance($account);
        if (empty($account_info)) {
            return '';
        }
        if ($account_info->tablename == 'members_grouplist') {
            $this->db->where('GID', $account_info->RFID);
            $rowdata = $this->db->get('members_grouplist')->row();
            return $rowdata ? $rowdata->name : '';
        } else if ($account_info->tablename == 'members') {
            $this->db->where('PID', $account_info->RFID);
            $rowdata = $this->db->get('members')->row();
            return $rowdata ? ($rowdata->firstname . ' ' . $rowdata->middlename . ' ' . $rowdata->lastname) : '';
        }
        return '';
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
        $reference_no = isset($main_array['reference_no']) ? trim($main_array['reference_no']) : '';
        $document_no = isset($main_array['document_no']) ? trim($main_array['document_no']) : '';
        
        // Ensure reference_no column exists (auto JV #)
        $has_ref_no = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'reference_no'")->row();
        if (!$has_ref_no) {
            $this->db->query("ALTER TABLE general_journal_entry ADD COLUMN reference_no VARCHAR(100) NULL DEFAULT NULL AFTER description");
            $has_ref_no = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'reference_no'")->row();
        }

        // Ensure document_no column exists (manual required Document No.)
        $has_doc_no = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'document_no'")->row();
        if (!$has_doc_no) {
            $after_col = $has_ref_no ? 'reference_no' : 'description';
            $this->db->query("ALTER TABLE general_journal_entry ADD COLUMN document_no VARCHAR(100) NULL DEFAULT NULL AFTER `$after_col`");
            $has_doc_no = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'document_no'")->row();
        }

        // Always allocate JV-{YYYY}{######} inside the transaction (entry date year)
        if ($has_ref_no) {
            $entry_year = !empty($main_array['entrydate']) ? date('Y', strtotime($main_array['entrydate'])) : date('Y');
            $reference_no = $this->get_next_journal_voucher_no($entry_year);
            $main_array['reference_no'] = $reference_no;
        }
        $reference_no_sql = ($has_ref_no && $reference_no !== '') ? $this->db->escape($reference_no) : 'NULL';
        $document_no_sql = ($has_doc_no && $document_no !== '') ? $this->db->escape($document_no) : 'NULL';
        
        // Check if reference_type column exists (Journal Voucher)
        $has_ref_type = $this->db->query("SHOW COLUMNS FROM general_journal_entry LIKE 'reference_type'")->row();

        $cols = array('entrydate', 'description');
        $vals = array($entrydate, $description);
        if ($has_ref_no) {
            $cols[] = 'reference_no';
            $vals[] = $reference_no_sql;
        }
        if ($has_doc_no) {
            $cols[] = 'document_no';
            $vals[] = $document_no_sql;
        }
        $cols[] = 'PIN';
        $vals[] = $pin_value;
        if ($has_ref_type) {
            $cols[] = 'reference_type';
            $vals[] = "'journal_voucher'";
        }
        $insert_sql = 'INSERT INTO general_journal_entry (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
        
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

        // Ensure sub-ledger link columns exist on general_journal
        $this->ensure_general_journal_link_columns();

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
        
        // Check if reference_type column exists in general_journal (Journal Voucher line items)
        $has_gj_ref_type = $this->db->query("SHOW COLUMNS FROM general_journal LIKE 'reference_type'")->row();
        
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
            
            // Set reference_type for JV line items when column exists
            if ($has_gj_ref_type) {
                $value['reference_type'] = 'journal_voucher';
            }
            
            // Ensure required fields are present
            if (!isset($value['entrydate']) && isset($main_array['entrydate'])) {
                $value['entrydate'] = $main_array['entrydate'];
            }

            // Sanitize optional sub-ledger link fields (avoid empty string on numeric cols)
            $link_keys = array('link_type', 'customerid', 'supplierid', 'LID', 'PID', 'member_id', 'invoiceid');
            foreach ($link_keys as $lk) {
                if (!array_key_exists($lk, $value) || $value[$lk] === null || $value[$lk] === '') {
                    unset($value[$lk]);
                }
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

    function count_saving_account($key=null, $account_type_filter=null, $status_filter=null, $gl_posted_filter=null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $this->db->from('members_account ma');
        $this->db->join('saving_account_type sat', 'ma.account_cat = sat.account AND sat.PIN = ' . $pin_esc, 'left');
        $this->db->where('ma.PIN', $pin);
        
        // Filter by account type (Special or MSO)
        if (!is_null($account_type_filter) && $account_type_filter != '' && $account_type_filter != 'all') {
            if ($account_type_filter == 'special') {
                // Special accounts: check account_setup prefix, account_type, or name/description contains "special"
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $pin_esc, 'left');
                $this->db->where("((sat.account_setup IS NOT NULL AND sat.account_setup != '' AND (LEFT(sat.account_setup, 2) = '10' OR ac.account_type = 10 OR ac.account_type = '10')) OR LOWER(sat.name) LIKE '%special%' OR LOWER(sat.description) LIKE '%special%')", NULL, FALSE);
            } else if ($account_type_filter == 'mso') {
                // MSO accounts: check account_setup prefix, account_type, or name/description contains "mso"
                $this->db->join('account_chart ac', 'sat.account_setup = ac.account AND ac.PIN = ' . $pin_esc, 'left');
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
        
        // Filter by GL posted status
        if (!is_null($gl_posted_filter) && $gl_posted_filter != '' && $gl_posted_filter != 'all') {
            if ($gl_posted_filter == 'posted') {
                $this->db->where("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") > 0", NULL, FALSE);
            } else if ($gl_posted_filter == 'not_posted') {
                $this->db->where("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") = 0", NULL, FALSE);
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

    function search_saving_account($key=null, $limit=40, $start=0, $account_type_filter=null, $status_filter=null, $gl_posted_filter=null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $this->db->select('ma.*, m.firstname, m.middlename, m.lastname, m.member_id as member_id_display, mg.name as group_name, sat.description as account_type_name, sat.account as account_type_code, sat.name as account_type_name_display');
        $this->db->select("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") AS gl_posted_count", FALSE);
        $this->db->select("(SELECT COUNT(*) FROM savings_transaction st LEFT JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . " AND gl.id IS NULL) AS unposted_count", FALSE);
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
        
        // Filter by GL posted status
        if (!is_null($gl_posted_filter) && $gl_posted_filter != '' && $gl_posted_filter != 'all') {
            if ($gl_posted_filter == 'posted') {
                $this->db->where("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") > 0", NULL, FALSE);
            } else if ($gl_posted_filter == 'not_posted') {
                $this->db->where("(SELECT COUNT(DISTINCT gl.id) FROM savings_transaction st INNER JOIN general_ledger gl ON gl.fromtable = 'savings_transaction' AND gl.refferenceID = st.receipt AND gl.PIN = st.PIN WHERE st.account = ma.account AND st.PIN = " . $pin_esc . ") = 0", NULL, FALSE);
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

        // Explicit NULL for inherit (CI2 may otherwise omit or stringify null)
        if (array_key_exists('interest_frequency', $data) && $data['interest_frequency'] === null) {
            $this->db->set('interest_frequency', 'NULL', FALSE);
            unset($data['interest_frequency']);
        }

        if (!empty($data)) {
            return $this->db->update('members_account', $data);
        }
        return $this->db->update('members_account');
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

    /* =====================================================================
     * AUTOMATED SAVINGS INTEREST POSTING (MONTHLY / QUARTERLY)
     * ===================================================================== */

    /**
     * Resolve effective interest frequency for a member account.
     * Account override wins when set; otherwise product default is used.
     *
     * @param string|null $account_override members_account.interest_frequency (NULL/empty = inherit)
     * @param string|null $type_frequency saving_account_type.interest_frequency
     * @return array ('frequency' => NONE|MONTHLY|QUARTERLY, 'source' => INHERIT|OVERRIDE)
     */
    function effective_interest_frequency($account_override, $type_frequency) {
        $override = strtoupper(trim((string) $account_override));
        if ($override !== '' && in_array($override, array('NONE', 'MONTHLY', 'QUARTERLY'))) {
            return array('frequency' => $override, 'source' => 'OVERRIDE');
        }
        $type_freq = strtoupper(trim((string) $type_frequency));
        if (!in_array($type_freq, array('NONE', 'MONTHLY', 'QUARTERLY'))) {
            $type_freq = 'NONE';
        }
        return array('frequency' => $type_freq, 'source' => 'INHERIT');
    }

    /**
     * Normalize interest frequency override from a form value.
     * Empty / INHERIT => NULL (store as inherit product default).
     */
    function normalize_interest_frequency_override($value) {
        $value = strtoupper(trim((string) $value));
        if ($value === '' || $value === 'INHERIT') {
            return null;
        }
        if (in_array($value, array('NONE', 'MONTHLY', 'QUARTERLY'))) {
            return $value;
        }
        return null;
    }

    /**
     * Savings account types eligible for interest posting.
     * Includes types with rate > 0 where the product frequency is not NONE,
     * OR at least one active account has a MONTHLY/QUARTERLY override.
     *
     * @param string|null $frequency Optional filter: 'MONTHLY' or 'QUARTERLY' (product default only; posting uses effective freq)
     * @return array saving_account_type rows
     */
    function interest_enabled_account_types($frequency = null) {
        $pin = current_user()->PIN;
        $pin_esc = $this->db->escape($pin);
        $has_override_col = $this->db->field_exists('interest_frequency', 'members_account');

        $this->db->where('PIN', $pin);
        $this->db->where('interest_rate >', 0);
        if (!is_null($frequency)) {
            $this->db->where('interest_frequency', $frequency);
        } else if ($has_override_col) {
            // Product default enabled, OR any active account override enables interest
            $this->db->where("(interest_frequency != 'NONE' OR account IN (
                SELECT DISTINCT ma.account_cat FROM members_account ma
                WHERE ma.PIN = {$pin_esc}
                AND (ma.status = '1' OR ma.status IS NULL)
                AND UPPER(TRIM(ma.interest_frequency)) IN ('MONTHLY','QUARTERLY')
            ))", NULL, FALSE);
        } else {
            $this->db->where('interest_frequency !=', 'NONE');
        }
        $this->db->order_by('account', 'ASC');
        return $this->db->get('saving_account_type')->result();
    }

    /**
     * Compute interest for every active savings account of a given type for a period.
     *
     * Interest = base_balance x (annual_rate / 100) x days_in_period / 365
     * where base_balance depends on the type's interest_basis:
     *  - ADB: average of the daily end-of-day balances over the period
     *  - LOWEST: lowest end-of-day balance within the period
     *  - EOP: balance at the end of the period
     *
     * Balances include the maintaining balance (virtual_balance) since it is
     * still the member's deposit.
     *
     * Effective frequency = account override if set, else product default.
     * When $run_frequency is MONTHLY or QUARTERLY, only accounts whose
     * effective frequency matches are included (others omitted from the list).
     *
     * @param object $type saving_account_type row (with interest config)
     * @param string $period_start Y-m-d (first day of period)
     * @param string $period_end Y-m-d (last day of period)
     * @param string|null $run_frequency MONTHLY|QUARTERLY — filter by effective frequency
     * @return array list of computation rows (one per account)
     */
    function compute_interest_for_period($type, $period_start, $period_end, $run_frequency = null) {
        $pin = current_user()->PIN;
        $results = array();

        $annual_rate = (float) $type->interest_rate;
        if ($annual_rate <= 0) {
            return $results;
        }

        $start_ts = strtotime($period_start);
        $end_ts = strtotime($period_end);
        $days_in_period = (int) round(($end_ts - $start_ts) / 86400) + 1;
        if ($days_in_period <= 0) {
            return $results;
        }

        $basis = strtoupper(trim($type->interest_basis));
        if (!in_array($basis, array('ADB', 'LOWEST', 'EOP'))) {
            $basis = 'ADB';
        }
        $min_balance = isset($type->interest_min_balance) ? (float) $type->interest_min_balance : 0;
        $run_frequency = strtoupper(trim((string) $run_frequency));
        if (!in_array($run_frequency, array('MONTHLY', 'QUARTERLY'))) {
            // Backward compatible: use product frequency when caller does not pass a run frequency
            $run_frequency = strtoupper(trim((string) $type->interest_frequency));
        }

        $has_override_col = $this->db->field_exists('interest_frequency', 'members_account');
        $freq_select = $has_override_col ? 'ma.interest_frequency AS account_interest_frequency' : "NULL AS account_interest_frequency";

        // Active accounts of this type with member/group names
        $this->db->select("ma.account, ma.RFID, ma.member_id, ma.balance, ma.virtual_balance, ma.account_cat, {$freq_select}, COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.firstname,''),' ',COALESCE(m.lastname,''))), ''), mg.name, '') AS holder_name", FALSE);
        $this->db->from('members_account ma');
        $this->db->join('members m', "ma.RFID = m.PID AND m.PIN = ma.PIN AND ma.tablename = 'members'", 'left');
        $this->db->join('members_grouplist mg', "ma.RFID = mg.GID AND mg.PIN = ma.PIN AND ma.tablename = 'members_grouplist'", 'left');
        $this->db->where('ma.PIN', $pin);
        $this->db->where('ma.account_cat', $type->account);
        $this->db->where("(ma.status = '1' OR ma.status IS NULL)", NULL, FALSE);
        $this->db->order_by('ma.account', 'ASC');
        $accounts = $this->db->get()->result();

        foreach ($accounts as $acc) {
            $effective = $this->effective_interest_frequency(
                isset($acc->account_interest_frequency) ? $acc->account_interest_frequency : null,
                $type->interest_frequency
            );

            // Only include accounts matching the posting run frequency
            if (!in_array($run_frequency, array('MONTHLY', 'QUARTERLY')) || $effective['frequency'] !== $run_frequency) {
                continue;
            }

            $current_total = (float) $acc->balance + (float) $acc->virtual_balance;

            $bases = $this->savings_balance_bases($acc->account, $current_total, $period_start, $period_end);

            if ($basis == 'LOWEST') {
                $base_balance = $bases['lowest'];
            } else if ($basis == 'EOP') {
                $base_balance = $bases['eop'];
            } else {
                $base_balance = $bases['adb'];
            }
            // Guard against negative balances from historical data inconsistencies
            $base_balance = max(0, round($base_balance, 2));

            $existing = $this->get_interest_posting($acc->account, $period_start, $period_end);
            $already_posted = ($existing && strtoupper($existing->status) == 'POSTED');

            $interest = 0;
            $eligible = true;
            $skip_reason = '';
            if ($base_balance < $min_balance || $base_balance <= 0) {
                $eligible = false;
                $skip_reason = 'BELOW_MIN_BALANCE';
            } else {
                $interest = round($base_balance * ($annual_rate / 100) * $days_in_period / 365, 2);
                if ($interest <= 0) {
                    $eligible = false;
                    $skip_reason = 'ZERO_INTEREST';
                }
            }
            if ($already_posted) {
                $eligible = false;
                $skip_reason = 'ALREADY_POSTED';
            }

            $results[] = array(
                'account' => $acc->account,
                'account_cat' => $acc->account_cat,
                'member_id' => $acc->member_id,
                'holder_name' => trim($acc->holder_name),
                'RFID' => $acc->RFID,
                'current_balance' => $current_total,
                'basis' => $basis,
                'base_balance' => $base_balance,
                'annual_rate' => $annual_rate,
                'days' => $days_in_period,
                'interest' => $interest,
                'eligible' => $eligible,
                'skip_reason' => $skip_reason,
                'effective_frequency' => $effective['frequency'],
                'frequency_source' => $effective['source'],
            );
        }

        return $results;
    }

    /**
     * Reconstruct daily end-of-day balances of a savings account over a period.
     *
     * The current total balance (balance + virtual_balance) is used as the
     * anchor, and signed transaction amounts (CR +, DR -) are walked backward
     * to the end of the period and through it day by day. Recorded transaction
     * amounts always include the maintaining-balance portion, so the math is
     * consistent with the total balance.
     *
     * @return array ('adb' => float, 'lowest' => float, 'eop' => float)
     */
    private function savings_balance_bases($account, $current_total, $period_start, $period_end) {
        $pin = current_user()->PIN;

        // Void reversal rows are excluded: they are recorded in the ledger but
        // do not move members_account.balance, which is our anchor.
        $not_void = "COALESCE(system_comment, '') NOT LIKE 'VOID TRANSACTION%'";

        // Net movement after the period end -> balance at period end
        $this->db->select("COALESCE(SUM(CASE WHEN trans_type = 'CR' THEN amount ELSE -amount END), 0) AS net", FALSE);
        $this->db->from('savings_transaction');
        $this->db->where('PIN', $pin);
        $this->db->where('account', $account);
        $this->db->where($not_void, NULL, FALSE);
        $this->db->where('DATE(trans_date) >', $period_end);
        $net_after = (float) $this->db->get()->row()->net;
        $eop_balance = $current_total - $net_after;

        // Net movement per day inside the period
        $this->db->select("DATE(trans_date) AS tdate, COALESCE(SUM(CASE WHEN trans_type = 'CR' THEN amount ELSE -amount END), 0) AS net", FALSE);
        $this->db->from('savings_transaction');
        $this->db->where('PIN', $pin);
        $this->db->where('account', $account);
        $this->db->where($not_void, NULL, FALSE);
        $this->db->where('DATE(trans_date) >=', $period_start);
        $this->db->where('DATE(trans_date) <=', $period_end);
        $this->db->group_by('DATE(trans_date)');
        $daily_rows = $this->db->get()->result();

        $daily_net = array();
        foreach ($daily_rows as $row) {
            $daily_net[$row->tdate] = (float) $row->net;
        }

        // Walk backward from period end, computing each day's end-of-day balance
        $eod_balances = array();
        $running = $eop_balance;
        $ts = strtotime($period_end);
        $start_ts = strtotime($period_start);
        while ($ts >= $start_ts) {
            $date = date('Y-m-d', $ts);
            $eod_balances[$date] = $running;
            if (isset($daily_net[$date])) {
                $running -= $daily_net[$date];
            }
            $ts = strtotime('-1 day', $ts);
        }

        $count = count($eod_balances);
        $sum = 0;
        $lowest = null;
        foreach ($eod_balances as $bal) {
            $sum += $bal;
            if (is_null($lowest) || $bal < $lowest) {
                $lowest = $bal;
            }
        }

        return array(
            'adb' => ($count > 0) ? ($sum / $count) : 0,
            'lowest' => is_null($lowest) ? 0 : $lowest,
            'eop' => $eop_balance,
        );
    }

    /**
     * Get an interest posting log row for an account and period (if any).
     */
    function get_interest_posting($account, $period_start, $period_end) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('account', $account);
        $this->db->where('period_start', $period_start);
        $this->db->where('period_end', $period_end);
        return $this->db->get('savings_interest_posting')->row();
    }

    /**
     * Record an interest posting in the log (insert, or revive a VOIDED row).
     */
    function log_interest_posting($data) {
        $user = current_user();
        $data['PIN'] = $user->PIN;
        $data['createdby'] = $user->id;
        $data['status'] = 'POSTED';

        $existing = $this->get_interest_posting($data['account'], $data['period_start'], $data['period_end']);
        if ($existing) {
            return $this->db->update('savings_interest_posting', $data, array('id' => $existing->id));
        }
        return $this->db->insert('savings_interest_posting', $data);
    }

    /**
     * Mark an interest posting log row as VOIDED (called when the interest
     * transaction is voided), allowing the period to be re-posted.
     */
    function void_interest_posting_by_receipt($receipt) {
        // Safe no-op if the interest posting feature has not been installed yet
        if (!$this->db->table_exists('savings_interest_posting')) {
            return FALSE;
        }
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('receipt', $receipt);
        $this->db->where('status', 'POSTED');
        return $this->db->update('savings_interest_posting', array('status' => 'VOIDED'));
    }

    /**
     * Count interest posting log rows (for pagination).
     * NOTE: Resolve PIN before touching Active Record — calling current_user()
     * after from() pollutes the query builder (CI2 joins users and causes
     * "Column id is ambiguous").
     */
    function count_interest_posting_history($account_cat = null, $period_start = null) {
        $pin = current_user()->PIN;
        $this->db->from('savings_interest_posting sip');
        $this->db->where('sip.PIN', $pin);
        if (!is_null($account_cat) && $account_cat !== '') {
            $this->db->where('sip.account_cat', $account_cat);
        }
        if (!is_null($period_start) && $period_start !== '') {
            $this->db->where('sip.period_start', $period_start);
        }
        return $this->db->count_all_results();
    }

    /**
     * Interest posting log rows with member names (newest first).
     */
    function interest_posting_history($account_cat = null, $period_start = null, $limit = 40, $start = 0) {
        $pin = current_user()->PIN;
        $this->db->select("sip.*, sat.name AS type_name, COALESCE(NULLIF(TRIM(CONCAT(COALESCE(m.firstname,''),' ',COALESCE(m.lastname,''))), ''), mg.name, '') AS holder_name, ma.member_id AS member_id", FALSE);
        $this->db->from('savings_interest_posting sip');
        $this->db->join('saving_account_type sat', 'sip.account_cat = sat.account AND sat.PIN = ' . $this->db->escape($pin), 'left');
        $this->db->join('members_account ma', 'sip.account = ma.account AND ma.PIN = sip.PIN', 'left');
        $this->db->join('members m', "ma.RFID = m.PID AND m.PIN = ma.PIN AND ma.tablename = 'members'", 'left');
        $this->db->join('members_grouplist mg', "ma.RFID = mg.GID AND mg.PIN = ma.PIN AND ma.tablename = 'members_grouplist'", 'left');
        $this->db->where('sip.PIN', $pin);
        if (!is_null($account_cat) && $account_cat !== '') {
            $this->db->where('sip.account_cat', $account_cat);
        }
        if (!is_null($period_start) && $period_start !== '') {
            $this->db->where('sip.period_start', $period_start);
        }
        $this->db->order_by('sip.createdon', 'DESC');
        $this->db->order_by('sip.id', 'DESC');
        $this->db->limit((int) $limit, (int) $start);
        return $this->db->get()->result();
    }

    /**
     * Distinct interest posting periods for history filter dropdown.
     * Returns newest first: period_start, period_end, period_type, label.
     */
    function interest_posting_period_list($account_cat = null) {
        $pin = current_user()->PIN;
        $this->db->select('period_start, period_end, period_type', FALSE);
        $this->db->from('savings_interest_posting');
        $this->db->where('PIN', $pin);
        if (!is_null($account_cat) && $account_cat !== '') {
            $this->db->where('account_cat', $account_cat);
        }
        $this->db->group_by('period_start');
        $this->db->group_by('period_end');
        $this->db->group_by('period_type');
        $this->db->order_by('period_start', 'DESC');
        $rows = $this->db->get()->result();

        $periods = array();
        foreach ($rows as $row) {
            if (strtoupper($row->period_type) == 'QUARTERLY') {
                $label = 'Q' . ceil(date('n', strtotime($row->period_start)) / 3) . ' ' . date('Y', strtotime($row->period_start));
            } else {
                $label = strtoupper(date('M Y', strtotime($row->period_start)));
            }
            $periods[] = (object) array(
                'period_start' => $row->period_start,
                'period_end' => $row->period_end,
                'period_type' => $row->period_type,
                'label' => $label,
            );
        }
        return $periods;
    }

}
