<?php
/**
 * CIC prep CSV field map.
 *
 * Keys  = internal logical field names used by validators and DB mapping.
 * Values = CSV column headers written to the export file.
 *
 * Column order follows the array order in this file.
 *
 * When CIC provides an official Subject / Contract schema:
 * 1. Keep the keys stable (or add aliases in your DB-query layer).
 * 2. Change the values to match CIC column names / codes.
 * 3. Reorder entries to match the required file layout.
 * 4. Optionally copy this file to field_map_cic_official.php and
 *    pass that map into CicCsvExporter so prep and official formats coexist.
 *
 * Request the official data dictionary from:
 * datasubmission@creditinfo.gov.ph
 */
return array(
    'borrower_id'         => 'borrower_id',
    'name'                => 'name',
    'date_of_birth'       => 'date_of_birth',
    'gender'              => 'gender',
    'civil_status'        => 'civil_status',
    'nationality'         => 'nationality',
    'contact_number'      => 'contact_number',
    'id_number'           => 'id_number',
    'loan_amount'         => 'loan_amount',
    'loan_date'           => 'loan_date',
    'loan_term_months'    => 'loan_term_months',
    'outstanding_balance' => 'outstanding_balance',
    'payment_status'      => 'payment_status',
);
