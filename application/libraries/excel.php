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
        // Only require PHPExcel.php - it will load the Autoloader
        // The Autoloader will handle loading IOFactory when needed
        if (!class_exists('PHPExcel', false)) {
            require_once APPPATH.'/libraries/excel/PHPExcel.php';
        }
        // Don't explicitly require IOFactory - let autoloader handle it
        // This prevents "class already in use" errors
    }
} 
?>