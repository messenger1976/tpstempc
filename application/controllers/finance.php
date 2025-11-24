<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of finance
 *
 * @author miltone
 */
class Finance extends CI_Controller {

    //put your code here


    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->form_validation->set_error_delimiters('<div class="error_message">', '</div>');

        $this->data['current_title'] = lang('page_finance');
        $this->lang->load('setting');
        $this->lang->load('finance');
        $this->load->model('member_model');
        $this->load->model('finance_model');
        $this->load->model('setting_model');
    }

    function index() {
        
    }

    function finance_account_create($parent_account = null) {

        $this->data['parent'] = $parent_account;
        $this->data['title'] = lang('finance_account_create');
        if (!is_null($parent_account)) {
            $this->data['parent_info'] = $this->finance_model->account_chart(null, $parent_account)->row();
        }
        $this->form_validation->set_rules('accountcode', lang('finance_account_code'), 'required');
        $this->form_validation->set_rules('account_type', lang('member_group_description'), 'required');
        $this->form_validation->set_rules('accountname', lang('finance_account_name'), 'required');
        $this->form_validation->set_rules('accountdescription', lang('finance_account_description'), '');
      

        if ($this->form_validation->run() == TRUE) {

            /*$parent_acc = '';
            $parent_path = '';
            if (!is_null($parent_account)) {
                $parent_account_info = $this->finance_model->account_chart(null, $parent_account)->row();
                $parent_acc = $parent_account_info->account;
                $parent_path = $parent_account_info->path . '/' . $parent_acc;
            }
            $is_header = 0;
            if ($this->input->post('is_header')) {
                $is_header = 1;
            }
            */
            
            $tmp = $this->input->post('account_type');
            $name = $this->input->post('accountname');
            $accountcode = $this->input->post('accountcode');
            $description = $this->input->post('accountdescription');
            $tmp1 = explode('_', $tmp);
            $accounttype = $tmp1[0];
            $accounttype_sub = $tmp1[1];
            
            
            $create_account = array(
                'account_type' => $accounttype,
                'sub_account_type' => $accounttype_sub,
                'name' => trim($name),
                'account' => $accountcode,
                'description' => trim($description),
                'createdby' => current_user()->id,
                'PIN' =>  current_user()->PIN
            );
            $return = $this->finance_model->create_chart_account($create_account);
            if ($return) {
                $this->session->set_flashdata('message', lang('finance_account_create_success'));
                redirect(current_lang() . '/finance/finance_account_create/' . $parent_account, 'refresh');
            } else {
                $this->data['warning'] = lang('finance_account_create_fail');
            }
        }

        $this->data['account_typelist'] = $this->finance_model->account_typelist()->result();

        $this->data['content'] = 'finance/create_account';
        $this->load->view('template', $this->data);
    }

    function finance_account_edit($id) {
        $this->data['id'] = $id;
        $id = decode_id($id);
        $accountinfo = $this->finance_model->account_chart($id, null)->row();

        $this->data['parent'] = null;

        $this->data['title'] = lang('finance_account_edit');
        if ($accountinfo->account_parent != 0) {
            $this->data['parent_info'] = $this->finance_model->account_chart(null, $accountinfo->account_parent)->row();
        }
        $this->form_validation->set_rules('accountcode', lang('finance_account_code'), 'required');
        $this->form_validation->set_rules('account_type', lang('member_group_description'), 'required');
        $this->form_validation->set_rules('accountname', lang('finance_account_name'), 'required');
        $this->form_validation->set_rules('accountdescription', lang('finance_account_description'), '');
        $this->form_validation->set_rules('is_header', lang('finance_account_is_header'), '');


        if ($this->form_validation->run() == TRUE) {

            
             $tmp = $this->input->post('account_type');
            $name = $this->input->post('accountname');
            $accountcode = $this->input->post('accountcode');
            $description = $this->input->post('accountdescription');
            $tmp1 = explode('_', $tmp);
            $accounttype = $tmp1[0];
            $accounttype_sub = $tmp1[1];
            
            
            $create_account = array(
                'account_type' => $accounttype,
                'sub_account_type' => $accounttype_sub,
                'account' => trim($accountcode),
                'name' => trim($name),
                'description' => trim($description),
                'PIN' =>  current_user()->PIN
            );
           
            $return = $this->finance_model->edit_chart_account($create_account, $id);
            if ($return) {
                $this->session->set_flashdata('message', lang('finance_account_create_success'));
                redirect(current_lang() . '/finance/finance_account_edit/' . $this->data['id'], 'refresh');
            } else {
                $this->data['warning'] = lang('finance_account_create_fail');
            }
        }

        $this->data['accountinfo'] = $this->finance_model->account_chart($id, null)->row();
        $this->data['account_typelist'] = $this->finance_model->account_typelist()->result();
        $this->data['content'] = 'finance/edit_account_chart';
        $this->load->view('template', $this->data);
    }

    function finance_account_list() {
        $this->data['title'] = lang('finance_account_list');
        $account_chart = $this->finance_model->account_chart()->result();
        
        // Sort by account field (ASC)
        usort($account_chart, function($a, $b) {
            return (int)$a->account - (int)$b->account;
        });
        
        $this->data['account_chart'] = $account_chart;
        $this->data['content'] = 'finance/account_chart_list';
        $this->load->view('template', $this->data);
    }

    function finance_account_list_print() {
        $this->data['title'] = 'Chart of Accounts';
        // Get accounts organized by account type for ladder display
        $account_chart_by_type = $this->finance_model->account_chart_by_accounttype();
        
        // Sort account types by account code (ASC)
        uasort($account_chart_by_type, function($a, $b) {
            $account_a = isset($a['info']->account) ? (int)$a['info']->account : 0;
            $account_b = isset($b['info']->account) ? (int)$b['info']->account : 0;
            return $account_a - $account_b;
        });
        
        // Sort accounts within each type by account field (ASC)
        foreach ($account_chart_by_type as $type_id => $type_data) {
            if (isset($type_data['data']) && is_array($type_data['data'])) {
                usort($type_data['data'], function($a, $b) {
                    return (int)$a->account - (int)$b->account;
                });
                $account_chart_by_type[$type_id]['data'] = $type_data['data'];
            }
        }
        
        $this->data['account_chart_by_type'] = $account_chart_by_type;
        
        // Sort the flat list by account field (ASC)
        $account_chart = $this->finance_model->account_chart()->result();
        usort($account_chart, function($a, $b) {
            return (int)$a->account - (int)$b->account;
        });
        $this->data['account_chart'] = $account_chart;
        
        $this->load->view('finance/print/account_chart_list_print', $this->data);
    }

    function finance_account_list_export() {
        // Clear ALL output buffers first
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Disable CodeIgniter's output completely
        $this->output->enable_profiler(FALSE);
        // Prevent CodeIgniter from sending output
        $this->output->set_output('');
        
        // Load Excel library
        $this->load->library('excel');
        
        // Get account chart data - same as list function
        $account_chart = $this->finance_model->account_chart()->result();
        
        // Check if we have data
        if (empty($account_chart) || !is_array($account_chart) || count($account_chart) == 0) {
            // Clear buffers before redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            $this->session->set_flashdata('warning', 'No data available to export');
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            exit();
        }
        
        // Sort by account field (ASC)
        usort($account_chart, function($a, $b) {
            return (int)$a->account - (int)$b->account;
        });
        
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        
        // Set document properties
        $objPHPExcel->getProperties()->setCreator(company_info()->name)
                                     ->setTitle("Chart of Accounts")
                                     ->setSubject("Chart of Accounts Export")
                                     ->setDescription("Chart of Accounts exported from " . company_info()->name);
        
        // Set active sheet index to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        
        // Set sheet title
        $sheet->setTitle('Chart of Accounts');
        
        // Set column headers
        $sheet->setCellValue('A1', lang('sno'));
        $sheet->setCellValue('B1', lang('account_no'));
        $sheet->setCellValue('C1', lang('finance_account_type'));
        $sheet->setCellValue('D1', lang('finance_account_name'));
        $sheet->setCellValue('E1', lang('finance_account_description'));
        
        // Style the header row
        $headerStyle = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FFFFFF'),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '4472C4')
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
        );
        
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(40);
        
        // Populate data
        $row = 2;
        $i = 1;
        foreach ($account_chart as $account) {
            // Get account type name - exactly as in view
            $account_type_name = '';
            if (isset($account->account_type)) {
                $account_type_result = $this->finance_model->account_type(null, $account->account_type);
                if ($account_type_result && $account_type_result->num_rows() > 0) {
                    $account_type = $account_type_result->row();
                    $account_type_name = isset($account_type->name) ? $account_type->name : '';
                }
            }
            
            // Write data to cells
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $account->account);
            $sheet->setCellValue('C' . $row, $account_type_name);
            $sheet->setCellValue('D' . $row, $account->name);
            $sheet->setCellValue('E' . $row, $account->description);
            
            // Add borders to cells
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ));
            
            $row++;
        }
        
        // Set filename
        $filename = 'Chart_of_Accounts_' . date('Y-m-d_His') . '.xls';
        
        // Clear any remaining output buffers before sending headers
        if (ob_get_level()) {
            ob_end_clean();
        }
        while (@ob_end_clean());
        
        // Set headers - MUST be before any output
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');
        
        // Create writer and output directly
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        
        // Exit immediately to prevent any further output
        exit();
    }

    function finance_account_delete($id) {
        $id = decode_id($id);
        
        // Check if account exists and belongs to current user's PIN
        $account_info = $this->finance_model->account_chart($id, null)->row();
        
        if (!$account_info) {
            $this->session->set_flashdata('warning', lang('finance_account_not_found'));
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            return;
        }
        
        if ($account_info->PIN != current_user()->PIN) {
            $this->session->set_flashdata('warning', lang('finance_account_not_found'));
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            return;
        }
        
        // Check if account has transactions
        $has_transactions = $this->finance_model->check_account_has_transactions($account_info->account);
        
        if ($has_transactions) {
            $this->session->set_flashdata('warning', lang('finance_account_has_transactions'));
            redirect(current_lang() . '/finance/finance_account_list', 'refresh');
            return;
        }
        
        // Proceed with deletion
        $result = $this->finance_model->delete_chart_account($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('finance_account_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('finance_account_delete_fail'));
        }
        
        redirect(current_lang() . '/finance/finance_account_list', 'refresh');
    }

    function journalentry() {
        $this->data['title'] = lang('journalentry');
        $this->form_validation->set_rules('issue_date', lang('journalentry_description'), 'required|valid_date');
        $this->form_validation->set_rules('description11', lang('description'), 'required');

        if ($this->form_validation->run() == TRUE) {
            $array_items = array();
            $account = $this->input->post('account');
            $description = $this->input->post('description');
            $credit = $this->input->post('credit');
            $debit = $this->input->post('debit');
            $act = count($account);
            $date = format_date(trim($this->input->post('issue_date')));
            $out_description = trim($this->input->post('description11'));
            $summ_credit = $this->input->post('summation_credit');
            $summ_debit = $this->input->post('summation_debit');

            if ($summ_credit == $summ_debit) {
                for ($i = 0; $i < $act; $i++) {
                    $account_code = $account[$i];
                    $credit_amount = str_replace(',','',$credit[$i]);
                    $debit_amount = str_replace(',','',$debit[$i]);
                    $description_data = $description[$i];

                    $tmp_array = array();
                    if (empty($account_code) || (empty($credit_amount) && empty($debit_amount))) {
                        
                    } else {
                        $tmp_array['account'] = $account_code;
                        $tmp_array['description'] = $description_data;
                        $tmp_array['credit'] = ($credit_amount > 0 ? $credit_amount : 0);
                        $tmp_array['debit'] = ($debit_amount > 0 ? $debit_amount : 0);
                        $tmp_array['entrydate'] = $date;
                        $tmp_array['createdby'] = current_user()->id;

                        $array_items[] = $tmp_array;
                    }
                }
                
                $main_array = array(
                    'entrydate' => $date,
                    'description' => $out_description
                );
                
                $insert = $this->finance_model->enter_journal($main_array,$array_items);
                if($insert){
                    $this->session->set_flashdata('message','Journal Recorded');
                    redirect(current_lang().'/finance/journalentry','refresh');
                }
                
            } else {
                $this->data['warning'] = 'Make sure summmation of credit and debit are equal';
            }
        }



        $this->data['taxcode_list'] = $this->setting_model->tax_info()->result();
        $this->data['account_list'] = $this->finance_model->account_chart_by_accounttype();





        $this->data['content'] = 'finance/journalentry';
        $this->load->view('template', $this->data);
    }

    // Chart Type Management
    function chart_type_list() {
        $this->data['title'] = lang('chart_type_list');
        $this->data['chart_types'] = $this->finance_model->account_type()->result();
        $this->data['content'] = 'finance/chart_type_list';
        $this->load->view('template', $this->data);
    }

    function chart_type_create($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->data['title'] = lang('chart_type_edit');
            $this->data['chart_type'] = $this->finance_model->account_type($id)->row();
        } else {
            $this->data['title'] = lang('chart_type_create');
        }

        $this->form_validation->set_rules('name', lang('chart_type_name'), 'required');
        $this->form_validation->set_rules('account', lang('chart_type_account'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'name' => trim($this->input->post('name')),
                'account' => trim($this->input->post('account'))
            );

            if (is_null($id)) {
                // Check if account number already exists
                $existing = $this->finance_model->account_type(null, $data['account'])->row();
                if ($existing) {
                    $this->data['warning'] = lang('chart_type_account_exists');
                } else {
                    $result = $this->finance_model->create_chart_type($data);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_type_create_success'));
                        redirect(current_lang() . '/finance/chart_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_type_create_fail');
                    }
                }
            } else {
                // Check if account number already exists for another record
                $existing = $this->finance_model->account_type(null, $data['account'])->row();
                if ($existing && $existing->id != $id) {
                    $this->data['warning'] = lang('chart_type_account_exists');
                } else {
                    $result = $this->finance_model->update_chart_type($data, $id);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_type_update_success'));
                        redirect(current_lang() . '/finance/chart_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_type_update_fail');
                    }
                }
            }
        }

        $this->data['content'] = 'finance/chart_type_form';
        $this->load->view('template', $this->data);
    }

    function chart_type_edit($id) {
        $this->chart_type_create($id);
    }

    function chart_type_delete($id) {
        $id = decode_id($id);
        $result = $this->finance_model->delete_chart_type($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('chart_type_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('chart_type_delete_fail_in_use'));
        }
        
        redirect(current_lang() . '/finance/chart_type_list', 'refresh');
    }

    // Chart Sub Type Management
    function chart_sub_type_list() {
        $this->data['title'] = lang('chart_sub_type_list');
        $this->data['chart_sub_types'] = $this->finance_model->account_type_sub()->result();
        $this->data['chart_types'] = $this->finance_model->account_type()->result();
        $this->data['content'] = 'finance/chart_sub_type_list';
        $this->load->view('template', $this->data);
    }

    function chart_sub_type_create($id = null) {
        $this->data['id'] = $id;
        if (!is_null($id)) {
            $id = decode_id($id);
            $this->data['title'] = lang('chart_sub_type_edit');
            $this->data['chart_sub_type'] = $this->finance_model->account_type_sub($id)->row();
        } else {
            $this->data['title'] = lang('chart_sub_type_create');
        }

        $this->form_validation->set_rules('name', lang('chart_sub_type_name'), 'required');
        $this->form_validation->set_rules('accounttype', lang('chart_type'), 'required');
        $this->form_validation->set_rules('sub_account', lang('chart_sub_type_account'), 'required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'name' => trim($this->input->post('name')),
                'accounttype' => trim($this->input->post('accounttype')),
                'sub_account' => trim($this->input->post('sub_account'))
            );

            if (is_null($id)) {
                // Check if sub account number already exists for this account type
                $existing = $this->finance_model->account_type_sub(null, $data['accounttype'], $data['sub_account'])->row();
                if ($existing) {
                    $this->data['warning'] = lang('chart_sub_type_account_exists');
                } else {
                    $result = $this->finance_model->create_chart_sub_type($data);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_sub_type_create_success'));
                        redirect(current_lang() . '/finance/chart_sub_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_sub_type_create_fail');
                    }
                }
            } else {
                // Check if sub account number already exists for another record
                $existing = $this->finance_model->account_type_sub(null, $data['accounttype'], $data['sub_account'])->row();
                if ($existing && $existing->id != $id) {
                    $this->data['warning'] = lang('chart_sub_type_account_exists');
                } else {
                    $result = $this->finance_model->update_chart_sub_type($data, $id);
                    if ($result) {
                        $this->session->set_flashdata('message', lang('chart_sub_type_update_success'));
                        redirect(current_lang() . '/finance/chart_sub_type_list', 'refresh');
                    } else {
                        $this->data['warning'] = lang('chart_sub_type_update_fail');
                    }
                }
            }
        }

        $this->data['chart_types'] = $this->finance_model->account_type()->result();
        $this->data['content'] = 'finance/chart_sub_type_form';
        $this->load->view('template', $this->data);
    }

    function chart_sub_type_edit($id) {
        $this->chart_sub_type_create($id);
    }

    function chart_sub_type_delete($id) {
        $id = decode_id($id);
        $result = $this->finance_model->delete_chart_sub_type($id);
        
        if ($result) {
            $this->session->set_flashdata('message', lang('chart_sub_type_delete_success'));
        } else {
            $this->session->set_flashdata('warning', lang('chart_sub_type_delete_fail_in_use'));
        }
        
        redirect(current_lang() . '/finance/chart_sub_type_list', 'refresh');
    }

}

?>
