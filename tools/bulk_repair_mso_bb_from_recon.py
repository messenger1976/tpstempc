# -*- coding: utf-8 -*-
"""
Bulk repair MSO beginning balances from Saving_Account_List_2024-MSO-Recon.xlsx Source.

Usage:
  python tools/bulk_repair_mso_bb_from_recon.py --dry-run
  python tools/bulk_repair_mso_bb_from_recon.py --apply
  python tools/bulk_repair_mso_bb_from_recon.py --write-sql tools/bulk_repair_mso_bb_from_recon.sql

Safe rules:
  - Only active MSO accounts (account_cat=2017) with old_members_acct M-2024-12-*
  - Book amount from recon Source col MSO > 0
  - Skip missing book accounts (yellow-keep orphans)
  - Skip TAPSTEMCO tracing ORs 00368/00369 (handled in runbook Part 4)
  - Cross-COA (ST on MSO, GL on 21110): rewrite ST to book; INSERT MSO GL; leave 21110 alone
"""
from __future__ import print_function
import argparse
import json
import os
import re
import subprocess
import sys

try:
    import openpyxl
except ImportError:
    sys.exit("openpyxl required")

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
RECON = r"C:\Users\Admin\Downloads\Saving_Account_List_2024-MSO-Recon.xlsx"
MYSQL = r"C:\xampp\mysql\bin\mysql.exe"
DB = "tapstemco"
PIN = 105
SKIP_OR = {"M-2024-12-00368", "M-2024-12-00369"}  # sheet A TAPSTEMCO tracing


def mysql_query(sql):
    p = subprocess.run(
        [MYSQL, "-u", "root", DB, "-N", "-B", "-e", sql],
        capture_output=True, text=True, encoding="utf-8", errors="replace",
    )
    if p.returncode != 0:
        raise RuntimeError(p.stderr or p.stdout)
    rows = []
    for line in p.stdout.splitlines():
        if line.strip():
            rows.append(line.split("\t"))
    return rows


def mysql_exec(sql):
    p = subprocess.run(
        [MYSQL, "-u", "root", DB, "-e", sql],
        capture_output=True, text=True, encoding="utf-8", errors="replace",
    )
    if p.returncode != 0:
        raise RuntimeError(p.stderr or p.stdout)
    return p.stdout


def esc(s):
    return str(s).replace("\\", "\\\\").replace("'", "''")


def load_book():
    wb = openpyxl.load_workbook(RECON, data_only=True)
    ws = wb["Source"]
    book = {}
    for r in range(10, ws.max_row + 1):
        acct = ws.cell(r, 11).value
        name = ws.cell(r, 3).value
        amt = ws.cell(r, 7).value
        if not acct or not str(acct).startswith("M-2024-12-"):
            continue
        try:
            amt_f = round(float(amt or 0), 2)
        except Exception:
            continue
        if amt_f <= 0:
            continue
        book[str(acct).strip()] = (str(name).strip(), amt_f)
    return book


def norm_name(s):
    return re.sub(r"\s+", " ", (s or "").upper().replace(",", "").strip())


