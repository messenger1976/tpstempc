<?php
/**
 * Prints read-only SQL for voided savings interest whose general-ledger
 * net is not zero. Does not open a database connection and does not
 * change rows.
 *
 *   php tools/diagnose_voided_savings_interest_gl.php
 */
function savings_interest_void_diagnostic_sql() {
    return <<<'SQL'
-- Read-only diagnostic for voided savings interest whose general-ledger
-- net is not zero. Do not add INSERT, UPDATE, or DELETE to this file.
-- It does not change balances. Run it in a client that is not set to
-- auto-commit writes, and do not append a repair statement.
--
-- Why this exists
-- ---------------
-- Savings interest is stored as savings_transaction.trans_type = 'CR'
-- (credit() records every savings increase as CR) with system_comment
-- INTEREST. The void used to tag ORIG_TYPE with that stored CR and then
-- post the reversing receipt like a withdrawal:
--     Dr savings liability
--     Cr cash or adjustment / equity
-- The original interest journal stays in place:
--     Dr interest expense (often 71100)
--     Cr savings liability
-- Liability nets to zero. Interest expense does not. The account ledger
-- and the trial balance keep the debit. The original line is supposed to
-- remain; the missing piece is an equal credit on the same expense account.
--
-- Code after the fix mirrors the original lines when a void is posted
-- for the first time. It does not rewrite a void receipt that already
-- has general_ledger rows. Those historical rows need an audited journal,
-- described at the bottom. This script only finds them.
--
-- Parameters: @pin NULL lists every cooperative. Replace NULL with one
-- PIN before reviewing a single tenant. This assignment does not write
-- table data. Amounts use ROUND(..., 2).

SET @pin := NULL;

-- 1. Voided interest source rows and the reversing savings receipt.
--    Interest is system_comment INTEREST, or trans_type INT/IN/IR/INTEREST.
--    A deposit whose free-text comment merely contains "interest" is excluded.
SELECT
    src.PIN,
    src.receipt AS source_receipt,
    src.trans_type AS source_trans_type,
    src.system_comment AS source_system_comment,
    src.paymethod AS source_paymethod,
    src.amount AS source_amount,
    src.trans_date AS source_date,
    src.account AS savings_account,
    v.receipt AS void_receipt,
    v.trans_date AS void_date,
    v.system_comment AS void_system_comment,
    v.amount AS void_amount
FROM savings_transaction src
INNER JOIN savings_transaction v
    ON v.PIN = src.PIN
   AND v.comment LIKE CONCAT('VOID-', src.receipt, ' -%')
WHERE (@pin IS NULL OR src.PIN = @pin)
  AND UPPER(COALESCE(src.comment, '')) NOT LIKE 'VOID-%'
  AND UPPER(COALESCE(src.system_comment, '')) NOT LIKE 'VOID TRANSACTION%'
  AND (
        UPPER(TRIM(src.trans_type)) IN ('INT', 'IN', 'INTEREST', 'IR')
        OR UPPER(COALESCE(src.system_comment, '')) LIKE '%INTEREST%'
      )
ORDER BY src.PIN, src.trans_date, src.receipt;

-- 2. Net debit-minus-credit of each source receipt plus its void receipt,
--    by GL account. Other journals (manual entries, other interest, other
--    tenants) are not included. A pair that was reversed correctly has
--    no row here, because every account nets to zero.
--    net_debit > 0 means that account still has an unreversed debit
--    (the usual interest-expense symptom).
--    net_debit < 0 means that account still has an extra credit
--    (the usual cash / adjustment symptom of the old void).
SELECT
    src.PIN,
    src.receipt AS source_receipt,
    v.receipt AS void_receipt,
    gl.account,
    ROUND(SUM(CASE WHEN gl.refferenceID = src.receipt THEN gl.debit ELSE 0 END), 2) AS source_debit,
    ROUND(SUM(CASE WHEN gl.refferenceID = src.receipt THEN gl.credit ELSE 0 END), 2) AS source_credit,
    ROUND(SUM(CASE WHEN gl.refferenceID = v.receipt THEN gl.debit ELSE 0 END), 2) AS void_debit,
    ROUND(SUM(CASE WHEN gl.refferenceID = v.receipt THEN gl.credit ELSE 0 END), 2) AS void_credit,
    ROUND(SUM(gl.debit - gl.credit), 2) AS net_debit,
    MIN(gl.date) AS earliest_gl_date,
    MAX(gl.date) AS latest_gl_date
FROM savings_transaction src
INNER JOIN savings_transaction v
    ON v.PIN = src.PIN
   AND v.comment LIKE CONCAT('VOID-', src.receipt, ' -%')
INNER JOIN general_ledger gl
    ON gl.PIN = src.PIN
   AND gl.fromtable = 'savings_transaction'
   AND gl.refferenceID IN (src.receipt, v.receipt)
WHERE (@pin IS NULL OR src.PIN = @pin)
  AND UPPER(COALESCE(src.comment, '')) NOT LIKE 'VOID-%'
  AND UPPER(COALESCE(src.system_comment, '')) NOT LIKE 'VOID TRANSACTION%'
  AND (
        UPPER(TRIM(src.trans_type)) IN ('INT', 'IN', 'INTEREST', 'IR')
        OR UPPER(COALESCE(src.system_comment, '')) LIKE '%INTEREST%'
      )
GROUP BY src.PIN, src.receipt, v.receipt, gl.account
HAVING ABS(ROUND(SUM(gl.debit - gl.credit), 2)) >= 0.01
ORDER BY src.PIN, src.receipt, gl.account;

-- 3. Same interest voids that have source GL lines and no void-receipt
--    GL lines at all (void saved on the savings row, books not reversed).
SELECT
    src.PIN,
    src.receipt AS source_receipt,
    v.receipt AS void_receipt,
    src.amount,
    src.trans_date AS source_date,
    v.trans_date AS void_date
FROM savings_transaction src
INNER JOIN savings_transaction v
    ON v.PIN = src.PIN
   AND v.comment LIKE CONCAT('VOID-', src.receipt, ' -%')
WHERE (@pin IS NULL OR src.PIN = @pin)
  AND UPPER(COALESCE(src.comment, '')) NOT LIKE 'VOID-%'
  AND UPPER(COALESCE(src.system_comment, '')) NOT LIKE 'VOID TRANSACTION%'
  AND (
        UPPER(TRIM(src.trans_type)) IN ('INT', 'IN', 'INTEREST', 'IR')
        OR UPPER(COALESCE(src.system_comment, '')) LIKE '%INTEREST%'
      )
  AND EXISTS (
        SELECT 1 FROM general_ledger g
        WHERE g.PIN = src.PIN
          AND g.fromtable = 'savings_transaction'
          AND g.refferenceID = src.receipt
      )
  AND NOT EXISTS (
        SELECT 1 FROM general_ledger g
        WHERE g.PIN = src.PIN
          AND g.fromtable = 'savings_transaction'
          AND g.refferenceID = v.receipt
      )
ORDER BY src.PIN, src.trans_date, src.receipt;

-- Proposed audited repair (do not execute from this file)
-- -------------------------------------------------------
-- For each source_receipt / void_receipt group returned above, post ONE
-- new balanced journal in an open period. Do not delete or edit the
-- original general_ledger rows. Do not post a second mirror against a
-- void receipt that already has lines.
--
-- Date:
--   Use the void date when that date is still open, if the historical
--   ledger and trial balance for that period should change.
--   Use a current open date if the books for the void date are closed.
--   A current date fixes the trial balance from that date forward.
--   Reports whose "until" date is before the repair date will still
--   show the original debit. That is the as-of rule. Reopening a closed
--   period to backdate the repair is an explicit close-books decision.
--
-- Lines, taken from query 2 (one pair at a time):
--   net_debit > 0  ->  credit that account for net_debit
--   net_debit < 0  ->  debit that account for ABS(net_debit)
--   Skip accounts whose net is already 0. In particular, do not debit
--   the savings liability again when the old void already did.
--   The debits and credits of this journal are equal because the source
--   journal and the void journal were each balanced.
--
-- Typical legacy shape for 64.61:
--   Dr adjustment or cash     64.61     (undo the withdrawal-style credit)
--   Cr interest expense       64.61     (the account named in query 2,
--                                        not "whichever interest account
--                                        sorts first today")
--
-- If query 3 returns the row (no void GL at all), the same rule produces
-- the full mirror:
--   Dr savings liability      amount
--   Cr interest expense       amount
--
-- After the journal is posted, rerun query 2. The pair should disappear.
-- Query 2 must not be used to build a journal that also includes manual
-- or other-receipt lines; it already excludes them.
--
-- Member savings balance is a separate subledger. This void path does not
-- change members_account.balance (void rows are excluded from interest
-- balance rebuilds). Do not "fix" that balance inside the GL repair
-- unless a separate subledger tie-out says to.
--
-- Leave @pin NULL only for a cooperative-wide review. Repair one PIN
-- at a time and keep the journal description pointed at the source
-- receipt and the void receipt.
SQL;
}

function savings_interest_void_diagnostic_is_read_only($sql) {
    $stripped = preg_replace("/--.*$/m", "", $sql);
    $stripped = preg_replace("#/\\*.*?\\*/#s", "", $stripped);
    return !preg_match("/\\b(INSERT|UPDATE|DELETE|ALTER|DROP|TRUNCATE|REPLACE|CREATE)\\b/i", $stripped);
}

if (PHP_SAPI === "cli" && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $sql = savings_interest_void_diagnostic_sql();
    if (!savings_interest_void_diagnostic_is_read_only($sql)) {
        fwrite(STDERR, "Diagnostic SQL is not read-only\n");
        exit(1);
    }
    echo $sql;
    if (substr($sql, -1) !== "\n") {
        echo "\n";
    }
}