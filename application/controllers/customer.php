<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of customer
 *
 * @author miltone
 */
class Customer extends CI_Controller {

    //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_customer');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->lang->load('setting');
        $this->lang->load('customer');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
        $this->load->model('customer_model');
    }

    function customer_register($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        if (is_null($id)) {
            $this->data['title'] = lang('customer_register');
        } else {
            $this->data['title'] = lang('customer_edit');
        }

        $this->form_validation->set_rules('pre_phone1', '', '');
        $this->form_validation->set_rules('name', lang('customer_name'), 'required');
        if (is_null($id)) {
            $this->form_validation->set_rules('identity', lang('customer_id'), 'required');
        }
        $this->form_validation->set_rules('address', lang('customer_address'), 'required');
        $this->form_validation->set_rules('email', lang('customer_email'), 'valid_email');
        $this->form_validation->set_rules('phone', lang('customer_phone'), 'required|numeric|valid_phone');
        $this->form_validation->set_rules('fax', lang('customer_fax'), '');
        $this->form_validation->set_rules('additional', lang('customer_additional'), '');
        if ($this->form_validation->run() == TRUE) {
            $customer_number = trim($this->input->post('identity'));
            $customer_info = array(
                'PIN' => current_user()->PIN,
                'name' => trim($this->input->post('name')),
                'address' => trim($this->input->post('address')),
                'email' => trim($this->input->post('email')),
                'fax' => trim($this->input->post('fax')),
                'additional' => trim($this->input->post('additional')),
                'phone' => trim($this->input->post('pre_phone1')) . trim($this->input->post('phone')),
            );
            $error = 0;
            if (is_null($id)) {
                if (!$this->customer_model->is_number_exist($customer_number)) {
                    $customer_info['customerid'] = $customer_number;
                } else {
                    $error = 1;
                }
            }
            if ($error == 0) {
                $create = $this->customer_model->create_customer($customer_info, $id);
                if ($create) {
                    $this->session->set_flashdata('message', lang('customer_registration_success'));
                    redirect(current_lang() . '/customer/customer_register/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('customer_registration_fail');
                }
            } else {
                $this->data['warning'] = lang('customer_number_exist');
            }
        }

        if (!is_null($id)) {
            $this->data['customerinfo'] = $this->customer_model->customer_info($id)->row();
        }

        $this->data['content'] = 'customer/customer_register';
        $this->load->view('template', $this->data);
    }

    function customerlist() {
        $this->load->library('pagination');
        $this->data['title'] = lang('customer_list');

        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }


        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }


        $config["base_url"] = site_url(current_lang() . '/customer/customerlist/');
        $config["total_rows"] = $this->customer_model->count_customer($key);
        $config["uri_segment"] = 4;

        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';

        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';

        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';

        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';

        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';


        $config["num_links"] = 10;


        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();

        $this->data['customer_list'] = $this->customer_model->search_customer($key, $config["per_page"], $page);



        $this->data['content'] = 'customer/customerlist';
        $this->load->view('template', $this->data);
    }

    function customersales_quote() {
        $this->data['title'] = lang('customer_salesquote');
        $this->data['customerlist'] = $this->customer_model->customer_info()->result();
        $this->form_validation->set_rules('issue_date', lang('salesquote_date'), 'required|valid_date');
        $this->form_validation->set_rules('customerid', lang('customer_name'), 'required');
        $this->form_validation->set_rules('address', lang('salesquote_address'), '');
        $this->form_validation->set_rules('summary', lang('salesquote_summary'), '');
        $this->form_validation->set_rules('notes', lang('salesquote_notes'), '');

        if ($this->form_validation->run() == TRUE) {
            $array_items = array();
            $itemlist = $this->input->post('item');
            $account = $this->input->post('account');
            $description = $this->input->post('description');
            $qty = $this->input->post('qty');
            $price = $this->input->post('price');
            $taxcode = $this->input->post('taxcode');
            $sub_total = $this->input->post('total');
            //$grandtotal = $this->input->post('summation');
            //total row
            $count = count($itemlist);
            $grandtotal_tax = 0;
            $grandtotal = 0;
            for ($i = 0; $i < $count; $i++) {
                $item_code = $itemlist[$i];
                $account_code = $account[$i];
                $description_code = $description[$i];
                $qty_code = $qty[$i];
                $price_code = $price[$i];

                $taxcode_code = $taxcode[$i];
                $subtotal_code = $sub_total[$i];

                if (empty($item_code) || empty($account_code) || empty($description_code) || empty($price_code) || empty($qty_code) || !is_numeric($qty_code) || !is_numeric($price_code)) {
                    
                } else {
                    $index = count($array_items);
                    $array_items[$index] = array(
                        'itemcode' => $item_code,
                        'account' => $account_code,
                        'description' => $description_code,
                        'qty' => $qty_code,
                        'unit_price' => $price_code,
                        'amount' => ($qty_code * $price_code),
                        'taxcode' => $taxcode_code,
                        'PIN' => current_user()->PIN,
                    );
                    $grandtotal += $array_items[$index]['amount'];
                    if (!empty($taxcode_code)) {
                        $taxinfodata = $this->setting_model->tax_info(null, $taxcode_code)->row();
                        if (count($taxinfodata) > 0) {
                            $array_items[$index]['tax_included'] = 1;
                            $array_items[$index]['taxamount'] = (($taxinfodata->rate / 100) * ($qty_code * $price_code));
                            $grandtotal_tax += $array_items[$index]['taxamount'];
                        }
                    }
                }
            }

            //  echo '<pre>';
            //  print_r($array_items);
            // echo '</pre>';
            // exit;
            if (count($array_items) > 0) {
                $main_data = array(
                    'issue_date' => format_date(trim($this->input->post('issue_date'))),
                    'customerid' => trim($this->input->post('customerid')),
                    'address' => trim($this->input->post('address')),
                    'summary' => trim($this->input->post('summary')),
                    'notes' => trim($this->input->post('notes')),
                    'totalamount' => $grandtotal,
                    'totalamounttax' => $grandtotal_tax,
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );

                $create = $this->customer_model->create_customer_sales_quote($main_data, $array_items);
                if ($create) {
                    redirect(current_lang() . '/customer/sales_quote_view/' . encode_id($create), 'refresh');
                } else {
                    $this->data['warning'] = lang('salesquoteprocess_fail');
                }
            } else {
                $this->data['warning'] = lang('salesquoteprocess_fail_at_all');
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['item_list'] = $this->setting_model->item_info(null, null, 1)->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(40, 50));

        $this->data['content'] = 'customer/salesquote';
        $this->load->view('template', $this->data);
    }

    function customersales_invoice() {
        $this->data['title'] = ' Sales Invoice';
        $this->data['customerlist'] = $this->customer_model->customer_info()->result();
        $this->form_validation->set_rules('issue_date', lang('salesquote_date'), 'required|valid_date');
        $this->form_validation->set_rules('due_date', 'Due Date', 'required|valid_date');
        $this->form_validation->set_rules('customerid', lang('customer_name'), 'required');
        $this->form_validation->set_rules('address', lang('salesquote_address'), '');
        $this->form_validation->set_rules('summary', lang('salesquote_summary'), '');
        $this->form_validation->set_rules('notes', lang('salesquote_notes'), '');

        if ($this->form_validation->run() == TRUE) {
            $array_items = array();
            $itemlist = $this->input->post('item');
            $account = $this->input->post('account');
            $description = $this->input->post('description');
            $qty = $this->input->post('qty');
            $price = $this->input->post('price');
            $taxcode = $this->input->post('taxcode');
            $sub_total = $this->input->post('total');
            //$grandtotal = $this->input->post('summation');
            //total row
            $count = count($itemlist);
            $grandtotal_tax = 0;
            $grandtotal = 0;
            for ($i = 0; $i < $count; $i++) {
                $item_code = $itemlist[$i];
                $account_code = $account[$i];
                $description_code = $description[$i];
                $qty_code = $qty[$i];
                $price_code = $price[$i];

                $taxcode_code = $taxcode[$i];
                $subtotal_code = $sub_total[$i];

                if (empty($item_code) || empty($account_code) || empty($description_code) || empty($price_code) || empty($qty_code) || !is_numeric($qty_code) || !is_numeric($price_code)) {
                    
                } else {
                    $index = count($array_items);
                    $array_items[$index] = array(
                        'itemcode' => $item_code,
                        'account' => $account_code,
                        'description' => $description_code,
                        'qty' => $qty_code,
                        'unit_price' => $price_code,
                        'amount' => ($qty_code * $price_code),
                        'taxcode' => $taxcode_code,
                        'balance' => ($qty_code * $price_code),
                        'PIN' => current_user()->PIN,
                    );
                    $grandtotal += $array_items[$index]['amount'];
                    if (!empty($taxcode_code)) {
                        $taxinfodata = $this->setting_model->tax_info(null, $taxcode_code)->row();
                        if (count($taxinfodata) > 0) {
                            $array_items[$index]['tax_included'] = 1;
                            $array_items[$index]['taxamount'] = (($taxinfodata->rate / 100) * ($qty_code * $price_code));
                            $array_items[$index]['balance'] += $array_items[$index]['taxamount'];
                            $grandtotal_tax += $array_items[$index]['taxamount'];
                        }
                    }
                }
            }

            //  echo '<pre>';
            //  print_r($array_items);
            // echo '</pre>';
            // exit;
            if (count($array_items) > 0) {
                $main_data = array(
                    'issue_date' => format_date(trim($this->input->post('issue_date'))),
                    'due_date' => format_date(trim($this->input->post('due_date'))),
                    'customerid' => trim($this->input->post('customerid')),
                    'address' => trim($this->input->post('address')),
                    'summary' => trim($this->input->post('summary')),
                    'notes' => trim($this->input->post('notes')),
                    'totalamount' => $grandtotal,
                    'totalamounttax' => $grandtotal_tax,
                    'createdby' => current_user()->id,
                    'balance' => ($grandtotal + $grandtotal_tax),
                    'PIN' => current_user()->PIN,
                );

                $create = $this->customer_model->create_customer_sales_invoice($main_data, $array_items);
                if ($create) {
                    redirect(current_lang() . '/customer/sales_invoice_view/' . encode_id($create), 'refresh');
                } else {
                    $this->data['warning'] = lang('salesquoteprocess_fail');
                }
            } else {
                $this->data['warning'] = lang('salesquoteprocess_fail_at_all');
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['item_list'] = $this->setting_model->item_info(null, null, 1)->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(40, 50));

        $this->data['content'] = 'customer/salesinvoice';
        $this->load->view('template', $this->data);
    }

    function sales_quote_view($quoteid) {
        $this->data['title'] = lang('customer_salesquote');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_quote', array('id' => $quoteid))->row();
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'customer/salesquote_view';
        $this->load->view('template', $this->data);
    }

    function sales_invoice_delete($quoteid) {
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        if ($transaction->status == 0) {
            //remove invoice
            $this->db->delete('sales_invoice_item', array('invoiceid' => $quoteid));
            $this->db->delete('sales_invoice', array('id' => $quoteid));
            $this->db->delete('general_ledger_entry', array('id' => $transaction->ledger_entry));
            $this->db->delete('general_ledger', array('entryid' => $transaction->ledger_entry));
            $this->session->set_flashdata('message', 'Invoice removed');
            redirect(current_lang() . '/customer/customersales_invoice_list', 'refresh');
        }
        redirect(current_lang() . '/customer/customersales_invoice_list', 'refresh');
    }

    function sales_invoice_view($quoteid) {
        $this->data['title'] = 'Sales Invoice';
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);

        $transaction = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'customer/salesinvoice_view';
        $this->load->view('template', $this->data);
    }

    function print_sales_quote($quoteid) {
        $this->data['title'] = lang('customer_salesquote');
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_quote', array('id' => $quoteid))->row();
        if ($transaction) {
            include 'pdf/sales_quote.php';
            exit;
        }
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'customer/salesquote_view';
        $this->load->view('template', $this->data);
    }

    function print_sales_invoice($quoteid) {
        $this->data['title'] = 'Sales Invoice';
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        if ($transaction) {
            include 'pdf/sales_invoice.php';
            exit;
        }
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'customer/salesinvoice_view';
        $this->load->view('template', $this->data);
    }

    function customersales_quote_list() {
        $this->data['title'] = lang('customersales_quote');
        $this->data['sales_quote'] = $this->finance_model->sales_quote_list();
        $this->data['content'] = 'finance/customer_sales_quote';
        $this->load->view('template', $this->data);
    }

    function customersales_invoice_list() {
        $this->data['title'] = lang('customersales_invoice');
        $this->data['sales_quote'] = $this->finance_model->sales_invoice_list();
        $this->data['content'] = 'finance/customer_sales_invoice';
        $this->load->view('template', $this->data);
    }

    function sendquote($quoteid) {
        $this->load->library('maildata');
        $this->data['title'] = lang('send_quote');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_quote', array('id' => $quoteid))->row();

        if ($transaction) {
            include 'pdf/sales_quote_mail.php';
            //change permission
            $file_name = 'salesquote/quote_' . $quoteid . '.pdf';
            chmod("./uploads/" . $file_name, 0777);
            $this->data['filename'] = $file_name;
            $this->form_validation->set_rules('recipient', lang('email_to'), 'required|valid_emails');
            $this->form_validation->set_rules('copy', lang('email_cc'), 'valid_emails');
            $this->form_validation->set_rules('subject', lang('email_title'), 'required');
            $this->form_validation->set_rules('body', lang('email_body'), 'required');

            if ($this->form_validation->run() == TRUE) {
                $subject = trim($this->input->post('subject'));
                $recepient = trim($this->input->post('recipient'));
                $cc = trim($this->input->post('copy'));
                $message = trim($this->input->post('body'));
                $recipient_email = explode(',', $recepient);
                $cc_emails = explode(',', $cc);
                $attachment = './uploads/' . $file_name;

                $send = $this->maildata->send_email($subject, $message, $recipient_email, $cc_emails, $attachment);
                if ($send) {
                    $this->session->set_flashdata('message', lang('email_sent_success'));
                    redirect(current_lang() . '/customer/sendquote/' . $this->data['quoteid'], 'refresh');
                } else {
                    $this->data['warning'] = lang('mail_sent_fail');
                }
            }
        }
        $this->data['customerinfo'] = $this->customer_model->customer_info(null, $transaction->customerid)->row();
        $this->data['content'] = 'customer/send_quote_email';
        $this->load->view('template', $this->data);
    }

    function sendsalesinvoice($quoteid) {
        $this->load->library('maildata');
        $this->data['title'] = lang('send_quote');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();

        if ($transaction) {
            include 'pdf/sales_invoice_mail.php';
            //change permission
            $file_name = 'salesinvoice/invoice_' . $quoteid . '.pdf';
            chmod("./uploads/" . $file_name, 0777);
            $this->data['filename'] = $file_name;
            $this->form_validation->set_rules('recipient', lang('email_to'), 'required|valid_emails');
            $this->form_validation->set_rules('copy', lang('email_cc'), 'valid_emails');
            $this->form_validation->set_rules('subject', lang('email_title'), 'required');
            $this->form_validation->set_rules('body', lang('email_body'), 'required');

            if ($this->form_validation->run() == TRUE) {
                $subject = trim($this->input->post('subject'));
                $recepient = trim($this->input->post('recipient'));
                $cc = trim($this->input->post('copy'));
                $message = trim($this->input->post('body'));
                $recipient_email = explode(',', $recepient);
                $cc_emails = explode(',', $cc);
                $attachment = './uploads/' . $file_name;

                $send = $this->maildata->send_email($subject, $message, $recipient_email, $cc_emails, $attachment);
                if ($send) {
                    $this->session->set_flashdata('message', lang('email_sent_success'));
                    redirect(current_lang() . '/customer/sendsalesinvoice/' . $this->data['quoteid'], 'refresh');
                } else {
                    $this->data['warning'] = lang('mail_sent_fail');
                }
            }
        }

        $this->data['customerinfo'] = $this->customer_model->customer_info(null, $transaction->customerid)->row();
        $this->data['content'] = 'customer/send_invoice_email';
        $this->load->view('template', $this->data);
    }

    function copytonewinvoice($quoteid) {
        $this->data['title'] = lang('copytonewinvoice');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_quote', array('id' => $quoteid))->row();
        $this->data['quoteinfo'] = $transaction;
        $this->form_validation->set_rules('issue_date', lang('salesquote_date'), 'required|valid_date');
        $this->form_validation->set_rules('due_date', lang('due_date'), 'required|valid_date');

        if ($this->form_validation->run() == TRUE) {
            $issue_date = trim($this->input->post('issue_date'));
            $due_date = trim($this->input->post('due_date'));
            $summary = trim($this->input->post('summary'));
            $notes = trim($this->input->post('notes'));
            $create_new_invoice = $this->customer_model->copy_quote_to_invoice($issue_date, $due_date, $quoteid, $summary, $notes);
            if ($create_new_invoice) {
                redirect(current_lang() . '/customer/sales_invoice_view/' . encode_id($create_new_invoice), 'refresh');
            } else {
                $this->data['warning'] = lang('copy_fail');
            }
        }


        $this->data['content'] = 'customer/copytonewinvoice';
        $this->load->view('template', $this->data);
    }

    function pay_sales_invoice($quoteid) {
        $this->data['title'] = lang('customer_pay_invoice');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('sales_invoice', array('id' => $quoteid))->row();
        $this->data['transaction'] = $transaction;
        if ($this->input->post('amount')) {
            $_POST['amount'] = str_replace(',', '', $_POST['amount']);
        }
        $this->form_validation->set_rules('paydate', lang('paydate'), 'required|valid_date');
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('received_account', lang('invoice_pay_in'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $paydate = format_date(trim($this->input->post('paydate')));
            $amount = trim($this->input->post('amount'));
            $received_in_account = trim($this->input->post('received_account'));
            $create_new_invoice = $this->customer_model->customer_pay_invoice($paydate, $amount,$received_in_account, $quoteid);
            if ($create_new_invoice) {
                redirect(current_lang() . '/customer/invoice_receipt/' . $create_new_invoice, 'refresh');
            } else {
                $this->data['warning'] = lang('copy_fail');
            }
        }


        $this->data['content'] = 'customer/pay_sales_invoice';
        $this->load->view('template', $this->data);
    }

    function invoice_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->customer_model->get_invoice_transaction($receipt);
        if ($trans) {
            $this->data['title'] = lang('view_receipt');
            $this->data['trans'] = $trans;
            $this->data['content'] = 'customer/invoice_receipt';
            $this->load->view('template', $this->data);
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

    function print_invoice_receipt($receipt) {
        $this->lang->load('setting');
        $trans = $this->customer_model->get_invoice_transaction($receipt);
        if ($trans) {
            include 'pdf/sales_receipt.php';
        } else {
            return show_error('Transaction id not exist..', 500, 'INVALID RECEIPT NUMBER');
        }
    }

}
