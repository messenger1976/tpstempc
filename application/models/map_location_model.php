<?php

/**
 * Manual map pins: address geocode cache rows, per-member pins and the office pin.
 * Read paths for the maps themselves stay in member_model.
 */
class Map_location_model extends CI_Model {

    const SOURCES = 'nominatim,talibon_fallback,talibon_center,manual';
    const STATUSES = 'ok,failed,pending';

    function __construct() {
        parent::__construct();
    }

    function geocode_table_ready() {
        return $this->db->table_exists('member_address_geocode');
    }

    function member_pin_ready() {
        return $this->db->field_exists('map_lat', 'members_contact');
    }

    function office_pin_ready() {
        return $this->db->field_exists('map_lat', 'companyinfo');
    }

    /**
     * Address cache rows with how many of this organisation's active members use each one.
     *
     * @param array $filters source, status, q, scope ('mine' = only addresses my members use)
     */
    function list_addresses($filters, $limit, $offset) {
        list($sql, $params) = $this->address_list_sql($filters);
        $sql = "SELECT g.*, IFNULL(mc.member_count, 0) AS member_count " . $sql
             . " ORDER BY CASE WHEN g.geocode_status <> 'ok' THEN 0
                              WHEN g.source IN ('talibon_fallback','talibon_center') THEN 1
                              ELSE 2 END,
                         member_count DESC, g.address_raw ASC
                 LIMIT " . intval($offset) . ", " . intval($limit);
        return $this->db->query($sql, $params)->result();
    }

    function count_addresses($filters) {
        list($sql, $params) = $this->address_list_sql($filters);
        $row = $this->db->query("SELECT COUNT(*) AS cnt " . $sql, $params)->row();
        return $row ? intval($row->cnt) : 0;
    }

    private function address_list_sql($filters) {
        $params = array(current_user()->PIN);
        $join = (isset($filters['scope']) && $filters['scope'] === 'all') ? 'LEFT JOIN' : 'INNER JOIN';
        $sql = "FROM member_address_geocode g
                $join (
                    SELECT UPPER(TRIM(c.physicaladdress)) AS address_key, COUNT(*) AS member_count
                    FROM members m
                    INNER JOIN members_contact c ON c.PID = m.PID
                    WHERE m.PIN = ?
                      AND m.status = 1
                      AND c.physicaladdress IS NOT NULL
                      AND TRIM(c.physicaladdress) != ''
                    GROUP BY UPPER(TRIM(c.physicaladdress))
                ) mc ON mc.address_key = g.address_key
                WHERE 1 = 1";

        if (!empty($filters['source']) && in_array($filters['source'], explode(',', self::SOURCES), true)) {
            $sql .= " AND g.source = ?";
            $params[] = $filters['source'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], explode(',', self::STATUSES), true)) {
            $sql .= " AND g.geocode_status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND g.address_raw LIKE ?";
            $params[] = '%' . $filters['q'] . '%';
        }

        return array($sql, $params);
    }

    function get_address($id) {
        return $this->db->where('id', intval($id))->get('member_address_geocode')->row();
    }

    /**
     * Sample of this organisation's members at an address (for context on the edit page).
     */
    function members_at_address($address_key, $limit = 20) {
        return $this->db->query(
            "SELECT m.id, m.member_id,
                    TRIM(CONCAT(IFNULL(m.firstname,''), ' ', IFNULL(m.lastname,''))) AS name
             FROM members m
             INNER JOIN members_contact c ON c.PID = m.PID
             WHERE m.PIN = ?
               AND m.status = 1
               AND UPPER(TRIM(c.physicaladdress)) = ?
             ORDER BY m.lastname ASC, m.firstname ASC
             LIMIT " . intval($limit),
            array(current_user()->PIN, $address_key)
        )->result();
    }

    function save_address_pin($id, $lat, $lng) {
        $data = array(
            'lat' => $lat,
            'lng' => $lng,
            'geocode_status' => 'ok',
            'source' => 'manual',
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($this->db->field_exists('updated_by', 'member_address_geocode')) {
            $data['updated_by'] = current_user()->id;
        }
        return $this->db->update('member_address_geocode', $data, array('id' => intval($id)));
    }

    /**
     * Hand the row back to the geocoder: next install_member_address_geocode.php run re-geocodes it.
     */
    function reset_address($id) {
        $data = array(
            'lat' => NULL,
            'lng' => NULL,
            'geocode_status' => 'pending',
            'source' => NULL,
            'updated_at' => date('Y-m-d H:i:s'),
        );
        if ($this->db->field_exists('updated_by', 'member_address_geocode')) {
            $data['updated_by'] = current_user()->id;
        }
        return $this->db->update('member_address_geocode', $data, array('id' => intval($id)));
    }

    /**
     * Member (scoped to the current organisation) with contact address, own pin and address pin.
     */
    function get_member($member_row_id) {
        $member = $this->db->where('id', intval($member_row_id))
                           ->where('PIN', current_user()->PIN)
                           ->get('members')->row();
        if (!$member) {
            return null;
        }

        $contact = $this->db->where('PID', $member->PID)->get('members_contact')->row();
        $member->address = ($contact && isset($contact->physicaladdress)) ? trim($contact->physicaladdress) : '';
        $member->map_lat = ($contact && isset($contact->map_lat)) ? $contact->map_lat : null;
        $member->map_lng = ($contact && isset($contact->map_lng)) ? $contact->map_lng : null;
        $member->map_updated_at = ($contact && isset($contact->map_updated_at)) ? $contact->map_updated_at : null;
        $member->has_contact_row = (bool) $contact;

        $member->address_pin = null;
        if ($member->address !== '' && $this->geocode_table_ready()) {
            $member->address_pin = $this->db->query(
                "SELECT id, lat, lng, geocode_status, source FROM member_address_geocode
                 WHERE address_key = UPPER(TRIM(?)) LIMIT 1",
                array($member->address)
            )->row();
        }

        return $member;
    }

    function save_member_pin($member, $lat, $lng) {
        $data = array(
            'map_lat' => $lat,
            'map_lng' => $lng,
            'map_updated_at' => date('Y-m-d H:i:s'),
            'map_updated_by' => current_user()->id,
        );
        if (!$member->has_contact_row) {
            return FALSE;
        }
        return $this->db->update('members_contact', $data, array('PID' => $member->PID));
    }

    function get_office() {
        return $this->db->where('PIN', current_user()->PIN)->get('companyinfo')->row();
    }

    function save_office_pin($company_id, $lat, $lng) {
        return $this->db->update('companyinfo', array(
            'map_lat' => $lat,
            'map_lng' => $lng,
            'map_updated_at' => date('Y-m-d H:i:s'),
            'map_updated_by' => current_user()->id,
        ), array('id' => intval($company_id), 'PIN' => current_user()->PIN));
    }
}
