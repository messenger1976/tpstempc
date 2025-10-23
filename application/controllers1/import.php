<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of import
 *
 * @author miltone
 */
class Import extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');
        $this->data['current_title'] = 'Data Migration';
        $this->load->library('loanbase');
        $this->load->model('finance_model');
        $this->load->model('member_model');
        $this->load->model('contribution_model');
        $this->load->model('setting_model');
        $this->load->model('customer_model');
        $this->load->model('loan_model');
        $this->load->model('share_model');
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

    function valid_phone($str) {
        $CI = & get_instance();
        if ($str != "") {
            $str = str_replace(' ', '', trim($str));
            if (preg_match("/^[0-9]{12}$/", $str)) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    function valid_date($date) {
        if ($date != "") {
            if (preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $date)) {
                $date_array = explode("-", $date);
                if (checkdate($date_array[1], $date_array[0], $date_array[2])) {
                    return TRUE;
                } else {
                    //    $CI->form_validation->set_message('valid_date', "The %s must contain DD-MM-YYYY");
                    return FALSE;
                }
            } else {
                //  $CI->form_validation->set_message('valid_date', "The %s must contain DD-MM-YYYY");
                return FALSE;
            }
        }
    }

    function import_member() {
        $this->data['title'] = 'Import Member';

        $this->form_validation->set_rules('upload', 'upload', '');
        $upload_photo = true;
        $file_name = '';
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "xls")) {
                $this->data['logo_error'] = 'Only xls extension is required.. Save your file as excel 97-2003';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads');

                $upload_photo = TRUE;
            }
        } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
            $this->data['logo_error'] = 'The file name is required';
            $upload_photo = FALSE;
        }



        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $this->load->library('excel');

            //change permission
            chmod("./uploads/" . $file_name, 0777);
            $objPHPExcel = IOFactory::load("./uploads/$file_name");
            $number_sheet = $objPHPExcel->getSheetCount();
            $report_array = array();
            $inserted_row = 0;
            if ($number_sheet == 1) {

                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $total_error = 0;
                    $imported = 0;
                    for ($i = 2; $i <= $highestRow; $i++) {

                        $cell = $worksheet->getCellByColumnAndRow(0, $i);
                        $member_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(1, $i);
                        $registration_fee = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(2, $i);
                        $fname = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(3, $i);
                        $lname = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(4, $i);
                        $othername = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(5, $i);
                        $gender = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(6, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $dob = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $dob = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(7, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $join_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $join_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(8, $i);
                        $mobile1 = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(9, $i);
                        $mobile12 = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(10, $i);
                        $email = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(11, $i);
                        $postal_address = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(12, $i);
                        $physical_address = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(13, $i);
                        $kin_name = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(14, $i);
                        $kin_relation = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(15, $i);
                        $kin_mobile = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(16, $i);
                        $kin_email = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(17, $i);
                        $kin_postal_address = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(18, $i);
                        $kin_physical_address = trim($cell->getValue());


                        $error_number = 0;
                        $er = '';
                        if (empty($member_no) || empty($fname) || empty($lname) || $registration_fee == '' || empty($gender) || empty($mobile1) || empty($kin_name) || empty($kin_relation) || empty($kin_mobile)) {
                            $er = 'One of the required column is empty';
                            $error_number++;
                        }

                        if ($dob != '' && !$this->valid_date($dob)) {
                            $er.= ', Invalid value in Birth of Date';
                            $error_number++;
                        }
                        if ($join_date != '' && !$this->valid_date($join_date)) {
                            $er.= ', Invalid value in Join Date';
                            $error_number++;
                        }

                        if ($join_date != '' && !$this->valid_date($join_date)) {
                            $er.= ', Invalid value in Join Date';
                            $error_number++;
                        }
                        if ($mobile1 != '' && !$this->valid_phone($mobile1)) {
                            $er.= ', Invalid Mobile number. User 255xxxxxxxxx';
                            $error_number++;
                        }
                        if ($mobile12 != '' && !$this->valid_phone($mobile12)) {
                            $er.= ', Invalid Mobile number. User 255xxxxxxxxx';
                            $error_number++;
                        }
                        if ($kin_mobile != '' && !$this->valid_phone($kin_mobile)) {
                            $er.= ', Invalid Mobile number. User 255xxxxxxxxx';
                            $error_number++;
                        }
                        if ($email != '' && !valid_email($email)) {
                            $er.= ', Invalid email address';
                            $error_number++;
                        }
                        if ($kin_email != '' && !valid_email($kin_email)) {
                            $er.= ', Invalid email address';
                            $error_number++;
                        }

                        $is_exist = $this->member_model->is_member_exist($member_no);

                        if ($is_exist == TRUE) {
                            $er.= ', Member Number already exist';
                            $error_number++;
                        }



                        if ($error_number == 0) {
                            $imported ++;
                            $new_member = array(
                                'member_id' => $member_no,
                                'firstname' => ucfirst(strtolower(trim($fname))),
                                'middlename' => ucfirst(strtolower(trim($othername))),
                                'lastname' => ucfirst(strtolower(trim($lname))),
                                'gender' => strtoupper(substr(trim($gender), 0, 1)),
                                'dob' => format_date(trim($dob)),
                                'joiningdate' => format_date(trim($join_date)),
                                'createdby' => $this->session->userdata('user_id'),
                                'PIN' => current_user()->PIN
                            );


                            $registrationfee = (double) trim($registration_fee);

                            //create member / Add member info
                            $return = $this->member_model->add_member($new_member, $registrationfee);
                            if ($return) {
// create account for that member
                                $username = $member_no;
                                $email11 = $member_no;
                                $password = alphaID($return, FALSE, 4);

                                // create account for login
                                $additional_data = array(
                                    'first_name' => $new_member['firstname'],
                                    'last_name' => $new_member['lastname'],
                                    'member_id' => $member_no,
                                    'oldpass' => $password,
                                    'MID' => $return,
                                    'PIN' => current_user()->PIN,
                                    'company' => company_info()->name,
                                );

                                $this->ion_auth->register($username, $password, $email11, $additional_data, array(3));

                                $member_info = $this->member_model->member_basic_info($return)->row();

                                // add contact for that member

                                $member_contact = array(
                                    'PID' => $member_info->PID,
                                    'phone1' => trim($mobile1),
                                    'phone2' => trim($mobile12),
                                    'email' => trim($email),
                                    'postaladdress' => trim($postal_address),
                                    'physicaladdress' => trim($this->input->post('physical')),
                                    'createdby' => current_user()->id,
                                    'PIN' => current_user()->PIN
                                );

                                $this->member_model->add_contact($member_contact, $return, $member_info->formstatus);

                                $member_info = $this->member_model->member_basic_info($return)->row();
                                // add next of kin

                                $member_nextkin = array(
                                    'PID' => $member_info->PID,
                                    'phone' => trim($kin_mobile),
                                    'email' => trim($kin_email),
                                    'relationship' => trim($kin_relation),
                                    'name' => ucfirst(strtolower(trim($kin_name))),
                                    'postaladdress' => trim($kin_postal_address),
                                    'physicaladdress' => trim($kin_physical_address),
                                    'createdby' => current_user()->id,
                                    'PIN' => current_user()->PIN
                                );
                                $this->member_model->add_nextkininfo($member_nextkin, $return, $member_info->formstatus);
                            }
                        } else {
                            $total_error++;
                            $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! ' . $er . '<br/></div>';
                        }
                    }


                    if ($total_error == 0) {
                        unlink("./uploads/$file_name");
                        $this->session->set_flashdata('message', $imported . ' row(s) migrated successfully !!');
                        redirect('import/import_member', 'refresh');
                    } else {
                        unlink("./uploads/$file_name");
                        $this->data['title'] = "Migrate Feedback";
                        $this->data['feedback'] = $report_array;
                        $this->data['imported'] = $imported;
                    }
                }
            } else {
                unlink("./uploads/$file_name");
                echo $this->data['warning'] = 'Your excel file has more than one worksheet. Please make sure the attached file has only one sheet';
            }
        }

        $this->data['content'] = 'migration/import_member';
        $this->load->view('template', $this->data);
    }

    function import_contribution() {
        $this->data['title'] = 'Import Contributions';

        $this->form_validation->set_rules('upload', 'upload', '');
        $upload_photo = true;
        $file_name = '';
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "xls")) {
                $this->data['logo_error'] = 'Only xls extension is required.. Save your file as excel 97-2003';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads');

                $upload_photo = TRUE;
            }
        } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
            $this->data['logo_error'] = 'The file name is required';
            $upload_photo = FALSE;
        }



        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $this->load->library('excel');

            //change permission
            chmod("./uploads/" . $file_name, 0777);
            $objPHPExcel = IOFactory::load("./uploads/$file_name");
            $number_sheet = $objPHPExcel->getSheetCount();
            $report_array = array();
            $inserted_row = 0;
            if ($number_sheet == 1) {

                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $total_error = 0;
                    $imported = 0;
                    for ($i = 2; $i <= $highestRow; $i++) {

                        $cell = $worksheet->getCellByColumnAndRow(0, $i);
                        $member_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(1, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(2, $i);
                        $amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(3, $i);
                        $pay_mode = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(4, $i);
                        $comment = trim($cell->getValue());


                        $error_number = 0;
                        $er = '';
                        if (empty($member_no) || empty($date) || $amount == '' || empty($pay_mode)) {
                            $er = 'One of the required column is empty';
                            $error_number++;
                        }

                        if (!$this->valid_date($date)) {
                            $er.= ', Invalid value in Payment Date';
                            $error_number++;
                        }

                        if (!is_numeric($amount)) {
                            $er.= ', Only numeric value is required in Amount column';
                            $error_number++;
                        }


                        $is_exist = $this->member_model->is_member_exist($member_no);

                        if ($is_exist == FALSE) {
                            $er.= ', Member Number not exist';
                            $error_number++;
                        }



                        if ($error_number == 0) {

                            $member_info = $this->member_model->member_basic_info(null, null, $member_no)->row();

                            $pid = $member_info->PID;
                            $member_id = $member_no;



                            $trans_type = 'CR';

                            $comment = 'Contribution';
                            $paymethod = $pay_mode;
                            $amount = $amount;
                            $customer_name = $member_info->firstname . ' ' . $member_info->lastname;
                            $continue = true;
                            $date = format_date($date);
                            $check_number_received = '';
                            if ($continue) {
                                //now finalize

                                $receipt = $this->contribution_model->contribution_transaction($trans_type, $pid, $member_id, $amount, $paymethod, $comment, $check_number_received, $month = '', $auto = 0, $date);
                                if ($receipt) {
                                    $imported ++;
                                } else {
                                    $total_error++;
                                    $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! <br/></div>';
                                }
                            }
                        } else {
                            $total_error++;
                            $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! ' . $er . '<br/></div>';
                        }
                    }

                    if ($total_error == 0) {
                        unlink("./uploads/$file_name");
                        $this->session->set_flashdata('message', $imported . ' row(s) migrated successfully !!');
                        redirect('import/import_contribution', 'refresh');
                    } else {
                        unlink("./uploads/$file_name");
                        $this->data['title'] = "Migrate Feedback";
                        $this->data['feedback'] = $report_array;
                        $this->data['imported'] = $imported;
                    }
                }
            } else {
                unlink("./uploads/$file_name");
                echo $this->data['warning'] = 'Your excel file has more than one worksheet. Please make sure the attached file has only one sheet';
            }
        }


        $this->data['content'] = 'migration/import_contribution';
        $this->load->view('template', $this->data);
    }

    //used in migrate share

    function is_max_share_reached($newshare, $previuous_share, $maxshare) {
        $temp = $newshare + $previuous_share;
        if ($temp > $maxshare) {
            return TRUE;
        }
        return FALSE;
    }

    function get_share_from_amount($dividend, $divisor) {
        $quotient = intval($dividend / $divisor);
        $remainder = $dividend % $divisor;
        return array($quotient, $remainder);
    }

    function import_share() {

        $this->data['title'] = 'Import Member Share';

        $this->form_validation->set_rules('upload', 'upload', '');
        $upload_photo = true;
        $file_name = '';
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "xls")) {
                $this->data['logo_error'] = 'Only xls extension is required.. Save your file as excel 97-2003';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads');

                $upload_photo = TRUE;
            }
        } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
            $this->data['logo_error'] = 'The file name is required';
            $upload_photo = FALSE;
        }



        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $this->load->library('excel');

            //change permission
            chmod("./uploads/" . $file_name, 0777);
            $objPHPExcel = IOFactory::load("./uploads/$file_name");
            $number_sheet = $objPHPExcel->getSheetCount();
            $report_array = array();
            $inserted_row = 0;
            if ($number_sheet == 1) {

                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $total_error = 0;
                    $imported = 0;
                    for ($i = 2; $i <= $highestRow; $i++) {

                        $cell = $worksheet->getCellByColumnAndRow(0, $i);
                        $member_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(1, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(2, $i);
                        $amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(3, $i);
                        $pay_mode = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(4, $i);
                        $comment = trim($cell->getValue());


                        $error_number = 0;
                        $er = '';
                        if (empty($member_no) || empty($date) || $amount == '' || empty($pay_mode)) {
                            $er = 'One of the required column is empty';
                            $error_number++;
                        }

                        if (!$this->valid_date($date)) {
                            $er.= ', Invalid value in Payment Date';
                            $error_number++;
                        }

                        if (!is_numeric($amount)) {
                            $er.= ', Only numeric value is required in Amount column';
                            $error_number++;
                        }


                        $is_exist = $this->member_model->is_member_exist($member_no);

                        if ($is_exist == FALSE) {
                            $er.= ', Member Number not exist';
                            $error_number++;
                        }



                        if ($error_number == 0) {

                            $member_info = $this->member_model->member_basic_info(null, null, $member_no)->row();

                            $pid = $member_info->PID;
                            $member_id = $member_no;
                            $amount = $amount;
                            $real_amount = $amount;
                            $comment = 'Buy Share';
                            $paymethod = $pay_mode;
                            $check_number_received = '';
                            $date = format_date($date);
                            $share_info = $this->share_model->share_member_info($pid, $member_id);
                            $share_setup = $this->setting_model->share_setting_info();
                            $cost_per_share = $share_setup->amount;

                            if ($share_info) {
                                //share row exist
                                $amount = $amount + $share_info->remainbalance;
                                $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                                $totalshare = $share_item[0];
                                $remaining_amount = $share_item[1];
                                $share_number = 0;
                                $check_number_received = '';
                                $is_max_share_reached = $this->is_max_share_reached($totalshare, $share_info->totalshare, $share_setup->max_share);
                                if (!$is_max_share_reached) {
                                    //safe to add share
                                    $share_number = $totalshare;
                                    $amountshare = ($totalshare * $share_setup->amount);
                                    $remain_amount = $remaining_amount;
                                    $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received, $date);
                                    if ($add_share) {
                                        $imported++;
                                    } else {
                                        $total_error++;
                                        $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! <br/></div>';
                                    }
                                }
                            } else {
                                // share row not exist

                                $share_item = $this->get_share_from_amount($amount, $share_setup->amount);
                                $totalshare = $share_item[0];
                                $remaining_amount = $share_item[1];
                                $share_number = 0;
                                $is_max_share_reached = $this->is_max_share_reached($totalshare, 0, $share_setup->max_share);
                                if (!$is_max_share_reached) {
                                    //safe to add share
                                    $share_number = $totalshare;
                                    $amountshare = ($totalshare * $share_setup->amount);
                                    $remain_amount = $remaining_amount;
                                    $add_share = $this->share_model->add_share($pid, $member_id, $paymethod, $cost_per_share, $share_number, $amountshare, $remain_amount, $real_amount, $comment, $check_number_received, $date);

                                    if ($add_share) {
                                        $imported++;
                                    } else {
                                        $total_error++;
                                        $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! <br/></div>';
                                    }
                                }
                            }
                        } else {
                            $total_error++;
                            $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! ' . $er . '<br/></div>';
                        }
                    }

                    if ($total_error == 0) {
                        unlink("./uploads/$file_name");
                        $this->session->set_flashdata('message', $imported . ' row(s) migrated successfully !!');
                        redirect('import/import_share', 'refresh');
                    } else {
                        unlink("./uploads/$file_name");
                        $this->data['title'] = "Migrate Feedback";
                        $this->data['feedback'] = $report_array;
                        $this->data['imported'] = $imported;
                    }
                }
            } else {
                unlink("./uploads/$file_name");
                echo $this->data['warning'] = 'Your excel file has more than one worksheet. Please make sure the attached file has only one sheet';
            }
        }



        $this->data['content'] = 'migration/import_share';
        $this->load->view('template', $this->data);
    }

    function import_loan() {
        $this->data['title'] = 'Import Loan';

        $this->form_validation->set_rules('upload', 'upload', '');
        $upload_photo = true;
        $file_name = '';
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "xls")) {
                $this->data['logo_error'] = 'Only xls extension is required.. Save your file as excel 97-2003';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads');

                $upload_photo = TRUE;
            }
        } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
            $this->data['logo_error'] = 'The file name is required';
            $upload_photo = FALSE;
        }



        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $this->load->library('excel');

            //change permission
            chmod("./uploads/" . $file_name, 0777);
            $objPHPExcel = IOFactory::load("./uploads/$file_name");
            $number_sheet = $objPHPExcel->getSheetCount();
            $report_array = array();
            $inserted_row = 0;
            if ($number_sheet == 1) {

                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $total_error = 0;
                    $imported = 0;
                    for ($i = 2; $i <= $highestRow; $i++) {



                        $cell = $worksheet->getCellByColumnAndRow(0, $i);
                        $loan_id = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(1, $i);
                        $member_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(2, $i);
                        $product_id = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(3, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $application_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $application_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(4, $i);
                        $base_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(5, $i);
                        $install_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(6, $i);
                        $install_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(7, $i);
                        $total_interest_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(8, $i);
                        $total_loan_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(9, $i);
                        $repay_source = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(10, $i);
                        $monthly_income = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(11, $i);
                        $loan_purpose = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(12, $i);
                        $evaluated_status = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(13, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $evaluated_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $evaluated_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(14, $i);
                        $evaluated_comment = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(15, $i);
                        $approval_status = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(16, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $approval_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $approval_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(17, $i);
                        $approval_comment = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(18, $i);
                        $disbursed_status = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(19, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $disbursed_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $disbursed_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(20, $i);
                        $disbursed_comment = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(21, $i);
                        $loan_status = trim($cell->getValue());


                        $error_number = 0;
                        $er = '';
                        if ($loan_id == '' || $member_no == '' || $product_id == '' || $application_date == '' || $base_amount == '' ||
                                $install_no == '' || $install_amount == '' || $total_interest_amount == '' ||
                                $total_loan_amount == '' || $repay_source == '' || $monthly_income == '' || $evaluated_status == '' || $approval_status == '' || $disbursed_status == '' || $loan_status == '') {
                            $er = 'One of the required column is empty';
                            $error_number++;
                        }

                        if ($application_date != '' && !$this->valid_date($application_date)) {
                            $er.= ', Invalid value in Application  Date';
                            $error_number++;
                        }

                        if (!is_numeric($product_id) || !is_numeric($evaluated_status) || !is_numeric($approval_status) || !is_numeric($disbursed_status) || !is_numeric($loan_status)) {
                            $er.= ',Only numeric required in Evaluated,Approval,Disbirsed and Status columns';
                            $error_number++;
                        }

                        if ($evaluated_status != 0 && !$this->valid_date($evaluated_date)) {
                            $er.= ', Invalid value in Evaluated Date column';
                            $error_number++;
                        }
                        if ($approval_status != 0 && !$this->valid_date($approval_date)) {
                            $er.= ', Invalid value in Approval Date column';
                            $error_number++;
                        }
                        if ($disbursed_status != 0 && !$this->valid_date($disbursed_date)) {
                            $er.= ', Invalid value in Disbursed Date column';
                            $error_number++;
                        }



                        $is_exist = $this->member_model->is_member_exist($member_no);

                        if ($is_exist == false) {
                            $er.= ', Member Number does not exist';
                            $error_number++;
                        }

                        $is_exist1 = $this->loan_model->is_loan_exist($loan_id);

                        if ($is_exist1 == TRUE) {
                            $er.= ', Loan Number already exist';
                            $error_number++;
                        }

                        $is_exist12 = $this->loan_model->is_loan_product_exist($product_id);

                        if ($is_exist12 == FALSE) {
                            $er.= ', Loan Product does not exist';
                            $error_number++;
                        }



                        if ($error_number == 0) {

                            $pin = current_user()->PIN;

                            $product = $this->setting_model->loanproduct($product_id)->row();
                            $member_info = $this->member_model->member_basic_info(null, null, $member_no)->row();

                            $createloan = array(
                                'LID' => trim($loan_id),
                                'PID' => trim($member_info->PID),
                                'member_id' => trim($member_no),
                                'product_type' => trim($product_id),
                                'rate' => $product->interest_rate,
                                'interval' => $product->interval,
                                'basic_amount' => trim($base_amount),
                                'number_istallment' => trim($install_no),
                                'pay_source' => trim(strtoupper($repay_source)),
                                'applicationdate' => format_date(trim($application_date)),
                                'loan_purpose' => trim($loan_purpose),
                                'monthly_income' => trim($monthly_income),
                                'installment_amount' => trim($install_amount),
                                'total_interest_amount' => trim($total_interest_amount),
                                'total_loan' => trim($total_loan_amount),
                                'createdby' => current_user()->id,
                                'createdon' => format_date(trim($application_date)),
                                'PIN' => $pin,
                                'edit' => 0,
                                'evaluated' => $evaluated_status,
                                'approval' => $approval_status,
                                'disburse' => $disbursed_status,
                                'status' => $loan_status,
                            );
                            $insert = $this->db->insert('loan_contract', $createloan);
                            if ($insert) {
                                if ($evaluated_status == 1) {
                                    //add evaluation comment
                                    $evaluation_array = array(
                                        'LID' => $loan_id,
                                        'status' => $evaluated_status,
                                        'comment' => $evaluated_comment,
                                        'createdby' => current_user()->id,
                                        'createdon' => format_date($evaluated_date),
                                        'PIN' => $pin,
                                    );
                                    $this->db->insert('loan_contract_evaluation', $evaluation_array);
                                }

                                if ($approval_status == 4) {

                                    $this->db->update('loan_contract', array('edit' => 1), array('LID' => $loan_id));

                                    //add evaluation comment
                                    $approve_array = array(
                                        'LID' => $loan_id,
                                        'status' => $approval_status,
                                        'comment' => $approval_comment,
                                        'createdby' => current_user()->id,
                                        'createdon' => format_date($approval_date),
                                        'PIN' => $pin,
                                    );
                                    $this->db->insert('loan_contract_approve', $approve_array);
                                }

                                if ($disbursed_status == 1) {
                                    //add evaluation comment
                                    $disbursed_array = array(
                                        'LID' => $loan_id,
                                        'disbursedate' => format_date(trim($disbursed_date)),
                                        'comment' => trim($disbursed_comment),
                                        'createdby' => current_user()->id,
                                        'createdon' => format_date(trim($disbursed_date)),
                                        'PIN' => $pin,
                                    );

                                    $this->db->trans_start();
                                    $this->db->insert('loan_contract_disburse', $disbursed_array);



                                    //bank account
                                    $credit_account = 1000001;
                                    //ledger entry ID
                                    $ledger_entry = array('date' => $disbursed_array['disbursedate'], 'PIN' => $pin);
                                    $this->db->insert('general_ledger_entry', $ledger_entry);
                                    $ledger_entry_id = $this->db->insert_id();

                                    //ledger data
                                    $ledger = array(
                                        'journalID' => 4,
                                        'entryid' => $ledger_entry_id,
                                        'LID' => $loan_id,
                                        'date' => $disbursed_array['disbursedate'],
                                        'description' => 'Loan Disbursed',
                                        'linkto' => 'loan_contract.LID',
                                        'fromtable' => 'loan_contract',
                                        'paid' => 0,
                                        'PID' => $member_info->PID,
                                        'member_id' => $member_no,
                                        'PIN' => $pin,
                                    );

                                    $ledger['account'] = $credit_account;
                                    $ledger['credit'] = $base_amount;
                                    $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                                    $this->db->insert('general_ledger', $ledger);

                                    $ledger['credit'] = 0;
                                    $ledger['debit'] = 0;

                                    //debit account
                                    $debit_account = $product->loan_principle_account;
                                    $ledger['debit'] = $base_amount;
                                    $ledger['account'] = $debit_account;
                                    $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                                    $this->db->insert('general_ledger', $ledger);
                                    $this->db->trans_complete();
                                }
                                $imported ++;
                            } else {
                                $total_error++;
                                $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !!<br/></div>';
                            }
                        } else {
                            $total_error++;
                            $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! ' . $er . '<br/></div>';
                        }
                    }


                    if ($total_error == 0) {
                        unlink("./uploads/$file_name");
                        $this->session->set_flashdata('message', $imported . ' row(s) migrated successfully !!');
                        redirect('import/import_loan', 'refresh');
                    } else {
                        unlink("./uploads/$file_name");
                        $this->data['title'] = "Migrate Feedback";
                        $this->data['feedback'] = $report_array;
                        $this->data['imported'] = $imported;
                    }
                }
            } else {
                unlink("./uploads/$file_name");
                echo $this->data['warning'] = 'Your excel file has more than one worksheet. Please make sure the attached file has only one sheet';
            }
        }


        $this->data['content'] = 'migration/import_loan';
        $this->load->view('template', $this->data);
    }

    function import_repayment() {

        $this->data['title'] = 'Import Loan Repayment Schedule';

        $this->form_validation->set_rules('upload', 'upload', '');
        $upload_photo = true;
        $file_name = '';
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "xls")) {
                $this->data['logo_error'] = 'Only xls extension is required.. Save your file as excel 97-2003';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads');

                $upload_photo = TRUE;
            }
        } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
            $this->data['logo_error'] = 'The file name is required';
            $upload_photo = FALSE;
        }



        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $this->load->library('excel');

            //change permission
            chmod("./uploads/" . $file_name, 0777);
            $objPHPExcel = IOFactory::load("./uploads/$file_name");
            $number_sheet = $objPHPExcel->getSheetCount();
            $report_array = array();
            $inserted_row = 0;
            if ($number_sheet == 1) {

                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $total_error = 0;
                    $imported = 0;
                    for ($i = 2; $i <= $highestRow; $i++) {



                        $cell = $worksheet->getCellByColumnAndRow(0, $i);
                        $loan_id = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(1, $i);
                        $install_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(2, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $repay_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $repay_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(3, $i);
                        $repay_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(4, $i);
                        $interest_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(5, $i);
                        $principal_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(6, $i);
                        $balance_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(7, $i);
                        $repay_status = trim($cell->getValue());



                        $error_number = 0;
                        $er = '';
                        if ($loan_id == '' || $install_no == '' || $repay_date == '' || $repay_amount == '' || $principal_amount == '' ||
                                $balance_amount == '' || $repay_status == '') {
                            $er = 'One of the required column is empty';
                            $error_number++;
                        }

                        if ($repay_date != '' && !$this->valid_date($repay_date)) {
                            $er.= ', Invalid value in Repay  Date';
                            $error_number++;
                        }

                        if (!is_numeric($repay_amount) || !is_numeric($interest_amount) || !is_numeric($principal_amount) || !is_numeric($balance_amount) || !is_numeric($repay_status)) {
                            $er.= ',Only numeric required in Repay Amount,Iterest,Principal and Loan Balance columns';
                            $error_number++;
                        }


                        $is_exist1 = $this->loan_model->is_loan_exist($loan_id);

                        if ($is_exist1 == false) {
                            $er.= ', Loan Number does not  exist';
                            $error_number++;
                        }




                        if ($error_number == 0) {

                            $pin = current_user()->PIN;

                            $createloan = array(
                                'LID' => trim($loan_id),
                                'installment_number' => trim($install_no),
                                'repaydate' => format_date(trim($repay_date)),
                                'repayamount' => trim($repay_amount),
                                'interest' => trim($interest_amount),
                                'principle' => trim($principal_amount),
                                'balance' => trim($balance_amount),
                                'status' => trim($repay_status),
                                'PIN' => $pin,
                                'month' => date("Ym", strtotime($repay_date)),
                            );
                            $insert = $this->db->insert('loan_contract_repayment_schedule', $createloan);
                            if ($insert) {
                                $imported ++;
                            } else {
                                $total_error++;
                                $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !!<br/></div>';
                            }
                        } else {
                            $total_error++;
                            $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! ' . $er . '<br/></div>';
                        }
                    }


                    if ($total_error == 0) {
                        unlink("./uploads/$file_name");
                        $this->session->set_flashdata('message', $imported . ' row(s) migrated successfully !!');
                        redirect('import/import_repayment', 'refresh');
                    } else {
                        unlink("./uploads/$file_name");
                        $this->data['title'] = "Migrate Feedback";
                        $this->data['feedback'] = $report_array;
                        $this->data['imported'] = $imported;
                    }
                }
            } else {
                unlink("./uploads/$file_name");
                echo $this->data['warning'] = 'Your excel file has more than one worksheet. Please make sure the attached file has only one sheet';
            }
        }


        $this->data['content'] = 'migration/import_repayment';
        $this->load->view('template', $this->data);
    }

    function import_repay_trans() {

        $this->data['title'] = 'Import Loan Repayment Transactions';

        $this->form_validation->set_rules('upload', 'upload', '');
        $upload_photo = true;
        $file_name = '';
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            $extension = $this->getExtension($_FILES['file']['name']);
            if (($extension != "xls")) {
                $this->data['logo_error'] = 'Only xls extension is required.. Save your file as excel 97-2003';
                $upload_photo = FALSE;
            } else {
                $file_name = $this->upload_file($_FILES, 'file', 'uploads');

                $upload_photo = TRUE;
            }
        } else if (isset($_FILES['file']['name']) && $_FILES['file']['name'] == '') {
            $this->data['logo_error'] = 'The file name is required';
            $upload_photo = FALSE;
        }



        if ($this->form_validation->run() == TRUE && $upload_photo == TRUE) {
            $this->load->library('excel');

            //change permission
            chmod("./uploads/" . $file_name, 0777);
            $objPHPExcel = IOFactory::load("./uploads/$file_name");
            $number_sheet = $objPHPExcel->getSheetCount();
            $report_array = array();
            $inserted_row = 0;
            if ($number_sheet == 1) {

                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $highestRow = $worksheet->getHighestRow();
                    $total_error = 0;
                    $imported = 0;
                    for ($i = 2; $i <= $highestRow; $i++) {



                        $cell = $worksheet->getCellByColumnAndRow(0, $i);
                        $loan_id = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(1, $i);
                        $install_no = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(2, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $due_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $due_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(3, $i);
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $paid_date = PHPExcel_Style_NumberFormat::toFormattedString($cell->getValue(), "DD-MM-YYYY");
                        } else {
                            $paid_date = trim($cell->getValue());
                        }

                        $cell = $worksheet->getCellByColumnAndRow(4, $i);
                        $paid_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(5, $i);
                        $interest_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(6, $i);
                        $penalt_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(7, $i);
                        $principal_amount = trim($cell->getValue());

                        $cell = $worksheet->getCellByColumnAndRow(8, $i);
                        $loan_balance = trim($cell->getValue());



                        $error_number = 0;
                        $er = '';
                        if ($loan_id == '' || $install_no == '' || $due_date == '' || $paid_date == '' || $paid_amount == '' ||
                                $interest_amount == '' || $principal_amount == '' || $penalt_amount == '' || $loan_balance == '') {
                            $er = 'One of the required column is empty';
                            $error_number++;
                        }

                        if ($paid_date != '' && !$this->valid_date($paid_date)) {
                            $er.= ', Invalid value in Paid  Date';
                            $error_number++;
                        }
                        if ($due_date != '' && !$this->valid_date($due_date)) {
                            $er.= ', Invalid value in Due  Date';
                            $error_number++;
                        }

                        if (!is_numeric($paid_amount) || !is_numeric($interest_amount) || !is_numeric($principal_amount) || !is_numeric($loan_balance) || !is_numeric($penalt_amount)) {
                            $er.= ',Only numeric required in Paid Amount,Interest,Principal,Penalty and Loan Balance columns';
                            $error_number++;
                        }


                        $is_exist1 = $this->loan_model->is_loan_exist($loan_id);

                        if ($is_exist1 == false) {
                            $er.= ', Loan Number does not  exist';
                            $error_number++;
                        }




                        if ($error_number == 0) {

                            $pin = current_user()->PIN;

                            $receipt = $this->loan_model->receiptNo();

                            $this->db->trans_start();
                            $array = array(
                                'LID' => trim($loan_id),
                                'receipt' => $receipt,
                                'installment' => $install_no,
                                'amount' => trim($paid_amount),
                                'paydate' => format_date(trim($paid_date)),
                                'createdby' => current_user()->id,
                                'PIN' => $pin,
                                'createdon' => format_date(trim($paid_date)),
                            );

                            $this->db->insert('loan_repayment_receipt', $array);

                            $array_data = array(
                                'LID' => $loan_id,
                                'receipt' => $receipt,
                                'installment' => $install_no,
                                'amount' => $paid_amount,
                                'paydate' => format_date($paid_date),
                                'interest' => trim($interest_amount),
                                'principle' => trim($principal_amount),
                                'duedate' => format_date($due_date),
                                'createdon' => format_date($paid_date),
                                'balance' => trim($loan_balance),
                                'iliyobaki' => 0,
                                'createdby' => current_user()->id,
                                'PIN' => $pin,
                            );



                            $insert = $this->db->insert('loan_contract_repayment', $array_data);
                            $referenceID = $this->db->insert_id();
                            //general entry id
                            $ledger_entry = array('date' => $array_data['paydate'], 'PIN' => $pin);
                            $this->db->insert('general_ledger_entry', $ledger_entry);
                            $ledger_entry_id = $this->db->insert_id();

                            $LID = $array_data['LID'];
                            $infodata = $this->loan_model->loan_info($LID)->row();
                            $product = $this->setting_model->loanproduct($infodata->product_type)->row();
                            //prepare to enter ledger
                            //ledger data
                            $ledger = array(
                                'journalID' => 4,
                                'refferenceID' => $referenceID,
                                'entryid' => $ledger_entry_id,
                                'LID' => $LID,
                                'date' => $array_data['paydate'],
                                'description' => 'Loan Repayment',
                                'linkto' => 'loan_contract_repayment.id',
                                'fromtable' => 'loan_contract_repayment',
                                'paid' => 0,
                                'PIN' => $pin,
                                'PID' => $infodata->PID,
                                'member_id' => $infodata->member_id,
                            );

                            //bank account
                            $debit_account = 1000001;
                            $ledger['account'] = $debit_account;
                            $ledger['debit'] = $array_data['principle'];
                            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                            $this->db->insert('general_ledger', $ledger);


                            $ledger['credit'] = 0;
                            $ledger['debit'] = 0;
                            $ledger['account'] = $product->loan_principle_account;
                            $ledger['credit'] = $array_data['principle'];
                            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                            $this->db->insert('general_ledger', $ledger);

                            //interest
                            //debit account
                            //bank account
                            $ledger['credit'] = 0;
                            $ledger['debit'] = 0;
                            $debit_account = 1000001;
                            $ledger['account'] = $debit_account;
                            $ledger['debit'] = $array_data['interest'];
                            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                            $this->db->insert('general_ledger', $ledger);

                            //credit Income account
                            $ledger['credit'] = 0;
                            $ledger['debit'] = 0;
                            $ledger['account'] = $product->loan_interest_account;
                            $ledger['credit'] = $array_data['interest'];
                            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                            $this->db->insert('general_ledger', $ledger);


                            //credit equity
                            $ledger['credit'] = 0;
                            $ledger['debit'] = 0;
                            $ledger['account'] = 3000002;
                            $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                            $ledger['credit'] = $array_data['interest'];
                            $this->db->insert('general_ledger', $ledger);


                            //check if penalty exist
                            if (array_key_exists('penalt', $array_data)) {
                                $ledger['credit'] = 0;
                                $ledger['debit'] = 0;
                                $debit_account = 1000001;
                                $ledger['account'] = $debit_account;
                                $ledger['debit'] = $array_data['penalt'];
                                $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                                $this->db->insert('general_ledger', $ledger);

                                //credit Income account
                                $ledger['credit'] = 0;
                                $ledger['debit'] = 0;
                                $ledger['account'] = $product->loan_penalt_account;
                                $ledger['credit'] = $array_data['penalt'];
                                $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                                $this->db->insert('general_ledger', $ledger);


                                //credit equity
                                $ledger['credit'] = 0;
                                $ledger['debit'] = 0;
                                $ledger['account'] = 3000002;
                                $ledger['account_type'] = account_row_info($ledger['account'])->account_type;
                                $ledger['credit'] = $array_data['penalt'];
                                $this->db->insert('general_ledger', $ledger);
                            }


                            //$this->db->update('loan_contract_repayment_schedule', array('status' => 1), array('id' => $repay_schedule_ref));
                            $this->db->update('loan_repayment_receipt', array('affect_loan' => 1, 'installment' => $array_data['installment']), array('receipt' => $array_data['receipt']));
                            $this->db->trans_complete();


                            $imported ++;
                        } else {
                            $total_error++;
                            $report_array[] = '<div style="color:red;"> ==> Row No ' . $i . ' not imported !! ' . $er . '<br/></div>';
                        }
                    }


                    if ($total_error == 0) {
                        unlink("./uploads/$file_name");
                        $this->session->set_flashdata('message', $imported . ' row(s) migrated successfully !!');
                        redirect('import/import_repay_trans', 'refresh');
                    } else {
                        unlink("./uploads/$file_name");
                        $this->data['title'] = "Migrate Feedback";
                        $this->data['feedback'] = $report_array;
                        $this->data['imported'] = $imported;
                    }
                }
            } else {
                unlink("./uploads/$file_name");
                echo $this->data['warning'] = 'Your excel file has more than one worksheet. Please make sure the attached file has only one sheet';
            }
        }


        $this->data['content'] = 'migration/import_repayment_trans';
        $this->load->view('template', $this->data);
    }

}
//ALTER TABLE  `loan_repayment_receipt` CHANGE  `createdon`  `createdon` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
