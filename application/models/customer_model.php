<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of customer_model
 *
 * @author miltone
 */
class Customer_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function is_number_exist($customer_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('customerid', $customer_id);
        $check = $this->db->get('customer')->row();

        if (count($check) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function create_customer($data, $id = null) {
        if (is_null($id)) {
            return $this->db->insert('customer', $data);
        } else {
            return $this->db->update('customer', $data, array('id' => $id));
        }
    }

    function customer_info($id = null, $customerid = null) {
         $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($customerid)) {
            $this->db->where('customerid', $customerid);
        }

        return $this->db->get('customer');
    }

    function count_customer($key) {
        $pin = current_user()->PIN;
        $sql = "SELECT * FROM customer WHERE PIN='$pin' ";
        if (!is_null($key)) {
            $sql.= " AND (customerid LIKE '%$key%' OR name LIKE '%$key%')";
        }
        return count($this->db->query($sql)->result());
    }

    function search_customer($key, $limit, $start) {
       $pin = current_user()->PIN;
        $sql = "SELECT * FROM customer WHERE PIN='$pin' ";
        if (!is_null($key)) {
            $sql.= " AND (customerid LIKE '%$key%' OR name LIKE '%$key%')";
        }
$sql.= " LIMIT $start,$limit";
       // $this->db->limit($limit, $start);
        return $this->db->query($sql)->result();
    }

    function create_customer_sales_quote($main_data, $array_items) {
        // insert in maid table first
        $insert = $this->db->insert('sales_quote', $main_data);
        $quoteid = $this->db->insert_id();


        if ($insert) {
            //insert data

            foreach ($array_items as $key => $value) {
                $value['quoteid'] = $quoteid;
                $this->db->insert('sales_quote_item', $value);
            }

            return $quoteid;
        }

        return FALSE;
    }

    function create_customer_sales_invoice($main_data, $array_items) {
        // insert in maid table first
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $insert = $this->db->insert('sales_invoice', $main_data);
        $quoteid = $this->db->insert_id();
        if ($insert) {
            //insert data into general_ledger
            $ledger_entry = array('date' => $main_data['issue_date'],'PIN' => $pin);
            $this->db->insert('general_ledger_entry', $ledger_entry);
            $ledger_entry_id = $this->db->insert_id();
            $this->db->update('sales_invoice', array('ledger_entry' => $ledger_entry_id), array('id' => $quoteid));

            $ledger = array(
                'journalID' => 1,
                'refferenceID' => $quoteid,
                'entryid' => $ledger_entry_id,
                'invoiceid' => $quoteid,
                'date' => $main_data['issue_date'],
                'description' => 'Sales Invoice',
                'linkto' => 'sales_invoice.id',
                'fromtable' => 'sales_invoice',
                'paid' => 0,
                'PIN' => $pin,
                'customerid' => $main_data['customerid'],
            );

            foreach ($array_items as $key => $value) {
                $value['invoiceid'] = $quoteid;
                $this->db->insert('sales_invoice_item', $value);
                //credit account sales account (income)
                $ledger['account'] = $value['account'];
                $ledger['credit'] = $value['amount'];
                //$ledger['account_type'] = account_row_info($value['account'])->account_type;
$infoaccount = account_row_info($value['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
                $this->db->insert('general_ledger', $ledger);
            }

            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            if ($main_data['totalamounttax'] > 0) {
                //credit Tax payable (Liabilities)
                $ledger['account'] = 2010001;
                //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
                $ledger['credit'] = $main_data['totalamounttax'];
                $this->db->insert('general_ledger', $ledger);
            }
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;

            //debit account receivable asset
            $ledger['account'] = 1010002;
            //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['debit'] = $main_data['balance'];
            $this->db->insert('general_ledger', $ledger);

            //credit equity
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = 3000002;
           // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['credit'] = $main_data['totalamount'];
            $this->db->insert('general_ledger', $ledger);


            $this->db->trans_complete();

            return $quoteid;
        }

        $this->db->trans_complete();
        return FALSE;
    }

    function copy_quote_to_invoice($issue_date, $due_date, $quoteid, $summary, $notes) {
         $pin = current_user()->PIN;
        $this->db->trans_start();

        $main_trans = $this->db->get_where('sales_quote', array('id' => $quoteid))->row();
        $main_data = array(
            'issue_date' => format_date($issue_date),
            'due_date' => format_date($due_date),
            'customerid' => $main_trans->customerid,
            'address' => $main_trans->address,
            'summary' => $summary,
            'notes' => $notes,
            'PIN' => $pin,
            'totalamount' => $main_trans->totalamount,
            'totalamounttax' => $main_trans->totalamounttax,
            'createdby' => current_user()->id,
            'balance' => ($main_trans->totalamount + $main_trans->totalamounttax)
        );
        $insert = $this->db->insert('sales_invoice', $main_data);
        $invoiceid = $this->db->insert_id();



        $ledger_entry = array('date' => $main_data['issue_date'],'PIN'=>$pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();
        $this->db->update('sales_invoice', array('ledger_entry' => $ledger_entry_id), array('id' => $invoiceid));

        $ledger = array(
            'journalID' => 1,
            'refferenceID' => $invoiceid,
            'entryid' => $ledger_entry_id,
            'invoiceid' => $invoiceid,
            'date' => $main_data['issue_date'],
            'description' => 'Sales Invoice',
            'linkto' => 'sales_invoice.id',
            'fromtable' => 'sales_invoice',
            'paid' => 0,
             'PIN' => $pin,
            'customerid' => $main_data['customerid'],
        );





        $get_items = $this->db->get_where('sales_quote_item', array('quoteid' => $quoteid))->result();
        foreach ($get_items as $key => $value) {
            unset($value->quoteid);
            unset($value->id);
            $value->invoiceid = $invoiceid;
            $value->balance = ($value->amount + $value->taxamount);
            $this->db->insert('sales_invoice_item', $value);


            //credit account sales account (income)
            $ledger['account'] = $value->account;
            $ledger['credit'] = $value->amount;
           // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $this->db->insert('general_ledger', $ledger);
        }

        $this->db->update('sales_quote', array('copy_to_invoice' => 1), array('id' => $quoteid));



        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        if ($main_data['totalamounttax'] > 0) {
            //credit Tax payable (Liabilities)
            $ledger['account'] = 2010001;
           // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
            $ledger['credit'] = $main_data['totalamounttax'];
            $this->db->insert('general_ledger', $ledger);
        }


        $ledger['credit'] = 0;
        $ledger['debit'] = 0;

        //debit account receivable asset
        $ledger['account'] = 1010002;
       // $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $ledger['debit'] = $main_data['balance'];
        $this->db->insert('general_ledger', $ledger);

        //credit equity
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = 3000002;
        //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $ledger['credit'] = $main_data['totalamount'];
        $this->db->insert('general_ledger', $ledger);


        $this->db->trans_complete();

        return $invoiceid;
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM invoice_payment_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 15);
    }

    function customer_pay_invoice($paydate, $amount, $received_in_account, $quoteid) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $original_amount = $amount;
        $main_trans = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        $trans = array(
            'customerid' => $main_trans->customerid,
            'paydate' => $paydate,
            'paymethod' => 'CASH',
            'invoiceid' => $quoteid,
            'customerid' => $main_trans->customerid,
            'comment' => 'pay invoice',
            'amount' => $amount,
             'PIN' => $pin,
            'createdby' => current_user()->id,
            'previous_balance' => $main_trans->balance,
        );

        $this->db->where('id', $quoteid);
        $this->db->set('balance', "balance-{$amount}", FALSE);
        $this->db->update('sales_invoice');
        $balance = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        $final_pay = 0;
        if ($balance->balance <= 0) {
            $this->db->where('id', $quoteid);
            $this->db->set('status', 1, FALSE);
            $this->db->update('sales_invoice');
            $final_pay = 1;
        } else {
            $this->db->where('id', $quoteid);
            $this->db->set('status', 2, FALSE);
            $this->db->update('sales_invoice');
            $final_pay = 2;
        }

        $receipt = $this->receiptNo();

        $trans['receipt'] = $receipt;

        $this->db->insert('invoice_payment_transaction', $trans);

        $item_list = $this->db->query("SELECT * FROM sales_invoice_item WHERE invoiceid  = $quoteid AND balance != 0")->result();
        $amount2 = $amount;
        foreach ($item_list as $key => $value) {
            $tmp = $amount2 - $value->balance;
            if ($tmp > 0) {
                $this->db->where('id', $value->id);
                $this->db->set('balance', 0, FALSE);
                $this->db->update('sales_invoice_item');
                $amount2 = $tmp;
            } else {
                if ($amount2 > 0) {
                    $this->db->where('id', $value->id);
                    $this->db->set('balance', "balance-{$amount2}", FALSE);
                    $this->db->update('sales_invoice_item');
                    $amount2 = 0;
                }
            }
        }


        // now goes to double entry in sales
        //now insert to income journal
        //credit account receivable
        $credit_account = 1010002;
        $debit_account = $received_in_account;

        $ledger_entry = array('date' => $paydate);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $ledger = array(
            'journalID' => 3,
            'refferenceID' => $quoteid,
            'entryid' => $ledger_entry_id,
            'invoiceid' => $quoteid,
            'date' => $paydate,
            'description' => 'Receive Payment',
            'linkto' => 'sales_invoice.id',
            'fromtable' => 'sales_invoice',
            'paid' => $final_pay,
             'PIN' => $pin,
            'customerid' => $main_trans->customerid,
        );


        //credit
        $ledger['credit'] = $original_amount;
        $ledger['debit'] = 0;
        $ledger['account'] = $credit_account;
        //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);


        //debit
        $ledger['credit'] = 0;
        $ledger['debit'] = $original_amount;
        $ledger['account'] = $received_in_account;
        //$ledger['account_type'] = account_row_info($ledger['account'])->account_type;
$infoaccount = account_row_info($ledger['account']);
        $ledger['account_type'] = $infoaccount->account_type;
      $ledger['sub_account_type'] = $infoaccount->sub_account_type;
        $this->db->insert('general_ledger', $ledger);

        //update paid for previous transaction in ledger
        $this->db->where('invoiceid', $quoteid);
        $this->db->set('paid', $final_pay, FALSE);
        $this->db->update('general_ledger');

        $this->db->trans_complete();

        return $receipt;
    }

    function get_invoice_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        return $this->db->get('invoice_payment_transaction')->row();
    }

}
