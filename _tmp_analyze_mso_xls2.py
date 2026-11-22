# -*- coding: utf-8 -*-
import re
import xlrd
from collections import defaultdict

path = r'C:\Users\Admin\Downloads\2026.11.22-Savings-MSO-Account_Ledger_21120_2026-11-22_101551 (1).xls'
book = xlrd.open_workbook(path, formatting_info=True)
sheet = book.sheet_by_index(0)

print('sheet=', sheet.name, 'rows=', sheet.nrows, 'cols=', sheet.ncols)

def cell_rgb(row, col):
    xf = book.xf_list[sheet.cell_xf_index(row, col)]
    if not xf.background.fill_pattern:
        return None, None
    pci = xf.background.pattern_colour_index
    return pci, book.colour_map.get(pci)

def is_colored(row, col):
    pci, rgb = cell_rgb(row, col)
    if not pci and not rgb:
        return False, None, None
    # any non-default fill
    if pci in (0, 64, 65):  # often automatic/none
        # still check pattern
        xf = book.xf_list[sheet.cell_xf_index(row, col)]
        if not xf.background.fill_pattern:
            return False, pci, rgb
    return True, pci, rgb

# Find header row and all column headers across wider cols
print('\n--- scan headers / first 20 rows (non-empty cells) ---')
for row in range(min(20, sheet.nrows)):
    parts = []
    for col in range(min(sheet.ncols, 20)):
        v = sheet.cell_value(row, col)
        if v != '' and v is not None:
            parts.append('%s=%r' % (col, v))
    if parts:
        print('R%s' % (row + 1), ' | '.join(parts[:15]))

# Collect all uniquely colored data rows
colored_rows = []
color_counts = defaultdict(int)
for row in range(sheet.nrows):
    row_hit = False
    colors = []
    for col in range(min(sheet.ncols, 15)):
        hit, pci, rgb = is_colored(row, col)
        if hit and rgb:
            r, g, b = rgb
            # ignore pure white
            if (r, g, b) == (255, 255, 255):
                continue
            row_hit = True
            colors.append((col, pci, rgb))
            color_counts[(pci, rgb)] += 1
    if row_hit:
        vals = [sheet.cell_value(row, c) for c in range(min(12, sheet.ncols))]
        colored_rows.append((row + 1, vals, colors[:3]))

print('\n--- fill color frequencies ---')
for k, n in sorted(color_counts.items(), key=lambda x: -x[1])[:20]:
    print(n, k)

print('\ncolored_dataish_rows=', len(colored_rows))
# Print colored rows that look like ledger lines (have Beginning Balances or receipt)
print('\n--- colored ledger-like rows ---')
for rownum, vals, colors in colored_rows:
    s = ' '.join(str(v) for v in vals)
    if any(x in s for x in ('Beginning', 'Receipt', 'Savings', 'Debit', 'Credit', 'NAME', 'remark', 'Remark', 'FIX', 'double', 'Duplicate')) or vals[2] or vals[3]:
        # skip pure title noise somewhat
        if vals[1] or vals[2] or vals[3] or (isinstance(vals[0], str) and '31-12' in str(vals[0])):
            print('ROW', rownum, 'colors=', colors, 'vals=', vals[:10])

# Look for a Remarks column
print('\n--- search Remark/Note/Comment columns ---')
for row in range(min(25, sheet.nrows)):
    for col in range(sheet.ncols):
        v = str(sheet.cell_value(row, col)).strip().lower()
        if v in ('remarks', 'remark', 'notes', 'note', 'comment', 'action', 'fix', 'status') or 'remark' in v:
            print('Found header at row', row + 1, 'col', col, repr(sheet.cell_value(row, col)))

# Dump all non-empty cells in columns 9-20 for rows with content (right-side remarks area)
print('\n--- right-side columns (9-20) non-empty samples ---')
shown = 0
for row in range(sheet.nrows):
    for col in range(9, min(25, sheet.ncols)):
        v = sheet.cell_value(row, col)
        if v != '' and v is not None:
            hit, pci, rgb = is_colored(row, col)
            print('R%s C%s yellowish=%s val=%r' % (row + 1, col, rgb, v))
            shown += 1
    if shown > 80:
        break

print('\ndone shown_right=', shown)
