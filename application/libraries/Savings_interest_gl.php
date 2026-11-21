<?php
/**
 * Savings interest void → general ledger policy.
 *
 * Interest credits the member (savings_transaction.trans_type stays CR because
 * credit() records every savings increase as CR) and the GL is:
 *   Dr interest expense / Cr savings liability.
 * A void must reverse those exact accounts. Tagging the void with the stored
 * trans_type (CR) makes the poster treat it as a cash withdrawal:
 *   Dr savings liability / Cr cash or adjustment equity
 * so the expense debit remains in the account ledger and the trial balance.
 *
 * Original GL lines stay. The void adds an offsetting entry. Reports sum every
 * line in the date window; they do not drop voided sources. A reversal dated
 * after the report "until" date correctly remains outside that window.
 *
 * No database access. Finance_model calls this and applies PIN there.
 */
class Savings_interest_gl {

    /**
     * True for an interest posting, including interest stored as trans_type CR.
     * A free-text comment that merely contains "interest" is not enough: that
     * row was a cash deposit and must be reversed against cash.
     * Void rows are not new interest postings.
     */
    public static function is_interest_source($trans_type, $system_comment) {
        if (self::is_void_source($system_comment, '')) {
            return false;
        }
        $type = strtoupper(trim((string) $trans_type));
        if (in_array($type, array('INT', 'IN', 'INTEREST', 'IR'), true)) {
            return true;
        }
        $sys = strtoupper(trim((string) $system_comment));
        return ($sys !== '' && strpos($sys, 'INTEREST') !== false);
    }

    public static function is_void_source($system_comment, $comment = '') {
        $sys = strtoupper(trim((string) $system_comment));
        if ($sys !== '' && strpos($sys, 'VOID TRANSACTION') === 0) {
            return true;
        }
        return (strpos((string) $comment, 'VOID-') === 0);
    }

    /**
     * ORIG_TYPE stored on the reversing savings row.
     * Interest stored as CR is tagged INT so a later post credits expense.
     */
    public static function void_origin_type($trans_type, $system_comment) {
        if (self::is_interest_source($trans_type, $system_comment)) {
            return 'INT';
        }
        $type = strtoupper(trim((string) $trans_type));
        return ($type !== '') ? $type : 'CR';
    }

    public static function build_void_system_comment($trans_type, $system_comment, $paymethod) {
        $method = trim((string) $paymethod);
        if ($method === '') {
            $method = 'N/A';
        }
        return 'VOID TRANSACTION | ORIG_TYPE:' . self::void_origin_type($trans_type, $system_comment) . ' | ORIG_METHOD:' . $method;
    }

    public static function void_comment_targets_interest($system_comment) {
        $sys = strtoupper(trim((string) $system_comment));
        return (strpos($sys, 'VOID TRANSACTION') !== false && strpos($sys, 'ORIG_TYPE:INT') !== false);
    }

    /**
     * Interest void, including a legacy void row tagged ORIG_TYPE:CR because
     * the source interest was stored as a savings credit.
     */
    public static function should_treat_as_interest_void($void_system_comment, $original_trans_type = null, $original_system_comment = null) {
        if (self::void_comment_targets_interest($void_system_comment)) {
            return true;
        }
        if (!self::is_void_source($void_system_comment, '')) {
            return false;
        }
        if ($original_trans_type === null && $original_system_comment === null) {
            return false;
        }
        return self::is_interest_source($original_trans_type, $original_system_comment);
    }

