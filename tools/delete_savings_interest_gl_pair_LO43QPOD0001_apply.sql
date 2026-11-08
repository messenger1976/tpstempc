-- DELETE only the balanced general_ledger pair for receipts
-- LO43QPOD0001 and SV00002764, amount 64.61, date 2022-02-28.
-- This is not the correction script repair_pin105_lo43qpod0001_apply.sql.
-- Savings rows, member rows, the chart, and every other GL line stay.
--
-- Sourcing this file with the variables unset creates and drops a routine
-- and deletes nothing. CREATE PROCEDURE and DROP PROCEDURE are DDL.
-- MariaDB implicit-commits them. They need CREATE ROUTINE and DROP ROUTINE.
-- They are not undone by the financial ROLLBACK. There is no table DDL.
--
-- The DELETE runs only inside one InnoDB transaction, and only when you set
-- every value below in this same session after reading the preflight.
-- This file does not assign them. Do not set them until the backup file has
-- been checked by hand and a person has approved changing reports from
-- 2022-02-28. There is no override flag.
--
--   SET @expected_database := 'YOUR_DATABASE_NAME';
--   SET @expected_pin := <PIN you intend>;
--   SET @expect_expense_account := <expense account from preflight>;
--   SET @expect_liability_account := <liability account from preflight>;
--   SET @expect_offset_account := <void credit account from preflight>;
--   SET @confirm_source_receipt := 'LO43QPOD0001';
--   SET @confirm_void_receipt := 'SV00002764';
--   SET @confirm_backup := 'BACKUP-TAKEN-BEFORE-GL-PAIR-DELETE';
--   SET @confirm_historical_effect := 'APPROVE-DELETE-AFFECTS-REPORTS-FROM-2022-02-28';
--   SET @confirm_delete := 'DELETE-GL-PAIR-LO43QPOD0001-SV00002764';
--
-- Backup is outside this file. Database name is the first name after --tables.
-- The backup token does not create or check a file.
--   mysqldump [options] --tables YOUR_DATABASE_NAME account_chart fiscal_year general_journal_entry general_ledger general_ledger_entry gl_books_close journal_entry journal_entry_items savings_transaction > backup.sql

DROP PROCEDURE IF EXISTS delete_gl_pair_lo43qpod0001;

DELIMITER $$

