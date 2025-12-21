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
        if (count($come) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function is_loan_exist($loan_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('LID', $loan_id);
        $come = $this->db->get('loan_contract')->row();
        if (count($come) > 0) {
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
        if (count($check) == 1) {
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

    function edit_loan_info($data, $loanid) {
        $this->db->update('loan_contract', $data, array('LID' => $loanid));
        return $loanid;
    }

    function add_newloan($data, $processingfee = 0) {
        $loanid = $this->db->get('auto_inc')->row()->loan;
        // increatent 1 next PIN
        $this->db->set('loan', 'loan+1', FALSE);
        $this->db->update('auto_inc');

        $data['LID'] = 'LN' . $loanid;

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

    function loan_wait_evaluation() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT * FROM loan_contract WHERE PIN='$pin' AND (status=0 OR status=3) ORDER BY applicationdate DESC")->result();
    }

    function loan_wait_approval() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT * FROM loan_contract WHERE PIN='$pin' AND status=1 ORDER BY applicationdate DESC")->result();
    }

    function loan_wait_disburse() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT * FROM loan_contract WHERE PIN='$pin' AND status=4 AND disburse=0 ORDER BY applicationdate DESC")->result();
    }

    function loan_repay_list() {
        $pin = current_user()->PIN;
        return $this->db->query("SELECT loan_contract.*,members.firstname,members.middlename,members.lastname  FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin' AND loan_contract.status=4 AND loan_contract.disburse=1 ORDER BY loan_contract.LID ASC")->result();
    }

    function count_loan($key = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT loan_contract.* FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID WHERE loan_contract.PIN='$pin'  ";

        if (!is_null($key)) {
            $sql .= "  AND (loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }

        return count($this->db->query($sql)->result());
    }

    function search_loan($key, $limit, $start) {
        $pin = current_user()->PIN;
        $sql = "SELECT loan_contract.*,loan_status.name FROM loan_contract INNER JOIN members ON members.PID=loan_contract.PID ";
        $sql .= " INNER JOIN loan_status ON loan_status.code=loan_contract.status WHERE loan_contract.PIN='$pin'";

        if (!is_null($key)) {
            $sql .= "  AND ( loan_contract.LID LIKE '$key%' OR loan_contract.member_id LIKE '$key%' OR members.firstname LIKE '$key%' OR members.lastname LIKE '$key%')";
        }

        $sql.= " ORDER BY loan_contract.applicationdate ASC LIMIT $start,$limit";

        return $this->db->query($sql)->result();
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

    function loan_repay_receipt($LID, $amount, $paydate) {
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

        $this->db->insert('loan_repayment_receipt', $array);
        return $receipt;
    }

    function add_remain_balance($LID, $amount) {
        $pin = current_user()->PIN;
        $check = $this->db->get_where('loan_balance_carry', array('LID' => $LID, 'PIN' => $pin))->row();
        if (count($check) > 0) {
            $this->db->where('LID', $LID);
            $this->db->where('PIN', $pin);
            $this->db->set('balance', $amount, FALSE);
            return $this->db->update('loan_balance_carry');
        } else {
            return $this->db->insert('loan_balance_carry', array('LID' => $LID, 'PIN' => $pin, 'balance' => $amount));
        }
    }

    function record_loan_repayment($array_data, $repay_schedule_ref) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $insert = $this->db->insert('loan_contract_repayment', $array_data);
        $referenceID = $this->db->insert_id();
        //general entry id
        $ledger_entry = array('date' => $array_data['paydate'], 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $LID = $array_data['LID'];
        $infodata = $this->loan_model->loan_info($LID)->row();
        $product = $this->setting_model->loanproduct($infodata->product_type)->row();
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

        //bank account
        $debit_account = 1010001;
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
        //bank account
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $debit_account = 1010001;
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
        $ledger['account'] = 3000002;
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $ledger['credit'] = $array_data['interest'];
        $this->db->insert('general_ledger', $ledger);


        //check if penalty exist
        if (array_key_exists('penalt', $array_data)) {
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $debit_account = 1010001;
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
            $ledger['account'] = 3000002;
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['credit'] = $array_data['penalt'];
            $this->db->insert('general_ledger', $ledger);
        }


        $this->db->update('loan_contract_repayment_schedule', array('status' => 1), array('id' => $repay_schedule_ref));
        $this->db->update('loan_repayment_receipt', array('affect_loan' => 1, 'installment' => $array_data['installment']), array('receipt' => $array_data['receipt']));
        $this->db->trans_complete();
        return $insert;
    }
    
    
    //paying all loan before the end of the given duration
     function record_loan_repayment_all($array_data, $repay_schedule_ref, $loan_id) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $insert = $this->db->insert('loan_contract_repayment', $array_data);
        $referenceID = $this->db->insert_id();
        //general entry id
        $ledger_entry = array('date' => $array_data['paydate'], 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $LID = $array_data['LID'];
        $infodata = $this->loan_model->loan_info($LID)->row();
        $product = $this->setting_model->loanproduct($infodata->product_type)->row();
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

        //bank account
        $debit_account = 1010001;
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
        //bank account
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $debit_account = 1010001;
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
        $ledger['account'] = 3000002;
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $ledger['credit'] = $array_data['interest'];
        $this->db->insert('general_ledger', $ledger);


        //check if penalty exist
        if (array_key_exists('penalt', $array_data)) {
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $debit_account = 1010001;
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
            $ledger['account'] = 3000002;
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
 $infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['credit'] = $array_data['penalt'];
            $this->db->insert('general_ledger', $ledger);
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

    // Loan Beginning Balances Methods
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

    function loan_beginning_balance_post_to_ledger($id) {
        $pin = current_user()->PIN;
        $balance = $this->loan_beginning_balance_list(null, $id)->row();
        
        if (!$balance || $balance->posted == 1) {
            return false; // Already posted or doesn't exist
        }
        
        // Get fiscal year info
        $fiscal_year = $this->db->where('id', $balance->fiscal_year_id)->get('fiscal_year')->row();
        if (!$fiscal_year) {
            return false;
        }
        
        // Get loan product info
        $product = $this->db->where('id', $balance->loan_product_id)->where('PIN', $pin)->get('loan_product')->row();
        if (!$product) {
            return false;
        }
        
        $this->db->trans_start();
        
        // Create ledger entry
        $ledger_entry = array(
            'date' => $fiscal_year->start_date,
            'PIN' => $pin
        );
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();
        
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
            }
            
            $this->db->insert('general_ledger', $ledger);
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
            }
            
            $this->db->insert('general_ledger', $ledger);
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
            }
            
            $this->db->insert('general_ledger', $ledger);
        }
        
        // Update loan beginning balance as posted
        $update_data = array(
            'posted' => 1,
            'posted_date' => date('Y-m-d H:i:s'),
            'posted_by' => current_user()->id
        );
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $this->db->update('loan_beginning_balances', $update_data);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
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
