<?php

class Report_Model extends CI_Model {

    function report_loan($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }

        return $this->db->get('report_table_loan');
    }

    function report_share($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }
        $this->db->where('user', $this->session->userdata('user_id'));
        return $this->db->get('report_table_share');
    }

    function report_contribution($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }
        $this->db->where('user', $this->session->userdata('user_id'));
        return $this->db->get('report_table_contribution');
    }
    function report_mortuary($id = null, $link = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($link)) {
            $this->db->where('link', $link);
        }
        $this->db->where('user', $this->session->userdata('user_id'));
        return $this->db->get('report_table_mortuary');
    }
    
    function report_saving($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }

        return $this->db->get('report_table_saving');
    }
    function report_saving1($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('description', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }

        return $this->db->get('report_table_saving');
    }

    function report_list($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }

        return $this->db->get('report_table');
    }

    function report_memberlist($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }

        return $this->db->get('report_table_member');
    }

    function report_list_journal($id = null, $ink = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($ink)) {
            $this->db->where('link', $ink);
        }

        return $this->db->get('report_table_journal');
    }

    function create_ledger_trans_summary($from, $until) {
        $return = array();
        $account_type = $this->finance_model->account_typelist()->result();
        foreach ($account_type as $key => $value) {
            $account_list = $this->finance_model->account_chart(null, null, $value->account)->result();
            foreach ($account_list as $key1 => $value1) {
                $previous = $this->ledger_trans_summary($from, $until, $value1->account, TRUE);
                $current = $this->ledger_trans_summary($from, $until, $value1->account);
                if (count($previous) > 0 || count($current) > 0) {
                    $balance = 0;
                    if (count($previous) > 0) {
                        $balance = $previous[0]->debit - $previous[0]->credit;
                    }
                    if ($value->id == 4 || $value->id == 5) {
                        if (count($current) > 0) {
                            $current = $current[0];
                            $return[$value->id][$value1->account] = array('balance' => $balance, 'current' => $current);
                        }
                    } else {
                        if (count($current) > 0) {
                            $current = $current[0];
                        }
                        $return[$value->id][$value1->account] = array('balance' => $balance, 'current' => $current);
                    }
                }
            }
        }

        return $return;
    }

    function get_balance_sheet_data($date, $category) {
        $pin = current_user()->PIN;
        
        // Validate inputs
        if (empty($date) || empty($category) || empty($pin)) {
            return array();
        }
        
        // Ensure date is in correct format
        $date = date('Y-m-d', strtotime($date));
        if ($date === false || $date === '1970-01-01') {
            return array();
        }
        
        // Escape inputs for security
        $pin = $this->db->escape($pin);
        $date = $this->db->escape($date);
        // Handle both integer and string account_type values
        // account_chart.account_type stores the account type code (10000 for Assets, 20000 for Liabilities, 30000 for Equity)
        // which should match account_type.account
        $category_int = (int)$category;
        $category_str = $this->db->escape($category);
        
        $sql = "SELECT 
                    general_ledger.account as account, 
                    account_chart.account_type as account_type,
                    account_chart.name as name, 
                    SUM(general_ledger.credit) as credit,
                    SUM(general_ledger.debit) as debit
                FROM general_ledger 
                INNER JOIN account_chart ON account_chart.account = general_ledger.account 
                WHERE general_ledger.PIN = account_chart.PIN 
                    AND account_chart.PIN = $pin 
                    AND general_ledger.date <= $date 
                    AND (
                        CAST(account_chart.account_type AS UNSIGNED) = $category_int
                        OR account_chart.account_type = $category_str
                    )
                GROUP BY general_ledger.account, account_chart.name, account_chart.account_type
                ORDER BY general_ledger.account ASC";

        return $this->db->query($sql)->result();
    }

    function ledger_trans_summary($from, $until, $account = null, $previous = false) {
        $pin = current_user()->PIN;
        if ($previous) {
            $sql = "SELECT general_ledger.account as account,account_chart.account_type as account_type,account_chart.name as name,SUM(general_ledger.credit) as credit,SUM(general_ledger.debit) as debit
                    FROM general_ledger INNER JOIN account_chart ON
                account_chart.account=general_ledger.account WHERE general_ledger.PIN=account_chart.PIN AND account_chart.PIN='$pin' AND general_ledger.date < '$from' ";
        } else {
            $sql = "SELECT general_ledger.account as account,account_chart.account_type as account_type,account_chart.name as name,SUM(general_ledger.credit) as credit,SUM(general_ledger.debit) as debit
                    FROM general_ledger INNER JOIN account_chart ON
                account_chart.account=general_ledger.account WHERE general_ledger.PIN=account_chart.PIN AND account_chart.PIN='$pin' AND general_ledger.date >= '$from' AND general_ledger.date <= '$until'";
        }

        if (!is_null($account)) {
            $sql .= " AND general_ledger.account = '$account'";
        }


        $sql.=" GROUP BY general_ledger.account ORDER BY general_ledger.date ASC, general_ledger.journalID ASC,general_ledger.refferenceID ASC";

        return $this->db->query($sql)->result();
    }

    function ledger_trans($from, $until, $account = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT general_ledger.*,account_chart.name,(SELECT type FROM journal WHERE id=general_ledger.journalID) as trans_comment FROM general_ledger INNER JOIN account_chart ON
                account_chart.account=general_ledger.account WHERE general_ledger.PIN=account_chart.PIN AND account_chart.PIN='$pin' AND general_ledger.date >= '$from' AND general_ledger.date <= '$until' AND general_ledger.account_type != 30 ";

        if (!is_null($account)) {
            $sql .= " AND general_ledger.account = '$account'";
        }
        $sql.=" ORDER BY general_ledger.date ASC,general_ledger.entryid ASC,general_ledger.debit DESC";

        return $this->db->query($sql)->result();
    }

    /**
     * Get related entity (Person/Member/Item) name and URL for a GL transaction row.
     * Returns array('name' => string, 'url' => string). Empty url means no link.
     */
    function get_gl_related_entity($row) {
        $name = '';
        $url = '';
        $pin = current_user()->PIN;
        $fromtable = isset($row->fromtable) ? $row->fromtable : '';
        $ref = isset($row->refferenceID) ? $row->refferenceID : null;
        if (empty($fromtable) || ($ref === null && $ref !== '0' && $ref !== 0)) {
            return array('name' => '', 'url' => '');
        }
        $ref_int = (is_numeric($ref) || ctype_digit((string)$ref)) ? (int)$ref : 0;

        switch ($fromtable) {
            case 'sales_invoice':
                $inv = $this->db->query('SELECT customerid FROM sales_invoice WHERE id = ? AND PIN = ? LIMIT 1', array($ref_int, $pin))->row();
                if ($inv && !empty($inv->customerid)) {
                    $c = $this->db->query('SELECT id, name FROM customer WHERE customerid = ? AND PIN = ? LIMIT 1', array($inv->customerid, $pin))->row();
                    if ($c) {
                        $name = $c->name;
                        $url = current_lang() . '/customer/sales_invoice_view/' . encode_id($ref_int);
                    }
                }
                break;
            case 'purchase_invoice':
                $inv = $this->db->query('SELECT supplierid FROM purchase_invoice WHERE id = ? AND PIN = ? LIMIT 1', array($ref_int, $pin))->row();
                if ($inv && !empty($inv->supplierid)) {
                    $s = $this->db->query('SELECT id, name FROM supplier WHERE supplierid = ? AND PIN = ? LIMIT 1', array($inv->supplierid, $pin))->row();
                    if ($s) {
                        $name = $s->name;
                        $url = current_lang() . '/supplier/purchase_invoice_view/' . encode_id($ref_int);
                    }
                }
                break;
            case 'journal_entry':
                $je = $this->db->query('SELECT reference_type, reference_id FROM journal_entry WHERE id = ? AND PIN = ? LIMIT 1', array($ref_int, $pin))->row();
                if ($je && isset($je->reference_type)) {
                    if ($je->reference_type === 'cash_receipt' && !empty($je->reference_id)) {
                        $r = $this->db->query('SELECT received_from FROM cash_receipts WHERE id = ? AND PIN = ? LIMIT 1', array((int)$je->reference_id, $pin))->row();
                        if ($r) {
                            $name = $r->received_from;
                            $url = current_lang() . '/cash_receipt/cash_receipt_view/' . encode_id((int)$je->reference_id);
                        }
                    } elseif ($je->reference_type === 'cash_disbursement' && !empty($je->reference_id)) {
                        $d = $this->db->query('SELECT paid_to FROM cash_disbursements WHERE id = ? AND PIN = ? LIMIT 1', array((int)$je->reference_id, $pin))->row();
                        if ($d) {
                            $name = $d->paid_to;
                            $url = current_lang() . '/cash_disbursement/cash_disbursement_view/' . encode_id((int)$je->reference_id);
                        }
                    }
                }
                if ($name === '' && $ref_int > 0) {
                    $url = current_lang() . '/finance/journal_entry_view/' . encode_id($ref_int);
                    $name = 'Journal Entry #' . $ref_int;
                }
                break;
            case 'loan_contract':
            case 'loan_contract_repayment':
                $LID = isset($row->LID) ? $row->LID : null;
                if (!$LID && $ref) {
                    $LID = $ref;
                }
                if ($LID) {
                    if ($fromtable === 'loan_contract_repayment' && $ref_int > 0) {
                        $rep = $this->db->query('SELECT LID FROM loan_contract_repayment WHERE id = ? AND PIN = ? LIMIT 1', array($ref_int, $pin))->row();
                        if ($rep) $LID = $rep->LID;
                    }
                    $lc = $this->db->query('SELECT member_id, PID FROM loan_contract WHERE LID = ? AND PIN = ? LIMIT 1', array($LID, $pin))->row();
                    if ($lc && (isset($lc->member_id) || isset($lc->PID))) {
                        $m = $this->db->query('SELECT CONCAT(firstname, " ", middlename, " ", lastname) AS name, member_id FROM members WHERE PID = ? AND PIN = ? LIMIT 1', array($lc->PID, $pin))->row();
                        if ($m) {
                            $name = trim($m->name) . ' (' . $LID . ')';
                            $url = current_lang() . '/loan/view_repayment_schedule/' . $LID;
                        }
                    }
                }
                break;
            case 'loan_beginning_balances':
                $lb = $this->db->query('SELECT member_id FROM loan_beginning_balances WHERE id = ? AND PIN = ? LIMIT 1', array($ref_int, $pin))->row();
                if ($lb && !empty($lb->member_id)) {
                    $m = $this->db->query('SELECT CONCAT(firstname, " ", middlename, " ", lastname) AS name FROM members WHERE member_id = ? AND PIN = ? LIMIT 1', array($lb->member_id, $pin))->row();
                    if ($m) {
                        $name = trim($m->name);
                        $url = current_lang() . '/report_member/member_profile/?member=' . urlencode($lb->member_id);
                    }
                }
                break;
            case 'member_registrationfee':
                $mr = $this->db->query('SELECT PID FROM member_registrationfee WHERE id = ? AND PIN = ? LIMIT 1', array($ref_int, $pin))->row();
                if ($mr && !empty($mr->PID)) {
                    $m = $this->db->query('SELECT CONCAT(firstname, " ", middlename, " ", lastname) AS name, member_id FROM members WHERE PID = ? AND PIN = ? LIMIT 1', array($mr->PID, $pin))->row();
                    if ($m) {
                        $name = trim($m->name);
                        $url = current_lang() . '/report_member/member_profile/?member=' . urlencode($m->member_id);
                    }
                }
                break;
            case 'contribution_settings':
                $ct = $this->db->query('SELECT PID FROM contribution_transaction WHERE receipt = ? AND PIN = ? LIMIT 1', array($ref, $pin))->row();
                if ($ct && !empty($ct->PID)) {
                    $m = $this->db->query('SELECT CONCAT(firstname, " ", middlename, " ", lastname) AS name, member_id FROM members WHERE PID = ? AND PIN = ? LIMIT 1', array($ct->PID, $pin))->row();
                    if ($m) {
                        $name = trim($m->name);
                        $url = current_lang() . '/report_member/member_profile/?member=' . urlencode($m->member_id);
                    }
                }
                break;
            case 'savings_transaction':
                $st = $this->db->query('SELECT account FROM savings_transaction WHERE receipt = ? AND PIN = ? LIMIT 1', array($ref, $pin))->row();
                if ($st && !empty($st->account)) {
                    $ma = $this->db->query('SELECT account, RFID FROM members_account WHERE account = ? AND PIN = ? LIMIT 1', array($st->account, $pin))->row();
                    if ($ma && !empty($ma->RFID)) {
                        $m = $this->db->query('SELECT CONCAT(firstname, " ", middlename, " ", lastname) AS name, member_id FROM members WHERE PID = ? AND PIN = ? LIMIT 1', array($ma->RFID, $pin))->row();
                        if ($m) {
                            $name = trim($m->name);
                            $url = current_lang() . '/saving/receipt_view/' . $ref;
                        }
                    }
                }
                break;
            case 'general_journal':
                if ($ref_int > 0) {
                    $name = 'Journal Entry #' . $ref_int;
                    $url = current_lang() . '/finance/journal_entry_view/' . encode_id($ref_int);
                }
                break;
            default:
                break;
        }
        return array('name' => $name, 'url' => $url);
    }

    function journal_trans($from, $until, $journal_id) {
        $pin = current_user()->PIN;
        
        // Validate inputs
        if (empty($from) || empty($until) || empty($journal_id) || empty($pin)) {
            return array();
        }
        
        // Ensure dates are in correct format
        $from = date('Y-m-d', strtotime($from));
        $until = date('Y-m-d', strtotime($until));
        if ($from === false || $from === '1970-01-01' || $until === false || $until === '1970-01-01') {
            return array();
        }
        
        // Escape inputs for security
        $pin = $this->db->escape($pin);
        $from = $this->db->escape($from);
        $until = $this->db->escape($until);
        
        // Store original journal_id before escaping for mapping lookup
        $journal_id_int = (int)$journal_id;
        $journal_id = $this->db->escape($journal_id);
        
        // Check if journal table has a journalID field or if we need to use the id directly
        // Sometimes transactions are posted with a different journalID than the journal type id
        // Try to get the actual journalID from the journal table if it exists
        $journal_info = $this->db->query("SELECT * FROM journal WHERE id = $journal_id LIMIT 1")->row();
        $actual_journal_id = null;
        
        // If journal table has a journalID field, use it
        if ($journal_info && isset($journal_info->journalID) && !empty($journal_info->journalID)) {
            $actual_journal_id = $this->db->escape($journal_info->journalID);
        }
        
        // If no journalID field found, check for known mappings
        // Common mapping: journal type 3 uses journalID 5
        if (!$actual_journal_id) {
            $journal_type_mappings = array(
                3 => 5,  // Journal type 3 maps to journalID 5
                // Add other mappings here if needed
            );
            
            if (isset($journal_type_mappings[$journal_id_int])) {
                $actual_journal_id = $this->db->escape($journal_type_mappings[$journal_id_int]);
            }
        }
        
        // Build the journalID condition - check both the journal type id and mapped journalID
        // This ensures we catch transactions regardless of which journalID was used
        if ($actual_journal_id && $actual_journal_id != $journal_id) {
            // Check both the original journal type ID and the mapped journalID
            $journal_id_condition = "(general_ledger.journalID = $journal_id OR general_ledger.journalID = $actual_journal_id)";
        } else if ($actual_journal_id) {
            // Use the mapped journalID if it's the same as journal_id
            $journal_id_condition = "general_ledger.journalID = $actual_journal_id";
        } else {
            // Use the journal type ID directly
            $journal_id_condition = "general_ledger.journalID = $journal_id";
        }
        
        $sql = "SELECT general_ledger.*,
                account_chart.name,
                general_ledger.entryid,
                (SELECT type FROM journal WHERE id=general_ledger.journalID) as trans_comment 
                FROM general_ledger 
                INNER JOIN account_chart ON account_chart.account = general_ledger.account 
                WHERE general_ledger.PIN = account_chart.PIN 
                    AND account_chart.PIN = $pin 
                    AND general_ledger.date >= $from 
                    AND general_ledger.date <= $until 
                    AND general_ledger.account_type != 3 
                    AND $journal_id_condition
                ORDER BY general_ledger.date ASC, general_ledger.entryid ASC, general_ledger.debit DESC";

        $result = $this->db->query($sql)->result();
        
        // Debug: If no results and we're using a mapped journalID, log it
        if (empty($result) && $actual_journal_id) {
            log_message('debug', "journal_trans: No results found for journal type ID $journal_id_int, mapped journalID $actual_journal_id, date range $from to $until, PIN $pin");
        }
        
        return $result;
    }

    function registration_fee_collection($fromdate, $todate) {
        $pin = current_user()->PIN;
        $sql = "SELECT member_registrationfee.member_id,member_registrationfee.date,member_registrationfee.credit as amount, CONCAT(members.firstname, ' ',members.middlename,' ',members.lastname) as name 
FROM member_registrationfee INNER JOIN members ON member_registrationfee.member_id= members.member_id WHERE members.PIN=member_registrationfee.PIN AND members.PIN='$pin' AND member_registrationfee.date >= '$fromdate' AND member_registrationfee.date <= '$todate'
                ORDER BY member_registrationfee.date ASC,name ASC ";

        return $this->db->query($sql)->result();
    }
    
    function loan_processing_fee_collection($fromdate, $todate) {
       $pin = current_user()->PIN;
        $sql = "SELECT * FROM loanprocessing_fee WHERE PIN='$pin' AND  createdon >= '$fromdate' AND createdon <= '$todate'  ORDER BY createdon ASC";
        return $this->db->query($sql)->result();
    }


    function account_saving_balance($fromdate, $todate, $account_type = '') {
        $pin = current_user()->PIN;
        $sql = "SELECT members_account.*, members.member_id as members_member_id FROM members_account LEFT JOIN members ON members_account.RFID = members.PID AND members.PIN = '$pin' WHERE members_account.PIN='$pin' AND members_account.createdon >= '$fromdate 00:00:00' AND members_account.createdon <= '$todate 23:59:59' ";
        if ($account_type != '') {
            $sql.= " AND members_account.account_cat='$account_type' ";
        }
        $sql.=" ORDER BY ABS(members_account.member_id) ASC";

        return $this->db->query($sql)->result();
    }

    function account_contribution_balance($fromdate, $todate) {
        $pin = current_user()->PIN;
        //$sql = "SELECT members.PID,members.member_id,CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name,members_contribution.balance FROM members LEFT JOIN members_contribution ON members.PID=members_contribution.PID  WHERE members.PIN='$pin' AND members.joiningdate >= '$fromdate 00:00:00' AND members.joiningdate <= '$todate 23:59:59' ";
        $sql = "SELECT members.PID,members.member_id,CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name,members_contribution.balance FROM members LEFT JOIN members_contribution ON members.PID=members_contribution.PID  WHERE members.PIN='$pin' AND members.joiningdate <= '$todate 23:59:59' ";
        $sql.=" ORDER BY members.PID ASC";

        return $this->db->query($sql)->result();
    }
    function account_mortuary_balance($fromdate, $todate, $status='') {
        $pin = current_user()->PIN;
        $sql = "SELECT members.PID,members.member_id,CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name,members_mortuary.balance, mortuary_settings.status_flag as status 
        FROM members 
        LEFT JOIN members_mortuary ON members.PID=members_mortuary.PID 
        RIGHT JOIN mortuary_settings ON members.PID=mortuary_settings.PID AND mortuary_settings.PIN='$pin'
        WHERE mortuary_settings.PIN='$pin' AND members.joiningdate >= '$fromdate 00:00:00' AND members.joiningdate <= '$todate 23:59:59' ";
        if($status<>'' || $status<>'0'){
            $sql.=" AND mortuary_settings.status_flag='".$status."'";
        }
        $sql.=" ORDER BY ABS(members.member_id) ASC";
        
        return $this->db->query($sql)->result();
    }
    
    function share_balance($fromdate, $todate) {
        $pin = current_user()->PIN;
        $sql = "SELECT members.PID,members.member_id,CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name,
                members_share.amount,members_share.totalshare,members_share.remainbalance FROM members LEFT JOIN members_share ON members.PID=members_share.PID  WHERE members.PIN='$pin' AND members.joiningdate >= '$fromdate 00:00:00' AND members.joiningdate <= '$todate 23:59:59' ";

        $sql.=" ORDER BY members.PID ASC";

        return $this->db->query($sql)->result();
    }

    function member_list_data($fromdate, $todate, $column) {
        $pin = current_user()->PIN;
        $sql = "SELECT members.PID as internal, ";
        $table = array();
        foreach ($column as $key => $value) {
            $tmp = explode('.', $value);
            $table[$tmp[0]] = $tmp[0];

            $sql.= $value . ' AS ' . str_replace('.', '', $value) . ', ';
        }

        $sql = rtrim($sql, ', ');

        $sql.= ' FROM members';
        if (in_array('members_contact', $table)) {
            $sql.=' LEFT JOIN members_contact ON members_contact.PID=members.PID';
        }
        if (in_array('members_nextkin', $table)) {
            $sql.='  LEFT JOIN members_nextkin ON members_nextkin.PID=members.PID';
        }

        $sql.= " WHERE members.PIN='$pin' AND members.joiningdate >= '$fromdate' AND members.joiningdate <= '$todate' ORDER BY internal ASC";

        return $this->db->query($sql)->result();
    }

    function saving_account_name($refference, $table) {
        $sql = '';
        if ($table == 'members') {
            $sql = "SELECT CONCAT(firstname,' ',middlename,' ',lastname) as name FROM members where PID='$refference'";
        }
        if ($sql != '') {
            $result = $this->db->query($sql)->row();
            return $result->name;
        }
        return '';
    }

    function account_saving_statement($fromdate, $until, $account) {
        $pin = current_user()->PIN;
        $sql = "SELECT  id, amount, account, trans_date,comment,system_comment,trans_type,paymethod,receipt,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance, (SELECT SUM(CASE when trans_type = 'CR' then amount else 0 end) FROM savings_transaction WHERE account='$account' AND PIN='$pin' AND trans_date < '$fromdate 00:00:00' AND comment NOT LIKE 'VOID-%' AND receipt NOT IN (SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(vt.comment, ' ', 1), 'VOID-', -1) FROM savings_transaction vt WHERE vt.account='$account' AND vt.PIN='$pin' AND vt.comment LIKE 'VOID-%') ) as credit_total,
        (SELECT SUM(CASE when trans_type = 'DR' then amount else 0 end) FROM savings_transaction WHERE account='$account' AND PIN='$pin' AND trans_date < '$fromdate 00:00:00' AND comment NOT LIKE 'VOID-%' AND receipt NOT IN (SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(vt.comment, ' ', 1), 'VOID-', -1) FROM savings_transaction vt WHERE vt.account='$account' AND vt.PIN='$pin' AND vt.comment LIKE 'VOID-%') ) as debit_total
