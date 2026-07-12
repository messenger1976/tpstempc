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
        if (!empty($data)) {
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
        $debit_account = 1010003;

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
        $accountinfo = account_row_info($ledgerbook['account']);
        $ledgerbook['account_type'] = $accountinfo->account_type;
        $ledgerbook['sub_account_type'] = $accountinfo->sub_account_type;
        $this->db->insert('general_ledger', $ledgerbook);

        $ledgerbook['credit'] = 0;
        $ledgerbook['debit'] = 0;
        //retain earning
        $ledgerbook['account'] = 3000002;
        $ledgerbook['credit'] = $registrationfee;
        $accountinfo = account_row_info($ledgerbook['account']);
        $ledgerbook['account_type'] = $accountinfo->account_type;
        $ledgerbook['sub_account_type'] = $accountinfo->sub_account_type;
        $this->db->insert('general_ledger', $ledgerbook);

        $ledgerbook['credit'] = 0;
        $ledgerbook['debit'] = 0;
        $ledgerbook['account'] = $debit_account;
        $accountinfo = account_row_info($ledgerbook['account']);
        $ledgerbook['account_type'] = $accountinfo->account_type;
        $ledgerbook['sub_account_type'] = $accountinfo->sub_account_type;

        $ledgerbook['debit'] = $registrationfee;
        $this->db->insert('general_ledger', $ledgerbook);

        return $return;
    }
    function add_none_member($data, $registrationfee = 0) {
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
        $debit_account = 1010003;

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
        $accountinfo = account_row_info($ledgerbook['account']);
        $ledgerbook['account_type'] = $accountinfo->account_type;
        $ledgerbook['sub_account_type'] = $accountinfo->sub_account_type;
        $this->db->insert('general_ledger', $ledgerbook);

        $ledgerbook['credit'] = 0;
        $ledgerbook['debit'] = 0;
        //retain earning
        $ledgerbook['account'] = 3000002;
        $ledgerbook['credit'] = $registrationfee;
        $accountinfo = account_row_info($ledgerbook['account']);
        $ledgerbook['account_type'] = $accountinfo->account_type;
        $ledgerbook['sub_account_type'] = $accountinfo->sub_account_type;
        $this->db->insert('general_ledger', $ledgerbook);

        $ledgerbook['credit'] = 0;
        $ledgerbook['debit'] = 0;
        $ledgerbook['account'] = $debit_account;
        $accountinfo = account_row_info($ledgerbook['account']);
        $ledgerbook['account_type'] = $accountinfo->account_type;
        $ledgerbook['sub_account_type'] = $accountinfo->sub_account_type;

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

        if (empty($return) || $return === NULL) {
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
        if (empty($return) || $return === NULL) {
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
        if (empty($contact) || $contact === NULL) {
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
        if (empty($contact) || $contact === NULL) {
            $return = $this->db->insert('members_nextkin', $data);
        } else {
            $return = $this->db->update('members_nextkin', $data, array('PID' => $data['PID']));
        }

        if ($formstatus < 3) {
            $this->db->update('members', array('formstatus' => 3), array('id' => $id));
        }

        return $return;
    }

    function member_basic_info($id = null, $PID = null, $member_id = null, $inactive=null) {
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
        if (!is_null($inactive)) {
            $this->db->where('status', $inactive);
        }
        
        $this->db->order_by('firstname', 'ASC');
        $this->db->order_by('middlename', 'ASC');
        $this->db->order_by('lastname', 'ASC');
        return $this->db->get('members');
    }

    function count_member($key = null, $searchstatus = null, $searchmember = null) {
        $pin = current_user()->PIN;
        $sql = "SELECT * FROM members WHERE PIN='$pin' AND status != 2 ";

        if (!is_null($key)) {
            $sql.= " AND ( PID  LIKE '%$key%' OR member_id LIKE '%$key%' OR firstname LIKE '%$key%' OR
            middlename LIKE '%$key%' OR  lastname LIKE '%$key%')";
        }
        if (!is_null($searchstatus) && $searchstatus!='') {
            $sql.= " AND ( status = $searchstatus)";
        }
        if (!is_null($searchmember) && $searchmember!='') {
            $searchmember = $searchmember==1?0:1;
            $sql.= " AND ( none_member = $searchmember)";
        }
        return count($this->db->query($sql)->result());
    }

    function search_member($key, $searchstatus, $searchmember,$limit, $start) {

        $pin = current_user()->PIN;
        $sql = "SELECT * FROM members WHERE PIN='$pin' AND status != 2 ";

        if (!is_null($key)) {
            $sql.= " AND ( PID  LIKE '%$key%' OR member_id LIKE '%$key%' OR firstname LIKE '%$key%' OR
            middlename LIKE '%$key%' OR  lastname LIKE '%$key%')";
        }
        if (!is_null($searchstatus) && $searchstatus!='') {
            $sql.= " AND (status = $searchstatus)";
        }
        if (!is_null($searchmember) && $searchmember!='') {
            $searchmember = $searchmember==1?0:1;
            $sql.= " AND ( none_member = $searchmember)";
        }
        $sql.= " ORDER BY ABS(member_id) asc LIMIT $start,$limit";

        return $this->db->query($sql)->result();
    }

    function soft_delete_member($id) {
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $this->db->update('members', array('status' => 2));
        return $this->db->affected_rows() > 0;
    }

    function member_group_cross($pid) {
        $this->db->where('PID', $pid);
        return $this->db->get('members_groups')->result();
    }

    function member_name($member_id = null, $PID = null) {
        $member = $this->member_basic_info(null, $PID, $member_id)->row();
        return $member->firstname . ' ' . $member->middlename . ' ' . $member->lastname;
    }
    
    function member_contribution_balance($pid) {
        $this->db->where('PID', $pid);
        $return = $this->db->get('members_contribution')->row();
        if (empty($return) || $return === NULL) {
            $fields = $this->db->list_fields('members_contribution');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }

        return $return;
    }

    function member_mortuary_balance($pid) {
        $this->db->where('PID', $pid);
        $return = $this->db->get('members_mortuary')->row();
        if (empty($return) || $return === NULL) {
            $fields = $this->db->list_fields('members_mortuary');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }
        
        return $return;
    }
    
    function member_share_balance($pid) {
         $this->db->where('PID', $pid);
         $return = $this->db->get('members_share')->row();         
         
        if (empty($return) || $return === NULL) {
            $fields = $this->db->list_fields('members_share');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }

        return $return;
    }
    
    /**
     * Get share balance row by PID and member_id (amount + remainbalance = total share balance).
     */
    function member_share_balance_by_member($pid, $member_id) {
        $this->db->where('PIN', current_user()->PIN);
        $this->db->where('PID', $pid);
        $this->db->where('member_id', $member_id);
        $return = $this->db->get('members_share')->row();
        if (empty($return) || $return === NULL) {
            $fields = $this->db->list_fields('members_share');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }
        return $return;
    }
    
    /**
     * Sum of total_balance from loan_beginning_balances for a member (excluding those that have loan_contract).
     */
    function member_loan_beginning_balances_sum($member_id) {
        $pin = current_user()->PIN;
        $this->db->select('COALESCE(SUM(total_balance), 0) as total', FALSE);
        $this->db->where('PIN', $pin);
        $this->db->where('member_id', $member_id);
        $this->db->where('(loan_id IS NULL OR loan_id NOT IN (SELECT LID FROM loan_contract WHERE PIN = ' . $this->db->escape($pin) . '))', NULL, FALSE);
        $row = $this->db->get('loan_beginning_balances')->row();
        return ($row && is_numeric($row->total)) ? floatval($row->total) : 0;
    }
    
    function member_current_total_loan($pid) {
         $this->db->where('PID', $pid);
         $this->db->where('status', '4');
         $this->db->order_by('id', 'DESC');
         $this->db->limit(1);
         $return = $this->db->get('loan_contract')->row();         
         
        if (empty($return) || $return === NULL) {
            $fields = $this->db->list_fields('loan_contract');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }

        return $return;
    }
    
    
     function member_current_loan_payment($LID) {
         $this->db->select('SUM(amount) as total_paid_amount');
         $this->db->where('LID', $LID);
         $return = $this->db->get('loan_contract_repayment')->row();         
         
        if (empty($return) || $return === NULL) {
            $fields = $this->db->list_fields('loan_contract_repayment');
            $fieldschange = array_flip($fields);
            foreach ($fieldschange as $key => $value) {
                $fieldschange[$key] = '';
            }
            $return = (Object) $fieldschange;
        }

        return $return;
    }

    /**
     * Active members with physical addresses, grouped for the dashboard OSM map.
     * Requires member_address_geocode (see install_member_address_geocode.php).
     *
     * @return array List of location buckets with lat/lng, count, and sample members
     */
    function get_member_map_locations() {
        $pin = current_user()->PIN;
        $locations = array();

        if (!$this->db->table_exists('member_address_geocode')) {
            return $locations;
        }

        $sql = "SELECT
                    UPPER(TRIM(c.physicaladdress)) AS address_key,
                    TRIM(c.physicaladdress) AS address,
                    g.lat,
                    g.lng,
                    g.geocode_status,
                    COUNT(*) AS member_count
                FROM members m
                INNER JOIN members_contact c ON c.PID = m.PID
                LEFT JOIN member_address_geocode g
                    ON g.address_key = UPPER(TRIM(c.physicaladdress))
                WHERE m.PIN = ?
                  AND m.status = 1
                  AND c.physicaladdress IS NOT NULL
                  AND TRIM(c.physicaladdress) != ''
                GROUP BY UPPER(TRIM(c.physicaladdress)), g.lat, g.lng, g.geocode_status
                ORDER BY member_count DESC";

        $rows = $this->db->query($sql, array($pin))->result();

        foreach ($rows as $row) {
            if ($row->geocode_status !== 'ok' || $row->lat === null || $row->lng === null) {
                continue;
            }

            $sample = $this->db->query(
                "SELECT m.member_id,
                        TRIM(CONCAT(IFNULL(m.firstname,''), ' ', IFNULL(m.lastname,''))) AS name
                 FROM members m
                 INNER JOIN members_contact c ON c.PID = m.PID
                 WHERE m.PIN = ?
                   AND m.status = 1
                   AND UPPER(TRIM(c.physicaladdress)) = ?
                 ORDER BY m.lastname ASC, m.firstname ASC
                 LIMIT 8",
                array($pin, $row->address_key)
            )->result_array();

            $locations[] = array(
                'address' => $row->address,
                'lat' => floatval($row->lat),
                'lng' => floatval($row->lng),
                'member_count' => intval($row->member_count),
                'members' => $sample,
            );
        }

        return $locations;
    }

    /**
     * Office / cooperative HQ location for dashboard map directions.
     * Uses companyinfo.address matched against the geocode cache when possible.
     */
    function get_office_map_location() {
        $company = company_info();
        $address = ($company && !empty($company->address)) ? trim($company->address) : '';
        $label = ($company && !empty($company->name)) ? $company->name : 'Office';

        $office = array(
            'label' => $label,
            'address' => $address !== '' ? $address : 'Purok 1, San Jose, Talibon, Bohol',
            'lat' => 10.1365976,
            'lng' => 124.3132049,
        );

        if (!$this->db->table_exists('member_address_geocode') || $address === '') {
            return $office;
        }

        $key = strtoupper(trim(preg_replace('/\s+/', ' ', $address)));
        $row = $this->db->query(
            "SELECT lat, lng, address_raw FROM member_address_geocode
             WHERE geocode_status = 'ok' AND lat IS NOT NULL AND lng IS NOT NULL
               AND (address_key = ? OR address_raw = ?)
             LIMIT 1",
            array($key, $address)
        )->row();

        if (!$row) {
            // Prefer San Jose (office barangay) over town-center fallbacks
            $row = $this->db->query(
                "SELECT lat, lng, address_raw FROM member_address_geocode
                 WHERE geocode_status = 'ok' AND lat IS NOT NULL AND lng IS NOT NULL
                   AND address_key LIKE '%SAN JOSE%TALIBON%'
                 ORDER BY CASE WHEN address_key = 'SAN JOSE, TALIBON, BOHOL' THEN 0 ELSE 1 END
                 LIMIT 1"
            )->row();
        }

        if ($row) {
            $office['lat'] = floatval($row->lat);
            $office['lng'] = floatval($row->lng);
        }

        return $office;
    }

    /**
     * Counts for map footer: total addressed members vs plotted locations.
     */
    function get_member_map_stats() {
        $pin = current_user()->PIN;
        $stats = array(
            'with_address' => 0,
            'plotted' => 0,
            'locations' => 0,
            'table_ready' => $this->db->table_exists('member_address_geocode'),
        );

        $row = $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM members m
             INNER JOIN members_contact c ON c.PID = m.PID
             WHERE m.PIN = ?
               AND m.status = 1
               AND c.physicaladdress IS NOT NULL
               AND TRIM(c.physicaladdress) != ''",
            array($pin)
        )->row();
        $stats['with_address'] = $row ? intval($row->cnt) : 0;

        if (!$stats['table_ready']) {
            return $stats;
        }

        $row = $this->db->query(
            "SELECT COUNT(*) AS plotted, COUNT(DISTINCT UPPER(TRIM(c.physicaladdress))) AS locations
             FROM members m
             INNER JOIN members_contact c ON c.PID = m.PID
             INNER JOIN member_address_geocode g
                ON g.address_key = UPPER(TRIM(c.physicaladdress))
               AND g.geocode_status = 'ok'
               AND g.lat IS NOT NULL
               AND g.lng IS NOT NULL
             WHERE m.PIN = ?
               AND m.status = 1
               AND c.physicaladdress IS NOT NULL
               AND TRIM(c.physicaladdress) != ''",
            array($pin)
        )->row();

        if ($row) {
            $stats['plotted'] = intval($row->plotted);
            $stats['locations'] = intval($row->locations);
        }

        return $stats;
    }
    
    
}

?>
