<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Loan_eligibility - loan application rules shared by the staff Loan
 * Application form (Loan controller) and the Member Loan App.
 *
 * The share, contribution-minimum, contribution-times and maximum-loan checks
 * are switched off on purpose (see ESKB 10_LOANS/LOAN_APPLICATION_BYPASSES.md);
 * they stay off here so both channels apply exactly the same rules.
 */
class Loan_eligibility {

    /** Amount the maximum-loan rule would allow (set by maximum_loan_allowed). */
    public $loan_allowed = 0;

    /** @var CI_Controller */
    private $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Run every rule for a new application.
     *
     * @return array('ok' => bool, 'message' => string, 'installment_amount' => float, 'total_interest' => float)
     */
    public function check($product, $amount, $installment, $pid, $member_id) {
        $this->CI->load->library('loanbase');
        $this->CI->load->model('share_model');
        $this->CI->load->model('finance_model');
        $this->CI->load->model('contribution_model');
        $this->CI->lang->load('loan');

        $out = array('ok' => false, 'message' => '', 'installment_amount' => 0, 'total_interest' => 0);
        if (!$product) {
            $out['message'] = 'Please choose a loan product.';
            return $out;
        }
        if ((float) $amount <= 0 || (int) $installment <= 0) {
            $out['message'] = 'Enter the amount and the number of installments.';
            return $out;
        }
        if (!($product->maxmum_time >= $installment)) {
            $out['message'] = lang('loan_maximum_duration');
            return $out;
        }

        $share_info = $this->CI->share_model->share_member_info($pid, $member_id);
        $saving_account = $this->CI->finance_model->saving_account_balance_PID($pid, $member_id);
        $contribution = $this->CI->contribution_model->contribution_balance($pid, $member_id);

        if (!$this->pass_contribution_condition($product, $contribution)) {
            $out['message'] = lang('loan_contribution_insufficient');
            return $out;
        }
        if (!$this->pass_saving_condition($product, $saving_account)) {
            $out['message'] = lang('loan_saving_insufficient');
            return $out;
        }
        if (!$this->pass_share_condition($product, $share_info)) {
            $out['message'] = lang('loan_share_insufficient');
            return $out;
        }
        if (!$this->maximum_contributions_times($product, $amount, $contribution)) {
            $out['message'] = lang('loan_contribution_times_exceed');
            return $out;
        }
        if (!$this->maximum_loan_allowed($product, $amount, $contribution, $pid)) {
            $out['message'] = lang('loan_not_allowed', number_format($this->loan_allowed, 2));
            return $out;
        }

        $installment_amount = $this->CI->loanbase->get_installment($product->interest_rate, $amount, $installment, $product->interest_method, $product->interval);
        $total_interest = $this->CI->loanbase->totalInterest($product->interest_rate, $amount, $installment, $installment_amount, $product->interest_method, $product->interval);

        $out['ok'] = true;
        $out['installment_amount'] = $installment_amount;
        $out['total_interest'] = $total_interest;
        return $out;
    }

    function pass_monthly_income($monthy_income, $pid, $newinstall = 0, $exclude_lid = null) {
        $pin = current_user()->PIN;
        $monthly_contribution = 0;
        $contr_setup = $this->CI->db->get_where('contribution_settings', array('PID' => $pid, 'PIN' => $pin))->row();
        if ($contr_setup) {
            $monthly_contribution = $contr_setup->amount;
        } else {
            $this->CI->db->where('PIN', $pin);
            $contr_setup = $this->CI->db->get('contribution_global')->row();
            $monthly_contribution = $contr_setup ? $contr_setup->amount : 0;
        }
        //check open_loan (when editing, exclude current loan so its installment is only counted via $newinstall)
        $sql = "SELECT * FROM loan_contract WHERE PID='" . $this->CI->db->escape_str($pid) . "' AND disburse=1 AND status=4";
        if ($exclude_lid !== null && $exclude_lid !== '') {
            $sql .= " AND LID != '" . $this->CI->db->escape_str($exclude_lid) . "'";
        }
        $open_loan = $this->CI->db->query($sql)->result();
        $repay_installment = 0;
        foreach ($open_loan as $value) {
            $repay_installment += $value->installment_amount;
        }

        $total_contr_from_salary = $repay_installment + $monthly_contribution + $newinstall;
        $remain_theluth = ((1 / 3) * $monthy_income);
        if ($total_contr_from_salary <= $remain_theluth) {
            return TRUE;
        }
        return FALSE;
    }

    function pass_share_condition($product, $share) {
        // TEMPORARILY DISABLED - share requirement check removed for loan application
        return TRUE;
        /*
        if ($product->loan_security_share_min > 0) {
            if ($share) {
                if ($share->totalshare >= $product->loan_security_share_min) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        return TRUE;
        */
    }

    function pass_saving_condition($product, $saving) {
        if ($product->loan_security_saving_minimum > 0) {
            if ($saving) {
                if ($saving->balance >= $product->loan_security_saving_minimum) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        return TRUE;
    }

    function maximum_loan_allowed($product, $loan_amount, $contribution, $pid) {
        // TEMPORARILY DISABLED - maximum loan amount check bypassed
        return TRUE;
        /*
        $total_amount = $contribution->balance * $product->loan_security_contribution_times;

        $open_loan = $this->CI->db->query("SELECT * FROM loan_contract WHERE PID='$pid' AND approval=4")->result();

        $principles = 0;
        $amount_paid = 0;
        foreach ($open_loan as $key => $value) {
            $amount_paid += $this->CI->db->query("SELECT SUM('amount') as amount FROM loan_repayment_receipt WHERE LID='$value->LID'")->row()->amount;
            $principles += $value->basic_amount;
        }
        $open_principle = $principles - $amount_paid;
        $allowed = $total_amount - $open_principle;
        $this->loan_allowed = $allowed;
        if ($allowed >= $loan_amount) {
            return TRUE;
        }
        return FALSE;
        */
    }

    function maximum_contributions_times($product, $loan_amount, $contribution) {
        // TEMPORARILY DISABLED - contribution times check bypassed
        return TRUE;
        /*
        $total_amount = $contribution->balance * $product->loan_security_contribution_times;
        if ($total_amount == 0) {
            return TRUE;
        } else {
            if (count($contribution) > 0) {
                if ($loan_amount <= $total_amount) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }
        return TRUE;
        */
    }

    function pass_contribution_condition($product, $contribution) {
        // TEMPORARILY DISABLED - minimum contribution amount check bypassed
        return TRUE;
        /*
        if ($product->loan_security_contribution_min > 0) {
            if ($contribution) {
                if ($contribution->balance >= $product->loan_security_contribution_min) {

                    return TRUE;
                } else {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        return TRUE;
        */
    }
}
