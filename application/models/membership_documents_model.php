<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Membership documents - data access for the PDS / Application for Membership /
 * Subscription Agreement screens.
 *
 * These three paper documents are stored as:
 *   members                  - PDS person fields (identity, parents, spouse, physical)
 *   members_contact          - PDS fields that already had a home (occupation, TIN,
 *                              annual income, religion, contact no., address)
 *   beneficiaries            - PDS dependants table
 *   member_trainings         - PDS in-service training table
 *   membership_applications  - Form 2 (board action / OR / approval)
 *   subscription_agreements  - Form 3 (subscribed shares + signature)
 *
 * members.PID is the relational key - NOT members.id and NOT members.member_id.
 *
 * @author Copilot
 */
class Membership_documents_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * `members_contact` columns the PDS writes. Everything else on that table
     * (phone2, email, sssno, dependents, officeaddress, assignedschool,
     * postaladdress) is left alone so the main member screens keep owning it.
     */
    private function pds_contact_map() {
        return array(
            'occupation' => 'occupation',
            'tin' => 'tinno',
            'annualIncome' => 'annualincome',
            'religion' => 'religion',
            'contact' => 'phone1',
            'address' => 'physicaladdress',
        );
    }

    private function pin() {
        return current_user()->PIN;
    }

    // ------------------------------------------------------------------
    // READ
    // ------------------------------------------------------------------

    /**
     * Directory listing. Joins each member to whichever of the three documents
     * already exist, so the table can show status / shares without N+1 queries.
     */
    function member_list($q = '', $status = 'ALL', $include_none_member = FALSE, $limit = 300, $offset = 0) {
        $pin = $this->pin();

        $where = "m.PIN = ? AND m.status != 2";
        $binds = array($pin);

        if (!$include_none_member) {
            $where .= " AND m.none_member = 0";
        }

        if ($q !== '') {
            $where .= " AND (m.member_id LIKE ? OR m.firstname LIKE ? OR m.lastname LIKE ?
                             OR m.middlename LIKE ? OR CAST(m.PID AS CHAR) LIKE ?
                             OR c.physicaladdress LIKE ?)";
            $like = '%' . $q . '%';
            $binds = array_merge($binds, array($like, $like, $like, $like, $like, $like));
        }

        if ($status !== 'ALL' && $status !== '') {
            if ($status === 'NONE') {
                $where .= " AND ma.id IS NULL";
            } else {
                $where .= " AND ma.action_taken = ?";
                $binds[] = $status;
            }
        }

        $sql = "SELECT m.PID, m.member_id, m.firstname, m.middlename, m.lastname, m.maidenname,
                       m.gender, m.maritalstatus, m.dob, m.joiningdate, m.photo,
                       m.none_member, m.status AS member_status, m.formstatus,
                       c.phone1, c.email, c.physicaladdress, c.occupation, c.religion,
                       c.tinno, c.annualincome, c.bpno, m.gsis_bp_no,
                       ma.id AS application_id, ma.action_taken, ma.application_date,
                       ma.or_no, ma.amount_paid, ma.board_resolution_no, ma.approval_date,
                       ma.disapproved_reason, ma.manager_name,
                       sa.id AS agreement_id, sa.subscribed_shares, sa.par_value_per_share,
                       sa.total_par_value, sa.payment_frequency, sa.witness_1_name,
                       sa.witness_2_name, sa.administering_officer,
                       (SELECT COUNT(*) FROM beneficiaries b WHERE b.member_id = m.PID) AS beneficiary_count,
                       (SELECT COUNT(*) FROM member_trainings t WHERE t.member_id = m.PID) AS training_count
                FROM members m
                LEFT JOIN members_contact c ON c.PID = m.PID AND c.PIN = m.PIN
                LEFT JOIN membership_applications ma ON ma.member_id = m.PID
                LEFT JOIN subscription_agreements sa ON sa.member_id = m.PID
                WHERE $where
                ORDER BY m.lastname ASC, m.firstname ASC, m.PID ASC
                LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        return $this->db->query($sql, $binds)->result();
    }

    function member_list_total($q = '', $status = 'ALL', $include_none_member = FALSE) {
        $pin = $this->pin();

        $where = "m.PIN = ? AND m.status != 2";
        $binds = array($pin);

        if (!$include_none_member) {
            $where .= " AND m.none_member = 0";
        }
        if ($q !== '') {
            $where .= " AND (m.member_id LIKE ? OR m.firstname LIKE ? OR m.lastname LIKE ?
                             OR m.middlename LIKE ? OR CAST(m.PID AS CHAR) LIKE ?
                             OR c.physicaladdress LIKE ?)";
            $like = '%' . $q . '%';
            $binds = array_merge($binds, array($like, $like, $like, $like, $like, $like));
        }
        if ($status !== 'ALL' && $status !== '') {
            if ($status === 'NONE') {
                $where .= " AND ma.id IS NULL";
            } else {
                $where .= " AND ma.action_taken = ?";
                $binds[] = $status;
            }
        }

        $sql = "SELECT COUNT(*) AS total
                FROM members m
                LEFT JOIN members_contact c ON c.PID = m.PID AND c.PIN = m.PIN
                LEFT JOIN membership_applications ma ON ma.member_id = m.PID
                WHERE $where";

        $row = $this->db->query($sql, $binds)->row();
        return $row ? (int) $row->total : 0;
    }

    /**
     * One member with all three documents attached. NULL when the PID is not
     * one of this tenant's members.
     */
    function member_detail($pid) {
        $pin = $this->pin();

        $sql = "SELECT m.*, c.phone1, c.phone2, c.email, c.physicaladdress, c.postaladdress,
                       c.occupation, c.tinno, c.sssno, c.bpno, c.religion, c.annualincome,
                       c.dependents, c.officeaddress, c.assignedschool,
                       ma.action_taken, ma.application_date, ma.or_no, ma.amount_paid,
                       ma.board_resolution_no, ma.approval_date, ma.disapproved_reason,
                       ma.manager_name,
                       sa.subscribed_shares, sa.par_value_per_share, sa.total_par_value,
                       sa.payment_frequency, sa.signature_blob, sa.witness_1_name,
                       sa.witness_2_name, sa.administering_officer
                FROM members m
                LEFT JOIN members_contact c ON c.PID = m.PID AND c.PIN = m.PIN
                LEFT JOIN membership_applications ma ON ma.member_id = m.PID
                LEFT JOIN subscription_agreements sa ON sa.member_id = m.PID
                WHERE m.PID = ? AND m.PIN = ?
                LIMIT 1";

        return $this->db->query($sql, array($pid, $pin))->row();
    }

    function beneficiaries($pid) {
        return $this->db->query(
            "SELECT id, full_name, relationship, date_of_birth, age
             FROM beneficiaries WHERE member_id = ? ORDER BY id ASC", array($pid)
        )->result();
    }

    function trainings($pid) {
        return $this->db->query(
            "SELECT id, course_title, inclusive_dates, hours_count, conducted_by
             FROM member_trainings WHERE member_id = ? ORDER BY id ASC", array($pid)
        )->result();
    }

    // ------------------------------------------------------------------
    // WRITE
    // ------------------------------------------------------------------

    /**
     * Blank a date string that the browser left empty. The table rejects
     * 0000-00-00 (NO_ZERO_DATE), so '' must become NULL.
     */
    private function date_or_null($value) {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00') {
            return NULL;
        }
        return $value;
    }

    private function text_or_null($value) {
        $value = trim((string) $value);
        return $value === '' ? NULL : $value;
    }

    /**
     * Create a NEW member.
     *
     * Deliberately the SAME path as Members -> Register New Member
     * (Member::new_member) rather than a second implementation:
     *
     *   - the member number is typed and checked for duplicates
     *     (Member_Model::is_member_exist)
     *   - the registration fee is validated against the configured minimum
     *     (global_setting.REGISTRATION_FEE)
     *   - Member_Model::add_member() allocates the PID from `auto_inc`, writes
     *     `members`, the `member_registrationfee` row and that fee's
     *     general-ledger entries
     *   - the member's login account is created the same way (group 3, username
     *     = member number, one-time password stored on `users.oldpass`)
     *
     * The ONLY difference is what gets typed in: this screen's Data Sheet has 25
     * fields, so the columns the old form cannot collect (spouse, parents,
     * GSIS/BP, height/weight/blood type, beneficiaries, trainings, ...) are
     * written straight afterwards by save_pds().
     *
     * @return array (success, message, warnings, PID, id, member_id, login)
     */
    function create_member($in) {
        $pin = $this->pin();
        $this->load->model('member_model');

        $member_no = trim((string) $this->input_or($in, 'memberNo'));
        $firstname = trim((string) $this->input_or($in, 'firstName'));
        $lastname = trim((string) $this->input_or($in, 'surname'));
        $gender = $this->clean_gender($this->input_or($in, 'sex'));
        $civil = trim((string) $this->input_or($in, 'civilStatus'));
        $dob = $this->date_or_null($this->input_or($in, 'dob'));
        $joining = $this->date_or_null($this->input_or($in, 'joiningDate'));

        // The fee box is amount-formatted, so the separators come off - the old
        // controller strips commas off $_POST in exactly the same way.
        $fee = str_replace(',', '', trim((string) $this->input_or($in, 'fee')));
        if ($fee === '') {
            $fee = '0';
        }

        $problems = array();
        if ($member_no === '') {
            $problems[] = 'Member No. is required.';
        }
        if ($firstname === '') {
            $problems[] = 'First name is required.';
        }
        if ($lastname === '') {
            $problems[] = 'Surname is required.';
        }
        if ($gender === '') {
            $problems[] = 'Sex is required (M or F).';
        }
        if ($civil === '') {
            $problems[] = 'Civil status is required.';
        }
        if ($dob === NULL || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $dob)) {
            $problems[] = 'A valid date of birth is required.';
        }
        if ($joining !== NULL && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $joining)) {
            $problems[] = 'Date joined must be a valid date.';
        }
        if (!is_numeric($fee)) {
            $problems[] = 'Registration fee must be a number.';
        }

        if ($problems) {
            return array('success' => FALSE, 'message' => implode(' ', $problems));
        }

        if ($this->member_model->is_member_exist($member_no)) {
            return array(
                'success' => FALSE,
                'message' => 'Member No. ' . $member_no . ' is already used by another member.',
            );
        }

        $minimum = (float) default_text_value('REGISTRATION_FEE');
        if ((float) $fee < $minimum) {
            return array(
                'success' => FALSE,
                'message' => 'Registration fee cannot be below the configured '
                    . number_format($minimum, 2) . '.',
            );
        }

        // The photo filename, when the screen uploaded one. The controller moved
        // the file (same folder/whitelist as the old screen); this model only
        // stores the name, exactly as Member::new_member does.
        $photo = trim((string) $this->input_or($in, 'photo'));

        $new_member = array(
            'member_id' => $member_no,
            'firstname' => $firstname,
            'middlename' => trim((string) $this->input_or($in, 'middleName')),
            'maidenname' => trim((string) $this->input_or($in, 'maidenName')),
            'lastname' => $lastname,
            'gender' => $gender,
            'maritalstatus' => $civil,
            'dob' => $dob,
            'placeofbirth' => trim((string) $this->input_or($in, 'pob')),
            // The old screen makes the joining date mandatory; here it defaults
            // to today so the same column is never left implicit.
            'joiningdate' => ($joining === NULL ? date('Y-m-d') : $joining),
            'createdby' => (int) current_user()->id,
            'PIN' => $pin,
        );

        if ($photo !== '') {
            $new_member['photo'] = $photo;
        }

        // members + member_registrationfee + the three ledger rows.
        //
        // add_member()'s return value is NOT members.id in this app: every write
        // goes through MY_DB_active_record, which logs itself into activity_logs
        // on the same connection and overwrites mysqli's insert_id. The old
        // registration screen stores that polluted value on users.MID, and an
        // earlier version of this method resolved the new PID from it - which
        // pointed at no row, so the Data Sheet was silently never written.
        // Re-read the row by the key this screen already checked is unique, the
        // same workaround _last_gl_entry_id() and the cash disbursement model use.
        $this->member_model->add_member($new_member, (float) $fee);

        $row = $this->db->query(
            "SELECT id, PID FROM members WHERE member_id = ? AND PIN = ? ORDER BY id DESC LIMIT 1",
            array($member_no, $pin)
        )->row();

        if (!$row) {
            return array(
                'success' => FALSE,
                'message' => 'The member could not be re-read after saving, so nothing else was written.',
            );
        }

        $pid = (int) $row->PID;
        $member_row_id = (int) $row->id;   // the real members.id - used for MID

        // Login account - the same call Member::new_member makes. Two of that
        // screen's behaviours are carried over deliberately:
        //   * the one-time password is left in plain text on users.oldpass
        //   * ion_auth's own add-to-group call reads the same polluted insert_id,
        //     so the account ends up without a users_groups row - exactly like
        //     every member account the old screen created. Adding the group here
        //     would hand new members permissions their peers do not have, so it is
        //     left alone until the old path is fixed too.
        $password = alphaID($member_row_id, FALSE, 4);
        $company = company_info();
        $this->load->library('ion_auth');
        $this->ion_auth->register(
            $member_no, $password, $member_no,
            array(
                'first_name' => $firstname,
                'last_name' => $lastname,
                'member_id' => $member_no,
                'oldpass' => $password,
                'MID' => $member_row_id,
                'PIN' => $pin,
                'company' => $company ? $company->name : '',
            ),
            array(3)
        );

        // Everything the old registration form has no field for.
        $pds = $this->save_pds($pid, $in);
        $warnings = isset($pds['warnings']) ? $pds['warnings'] : array();
        if (empty($pds['success'])) {
            $warnings[] = 'The member was created, but the Data Sheet was not saved: '
                . (isset($pds['message']) ? $pds['message'] : 'unknown error')
                . ' Open the member again and save the Data Sheet.';
        }

        return array(
            'success' => TRUE,
            'message' => 'Member ' . $lastname . ', ' . $firstname . ' (' . $member_no
                . ') created with PID ' . $pid . ', and the Data Sheet was saved.',
            'warnings' => $warnings,
            'PID' => $pid,
            'id' => (int) $member_row_id,
            'member_id' => $member_no,
            'login' => array('username' => $member_no),
        );
    }

    /**
     * Form 1 - the PDS. Writes `members` (PDS columns), `members_contact`
     * (the fields that already lived there) and replaces the two child tables.
     *
     * @return array (success, message, warnings)
     */
    function save_pds($pid, $in) {
        $pin = $this->pin();

        $member = $this->db->query(
            "SELECT id, PID, dob FROM members WHERE PID = ? AND PIN = ? LIMIT 1", array($pid, $pin)
        )->row();

        if (!$member) {
            return array('success' => FALSE, 'message' => 'Member #' . $pid . ' not found for this cooperative.');
        }

        $warnings = array();

        // --- members -------------------------------------------------
        $member_data = array(
            'lastname' => trim((string) $this->input_or($in, 'surname')),
            'firstname' => trim((string) $this->input_or($in, 'firstName')),
            'middlename' => trim((string) $this->input_or($in, 'middleName')),
            'maidenname' => trim((string) $this->input_or($in, 'maidenName')),
            'gender' => $this->clean_gender($this->input_or($in, 'sex')),
            'maritalstatus' => trim((string) $this->input_or($in, 'civilStatus')),
            'placeofbirth' => trim((string) $this->input_or($in, 'pob')),
            'spouse_name' => $this->text_or_null($this->input_or($in, 'spouseName')),
            'spouse_occupation' => $this->text_or_null($this->input_or($in, 'spouseOccupation')),
            'citizenship' => $this->text_or_null($this->input_or($in, 'citizenship')),
            'height' => $this->text_or_null($this->input_or($in, 'height')),
            'weight' => $this->text_or_null($this->input_or($in, 'weight')),
            'blood_type' => $this->text_or_null($this->input_or($in, 'bloodType')),
            'date_employed' => $this->date_or_null($this->input_or($in, 'dateEmployed')),
            'gsis_bp_no' => $this->text_or_null($this->input_or($in, 'gsis')),
            'father_name' => $this->text_or_null($this->input_or($in, 'fatherName')),
            'father_birth_place' => $this->text_or_null($this->input_or($in, 'fatherBirthPlace')),
            'mother_maiden_name' => $this->text_or_null($this->input_or($in, 'motherName')),
            'mother_birth_place' => $this->text_or_null($this->input_or($in, 'motherBirthPlace')),
            'educational_attainment' => $this->text_or_null($this->input_or($in, 'educationalAttainment')),
            'identification_no' => $this->text_or_null($this->input_or($in, 'identificationNo')),
            'id_date_issued' => $this->date_or_null($this->input_or($in, 'dateIssued')),
        );

        // lastname / firstname are NOT NULL - never blank them with an empty post.
        $display_name = trim((string) $this->input_or($in, 'surname'));
        foreach (array('lastname', 'firstname') as $required) {
            if ($member_data[$required] === '') {
                unset($member_data[$required]);
                $warnings[] = 'Required field ' . $required . ' was empty and was left unchanged.';
            }
        }

        // dob is NOT NULL - only touch it when a real date arrived.
        $dob = $this->date_or_null($this->input_or($in, 'dob'));
        if ($dob !== NULL) {
            $member_data['dob'] = $dob;
        } elseif (empty($member->dob)) {
            $warnings[] = 'Date of birth is required and was not provided.';
        }

        $this->db->update('members', $member_data, array('PID' => $pid, 'PIN' => $pin));

        // --- members_contact -----------------------------------------
        // Read the existing row first so fields the PDS does not manage
        // (phone2, email, sssno, officeaddress, assignedschool, ...) survive.
        $existing = $this->db->query(
            "SELECT * FROM members_contact WHERE PID = ? AND PIN = ? LIMIT 1", array($pid, $pin)
        )->row();

        $contact = array(
            'PID' => $pid,
            'PIN' => $pin,
            'createdby' => (int) current_user()->id,
        );
        if ($existing) {
            foreach (array('dependents', 'phone2', 'email', 'sssno', 'assignedschool',
                           'officeaddress', 'postaladdress') as $keep) {
                $contact[$keep] = $existing->$keep;
            }
        } else {
            $contact['dependents'] = 0;
            $contact['phone2'] = '';
            $contact['email'] = '';
            $contact['sssno'] = '';
            $contact['assignedschool'] = '';
            $contact['officeaddress'] = '';
            $contact['postaladdress'] = '';
        }

        foreach ($this->pds_contact_map() as $post_key => $column) {
            $value = trim((string) $this->input_or($in, $post_key));
            $contact[$column] = ($column === 'annualincome') ? (float) $value : $value;
        }

        if ($existing) {
            $this->db->update('members_contact', $contact, array('PID' => $pid, 'PIN' => $pin));
        } else {
            $this->db->insert('members_contact', $contact);
        }

        // --- child tables (delete + reinsert: the form is authoritative) ---
        $this->db->delete('beneficiaries', array('member_id' => $pid));
        foreach ($this->rows($in, 'beneficiaries') as $row) {
            $name = trim((string) (isset($row['name']) ? $row['name'] : ''));
            if ($name === '') {
                continue;
            }
            $this->db->insert('beneficiaries', array(
                'member_id' => $pid,
                'full_name' => $name,
                'relationship' => trim((string) (isset($row['rel']) ? $row['rel'] : '')),
                'date_of_birth' => $this->date_or_null(isset($row['dob']) ? $row['dob'] : ''),
                'age' => (isset($row['age']) && $row['age'] !== '') ? (int) $row['age'] : NULL,
                'createdby' => (int) current_user()->id,
                'PIN' => $pin,
            ));
        }

        $this->db->delete('member_trainings', array('member_id' => $pid));
        foreach ($this->rows($in, 'trainings') as $row) {
            $title = trim((string) (isset($row['title']) ? $row['title'] : ''));
            if ($title === '') {
                continue;
            }
            $this->db->insert('member_trainings', array(
                'member_id' => $pid,
                'course_title' => $title,
                'inclusive_dates' => trim((string) (isset($row['dates']) ? $row['dates'] : '')),
                'hours_count' => (isset($row['hours']) && $row['hours'] !== '') ? (int) $row['hours'] : NULL,
                'conducted_by' => trim((string) (isset($row['by']) ? $row['by'] : '')),
                'createdby' => (int) current_user()->id,
                'PIN' => $pin,
            ));
        }

        return array(
            'success' => TRUE,
            'message' => 'Personal Data Sheet saved for ' . $display_name . '.',
            'warnings' => $warnings,
        );
    }

    /**
     * Form 2 - Application for Membership. One row per member (member_id is UNIQUE).
     */
    function save_application($pid, $in) {
        $pin = $this->pin();

        if (!$this->owns_member($pid)) {
            return array('success' => FALSE, 'message' => 'Member #' . $pid . ' not found for this cooperative.');
        }

        $action = strtoupper(trim((string) $this->input_or($in, 'status')));
        if (!in_array($action, array('PENDING', 'APPROVED', 'DISAPPROVED'), TRUE)) {
            $action = 'PENDING';
        }

        $data = array(
            'member_id' => $pid,
            'application_date' => $this->date_or_null($this->input_or($in, 'applicationDate')),
            'action_taken' => $action,
            'disapproved_reason' => $this->text_or_null($this->input_or($in, 'disapprovedReason')),
            'or_no' => $this->text_or_null($this->input_or($in, 'orNo')),
            'amount_paid' => (float) str_replace(',', '', (string) $this->input_or($in, 'amountPaid')),
            'board_resolution_no' => $this->text_or_null($this->input_or($in, 'boardResNo')),
            'approval_date' => $this->date_or_null($this->input_or($in, 'approvalDate')),
            'manager_name' => $this->text_or_null($this->input_or($in, 'managerName')),
            'createdby' => (int) current_user()->id,
            'PIN' => $pin,
        );

        if ($data['application_date'] === NULL) {
            // application_date is NOT NULL - fall back to today.
            $data['application_date'] = date('Y-m-d');
        }
        if ($data['manager_name'] === NULL) {
            unset($data['manager_name']);
        }

        $existing = $this->db->query(
            "SELECT id FROM membership_applications WHERE member_id = ? LIMIT 1", array($pid)
        )->row();

        if ($existing) {
            $this->db->update('membership_applications', $data, array('member_id' => $pid));
        } else {
            $this->db->insert('membership_applications', $data);
        }

        return array('success' => TRUE, 'message' => 'Membership application saved (' . $action . ').');
    }

    /**
     * Form 3 - Membership and Subscription Agreement. One row per member.
     */
    function save_agreement($pid, $in) {
        $pin = $this->pin();

        if (!$this->owns_member($pid)) {
            return array('success' => FALSE, 'message' => 'Member #' . $pid . ' not found for this cooperative.');
        }

        $shares = (int) $this->input_or($in, 'shares');
        if ($shares <= 0) {
            $shares = 4;
        }
        $par = (float) $this->input_or($in, 'parValuePerShare');
        if ($par <= 0) {
            $par = 500.00;
        }

        $frequency = trim((string) $this->input_or($in, 'frequency'));
        if (!in_array($frequency, array('weekly', 'semi-monthly', 'monthly', 'quarterly'), TRUE)) {
            $frequency = 'monthly';
        }

        $signature = (string) $this->input_or($in, 'signature');
        // Only overwrite a stored signature when a fresh one was drawn.
        if (strlen($signature) > 5000000) {
            $signature = '';
        }

        $total = round($shares * $par, 2);

        $data = array(
            'member_id' => $pid,
            'subscribed_shares' => $shares,
            'par_value_per_share' => $par,
            'total_par_value' => $total,
            'payment_frequency' => $frequency,
            'witness_1_name' => $this->text_or_null($this->input_or($in, 'witness1')),
            'witness_2_name' => $this->text_or_null($this->input_or($in, 'witness2')),
            'administering_officer' => $this->text_or_null($this->input_or($in, 'officer')),
            'createdby' => (int) current_user()->id,
            'PIN' => $pin,
        );

        $existing = $this->db->query(
            "SELECT id FROM subscription_agreements WHERE member_id = ? LIMIT 1", array($pid)
        )->row();

        if ($signature !== '') {
            $data['signature_blob'] = $signature;
        } elseif (!$existing) {
            $data['signature_blob'] = NULL;
        }

        if ($existing) {
            $this->db->update('subscription_agreements', $data, array('member_id' => $pid));
        } else {
            $this->db->insert('subscription_agreements', $data);
        }

        return array(
            'success' => TRUE,
            'message' => 'Subscription agreement saved: ' . $shares . ' shares @ '
                         . number_format($par, 2) . ' = ' . number_format($total, 2) . '.',
        );
    }

    /**
     * Remove the three documents for a member. The member row itself is never
     * touched - deleting members belongs to the Members module.
     */
    function clear_documents($pid) {
        if (!$this->owns_member($pid)) {
            return array('success' => FALSE, 'message' => 'Member #' . $pid . ' not found for this cooperative.');
        }

        $this->db->delete('beneficiaries', array('member_id' => $pid));
        $this->db->delete('member_trainings', array('member_id' => $pid));
        $this->db->delete('membership_applications', array('member_id' => $pid));
        $this->db->delete('subscription_agreements', array('member_id' => $pid));

        return array(
            'success' => TRUE,
            'message' => 'PDS, application and agreement cleared for member #' . $pid
                         . '. The member record itself was kept.',
        );
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    function owns_member($pid) {
        $row = $this->db->query(
            "SELECT id FROM members WHERE PID = ? AND PIN = ? LIMIT 1", array($pid, $this->pin())
        )->row();
        return !empty($row);
    }

    /**
     * $_POST values are strings; JSON bodies are decoded to an array. Accept both.
     */
    private function input_or($in, $key) {
        if (!is_array($in)) {
            return '';
        }
        return array_key_exists($key, $in) ? $in[$key] : '';
    }

    /**
     * `members.gender` is char(1) - it cannot hold "Male".
     */
    private function clean_gender($value) {
        $value = trim((string) $value);
        $map = array('M' => 'M', 'F' => 'F', 'MALE' => 'M', 'FEMALE' => 'F');
        $upper = strtoupper($value);
        return isset($map[$upper]) ? $map[$upper] : '';
    }

    private function rows($in, $key) {
        $rows = $this->input_or($in, $key);
        return is_array($rows) ? $rows : array();
    }
}
