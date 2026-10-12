<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Memberapp_schema_model - creates the Member Loan App tables on first use,
 * the same way Loan_model::ensure_* adds columns (SQL files are not deployed).
 *
 * Tables:
 *   member_app_otp             one-time codes for activation / password reset
 *   member_app_login_attempts  rate limiting
 *   member_app_products        loan products offered in the app (none = all)
 *   paymongo_settings          gateway keys and fee rules (single row, id 1)
 *   loan_online_payments       one row per PayMongo QR attempt
 *
 * Seeds the "Online - PayMongo" payment method per cooperative (GL account
 * left empty for Finance to set) and the staff access_level links.
 */
class Memberapp_schema_model extends CI_Model {

    const PAYMENT_METHOD = 'Online - PayMongo';

    private static $done = false;

    public function ensure() {
        if (self::$done) {
            return;
        }
        self::$done = true;
        if ($this->db->table_exists('loan_online_payments') && $this->db->table_exists('member_app_products')) {
            $this->ensure_fee_columns();
            return;
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `member_app_otp` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `member_id` varchar(50) NOT NULL,
            `PIN` bigint(20) NOT NULL,
            `purpose` varchar(20) NOT NULL COMMENT 'activate | reset | staff',
            `code_hash` varchar(255) NOT NULL,
            `channel` varchar(10) NOT NULL COMMENT 'sms | email | staff',
            `destination` varchar(100) DEFAULT NULL COMMENT 'masked',
            `attempts` int(11) NOT NULL DEFAULT 0,
            `expires_at` datetime NOT NULL,
            `verified_at` datetime DEFAULT NULL,
            `used_at` datetime DEFAULT NULL,
            `ip` varchar(45) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL COMMENT 'staff user for office codes',
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_member_purpose` (`member_id`, `purpose`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `member_app_login_attempts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `identity` varchar(100) NOT NULL,
            `kind` varchar(20) NOT NULL,
            `ip` varchar(45) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_identity` (`identity`, `kind`, `created_at`),
            KEY `idx_ip` (`ip`, `kind`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `member_app_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `PIN` bigint(20) NOT NULL,
            `product_id` int(11) NOT NULL,
            `max_amount` decimal(14,2) DEFAULT NULL,
            `updated_by` int(11) DEFAULT NULL,
            `update_date_time` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_pin_product` (`PIN`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `paymongo_settings` (
            `id` int(11) NOT NULL,
            `enabled` tinyint(1) NOT NULL DEFAULT 0,
            `public_key` varchar(255) DEFAULT NULL,
            `secret_key` varchar(255) DEFAULT NULL,
            `webhook_secret` varchar(255) DEFAULT NULL,
            `min_amount` decimal(12,2) NOT NULL DEFAULT 20.00,
            `description_prefix` varchar(100) DEFAULT 'Loan Payment',
            `receipt_note` varchar(255) DEFAULT NULL,
            `fee_enabled` tinyint(1) NOT NULL DEFAULT 0,
            `fee_percent` decimal(6,3) NOT NULL DEFAULT 0,
            `fee_fixed` decimal(12,2) NOT NULL DEFAULT 0,
            `payment_method_name` varchar(50) NOT NULL DEFAULT 'Online - PayMongo',
            `updated_by` int(11) DEFAULT NULL,
            `update_date_time` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        if (!$this->db->where('id', 1)->count_all_results('paymongo_settings')) {
            $this->db->insert('paymongo_settings', array('id' => 1, 'enabled' => 0));
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `loan_online_payments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `reference_no` varchar(30) NOT NULL,
            `LID` varchar(50) NOT NULL,
            `PID` bigint(20) NOT NULL,
            `member_id` varchar(50) NOT NULL,
            `user_id` int(11) DEFAULT NULL,
            `PIN` bigint(20) NOT NULL,
            `channel` varchar(20) NOT NULL DEFAULT 'qrph',
            `pay_option` varchar(20) NOT NULL DEFAULT 'custom',
            `amount` decimal(14,2) NOT NULL COMMENT 'applied to the loan',
            `fee_amount` decimal(14,2) NOT NULL DEFAULT 0,
            `charged_amount` decimal(14,2) NOT NULL COMMENT 'amount + fee',
            `amount_centavos` int(11) NOT NULL,
            `intent_id` varchar(100) DEFAULT NULL,
            `payment_id` varchar(100) DEFAULT NULL,
            `qr_image_url` mediumtext,
            `qr_test_url` varchar(500) DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending | paid | expired | failed | cancelled',
            `expires_at` datetime DEFAULT NULL,
            `paid_at` datetime DEFAULT NULL,
            `settled_at` datetime DEFAULT NULL,
            `cash_receipt_id` int(11) DEFAULT NULL,
            `receipt_no` varchar(30) DEFAULT NULL,
            `settle_error` varchar(500) DEFAULT NULL,
            `last_error` varchar(500) DEFAULT NULL,
            `webhook_received_at` datetime DEFAULT NULL,
            `create_date_time` datetime NOT NULL,
            `update_date_time` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_reference` (`reference_no`),
            KEY `idx_intent` (`intent_id`),
            KEY `idx_loan` (`LID`, `PIN`, `status`),
            KEY `idx_member` (`PID`, `PIN`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->ensure_fee_columns();
        $this->seed_payment_method();
        $this->seed_access_links();
    }

    /** Convenience fee GL account and the separate fee Cash Receipt it produces. */
    private function ensure_fee_columns() {
        $columns = array(
            'paymongo_settings' => array(
                'fee_gl_account' => "VARCHAR(50) NULL DEFAULT NULL COMMENT 'income account credited with the convenience fee' AFTER `fee_fixed`",
            ),
            'loan_online_payments' => array(
                'fee_receipt_id' => "INT(11) NULL DEFAULT NULL AFTER `receipt_no`",
                'fee_receipt_no' => "VARCHAR(30) NULL DEFAULT NULL AFTER `fee_receipt_id`",
            ),
        );
        foreach ($columns as $table => $defs) {
            foreach ($defs as $col => $definition) {
                if (!$this->db->query("SHOW COLUMNS FROM `$table` LIKE '" . $this->db->escape_str($col) . "'")->row()) {
                    $this->db->query("ALTER TABLE `$table` ADD COLUMN `$col` $definition");
                }
            }
        }
    }

    private function seed_payment_method() {
        $pins = $this->db->query('SELECT DISTINCT PIN FROM companyinfo')->result();
        foreach ($pins as $p) {
            $exists = $this->db->where('name', self::PAYMENT_METHOD)->where('PIN', $p->PIN)->count_all_results('paymentmenthod');
            if (!$exists) {
                $this->db->insert('paymentmenthod', array(
                    'name' => self::PAYMENT_METHOD,
                    'description' => 'Member Loan App QR Ph payments. Set the clearing / bank GL account before enabling.',
                    'gl_account_code' => null,
                    'status' => 1,
                    'PIN' => $p->PIN,
                ));
            }
        }
    }

    /** Staff links; only the Admin group (1) is allowed by default. */
    private function seed_access_links() {
        $links = array(
            array(5, 'Online_loan_payments'),
            array(9, 'Paymongo_settings'),
            array(1, 'Member_app_access'),
        );
        $groups = $this->db->select('id')->get('groups')->result();
        foreach ($groups as $g) {
            foreach ($links as $l) {
                $exists = $this->db->where('group_id', $g->id)->where('Module', $l[0])->where('link', $l[1])->count_all_results('access_level');
                if (!$exists) {
                    $this->db->insert('access_level', array(
                        'group_id' => $g->id,
                        'Module' => $l[0],
                        'link' => $l[1],
                        'allow' => (int) $g->id === 1 ? 1 : 0,
                    ));
                }
            }
        }
    }
}
