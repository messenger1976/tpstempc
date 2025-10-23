<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of migration
 *
 * @author miltone
 */
class Migration extends CI_Controller {

    //put your code here
    public $DB1 = null;
    public $db = null;

    function __construct() {
        parent::__construct();

        $oldsaccoss['hostname'] = 'localhost';
        $oldsaccoss['username'] = 'root';
        $oldsaccoss['password'] = '19881988';
        $oldsaccoss['database'] = 'saccos_nhif2015'; //'dvisacco_new';
        $oldsaccoss['dbdriver'] = 'mysql';
        $oldsaccoss['dbprefix'] = '';
        $oldsaccoss['pconnect'] = TRUE;
        $oldsaccoss['db_debug'] = TRUE;
        $oldsaccoss['cache_on'] = FALSE;
        $oldsaccoss['cachedir'] = '';
        $oldsaccoss['char_set'] = 'utf8';
        $oldsaccoss['dbcollat'] = 'utf8_general_ci';
        $oldsaccoss['swap_pre'] = '';
        $oldsaccoss['autoinit'] = TRUE;
        $oldsaccoss['stricton'] = FALSE;

        $db['hostname'] = 'localhost';
        $db['username'] = 'root';
        $db['password'] = '19881988';
        $db['database'] = 'saccos_nhif2015new'; //'dvisacco_new';
        $db['dbdriver'] = 'mysql';
        $db['dbprefix'] = '';
        $db['pconnect'] = TRUE;
        $db['db_debug'] = TRUE;
        $db['cache_on'] = FALSE;
        $db['cachedir'] = '';
        $db['char_set'] = 'utf8';
        $db['dbcollat'] = 'utf8_general_ci';
        $db['swap_pre'] = '';
        $db['autoinit'] = TRUE;
        $db['stricton'] = FALSE;


        $this->DB1 = $this->load->database($oldsaccoss, TRUE);
        $this->db = $this->load->database($db, TRUE);

        $this->db->db_select();
        $this->load->model('member_model');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
        $this->load->library('ion_auth');
        $this->load->model('share_model');
    }

    //run first this function
    function migrate_member() {
        $this->DB1->db_select();
        $member = $this->DB1->query("SELECT * FROM members")->result();

        $this->db->db_select();
        foreach ($member as $key => $value) {

            //echo $value->member_name . '<br/>';
            if (!$this->member_model->is_member_exist($value->membership_no)) {
                //add new member

                $new_member = array(
                    'member_id' => $value->membership_no,
                    'firstname' => ucwords(trim(strtolower($value->member_name))),
                    'middlename' => ucwords(trim(strtolower($value->middle_name))),
                    'lastname' => ucwords(trim(strtolower($value->sur_name))),
                    'gender' => substr(trim($value->gender), 0, 1),
                    'maritalstatus' => trim($value->marital),
                    'dob' => trim($value->date_of_birth),
                    'joiningdate' => trim($value->joining_date),
                    'createdby' => 1
                );

                $return = $this->member_model->add_member($new_member, 0);
                if ($return) {
                    $member_id = $value->membership_no;
                    $username = $member_id;
                    $email = $member_id;
                    $password = alphaID($return, FALSE, 4);

                    // create account for login
                    $additional_data = array(
                        'first_name' => $new_member['firstname'],
                        'last_name' => $new_member['lastname'],
                        'member_id' => $member_id,
                        'oldpass' => $password,
                        'MID' => $return,
                    );

                    $this->ion_auth->register($username, $password, $email, $additional_data, array(3));
                    echo 'Member No : ' . $value->membership_no . ' === ' . $value->member_name . ' ' . $value->middle_name . ' ' . $value->member_name . '<br/>';
                }
            }
        }
    }

    function migrate_contribution_settings() {
        $this->DB1->db_select();
        $contribution = $this->DB1->query("SELECT * FROM contributions")->result();

        $this->db->db_select();
        foreach ($contribution as $key => $value) {
            $userdata = $this->db->query("SELECT * from members where member_id='$value->member_id'")->row();


            $PID = $userdata->PID;
            $member_id = $value->member_id;

            $source = ($value->contribution_source == 'Salary' ? 2 : 1);
            $amount = $value->contribution;
            if ($value->contribution > 0) {
                $info = array(
                    'PID' => $PID,
                    'member_id' => $member_id,
                    'contribute_source' => $source,
                    'amount' => $amount,
                    'createdby' => 1,
                );
                $accountdata = $this->contribution_model->contribution_setting($info, $id);
                if ($accountdata) {
                    echo 'Member No : ' . $member_id . ' Contribution : ' . $amount . '<br/>';
                }
            }
        }
    }

