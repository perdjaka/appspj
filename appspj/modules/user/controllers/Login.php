<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of Class Login
 * @author Guntar 30/10/2013
 */ 
class Login extends MX_Controller {	
	public function __construct() {
        parent::__construct();
    } 	
	public function index() {
		$this->load->library('form_validation');		
		$user_data = array();		
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');		 		
		$username = $this->input->post('username',TRUE);
        $password = $this->input->post('password',TRUE);
		$this->load->model('user/ModelUser','muser',TRUE);		
		$query = $this->muser->login_user($username,$password);			
        if($query->num_rows() > 0) {            
           /*	$obj =& get_instance();
			
			$q = $obj->db->query("SELECT * FROM user US WHERE US.user_name = '".$username."' AND US.user_password= '"bdjCgzaC1MQnzD1BFnwbdCeJC8ezAXvXfsOrc3W16bG8GjEfONx73jhLX2z60RHu2DQjzXU3PO2Y5rxYJr2Tow=="'");			
			$this->db->$q->result();
			
			if($q->num_rows() > 0){$r = $q->row();
	            echo '<div class="footer">';
			
	            echo 'Welcome '.$r->user_name;	        
	            echo '</div>';
        	}else{
            echo ' ';
        	}*/						
          	$user_data = array(	'username'=>$username,            			  
						  		'logged_in'=>TRUE);            
           	$this->session->set_userdata($user_data);			
			$user_data['message']='correct username or password'; 
			redirect('master/staff',$user_data);						
        }else{
            $data['message']='incorrect username or password';            
            $this->session->set_flashdata('message','incorrect username or password');
			redirect('home', $data);			
       }			
	}
}
/* End of file Login.php */
/* Location: ./appspj/modules/user/controllers/Login.php */