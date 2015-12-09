<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 * Class of home.php
 *
 * @author Guntar 25/06/2013
 */

class Home extends CI_Controller {

	public function __construct() {
		parent::__construct();
		//for dev set TRUE
		$this -> output -> enable_profiler(FALSE);
	}

	public function index() {
		$data = array('title' => 'BBPPT | Application SPJ', 'username' => $this -> session -> userdata('username'));

		$this -> load -> view('header', $data);
		$this -> load -> view('main', $data);
		$this -> load -> view('user/login', $data);
		$this -> load -> view('footer', $data);
	}

}

/* End of file home.php */
/* Location: ./appspj/controllers/home.php */
