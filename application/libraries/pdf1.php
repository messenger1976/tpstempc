<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class pdf1 {

    function pdf1() {
        $CI = & get_instance();
        log_message('Debug', 'mPDF class is loaded.');
    }

    function load($param = NULL) {
        include_once APPPATH . '/third_party/mpdf/mpdf.php';
 
        if ($param == NULL) {
            $param = 'A4';
        }
        return new mPDF('',$param,'','','10','10',30,10,10,5);
    }

}
