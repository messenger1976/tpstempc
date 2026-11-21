<?php
/**
 * Apply audited repair journals for savings interest voids whose GL was posted
 * like a withdrawal (legacy bug). Does not delete or edit existing GL lines.
 *
 * Dry-run (default):
 *   php tools/repair_voided_savings_interest_gl.php
 *   php tools/repair_voided_savings_interest_gl.php --receipt=L043QPOD0001
 *
 * Apply:
 *   php tools/repair_voided_savings_interest_gl.php --apply
 *   php tools/repair_voided_savings_interest_gl.php --apply --receipt=L043QPOD0001
 *
 * Optional:
 *   --pin=NNNN
 *   --date=YYYY-MM-DD   (defaults to each void's trans_date)
 */
require dirname(__DIR__) . '/application/libraries/Savings_interest_gl.php';

$opts = array(
    'apply' => false,
    'pin' => null,
    'receipt' => null,
    'date' => null,
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'tapstemco',
);
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--apply') {
        $opts['apply'] = true;
        continue;
    }
    if (preg_match('/^--pin=(.+)$/', $arg, $m)) {
        $opts['pin'] = $m[1];
        continue;
    }
    if (preg_match('/^--receipt=(.+)$/', $arg, $m)) {
        $opts['receipt'] = $m[1];
        continue;
    }
    if (preg_match('/^--date=(\d{4}-\d{2}-\d{2})$/', $arg, $m)) {
        $opts['date'] = $m[1];
        continue;
    }
    fwrite(STDERR, "Unknown argument: {$arg}\n");
    exit(1);
}

$mysqli = @new mysqli($opts['host'], $opts['user'], $opts['pass'], $opts['db']);
if ($mysqli->connect_errno) {
    fwrite(STDERR, 'DB connect failed: ' . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8');

function q($mysqli, $sql, $types = '', $params = array()) {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $mysqli->error . ' SQL=' . $sql);
    }
    if ($types !== '' && !empty($params)) {
        $bind = array($types);
        foreach ($params as $k => $v) {
            $bind[] = &$params[$k];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind);
    }
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Execute failed: ' . $err);
    }
    $res = $stmt->get_result();
    $rows = array();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $res->free();
    }
    $stmt->close();
    return $rows;
}

function column_exists($mysqli, $table, $column) {
    $table = $mysqli->real_escape_string($table);
    $column = $mysqli->real_escape_string($column);
    $res = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    $ok = ($res && $res->num_rows > 0);
    if ($res) {
        $res->free();
    }
    return $ok;
}

function next_jv_no($mysqli, $pin, $year) {
    $prefix = 'JV-' . $year;
    $like = $prefix . '%';
    $rows = q(
        $mysqli,
        "SELECT reference_no FROM general_journal_entry
         WHERE PIN = ? AND reference_no LIKE ?
         ORDER BY reference_no DESC LIMIT 1",
        'ss',
        array($pin, $like)
    );
    $seq = 1;
    if (!empty($rows[0]['reference_no']) && preg_match('/JV-\d{4}(\d+)$/', $rows[0]['reference_no'], $m)) {
        $seq = ((int) $m[1]) + 1;
    }
    return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
}

function gl_lines_for_receipt($mysqli, $pin, $receipt) {
    return q(
        $mysqli,
        "SELECT account, debit, credit, description, journalID, date, PID, member_id,
                account_type, sub_account_type
         FROM general_ledger
         WHERE PIN = ? AND fromtable = 'savings_transaction' AND refferenceID = ?
         ORDER BY id ASC",
        'ss',
        array($pin, $receipt)
    );
}

function account_info($mysqli, $pin, $account) {
    $rows = q(
        $mysqli,
        "SELECT account, account_type, sub_account_type, name
         FROM account_chart WHERE PIN = ? AND account = ? LIMIT 1",
        'ss',
        array($pin, $account)
    );
    return !empty($rows[0]) ? $rows[0] : null;
}

$sql = "
SELECT
    src.PIN,
    src.receipt AS source_receipt,
    src.trans_type AS source_trans_type,
    src.system_comment AS source_system_comment,
    src.amount AS source_amount,
    src.trans_date AS source_date,
    v.receipt AS void_receipt,
    v.trans_date AS void_date,
    v.system_comment AS void_system_comment
FROM savings_transaction src
INNER JOIN savings_transaction v
    ON v.PIN = src.PIN
   AND v.comment LIKE CONCAT('VOID-', src.receipt, ' -%')
WHERE UPPER(COALESCE(src.comment, '')) NOT LIKE 'VOID-%'
  AND UPPER(COALESCE(src.system_comment, '')) NOT LIKE 'VOID TRANSACTION%'
  AND (
        UPPER(TRIM(src.trans_type)) IN ('INT', 'IN', 'INTEREST', 'IR')
        OR UPPER(COALESCE(src.system_comment, '')) LIKE '%INTEREST%'
      )
