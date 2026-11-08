#!/bin/bash
# Disposable MariaDB tests for the PIN 105 repair scripts.
# Does not connect to a database named tapstemco.
set -u
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PREFLIGHT="$ROOT/tools/repair_pin105_lo43qpod0001_preflight.sql"
APPLY="$ROOT/tools/repair_pin105_lo43qpod0001_apply.sql"
DB="repair_sql_test"
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

setup_db() {
    mysql_admin -e "DROP DATABASE IF EXISTS ${DB}; CREATE DATABASE ${DB} CHARACTER SET latin1 COLLATE latin1_swedish_ci;"
    mysql_admin "$DB" <<'SQL'
SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';

CREATE TABLE account_chart (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  account bigint(20) NOT NULL,
  account_type bigint(20) NOT NULL,
  sub_account_type bigint(20) NOT NULL,
  account_parent bigint(20) NOT NULL,
  is_header int(11) NOT NULL DEFAULT 0,
  name varchar(100) NOT NULL,
  description text NOT NULL,
  path text NOT NULL,
  createdon timestamp NOT NULL DEFAULT current_timestamp(),
  createdby int(11) NOT NULL,
  PIN bigint(20) NOT NULL,
  edit int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE fiscal_year (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  status tinyint(1) NOT NULL DEFAULT 0,
  created_by int(11) NOT NULL,
  created_at datetime DEFAULT current_timestamp(),
  PIN varchar(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE general_journal_entry (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  entrydate date NOT NULL,
  description text NOT NULL,
  reference_no varchar(100) DEFAULT NULL,
  document_no varchar(100) DEFAULT NULL,
  PIN bigint(20) NOT NULL,
  reference_type varchar(50) DEFAULT 'journal_voucher',
  is_voided tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE general_journal (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  entryid bigint(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

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
  previous_closed_as_of date DEFAULT NULL,
  action varchar(32) NOT NULL DEFAULT 'set',
  fiscal_year_id int(11) DEFAULT NULL,
  note varchar(255) DEFAULT NULL,
  created_by int(11) DEFAULT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE journal_entry (
  id int(11) NOT NULL AUTO_INCREMENT,
  entry_date date NOT NULL,
  description text DEFAULT NULL,
  reference_type varchar(50) DEFAULT NULL,
  reference_id int(11) DEFAULT NULL,
  createdby int(11) NOT NULL,
  PIN varchar(20) NOT NULL,
  created_at datetime NOT NULL,
  is_voided tinyint(1) NOT NULL DEFAULT 0,
  voided_by_entryid int(11) DEFAULT NULL,
  voids_entryid int(11) DEFAULT NULL,
  void_reason varchar(255) DEFAULT NULL,
  voided_on datetime DEFAULT NULL,
  voided_by int(11) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE journal_entry_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  journal_id int(11) NOT NULL,
  reference_type varchar(50) DEFAULT NULL,
  account varchar(50) NOT NULL,
  debit decimal(15,2) NOT NULL DEFAULT 0.00,
  credit decimal(15,2) NOT NULL DEFAULT 0.00,
  description varchar(255) DEFAULT NULL,
  PIN varchar(20) NOT NULL,
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
  previous_balance double NOT NULL DEFAULT 0,
  system_comment varchar(255) NOT NULL,
  PIN bigint(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE members_account (
  id int(11) NOT NULL AUTO_INCREMENT,
  account varchar(20) NOT NULL,
  PIN bigint(20) NOT NULL,
  balance double NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE users (
  id int(11) NOT NULL,
  PIN bigint(20) NOT NULL,
  username varchar(50) NOT NULL,
  deleted tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE groups (
  id int(11) NOT NULL,
  name varchar(50) NOT NULL,
  PIN bigint(20) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE users_groups (
  user_id int(11) NOT NULL,
  group_id int(11) NOT NULL,
  PRIMARY KEY (user_id, group_id)
) ENGINE=InnoDB;

CREATE TABLE access_level (
  id int(11) NOT NULL AUTO_INCREMENT,
  group_id int(11) NOT NULL,
  Module int(11) NOT NULL,
  link varchar(100) NOT NULL,
  allow tinyint(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO account_chart (account, account_type, sub_account_type, account_parent, is_header, name, description, path, createdby, PIN)
VALUES
(11100, 10000, 11000, 0, 0, 'Cash and Cash Equivalents', 'fixture', '11100', 7, 105),
(21110, 20000, 21000, 0, 0, 'Savings Deposit - Special', 'fixture', '21110', 7, 105),
(71100, 70000, 71000, 0, 0, 'Interest Expense on Borrowings', 'fixture', '71100', 7, 105);

INSERT INTO fiscal_year (name, start_date, end_date, status, created_by, PIN)
VALUES
('FY 2022 fixture', '2022-01-01', '2022-12-31', 1, 7, '105'),
('FY 2024 fixture', '2024-01-01', '2024-12-31', 1, 7, '105');

INSERT INTO users (id, PIN, username, deleted) VALUES
(7, 105, 'fixture_actor', NULL),
(8, 999, 'fixture_other_pin', NULL),
(9, 105, 'fixture_no_perm', 0);
INSERT INTO groups (id, name, PIN) VALUES (3, 'fixture_review', 105), (4, 'fixture_other', 999);
INSERT INTO users_groups (user_id, group_id) VALUES (7, 3), (8, 4), (9, 3);
INSERT INTO access_level (group_id, Module, link, allow) VALUES (3, 6, 'Review_journal_entry', 1);
DELETE FROM access_level WHERE group_id = 3 AND link = 'Review_journal_entry' AND 1 = 0;
INSERT INTO users_groups (user_id, group_id) VALUES (9, 4);
DELETE FROM users_groups WHERE user_id = 9 AND group_id = 3;

INSERT INTO savings_transaction (id, receipt, account, trans_type, paymethod, trans_date, amount, previous_balance, system_comment, PIN)
VALUES
(606, 'LO43QPOD0001', '24', 'CR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 0, 'INTEREST', 105),
(2764, 'SV00002764', '24', 'DR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 0, 'VOID TRANSACTION | ORIG_TYPE:CR | ORIG_METHOD:ADJU', 105),
(8001, 'LO43QPOD0001', '24', 'CR', 'ADJUSTMENT', '2022-02-28 08:00:00', 64.61, 0, 'INTEREST', 999);

INSERT INTO members_account (account, PIN, balance) VALUES ('24', 105, 100.00), ('24', 999, 50.00);

INSERT INTO general_ledger (
  id, entryid, refferenceID, journalID, `date`, account, description, credit, debit,
  linkto, fromtable, PID, member_id, customerid, supplierid, balance,
  account_type, sub_account_type, paid, invoiceid, LID, PIN
) VALUES
(3830, '39755', 'LO43QPOD0001', 5, '2022-02-28', 71100, 'fixture source debit', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(3831, '39755', 'LO43QPOD0001', 5, '2022-02-28', 21110, 'fixture source credit', 64.61, 0, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 20000, 21000, 1, 0, '', 105),
(11029, '169383', 'SV00002764', 5, '2022-02-28', 21110, 'fixture void debit', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 20000, 21000, 1, 0, '', 105),
(11030, '169383', 'SV00002764', 5, '2022-02-28', 11100, 'fixture void credit', 64.61, 0, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 10000, 11000, 1, 0, '', 105),
(5001, '5001', 'FX36P2400001', 5, '2024-12-31', 71100, 'fixture active 36.24', 0, 36.24, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(5002, '5002', 'FX20P2300001', 5, '2024-12-31', 71100, 'fixture active 20.23', 0, 20.23, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105),
(9001, '9001', 'FXOTHERPIN', 5, '2022-02-28', 71100, 'other pin', 0, 64.61, 'savings_transaction.receipt', 'savings_transaction', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 999);

ALTER TABLE general_ledger AUTO_INCREMENT = 14070;
ALTER TABLE general_ledger_entry AUTO_INCREMENT = 5996;
ALTER TABLE journal_entry AUTO_INCREMENT = 4495;
SQL
}

run_apply() {
    local extra="$1"
    sudo mysql --socket="$SOCK" --batch --raw "$DB" --init-command="SET SESSION sql_mode='NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'; ${extra}" < "$APPLY"
}

status_of() {
    printf '%s\n' "$1" | awk -F '\t' '$1=="repair_status" {next} $1 ~ /^(PREFLIGHT_ONLY|APPLIED|ALREADY_APPLIED|REFUSED|ROLLED_BACK)$/ {print $1; exit}'
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

marker_count() {
    mysql_admin "$DB" -e "SELECT COUNT(*) FROM journal_entry WHERE reference_type='savings_interest_repair';"
}

gl_repair_count() {
    mysql_admin "$DB" -e "SELECT COUNT(*) FROM general_ledger WHERE description LIKE 'Repair 64.61 DR11100%';"
}

if [[ "${1:-}" == "--setup-only" ]]; then
    setup_db
    exit 0
fi

echo "server $(mysql_admin -e 'SELECT VERSION();')"

sql_code() {
    sed -E 's/--.*$//' "$1"
}

# Static guards. Comments may name forbidden statements; executable text may not.
check "preflight has no data-changing statements" \
    "! sql_code '$PREFLIGHT' | grep -Eiq '(^|[^A-Za-z_])(INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|TRUNCATE)([^A-Za-z_]|$)'"
check "apply has no data UPDATE, DELETE, or table DDL" \
    "! sql_code '$APPLY' | grep -E 'UPDATE |DELETE |ALTER |TRUNCATE |DROP TABLE|CREATE TABLE' | grep -v 'FOR UPDATE' | grep -q ."

setup_db
pre_out=$(sudo mysql --socket="$SOCK" --batch --raw "$DB" < "$PREFLIGHT")
check "preflight names the prepared date" "printf '%s' \"$pre_out\" | grep -q '2022-02-28'"
check "preflight reports window 121.08" "printf '%s' \"$pre_out\" | grep -q '121.08'"
check "preflight sees no legacy header" "printf '%s' \"$pre_out\" | grep -q 'NO_LEGACY_HEADER'"

unset_out=$(run_apply "")
assert_status "unset tokens stay preflight only" "$unset_out" PREFLIGHT_ONLY
check "unset tokens write no marker" "[[ \"$(marker_count)\" == 0 ]]"

TOKENS="SET @expected_database='${DB}'; SET @actor_user_id=7; SET @confirm_apply='APPLY-PIN105-LO43QPOD0001'; SET @confirm_backup='BACKUP-TAKEN-TAPSTEMCO-PIN105'; SET @confirm_historical_date='APPROVE-POSTING-DATE-2022-02-28';"

ok_out=$(run_apply "$TOKENS")
assert_status "authorized apply posts" "$ok_out" APPLIED
check "window reported 56.47" "printf '%s' \"$ok_out\" | grep -q $'\t56.47\t'"
check "member flag unchanged" "printf '%s' \"$ok_out\" | grep -q $'\t1$'"
check "one repair marker" "[[ \"$(marker_count)\" == 1 ]]"
check "two repair GL lines" "[[ \"$(gl_repair_count)\" == 2 ]]"

balance=$(mysql_admin "$DB" -e "SELECT ROUND(SUM(CASE WHEN account=11100 THEN debit ELSE 0 END),2), ROUND(SUM(CASE WHEN account=71100 THEN credit ELSE 0 END),2) FROM general_ledger WHERE description LIKE 'Repair 64.61 DR11100%';")
check "new lines are DR 11100 and CR 71100 of 64.61" "[[ \"$balance\" == $'64.61\t64.61' ]]"

pair=$(mysql_admin "$DB" -e "SELECT ROUND(SUM(CASE WHEN account=71100 THEN debit-credit ELSE 0 END),2), ROUND(SUM(CASE WHEN account=11100 THEN debit-credit ELSE 0 END),2), ROUND(SUM(CASE WHEN account=21110 THEN debit-credit ELSE 0 END),2) FROM general_ledger WHERE PIN=105 AND (id IN (3830,3831,11029,11030) OR description LIKE 'Repair 64.61 DR11100%');")
check "pair residual is zero on 71100 11100 and 21110" "[[ \"$pair\" == $'0.00\t0.00\t0.00' ]]"

header=$(mysql_admin "$DB" -e "SELECT COUNT(*) FROM general_ledger_entry WHERE id IN (39755,169383); SELECT entryid FROM general_ledger WHERE description LIKE 'Repair 64.61 DR11100%' LIMIT 1;")
header_count=$(printf '%s\n' "$header" | head -n 1)
check "legacy header ids were not created" "[[ '$header_count' == 0 ]]"
check "fresh entryid is not a legacy id" "! printf '%s\n' \"$header\" | grep -Eq '^(39755|169383)$'"

sav=$(mysql_admin "$DB" -e "SELECT id, receipt, ROUND(amount,2), system_comment FROM savings_transaction WHERE id IN (606,2764) ORDER BY id;")
check "savings source row unchanged" "printf '%s\n' \"$sav\" | grep -q $'606\tLO43QPOD0001\t64.61\tINTEREST'"
check "savings void row unchanged" "printf '%s\n' \"$sav\" | grep -q '2764	SV00002764	64.61	VOID TRANSACTION | ORIG_TYPE:CR'"
mem=$(mysql_admin "$DB" -e "SELECT PIN, ROUND(balance,2) FROM members_account ORDER BY PIN;")
check "member balances unchanged" "[[ \"$mem\" == $'105\t100.00\n999\t50.00' ]]"
other=$(mysql_admin "$DB" -e "SELECT COUNT(*), ROUND(SUM(debit),2) FROM general_ledger WHERE PIN=999;")
check "other PIN untouched" "[[ \"$other\" == $'1\t64.61' ]]"
kept=$(mysql_admin "$DB" -e "SELECT ROUND(debit,2) FROM general_ledger WHERE id IN (5001,5002) ORDER BY id;")
check "36.24 and 20.23 remain" "[[ \"$kept\" == $'36.24\n20.23' ]]"
orig=$(mysql_admin "$DB" -e "SELECT id, account, ROUND(debit,2), ROUND(credit,2) FROM general_ledger WHERE id IN (3830,3831,11029,11030) ORDER BY id;")
check "original four GL lines unchanged" "[[ \"$orig\" == $'3830\t71100\t64.61\t0.00\n3831\t21110\t0.00\t64.61\n11029\t21110\t64.61\t0.00\n11030\t11100\t0.00\t64.61' ]]"
gj=$(mysql_admin "$DB" -e "SELECT COUNT(*) FROM general_journal_entry; SELECT COUNT(*) FROM general_journal;")
check "journal voucher tables unchanged" "[[ \"$gj\" == $'0\n0' ]]"

again=$(run_apply "$TOKENS")
assert_status "second apply is idempotent" "$again" ALREADY_APPLIED
check "second apply adds no marker" "[[ \"$(marker_count)\" == 1 ]]"
check "second apply adds no GL line" "[[ \"$(gl_repair_count)\" == 2 ]]"

setup_db
bad_actor=$(run_apply "SET @expected_database='${DB}'; SET @actor_user_id=8; SET @confirm_apply='APPLY-PIN105-LO43QPOD0001'; SET @confirm_backup='BACKUP-TAKEN-TAPSTEMCO-PIN105'; SET @confirm_historical_date='APPROVE-POSTING-DATE-2022-02-28';")
assert_status "other PIN actor is refused" "$bad_actor" REFUSED
noperm=$(run_apply "SET @expected_database='${DB}'; SET @actor_user_id=9; SET @confirm_apply='APPLY-PIN105-LO43QPOD0001'; SET @confirm_backup='BACKUP-TAKEN-TAPSTEMCO-PIN105'; SET @confirm_historical_date='APPROVE-POSTING-DATE-2022-02-28';")
assert_status "actor without Review_journal_entry is refused" "$noperm" REFUSED
check "refused actors wrote nothing" "[[ \"$(marker_count)\" == 0 ]]"

setup_db
mysql_admin "$DB" -e "DELETE FROM savings_transaction WHERE id=606;"
missing=$(run_apply "$TOKENS")
assert_status "missing source row is refused" "$missing" REFUSED

setup_db
mysql_admin "$DB" -e "UPDATE savings_transaction SET amount=64.62 WHERE id=606;"
changed=$(run_apply "$TOKENS")
assert_status "changed source amount is refused" "$changed" REFUSED

setup_db
mysql_admin "$DB" -e "INSERT INTO gl_books_close (PIN, closed_as_of, action, created_at) VALUES (105, '2022-02-28', 'set', '2026-01-01 00:00:00');"
closed=$(run_apply "$TOKENS")
assert_status "closed through the posting date is refused" "$closed" REFUSED
check "closed-book refusal wrote nothing" "[[ \"$(marker_count)\" == 0 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO gl_books_close (PIN, closed_as_of, action, created_at) VALUES (105, '2022-02-27', 'set', '2026-01-01 00:00:00');"
open_after=$(run_apply "$TOKENS")
assert_status "close date before 2022-02-28 still allows the posting date" "$open_after" APPLIED

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger_entry (id, \`date\`, PIN) VALUES (39755, '2022-02-28', 105);"
legacy=$(run_apply "$TOKENS")
assert_status "a real legacy header id is refused" "$legacy" REFUSED
check "legacy-header refusal wrote no marker" "[[ \"$(marker_count)\" == 0 ]]"

setup_db
mysql_admin "$DB" -e "ALTER TABLE general_ledger_entry AUTO_INCREMENT = 39755;"
collide_legacy=$(run_apply "$TOKENS")
assert_status "next header id equal to legacy 39755 is refused" "$collide_legacy" REFUSED

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger (id, entryid, refferenceID, journalID, \`date\`, account, description, credit, debit, linkto, fromtable, PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) VALUES (9100, '7000', 'FXCOLLIDE', 5, '2025-01-01', 11100, 'unrelated', 0, 1, 'x', 'x', 0, '', '', '', 0, 10000, 11000, 1, 0, '', 105); ALTER TABLE general_ledger_entry AUTO_INCREMENT = 7000;"
collide=$(run_apply "$TOKENS")
assert_status "next header id already used as a detail entryid is refused" "$collide" REFUSED
check "collision refusal wrote no marker" "[[ \"$(marker_count)\" == 0 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger (id, entryid, refferenceID, journalID, \`date\`, account, description, credit, debit, linkto, fromtable, PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) VALUES (9200, '9200', 'FXMANUAL', 5, '2025-06-01', 71100, 'unlinked manual', 64.61, 0, 'x', 'general_journal', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105);"
amb=$(run_apply "$TOKENS")
assert_status "unlinked 64.61 without review token is refused" "$amb" REFUSED
amb_ok=$(run_apply "${TOKENS} SET @confirm_manual_review='REVIEWED-NO-OTHER-641-CORRECTION';")
assert_status "reviewed unlinked 64.61 outside the window can post" "$amb_ok" APPLIED
manual_left=$(mysql_admin "$DB" -e "SELECT ROUND(credit,2) FROM general_ledger WHERE id=9200;")
check "unlinked manual line was not edited" "[[ \"$manual_left\" == 64.61 ]]"

setup_db
mysql_admin "$DB" -e "INSERT INTO general_ledger (id, entryid, refferenceID, journalID, \`date\`, account, description, credit, debit, linkto, fromtable, PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) VALUES (9300, '9300', 'FXLINK', 5, '2025-06-01', 71100, 'note LO43QPOD0001', 64.61, 0, 'x', 'general_journal', 0, '', '', '', 0, 70000, 71000, 1, 0, '', 105);"
linked=$(run_apply "${TOKENS} SET @confirm_manual_review='REVIEWED-NO-OTHER-641-CORRECTION';")
assert_status "a line that already names the receipt is refused even after review" "$linked" REFUSED

setup_db
mysql_admin "$DB" -e "ALTER TABLE general_ledger ADD CONSTRAINT chk_block_repair CHECK (NOT (account = 71100 AND credit > 64 AND credit < 65 AND description LIKE 'Repair 64.61%'));"
partial=$(run_apply "$TOKENS")
assert_status "failed GL insert rolls back" "$partial" ROLLED_BACK
check "rolled back journal header is gone" "[[ \"$(marker_count)\" == 0 ]]"
check "rolled back GL lines are gone" "[[ \"$(gl_repair_count)\" == 0 ]]"
check "rolled back GL header is gone" "[[ \"$(mysql_admin "$DB" -e 'SELECT COUNT(*) FROM general_ledger_entry;')\" == 0 ]]"
check "savings rows survive the rollback" "[[ \"$(mysql_admin "$DB" -e 'SELECT COUNT(*) FROM savings_transaction WHERE id IN (606,2764);')\" == 2 ]]"

setup_db
wrong_db=$(run_apply "SET @expected_database='not_the_schema'; SET @actor_user_id=7; SET @confirm_apply='APPLY-PIN105-LO43QPOD0001'; SET @confirm_backup='BACKUP-TAKEN-TAPSTEMCO-PIN105'; SET @confirm_historical_date='APPROVE-POSTING-DATE-2022-02-28';")
assert_status "wrong schema name is refused" "$wrong_db" REFUSED

setup_db
sudo mysql --socket="$SOCK" --batch --raw -e "SELECT GET_LOCK('tapstemco_repair_pin105_lo43qpod0001', 2); SELECT SLEEP(12);" >/dev/null &
lock_pid=$!
sleep 0.4
locked=$(run_apply "$TOKENS")
assert_status "second session cannot post while the repair lock is held" "$locked" REFUSED
check "locked session wrote no marker" "[[ \"$(marker_count)\" == 0 ]]"
wait "$lock_pid" || true

mysql_admin -e "DROP DATABASE IF EXISTS ${DB};"

if [[ "$FAILS" -gt 0 ]]; then
    echo "$FAILS failed"
    exit 1
fi
echo "all passed"
exit 0
