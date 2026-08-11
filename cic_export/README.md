# CIC Credit Data CSV Export Module

Plain PHP package for preparing borrower and loan data for submission to the **Credit Information Corporation (CIC)** under the **Credit Information System Act (CISA)** / RA 9510.

This module validates rows, exports a **UTF-8 without BOM** CSV, logs validation errors, and optionally writes invalid rows to a separate error file. It is framework-agnostic and ready to plug into CodeIgniter or any PHP system via `require_once`.

> **Important:** Official CIC production layouts (Subject / Contract files) are not public. Request the data dictionary and technical specs from [datasubmission@creditinfo.gov.ph](mailto:datasubmission@creditinfo.gov.ph). Treat this CSV as a **local prep / quality-control** extract until you map fields to the official schema.

## CodeIgniter integration (Tapstemco)

This package is wired into the live app:

| Piece | Location |
|-------|----------|
| Controller | `application/controllers/cic.php` → `/en/cic/index` |
| Model | `application/models/cic_model.php` (maps members / loan_contract) |
| View | `application/views/cic/index.php` |
| Permission | `Export_CIC` on Loan module (5) |
| Installer | `tools/add_cic_export_permission.php` (run once, then remove) |
| Menu | Loan → **CIC Credit Export** |

### Enable access

1. Open `http://localhost/tapstemco/tools/add_cic_export_permission.php`
2. Optionally click **Enable for All Groups**, or assign **Export_CIC** under User Management → Privileges → Loan
3. Delete or lock down the installer script afterward
4. Use **Loan → CIC Credit Export** to preview and download CSV

Field mapping from Tapstemco DB is in `cic_model.php` (SSS/TIN → `id_number`, `basic_amount` → `loan_amount`, principal outstanding, nationality default `Filipino`).

---

## Quick start (standalone CLI)

```bash
cd cic_export
php example_usage.php
```

Outputs (example period `202608`):

| Artifact | Path |
|----------|------|
| Valid CSV | `output/202608/CIC_PREP_{COOP}_{YYYYMM}.csv` |
| Error CSV (optional) | `output/202608/CIC_PREP_{COOP}_{YYYYMM}_errors.csv` |
| Validation log | `logs/validation_{YYYYMM}.log` |

## Layout

```
cic_export/
  src/
    CicValidator.php      # Row validation rules
    CicCsvExporter.php    # UTF-8 CSV export
    CicErrorLogger.php    # Append-only error log
  config/
    field_map.php         # Logical field → CSV header / column order
  output/                 # Runtime exports (gitignored)
  logs/                   # Runtime logs (gitignored)
  example_usage.php
  README.md
```

## Classes

### CicValidator

Enforces:

- `borrower_id` and `name` required
- `date_of_birth` and `loan_date` valid `YYYY-MM-DD`
- `loan_amount` numeric and `> 0`
- `outstanding_balance` numeric and `>= 0`
- `outstanding_balance` must not exceed `loan_amount`

Returns:

```php
array(
    'valid'  => true|false,
    'errors' => array(array('field' => '...', 'message' => '...'), ...),
    'row'    => /* normalized associative array */,
);
```

### CicCsvExporter

Options:

| Option | Meaning |
|--------|---------|
| `output_path` | Destination for valid rows (required) |
| `export_invalid_rows` | `false` = skip invalid; `true` = also write error CSV |
| `error_output_path` | Required when exporting invalid rows |
| `field_map` | From `config/field_map.php` |
| `logger` | `CicErrorLogger` instance |

Encoding: **UTF-8 without BOM** (aligned with CIC technical practice). Do not add a BOM.

### CicErrorLogger

Line format:

```
YYYY-MM-DD HH:MM:SS | row={n} | borrower_id={id} | field={f} | {message}
```

Optional PII masking for long digit sequences and sensitive fields in log lines. Full data stays in secured CSV files only.

## Integrating with your database

In [`example_usage.php`](example_usage.php), replace the sample `$rows` array with a query that returns associative arrays using these **logical keys**:

`borrower_id`, `name`, `date_of_birth`, `gender`, `civil_status`, `nationality`, `contact_number`, `id_number`, `loan_amount`, `loan_date`, `loan_term_months`, `outstanding_balance`, `payment_status`

Map real table columns to those keys in SQL (`AS borrower_id`, etc.) or in PHP after fetch. Comments in `example_usage.php` show PDO and CodeIgniter 2 stubs.

