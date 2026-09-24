<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Membership documents - JSON API + auth-gated page wrapper for the
 * TAPSTEMCO membership system screens (PDS / Application for Membership /
 * Subscription Agreement).
 *
 * The screens themselves are the hand-built HTML documents under
 * /membership_system/. They were originally localStorage-only; this controller
 * is the persistence layer they now talk to with fetch().
 *
 * Every endpoint requires a signed-in session and is scoped to the signed-in
 * user's PIN (cooperative).
 *
 *   GET  /membership_documents/page          - serve the screen (login required)
 *   GET  /membership_documents/session       - who am I / is the session live
 *   GET  /membership_documents/members       - directory listing
 *   GET  /membership_documents/member/<PID>  - one member with all three documents
 *   POST /membership_documents/create_member - create a member, using the same
 *                                              logic as Members -> Register New Member
 *                                              (multipart when a photo is attached)
 *   POST /membership_documents/save_pds      - the PDS (multipart when the
 *                                              member's photo is replaced)
 *   POST /membership_documents/save_application
 *   POST /membership_documents/save_agreement
 *   POST /membership_documents/clear_documents
 *
 * @author Copilot
 */
class Membership_documents extends CI_Controller {

    /** The single-page screen kept under /membership_system/. */
    private $screen_file = 'membership_system/tapstemco_cooperative_management_system.html';

    /**
     * Module 1 (Member Registration) owns the member record, so the PDS and its
     * companion documents are permissioned there. Roles are created by
     * sql/add_membership_documents_roles.sql.
     */
    private $module_id = 1;

    function __construct() {
        parent::__construct();
        $this->load->model('membership_documents_model');
    }

    // ------------------------------------------------------------------
    // Session helpers
    // ------------------------------------------------------------------

    /**
     * Page gate: bounce to the dashboard with the app's usual flash message
     * (same pattern as saving.php / ar.php) when the group lacks the role.
     *
     * A session row pointing at a deleted user still counts as "logged in"
     * in this app, so that is handled as signed out - matching require_login_json().
     */
    private function require_role_or_redirect($role) {
        if (!$this->ion_auth->logged_in() || !current_user()) {
            redirect('auth/login', 'refresh');
        }
        if (!has_role($this->module_id, $role)) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
        }
    }

    /**
     * JSON endpoints answer 401 instead of redirecting, so fetch() can tell the
     * difference between "not signed in" and a server error.
     */
    private function require_login_json() {
        if (!$this->ion_auth->logged_in() || !current_user()) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'message' => 'Not signed in. Please sign in to the cooperative system first.',
                    'login_url' => site_url('auth/login'),
                )));
            $this->output->_display();
            exit;
        }
    }

    /**
     * Signed in but not allowed -> 403, so the screen can explain itself rather
     * than looking broken.
     */
    private function require_role_json($role) {
        $this->require_login_json();
        if (!has_role($this->module_id, $role)) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => FALSE,
                    'message' => 'Access denied: your group does not have the "' . $role . '" permission.',
                    'permission' => $role,
                )));
            $this->output->_display();
            exit;
        }
    }

    private function json($payload, $status = 200) {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    /**
     * Accepts a JSON body or a normal form post.
     *
     * This app runs CodeIgniter 2.2.0, which has no $this->input->raw_input_stream,
     * so the request body is read directly. A JSON Content-Type leaves $_POST
     * empty, hence the fallback order.
     */
    private function payload() {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, TRUE);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $this->input->post();
    }

    // ------------------------------------------------------------------
    // Page
    // ------------------------------------------------------------------

    /**
     * Serve the screen behind the app's session. This is the URL to link from
     * the menu, so the page cannot be opened without signing in.
     */
    function page() {
        $this->require_role_or_redirect('View_membership_documents');

        $path = FCPATH . $this->screen_file;
        if (!is_file($path)) {
            show_404();
            return;
        }

        $html = file_get_contents($path);
        if ($html === FALSE) {
            show_error('Unable to read ' . $this->screen_file);
            return;
        }

        // Hand the screen its two base URLs. They differ deliberately:
        //   base_url()                    -> assets, which live at the web root
        //   site_url('membership_documents/') -> the JSON API, which MY_Config
        //                                      localizes to /<lang>/membership_documents/
        // Using the un-prefixed path here would be redirected and lose the
        // query string, silently breaking search, filters and paging.
        // The registration fee default is injected too, so the new-member panel
        // starts from Default Settings rather than a guess.
        // site_url('dashboard') is the Home tab's target: MY_Config localizes it
        // to /<lang>/dashboard, keeping the jump on the same host and language
        // segment as the signed-in session.
        $html = str_replace(
            array('__TAPSTEMCO_APP_BASE__', '__TAPSTEMCO_API_BASE__', '__TAPSTEMCO_REGISTRATION_FEE__', '__TAPSTEMCO_DASHBOARD_URL__'),
            array(base_url(), site_url('membership_documents/'), (string) default_text_value('REGISTRATION_FEE'), site_url('dashboard')),
            $html
        );

        $this->output
            ->set_content_type('text/html')
            ->set_output($html);
    }

    // ------------------------------------------------------------------
    // API
    // ------------------------------------------------------------------

    function session() {
        $this->require_role_json('View_membership_documents');
        $user = current_user();

        $this->json(array(
            'success' => TRUE,
            'user' => array(
                'id' => (int) $user->id,
                'username' => $user->username,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'PIN' => $user->PIN,
            ),
            // The screen hides/disables controls it cannot use, so the UI never
            // offers an action that would only come back 403.
            'can' => array(
                'view' => has_role($this->module_id, 'View_membership_documents'),
                'edit' => has_role($this->module_id, 'Edit_membership_documents'),
                // Creating a member belongs to Module 1's Register_new_member -
                // the same role the sidebar's Register New Member link uses.
                'create' => has_role($this->module_id, 'Register_new_member'),
                'delete' => has_role($this->module_id, 'Delete_membership_documents'),
            ),
        ));
    }

    function members() {
        $this->require_role_json('View_membership_documents');

        $q = trim((string) $this->input->get('q'));
        $status = strtoupper(trim((string) $this->input->get('status')));
        $include_none = (bool) $this->input->get('include_none_member');

        $limit = (int) $this->input->get('limit');
        if ($limit <= 0 || $limit > 500) {
            $limit = 300;
        }
        $offset = (int) $this->input->get('offset');
        if ($offset < 0) {
            $offset = 0;
        }

        $total = $this->membership_documents_model->member_list_total($q, $status, $include_none);
        $rows = $this->membership_documents_model->member_list($q, $status, $include_none, $limit, $offset);

        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->shape_member($row);
        }

        $this->json(array(
            'success' => TRUE,
            'options' => array(
                'gender' => lang('member_genderoption'),
                'civil_status' => lang('member_maritalstatus_option'),
                'payment_frequency' => array('weekly', 'semi-monthly', 'monthly', 'quarterly'),
            ),
            'total' => $total,
            'returned' => count($out),
            'limit' => $limit,
            'offset' => $offset,
            'members' => $out,
        ));
    }

    function member($pid = NULL) {
        $this->require_role_json('View_membership_documents');

        $pid = (int) $pid;
        $row = $this->membership_documents_model->member_detail($pid);

        if (!$row) {
            $this->json(array('success' => FALSE, 'message' => 'Member #' . $pid . ' not found for this cooperative.'), 404);
            return;
        }

        $record = $this->shape_member($row);

        $record['beneficiaries'] = array();
        foreach ($this->membership_documents_model->beneficiaries($pid) as $b) {
            $record['beneficiaries'][] = array(
                'name' => $b->full_name,
                'rel' => $b->relationship,
                'dob' => $b->date_of_birth,
                'age' => $b->age,
            );
        }

        $record['trainings'] = array();
        foreach ($this->membership_documents_model->trainings($pid) as $t) {
            $record['trainings'][] = array(
                'title' => $t->course_title,
                'dates' => $t->inclusive_dates,
                'hours' => $t->hours_count,
                'by' => $t->conducted_by,
            );
        }

        $record['has_signature'] = !empty($row->signature_blob);
        $record['signature_preview'] = !empty($row->signature_blob) ? $row->signature_blob : NULL;

        $this->json(array('success' => TRUE, 'member' => $record));
    }

    /**
     * Create a NEW member.
     *
     * Same logic as Members -> Register New Member (Member::new_member): typed
     * member number with a duplicate check, registration fee validated against
     * the configured minimum, Member_Model::add_member() for the PID, the
     * member_registrationfee row and its general-ledger entries, and the login
     * account. The extra PDS data this screen collects is saved right after.
     *
     * Permission is Module 1's Register_new_member - member creation stays owned
     * by the same role that owns the old registration screen.
     */
    function create_member() {
        $this->require_role_json('Register_new_member');

        $payload = $this->payload();
        $uploaded_photo = NULL;

        // The photo is the one part that cannot travel in a JSON body, so the
        // screen switches to multipart as soon as a file is attached. It stays
        // optional, exactly as on the old registration screen.
        if (!empty($_FILES['photo']['name'])) {
            if (!isset($_FILES['photo']['error']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $this->json(array(
                    'success' => FALSE,
                    'message' => 'The photo could not be uploaded (error '
                        . (isset($_FILES['photo']['error']) ? (int) $_FILES['photo']['error'] : 'unknown') . ').',
                ), 400);
                return;
            }

            $stored = $this->store_member_photo($_FILES['photo']);
            if ($stored['file'] === NULL) {
                $this->json(array('success' => FALSE, 'message' => $stored['error']), 400);
                return;
            }

            $uploaded_photo = $stored['file'];
            $payload['photo'] = $uploaded_photo;
        }

        $result = $this->membership_documents_model->create_member($payload);

        // A refused create (duplicate member number, fee below the minimum,
        // missing field) must not leave the file behind. The old screen leaves
        // the uploaded file orphaned in that case; this one cleans up.
        if (empty($result['success']) && $uploaded_photo !== NULL) {
            @unlink(FCPATH . 'uploads/memberphoto/' . $uploaded_photo);
        }

        $this->json(array(
            'success' => !empty($result['success']),
            'message' => isset($result['message']) ? $result['message'] : '',
            'warnings' => isset($result['warnings']) ? $result['warnings'] : array(),
            'PID' => isset($result['PID']) ? (int) $result['PID'] : NULL,
            'member_id' => isset($result['member_id']) ? $result['member_id'] : NULL,
            'login' => isset($result['login']) ? $result['login'] : NULL,
        ), empty($result['success']) ? 400 : 200);
    }

    /**
     * Store an uploaded member photo.
     *
     * Deliberately mirrors Member::getExtension() + Member::upload_file(): same
     * folder (uploads/memberphoto), same jpg/jpeg/png/gif whitelist, same
     * `<time><original name>` filename - so a member registered here and a member
     * registered through the old screen end up with the same kind of file. The
     * model is handed the finished filename; it never sees $_FILES.
     *
     * @return array ('file' => filename|NULL, 'error' => message|NULL)
     */
    private function store_member_photo($file) {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif'), TRUE)) {
            return array('file' => NULL, 'error' => lang('member_photo_error'));
        }

        $folder = FCPATH . 'uploads/memberphoto';
        if (!is_dir($folder) && !@mkdir($folder, 0777, TRUE)) {
            return array('file' => NULL, 'error' => 'The photo folder is not writable on the server.');
        }

        // basename() stops a crafted "../../x.jpg" from leaving the folder.
        $filename = time() . basename($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $folder . '/' . $filename)) {
            return array('file' => NULL, 'error' => 'The photo could not be stored on the server.');
        }

        return array('file' => $filename, 'error' => NULL);
    }

    /**
     * Save the Data Sheet of an EXISTING member.
     *
     * The member's picture travels with the Data Sheet in both directions:
     * create_member() sets it on a new record, and this endpoint replaces it
     * when the user picks a file while editing. It stays optional - an empty
     * file field means "keep the picture that is on file".
     *
     * The permission is Edit_membership_documents, the same right that owns
     * every other field on this form.
     */
    function save_pds() {
        $this->require_role_json('Edit_membership_documents');
        $payload = $this->payload();
        $pid = $this->pid_from($payload);
        if ($pid === NULL) {
            return;
        }

        // A file cannot ride in a JSON body, so the screen switches to
        // multipart only when a picture is attached or replaced.
        $uploaded_photo = NULL;
        if (!empty($_FILES['photo']['name'])) {
            if (!isset($_FILES['photo']['error']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $this->json(array(
                    'success' => FALSE,
                    'message' => 'The photo could not be uploaded (error '
                        . (isset($_FILES['photo']['error']) ? (int) $_FILES['photo']['error'] : 'unknown') . ').',
                ), 400);
                return;
            }

            $stored = $this->store_member_photo($_FILES['photo']);
            if ($stored['file'] === NULL) {
                $this->json(array('success' => FALSE, 'message' => $stored['error']), 400);
                return;
            }

            $uploaded_photo = $stored['file'];
            $payload['photo'] = $uploaded_photo;
        }

        $result = $this->membership_documents_model->save_pds($pid, $payload);

        if ($uploaded_photo !== NULL) {
            if (empty($result['success'])) {
                // Nothing was written, so the new file must not stay behind.
                @unlink(FCPATH . 'uploads/memberphoto/' . basename($uploaded_photo));
            } else {
                // The replaced picture is nobody's any more, so it goes - unless
                // another member row still points at the same filename.
                $replaced = isset($result['replaced_photo']) ? $result['replaced_photo'] : '';
                if ($replaced !== ''
                        && !$this->membership_documents_model->other_member_uses_photo($replaced, $pid)) {
                    @unlink(FCPATH . 'uploads/memberphoto/' . basename($replaced));
                }
            }
        }

        // Hand the screen the filename that is stored now, so its preview goes
        // back to the saved picture - the old one when no file travelled.
        $this->save($result, array(
            'photo' => isset($result['photo']) ? $result['photo'] : NULL,
        ));
    }

    function save_application() {
        $this->require_role_json('Edit_membership_documents');
        $payload = $this->payload();
        $pid = $this->pid_from($payload);
        if ($pid === NULL) {
            return;
        }
        $this->save($this->membership_documents_model->save_application($pid, $payload));
    }

    function save_agreement() {
        $this->require_role_json('Edit_membership_documents');
        $payload = $this->payload();
        $pid = $this->pid_from($payload);
        if ($pid === NULL) {
            return;
        }
        $this->save($this->membership_documents_model->save_agreement($pid, $payload));
    }

    function clear_documents() {
        $this->require_role_json('Delete_membership_documents');
        $payload = $this->payload();
        $pid = $this->pid_from($payload);
        if ($pid === NULL) {
            return;
        }
        $this->save($this->membership_documents_model->clear_documents($pid));
    }

    /**
     * `member_id` must be present and numeric.
     *
     * This matters: members.PID 0 is a real row, so a missing key must not be
     * allowed to silently resolve to 0.
     *
     * @return int|NULL NULL when the request is invalid (already answered).
     */
    private function pid_from($payload) {
        if (!is_array($payload) || !array_key_exists('member_id', $payload) || !is_numeric($payload['member_id'])) {
            $this->json(array(
                'success' => FALSE,
                'message' => 'member_id is required and must be the member\'s PID.',
            ), 400);
            return NULL;
        }
        return (int) $payload['member_id'];
    }

    /**
     * Answer a model result. `$extra` carries endpoint-specific fields (the PDS
     * save reports the member photo it stored).
     */
    private function save($result, $extra = array()) {
        $this->json(array_merge(array(
            'success' => !empty($result['success']),
            'message' => isset($result['message']) ? $result['message'] : '',
            'warnings' => isset($result['warnings']) ? $result['warnings'] : array(),
        ), $extra), empty($result['success']) ? 400 : 200);
    }

    /**
     * DB row -> the shape the screens already use, so the markup/print templates
     * hardly change. Display labels are included alongside the stored codes
     * because `members.gender` is a char(1) and `members.maritalstatus` holds
     * the app's option keys.
     */
    private function shape_member($row) {
        $gender_labels = lang('member_genderoption');
        $civil_labels = lang('member_maritalstatus_option');

        $gender = isset($row->gender) ? trim((string) $row->gender) : '';
        // Legacy rows store the label rather than the key in some places.
        if ($gender !== '' && !isset($gender_labels[$gender])) {
            foreach ($gender_labels as $code => $label) {
                if (strcasecmp($label, $gender) === 0) {
                    $gender = $code;
                    break;
                }
            }
        }
        $gender = strtoupper(substr($gender, 0, 1)) === 'M' ? 'M' : (strtoupper(substr($gender, 0, 1)) === 'F' ? 'F' : '');

        $civil = isset($row->maritalstatus) ? trim((string) $row->maritalstatus) : '';

        $dob = isset($row->dob) ? $row->dob : '';
        $age = '';
        if ($dob && $dob !== '0000-00-00') {
            $birth = date_create($dob);
            if ($birth) {
                $age = (int) date_diff($birth, date_create('today'))->y;
            }
        }

        $shares = isset($row->subscribed_shares) && $row->subscribed_shares !== NULL
            ? (int) $row->subscribed_shares : 4;
        $par = isset($row->par_value_per_share) && $row->par_value_per_share !== NULL
            ? (float) $row->par_value_per_share : 500.00;
        $total = isset($row->total_par_value) && $row->total_par_value !== NULL
            ? (float) $row->total_par_value : round($shares * $par, 2);

        $action = isset($row->action_taken) && $row->action_taken ? $row->action_taken : NULL;

        return array(
            'id' => (int) $row->PID,
            'member_id' => $row->member_id,
            // Stored filename for the 1x1 picture on the printed Data Sheet.
            // '' means "no picture of their own": blank, '0' and the avatar.gif
            // column default all count as none, the same rule member_avatar_url()
            // uses elsewhere in the app.
            'photo' => $this->own_photo(isset($row->photo) ? $row->photo : ''),
            'surname' => $row->lastname,
            'firstName' => $row->firstname,
            'middleName' => $row->middlename,
            'maidenName' => isset($row->maidenname) ? $row->maidenname : '',
            'sex' => $gender,
            'sexLabel' => isset($gender_labels[$gender]) ? $gender_labels[$gender] : '',
            'civilStatus' => $civil,
            'civilStatusLabel' => isset($civil_labels[$civil]) ? $civil_labels[$civil] : $civil,
            'spouseName' => isset($row->spouse_name) ? $row->spouse_name : '',
            'spouseOccupation' => isset($row->spouse_occupation) ? $row->spouse_occupation : '',
            'dob' => ($dob === '0000-00-00') ? '' : $dob,
            'pob' => isset($row->placeofbirth) ? $row->placeofbirth : '',
            'citizenship' => isset($row->citizenship) ? $row->citizenship : '',
            'age' => $age,
            'occupation' => isset($row->occupation) ? $row->occupation : '',
            'height' => isset($row->height) ? $row->height : '',
            'weight' => isset($row->weight) ? $row->weight : '',
            'bloodType' => isset($row->blood_type) ? $row->blood_type : '',
            'address' => isset($row->physicaladdress) ? $row->physicaladdress : '',
            'contact' => isset($row->phone1) ? $row->phone1 : '',
            'dateEmployed' => $this->date_out(isset($row->date_employed) ? $row->date_employed : ''),
            'tin' => isset($row->tinno) ? $row->tinno : '',
            'annualIncome' => isset($row->annualincome) ? $row->annualincome : '',
            'religion' => isset($row->religion) ? $row->religion : '',
            'gsis' => isset($row->gsis_bp_no) && $row->gsis_bp_no !== NULL
                ? $row->gsis_bp_no : (isset($row->bpno) ? $row->bpno : ''),
            'fatherName' => isset($row->father_name) ? $row->father_name : '',
            'fatherBirthPlace' => isset($row->father_birth_place) ? $row->father_birth_place : '',
            'motherName' => isset($row->mother_maiden_name) ? $row->mother_maiden_name : '',
            'motherBirthPlace' => isset($row->mother_birth_place) ? $row->mother_birth_place : '',
            'educationalAttainment' => isset($row->educational_attainment) ? $row->educational_attainment : '',
            'identificationNo' => isset($row->identification_no) ? $row->identification_no : '',
            'dateIssued' => $this->date_out(isset($row->id_date_issued) ? $row->id_date_issued : ''),
            'status' => $action ? $action : 'NONE',
            'applicationDate' => $this->date_out(isset($row->application_date) ? $row->application_date : ''),
            'orNo' => isset($row->or_no) ? $row->or_no : '',
            'amountPaid' => isset($row->amount_paid) && $row->amount_paid !== NULL ? number_format((float) $row->amount_paid, 2, '.', '') : '',
            'boardResNo' => isset($row->board_resolution_no) ? $row->board_resolution_no : '',
            'approvalDate' => $this->date_out(isset($row->approval_date) ? $row->approval_date : ''),
            'disapprovedReason' => isset($row->disapproved_reason) ? $row->disapproved_reason : '',
            'managerName' => isset($row->manager_name) ? $row->manager_name : '',
            'shares' => $shares,
            'parValuePerShare' => $par,
            'totalParValue' => $total,
            'totalParValueLabel' => 'PHP ' . number_format($total, 2),
            'frequency' => isset($row->payment_frequency) && $row->payment_frequency ? $row->payment_frequency : 'monthly',
            'witness1' => isset($row->witness_1_name) ? $row->witness_1_name : '',
            'witness2' => isset($row->witness_2_name) ? $row->witness_2_name : '',
            'officer' => isset($row->administering_officer) ? $row->administering_officer : '',
            'beneficiaryCount' => isset($row->beneficiary_count) ? (int) $row->beneficiary_count : NULL,
            'trainingCount' => isset($row->training_count) ? (int) $row->training_count : NULL,
            'hasApplication' => !empty($action),
            'hasAgreement' => isset($row->agreement_id) && $row->agreement_id !== NULL ? TRUE : (isset($row->subscribed_shares) && $row->subscribed_shares !== NULL),
        );
    }

    /**
     * The member's own photo file, or '' when they only carry the placeholder.
     * Mirrors member_avatar_url() so the screen and the printed form agree on
     * what counts as "has a picture".
     */
    private function own_photo($photo) {
        $photo = trim((string) $photo);
        if ($photo === '' || $photo === '0' || strtolower($photo) === 'avatar.gif') {
            return '';
        }
        return $photo;
    }

    private function date_out($value) {
        return ($value === NULL || $value === '' || $value === '0000-00-00') ? '' : $value;
    }
}
