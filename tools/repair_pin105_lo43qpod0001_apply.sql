-- PIN 105 repair for receipt LO43QPOD0001 / void SV00002764.
-- Prepared posting date: 2022-02-28.
-- Posts one new balanced journal only:
--   DR 11100 Cash and Cash Equivalents          64.61
--   CR 71100 Interest Expense on Borrowings     64.61
-- Does not update or delete the source lines, the void lines, savings rows,
-- or member balances. Does not post anything to savings liability 21110.
--
-- Sourcing this file with the confirmation variables unset creates and then
-- drops a routine and writes no financial rows. CREATE PROCEDURE and
-- DROP PROCEDURE are DDL. MariaDB implicit-commits them. They are not undone
-- by the financial ROLLBACK. They require CREATE ROUTINE and DROP ROUTINE.
-- There is no CREATE/ALTER/DROP of a table and no temporary table.
--
-- The financial inserts run only inside the routine, in one transaction, and
-- only when every confirmation below is set in this session. None of those
-- values is assigned by this file.
--
--   SET @expected_database       := 'tapstemco';
--   SET @actor_user_id           := <users.id for PIN 105>;
--   SET @confirm_apply           := 'APPLY-PIN105-LO43QPOD0001';
--   SET @confirm_backup          := 'BACKUP-TAKEN-TAPSTEMCO-PIN105';
--   SET @confirm_historical_date := 'APPROVE-POSTING-DATE-2022-02-28';
--   SET @confirm_manual_review   := 'REVIEWED-NO-OTHER-641-CORRECTION';
--
-- @confirm_manual_review is required only when other PIN 105 lines already
-- use 64.61 on 11100 or 71100. It does not override a closed book, a changed
-- source pair, a linked extra line, or a 71100 window net other than 121.08.
-- Closed books are the latest gl_books_close.closed_as_of for PIN 105.
-- fiscal_year.status is not a close flag. There is no reopen switch.
--
-- Take the backup first. The backup token does not look for a dump file.
--   mysqldump --single-transaction --skip-lock-tables --no-tablespaces \
--     --default-character-set=utf8 \
--     --tables account_chart fiscal_year general_journal_entry general_ledger \
--     general_ledger_entry gl_books_close journal_entry journal_entry_items \
--     savings_transaction \
--     tapstemco > tapstemco_pin105_lo43qpod0001_pre_repair.sql

DROP PROCEDURE IF EXISTS repair_pin105_lo43qpod0001;

DELIMITER $$

