# Email / SMTP settings

Administrators save one SMTP configuration for the shared mailer. The same settings are used for member-app email codes, customer quotations, sales invoices, supplier purchase orders, supplier invoices, and account-credential emails.

SMTP is **off** until an administrator checks **Enable SMTP** and saves. Until then, outgoing mail keeps the transport the application already uses.

## Where to enter the mailbox later

1. Sign in as a user in the `admin` group (`ion_auth` administrator).
2. Open **Settings → Email / SMTP**.
3. The address is `/{lang}/setting/smtp_settings` (for example `/en/setting/smtp_settings`).
4. Type the host, port, encryption, username, password, From address, and From name on that form. No mailbox is filled in by the application.
5. Save while **Enable SMTP** is still unchecked. The password field should then say that an encrypted password is saved. The password itself is not shown again.
6. Check **Enable SMTP** and save. Leaving the password blank on that second save keeps the password already stored.

A blank password never clears a saved password. Use **Remove saved password** for that.

## Server key (required before a password can be stored)

The password is encrypted with AES-256-GCM. The key is not stored in the database and must not be committed.

Use either:

- Environment variable `TAPSTEMCO_SMTP_KEY`, or
- The file `application/config/smtp.key` (gitignored). Copy `application/config/smtp.key.example` and replace the placeholder.

Generate a key on the server:

```bash
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
```

If the key is missing, the form still loads, but a new password is rejected and nothing is written in plaintext. If SMTP is enabled and the saved password cannot be decrypted, the message is not sent and the application does not switch back to the previous transport.

The CodeIgniter `encryption_key` in `application/config/config.php` is not used for this password.

## Database setup

Opening **Settings → Email / SMTP** as an administrator creates `email_smtp_settings` if it is missing, and inserts one disabled row (`id = 1`, `is_active = 0`). That does not change existing mail settings.

To review or run the same statement yourself:

```bash
php tools/smtp_settings_setup.php
```

The script only prints SQL. It does not connect to a database.

## Failure behavior

- SMTP not enabled: the shared mailer does not apply these settings.
- SMTP enabled but incomplete, or the password cannot be read: send fails with a short message that does not include the password or the email body.
- SMTP enabled and the mail server rejects the message: send fails once. There is no second attempt on the previous transport.

Certificate verification is left at the PHP default. The settings page has no option to disable it.
