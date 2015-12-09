<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of Class Logout
 * @author Guntar 30/10/2013
 */ 
class Logout extends MX_Controller {		
	public function __construct() { parent::__construct(); } 	
	public function index() {						
		$this->session->unset_userdata();				
		redirect('','refresh');
	}
}
/* End of file Logout.php */
/* Location: ./appspj/modules/user/controllers/Logout.php */