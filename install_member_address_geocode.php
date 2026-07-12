<?php
/**
 * Installation Script: Member Address Geocode Cache
 *
 * Creates member_address_geocode and geocodes unique physical addresses
 * via OpenStreetMap Nominatim for the dashboard members map.
 *
 * Usage (CLI recommended — geocoding takes ~1s per unique address):
 *   php install_member_address_geocode.php
 *   php install_member_address_geocode.php --force   (re-geocode all)
 *
 * Browser: http://localhost/tapstemco/install_member_address_geocode.php
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}
require_once('application/config/database.php');

$db_config = $db['default'];
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');
@set_time_limit(0);

$is_cli = (php_sapi_name() === 'cli');
$force = false;
if ($is_cli) {
    $force = in_array('--force', isset($argv) ? $argv : array(), true);
} else {
    $force = isset($_GET['force']) && $_GET['force'] == '1';
}

function out($msg, $is_cli) {
    if ($is_cli) {
        echo strip_tags($msg) . PHP_EOL;
    } else {
        echo $msg;
    }
}

if (!$is_cli) {
    echo "<!DOCTYPE html><html><head><title>Member Address Geocode - Installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; border-bottom: 2px solid #1ab394; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .step { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        pre { background: #eee; padding: 8px; overflow: auto; max-height: 300px; }
    </style></head><body><div class='container'>
    <h1>Member Address Geocode Cache - Installation</h1>";
}

$all_ok = true;

out($is_cli ? "Step 1: Create table" : "<div class='step'><h2>Step 1: Create member_address_geocode</h2>", $is_cli);

$create_sql = "CREATE TABLE IF NOT EXISTS `member_address_geocode` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `address_key` VARCHAR(255) NOT NULL,
  `address_raw` VARCHAR(255) NOT NULL,
  `lat` DECIMAL(10,7) NULL DEFAULT NULL,
  `lng` DECIMAL(10,7) NULL DEFAULT NULL,
  `geocode_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `source` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_address_key` (`address_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($create_sql) === TRUE) {
    out($is_cli ? "OK: table ready" : "<div class='success'>Table member_address_geocode is ready.</div>", $is_cli);
} else {
    out($is_cli ? "ERROR: " . $mysqli->error : "<div class='error'>Error: " . htmlspecialchars($mysqli->error) . "</div>", $is_cli);
    $all_ok = false;
}
if (!$is_cli) echo "</div>";

/**
 * Known approximate centers for Talibon barangays (fallback when Nominatim fails).
 * Source: approximate OSM / municipal locations around Talibon, Bohol.
 */
$talibon_fallback = array(
    'POBLACION' => array(10.1494, 124.3252),
    'SAN JOSE' => array(10.1558, 124.3185),
    'SAN ISIDRO' => array(10.1380, 124.3100),
    'SAN AGUSTIN' => array(10.1620, 124.3350),
    'SAN FRANCISCO' => array(10.1450, 124.3400),
    'BAGACAY' => array(10.1700, 124.3100),
    'SAN ROQUE' => array(10.1300, 124.3300),
    'TANGHALIGUE' => array(10.1200, 124.3000),
    'BALINTAWAK' => array(10.1580, 124.3280),
    'SUBA' => array(10.1400, 124.3500),
    'STO. NIÑO' => array(10.1650, 124.3200),
    'STO NIÑO' => array(10.1650, 124.3200),
    'SANTO NINO' => array(10.1650, 124.3200),
    'GUINDACPAN' => array(10.1800, 124.2800),
    'SAN CARLOS' => array(10.1250, 124.3150),
    'ZAMORA' => array(10.1350, 124.3450),
    'SAN PEDRO' => array(10.1500, 124.3050),
    'BURGOS' => array(10.1600, 124.3500),
    'SIKATUNA' => array(10.1750, 124.3400),
    'NOCNOCAN' => array(10.1100, 124.2900),
    'MAGSAYSAY' => array(10.1480, 124.3150),
    'CATABAN' => array(10.1900, 124.3000),
    'CALITUBAN' => array(10.2000, 124.2700),
    'MAHANAY' => array(10.1850, 124.2600),
    'BUSALIAN' => array(10.1150, 124.3400),
);

function normalize_address_key($address) {
    // Must match MySQL UPPER(TRIM(physicaladdress)) used in dashboard JOINs
    return strtoupper(trim(preg_replace('/\s+/', ' ', $address)));
}

function build_geocode_query($address) {
    $q = trim($address);
    // Soft-clean purok / sitio prefixes for better Nominatim hits
    $q = preg_replace('/^(P-?\d+\s*[-,]?\s*|PUROK-?\d+\s*[-,]?\s*|PUROK\s+\d+\s*[-,]?\s*)/i', '', $q);
    $q = trim($q, " \t\n\r\0\x0B,-");
    if (stripos($q, 'Philippines') === false) {
        $q .= ', Philippines';
    }
    return $q;
}

function detect_barangay($address_key, $talibon_fallback) {
    foreach ($talibon_fallback as $brgy => $coords) {
        if (strpos($address_key, $brgy) !== false) {
            return $coords;
        }
    }
    return null;
}

function nominatim_geocode($query) {
    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ph&q=' . rawurlencode($query);
    $opts = array(
        'http' => array(
            'method' => 'GET',
            'header' => "User-Agent: TAPSTEMCO-Dashboard/1.0 (cooperative member map)\r\nAccept: application/json\r\n",
            'timeout' => 20,
        ),
    );
    $ctx = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return null;
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data[0]['lat']) || empty($data[0]['lon'])) {
        return null;
    }
    return array(floatval($data[0]['lat']), floatval($data[0]['lon']));
}

