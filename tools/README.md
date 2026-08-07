# tools/

One-off **install**, **check**, **debug**, and **test** PHP scripts for TAPSTEMCO.

Put **new** test / installer / diagnostic scripts in this folder — not in the application root.

## Usage

Browser (local):

```
http://localhost/tapstemco/tools/<script_name>.php
```

CLI (from app root or tools):

```
php tools/<script_name>.php
```

Scripts that load the app or DB config resolve paths relative to the parent app root (`../`).

## Security

These scripts often bypass normal auth. Do **not** leave them reachable on production without protection. Prefer delete or restrict after use.

## Categories (examples)

| Prefix / pattern | Purpose |
|------------------|---------|
| `test_*.php` | Smoke / feature tests |
| `check_*.php` | Diagnostics |
| `install_*.php` | One-time schema/feature installers |
| `add_*_permission.php` | Permission seed scripts |
| `create_*.php` | Table / structure helpers |
| `*_debug.php`, `view_logs.php`, `clear_cache.php` | Ops helpers |
| `tmp_*.html`, other root HTML snapshots | Saved page captures / scratch HTML (not app views) |

Do **not** move CodeIgniter directory `index.html` stubs under `application/` — those are intentional “403 Forbidden” guards.
