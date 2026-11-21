<?php
/**
 * Email/SMTP settings setup for an existing installation.
 *
 * Prints the CREATE TABLE statement and key instructions.
 * Does not connect to a database. An administrator can create the same
 * empty, disabled row by opening Settings -> Email / SMTP, or run the
 * printed SQL on the existing database.
 *
 *   php tools/smtp_settings_setup.php
 *
 * Do not put mailbox passwords or the encryption key in this file.
 */
if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

define('SMTP_SETTINGS_STANDALONE', true);
require dirname(__DIR__) . '/application/libraries/Smtp_settings.php';

$smtp = new Smtp_settings();

echo "Email/SMTP settings setup\n";
echo "SMTP stays disabled (is_active = 0) until an administrator enables it in Settings -> Email / SMTP.\n\n";
echo "Encryption key (required before a password can be saved):\n";
echo "  Set TAPSTEMCO_SMTP_KEY, or create application/config/smtp.key from application/config/smtp.key.example.\n";
echo "  Generate: php -r \"echo base64_encode(random_bytes(32)), PHP_EOL;\"\n";
echo "  Do not commit smtp.key.\n\n";
echo $smtp->schema_sql() . ";\n\n";
echo "INSERT INTO `email_smtp_settings` (`id`, `is_active`)\n";
echo "SELECT 1, 0 FROM DUAL\n";
echo "WHERE NOT EXISTS (SELECT 1 FROM `email_smtp_settings` WHERE `id` = 1);\n";
echo "\nNo database changes were made.\n";
exit(0);
