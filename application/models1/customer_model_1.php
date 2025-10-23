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
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($customerid)) {
            $this->db->where('customerid', $customerid);
        }

        return $this->db->get('customer');
    }

    function count_customer($key) {
        if (!is_null($key)) {
            $this->db->like('customerid', $key);
            $this->db->or_like('name', $key);
        }
        return count($this->db->get('customer')->result());
    }

    function search_customer($key, $limit, $start) {
        if (!is_null($key)) {
            $this->db->like('customerid', $key);
            $this->db->or_like('name', $key);
        }

        $this->db->limit($limit, $start);
        return $this->db->get('customer')->result();
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
        $insert = $this->db->insert('sales_invoice', $main_data);
        $quoteid = $this->db->insert_id();
        if ($insert) {
            //insert data
            //post to general ledger
            $insert = array(
                'JR' => $quoteid,
                'date' => $main_data['issue_date'],
                'description' => 'Sales Invoice',
                'trans_comment' => 'SALES',
                'linkto' => 'sales_invoice.id',
                'fromtable' => 'sales_invoice',
                'customerid' => $main_data['customerid'],
            );
            foreach ($array_items as $key => $value) {
                $value['invoiceid'] = $quoteid;
                $this->db->insert('sales_invoice_item', $value);


                //credit account sales account (income)
                $insert['account'] = $value['account'];
                $insert['credit'] = $value['amount'];
                $insert['account_type'] = account_row_info($value['amount'])->account_type;
                $this->db->insert('general_ledger', $insert);
            }


            $insert['credit'] = 0;
            $insert['debit'] = 0;
            if ($main_data['totalamounttax'] > 0) {
                //credit Tax payable (Liabilities)
                $insert['account'] = 2000001;
                $insert['account_type'] = account_row_info($insert['account'])->account_type;
                $insert['credit'] = $main_data['totalamounttax'];
                $this->db->insert('general_ledger', $insert);
            }
            $insert['credit'] = 0;
            $insert['debit'] = 0;
            //debit account receivable asset
            $insert['account'] = 1000002;
            $insert['account_type'] = account_row_info($insert['account'])->account_type;
            $insert['debit'] = $main_data['balance'];
            $this->db->insert('general_ledger', $insert);

            //credit equity
            $insert['credit'] = 0;
            $insert['debit'] = 0;
            $insert['account'] = 3000002;
            $insert['account_type'] = account_row_info($insert['account'])->account_type;
            $insert['credit'] = $main_data['balance'];
            $this->db->insert('general_ledger', $insert);

            return $quoteid;
        }

        return FALSE;
    }

    function copy_quote_to_invoice($issue_date, $due_date, $quoteid, $summary, $notes) {
        $main_trans = $this->db->get_where('sales_quote', array('id' => $quoteid))->row();
        $main_data = array(
            'issue_date' => format_date($issue_date),
            'due_date' => format_date($due_date),
            'customerid' => $main_trans->customerid,
            'address' => $main_trans->address,
            'summary' => $summary,
            'notes' => $notes,
            'totalamount' => $main_trans->totalamount,
            'totalamounttax' => $main_trans->totalamounttax,
            'createdby' => current_user()->id,
            'balance' => ($main_trans->totalamount + $main_trans->totalamounttax)
        );
        $insert = $this->db->insert('sales_invoice', $main_data);
        $invoiceid = $this->db->insert_id();

        $get_items = $this->db->get_where('sales_quote_item', array('quoteid' => $quoteid))->result();
        foreach ($get_items as $key => $value) {
            unset($value->quoteid);
            unset($value->id);
            $value->invoiceid = $invoiceid;
            $value->balance = ($value->amount + $value->taxamount);
            $this->db->insert('sales_invoice_item', $value);
        }
        $this->db->update('sales_quote', array('copy_to_invoice' => 1), array('id' => $quoteid));
        return $invoiceid;
    }

    function receiptNo() {
        $query = $this->db->query("SELECT MAX(id) as id  FROM invoice_payment_transaction")->row();
        return alphaID(($query->id * time()), FALSE, 15);
    }

    function customer_pay_invoice($paydate, $amount, $quoteid) {
        $main_trans = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        $trans = array(
            'customerid' => $main_trans->customerid,
            'paymethod' => 'CASH',
            'invoiceid' => $quoteid,
            'customerid' => $main_trans->customerid,
            'comment' => 'pay invoice',
            'amount' => $amount,
            'createdby' => current_user()->id,
            'previous_balance' => $main_trans->balance,
        );

        $this->db->where('id', $quoteid);
        $this->db->set('balance', "balance-{$amount}", FALSE);
        $this->db->update('sales_invoice');
        $balance = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        if ($balance->balance <= 0) {
            $this->db->where('id', $quoteid);
            $this->db->set('status', 1, FALSE);
            $this->db->update('sales_invoice');
        }
        $receipt = $this->receiptNo();

        $trans['receipt'] = $receipt;

        $this->db->insert('invoice_payment_transaction', $trans);

        // now goes to double entry in sales
        //now insert to income journal
        //$credit_account = 100004;
        $debit_account = 100007;

        $income_journal_entry = array(
            'trans_date' => date('Y-m-d'),
            'description' => 'Invoice payment - #' . $quoteid
        );

        //insert here
        $this->db->insert('journal_income_entries', $income_journal_entry);
        $jid = $this->db->insert_id();

        //jornal tansaction income
        $credit_trans1 = array(
            'JR' => $jid,
            'date' => date('Y-m-d'),
            'description' => 'Payment Invoice #' . $quoteid,
            'createdby' => current_user()->id,
            'fromtable' => 'customer',
            'linkto' => 'customer.customerid',
            'invoiceid' => $quoteid,
            'customerid' => $main_trans->customerid
        );


        $item_list = $this->db->query("SELECT * FROM sales_invoice_item WHERE invoiceid  = $quoteid AND balance != 0")->result();
        $amount2 = $amount;
        foreach ($item_list as $key => $value) {
            $tmp = $amount2 - $value->balance;
            if ($tmp > 0) {
                $this->db->where('id', $value->id);
                $this->db->set('balance', 0, FALSE);
                $this->db->update('sales_invoice_item');
                $credit_trans1['account'] = $value->account;
                $credit_trans1['credit'] = $value->balance;
                //insert now credit
                $this->db->insert('journal_income_transaction', $credit_trans1);

                $amount2 = $tmp;
            } else {
                if ($amount2 > 0) {
                    $this->db->where('id', $value->id);
                    $this->db->set('balance', "balance-{$amount2}", FALSE);
                    $this->db->update('sales_invoice_item');
                    $credit_trans1['account'] = $value->account;
                    $credit_trans1['credit'] = $amount2;
                    //insert now credit
                    $this->db->insert('journal_income_transaction', $credit_trans1);
                    $amount2 = 0;
                }
            }
        }




        $credit_trans = array(
            'JR' => $jid,
            'date' => date('Y-m-d'),
            'description' => 'Payment Invoice #' . $quoteid,
            'createdby' => current_user()->id,
            'fromtable' => 'customer',
            'linkto' => 'customer.customerid',
            'invoiceid' => $quoteid,
            'customerid' => $main_trans->customerid
        );
        //debit
        $debit_trans = $credit_trans;
        $debit_trans['account'] = $debit_account;
        $debit_trans['debit'] = $amount;
        $this->db->insert('journal_income_transaction', $debit_trans);


        return $receipt;
    }

    function get_invoice_transaction($receipt) {
        $this->db->where('receipt', $receipt);
        return $this->db->get('invoice_payment_transaction')->row();
    }

}
