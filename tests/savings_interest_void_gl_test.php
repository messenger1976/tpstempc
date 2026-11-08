<?php
/**
 * Savings interest void: ledger and trial-balance net effect.
 * No database and no network.
 *
 *   php tests/savings_interest_void_gl_test.php
 *
 * Screenshot repro (synthetic member, real amounts and chart account):
 *   Account 71100 interest expense, range 2021-11-01 through 2024-12-31.
 *   Voided savings interest 64.61 dated 2022-02-28, receipt LO43QPOD0001,
 *   plus active interest 36.24 and 20.23. Period debit 121.08, credit 0.00.
 *   That 121.08 is the bug: the void did not credit 71100.
 *
 * Policy under these fixtures:
 *   Active interest stays a debit. A void keeps the original debit and adds
 *   an equal credit on the same accounts, dated the void date. Inside the
 *   report window the net of the voided 64.61 is zero and the ending debit
 *   is 56.47 (36.24 + 20.23). A void dated after the window stays outside it.
 *   Manual journals and other tenants are not removed. Repeating a void or
 *   repairing an already reversed pair adds nothing.
 */
require dirname(__DIR__) . '/application/libraries/Savings_interest_gl.php';

$failures = 0;
function check($label, $condition) {
    global $failures;
    if ($condition) {
        echo "ok  $label\n";
        return;
    }
    $failures++;
    echo "FAIL $label\n";
}

function money_is($label, $actual, $expected) {
    check($label . ' (got ' . number_format((float) $actual, 2) . ', expected ' . number_format((float) $expected, 2) . ')', abs((float) $actual - (float) $expected) < 0.001);
}

/* Receipt transcribed from the ledger ref and the search-transaction receipt.
 * 12 characters from alphaID. Letter O in the 2nd and 7th positions, digit 0
 * in the last three numeric places: L O 4 3 Q P O D 0 0 0 1. */
const SCREENSHOT_RECEIPT = 'LO43QPOD0001';
const PIN = '4242';
const OTHER_PIN = '7777';
const EXPENSE = '71100';
const LIABILITY = '21000';
const ADJUSTMENT = '32000';
const FROM = '2021-11-01';
const UNTIL = '2024-12-31';
const ACTIVE_A = 36.24;
const ACTIVE_B = 20.23;
const VOIDED_AMT = 64.61;

function gl($pin, $account, $date, $debit, $credit, $ref, $fromtable, $desc, $journal = 5) {
    return array(
        'PIN' => $pin,
        'account' => $account,
        'date' => $date,
        'debit' => $debit,
        'credit' => $credit,
        'refferenceID' => $ref,
        'fromtable' => $fromtable,
        'description' => $desc,
        'journalID' => $journal,
        'account_type' => ($account === EXPENSE ? 70000 : ($account === LIABILITY ? 20000 : 30000)),
        'PID' => 'PID-FIX-641',
        'member_id' => 'MBR-FIX-641',
    );
}

function interest_pair($pin, $date, $amount, $ref, $desc) {
    return array(
        gl($pin, EXPENSE, $date, $amount, 0, $ref, 'savings_transaction', $desc, 5),
        gl($pin, LIABILITY, $date, 0, $amount, $ref, 'savings_transaction', $desc, 5),
    );
}

function with_pin($lines, $pin) {
    $out = array();
    foreach ($lines as $line) {
        $line['PIN'] = $pin;
        $out[] = $line;
    }
    return $out;
}

function refs_of($lines) {
    $refs = array();
    foreach ($lines as $line) {
        $refs[] = $line['refferenceID'];
    }
    return $refs;
}

check('screenshot receipt is 12 characters', strlen(SCREENSHOT_RECEIPT) === 12);
check('screenshot receipt keeps letter O and trailing zeros', SCREENSHOT_RECEIPT === 'LO43QPOD0001');
check(
    'void comment parses the screenshot receipt',
    Savings_interest_gl::original_receipt_from_comment('VOID-' . SCREENSHOT_RECEIPT . ' - posted to the wrong account') === SCREENSHOT_RECEIPT
);

