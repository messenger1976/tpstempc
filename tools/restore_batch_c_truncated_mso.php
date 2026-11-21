<?php
/**
 * Restore Batch C truncated-member_id MSO beginning-balance twins.
 *
 * Use when Batch C deleted the 13 short-member_id copies and you need them
 * back before applying the red-box 3-MSO / 6-Special voids.
 *
 * Usage (from app root, with DB credentials in application/config/database.php):
 *   php tools/restore_batch_c_truncated_mso.php           # apply
 *   php tools/restore_batch_c_truncated_mso.php --dry-run # preview only
 *
 * Expects keep-side receipts to still exist. PIN=105.
 */
define('BASEPATH', true);
include dirname(__DIR__) . '/application/config/database.php';

$dry = in_array('--dry-run', $argv, true);

$mysqli = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);
if ($mysqli->connect_error) {
    fwrite(STDERR, 'CONNECT_FAIL: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

$twins = array(
    // void_receipt (restore), amount, trunc_member_id, sav_account, keep_receipt, PID
    array('UPLWOMD30001', 10061.86, '2009', 150, 'G17SK0F40001', 128),
    array('X2OSVFE30001', 6733.72, '1997', 151, 'BFZ8STF40001', 138),
    array('CMD9UHI30001', 4113.25, '2009', 156, 'W4TLTVJ40001', 152),
    array('P7NP6Y730001', 3166.10, '2009', 143, 'RIRI4C940001', 101),
    array('YDAHGVG30001', 2660.10, '1996', 154, 'WUZNE9I40001', 148),
    array('0EL0ITC30001', 2205.02, '2012', 149, 'UHJBD7E40001', 124),
    array('ZYBDNOH30001', 2100.33, '2001', 155, '3CP4M2J40001', 150),
    array('VIPEKK930001', 1365.96, '2020', 145, 'ZPWFJYA40001', 105),
    array('61V747B30001', 1275.93, '2014', 147, 'ZA8DYKC40001', 109),
    array('4XVO29F30001', 1020.10, '2019', 152, 'IWSPZMG40001', 142),
    array('8O89RDA30001', 643.30, '1995', 146, '04NWQRB40001', 106),
    array('0G6KDR830001', 200.00, '2005', 144, 'YDDZB5A40001', 103),
    array('0HWK92G30001', 50.00, '2012', 153, 'RND67GH40001', 146),
);

function q(mysqli $db, $sql) {
    $r = $db->query($sql);
    if ($r === false) {
        throw new Exception($db->error . "\nSQL: " . $sql);
    }
    return $r;
}

echo $dry ? "=== DRY RUN (no writes) ===\n" : "=== APPLY ===\n";
echo "PIN=105  twins=" . count($twins) . "  total=35595.67\n\n";

$st_ins = 0;
$gl_ins = 0;
$st_skip = 0;
$gl_skip = 0;

try {
    foreach ($twins as $t) {
        list($void_receipt, $amount, $trunc_member, $sav_acct, $keep_receipt, $pid) = $t;
        $void_e = $mysqli->real_escape_string($void_receipt);
        $keep_e = $mysqli->real_escape_string($keep_receipt);
        $amt = number_format($amount, 2, '.', '');
        $pid_i = (int) $pid;
        $sav_acct_i = (int) $sav_acct;

        $keep_st = q($mysqli, "SELECT * FROM savings_transaction WHERE PIN=105 AND receipt='{$keep_e}' LIMIT 1")->fetch_assoc();
        if (!$keep_st) {
            throw new Exception("Missing keep savings_transaction receipt={$keep_receipt}");
        }
        $keep_gl = q($mysqli, "SELECT * FROM general_ledger WHERE PIN=105 AND refferenceID='{$keep_e}' AND account=21120 LIMIT 1")->fetch_assoc();
        if (!$keep_gl) {
            throw new Exception("Missing keep general_ledger receipt={$keep_receipt} account=21120");
        }

        $st_exists = q($mysqli, "SELECT id FROM savings_transaction WHERE PIN=105 AND receipt='{$void_e}' LIMIT 1")->num_rows > 0;
        $gl_exists = q($mysqli, "SELECT id FROM general_ledger WHERE PIN=105 AND account=21120 AND refferenceID='{$void_e}' LIMIT 1")->num_rows > 0;

        echo sprintf(
            "%s  amt=%s  trunc=%s  keep=%s(%s)  ST=%s  GL=%s\n",
            $void_receipt,
            $amt,
            $trunc_member,
            $keep_receipt,
            $keep_gl['member_id'],
            $st_exists ? 'exists' : 'MISSING',
            $gl_exists ? 'exists' : 'MISSING'
        );

        if ($st_exists) {
            $st_skip++;
        } elseif (!$dry) {
            $refno_e = $mysqli->real_escape_string($keep_st['refno']);
            $pay_e = $mysqli->real_escape_string($keep_st['paymethod']);
            $cheque_e = $mysqli->real_escape_string($keep_st['cheque_num']);
            $tdate_e = $mysqli->real_escape_string($keep_st['trans_date']);
            $comment_e = $mysqli->real_escape_string($keep_st['comment']);
            $name_e = $mysqli->real_escape_string($keep_st['customer_name']);
            $createdby = (int) $keep_st['createdby'];
            q($mysqli, "INSERT INTO savings_transaction
                (receipt, refno, account, account_cat, trans_type, paymethod, cheque_num, trans_date,
                 amount, previous_balance, comment, system_comment, PID, customer_name, createdby, PIN)
                VALUES
                ('{$void_e}','{$refno_e}',{$sav_acct_i},2017,'CR','{$pay_e}','{$cheque_e}','{$tdate_e}',
                 {$amt},0,'{$comment_e}','BEGINNING BALANCE',{$pid_i},'{$name_e}',{$createdby},105)");
            $st_ins++;
        }

        if ($gl_exists) {
            $gl_skip++;
        } elseif (!$dry) {
            $er = q($mysqli, "SELECT IFNULL(MAX(CAST(entryid AS UNSIGNED)),0)+1 AS n FROM general_ledger WHERE PIN=105")->fetch_assoc();
            $entryid = (string) $er['n'];
            $trunc_e = $mysqli->real_escape_string($trunc_member);
            $desc = $mysqli->real_escape_string(
                'Savings Beginning Balance - ' . $keep_st['customer_name']
                . ' (Account: ' . $sav_acct . ', Receipt: ' . $void_receipt . ')'
            );
            $date_e = $mysqli->real_escape_string($keep_gl['date']);
            $linkto_e = $mysqli->real_escape_string($keep_gl['linkto']);
            $from_e = $mysqli->real_escape_string($keep_gl['fromtable']);
            $acct_type = (int) $keep_gl['account_type'];
            $sub_type = (int) $keep_gl['sub_account_type'];
            $paid = (int) $keep_gl['paid'];
            q($mysqli, "INSERT INTO general_ledger
                (entryid, refferenceID, journalID, date, account, description, credit, debit,
                 linkto, fromtable, PID, member_id, customerid, supplierid, balance,
                 account_type, sub_account_type, paid, invoiceid, LID, PIN)
                VALUES
                ('{$entryid}','{$void_e}',8,'{$date_e}',21120,'{$desc}',{$amt},0,
                 '{$linkto_e}','{$from_e}',{$pid_i},'{$trunc_e}','','',0,
                 {$acct_type},{$sub_type},{$paid},0,'',105)");
            $gl_ins++;
        }
    }

    echo "\nSummary: ST inserted={$st_ins} skipped={$st_skip} | GL inserted={$gl_ins} skipped={$gl_skip}\n";

    if (!$dry) {
        $m = q($mysqli, "SELECT ROUND(SUM(credit)-SUM(debit),2) net
            FROM general_ledger gl
            INNER JOIN account_chart ac ON ac.account=gl.account AND ac.PIN=gl.PIN
            WHERE gl.PIN=105 AND gl.account=21120 AND gl.date<='2024-12-31'")->fetch_assoc();
        $c = q($mysqli, "SELECT COUNT(*) n, ROUND(IFNULL(SUM(credit),0),2) total
            FROM general_ledger
            WHERE PIN=105 AND account=21120 AND refferenceID IN (
              'UPLWOMD30001','X2OSVFE30001','CMD9UHI30001','P7NP6Y730001','YDAHGVG30001',
              '0EL0ITC30001','ZYBDNOH30001','VIPEKK930001','61V747B30001','4XVO29F30001',
              '8O89RDA30001','0G6KDR830001','0HWK92G30001'
            )")->fetch_assoc();
        echo "Verify truncated twins present: n={$c['n']} total={$c['total']} (expect 13 / 35595.67)\n";
        echo "MSO 21120 net now={$m['net']}\n";
        if ((int) $c['n'] !== 13 || abs($c['total'] - 35595.67) > 0.02) {
            fwrite(STDERR, "WARNING: twin count/total not as expected — review before red-box voids.\n");
            exit(2);
        }
    } else {
        echo "Dry-run only — no changes written.\n";
    }

    echo "Done.\n";
} catch (Exception $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
