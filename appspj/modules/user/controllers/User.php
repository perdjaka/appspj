<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of Class User
 * @author Guntar 30/10/2013
 */
class User extends MX_Controller {    	
	public function __construct() {
        parent::__construct();		
		$this->load->model('ModelUser','muser',TRUE);
  	}	
	public function index(){		
		//$this->load->library('session');
		$data = array('title' => 'BBPPT | User');        		
		$this->load->view('header',$data);
		$this->load->view('main',$data);		
		$this->load->view('ViewUser',$data);
		$this->load->view('footer',$data);
	}
}
/* End of file User.php */
/* Location: ./appspj/modules/user/controllers/User.php */