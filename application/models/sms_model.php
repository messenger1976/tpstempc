<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sms_model
 *
 * @author miltone
 */
class Sms_Model extends CI_Model {

    //put your code here

    function __construct() {
        parent::__construct();
    }

    function sender_list($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('sms_senderid');
    }

    function group_list($id = null) {
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        return $this->db->get('sms_contact_group');
    }

    function add_sender($array, $id = null) {
        if (!is_null($id)) {
            return $this->db->update('sms_senderid', $array, array('id' => $id));
        } else {
            return $this->db->insert('sms_senderid', $array);
        }
    }

    function add_group($array, $id = null) {
        if (!is_null($id)) {
            return $this->db->update('sms_contact_group', $array, array('id' => $id));
        } else {
            return $this->db->insert('sms_contact_group', $array);
        }
    }

    function count_contact($key = null) {

        if (!is_null($key)) {
            $this->db->or_like('name', $key);
            $this->db->or_like('mobile', $key);
        }

        return count($this->db->get('sms_contact')->result());
    }

    function search_contact($key, $limit, $start) {

        if (!is_null($key)) {
            $this->db->or_like('name', $key);
            $this->db->or_like('mobile', $key);
        }

        $this->db->limit($limit, $start);
        return $this->db->get('sms_contact')->result();
    }

    function add_contact($data, $id = null) {
        if (!is_null($id)) {
            return $this->db->update('sms_contact', $data, array('id' => $id));
        } else {
            return $this->db->insert('sms_contact', $data);
        }
    }

    function count_sms_contact($gp) {

        if ($gp == 1) {
            return $this->db->query("SELECT COUNT(phone1) as phone FROM members_contact")->row()->phone;
        } else if ($gp == 2) {
            return $this->db->query("SELECT COUNT(phone) as phone FROM customer")->row()->phone;
        } else if ($gp == 3) {
            return $this->db->query("SELECT COUNT(phone) as phone FROM supplier")->row()->phone;
        } else {
            return $this->db->query("SELECT COUNT(mobile) as phone FROM sms_contact WHERE `group`='$gp'")->row()->phone;
        }
    }

    function group_number($gp) {

        if ($gp == 1) {
            return $this->db->query("SELECT phone1 as mobile FROM members_contact")->result();
        } else if ($gp == 2) {
            return $this->db->query("SELECT phone as mobile FROM customer")->result();
        } else if ($gp == 3) {
            return $this->db->query("SELECT phone as mobile FROM supplier")->result();
        } else {
            return $this->db->query("SELECT mobile as mobile FROM sms_contact WHERE `group`='$gp'")->result();
        }
    }

}
