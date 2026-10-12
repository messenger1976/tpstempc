<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base class for the Member Loan App controllers (application/controllers/memberapp/).
 * Loaded with require_once because CodeIgniter 2 has no MY_Controller here.
 */
class Memberapp_controller extends CI_Controller {

    /** Bump to force phones to download new app assets. */
    const ASSET_VERSION = '1.0.0';

    protected $data = array();

    public function __construct() {
        parent::__construct();
        $this->load->helper(array('url', 'cookie', 'form'));
        $this->load->model('memberapp_schema_model');
        $this->memberapp_schema_model->ensure();
        $this->load->library('memberapp_guard');
        $this->load->model('memberapp_model');
        $this->lang->load('loan');

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');

        $this->data['asset_version'] = self::ASSET_VERSION;
        $this->data['app_name'] = 'TAPSTEMCO Loans';
        $company = $this->db->limit(1)->get('companyinfo')->row();
        $this->data['company_name'] = $company && !empty($company->name) ? $company->name : 'TAPSTEMCO';
    }

    protected function no_cache() {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    /** Render an auth (signed-out) page inside the auth layout. */
    protected function auth_view($view, $data = array()) {
        $this->no_cache();
        $data = array_merge($this->data, $data);
        $data['csrf_field'] = $this->memberapp_guard->csrf_field();
        $data['content_view'] = $view;
        $this->load->view('memberapp/layout/auth', $data);
    }

    protected function asset($path) {
        return base_url('media/memberapp/' . ltrim($path, '/')) . '?v=' . self::ASSET_VERSION;
    }
}