### CodeIgniter 2 sketch

```php
require_once FCPATH . 'cic_export/src/CicValidator.php';
require_once FCPATH . 'cic_export/src/CicErrorLogger.php';
require_once FCPATH . 'cic_export/src/CicCsvExporter.php';

$fieldMap = require FCPATH . 'cic_export/config/field_map.php';
$rows = $this->your_model->get_cic_prep_rows($asOfDate);

$exporter = new CicCsvExporter(array(
    'output_path'         => $path,
    'error_output_path'   => $errorPath,
    'export_invalid_rows' => true,
    'field_map'           => $fieldMap,
    'logger'              => new CicErrorLogger($logPath),
));
$summary = $exporter->export($rows);
```

## Adapting when CIC provides an official schema

1. Keep **logical keys** stable in validators and DB mapping.
2. Edit [`config/field_map.php`](config/field_map.php): change **values** (CSV headers) and **array order** to match CIC columns.
3. Optionally copy to `config/field_map_cic_official.php` and pass that map into the exporter so prep and official formats can coexist.
4. Extend `CicValidator` (or add a second validator) for CIC-specific codes, lengths, and enums from the official dictionary.
5. Final transmission format may be delimited `.txt` (not CSV) → ZIP → GPG. You can write a thin adapter that reads the same validated rows and emits the official delimiter.

## Best practices for CIC submission preparation

1. **Register** as a Submitting Entity and use the CIC Covered Entity (CE) Portal; designate an Authorized Representative and Batch Operator.
2. **Request** the official data dictionary and validation rules from CIC; do not invent production column codes.
3. **Notify members** of CISA submission obligations (see CDA MC 2019-01 templates / CIC circulars) and keep signed acknowledgements on loan applications.
4. **Cut-off date:** use period-end outstanding balances and status (typically prior month for a monthly regular submission).
5. **Pre-validate locally** with this module; fix source data (membership KYC, loan balances) before portal upload.
6. **Reconcile counts** on the Transmittal Form (subjects / contracts / records) with your export summary.
7. **Package** per CIC instructions (historically: UTF-8 without BOM text, ZIP, then encrypt with Gpg4Win). This module’s CSV is an intermediate prep file.
8. Keep a **read-only archive** of each period’s accepted file and CIC acknowledgment / error report.

## Monthly / quarterly batch exports

- Schedule via Windows Task Scheduler or cron: `php /path/to/cic_export/example_usage.php` (or a thin CLI wrapper that accepts `--period=YYYYMM`).
- Parameterize `as_of_date` / period in the DB query (last day of prior month for monthly runs).
- Write under `output/{YYYYMM}/` with names like `CIC_PREP_{COOP}_{YYYYMM}.csv`.
- Never overwrite a finalized period without archiving first; treat re-runs as versioned files (`_v2`) if needed.
- Archive matching `logs/validation_{YYYYMM}.log` and error CSVs with the same period folder.
- After a successful CIC portal submission, record who ran the job, file hashes, and portal reference numbers in your ops checklist.

## Error logging strategy

| Channel | Purpose |
|---------|---------|
| `logs/validation_*.log` | Per-field failures for ops / data cleanup |
| `*_errors.csv` | Invalid rows for spreadsheet review (optional) |
| App activity log (CI) | Who generated the export (wire when integrating UI) |

- Deny HTTP access to `cic_export/output` and `cic_export/logs` (place outside webroot in production, or block via server config).
- Rotate logs with the batch period; retain per retention policy / audit needs.
- Mask PII in application logs; keep full identifiers only in access-controlled export files.

## Data privacy and secure file handling

Exports contain **personal and credit data** under the Data Privacy Act of 2012 and CISA. Treat them as confidential.

- Store export/log directories with restrictive OS ACLs; least privilege for batch accounts.
- Prefer encryption at rest for archives; transmit to CIC only via approved encrypted channels (e.g. GPG to CE Portal).
- Delete temporary download copies after successful submission.
- Log access (who generated / downloaded / submitted); do not email unencrypted CSVs.
- Do not commit contents of `output/` or `logs/` (see `.gitignore`).
- Limit browser-based downloads; prefer server-side batch + secured transfer for production volumes.

## Legal / compliance note

This package helps with **data preparation quality**. It does not replace CIC registration, official schemas, portal procedures, or legal advice. Confirm current circulars and deadlines with CIC and your cooperative’s compliance officer.
