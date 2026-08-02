<?php
/**
 * Example usage: CIC credit data CSV export (plain PHP).
 *
 * Run from CLI:
 *   php example_usage.php
 *
 * Or open in a browser under a non-public path (prefer CLI for batch jobs).
 *
 * REPLACE the sample $rows block with a database query when integrating
 * into your cooperative lending system.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = __DIR__;

require_once $baseDir . '/src/CicValidator.php';
require_once $baseDir . '/src/CicErrorLogger.php';
require_once $baseDir . '/src/CicCsvExporter.php';

$fieldMap = require $baseDir . '/config/field_map.php';

// -----------------------------------------------------------------------------
// Period / naming for monthly or quarterly batches
// -----------------------------------------------------------------------------
$period = date('Ym'); // e.g. 202608 — use prior month for production cut-offs
$coopCode = 'TAPSTEMCO'; // replace with your cooperative code / short name
$periodDir = $baseDir . '/output/' . $period;

if (!is_dir($periodDir)) {
    mkdir($periodDir, 0750, true);
}

$outputFile = $periodDir . '/CIC_PREP_' . $coopCode . '_' . $period . '.csv';
$errorFile  = $periodDir . '/CIC_PREP_' . $coopCode . '_' . $period . '_errors.csv';
$logFile    = $baseDir . '/logs/validation_' . $period . '.log';

// Set true to write invalid rows to a separate error CSV; false = skip only.
$exportInvalidToFile = true;

// -----------------------------------------------------------------------------
// DATA SOURCE
//
// REPLACE WITH DB QUERY — examples:
//
// PDO:
//   $pdo = new PDO('mysql:host=localhost;dbname=tapstemco;charset=utf8mb4', $user, $pass);
//   $asOfDate = date('Y-m-t', strtotime('last month')); // period end
//   $stmt = $pdo->prepare("
//       SELECT
//           m.member_id AS borrower_id,
//           CONCAT(m.firstname, ' ', m.lastname) AS name,
//           m.dob AS date_of_birth,
//           m.gender,
//           m.civil_status,
//           m.nationality,
//           m.phone AS contact_number,
//           m.id_number,
//           l.loan_amount,
//           l.disbursement_date AS loan_date,
//           l.term_months AS loan_term_months,
//           l.outstanding_balance,
//           l.payment_status
//       FROM loans l
//       INNER JOIN members m ON m.member_id = l.member_id
//       WHERE l.status IN ('active', 'closed')
//         AND l.disbursement_date <= :as_of
//   ");
//   $stmt->execute(array(':as_of' => $asOfDate));
//   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
//
// CodeIgniter 2 (from a controller or model):
//   $this->load->database();
//   $rows = $this->db
//       ->select('...')
//       ->from('loans')
//       ->join('members', '...')
//       ->where('...', $asOfDate)
//       ->get()
//       ->result_array();
//   require_once APPPATH . '../cic_export/src/CicValidator.php';
//   // ... then call CicCsvExporter as below
//
// Map your real column names to the logical keys in config/field_map.php
// (borrower_id, name, date_of_birth, ...). Keep those keys stable.
// -----------------------------------------------------------------------------

$rows = array(
    // Valid row
    array(
        'borrower_id'         => 'M-1001',
        'name'                => 'Juan Dela Cruz',
        'date_of_birth'       => '1985-03-15',
        'gender'              => 'M',
        'civil_status'        => 'Married',
        'nationality'         => 'Filipino',
        'contact_number'      => '09171234567',
        'id_number'           => '1234-5678-9012',
        'loan_amount'         => 50000.00,
        'loan_date'           => '2025-01-10',
        'loan_term_months'    => 12,
        'outstanding_balance' => 25000.00,
        'payment_status'      => 'Current',
    ),
    // Valid row — fully paid
    array(
        'borrower_id'         => 'M-1002',
        'name'                => 'Maria Santos',
        'date_of_birth'       => '1990-07-22',
        'gender'              => 'F',
        'civil_status'        => 'Single',
        'nationality'         => 'Filipino',
        'contact_number'      => '09189876543',
        'id_number'           => '9876-5432-1098',
        'loan_amount'         => 20000.00,
        'loan_date'           => '2024-06-01',
        'loan_term_months'    => 6,
        'outstanding_balance' => 0,
        'payment_status'      => 'Paid',
    ),
    // Invalid — missing name, bad date, outstanding > loan_amount
    array(
        'borrower_id'         => 'M-1003',
        'name'                => '',
        'date_of_birth'       => '15-03-1985',
        'gender'              => 'M',
        'civil_status'        => 'Single',
        'nationality'         => 'Filipino',
        'contact_number'      => '09171112222',
        'id_number'           => '1111-2222-3333',
        'loan_amount'         => 10000,
        'loan_date'           => '2025-13-40',
        'loan_term_months'    => 3,
        'outstanding_balance' => 15000,
        'payment_status'      => 'Past Due',
    ),
    // Invalid — loan_amount not > 0
    array(
        'borrower_id'         => 'M-1004',
        'name'                => 'Pedro Reyes',
        'date_of_birth'       => '1978-11-02',
        'gender'              => 'M',
        'civil_status'        => 'Widowed',
        'nationality'         => 'Filipino',
        'contact_number'      => '09173334444',
        'id_number'           => '4444-5555-6666',
        'loan_amount'         => 0,
        'loan_date'           => '2025-02-01',
        'loan_term_months'    => 12,
        'outstanding_balance' => 0,
        'payment_status'      => 'Current',
    ),
);

// -----------------------------------------------------------------------------
// Run export
// -----------------------------------------------------------------------------

$logger = new CicErrorLogger($logFile, true);

$exporter = new CicCsvExporter(array(
    'output_path'         => $outputFile,
    'error_output_path'   => $exportInvalidToFile ? $errorFile : null,
    'export_invalid_rows' => $exportInvalidToFile,
    'field_map'           => $fieldMap,
    'logger'              => $logger,
));

$summary = $exporter->export($rows);

// -----------------------------------------------------------------------------
// Report
// -----------------------------------------------------------------------------

$isCli = (php_sapi_name() === 'cli');
$nl = $isCli ? PHP_EOL : "<br>\n";

echo $isCli ? '' : '<pre>';
echo 'CIC prep export complete' . $nl;
echo 'Period:   ' . $period . $nl;
echo 'Total:    ' . $summary['total'] . $nl;
echo 'Exported: ' . $summary['exported'] . $nl;
echo 'Skipped:  ' . $summary['skipped'] . $nl;
echo 'Output:   ' . $summary['output_file'] . $nl;
if (!empty($summary['error_file'])) {
    echo 'Errors:   ' . $summary['error_file'] . $nl;
}
if (!empty($summary['log_file'])) {
    echo 'Log:      ' . $summary['log_file'] . $nl;
}
echo $isCli ? '' : '</pre>';