/* Interest is stored as CR. The void tag must still say INT. */
check('CR + INTEREST system comment is interest', Savings_interest_gl::is_interest_source('CR', 'INTEREST'));
check('INT trans type is interest', Savings_interest_gl::is_interest_source('INT', 'INTEREST'));
check(
    'deposit comment that merely says interest is not an interest GL source',
    Savings_interest_gl::is_interest_source('CR', 'NORMAL DEPOSIT') === false
);
check(
    'void row is not a new interest posting',
    Savings_interest_gl::is_interest_source('DR', 'VOID TRANSACTION | ORIG_TYPE:INT | ORIG_METHOD:ADJUSTMENT') === false
);
check(
    'interest void origin type is INT even when stored as CR',
    Savings_interest_gl::void_origin_type('CR', 'INTEREST') === 'INT'
);
$void_comment = Savings_interest_gl::build_void_system_comment('CR', 'INTEREST', 'ADJUSTMENT');
check(
    'interest void system comment tags ORIG_TYPE:INT and keeps ADJUSTMENT',
    $void_comment === 'VOID TRANSACTION | ORIG_TYPE:INT | ORIG_METHOD:ADJUSTMENT'
);
check(
    'deposit void stays ORIG_TYPE:CR',
    Savings_interest_gl::build_void_system_comment('CR', 'NORMAL DEPOSIT', 'CASH') === 'VOID TRANSACTION | ORIG_TYPE:CR | ORIG_METHOD:CASH'
);
check(
    'withdrawal void stays ORIG_TYPE:DR',
    Savings_interest_gl::void_origin_type('DR', 'NORMAL WITHDRAWAL') === 'DR'
);

$interest_roles = Savings_interest_gl::posting_roles('CR', 'INTEREST');
check('active interest debits expense', $interest_roles['debit'] === 'interest_expense' && $interest_roles['credit'] === 'savings_liability');
$fixed_void_roles = Savings_interest_gl::posting_roles('DR', $void_comment);
check(
    'fixed interest void credits expense and debits liability',
    $fixed_void_roles['kind'] === 'void_interest'
    && $fixed_void_roles['debit'] === 'savings_liability'
    && $fixed_void_roles['credit'] === 'interest_expense'
);
$legacy_void_comment = 'VOID TRANSACTION | ORIG_TYPE:CR | ORIG_METHOD:ADJUSTMENT';
$legacy_roles = Savings_interest_gl::posting_roles('DR', $legacy_void_comment);
check(
    'legacy CR tag would credit cash or adjustment, which is the bug',
    $legacy_roles['kind'] === 'void_withdrawal' && $legacy_roles['credit'] === 'cash_or_adjustment'
);
check(
    'legacy CR tag is still an interest void once the source row is known',
    Savings_interest_gl::should_treat_as_interest_void($legacy_void_comment, 'CR', 'INTEREST')
);
check(
    'legacy CR tag on a real deposit is not an interest void',
    Savings_interest_gl::should_treat_as_interest_void($legacy_void_comment, 'CR', 'NORMAL DEPOSIT') === false
);

$source_desc = '[JV] Savings Interest - FIXTURE MEMBER (Account: 900024, Receipt: ' . SCREENSHOT_RECEIPT . ')';
$source_lines = interest_pair(PIN, '2022-02-28', VOIDED_AMT, SCREENSHOT_RECEIPT, $source_desc);
$active_lines = array_merge(
    interest_pair(PIN, '2024-12-31', ACTIVE_A, 'FX36P2400001', '[JV] Savings Interest - FIXTURE ACTIVE A'),
    interest_pair(PIN, '2024-12-31', ACTIVE_B, 'FX20P2300001', '[JV] Savings Interest - FIXTURE ACTIVE B')
);
$manual = gl(PIN, EXPENSE, '2024-06-15', 50.00, 0, 'FXMANUAL5000', 'general_journal', 'Manual accrual fixture');
$other_tenant = gl(OTHER_PIN, EXPENSE, '2024-06-15', 1000.00, 0, 'FXOTHERPIN01', 'general_journal', 'Other cooperative');

/* --- Before: active lines only, matching the screenshot totals --- */
$before_books = array_merge($source_lines, $active_lines);
$before = Savings_interest_gl::report_totals($before_books, EXPENSE, PIN, FROM, UNTIL, true);
money_is('before ledger opening', $before['ledger']['opening_balance'], 0.00);
money_is('before ledger period debit', $before['ledger']['period_debit'], 121.08);
money_is('before ledger period credit', $before['ledger']['period_credit'], 0.00);
money_is('before ledger ending', $before['ledger']['ending_balance'], 121.08);
money_is('before trial balance debit', $before['trial_balance']['debit'], 121.08);
money_is('before trial balance credit', $before['trial_balance']['credit'], 0.00);
money_is('before export ending matches ledger', $before['export']['ending_balance'], $before['ledger']['ending_balance']);
check('before export line count is the three expense debits', $before['export']['line_count'] === 3);

