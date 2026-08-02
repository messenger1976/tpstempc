<?php
/**
 * Append-only validation error logger for CIC prep exports.
 *
 * Strategy:
 * - Keep log files outside the public web root or deny HTTP access.
 * - Rotate / archive with each monthly or quarterly batch.
 * - Prefer masking id_number and contact_number in log lines; full PII
 *   belongs only in secured export files under access control.
 */
class CicErrorLogger
{
    /** @var string Absolute path to the log file */
    protected $logPath;

    /** @var bool Mask sensitive fields in log lines */
    protected $maskPii;

    /**
     * @param string $logPath Absolute path to log file
     * @param bool   $maskPii Whether to mask id_number / contact_number style values in messages
     */
    public function __construct($logPath, $maskPii = true)
    {
        $this->logPath = $logPath;
        $this->maskPii = (bool) $maskPii;

        $dir = dirname($logPath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0750, true) && !is_dir($dir)) {
                throw new RuntimeException('Unable to create log directory: ' . $dir);
            }
        }
    }

    /**
     * @return string
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * Log a single validation error for a row.
     *
     * @param int    $rowNumber 1-based row index in the batch
     * @param string $borrowerId
     * @param string $field
     * @param string $message
     * @return void
     */
    public function logError($rowNumber, $borrowerId, $field, $message)
    {
        $borrowerId = $this->safeScalar($borrowerId);
        $field = $this->safeScalar($field);
        $message = $this->safeScalar($message);

        if ($this->maskPii) {
            $message = $this->maskSensitiveInMessage($message);
            if ($field === 'id_number' || $field === 'contact_number') {
                $borrowerId = $this->maskValue($borrowerId);
            }
        }

        $line = sprintf(
            '%s | row=%d | borrower_id=%s | field=%s | %s',
            date('Y-m-d H:i:s'),
            (int) $rowNumber,
            $borrowerId !== '' ? $borrowerId : '-',
            $field !== '' ? $field : '-',
            $message
        );

        $this->append($line);
    }

    /**
     * Log all errors from a validator result for one row.
     *
     * @param int   $rowNumber
     * @param array $result Validator result with keys valid, errors, row
     * @return void
     */
    public function logRowErrors($rowNumber, array $result)
    {
        $borrowerId = '';
        if (isset($result['row']['borrower_id'])) {
            $borrowerId = $result['row']['borrower_id'];
        }

        if (empty($result['errors']) || !is_array($result['errors'])) {
            return;
        }

        foreach ($result['errors'] as $error) {
            $field = isset($error['field']) ? $error['field'] : '';
            $message = isset($error['message']) ? $error['message'] : 'Validation failed';
            $this->logError($rowNumber, $borrowerId, $field, $message);
        }
    }

    /**
     * Write a batch summary footer.
     *
     * @param array $summary Keys: total, exported, skipped, output_file, error_file
     * @return void
     */
    public function logBatchSummary(array $summary)
    {
        $total = isset($summary['total']) ? (int) $summary['total'] : 0;
        $exported = isset($summary['exported']) ? (int) $summary['exported'] : 0;
        $skipped = isset($summary['skipped']) ? (int) $summary['skipped'] : 0;
        $outputFile = isset($summary['output_file']) ? $summary['output_file'] : '';
        $errorFile = isset($summary['error_file']) ? $summary['error_file'] : '';

        $this->append(str_repeat('-', 72));
        $this->append(sprintf(
            '%s | BATCH SUMMARY | total=%d | exported=%d | skipped=%d',
            date('Y-m-d H:i:s'),
            $total,
            $exported,
            $skipped
        ));
        if ($outputFile !== '') {
            $this->append(sprintf('%s | output_file=%s', date('Y-m-d H:i:s'), $outputFile));
        }
        if ($errorFile !== '') {
            $this->append(sprintf('%s | error_file=%s', date('Y-m-d H:i:s'), $errorFile));
        }
        $this->append(str_repeat('-', 72));
    }

    /**
     * Write a free-form informational line (e.g. empty dataset warning).
     *
     * @param string $message
     * @return void
     */
    public function logInfo($message)
    {
        $this->append(sprintf('%s | INFO | %s', date('Y-m-d H:i:s'), $this->safeScalar($message)));
    }

    /**
     * Mask a value for logs: keep last 4 characters when long enough.
     *
     * @param mixed $value
     * @return string
     */
    public function maskValue($value)
    {
        $value = $this->safeScalar($value);
        if ($value === '') {
            return '';
        }
        $len = strlen($value);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - 4) . substr($value, -4);
    }

    /**
     * @param string $line
     * @return void
     */
    protected function append($line)
    {
        $ok = file_put_contents(
            $this->logPath,
            $line . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($ok === false) {
            throw new RuntimeException('Unable to write log file: ' . $this->logPath);
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function safeScalar($value)
    {
        if ($value === null) {
            return '';
        }
        if (is_scalar($value)) {
            return trim((string) $value);
        }
        return '';
    }

    /**
     * Best-effort masking of long digit sequences that look like IDs / phones.
     *
     * @param string $message
     * @return string
     */
    protected function maskSensitiveInMessage($message)
    {
        return preg_replace_callback(
            '/\b(\d{7,})\b/',
            function ($matches) {
                return $this->maskValue($matches[1]);
            },
            $message
        );
    }
}