FROM  
    savings_transaction WHERE account='$account' AND PIN='$pin' AND trans_date>='$fromdate 00:00:00' AND trans_date <= '$until 23:59:59' AND comment NOT LIKE 'VOID-%' AND receipt NOT IN (SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(vt.comment, ' ', 1), 'VOID-', -1) FROM savings_transaction vt WHERE vt.account='$account' AND vt.PIN='$pin' AND vt.comment LIKE 'VOID-%')  ORDER BY trans_date ASC";

        return $this->db->query($sql)->result();
    }

    function contribution_statement($fromdate, $until, $member_id) {
 $pin = current_user()->PIN;
        $sql = "SELECT  PID,member_id, createdon,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance
   
FROM  
  contribution_transaction WHERE member_id='$member_id' AND PIN='$pin' AND createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  ORDER BY createdon ASC";

        return $this->db->query($sql)->result();
    }
    function mortuary_statement($fromdate, $until, $member_id) {
        $pin = current_user()->PIN;
        $sql = "SELECT PID,member_id, trans_date, comment, system_comment, trans_type, paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance

FROM
  mortuary_transaction WHERE member_id='$member_id' AND PIN='$pin' AND trans_date>='$fromdate 00:00:00' AND trans_date <= '$until 23:59:59'  ORDER BY trans_date ASC";
        
        return $this->db->query($sql)->result();
    }

    function mortuary_ledger($fromdate, $until, $member_id) {
        $pin = current_user()->PIN;
        $sql = "SELECT id,PID,member_id, trans_date, comment, system_comment, trans_type, paymethod,
        case when trans_type = 'CR' then amount else 0 end as credit,
        case when trans_type = 'DR' then amount else 0 end as debit,
        previous_balance
        FROM  mortuary_transaction WHERE member_id='$member_id' AND PIN='$pin' AND trans_date>='$fromdate 00:00:00' AND trans_date <= '$until 23:59:59'  ORDER BY trans_date ASC";
        
        return $this->db->query($sql)->result();
    }

    function contribution_statement_previous($fromdate, $member_id) {
$current_user = current_user()->PIN;
        $sql = "SELECT  PID,member_id,
SUM(COALESCE(case when trans_type = 'CR' then amount else 0 end)) as credit,
SUM(COALESCE(case when trans_type = 'DR' then amount else 0 end)) as debit
FROM  
  contribution_transaction WHERE member_id='$member_id' AND PIN='$current_user' AND createdon < '$fromdate 00:00:00' GROUP BY member_id";
        return $this->db->query($sql)->row();
    }
    function mortuary_statement_previous($fromdate, $member_id) {
        $current_user = current_user()->PIN;
        $sql = "SELECT  PID,member_id,
SUM(COALESCE(case when trans_type = 'CR' then amount else 0 end)) as credit,
SUM(COALESCE(case when trans_type = 'DR' then amount else 0 end)) as debit
FROM
  mortuary_transaction WHERE member_id='$member_id' AND PIN='$current_user' AND trans_date < '$fromdate 00:00:00' GROUP BY member_id";
        return $this->db->query($sql)->row();
    }
    
    function share_statement($fromdate, $until, $member_id) {
$current_user = current_user()->PIN;
        $sql = "SELECT  PID,member_id, createdon,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance,share_no,previous_share
   
FROM  
  share_transaction WHERE member_id='$member_id' AND PIN='$current_user' AND createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  ORDER BY createdon ASC";

        return $this->db->query($sql)->result();
    }

    function account_saving_transactions($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  account, trans_date,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance
FROM  
  savings_transaction WHERE PIN='$pin' AND  trans_date>='$fromdate 00:00:00' AND trans_date <= '$until 23:59:59'  ORDER BY trans_date ASC";

        return $this->db->query($sql)->result();
    }

    function contribution_transactions($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  PID,member_id, createdon,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance
FROM  
  contribution_transaction WHERE PIN='$pin' AND  createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  ORDER BY createdon ASC";

        return $this->db->query($sql)->result();
    }