out($is_cli ? "Step 2: Sync unique addresses" : "<div class='step'><h2>Step 2: Sync unique member addresses</h2>", $is_cli);

$addr_sql = "SELECT TRIM(physicaladdress) AS address_raw, COUNT(*) AS cnt
             FROM members_contact
             WHERE physicaladdress IS NOT NULL AND TRIM(physicaladdress) != ''
             GROUP BY UPPER(TRIM(physicaladdress))
             ORDER BY cnt DESC";
$addr_res = $mysqli->query($addr_sql);
$unique = array();
if ($addr_res) {
    while ($row = $addr_res->fetch_assoc()) {
        $unique[] = $row;
    }
}

out(($is_cli ? "" : "<div class='info'>") . "Found " . count($unique) . " unique addresses." . ($is_cli ? "" : "</div>"), $is_cli);

$synced = 0;
foreach ($unique as $row) {
    $raw = $row['address_raw'];
    $key = normalize_address_key($raw);
    $key_esc = $mysqli->real_escape_string($key);
    $raw_esc = $mysqli->real_escape_string($raw);
    $exists = $mysqli->query("SELECT id FROM member_address_geocode WHERE address_key = '$key_esc' LIMIT 1");
    if ($exists && $exists->num_rows > 0) {
        $mysqli->query("UPDATE member_address_geocode SET address_raw = '$raw_esc' WHERE address_key = '$key_esc'");
    } else {
        $mysqli->query("INSERT INTO member_address_geocode (address_key, address_raw, geocode_status, updated_at)
                        VALUES ('$key_esc', '$raw_esc', 'pending', NOW())");
        $synced++;
    }
}
out(($is_cli ? "" : "<div class='success'>") . "Inserted $synced new address keys." . ($is_cli ? "" : "</div></div>"), $is_cli);

out($is_cli ? "Step 3: Geocode via Nominatim (+ fallback)" : "<div class='step'><h2>Step 3: Geocode addresses (Nominatim + local fallback)</h2><pre>", $is_cli);

$status_filter = $force ? "1=1" : "geocode_status IN ('pending','failed') OR lat IS NULL OR lng IS NULL";
$todo = $mysqli->query("SELECT * FROM member_address_geocode WHERE $status_filter ORDER BY id ASC");
$ok_count = 0;
$fail_count = 0;
$skip_count = 0;

while ($todo && ($row = $todo->fetch_assoc())) {
    if (!$force && $row['geocode_status'] === 'ok' && $row['lat'] !== null && $row['lng'] !== null) {
        $skip_count++;
        continue;
    }

    $key = $row['address_key'];
    $query = build_geocode_query($row['address_raw']);
    $coords = nominatim_geocode($query);
    $source = 'nominatim';

    if (!$coords) {
        $coords = detect_barangay($key, $talibon_fallback);
        $source = $coords ? 'talibon_fallback' : null;
    }

    // Default to Talibon town center if still unknown
    if (!$coords && (strpos($key, 'TALIBON') !== false || strpos($key, 'BOHOL') !== false)) {
        $coords = array(10.1494, 124.3252);
        $source = 'talibon_center';
    }

    $id = intval($row['id']);
    if ($coords) {
        $lat = $coords[0];
        $lng = $coords[1];
        // Slight jitter for same-barangay fallback so markers don't stack perfectly
        if ($source !== 'nominatim') {
            $lat += (crc32($key) % 100) / 100000;
            $lng += ((crc32($key) >> 8) % 100) / 100000;
        }
        $mysqli->query("UPDATE member_address_geocode
                        SET lat = $lat, lng = $lng, geocode_status = 'ok', source = '" . $mysqli->real_escape_string($source) . "', updated_at = NOW()
                        WHERE id = $id");
        $ok_count++;
        out("OK [$source] {$row['address_raw']} => $lat, $lng", $is_cli);
    } else {
        $mysqli->query("UPDATE member_address_geocode
                        SET geocode_status = 'failed', source = NULL, updated_at = NOW()
                        WHERE id = $id");
        $fail_count++;
        out("FAIL {$row['address_raw']}", $is_cli);
    }

    // Nominatim usage policy: max 1 request/second
    sleep(1);
}

if (!$is_cli) echo "</pre>";
out(($is_cli ? "" : "<div class='info'>") . "Geocoded OK: $ok_count, Failed: $fail_count, Skipped: $skip_count" . ($is_cli ? "" : "</div></div>"), $is_cli);

$stats = $mysqli->query("SELECT
    SUM(geocode_status='ok') AS ok_n,
    SUM(geocode_status='failed') AS fail_n,
    SUM(geocode_status='pending') AS pending_n,
    COUNT(*) AS total_n
    FROM member_address_geocode")->fetch_assoc();

out($is_cli ? "Done. OK={$stats['ok_n']} Failed={$stats['fail_n']} Pending={$stats['pending_n']} Total={$stats['total_n']}"
    : "<div class='step'><h2>Summary</h2><div class='success'><strong>Installation complete.</strong><br>
       Cached addresses: {$stats['total_n']}<br>
       Geocoded OK: {$stats['ok_n']}<br>
       Failed: {$stats['fail_n']}<br>
       Pending: {$stats['pending_n']}<br><br>
       Open the Dashboard to view the Members Map above Loan Aging Summary.<br>
       Re-run with <code>?force=1</code> (or <code>--force</code> in CLI) to re-geocode.</div></div></div></body></html>", $is_cli);

$mysqli->close();
