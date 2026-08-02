<?php
/**
 * Validates borrower / loan rows before CIC prep CSV export.
 *
 * Enforce only the business rules required for local prep quality.
 * Official CIC validation rules may be stricter — request them from CIC
 * and extend this class or add a CicOfficialValidator when available.
 */
class CicValidator
{
    /** @var string[] Logical field names expected on each row */
    protected $expectedFields = array(
        'borrower_id',
        'name',
        'date_of_birth',
        'gender',
        'civil_status',
        'nationality',
        'contact_number',
        'id_number',
        'loan_amount',
        'loan_date',
        'loan_term_months',
        'outstanding_balance',
        'payment_status',
    );

    /**
     * Validate a single associative row.
     *
     * @param array $row Raw borrower/loan data
     * @return array{valid:bool,errors:array<int,array{field:string,message:string}>,row:array}
     */
    public function validateRow(array $row)
    {
        $errors = array();
        $normalized = $this->normalizeRow($row);

        if ($this->isBlank($normalized['borrower_id'])) {
            $errors[] = array(
                'field'   => 'borrower_id',
                'message' => 'borrower_id is required',
            );
        }

        if ($this->isBlank($normalized['name'])) {
            $errors[] = array(
                'field'   => 'name',
                'message' => 'name is required',
            );
        }

        if (!$this->isValidDateYmd($normalized['date_of_birth'])) {
            $errors[] = array(
                'field'   => 'date_of_birth',
                'message' => 'date_of_birth must be a valid YYYY-MM-DD date',
            );
        }

        if (!$this->isValidDateYmd($normalized['loan_date'])) {
            $errors[] = array(
                'field'   => 'loan_date',
                'message' => 'loan_date must be a valid YYYY-MM-DD date',
            );
        }

        $loanAmount = $normalized['loan_amount'];
        if (!$this->isNumericValue($loanAmount) || (float) $loanAmount <= 0) {
            $errors[] = array(
                'field'   => 'loan_amount',
                'message' => 'loan_amount must be numeric and greater than 0',
            );
        }

        $outstanding = $normalized['outstanding_balance'];
        if (!$this->isNumericValue($outstanding) || (float) $outstanding < 0) {
            $errors[] = array(
                'field'   => 'outstanding_balance',
                'message' => 'outstanding_balance must be numeric and greater than or equal to 0',
            );
        }

        if (
            $this->isNumericValue($loanAmount)
            && $this->isNumericValue($outstanding)
            && (float) $outstanding > (float) $loanAmount
        ) {
            $errors[] = array(
                'field'   => 'outstanding_balance',
                'message' => 'outstanding_balance must not exceed loan_amount',
            );
        }

        return array(
            'valid'  => empty($errors),
            'errors' => $errors,
            'row'    => $normalized,
        );
    }

    /**
     * Validate a list of rows. Returns results keyed by 1-based row number.
     *
     * @param array $rows
     * @return array<int,array{valid:bool,errors:array,row:array}>
     */
    public function validateBatch(array $rows)
    {
        $results = array();
        $rowNumber = 0;

        foreach ($rows as $row) {
            $rowNumber++;
            if (!is_array($row)) {
                $results[$rowNumber] = array(
                    'valid'  => false,
                    'errors' => array(array(
                        'field'   => '_row',
                        'message' => 'Row must be an associative array',
                    )),
                    'row'    => array(),
                );
                continue;
            }
            $results[$rowNumber] = $this->validateRow($row);
        }

        return $results;
    }

    /**
     * Trim strings and format amounts consistently for CSV output.
     *
     * @param array $row
     * @return array
     */
    protected function normalizeRow(array $row)
    {
        $normalized = array();

        foreach ($this->expectedFields as $field) {
            $value = array_key_exists($field, $row) ? $row[$field] : '';

            if (is_string($value)) {
                $value = trim($value);
            } elseif ($value === null) {
                $value = '';
            }

            if ($field === 'loan_amount' || $field === 'outstanding_balance') {
                if ($this->isNumericValue($value)) {
                    $value = number_format((float) $value, 2, '.', '');
                } elseif (is_string($value)) {
                    $value = trim($value);
                }
            }

            if ($field === 'loan_term_months' && $this->isNumericValue($value)) {
                $value = (string) (int) $value;
            }

            $normalized[$field] = $value;
        }

        return $normalized;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isBlank($value)
    {
        if ($value === null) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        return $value === '';
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isValidDateYmd($value)
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $value);
        if ($dt === false) {
            return false;
        }

        $errors = DateTime::getLastErrors();
        if (is_array($errors) && (!empty($errors['warning_count']) || !empty($errors['error_count']))) {
            return false;
        }

        return $dt->format('Y-m-d') === $value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isNumericValue($value)
    {
        if ($value === null || $value === '') {
            return false;
        }
        if (is_bool($value)) {
            return false;
        }
        return is_numeric($value);
    }
}