CREATE PROCEDURE delete_gl_pair_lo43qpod0001()
SQL SECURITY INVOKER
proc: BEGIN
    DECLARE v_pin BIGINT DEFAULT 0;
    DECLARE v_expense BIGINT DEFAULT 0;
    DECLARE v_liability BIGINT DEFAULT 0;
    DECLARE v_offset BIGINT DEFAULT 0;
    DECLARE v_cnt INT DEFAULT 0;
    DECLARE v_id_exp BIGINT DEFAULT NULL;
    DECLARE v_id_liab_src BIGINT DEFAULT NULL;
    DECLARE v_id_liab_void BIGINT DEFAULT NULL;
    DECLARE v_id_off BIGINT DEFAULT NULL;
    DECLARE v_entry_src VARCHAR(50) DEFAULT NULL;
    DECLARE v_entry_void VARCHAR(50) DEFAULT NULL;
    DECLARE v_deleted INT DEFAULT 0;
    DECLARE v_closed DATE DEFAULT NULL;
    DECLARE v_before_dr DECIMAL(18, 2) DEFAULT 0;
    DECLARE v_before_cr DECIMAL(18, 2) DEFAULT 0;
    DECLARE v_after_dr DECIMAL(18, 2) DEFAULT 0;
    DECLARE v_after_cr DECIMAL(18, 2) DEFAULT 0;
    DECLARE v_window_before DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_window_after DECIMAL(18, 2) DEFAULT NULL;
    DECLARE v_other_kept INT DEFAULT 0;
    DECLARE v_sav_src INT DEFAULT 0;
    DECLARE v_sav_void INT DEFAULT 0;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_cnt = v_cnt;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            @delete_pair_errno = MYSQL_ERRNO,
            @delete_pair_diag = MESSAGE_TEXT;
        IF @delete_pair_tx = 1 THEN
            ROLLBACK;
            SET @delete_pair_tx = 0;
            SET @delete_pair_status = 'ROLLED_BACK';
        ELSE
            SET @delete_pair_status = 'REFUSED';
        END IF;
        IF @delete_pair_message IS NULL OR @delete_pair_message = '' THEN
            SET @delete_pair_message = @delete_pair_diag;
        END IF;
        SET @delete_pair_lock_release = RELEASE_LOCK('delete_gl_pair_LO43QPOD0001');
    END;

    SET @delete_pair_tx = 0;
    SET @delete_pair_status = 'REFUSED';
    SET @delete_pair_message = NULL;
    SET @delete_pair_errno = NULL;
    SET @delete_pair_diag = NULL;
    SET @delete_pair_rows = NULL;
    SET @delete_pair_window_before = NULL;
    SET @delete_pair_window_after = NULL;

    SET @delete_pair_lock = GET_LOCK('delete_gl_pair_LO43QPOD0001', 10);
    IF @delete_pair_lock IS NULL OR @delete_pair_lock <> 1 THEN
        SET @delete_pair_message = 'Delete lock not acquired';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete lock not acquired';
    END IF;

    IF NOT (
        @confirm_delete <=> 'DELETE-GL-PAIR-LO43QPOD0001-SV00002764'
        AND @confirm_backup <=> 'BACKUP-TAKEN-BEFORE-GL-PAIR-DELETE'
        AND @confirm_historical_effect <=> 'APPROVE-DELETE-AFFECTS-REPORTS-FROM-2022-02-28'
        AND @confirm_source_receipt <=> 'LO43QPOD0001'
        AND @confirm_void_receipt <=> 'SV00002764'
        AND @expected_database <=> DATABASE()
        AND CAST(IFNULL(@expected_pin, 0) AS UNSIGNED) > 0
        AND CAST(IFNULL(@expect_expense_account, 0) AS UNSIGNED) > 0
        AND CAST(IFNULL(@expect_liability_account, 0) AS UNSIGNED) > 0
        AND CAST(IFNULL(@expect_offset_account, 0) AS UNSIGNED) > 0
    ) THEN
        SET @delete_pair_status = 'PREFLIGHT_ONLY';
        SET @delete_pair_message = 'Delete flags, database, PIN, or accounts are unset. No rows deleted.';
        SET @delete_pair_lock_release = RELEASE_LOCK('delete_gl_pair_LO43QPOD0001');
        LEAVE proc;
    END IF;

    SET v_pin = CAST(@expected_pin AS UNSIGNED);
    SET v_expense = CAST(@expect_expense_account AS UNSIGNED);
    SET v_liability = CAST(@expect_liability_account AS UNSIGNED);
    SET v_offset = CAST(@expect_offset_account AS UNSIGNED);
    IF v_expense = v_liability OR v_expense = v_offset OR v_liability = v_offset THEN
        SET @delete_pair_message = 'Expense, liability, and offset accounts must be three different accounts';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Expense, liability, and offset accounts must differ';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME IN ('general_ledger', 'savings_transaction', 'gl_books_close')
      AND ENGINE = 'InnoDB';
    IF v_cnt <> 3 THEN
        SET @delete_pair_message = 'general_ledger, savings_transaction, and gl_books_close must be InnoDB';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Required tables are missing or not InnoDB';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.TRIGGERS
    WHERE EVENT_OBJECT_SCHEMA = DATABASE()
      AND EVENT_OBJECT_TABLE IN ('general_ledger', 'general_ledger_entry', 'savings_transaction');
    IF v_cnt <> 0 THEN
        SET @delete_pair_message = 'Triggers exist on a table this delete locks; refusing';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Triggers exist on a locked table; refusing';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
      AND REFERENCED_TABLE_NAME = 'general_ledger';
    IF v_cnt <> 0 THEN
        SET @delete_pair_message = 'A foreign key references general_ledger; refusing';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A foreign key references general_ledger; refusing';
    END IF;

    SELECT COUNT(*) INTO v_cnt FROM gl_books_close WHERE CAST(PIN AS UNSIGNED) = v_pin;
    IF v_cnt > 0 THEN
        SELECT closed_as_of INTO v_closed
        FROM gl_books_close
        WHERE CAST(PIN AS UNSIGNED) = v_pin
        ORDER BY id DESC
        LIMIT 1;
    END IF;
    IF v_closed IS NOT NULL AND v_closed >= '2022-02-28' THEN
        SET @delete_pair_message = 'Books are closed through 2022-02-28 or later; no bypass';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Books are closed through 2022-02-28 or later; no bypass';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM journal_entry
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND (
            reference_type = 'savings_interest_repair'
            OR description LIKE '%REPAIR-LO43QPOD0001-SV00002764%'
            OR description LIKE '%LO43QPOD0001%'
          );
    IF v_cnt <> 0 THEN
        SET @delete_pair_message = 'A journal marker already mentions this pair; refusing';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A journal marker already mentions this pair; refusing';
    END IF;

    SELECT COUNT(*) INTO v_sav_src
    FROM savings_transaction
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND receipt = 'LO43QPOD0001'
      AND trans_type = 'CR'
      AND UPPER(system_comment) = 'INTEREST'
      AND UPPER(paymethod) = 'ADJUSTMENT'
      AND DATE(trans_date) = '2022-02-28'
      AND ABS(amount - 64.61) < 0.005
      AND UPPER(IFNULL(comment, '')) NOT LIKE 'VOID-%';
    SELECT COUNT(*) INTO v_sav_void
    FROM savings_transaction
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND receipt = 'SV00002764'
      AND trans_type = 'DR'
      AND UPPER(system_comment) LIKE 'VOID TRANSACTION%'
      AND comment LIKE 'VOID-LO43QPOD0001 -%'
      AND UPPER(paymethod) = 'ADJUSTMENT'
      AND DATE(trans_date) = '2022-02-28'
      AND ABS(amount - 64.61) < 0.005;
    IF v_sav_src <> 1 OR v_sav_void <> 1 THEN
        SET @delete_pair_message = 'Savings source and void pair cannot be verified';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Savings source and void pair cannot be verified';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM savings_transaction s
    INNER JOIN savings_transaction v
        ON CAST(v.PIN AS UNSIGNED) = CAST(s.PIN AS UNSIGNED)
       AND v.receipt = 'SV00002764'
       AND CAST(v.account AS CHAR) = CAST(s.account AS CHAR)
    WHERE CAST(s.PIN AS UNSIGNED) = v_pin
      AND s.receipt = 'LO43QPOD0001';
    IF v_cnt <> 1 THEN
        SET @delete_pair_message = 'Source and void are not the same savings account';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Source and void are not the same savings account';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID IN ('LO43QPOD0001', 'SV00002764');
    IF v_cnt = 0 THEN
        SET @delete_pair_status = 'ALREADY_REMOVED';
        SET @delete_pair_message = 'No GL rows remain for these receipts. Nothing deleted.';
        SET @delete_pair_rows = 0;
        SET @delete_pair_lock_release = RELEASE_LOCK('delete_gl_pair_LO43QPOD0001');
        LEAVE proc;
    END IF;
    IF v_cnt <> 4 THEN
        SET @delete_pair_message = 'These receipts do not have exactly four GL rows';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'These receipts do not have exactly four GL rows';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'LO43QPOD0001'
      AND fromtable = 'savings_transaction'
      AND linkto = 'savings_transaction.receipt'
      AND journalID = 5
      AND `date` = '2022-02-28'
      AND account = v_expense
      AND ABS(debit - 64.61) < 0.005
      AND ABS(credit) < 0.005;
    IF v_cnt <> 1 THEN
        SET @delete_pair_message = 'Source expense debit 64.61 was not found';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Source expense debit 64.61 was not found';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'LO43QPOD0001'
      AND fromtable = 'savings_transaction'
      AND linkto = 'savings_transaction.receipt'
      AND journalID = 5
      AND `date` = '2022-02-28'
      AND account = v_liability
      AND ABS(credit - 64.61) < 0.005
      AND ABS(debit) < 0.005;
    IF v_cnt <> 1 THEN
        SET @delete_pair_message = 'Source liability credit 64.61 was not found';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Source liability credit 64.61 was not found';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'SV00002764'
      AND fromtable = 'savings_transaction'
      AND linkto = 'savings_transaction.receipt'
      AND journalID = 5
      AND `date` = '2022-02-28'
      AND account = v_liability
      AND ABS(debit - 64.61) < 0.005
      AND ABS(credit) < 0.005;
    IF v_cnt <> 1 THEN
        SET @delete_pair_message = 'Void liability debit 64.61 was not found';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Void liability debit 64.61 was not found';
    END IF;
    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'SV00002764'
      AND fromtable = 'savings_transaction'
      AND linkto = 'savings_transaction.receipt'
      AND journalID = 5
      AND `date` = '2022-02-28'
      AND account = v_offset
      AND ABS(credit - 64.61) < 0.005
      AND ABS(debit) < 0.005;
    IF v_cnt <> 1 THEN
        SET @delete_pair_message = 'Void offset credit 64.61 was not found';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Void offset credit 64.61 was not found';
    END IF;

    SELECT id, entryid INTO v_id_exp, v_entry_src
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'LO43QPOD0001'
      AND account = v_expense
      AND ABS(debit - 64.61) < 0.005
    LIMIT 1;
    SELECT id INTO v_id_liab_src
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'LO43QPOD0001'
      AND account = v_liability
      AND ABS(credit - 64.61) < 0.005
      AND TRIM(entryid) = TRIM(v_entry_src)
    LIMIT 1;
    SELECT id, entryid INTO v_id_liab_void, v_entry_void
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'SV00002764'
      AND account = v_liability
      AND ABS(debit - 64.61) < 0.005
    LIMIT 1;
    SELECT id INTO v_id_off
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID = 'SV00002764'
      AND account = v_offset
      AND ABS(credit - 64.61) < 0.005
      AND TRIM(entryid) = TRIM(v_entry_void)
    LIMIT 1;
    IF v_id_exp IS NULL OR v_id_liab_src IS NULL OR v_id_liab_void IS NULL OR v_id_off IS NULL
        OR v_entry_src IS NULL OR v_entry_void IS NULL
        OR TRIM(v_entry_src) = TRIM(v_entry_void) THEN
        SET @delete_pair_message = 'The four lines do not form two distinct entry groups';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'The four lines do not form two distinct entry groups';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE id NOT IN (v_id_exp, v_id_liab_src, v_id_liab_void, v_id_off)
      AND TRIM(entryid) IN (TRIM(v_entry_src), TRIM(v_entry_void));
    IF v_cnt <> 0 THEN
        SET @delete_pair_message = 'Another GL row shares an entry id with this pair';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Another GL row shares an entry id with this pair';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) INTO v_window_before
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND account = v_expense
      AND `date` >= '2021-11-01'
      AND `date` <= '2024-12-31';
    SELECT COUNT(*) INTO v_other_kept
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND account = v_expense
      AND id NOT IN (v_id_exp, v_id_liab_src, v_id_liab_void, v_id_off)
      AND (ABS(debit - 36.24) < 0.005 OR ABS(debit - 20.23) < 0.005);

    SET @delete_pair_tx = 1;
    START TRANSACTION;

    SELECT id INTO v_cnt FROM savings_transaction
    WHERE CAST(PIN AS UNSIGNED) = v_pin AND receipt = 'LO43QPOD0001' FOR UPDATE;
    SELECT id INTO v_cnt FROM savings_transaction
    WHERE CAST(PIN AS UNSIGNED) = v_pin AND receipt = 'SV00002764' FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = v_id_exp AND CAST(PIN AS UNSIGNED) = v_pin FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = v_id_liab_src AND CAST(PIN AS UNSIGNED) = v_pin FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = v_id_liab_void AND CAST(PIN AS UNSIGNED) = v_pin FOR UPDATE;
    SELECT id INTO v_cnt FROM general_ledger WHERE id = v_id_off AND CAST(PIN AS UNSIGNED) = v_pin FOR UPDATE;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID IN ('LO43QPOD0001', 'SV00002764');
    IF v_cnt <> 4 THEN
        SET @delete_pair_message = 'GL row count changed while locked';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GL row count changed while locked';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0), 2), ROUND(COALESCE(SUM(credit), 0), 2)
        INTO v_before_dr, v_before_cr
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin;

    DELETE FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND id IN (v_id_exp, v_id_liab_src, v_id_liab_void, v_id_off)
      AND refferenceID IN ('LO43QPOD0001', 'SV00002764')
      AND fromtable = 'savings_transaction'
      AND linkto = 'savings_transaction.receipt'
      AND journalID = 5
      AND `date` = '2022-02-28';
    SET v_deleted = ROW_COUNT();
    IF v_deleted <> 4 THEN
        SET @delete_pair_message = 'Delete did not remove exactly four GL rows';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete did not remove exactly four GL rows';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND refferenceID IN ('LO43QPOD0001', 'SV00002764');
    IF v_cnt <> 0 THEN
        SET @delete_pair_message = 'GL rows for these receipts remain after delete';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'GL rows for these receipts remain after delete';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0), 2), ROUND(COALESCE(SUM(credit), 0), 2)
        INTO v_after_dr, v_after_cr
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin;
    IF ABS((v_before_dr - v_before_cr) - (v_after_dr - v_after_cr)) >= 0.01
        OR ABS((v_before_dr - v_after_dr) - 129.22) >= 0.01
        OR ABS((v_before_cr - v_after_cr) - 129.22) >= 0.01 THEN
        SET @delete_pair_message = 'PIN ledger balance changed by more than the balanced pair';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'PIN ledger balance changed by more than the balanced pair';
    END IF;

    SELECT ROUND(COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0), 2) INTO v_window_after
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND account = v_expense
      AND `date` >= '2021-11-01'
      AND `date` <= '2024-12-31';
    IF ABS((v_window_before - v_window_after) - 64.61) >= 0.01 THEN
        SET @delete_pair_message = 'Expense window did not fall by exactly 64.61';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Expense window did not fall by exactly 64.61';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM general_ledger
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND account = v_expense
      AND (ABS(debit - 36.24) < 0.005 OR ABS(debit - 20.23) < 0.005);
    IF v_cnt <> v_other_kept THEN
        SET @delete_pair_message = '36.24 or 20.23 expense lines changed';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '36.24 or 20.23 expense lines changed';
    END IF;

    SELECT COUNT(*) INTO v_cnt
    FROM savings_transaction
    WHERE CAST(PIN AS UNSIGNED) = v_pin
      AND (
            (receipt = 'LO43QPOD0001' AND trans_type = 'CR' AND ABS(amount - 64.61) < 0.005)
            OR (receipt = 'SV00002764' AND trans_type = 'DR' AND ABS(amount - 64.61) < 0.005)
          );
    IF v_cnt <> 2 OR v_sav_src <> 1 OR v_sav_void <> 1 THEN
        SET @delete_pair_message = 'Savings source or void row changed';
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Savings source or void row changed';
    END IF;

    COMMIT;
    SET @delete_pair_tx = 0;
    SET @delete_pair_status = 'DELETED';
    SET @delete_pair_message = 'Deleted four GL rows for LO43QPOD0001 and SV00002764';
    SET @delete_pair_rows = 4;
    SET @delete_pair_window_before = v_window_before;
    SET @delete_pair_window_after = v_window_after;
    SET @delete_pair_lock_release = RELEASE_LOCK('delete_gl_pair_LO43QPOD0001');
END proc$$

DELIMITER ;

CALL delete_gl_pair_lo43qpod0001();

SELECT
    @delete_pair_status AS delete_pair_status,
    @delete_pair_message AS delete_pair_message,
    @delete_pair_errno AS delete_pair_errno,
    @delete_pair_rows AS rows_deleted,
    @delete_pair_window_before AS expense_window_before,
    @delete_pair_window_after AS expense_window_after;

DROP PROCEDURE IF EXISTS delete_gl_pair_lo43qpod0001;