";
$types = '';
$params = array();
if ($opts['pin'] !== null) {
    $sql .= ' AND src.PIN = ?';
    $types .= 's';
    $params[] = $opts['pin'];
}
if ($opts['receipt'] !== null) {
    $sql .= ' AND src.receipt = ?';
    $types .= 's';
    $params[] = $opts['receipt'];
}
$sql .= ' ORDER BY src.PIN, src.trans_date, src.receipt';

try {
    $pairs = q($mysqli, $sql, $types, $params);
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

if (empty($pairs)) {
    echo "No interest void pairs found";
    if ($opts['receipt']) {
        echo ' for receipt ' . $opts['receipt'];
    }
    echo ".\n";
    exit(0);
}

$has_ref_no = column_exists($mysqli, 'general_journal_entry', 'reference_no');
$has_doc_no = column_exists($mysqli, 'general_journal_entry', 'document_no');
$has_ref_type = column_exists($mysqli, 'general_journal_entry', 'reference_type');

$repaired = 0;
$skipped = 0;
$failed = 0;

foreach ($pairs as $pair) {
    $pin = $pair['PIN'];
    $source = $pair['source_receipt'];
    $void = $pair['void_receipt'];
    $source_lines = gl_lines_for_receipt($mysqli, $pin, $source);
    $void_lines = gl_lines_for_receipt($mysqli, $pin, $void);
    $diag = Savings_interest_gl::diagnose_interest_void($source_lines, $void_lines);

    echo str_repeat('-', 72) . "\n";
    echo "PIN {$pin}  source {$source}  void {$void}  amount {$pair['source_amount']}\n";
    echo "status: {$diag['status']}\n";

    // Prior audited repairs live on general_journal, not the void receipt.
    $prior = q(
        $mysqli,
        "SELECT gje.id, gje.reference_no, gje.document_no
         FROM general_journal_entry gje
         INNER JOIN general_ledger gl
             ON gl.PIN = gje.PIN
            AND gl.fromtable = 'general_journal'
            AND gl.refferenceID = CAST(gje.id AS CHAR)
         WHERE gje.PIN = ?
           AND (
                gje.document_no = ?
                OR gje.description LIKE ?
                OR gl.description LIKE ?
           )
         GROUP BY gje.id, gje.reference_no, gje.document_no
         LIMIT 5",
        'ssss',
        array(
            $pin,
            'REPAIR-' . $void,
            '%source ' . $source . ' / void ' . $void . '%',
            '%source ' . $source . ' / void ' . $void . '%',
        )
    );
    if (!empty($prior)) {
        $ids = array();
        foreach ($prior as $p) {
            $ids[] = $p['id'] . (!empty($p['reference_no']) ? ('/' . $p['reference_no']) : '');
        }
        echo 'skip: repair journal already posted (' . implode(', ', $ids) . ")\n";
        $skipped++;
        continue;
    }

    if ($diag['status'] === 'already_reversed' || empty($diag['repair'])) {
        echo "skip: already nets to zero\n";
        $skipped++;
        continue;
    }
    if ($diag['status'] === 'no_gl') {
        echo "skip: no GL on either receipt\n";
        $skipped++;
        continue;
    }

    $repair_date = $opts['date'];
    if ($repair_date === null || $repair_date === '') {
        $repair_date = !empty($pair['void_date']) ? substr($pair['void_date'], 0, 10) : date('Y-m-d');
    }
    $desc = 'Repair interest void GL — source ' . $source . ' / void ' . $void
        . ' (undo withdrawal-style credit; credit interest expense)';

    echo "repair date: {$repair_date}\n";
    echo "journal lines:\n";
    foreach ($diag['repair'] as $line) {
        $info = account_info($mysqli, $pin, $line['account']);
        $name = $info ? $info['name'] : '?';
        echo sprintf(
            "  %-12s %-40s  Dr %10.2f  Cr %10.2f\n",
            $line['account'],
            substr($name, 0, 40),
            $line['debit'],
            $line['credit']
        );
        if (!$info) {
            echo "  ERROR: account {$line['account']} not in chart for PIN {$pin}\n";
            $failed++;
            continue 2;
        }
    }

    if (!Savings_interest_gl::lines_are_balanced($diag['repair'])) {
        echo "ERROR: proposed repair does not balance\n";
        $failed++;
        continue;
    }

    if (!$opts['apply']) {
        echo "dry-run only (pass --apply to post)\n";
        continue;
    }

    $mysqli->begin_transaction();
    try {
        $cols = array('entrydate', 'description', 'PIN');
        $vals = array('?', '?', '?');
        $bind_types = 'sss';
        $bind_vals = array($repair_date, $desc, $pin);
        $reference_no = '';
        if ($has_ref_no) {
            $year = date('Y', strtotime($repair_date));
            $reference_no = next_jv_no($mysqli, $pin, $year);
            $cols[] = 'reference_no';
            $vals[] = '?';
            $bind_types .= 's';
            $bind_vals[] = $reference_no;
        }
        if ($has_doc_no) {
            $doc = 'REPAIR-' . $void;
            $cols[] = 'document_no';
            $vals[] = '?';
            $bind_types .= 's';
            $bind_vals[] = $doc;
        }
        if ($has_ref_type) {
            $cols[] = 'reference_type';
            $vals[] = '?';
            $bind_types .= 's';
            $bind_vals[] = 'journal_voucher';
        }

        $sql_ins = 'INSERT INTO general_journal_entry (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
        $stmt = $mysqli->prepare($sql_ins);
        if (!$stmt) {
            throw new RuntimeException($mysqli->error);
        }
        $bind = array($bind_types);
        foreach ($bind_vals as $k => $v) {
            $bind[] = &$bind_vals[$k];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
        $journal_id = (int) $mysqli->insert_id;
        $stmt->close();
        if ($journal_id <= 0) {
            throw new RuntimeException('Failed to create general_journal_entry');
        }

        foreach ($diag['repair'] as $line) {
            $info = account_info($mysqli, $pin, $line['account']);
            $line_desc = $desc;
            $stmt = $mysqli->prepare(
                "INSERT INTO general_journal
                    (entryid, account, debit, credit, description, PIN)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                throw new RuntimeException($mysqli->error);
            }
            $acct = $line['account'];
            $debit = (float) $line['debit'];
            $credit = (float) $line['credit'];
            $stmt->bind_param('isddss', $journal_id, $acct, $debit, $credit, $line_desc, $pin);
            if (!$stmt->execute()) {
                throw new RuntimeException($stmt->error);
            }
            $stmt->close();
        }

        $stmt = $mysqli->prepare('INSERT INTO general_ledger_entry (date, PIN) VALUES (?, ?)');
        if (!$stmt) {
            throw new RuntimeException($mysqli->error);
        }
        $stmt->bind_param('ss', $repair_date, $pin);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
        $ledger_entry_id = (int) $mysqli->insert_id;
        $stmt->close();
        if ($ledger_entry_id <= 0) {
            throw new RuntimeException('Failed to create general_ledger_entry');
        }

        foreach ($diag['repair'] as $line) {
            $info = account_info($mysqli, $pin, $line['account']);
            $acct = $line['account'];
            $debit = (float) $line['debit'];
            $credit = (float) $line['credit'];
            $acct_type = $info['account_type'];
            $sub_type = isset($info['sub_account_type']) ? $info['sub_account_type'] : null;
            $line_desc = $desc;
            $journal_code = 5;
            $ref_id = (string) $journal_id;
            $linkto = 'general_journal.entryid';
            $fromtable = 'general_journal';

            $stmt = $mysqli->prepare(
                "INSERT INTO general_ledger
                    (journalID, refferenceID, entryid, date, description, linkto, fromtable,
                     account, debit, credit, account_type, sub_account_type, PIN)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                throw new RuntimeException($mysqli->error);
            }
            // i s i s s s s s d d i s s
            if ($sub_type === null) {
                $sub_type = '';
            }
            $acct_type = (int) $acct_type;
            $stmt->bind_param(
                'isisssssddiss',
                $journal_code,
                $ref_id,
                $ledger_entry_id,
                $repair_date,
                $line_desc,
                $linkto,
                $fromtable,
                $acct,
                $debit,
                $credit,
                $acct_type,
                $sub_type,
                $pin
            );
            if (!$stmt->execute()) {
                throw new RuntimeException($stmt->error);
            }
            $stmt->close();
        }

        // Re-check pair nets to zero when repair lines are included with the pair.
        $repair_as_gl = array();
        foreach ($diag['repair'] as $line) {
            $repair_as_gl[] = array(
                'account' => $line['account'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            );
        }
        $after = Savings_interest_gl::diagnose_interest_void(
            $source_lines,
            array_merge($void_lines, $repair_as_gl)
        );
        if ($after['status'] !== 'already_reversed') {
            throw new RuntimeException('Post-check failed; status=' . $after['status']);
        }

        $mysqli->commit();
        echo "APPLIED journal #{$journal_id}" . ($reference_no !== '' ? " ({$reference_no})" : '') . "\n";
        $repaired++;
    } catch (Exception $e) {
        $mysqli->rollback();
        echo 'FAILED: ' . $e->getMessage() . "\n";
        $failed++;
    }
}

echo str_repeat('=', 72) . "\n";
echo ($opts['apply'] ? 'APPLY' : 'DRY-RUN') . " complete. repaired={$repaired} skipped={$skipped} failed={$failed}\n";
$mysqli->close();
exit($failed > 0 ? 1 : 0);