//Added by Herald
    function mortuary_transactions($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  PID,member_id, createdon,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance
FROM
  mortuary_transaction WHERE PIN='$pin' AND  createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  ORDER BY createdon ASC";
        
        return $this->db->query($sql)->result();
    }
    
    function share_transactions($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  PID,member_id, createdon,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance,share_no
FROM  
  share_transaction WHERE PIN='$pin' AND   createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  ORDER BY createdon ASC";

        return $this->db->query($sql)->result();
    }

    function account_saving_transactions_summary($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  account, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit

FROM  
  savings_transaction WHERE PIN='$pin' AND   trans_date>='$fromdate 00:00:00' AND trans_date <= '$until 23:59:59'  GROUP BY account";

        return $this->db->query($sql)->result();
    }

    function contribution_transactions_summary($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  member_id, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit

FROM  
  contribution_transaction WHERE PIN='$pin' AND   createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  GROUP BY member_id ORDER BY member_id";

        return $this->db->query($sql)->result();
    }
    //Added by Herald
    function mortuary_transactions_summary($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  member_id, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit
        
FROM
  mortuary_transaction WHERE PIN='$pin' AND   createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  GROUP BY member_id";
        
        return $this->db->query($sql)->result();
    }
    
    function share_transactions_summary($fromdate, $until) {
        $pin = current_user()->PIN;
        $sql = "SELECT  member_id, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit,
SUM(case when trans_type = 'CR' then share_no else 0 end) as credit_sha, SUM(case when trans_type = 'DR' then share_no else 0 end) as debit_sha
FROM  
  share_transaction WHERE PIN='$pin' AND   createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  GROUP BY member_id";

        return $this->db->query($sql)->result();
    }

    function account_saving_transactions_summary_previous($fromdate, $account) {
        $pin = current_user()->PIN;
        $sql = "SELECT  account, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit

FROM  
  savings_transaction WHERE PIN='$pin' AND   trans_date < '$fromdate 00:00:00' AND account = '$account'";

        return $this->db->query($sql)->row();
    }

    function contribution_transactions_summary_previous($fromdate, $member_id) {
        $pin = current_user()->PIN;
        $sql = "SELECT  member_id, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit

FROM  
  contribution_transaction WHERE PIN='$pin' AND   createdon < '$fromdate 00:00:00' AND member_id = '$member_id'";

        return $this->db->query($sql)->row();
    }
