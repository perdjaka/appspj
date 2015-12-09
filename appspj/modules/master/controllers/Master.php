<?php if (!defined('BASEPATH'))	exit('No direct script access allowed');
/**
 * Description Class of Master
 * @version 1.0
 * @author Guntar 4/09/2013
 */
class Master extends MX_Controller {
	function __construct() {
		parent::__construct();		
		$this -> load -> library('grocery_CRUD');		
		$this -> load -> model('ModelMaster','mm',TRUE);
		$this -> load -> helper('date_format_helper');
		$this -> output -> enable_profiler(FALSE);
	}
	public function _master_output($output = NULL, $judul = NULL) {
		$data = array('title' => 'BBPPT | Master', 
					  'userdata' => $this -> session -> userdata('username'),
					  'judul'	=> $judul);
		$this -> load -> view('header', $data);
		$this -> load -> view('main', $data);
		$this -> load -> view('ViewMaster.php', $output);
		$this -> load -> view('footer', $data);
	}
	function index() {
		/*if($this->session->userdata('logged_in') == ''){ redirect('home',TRUE); }
		$this -> _master_output((object) array('output' => '', 'js_files' => array(), 'css_files' => array()));*/
		$this -> staff();
	}
	function pangkat() {
		try {
			$crud = new grocery_CRUD();			
			$crud -> set_theme('datatables')//twitter-bootstrap
				  -> set_table('pangkat')
				  -> set_subject('Pangkat')				 				  
				  -> required_fields('golongan','pangkat')
				  -> columns('golongan', 'pangkat')
				  -> unset_export()
				  -> unset_print()
				  -> unset_delete()
				  -> unset_add();			
			$judul = "List Pangkat";
			$output = $crud -> render();
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function kota() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_relation('provinsi', 'provinsi', '{nama_provinsi}')
				  -> set_table('kota')
				  -> order_by('id')
				  -> set_subject('Kota')
				  -> required_fields('id')
				  -> columns('id', 'kota', 'provinsi')
				  -> display_as('id', 'ID')				  
				  -> unset_print()
				  -> unset_delete();
			$output = $crud -> render();
			$judul = "List Kota ";
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function provinsi() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_table('provinsi')
				  -> order_by('nama_provinsi','DESC')				  
				  -> set_subject('Provinsi')
				  -> required_fields('id')
				  -> required_fields('nama_provinsi')
				  -> columns('nama_provinsi')				  
				  -> display_as('nama_provinsi', 'Nama Provinsi')
				  -> unset_print()
				  -> unset_delete();
			$output = $crud -> render();
			$judul = "List Provinsi";
			$this -> _master_output($output);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function sbuPenginapan() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_relation('provinsi', 'provinsi', '{nama_provinsi}')
				  -> set_table('sbu_penginapan')
				  -> set_subject('SBU Penginapan')
				  -> columns('provinsi', 'suite', 'star4', 'star3', 'star2', 'star1')
				  -> display_as('star4', 'Star 4')
				  -> display_as('star3', 'Star 3')
				  -> display_as('star2', 'Star 2')
				  -> display_as('star1', 'Star 1')
				  -> callback_column('suite', array($this, 'valueToRupiah'))				  
				  -> callback_column('star4', array($this, 'valueToRupiah'))
				  -> callback_column('star3', array($this, 'valueToRupiah'))
				  -> callback_column('star2', array($this, 'valueToRupiah'))
				  -> callback_column('star1', array($this, 'valueToRupiah'))
				  -> unset_print()
				  -> unset_delete();
			$output = $crud -> render();
			$judul = "List SBU Penginapan";
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function sbuTaxi() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_table('sbu_taxi')
				  -> set_relation('provinsi', 'provinsi', '{nama_provinsi}')
				  -> set_subject('SBU Taxi')
				  -> columns('provinsi', 'taxi')
				  -> display_as('provinsi', 'Provinsi')
				  -> callback_column('taxi', array($this, 'valueToRupiah'))
				  -> unset_print()
				  -> unset_delete();
			$output = $crud -> render();
			$judul = "List SBU Taxi";
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function sbuPesawat() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_relation('asal', 'kota', '{kota}')
				  -> set_relation('tujuan', 'kota', '{kota}')
				  -> set_table('sbu_pesawat')
				  -> set_subject('SBU Pesawat')
				  -> columns('asal', 'tujuan', 'bisnis', 'ekonomi')
				  -> display_as('asal', 'Departure City')
				  -> display_as('tujuan', 'Destination City')
				  -> display_as('ekonomi', 'Economy Class')
				  -> display_as('bisnis', 'Bussiness Class')
				  -> callback_column('bisnis', array($this, 'valueToRupiah'))
				  -> callback_column('ekonomi', array($this, 'valueToRupiah'))
				  -> unset_print()
				  -> unset_delete();
			$output = $crud -> render();
			$judul = "List SBU Pesawat";
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function staff() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_table('staff')
				  -> set_subject('Staff SDPPI')
				  -> set_relation('id_subdit','subdit','{subdit}')
				  //-> set_relation('id_eselon','eselon','{eselon}')
				  -> set_relation('golongan','pangkat','{golongan}')
				  -> columns('nama', 'golongan', 'nip', 'jabatan','id_subdit')
				  -> field_type('id_eselon','hidden')
				  -> display_as('nip', 'NIP')
				  -> display_as('nama', 'Nama Pegawai')
				  -> display_as('id_subdit', 'Sub Direktorat')
				  -> unset_export()
				  -> unset_print()
				  -> unset_delete();
			$judul = 'Staff Management';			
			$output = $crud -> render();
			$this -> _master_output($output, $judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function sbuUangSaku() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_relation('provinsi', 'provinsi', '{nama_provinsi}')
				  -> set_table('sbu_uang_saku')
				  -> set_subject('SBU Uang Saku')
				  -> columns('provinsi', 'fullboard_luar', 'fullboard_dalam', 'fullday_dalam', 'uang_saku_murni')		
				  -> callback_column('fullboard_luar', array($this, 'valueToRupiah'))
				  -> callback_column('fullboard_dalam', array($this, 'valueToRupiah'))
				  -> callback_column('fullday_dalam', array($this, 'valueToRupiah'))
				  -> callback_column('uang_saku_murni', array($this, 'valueToRupiah'))				  
				  -> display_as('fullboard_luar', 'Fullboard Luar')				  
				  -> display_as('fullboard_dalam', 'Fullboard Dalam')				  
				  -> display_as('fullday_dalam', 'Fullday Dalam')
				  -> display_as('uang_saku_murni', 'Uang Saku Murni')				  
				  -> unset_print()
				  -> unset_delete()				  
				  -> unset_read();				  
			$output = $crud -> render();
			$judul = 'List SBU Uang Saku';
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function dinas() {
		try {
			$today= date('Y-m-d h:i:s');
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables') 
				  -> set_table('dinas')
				  -> set_subject('Dinas SDPPI')
				  -> set_relation('kota_asal', 'kota', '{kota}')
				  -> set_relation('kota_tujuan', 'kota', '{kota}')
				  -> set_relation('id_subdit', 'subdit', '{subdit}')
				  -> set_relation('nota_dinas', 'nota_dinas', '{nomor}')
				  -> set_relation('nota_dinas_1', 'jenis_surat', '{nomor}')
				  -> columns('maksud', 'berangkat', 'kembali','id_subdit','kota_tujuan')
				  -> required_fields('maksud')
				  -> field_type('create_date', 'hidden',$today)
				  -> field_type('update_date', 'hidden',$today)
				  -> display_as('maksud', 'Maksud Dinas')
				  -> display_as('berangkat', 'Berangkat')
				  -> display_as('kembali', 'Kembali')
				  -> display_as('id_subdit', 'Subdit Kegiatan')
				  -> display_as('nota_dinas_1','Nota Extra')
				  -> display_as('nota_dinas','Nota Dinas')
				  -> display_as('kota_asal', 'Departure')
				  -> display_as('kota_tujuan', 'Destination')
				  -> order_by('id','DESC')
				  -> callback_column('kembali', array($this, 'day'))
				  -> callback_column('berangkat', array($this, 'day'))
				  -> callback_before_insert(array($this,'callback_insert_date'))
				  -> callback_before_update(array($this,'callback_update_date'));				  				  
			$output= $crud -> render();	
			$judul = 'List Dinas';		
			$this->_master_output($output,$judul);			
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function callback_insert_date($post_array) {		
	 	$post_array['create_date']  = date('Y-m-d h:i:s');
		$post_array['updated_date'] = date('Y-m-d h:i:s');	 
	  return $post_array;
	}
	function callback_update_date($post_array) {
		$post_array['updated_date'] = date('Y-m-d h:i:s');	 
	  return $post_array;
	}
	function kegiatan() {
		try {
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_table('kegiatan')
				  -> set_subject('Kegiatan')
				  -> set_relation('id_subdit','subdit','{subdit}')
				  -> fields('nama_kegiatan', 'pagu', 'tahun','id_subdit')
				  -> required_fields('nama_kegiatan','tahun')
				  -> display_as('nama_kegiatan', 'Nama Kegiatan')
				  -> display_as('id_subdit', 'Sub Direktorat')
				  -> unset_export()
				  -> unset_print();
			$output = $crud -> render();
			$judul = 'List Kegiatan';
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}	
	/**   
	 * Description relation n_n function on grocery crud
	 * set_relation_n_n($field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table, 
	 *			 $primary_key_alias_to_selection_table , $title_field_selection_table , 
	 *			 $priority_field_relation_table = null, $where_clause = null)
	**/		
	function perjalanan() {
		try {			
			$status = 'Declined';
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_table('perjalanan_multi')				  
				  -> set_subject('Perjalanan Dinas Multi Personel')			
				  -> set_relation('dinas', 'dinas', '{maksud}')
				  //-> set_relation('kegiatan', 'kegiatan', '{nama_kegiatan}') // not_use now
				  -> set_relation('tiket1', 'v_tiket', '{dinas}')
				  -> set_primary_key('id', 'v_tiket')				 			   
				  -> set_relation_n_n('personel','perjalanan_multi_detail','staff', 'id_perjalanan', 'personil','nama')
				  -> where('status LIKE','%Declined%')
				  -> order_by('id','desc')	  
				  -> columns('dinas','personel','status','tiket1')
				  -> required_fields('dinas','uang_saku')  
				  -> field_type('create_date', 'hidden')
				  -> field_type('update_date', 'hidden')
				  -> field_type('status', 'hidden',$status) //http://www.grocerycrud.com/documentation/options_functions/field_type				  
				  -> field_type('tgl_approval', 'hidden')				  
				  -> field_type('kegiatan', 'hidden')
				  -> field_type('airport_tax_tujuan')
            //,'hidden')
				  -> field_type('airport_tax_asal')//, 'hidden')
				  -> display_as('tiket1', 'Tiket')
				  -> display_as('tiket_manual', 'Nominal Manual Tiket')
				  -> display_as('tiket2', 'Non Pesawat')
				  -> display_as('type1', 'Type')				  
				  -> display_as('tgl_spt', 'Tanggal SPT')				  
				  -> display_as('uang_saku', 'Jenis Uang Saku')				
				  -> display_as('airport_tax_tujuan', ' Airport Tax Kota Tujuan')
				  -> display_as('airport_tax_asal', ' Airport Tax Kota Asal')
				  -> add_action('Approve','../assets/images/approved.png','report/approveSPJ')
				  -> add_action('Review','../assets/images/pdf.png','report/cetakReviewSppt')
				  -> callback_column('tgl_approval', array($this, 'day'))
				  -> callback_column('tgl_spt', array($this, 'day'))				  
				  //-> unset_delete()
				  -> unset_print()
				  -> unset_export();							  						
			$output = $crud -> render();
			$judul = 'List Perjalanan ';
			$this->_master_output($output,$judul);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}	
	function valueToRupiah($value, $row) { return 'Rp. ' . strrev(implode('.', str_split(strrev(strval($value)), 3))); }
	function day($date)	{
		$change = gmdate($date,time()+60*60*8);
		$split 	= explode("-",$change,3);
		$date 	= $split[2];
		$month 	= month($split[1]);
		$year 	= $split[0];		
		return $date.' '.$month.' '.$year;
	}
	function month($month)
	{
		switch ($month)
		{
			case 1:
				return "Januari";
				break;
			case 2:
				return "Februari";
				break;
			case 3:
				return "Maret";
				break;
			case 4:
				return "April";
				break;
			case 5:
				return "Mei";
				break;
			case 6:
				return "Juni";
				break;
			case 7:
				return "Juli";
				break;
			case 8:
				return "Agustus";
				break;
			case 9:
				return "September";
				break;
			case 10:
				return "Oktober";
				break;
			case 11:
				return "November";
				break;
			case 12:
				return "Desember";
				break;
		}
	}   
   function day_name($date) {
		$change = gmdate($date, time() + 60 * 60 * 8);
		$split = explode("-",$change);
		$date = $split[2];
		$month = $split[1];
		$year = $split[0];

		$name = date("l", mktime(0,0,0,$month,$date,$year));
		$name_of_day = "";
			 if($name == "Sunday") 		{$name_of_day="Minggu";}
		else if($name == "Monday") 		{$name_of_day = "Senin";}
		else if($name == "Tuesday") 	{$name_of_day = "Selasa";}
		else if($name == "Wednesday") 	{$name_of_day = "Rabu";}
		else if($name == "Thursday") 	{$name_of_day = "Kamis";}
		else if($name == "Friday") 		{$name_of_day = "Jumat";}
		else if($name == "Saturday") 	{$name_of_day = "Sabtu";}
		
		return $name_of_day;
	}
}
/* End of file Master.php */
/* Location: ./appspj/modules/master/controllers/Master.php */