CREATE PROCEDURE repair_pin105_lo43qpod0001()
SQL SECURITY INVOKER
proc: BEGIN
    DECLARE v_cnt INT DEFAULT 0;
    DECLARE v_marker INT DEFAULT 0;
    DECLARE v_ambiguous INT DEFAULT 0;
    DECLARE v_linked INT DEFAULT 0;
    DECLARE v_window DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_next_gl BIGINT DEFAULT NULL;
    DECLARE v_next_je BIGINT DEFAULT NULL;
    DECLARE v_je_id BIGINT DEFAULT NULL;
    DECLARE v_glh_id BIGINT DEFAULT NULL;
    DECLARE v_line_dr BIGINT DEFAULT NULL;
    DECLARE v_line_cr BIGINT DEFAULT NULL;
    DECLARE v_closed DATE DEFAULT NULL;
    DECLARE v_pair_71100 DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_pair_11100 DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_pair_21110 DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_liab_debit DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_liab_credit DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_other_interest INT DEFAULT 0;
    DECLARE v_gj_count INT DEFAULT 0;
    DECLARE v_gj_lines INT DEFAULT 0;
    DECLARE v_sav_ids INT DEFAULT 0;
    DECLARE v_ma_count INT DEFAULT NULL;
    DECLARE v_ma_balance DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_has_ma INT DEFAULT 0;
    DECLARE v_has_ma_balance INT DEFAULT 0;
    DECLARE v_has_deleted INT DEFAULT 0;
    DECLARE v_user_ok INT DEFAULT 0;
    DECLARE v_perm_ok INT DEFAULT 0;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_cnt = v_cnt;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            @repair_errno = MYSQL_ERRNO,
            @repair_sqlstate = RETURNED_SQLSTATE,
            @repair_diag = MESSAGE_TEXT;
        IF @repair_tx = 1 THEN
            ROLLBACK;
            SET @repair_tx = 0;
            SET @repair_status = 'ROLLED_BACK';
        ELSE
            SET @repair_status = 'REFUSED';
        END IF;
        IF @repair_message IS NULL OR @repair_message = '' THEN
            SET @repair_message = @repair_diag;
        END IF;
        SET @repair_lock_release = RELEASE_LOCK('tapstemco_repair_pin105_lo43qpod0001');
    END;

    SET @repair_tx = 0;
    SET @repair_status = 'REFUSED';
    SET @repair_message = NULL;
    SET @repair_errno = NULL;
    SET @repair_sqlstate = NULL;
    SET @repair_diag = NULL;
    SET @repair_journal_entry_id = NULL;
    SET @repair_gl_entry_id = NULL;
    SET @repair_gl_debit_line_id = NULL;
    SET @repair_gl_credit_line_id = NULL;
    SET @repair_window_net = NULL;
    SET @repair_pair_71100 = NULL;
    SET @repair_pair_11100 = NULL;
    SET @repair_pair_21110 = NULL;
    SET @repair_member_unchanged = NULL;

    SET @repair_lock = GET_LOCK('tapstemco_repair_pin105_lo43qpod0001', 10);
    IF @repair_lock IS NULL OR @repair_lock <> 1 THEN
        SET @repair_message = 'Repair lock not acquired';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Repair lock not acquired';
    END IF;

    IF NOT (
        @confirm_apply <=> 'APPLY-PIN105-LO43QPOD0001'
        AND @confirm_backup <=> 'BACKUP-TAKEN-TAPSTEMCO-PIN105'
        AND @confirm_historical_date <=> 'APPROVE-POSTING-DATE-2022-02-28'
        AND CAST(IFNULL(@actor_user_id, 0) AS UNSIGNED) > 0
    ) THEN
        SET @repair_status = 'PREFLIGHT_ONLY';
        SET @repair_message = 'Confirmation tokens or actor id are unset. No financial rows written.';
        SET @repair_lock_release = RELEASE_LOCK('tapstemco_repair_pin105_lo43qpod0001');
        LEAVE proc;
    END IF;

    IF @expected_database IS NULL OR @expected_database <> DATABASE() THEN
        SET @repair_message = 'expected_database is unset or does not match DATABASE()';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'expected_database is unset or does not match DATABASE()';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN ('general_ledger', 'general_ledger_entry', 'journal_entry', 'journal_entry_items')
      AND ENGINE = 'InnoDB';
    IF v_cnt <> 4 THEN
        SET @repair_message = 'Write tables must all be InnoDB';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Write tables must all be InnoDB';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TRIGGERS
    WHERE EVENT_OBJECT_SCHEMA = DATABASE()
      AND EVENT_OBJECT_TABLE IN (
          'general_ledger', 'general_ledger_entry', 'journal_entry',
          'journal_entry_items', 'general_journal_entry', 'savings_transaction'
      );
    IF v_cnt <> 0 THEN
        SET @repair_message = 'Triggers exist on a repair table; refusing';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Triggers exist on a repair table; refusing';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN (
          'gl_books_close', 'account_chart', 'savings_transaction',
          'users', 'users_groups', 'groups', 'access_level', 'general_journal_entry'
      );
    IF v_cnt <> 8 THEN
        SET @repair_message = 'Required audit tables are missing; see the README metadata list';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Required audit tables are missing; see the README metadata list';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'general_ledger'
      AND IS_NULLABLE = 'NO'
      AND COLUMN_DEFAULT IS NULL
      AND EXTRA NOT LIKE '%auto_increment%'
      AND COLUMN_NAME NOT IN (
          'entryid', 'refferenceID', 'journalID', 'date', 'account', 'description',
          'credit', 'debit', 'linkto', 'fromtable', 'PID', 'member_id', 'customerid',
          'supplierid', 'balance', 'account_type', 'sub_account_type', 'invoiceid',
          'LID', 'PIN'
      );
    IF v_cnt <> 0 THEN
        SET @repair_message = 'general_ledger has an unexpected required column';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'general_ledger has an unexpected required column';
    END IF;

    SELECT COUNT(*) INTO v_has_deleted
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'deleted';

    SELECT COUNT(*) INTO v_has_ma
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'members_account';
    IF v_has_ma = 1 THEN
        SELECT COUNT(*) INTO v_has_ma_balance
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'members_account'
          AND COLUMN_NAME = 'balance';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'general_journal';
    IF v_cnt = 1 THEN
        SELECT COUNT(*) INTO v_gj_lines FROM general_journal;
    END IF;
    SELECT COUNT(*) INTO v_gj_count FROM general_journal_entry;

    IF v_has_deleted = 1 THEN
        SELECT COUNT(*) INTO v_user_ok
        FROM users
        WHERE id = CAST(@actor_user_id AS UNSIGNED)
          AND CAST(PIN AS UNSIGNED) = 105
          AND (deleted IS NULL OR deleted = 0);
    ELSE
        SELECT COUNT(*) INTO v_user_ok
        FROM users
        WHERE id = CAST(@actor_user_id AS UNSIGNED)
          AND CAST(PIN AS UNSIGNED) = 105;
    END IF;
    IF v_user_ok <> 1 THEN
        SET @repair_message = 'Actor is not one active users row for PIN 105';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Actor is not one active users row for PIN 105';
    END IF;

    SELECT COUNT(*) INTO v_perm_ok
    FROM users_groups ug
    INNER JOIN groups g ON g.id = ug.group_id
    INNER JOIN access_level al ON al.group_id = g.id
    WHERE ug.user_id = CAST(@actor_user_id AS UNSIGNED)
      AND CAST(g.PIN AS UNSIGNED) = 105
      AND al.Module = 6
      AND al.link = 'Review_journal_entry'
      AND al.allow = 1;
    IF v_perm_ok < 1 THEN
        SET @repair_message = 'Actor lacks Module 6 Review_journal_entry on PIN 105';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Actor lacks Module 6 Review_journal_entry on PIN 105';
    END IF;

    SELECT COUNT(*) INTO v_marker
    FROM journal_entry
    WHERE PIN = '105'
      AND reference_type = 'savings_interest_repair'
      AND description LIKE '%REPAIR-LO43QPOD0001-SV00002764%'
      AND (is_voided IS NULL OR is_voided = 0);
    IF v_marker > 1 THEN
        SET @repair_message = 'More than one repair marker exists';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'More than one repair marker exists';
    END IF;
    IF v_marker = 1 THEN
        SELECT id INTO v_je_id
        FROM journal_entry
        WHERE PIN = '105'
          AND reference_type = 'savings_interest_repair'
          AND description LIKE '%REPAIR-LO43QPOD0001-SV00002764%'
          AND (is_voided IS NULL OR is_voided = 0)
        ORDER BY id
        LIMIT 1;
        SELECT COUNT(*) INTO v_cnt
        FROM general_ledger
        WHERE PIN = 105
          AND fromtable = 'journal_entry'
          AND refferenceID = CAST(v_je_id AS CHAR)
          AND `date` = '2022-02-28'
          AND (
                (account = 11100 AND ABS(debit - 64.61) < 0.005 AND ABS(credit) < 0.005)
                OR (account = 71100 AND ABS(credit - 64.61) < 0.005 AND ABS(debit) < 0.005)
              );
        IF v_cnt <> 2 THEN
            SET @repair_message = 'Repair marker does not match the two expected GL lines';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Repair marker does not match the two expected GL lines';
        END IF;
        SELECT COUNT(*) INTO v_cnt
        FROM general_ledger
        WHERE PIN = 105
          AND fromtable = 'journal_entry'
          AND refferenceID = CAST(v_je_id AS CHAR);
        IF v_cnt <> 2 THEN
            SET @repair_message = 'Repair marker has extra GL lines';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Repair marker has extra GL lines';
        END IF;
        SELECT CAST(entryid AS UNSIGNED) INTO v_glh_id
        FROM general_ledger
        WHERE PIN = 105
          AND fromtable = 'journal_entry'
          AND refferenceID = CAST(v_je_id AS CHAR)
        LIMIT 1;
        IF v_glh_id IN (39755, 169383) THEN
            SET @repair_message = 'Repair marker uses a legacy entry id';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Repair marker uses a legacy entry id';
        END IF;
        SELECT COUNT(*) INTO v_cnt
        FROM general_ledger_entry
        WHERE id = v_glh_id
          AND PIN = 105
          AND `date` = '2022-02-28';
        IF v_cnt <> 1 THEN
            SET @repair_message = 'Repair GL header is missing';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Repair GL header is missing';
        END IF;
        SELECT ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) INTO v_window
        FROM general_ledger
        WHERE PIN = 105
          AND account = 71100
          AND `date` >= '2021-11-01'
          AND `date` <= '2024-12-31';
        SET @repair_status = 'ALREADY_APPLIED';
        SET @repair_message = 'Repair already posted. No additional rows written.';
        SET @repair_journal_entry_id = v_je_id;
        SET @repair_gl_entry_id = v_glh_id;
        SET @repair_window_net = v_window;
        SET @repair_lock_release = RELEASE_LOCK('tapstemco_repair_pin105_lo43qpod0001');
        LEAVE proc;
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM gl_books_close WHERE PIN = 105;
    IF v_cnt > 0 THEN
        SELECT closed_as_of INTO v_closed
        FROM gl_books_close
        WHERE PIN = 105
        ORDER BY id DESC
        LIMIT 1;
    END IF;
    IF v_closed IS NOT NULL AND v_closed >= '2022-02-28' THEN
        SET @repair_message = 'Books are closed through 2022-02-28 or later; no reopen override';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Books are closed through 2022-02-28 or later; no reopen override';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger_entry
    WHERE id IN (39755, 169383);
    IF v_cnt <> 0 THEN
        SET @repair_message = 'Legacy entry ids 39755 or 169383 now have headers';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Legacy entry ids 39755 or 169383 now have headers';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE PIN = 105
      AND TRIM(entryid) IN ('39755', '169383');
    IF v_cnt <> 4 THEN
        SET @repair_message = 'Legacy entry ids no longer have exactly four PIN 105 lines';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Legacy entry ids no longer have exactly four PIN 105 lines';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM general_ledger WHERE id = 3830 AND PIN = 105 AND TRIM(entryid) = '39755' AND refferenceID = 'LO43QPOD0001' AND journalID = 5 AND `date` = '2022-02-28' AND account = 71100 AND ABS(debit - 64.61) < 0.005 AND ABS(credit) < 0.005 AND linkto = 'savings_transaction.receipt' AND fromtable = 'savings_transaction' AND account_type = 70000 AND sub_account_type = 71000 AND ABS(balance) < 0.005 AND paid = 1 AND invoiceid = 0 AND TRIM(LID) = '';
    IF v_cnt <> 1 THEN
        SET @repair_message = 'GL 3830 does not match the authorized source debit';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GL 3830 does not match the authorized source debit';
    END IF;
    SELECT COUNT(*) INTO v_cnt FROM general_ledger WHERE id = 3831 AND PIN = 105 AND TRIM(entryid) = '39755' AND refferenceID = 'LO43QPOD0001' AND journalID = 5 AND `date` = '2022-02-28' AND account = 21110 AND ABS(credit - 64.61) < 0.005 AND ABS(debit) < 0.005 AND linkto = 'savings_transaction.receipt' AND fromtable = 'savings_transaction' AND account_type = 20000 AND sub_account_type = 21000;
    IF v_cnt <> 1 THEN
        SET @repair_message = 'GL 3831 does not match the authorized source credit';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GL 3831 does not match the authorized source credit';
    END IF;
    SELECT COUNT(*) INTO v_cnt FROM general_ledger WHERE id = 11029 AND PIN = 105 AND TRIM(entryid) = '169383' AND refferenceID = 'SV00002764' AND journalID = 5 AND `date` = '2022-02-28' AND account = 21110 AND ABS(debit - 64.61) < 0.005 AND ABS(credit) < 0.005 AND linkto = 'savings_transaction.receipt' AND fromtable = 'savings_transaction' AND account_type = 20000 AND sub_account_type = 21000;
    IF v_cnt <> 1 THEN
        SET @repair_message = 'GL 11029 does not match the authorized void debit';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GL 11029 does not match the authorized void debit';
    END IF;
    SELECT COUNT(*) INTO v_cnt FROM general_ledger WHERE id = 11030 AND PIN = 105 AND TRIM(entryid) = '169383' AND refferenceID = 'SV00002764' AND journalID = 5 AND `date` = '2022-02-28' AND account = 11100 AND ABS(credit - 64.61) < 0.005 AND ABS(debit) < 0.005 AND linkto = 'savings_transaction.receipt' AND fromtable = 'savings_transaction' AND account_type = 10000 AND sub_account_type = 11000;
    IF v_cnt <> 1 THEN
        SET @repair_message = 'GL 11030 does not match the authorized void credit';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GL 11030 does not match the authorized void credit';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM savings_transaction WHERE id = 606 AND PIN = 105 AND receipt = 'LO43QPOD0001' AND CAST(account AS CHAR) = '24' AND trans_type = 'CR' AND paymethod = 'ADJUSTMENT' AND DATE(trans_date) = '2022-02-28' AND ABS(amount - 64.61) < 0.005 AND system_comment = 'INTEREST';
    IF v_cnt <> 1 THEN
        SET @repair_message = 'Savings source 606 does not match LO43QPOD0001';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Savings source 606 does not match LO43QPOD0001';
    END IF;
    SELECT COUNT(*) INTO v_cnt FROM savings_transaction WHERE id = 2764 AND PIN = 105 AND receipt = 'SV00002764' AND CAST(account AS CHAR) = '24' AND trans_type = 'DR' AND paymethod = 'ADJUSTMENT' AND DATE(trans_date) = '2022-02-28' AND ABS(amount - 64.61) < 0.005 AND system_comment LIKE 'VOID TRANSACTION | ORIG_TYPE:CR%';
    IF v_cnt <> 1 THEN
        SET @repair_message = 'Savings void 2764 does not match SV00002764';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Savings void 2764 does not match SV00002764';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM account_chart WHERE PIN = 105 AND account = 11100 AND name = 'Cash and Cash Equivalents' AND account_type = 10000 AND sub_account_type = 11000 AND is_header = 0;
    IF v_cnt <> 1 THEN
        SET @repair_message = 'Account 11100 is missing or is not a posting leaf';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Account 11100 is missing or is not a posting leaf';
    END IF;
    SELECT COUNT(*) INTO v_cnt FROM account_chart WHERE PIN = 105 AND account = 71100 AND name = 'Interest Expense on Borrowings' AND account_type = 70000 AND sub_account_type = 71000 AND is_header = 0;
    IF v_cnt <> 1 THEN
        SET @repair_message = 'Account 71100 is missing or is not a posting leaf';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Account 71100 is missing or is not a posting leaf';
    END IF;
    SELECT COUNT(*) INTO v_cnt FROM account_chart WHERE PIN = 105 AND account = 21110 AND name = 'Savings Deposit - Special' AND account_type = 20000 AND sub_account_type = 21000 AND is_header = 0;
    IF v_cnt <> 1 THEN
        SET @repair_message = 'Account 21110 is missing or is not the expected leaf';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Account 21110 is missing or is not the expected leaf';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) INTO v_window
    FROM general_ledger
    WHERE PIN = 105 AND account = 71100 AND `date` >= '2021-11-01' AND `date` <= '2024-12-31';
    IF v_window IS NULL OR ABS(v_window - 121.08) >= 0.01 THEN
        SET @repair_message = '71100 window net is not 121.08; repair refused';
        SET @repair_window_net = v_window;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '71100 window net is not 121.08; repair refused';
    END IF;

    SELECT COUNT(*) INTO v_linked
    FROM general_ledger
    WHERE PIN = 105
      AND id NOT IN (3830, 3831, 11029, 11030)
      AND (
            refferenceID IN ('LO43QPOD0001', 'SV00002764')
            OR description LIKE '%LO43QPOD0001%'
            OR description LIKE '%SV00002764%'
          );
    IF v_linked <> 0 THEN
        SET @repair_message = 'Another line already references these receipts';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Another line already references these receipts';
    END IF;

    SELECT COUNT(*) INTO v_ambiguous
    FROM general_ledger
    WHERE PIN = 105
      AND id NOT IN (3830, 3831, 11029, 11030)
      AND account IN (71100, 11100)
      AND (ABS(debit - 64.61) < 0.005 OR ABS(credit - 64.61) < 0.005);
    IF v_ambiguous <> 0 AND NOT (@confirm_manual_review <=> 'REVIEWED-NO-OTHER-641-CORRECTION') THEN
        SET @repair_message = 'Other 64.61 lines exist; manual-review token required';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Other 64.61 lines exist; manual-review token required';
    END IF;

    SELECT AUTO_INCREMENT INTO v_next_gl
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'general_ledger_entry';
    IF v_next_gl IS NULL OR v_next_gl IN (39755, 169383) THEN
        SET @repair_message = 'Next GL header id is missing or collides with a legacy entry id';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Next GL header id is missing or collides with a legacy entry id';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE TRIM(entryid) = CAST(v_next_gl AS CHAR);
    IF v_cnt <> 0 THEN
        SET @repair_message = 'Next GL header id is already used as a detail entryid';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Next GL header id is already used as a detail entryid';
    END IF;

    SELECT AUTO_INCREMENT INTO v_next_je
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'journal_entry';
    IF v_next_je IS NULL OR v_next_je IN (39755, 169383) THEN
        SET @repair_message = 'Next journal header id is missing or collides with a legacy entry id';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Next journal header id is missing or collides with a legacy entry id';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE fromtable = 'journal_entry'
      AND refferenceID = CAST(v_next_je AS CHAR);
    IF v_cnt <> 0 THEN
        SET @repair_message = 'Next journal id is already a GL reference';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Next journal id is already a GL reference';
    END IF;

    SELECT COUNT(*) INTO v_other_interest
    FROM general_ledger
    WHERE PIN = 105
      AND account = 71100
      AND id NOT IN (3830, 3831, 11029, 11030)
      AND (ABS(debit - 36.24) < 0.005 OR ABS(debit - 20.23) < 0.005);
    SELECT ROUND(COALESCE(SUM(debit), 0), 2), ROUND(COALESCE(SUM(credit), 0), 2)
        INTO v_liab_debit, v_liab_credit
    FROM general_ledger
    WHERE PIN = 105 AND account = 21110;
    SELECT COUNT(*) INTO v_sav_ids FROM savings_transaction WHERE PIN = 105 AND id IN (606, 2764);
    IF v_has_ma = 1 THEN
        IF v_has_ma_balance = 1 THEN
            SELECT COUNT(*), ROUND(COALESCE(SUM(balance), 0), 2)
                INTO v_ma_count, v_ma_balance
            FROM members_account
            WHERE CAST(PIN AS UNSIGNED) = 105
              AND CAST(account AS CHAR) = '24';
        ELSE
            SELECT COUNT(*) INTO v_ma_count
            FROM members_account
            WHERE CAST(PIN AS UNSIGNED) = 105
              AND CAST(account AS CHAR) = '24';
        END IF;
    END IF;

    SET @repair_tx = 1;
    START TRANSACTION;

    SELECT id INTO v_cnt FROM general_ledger WHERE id = 3830 AND PIN = 105 FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = 3831 AND PIN = 105 FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = 11029 AND PIN = 105 FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = 11030 AND PIN = 105 FOR UPDATE;
    SELECT id INTO v_cnt FROM savings_transaction WHERE id = 606 AND PIN = 105 FOR UPDATE;
    SELECT id INTO v_cnt FROM savings_transaction WHERE id = 2764 AND PIN = 105 FOR UPDATE;

    SELECT COUNT(*) INTO v_cnt
    FROM journal_entry
    WHERE PIN = '105'
      AND reference_type = 'savings_interest_repair'
      AND description LIKE '%REPAIR-LO43QPOD0001-SV00002764%'
      AND (is_voided IS NULL OR is_voided = 0);
    IF v_cnt <> 0 THEN
        SET @repair_message = 'Repair marker appeared while this lock was waited';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Repair marker appeared while this lock was waited';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM general_ledger WHERE id = 3830 AND PIN = 105 AND account = 71100 AND ABS(debit - 64.61) < 0.005 AND refferenceID = 'LO43QPOD0001';
    IF v_cnt <> 1 THEN
        SET @repair_message = 'Source GL line changed while locked';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Source GL line changed while locked';
    END IF;

    INSERT INTO journal_entry (
        entry_date, description, reference_type, reference_id, createdby, PIN, created_at, is_voided
    ) VALUES (
        '2022-02-28',
        'REPAIR-LO43QPOD0001-SV00002764 PIN105 DR11100 CR71100 64.61 dated 2022-02-28. Savings liability 21110 unchanged.',
        'savings_interest_repair',
        NULL,
        CAST(@actor_user_id AS UNSIGNED),
        '105',
        NOW(),
        0
    );
    SET v_je_id = LAST_INSERT_ID();
    IF v_je_id IS NULL OR v_je_id = 0 OR v_je_id IN (39755, 169383) THEN
        SET @repair_message = 'New journal header id is unusable';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'New journal header id is unusable';
    END IF;

    INSERT INTO journal_entry_items (
        journal_id, reference_type, account, debit, credit, description, PIN
    ) VALUES (
        v_je_id,
        'savings_interest_repair',
        '11100',
        64.61,
        0.00,
        'Repair 64.61 DR11100 CR71100 LO43QPOD0001 SV00002764',
        '105'
    );
    INSERT INTO journal_entry_items (
        journal_id, reference_type, account, debit, credit, description, PIN
    ) VALUES (
        v_je_id,
        'savings_interest_repair',
        '71100',
        0.00,
        64.61,
        'Repair 64.61 DR11100 CR71100 LO43QPOD0001 SV00002764',
        '105'
    );

    INSERT INTO general_ledger_entry (`date`, PIN) VALUES ('2022-02-28', 105);
    SET v_glh_id = LAST_INSERT_ID();
    IF v_glh_id IS NULL OR v_glh_id = 0 OR v_glh_id IN (39755, 169383) THEN
        SET @repair_message = 'New GL header id collides with a legacy entry id';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'New GL header id collides with a legacy entry id';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE TRIM(entryid) = CAST(v_glh_id AS CHAR);
    IF v_cnt <> 0 THEN
        SET @repair_message = 'New GL header id is already a detail entryid';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'New GL header id is already a detail entryid';
    END IF;

    INSERT INTO general_ledger (
        entryid, refferenceID, journalID, `date`, account, description, credit, debit,
        linkto, fromtable, PID, member_id, customerid, supplierid, balance,
        account_type, sub_account_type, paid, invoiceid, LID, PIN
    ) VALUES (
        CAST(v_glh_id AS CHAR), CAST(v_je_id AS CHAR), 5, '2022-02-28', 11100,
        'Repair 64.61 DR11100 CR71100 LO43QPOD0001 SV00002764',
        0, 64.61, 'journal_entry.id', 'journal_entry',
        0, '', '', '', 0, 10000, 11000, 1, 0, '', 105
    );
    SET v_line_dr = LAST_INSERT_ID();

    INSERT INTO general_ledger (
        entryid, refferenceID, journalID, `date`, account, description, credit, debit,
        linkto, fromtable, PID, member_id, customerid, supplierid, balance,
        account_type, sub_account_type, paid, invoiceid, LID, PIN
    ) VALUES (
        CAST(v_glh_id AS CHAR), CAST(v_je_id AS CHAR), 5, '2022-02-28', 71100,
        'Repair 64.61 DR11100 CR71100 LO43QPOD0001 SV00002764',
        64.61, 0, 'journal_entry.id', 'journal_entry',
        0, '', '', '', 0, 70000, 71000, 1, 0, '', 105
    );
    SET v_line_cr = LAST_INSERT_ID();

    SELECT ROUND(COALESCE(SUM(debit), 0), 2), ROUND(COALESCE(SUM(credit), 0), 2)
        INTO v_pair_11100, v_pair_71100
    FROM general_ledger
    WHERE id IN (v_line_dr, v_line_cr);
    IF ABS(v_pair_11100 - 64.61) >= 0.01 OR ABS(v_pair_71100 - 64.61) >= 0.01 THEN
        SET @repair_message = 'New journal does not balance at 64.61';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'New journal does not balance at 64.61';
    END IF;

    SELECT
        ROUND(COALESCE(SUM(CASE WHEN account = 71100 THEN debit - credit ELSE 0 END), 0), 2),
        ROUND(COALESCE(SUM(CASE WHEN account = 11100 THEN debit - credit ELSE 0 END), 0), 2),
        ROUND(COALESCE(SUM(CASE WHEN account = 21110 THEN debit - credit ELSE 0 END), 0), 2)
        INTO v_pair_71100, v_pair_11100, v_pair_21110
    FROM general_ledger
    WHERE PIN = 105
      AND (
            id IN (3830, 3831, 11029, 11030, v_line_dr, v_line_cr)
          );
    IF ABS(v_pair_71100) >= 0.01 OR ABS(v_pair_11100) >= 0.01 OR ABS(v_pair_21110) >= 0.01 THEN
        SET @repair_message = 'Pair residual is not zero after the repair';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pair residual is not zero after the repair';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) INTO v_window
    FROM general_ledger
    WHERE PIN = 105 AND account = 71100 AND `date` >= '2021-11-01' AND `date` <= '2024-12-31';
    IF ABS(v_window - 56.47) >= 0.01 THEN
        SET @repair_message = '71100 window net is not 56.47 after the repair';
        SET @repair_window_net = v_window;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '71100 window net is not 56.47 after the repair';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM general_ledger WHERE id IN (3830, 3831, 11029, 11030) AND PIN = 105 AND (
        (id = 3830 AND account = 71100 AND ABS(debit - 64.61) < 0.005) OR
        (id = 3831 AND account = 21110 AND ABS(credit - 64.61) < 0.005) OR
        (id = 11029 AND account = 21110 AND ABS(debit - 64.61) < 0.005) OR
        (id = 11030 AND account = 11100 AND ABS(credit - 64.61) < 0.005)
    );
    IF v_cnt <> 4 THEN
        SET @repair_message = 'Original four GL lines changed inside the transaction';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Original four GL lines changed inside the transaction';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM savings_transaction WHERE PIN = 105 AND id IN (606, 2764) AND (
        (id = 606 AND receipt = 'LO43QPOD0001' AND ABS(amount - 64.61) < 0.005) OR
        (id = 2764 AND receipt = 'SV00002764' AND ABS(amount - 64.61) < 0.005)
    );
    IF v_cnt <> 2 OR v_sav_ids <> 2 THEN
        SET @repair_message = 'Savings source or void row changed';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Savings source or void row changed';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0), 2), ROUND(COALESCE(SUM(credit), 0), 2)
        INTO v_pair_11100, v_pair_71100
    FROM general_ledger
    WHERE PIN = 105 AND account = 21110;
    IF ABS(v_pair_11100 - v_liab_debit) >= 0.01 OR ABS(v_pair_71100 - v_liab_credit) >= 0.01 THEN
        SET @repair_message = 'Account 21110 totals changed';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Account 21110 totals changed';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE PIN = 105
      AND account = 71100
      AND id NOT IN (3830, 3831, 11029, 11030, v_line_dr, v_line_cr)
      AND (ABS(debit - 36.24) < 0.005 OR ABS(debit - 20.23) < 0.005);
    IF v_cnt <> v_other_interest THEN
        SET @repair_message = '36.24 or 20.23 interest lines changed';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '36.24 or 20.23 interest lines changed';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM general_journal_entry;
    IF v_cnt <> v_gj_count THEN
        SET @repair_message = 'general_journal_entry changed';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'general_journal_entry changed';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'general_journal';
    IF v_cnt = 1 THEN
        SELECT COUNT(*) INTO v_cnt FROM general_journal;
        IF v_cnt <> v_gj_lines THEN
            SET @repair_message = 'general_journal lines changed';
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'general_journal lines changed';
        END IF;
    END IF;

    IF v_has_ma = 1 THEN
        IF v_has_ma_balance = 1 THEN
            SELECT COUNT(*), ROUND(COALESCE(SUM(balance), 0), 2)
                INTO v_cnt, v_pair_71100
            FROM members_account
            WHERE CAST(PIN AS UNSIGNED) = 105
              AND CAST(account AS CHAR) = '24';
            IF v_cnt <> v_ma_count OR ABS(v_pair_71100 - v_ma_balance) >= 0.01 THEN
                SET @repair_message = 'members_account balance changed';
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'members_account balance changed';
            END IF;
        ELSE
            SELECT COUNT(*) INTO v_cnt
            FROM members_account
            WHERE CAST(PIN AS UNSIGNED) = 105
              AND CAST(account AS CHAR) = '24';
            IF v_cnt <> v_ma_count THEN
                SET @repair_message = 'members_account rows changed';
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'members_account rows changed';
            END IF;
        END IF;
        SET @repair_member_unchanged = 1;
    ELSE
        SET @repair_member_unchanged = NULL;
    END IF;

    COMMIT;
    SET @repair_tx = 0;
    SET @repair_status = 'APPLIED';
    SET @repair_message = 'Posted DR11100 CR71100 64.61 dated 2022-02-28';
    SET @repair_journal_entry_id = v_je_id;
    SET @repair_gl_entry_id = v_glh_id;
    SET @repair_gl_debit_line_id = v_line_dr;
    SET @repair_gl_credit_line_id = v_line_cr;
    SET @repair_window_net = v_window;
    SET @repair_pair_71100 = 0;
    SET @repair_pair_11100 = 0;
    SET @repair_pair_21110 = 0;
    SET @repair_lock_release = RELEASE_LOCK('tapstemco_repair_pin105_lo43qpod0001');
END proc$$

DELIMITER ;

CALL repair_pin105_lo43qpod0001();

SELECT
    @repair_status AS repair_status,
    @repair_message AS repair_message,
    @repair_errno AS repair_errno,
    @repair_journal_entry_id AS journal_entry_id,
    @repair_gl_entry_id AS general_ledger_entry_id,
    @repair_gl_debit_line_id AS gl_debit_11100_id,
    @repair_gl_credit_line_id AS gl_credit_71100_id,
    @repair_window_net AS window_71100_net,
    @repair_pair_71100 AS pair_71100_net,
    @repair_pair_11100 AS pair_11100_net,
    @repair_pair_21110 AS pair_21110_net,
    @repair_member_unchanged AS member_unchanged;

DROP PROCEDURE IF EXISTS repair_pin105_lo43qpod0001;
