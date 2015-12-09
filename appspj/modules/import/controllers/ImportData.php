<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of Class ImportData
 * Controller for manipulation data from xls file where become to update database record
 * @version 1.0
 * @author Guntar 15/11/2013
 */
class ImportData extends MX_Controller {    	
	function __construct() {
        parent::__construct();		
		$this->load->helper('url');
		$this->load->library('session');
		$this->load->library('grocery_CRUD');
		$this->load->library('excel');
		$this->load->helper('date_format_helper');
		$this->load->library('pagination');
		$this->load->model('ModelsImport','mi',TRUE);				
		$this->output->enable_profiler(FALSE);
  	}
	function _import_output($output = null) {
		$data = array("title"=>"BBPPT | Import",
					  "judul"=>"Import Data");					  
		$this->load->view('header',$data);
		$this->load->view('main',$data);			
		$this->load->view('ViewImport',$output);	
		$this->load->view('footer',$data);	
	}	
	function index() {
		$error = "";		
		$this->_import_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));		
		$username = $this->session->set_userdata('username');		
	}	
	function valueToRupiah($value, $row) { return 'Rp. '.strrev(implode('.',str_split(strrev(strval($value)),3))); }	
	function get_objReader() {        
        $objReader = PHPExcel_IOFactory::createReader('Excel5');
		return $objReader;
    }    
    function get_objWorksheet($uploadpath,$sheetname) {
        $objReader = $this->get_objReader();        
        $objReader->setReadDataOnly(true);
        $objReader->setLoadSheetsOnly(array($sheetname));        
        $objPHPExcel = $objReader->load($uploadpath);
        $objWorksheet = $objPHPExcel->getActiveSheet();        
        return $objWorksheet;
    }    
    function get_objWorksheets($uploadpath,$sheetname,$idx) {
        $objReader = $this->get_objReader();        
        $objReader->setReadDataOnly(true);
        $objReader->setLoadSheetsOnly($sheetname);        
        $objPHPExcel = $objReader->load($uploadpath);
        $objWorksheet = $objPHPExcel->getActiveSheet($idx);        
        return $objWorksheet;
    }		
	function upload(){        		                
        $data = array("title"=>"BBPPT | SPJ");		        
       	//$tabel_name = trim($this->input->post("tabel_name"));
		$userfile	= $this->input->post("userfile");               
        if($userfile == NULL) {           
        	$error_tmp = "ADA KESALAHAN SILAHKAN ULANGI";
			echo $error_tmp;
        }else{ $this->uploading(); }                        
    }       
    function uploading() {   
	    $new_name = 'Import_sbu_all_'.date('d').' '.month(date('m')).' '.date('Y');		
        $config['upload_path'] 	= './upload/';
        $config['allowed_types']= 'xls|xlsx';
		$config['file_name']	= $new_name;		
        $this->load->library('upload', $config);            
        //uploading file to directory upload
        if (!$this->upload->do_upload()) {
            $error	= array('error' => $this->upload->display_errors());
			$data 	= array("title"=>"SPJ | Import",
					     	"judul"=>"Import Data Error");					     				
			redirect('import',$error);		
        }else{
            $res = array('upload_data' => $this->upload->data());
            $filename = $res['upload_data']['file_name'];            
            $file_ext = $res['upload_data']['file_ext'];           
            //print_r($this->upload->data());
			// application/vnd.ms-excel xls
			// application/vnd.openxmlformats-officedocument.spreadsheetml.sheet xlsx			          
            if($file_ext == ".xls") { $objReader = PHPExcel_IOFactory::createReader('Excel5'); }
			else if($file_ext == ".xlsx") { $objReader = PHPExcel_IOFactory::createReader('Excel2007'); }
			else if($file_ext == ".ods") { $objReader = PHPExcel_IOFactory::createReader('OOCalc');	} //for Libre Office
			else redirect('home','refresh');
					
            $uploadpath = "./upload/".$filename;			
                $objReader->setReadDataOnly(TRUE);
               	$objPHPExcel = $objReader->load($uploadpath);				
				$this->showXls();
                /**$data = array("title"=>"SDPPI | Import SBU",
							  "judul"=>"Import Telah Sukses",
							  "file"=>$this->input->post($filename));
				
				$this->load->view('header',$data);
                $this->load->view('main',$data);                
                $this->load->view('v_xls',$data);   
                $this->load->view('footer',$data);**/
		}
	}	
	function readXls() {
		$inputFileName = './upload/update_sbu_all 2012.xlsx';  
		//$inputFileName = './upload/sbu_pesawat.xls';
		$inputFileType = PHPExcel_IOFactory::identify($inputFileName);  
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);  
		$objReader->setReadDataOnly(true); 		
		
		/**  Load $inputFileName to a PHPExcel Object  **/  
		$objPHPExcel = $objReader->load($inputFileName);  
		$sheetName = $objPHPExcel->getActiveSheet()->getTitle();		
		$total_sheets=$objPHPExcel->getSheetCount();	
		//print_r($total_sheets);		
		$allSheetName 		= $objPHPExcel->getSheetNames();  
		$objWorksheet 		= $objPHPExcel->setActiveSheetIndex(0); // first sheet  
		$highestRow 		= $objWorksheet->getHighestRow();   
		$highestColumn 		= $objWorksheet->getHighestColumn();   
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);		
		echo "<pre>";
			print_r($allSheetName);
		echo "</pre>";
		
		print_r("Sheet ".$sheetName);
		echo "<pre>";
			print_r("Jumlah Rows adalah : ".$highestRow." Row.");
			echo "</br>";
			print_r("Jumlah Column adalah : ".$highestColumnIndex." Column.");
		echo "</pre>";		
        $arr_data = array();
		for ($row = 1; $row <= $highestRow; ++$row) {  
    		for ($col = 0; $col < $highestColumnIndex; ++$col) {  
    			$value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();			  
        			if(is_array($arr_data) ) { $arr_data[$row][$col+1] = $value; }  
    		}  
		}		
		echo "<pre>";
			print_r($arr_data);
		echo "</pre>";		
	}	
	function saveXls() {				        
        $filename = $this->input->post('filename');        
        if($filename == ''){
	          redirect('import');
        }
		        
        $ufn = './uploads/'.$filename;
        $table_name = $this->input->post("table_name");
		        
        $seq_number = str_pad($this->dp->sequence_number(), 5, '0', STR_PAD_LEFT);
        
        $file_xls = date("Ym").$seq_number;

		/** SBU Pesawat **/
        $objWorksheet = $this->get_objWorksheet($ufn,"SBU Pesawat");
        $highestColumn = $objWorksheet->getHighestColumn();
        $colNumber = PHPExcel_Cell::columnIndexFromString($highestColumn);

        $posisi = array();
        for($_ii=0;$_ii<$colNumber;$_ii++){
            $r = $objWorksheet->getCell(PHPExcel_Cell::stringFromColumnIndex($_ii)."1")->getValue();
            @$posisi["$r"]=PHPExcel_Cell::stringFromColumnIndex($_ii);
        }

        $highestRow = $objWorksheet->getHighestRow();
			        
        $sbu_pesawat = array();        
		
        for($row = 2; $row <= $highestRow; $row++){			
            $_sbu_pesawat = array(		                    
							'id'=>$objWorksheet->getCell($posisi['id'].$row)->getValue(),
							'asal'=>$objWorksheet->getCell($posisi['asal'].$row)->getValue(),
							'tujuan'=>$objWorksheet->getCell($posisi['tujuan'].$row)->getValue(),
							'bisnis'=>$objWorksheet->getCell($posisi['bisnis'].$row)->getValue(),
							'ekonomi'=>$objWorksheet->getCell($posisi['ekonomi'].$row)->getValue()
			);           
        }
		
        /* Insert SBU Pesawat*/        
        $this->db->insert('sbu_pesawat',$_sbu_pesawat);
		
		echo "Sukses";        
   	}
	function importXls(){		
		$this->uri->segment(3);
		$this->load->library('excel');
		$this->load->library('PHPExcel/iofactory');
		error_reporting(E_ALL ^ E_NOTICE);
			/*
			for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
				for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
					echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
				}
					echo "\n";
			*/	
		$objPHPExcel = PHPExcel_IOFactory::load('./upload/update_sbu_all 2012.xls');
			foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			    $worksheetTitle     = $worksheet->getTitle();
			    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
			    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
			    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			    $nrColumns = ord($highestColumn) - 64;
			    echo "<br>The worksheet ".$worksheetTitle." has ";
			    echo $nrColumns . ' columns (A-' . $highestColumn . ') ';
			    echo ' and ' . $highestRow . ' row.';
			    echo '<br>Data: <table border="1"><tr>';
			    for ($row = 1; $row <= $highestRow; ++ $row) {
			        echo '<tr>';
			        for ($col = 0; $col < $highestColumnIndex; ++ $col) {
			            $cell = $worksheet->getCellByColumnAndRow($col, $row);
			            $val = $cell->getValue();
			            $dataType = PHPExcel_Cell_DataType::dataTypeForValue($val);
			            echo '<td>' . $val . '<br>(Typ ' . $dataType . ')</td>';
			        }
			        echo '</tr>';
			    }
			echo '</table>';
		}			
	}
	function showXls() {
		$data = array("title"=>'Show Content',
					  "file"=>'update_sbu_all 2012.xls');
		$this->load->view('header',$data);	
		$this->load->view('main',$data);
		$this->load->view('ViewXls',$data);
		$this->load->view('footer',$data);		
	}	
	function viewXlsSbuPesawat() {
        //$this->output->cache(5);
        $path = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['SCRIPT_NAME']);
        //$filename = $this->input->("filename",TRUE);
		$filename = 'update_sbu_all 2012.xls'; 
       	$uploadpath = "./upload/".$filename;
        $sheetname = "SBU Pesawat";
        $objWorksheet = $this->get_objWorksheet($uploadpath,$sheetname);
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn();	
       	$colNumber = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $posisi = array();
        for($_ii=0;$_ii<$colNumber;$_ii++){
            $r = $objWorksheet->getCell(PHPExcel_Cell::stringFromColumnIndex($_ii)."1")->getValue();
            @$posisi["$r"]=PHPExcel_Cell::stringFromColumnIndex($_ii);
        }		
		$config['base_url'] = base_url() . "import/showXls";
		$config['total_rows'] = $highestRow ;
        $config['per_page'] = 20;
        $config['uri_segment'] = 3;
		$config['num_links'] = 2;
		$config['use_page_numbers'] = TRUE;		
		$config['display_pages'] = TRUE;		
		$this->pagination->initialize($config);					
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;       
        $data = array("highestRow"=>$highestRow,
                      "objWorksheet"=>$objWorksheet,
                      "posisi"=>$posisi,                      
                      "links"=> $this->pagination->create_links()
                      );					  
        $this->load->view('XlsSbuPesawat',$data);
	}	
	function viewXlsSbuPenginapan() {
		$this->output->cache(5);		
		$path = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['SCRIPT_NAME']);
        //$filename = $this->uri->segment(4);
        $filename = 'update_sbu_all 2012.xls'; 
        $uploadpath = "./upload/".$filename;
        $sheetname = "SBU Penginapan";		
		$objWorksheet = $this->get_objWorksheet($uploadpath,$sheetname);
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn();		
        $colNumber = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $posisi = array();
        for($_ii=0;$_ii<$colNumber;$_ii++){
            $r = $objWorksheet->getCell(PHPExcel_Cell::stringFromColumnIndex($_ii)."1")->getValue();
            @$posisi["$r"]=PHPExcel_Cell::stringFromColumnIndex($_ii);
		}		
		$config['base_url'] = base_url() . "import/showXls";
		$config['total_rows'] = $highestRow ;
        $config['per_page'] = 20;
        $config['uri_segment'] = 3;
		$config['num_links'] = 2;
		$config['use_page_numbers'] = TRUE;		
		$config['display_pages'] = TRUE;		
		$this->pagination->initialize($config);		
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;       
        $data = array("highestRow"=>$highestRow,
                      "objWorksheet"=>$objWorksheet,
                      "posisi"=>$posisi,                      
                      "links"=> $this->pagination->create_links()
					 );					  					  
        $this->load->view('XlsSbuPenginapan',$data);
	}	
	function viewXlsSbuUangSaku() {
		//$this->output->cache(5);		
		$path = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['SCRIPT_NAME']);
        //$filename = $this->uri->segment(4);
		$filename = 'update_sbu_all 2012.xls'; 
        $uploadpath = "./upload/".$filename;
        $sheetname = "SBU Uang Saku";		
		$objWorksheet = $this->get_objWorksheet($uploadpath,$sheetname);
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn(); 
        $colNumber = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $posisi = array();
        for($_ii=0;$_ii<$colNumber;$_ii++){
            $r = $objWorksheet->getCell(PHPExcel_Cell::stringFromColumnIndex($_ii)."1")->getValue();
            @$posisi["$r"]=PHPExcel_Cell::stringFromColumnIndex($_ii);
        }		
		$config['base_url'] = base_url() . "import/showXls";
		$config['total_rows'] = $highestRow ;
        $config['per_page'] = 20;
        $config['uri_segment'] = 3;	
		$config['num_links'] = 2;
		$config['use_page_numbers'] = TRUE;		
		$config['display_pages'] = TRUE;		
		$this->pagination->initialize($config);			
		$data = array("highestRow"=>$highestRow,
                      "objWorksheet"=>$objWorksheet,
                      "posisi"=>$posisi,                      
                      "links"=> $this->pagination->create_links()
                      );		
		$this->load->view('XlsSbuUangSaku',$data);
	}	
	function viewXlsSbuTaxi() {
		//$this->output->cache(5);
		$path = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['SCRIPT_NAME']);
        //$filename = $this->uri->segment(4);
        $filename = 'update_sbu_all 2012.xls'; 
        $uploadpath = "./upload/".$filename;
        $sheetname = "SBU Taxi";		
		$objWorksheet = $this->get_objWorksheet($uploadpath,$sheetname);
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn(); 		
        $colNumber = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $posisi = array();
        for($_ii=0;$_ii<$colNumber;$_ii++){
            $r = $objWorksheet->getCell(PHPExcel_Cell::stringFromColumnIndex($_ii)."1")->getValue();
            @$posisi["$r"]=PHPExcel_Cell::stringFromColumnIndex($_ii);
        }
		$config['base_url'] = base_url() . "import/showXls";
		$config['total_rows'] = $highestRow ;
        $config['per_page'] = 20;
        $config['uri_segment'] = 3;
		$config['num_links'] = 2;
		$config['use_page_numbers'] = TRUE;		
		$config['display_pages'] = TRUE;		
		$this->pagination->initialize($config);				
		$data = array("highestRow"=>$highestRow,
                      "objWorksheet"=>$objWorksheet,
                      "posisi"=>$posisi,                      
                      "links"=> $this->pagination->create_links()
                      ); 		
		$this->load->view('XlsSbuTaxi',$data);
	}
}
/* End of file ImportData.php */
/* Location: ./appspj/modules/import/controllers/ImportData.php */