def plan(book):
    sql = """
SELECT ma.id, ma.old_members_acct, ma.member_id,
       CONCAT(m.firstname,'  ',m.lastname),
       ROUND(ma.balance,2),
       st.receipt, ROUND(IFNULL(st.amount,0),2), IFNULL(st.customer_name,''),
       (SELECT gl.account FROM general_ledger gl
         WHERE gl.PIN=105 AND gl.refferenceID=st.receipt AND gl.account IN (21110,21120)
         ORDER BY FIELD(gl.account,21120,21110) LIMIT 1) AS gl_coa,
       (SELECT ROUND(gl.credit,2) FROM general_ledger gl
         WHERE gl.PIN=105 AND gl.refferenceID=st.receipt AND gl.account IN (21110,21120)
         ORDER BY FIELD(gl.account,21120,21110) LIMIT 1) AS gl_credit
FROM members_account ma
JOIN members m ON m.member_id=ma.member_id
LEFT JOIN savings_transaction st
  ON st.account=ma.id AND st.system_comment='BEGINNING BALANCE' AND st.PIN=105
WHERE ma.PIN=105 AND ma.account_cat=2017 AND ma.status=1
  AND ma.old_members_acct LIKE 'M-2024-12-%'
ORDER BY ma.old_members_acct, ma.id
"""
    by_acct = {}
    for parts in mysql_query(sql):
        while len(parts) < 10:
            parts.append("")
        row = {
            "id": int(parts[0]),
            "acct": parts[1],
            "member_id": parts[2],
            "member_name": parts[3],
            "bal": float(parts[4] or 0),
            "receipt": None if parts[5] in ("", "NULL") else parts[5],
            "bb_amt": float(parts[6] or 0),
            "bb_name": parts[7],
            "gl_coa": None if parts[8] in ("", "NULL") else int(float(parts[8])),
            "gl_credit": None if parts[9] in ("", "NULL") else float(parts[9]),
        }
        cur = by_acct.get(row["acct"])
        if cur is None or (cur["receipt"] is None and row["receipt"]):
            by_acct[row["acct"]] = row

    actions = []
    skipped = []
    for acct, (bname, bamt) in sorted(book.items()):
        if acct in SKIP_OR:
            skipped.append((acct, "tapstemco_tracing"))
            continue
        row = by_acct.get(acct)
        if not row:
            skipped.append((acct, "missing_account"))
            continue

        name_ok = norm_name(row["bb_name"]) == norm_name(row["member_name"])
        amt_ok = row["receipt"] and abs(row["bb_amt"] - bamt) < 0.015
        bal_ok = abs(row["bal"] - bamt) < 0.015
        gl_ok = row["gl_coa"] == 21120 and row["gl_credit"] is not None and abs(row["gl_credit"] - bamt) < 0.015

        if row["receipt"] and name_ok and amt_ok and bal_ok and gl_ok:
            skipped.append((acct, "already_ok"))
            continue

        if row["receipt"] and row["gl_coa"] == 21120:
            kind = "rewrite_21120"
        elif row["receipt"] and row["gl_coa"] == 21110:
            kind = "cross_special_gl"
        elif row["receipt"] and row["gl_coa"] is None:
            kind = "st_no_gl"
        else:
            kind = "missing_st"
            # find existing MSO GL for member
            g = mysql_query(
                "SELECT refferenceID, ROUND(credit,2) FROM general_ledger "
                "WHERE PIN=105 AND account=21120 AND member_id='%s' "
                "AND description LIKE '%%%%Beginning Balance%%%%' LIMIT 1"
                % esc(row["member_id"])
            )
            if g:
                row["receipt"] = g[0][0]
                row["gl_credit"] = float(g[0][1])
                row["gl_coa"] = 21120

        actions.append({
            "kind": kind,
            "acct": acct,
            "book_amt": bamt,
            "book_name": bname,
            "id": row["id"],
            "member_id": row["member_id"],
            "member_name": row["member_name"],
            "receipt": row["receipt"],
            "bb_amt": row["bb_amt"],
            "gl_coa": row["gl_coa"],
            "gl_credit": row["gl_credit"],
        })
    return actions, skipped


