<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of smssending
 *
 * @author Helma Technologies Co Ltd
 */
class Smssending {

    //put your code here

    function __construct() {
        
    }

    public function __get($var) {
        return get_instance()->$var;
    }

    
      function send_sms_single($sender, $message, $recepient) {
        
        $recepient_mobile = array();
        $log = array();
        
            $message_id = alphaID(time() . substr($recepient,-9));
            $recepient_mobile[$message_id] = $recepient;
            $key = 0;
            $log[$key]['message_id'] = $message_id;
            $log[$key]['mobile'] = $recepient;
            $log[$key]['message'] = $message;
            $log[$key]['sender'] = $sender;
        
        $array_to_json = array(
            'token' => API_TOKEN,
            'sender' => $sender,
            'message' => $message,
            'push' => PUSH_URL,
            'recipient' => $recepient_mobile
        );

        $json_string = json_encode($array_to_json);
        $this->curl->create(API_URL);
        $this->curl->options(
                array(
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                    CURLOPT_POSTFIELDS => $json_string,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_POST => 1
                )
        );
        $send = $this->curl->execute();
        
        $json_return = json_decode($send);
        if(isset($json_return->status)){
        if ($json_return->status == 'succeed') {
            $this->db->insert_batch('sms_sent', $log);
            return $json_return->description;
        } else {
            return $json_return->description;
        }
        }else{
            return 'NETWORK PROBLEM';
        }
    }

    function send_sms($sender, $message, $recepient = array()) {
       
        $recepient_mobile = array();
        $log = array();
        foreach ($recepient as $key => $value) {
            $message_id = alphaID(time() . substr($value->mobile,-9));
            $recepient_mobile[$message_id] = $value->mobile;
            $log[$key]['message_id'] = $message_id;
            $log[$key]['mobile'] = $value->mobile;
            $log[$key]['message'] = $message;
            $log[$key]['sender'] = $sender;
        }
        $array_to_json = array(
            'token' => API_TOKEN,
            'sender' => $sender,
            'message' => $message,
            'push' => PUSH_URL,
            'recipient' => $recepient_mobile
        );

        $json_string = json_encode($array_to_json);
        $this->curl->create(API_URL);
        $this->curl->options(
                array(
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                    CURLOPT_POSTFIELDS => $json_string,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_POST => 1
                )
        );
        $send = $this->curl->execute();
        
        $json_return = json_decode($send);
        if(isset($json_return->status)){
        if ($json_return->status == 'succeed') {
            $this->db->insert_batch('sms_sent', $log);
            return $json_return->description;
        } else {
            return $json_return->description;
        }
        }else{
            return 'SMS SENT';
        }
    }

}
