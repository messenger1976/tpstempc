<?php

/**
 * Manual map pins for the OpenStreetMap views (dashboard members map, collector map):
 * address geocode cache rows, per-member pins and the office pin.
 */
class Map_location extends CI_Controller {

    const PER_PAGE = 50;

    function __construct() {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        if (!can_manage_map_locations()) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('dashboard', 'refresh');
            return;
        }

        $this->data['current_title'] = 'Map Locations';
        $this->load->model('map_location_model');
        $this->load->model('member_model');
    }

    function index() {
        $this->data['title'] = 'Map Locations';
        $this->data['table_ready'] = $this->map_location_model->geocode_table_ready();
        $this->data['member_pin_ready'] = $this->map_location_model->member_pin_ready();
        $this->data['office_pin_ready'] = $this->map_location_model->office_pin_ready();

        $filters = array(
            'source' => trim((string) $this->input->get('source')),
            'status' => trim((string) $this->input->get('status')),
            'q' => trim((string) $this->input->get('q')),
            'scope' => $this->input->get('scope') === 'all' ? 'all' : 'mine',
        );
        $page = max(1, intval($this->input->get('page')));

        $this->data['filters'] = $filters;
        $this->data['page'] = $page;
        $this->data['per_page'] = self::PER_PAGE;
        $this->data['addresses'] = array();
        $this->data['total'] = 0;

        if ($this->data['table_ready']) {
            $this->data['total'] = $this->map_location_model->count_addresses($filters);
            $this->data['addresses'] = $this->map_location_model->list_addresses($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE);
        }

        $this->data['content'] = 'map_location/index';
        $this->load->view('template', $this->data);
    }

    function edit_address($id = null) {
        if (!$this->map_location_model->geocode_table_ready()) {
            $this->session->set_flashdata('warning', 'Run tools/install_member_address_geocode.php first.');
            redirect(current_lang() . '/map_location/index', 'refresh');
        }

        $row = $this->map_location_model->get_address($id);
        if (!$row) {
            show_404();
        }

        if ($this->input->post('action') === 'save') {
            $coords = $this->read_coords();
            if ($coords['error'] === '') {
                $this->map_location_model->save_address_pin($row->id, $coords['lat'], $coords['lng']);
                $this->session->set_flashdata('message', 'Address pin saved. It will not be overwritten by the geocode script.');
                redirect(current_lang() . '/map_location/edit_address/' . $row->id, 'refresh');
            }
            $this->data['warning'] = $coords['error'];
        }

        $this->data['title'] = 'Edit Address Pin';
        $this->data['row'] = $row;
        $this->data['members'] = $this->map_location_model->members_at_address($row->address_key);
        $this->data['picker'] = $this->picker_data(
            $row->geocode_status === 'ok' ? $row->lat : null,
            $row->geocode_status === 'ok' ? $row->lng : null
        );
        $this->data['content'] = 'map_location/edit_address';
        $this->load->view('template', $this->data);
    }

    function reset_address($id = null) {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect(current_lang() . '/map_location/index', 'refresh');
        }
        $row = $this->map_location_model->get_address($id);
        if (!$row) {
            show_404();
        }
        $this->map_location_model->reset_address($row->id);
        $this->session->set_flashdata('message', 'Address pin reset. The next geocode script run will look it up again.');
        redirect(current_lang() . '/map_location/edit_address/' . $row->id, 'refresh');
    }

    function member_pin($id = null) {
        if (!$this->map_location_model->member_pin_ready()) {
            $this->session->set_flashdata('warning', 'Run tools/install_map_locations.php first.');
            redirect(current_lang() . '/map_location/index', 'refresh');
        }

        $member = $this->map_location_model->get_member(decode_id($id));
        if (!$member) {
            show_404();
        }

        $action = $this->input->post('action');
        if ($action === 'save' || $action === 'clear') {
            if (!$member->has_contact_row) {
                $this->data['warning'] = 'Save the member\'s contact information first.';
            } elseif ($action === 'clear') {
                $this->map_location_model->save_member_pin($member, NULL, NULL);
                $this->session->set_flashdata('message', 'Member pin cleared. The map now uses the address pin.');
                redirect(current_lang() . '/map_location/member_pin/' . $id, 'refresh');
            } else {
                $coords = $this->read_coords();
                if ($coords['error'] === '') {
                    $this->map_location_model->save_member_pin($member, $coords['lat'], $coords['lng']);
                    $this->session->set_flashdata('message', 'Member pin saved.');
                    redirect(current_lang() . '/map_location/member_pin/' . $id, 'refresh');
                }
                $this->data['warning'] = $coords['error'];
            }
        }

        $start_lat = $member->map_lat;
        $start_lng = $member->map_lng;
        if (($start_lat === null || $start_lng === null) && $member->address_pin && $member->address_pin->geocode_status === 'ok') {
            $start_lat = $member->address_pin->lat;
            $start_lng = $member->address_pin->lng;
        }

        $this->data['title'] = 'Member Map Pin';
        $this->data['member'] = $member;
        $this->data['encoded_id'] = $id;
        $this->data['picker'] = $this->picker_data($start_lat, $start_lng);
        $this->data['content'] = 'map_location/member_pin';
        $this->load->view('template', $this->data);
    }

    function office() {
        if (!$this->map_location_model->office_pin_ready()) {
            $this->session->set_flashdata('warning', 'Run tools/install_map_locations.php first.');
            redirect(current_lang() . '/map_location/index', 'refresh');
        }

        $company = $this->map_location_model->get_office();
        if (!$company) {
            show_404();
        }

        $action = $this->input->post('action');
        if ($action === 'clear') {
            $this->map_location_model->save_office_pin($company->id, NULL, NULL);
            $this->session->set_flashdata('message', 'Office pin cleared. The map falls back to the address lookup.');
            redirect(current_lang() . '/map_location/office', 'refresh');
        } elseif ($action === 'save') {
            $coords = $this->read_coords();
            if ($coords['error'] === '') {
                $this->map_location_model->save_office_pin($company->id, $coords['lat'], $coords['lng']);
                $this->session->set_flashdata('message', 'Office pin saved.');
                redirect(current_lang() . '/map_location/office', 'refresh');
            }
            $this->data['warning'] = $coords['error'];
        }

        $this->data['title'] = 'Office Map Pin';
        $this->data['company'] = $company;
        $this->data['has_office_pin'] = ($company->map_lat !== null && $company->map_lng !== null);
        $this->data['picker'] = $this->picker_data($company->map_lat, $company->map_lng);
        $this->data['content'] = 'map_location/office';
        $this->load->view('template', $this->data);
    }

    /**
     * Validate posted lat/lng. Pins outside the Philippines need confirm_outside=1.
     *
     * @return array lat, lng, error ('' when valid)
     */
    private function read_coords() {
        $lat = trim((string) $this->input->post('lat'));
        $lng = trim((string) $this->input->post('lng'));
        $result = array('lat' => null, 'lng' => null, 'error' => '');

        if ($lat === '' || $lng === '' || !is_numeric($lat) || !is_numeric($lng)) {
            $result['error'] = 'Latitude and longitude must both be numbers. Click the map or drag the marker to set them.';
            return $result;
        }

        $lat = round(floatval($lat), 7);
        $lng = round(floatval($lng), 7);
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $result['error'] = 'Latitude must be between -90 and 90, and longitude between -180 and 180.';
            return $result;
        }

        $inside_ph = ($lat >= 4 && $lat <= 22 && $lng >= 116 && $lng <= 127);
        if (!$inside_ph && $this->input->post('confirm_outside') !== '1') {
            $result['error'] = 'That pin is outside the Philippines. Tick "Pin is outside the Philippines" to save it anyway.';
            return $result;
        }

        $result['lat'] = $lat;
        $result['lng'] = $lng;
        return $result;
    }

    /**
     * Starting point for the picker: posted values (after a failed save), else the current pin, else the office.
     */
    private function picker_data($lat, $lng) {
        $office = $this->member_model->get_office_map_location();
        $posted_lat = $this->input->post('lat');
        $posted_lng = $this->input->post('lng');
        if (is_numeric($posted_lat) && is_numeric($posted_lng)) {
            $lat = $posted_lat;
            $lng = $posted_lng;
        }
        $has_pin = ($lat !== null && $lng !== null && $lat !== '' && $lng !== '');
        return array(
            'lat' => $has_pin ? floatval($lat) : null,
            'lng' => $has_pin ? floatval($lng) : null,
            'office' => $office,
        );
    }
}
