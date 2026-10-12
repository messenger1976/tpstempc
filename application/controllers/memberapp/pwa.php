<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/Memberapp_controller.php';

/**
 * Installable-app files for the Member Loan App. They are served from
 * /customer_login/ so the service worker's scope covers the whole app.
 */
class Pwa extends Memberapp_controller {

    public function manifest() {
        $base = $this->base_path();
        $app = $base . 'customer_login/app';
        $icons = $base . 'media/memberapp/icons/';
        $manifest = array(
            'id' => $base . 'customer_login/',
            'name' => $this->data['company_name'] . ' Loans',
            'short_name' => 'TAP Loans',
            'description' => 'Check your loans, pay with QR Ph and apply for a loan.',
            'start_url' => $app . '?source=pwa',
            'scope' => $base . 'customer_login/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#0f6b3a',
            'theme_color' => '#0f6b3a',
            'lang' => 'en-PH',
            'categories' => array('finance'),
            'icons' => array(
                array('src' => $icons . 'icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'),
                array('src' => $icons . 'icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'),
                array('src' => $icons . 'maskable-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'),
                array('src' => $icons . 'maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'),
            ),
            'shortcuts' => array(
                array('name' => 'Pay a loan', 'short_name' => 'Pay', 'url' => $app . '?source=shortcut#/pay', 'icons' => array(array('src' => $icons . 'shortcut-96.png', 'sizes' => '96x96'))),
                array('name' => 'My loans', 'short_name' => 'Loans', 'url' => $app . '?source=shortcut#/loans', 'icons' => array(array('src' => $icons . 'shortcut-96.png', 'sizes' => '96x96'))),
                array('name' => 'Apply for a loan', 'short_name' => 'Apply', 'url' => $app . '?source=shortcut#/apply', 'icons' => array(array('src' => $icons . 'shortcut-96.png', 'sizes' => '96x96'))),
            ),
        );
        header('Content-Type: application/manifest+json; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode($manifest, JSON_UNESCAPED_SLASHES);
    }

    public function sw() {
        header('Content-Type: application/javascript; charset=UTF-8');
        header('Cache-Control: no-cache');
        header('Service-Worker-Allowed: ' . $this->base_path() . 'customer_login/');
        $this->load->view('memberapp/pwa/sw_js', array(
            'version' => self::ASSET_VERSION,
            'base' => $this->base_path(),
        ));
    }

    public function offline() {
        header('Cache-Control: public, max-age=86400');
        $this->load->view('memberapp/pwa/offline', $this->data + array('base' => $this->base_path()));
    }

    /** Path part of base_url, e.g. "/" or "/tapstemco/". */
    private function base_path() {
        $path = parse_url(base_url(), PHP_URL_PATH);
        return rtrim($path ? $path : '/', '/') . '/';
    }
}
