<?php

/**
 * Accounts Receivable (AR) Controller
 * AR Balances, AR Ledger, AR Aging Report
 */
class Ar extends CI_Controller {

    function __construct() {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!has_role(6, 'View_AR')) {
            $this->session->set_flashdata('warning', 'You do not have permission to access Accounts Receivable.');
            redirect(current_lang(), 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = lang('ar_module_title');
        $this->lang->load('finance');
        $this->lang->load('customer');
        $this->lang->load('ar');
        $this->load->model('ar_model');
        $this->load->model('customer_model');
        $this->load->model('finance_model');
    }

    function index() {
        redirect(current_lang() . '/ar/ar_balances', 'refresh');
    }

    /**
     * AR Balances - list customers with outstanding AR balance as of date
     */
    function ar_balances() {
        $this->data['title'] = lang('ar_balances_title');
        $as_of = $this->input->get('as_of') ?: $this->input->post('as_of');
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        } else {
            $as_of = date('Y-m-d', strtotime($as_of));
        }
        $this->data['as_of'] = $as_of;
        $this->data['balances'] = $this->ar_model->get_ar_balances($as_of);
        $this->data['total_balance'] = $this->ar_model->get_ar_total_balance($as_of);
        $this->data['content'] = 'ar/ar_balances';
        $this->load->view('template', $this->data);
    }

    /**
     * Print AR Balances
     */
    function ar_balances_print() {
        $as_of = $this->input->get('as_of');
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        } else {
            $as_of = date('Y-m-d', strtotime($as_of));
        }
        $this->data['as_of'] = $as_of;
        $this->data['balances'] = $this->ar_model->get_ar_balances($as_of);
        $this->data['total_balance'] = $this->ar_model->get_ar_total_balance($as_of);
        $html = $this->load->view('ar/print/ar_balances_print', $this->data, true);
        $this->load->library('pdf');
        $this->pdf->set_subtitle('');
        $this->pdf->hidefooter(FALSE);
        $this->pdf->AddPage();
        $this->pdf->writeHTML($html, true, false, false, false, '');
        $this->pdf->Output('AR_Balances_' . str_replace('-', '', $as_of) . '.pdf', 'I');
        exit;
    }

    /**
     * AR Ledger - transactions for AR account with running balance
     */
    function ar_ledger() {
        $this->data['title'] = lang('ar_ledger_title');
        $customer_id = $this->input->get('customer_id') ?: $this->input->post('customer_id');
        $date_from = $this->input->get('date_from') ?: $this->input->post('date_from');
        $date_to   = $this->input->get('date_to') ?: $this->input->post('date_to');
        if (empty($date_from)) {
            $date_from = date('Y-m-01');
        }
        if (empty($date_to)) {
            $date_to = date('Y-m-d');
        }
        $this->data['customer_id'] = $customer_id;
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['customers'] = $this->ar_model->get_customers_list();
        $this->data['ledger'] = $this->ar_model->get_ar_ledger($customer_id, $date_from, $date_to);
        $this->data['content'] = 'ar/ar_ledger';
        $this->load->view('template', $this->data);
    }

    /**
     * Print AR Ledger
     */
    function ar_ledger_print() {
        $customer_id = $this->input->get('customer_id');
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to   = $this->input->get('date_to') ?: date('Y-m-d');
        $this->data['customer_id'] = $customer_id;
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['customers'] = $this->ar_model->get_customers_list();
        $this->data['ledger'] = $this->ar_model->get_ar_ledger($customer_id, $date_from, $date_to);
        $customer_name = 'All Customers';
        if (!empty($customer_id)) {
            $c = $this->customer_model->customer_info(null, $customer_id)->row();
            $customer_name = $c ? $c->name : $customer_id;
        }
        $this->data['customer_name'] = $customer_name;
        $html = $this->load->view('ar/print/ar_ledger_print', $this->data, true);
        $this->load->library('pdf');
        $this->pdf->AddPage();
        $this->pdf->writeHTML($html, true, false, false, false, '');
        $this->pdf->Output('AR_Ledger_' . str_replace('-', '', $date_from) . '_' . str_replace('-', '', $date_to) . '.pdf', 'I');
        exit;
    }

    /**
     * AR Aging Report - form (as of date)
     */
    function ar_aging() {
        $this->data['title'] = lang('ar_aging_title');
        $as_of = $this->input->post('as_of');
        if (empty($as_of)) {
            $as_of = $this->input->get('as_of') ?: date('Y-m-d');
        } else {
            $as_of = date('Y-m-d', strtotime($as_of));
        }
        $this->data['as_of'] = $as_of;
        $this->data['aging_data'] = $this->ar_model->get_ar_aging($as_of);
        $this->data['content'] = 'ar/ar_aging';
        $this->load->view('template', $this->data);
    }

    /**
     * Print AR Aging Report
     */
    function ar_aging_print() {
        $as_of = $this->input->get('as_of');
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        } else {
            $as_of = date('Y-m-d', strtotime($as_of));
        }
        $this->data['as_of'] = $as_of;
        $this->data['aging_data'] = $this->ar_model->get_ar_aging($as_of);
        $html = $this->load->view('ar/print/ar_aging_print', $this->data, true);
        $this->load->library('pdf');
        $this->pdf->AddPage();
        $this->pdf->writeHTML($html, true, false, false, false, '');
        $this->pdf->Output('AR_Aging_' . str_replace('-', '', $as_of) . '.pdf', 'I');
        exit;
    }

    /**
     * Export AR Aging to Excel (optional - CSV for simplicity)
     */
    function ar_aging_export() {
        $as_of = $this->input->get('as_of') ?: date('Y-m-d');
        $as_of = date('Y-m-d', strtotime($as_of));
        $aging_data = $this->ar_model->get_ar_aging($as_of);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=AR_Aging_' . str_replace('-', '', $as_of) . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('Aging Bucket', 'Customer', 'Customer #', 'Invoice #', 'Issue Date', 'Due Date', 'Days Overdue', 'Balance'));
        foreach ($aging_data as $bucket_key => $bucket) {
            foreach ($bucket['invoices'] as $inv) {
                fputcsv($out, array(
                    $bucket['label'],
                    $inv['customer_name'],
                    $inv['customer_number'],
                    $inv['invoice_id'],
                    $inv['issue_date'],
                    $inv['due_date'],
                    $inv['days_overdue'],
                    $inv['balance']
                ));
            }
        }
        fclose($out);
        exit;
    }
}
