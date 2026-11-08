#!/bin/bash
# Disposable tests for the GL-pair deletion scripts. No database named tapstemco.
set -u
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PREFLIGHT="$ROOT/tools/delete_savings_interest_gl_pair_LO43QPOD0001_preflight.sql"
APPLY="$ROOT/tools/delete_savings_interest_gl_pair_LO43QPOD0001_apply.sql"
DB="delete_gl_pair_test"
SOCK="${REPAIR_SQL_SOCK:-/var/run/mysqld/mysqld.sock}"
FAILS=0

mysql_admin() {
    sudo mysql --socket="$SOCK" --batch --raw --skip-column-names "$@"
}

check() {
    local label="$1"
    local cond="$2"
    if eval "$cond"; then
        echo "ok  $label"
    else
        echo "FAIL $label"
        FAILS=$((FAILS + 1))
    fi
}

sql_code() {
    sed -E 's/--.*$//' "$1"
}

status_of() {
    printf '%s\n' "$1" | awk -F '\t' '$1=="delete_pair_status" {next} $1 ~ /^(PREFLIGHT_ONLY|DELETED|ALREADY_REMOVED|REFUSED|ROLLED_BACK)$/ {print $1; exit}'
}

assert_status() {
    local label="$1"
    local output="$2"
    local want="$3"
    local got
    got="$(status_of "$output")"
    if [[ "$got" == "$want" ]]; then
        echo "ok  $label"
    else
        echo "FAIL $label (got ${got:-empty})"
        FAILS=$((FAILS + 1))
    fi
}

