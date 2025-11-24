<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('cookie');
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->lang->load('auth');
        $this->data['current_title'] = lang('page_auth');
    }

    //redirect if needed, otherwise display the user list
    function index() {
        $this->load->library('pagination');
        $this->data['title'] = lang('user_manager_list');
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }


        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 10);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        // Get sort parameters
        $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_on';
        $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
        
        // Validate sort_by to prevent SQL injection
        $allowed_sort_fields = array('created_on', 'first_name', 'last_name', 'email');
        if (!in_array($sort_by, $allowed_sort_fields)) {
            $sort_by = 'created_on';
        }
        
        // Validate sort_order
        $sort_order = strtoupper($sort_order);
        if ($sort_order != 'ASC' && $sort_order != 'DESC') {
            $sort_order = 'DESC';
        }

        // Get group filter
        $group_id = null;
        if (isset($_POST['group_id']) && $_POST['group_id'] != '') {
            $group_id = (int)$_POST['group_id'];
        } else if (isset($_GET['group_id']) && $_GET['group_id'] != '') {
            $group_id = (int)$_GET['group_id'];
        }
        if ($group_id !== null && $group_id <= 0) {
            $group_id = null;
        }

        // Build query string for pagination
        $query_params = array();
        if (!is_null($key)) {
            $query_params['key'] = $key;
        }
        $query_params['sort_by'] = $sort_by;
        $query_params['sort_order'] = $sort_order;
        if ($group_id !== null) {
            $query_params['group_id'] = $group_id;
        }
        $config['suffix'] = '?' . http_build_query($query_params);
        
        // Get groups list for dropdown
        $this->data['groups'] = $this->ion_auth->grouplist()->result();
        $this->data['selected_group_id'] = $group_id;


        $config["base_url"] = site_url(current_lang() . '/auth/index/');
       $config["total_rows"] = $this->ion_auth->count_users($key, $group_id);
        $config["uri_segment"] = 4;

        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';

        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';

        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';

        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';

        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';


        $config["num_links"] = 10;


        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();

        $this->data['users'] = $this->ion_auth->all_users($key, $config["per_page"], $page, $sort_by, $sort_order, $group_id);
        
        // Pass sort parameters to view
        $this->data['sort_by'] = $sort_by;
        $this->data['sort_order'] = $sort_order;



        $this->data['content'] = 'auth/index';
        $this->load->view('template', $this->data);
    }

    //log the user in
    function login() {
        $this->data['title'] = "Login";

        //validate form input
        $this->form_validation->set_rules('identity', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == true) {
            //check to see if the user is logging in
            //check for "remember me"
            $remember = (bool) $this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                //if the login is successful
                //log the login activity
                $this->load->helper('activity_log');
                $user = $this->ion_auth->user()->row();
                log_login($user->id, $user->username);
                
                //redirect them back to the home page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('/', 'refresh');
            } else {
                //if the login was un-successful
                //redirect them back to the login page
                $this->session->set_flashdata('warning', $this->ion_auth->errors());
                redirect('auth/login', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
            }
        } else {
            //the user is not logging in so display the login page
            //set the flash data error message if there is one
          //  $this->data['message'] = ($this->session->flashdata('message');

            $this->data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
            );
            $this->data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
            );

            $this->data['logo'] = company_info_detail()->logo;
            $this->data['name'] = company_info_detail()->name;
            
            // Pass cookie names for remember me functionality
            $this->load->config('ion_auth', TRUE);
            $this->data['identity_cookie_name'] = $this->config->item('identity_cookie_name', 'ion_auth');
            $this->data['remember_cookie_name'] = $this->config->item('remember_cookie_name', 'ion_auth');

            $this->_render_page('auth/login', $this->data);
        }
    }

    //log the user out
    function logout() {
        $this->data['title'] = "Logout";

        //log the logout activity before logging out
        $this->load->helper('activity_log');
        if ($this->ion_auth->logged_in()) {
            $user = $this->ion_auth->user()->row();
            log_logout($user->id, $user->username);
        }

        //log the user out
        $logout = $this->ion_auth->logout();

        //redirect them to the login page
        $this->session->set_flashdata('message', $this->ion_auth->messages());
        redirect('auth/login', 'refresh');
    }

    //change password
    function change_password() {
        $this->data['title'] = 'Change Password';
        $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
        $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        $user = $this->ion_auth->user()->row();

        if ($this->form_validation->run() == false) {
            //display the form
            //set the flash data error message if there is one
            //$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
            $this->data['old_password'] = array(
                'name' => 'old',
                'id' => 'old',
                'class'=>'form-control',
                'type' => 'password',
            );
            $this->data['new_password'] = array(
                'name' => 'new',
                'id' => 'new',
                'class'=>'form-control',
                'type' => 'password',
                'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
            );
            $this->data['new_password_confirm'] = array(
                'name' => 'new_confirm',
                'id' => 'new_confirm',
                'class'=>'form-control',
                'type' => 'password',
                'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
            );
            $this->data['user_id'] = array(
                'name' => 'user_id',
                'id' => 'user_id',
                'type' => 'hidden',
                'value' => $user->id,
            );

             $this->data['content'] = 'auth/change_password';
        $this->load->view('template', $this->data);
            //render
           // $this->_render_page('auth/change_password', $this->data);
        } else {
            $identity = $this->session->userdata('identity');

            $change = $this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));

            if ($change) {
                //if the password was successfully changed
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                $this->logout();
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('auth/change_password', 'refresh');
            }
        }
    }

    //forgot password
    function forgot_password() {
        //setting validation rules by checking wheather identity is username or email
        if ($this->config->item('identity', 'ion_auth') == 'username') {
            $this->form_validation->set_rules('email', $this->lang->line('forgot_password_username_identity_label'), 'required');
        } else {
            $this->form_validation->set_rules('email', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
        }


        if ($this->form_validation->run() == false) {
            //setup the input
            $this->data['email'] = array('name' => 'email',
                'id' => 'email',
            );

            if ($this->config->item('identity', 'ion_auth') == 'username') {
                $this->data['identity_label'] = $this->lang->line('forgot_password_username_identity_label');
            } else {
                $this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
            }

            //set any errors and display the form
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            $this->_render_page('auth/forgot_password', $this->data);
        } else {
            // get identity from username or email
            if ($this->config->item('identity', 'ion_auth') == 'username') {
                $identity = $this->ion_auth->where('username', strtolower($this->input->post('email')))->users()->row();
            } else {
                $identity = $this->ion_auth->where('email', strtolower($this->input->post('email')))->users()->row();
            }
            if (empty($identity)) {

                if ($this->config->item('identity', 'ion_auth') == 'username') {
                    $this->ion_auth->set_message('forgot_password_username_not_found');
                } else {
                    $this->ion_auth->set_message('forgot_password_email_not_found');
                }

                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("auth/forgot_password", 'refresh');
            }

            //run the forgotten password method to email an activation code to the user
            $forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

            if ($forgotten) {
                //if there were no errors
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("auth/login", 'refresh'); //we should display a confirmation page here instead of the login page
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect("auth/forgot_password", 'refresh');
            }
        }
    }

    //reset password - final step for forgotten password
    public function reset_password($code = NULL) {
        if (!$code) {
            show_404();
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            //if the code is valid then display the password reset form

            $this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

            if ($this->form_validation->run() == false) {
                //display the form
                //set the flash data error message if there is one
                $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = array(
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
                );
                $this->data['new_password_confirm'] = array(
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
                );
                $this->data['user_id'] = array(
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                );
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;

                //render
                $this->_render_page('auth/reset_password', $this->data);
            } else {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {

                    //something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($code);

                    show_error($this->lang->line('error_csrf'));
                } else {
                    // finally change the password
                    $identity = $user->{$this->config->item('identity', 'ion_auth')};

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        //if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        $this->logout();
                    } else {
                        $this->session->set_flashdata('message', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code, 'refresh');
                    }
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    //activate the user
    function activate($id, $code=false) {
        $id = decode_id($id);
        
        if ($code !== false) {
            $activation = $this->ion_auth->activate($id, $code);
        } else  {
            $activation = $this->ion_auth->activate($id);
        }

        if ($activation) {
            //redirect them to the auth page
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("auth", 'refresh');
        } else {
            //redirect them to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    //deactivate the user
    function deactivate($id = NULL) {
        $this->data['title'] = lang('deactivate_heading');
        $id = decode_id($id);
        $id = (int) $id;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
        $this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

        $this->data['user'] = $this->ion_auth->user($id)->row();
        $this->data['csrf'] = $this->_get_csrf_nonce();

        // do we really want to deactivate?
        if ($this->input->post('confirm') == 'yes') {
            // do we have a valid request?
            // do we have the right userlevel?
            if ($this->ion_auth->logged_in()) {
                $this->ion_auth->deactivate($id);
            }
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            //redirect them back to the auth page
            redirect(current_lang() . '/auth/index', 'refresh');
        }



        $this->data['content'] = 'auth/deactivate_user';
        $this->load->view('template', $this->data);
    }

    //delete the user (soft delete - hide the record)
    function delete_user($id = NULL) {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $id = decode_id($id);
        $id = (int) $id;

        if (!$id) {
            $this->session->set_flashdata('warning', 'Invalid user ID');
            redirect(current_lang() . '/auth/index', 'refresh');
        }

        // Prevent deleting the current logged-in user
        $current_user = $this->ion_auth->user()->row();
        if ($id == $current_user->id) {
            $this->session->set_flashdata('warning', 'You cannot delete your own account');
            redirect(current_lang() . '/auth/index', 'refresh');
        }

        // Perform soft delete - set deleted field to 1 (hide the record)
        $pin = current_user()->PIN;
        $this->db->where('id', $id);
        $this->db->where('PIN', $pin);
        $this->db->update('users', array('deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('message', 'User has been deleted successfully');
        } else {
            $this->session->set_flashdata('warning', 'Failed to delete user');
        }

        redirect(current_lang() . '/auth/index', 'refresh');
    }

    //create a new user
    function create_user() {
        $this->data['title'] = lang('account_creation_new');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $tables = $this->config->item('tables', 'ion_auth');

        //validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required|xss_clean');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required|xss_clean');
        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
        $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'required|xss_clean');
        $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');
        $this->form_validation->set_rules('username', $this->lang->line('create_user_username_label'), 'required|is_unique[users.username]');
        $this->form_validation->set_rules('groupname', $this->lang->line('edit_group_name_label'), 'required');


        if ($this->form_validation->run() == true) {
            $username = trim($this->input->post('username'));
            $email = strtolower($this->input->post('email'));
            $password = trim($this->input->post('password'));
            $users_group = trim($this->input->post('groupname'));

            $additional_data = array(
                'first_name' => ucwords(strtolower(trim($this->input->post('first_name')))),
                'last_name' => ucwords(strtolower(trim($this->input->post('last_name')))),
                'phone' => trim($this->input->post('pre_phone')) . trim($this->input->post('phone')),
                  'PIN' => current_user()->PIN
                
            );
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, array($users_group))) {
            //check to see if we are creating the user
            //redirect them back to the admin page
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect(current_lang() . "/auth/create_user", 'refresh');
        }
        //display the create user form
        //set the flash data error message if there is one
        $this->data['warning'] = ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message'));
        $this->data['grouplist'] = $this->ion_auth->grouplist()->result();

        $this->data['content'] = 'auth/create_user';
        $this->load->view('template', $this->data);
    }

    //edit a user
    function edit_user($id) {
        $this->data['title'] = "Edit User";
        $id = decode_id($id);
        if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && !($this->ion_auth->user()->row()->id == $id))) {
            redirect('auth', 'refresh');
        }

        $user = $this->ion_auth->user($id)->row();
        $groups = $this->ion_auth->groups()->result_array();
        $currentGroups = $this->ion_auth->get_users_groups($id)->result();

        //validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'required|xss_clean');
        $this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'required|xss_clean');
        $this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'required|xss_clean');
        $this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'required|xss_clean');
        $this->form_validation->set_rules('groups', $this->lang->line('edit_user_validation_groups_label'), 'xss_clean');

        if (isset($_POST) && !empty($_POST)) {
            // do we have a valid request?
            if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                show_error($this->lang->line('error_csrf'));
            }

            //update the password if it was posted
            if ($this->input->post('password')) {
                $this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
                $this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
            }

            if ($this->form_validation->run() === TRUE) {
                $data = array(
                   'PIN' => current_user()->PIN,
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                );

                //update the password if it was posted
                if ($this->input->post('password')) {
                    $data['password'] = $this->input->post('password');
                }



                // Only allow updating groups if user is admin
                if ($this->ion_auth->is_admin()) {
                    //Update the groups user belongs to
                    $groupData = $this->input->post('groups');

                    if (isset($groupData) && !empty($groupData)) {

                        $this->ion_auth->remove_from_group('', $id);

                        foreach ($groupData as $grp) {
                            $this->ion_auth->add_to_group($grp, $id);
                        }
                    }
                }

                //check to see if we are updating the user
                if ($this->ion_auth->update($user->id, $data)) {
                    //redirect them back to the admin page if admin, or to the base url if non admin
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                    if ($this->ion_auth->is_admin()) {
                        redirect('auth', 'refresh');
                    } else {
                        redirect('/', 'refresh');
                    }
                } else {
                    //redirect them back to the admin page if admin, or to the base url if non admin
                    $this->session->set_flashdata('message', $this->ion_auth->errors());
                    if ($this->ion_auth->is_admin()) {
                        redirect('auth', 'refresh');
                    } else {
                        redirect('/', 'refresh');
                    }
                }
            }
        }

        //display the edit user form
        $this->data['csrf'] = $this->_get_csrf_nonce();

        //set the flash data error message if there is one
        $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

        //pass the user to the view
        $this->data['user'] = $user;
        $this->data['groups'] = $groups;
        $this->data['currentGroups'] = $currentGroups;

        $this->data['first_name'] = array(
            'name' => 'first_name',
            'id' => 'first_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('first_name', $user->first_name),
        );
        $this->data['last_name'] = array(
            'name' => 'last_name',
            'id' => 'last_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('last_name', $user->last_name),
        );
        $this->data['company'] = array(
            'name' => 'company',
            'id' => 'company',
            'type' => 'text',
            'value' => $this->form_validation->set_value('company', $user->company),
        );
        $this->data['phone'] = array(
            'name' => 'phone',
            'id' => 'phone',
            'type' => 'text',
            'value' => $this->form_validation->set_value('phone', $user->phone),
        );
        $this->data['password'] = array(
            'name' => 'password',
            'id' => 'password',
            'type' => 'password'
        );
        $this->data['password_confirm'] = array(
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'type' => 'password'
        );

        //$this->_render_page('auth/edit_user', $this->data);
        
        //pass the user to the view
        //$this->data['group'] = $group;
        $this->data['content'] = 'auth/edit_user';
        $this->load->view('template', $this->data);
    }

    //group list
    function grouplist() {
        $this->data['title'] = $this->lang->line('view_group_list');

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }


        $this->data['grouplist'] = $this->ion_auth->grouplist()->result();
        $this->data['content'] = 'auth/grouplist';
        $this->load->view('template', $this->data);
    }

    // create a new group
    function create_group() {
        $this->data['title'] = $this->lang->line('create_group_title');

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        //validate form input
        $this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'required|alpha_dash|xss_clean');
        $this->form_validation->set_rules('description', $this->lang->line('create_group_validation_desc_label'), 'xss_clean|required');

        if ($this->form_validation->run() == TRUE) {
            $new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
            if ($new_group_id) {
                // check to see if we are creating the group
                // redirect them back to the admin page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect(current_lang() . "/auth/create_group", 'refresh');
            }
        }

        //display the create group form
        //set the flash data error message if there is one
        $this->data['warning'] = ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message'));

        $this->data['content'] = 'auth/create_group';
        $this->load->view('template', $this->data);
    }

    //edit a group
    function edit_group($id) {
        // bail if no group id given
        $id = decode_id($id);
        if (!$id || empty($id)) {
            redirect(current_lang() . '/dashboard', 'refresh');
        }

        $this->data['title'] = $this->lang->line('edit_group_title');

        if (!$this->ion_auth->logged_in()) {
            redirect(current_lang() . '/dashboard', 'refresh');
        }

        $group = $this->ion_auth->group($id)->row();

        //validate form input
        $this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'required|alpha_dash|xss_clean');
        $this->form_validation->set_rules('group_description', $this->lang->line('edit_group_validation_desc_label'), 'xss_clean|required');



        if ($this->form_validation->run() === TRUE) {

            $group_update = $this->ion_auth->update_group($id, $_POST['group_name'], $_POST['group_description']);

            if ($group_update) {
                $this->session->set_flashdata('message', $this->lang->line('edit_group_saved_success'));
            } else {
                $this->session->set_flashdata('warning', $this->ion_auth->errors());
            }
            redirect(current_lang() . "/auth/edit_group/" . encode_id($id), 'refresh');
        }



        //pass the user to the view
        $this->data['group'] = $group;
        $this->data['content'] = 'auth/edit_group';
        $this->load->view('template', $this->data);
    }

    function _get_csrf_nonce() {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    function _valid_csrf_nonce() {
        if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
                $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function _render_page($view, $data=null, $render=false) {

        $this->viewdata = (empty($data)) ? $this->data : $data;

        $view_html = $this->load->view($view, $this->viewdata, $render);

        if (!$render)
            return $view_html;
    }
    
    
    function grouprole($group_id){
           
        
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }

        $this->data['title'] = lang('privillege_heading');
         $group_id = decode_id($group_id);
         
        $group = $this->ion_auth->group($group_id)->row();
        if ($this->input->post('save')) {
            $priv = $this->ion_auth->privilege_list($group_id);
            foreach ($priv[1] as $key => $value) {
                foreach ($value as $k => $v) {
                    if ($this->input->post('module_' . $v[0] . '_' . $v[1])) {
                        $check = $this->db->get_where('access_level', array('group_id' => $group_id, 'Module' => $v[0], 'link' => $k))->row();
                        if ($check !== null) {
                            $this->db->update('access_level', array('allow' => $this->input->post('module_' . $v[0] . '_' . $v[1])), array('group_id' => $group_id, 'Module' => $v[0], 'link' => $k));
                        } else {
                            $this->db->insert('access_level', array('group_id' => $group_id, 'Module' => $v[0], 'link' => $k, 'allow' => 1));
                        }
                    } else {
                        $this->db->update('access_level', array('allow' => 0), array('group_id' => $group_id, 'Module' => $v[0], 'link' => $k));
                    }
                }
            }
            $this->session->set_flashdata('message', lang('privillege_settings_success'));
            redirect(current_lang().'/auth/grouprole/' . encode_id($group_id), 'refresh');
        }
        $this->data['privilege_list'] = $this->ion_auth->privilege_list($group_id);
        $this->data['group_id'] = $group_id;
        $this->data['group_info'] = $group;
        
        
        
        
        $this->data['content'] = 'auth/assign_privillege';
        $this->load->view('template',  $this->data);
    }

    //register a new user (public registration)
    function register() {
        $this->data['title'] = "Register";

        // If user is already logged in, redirect to home
        if ($this->ion_auth->logged_in()) {
            redirect('/', 'refresh');
        }

        $tables = $this->config->item('tables', 'ion_auth');

        //validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required|xss_clean');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required|xss_clean');
        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
        $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');
        $this->form_validation->set_rules('username', $this->lang->line('create_user_username_label'), 'required|is_unique[users.username]');

        if ($this->form_validation->run() == true) {
            $username = trim($this->input->post('username'));
            $email = strtolower($this->input->post('email'));
            $password = trim($this->input->post('password'));
            
            // Get the member group ID
            $member_group = $this->ion_auth->where('name', 'member')->group()->row();
            
            if (!$member_group) {
                // If member group doesn't exist, try to get default group from config
                $default_group_name = $this->config->item('default_group', 'ion_auth');
                $member_group = $this->ion_auth->where('name', $default_group_name)->group()->row();
            }
            
            if (!$member_group) {
                // If still no group found, use first available group (fallback)
                $member_group = $this->ion_auth->groups()->row();
            }

            $additional_data = array(
                'first_name' => ucwords(strtolower(trim($this->input->post('first_name')))),
                'last_name' => ucwords(strtolower(trim($this->input->post('last_name'))))
            );

            // PIN is not required for public registration, but if the field exists and is required, 
            // you may need to set a default value or handle it differently based on your database schema
            // For now, we'll omit it as public users may not have a PIN assigned yet

            // Register user with member group
            $group_id = $member_group ? $member_group->id : null;
            $groups_array = $group_id ? array($group_id) : array();

            if ($this->ion_auth->register($username, $password, $email, $additional_data, $groups_array)) {
                //if the registration is successful
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('auth/login', 'refresh');
            } else {
                //if the registration was un-successful
                $this->session->set_flashdata('warning', $this->ion_auth->errors());
                redirect('auth/register', 'refresh');
            }
        } else {
            //the user is not registering so display the register page
            //set the flash data error message if there is one
            $this->data['warning'] = ($this->ion_auth->errors() ? $this->ion_auth->errors() : (validation_errors() ? validation_errors() : $this->session->flashdata('warning')));
            $this->data['message'] = $this->session->flashdata('message');

            $this->data['logo'] = company_info_detail()->logo;
            $this->data['name'] = company_info_detail()->name;

            $this->_render_page('auth/register', $this->data);
        }
    }

}
