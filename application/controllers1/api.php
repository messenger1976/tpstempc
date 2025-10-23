<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of api
 *
 * @author miltone
 */
class Api extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function delivery() {
        $jsondata = file_get_contents('php://input');
        $data = json_decode($jsondata);
        $this->db->update('sms_sent', array('delivery_status' => $data->status), array('message_id' => $data->message_id));
        echo json_encode(array('status' => 1));
    }

    function send_account() {
        $filehandle = fopen("./cron/lock.txt", "w+");
        if (flock($filehandle, LOCK_EX | LOCK_NB)) {
            $data = $this->db->query("SELECT members_contact.phone1,users.id,users.username,users.oldpass FROM members_contact  INNER JOIN members ON members.PID=members_contact.PID INNER JOIN users ON users.member_id=members.member_id  WHERE users.sms_sent='0' LIMIT 1")->row();
            if($data->phone1<>''){
            $message="Your account credentials. USERNAME : ".$data->username.', PASSWORD : '.$data->oldpass;
            $this->smssending->send_sms_single(SENDER, $message, $data->phone1); 
            $this->db->update('users',array('sms_sent'=>1),array('id'=>$id));
            }
            sleep(1); // Cron job code for demonstration
            flock($filehandle, LOCK_UN);  // don't forget to release the lock
        } else {
            
        }

        fclose($filehandle);
    }
    function send_contribution() {
        $filehandle = fopen("./cron/lock1.txt", "w+");
        if (flock($filehandle, LOCK_EX | LOCK_NB)) {
            $data = $this->db->query("SELECT members_contact.phone1,users.id,users.username,users.oldpass FROM members_contact  INNER JOIN members ON members.PID=members_contact.PID INNER JOIN users ON users.member_id=members.member_id  WHERE users.sms_sent='0' LIMIT 1")->row();
            if($data->phone1<>''){
            $message="Your account credentials. USERNAME : ".$data->username.', PASSWORD : '.$data->oldpass;
            $this->smssending->send_sms_single(SENDER, $message, $data->phone1);    
            }
            sleep(1); // Cron job code for demonstration
            flock($filehandle, LOCK_UN);  // don't forget to release the lock
        } else {
            
        }

        fclose($filehandle);
    }

   

}
