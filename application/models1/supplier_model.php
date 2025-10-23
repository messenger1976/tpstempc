<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of supplier_model
 *
 * @author miltone
 */
class Supplier_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function is_number_exist($supplier_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('supplierid', $supplier_id);
        $check = $this->db->get('supplier')->row();

        if (count($check) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function create_supplier($data, $id = null) {
        if (is_null($id)) {
            return $this->db->insert('supplier', $data);
        } else {
            return $this->db->update('supplier', $data, array('id' => $id));
        }
    }

    function supplier_info($id = null, $supplierid = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($supplierid)) {
            $this->db->where('supplierid', $supplierid);
        }

        return $this->db->get('supplier');
    }

    function count_supplier($key) {
        $pin = current_user()->PIN;
        $sql = " SELECT * FROM supplier WHERE PIN='$pin'";
        if (!is_null($key)) {
            $sql.= "AND (supplierid LIKE  '%$key%' OR name LIKE '%$key%')";
        }
        return count($this->db->query($sql)->result());
    }

    function search_supplier($key, $limit, $start) {
        $pin = current_user()->PIN;
        $sql = " SELECT * FROM supplier WHERE PIN='$pin'";
        if (!is_null($key)) {
            $sql.= "AND (supplierid LIKE  '%$key%' OR name LIKE '%$key%')";
        }

        $sql.= " LIMIT $start,$limit";
        //$this->db->limit($limit, $start);
        return $this->db->query($sql)->result();
    }

    function purchase_order_list() {
        $this->db->where('PIN', current_user()->PIN);
        return $this->db->get('purchase_order')->result();
    }

    function create_purchase_order($main_data, $array_items) {
        // insert in maid table first
        $insert = $this->db->insert('purchase_order', $main_data);
        $quoteid = $this->db->insert_id();
        if ($insert) {
            //insert data
            foreach ($array_items as $key => $value) {
                $value['orderid'] = $quoteid;
                $this->db->insert('purchase_order_item', $value);
            }

            return $quoteid;
        }

        return FALSE;
    }

    function create_purchase_invoice($main_data, $array_items) {
        $pin = current_user()->PIN;
        //lock for transactions
        // transaction start
        $this->db->trans_start();
        // insert in maid table first
        $insert = $this->db->insert('purchase_invoice', $main_data);
        $quoteid = $this->db->insert_id();
        if ($insert) {
            //insert data
            // double entry start here
            $ledger_entry = array('date' => $main_data['issue_date'], 'PIN' => $pin);
            $this->db->insert('general_ledger_entry', $ledger_entry);
            $ledger_entry_id = $this->db->insert_id();
            $this->db->update('purchase_invoice', array('ledger_entry' => $ledger_entry_id), array('id' => $quoteid));


            $ledger = array(
                'journalID' => 6,
                'refferenceID' => $quoteid,
                'entryid' => $ledger_entry_id,
                'invoiceid' => $quoteid,
                'date' => $main_data['issue_date'],
                'description' => 'Purchase Invoice',
                'linkto' => 'purchase_invoice.id',
                'fromtable' => 'purchase_invoice',
                'paid' => 0,
                'PIN' => $pin,
                'supplierid' => $main_data['supplierid'],
            );



            foreach ($array_items as $key => $value) {
                $value['invoiceid'] = $quoteid;
                $this->db->insert('purchase_invoice_item', $value);
                //debit accounts
                $ledger['account'] = $value['account'];
                $ledger['debit'] = $value['amount'];
                $ledger['account_type'] = account_row_info($value['account'])->account_type;
                $this->db->insert('general_ledger', $ledger);
            }

            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            if ($main_data['totalamounttax'] > 0) {
                //debit tax account
                $ledger['account'] = 2000001;
                $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                $ledger['debit'] = $main_data['totalamounttax'];
                $this->db->insert('general_ledger', $ledger);
            }


            $ledger['credit'] = 0;
            $ledger['debit'] = 0;

            //credit account payable asset
            $ledger['account'] = 2000002;
            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
            $ledger['credit'] = $main_data['balance'];
            $this->db->insert('general_ledger', $ledger);

            // //credit equity
            $ledger['credit'] = 0;
            $ledger['debit'] = 0;
            $ledger['account'] = 3000002;
            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
            $ledger['debit'] = $main_data['totalamount'];
            $this->db->insert('general_ledger', $ledger);

            $this->db->trans_complete();
            return $quoteid;
        }




        $this->db->trans_complete();
        // rollback if one of any query fail
        return FALSE;
    }

    function purchase_invoice_list() {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->order_by('status', 'ASC');
        return $this->db->get('purchase_invoice')->result();
    }

    function copy_order_to_invoice($issue_date, $due_date, $quoteid, $summary, $notes) {
        $pin = current_user()->PIN;
        $main_trans = $this->db->get_where('purchase_order', array('id' => $quoteid))->row();
        $main_data = array(
            'issue_date' => format_date($issue_date),
            'due_date' => format_date($due_date),
            'supplierid' => $main_trans->supplierid,
            'summary' => $summary,
            'notes' => $notes,
            'PIN' => $pin,
            'totalamount' => $main_trans->totalamount,
            'totalamounttax' => $main_trans->totalamounttax,
            'createdby' => current_user()->id,
            'balance' => ($main_trans->totalamount + $main_trans->totalamounttax)
        );
        $insert = $this->db->insert('purchase_invoice', $main_data);
        $invoiceid = $this->db->insert_id();

        $get_items = $this->db->get_where('purchase_order_item', array('orderid' => $quoteid))->result();
        foreach ($get_items as $key => $value) {
            unset($value->orderid);
            unset($value->id);
            $value->invoiceid = $invoiceid;
            $value->balance = ($value->amount + $value->taxamount);
            $this->db->insert('purchase_invoice_item', $value);
        }
        $this->db->update('purchase_order', array('copy_to_invoice' => 1), array('id' => $quoteid));



        ///////////////////////////////////////////////////////////////////////
        // double entry start here
        $ledger_entry = array('date' => $main_data['issue_date'], 'PIN' => $pin);

        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();
        $this->db->update('purchase_invoice', array('ledger_entry' => $ledger_entry_id), array('id' => $invoiceid));



        $ledger = array(
            'journalID' => 6,
            'refferenceID' => $invoiceid,
            'entryid' => $ledger_entry_id,
            'invoiceid' => $invoiceid,
            'date' => $main_data['issue_date'],
            'description' => 'Purchase Invoice',
            'linkto' => 'purchase_invoice.id',
            'fromtable' => 'purchase_invoice',
            'paid' => 0,
            'PIN' => $pin,
            'supplierid' => $main_data['supplierid'],
        );



        foreach ($get_items as $key => $value) {

            $ledger['invoiceid'] = $invoiceid;
            //debit accounts
            $ledger['account'] = $value->account;
            $ledger['debit'] = $value->amount;
            $ledger['account_type'] = account_row_info($value->account)->account_type;

            $this->db->insert('general_ledger', $ledger);
        }

        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        if ($main_data['totalamounttax'] > 0) {
            //debit tax account
            $ledger['account'] = 2000001;
            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
            $ledger['debit'] = $main_data['totalamounttax'];
            $this->db->insert('general_ledger', $ledger);
        }


        $ledger['credit'] = 0;
        $ledger['debit'] = 0;

        //credit account payable asset
        $ledger['account'] = 2000002;
        $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
        $ledger['credit'] = $main_data['balance'];
        $this->db->insert('general_ledger', $ledger);

        // //credit equity
        $ledger['credit'] = 0;
        $ledger['debit'] = 0;
        $ledger['account'] = 3000002;
        $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
        $ledger['debit'] = $main_data['totalamount'];
        $this->db->insert('general_ledger', $ledger);

        return $invoiceid;
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM invoice_payment_purchase_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 15);
    }

    function supplier_pay_invoice($paydate, $amount, $received_in_account, $quoteid) {
        $pin = current_user()->PIN;
        $this->db->trans_start();
        $original_amount = $amount;
        $main_trans = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        $trans = array(
            'paydate' => $paydate,
            'paymethod' => 'CASH',
            'invoiceid' => $quoteid,
            'supplierid' => $main_trans->supplierid,
            'comment' => 'pay invoice',
            'amount' => $amount,
            'createdby' => current_user()->id,
            'previous_balance' => $main_trans->balance,
            'PIN' => $pin,
        );

        $this->db->where('id', $quoteid);
        $this->db->set('balance', "balance-{$amount}", FALSE);
        $this->db->update('purchase_invoice');
        $balance = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        $final_pay = 0;
        if ($balance->balance <= 0) {
            $this->db->where('id', $quoteid);
            $this->db->set('status', 1, FALSE);
            $this->db->update('purchase_invoice');
            $final_pay = 1;
        } else {
            $this->db->where('id', $quoteid);
            $this->db->set('status', 2, FALSE);
            $this->db->update('purchase_invoice');
            $final_pay = 2;
        }

        $receipt = $this->receiptNo();

        $trans['receipt'] = $receipt;

        $this->db->insert('invoice_payment_purchase_transaction', $trans);

        $item_list = $this->db->query("SELECT * FROM purchase_invoice_item WHERE invoiceid  = $quoteid AND balance != 0")->result();
        $amount2 = $amount;
        foreach ($item_list as $key => $value) {
            $tmp = $amount2 - $value->balance;
            if ($tmp > 0) {
                $this->db->where('id', $value->id);
                $this->db->set('balance', 0, FALSE);
                $this->db->update('purchase_invoice_item');
                $amount2 = $tmp;
            } else {
                if ($amount2 > 0) {
                    $this->db->where('id', $value->id);
                    $this->db->set('balance', "balance-{$amount2}", FALSE);
                    $this->db->update('purchase_invoice_item');
                    $amount2 = 0;
                }
            }
        }


        // now goes to double entry in sales
        //credit account receivable
        $debit_account = 2000002;

        $credit_account = $received_in_account;

        $ledger_entry = array('date' => $paydate, 'PIN' => $pin);
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        $ledger = array(
            'journalID' => 6,
            'refferenceID' => $quoteid,
            'entryid' => $ledger_entry_id,
            'invoiceid' => $quoteid,
            'date' => $paydate,
            'description' => 'Purchase Invoice Payment',
            'linkto' => 'purchase_invoice.id',
            'fromtable' => 'purchase_invoice',
            'paid' => $final_pay,
            'PIN' => $pin,
            'supplierid' => $main_trans->supplierid,
        );


        //credit
        $ledger['credit'] = $original_amount;
        $ledger['debit'] = 0;
        $ledger['account'] = $credit_account;
        $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
        $this->db->insert('general_ledger', $ledger);


        //debit
        $ledger['credit'] = 0;
        $ledger['debit'] = $original_amount;
        $ledger['account'] = $debit_account;
        $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
        $this->db->insert('general_ledger', $ledger);

        //update paid for previous transaction in ledger
        $this->db->where('invoiceid', $quoteid);
        $this->db->set('paid', $final_pay, FALSE);
        $this->db->update('general_ledger');

        $this->db->trans_complete();

        return $receipt;
    }

}
