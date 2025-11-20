<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of member
 *
 * @author miltone
 */
class Member extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_member');
        $this->lang->load('setting');
        $this->lang->load('member');
        $this->load->model('member_model');
        $this->load->library('ion_auth');
    }

    /*
     * Function to uploads files to server
     * @author Miltone Urassa
     * @Contact miltoneurassa@yahoo.com
     */

    function upload_file($array, $name, $folder) {
        $filename = time() . $array[$name]['name'];

        $path = './' . $folder . '/';
        $path1 = './' . $folder . '/';
        $path = $path . basename($filename);

        if (move_uploaded_file($_FILES[$name]['tmp_name'], $path)) {
            // chmod($path1.$filename, 777);
            return $filename;
        } else {
            return 0;
        }
    }

    /*
     *  @author Miltone Urassa
     *  @Contact : miltoneurassa@yahoo.com
     *  function Name :  getExtension
     *  Description : File extension
     *  @parm filename
     *  @return file extension in lower case
     * 
     */

    function getExtension($str) {
        $i = strrpos($str, ".");
        if (!$i) {
            return "";
        }
        $l = strlen($str) - $i;
        $ext = substr($str, $i + 1, $l);
        return strtolower($ext);
    }

    function index() {
        
    }

    function deactivate($id) {
        $id = decode_id($id);
        $this->db->update('members', array('status' => 0), array('id' => $id));
        $this->session->set_flashdata('message', lang('member_deactivated'));
        redirect(current_lang() . '/member/member_list', 'refresh');
    }

    function activate($id) {
        $id = decode_id($id);
        $this->db->update('members', array('status' => 1), array('id' => $id));
        $this->session->set_flashdata('message', lang('member_activated'));
        redirect(current_lang() . '/member/member_list', 'refresh');
    }

    function add_group() {
        $this->data['title'] = lang('member_add_group');

        $this->form_validation->set_rules('gpname', lang('member_group_name'), 'required');
        $this->form_validation->set_rules('gpdescription', lang('member_group_description'), 'required');
        if ($this->form_validation->run() == TRUE) {

            $new_group = array(
                'name' => trim($this->input->post('gpname')),
                'description' => trim($this->input->post('gpdescription')),
                'createdby' => current_user()->id,
                'PIN' => current_user()->PIN,
            );

            $return = $this->member_model->add_group($new_group);
            if ($return) {
                $this->session->set_flashdata('message', lang('member_group_success'));
                redirect(current_lang() . '/member/add_group', 'refresh');
            } else {
                $this->data['warning'] = lang('member_group_fail');
            }
        }

        $this->data['content'] = 'member/create_member_group';
        $this->load->view('template', $this->data);
    }

    function member_group_edit($id) {
        $this->data['id'] = $id;

        $id = decode_id($id);
        $this->data['grouplist'] = $this->member_model->member_group($id)->row();
        $this->data['title'] = lang('member_add_group_edit');

        $this->form_validation->set_rules('gpname', lang('member_group_name'), 'required');
        $this->form_validation->set_rules('gpdescription', lang('member_group_description'), 'required');
        if ($this->form_validation->run() == TRUE) {

            $new_group = array(
                'name' => trim($this->input->post('gpname')),
                'description' => trim($this->input->post('gpdescription')),
                'createdby' => current_user()->id,
            );
            $return = $this->member_model->edit_group($new_group, $id);
            if ($return) {
                $this->session->set_flashdata('message', lang('member_group_success'));
                redirect(current_lang() . '/member/member_group_edit/' . encode_id($id), 'refresh');
            } else {
                $this->data['warning'] = lang('member_group_fail');
            }
        }

        $this->data['content'] = 'member/edit_member_group';
        $this->load->view('template', $this->data);
    }

    function member_group_list() {
        $this->data['title'] = lang('member_group_list');
        $this->data['grouplist'] = $this->member_model->member_group()->result();
        $this->data['content'] = 'member/member_grouplist';
        $this->load->view('template', $this->data);
    }

    function new_member() {

        $this->data['title'] = lang('member_registration');
        if ($this->input->post('fee')) {
            $_POST['fee'] = str_replace(',', '', $_POST['fee']);
        }
        $this->form_validation->set_rules('fee', lang('member_registration_fee'), 'required|numeric');
        $this->form_validation->set_rules('memberid', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('firstname', lang('member_firstname'), 'required');
        $this->form_validation->set_rules('middlename', lang('member_middlename'), 'alpha');
        $this->form_validation->set_rules('lastname', lang('member_lastname'), 'required');
        $this->form_validation->set_rules('gender', lang('member_gender'), 'required|alpha');
        $this->form_validation->set_rules('maritalstatus', lang('member_maritalstatus'), 'required');
        $this->form_validation->set_rules('dob', lang('member_dob'), 'required|valid_date');
        $this->form_validation->set_rules('joindate', lang('member_join_date'), 'required|valid_date');



        // upload files
        $upload_photo = true;
        $file_name = 0;
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
                $this->data['logo_error'] = lang('member_photo_error');
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads/memberphoto');
                $upload_photo = TRUE;
            }
        }


        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $memberfee = default_text_value('REGISTRATION_FEE');

            $registrationfee = trim($this->input->post('fee'));
            if ($memberfee <= $registrationfee) {
                $member_id = trim($this->input->post('memberid'));
                //check if member id exist

                if (!$this->member_model->is_member_exist($member_id)) {
                    //add new member
                    $new_member = array(
                        'member_id' => $member_id,
                        'firstname' => trim($this->input->post('firstname')),
                        'middlename' => trim($this->input->post('middlename')),
                        'maidenname' => trim($this->input->post('maidenname')),
                        'lastname' => trim($this->input->post('lastname')),
                        'gender' => trim($this->input->post('gender')),
                        'maritalstatus' => trim($this->input->post('maritalstatus')),
                        'dob' => format_date(trim($this->input->post('dob'))),
                        'joiningdate' => format_date(trim($this->input->post('joindate'))),
                        'createdby' => $this->session->userdata('user_id'),
                        'PIN' => current_user()->PIN
                    );
                    if ($file_name != 0) {
                        $new_member['photo'] = $file_name;
                    }
                    $registrationfee = trim($this->input->post('fee'));
                    $return = $this->member_model->add_member($new_member, $registrationfee);
                    if ($return) {

                        $username = $member_id;
                        $email = $member_id;
                        $password = alphaID($return, FALSE, 4);

                        // create account for login
                        $additional_data = array(
                            'first_name' => $new_member['firstname'],
                            'last_name' => $new_member['lastname'],
                            'member_id' => $member_id,
                            'oldpass' => $password,
                            'MID' => $return,
                            'PIN' => current_user()->PIN,
                            'company' => company_info()->name,
                        );

                        $this->ion_auth->register($username, $password, $email, $additional_data, array(3));


                        $this->session->set_flashdata('message', lang('member_create_success'));
                        redirect(current_lang() . '/member/memberinfo/' . encode_id($return), 'refresh');
                    } else {
                        $this->data['warning'] = lang('member_create_fail');
                    }
                } else {
                    $this->data['warning'] = lang('member_exist');
                }
            } else {
                $this->data['warning'] = lang('member_invalid_registrationfee');
            }
        }

        $this->data['content'] = 'member/new_member';
        $this->load->view('template', $this->data);
    }
    function none_member() {

        $this->data['title'] = lang('member_none_registration');
        
        //$this->form_validation->set_rules('fee', lang('member_registration_fee'), 'required|numeric');
        //$this->form_validation->set_rules('memberid', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('firstname', lang('member_firstname'), 'required');
        $this->form_validation->set_rules('middlename', lang('member_middlename'), 'alpha');
        $this->form_validation->set_rules('lastname', lang('member_lastname'), 'required');
        $this->form_validation->set_rules('gender', lang('member_gender'), 'required|alpha');
        $this->form_validation->set_rules('maritalstatus', lang('member_maritalstatus'), 'required');
        $this->form_validation->set_rules('dob', lang('member_dob'), 'required|valid_date');
        $this->form_validation->set_rules('joindate', lang('member_join_date'), 'required|valid_date');



        // upload files
        $upload_photo = true;
        $file_name = 0;
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
                $this->data['logo_error'] = lang('member_photo_error');
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads/memberphoto');
                $upload_photo = TRUE;
            }
        }


        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $memberfee = default_text_value('REGISTRATION_FEE');

            $registrationfee = trim($this->input->post('fee'));
            $none_member_flag = trim($this->input->post('none_member'));
            
            if ($registrationfee==0) {
                //$member_id = trim($this->input->post('memberid'));
                $member_id = $this->db->get('auto_inc')->row()->none_member;

                // increment 1 next PIN
                $this->db->set('none_member', 'none_member+1', FALSE);
                $this->db->update('auto_inc');

                //check if member id exist

                if (!$this->member_model->is_member_exist($member_id)) {
                    //add new member
                    $new_member = array(
                        'member_id' => $member_id,
                        'firstname' => trim($this->input->post('firstname')),
                        'middlename' => trim($this->input->post('middlename')),
                        'maidenname' => trim($this->input->post('maidenname')),
                        'lastname' => trim($this->input->post('lastname')),
                        'gender' => trim($this->input->post('gender')),
                        'maritalstatus' => trim($this->input->post('maritalstatus')),
                        'none_member' => $none_member_flag,
                        'dob' => format_date(trim($this->input->post('dob'))),
                        'joiningdate' => format_date(trim($this->input->post('joindate'))),
                        'createdby' => $this->session->userdata('user_id'),
                        'PIN' => current_user()->PIN
                    );
                    if ($file_name != 0) {
                        $new_member['photo'] = $file_name;
                    }
                    $registrationfee = trim($this->input->post('fee'));
                    $return = $this->member_model->add_member($new_member, $registrationfee);
                    if ($return) {

                        $username = $member_id;
                        $email = $member_id;
                        $password = alphaID($return, FALSE, 4);

                        // create account for login
                        $additional_data = array(
                            'first_name' => $new_member['firstname'],
                            'last_name' => $new_member['lastname'],
                            'member_id' => $member_id,
                            'oldpass' => $password,
                            'MID' => $return,
                            'PIN' => current_user()->PIN,
                            'company' => company_info()->name,
                        );

                        $this->ion_auth->register($username, $password, $email, $additional_data, array(3));


                        $this->session->set_flashdata('message', lang('member_create_success'));
                        redirect(current_lang() . '/member/memberinfo/' . encode_id($return), 'refresh');
                    } else {
                        $this->data['warning'] = lang('member_create_fail');
                    }
                }
            } else {
                $this->data['warning'] = lang('member_invalid_registrationfee');
            }
        }

        $this->data['content'] = 'member/none_member';
        $this->load->view('template', $this->data);
    }

    function memberinfo($id) {
        $id = decode_id($id);
        $this->data['basicinfo'] = $this->member_model->member_basic_info($id)->row();

        $status = lang('member_registration_status');
        $member_id = trim($this->input->post('memberid'));
        $this->data['title'] = lang('member_infopage');
        if ($this->data['basicinfo']->formstatus != 3) {
            $this->data['subtitle'] = ' : ' . lang('member_registration_status_label') . ' <label>' . $status[$this->data['basicinfo']->formstatus] . '</label>';
        }

        //$this->form_validation->set_rules('firstname', lang('member_firstname'), 'required|alpha');
        //$this->form_validation->set_rules('middlename', lang('member_middlename'), 'alpha');
        //$this->form_validation->set_rules('lastname', lang('member_lastname'), 'required|alpha');
        $this->form_validation->set_rules('memberid', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('firstname', lang('member_firstname'), 'required');
        $this->form_validation->set_rules('middlename', lang('member_middlename'), 'alpha');
        $this->form_validation->set_rules('lastname', lang('member_lastname'), 'required');
        $this->form_validation->set_rules('gender', lang('member_gender'), 'required|alpha');
        $this->form_validation->set_rules('maritalstatus', lang('member_maritalstatus'), 'required');
        $this->form_validation->set_rules('dob', lang('member_dob'), 'required|valid_date');
        $this->form_validation->set_rules('joindate', lang('member_join_date'), 'required|valid_date');


        // check if new photo uploaded
        // upload files
        $upload_photo = true;
        $file_name = 0;
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
                $this->data['logo_error'] = 'Invalid format, only jpg,jpeg,png and gif is supported';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads/memberphoto');
                $upload_photo = TRUE;
            }
        }




        if ($this->form_validation->run() == TRUE & $upload_photo == TRUE) {
            //edit member info
            $edit_member = array(
                'member_id' => $member_id,
                'firstname' => trim($this->input->post('firstname')),
                'middlename' => trim($this->input->post('middlename')),
                'maidenname' => trim($this->input->post('maidenname')),
                'lastname' => trim($this->input->post('lastname')),
                'gender' => trim($this->input->post('gender')),
                'maritalstatus' => trim($this->input->post('maritalstatus')),
                'dob' => format_date(trim($this->input->post('dob'))),
                'joiningdate' => format_date(trim($this->input->post('joindate'))),
            );

            if ($file_name != 0) {
                $edit_member['photo'] = $file_name;
            }

            $return = $this->member_model->edit_member($edit_member, $id);
            if ($return) {
                $this->session->set_flashdata('message', lang('member_edited_success'));
                redirect(current_lang() . '/member/memberinfo/' . encode_id($id), 'refresh');
            } else {
                $this->data['warning'] = lang('member_edited_fail');
            }
        }



        $this->data['content'] = 'member/edit_memberinfo';
        $this->load->view('template', $this->data);
    }

    function none_memberinfo($id) {
        $id = decode_id($id);
        $this->data['basicinfo'] = $this->member_model->member_basic_info($id)->row();

        $status = lang('member_registration_status');
        $member_id = trim($this->input->post('memberid'));
        $this->data['title'] = lang('member_infopage');
        if ($this->data['basicinfo']->formstatus != 3) {
            $this->data['subtitle'] = ' : ' . lang('member_registration_status_label') . ' <label>' . $status[$this->data['basicinfo']->formstatus] . '</label>';
        }

        //$this->form_validation->set_rules('firstname', lang('member_firstname'), 'required|alpha');
        //$this->form_validation->set_rules('middlename', lang('member_middlename'), 'alpha');
        //$this->form_validation->set_rules('lastname', lang('member_lastname'), 'required|alpha');
        $this->form_validation->set_rules('memberid', lang('member_member_id'), 'required');
        $this->form_validation->set_rules('firstname', lang('member_firstname'), 'required');
        $this->form_validation->set_rules('middlename', lang('member_middlename'), 'alpha');
        $this->form_validation->set_rules('lastname', lang('member_lastname'), 'required');
        $this->form_validation->set_rules('gender', lang('member_gender'), 'required|alpha');
        $this->form_validation->set_rules('maritalstatus', lang('member_maritalstatus'), 'required');
        $this->form_validation->set_rules('dob', lang('member_dob'), 'required|valid_date');
        $this->form_validation->set_rules('joindate', lang('member_join_date'), 'required|valid_date');


        // check if new photo uploaded
        // upload files
        $upload_photo = true;
        $file_name = 0;
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) {
                $this->data['logo_error'] = 'Invalid format, only jpg,jpeg,png and gif is supported';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads/memberphoto');
                $upload_photo = TRUE;
            }
        }




        if ($this->form_validation->run() == TRUE & $upload_photo == TRUE) {
            //edit member info
            $edit_member = array(
                'member_id' => $member_id,
                'firstname' => trim($this->input->post('firstname')),
                'middlename' => trim($this->input->post('middlename')),
                'maidenname' => trim($this->input->post('maidenname')),
                'lastname' => trim($this->input->post('lastname')),
                'gender' => trim($this->input->post('gender')),
                'maritalstatus' => trim($this->input->post('maritalstatus')),
                'dob' => format_date(trim($this->input->post('dob'))),
                'joiningdate' => format_date(trim($this->input->post('joindate'))),
            );

            if ($file_name != 0) {
                $edit_member['photo'] = $file_name;
            }

            $return = $this->member_model->edit_member($edit_member, $id);
            if ($return) {
                $this->session->set_flashdata('message', lang('member_edited_success'));
                redirect(current_lang() . '/member/none_memberinfo/' . encode_id($id), 'refresh');
            } else {
                $this->data['warning'] = lang('member_edited_fail');
            }
        }



        $this->data['content'] = 'member/edit_none_memberinfo';
        $this->load->view('template', $this->data);
    }

    function membercontact($id) {


        $id = decode_id($id);
        $this->data['basicinfo'] = $this->member_model->member_basic_info($id)->row();
        $this->data['contactinfo'] = $this->member_model->member_contact($this->data['basicinfo']->PID);

        $status = lang('member_registration_status');
        $this->data['title'] = lang('member_contact_info');
        if ($this->data['basicinfo']->formstatus != 3) {
            $this->data['subtitle'] = ' : ' . lang('member_registration_status_label') . ' <label>' . $status[$this->data['basicinfo']->formstatus] . '</label>';
        }
        $this->form_validation->set_rules('pre_phone1', '', 'required');
        $this->form_validation->set_rules('pre_phone2', '', 'required');
        //$this->form_validation->set_rules('phone1', lang('member_contact_phone1'), 'required|numeric|valid_phone');
        $this->form_validation->set_rules('phone1', lang('member_contact_phone1'), 'numeric|numeric|valid_phone');
        $this->form_validation->set_rules('phone2', lang('member_contact_phone2'), 'numeric|valid_phone');
        $this->form_validation->set_rules('email', lang('member_contact_email'), 'valid_email');
        $this->form_validation->set_rules('box', lang('member_contact_box'), '');
        $this->form_validation->set_rules('physical', lang('member_contact_physical'), '');
        $this->form_validation->set_rules('occupation', lang('member_contact_occupation'), '');
        $this->form_validation->set_rules('tinno', lang('member_contact_tinno'), '');
        $this->form_validation->set_rules('sssno', lang('member_contact_sssno'), '');
        $this->form_validation->set_rules('bpno', lang('member_contact_bpno'), '');
        $this->form_validation->set_rules('officeaddress', lang('member_contact_officeaddress'), '');
        $this->form_validation->set_rules('assignedschool', 'Assigned School', '');
        

        if ($this->form_validation->run() == TRUE) {
            $member_contact = array(
                'PID' => $this->data['basicinfo']->PID,
                'phone1' => trim($this->input->post('pre_phone1')) . trim($this->input->post('phone1')),
                'email' => trim($this->input->post('email')),
                'occupation' => trim($this->input->post('occupation')),
                'tinno' => trim($this->input->post('tinno')),
                'sssno' => trim($this->input->post('sssno')),
                'bpno' => trim($this->input->post('bpno')),
                'postaladdress' => trim($this->input->post('box')),
                'physicaladdress' => trim($this->input->post('physical')),
                'assignedschool' => trim($this->input->post('assignedschool')),
                'officeaddress' => trim($this->input->post('officeaddress')),
                'createdby' => current_user()->id,
                'PIN'=>  current_user()->PIN
            );

            if ($this->input->post('phone2')) {
                $member_contact['phone2'] = trim($this->input->post('pre_phone2')) . trim($this->input->post('phone2'));
            }


            $return = $this->member_model->add_contact($member_contact, $id, $this->data['basicinfo']->formstatus);
            if ($return) {
                $this->session->set_flashdata('message', lang('member_contact_success'));
                redirect(current_url(), 'refresh');
            } else {
                $this->data['warning'] = lang('member_contact_fail');
            }
        }

        $this->data['content'] = 'member/contactinfo';
        $this->load->view('template', $this->data);
    }

    function membernextkin($id) {

        $id = decode_id($id);
        $this->data['basicinfo'] = $this->member_model->member_basic_info($id)->row();
        $this->data['nextkininfo'] = $this->member_model->member_nextkin($this->data['basicinfo']->PID);

        $status = lang('member_registration_status');
        $this->data['title'] = lang('nextkin_title');
        if ($this->data['basicinfo']->formstatus != 3) {
            $this->data['subtitle'] = ' : ' . lang('member_registration_status_label') . ' <label>' . $status[$this->data['basicinfo']->formstatus] . '</label>';
        }
        $this->form_validation->set_rules('pre_phone1', '', 'required');
        $this->form_validation->set_rules('phone1', lang('member_contact_phone1'), 'required|numeric|valid_phone');
        $this->form_validation->set_rules('email', lang('member_contact_email'), 'valid_email');
        $this->form_validation->set_rules('name', lang('nextkin_name'), 'required');
        $this->form_validation->set_rules('relationship', lang('nextkin_relationship'), 'required');
        $this->form_validation->set_rules('box', lang('member_contact_box'), '');
        $this->form_validation->set_rules('physical', lang('member_contact_physical'), '');


        if ($this->form_validation->run() == TRUE) {
            $member_nextkin = array(
                'PID' => $this->data['basicinfo']->PID,
                'phone' => trim($this->input->post('pre_phone1')) . trim($this->input->post('phone1')),
                'email' => trim($this->input->post('email')),
                'relationship' => trim($this->input->post('relationship')),
                'name' => trim($this->input->post('name')),
                'postaladdress' => trim($this->input->post('box')),
                'physicaladdress' => trim($this->input->post('physical')),
                'createdby' => current_user()->id,
                'PIN'=>  current_user()->PIN
            );




            $return = $this->member_model->add_nextkininfo($member_nextkin, $id, $this->data['basicinfo']->formstatus);
            if ($return) {
                $this->session->set_flashdata('message', lang('member_contact_success'));
                redirect(current_url(), 'refresh');
            } else {
                $this->data['warning'] = lang('member_contact_fail');
            }
        }

        $this->data['content'] = 'member/nextkininfo';
        $this->load->view('template', $this->data);
    }

    function member_list() {
        $this->load->library('pagination');
        $this->data['title'] = lang('member_list');

        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }


        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        $searchstatus = null;
        $searchmember = null;
        
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }
        $this->data['key']= $key;

        if (isset($_POST['searchstatus']) || $_POST['searchstatus'] != '') {
            $searchstatus = $_POST['searchstatus'];
        } else if (isset($_GET['searchstatus'])) {
            $searchstatus = $_GET['searchstatus'];
        } else {
            $searchstatus = '';
        }
        $this->data['searchstatus']=$searchstatus;

        if (isset($_POST['searchmember']) || $_POST['searchmember'] != '') {
            $searchmember = $_POST['searchmember'];
        } else if (isset($_GET['searchmember'])) {
            $searchmember = $_GET['searchmember'];
        } else {
            $searchmember = '';
        }
        $this->data['searchmember']=$searchmember;


        $suffix_array = array();

        if (!is_null($key)) {
            $suffix_array['key'] = $key;
        }

        if (!is_null($searchstatus) || $searchstatus!='') {
            $suffix_array['searchstatus'] = $searchstatus;
        }
        if (!is_null($searchmember) || $searchmember!='') {
            $suffix_array['searchmember'] = $searchmember;
        }
        $this->data['jxy'] = $suffix_array;
        if (count($suffix_array) > 0) {
            $query_string = http_build_query($suffix_array, '', '&');
            $config['suffix'] = '?' . $query_string;
        }

        /*if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }*/


        $config["base_url"] = site_url(current_lang() . '/member/member_list/');
        $config["total_rows"] = $this->member_model->count_member($key, $searchstatus, $searchmember);
        $config["uri_segment"] = 4;

        $config['full_tag_open'] = '<div class="pagination" style="background-color:#fff; margin-left:0px;">';
        $config['full_tag_close'] = '</div>';

        $config['num_tag_open'] = '<div class="link-pagination">';
        $config['num_tag_close'] = '</div>';

        $config['prev_tag_open'] = '<div class="link-pagination">';
        $config['prev_tag_close'] = '</div>';

        $config['next_tag_open'] = '<div class="link-pagination">';
        $config['next_tag_close'] = '</div>';
        
        $config['last_tag_open'] = '<div class="link-pagination">';
        $config['last_tag_close'] = '</div>';
        
        $config['first_tag_open'] = '<div class="link-pagination">';
        $config['first_tag_close'] = '</div>';

        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Previous';
        $config['cur_tag_open'] = '<div class="link-pagination current">';
        $config['cur_tag_close'] = '</div>';


        $config["num_links"] = 10;


        $this->pagination->initialize($config);
        $page = ($this->uri->segment(4) ? $this->uri->segment(4) : 0);
        $this->data['links'] = $this->pagination->create_links();

        $this->data['member_list'] = $this->member_model->search_member($key,$searchstatus, $searchmember, $config["per_page"], $page);



        $this->data['content'] = 'member/memberlist';
        $this->load->view('template', $this->data);
    }

    function membergroup($id) {
        $id = decode_id($id);
        $this->data['title'] = lang('member_addgroup');
        $this->data['basicinfo'] = $this->member_model->member_basic_info($id)->row();
        $this->data['allgroup'] = $this->member_model->member_group()->result();
        $selected_group = $this->member_model->member_group_cross($this->data['basicinfo']->PID);

        $selected_group_array1 = array();
        foreach ($selected_group as $key => $value) {
            $selected_group_array1[] = $value->group_id;
        }

        $selected_gp_array = $selected_group_array1;
        if ($this->input->post('SAVEGRP')) {
            if ($this->input->post('selectedgp')) {

                $expl = explode(',', $this->input->post('selectedgp'));
                $delete_this = array_diff($selected_gp_array, $expl);
                foreach ($delete_this as $keyx => $valuex) {
                    $this->db->delete('members_groups', array('group_id' => $valuex, 'PID' => $this->data['basicinfo']->PID));
                }


                foreach ($expl as $keyy => $valuey) {
                    //check
                    $check = $this->db->get_where('members_groups', array('group_id' => $valuey, 'PID' => $this->data['basicinfo']->PID))->result();
                    if (count($check) == 0) {

                        $gp = $this->member_model->member_group($valuey)->row();
                        $array_insert = array(
                            'group_id' => $valuey,
                            'GID' => $gp->GID,
                            'member_id' => $this->data['basicinfo']->member_id,
                            'PID' => $this->data['basicinfo']->PID,
                            'createdby' => $this->session->userdata('user_id'),
                            'PIN'=>  current_user()->PIN
                        );
                        $this->db->insert('members_groups', $array_insert);
                    }
                }
                $this->data['message'] = lang('member_contact_success');
            } else {
                //remove all
                $delete = $this->db->delete('members_groups', array('PID' => $this->data['basicinfo']->PID));
                if ($delete) {
                    $this->data['message'] = lang('member_contact_success');
                }
            }
        }




        $selected_group = $this->member_model->member_group_cross($this->data['basicinfo']->PID);

        $selected_group_array = array();
        foreach ($selected_group as $key => $value) {
            $selected_group_array[] = $value->group_id;
        }

        $this->data['selected_gp_array'] = $selected_group_array;
        $this->data['selected_gp'] = $selected_group;

        $this->data['content'] = 'member/member_group';
        $this->load->view('template', $this->data);
    }
    
    
    
     function member_current_state() {
        $this->load->library('pagination');
        $this->data['title'] = lang('member_current_state');

        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }


        if (isset($_GET['row_per_pg'])) {
            $this->session->set_userdata('PER_PAGE', $_GET['row_per_pg']);
        } else if (!$this->session->userdata('PER_PAGE')) {
            $this->session->set_userdata('PER_PAGE', 40);
        }

        $config["per_page"] = $this->session->userdata('PER_PAGE');

        $key = null;
        if (isset($_POST['key']) && $_POST['key'] != '') {
            $key = $_POST['key'];
        } else if (isset($_GET['key'])) {
            $key = $_GET['key'];
        }

        if (!is_null($key)) {
            $config['suffix'] = '?key=' . $key;
        }


        $config["base_url"] = site_url(current_lang() . '/member/member_current_state/');
        $config["total_rows"] = $this->member_model->count_member($key);
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

        $this->data['member_state'] = $this->member_model->search_member($key, '1','1',$config["per_page"], $page);


      

        $this->data['content'] = 'member/memberstate';
        $this->load->view('template', $this->data);
    }

    

}

?>