    /**
     * Receipt embedded in "VOID-{receipt} - {reason}".
     */
    public static function original_receipt_from_comment($comment) {
        if (preg_match('/VOID-([^ ]+)/', (string) $comment, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Which accounts a savings GL posting debits and credits.
     * void_interest always credits interest expense and debits the liability,
     * the opposite of the original interest journal.
     *
     * @return array kind, debit, credit
     */
    public static function posting_roles($trans_type, $system_comment) {
        $type = strtoupper(trim((string) $trans_type));
        $sys = strtoupper(trim((string) $system_comment));
        $is_void = (strpos($sys, 'VOID TRANSACTION') === 0);
        if ($is_void && strpos($sys, 'ORIG_TYPE:INT') !== false) {
            return array(
                'kind' => 'void_interest',
                'debit' => 'savings_liability',
                'credit' => 'interest_expense',
            );
        }
        if (!$is_void && (in_array($type, array('INT', 'IN', 'INTEREST', 'IR'), true) || strpos($sys, 'INTEREST') !== false)) {
            return array(
                'kind' => 'interest',
                'debit' => 'interest_expense',
                'credit' => 'savings_liability',
            );
        }
        if ($type === 'DR' || strpos($sys, 'NORMAL WITHDRAWAL') !== false) {
            return array(
                'kind' => $is_void ? 'void_withdrawal' : 'withdrawal',
                'debit' => 'savings_liability',
                'credit' => 'cash_or_adjustment',
            );
        }
        return array(
            'kind' => $is_void ? 'void_deposit' : 'deposit',
            'debit' => 'cash_or_adjustment',
            'credit' => 'savings_liability',
        );
    }

    public static function void_line_description($original_description, $void_receipt, $original_receipt) {
        $prefix = (strpos((string) $original_description, '[JV]') !== false) ? '[JV] ' : '';
        $text = $prefix . 'Savings Interest Void - reverses ' . $original_receipt . ' (Void receipt: ' . $void_receipt . ')';
        if (strlen($text) > 240) {
            $text = substr($text, 0, 240);
        }
        return $text;
    }

    /**
     * Swap debit and credit on the source lines. Same accounts, same journal.
     * Does not re-pick an interest account by name.
     *
     * @param array $original_lines objects or arrays
     * @return array GL rows without PIN or entryid
     */
    public static function mirror_lines($original_lines, $void_date, $void_receipt, $original_receipt) {
        $ts = strtotime((string) $void_date);
        $void_date = ($ts === false) ? '' : date('Y-m-d', $ts);
        $out = array();
        foreach ($original_lines as $line) {
            if (is_object($line)) {
                $line = get_object_vars($line);
            }
            if (!is_array($line)) {
                continue;
            }
            $account = isset($line['account']) ? (string) $line['account'] : '';
            $debit = isset($line['debit']) ? (float) $line['debit'] : 0.0;
            $credit = isset($line['credit']) ? (float) $line['credit'] : 0.0;
            if ($account === '' || $void_date === '' || (abs($debit) < 0.0000001 && abs($credit) < 0.0000001)) {
                continue;
            }
            $row = array(
                'journalID' => isset($line['journalID']) ? $line['journalID'] : 5,
                'refferenceID' => (string) $void_receipt,
                'date' => $void_date,
                'description' => self::void_line_description(
                    isset($line['description']) ? $line['description'] : '',
                    $void_receipt,
                    $original_receipt
                ),
                'linkto' => 'savings_transaction.receipt',
                'fromtable' => 'savings_transaction',
                'account' => $account,
                'debit' => round($credit, 2),
                'credit' => round($debit, 2),
            );
            if (array_key_exists('account_type', $line)) {
                $row['account_type'] = $line['account_type'];
            }
            if (array_key_exists('sub_account_type', $line)) {
                $row['sub_account_type'] = $line['sub_account_type'];
            }
            foreach (array('PID', 'member_id') as $fk) {
                if (array_key_exists($fk, $line) && $line[$fk] !== null) {
                    $row[$fk] = $line[$fk];
                }
            }
            foreach (array('customerid', 'supplierid', 'LID', 'invoiceid') as $fk) {
                if (isset($line[$fk]) && $line[$fk] !== '' && $line[$fk] !== null) {
                    $row[$fk] = $line[$fk];
                }
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Lines to insert for this void receipt.
     * Any GL already stored against the void receipt is left as-is, including a
     * legacy reversal that hit the wrong account. A second mirror would
     * double-reverse the liability and still leave the bad credit for repair.
     */
    public static function reversal_lines_to_post($original_lines, $existing_void_lines, $void_date, $void_receipt, $original_receipt) {
        if (!empty($existing_void_lines)) {
            return array();
        }
        return self::mirror_lines($original_lines, $void_date, $void_receipt, $original_receipt);
    }

    public static function lines_are_balanced($lines) {
        $debit = 0.0;
        $credit = 0.0;
        foreach ($lines as $line) {
            if (is_object($line)) {
                $line = get_object_vars($line);
            }
            $debit += isset($line['debit']) ? (float) $line['debit'] : 0.0;
            $credit += isset($line['credit']) ? (float) $line['credit'] : 0.0;
        }
        return abs(round($debit, 2) - round($credit, 2)) < 0.01;
    }

    /**
     * Account ledger totals. Same rules as report_model::get_account_ledger:
     * opening is strictly before $from, the period is inclusive, other PINs
     * and other accounts are ignored. Debit-normal accounts (expense) use
     * debit minus credit. Credit-normal accounts use credit minus debit.
     */
    public static function account_statement($lines, $account, $pin, $from, $until, $debit_normal = true) {
        $from = date('Y-m-d', strtotime($from));
        $until = date('Y-m-d', strtotime($until));
        $open_debit = 0.0;
        $open_credit = 0.0;
        $period_debit = 0.0;
        $period_credit = 0.0;
        $period_lines = array();
        foreach ($lines as $line) {
            if (is_object($line)) {
                $line = get_object_vars($line);
            }
            if (!is_array($line)) {
                continue;
            }
            if ((string) (isset($line['PIN']) ? $line['PIN'] : '') !== (string) $pin) {
                continue;
            }
            if ((string) (isset($line['account']) ? $line['account'] : '') !== (string) $account) {
                continue;
            }
            $date = substr((string) (isset($line['date']) ? $line['date'] : ''), 0, 10);
            $debit = isset($line['debit']) ? (float) $line['debit'] : 0.0;
            $credit = isset($line['credit']) ? (float) $line['credit'] : 0.0;
            if ($date !== '' && $date < $from) {
                $open_debit += $debit;
                $open_credit += $credit;
            } elseif ($date !== '' && $date >= $from && $date <= $until) {
                $period_debit += $debit;
                $period_credit += $credit;
                $period_lines[] = $line;
            }
        }
        if ($debit_normal) {
            $opening = $open_debit - $open_credit;
            $ending = $opening + $period_debit - $period_credit;
        } else {
            $opening = $open_credit - $open_debit;
            $ending = $opening + $period_credit - $period_debit;
        }
        return array(
            'opening_balance' => round($opening, 2),
            'opening_debit' => round($open_debit, 2),
            'opening_credit' => round($open_credit, 2),
            'period_debit' => round($period_debit, 2),
            'period_credit' => round($period_credit, 2),
            'ending_balance' => round($ending, 2),
            'lines' => $period_lines,
        );
    }

    /**
     * Trial-balance sides from report_model::trial_balance_ending_sides.
     * Net is always debit minus credit (opening + period), for every account type.
     * Positive net is the debit column. Negative net is the credit column.
     */
    public static function trial_balance_sides($opening_debit, $opening_credit, $period_debit, $period_credit) {
        $net = round(
            ((float) $opening_debit - (float) $opening_credit) + ((float) $period_debit - (float) $period_credit),
            2
        );
        $debit = 0.0;
        $credit = 0.0;
        if ($net > 0.005) {
            $debit = $net;
        } elseif ($net < -0.005) {
            $credit = abs($net);
        }
        return array('debit' => round($debit, 2), 'credit' => round($credit, 2), 'net' => $net);
    }

    /**
     * Ledger, trial balance, and the account-ledger Excel/PDF export all read
     * these same totals. Export does not apply a second void filter.
     */
    public static function report_totals($lines, $account, $pin, $from, $until, $debit_normal = true) {
        $stmt = self::account_statement($lines, $account, $pin, $from, $until, $debit_normal);
        $tb = self::trial_balance_sides(
            $stmt['opening_debit'],
            $stmt['opening_credit'],
            $stmt['period_debit'],
            $stmt['period_credit']
        );
        return array(
            'ledger' => $stmt,
            'trial_balance' => $tb,
            'export' => array(
                'opening_balance' => $stmt['opening_balance'],
                'period_debit' => $stmt['period_debit'],
                'period_credit' => $stmt['period_credit'],
                'ending_balance' => $stmt['ending_balance'],
                'line_count' => count($stmt['lines']),
            ),
        );
    }

    /**
     * Net debit-minus-credit of the source receipt plus its void receipt.
     * Other references (manual journals, other interest) are not included.
     *
     * status:
     *   no_gl | missing_reversal | wrong_or_partial_reversal | already_reversed
     * repair: journal lines that bring each touched account to a zero net
     * for this pair. Empty when already reversed. Not applied automatically.
     */
    public static function diagnose_interest_void($original_lines, $reversal_lines) {
        $nets = array();
        $add = function ($line) use (&$nets) {
            if (is_object($line)) {
                $line = get_object_vars($line);
            }
            if (!is_array($line)) {
                return;
            }
            $account = isset($line['account']) ? (string) $line['account'] : '';
            if ($account === '') {
                return;
            }
            if (!isset($nets[$account])) {
                $nets[$account] = 0.0;
            }
            $nets[$account] = round(
                $nets[$account] + (float) (isset($line['debit']) ? $line['debit'] : 0) - (float) (isset($line['credit']) ? $line['credit'] : 0),
                2
            );
        };
        foreach ($original_lines as $line) {
            $add($line);
        }
        foreach ($reversal_lines as $line) {
            $add($line);
        }

        if (empty($original_lines) && empty($reversal_lines)) {
            return array('status' => 'no_gl', 'repair' => array(), 'nets' => $nets);
        }

        $open = array();
        foreach ($nets as $account => $net) {
            if (abs($net) >= 0.01) {
                $open[$account] = $net;
            }
        }
        if (empty($open)) {
            return array('status' => 'already_reversed', 'repair' => array(), 'nets' => $nets);
        }

        $repair = array();
        foreach ($open as $account => $net) {
            if ($net > 0) {
                $repair[] = array('account' => $account, 'debit' => 0.0, 'credit' => round($net, 2));
            } else {
                $repair[] = array('account' => $account, 'debit' => round(abs($net), 2), 'credit' => 0.0);
            }
        }
        $status = empty($reversal_lines) ? 'missing_reversal' : 'wrong_or_partial_reversal';
        return array('status' => $status, 'repair' => $repair, 'nets' => $nets);
    }
}