setup_db() {
    mysql_admin -e "DROP DATABASE IF EXISTS ${DB}; CREATE DATABASE ${DB} CHARACTER SET latin1 COLLATE latin1_swedish_ci;"
    mysql_admin "$DB" <<'SQL'
SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
CREATE TABLE general_ledger (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  entryid varchar(50) NOT NULL,
  refferenceID varchar(50) NOT NULL,
  journalID int(11) NOT NULL,
  `date` date NOT NULL,
  account bigint(20) NOT NULL,
  description varchar(200) NOT NULL,
  credit double NOT NULL,
  debit double NOT NULL,
  linkto varchar(100) NOT NULL,
  fromtable varchar(50) NOT NULL,
  PID bigint(20) NOT NULL,
  member_id varchar(50) NOT NULL,
  customerid varchar(50) NOT NULL,
  supplierid varchar(50) NOT NULL,
  balance double NOT NULL,
  account_type int(11) NOT NULL,
  sub_account_type bigint(20) NOT NULL,
  paid int(11) NOT NULL DEFAULT 1,
  invoiceid int(11) NOT NULL,
  LID varchar(50) NOT NULL,
  PIN bigint(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE TABLE general_ledger_entry (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  PIN bigint(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE TABLE gl_books_close (
  id int(11) NOT NULL AUTO_INCREMENT,
  PIN bigint(20) NOT NULL,
  closed_as_of date DEFAULT NULL,
  action varchar(32) NOT NULL DEFAULT 'set',
  created_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE TABLE journal_entry (
  id int(11) NOT NULL AUTO_INCREMENT,
  entry_date date NOT NULL,
  description text DEFAULT NULL,
  reference_type varchar(50) DEFAULT NULL,
  createdby int(11) NOT NULL,
  PIN varchar(20) NOT NULL,
  created_at datetime NOT NULL,
  is_voided tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE TABLE savings_transaction (
  id int(11) NOT NULL,
  receipt varchar(50) NOT NULL,
  account varchar(20) NOT NULL,
  trans_type varchar(10) NOT NULL,
  paymethod varchar(30) NOT NULL,
  trans_date datetime NOT NULL,
  amount double NOT NULL,
  system_comment varchar(255) NOT NULL,
  comment varchar(255) NOT NULL DEFAULT '',
  PIN bigint(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;
CREATE TABLE fiscal_year (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  status tinyint(1) NOT NULL DEFAULT 0,
  PIN varchar(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO fiscal_year (name, start_date, end_date, status, PIN)
VALUES ('FY 2022 fixture', '2022-01-01', '2022-12-31', 1, '105');

INSERT INTO savings_transaction (id, receipt, account, trans_type, paymethod, trans_date, amount, system_comment, comment, PIN)
VALUES
(4101, 'LO43QPOD0001', '24', 'CR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 'INTEREST', 'fixture interest', 105),
(4102, 'SV00002764', '24', 'DR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 'VOID TRANSACTION | ORIG_TYPE:CR | ORIG_METHOD:ADJU', 'VOID-LO43QPOD0001 - fixture', 105),
(4201, 'LO43QPOD0001', '24', 'CR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 'INTEREST', 'other pin', 222),
(4202, 'SV00002764', '24', 'DR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 'VOID TRANSACTION | ORIG_TYPE:CR', 'VOID-LO43QPOD0001 - other pin', 222),
(4301, 'FXKEEP6461', '24', 'CR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 'INTEREST', 'different receipt', 105);

INSERT INTO general_ledger (
  id, entryid, refferenceID, journalID, `date`, account, description, credit, debit,
  linkto, fromtable, PID, member_id, customerid, supplierid, balance,
  account_type, sub_account_type, paid, invoiceid, LID, PIN
) VALUES
(71001, '81001', 'LO43QPOD0001', 5, '2022-02-28', 71100, 'fixture source debit', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(71002, '81001', 'LO43QPOD0001', 5, '2022-02-28', 21110, 'fixture source credit', 64.61, 0, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 20000, 21000, 1, 0, '', 105),
(71003, '81002', 'SV00002764', 5, '2022-02-28', 21110, 'fixture void debit', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 20000, 21000, 1, 0, '', 105),
(71004, '81002', 'SV00002764', 5, '2022-02-28', 11100, 'fixture void credit', 64.61, 0, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 10000, 11000, 1, 0, '', 105),
(72001, '82001', 'FX36P2400001', 5, '2024-12-31', 71100, 'fixture 36.24', 0, 36.24, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(72002, '82002', 'FX20P2300001', 5, '2024-12-31', 71100, 'fixture 20.23', 0, 20.23, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(72003, '82003', 'FXKEEP6461', 5, '2025-06-01', 71100, 'other receipt same amount', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(73001, '91001', 'LO43QPOD0001', 5, '2022-02-28', 71100, 'other pin', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 222),
(73002, '91001', 'LO43QPOD0001', 5, '2022-02-28', 21110, 'other pin', 64.61, 0, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 20000, 21000, 1, 0, '', 222),
(73003, '91002', 'SV00002764', 5, '2022-02-28', 21110, 'other pin', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 20000, 21000, 1, 0, '', 222),
(73004, '91002', 'SV00002764', 5, '2022-02-28', 11100, 'other pin', 64.61, 0, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 10000, 11000, 1, 0, '', 222);
SQL
}

FLAGS="SET @expected_database='${DB}'; SET @expected_pin=105; SET @expect_expense_account=71100; SET @expect_liability_account=21110; SET @expect_offset_account=11100; SET @confirm_source_receipt='LO43QPOD0001'; SET @confirm_void_receipt='SV00002764'; SET @confirm_backup='BACKUP-TAKEN-BEFORE-GL-PAIR-DELETE'; SET @confirm_historical_effect='APPROVE-DELETE-AFFECTS-REPORTS-FROM-2022-02-28'; SET @confirm_delete='DELETE-GL-PAIR-LO43QPOD0001-SV00002764';"

run_apply() {
    sudo mysql --socket="$SOCK" --batch --raw "$DB" --init-command="SET SESSION sql_mode='NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'; $1" < "$APPLY"
}

pair_count() {
    mysql_admin "$DB" -e "SELECT COUNT(*) FROM general_ledger WHERE PIN=105 AND refferenceID IN ('LO43QPOD0001','SV00002764');"
}

if [[ "${1:-}" == "--setup-only" ]]; then
    setup_db
    exit 0
fi

echo "server $(mysql_admin -e 'SELECT VERSION();')"
check "preflight executable text has no writes" \
    "! sql_code '$PREFLIGHT' | grep -Eiq '(^|[^A-Za-z_])(INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE)([^A-Za-z_]|$)'"
check "apply does not delete savings rows" \
    "! sql_code '$APPLY' | grep -Eiq 'DELETE[[:space:]]+FROM[[:space:]]+savings_transaction'"

setup_db
pre_out=$(sudo mysql --socket="$SOCK" --batch --raw "$DB" --init-command="SET @expected_database='${DB}'; SET @expected_pin=105; SET @expect_expense_account=71100;" < "$PREFLIGHT")
check "preflight shows the synthetic source receipt" "printf '%s' \"$pre_out\" | grep -q 'LO43QPOD0001'"
check "preflight window starts at 121.08" "printf '%s' \"$pre_out\" | grep -q '121.08'"
check "preflight reports no header" "printf '%s' \"$pre_out\" | grep -q 'NO_HEADER_FOR_THESE_ENTRYIDS'"

preview=$(run_apply "")
assert_status "unset flags do not delete" "$preview" PREFLIGHT_ONLY
check "preview leaves the four rows" "[[ \"$(pair_count)\" == 4 ]]"

before_net=$(mysql_admin "$DB" -e "SELECT ROUND(SUM(debit)-SUM(credit),2) FROM general_ledger WHERE PIN=105;")
ok_out=$(run_apply "$FLAGS")
assert_status "different ids are deleted" "$ok_out" DELETED
check "expense window reported 56.47" "printf '%s' \"$ok_out\" | grep -q $'\t56.47'"
check "pair rows are gone" "[[ \"$(pair_count)\" == 0 ]]"
check "savings rows remain" "[[ \"$(mysql_admin "$DB" -e "SELECT COUNT(*) FROM savings_transaction WHERE PIN=105 AND id IN (4101,4102);")\" == 2 ]]"
check "36.24 and 20.23 remain" "[[ \"$(mysql_admin "$DB" -e "SELECT ROUND(debit,2) FROM general_ledger WHERE id IN (72001,72002) ORDER BY id;")\" == $'36.24\n20.23' ]]"
check "other receipt of 64.61 remains" "[[ \"$(mysql_admin "$DB" -e "SELECT COUNT(*) FROM general_ledger WHERE id=72003;")\" == 1 ]]"
check "other PIN pair remains" "[[ \"$(mysql_admin "$DB" -e "SELECT COUNT(*) FROM general_ledger WHERE PIN=222 AND refferenceID IN ('LO43QPOD0001','SV00002764');")\" == 4 ]]"
bal=$(mysql_admin "$DB" -e "SELECT ROUND(SUM(debit)-SUM(credit),2) FROM general_ledger WHERE PIN=105;")
check "PIN 105 debit minus credit is unchanged" "[[ \"$bal\" == \"$before_net\" ]]"

again=$(run_apply "$FLAGS")
assert_status "repeat deletes nothing new" "$again" ALREADY_REMOVED
check "repeat still has zero pair rows" "[[ \"$(pair_count)\" == 0 ]]"

setup_db
mysql_admin "$DB" -e "DELETE FROM general_ledger WHERE id=71004;"
missing=$(run_apply "$FLAGS")
assert_status "missing void line is refused" "$missing" REFUSED
check "partial group was not deleted" "[[ \"$(pair_count)\" == 3 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger (id, entryid, refferenceID, journalID, \`date\`, account, description, credit, debit, linkto, fromtable, PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) VALUES (71009, '81009', 'LO43QPOD0001', 5, '2022-02-28', 71100, 'extra', 0, 1, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105);"
extra=$(run_apply "$FLAGS")
assert_status "extra line on the source receipt is refused" "$extra" REFUSED
check "extra group was not deleted" "[[ \"$(pair_count)\" == 5 ]]"

setup_db
mysql_admin "$DB" -e "UPDATE general_ledger SET debit=10 WHERE id=71001;"
changed=$(run_apply "$FLAGS")
assert_status "changed amount is refused" "$changed" REFUSED

setup_db
mysql_admin "$DB" -e "UPDATE general_ledger SET \`date\`='2022-03-01' WHERE id=71001;"
baddate=$(run_apply "$FLAGS")
assert_status "changed date is refused" "$baddate" REFUSED

setup_db
mysql_admin "$DB" -e "UPDATE savings_transaction SET comment='NO-LINK' WHERE id=4102;"
nolink=$(run_apply "$FLAGS")
assert_status "void without the source receipt link is refused" "$nolink" REFUSED

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger (id, entryid, refferenceID, journalID, \`date\`, account, description, credit, debit, linkto, fromtable, PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) VALUES (71008, '81001', 'FXSHARE', 5, '2022-02-28', 99999, 'shared entry', 1, 0, 'x', 'x', 0, '', '', '', 0, 1, 1, 1, 0, '', 105);"
shared=$(run_apply "$FLAGS")
assert_status "a row sharing the source entry id is refused" "$shared" REFUSED
check "shared-entry refusal deleted nothing from the pair" "[[ \"$(pair_count)\" == 4 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO journal_entry (entry_date, description, reference_type, createdby, PIN, created_at) VALUES ('2026-11-08', 'REPAIR-LO43QPOD0001-SV00002764', 'savings_interest_repair', 1, '105', '2026-11-08 00:00:00');"
marker=$(run_apply "$FLAGS")
assert_status "an existing correction marker is refused" "$marker" REFUSED
check "marker refusal deleted nothing" "[[ \"$(pair_count)\" == 4 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO gl_books_close (PIN, closed_as_of, action, created_at) VALUES (105, '2022-02-28', 'set', '2026-01-01 00:00:00');"
closed=$(run_apply "$FLAGS")
assert_status "closed books are refused" "$closed" REFUSED
check "closed-book refusal deleted nothing" "[[ \"$(pair_count)\" == 4 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger_entry (id, \`date\`, PIN) VALUES (81001, '2022-02-28', 105), (81002, '2022-02-28', 105);"
headed=$(run_apply "$FLAGS")
assert_status "exclusive legacy headers do not block the detail delete" "$headed" DELETED
check "headers were not deleted" "[[ \"$(mysql_admin "$DB" -e 'SELECT COUNT(*) FROM general_ledger_entry WHERE id IN (81001,81002);')\" == 2 ]]"
check "details under those headers are gone" "[[ \"$(pair_count)\" == 0 ]]"

setup_db
mysql_admin "$DB" -e "CREATE TRIGGER block_gl_delete BEFORE DELETE ON general_ledger FOR EACH ROW SET @blocked = 1;"
trig=$(run_apply "$FLAGS")
assert_status "a trigger refuses the delete" "$trig" REFUSED
check "trigger case deleted nothing" "[[ \"$(pair_count)\" == 4 ]]"

setup_db
mysql_admin "$DB" <<'SQL'
DROP PROCEDURE IF EXISTS probe_delete_rollback;
DELIMITER $$
CREATE PROCEDURE probe_delete_rollback()
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
  END;
  START TRANSACTION;
  DELETE FROM general_ledger WHERE PIN=105 AND refferenceID IN ('LO43QPOD0001','SV00002764');
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'probe rollback';
  COMMIT;
END$$
DELIMITER ;
CALL probe_delete_rollback();
DROP PROCEDURE IF EXISTS probe_delete_rollback;
SQL
check "engine rolls back a signaled delete of this pair" "[[ \"$(pair_count)\" == 4 ]]"

mysql_admin -e "DROP DATABASE IF EXISTS ${DB};"
if [[ "$FAILS" -gt 0 ]]; then
    echo "$FAILS failed"
    exit 1
fi
echo "all passed"
exit 0
