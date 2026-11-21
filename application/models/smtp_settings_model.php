<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Single-row store for email_smtp_settings.
 * read_row() does not create the table. ensure_schema() is called only from
 * the administrator settings page and from tools/smtp_settings_setup.php.
 */
class Smtp_settings_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->library('smtp_settings');
    }

    public function read_row() {
        if (!$this->db->table_exists(Smtp_settings::TABLE)) {
            return $this->smtp_settings->blank_row();
        }
        $row = $this->db->where('id', 1)->limit(1)->get(Smtp_settings::TABLE)->row_array();
        return $this->smtp_settings->normalize_row($row);
    }

    public function write_row($data) {
        if (!is_array($data)) {
            return false;
        }
        if (array_key_exists('smtp_pass', $data) && $data['smtp_pass'] !== null && $data['smtp_pass'] !== '') {
            if (!is_string($data['smtp_pass']) || strpos($data['smtp_pass'], 'v1:') !== 0) {
                return false;
            }
        }
        $clean = array();
        foreach (array('smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_crypto', 'smtp_timeout', 'from_email', 'from_name', 'is_active', 'updated_by', 'updated_at') as $column) {
            if (array_key_exists($column, $data)) {
                $clean[$column] = $data[$column];
            }
        }
        if (!$this->db->table_exists(Smtp_settings::TABLE)) {
            return false;
        }
        $exists = $this->db->where('id', 1)->count_all_results(Smtp_settings::TABLE) > 0;
        if ($exists) {
            $this->db->where('id', 1);
            return (bool) $this->db->update(Smtp_settings::TABLE, $clean);
        }
        $clean['id'] = 1;
        if (!isset($clean['is_active'])) {
            $clean['is_active'] = 0;
        }
        return (bool) $this->db->insert(Smtp_settings::TABLE, $clean);
    }

    /**
     * Creates the empty, disabled settings row. Safe to run more than once.
     * Does not copy or enable any mailbox.
     */
    public function ensure_schema() {
        $this->db->query($this->smtp_settings->schema_sql());
        if ($this->db->where('id', 1)->count_all_results(Smtp_settings::TABLE) > 0) {
            return true;
        }
        return (bool) $this->db->insert(Smtp_settings::TABLE, array(
            'id' => 1,
            'is_active' => 0,
        ));
    }
}