    function migrate_contribution_payment() {
        $this->DB1->db_select();
        $contributionpayment = $this->DB1->query("SELECT * FROM contributions_payment order by date ASC")->result();
        foreach ($contributionpayment as $key => $value) {
            $this->DB1->db_select();
            $contribution = $this->DB1->query("SELECT * FROM contributions WHERE id='$value->contribution_id'")->row();
            if (count($contribution) > 0) {
                $this->db->db_select();
                $userdata = $this->db->query("SELECT * from members where member_id='$contribution->member_id'")->row();

                $pid = $userdata->PID;
                $member_id = $userdata->member_id;



                $trans_type = 'CR';

                $comment = 'Contribution';
                $paymethod = 'CASH';
                $amount = $value->amount;
                $customer_name = $userdata->firstname . ' ' . $userdata->lastname;
                $continue = true;
                $date = $value->date;
                $check_number_received = '';
                if ($continue) {
                    //now finalize

                    $receipt = $this->contribution_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $check_number_received, $month = '', $auto = 0, $date);
                    if ($receipt) {
                        echo 'Receipt No : ' . $receipt . ' created Amount ==' . $amount = $value->amount . 'Member ID==' . $member_id . '<br/>';
                    }
                }
            }
        }
    }

    //compare last update
    function migrate_contribution_commulative() {
        $this->DB1->db_select();
        $contributionpayment = $this->DB1->query("SELECT * FROM contributions")->result();

        foreach ($contributionpayment as $key => $contribution) {
            $this->db->db_select();
            $userdata = $this->db->query("SELECT * from members where member_id='$contribution->member_id'")->row();

            $comulative = $this->db->query("SELECT * FROM members_contribution WHERE member_id='$contribution->member_id'")->row();
            $pid = $userdata->PID;
            $member_id = $userdata->member_id;


            $trans_type = 'CR';

            $comment = 'CONTRIBUTION_MIGRATED';
            $paymethod = 'CASH';
            $amount = ($contribution->cumulative_contribution - $comulative->balance);

            $customer_name = $userdata->firstname . ' ' . $userdata->lastname;
            $continue = true;
            $date = date('Y-m-d');
            $check_number_received = '';
            if ($amount != 0) {
                if ($continue) {
                    //now finalize
                    $receipt = $this->contribution_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $check_number_received, $month = '', $auto = 0, $date);
                    if ($receipt) {
                        echo 'Receipt No : ' . $receipt . ' created Amount ==' . $amount = $value->amount . 'Member ID==' . $member_id . '<br/>';
                    }
                }
            }
        }
    }

    //used in migrate share

    function is_max_share_reached($newshare, $previuous_share, $maxshare) {
        $temp = $newshare + $previuous_share;
        if ($temp > $maxshare) {
            return TRUE;
        }
        return FALSE;
    }

    function get_share_from_amount($dividend, $divisor) {
        $quotient = intval($dividend / $divisor);
        $remainder = $dividend % $divisor;
        return array($quotient, $remainder);
    }

    function migrate_share() {
        $this->DB1->db_select();
        $contributionpayment = $this->DB1->query("SELECT * FROM share_payment ORDER BY date ASC")->result();


        foreach ($contributionpayment as $key => $value) {
            $this->DB1->db_select();
            $contribution = $this->DB1->query("SELECT * FROM share_master WHERE id='$value->share_id'")->row();

            if (count($contribution) > 0) {
                $this->db->db_select();
                $userdata = $this->db->query("SELECT * from members where member_id='$contribution->member_id'")->row();

                $pid = $userdata->PID;
                $member_id = $userdata->member_id;
                $amount = $value->amount;
                $real_amount = $amount;
                $comment = 'Buy Share';
                $paymethod = 'CASH';
                $check_number_received = '';
                $date = $value->date;
                $share_info = $this->share_model->share_member_info($pid, $member_id);
                $share_setup = $this->setting_model->share_setting_info();
                $cost_per_share = $share_setup->amount;

                if ($share_info) {
                    //share row exist
                    $amount = $amount + $share_info->remainbalance;
                    $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                    $totalshare = $share_item[0];
                    $remaining_amount = $share_item[1];
                    $share_number = 0;
                    $check_number_received = '';
                    $is_max_share_reached = $this->is_max_share_reached($totalshare, $share_info->totalshare, $share_setup->max_share);
                    if (!$is_max_share_reached) {
                        //safe to add share
                        $share_number = $totalshare;
                        $amountshare = ($totalshare * $share_setup->amount);
                        $remain_amount = $remaining_amount;
                        $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received, $date);
                        if ($add_share) {
                            echo 'Transaction no : ' . $add_share . ' created <br/>';
                        }
                    }
                } else {
                    // share row not exist

                    $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                    $totalshare = $share_item[0];
                    $remaining_amount = $share_item[1];
                    $share_number = 0;
                    $is_max_share_reached = $this->is_max_share_reached($totalshare, 0, $share_setup->max_share);
                    if (!$is_max_share_reached) {
                        //safe to add share
                        $share_number = $totalshare;
                        $amountshare = ($totalshare * $share_setup->amount);
                        $remain_amount = $remaining_amount;
                        $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received, $date);

                        if ($add_share) {

                            echo 'Transaction no : ' . $add_share . ' created <br/>';
                        }
                    }
                }
            }
        }
    }

    function migrate_share_commullative() {
        $this->DB1->db_select();
        $contributionpayment = $this->DB1->query("SELECT * FROM share_master ")->result();


        foreach ($contributionpayment as $key => $value) {
            $this->db->db_select();
            $contribution = $this->DB1->query("SELECT * FROM members_share WHERE member_id='$value->member_id'")->row();

            //if (count($contribution) > 0) {
            $this->db->db_select();
            $userdata = $this->db->query("SELECT * from members where member_id='$value->member_id'")->row();

            $pid = $userdata->PID;
            $member_id = $userdata->member_id;
            if (count($contribution) > 0) {
                $amount = ($value->cumulative_contribution - $contribution->amount - $contribution->remainbalance);
            } else {
                $amount = $value->cumulative_contribution;
            }
            
            if($amount > 1){
                
            
            $real_amount = $amount;
            $comment = 'BUY_SHARE_MIGRATE';
            $paymethod = 'CASH';
            $check_number_received = '';
            $date = date('Y-m-d');
            $share_info = $this->share_model->share_member_info($pid, $member_id);
            $share_setup = $this->setting_model->share_setting_info();
            $cost_per_share = $share_setup->amount;

            if ($share_info) {
                //share row exist
                    $amount = $amount + $share_info->remainbalance;
                    $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                    $totalshare = $share_item[0];
                    $remaining_amount = $share_item[1];
                    $share_number = 0;
                    $check_number_received = '';
                    $is_max_share_reached = $this->is_max_share_reached($totalshare, $share_info->totalshare, $share_setup->max_share);
                    if (!$is_max_share_reached) {
                        //safe to add share
                        $share_number = $totalshare;
                        $amountshare = ($totalshare * $share_setup->amount);
                        $remain_amount = $remaining_amount;
                        $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received, $date);
                        if ($add_share) {
                            echo 'Transaction no : ' . $add_share . ' created <br/>';
                        }
                    }
                
            } else {
                // share row not exist
                    $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                    $totalshare = $share_item[0];
                    $remaining_amount = $share_item[1];
                    $share_number = 0;
                    $is_max_share_reached = $this->is_max_share_reached($totalshare, 0, $share_setup->max_share);
                    if (!$is_max_share_reached) {
                        //safe to add share
                        $share_number = $totalshare;
                        $amountshare = ($totalshare * $share_setup->amount);
                        $remain_amount = $remaining_amount;
                        $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received, $date);

                        if ($add_share) {

                            echo 'Transaction no : ' . $add_share . ' created <br/>';
                        }
                    }
                
            }
            
            
            }
            //}
        }
    }

}
