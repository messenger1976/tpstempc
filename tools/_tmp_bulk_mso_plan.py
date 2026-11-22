# -*- coding: utf-8 -*-
"""Plan bulk MSO BB realignment from recon Source vs local DB."""
import openpyxl
import subprocess
import json
import re

RECON = r"C:\Users\Admin\Downloads\Saving_Account_List_2024-MSO-Recon.xlsx"
MYSQL = r"C:\xampp\mysql\bin\mysql.exe"

wb = openpyxl.load_workbook(RECON, data_only=True)
ws = wb["Source"]

book = {}  # old_members_acct -> (name, amount)
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

print("book MSO rows with amount>0:", len(book))

# dump book to JSON for next step
with open(r"C:\xampp\htdocs\tapstemco\tools\_tmp_mso_book.json", "w", encoding="utf-8") as f:
    json.dump(book, f, ensure_ascii=False, indent=0)

# pull DB state
sql = r"""
SELECT ma.id, ma.old_members_acct, ma.member_id,
       CONCAT(m.firstname,'  ',m.lastname) AS member_name,
       ROUND(ma.balance,2) AS bal,
       st.receipt, ROUND(st.amount,2) AS bb_amt, st.customer_name
FROM members_account ma
JOIN members m ON m.member_id = ma.member_id
LEFT JOIN savings_transaction st
  ON st.account = ma.id AND st.system_comment = 'BEGINNING BALANCE' AND st.PIN = 105
WHERE ma.PIN = 105 AND ma.account_cat = 2017 AND ma.status = 1
  AND ma.old_members_acct LIKE 'M-2024-12-%'
ORDER BY ma.old_members_acct, ma.id;
"""
proc = subprocess.run(
    [MYSQL, "-u", "root", "tapstemco", "-N", "-B", "-e", sql],
    capture_output=True, text=True, encoding="utf-8", errors="replace"
)
if proc.returncode != 0:
    print(proc.stderr)
    raise SystemExit(1)

db_rows = []
for line in proc.stdout.splitlines():
    if not line.strip():
        continue
    parts = line.split("\t")
    while len(parts) < 8:
        parts.append("")
    db_rows.append({
        "id": int(parts[0]),
        "acct": parts[1],
        "member_id": parts[2],
        "member_name": parts[3],
        "bal": float(parts[4] or 0),
        "receipt": parts[5] if parts[5] != "NULL" else None,
        "bb_amt": float(parts[6]) if parts[6] not in ("", "NULL") else None,
        "bb_name": parts[7] if parts[7] != "NULL" else None,
    })

# one active row per acct (prefer one with BB)
by_acct = {}
for row in db_rows:
    cur = by_acct.get(row["acct"])
    if cur is None:
        by_acct[row["acct"]] = row
    elif cur["receipt"] is None and row["receipt"]:
        by_acct[row["acct"]] = row

need = []
missing_acct = []
missing_bb = []
already_ok = []
gl_delta = 0.0

for acct, (bname, bamt) in sorted(book.items()):
    row = by_acct.get(acct)
    if not row:
        missing_acct.append((acct, bname, bamt))
        continue
    if not row["receipt"]:
        missing_bb.append((acct, bname, bamt, row["id"], row["member_id"]))
        continue
    name_ok = re.sub(r"\s+", " ", (row["bb_name"] or "").upper().replace(",", "")) == re.sub(
        r"\s+", " ", (row["member_name"] or "").upper().replace(",", "")
    )
    amt_ok = row["bb_amt"] is not None and abs(row["bb_amt"] - bamt) < 0.015
    bal_ok = abs(row["bal"] - bamt) < 0.015
    if name_ok and amt_ok and bal_ok:
        already_ok.append(acct)
        continue
    delta = bamt - (row["bb_amt"] or 0)
    gl_delta += delta
    need.append({
        "acct": acct,
        "book_name": bname,
        "book_amt": bamt,
        "id": row["id"],
        "member_id": row["member_id"],
        "member_name": row["member_name"],
        "receipt": row["receipt"],
        "bb_amt": row["bb_amt"],
        "bb_name": row["bb_name"],
        "bal": row["bal"],
        "delta": round(delta, 2),
    })

print("DB active M- accounts:", len(by_acct))
print("already OK:", len(already_ok))
print("need repair:", len(need))
print("book acct missing in DB:", len(missing_acct))
print("DB acct missing BB ST:", len(missing_bb))
print("GL delta if rewrite BBs:", round(gl_delta, 2))
print("sample need (10):")
for x in need[:10]:
    print(" ", x["acct"], "bb", x["bb_amt"], "->", x["book_amt"], "|", x["bb_name"], "->", x["member_name"])
if missing_acct[:5]:
    print("missing acct sample:", missing_acct[:5])
if missing_bb[:5]:
    print("missing BB sample:", missing_bb[:5])

with open(r"C:\xampp\htdocs\tapstemco\tools\_tmp_mso_need_repair.json", "w", encoding="utf-8") as f:
    json.dump({"need": need, "missing_acct": missing_acct, "missing_bb": missing_bb, "gl_delta": round(gl_delta, 2)}, f, indent=2)
print("wrote _tmp_mso_need_repair.json")
