<?php if (!defined('BASEPATH'))	exit('No direct script access allowed');
/**
 * Description of Class SubReport
 * View Report Directed to page where list report for create report as pdf file.
 * @version 1.0
 * @author Guntar 2/11/2013
 */
class SubReport extends MX_Controller {
	function __construct() {
		parent::__construct();
		$userdata = $this->session->userdata('logged_in');
		$this -> load -> library('grocery_CRUD');		
		$this -> output -> enable_profiler(FALSE);
		$this->load->model('report/ModelReport','mr',TRUE);
	}
	public function _sub_report_output($output = NULL, $judul = NULL) {
		$data = array('title' => 'BBPPT | Sub Report', 
					  'userdata'=> $this -> session -> userdata('username'),
					  'kegiatan'=> $this -> mr -> getMaksud(),
					  'kota' 	=> $this -> mr -> getKotaTujuan(),
					  'hari' 	=> $this -> mr -> getTotalHariDinas(),
					  'jumlah'	=> $this -> mr -> getCountRowsSpj(),
					  'id'		=> $this->uri->segment(3)
					  );
		$this -> load -> view('header', $data);
		$this -> load -> view('main', $data);		
		$rows = $this -> mr -> getCountRowsSpj();		
			if($rows == 1 || $rows == 0)
				{ $this -> load -> view('ViewReportSingle.php', $output); }			
			else 
				{ $this -> load -> view('ViewReportMulti.php', $output); }		
		$this -> load -> view('footer', $data);
	}
	function index() {		
		$id = $this->uri->segment(3);		
		$this -> mr -> setIdPerjalanan($id);
		$this -> isSingleOrMulti();
	}	
	function isSingleOrMulti() {
		$rows = $this -> mr -> getCountRowsSpj();		
			if($rows == 1 || $rows == 0)
				$this -> viewSingleSubReport();			
			else 
				$this -> viewMultiSubReport();
	}
	function viewSingleSubReport() {
		$id = $this->uri->segment(3);
		$crudViewSingleSubReport   = new grocery_CRUD();				
		$crudViewSingleSubReport  -> set_theme('datatables')
								  -> set_table('v_list_spj')
								  -> set_primary_key('ID', 'v_list_spj')
								  -> set_subject('List Sub Report SPJ')
								  -> where('id_perjalanan =' ,$id)				  
								  -> columns('ID','Nama_Staff', 'Nomor_SPT', 'Tanggal_Berangkat','Tanggal_Kembali','Subdit_Kegiatan')
								  -> add_action('SPPD','../../assets/images/pdf.png','report/cetakSppd')
								  -> add_action('Kwitansi','../../assets/images/pdf.png','report/cetakKuitansi')
								  -> add_action('SPT','../../assets/images/pdf.png','report/cetakSpj')
								  -> callback_column('Tanggal_Berangkat', array($this, 'day'))
				  				  -> callback_column('Tanggal_Kembali', array($this, 'day'))
								  -> unset_export()
								  -> unset_delete()
								  -> unset_add()
								  -> unset_edit()
								  -> unset_read()
								  -> unset_print();		
		$outputViewSingleSubReport = $crudViewSingleSubReport -> render();
		$this -> _sub_report_output($outputViewSingleSubReport);	
	}
	function viewMultiSubReport() {
		$id = $this->uri->segment(3);
		$crudViewMultiSubReport    = new grocery_CRUD();						
		$crudViewMultiSubReport   -> set_theme('datatables')
								  -> set_table('v_list_spj')
								  -> set_primary_key('ID', 'v_list_spj')
								  -> set_subject('List Sub Report SPJ')
								  -> where('id_perjalanan =' ,$id)				  
								  -> columns('ID','Nama_Staff', 'Nomor_SPT', 'Tanggal_Berangkat','Tanggal_Kembali','Subdit_Kegiatan')
								  -> add_action('SPPD','../../assets/images/pdf.png','report/cetakSppd')
								  -> add_action('Kwitansi','../../assets/images/pdf.png','report/cetakKuitansi')
								  -> callback_column('Tanggal_Berangkat', array($this, 'day'))
				  				  -> callback_column('Tanggal_Kembali', array($this, 'day'))					  				  
								  -> unset_export()
								  -> unset_delete()
								  -> unset_add()
								  -> unset_edit()
								  -> unset_read()
								  //-> unset_print()		
		$outputViewMultiSubReport = $crudViewMultiSubReport -> render();
		$this -> _sub_report_output($outputViewMultiSubReport);
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
/* End of file SubReport.php */
/* Location: ./appspj/modules/report/controllers/SubReport.php */