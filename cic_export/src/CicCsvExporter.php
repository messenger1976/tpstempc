<?php
/**
 * Export validated borrower / loan rows to UTF-8 (no BOM) CSV for CIC prep.
 *
 * Production CIC submissions often require UTF-8 without BOM text, then ZIP + GPG.
 * This class produces an intermediate validated CSV. Do not add a UTF-8 BOM —
 * CIC technical requirements historically reject BOM-prefixed files.
 */
class CicCsvExporter
{
    /** @var CicValidator */
    protected $validator;

    /** @var CicErrorLogger|null */
    protected $logger;

    /** @var array Logical field => CSV header (order = column order) */
    protected $fieldMap;

    /** @var string Absolute path for valid-row CSV */
    protected $outputPath;

    /** @var string|null Absolute path for invalid-row CSV */
    protected $errorOutputPath;

    /** @var bool When true, write invalid rows to error CSV; otherwise skip them */
    protected $exportInvalidRows;

    /**
     * @param array $options {
     *   @type string           $output_path          Required. Destination CSV path.
     *   @type string|null      $error_output_path    Optional. Invalid-row CSV path.
     *   @type bool             $export_invalid_rows  Default false (skip invalid).
     *   @type array            $field_map            From config/field_map.php
     *   @type CicValidator     $validator            Optional custom validator
     *   @type CicErrorLogger   $logger               Optional logger
     * }
     */
    public function __construct(array $options)
    {
        if (empty($options['output_path']) || !is_string($options['output_path'])) {
            throw new InvalidArgumentException('output_path is required');
        }

        $this->outputPath = $options['output_path'];
        $this->errorOutputPath = !empty($options['error_output_path'])
            ? $options['error_output_path']
            : null;
        $this->exportInvalidRows = !empty($options['export_invalid_rows']);
        $this->fieldMap = !empty($options['field_map']) && is_array($options['field_map'])
            ? $options['field_map']
            : $this->defaultFieldMap();
        $this->validator = isset($options['validator']) && $options['validator'] instanceof CicValidator
            ? $options['validator']
            : new CicValidator();
        $this->logger = isset($options['logger']) && $options['logger'] instanceof CicErrorLogger
            ? $options['logger']
            : null;

        if ($this->exportInvalidRows && $this->errorOutputPath === null) {
            throw new InvalidArgumentException(
                'error_output_path is required when export_invalid_rows is true'
            );
        }

        $this->ensureDirectory(dirname($this->outputPath));
        if ($this->errorOutputPath !== null) {
            $this->ensureDirectory(dirname($this->errorOutputPath));
        }
    }

    /**
     * Validate and export a batch of rows.
     *
     * @param array $rows List of associative arrays
     * @return array{
     *   total:int,
     *   exported:int,
     *   skipped:int,
     *   output_file:string,
     *   error_file:string|null,
     *   log_file:string|null
     * }
     */
    public function export(array $rows)
    {
        $total = count($rows);
        $exported = 0;
        $skipped = 0;

        $outHandle = fopen($this->outputPath, 'wb');
        if ($outHandle === false) {
            throw new RuntimeException('Unable to open output file: ' . $this->outputPath);
        }

        $errorHandle = null;
        if ($this->exportInvalidRows && $this->errorOutputPath !== null) {
            $errorHandle = fopen($this->errorOutputPath, 'wb');
            if ($errorHandle === false) {
                fclose($outHandle);
                throw new RuntimeException('Unable to open error file: ' . $this->errorOutputPath);
            }
        }

        try {
            // UTF-8 without BOM — do not write "\xEF\xBB\xBF"
            $headers = array_values($this->fieldMap);
            fputcsv($outHandle, $headers);

            if ($errorHandle !== null) {
                fputcsv($errorHandle, array_merge($headers, array('error_messages')));
            }

            if ($total === 0) {
                if ($this->logger !== null) {
                    $this->logger->logInfo('Empty dataset: wrote header-only CSV');
                }
            }

            $rowNumber = 0;
            foreach ($rows as $row) {
                $rowNumber++;
                if (!is_array($row)) {
                    $skipped++;
                    if ($this->logger !== null) {
                        $this->logger->logError(
                            $rowNumber,
                            '',
                            '_row',
                            'Row must be an associative array'
                        );
                    }
                    continue;
                }

                $result = $this->validator->validateRow($row);

                if ($result['valid']) {
                    fputcsv($outHandle, $this->mapRowValues($result['row']));
                    $exported++;
                    continue;
                }

                $skipped++;
                if ($this->logger !== null) {
                    $this->logger->logRowErrors($rowNumber, $result);
                }

                if ($errorHandle !== null) {
                    $messages = array();
                    foreach ($result['errors'] as $error) {
                        $field = isset($error['field']) ? $error['field'] : '';
                        $msg = isset($error['message']) ? $error['message'] : '';
                        $messages[] = $field !== '' ? $field . ': ' . $msg : $msg;
                    }
                    $errorRow = $this->mapRowValues($result['row']);
                    $errorRow[] = implode('; ', $messages);
                    fputcsv($errorHandle, $errorRow);
                }
            }
        } finally {
            fclose($outHandle);
            if ($errorHandle !== null) {
                fclose($errorHandle);
            }
        }

        $summary = array(
            'total'       => $total,
            'exported'    => $exported,
            'skipped'     => $skipped,
            'output_file' => $this->outputPath,
            'error_file'  => ($this->exportInvalidRows && $skipped > 0)
                ? $this->errorOutputPath
                : (($this->exportInvalidRows) ? $this->errorOutputPath : null),
            'log_file'    => $this->logger !== null ? $this->logger->getLogPath() : null,
        );

        // Always report error file path when mode is enabled so callers know where it was written
        if ($this->exportInvalidRows) {
            $summary['error_file'] = $this->errorOutputPath;
        }

        if ($this->logger !== null) {
            $this->logger->logBatchSummary($summary);
        }

        return $summary;
    }

    /**
     * @param array $row Normalized row keyed by logical field names
     * @return array Ordered values matching field map
     */
    protected function mapRowValues(array $row)
    {
        $values = array();
        foreach (array_keys($this->fieldMap) as $field) {
            $values[] = array_key_exists($field, $row) ? $row[$field] : '';
        }
        return $values;
    }

    /**
     * @param string $dir
     * @return void
     */
    protected function ensureDirectory($dir)
    {
        if ($dir === '' || $dir === '.' || is_dir($dir)) {
            return;
        }
        if (!mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Unable to create directory: ' . $dir);
        }
    }

    /**
     * @return array
     */
    protected function defaultFieldMap()
    {
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
    }
}
