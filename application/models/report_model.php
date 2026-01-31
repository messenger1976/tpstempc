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
        $sql = "SELECT general_ledger.account as account, account_chart.account_type as account_type,account_chart.name as name, SUM(general_ledger.credit) as credit,SUM(general_ledger.debit) as debit
                    FROM general_ledger INNER JOIN account_chart ON account_chart.account=general_ledger.account WHERE general_ledger.PIN=account_chart.PIN AND account_chart.PIN='$pin' AND general_ledger.date <= '$date' AND account_chart.account_type='$category'  GROUP BY general_ledger.account";

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

    function journal_trans($from, $until, $journal_id) {
        $pin = current_user()->PIN;
        $sql = "SELECT general_ledger.*,account_chart.name,general_ledger.entryid,(SELECT type FROM journal WHERE id=general_ledger.journalID) as trans_comment FROM general_ledger INNER JOIN account_chart ON
                account_chart.account=general_ledger.account WHERE general_ledger.PIN=account_chart.PIN AND account_chart.PIN='$pin' AND general_ledger.date >= '$from' AND general_ledger.date <= '$until' AND general_ledger.account_type != 3 ";


        $sql .= " AND general_ledger.journalID = '$journal_id'";

        $sql.=" ORDER BY general_ledger.date ASC, general_ledger.entryid ASC,general_ledger.debit DESC";

        return $this->db->query($sql)->result();
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
        $sql = "SELECT  id, amount, account, trans_date,comment,system_comment,trans_type,paymethod,
case when trans_type = 'CR' then amount else 0 end as credit,
case when trans_type = 'DR' then amount else 0 end as debit,
previous_balance, (SELECT SUM(CASE when trans_type = 'CR' then amount else 0 end) FROM savings_transaction WHERE account='$account' AND trans_date < '$fromdate 00:00:00' ) as credit_total,
    (SELECT SUM(CASE when trans_type = 'DR' then amount else 0 end) FROM savings_transaction WHERE account='$account' AND trans_date < '$fromdate 00:00:00' ) as debit_total
FROM  
  savings_transaction WHERE account='$account' AND trans_date>='$fromdate 00:00:00' AND trans_date <= '$until 23:59:59'  ORDER BY trans_date ASC";

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
  contribution_transaction WHERE PIN='$pin' AND   createdon>='$fromdate 00:00:00' AND createdon <= '$until 23:59:59'  GROUP BY ABS(member_id)";

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

    function loan_transactions($fromdate, $untill) {
        $pin = current_user()->PIN;
        $sql = "SELECT * FROM loan_repayment_receipt WHERE PIN='$pin' AND  paydate >= '$fromdate' AND paydate <= '$untill'  ORDER BY paydate ASC";
        return $this->db->query($sql)->result();
    }

    function loan_transactions_summary($fromdate, $untill) {
        $pin = current_user()->PIN;
        $sql = "SELECT LID, SUM(amount) as amount FROM loan_repayment_receipt WHERE PIN='$pin' AND paydate >= '$fromdate' AND paydate <= '$untill' GROUP BY LID ";
        return $this->db->query($sql)->result();
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
                    LEFT JOIN cash_disbursement_items cdi ON cdi.disburse_id = cd.id
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

}
