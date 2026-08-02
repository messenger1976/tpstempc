<?php

/**
 * CIC prep export model — maps Tapstemco member/loan data to CIC logical fields.
 */
class Cic_model extends CI_Model {

    /**
     * Fetch borrower/loan rows for CIC prep export.
     *
     * @param string $as_of_date YYYY-MM-DD cut-off (disbursed on or before)
     * @param bool   $include_closed Include closed loans (status = 5)
     * @return array List of associative arrays with CIC logical keys
     */
    function get_cic_prep_rows($as_of_date, $include_closed = false) {
        $pin = current_user()->PIN;
        $as_of_date = date('Y-m-d', strtotime($as_of_date));
        $as_of_esc = $this->db->escape($as_of_date);
        $pin_esc = $this->db->escape($pin);

        $status_clause = $include_closed
            ? 'AND lc.status IN (4, 5)'
            : 'AND lc.status = 4';

        // Principal repaid only (so outstanding_balance stays <= loan_amount / basic_amount)
        $sql = "SELECT
                    m.member_id AS borrower_id,
                    TRIM(CONCAT(
                        COALESCE(m.firstname, ''), ' ',
                        COALESCE(m.middlename, ''), ' ',
                        COALESCE(m.lastname, '')
                    )) AS name,
                    m.dob AS date_of_birth,
                    m.gender,
                    m.maritalstatus AS civil_status,
                    mc.phone1 AS contact_number,
                    COALESCE(NULLIF(TRIM(mc.sssno), ''), NULLIF(TRIM(mc.tinno), ''), '') AS id_number,
                    lc.LID,
                    lc.basic_amount AS loan_amount,
                    lcd.disbursedate AS loan_date,
                    lc.number_istallment,
                    lc.interval AS loan_interval,
                    lc.status AS loan_status,
                    COALESCE((
                        SELECT SUM(lcr.principle)
                        FROM loan_contract_repayment lcr
                        WHERE lcr.LID = lc.LID
                    ), 0) AS principle_paid,
                    (
                        SELECT MIN(lcrs.repaydate)
                        FROM loan_contract_repayment_schedule lcrs
                        WHERE lcrs.LID = lc.LID AND lcrs.status = 0
                    ) AS oldest_unpaid_due_date
                FROM loan_contract lc
                INNER JOIN loan_contract_disburse lcd ON lcd.LID = lc.LID
                INNER JOIN members m ON m.PID = lc.PID AND m.PIN = lc.PIN
                LEFT JOIN members_contact mc ON mc.PID = m.PID
                WHERE lc.PIN = {$pin_esc}
                    AND lc.disburse = 1
                    {$status_clause}
                    AND lcd.disbursedate <= {$as_of_esc}
                ORDER BY m.member_id, lc.LID";

        $result = $this->db->query($sql)->result();
        $rows = array();

        foreach ($result as $loan) {
            $loan_amount = floatval($loan->loan_amount);
            $principle_paid = floatval($loan->principle_paid);
            $outstanding = max(0, round($loan_amount - $principle_paid, 2));

            // Closed loans: report as paid with zero outstanding
            if ((int) $loan->loan_status === 5) {
                $outstanding = 0;
            }

            $rows[] = array(
                'borrower_id'         => (string) $loan->borrower_id,
                'name'                => preg_replace('/\s+/', ' ', trim($loan->name)),
                'date_of_birth'       => $this->format_ymd($loan->date_of_birth),
                'gender'              => (string) $loan->gender,
                'civil_status'        => (string) $loan->civil_status,
                'nationality'         => 'Filipino', // no nationality column in members
                'contact_number'      => (string) $loan->contact_number,
                'id_number'           => (string) $loan->id_number,
                'loan_amount'         => $loan_amount,
                'loan_date'           => $this->format_ymd($loan->loan_date),
                'loan_term_months'    => $this->term_to_months($loan->number_istallment, $loan->loan_interval),
                'outstanding_balance' => $outstanding,
                'payment_status'      => $this->derive_payment_status(
                    (int) $loan->loan_status,
                    $outstanding,
                    $loan->oldest_unpaid_due_date,
                    $as_of_date
                ),
            );
        }

        return $rows;
    }

    /**
     * Count rows that would be exported (for preview).
     *
     * @param string $as_of_date
     * @param bool   $include_closed
     * @return int
     */
    function count_cic_prep_rows($as_of_date, $include_closed = false) {
        return count($this->get_cic_prep_rows($as_of_date, $include_closed));
    }

    /**
     * @param mixed $date
     * @return string YYYY-MM-DD or empty
     */
    protected function format_ymd($date) {
        if ($date === null || $date === '' || $date === '0000-00-00') {
            return '';
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return '';
        }
        return date('Y-m-d', $ts);
    }

    /**
     * Convert installment count + interval to approximate months.
     * interval 1 = month, 2 = week (per loan product setup).
     *
     * @param mixed $installments
     * @param mixed $interval
     * @return int
     */
    protected function term_to_months($installments, $interval) {
        $n = (int) $installments;
        $interval = (int) $interval;
        if ($n < 1) {
            return 0;
        }
        if ($interval === 2) {
            return max(1, (int) round($n / 4.345));
        }
        return $n;
    }

    /**
     * @param int         $loan_status
     * @param float       $outstanding
     * @param string|null $oldest_unpaid_due
     * @param string      $as_of_date
     * @return string
     */
    protected function derive_payment_status($loan_status, $outstanding, $oldest_unpaid_due, $as_of_date) {
        if ($loan_status === 5 || $outstanding <= 0) {
            return 'Paid';
        }

        if (!empty($oldest_unpaid_due) && $oldest_unpaid_due !== '0000-00-00') {
            $due = strtotime($oldest_unpaid_due);
            $as_of = strtotime($as_of_date);
            if ($due !== false && $as_of !== false && $due < $as_of) {
                return 'Past Due';
            }
        }

        return 'Current';
    }
}
