<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of loan
 *
 * @author Helma Technologies Co Ltd
 */
class Loanbase {

    //put your code here

    function get_installment($rate, $pricipal, $installment, $interest_method = 1, $interval = 1) {

        $amount = 0;
        $rate_required = 0;
        if ($interval == 1) {
            //monthly
            $rate_required = (($rate / 12) / 100);
        }else if($interval == 2){
            //weekly
          $rate_required = (($rate / 52) / 100);   
        }

        if ($interest_method == 1) {
            $up = pow((1 + $rate_required), $installment);

            $down = (pow((1 + $rate_required), $installment) - 1);

            $amount = (($pricipal * ($up / $down)) * $rate_required);
        } else if ($interest_method == 2) {
            $interest_per_month = $rate_required * $pricipal;
            $amount = ($pricipal / $installment) + $interest_per_month;
        }

        return round($amount, 2);
    }

    //

    function totalInterest($rate, $initialprinciple, $installment, $repay_amount, $interest_method = 1, $interval = 1) {
        $rate_required = 0;
        if ($interval == 1) {
            //monthly
            $rate_required = (($rate / 12) / 100);
        }else if($interval == 2){
            //weekly
          $rate_required = (($rate / 52) / 100);   
        }
        
        $interest = 0;
        if ($interest_method == 1) {
            $principal_init = $initialprinciple;

            for ($i = 1; $i <= $installment; $i++) {
                $tmp_int = ($rate_required * $initialprinciple);
                $interest += ($rate_required * $initialprinciple);
                $initialprinciple = ($initialprinciple - ($repay_amount - $tmp_int));
            }
        } else if ($interest_method == 2) {
            $interest_per_month = $rate_required * $initialprinciple;
            $interest = $interest_per_month * $installment;
        }

        return round($interest, 2);
    }

    function create_repayment_schedule($repayamount, $rate, $installment, $startdate, $initialprinciple, $LID, $interest_method = 1, $interval = 1) {
        $rate_required = 0;
        $schedule = array();
        $increase_day = '';
        if ($interval == 1) {
            $rate_required = (($rate / 12) / 100);
            $increase_day = '+1 month';
        }else if($interval == 2){
            //weekly
          $rate_required = (($rate / 52) / 100); 
          $increase_day = "+7 days";
        }

        if ($interest_method == 1) {
            $date = $startdate;
            $principal_init = $initialprinciple;
            $llop = $repayamount;
            for ($i = 1; $i <= $installment; $i++) {
                $repayamount = $llop;
                $array = array();
                //$tmp_int = ($rate_required * $initialprinciple);
                $array['repaydate'] = $date;
                $array['month'] = date('Ym',  strtotime($date));
                $array['LID'] = $LID;
                 $array['PIN'] = current_user()->PIN;
                $array['installment_number'] = $i;
                $array['repayamount'] = $repayamount;
                $interest = $rate_required * $initialprinciple;
                $array['interest'] = round($interest, 2);
                $array['principle'] = round(($repayamount - $interest), 2);
                $balance = ($initialprinciple - $array['principle']);
                // Clamp the running principal, not just the stored balance: a negative
                // running figure bills interest on a negative principal and makes every
                // later row negative. schedule_overrun() is what refuses such a contract.
                $array['balance'] = ($balance > 0 ? round($balance, 2) : 0);
                $schedule[] = $array;
                $initialprinciple = $array['balance'];
                $date1 = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) .$increase_day));
                $date = $date1;
            }
        } else if ($interest_method == 2) {
            $date = $startdate;
            $principal_init = $initialprinciple;
            $llop = $repayamount;
            $interest = $rate_required * $initialprinciple;
            for ($i = 1; $i <= $installment; $i++) {
                $repayamount = $llop;
                $array = array();
                //$tmp_int = ($rate_required * $initialprinciple);
                $array['repaydate'] = $date;
                $array['month'] = date('Ym',  strtotime($date));
                $array['LID'] = $LID;
                $array['PIN'] = current_user()->PIN;
                $array['installment_number'] = $i;
                $array['repayamount'] = $repayamount;
                $array['interest'] = round($interest, 2);
                $array['principle'] = round(($repayamount - $interest), 2);
                $balance = ($initialprinciple - $array['principle']);
                // Same clamp as the declining-balance block above.
                $array['balance'] = ($balance > 0 ? round($balance, 2) : 0);
                $schedule[] = $array;
                $initialprinciple = $array['balance'];
                $date1 = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . $increase_day));
                $date = $date1;
            }
        }
        return $schedule;
    }

    /**
     * Does a generated schedule actually amortise the principal it was built from?
     *
     * create_repayment_schedule() spreads the contract's own installment_amount over
     * the contract's own basic_amount / number_istallment / rate. When those figures
     * disagree (a contract edited after the fact, a reloan, an imported schedule), the
     * term either repays the whole principal long before it ends - which is what used
     * to leave a negative running balance and negative interest on every later row -
     * or never repays it at all. Those rows are what the Amount Due panels show as
     * Principal / Interest and what the repayment planner posts to the GL, so such a
     * contract must be corrected rather than stored.
     *
     * Read-only. Call it before writing a schedule.
     *
     * @param array $schedule         rows from create_repayment_schedule()
     * @param float $initialprinciple loan_contract.basic_amount
     * @return array{ok:bool,total_principle:float,overrun:float,shortfall:float,negative_rows:int}
     */
    function schedule_overrun(array $schedule, $initialprinciple) {
        $principal = round((float) $initialprinciple, 2);
        $total_principle = 0.0;
        $negative_rows = 0;
        foreach ($schedule as $row) {
            $total_principle += isset($row['principle']) ? (float) $row['principle'] : 0.0;
            if (isset($row['interest']) && (float) $row['interest'] < 0) {
                $negative_rows++;
            }
        }
        $total_principle = round($total_principle, 2);
        // Rounding across the term moves this by cents; half a percent (or a peso, for a
        // small loan) is the boundary between rounding and terms that disagree.
        $tolerance = round(max(1.00, abs($principal) * 0.005), 2);
        $delta = round($total_principle - $principal, 2);
        return array(
            'ok' => ($negative_rows === 0 && abs($delta) <= $tolerance),
            'total_principle' => $total_principle,
            'overrun' => round(max(0, $delta), 2),
            'shortfall' => round(max(0, -$delta), 2),
            'negative_rows' => (int) $negative_rows,
        );
    }

    function rowd($repay, $rate, $installment, $interval = 1) {
        $rate_required = 0;
        //$increate_day//
        if ($interval == 1) {
            $rate_required = (($rate / 12) / 100);
        }
        $up = pow((1 + $rate_required), $installment);

        $down = (pow((1 + $rate_required), $installment) - 1);

        $tmp = (($up / $down) * $rate_required);

        return ($repay / $tmp);
    }

}
