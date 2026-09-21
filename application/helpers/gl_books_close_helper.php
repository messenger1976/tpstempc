<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GL books close (period lock).
 *
 * Dates on or before closed_as_of cannot be posted, voided, or have GL
 * dates changed. Reopening is done by moving closed_as_of backward.
 */

if (!function_exists('gl_books_close_ensure_table')) {
    function gl_books_close_ensure_table() {
        static $ready = false;
        if ($ready) {
            return true;
        }
        if (!function_exists('get_instance')) {
            return false;
        }
        $CI =& get_instance();
        if (!$CI || empty($CI->db)) {
            return false;
        }
        $CI->db->query("CREATE TABLE IF NOT EXISTS `gl_books_close` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `PIN` bigint(20) NOT NULL,
            `closed_as_of` date DEFAULT NULL COMMENT 'Books closed through this date inclusive; NULL = fully open',
            `previous_closed_as_of` date DEFAULT NULL,
            `action` varchar(32) NOT NULL DEFAULT 'set',
            `fiscal_year_id` int(11) DEFAULT NULL,
            `note` varchar(255) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_pin_created` (`PIN`,`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='GL books close history; latest row per PIN is current'");
        $ready = true;
        return true;
    }
}

if (!function_exists('gl_books_close_normalize_date')) {
    function gl_books_close_normalize_date($date) {
        if ($date === null || $date === false) {
            return null;
        }
        $date = trim((string) $date);
        if ($date === '' || $date === '0000-00-00') {
            return null;
        }
        // App UI uses DD-MM-YYYY
        if (preg_match('/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/', $date) && function_exists('format_date')) {
            $date = format_date($date, true);
        } elseif (preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/', $date)) {
            $parts = explode('/', $date);
            // Prefer MM/DD/YYYY only when first part > 12 (ambiguous otherwise); still accept strtotime fallback
            if ((int) $parts[0] > 12 && (int) $parts[1] <= 12) {
                $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return null;
        }
        return date('Y-m-d', $ts);
    }
}

if (!function_exists('gl_books_closed_as_of')) {
    function gl_books_closed_as_of($pin = null) {
        if (!function_exists('get_instance')) {
            return null;
        }
        $CI =& get_instance();
        if (!$CI || empty($CI->db)) {
            return null;
        }
        if ($pin === null) {
            if (function_exists('current_user')) {
                $user = current_user();
                if ($user && isset($user->PIN)) {
                    $pin = $user->PIN;
                }
            }
        }
        if ($pin === null || $pin === '') {
            return null;
        }
        $key = (string) $pin;
        if (!isset($CI->gl_books_closed_cache) || !is_array($CI->gl_books_closed_cache)) {
            $CI->gl_books_closed_cache = array();
        }
        if (array_key_exists($key, $CI->gl_books_closed_cache)) {
            return $CI->gl_books_closed_cache[$key];
        }
        if (!gl_books_close_ensure_table()) {
            $CI->gl_books_closed_cache[$key] = null;
            return null;
        }
        $row = $CI->db->query(
            'SELECT closed_as_of FROM gl_books_close WHERE PIN = ? ORDER BY id DESC LIMIT 1',
            array($pin)
        )->row();
        $as_of = null;
        if ($row && !empty($row->closed_as_of) && $row->closed_as_of !== '0000-00-00') {
            $as_of = gl_books_close_normalize_date($row->closed_as_of);
        }
        $CI->gl_books_closed_cache[$key] = $as_of;
        return $as_of;
    }
}

if (!function_exists('gl_date_is_closed')) {
    function gl_date_is_closed($date, $pin = null) {
        $check = gl_books_close_normalize_date($date);
        if ($check === null) {
            return false;
        }
        $closed = gl_books_closed_as_of($pin);
        if ($closed === null) {
            return false;
        }
        return (strcmp($check, $closed) <= 0);
    }
}

if (!function_exists('gl_closed_period_message')) {
    function gl_closed_period_message($date = null) {
        $closed = gl_books_closed_as_of();
        if ($closed === null) {
            return '';
        }
        $closed_fmt = date('M d, Y', strtotime($closed));
        $line = function_exists('lang') ? lang('gl_books_closed_block') : '';
        if ($line === '' || $line === 'gl_books_closed_block') {
            $line = 'Books are closed through {date}. You cannot post, void, or change GL transactions on or before that date. A user with Close Books permission can reopen by moving the close date back.';
        }
        $msg = str_replace('{date}', $closed_fmt, $line);
        $check = gl_books_close_normalize_date($date);
        if ($check !== null) {
            $msg .= ' Transaction date: ' . date('M d, Y', strtotime($check)) . '.';
        }
        return $msg;
    }
}

if (!function_exists('gl_reject_closed_date')) {
    /**
     * @return string|false Error message when locked, otherwise false.
     */
    function gl_reject_closed_date($date, $pin = null) {
        if (gl_date_is_closed($date, $pin)) {
            return gl_closed_period_message($date);
        }
        return false;
    }
}

if (!function_exists('gl_save_books_close')) {
    /**
     * @param string|null $closed_as_of Y-m-d or null to fully reopen
     * @return array{success:bool,message:string,closed_as_of:?string}
     */
    function gl_save_books_close($closed_as_of, $action = 'set', $fiscal_year_id = null, $note = '') {
        $CI =& get_instance();
        if (!gl_books_close_ensure_table()) {
            return array('success' => false, 'message' => 'Could not prepare books-close storage.', 'closed_as_of' => null);
        }
        $user = function_exists('current_user') ? current_user() : null;
        if (!$user || empty($user->PIN)) {
            return array('success' => false, 'message' => 'Not logged in.', 'closed_as_of' => null);
        }
        $previous = gl_books_closed_as_of($user->PIN);
        $normalized = gl_books_close_normalize_date($closed_as_of);
        $row = array(
            'PIN' => $user->PIN,
            'closed_as_of' => $normalized,
            'previous_closed_as_of' => $previous,
            'action' => substr(preg_replace('/[^a-z0-9_]/i', '', (string) $action), 0, 32),
            'fiscal_year_id' => $fiscal_year_id ? (int) $fiscal_year_id : null,
            'note' => $note !== '' ? substr(trim((string) $note), 0, 255) : null,
            'created_by' => isset($user->id) ? (int) $user->id : null,
            'created_at' => date('Y-m-d H:i:s'),
        );
        if (!$CI->db->insert('gl_books_close', $row)) {
            return array('success' => false, 'message' => 'Failed to save books close date.', 'closed_as_of' => $previous);
        }
        if (!isset($CI->gl_books_closed_cache) || !is_array($CI->gl_books_closed_cache)) {
            $CI->gl_books_closed_cache = array();
        }
        $CI->gl_books_closed_cache[(string) $user->PIN] = $normalized;
        $msg = $normalized
            ? ('Books closed through ' . date('M d, Y', strtotime($normalized)) . '.')
            : 'Books are fully open. Period lock removed.';
        return array('success' => true, 'message' => $msg, 'closed_as_of' => $normalized);
    }
}

if (!function_exists('can_close_books')) {
    function can_close_books() {
        if (!function_exists('get_instance')) {
            return false;
        }
        $CI =& get_instance();
        if (isset($CI->ion_auth) && $CI->ion_auth->is_admin()) {
            return true;
        }
        return function_exists('has_role') ? has_role(6, 'Close_books') : false;
    }
}

if (!function_exists('gl_books_close_alert_html')) {
    function gl_books_close_alert_html() {
        $closed = gl_books_closed_as_of();
        if ($closed === null) {
            return '';
        }
        $fmt = date('M d, Y', strtotime($closed));
        $text = function_exists('lang') ? lang('gl_books_closed_banner') : '';
        if ($text === '' || $text === 'gl_books_closed_banner') {
            $text = 'Books are closed through {date}. Dates on or before this cannot be posted, voided, or changed.';
        }
        $text = str_replace('{date}', $fmt, $text);
        return '<div class="alert alert-warning" style="margin:0 0 16px;"><i class="fa fa-lock"></i> '
            . htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
            . '</div>';
    }
}
