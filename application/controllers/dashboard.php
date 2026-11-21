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

        // As-of is session-scoped only: cleared on logout so next login starts at today.
        $as_of_get = trim((string) $this->input->get('as_of'));
        if ($as_of_get !== '') {
            $as_of = $this->_resolve_as_of_date($as_of_get);
            $this->session->set_userdata('dashboard_as_of', $as_of);
        } else {
            $as_of = $this->_resolve_as_of_date($this->session->userdata('dashboard_as_of'));
        }
        $as_of_month = date('Y-m', strtotime($as_of));
        $this->data['as_of'] = $as_of;
        $this->data['as_of_label'] = date('F d, Y', strtotime($as_of));
        $is_today = ($as_of === date('Y-m-d'));

        // Get recent system activities for dashboard
        $this->data['recent_activities'] = $this->activity_log_model->get_recent_activities(10);

        // Historical as-of: reuse session KPI cache when same date is re-applied/refreshed.
        $kpi = null;
        if (!$is_today) {
            $cached = $this->session->userdata('dashboard_kpi_cache');
            if (is_array($cached)
                && isset($cached['as_of'], $cached['pin'], $cached['data'])
                && $cached['as_of'] === $as_of
                && (string) $cached['pin'] === (string) current_user()->PIN
                && is_array($cached['data'])) {
                $kpi = $cached['data'];
            }
        }

        if ($kpi === null) {
            $loan_aging_data = $this->report_model->loan_aging_dashboard_summary($as_of);
            $total_active_loans = 0;
            if (!empty($loan_aging_data) && is_array($loan_aging_data)) {
                foreach ($loan_aging_data as $bucket) {
                    if (is_array($bucket) && isset($bucket['total_balance'])) {
                        $total_active_loans += $bucket['total_balance'];
                    }
                }
            }

            $monthly_collections = $this->get_monthly_collections(6, $as_of);
            $last_month = date('Y-m', strtotime($as_of_month . '-01 -1 month'));
            $total_savings = $this->get_total_savings($as_of);
            $total_mortuary = $this->get_total_mortuary($as_of);

            $kpi = array(
                'total_members' => $this->count_active_members_as_of($as_of),
                'total_contributions' => $this->get_total_cbu_as_of($as_of),
                'loan_aging_data' => $loan_aging_data,
                'total_active_loans' => $total_active_loans,
                'monthly_collections' => $monthly_collections,
                'total_collections' => isset($monthly_collections[$as_of_month]) ? $monthly_collections[$as_of_month] : 0,
                'collections_month_label' => date('F Y', strtotime($as_of)),
                'collections_monthly' => isset($monthly_collections[$last_month]) ? $monthly_collections[$last_month] : 0,
                'loan_releases' => $this->get_monthly_loan_releases($as_of_month),
                'payment_rate' => $this->calculate_payment_rate($as_of),
                'active_loans_count' => $this->get_active_loans_count($as_of),
                'new_members_month' => $this->count_new_members_in_month($as_of_month),
                'collection_rate' => $this->calculate_collection_rate($as_of),
                'total_savings' => $total_savings,
                'total_share_capital' => $this->get_total_share_capital($as_of),
                'total_mortuary' => $total_mortuary,
                'net_assets' => $this->calculate_net_assets($total_active_loans, $as_of, $total_savings, $total_mortuary),
            );

            if (!$is_today) {
                $this->session->set_userdata('dashboard_kpi_cache', array(
                    'as_of' => $as_of,
                    'pin' => current_user()->PIN,
                    'data' => $kpi,
                ));
            } else {
                $this->session->unset_userdata('dashboard_kpi_cache');
            }
        }

        foreach ($kpi as $key => $value) {
            $this->data[$key] = $value;
        }

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
     * Normalize dashboard as-of date from GET (YYYY-MM-DD or DD-MM-YYYY). Defaults to today.
     */
    private function _resolve_as_of_date($raw) {
        if ($raw === null || $raw === false) {
            return date('Y-m-d');
        }
        $raw = trim((string) $raw);
        if ($raw === '') {
            return date('Y-m-d');
        }
        // Bootstrap datepicker posts DD-MM-YYYY; also accept YYYY-MM-DD.
        if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $raw)) {
            $raw = format_date($raw, true);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            $ts = strtotime($raw);
            if ($ts !== false) {
                $resolved = date('Y-m-d', $ts);
                // Do not allow future as-of dates.
                $today = date('Y-m-d');
                return ($resolved > $today) ? $today : $resolved;
            }
        }
        return date('Y-m-d');
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
     * Whether loan_contract_repayment.is_voided exists (cached per request).
     */
    private function _repayment_has_voided_column() {
        static $has = null;
        if ($has === null) {
            $has = (bool) $this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row();
        }
        return $has;
    }

    /**
     * Get monthly collections for N months ending on the as-of month.
     * As-of is session-scoped; logout/login restores today.
     */
    private function get_monthly_collections($months = 6, $as_of = null) {
        $pin = current_user()->PIN;
        $collections = array();
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }
        $anchor = date('Y-m-01', strtotime($as_of));
        $void_sql = $this->_repayment_has_voided_column()
            ? " AND (is_voided IS NULL OR is_voided = 0)"
            : '';

        $range_start = date('Y-m-01', strtotime($anchor . ' -' . ($months - 1) . ' months'));
        for ($i = $months - 1; $i >= 0; $i--) {
            $collections[date('Y-m', strtotime($anchor . " -$i months"))] = 0;
        }

        $sql = "SELECT DATE_FORMAT(paydate, '%Y-%m') AS ym,
                       COALESCE(SUM(amount), 0) AS total_collection
                FROM loan_contract_repayment
                WHERE PIN = ?
                  AND paydate >= ?
                  AND paydate <= ?" . $void_sql . "
                GROUP BY DATE_FORMAT(paydate, '%Y-%m')";
        $rows = $this->db->query($sql, array($pin, $range_start . ' 00:00:00', $as_of . ' 23:59:59'))->result();
        foreach ($rows as $row) {
            if (isset($collections[$row->ym])) {
                $collections[$row->ym] = floatval($row->total_collection);
            }
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
                WHERE lc.PIN = ?
                AND lc.status IN (4, 5)
                AND lc.disburse = 1
                AND lcd.disbursedate >= ?
                AND lcd.disbursedate <= ?";
        
        $result = $this->db->query($sql, array($pin, $start_date . ' 00:00:00', $end_date . ' 23:59:59'))->row();
        return intval($result->count);
    }
    
    /**
     * Fixed Beginning Balance loans have no schedule to measure, so schedule-based
     * rates leave them out while TAPSTEMCO_BB_FIXED_BALANCE is on.
     */
    private function _exclude_bb_fixed_sql($lc_alias) {
        if (!defined('TAPSTEMCO_BB_FIXED_BALANCE') || !TAPSTEMCO_BB_FIXED_BALANCE) {
            return '';
        }
        return " AND ({$lc_alias}.evaluated IS NULL OR {$lc_alias}.evaluated <> 'BEGINNING_BALANCE')";
    }

    /**
     * Calculate payment rate (percentage of loans paid on time as of date)
     */
    private function calculate_payment_rate($as_of = null) {
        $pin = current_user()->PIN;
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }
        
        $sql = "SELECT COUNT(DISTINCT lcrs.LID) as total_loans_with_due,
                SUM(CASE WHEN lcrs.status = 1 AND lcrs.repaydate <= ? THEN 1 ELSE 0 END) as paid_on_time
                FROM loan_contract_repayment_schedule lcrs
                INNER JOIN loan_contract lc ON lc.LID = lcrs.LID
                WHERE lc.PIN = ?
                AND lc.status IN (4, 5)
                AND lc.disburse = 1
                AND lcrs.repaydate <= ?" . $this->_exclude_bb_fixed_sql('lc');
        
        $result = $this->db->query($sql, array($as_of, $pin, $as_of))->row();
        
        if ($result && $result->total_loans_with_due > 0) {
            return round(($result->paid_on_time / $result->total_loans_with_due) * 100, 1);
        }
        
        return 0;
    }
    
    /**
     * Active/disbursed loans count as of date.
     */
    private function get_active_loans_count($as_of = null) {
        $pin = current_user()->PIN;
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }

        // Fast path for today (same as original dashboard).
        if ($as_of === date('Y-m-d')) {
            $sql = "SELECT COUNT(*) as count
                    FROM loan_contract
                    WHERE PIN = ?
                    AND status = 4
                    AND disburse = 1";
            $result = $this->db->query($sql, array($pin))->row();
            return $result ? intval($result->count) : 0;
        }
        
        $sql = "SELECT COUNT(DISTINCT lc.LID) as count
                FROM loan_contract lc
                INNER JOIN loan_contract_disburse lcd ON lcd.LID = lc.LID AND lcd.PIN = lc.PIN
                WHERE lc.PIN = ?
                AND lc.disburse = 1
                AND lc.status = 4
                AND lcd.disbursedate <= ?";
        
        $result = $this->db->query($sql, array($pin, $as_of . ' 23:59:59'))->row();
        return $result ? intval($result->count) : 0;
    }
    
    /**
     * Collection rate for the month of as_of (capped at as_of day).
     */
    private function calculate_collection_rate($as_of = null) {
        $pin = current_user()->PIN;
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }
        $start_date = date('Y-m-01', strtotime($as_of));
        $end_date = $as_of;
        
        $sql_due = "SELECT COALESCE(SUM(repayamount + balance), 0) as total_due
                    FROM loan_contract_repayment_schedule lcrs
                    INNER JOIN loan_contract lc ON lc.LID = lcrs.LID
                    WHERE lc.PIN = ?
                    AND lc.status IN (4, 5)
                    AND lc.disburse = 1
                    AND lcrs.repaydate BETWEEN ? AND ?" . $this->_exclude_bb_fixed_sql('lc');
        
        $due_result = $this->db->query($sql_due, array($pin, $start_date, $end_date))->row();
        $total_due = floatval($due_result->total_due);
        
        $sql_collected = "SELECT COALESCE(SUM(amount), 0) as total_collected
                          FROM loan_contract_repayment
                          WHERE PIN = ?
                          AND paydate >= ?
                          AND paydate <= ?";
        if ($this->_repayment_has_voided_column()) {
            $sql_collected .= " AND (is_voided IS NULL OR is_voided = 0)";
        }
        
        $collected_result = $this->db->query($sql_collected, array($pin, $start_date . ' 00:00:00', $end_date . ' 23:59:59'))->row();
        $total_collected = $collected_result ? floatval($collected_result->total_collected) : 0;
        
        if ($total_due > 0) {
            return round(($total_collected / $total_due) * 100, 1);
        }
        
        return 0;
    }
    
    /**
     * Get total savings as of date.
     * Today uses members_account.balance; historical uses one aggregate (not per-account).
     */
    private function get_total_savings($as_of = null) {
        if (empty($as_of) || $as_of === date('Y-m-d')) {
            return $this->finance_model->get_total_savings_amount(null, null, null);
        }
        return $this->finance_model->get_total_savings_amount_as_of_fast($as_of);
    }

    /**
     * Active members joined on/before as_of.
     */
    private function count_active_members_as_of($as_of) {
        $pin = current_user()->PIN;
        $sql = "SELECT COUNT(*) AS cnt
                FROM members
                WHERE PIN = ?
                  AND status = 1
                  AND (joiningdate IS NULL OR joiningdate = '0000-00-00' OR joiningdate = '0000-00-00 00:00:00' OR DATE(joiningdate) <= ?)";
        $result = $this->db->query($sql, array($pin, $as_of))->row();
        return $result ? intval($result->cnt) : 0;
    }

    /**
     * CBU balance as of date from contribution transactions (CR − DR).
     * Falls back to current ledger balance when as_of is today.
     */
    private function get_total_cbu_as_of($as_of) {
        $pin = current_user()->PIN;
        if ($as_of === date('Y-m-d')) {
            return floatval($this->contribution_model->total_cbu_balance());
        }
        $sql = "SELECT COALESCE(SUM(CASE WHEN trans_type = 'CR' THEN amount ELSE -amount END), 0) AS total
                FROM contribution_transaction
                WHERE PIN = ?
                  AND createdon <= ?";
        $result = $this->db->query($sql, array($pin, $as_of . ' 23:59:59'))->row();
        return $result ? floatval($result->total) : 0;
    }
    
    /**
     * Get total share capital as of date.
     * Today: members_share (or GL 30130 fallback). Historical: share_transaction or GL through as_of.
     */
    private function get_total_share_capital($as_of = null) {
        $pin = current_user()->PIN;
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }

        if ($as_of === date('Y-m-d')) {
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
                    AND account = '30130'";
            $result = $this->db->query($sql, array($pin))->row();
            return $result ? floatval($result->total) : 0;
        }

        if ($this->db->table_exists('share_transaction')) {
            $sql = "SELECT COALESCE(SUM(CASE WHEN trans_type = 'CR' THEN amount ELSE -amount END), 0) AS total
                    FROM share_transaction
                    WHERE PIN = ?
                      AND createdon <= ?";
            $result = $this->db->query($sql, array($pin, $as_of . ' 23:59:59'))->row();
            if ($result && abs(floatval($result->total)) > 0.009) {
                return floatval($result->total);
            }
        }

        $sql = "SELECT COALESCE(SUM(credit) - SUM(debit), 0) AS total
                FROM general_ledger
                WHERE PIN = ?
                AND account = '30130'
                AND `date` <= ?";
        $result = $this->db->query($sql, array($pin, $as_of . ' 23:59:59'))->row();
        return $result ? floatval($result->total) : 0;
    }
    
    /**
     * Mortuary fund as of date.
     */
    private function get_total_mortuary($as_of = null) {
        $pin = current_user()->PIN;
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }

        if ($as_of === date('Y-m-d')) {
            $sql = "SELECT COALESCE(SUM(balance), 0) as total
                    FROM members_mortuary
                    WHERE PIN = ?";
            $result = $this->db->query($sql, array($pin))->row();
            return $result ? floatval($result->total) : 0;
        }

        if ($this->db->table_exists('mortuary_transaction')) {
            $sql = "SELECT COALESCE(SUM(CASE WHEN trans_type = 'CR' THEN amount ELSE -amount END), 0) AS total
                    FROM mortuary_transaction
                    WHERE PIN = ?
                      AND COALESCE(trans_date, createdon) <= ?";
            $result = $this->db->query($sql, array($pin, $as_of . ' 23:59:59'))->row();
            return $result ? floatval($result->total) : 0;
        }

        return 0;
    }
    
    /**
     * Cash & cash equivalents from GL leaf cash accounts (11110-11199) as of date.
     */
    private function get_total_cash($as_of = null) {
        $pin = current_user()->PIN;
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }
        if ($as_of === date('Y-m-d')) {
            $sql = "SELECT COALESCE(SUM(debit) - SUM(credit), 0) AS total
                    FROM general_ledger
                    WHERE PIN = ?
                      AND account >= 11110
                      AND account < 11200";
            $result = $this->db->query($sql, array($pin))->row();
            return $result ? floatval($result->total) : 0;
        }
        $sql = "SELECT COALESCE(SUM(debit) - SUM(credit), 0) AS total
                FROM general_ledger
                WHERE PIN = ?
                  AND account >= 11110
                  AND account < 11200
                  AND `date` <= ?";
        $result = $this->db->query($sql, array($pin, $as_of . ' 23:59:59'))->row();
        return $result ? floatval($result->total) : 0;
    }

    /**
     * New active members whose joiningdate falls in the given YYYY-MM month.
     */
    private function count_new_members_in_month($month) {
        $pin = current_user()->PIN;
        $start = $month . '-01 00:00:00';
        $end = date('Y-m-t 23:59:59', strtotime($start));
        $sql = "SELECT COUNT(*) AS cnt
                FROM members
                WHERE PIN = ?
                  AND status = 1
                  AND joiningdate >= ?
                  AND joiningdate <= ?";
        $result = $this->db->query($sql, array($pin, $start, $end))->row();
        return $result ? intval($result->cnt) : 0;
    }

    /**
     * Cooperative working net assets as of date.
     * Pass already-computed savings/mortuary to avoid repeating heavy queries.
     */
    private function calculate_net_assets($total_active_loans = null, $as_of = null, $total_savings = null, $total_mortuary = null) {
        if (empty($as_of)) {
            $as_of = date('Y-m-d');
        }
        if ($total_active_loans === null) {
            $loan_aging_data = $this->report_model->loan_aging_dashboard_summary($as_of);
            $total_active_loans = 0;
            foreach ($loan_aging_data as $bucket) {
                $total_active_loans += $bucket['total_balance'];
            }
        }

        $total_cash = $this->get_total_cash($as_of);
        if ($total_savings === null) {
            $total_savings = $this->get_total_savings($as_of);
        }
        if ($total_mortuary === null) {
            $total_mortuary = $this->get_total_mortuary($as_of);
        }

        $assets = floatval($total_active_loans) + floatval($total_cash);
        $liabilities = floatval($total_savings) + floatval($total_mortuary);

        return $assets - $liabilities;
    }

}

?>