def sql_for_action(a):
    stmts = []
    mid = esc(a["member_name"])
    amt = "%.2f" % a["book_amt"]
    aid = a["id"]
    member_id = esc(a["member_id"])
    acct = esc(a["acct"])

    if a["kind"] == "rewrite_21120":
        rcpt = esc(a["receipt"])
        stmts.append(
            "UPDATE savings_transaction SET amount=%s, customer_name='%s', previous_balance=0 "
            "WHERE PIN=%s AND receipt='%s' AND account=%s;" % (amt, mid, PIN, rcpt, aid)
        )
        stmts.append(
            "UPDATE general_ledger SET credit=%s, member_id='%s', "
            "description='Savings Beginning Balance - %s (Account: %s, Receipt: %s)' "
            "WHERE PIN=%s AND account=21120 AND refferenceID='%s';"
            % (amt, member_id, mid, aid, rcpt, PIN, rcpt)
        )
    elif a["kind"] == "cross_special_gl":
        # Keep Special GL; rewrite ST; add MSO GL under new receipt
        old = esc(a["receipt"])
        new_rcpt = "BM%s" % aid
        stmts.append(
            "UPDATE savings_transaction SET receipt='%s', amount=%s, customer_name='%s', previous_balance=0 "
            "WHERE PIN=%s AND receipt='%s' AND account=%s;"
            % (new_rcpt, amt, mid, PIN, old, aid)
        )
        stmts.append(
            "INSERT INTO general_ledger "
            "(entryid, refferenceID, journalID, date, account, description, credit, debit, linkto, fromtable, "
            " PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) "
            "SELECT CONCAT('BM', %s), '%s', 8, '2024-12-31', 21120, "
            "'Savings Beginning Balance - %s (Account: %s, Receipt: %s)', %s, 0, "
            "'savings_transaction.receipt', 'savings_transaction', "
            "0, '%s', '', '', 0, 20000, 21000, 1, 0, '', %s "
            "FROM DUAL WHERE NOT EXISTS ("
            " SELECT 1 FROM general_ledger g WHERE g.PIN=%s AND g.account=21120 AND g.refferenceID='%s');"
            % (aid, new_rcpt, mid, aid, new_rcpt, amt, member_id, PIN, PIN, new_rcpt)
        )
    elif a["kind"] in ("st_no_gl", "missing_st"):
        if a["kind"] == "missing_st" and a["receipt"] and a["gl_coa"] == 21120:
            rcpt = esc(a["receipt"])
            # ensure ST exists for existing GL
            stmts.append(
                "INSERT INTO savings_transaction "
                "(receipt, refno, account, account_cat, trans_type, paymethod, cheque_num, trans_date, "
                " amount, previous_balance, comment, system_comment, PID, customer_name, createdby, PIN) "
                "SELECT '%s', '', %s, 2017, 'CR', 'ADJUSTMENT', '', '2024-12-31 08:00:00', "
                " %s, 0, 'Balance - Beginning', 'BEGINNING BALANCE', 0, '%s', 6017, %s "
                "FROM DUAL WHERE NOT EXISTS ("
                " SELECT 1 FROM savings_transaction s WHERE s.PIN=%s AND s.receipt='%s');"
                % (rcpt, aid, amt, mid, PIN, PIN, rcpt)
            )
            stmts.append(
                "UPDATE savings_transaction SET amount=%s, customer_name='%s', account=%s, "
                "system_comment='BEGINNING BALANCE' WHERE PIN=%s AND receipt='%s';"
                % (amt, mid, aid, PIN, rcpt)
            )
            stmts.append(
                "UPDATE general_ledger SET credit=%s, member_id='%s', "
                "description='Savings Beginning Balance - %s (Account: %s, Receipt: %s)' "
                "WHERE PIN=%s AND account=21120 AND refferenceID='%s';"
                % (amt, member_id, mid, aid, rcpt, PIN, rcpt)
            )
        else:
            new_rcpt = "BM%s" % aid
            if a["receipt"]:
                old = esc(a["receipt"])
                stmts.append(
                    "UPDATE savings_transaction SET receipt='%s', amount=%s, customer_name='%s', previous_balance=0 "
                    "WHERE PIN=%s AND receipt='%s' AND account=%s;"
                    % (new_rcpt, amt, mid, PIN, old, aid)
                )
            else:
                stmts.append(
                    "INSERT INTO savings_transaction "
                    "(receipt, refno, account, account_cat, trans_type, paymethod, cheque_num, trans_date, "
                    " amount, previous_balance, comment, system_comment, PID, customer_name, createdby, PIN) "
                    "SELECT '%s', '', %s, 2017, 'CR', 'ADJUSTMENT', '', '2024-12-31 08:00:00', "
                    " %s, 0, 'Balance - Beginning', 'BEGINNING BALANCE', 0, '%s', 6017, %s "
                    "FROM DUAL WHERE NOT EXISTS ("
                    " SELECT 1 FROM savings_transaction s WHERE s.PIN=%s AND s.account=%s AND s.system_comment='BEGINNING BALANCE');"
                    % (new_rcpt, aid, amt, mid, PIN, PIN, aid)
                )
            stmts.append(
                "INSERT INTO general_ledger "
                "(entryid, refferenceID, journalID, date, account, description, credit, debit, linkto, fromtable, "
                " PID, member_id, customerid, supplierid, balance, account_type, sub_account_type, paid, invoiceid, LID, PIN) "
                "SELECT CONCAT('BM', %s), '%s', 8, '2024-12-31', 21120, "
                "'Savings Beginning Balance - %s (Account: %s, Receipt: %s)', %s, 0, "
                "'savings_transaction.receipt', 'savings_transaction', "
                "0, '%s', '', '', 0, 20000, 21000, 1, 0, '', %s "
                "FROM DUAL WHERE NOT EXISTS ("
                " SELECT 1 FROM general_ledger g WHERE g.PIN=%s AND g.account=21120 AND g.refferenceID='%s');"
                % (aid, new_rcpt, mid, aid, new_rcpt, amt, member_id, PIN, PIN, new_rcpt)
            )
    else:
        return []

    stmts.append(
        "DELETE FROM savings_transaction WHERE PIN=%s AND account=%s AND system_comment<>'BEGINNING BALANCE';"
        % (PIN, aid)
    )
    stmts.append(
        "UPDATE members_account SET balance=%s WHERE id=%s AND member_id='%s' AND old_members_acct='%s';"
        % (amt, aid, member_id, acct)
    )
    return stmts


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true")
    ap.add_argument("--apply", action="store_true")
    ap.add_argument("--write-sql", default="")
    args = ap.parse_args()
    if not args.dry_run and not args.apply and not args.write_sql:
        args.dry_run = True

    book = load_book()
    actions, skipped = plan(book)
    from collections import Counter
    print("book rows", len(book))
    print("actions", len(actions), Counter(a["kind"] for a in actions))
    print("skipped", len(skipped), Counter(s for _, s in skipped))

    all_sql = [
        "-- Bulk MSO BB repair from recon Source",
        "-- PIN 105 | COA 21120",
        "SET @mso_before := (SELECT ROUND(SUM(credit)-SUM(debit),2) FROM general_ledger gl "
        "INNER JOIN account_chart ac ON ac.account=gl.account AND ac.PIN=gl.PIN "
        "WHERE gl.PIN=105 AND gl.account=21120 AND gl.date<='2024-12-31');",
        "SELECT @mso_before AS mso_before;",
    ]
    for a in actions:
        all_sql.append("-- %s %s %s -> %.2f" % (a["kind"], a["acct"], a["member_name"], a["book_amt"]))
        all_sql.extend(sql_for_action(a))

    all_sql.append(
        "SELECT ROUND(SUM(credit)-SUM(debit),2) AS mso_after FROM general_ledger gl "
        "INNER JOIN account_chart ac ON ac.account=gl.account AND ac.PIN=gl.PIN "
        "WHERE gl.PIN=105 AND gl.account=21120 AND gl.date<='2024-12-31';"
    )

    sql_text = "\n".join(all_sql) + "\n"
    if args.write_sql:
        path = args.write_sql
        if not os.path.isabs(path):
            path = os.path.join(ROOT, path)
        with open(path, "w", encoding="utf-8") as f:
            f.write(sql_text)
        print("wrote", path, "bytes", len(sql_text))

    if args.dry_run and not args.apply:
        print("DRY RUN only — no DB changes. Use --apply to execute, --write-sql to save.")
        return

    if args.apply:
        # execute in chunks via temp file (Windows-friendly)
        tmp = os.path.join(ROOT, "tools", "_tmp_bulk_mso_apply.sql")
        with open(tmp, "w", encoding="utf-8") as f:
            f.write(sql_text)
        print("Applying", tmp, "...")
        out = mysql_exec("source " + tmp.replace("\\", "/"))
        print(out[-2000:] if len(out) > 2000 else out)
        print("DONE")


if __name__ == "__main__":
    main()
