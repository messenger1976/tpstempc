<?php
define('BASEPATH', 'x');
include dirname(__DIR__) . '/application/config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($m->connect_error) {
    fwrite(STDERR, 'CONNECT_FAIL: ' . $m->connect_error . PHP_EOL);
    exit(1);
}
$needed = array(
    7 => 'Evaluated && Rejected',
    8 => 'Accepted && Rejected',
    9 => 'Accepted && Disbursed',
);
foreach ($needed as $code => $name) {
    $chk = $m->query('SELECT id FROM loan_status WHERE code = ' . (int) $code);
    if ($chk && $chk->num_rows === 0) {
        $stmt = $m->prepare('INSERT INTO loan_status (code, name) VALUES (?, ?)');
        $stmt->bind_param('is', $code, $name);
        $stmt->execute();
        echo "inserted {$code}\n";
    } else {
        echo "exists {$code}\n";
    }
}
$r = $m->query('SELECT code, name FROM loan_status WHERE code IN (7,8,9) ORDER BY code');
while ($row = $r->fetch_assoc()) {
    echo $row['code'] . ' => ' . $row['name'] . PHP_EOL;
}
$m->close();
