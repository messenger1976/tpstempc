<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/** PHPExcel */
/**
*   Load phpExcle library to mutch with Codeigniter
 * @author Miltone Urassa
 * @Contact miltoneurassa@yahoo.com
*/
class Excel
{
    
    function __construct()
    {
        require_once APPPATH.'/libraries/excel/PHPExcel.php';
        require_once APPPATH.'/libraries/excel/PHPExcel/IOFactory.php';
    }
} 
?>