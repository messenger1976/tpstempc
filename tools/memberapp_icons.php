<?php
/**
 * Builds the Member Loan App (PWA) icons from the cooperative logo.
 * Usage: php tools/memberapp_icons.php [path/to/logo.png]
 * Output: media/memberapp/icons/
 */
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

$root = dirname(__DIR__);
$src = isset($argv[1]) ? $argv[1] : $root . '/logo/1761642549tapstemco-logo.png';
$out = $root . '/media/memberapp/icons';
if (!is_dir($out)) {
    mkdir($out, 0775, true);
}
$logo = imagecreatefromstring(file_get_contents($src));
if (!$logo) {
    fwrite(STDERR, "Cannot read $src\n");
    exit(1);
}

/** @param int[]|null $bg RGB or null for transparent; $scale = logo size / canvas */
function make_icon($logo, $size, $bg, $scale, $file) {
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    $fill = $bg === null
        ? imagecolorallocatealpha($im, 0, 0, 0, 127)
        : imagecolorallocate($im, $bg[0], $bg[1], $bg[2]);
    imagefilledrectangle($im, 0, 0, $size, $size, $fill);
    imagealphablending($im, true);

    $w = imagesx($logo);
    $h = imagesy($logo);
    $target = (int) round($size * $scale);
    $ratio = min($target / $w, $target / $h);
    $dw = (int) round($w * $ratio);
    $dh = (int) round($h * $ratio);
    imagecopyresampled($im, $logo, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), 0, 0, $dw, $dh, $w, $h);
    imagepng($im, $file, 9);
    imagedestroy($im);
    echo "wrote $file\n";
}

$green = array(15, 107, 58);
make_icon($logo, 192, null, 1.0, "$out/icon-192.png");
make_icon($logo, 512, null, 1.0, "$out/icon-512.png");
make_icon($logo, 512, $green, 0.72, "$out/maskable-512.png");
make_icon($logo, 192, $green, 0.72, "$out/maskable-192.png");
make_icon($logo, 180, array(255, 255, 255), 0.86, "$out/apple-touch-icon.png");
make_icon($logo, 32, null, 1.0, "$out/favicon-32.png");
make_icon($logo, 96, null, 1.0, "$out/shortcut-96.png");