/* Legacy wrong reversal: Dr liability, Cr adjustment. Expense untouched. */
$legacy_void_lines = array(
    gl(PIN, LIABILITY, '2026-03-01', VOIDED_AMT, 0, 'SVFIX00001', 'savings_transaction', 'Legacy void treated as withdrawal'),
    gl(PIN, ADJUSTMENT, '2026-03-01', 0, VOIDED_AMT, 'SVFIX00001', 'savings_transaction', 'Legacy void treated as withdrawal'),
);
$legacy_books = array_merge($before_books, $legacy_void_lines, array($manual), array($other_tenant));
$legacy_in_screenshot_window = Savings_interest_gl::report_totals($legacy_books, EXPENSE, PIN, FROM, UNTIL, true);
money_is('legacy wrong reversal does not credit 71100, so the screenshot window keeps 121.08 interest plus the manual 50', $legacy_in_screenshot_window['ledger']['ending_balance'], 171.08);
money_is('legacy wrong reversal adds no credit on 71100 inside the screenshot window', $legacy_in_screenshot_window['ledger']['period_credit'], 0.00);
$legacy_through_today = Savings_interest_gl::report_totals($legacy_books, EXPENSE, PIN, FROM, '2026-12-31', true);
money_is('legacy wrong reversal still leaves 71100 debit through the void date', $legacy_through_today['ledger']['period_debit'] - $legacy_through_today['ledger']['period_credit'], 171.08);
/* 121.08 interest + 50 manual. The 64.61 was never credited. */
money_is('legacy 71100 ending includes the voided 64.61 and the manual 50', $legacy_through_today['ledger']['ending_balance'], 171.08);
$legacy_diag = Savings_interest_gl::diagnose_interest_void($source_lines, $legacy_void_lines);
check('legacy reversal is diagnosed as the wrong account', $legacy_diag['status'] === 'wrong_or_partial_reversal');
$legacy_repair_accounts = array();
foreach ($legacy_diag['repair'] as $repair_line) {
    $legacy_repair_accounts[$repair_line['account']] = $repair_line;
}
check('legacy repair credits 71100 and does not touch the liability', isset($legacy_repair_accounts[EXPENSE]) && !isset($legacy_repair_accounts[LIABILITY]));
money_is('legacy repair credits the unreversed 64.61', $legacy_repair_accounts[EXPENSE]['credit'], VOIDED_AMT);
money_is('legacy repair debits the account the void credited by mistake', $legacy_repair_accounts[ADJUSTMENT]['debit'], VOIDED_AMT);
check('legacy repair journal balances', Savings_interest_gl::lines_are_balanced($legacy_diag['repair']));

$repaired_books = array_merge($legacy_books, array(
    gl(PIN, EXPENSE, '2026-03-01', 0, VOIDED_AMT, 'FXREPAIR0001', 'general_journal', 'Audited repair credit'),
    gl(PIN, ADJUSTMENT, '2026-03-01', VOIDED_AMT, 0, 'FXREPAIR0001', 'general_journal', 'Audited repair debit'),
));
$after_repair = Savings_interest_gl::report_totals($repaired_books, EXPENSE, PIN, FROM, '2026-12-31', true);
money_is('after audited repair, 71100 ending is active interest plus the manual entry', $after_repair['ledger']['ending_balance'], 106.47);
$pair_after_repair = Savings_interest_gl::diagnose_interest_void(
    $source_lines,
    array_merge($legacy_void_lines, array(
        gl(PIN, EXPENSE, '2026-03-01', 0, VOIDED_AMT, 'FXREPAIR0001', 'general_journal', 'Audited repair credit'),
        gl(PIN, ADJUSTMENT, '2026-03-01', VOIDED_AMT, 0, 'FXREPAIR0001', 'general_journal', 'Audited repair debit'),
    ))
);
check('repair is not proposed again once the pair nets to zero', $pair_after_repair['status'] === 'already_reversed' && $pair_after_repair['repair'] === array());

