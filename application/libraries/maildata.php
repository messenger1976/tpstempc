<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of maildata
 *
 * @author Helma Technologies Tanzania
 */
class Maildata {

    //put your code here

    /** Safe delivery error. Never contains the SMTP password or message body. */
    public $last_error = '';

    function __construct() {
        
    }

    public function __get($var) {
        return get_instance()->$var;
    }
    
    
    function send_login_credential($name, $email, $password,$reply=null) {
        $company_reseller = reseller_info(current_user()->reffid);
        $subject = "VIKOBA PLUS";
        $message = "Hello {$name}, 
                    <div><br/><br/> Welcome to VIKOBA PLUS. <br/>
Please your advised to change your password once you login for the first time</div>";
        $message.= '<div><br/>Username : ' . $email . '<br/>Password : ' . $password . '<br/> Login URL :' . site_url(current_lang() . '/auth/login') . '</div>
<div><br/><br/>Kind Regards , <br/>  ' . $company_reseller->company . '<br/> Mobile : ' . $company_reseller->mobile . '<br/>Email : ' . $company_reseller->email . '<br/>Powered by : ' . lang('vendor') . '</div>             
';
        if ($this->send_email($subject, $message, $recipient = array($email),$company_reseller->email)) {
            return TRUE;
        }

        return FALSE;
    }

    function send_email($subject, $message, $recipient=array(), $bcc=array(), $attachment=null) {
        $this->last_error = '';
        $this->load->library('email');
        $this->load->library('smtp_settings');
        $this->load->model('smtp_settings_model');

        $plan = $this->smtp_settings->transport_plan(
            $this->smtp_settings_model->read_row(),
            $this->smtp_settings->load_key()
        );
        $result = $this->smtp_settings->deliver(
            $this->email,
            $plan,
            $subject,
            $message,
            $recipient,
            $bcc,
            $attachment
        );
        $this->last_error = (is_array($result) && isset($result['error'])) ? (string) $result['error'] : '';
        if (empty($result['ok']) && is_array($plan) && isset($plan['mode']) && $plan['mode'] !== 'legacy') {
            log_message('error', 'Outgoing mail was not sent. ' . $this->last_error);
        }
        return !empty($result['ok']);
    }

}

?>
