<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cashiering_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->ensure_tables();
    }

    public function ensure_tables() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `cashiering_reports` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `report_date` DATE NOT NULL,
                `cashier_name` VARCHAR(150) NULL,
                `beginning_cash` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `cash_in` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `cash_out` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `expected_cash` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `cash_counted` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `other_items` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `grand_total` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `over_short` DECIMAL(18,2) NOT NULL DEFAULT 0,
                `notes` TEXT NULL,
                `cash_breakdown` TEXT NULL,
                `created_by` INT NULL,
                `PIN` VARCHAR(50) NULL,
                `created_at` DATETIME NULL,
                `approved_by` INT NULL,
                `approved_at` DATETIME NULL,
                `status` VARCHAR(30) NOT NULL DEFAULT 'submitted',
                PRIMARY KEY (`id`),
                INDEX `idx_cashiering_report_date` (`report_date`),
                INDEX `idx_cashiering_pin` (`PIN`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        $this->ensure_report_columns();
    }

    /**
     * Void bookkeeping lives in extra columns, and CREATE TABLE IF NOT EXISTS is a
     * no-op on a table that already exists, so they are added on demand - the same
     * approach cash_receipt_model::ensure_received_from_columns() uses.
     */
    public function ensure_report_columns() {
        if (!$this->db->table_exists('cashiering_reports')) {
            return;
        }
        $columns = array(
            'voided_by' => 'INT NULL DEFAULT NULL',
            'voided_at' => 'DATETIME NULL DEFAULT NULL',
            'void_reason' => 'VARCHAR(255) NULL DEFAULT NULL',
        );
        foreach ($columns as $column => $definition) {
            if (!$this->db->query("SHOW COLUMNS FROM cashiering_reports LIKE '" . $this->db->escape_str($column) . "'")->row()) {
                $this->db->query("ALTER TABLE cashiering_reports ADD COLUMN `$column` $definition");
            }
        }
    }

    /**
     * A voided sheet must never feed a total, the carry-forward or the report list.
     * NULL is treated as active so pre-existing rows stay visible.
     */
    private function _only_active() {
        $this->db->where("(status IS NULL OR status != 'void')", NULL, FALSE);
    }

    public function get_daily_summary($date) {
        $pin = current_user()->PIN;

        $receipt_row = $this->db
            ->select('COALESCE(SUM(total_amount), 0) AS total', false)
            ->where('PIN', $pin)
            ->where('receipt_date', $date)
            ->where('cancelled', 0)
            ->get('cash_receipts')
            ->row();

        $cash_in_receipts = floatval(isset($receipt_row->total) ? $receipt_row->total : 0);
        $cash_in_repayments = $this->_loan_repayments_cash_in($date, $pin);

        $disbursement_row = $this->db
            ->select('COALESCE(SUM(total_amount), 0) AS total', false)
            ->where('PIN', $pin)
            ->where('disburse_date', $date)
            ->where('cancelled', 0)
            ->get('cash_disbursements')
            ->row();

        $this->db->where('PIN', $pin);
        $this->db->where('report_date', $date);
        $this->_only_active();
        $report_row = $this->db
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('cashiering_reports')
            ->row();

        $cash_in = $cash_in_receipts + $cash_in_repayments;
        $cash_out = floatval(isset($disbursement_row->total) ? $disbursement_row->total : 0);
        $beginning_cash = floatval(isset($report_row->beginning_cash) ? $report_row->beginning_cash : 0);
        $carried_from = null;
        // Nothing submitted yet for this date: the opening drawer is whatever the previous
        // shift counted, so the cashier only overrides it when the count actually differs.
        if (empty($report_row)) {
            $previous = $this->get_previous_report($date);
            if (!empty($previous)) {
                $beginning_cash = floatval(!empty($previous->cash_counted) ? $previous->cash_counted : $previous->grand_total);
                $carried_from = $previous->report_date;
            }
        }
        $expected_cash = $beginning_cash + $cash_in - $cash_out;

        /*
         * The figure above is what the drawer should hold after the day's movements.
         * A submitted sheet records its own reconciled figure instead - the cashier
         * may override "CASH ON HAND PER BOOKS" - and the two can legitimately differ,
         * typically by an unfunded disbursement. Returning only the computed value let
         * the dashboard contradict a filed sheet without saying so, so both figures are
         * returned and the caller surfaces the gap.
         */
        $filed_expected_cash = !empty($report_row) && isset($report_row->expected_cash)
            ? floatval($report_row->expected_cash)
            : NULL;
        $unreconciled = ($filed_expected_cash === NULL) ? NULL : ($expected_cash - $filed_expected_cash);

        return array(
            'beginning_cash' => $beginning_cash,
            'beginning_cash_carried_from' => $carried_from,
            'cash_in' => $cash_in,
            /* Split so the figure can be traced back to where the cash came from. */
            'cash_in_receipts' => $cash_in_receipts,
            'cash_in_repayments' => $cash_in_repayments,
            'cash_out' => $cash_out,
            'net_cash' => $cash_in - $cash_out,
            'expected_cash' => $expected_cash,
            'filed_expected_cash' => $filed_expected_cash,
            'unreconciled' => $unreconciled,
            'last_report' => $report_row,
        );
    }

    /**
     * Cash a loan repayment put in the drawer on a given date.
     *
     * Loan repayments are recorded in the loan module, not the Cash Receipts module,
     * so they never reach cash_receipts. Summing that table alone left CASH IN at 0.00
     * on a day a member actually paid in - LN1325 paid P39,780.16 on 2026-09-23 while
     * the dashboard reported nothing. This is the missing half of cash in.
     *
     * The cash a row represents is principle + interest + penalt, NOT the stored
     * `amount`: that column is inconsistent in this table (it holds principle +
     * interest on some rows and principle + interest + penalt on others, 12 vs 25
     * rows out of 36), so `amount + penalt` would double count the penalty on the
     * rows that already include it. Summing the components matches the general ledger
     * to the centavo - P39,780.16 for LN1325's four rows.
     *
     * Two exclusions stop the figure over-counting:
     *   - a repayment already applied to a Cash Receipt is counted in that receipt
     *     instead (see cash_receipt_model::mark_loan_repayment_applied());
     *   - a voided repayment is not cash. The is_voided column only exists on some
     *     installs, so it is probed rather than assumed.
     */
    private function _loan_repayments_cash_in($date, $pin) {
        if (!$this->db->table_exists('loan_contract_repayment')) {
            return 0.0;
        }

        $where = array(
            'r.PIN = ' . $this->db->escape($pin),
            'r.paydate = ' . $this->db->escape($date),
        );

        if ($this->db->query("SHOW COLUMNS FROM loan_contract_repayment LIKE 'is_voided'")->row()) {
            $where[] = '(r.is_voided IS NULL OR r.is_voided = 0)';
        }

        /* Only exclude via the cash receipt link when that column is present - it is
           created on demand by cash_receipt_model::ensure_received_from_columns(). */
        $dedupe = '';
        if ($this->db->table_exists('cash_receipts')
            && $this->db->query("SHOW COLUMNS FROM cash_receipts LIKE 'loan_repayment_receipt'")->row()) {
            $dedupe = ' AND NOT EXISTS (SELECT 1 FROM cash_receipts cr'
                . ' WHERE cr.PIN = r.PIN AND cr.loan_repayment_receipt = r.receipt'
                . ' AND (cr.cancelled IS NULL OR cr.cancelled = 0))';
        }

        $row = $this->db->query(
            'SELECT COALESCE(SUM(r.principle + r.interest + r.penalt), 0) AS total'
            . ' FROM loan_contract_repayment r'
            . ' WHERE ' . implode(' AND ', $where) . $dedupe
        )->row();

        return floatval($row ? $row->total : 0);
    }

    /**
     * Most recent submitted report before the given date.
     * Supplies the closing cash that opens the next shift.
     */
    public function get_previous_report($date) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->db->where('report_date <', $date);
        $this->_only_active();
        return $this->db
            ->order_by('report_date', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('cashiering_reports')
            ->row();
    }

    /**
     * Normalise a cash_breakdown value (JSON string or array) into the current
     * shape:
     *
     *   bills[key] = ['bundles' => n, 'loose' => n, 'rolls' => 0]
     *   coins[key] = ['rolls' => n, 'loose' => n, 'bundles' => 0]
     *   other      = ['checks' => x, 'advances' => x, 'others' => x]
     *   meta       = ['fund_name' => '', 'accountable_person' => '',
     *                 'counted_by' => '', 'other_funds' => '']
     *
     * Reports written by the first version of the sheet stored a flat
     * key => quantity map; those quantities are read as loose pieces and mapped
     * onto the current keys through cashiering_denominations()['legacy_keys'].
     *
     * @param string|array $raw
     * @return array
     */
    public function normalize_breakdown($raw) {
        $denoms = cashiering_denominations();
        $legacy = isset($denoms['legacy_keys']) ? $denoms['legacy_keys'] : array();

        $decoded = is_string($raw) ? json_decode($raw, TRUE) : $raw;
        if (!is_array($decoded)) {
            $decoded = array();
        }

        $blank = array('bundles' => 0, 'loose' => 0, 'rolls' => 0);
        $out = array(
            'bills' => array(),
            'coins' => array(),
            'other' => array('checks' => 0, 'advances' => 0, 'others' => 0),
            'meta' => array(
                'fund_name' => '',
                'accountable_person' => '',
                'counted_by' => '',
                'other_funds' => '',
                'others_specify' => '',
            ),
        );
        foreach (array('bills', 'coins') as $section) {
            foreach ($denoms[$section] as $denom) {
                $out[$section][$denom['key']] = $blank;
            }
        }

        /* Current shape. */
        foreach (array('bills', 'coins') as $section) {
            if (empty($decoded[$section]) || !is_array($decoded[$section])) {
                continue;
            }
            foreach ($decoded[$section] as $key => $value) {
                if (!isset($out[$section][$key]) || !is_array($value)) {
                    continue;
                }
                foreach (array('bundles', 'loose', 'rolls') as $column) {
                    $out[$section][$key][$column] = isset($value[$column]) ? (float) $value[$column] : 0;
                }
            }
        }
        foreach (array('checks', 'advances', 'others') as $key) {
            if (isset($decoded['other'][$key])) {
                $out['other'][$key] = (float) $decoded['other'][$key];
            }
        }
        if (!empty($decoded['meta']) && is_array($decoded['meta'])) {
            foreach ($out['meta'] as $key => $unused) {
                if (isset($decoded['meta'][$key])) {
                    $out['meta'][$key] = (string) $decoded['meta'][$key];
                }
            }
        }

        /* Legacy flat shape: every quantity is a loose piece. */
        foreach ($decoded as $key => $value) {
            if (is_array($value) || in_array($key, array('bills', 'coins', 'other', 'meta'), TRUE)) {
                continue;
            }
            $mapped = isset($legacy[$key]) ? $legacy[$key] : $key;
            foreach (array('bills', 'coins') as $section) {
                if (isset($out[$section][$mapped])) {
                    $out[$section][$mapped]['loose'] = (float) $value;
                }
            }
        }

        return $out;
    }

    /**
     * Money value of a normalised breakdown. A bundle or roll is worth
     * 'pieces_per_unit' pieces at the denomination's value.
     *
     * @param array $breakdown normalised by normalize_breakdown()
     * @return array ['bundles', 'loose_bills', 'rolls', 'loose_coins',
     *                'currency_total', 'cash_items', 'grand_total']
     */
    public function breakdown_totals($breakdown) {
        $denoms = cashiering_denominations();
        $pieces = isset($denoms['pieces_per_unit']) ? (float) $denoms['pieces_per_unit'] : 100;

        $totals = array(
            'bundles' => 0,
            'loose_bills' => 0,
            'rolls' => 0,
            'loose_coins' => 0,
            'currency_total' => 0,
            'cash_items' => 0,
            'grand_total' => 0,
        );

        foreach ($denoms['bills'] as $denom) {
            $value = (float) $denom['value'];
            $row = isset($breakdown['bills'][$denom['key']]) ? $breakdown['bills'][$denom['key']] : array();
            $bundles = isset($row['bundles']) ? (float) $row['bundles'] : 0;
            $loose = isset($row['loose']) ? (float) $row['loose'] : 0;
            $totals['bundles'] += $bundles * $value * $pieces;
            $totals['loose_bills'] += $loose * $value;
        }
        foreach ($denoms['coins'] as $denom) {
            $value = (float) $denom['value'];
            $row = isset($breakdown['coins'][$denom['key']]) ? $breakdown['coins'][$denom['key']] : array();
            $rolls = isset($row['rolls']) ? (float) $row['rolls'] : 0;
            $loose = isset($row['loose']) ? (float) $row['loose'] : 0;
            $totals['rolls'] += $rolls * $value * $pieces;
            $totals['loose_coins'] += $loose * $value;
        }

        $totals['currency_total'] = $totals['bundles'] + $totals['loose_bills']
            + $totals['rolls'] + $totals['loose_coins'];

        $other = isset($breakdown['other']) ? $breakdown['other'] : array();
        foreach (array('checks', 'advances', 'others') as $key) {
            $totals['cash_items'] += isset($other[$key]) ? (float) $other[$key] : 0;
        }

        $totals['grand_total'] = $totals['currency_total'] + $totals['cash_items'];

        return $totals;
    }

    public function save_report($data) {
        $this->db->insert('cashiering_reports', $data);
        return $this->db->insert_id();
    }

    public function get_report($id) {
        $pin = current_user()->PIN;
        return $this->db
            ->where('id', (int) $id)
            ->where('PIN', $pin)
            ->get('cashiering_reports')
            ->row();
    }

    /**
     * Newest active sheet for a date.
     *
     * Deliberately excludes voided rows, so voiding today's sheet puts the cashier
     * back on a blank one. Use get_report() to open a specific sheet whatever its
     * status - a voided sheet must stay printable.
     */
    public function get_report_for_date($date) {
        $pin = current_user()->PIN;
        $this->db->where('report_date', $date);
        $this->db->where('PIN', $pin);
        $this->_only_active();
        return $this->db
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('cashiering_reports')
            ->row();
    }

    public function get_recent_reports($limit = 10) {
        $pin = current_user()->PIN;
        $this->db->where('PIN', $pin);
        $this->_only_active();
        return $this->db
            ->order_by('report_date', 'DESC')
            ->limit((int) $limit)
            ->get('cashiering_reports')
            ->result();
    }

    /**
     * Display name of a user, for the void audit line. Empty when unknown.
     */
    public function user_name($user_id) {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return '';
        }
        $row = $this->db->select('first_name, last_name')
            ->where('id', $user_id)
            ->get('users')
            ->row();
        return $row ? trim($row->first_name . ' ' . $row->last_name) : '';
    }

    /**
     * True when a report has been voided and must not feed any total.
     */
    public function is_void($report) {
        return !empty($report) && isset($report->status) && $report->status === 'void';
    }

    /**
     * Who may void a count sheet.
     *
     * An admin may void any sheet. A cashier may void only their own report of the
     * current day - once the day is over, correcting a sheet is an admin action.
     * A voided sheet cannot be voided again.
     */
    public function user_can_void_report($report) {
        if (empty($report) || $this->is_void($report)) {
            return FALSE;
        }

        $CI = &get_instance();
        if ($CI->ion_auth->is_admin()) {
            return TRUE;
        }

        if (!function_exists('can_access_cashiering') || !can_access_cashiering()) {
            return FALSE;
        }

        $mine = isset($report->created_by) && (int) $report->created_by === (int) current_user()->id;
        $same_day = isset($report->report_date) && $report->report_date === date('Y-m-d');

        return $mine && $same_day;
    }

    /**
     * Mark a sheet void, keeping the row.
     *
     * The count sheet is the evidence of what was in the drawer, so it is
     * cancelled rather than deleted - the same convention cash_receipts.cancelled
     * follows. The reason is mandatory at the controller.
     */
    public function void_report($id, $reason, $actor_id = NULL) {
        $this->ensure_report_columns();

        $id = (int) $id;
        if ($id <= 0) {
            return FALSE;
        }

        $this->db->where('id', $id);
        $this->db->where('PIN', current_user()->PIN);
        return $this->db->update('cashiering_reports', array(
            'status' => 'void',
            'voided_by' => $actor_id === NULL ? (int) current_user()->id : (int) $actor_id,
            'voided_at' => date('Y-m-d H:i:s'),
            'void_reason' => substr(trim((string) $reason), 0, 255),
        ));
    }
}