/* --- After: mirror the source lines onto the void receipt --- */
$void_receipt = 'SVFIX00002';
$posted = Savings_interest_gl::reversal_lines_to_post($source_lines, array(), '2022-02-28', $void_receipt, SCREENSHOT_RECEIPT);
check('first void posts two reversing lines', count($posted) === 2);
check('reversal balances', Savings_interest_gl::lines_are_balanced($posted));
check('reversal keeps journal 5', (int) $posted[0]['journalID'] === 5 && (int) $posted[1]['journalID'] === 5);
check('reversal reference is the void receipt, not the source', $posted[0]['refferenceID'] === $void_receipt && $posted[1]['refferenceID'] === $void_receipt);
check('reversal fromtable stays savings_transaction', $posted[0]['fromtable'] === 'savings_transaction');
$expense_credit = null;
$liability_debit = null;
foreach ($posted as $line) {
    if ($line['account'] === EXPENSE) {
        $expense_credit = $line;
    }
    if ($line['account'] === LIABILITY) {
        $liability_debit = $line;
    }
}
check('reversal credits the same expense account', $expense_credit !== null && (float) $expense_credit['credit'] === VOIDED_AMT && (float) $expense_credit['debit'] === 0.0);
check('reversal debits the same liability account', $liability_debit !== null && (float) $liability_debit['debit'] === VOIDED_AMT);
check('reversal description keeps the JV marker and names both receipts', strpos($expense_credit['description'], '[JV] Savings Interest Void') === 0 && strpos($expense_credit['description'], SCREENSHOT_RECEIPT) !== false && strpos($expense_credit['description'], $void_receipt) !== false);
check('member link is copied onto the reversal', $expense_credit['member_id'] === 'MBR-FIX-641');

$repeated = Savings_interest_gl::reversal_lines_to_post($source_lines, $posted, '2022-02-28', $void_receipt, SCREENSHOT_RECEIPT);
check('repeating the void posts nothing', $repeated === array());
$legacy_repost = Savings_interest_gl::reversal_lines_to_post($source_lines, $legacy_void_lines, '2026-03-01', 'SVFIX00001', SCREENSHOT_RECEIPT);
check('repost does not add a second reversal on top of a legacy wrong posting', $legacy_repost === array());

$correct_books = array_merge($before_books, with_pin($posted, PIN), array($manual), array($other_tenant));
$after = Savings_interest_gl::report_totals($correct_books, EXPENSE, PIN, FROM, UNTIL, true);
money_is('after ledger opening', $after['ledger']['opening_balance'], 0.00);
money_is('after ledger period debit', $after['ledger']['period_debit'], 171.08);
money_is('after ledger period credit', $after['ledger']['period_credit'], 64.61);
money_is('after ledger ending (active 56.47 + manual 50)', $after['ledger']['ending_balance'], 106.47);
money_is('after trial balance debit', $after['trial_balance']['debit'], 106.47);
money_is('after trial balance credit', $after['trial_balance']['credit'], 0.00);
money_is('after export period debit matches ledger', $after['export']['period_debit'], $after['ledger']['period_debit']);
money_is('after export period credit matches ledger', $after['export']['period_credit'], $after['ledger']['period_credit']);
money_is('after export ending matches ledger', $after['export']['ending_balance'], $after['ledger']['ending_balance']);
check('original source debit is still in the ledger', in_array(SCREENSHOT_RECEIPT, refs_of($after['ledger']['lines']), true));
check('void credit is in the ledger', in_array($void_receipt, refs_of($after['ledger']['lines']), true));
check('manual journal reference is still in the ledger', in_array('FXMANUAL5000', refs_of($after['ledger']['lines']), true));
check('other tenant line is excluded', in_array('FXOTHERPIN01', refs_of($after['ledger']['lines']), true) === false);

$without_manual = Savings_interest_gl::report_totals(array_merge($before_books, with_pin($posted, PIN)), EXPENSE, PIN, FROM, UNTIL, true);
money_is('screenshot accounts after an in-period void, excluding the manual entry', $without_manual['ledger']['ending_balance'], 56.47);
money_is('screenshot trial balance debit after an in-period void', $without_manual['trial_balance']['debit'], 56.47);
money_is('period debit still lists the original 64.61 plus 36.24 and 20.23', $without_manual['ledger']['period_debit'], 121.08);

/* Void dated after the screenshot window: historical as-of is unchanged. */
$later = Savings_interest_gl::reversal_lines_to_post($source_lines, array(), '2026-11-08', 'SVFIX00003', SCREENSHOT_RECEIPT);
$later_books = array_merge($before_books, with_pin($later, PIN));
$historical = Savings_interest_gl::report_totals($later_books, EXPENSE, PIN, FROM, UNTIL, true);
money_is('void after 2024-12-31 does not change that ledger ending', $historical['ledger']['ending_balance'], 121.08);
money_is('void after 2024-12-31 does not change that trial balance', $historical['trial_balance']['debit'], 121.08);
$current = Savings_interest_gl::report_totals($later_books, EXPENSE, PIN, FROM, '2026-12-31', true);
money_is('same void is inside a report that runs through the void date', $current['ledger']['ending_balance'], 56.47);
money_is('trial balance through the void date drops the 64.61', $current['trial_balance']['debit'], 56.47);

