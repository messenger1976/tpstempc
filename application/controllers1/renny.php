<?php

/*
 * @author Miltone Urassa
 * @contact miltoneurassa@yahoo.com
 * 
 */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Renny extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -  
     * 		http://example.com/index.php/welcome/index
     *
     */
    function __construct() {
        parent::__construct();
        $this->load->library('ion_auth');
        $this->load->library('session');
    }

  function send_email(){
$message='sms sent';
	//send email to students

	     $this->load->library('email');
             $this->email->set_newline("\r\n");         
             $this->email->from('smsmtandao@gmail.com','SMS Mtandao'); // change it to yours
             $this->email->to('renfridfrancis@gmail.com'); // change it to yours
             $this->email->subject('SMS Mtandao');
	     $this->email->message($message);
	     $this->email->send();
	     echo $this->email->print_debugger(); 
	     $this->email->clear();	
} 
    
    
    
    

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
