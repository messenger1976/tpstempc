<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of welcome
 *
 * @author miltone
 */
class Dashboard extends CI_Controller {

    //put your code here

    function __construct() {
        parent::__construct();


        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
        $this->data['dashboard'] = 1;
        $this->data['current_title'] = lang('page_home');
    }

    function index() {
        // Load Activity Log Model (use lowercase for model name)
        $this->load->model('activity_log_model');
        
        // Get recent system activities for dashboard
        $this->data['recent_activities'] = $this->activity_log_model->get_recent_activities(10);
        
        $this->data['content'] = 'dashboard';
        $this->load->view('dashboard', $this->data);
    }

}

?>