/* Carried-forward balance: void in 2022, report starts in 2023. */
$carried = Savings_interest_gl::report_totals($correct_books, EXPENSE, PIN, '2023-01-01', UNTIL, true);
money_is('carried forward opening nets the 2022 void to zero and keeps no manual yet', $carried['ledger']['opening_balance'], 0.00);
money_is('2023-2024 period debit is the two active interest amounts plus the manual 50', $carried['ledger']['period_debit'], 106.47);
money_is('2023-2024 period credit is zero', $carried['ledger']['period_credit'], 0.00);
money_is('carried forward ending', $carried['ledger']['ending_balance'], 106.47);
check('2022 source line is not repeated inside the later window', in_array(SCREENSHOT_RECEIPT, refs_of($carried['ledger']['lines']), true) === false);

$liability_tb = Savings_interest_gl::report_totals(array_merge($before_books, with_pin($posted, PIN)), LIABILITY, PIN, FROM, UNTIL, false);
money_is('liability ledger ending keeps only the active interest credits', $liability_tb['ledger']['ending_balance'], 56.47);
money_is('liability trial balance debit column is zero', $liability_tb['trial_balance']['debit'], 0.00);
money_is('liability trial balance credit column is the active interest', $liability_tb['trial_balance']['credit'], 56.47);
$voided_pair_only = array_merge($source_lines, with_pin($posted, PIN));
$pair_liability = Savings_interest_gl::report_totals($voided_pair_only, LIABILITY, PIN, FROM, UNTIL, false);
money_is('voided interest pair nets to zero on the liability', $pair_liability['ledger']['ending_balance'], 0.00);
$pair_expense = Savings_interest_gl::report_totals($voided_pair_only, EXPENSE, PIN, FROM, UNTIL, true);
money_is('voided interest pair nets to zero on the expense', $pair_expense['ledger']['ending_balance'], 0.00);

$missing = Savings_interest_gl::diagnose_interest_void($source_lines, array());
check('void with no GL reversal is missing_reversal', $missing['status'] === 'missing_reversal');
check('missing-reversal repair balances', Savings_interest_gl::lines_are_balanced($missing['repair']));
$missing_accounts = array();
foreach ($missing['repair'] as $repair_line) {
    $missing_accounts[$repair_line['account']] = $repair_line;
}
money_is('missing reversal repair credits expense', $missing_accounts[EXPENSE]['credit'], VOIDED_AMT);
money_is('missing reversal repair debits liability', $missing_accounts[LIABILITY]['debit'], VOIDED_AMT);

$correct_diag = Savings_interest_gl::diagnose_interest_void($source_lines, with_pin($posted, PIN));
check('a posted mirror is already reversed', $correct_diag['status'] === 'already_reversed');
check('already reversed proposes no journal', $correct_diag['repair'] === array());

$no_gl = Savings_interest_gl::diagnose_interest_void(array(), array());
check('interest that never reached the GL is no_gl', $no_gl['status'] === 'no_gl');

$empty_mirror = Savings_interest_gl::mirror_lines(array(
    array('account' => EXPENSE, 'debit' => 0, 'credit' => 0, 'description' => '[JV] empty'),
), '2022-02-28', $void_receipt, SCREENSHOT_RECEIPT);
check('zero amount source lines are not mirrored', $empty_mirror === array());

require dirname(__DIR__) . '/tools/diagnose_voided_savings_interest_gl.php';
$diagnostic_sql = savings_interest_void_diagnostic_sql();
check('diagnostic SQL stays read-only', savings_interest_void_diagnostic_is_read_only($diagnostic_sql));
check('diagnostic SQL reads savings transactions and the general ledger', strpos($diagnostic_sql, 'FROM savings_transaction') !== false && strpos($diagnostic_sql, 'general_ledger') !== false);
check('diagnostic SQL keeps the PIN predicate', strpos($diagnostic_sql, 'src.PIN = @pin') !== false);

$model = file_get_contents(dirname(__DIR__) . '/application/models/finance_model.php');
$post_fn = strpos($model, 'function post_savings_to_gl(');
$cash_lookup = strpos($model, 'get_cash_account_for_savings', $post_fn);
$interest_branch = strpos($model, 'savings_interest_void_context', $post_fn);
check('post_savings_to_gl handles an interest void before the cash-account lookup', $interest_branch !== false && $cash_lookup !== false && $interest_branch < $cash_lookup);
check('void builds the system comment through the interest policy', strpos($model, 'Savings_interest_gl::build_void_system_comment') !== false);
check('void refuses to void a reversing entry', strpos($model, 'Cannot void a reversing entry') !== false);

if ($failures > 0) {
    echo $failures . " failed\n";
    exit(1);
}
echo "all passed\n";
exit(0);