//Added by Herald
    function mortuary_transactions_summary_previous($fromdate, $member_id) {
        $pin = current_user()->PIN;
        $sql = "SELECT  member_id, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit
        
FROM
  mortuary_transaction WHERE PIN='$pin' AND   createdon < '$fromdate 00:00:00' AND member_id = '$member_id'";
        
        return $this->db->query($sql)->row();
    }
    
    function share_transactions_summary_previous($fromdate, $member_id) {
        $pin = current_user()->PIN;
        $sql = "SELECT  member_id, SUM(case when trans_type = 'CR' then amount else 0 end) as credit, SUM(case when trans_type = 'DR' then amount else 0 end) as debit,
 SUM(case when trans_type = 'CR' then share_no else 0 end) as share_credit, SUM(case when trans_type = 'DR' then share_no else 0 end) as share_debit
FROM  
  share_transaction WHERE PIN='$pin' AND  createdon < '$fromdate 00:00:00' AND member_id = '$member_id'";

        return $this->db->query($sql)->row();
    }

    //////////////////////////////////LOAN////////////////////////
    function loan_delivery_list() {
        $pin = current_user()->PIN;
        if (!$this->ion_auth->in_group('Members')) {
            $sql = "SELECT loan_contract.LID, CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name FROM loan_contract
                  INNER JOIN members ON loan_contract.PID=members.PID WHERE loan_contract.PIN='$pin' AND loan_contract.disburse=1";
        } else {
            $sql = "SELECT loan_contract.LID, CONCAT(members.firstname,' ',members.middlename,' ',members.lastname) as name FROM loan_contract
                  INNER JOIN members ON loan_contract.PID=members.PID WHERE loan_contract.PIN='$pin' AND loan_contract.disburse=1 AND loan_contract.member_id='" . current_user()->member_id . "'";
        }
        return $this->db->query($sql)->result();
    }

    function loan_statement($LID) {
        $this->db->where('LID', $LID);
        $this->db->order_by('installment', 'ASC');
        return $this->db->get('loan_contract_repayment')->result();
    }

    function loan_list_report($fromdate, $until, $loan_status) {
        $pin = current_user()->PIN;
        $sql = "SELECT * FROM loan_contract WHERE PIN='$pin' AND applicationdate >= '$fromdate' AND applicationdate <= '$until'";
        if ($loan_status != '') {
            if ($loan_status == 0) {
                $sql.= " AND status=$loan_status";
            } else if ($loan_status == 1) {
                $sql.= " AND evaluated=$loan_status";
            } else if ($loan_status == 2) {
                $sql.= " AND status=$loan_status";
            } else if ($loan_status == 4) {
                $sql.= " AND approval=$loan_status";
            } else if ($loan_status == 5) {
                $sql.= " AND status=$loan_status";
            } else if ($loan_status == 6) {
                $sql.= " AND disburse=1";
            } else if ($loan_status == 7) {
                $sql.= " AND evaluated= 2";
            } else if ($loan_status == 8) {
                $sql.= " AND approval= 2";
            } else if ($loan_status == 9) {
                $sql.= " AND approval= 4 AND disburse=1";
            }
        }

        return $this->db->query($sql)->result();
    }

    function loan_list_balance($fromdate, $untill) {
        $pin = current_user()->PIN;
        $sql = "SELECT loan_contract_disburse.*,loan_contract.*,(SELECT SUM(loan_contract_repayment.amount) FROM loan_contract_repayment WHERE loan_contract_repayment.LID=loan_contract_disburse.LID) as repay,
             (SELECT SUM(loan_contract_repayment.principle) FROM loan_contract_repayment WHERE loan_contract_repayment.LID=loan_contract_disburse.LID) as principle,
             (SELECT SUM(loan_contract_repayment.interest) FROM loan_contract_repayment WHERE loan_contract_repayment.LID=loan_contract_disburse.LID) as interest,
             (SELECT SUM(loan_contract_repayment.penalt) FROM loan_contract_repayment WHERE loan_contract_repayment.LID=loan_contract_disburse.LID) as penalt FROM loan_contract_disburse INNER JOIN loan_contract
                ON loan_contract_disburse.LID=loan_contract.LID WHERE loan_contract.PIN='$pin' AND loan_contract_disburse.disbursedate >= '$fromdate' AND loan_contract_disburse.disbursedate <= '$untill'  ORDER BY loan_contract_disburse.disbursedate ASC";
        return $this->db->query($sql)->result();
    }

    function loan_transactions($fromdate, $untill, $loan_type_id = null, $member_id = null) {
        $pin = current_user()->PIN;
        $fromdate = $this->db->escape($fromdate);
        $untill = $this->db->escape($untill);
        $use_filter = ($loan_type_id !== null && $loan_type_id !== '' && $loan_type_id !== 'all')
            || ($member_id !== null && $member_id !== '' && $member_id !== 'all');
        if ($use_filter) {
            $sql = "SELECT lrr.* FROM loan_repayment_receipt lrr
                INNER JOIN loan_contract lc ON lc.LID = lrr.LID AND lc.PIN = lrr.PIN
                WHERE lrr.PIN='$pin' AND lrr.paydate >= $fromdate AND lrr.paydate <= $untill";
            if ($loan_type_id !== null && $loan_type_id !== '' && $loan_type_id !== 'all') {
                $lid = $this->db->escape($loan_type_id);
                $sql .= " AND lc.product_type = $lid";
            }
            if ($member_id !== null && $member_id !== '' && $member_id !== 'all') {
                $mid = $this->db->escape($member_id);
                $sql .= " AND lc.member_id = $mid";
            }
            $sql .= " ORDER BY lrr.paydate ASC";
            return $this->db->query($sql)->result();
        }
        $sql = "SELECT * FROM loan_repayment_receipt WHERE PIN='$pin' AND paydate >= $fromdate AND paydate <= $untill ORDER BY paydate ASC";
        return $this->db->query($sql)->result();
    }

    /**
     * Get distinct loan types (products) and members that have transactions in the date range, for filter dropdowns.
     */
    function loan_transaction_filter_options($fromdate, $untill) {
        $pin = current_user()->PIN;
        $fromdate = $this->db->escape($fromdate);
        $untill = $this->db->escape($untill);
        $loan_types = $this->db->query("SELECT id, name FROM loan_product WHERE PIN = " . $this->db->escape($pin) . " ORDER BY name")->result();
        $members_sql = "SELECT DISTINCT lc.member_id,
                CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) AS member_name
            FROM loan_repayment_receipt lrr
            INNER JOIN loan_contract lc ON lc.LID = lrr.LID AND lc.PIN = lrr.PIN
            LEFT JOIN members m ON m.member_id = lc.member_id AND m.PIN = lc.PIN
            WHERE lrr.PIN = " . $this->db->escape($pin) . "
            AND lrr.paydate >= $fromdate AND lrr.paydate <= $untill
            AND lc.member_id IS NOT NULL AND lc.member_id != ''
            ORDER BY member_name";
        $members = $this->db->query($members_sql)->result();
        return array('loan_types' => $loan_types, 'members' => $members);
    }

    function loan_transactions_summary($fromdate, $untill, $loan_type_id = null, $member_id = null) {
        $pin = current_user()->PIN;
        $fromdate_esc = $this->db->escape($fromdate);
        $untill_esc = $this->db->escape($untill);
        $use_filter = ($loan_type_id !== null && $loan_type_id !== '' && $loan_type_id !== 'all')
            || ($member_id !== null && $member_id !== '' && $member_id !== 'all');
        if ($use_filter) {
            $sql = "SELECT lrr.LID, SUM(lrr.amount) as amount FROM loan_repayment_receipt lrr
                INNER JOIN loan_contract lc ON lc.LID = lrr.LID AND lc.PIN = lrr.PIN
                WHERE lrr.PIN='$pin' AND lrr.paydate >= $fromdate_esc AND lrr.paydate <= $untill_esc";
            if ($loan_type_id !== null && $loan_type_id !== '' && $loan_type_id !== 'all') {
                $lid = $this->db->escape($loan_type_id);
                $sql .= " AND lc.product_type = $lid";
            }
            if ($member_id !== null && $member_id !== '' && $member_id !== 'all') {
                $mid = $this->db->escape($member_id);
                $sql .= " AND lc.member_id = $mid";
            }
            $sql .= " GROUP BY lrr.LID ORDER BY lrr.LID";
            return $this->db->query($sql)->result();
        }
        $sql = "SELECT LID, SUM(amount) as amount FROM loan_repayment_receipt WHERE PIN='$pin' AND paydate >= $fromdate_esc AND paydate <= $untill_esc GROUP BY LID ORDER BY LID";
        return $this->db->query($sql)->result();
    }

    function loan_aging_report($as_of_date = null) {
        $pin = current_user()->PIN;
        
        // Use current date if not provided
        if (is_null($as_of_date)) {
            $as_of_date = date('Y-m-d');
        } else {
            $as_of_date = date('Y-m-d', strtotime($as_of_date));
        }
        
        // Get all active disbursed loans
        $sql = "SELECT 
                    lc.LID,
                    lc.PID,
                    lc.member_id,
                    lc.basic_amount,
                    lc.total_loan,
                    lc.product_type,
                    lcd.disbursedate,
                    (SELECT SUM(lcr.amount) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as total_repaid,
                    (SELECT SUM(lcr.principle) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as principle_paid,
                    (SELECT SUM(lcr.interest) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as interest_paid,
                    (SELECT SUM(lcr.penalt) FROM loan_contract_repayment lcr WHERE lcr.LID = lc.LID) as penalty_paid,
                    (SELECT MIN(lcrs.repaydate) FROM loan_contract_repayment_schedule lcrs 
                     WHERE lcrs.LID = lc.LID AND lcrs.status = 0) as oldest_unpaid_due_date
                FROM loan_contract lc
                INNER JOIN loan_contract_disburse lcd ON lcd.LID = lc.LID
                WHERE lc.PIN = '$pin' 
                    AND lc.status = 4 
                    AND lc.disburse = 1
                    AND lcd.disbursedate <= '$as_of_date'
                ORDER BY lc.LID";
        
        $loans = $this->db->query($sql)->result();
        
        $aging_data = array();
        $aging_buckets = array(
            'current' => array('label' => 'Current (0-30 days)', 'min' => 0, 'max' => 30, 'loans' => array(), 'total_balance' => 0, 'total_principal' => 0, 'total_interest' => 0, 'total_penalty' => 0),
            '31_60' => array('label' => '31-60 days', 'min' => 31, 'max' => 60, 'loans' => array(), 'total_balance' => 0, 'total_principal' => 0, 'total_interest' => 0, 'total_penalty' => 0),
            '61_90' => array('label' => '61-90 days', 'min' => 61, 'max' => 90, 'loans' => array(), 'total_balance' => 0, 'total_principal' => 0, 'total_interest' => 0, 'total_penalty' => 0),
            '91_180' => array('label' => '91-180 days', 'min' => 91, 'max' => 180, 'loans' => array(), 'total_balance' => 0, 'total_principal' => 0, 'total_interest' => 0, 'total_penalty' => 0),
            'over_180' => array('label' => 'Over 180 days', 'min' => 181, 'max' => 9999, 'loans' => array(), 'total_balance' => 0, 'total_principal' => 0, 'total_interest' => 0, 'total_penalty' => 0),
        );
        
        foreach ($loans as $loan) {
            $total_repaid = floatval($loan->total_repaid ? $loan->total_repaid : 0);
            $outstanding_balance = floatval($loan->total_loan) - $total_repaid;
            
            // Skip loans with zero or negative balance
            if ($outstanding_balance <= 0) {
                continue;
            }
            
            $days_overdue = 0;
            $oldest_unpaid_due_date = null;
            
            if ($loan->oldest_unpaid_due_date) {
                $oldest_unpaid_due_date = $loan->oldest_unpaid_due_date;
                $due_date = new DateTime($oldest_unpaid_due_date);
                $current_date = new DateTime($as_of_date);
                $days_overdue = $current_date->diff($due_date)->days;
                
                // If due date is in the future, it's not overdue yet
                if ($due_date > $current_date) {
                    $days_overdue = 0;
                }
            }
            
            // Calculate outstanding principal, interest, and penalty
            $principle_paid = floatval($loan->principle_paid ? $loan->principle_paid : 0);
            $interest_paid = floatval($loan->interest_paid ? $loan->interest_paid : 0);
            $penalty_paid = floatval($loan->penalty_paid ? $loan->penalty_paid : 0);
            
            $outstanding_principal = floatval($loan->basic_amount) - $principle_paid;
            $outstanding_interest = 0; // Interest is typically paid first, so this might be 0
            $outstanding_penalty = 0; // Penalty is calculated per installment
            
            // Get unpaid installments to calculate interest and penalty
            $unpaid_sql = "SELECT 
                            SUM(repayamount + balance) as total_due,
                            SUM(interest) as total_interest,
                            SUM(balance) as total_balance
                           FROM loan_contract_repayment_schedule 
                           WHERE LID = '{$loan->LID}' AND status = 0";
            $unpaid_info = $this->db->query($unpaid_sql)->row();
            
            if ($unpaid_info) {
                $outstanding_interest = floatval($unpaid_info->total_interest ? $unpaid_info->total_interest : 0);
                $outstanding_balance = floatval($unpaid_info->total_due ? $unpaid_info->total_due : 0);
            }
            
            // Determine aging bucket
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
            
            $loan_data = array(
                'LID' => $loan->LID,
                'PID' => $loan->PID,
                'member_id' => $loan->member_id,
                'basic_amount' => floatval($loan->basic_amount),
                'total_loan' => floatval($loan->total_loan),
                'total_repaid' => $total_repaid,
                'outstanding_balance' => $outstanding_balance,
                'outstanding_principal' => $outstanding_principal,
                'outstanding_interest' => $outstanding_interest,
                'outstanding_penalty' => $outstanding_penalty,
                'days_overdue' => $days_overdue,
                'oldest_unpaid_due_date' => $oldest_unpaid_due_date,
                'disbursedate' => $loan->disbursedate,
                'product_type' => $loan->product_type
            );
            
            $aging_buckets[$bucket_key]['loans'][] = $loan_data;
            $aging_buckets[$bucket_key]['total_balance'] += $outstanding_balance;
            $aging_buckets[$bucket_key]['total_principal'] += $outstanding_principal;
            $aging_buckets[$bucket_key]['total_interest'] += $outstanding_interest;
            $aging_buckets[$bucket_key]['total_penalty'] += $outstanding_penalty;
        }
        
        // Get loan beginning balances that don't have corresponding loan_contract entries
        $sql_bb = "SELECT
                        loan_beginning_balances.*,
                        members.PID,
                        COALESCE(loan_beginning_balances.loan_id, CONCAT('BB-', loan_beginning_balances.id)) as LID
                    FROM loan_beginning_balances
                    INNER JOIN members ON members.member_id = loan_beginning_balances.member_id
                    WHERE loan_beginning_balances.PIN = '$pin'
                        AND members.PIN = '$pin'
                        AND loan_beginning_balances.total_balance > 0";
        
        // Exclude beginning balances that already have corresponding loan_contract entries
        $sql_bb .= " AND (loan_beginning_balances.loan_id IS NULL
                          OR loan_beginning_balances.loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN = '$pin' AND status = 4 AND disburse = 1))";
        
        // Only include beginning balances disbursed before or on the as_of_date
        $sql_bb .= " AND (loan_beginning_balances.disbursement_date IS NULL
                          OR loan_beginning_balances.disbursement_date <= '$as_of_date')";
        
        $beginning_balances = $this->db->query($sql_bb)->result();
        
        // Process beginning balances
        foreach ($beginning_balances as $bb) {
            $outstanding_balance = floatval($bb->total_balance);
            $outstanding_principal = floatval($bb->principal_balance);
            $outstanding_interest = floatval($bb->interest_balance);
            $outstanding_penalty = floatval($bb->penalty_balance);
            
            // Skip if no balance
            if ($outstanding_balance <= 0) {
                continue;
            }
            
            $days_overdue = 0;
            $oldest_unpaid_due_date = null;
            
            // Calculate days overdue based on last_date_paid or disbursement_date
            if ($bb->last_date_paid) {
                // Use last_date_paid to calculate aging
                $last_paid_date = new DateTime($bb->last_date_paid);
                $current_date = new DateTime($as_of_date);
                
                // Estimate next due date (assuming monthly payments)
                // If monthly_amort exists, we can estimate the next due date
                if ($bb->monthly_amort && $bb->term) {
                    // Estimate: last_date_paid + 1 month
                    $last_paid_date->modify('+1 month');
                    $oldest_unpaid_due_date = $last_paid_date->format('Y-m-d');
                    
                    $days_overdue = $current_date->diff($last_paid_date)->days;
                    if ($last_paid_date > $current_date) {
                        $days_overdue = 0;
                    }
                } else {
                    // If no payment schedule info, use disbursement_date as reference
                    if ($bb->disbursement_date) {
                        $disbursement_date = new DateTime($bb->disbursement_date);
                        $days_since_disbursement = $current_date->diff($disbursement_date)->days;
                        // Estimate: if disbursed more than 30 days ago and has balance, consider it overdue
                        if ($days_since_disbursement > 30) {
                            $days_overdue = $days_since_disbursement - 30; // Assume first payment due 30 days after disbursement
                            $oldest_unpaid_due_date = date('Y-m-d', strtotime($bb->disbursement_date . ' +30 days'));
                        }
                    }
                }
            } elseif ($bb->disbursement_date) {
                // No last_date_paid, use disbursement_date
                $disbursement_date = new DateTime($bb->disbursement_date);
                $current_date = new DateTime($as_of_date);
                $days_since_disbursement = $current_date->diff($disbursement_date)->days;
                
                // Estimate first payment due 30 days after disbursement
                $estimated_first_due = clone $disbursement_date;
                $estimated_first_due->modify('+30 days');
                $oldest_unpaid_due_date = $estimated_first_due->format('Y-m-d');
                
                if ($estimated_first_due < $current_date) {
                    $days_overdue = $current_date->diff($estimated_first_due)->days;
                } else {
                    $days_overdue = 0;
                }
            }
            
            // Determine aging bucket
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
            
            $loan_data = array(
                'LID' => $bb->LID,
                'PID' => $bb->PID,
                'member_id' => $bb->member_id,
                'basic_amount' => $outstanding_principal,
                'total_loan' => $outstanding_balance,
                'total_repaid' => 0, // No repayment info for beginning balances here
                'outstanding_balance' => $outstanding_balance,
                'outstanding_principal' => $outstanding_principal,
                'outstanding_interest' => $outstanding_interest,
                'outstanding_penalty' => $outstanding_penalty,
                'days_overdue' => $days_overdue,
                'oldest_unpaid_due_date' => $oldest_unpaid_due_date,
                'disbursedate' => $bb->disbursement_date ? $bb->disbursement_date : null,
                'product_type' => $bb->loan_product_id
            );
            
            $aging_buckets[$bucket_key]['loans'][] = $loan_data;
            $aging_buckets[$bucket_key]['total_balance'] += $outstanding_balance;
            $aging_buckets[$bucket_key]['total_principal'] += $outstanding_principal;
            $aging_buckets[$bucket_key]['total_interest'] += $outstanding_interest;
            $aging_buckets[$bucket_key]['total_penalty'] += $outstanding_penalty;
        }
        
        return $aging_buckets;
    }

    function save_edit_entry($id,$trans_date,$description,$paymethod, $trans_type, $amount, $comment=''){
        $this->db->where("id", $id);
        $this->db->set("trans_date", date('Y-m-d',strtotime($trans_date)));
        $this->db->set("system_comment", $description);
        $this->db->set("comment", $comment);
        $this->db->set("paymethod", $paymethod);
        $this->db->set("trans_type", $trans_type);
        $this->db->set("amount", $amount);
        $this->db->update('savings_transaction');

        

        return TRUE;
    }

    function mortuary_edit_entry($id,$trans_date,$description,$paymethod, $trans_type, $amount,$comment=''){
        $this->db->where("id", $id);
        $this->db->set("trans_date", date('Y-m-d',strtotime($trans_date)));
        $this->db->set("createdon", date('Y-m-d',strtotime($trans_date)));
        $this->db->set("system_comment", $description);
        $this->db->set("comment", $comment);
        $this->db->set("paymethod", $paymethod);
        $this->db->set("trans_type", $trans_type);
        $this->db->set("amount", $amount);
        $this->db->update('mortuary_transaction');
        return TRUE;
    }

    /**
     * Get cash flow report data
     * 
     * @param string $fromdate Start date
     * @param string $todate End date
     * @return array Cash flow data organized by activity type
     */
    function get_cash_flow_data($fromdate, $todate) {
        $pin = current_user()->PIN;
        $data = array(
            'operating_activities' => array(
                'cash_inflows' => array(),
                'cash_outflows' => array(),
                'net_cash' => 0
            ),
            'investing_activities' => array(
                'cash_inflows' => array(),
                'cash_outflows' => array(),
                'net_cash' => 0
            ),
            'financing_activities' => array(
                'cash_inflows' => array(),
                'cash_outflows' => array(),
                'net_cash' => 0
            ),
            'total_net_cash_flow' => 0,
            'beginning_cash' => 0,
            'ending_cash' => 0
        );

        // Operating Activities - Cash Receipts (money coming in)
        // Check if table exists first
        $cash_receipts = array();
        if ($this->db->table_exists('cash_receipts')) {
            $sql = "SELECT cr.*, COALESCE(SUM(cri.amount), 0) as total_amount
                    FROM cash_receipts cr
                    LEFT JOIN cash_receipt_items cri ON cri.receipt_id = cr.id
                    WHERE cr.PIN = ?
                      AND cr.receipt_date >= ?
                      AND cr.receipt_date <= ?
                    GROUP BY cr.id
                    ORDER BY cr.receipt_date ASC";
            $cash_receipts = $this->db->query($sql, array($pin, $fromdate, $todate))->result();
        }

        foreach ($cash_receipts as $receipt) {
            $amount = floatval($receipt->total_amount ? $receipt->total_amount : 0);
            if ($amount > 0) {
                $data['operating_activities']['cash_inflows'][] = array(
                    'date' => $receipt->receipt_date,
                    'description' => $receipt->description,
                    'amount' => $amount,
                    'reference' => 'Receipt #' . $receipt->receipt_no,
                    'received_from' => isset($receipt->received_from) ? $receipt->received_from : ''
                );
                $data['operating_activities']['net_cash'] += $amount;
            }
        }

        // Operating Activities - Cash Disbursements (money going out)
        // Check if table exists first
        $cash_disbursements = array();
        if ($this->db->table_exists('cash_disbursements')) {
            $sql = "SELECT cd.*, COALESCE(SUM(cdi.amount), 0) as total_amount
                    FROM cash_disbursements cd
                    LEFT JOIN cash_disbursement_items cdi ON cdi.disbursement_id = cd.id
                    WHERE cd.PIN = ?
                      AND cd.disburse_date >= ?
                      AND cd.disburse_date <= ?
                    GROUP BY cd.id
                    ORDER BY cd.disburse_date ASC";
            $cash_disbursements = $this->db->query($sql, array($pin, $fromdate, $todate))->result();
        }

        foreach ($cash_disbursements as $disbursement) {
            $amount = floatval($disbursement->total_amount ? $disbursement->total_amount : 0);
            if ($amount > 0) {
                $data['operating_activities']['cash_outflows'][] = array(
                    'date' => $disbursement->disburse_date,
                    'description' => $disbursement->description,
                    'amount' => $amount,
                    'reference' => 'Disbursement #' . $disbursement->disburse_no,
                    'paid_to' => isset($disbursement->paid_to) ? $disbursement->paid_to : ''
                );
                $data['operating_activities']['net_cash'] -= $amount;
            }
        }

        // Financing Activities - Member Contributions (money coming in)
        $contributions = array();
        if ($this->db->table_exists('members_contribution_transaction')) {
            $sql = "SELECT mc.*, m.member_id, CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) as member_name
                    FROM members_contribution_transaction mc
                    LEFT JOIN members m ON m.PID = mc.PID AND m.PIN = mc.PIN
                    WHERE mc.PIN = ?
                      AND mc.trans_date >= ?
                      AND mc.trans_date <= ?
                      AND mc.trans_type = 'CR'
                    ORDER BY mc.trans_date ASC";
            $contributions = $this->db->query($sql, array($pin, $fromdate, $todate))->result();
        }

        foreach ($contributions as $contribution) {
            $data['financing_activities']['cash_inflows'][] = array(
                'date' => $contribution->trans_date,
                'description' => 'Contribution from ' . $contribution->member_name,
                'amount' => floatval($contribution->amount),
                'reference' => 'Contribution Transaction'
            );
            $data['financing_activities']['net_cash'] += floatval($contribution->amount);
        }

        // Financing Activities - Share Purchases (money coming in)
        // Share purchases are CR (credit) transactions where money comes in
        $share_purchases = array();
        if ($this->db->table_exists('share_transaction')) {
            $sql = "SELECT st.*, m.member_id, CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) as member_name
                    FROM share_transaction st
                    LEFT JOIN members m ON m.PID = st.PID AND m.PIN = st.PIN
                    WHERE st.PIN = ?
                      AND DATE(st.createdon) >= ?
                      AND DATE(st.createdon) <= ?
                      AND st.trans_type = 'CR'
                    ORDER BY st.createdon ASC";
            $share_purchases = $this->db->query($sql, array($pin, $fromdate, $todate))->result();
        }

        foreach ($share_purchases as $purchase) {
            // Extract date from createdon (which is datetime)
            $date = date('Y-m-d', strtotime($purchase->createdon));
            $data['financing_activities']['cash_inflows'][] = array(
                'date' => $date,
                'description' => 'Share Purchase from ' . ($purchase->member_name ? $purchase->member_name : 'Member ID: ' . $purchase->member_id),
                'amount' => floatval($purchase->amount),
                'reference' => 'Share Transaction'
            );
            $data['financing_activities']['net_cash'] += floatval($purchase->amount);
        }

        // Financing Activities - Loan Disbursements (money going out)
        // Try different possible table/column combinations
        $loan_disbursements = array();
        if ($this->db->table_exists('loan_contract_disburse')) {
            // Use loan_contract_disburse table - get basic_amount or total_loan from loan_contract
            $sql = "SELECT lcd.*, lc.PID, lc.basic_amount, lc.total_loan, m.member_id, 
                           CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) as member_name
                    FROM loan_contract_disburse lcd
                    LEFT JOIN loan_contract lc ON lc.LID = lcd.LID AND lc.PIN = lcd.PIN
                    LEFT JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                    WHERE lcd.PIN = ?
                      AND DATE(lcd.disbursedate) >= ?
                      AND DATE(lcd.disbursedate) <= ?
                    ORDER BY lcd.disbursedate ASC";
            $loan_disbursements = $this->db->query($sql, array($pin, $fromdate, $todate))->result();
        }

        foreach ($loan_disbursements as $disbursement) {
            // Try basic_amount first (the principal amount), then total_loan (principal + interest)
            $amount = 0;
            if (isset($disbursement->basic_amount) && $disbursement->basic_amount > 0) {
                $amount = floatval($disbursement->basic_amount);
            } elseif (isset($disbursement->total_loan) && $disbursement->total_loan > 0) {
                $amount = floatval($disbursement->total_loan);
            }
            
            if ($amount > 0) {
                $date = isset($disbursement->disbursedate) ? date('Y-m-d', strtotime($disbursement->disbursedate)) : date('Y-m-d');
                $member_name = isset($disbursement->member_name) ? $disbursement->member_name : 'Member ID: ' . (isset($disbursement->member_id) ? $disbursement->member_id : 'N/A');
                $data['financing_activities']['cash_outflows'][] = array(
                    'date' => $date,
                    'description' => 'Loan Disbursement to ' . $member_name,
                    'amount' => $amount,
                    'reference' => 'Loan Disbursement'
                );
                $data['financing_activities']['net_cash'] -= $amount;
            }
        }

        // Financing Activities - Loan Repayments (money coming in)
        // Try loan_repayment_receipt table which seems to be the actual table used
        $loan_repayments = array();
        if ($this->db->table_exists('loan_repayment_receipt')) {
            $sql = "SELECT lrr.*, lc.PID, m.member_id, CONCAT(m.firstname, ' ', m.middlename, ' ', m.lastname) as member_name
                    FROM loan_repayment_receipt lrr
                    LEFT JOIN loan_contract lc ON lc.LID = lrr.LID AND lc.PIN = lrr.PIN
                    LEFT JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                    WHERE lrr.PIN = ?
                      AND DATE(lrr.paydate) >= ?
                      AND DATE(lrr.paydate) <= ?
                    ORDER BY lrr.paydate ASC";
            $loan_repayments = $this->db->query($sql, array($pin, $fromdate, $todate))->result();
        }

        foreach ($loan_repayments as $repayment) {
            $amount = isset($repayment->amount) ? floatval($repayment->amount) : 0;
            if ($amount > 0) {
                $date = isset($repayment->paydate) ? date('Y-m-d', strtotime($repayment->paydate)) : date('Y-m-d');
                $member_name = isset($repayment->member_name) ? $repayment->member_name : 'Member ID: ' . (isset($repayment->member_id) ? $repayment->member_id : 'N/A');
                $data['financing_activities']['cash_inflows'][] = array(
                    'date' => $date,
                    'description' => 'Loan Repayment from ' . $member_name,
                    'amount' => $amount,
                    'reference' => 'Loan Repayment'
                );
                $data['financing_activities']['net_cash'] += $amount;
            }
        }

        // Calculate total net cash flow
        $data['total_net_cash_flow'] = $data['operating_activities']['net_cash'] + 
                                       $data['investing_activities']['net_cash'] + 
                                       $data['financing_activities']['net_cash'];

        // Calculate beginning cash (from general ledger - cash accounts before start date)
        // Use raw SQL to avoid query builder issues
        $beginning_cash_sql = "SELECT SUM(gl.debit) as total_debit, SUM(gl.credit) as total_credit
                               FROM general_ledger gl
                               INNER JOIN account_chart ac ON ac.account = gl.account AND ac.PIN = gl.PIN
                               WHERE gl.PIN = ?
                                 AND gl.date < ?
                                 AND ac.account_type IN (10, 11)";
        $beginning_cash_query = $this->db->query($beginning_cash_sql, array($pin, $fromdate))->row();

        $beginning_debit = floatval($beginning_cash_query->total_debit);
        $beginning_credit = floatval($beginning_cash_query->total_credit);
        $data['beginning_cash'] = $beginning_debit - $beginning_credit;

        // Calculate ending cash
        $data['ending_cash'] = $data['beginning_cash'] + $data['total_net_cash_flow'];

        return $data;
    }

    /**
     * Account balances as of a date for Consolidated Statement of Financial Condition.
     * Assets: debit - credit; Liabilities / Equity / Statutory: credit - debit.
     */
    function get_financial_condition_balances($date) {
        $pin = current_user()->PIN;
        if (empty($date) || empty($pin)) {
            return array();
        }
        $date = date('Y-m-d', strtotime($date));
        if ($date === false || $date === '1970-01-01') {
            return array();
        }

        $sql = "SELECT
                    account_chart.account AS account,
                    account_chart.name AS name,
                    account_chart.account_type AS account_type,
                    account_chart.sub_account_type AS sub_account_type,
                    COALESCE(SUM(general_ledger.debit), 0) AS debit,
                    COALESCE(SUM(general_ledger.credit), 0) AS credit
                FROM account_chart
                LEFT JOIN general_ledger
                    ON general_ledger.account = account_chart.account
                    AND general_ledger.PIN = account_chart.PIN
                    AND general_ledger.date <= ?
                WHERE account_chart.PIN = ?
                    AND (
                        CAST(account_chart.account_type AS UNSIGNED) IN (10000, 20000, 30000, 30800)
                        OR account_chart.account_type IN ('10000', '20000', '30000', '30800')
                    )
                    AND CAST(account_chart.account AS UNSIGNED) BETWEEN 10000 AND 39999
                GROUP BY account_chart.account, account_chart.name, account_chart.account_type, account_chart.sub_account_type
                ORDER BY CAST(account_chart.account AS UNSIGNED) ASC";

        $rows = $this->db->query($sql, array($date, $pin))->result();
        $balances = array();
        foreach ($rows as $row) {
            $type = (int) $row->account_type;
            if ($type == 10000) {
                $bal = floatval($row->debit) - floatval($row->credit);
            } else {
                $bal = floatval($row->credit) - floatval($row->debit);
            }
            $balances[$row->account] = array(
                'account' => $row->account,
                'name' => $row->name,
                'account_type' => $row->account_type,
                'sub_account_type' => $row->sub_account_type,
                'balance' => $bal,
            );
        }
        return $balances;
    }

    /**
     * Build hierarchical rows for Consolidated Statement of Financial Condition.
     */
    function get_financial_condition_data($date) {
        $bal = $this->get_financial_condition_balances($date);
        $used = array();

        $amt = function ($code) use ($bal, &$used) {
            $code = (string) $code;
            if (!isset($bal[$code])) {
                return 0.0;
            }
            $used[$code] = true;
            return floatval($bal[$code]['balance']);
        };

        $peek = function ($code) use ($bal) {
            $code = (string) $code;
            return isset($bal[$code]) ? floatval($bal[$code]['balance']) : 0.0;
        };

        $name = function ($code, $fallback = null) use ($bal) {
            $code = (string) $code;
            if (isset($bal[$code]) && !empty($bal[$code]['name'])) {
                return $bal[$code]['name'];
            }
            return $fallback !== null ? $fallback : $code;
        };

        $rows = array();
        $push = function ($type, $label, $amount = null, $opts = array()) use (&$rows) {
            $rows[] = array_merge(array(
                'type' => $type,
                'label' => $label,
                'amount' => $amount,
                'indent' => 0,
                'bold' => false,
                'italic' => false,
                'peso' => false,
                'line' => '',
                'always_show' => false,
                'is_less' => false,
            ), $opts);
        };

        $sum_mark = function ($codes) use ($amt) {
            $t = 0.0;
            foreach ($codes as $c) {
                $t += $amt($c);
            }
            return $t;
        };

        // ===================== ASSETS =====================
        $push('section', 'ASSETS', null, array('bold' => true));
        $push('section', 'CURRENT ASSETS', null, array('bold' => true));

        $push('group', 'Cash and Cash Equivalents', null, array('bold' => true, 'indent' => 1));
        $cash_primary = array(
            '11110' => 'Cash on Hand',
            '11131' => 'Cash in Bank - LBP',
            '11132' => 'Cash in Bank - Bayanihan Coop.',
            '11150' => 'Petty Cash Fund',
        );
        $cash_all = array('11100', '11110', '11130', '11131', '11132', '11140', '11150', '11190');
        foreach ($cash_primary as $code => $label) {
            $push('account', $label, $amt($code), array('indent' => 2, 'always_show' => true));
        }
        foreach ($cash_all as $code) {
            if (isset($cash_primary[$code])) {
                continue;
            }
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 2));
            }
        }
        $total_cash = 0.0;
        foreach ($cash_all as $c) {
            $total_cash += $peek($c);
            $used[$c] = true;
        }
        $push('subtotal', 'Total Cash and Cash Equivalents', $total_cash, array(
            'indent' => 2, 'bold' => true, 'peso' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Loans and Receivables', null, array('bold' => true, 'indent' => 1));
        $push('group', 'Loans Receivable', null, array('bold' => true, 'indent' => 2));
        $loan_aging = array(
            '11210' => 'Loans Receivable - Current',
            '11220' => 'Loans Receivable - Past Due',
            '11230' => 'Loans Receivable - Restructured',
            '11240' => 'Loans Receivable - Loans in Litigation',
        );
        foreach ($loan_aging as $code => $label) {
            $push('account', $label, $amt($code), array('indent' => 3, 'always_show' => true));
        }
        $loan_products = array('11201', '11202', '11203', '11204', '11205', '11206');
        foreach ($loan_products as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 3));
            }
        }
        $v11200 = $amt('11200');
        if (abs($v11200) >= 0.005) {
            $push('account', $name('11200', 'Loans Receivable'), $v11200, array('indent' => 3));
        }
        $loan_allow = $amt('11242');
        $push('account', 'Less: All. For Prob. Losses on Loans', $loan_allow, array(
            'indent' => 3, 'always_show' => true, 'is_less' => true
        ));
        $gross_loans = 0.0;
        foreach (array_merge(array_keys($loan_aging), $loan_products, array('11200')) as $c) {
            $gross_loans += $peek($c);
            $used[$c] = true;
        }
        $net_loans = $gross_loans + $loan_allow;
        $push('subtotal', 'Net, Loans Receivable', $net_loans, array(
            'indent' => 3, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Accounts Receivable', null, array('bold' => true, 'indent' => 2));
        $ar_codes = array(
            '11250' => 'Accounts Receivable Trade - Current',
            '11260' => 'Accounts Receivable Trade - Past Due',
            '11270' => 'Accounts Receivable Trade - Restructured',
            '11280' => 'Accounts Receivable Trade - in Litigation',
        );
        foreach ($ar_codes as $code => $label) {
            $push('account', $label, $amt($code), array('indent' => 3, 'always_show' => ($code === '11260')));
        }
        $ar_allow = $amt('11281');
        $push('account', 'Less: All. For Prob. Losses on AR Trade PD', $ar_allow, array(
            'indent' => 3, 'always_show' => true, 'is_less' => true
        ));
        $gross_ar = 0.0;
        foreach (array_keys($ar_codes) as $c) {
            $gross_ar += $peek($c);
            $used[$c] = true;
        }
        $net_ar = $gross_ar + $ar_allow;
        $push('subtotal', 'Net, Accounts Receivable', $net_ar, array(
            'indent' => 3, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $other_recv = $sum_mark(array('11360', '11399'));
        $push('account', 'Other Current Receivables', $other_recv, array('indent' => 2, 'always_show' => true));
        $total_loans_recv = $net_loans + $net_ar + $other_recv;
        $push('subtotal', 'Total Loans and Receivables', $total_loans_recv, array(
            'indent' => 2, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Other Current Assets', null, array('bold' => true, 'indent' => 1));
        $push('account', 'Unused Supplies', $amt('12150'), array('indent' => 2, 'always_show' => true));
        $push('account', 'Prepaid Expenses', $amt('12170'), array('indent' => 2, 'always_show' => true));
        foreach (array('12160', '12161', '12200') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 2));
            }
        }
        $total_other_ca = 0.0;
        foreach (array('12150', '12170', '12160', '12161', '12200') as $c) {
            $total_other_ca += $peek($c);
            $used[$c] = true;
        }
        $push('subtotal', 'Total Other Current Assets', $total_other_ca, array(
            'indent' => 2, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $total_current_assets = $total_cash + $total_loans_recv + $total_other_ca;
        $push('subtotal', 'Total Current Assets', $total_current_assets, array(
            'indent' => 1, 'bold' => true, 'peso' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('section', 'NON - CURRENT ASSETS', null, array('bold' => true));
        $push('group', 'Investment', null, array('bold' => true, 'indent' => 1));
        $long_term_inv = $amt('13100') + $amt('13300') + $amt('13302');
        $push('account', 'Long-term Investment', $long_term_inv, array('indent' => 2, 'always_show' => true));
        $push('account', 'Investment in CLIMBS', $amt('13301'), array('indent' => 2, 'always_show' => true));
        $total_investment = $long_term_inv + $peek('13301');

        $push('group', 'Property, Plant and Equipment', null, array('bold' => true, 'indent' => 1));
        $ppe_pairs = array(
            array('14100', 'Land', null, true),
            array('14120', 'Building', '14121', true),
            array('14130', 'Building Improvements', '14131', true),
            array('14180', 'Furniture, Fixtures & Equip.', '14181', true),
            array('14210', 'Transportation Equipment', '14211', false),
            array('14220', 'Linens and Uniforms', '14221', false),
            array('14240', 'Leasehold Rights & Improvement', '14241', false),
        );
        $total_ppe = 0.0;
        foreach ($ppe_pairs as $pair) {
            $code = $pair[0];
            $label = $pair[1];
            $accum = $pair[2];
            $force = $pair[3];
            $v = $amt($code);
            if ($force || abs($v) >= 0.005) {
                $push('account', $label, $v, array('indent' => 2, 'always_show' => $force));
                $total_ppe += $v;
                if ($accum) {
                    $av = $amt($accum);
                    $push('account', 'Less: Accum. Depreciation- ' . $label, $av, array(
                        'indent' => 2,
                        'always_show' => ($force || abs($av) >= 0.005),
                        'is_less' => true
                    ));
                    $total_ppe += $av;
                }
            } elseif ($accum && abs($peek($accum)) >= 0.005) {
                $av = $amt($accum);
                $push('account', $label, $v, array('indent' => 2));
                $push('account', 'Less: Accum. Depreciation- ' . $label, $av, array(
                    'indent' => 2, 'is_less' => true
                ));
                $total_ppe += $v + $av;
            }
        }

        $push('group', 'Other Non Current Assets', null, array('bold' => true, 'indent' => 1));
        $push('account', 'Other Funds and Deposits', $amt('18200'), array('indent' => 2, 'always_show' => true));
        foreach (array('17400', '18100') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 2));
            }
        }
        $total_other_nca = 0.0;
        foreach (array('18200', '17400', '18100') as $c) {
            $total_other_nca += $peek($c);
            $used[$c] = true;
        }
        $total_noncurrent = $total_investment + $total_ppe + $total_other_nca;
        $push('subtotal', 'Total Non-Current Assets', $total_noncurrent, array(
            'indent' => 1, 'bold' => true, 'peso' => true, 'line' => 'single', 'always_show' => true
        ));

        $unmapped_assets = 0.0;
        $added_other_asset_hdr = false;
        foreach ($bal as $code => $info) {
            if ((int) $info['account_type'] != 10000 || !empty($used[$code])) {
                continue;
            }
            $v = floatval($info['balance']);
            if (abs($v) < 0.005) {
                continue;
            }
            if (!$added_other_asset_hdr) {
                $push('group', 'Other Assets', null, array('bold' => true, 'indent' => 1));
                $added_other_asset_hdr = true;
            }
            $push('account', $info['name'], $v, array('indent' => 2));
            $used[$code] = true;
            $unmapped_assets += $v;
        }

        $total_assets = $total_current_assets + $total_noncurrent + $unmapped_assets;
        $push('total', 'TOTAL ASSETS', $total_assets, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        // ===================== LIABILITIES =====================
        $push('spacer', '', null);
        $push('section', 'LIABILITIES & EQUITIES', null, array('bold' => true));
        $push('section', 'Current Liabilities', null, array('bold' => true));

        $push('account', 'Savings Deposit Payable - Special', $amt('21110'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Savings Deposit Payable - MSO', $amt('21120'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Loans Payable-Current', $amt('22100'), array('indent' => 1, 'always_show' => true));
        foreach (array('21100', '21130') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 1));
            }
        }
        $total_ap = 0.0;
        foreach (array('21110', '21120', '22100', '21100', '21130') as $c) {
            $total_ap += $peek($c);
            $used[$c] = true;
        }
        $push('subtotal', 'Total Accounts and Other Payables', $total_ap, array(
            'indent' => 1, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Accrued Expenses', null, array('bold' => true, 'indent' => 1));
        // Sample "Accounts Payable" maps to Non-Trade AP under accrued section
        $push('account', 'Accounts Payable', $amt('21220'), array('indent' => 2, 'always_show' => true));
        $push('account', 'SSS/ECC/Phil/Pag-ibig Prem. Payable', $amt('21320'), array('indent' => 2, 'always_show' => true));
        $push('account', 'SSS/Pag-ibig Loans Payable', $amt('21330'), array('indent' => 2, 'always_show' => true));
        $push('account', 'Withholding Tax Payable', $amt('21340'), array('indent' => 2, 'always_show' => true));
        foreach (array('21300', '21310', '21370', '21390') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 2));
            }
        }
        $total_accrued = 0.0;
        foreach (array('21220', '21320', '21330', '21340', '21300', '21310', '21370', '21390') as $c) {
            $total_accrued += $peek($c);
            $used[$c] = true;
        }
        $push('subtotal', 'Total Accrued Expenses', $total_accrued, array(
            'indent' => 2, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Other Current Liabilities', null, array('bold' => true, 'indent' => 1));
        $push('account', 'Interest on Share Capital Payable', $amt('21440'), array('indent' => 2, 'always_show' => true));
        $push('account', 'Patronage Refund Payable', $amt('21450'), array('indent' => 2, 'always_show' => true));
        $push('account', 'Due to Union Fed. CETF (APEX)', $amt('21460'), array('indent' => 2, 'always_show' => true));
        $push('account', 'Unearned Income', $amt('21410'), array('indent' => 2, 'always_show' => true));
        $push('account', 'Insurance Payable', $amt('21291'), array('indent' => 2, 'always_show' => true));
        foreach (array('21470', '21490') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 2));
            }
        }
        $total_ocl = 0.0;
        foreach (array('21440', '21450', '21460', '21410', '21291', '21470', '21490') as $c) {
            $total_ocl += $peek($c);
            $used[$c] = true;
        }
        $push('subtotal', 'Total Other Current Liabilities', $total_ocl, array(
            'indent' => 2, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $total_current_liab = $total_ap + $total_accrued + $total_ocl;
        $push('subtotal', 'Total Current Liabilities', $total_current_liab, array(
            'indent' => 1, 'bold' => true, 'italic' => true, 'peso' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('section', 'Non-Current Liabilities', null, array('bold' => true));
        $push('account', 'Retirement Payable', $amt('22400'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Members Benefit & Other Funds Payable', $amt('24120'), array('indent' => 1, 'always_show' => true));
        foreach (array('24150', '24190') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 1));
            }
        }
        $total_ncl = 0.0;
        foreach (array('22400', '24120', '24150', '24190') as $c) {
            $total_ncl += $peek($c);
            $used[$c] = true;
        }
        $push('subtotal', 'Total Non-Current Liabilities', $total_ncl, array(
            'indent' => 1, 'bold' => true, 'italic' => true, 'line' => 'single', 'always_show' => true
        ));

        $unmapped_liab = 0.0;
        $added_other_liab_hdr = false;
        foreach ($bal as $code => $info) {
            if ((int) $info['account_type'] != 20000 || !empty($used[$code])) {
                continue;
            }
            $v = floatval($info['balance']);
            if (abs($v) < 0.005) {
                continue;
            }
            if (!$added_other_liab_hdr) {
                $push('group', 'Other Liabilities', null, array('bold' => true, 'indent' => 1));
                $added_other_liab_hdr = true;
            }
            $push('account', $info['name'], $v, array('indent' => 1));
            $used[$code] = true;
            $unmapped_liab += $v;
        }

        $total_liabilities = $total_current_liab + $total_ncl + $unmapped_liab;
        $push('total', 'TOTAL LIABILITIES', $total_liabilities, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        // ===================== EQUITY =====================
        $push('spacer', '', null);
        $push('section', "MEMBERS' EQUITY", null, array('bold' => true));
        $push('account', 'Subscribed Share Capital - Common', $amt('30110'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Less: Subscription Receivable', $amt('30120'), array(
            'indent' => 1, 'always_show' => true, 'is_less' => true
        ));
        $push('account', 'Paid-Up Share Capital', $amt('30130'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Less: Treasury Share Capital', $amt('30131'), array(
            'indent' => 1, 'always_show' => true, 'is_less' => true
        ));
        $total_paid_up = $peek('30110') + $peek('30120') + $peek('30130') + $peek('30131');
        foreach (array('30110', '30120', '30130', '30131') as $c) {
            $used[$c] = true;
        }
        $push('subtotal', 'Total Paid-Up Share Capital', $total_paid_up, array(
            'indent' => 1, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));
        $push('account', 'Deposit for Share Capital Subscription', $amt('30300'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Undivided Net Surplus (Loss)', $amt('30600'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Donations and Grants', $amt('30700'), array('indent' => 1, 'always_show' => true));
        foreach (array('30150', '30400') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 1));
            }
        }
        $total_equity = $total_paid_up;
        foreach (array('30300', '30600', '30700', '30150', '30400') as $c) {
            $total_equity += $peek($c);
            $used[$c] = true;
        }

        $unmapped_equity = 0.0;
        foreach ($bal as $code => $info) {
            if ((int) $info['account_type'] != 30000 || !empty($used[$code])) {
                continue;
            }
            $v = floatval($info['balance']);
            if (abs($v) < 0.005) {
                continue;
            }
            $push('account', $info['name'], $v, array('indent' => 1));
            $used[$code] = true;
            $unmapped_equity += $v;
        }
        $total_equity += $unmapped_equity;
        $push('total', 'TOTAL EQUITIES', $total_equity, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        // ===================== STATUTORY FUNDS =====================
        $push('spacer', '', null);
        $push('section', 'Statutory Funds', null, array('bold' => true));
        $push('account', 'General Reserve Fund', $amt('30810'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Coop. Education & Training Fund-Local', $amt('30820'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Community Dev. Fund', $amt('30830'), array('indent' => 1, 'always_show' => true));
        $push('account', 'Optional Fund', $amt('30840'), array('indent' => 1, 'always_show' => true));
        foreach (array('31000') as $code) {
            $v = $amt($code);
            if (abs($v) >= 0.005) {
                $push('account', $name($code), $v, array('indent' => 1));
            }
        }
        $total_statutory = 0.0;
        foreach (array('30810', '30820', '30830', '30840', '31000') as $c) {
            $total_statutory += $peek($c);
            $used[$c] = true;
        }
        foreach ($bal as $code => $info) {
            if ((int) $info['account_type'] != 30800 || !empty($used[$code])) {
                continue;
            }
            $v = floatval($info['balance']);
            if (abs($v) < 0.005) {
                continue;
            }
            $push('account', $info['name'], $v, array('indent' => 1));
            $used[$code] = true;
            $total_statutory += $v;
        }
        $push('subtotal', 'Total Statutory Funds', $total_statutory, array(
            'indent' => 1, 'bold' => true, 'italic' => true, 'line' => 'double', 'always_show' => true
        ));

        $total_liab_equity = $total_liabilities + $total_equity + $total_statutory;
        $push('total', 'TOTAL LIABILITIES AND EQUITIES', $total_liab_equity, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        return array(
            'rows' => $rows,
            'totals' => array(
                'assets' => $total_assets,
                'liabilities' => $total_liabilities,
                'equity' => $total_equity,
                'statutory' => $total_statutory,
                'liabilities_and_equities' => $total_liab_equity,
                'difference' => $total_assets - $total_liab_equity,
            ),
        );
    }

    /**
     * Period activity for P&L accounts (revenue/expense/other) between dates inclusive.
     * Revenue/subsidy (40000/80000 income-like): credit - debit
     * Expense (70000 / subsidized expense): debit - credit
     */
    function get_financial_operations_balances($from, $until) {
        $pin = current_user()->PIN;
        if (empty($from) || empty($until) || empty($pin)) {
            return array();
        }
        $from = date('Y-m-d', strtotime($from));
        $until = date('Y-m-d', strtotime($until));
        if ($from === false || $until === false || $from === '1970-01-01' || $until === '1970-01-01') {
            return array();
        }

        $sql = "SELECT
                    account_chart.account AS account,
                    account_chart.name AS name,
                    account_chart.account_type AS account_type,
                    account_chart.sub_account_type AS sub_account_type,
                    COALESCE(SUM(general_ledger.debit), 0) AS debit,
                    COALESCE(SUM(general_ledger.credit), 0) AS credit
                FROM account_chart
                LEFT JOIN general_ledger
                    ON general_ledger.account = account_chart.account
                    AND general_ledger.PIN = account_chart.PIN
                    AND general_ledger.date >= ?
                    AND general_ledger.date <= ?
                WHERE account_chart.PIN = ?
                    AND (
                        CAST(account_chart.account_type AS UNSIGNED) IN (40000, 70000, 80000)
                        OR account_chart.account_type IN ('40000', '70000', '80000')
                    )
                    AND CAST(account_chart.account AS UNSIGNED) BETWEEN 40000 AND 89999
                GROUP BY account_chart.account, account_chart.name, account_chart.account_type, account_chart.sub_account_type
                ORDER BY CAST(account_chart.account AS UNSIGNED) ASC";

        $rows = $this->db->query($sql, array($from, $until, $pin))->result();
        $balances = array();
        $expense_like_800 = array('80450', '80510', '80560');
        foreach ($rows as $row) {
            $type = (int) $row->account_type;
            $code = (string) $row->account;
            if ($type == 70000 || in_array($code, $expense_like_800, true)) {
                $bal = floatval($row->debit) - floatval($row->credit);
            } else {
                $bal = floatval($row->credit) - floatval($row->debit);
            }
            $balances[$code] = array(
                'account' => $code,
                'name' => $row->name,
                'account_type' => $row->account_type,
                'sub_account_type' => $row->sub_account_type,
                'balance' => $bal,
            );
        }
        return $balances;
    }

    /**
     * Comparative Statement of Financial Operations - Lending
     * Columns: YTD (as-of), Current month, Prior month YTD
     */
    function get_financial_operations_data($as_of) {
        $as_of = date('Y-m-d', strtotime($as_of));
        $year_start = date('Y-01-01', strtotime($as_of));
        $month_start = date('Y-m-01', strtotime($as_of));
        $prev_month_end = date('Y-m-d', strtotime($month_start . ' -1 day'));

        $ytd = $this->get_financial_operations_balances($year_start, $as_of);
        $month = $this->get_financial_operations_balances($month_start, $as_of);
        // Prior YTD is only within the same calendar year (Jan as-of => empty prior)
        if ($prev_month_end < $year_start) {
            $prior = array();
            $prev_label_date = $prev_month_end;
        } else {
            $prior = $this->get_financial_operations_balances($year_start, $prev_month_end);
            $prev_label_date = $prev_month_end;
        }

        $cols = array('ytd' => $ytd, 'month' => $month, 'prior' => $prior);
        $used = array('ytd' => array(), 'month' => array(), 'prior' => array());

        $amt = function ($code, $col) use ($cols, &$used) {
            $code = (string) $code;
            $used[$col][$code] = true;
            if (!isset($cols[$col][$code])) {
                return 0.0;
            }
            return floatval($cols[$col][$code]['balance']);
        };

        $triple = function ($codes) use ($amt) {
            if (!is_array($codes)) {
                $codes = array($codes);
            }
            $out = array('ytd' => 0.0, 'month' => 0.0, 'prior' => 0.0);
            foreach ($codes as $code) {
                $out['ytd'] += $amt($code, 'ytd');
                $out['month'] += $amt($code, 'month');
                $out['prior'] += $amt($code, 'prior');
            }
            return $out;
        };

        $add = function ($a, $b) {
            return array(
                'ytd' => $a['ytd'] + $b['ytd'],
                'month' => $a['month'] + $b['month'],
                'prior' => $a['prior'] + $b['prior'],
            );
        };

        $sub = function ($a, $b) {
            return array(
                'ytd' => $a['ytd'] - $b['ytd'],
                'month' => $a['month'] - $b['month'],
                'prior' => $a['prior'] - $b['prior'],
            );
        };

        $zero = array('ytd' => 0.0, 'month' => 0.0, 'prior' => 0.0);
        $rows = array();
        $push = function ($type, $label, $amounts = null, $opts = array()) use (&$rows, $zero) {
            $rows[] = array_merge(array(
                'type' => $type,
                'label' => $label,
                'amounts' => $amounts,
                'indent' => 0,
                'bold' => false,
                'italic' => false,
                'peso' => false,
                'line' => '',
                'always_show' => false,
                'negate_display' => false,
            ), $opts);
        };

        $has_any = function ($amounts) {
            if ($amounts === null) {
                return false;
            }
            return abs($amounts['ytd']) >= 0.005 || abs($amounts['month']) >= 0.005 || abs($amounts['prior']) >= 0.005;
        };

        // ---- REVENUE ----
        $push('section', 'REVENUE ITEMS', null, array('bold' => true));
        $push('group', 'Income From Credit Operations', null, array('bold' => true, 'indent' => 0));

        $interest = $triple(array('40110', '40111', '40112', '40113', '40114', '40115', '40116', '40117'));
        $push('account', 'Interest Income from Loans', $interest, array('indent' => 1, 'always_show' => true));
        $service = $triple('40120');
        $push('account', 'Service Fees', $service, array('indent' => 1, 'always_show' => true));
        $filing = $triple('40130');
        $push('account', 'Filing Fees', $filing, array('indent' => 1, 'always_show' => true));
        $fines = $triple('40140');
        $push('account', 'Fines, Penalties & Surcharges', $fines, array('indent' => 1, 'always_show' => true));

        $total_credit_ops = $add($add($add($interest, $service), $filing), $fines);
        $push('subtotal', 'Total Income from Credit Operations', $total_credit_ops, array(
            'indent' => 1, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Add: Other Income', null, array('bold' => true));
        $inv_inc = $triple('40610');
        $push('account', 'Income/Interest from Investment/Deposits', $inv_inc, array('indent' => 1, 'always_show' => true));
        $memb = $triple('40620');
        $push('account', 'Membership Fees', $memb, array('indent' => 1, 'always_show' => true));
        $comm = $triple('40630');
        $push('account', 'Commission Income', $comm, array('indent' => 1, 'always_show' => true));
        $rental = $triple('40650');
        $push('account', 'Rental Income', $rental, array('indent' => 1, 'always_show' => true));
        $misc = $triple('40700');
        $push('account', 'Miscellaneous Income', $misc, array('indent' => 1, 'always_show' => true));

        // Unmapped other revenue under 40000
        $extra_rev = $zero;
        $mapped_rev = array('40110','40111','40112','40113','40114','40115','40116','40117','40120','40130','40140','40610','40620','40630','40650','40700');
        foreach ($ytd as $code => $info) {
            if ((int) $info['account_type'] != 40000) {
                continue;
            }
            if (in_array($code, $mapped_rev, true)) {
                continue;
            }
            $t = $triple($code);
            if ($has_any($t)) {
                $push('account', $info['name'], $t, array('indent' => 1));
                $extra_rev = $add($extra_rev, $t);
            }
        }

        $total_other_inc = $add($add($add($add($add($inv_inc, $memb), $comm), $rental), $misc), $extra_rev);
        $push('subtotal', 'Total Other Income', $total_other_inc, array(
            'indent' => 1, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $gross_rev = $add($total_credit_ops, $total_other_inc);
        $push('total', 'Total Gross Revenues', $gross_rev, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        // ---- EXPENSES ----
        $push('section', 'Less: Expenses', null, array('bold' => true));
        $push('group', 'Financing Costs', null, array('bold' => true));
        $int_bor = $triple('71100');
        $push('account', 'Interest Expense on Borrowings - LBP', $int_bor, array('indent' => 1, 'always_show' => true));
        $int_dep = $triple('71200');
        $push('account', 'Interest Expense on Deposits', $int_dep, array('indent' => 1, 'always_show' => true));
        $oth_fin = $triple('71300');
        $push('account', 'Other Financing Charges', $oth_fin, array('indent' => 1, 'always_show' => true));
        $total_fin = $add($add($int_bor, $int_dep), $oth_fin);
        $push('subtotal', 'Total Financing Costs', $total_fin, array(
            'indent' => 1, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $push('group', 'Administrative Costs', null, array('bold' => true));
        $admin_lines = array(
            '73110' => 'Salaries & Wages',
            '73120' => 'Employees Benefits',
            '73130' => 'SSS, PHIC, ECC, PAG-IBIG Premium Contrit.',
            '73140' => 'Retirement Benefit Expense',
            '73150' => "Officers' Honorarium & Allowances",
            '73170' => 'Litigation Expenses',
            '73190' => 'Office Supplies',
            '73200' => 'Meetings & Conferences',
            '73210' => 'Trainings / Seminars',
            '73230' => 'Power Light & Water',
            '73240' => 'Travel & Transportation',
            '73250' => 'Insurance',
            '73260' => 'Repair & Maintenance',
            '73280' => 'Taxes, Fees and Charges',
            '73291' => 'Professional Fee',
            '73290' => 'Communication Expense',
            '73300' => 'Representation Expense',
            '73340' => 'Miscellaneous Expenses',
            '73350' => 'Depreciation - Furn., Fixture Equipment',
            '73380' => 'Provision for Probable Losses on Loans',
            '73410' => 'Gen. Assembly Expenses',
            '73420' => 'Cooperative Celebration Expense',
            '73430' => "Member's Benefit Expenses",
            '73440' => 'Affiliation Fee',
        );
        // Sample-only lines without dedicated COA (show as dash unless later mapped)
        $admin_extra_labels = array(
            'Depreciation - Building',
            'Depreciation - Building Improvements',
            'Provision for Probable Losses on Accts. Rec.',
        );

        $total_admin = $zero;
        $admin_codes_used = array();
        foreach ($admin_lines as $code => $label) {
            $t = $triple($code);
            $admin_codes_used[] = $code;
            $push('account', $label, $t, array('indent' => 1, 'always_show' => true));
            $total_admin = $add($total_admin, $t);
        }
        foreach ($admin_extra_labels as $label) {
            $push('account', $label, $zero, array('indent' => 1, 'always_show' => true));
        }

        // Other admin expense accounts with activity
        foreach ($ytd as $code => $info) {
            if ((int) $info['account_type'] != 70000) {
                continue;
            }
            if (in_array($code, array('71100', '71200', '71300'), true)) {
                continue;
            }
            if (in_array($code, $admin_codes_used, true)) {
                continue;
            }
            $t = $triple($code);
            if ($has_any($t)) {
                $push('account', $info['name'], $t, array('indent' => 1));
                $total_admin = $add($total_admin, $t);
            }
        }

        $push('subtotal', 'Total Administrative Cost', $total_admin, array(
            'indent' => 1, 'bold' => true, 'line' => 'single', 'always_show' => true
        ));

        $total_exp = $add($total_fin, $total_admin);
        $push('total', 'Total Expenses', $total_exp, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        $net_before = $sub($gross_rev, $total_exp);
        $push('total', 'Net Surplus (Loss) Before Other', $net_before, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        // ---- OTHER ITEMS ----
        $push('group', 'Add/(Less): OTHER Items', null, array('bold' => true));
        $opt_sub = $triple('80400');
        $push('account', 'Optional Fund Subsidy', $opt_sub, array('indent' => 1, 'always_show' => true));
        $edu_sub = $triple('80500');
        $push('account', 'Education and Training Fund Subsidy', $edu_sub, array('indent' => 1, 'always_show' => true));
        $cdf_sub = $triple('80550');
        $push('account', 'CDF Subsidy', $cdf_sub, array('indent' => 1, 'always_show' => true));
        $cdf_exp = $triple('80560');
        // expense-like already positive as debit-credit; display as deduction (negate for netting)
        $cdf_exp_disp = array(
            'ytd' => -1 * $cdf_exp['ytd'],
            'month' => -1 * $cdf_exp['month'],
            'prior' => -1 * $cdf_exp['prior'],
        );
        $push('account', 'CDF Subsidized Expenses', $cdf_exp_disp, array(
            'indent' => 1, 'always_show' => true, 'negate_display' => false
        ));

        $total_other = $add($add($add($opt_sub, $edu_sub), $cdf_sub), $cdf_exp_disp);

        // Other 80000 activity
        foreach ($ytd as $code => $info) {
            if ((int) $info['account_type'] != 80000) {
                continue;
            }
            if (in_array($code, array('80400', '80500', '80550', '80560'), true)) {
                continue;
            }
            $t = $triple($code);
            if (!$has_any($t)) {
                continue;
            }
            // Subsidized expenses: show as negative contribution
            if (in_array($code, array('80450', '80510'), true)) {
                $t = array('ytd' => -$t['ytd'], 'month' => -$t['month'], 'prior' => -$t['prior']);
            }
            $push('account', $info['name'], $t, array('indent' => 1));
            $total_other = $add($total_other, $t);
        }

        $push('total', 'TOTAL OTHER ITEMS', $total_other, array(
            'bold' => true, 'line' => 'double', 'always_show' => true
        ));

        $net_surplus = $add($net_before, $total_other);
        $push('total', 'NET SURPLUS (NET LOSS)', $net_surplus, array(
            'bold' => true, 'peso' => true, 'line' => 'double', 'always_show' => true
        ));

        return array(
            'rows' => $rows,
            'periods' => array(
                'as_of' => $as_of,
                'year_start' => $year_start,
                'month_start' => $month_start,
                'prev_month_end' => $prev_month_end,
                'ytd_label' => 'For the period ended ' . strtoupper(date('F d, Y', strtotime($as_of))) . ' (Total)',
                'month_label' => 'For the month of ' . strtoupper(date('F Y', strtotime($as_of))),
                'prior_label' => 'For the period ended ' . strtoupper(date('F d, Y', strtotime($prev_label_date))) . ' (Previous Mo.)',
            ),
            'totals' => array(
                'gross_revenues' => $gross_rev,
                'total_expenses' => $total_exp,
                'net_before_other' => $net_before,
                'other_items' => $total_other,
                'net_surplus' => $net_surplus,
            ),
        );
    }

}
