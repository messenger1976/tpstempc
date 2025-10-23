<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_
 *
 * @author miltone
 */
class MY_Form_validation extends CI_Form_validation {

    public function __construct()
    {
        parent::__construct();
    }


    function valid_phone($str) {
        if ($str != "") {
            $str = str_replace(' ', '', trim($str));
            if (preg_match("/^[0-9]{10}$/", $str)) {
                return TRUE;
            } else {
               // $CI->form_validation->set_message('valid_phone', "The %s must contain 9 digit eg: 712765538");
                return FALSE;
            }
        }
    }
    
    
     /*
     * @author Miltone Urassa
     * validate date. the date format should be YYYY-mm-dd
     * eg 2013-07-28
     */

    function valid_date($date) {
        if ($date != "") {
            if (preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date)) {
                $date_array = explode("-", $date);
                if (checkdate($date_array[1], $date_array[0], $date_array[2])) {
                    return TRUE;
                } else {
                //    $CI->form_validation->set_message('valid_date', "The %s must contain DD-MM-YYYY");
                    return FALSE;
                }
            } else {
              //  $CI->form_validation->set_message('valid_date', "The %s must contain DD-MM-YYYY");
                return FALSE;
            }
        }
    }
    function valid_month($date) {
        if ($date != "") {
            if (preg_match("/^[0-9]{2}-[0-9]{4}$/", $date)) {
               
               $date_array = explode("-", $date);
               
                if (checkdate($date_array[0], 01, $date_array[1])) {
                    return TRUE;
                } else {
                //    $CI->form_validation->set_message('valid_date', "The %s must contain DD-MM-YYYY");
                    return FALSE;
                }
            } else {
              //  $CI->form_validation->set_message('valid_date', "The %s must contain DD-MM-YYYY");
                return FALSE;
            }
        }
    }


}

?>
