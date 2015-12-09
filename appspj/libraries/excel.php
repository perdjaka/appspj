<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of excel
 *
 * @author all
 */
require_once APPPATH."/third_party/PHPExcel.php";

class excel extends PHPExcel {
    public function __construct() {
        parent::__construct();
    }
}

?>
