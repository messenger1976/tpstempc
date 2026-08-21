<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of welcome
 *
 * @author miltone
 */
class Dashboard extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->data['dashboard'] = 1;
        $this->data['current_title'] = lang('page_home');
    }

    function index() {
        // Load Activity Log Model (use lowercase for model name)
        $this->load->model('activity_log_model');
        
        // Load Member Model
        $this->load->model('member_model');
        
        // Load Contribution Model
        $this->load->model('contribution_model');
        
        // Load Report Model for loan aging data
        $this->load->model('report_model');
        
        // Load Loan Model
        $this->load->model('loan_model');
        
        // Load Finance Model for savings calculations
        $this->load->model('finance_model');
        
        // Get recent system activities for dashboard
        $this->data['recent_activities'] = $this->activity_log_model->get_recent_activities(10);
        
        // Get total active members (status = 1 means active)
        $this->data['total_members'] = $this->member_model->count_member(null, 1, null);
        
        // Get total CBU balance from active members
        $this->data['total_contributions'] = $this->contribution_model->total_cbu_balance();
        
        // Get loan aging data for dashboard chart (as of today)
        $this->data['loan_aging_data'] = $this->report_model->loan_aging_report(date('Y-m-d'));
        
        // Calculate total outstanding loan balance
        $total_active_loans = 0;
        foreach ($this->data['loan_aging_data'] as $bucket) {
            $total_active_loans += $bucket['total_balance'];
        }
        $this->data['total_active_loans'] = $total_active_loans;
        
        // Get monthly collections for the last 6 months
        $this->data['monthly_collections'] = $this->get_monthly_collections(6);
        
        // Get current month collections
        $current_month = date('Y-m');
        $this->data['total_collections'] = isset($this->data['monthly_collections'][$current_month]) 
            ? $this->data['monthly_collections'][$current_month] 
            : 0;
        
        // Get last month collections
        $last_month = date('Y-m', strtotime('-1 month'));
        $this->data['collections_monthly'] = isset($this->data['monthly_collections'][$last_month]) 
            ? $this->data['monthly_collections'][$last_month] 
            : 0;
        
        // Get loan releases for current month
        $this->data['loan_releases'] = $this->get_monthly_loan_releases($current_month);
        
        // Calculate payment rate (percentage of loans paid on time)
        $this->data['payment_rate'] = $this->calculate_payment_rate();
        
        // Get active loans count
        $this->data['active_loans_count'] = $this->get_active_loans_count();
        
        // Get new members this month
        $this->data['new_members_month'] = $this->member_model->count_member(null, 1, date('Y-m-01'));
        
        // Calculate collection rate
        $this->data['collection_rate'] = $this->calculate_collection_rate();
        
        // Get total savings
        $this->data['total_savings'] = $this->get_total_savings();
        
        // Get total share capital
        $this->data['total_share_capital'] = $this->get_total_share_capital();
        
        // Get total mortuary
        $this->data['total_mortuary'] = $this->get_total_mortuary();
        
        // Calculate net assets
        $this->data['net_assets'] = $this->calculate_net_assets();

        // Member address locations for OpenStreetMap widget
        $this->data['member_map_locations'] = $this->member_model->get_member_map_locations();
        $this->data['member_map_stats'] = $this->member_model->get_member_map_stats();
        $this->data['office_map_location'] = $this->member_model->get_office_map_location();

        // Live loan application / release pipeline for dashboard widget
        $loan_pipeline = $this->get_loan_pipeline(8);
        $this->data['loan_pipeline_items'] = $loan_pipeline['items'];
        $this->data['loan_pipeline_summary'] = $loan_pipeline['summary'];
        
        $this->data['content'] = 'dashboard';
        $this->load->view('dashboard', $this->data);
    }

    /**
     * Build actionable loan pipeline rows for the dashboard.
     * Priority: awaiting evaluation → awaiting approval → ready to release.
     */
    private function get_loan_pipeline($limit = 8) {
        $limit = max(1, (int) $limit);
        $awaiting_eval = (int) $this->loan_model->count_loan_wait_evaluation();
        $awaiting_approval = (int) $this->loan_model->count_loan_wait_approval();
        $ready_release = (int) $this->loan_model->count_loan_wait_disburse();
        $pin = current_user()->PIN;
        $pending_cash_row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM loan_contract_disburse
             WHERE PIN = ?
               AND release_status = 'pending'",
            array($pin)
        )->row();
        $pending_cash = $pending_cash_row ? (int) $pending_cash_row->total : 0;

        $items = array();
        $seen = array();

        $push_rows = function ($rows, $stage, $pill, $label, $action_path) use (&$items, &$seen, $limit) {
            foreach ($rows as $row) {
                if (count($items) >= $limit) {
                    break;
                }
                $lid = isset($row->LID) ? (string) $row->LID : '';
                if ($lid === '' || isset($seen[$lid])) {
                    continue;
                }
                $seen[$lid] = true;
                $name = trim(implode(' ', array_filter(array(
                    isset($row->firstname) ? $row->firstname : '',
                    isset($row->middlename) ? $row->middlename : '',
                    isset($row->lastname) ? $row->lastname : '',
                ))));
                $items[] = array(
                    'LID' => $lid,
                    'member_id' => isset($row->member_id) ? $row->member_id : '',
                    'member_name' => $name !== '' ? $name : '—',
                    'product_name' => !empty($row->product_name) ? $row->product_name : (!empty($row->loan_product_name) ? $row->loan_product_name : ''),
                    'amount' => isset($row->basic_amount) ? floatval($row->basic_amount) : 0,
                    'applicationdate' => !empty($row->applicationdate) ? $row->applicationdate : null,
                    'stage' => $stage,
                    'pill' => $pill,
                    'status_label' => $label,
                    'action_url' => site_url(current_lang() . $action_path . $lid),
                );
            }
        };

        $push_rows(
            $this->loan_model->loan_wait_evaluation(null, $limit, 0),
            'evaluation',
            'pending',
            'Awaiting Evaluation',
            '/loan/loan_evaluation_action/'
        );
        $push_rows(
            $this->loan_model->loan_wait_approval(null, null, null, null, $limit, 0),
            'approval',
            'review',
            'Awaiting Approval',
            '/loan/loan_approval_action/'
        );
        $push_rows(
            $this->loan_model->loan_wait_disburse(null, null, null, null, $limit, 0),
            'release',
            'approved',
            'Ready to Release',
            '/loan/loan_disburse_entry/'
        );

        $amount_total = 0.0;
        foreach ($items as $item) {
            $amount_total += $item['amount'];
        }

        return array(
            'items' => $items,
            'summary' => array(
                'awaiting_evaluation' => $awaiting_eval,
                'awaiting_approval' => $awaiting_approval,
                'ready_to_release' => $ready_release,
                'pending_cash_release' => $pending_cash,
                'queue_total' => $awaiting_eval + $awaiting_approval + $ready_release,
                'listed_amount' => $amount_total,
            ),
        );
    }
    
    /**
     * Get monthly collections for the specified number of months
     */
    private function get_monthly_collections($months = 6) {
        $pin = current_user()->PIN;
        $collections = array();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $start_date = $date . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $sql = "SELECT COALESCE(SUM(amount), 0) as total_collection
                    FROM loan_contract_repayment
                    WHERE PIN = '$pin'
                    AND DATE(paydate) BETWEEN '$start_date' AND '$end_date'";
            
            $result = $this->db->query($sql)->row();
            $collections[$date] = floatval($result->total_collection);
        }
        
        return $collections;
    }
    
    /**
     * Get monthly loan releases
     */
    private function get_monthly_loan_releases($month) {
        $pin = current_user()->PIN;
        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $sql = "SELECT COUNT(*) as count
                FROM loan_contract lc
                INNER JOIN loan_contract_disburse lcd ON lcd.LID = lc.LID
                WHERE lc.PIN = '$pin'
                AND lc.status = 4
                AND lc.disburse = 1
                AND DATE(lcd.disbursedate) BETWEEN '$start_date' AND '$end_date'";
        
        $result = $this->db->query($sql)->row();
        return intval($result->count);
    }
    
    /**
     * Calculate payment rate (percentage of loans paid on time)
     */
    private function calculate_payment_rate() {
        $pin = current_user()->PIN;
        
        // Get total loans with payments due
        $sql = "SELECT COUNT(DISTINCT lcrs.LID) as total_loans_with_due,
                SUM(CASE WHEN lcrs.status = 1 AND lcrs.repaydate <= CURDATE() THEN 1 ELSE 0 END) as paid_on_time
                FROM loan_contract_repayment_schedule lcrs
                INNER JOIN loan_contract lc ON lc.LID = lcrs.LID
                WHERE lc.PIN = '$pin'
                AND lc.status = 4
                AND lc.disburse = 1
                AND lcrs.repaydate <= CURDATE()";
        
        $result = $this->db->query($sql)->row();
        
        if ($result->total_loans_with_due > 0) {
            return round(($result->paid_on_time / $result->total_loans_with_due) * 100, 1);
        }
        
        return 0;
    }
    
    /**
     * Get active loans count
     */
    private function get_active_loans_count() {
        $pin = current_user()->PIN;
        
        $sql = "SELECT COUNT(*) as count
                FROM loan_contract
                WHERE PIN = '$pin'
                AND status = 4
                AND disburse = 1";
        
        $result = $this->db->query($sql)->row();
        return intval($result->count);
    }
    
    /**
     * Calculate collection rate
     */
    private function calculate_collection_rate() {
        $pin = current_user()->PIN;
        $current_month = date('Y-m');
        $start_date = $current_month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        
        // Get total amount due this month
        $sql_due = "SELECT COALESCE(SUM(repayamount + balance), 0) as total_due
                    FROM loan_contract_repayment_schedule lcrs
                    INNER JOIN loan_contract lc ON lc.LID = lcrs.LID
                    WHERE lc.PIN = '$pin'
                    AND lc.status = 4
                    AND lc.disburse = 1
                    AND lcrs.repaydate BETWEEN '$start_date' AND '$end_date'";
        
        $due_result = $this->db->query($sql_due)->row();
        $total_due = floatval($due_result->total_due);
        
        // Get total collected this month
        $sql_collected = "SELECT COALESCE(SUM(amount), 0) as total_collected
                          FROM loan_contract_repayment
                          WHERE PIN = '$pin'
                          AND DATE(paydate) BETWEEN '$start_date' AND '$end_date'";
        
        $collected_result = $this->db->query($sql_collected)->row();
        $total_collected = floatval($collected_result->total_collected);
        
        if ($total_due > 0) {
            return round(($total_collected / $total_due) * 100, 1);
        }
        
        return 0;
    }
    
    /**
     * Get total savings
     */
    private function get_total_savings() {
        // Use the existing finance model method
        return $this->finance_model->get_total_savings_amount(null, null, null);
    }
    
    /**
     * Get total share capital (Paid-Up Share Capital).
     * Prefer the member share sub-ledger. When it has no rows (TAPSTEMCO share
     * balances were never migrated into members_share), fall back to GL 30130.
     * CBU beginning-balance postings (fromtable = contribution_settings) debit
     * 30130 by mistake and are excluded so they do not wipe out share capital.
     */
    private function get_total_share_capital() {
        $pin = current_user()->PIN;

        $sql = "SELECT COUNT(*) AS cnt, COALESCE(SUM(amount + remainbalance), 0) AS total
                FROM members_share
                WHERE PIN = ?";
        $result = $this->db->query($sql, array($pin))->row();
        if ($result && intval($result->cnt) > 0) {
            return floatval($result->total);
        }

        $sql = "SELECT COALESCE(SUM(credit) - SUM(debit), 0) AS total
                FROM general_ledger
                WHERE PIN = ?
                AND account = '30130'
                AND (fromtable IS NULL OR fromtable <> 'contribution_settings')";
        $result = $this->db->query($sql, array($pin))->row();
        return $result ? floatval($result->total) : 0;
    }
    
    /**
     * Get total mortuary
     */
    private function get_total_mortuary() {
        $pin = current_user()->PIN;
        
        $sql = "SELECT COALESCE(SUM(balance), 0) as total
                FROM members_mortuary
                WHERE PIN = '$pin'";
        
        $result = $this->db->query($sql)->row();
        return floatval($result->total);
    }
    
    /**
     * Calculate net assets
     */
    private function calculate_net_assets() {
        $total_savings = $this->get_total_savings();
        $total_share_capital = $this->get_total_share_capital();
        $total_contributions = $this->contribution_model->total_cbu_balance();
        $total_mortuary = $this->get_total_mortuary();
        
        // Calculate total active loans from aging data
        $loan_aging_data = $this->report_model->loan_aging_report(date('Y-m-d'));
        $total_active_loans = 0;
        foreach ($loan_aging_data as $bucket) {
            $total_active_loans += $bucket['total_balance'];
        }
        
        // Net assets = Assets (savings + share capital + contributions + mortuary) - Liabilities (loans)
        return ($total_savings + $total_share_capital + $total_contributions + $total_mortuary) - $total_active_loans;
    }

}

?>
