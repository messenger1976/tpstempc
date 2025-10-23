<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of supplier
 *
 * @author miltone
 */
class Supplier extends CI_Controller {

    //put your code here
    //put your code here
    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_supplier');
        $this->lang->load('member');
        $this->lang->load('finance');
        $this->lang->load('setting');
        $this->lang->load('customer');
        $this->lang->load('supplier');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
        $this->load->model('customer_model');
        $this->load->model('supplier_model');
    }
    
    
    
      function purchase_invoice_delete($quoteid) {
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        if ($transaction->status == 0) {
            //remove invoice
            $this->db->delete('purchase_invoice_item', array('invoiceid' => $quoteid));
            $this->db->delete('purchase_invoice', array('id' => $quoteid));
            $this->db->delete('general_ledger_entry', array('id' => $transaction->ledger_entry));
            $this->db->delete('general_ledger', array('entryid' => $transaction->ledger_entry));
            $this->session->set_flashdata('message', 'Invoice removed');
            redirect(current_lang() . '/supplier/supplier_purchase_invoice', 'refresh');
        }
        redirect(current_lang() . '/supplier/supplier_purchase_invoice', 'refresh');
    }

    function supplier_list() {

        $this->load->library('pagination');
        $this->data['title'] = lang('supplier_list_title');

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


        $config["base_url"] = site_url(current_lang() . '/supplier/supplier_list/');
        $config["total_rows"] = $this->supplier_model->count_supplier($key);
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

        $this->data['supplier_list'] = $this->supplier_model->search_supplier($key, $config["per_page"], $page);



        $this->data['content'] = 'supplier/supplier_list';
        $this->load->view('template', $this->data);
    }

    function supplier_register($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
        }
        if (is_null($id)) {
            $this->data['title'] = lang('supplier_register');
        } else {
            $this->data['title'] = lang('supplier_edit');
        }

        $this->form_validation->set_rules('pre_phone1', '', '');
        $this->form_validation->set_rules('name', lang('supplier_name'), 'required');
        if (is_null($id)) {
            $this->form_validation->set_rules('identity', lang('supplier_id'), 'required');
        }
        $this->form_validation->set_rules('address', lang('supplier_address'), 'required');
        $this->form_validation->set_rules('email', lang('supplier_email'), 'valid_email');
        $this->form_validation->set_rules('phone', lang('supplier_phone'), 'required|numeric|valid_phone');
        $this->form_validation->set_rules('fax', lang('supplier_fax'), '');
        $this->form_validation->set_rules('additional', lang('supplier_additional'), '');
        if ($this->form_validation->run() == TRUE) {
            $supplier_number = trim($this->input->post('identity'));
            $supplier_info = array(
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
                if (!$this->supplier_model->is_number_exist($supplier_number)) {
                    $supplier_info['supplierid'] = $supplier_number;
                } else {
                    $error = 1;
                }
            }
            if ($error == 0) {
                $create = $this->supplier_model->create_supplier($supplier_info, $id);
                if ($create) {
                    $this->session->set_flashdata('message', lang('supplier_registration_success'));
                    redirect(current_lang() . '/supplier/supplier_register/' . $this->data['id'], 'refresh');
                } else {
                    $this->data['warning'] = lang('supplier_registration_fail');
                }
            } else {
                $this->data['warning'] = lang('supplier_number_exist');
            }
        }

        if (!is_null($id)) {
            $this->data['supplierinfo'] = $this->supplier_model->supplier_info($id)->row();
        }

        $this->data['content'] = 'supplier/supplier_register';
        $this->load->view('template', $this->data);
    }

    function supplier_purchase_order() {
        $this->data['title'] = lang('supplier_purchase_order');
        $this->data['purchase_order'] = $this->supplier_model->purchase_order_list();
        $this->data['content'] = 'supplier/supplier_purchase_order';
        $this->load->view('template', $this->data);
    }

    function create_order() {

        $this->data['title'] = lang('new_purchase_order');
        $this->data['supplierlist'] = $this->supplier_model->supplier_info()->result();
        $this->form_validation->set_rules('issue_date', lang('purchaseorder_date'), 'required|valid_date');
        $this->form_validation->set_rules('delivery_date', lang('delivery_date'), 'required|valid_date');
        $this->form_validation->set_rules('supplierid', lang('supplier_name'), 'required');
        $this->form_validation->set_rules('address', lang('salesquote_address'), '');
        $this->form_validation->set_rules('summary', lang('salesquote_summary'), '');
        $this->form_validation->set_rules('notes', lang('salesquote_notes'), '');
        $this->form_validation->set_rules('authosizedby', lang('purchaseorder_authosizedby'), 'required');

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
                    'delivery_date' => format_date(trim($this->input->post('delivery_date'))),
                    'supplierid' => trim($this->input->post('supplierid')),
                    'address' => trim($this->input->post('address')),
                    'summary' => trim($this->input->post('summary')),
                    'notes' => trim($this->input->post('notes')),
                    'authorizedby' => trim($this->input->post('authosizedby')),
                    'totalamount' => $grandtotal,
                    'totalamounttax' => $grandtotal_tax,
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );

                $create = $this->supplier_model->create_purchase_order($main_data, $array_items);
                if ($create) {
                    redirect(current_lang() . '/supplier/purchase_order_view/' . encode_id($create), 'refresh');
                } else {
                    $this->data['warning'] = lang('salesquoteprocess_fail');
                }
            } else {
                $this->data['warning'] = lang('salesquoteprocess_fail_at_all');
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['item_list'] = $this->setting_model->item_info(null, null, 2)->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(40, 50));

        $this->data['content'] = 'supplier/create_order';
        $this->load->view('template', $this->data);
    }

    function purchase_order_view($quoteid) {
        $this->data['title'] = lang('supplier_purchase_order1');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_order', array('id' => $quoteid))->row();
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'supplier/purchase_order_view';
        $this->load->view('template', $this->data);
    }
    function purchase_invoice_view($quoteid) {
        $this->data['title'] = lang('supplier_purchase_invoice');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'supplier/purchase_invoice_view';
        $this->load->view('template', $this->data);
    }
    
    
    function print_sales_purchase_order($quoteid){
     $this->data['title'] = lang('supplier_purchase_order1');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_order', array('id' => $quoteid))->row();
        if ($transaction) {
            include 'pdf/purchase_order.php';
            exit;
        }
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'supplier/purchase_order_view';
        $this->load->view('template', $this->data);   
    }
    
    function print_sales_purchase_invoice($quoteid){
     $this->data['title'] = lang('supplier_purchase_invoice');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        if ($transaction) {
            include 'pdf/purchase_invoice.php';
            exit;
        }
        $this->data['transaction'] = $transaction;
        $this->data['content'] = 'supplier/purchase_invoice_view';
        $this->load->view('template', $this->data);   
    }
    
    
    
    function send_purchase_order($quoteid){
        $this->load->library('maildata');
        $this->data['title'] = lang('send_quote');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_order', array('id' => $quoteid))->row();

        if ($transaction) {
            include 'pdf/purchase_order_mail.php';
            //change permission
            $file_name = 'purchaseorder/purchase_order_'. $quoteid . '.pdf';
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
                    redirect(current_lang() . '/supplier/send_purchase_order/' . $this->data['quoteid'], 'refresh');
                } else {
                    $this->data['warning'] = lang('mail_sent_fail');
                }
            }
        }
        $this->data['customerinfo'] = $this->supplier_model->supplier_info(null, $transaction->supplierid)->row();
        $this->data['content'] = 'supplier/purchase_order_email';
        $this->load->view('template', $this->data);
    }
    
    function send_purchase_invoice($quoteid){
        $this->load->library('maildata');
        $this->data['title'] = lang('send_quote');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();

        if ($transaction) {
            include 'pdf/purchase_invoice_mail.php';
            //change permission
            $file_name = 'purchaseinvoice/purchase_invoice_'. $quoteid . '.pdf';
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
                    redirect(current_lang() . '/supplier/send_purchase_invoice/' . $this->data['quoteid'], 'refresh');
                } else {
                    $this->data['warning'] = lang('mail_sent_fail');
                }
            }
        }
        $this->data['customerinfo'] = $this->supplier_model->supplier_info(null, $transaction->supplierid)->row();
        $this->data['content'] = 'supplier/purchase_invoice_email';
        $this->load->view('template', $this->data);
    }
    
    
    function supplier_purchase_invoice(){
         $this->data['title'] = lang('supplier_purchase_invoice');
        $this->data['purchase_invoice'] = $this->supplier_model->purchase_invoice_list();
        $this->data['content'] = 'supplier/purchase_invoice';
        $this->load->view('template', $this->data);
    }
    
    
    
    function create_purchase_invoice(){
        
        $this->data['title'] = lang('new_purchase_invoice');
        $this->data['supplierlist'] = $this->supplier_model->supplier_info()->result();
        $this->form_validation->set_rules('issue_date', lang('purchaseorder_date'), 'required|valid_date');
        $this->form_validation->set_rules('due_date', lang('due_date'), 'required|valid_date');
        $this->form_validation->set_rules('supplierid', lang('supplier_name'), 'required');
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
                    'supplierid' => trim($this->input->post('supplierid')),
                    'summary' => trim($this->input->post('summary')),
                    'notes' => trim($this->input->post('notes')),
                    'totalamount' => $grandtotal,
                    'totalamounttax' => $grandtotal_tax,
                    'balance' => ($grandtotal+$grandtotal_tax),
                    'createdby' => current_user()->id,
                    'PIN' => current_user()->PIN,
                );

                $create = $this->supplier_model->create_purchase_invoice($main_data, $array_items);
                if ($create) {
                    redirect(current_lang() . '/supplier/purchase_invoice_view/' . encode_id($create), 'refresh');
                } else {
                    $this->data['warning'] = lang('salesquoteprocess_fail');
                }
            } else {
                $this->data['warning'] = lang('salesquoteprocess_fail_at_all');
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['item_list'] = $this->setting_model->item_info(null, null, 2)->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype(array(40, 50));

        $this->data['content'] = 'supplier/new_purchase_invoice';
        $this->load->view('template', $this->data);
    }
    
   
    
      function copytonewinvoice($quoteid) {
        $this->data['title'] = lang('copytonewinvoice');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        $this->data['quoteinfo'] = $transaction;
        $this->form_validation->set_rules('issue_date', lang('salesquote_date'), 'required|valid_date');
        $this->form_validation->set_rules('due_date', lang('due_date'), 'required|valid_date');

        if ($this->form_validation->run() == TRUE) {
            $issue_date = trim($this->input->post('issue_date'));
            $due_date = trim($this->input->post('due_date'));
            $summary = trim($this->input->post('summary'));
            $notes = trim($this->input->post('notes'));
            $create_new_invoice = $this->supplier_model->copy_order_to_invoice($issue_date, $due_date, $quoteid, $summary, $notes);
            if ($create_new_invoice) {
                redirect(current_lang() . '/supplier/purchase_invoice_view/' . encode_id($create_new_invoice), 'refresh');
            } else {
                $this->data['warning'] = lang('copy_fail');
            }
        }


        $this->data['content'] = 'supplier/copytonewinvoice';
        $this->load->view('template', $this->data);
    }
    
    
    function spendmoney_purchase_invoice($quoteid) {
        $this->data['title'] = lang('pay_invoice');
        $this->data['quoteid'] = $quoteid;
        $quoteid = decode_id($quoteid);
        $transaction = $this->db->get_where('purchase_invoice', array('id' => $quoteid))->row();
        $this->data['transaction'] = $transaction;
        if ($this->input->post('amount')) {
            $_POST['amount'] = str_replace(',', '', $_POST['amount']);
        }
        $this->form_validation->set_rules('paydate', lang('paydate'), 'required|valid_date');
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');
        $this->form_validation->set_rules('received_account', lang('pay_from_account'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $paydate = format_date(trim($this->input->post('paydate')));
            $amount = trim($this->input->post('amount'));
            $received_in_account = trim($this->input->post('received_account'));
            $create_new_invoice = $this->supplier_model->supplier_pay_invoice($paydate, $amount,$received_in_account, $quoteid);
            if ($create_new_invoice) {
                $this->session->set_flashdata('message','Payment Recorded successfully');
                redirect(current_lang() . '/supplier/spendmoney_purchase_invoice/' .  $this->data['quoteid'], 'refresh');
            } else {
                $this->data['warning'] = lang('copy_fail');
            }
        }


        $this->data['content'] = 'supplier/pay_purchase_invoice';
        $this->load->view('template', $this->data);

    }
    
    
    
}
