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
    
        $this->load->library('email');
      $this->email->from('info@helmatechnologies.com', 'VIKOBA PLUS');
        $this->email->subject($subject);
        $this->email->to($recipient);
        
        if($bcc){
        $this->email->bcc($bcc);
        }
        
        if (!is_null($attachment)) {
            $this->email->attach($attachment);
        }
        
     
        $this->email->message($message);
        if ($this->email->send()) {
           
            return TRUE;
        } else {
            return FALSE;
        }
    }

}

?>
