<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of member_model
 *
 * @author miltone
 */
class Member_Model extends CI_Model {

    //put your code here
    function __construct() {
        parent::__construct();
    }

    function is_member_exist($member_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('member_id', $member_id);
        $data = $this->db->get('members')->row();
        if (count($data) == 1) {
            return TRUE;
        }

        return FALSE;
    }

    function add_member($data, $registrationfee = 0) {
        $pin = current_user()->PIN;
        $SMID = $this->db->get('auto_inc')->row()->PID;
        // increatent 1 next PIN
        $this->db->set('PID', 'PID+1', FALSE);
        $this->db->update('auto_inc');
        $data['PID'] = $SMID;
        $this->db->insert('members', $data);
        $return = $this->db->insert_id();

        $array_registration = array(
            'date' => date('Y-m-d'),
            'PID' => $data['PID'],
            'member_id' => $data['member_id'],
            'credit' => $registrationfee,
            'createdby' => current_user()->id,
            'PIN' => $pin,
        );

        $this->db->insert('member_registrationfee', $array_registration);
        $refferenceid = $this->db->insert_id();
        //now insert to income journal
        $credit_account = 4000001;
        $debit_account = 1000003;

        $ledger_entry = array('date' => date('Y-m-d'));
        $this->db->insert('general_ledger_entry', $ledger_entry);
        $ledger_entry_id = $this->db->insert_id();

        //update ledger book
        $ledgerbook = array(
            'journalID' => 2,
            'refferenceID' => $refferenceid,
            'entryid' => $ledger_entry_id,
            'date' => date('Y-m-d'),
            'description' => 'Member Registration ',
            'linkto' => 'member_registrationfee.PID',
            'fromtable' => 'member_registrationfee',
            'PID' => $data['PID'],
            'member_id' => $data['member_id'],
            'PIN' => $pin
        );

        $ledgerbook['account'] = $credit_account;
        $ledgerbook['credit'] = $registrationfee;
        $ledgerbook['account_type'] = account_row_info($ledgerbook['account'])->account_type;
        $this->db->insert('general_ledger', $ledgerbook);

        $ledgerbook['credit'] = 0;
        $ledgerbook['debit'] = 0;
        //retain earning
        $ledgerbook['account'] = 3000002;
        $ledgerbook['credit'] = $registrationfee;
        $ledgerbook['account_type'] = account_row_info($ledgerbook['account'])->account_type;
        $this->db->insert('general_ledger', $ledgerbook);

        $ledgerbook['credit'] = 0;
        $ledgerbook['debit'] = 0;
        $ledgerbook['account'] = $debit_account;
        $ledgerbook['account_type'] = account_row_info($ledgerbook['account'])->account_type;

        $ledgerbook['debit'] = $registrationfee;
        $this->db->insert('general_ledger', $ledgerbook);

        return $return;
    }

    function add_group($data) {
        $SMID = $this->db->get('auto_inc')->row()->GID;
        // increatent 1 next PIN
        $this->db->set('GID', 'GID+1', FALSE);
        $this->db->update('auto_inc');
        $data['GID'] = $SMID;
        $this->db->insert('members_grouplist', $data);
        $return = $this->db->insert_id();

        /* $acc = $this->db->get('auto_account')->row()->GID;
          // increatent 1 next PIN
          $this->db->set('Number', 'Number+1', FALSE);
          $this->db->update('auto_account');



          $member_account_saving = array(
          'account' => $acc,
          'RFID' => $SMID,
          'member_id' => '_',
          'createdby' => $data['createdby'],
          'PIN' => $data['PIN'],
          'tablename' => 'members_grouplist'
          );
          $this->db->insert('members_account', $member_account_saving);
         */
        return $return;
    }

    function member_group($id = null, $account = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }

        if (!is_null($account)) {
            $this->db->where('account', $account);
        }

        return $this->db->get('members_grouplist');
    }

    function edit_member($data, $id) {
        return $this->db->update('members', $data, array('id' => $id));
    }

    function edit_group($data, $id) {
        return $this->db->update('members_grouplist', $data, array('id' => $id));
    }

    function member_contact($pid) {

        $this->db->where('PID', $pid);
        $return = $this->db->get('members_contact')->row();

        if (count($return) == 0) {
            $fields = $this->db->list_fields('members_contact');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }

        return $return;
    }

    function member_nextkin($pid) {
        $this->db->where('PID', $pid);
        $return = $this->db->get('members_nextkin')->row();
        if (count($return) == 0) {
            $fields = $this->db->list_fields('members_nextkin');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }

        return $return;
    }

    function add_contact($data, $id, $formstatus) {
        //check if data exist
        $this->db->where('PID', $data['PID']);
        $contact = $this->db->get('members_contact')->row();
        if (count($contact) == 0) {
            $return = $this->db->insert('members_contact', $data);
        } else {
            $return = $this->db->update('members_contact', $data, array('PID' => $data['PID']));
        }

        if ($formstatus < 3) {
            $this->db->update('members', array('formstatus' => 2), array('id' => $id));
        }

        return $return;
    }

    function add_nextkininfo($data, $id, $formstatus) {
        //check if data exist
        $this->db->where('PID', $data['PID']);
        $contact = $this->db->get('members_nextkin')->row();
        if (count($contact) == 0) {
            $return = $this->db->insert('members_nextkin', $data);
        } else {
            $return = $this->db->update('members_nextkin', $data, array('PID' => $data['PID']));
        }

        if ($formstatus < 3) {
            $this->db->update('members', array('formstatus' => 3), array('id' => $id));
        }

        return $return;
    }

    function member_basic_info($id = null, $PID = null, $member_id = null) {
        $this->db->where('PIN', current_user()->PIN);
        if (!is_null($id)) {
            $this->db->where('id', $id);
        }
        if (!is_null($PID)) {
            $this->db->where('PID', $PID);
        }
        if (!is_null($member_id)) {
            $this->db->where('member_id', $member_id);
        }

        $this->db->order_by('firstname', 'ASC');
        $this->db->order_by('middlename', 'ASC');
        $this->db->order_by('lastname', 'ASC');
        return $this->db->get('members');
    }

    function count_member($key = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT * FROM members WHERE PIN='$pin' ";

        if (!is_null($key)) {
            $sql.= " AND ( PID  LIKE '%$key%' OR member_id LIKE '%$key%' OR firstname LIKE '%$key%' OR
            middlename LIKE '%$key%' OR  lastname LIKE '%$key%')";
        }

        return count($this->db->query($sql)->result());
    }

    function search_member($key, $limit, $start) {

        $pin = current_user()->PIN;
        $sql = "SELECT * FROM members WHERE PIN='$pin' ";

        if (!is_null($key)) {
            $sql.= " AND ( PID  LIKE '%$key%' OR member_id LIKE '%$key%' OR firstname LIKE '%$key%' OR
            middlename LIKE '%$key%' OR  lastname LIKE '%$key%')";
        }
        $sql.= " LIMIT $start,$limit";

        return $this->db->query($sql)->result();
    }

    function member_group_cross($pid) {
        $this->db->where('PID', $pid);
        return $this->db->get('members_groups')->result();
    }

    function member_name($member_id = null, $PID = null) {
        $member = $this->member_basic_info(null, $PID, $member_id)->row();
        return $member->firstname . ' ' . $member->middlename . ' ' . $member->lastname;
    }

}

?>
