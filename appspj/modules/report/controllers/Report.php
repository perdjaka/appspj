<?php	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of Class Report
 * Generate report SPPD , KUITANSI & SPT to format pdf file
 * @version 1.0
 * @author Guntar 17/07/2013 
 */ 
class Report extends MX_Controller{
 	var $B;
	var $I;
	var $U;
	var $HREF;	
	var $NAMA_DIREKTUR = 'DWI HANDOKO';
	var $NAMA_PLT = 'FARIDA DWI CAHYARINI';
	var $TRANSPORTASI ='Pesawat Udara';
	var $KET_SPJ = 'Setelah melaksanakan tugas agar membuat laporan tertulis';
	var	$KET_SPPD = 'Setelah menjalankan tugas agar membuat Laporan tertulis.';
	var $NAMA_PPK ='Rr. DYAH KUNTI PRATIWI';
	var $NIP_PPK= '1964 0616 1988 03 2003';
	var $NAMA_BENDAHARA = 'DADAN KURNIA';
	var $NIP_BENDAHARA = '1977 0129 2009 12 1001';
	var $REPRESENTATIF = 150000;
	var $today;
		
	function __construct() {
		parent::__construct();		
		$this -> load -> library('grocery_CRUD');
		$this->load->model('report/ModelReport','mr',TRUE);				
		$this->load->model('master/ModelMaster','mm',TRUE);
		$config['upload_path'] = './upload/';
		$config['allowed_types'] = 'pdf';				
		$this->load->library('upload',$config);		
		$this->load->library('fpdf');		
		define('FPDF_FONTPATH',$this->config->item('fonts_path'));
		$this->load->helper('date_format_helper');
		$this->today = date("Y-m-d");
	}
	function setTransportasi($nama){
		$this->TRANSPORTASI = $nama;
	}
	function getTransportasi(){
		return $this->TRANSPORTASI;
	}
	public function _report_output($output = NULL, $info= NULL) {
		$data = array('title' => 'BBPPT | Master', 
					  'userdata' => $this -> session -> userdata('username'));
		$this -> load -> view('header', $data);
		$this -> load -> view('main', $data);
		$this -> load -> view('ViewReport.php', $output);
		$this -> load -> view('footer', $data);
	}
	function index() {
		//if($this->session->userdata('logged_in') == ''){ redirect('home',TRUE); }
		$output = $this -> perjalananDinas();
		//$this -> _report_output((object) array('output' => '', 'js_files' => array(), 'css_files' => array()));
	}
	function cetakReviewSppt(){
		$id = $this->uri->segment(3);
		$this->mr->setIdPerjalanan($id);
		$this -> isSingleOrMulti();		
	}
	function isSingleOrMulti() {
		$rows = $this -> mr -> getCountRowsSpj();		
			if($rows == 1 || $rows == 0)
				$this -> cetakReviewSpj();			
			else 
				$this -> cetakReviewSpjMulti();
	}
	function perjalananDinas() {
		try {
			$this -> load -> model ('master/ModelMaster','mm',TRUE);
			$status = 'Approved';
			$no_spt = '';
			$crud = new grocery_CRUD();
			$crud -> set_theme('datatables')
				  -> set_table('perjalanan_multi')				  
				  -> set_subject('Perjalanan Dinas Multi Personel')			
				  -> set_relation('dinas', 'dinas', '{maksud}')
				  //-> set_relation('kegiatan', 'kegiatan', '{nama_kegiatan}')
				  -> set_relation('tiket1', 'v_tiket', '{dinas}')
				  -> set_primary_key('id', 'v_tiket')				 			   
				  -> set_relation_n_n('personel','perjalanan_multi_detail','staff', 'id_perjalanan', 'personil','nama')
				  -> where('status LIKE','%Approved%')
				  -> order_by('id','desc')	  
				  -> columns('dinas','personel','status','tiket1')
				  -> required_fields('dinas','uang_saku')  
				  -> field_type('status', 'hidden',$status) //http://www.grocerycrud.com/documentation/options_functions/field_type
				  -> field_type('kegiatan', 'hidden','')
				  -> field_type('create_date', 'hidden')
				  -> field_type('update_date', 'hidden')
				  -> field_type('airport_tax_tujuan', 'hidden')
				  -> field_type('airport_tax_asal', 'hidden')
				  -> display_as('tiket1', 'Tiket')
				  -> display_as('tiket_manual', 'Nominal Manual Tiket')
				  -> display_as('tiket2', 'Non Pesawat')
				  -> display_as('type1', 'Type')
				  -> display_as('tgl_spt', 'Tanggal SPT')				  
				  -> display_as('uang_saku', 'Jenis Uang Saku')				
				  -> display_as('airport_tax_tujuan', ' Airport Tax Kota Tujuan')
				  -> display_as('airport_tax_asal', ' Airport Tax Kota Asal')
				  -> add_action('Cetak Surat','assets/images/pdf.png','report/subReport')
				  //-> callback_after_update('personel',array($this,'callback_check_sum_personel_onchange'))
				  -> callback_column('tgl_spt', array($this, 'day'))
				  -> callback_column('tgl_approval', array($this, 'day'))
				  //-> unset_delete()	  
				  -> unset_add()
				  -> unset_print()
				  -> unset_export();
			$output = $crud -> render();
			//$this -> _report_output((object) array('output' => '', 'js_files' => array(), 'css_files' => array()));		
			$this -> _report_output($output);
		} catch(Exception $e) {
			show_error($e -> getMessage() . ' --- ' . $e -> getTraceAsString());
		}
	}
	function callback_check_sum_personel_onchange($post_array,$personel){
		if(!empty($post_array['$personel'])) {
	    	$post_array['$personel'];
		}
		$sql = ("SELECT pmd.no_spt,pmd.id_detail from perjalanan_multi_detail pmd where pmd.id_perjalanan='".$id."'
				AND pmd.no_spt IS NOT NULL");
		$query = $this->db->query($sql);		
		foreach ($query->result() as $row) {
				 $noSptCurrent 	= $row->no_spt;
				 //$id_detail		= $row->id_detail;
		}
		if (!empty($noSptCurrent)){
			return $noSptCurrent;
		}
	}
	function callback_onchange_personel($post_array, $id_perjalanan) {	    
	    if(!empty($post_array['$id_perjalanan'])) {
	    	$post_array['$id_perjalanan'];
		} 
		else { unset($post_array['$id_perjalanan']); }	 
		$this->callback_set_spt_onchange_personel($id_perjalanan);
	return $this->db->update('perjalanan_multi_detail',$post_array,array('no_spt' => $personel));
	}
	function callback_spt_onchange_personel($id){	
		$sql = ("SELECT pmd.no_spt,pmd.id_detail from perjalanan_multi_detail pmd where pmd.id_perjalanan='".$id."'");
		$query = $this->db->query($sql);		
		foreach ($query->result() as $row) {
				 $noSptCurrent 	= $row->no_spt;
				 $id_detail		= $row->id_detail; 
		}
		
		if (count($id_detail) > 1){
			for($i = 0 ;$i < count($noSptCurrent); $i++ ){
				
			}
		}
		return ($noSptCurrent);	
	}
	function approveSPJ(){
		$id = $this->uri->segment(3);
		$this->mr->setIDPerjalanan($id);
		$this->mr->approveSpj();
	}
	function cetakSpj(){
		$id = $this->uri->segment(3);				
		$this->mr->setID($id);				
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		
		//Header
		//$this->fpdf->SetFont('helvetica','',11);
		//$this->fpdf->SetTextColor(5, 76, 143);
		//$this->fpdf->Text(3.5,1.2,'');
		//$this->fpdf->Text(3.5,1.7,'');
		//$this->fpdf->Text(3.5,2.2,'');		
		//$this->fpdf->Image('',3.3,2.4,8.5,0.65,'JPG');				
		//$this->fpdf->SetFont('helvetica','',9);
		//$this->fpdf->Text(3.5,3.4,'');
		//$this->fpdf->Text(9,3.4,'');
		//$this->fpdf->Text(13,3.4,'');
		//$this->fpdf->Text(16.9,3.4,'');
		//$this->fpdf->Text(3.5,3.8,'');
		//$this->fpdf->Text(10.53,3.8,'');
		//$this->fpdf->Text(17.7,3.8,'');		
		//$this->fpdf->Image('',1,0.8,2.3,2.6,'JPG');
		//$this->fpdf->Image('',1,4,19.1,0.2,'PNG');		
		
		//Content
		$sql = ("SELECT	pmd.id_perjalanan,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						k.kota,
						pmd.no_spt,
						pm.tgl_spt,
						pm.tiket1
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_detail = '".$id."'");		
		$query = $this->db->query($sql);				
		foreach($query->result() as $row) {
			$idPerjalanan	= $row->id_perjalanan;
			$namaStaff 		= $row->nama;
			$nip 			= $row->nip;
			$golongan	 	= $row->golongan;		   		   		   
			$pangkat 		= $row->pangkat;
			$jabatan 		= $row->jabatan;	
			$tujuan 		= $row->kota;
			$noSpt 			= $row->no_spt;
			$tglSpt 		= $row->tgl_spt;
			$tiket1 		= $row->tiket1;
		}
		//Set variable Id Perjalanan to class ModelReport
		$this->mr->setIdPerjalanan($idPerjalanan);
		$resultNotaDinas 	= $this->mr->getNotaDinas();		
		$nomorNotaDinas		= $resultNotaDinas[0];
		$tanggalNotaDinas	= $resultNotaDinas[1];
		$tentangNotaDinas 	= $resultNotaDinas[2];
		$isExtraNotes		= $resultNotaDinas[3];
		
		$this->fpdf->SetTextColor(0, 0, 0);
		$this->fpdf->SetFont('Arial','U',12);
		$this->fpdf->Text(6.8,5.3,'SURAT PERINTAH PELAKSANAAN TUGAS');		
		$this->fpdf->setFont('Arial','',12);
		$this->fpdf->Text(6.3,5.9,'Nomor :    '.$noSpt,'L');		
		
		$this->fpdf->setFont('Arial','',10);
		$this->fpdf->setXY(2.6,6.8);
		$this->fpdf->MultiCell(0.6,0.5,'A.','');
		$this->fpdf->setXY(3.3,6.8);
		$this->fpdf->MultiCell(6.5,0.5,'Pejabat Pemberi Tugas','');
		$this->fpdf->setXY(8.5,6.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.2,6.8);
		$this->fpdf->MultiCell(9.3,0.5,'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI','');
		
		//for Single Staff
		$this->fpdf->setXY(2.6,7.8);
		$this->fpdf->MultiCell(0.6,0.5,'B.','');
		$this->fpdf->setXY(3.3,7.8);
		$this->fpdf->MultiCell(6.5,0.5,'Dasar Pelaksanaan Tugas','');
		$this->fpdf->setXY(8.5,7.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(3.3,8.4);			
		$this->fpdf->MultiCell(5.6,0.5,'1. Undangan',0);
		$this->fpdf->setXY(8.5,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.3,8.4);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.1,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.4);
		$this->fpdf->MultiCell(8,0.5,$nomorNotaDinas);
		$this->fpdf->setXY(9.3,8.9);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.1,8.9);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.9);
		$this->fpdf->MultiCell(8,0.5,day($tanggalNotaDinas));
		$this->fpdf->setXY(9.3,9.4);
		$this->fpdf->MultiCell(2,0.5,'Tentang');
		$this->fpdf->setXY(11.1,9.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,9.4);
		$this->fpdf->MultiCell(7.5,0.5,$tentangNotaDinas);
		
		/**
		 * add nota tambahan(Rutin/Undangan [Internal or Eksternal],Rutin,)Kontrak , SK TIM)
		 */
		$headerNote = '';
		if(!empty($isExtraNotes))
		{
				$jenisNotaExtra = $this->mr->getJenisNotaDinasExtra();
				if($jenisNotaExtra == 'Internal' || $jenisNotaExtra == 'Eksternal'){
				   $headerNote = '2. Nota-Dinas';}
				else if($jenisNotaExtra == 'Kontrak'){ $headerNote = '2. Kontrak';}
				else if($jenisNotaExtra == 'Tim'){ $headerNote = '2. SK.TIM';}		
				$ResultExtraNotes = $this->mr->getExtraNotaDinas();
		
		$nomorExtra 	= $ResultExtraNotes[0];
		$tanggalExtra	= $ResultExtraNotes[1];
		$perihalExtra	= $ResultExtraNotes[2];
		
		$koorY = 10.9;
		$rs = 0.5;
		$this->fpdf->Ln();
		$this->fpdf->setXY(3.3,$koorY);			
		$this->fpdf->MultiCell(5.6,0.5,$headerNote);
		$this->fpdf->setXY(9.3,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,$nomorExtra);
		
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,day($tanggalExtra));
		$koorY = $koorY + $rs;		
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Perihal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(7.5,0.5,$perihalExtra);
		}else{
			$koorY = 10.9;
			$rs = 0.5;
		}
		$rs = 1;
		$koorY = $koorY +$rs;//15.9;
		$rr = 2.5;		
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->MultiCell(0.6,0.5,'C.','');
		$this->fpdf->setXY(3.3,$koorY);
		$this->fpdf->MultiCell(6.5,0.5,'Pegawai yang ditugaskan','');
		$this->fpdf->setXY(8.5,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(2.5,$koorY);
		$this->fpdf->MultiCell(1,1,'NO','LTRB','C');
		$this->fpdf->setXY(3.5,$koorY);
		$this->fpdf->MultiCell(4.5,1,'N   A   M   A  ','TB','C');
		$this->fpdf->setXY(8,$koorY);
		$this->fpdf->MultiCell(5.5,1,'GOL / NIP','LTB','C');
		$this->fpdf->setXY(13.5,$koorY);
		$this->fpdf->MultiCell(6,1,'JABATAN','LTRB','C');
		$this->fpdf->Ln();
		$rs =0.5;
		$koorX = array(2.5, 3.5, 8, 8, 13.5); 
		$rr = 1;
		$koorY = $koorY + $rr;
		$this->fpdf->setXY($koorX[0],$koorY);
		$this->fpdf->Cell(1,1,'1.','LRB','C');
		$this->fpdf->setX($koorX[1],$koorY);
		$this->fpdf->Cell(4.5,1,' '.ucwords(mb_strtolower($namaStaff)),'B','L');
		$this->fpdf->setX($koorX[2],$koorY);
		$this->fpdf->Cell(5.5,0.5,' '.$pangkat.' '.$golongan,'L','L');
		$this->fpdf->setXY($koorX[3],$koorY + $rs);
		$this->fpdf->Cell(5.5,0.5,' '.'NIP : '.$nip,'LB','L');
		$this->fpdf->setXY($koorX[4],$koorY);
		if(strlen($jabatan) < 28){
			$this->fpdf->MultiCell(6,1,$jabatan,'LRB','L');
		}else{
			$this->fpdf->MultiCell(6,0.5,$jabatan,'LRB','L');
		}
		$koorY = $koorY + 1.2;
		$this->fpdf->Ln();
		$this->fpdf->setXY(2.6,$koorY );
		$this->fpdf->Cell(0.6,0.5,'D.','');		
		$this->fpdf->Cell(5.3,0.5,'Maksud dan Tujuan Penugasan','');		
		$this->fpdf->Cell(0.5,0.5,':');		
		
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi p inner join dinas d on p.dinas = d.id where p.id = '".$idPerjalanan."'");
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat 	= $row->berangkat;		   		   
		   $tanggalKembali 		= $row->kembali;
		}
			$koorY = $koorY + 1;
			$this->fpdf->Ln();
			$this->fpdf->Text(3.25,$koorY,'1. Tempat Tujuan');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getKotaTujuan());
			$rt = 0.7;
			$koorY = $koorY + $rt ;	
			$this->fpdf->Text(3.25,$koorY,'2. Tanggal Kegiatan');
			$this->fpdf->Text(8,$koorY,':');
			$tgl_berangkat = str_split($tanggalBerangkat,4);
			$this->fpdf->Text(8.3,$koorY,$tgl_berangkat[2].' s/d '.day($tanggalKembali));
			$koorY = $koorY + $rt ;
			$this->fpdf->Text(3.65,$koorY,'');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getTotalHariDinas().' ('.$this->mr->terbilang($this->mr->getTotalHariDinas()).')'.' Hari');
			$rt = 0.6;
			$koorY = $koorY + $rt ;
			$this->fpdf->Text(3.25,$koorY,'3. Untuk');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->SetXY(8.2,$koorY-0.35);
			$this->fpdf->MultiCell(11.3,0.5,$this->mr->getMaksud().'.','');
			$rt = 0.6;
			
			if (empty($tiket1)){
				//if($jabatan == 'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI')
					 //$this->setTransportasi("Kendaraan Dinas");
				//else 
				$this->setTransportasi("Kendaraan Umum");
			}else(
				$this->setTransportasi("Pesawat Udara")
			);
		
			$koorY = $koorY + (2.5 * $rt);			
			$this->fpdf->Text(3.25,$koorY,'4. Alat Transportasi');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->TRANSPORTASI);
			$this->fpdf->Ln();
		
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->Cell(0.6,0.5,'E.','');		
		$this->fpdf->Cell(4.7,0.5,'Keterangan lain-lain','');		
		$this->fpdf->Cell(0.5,0.5,':');
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'1.','');
		$this->fpdf->MultiCell(15.5,0.5,'Melaporkan hasil pelaksanaan tugas selambat-lambatnya 7 (tujuh) hari kerja setelah pelaksanaan dengan melampirkan dokumen pendukung administrasi lainnya sesuai peraturan perundang - undangan yang berlaku.','');
		$rt = 2;
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'2.','');
		$this->fpdf->MultiCell(15.5,0.5,'Para nama pegawai yang ditugaskan agar melaksanakan tugas ini dengan penuh tanggung jawab dan berlaku sejak tanggal ditetapkan.','');
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(3.2,$koorY-0.5);
		$this->fpdf->Cell(0.6,0.5,'3.','');
		$this->fpdf->MultiCell(15.5,0.5,'Segala biaya yang dikeluarkan berkenaan dengan pelaksanaan kegiatan ini dibebankan pada kegiatan tersebut diatas.','');
		//Footer
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->Text(12,3,'Ditetapkan');
		$this->fpdf->Text(15,3,':');
		$this->fpdf->Text(15,3,'         BEKASI');
		
		$this->fpdf->setFont('Arial','U',11);
		$this->fpdf->Text(12,3.5,'Pada tanggal             ');
		$this->fpdf->Text(15,3.5,':');
		$this->fpdf->Text(15,3.5,'         '.month(date("m")).' '.date("Y "));		
		if($namaStaff === "DWI HANDOKO"){
				$TTD 	= $this->NAMA_PLT;
				$JAB_PLT = 'SEKRETARIS DIREKTORAT JENDERAL SDPPI';}
		else {$TTD = $this->NAMA_DIREKTUR;
			  $JAB_PLT = 'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI';}
		
		$this->fpdf->setFont('Arial','B',10);
		if($namaStaff === "DWI HANDOKO"){
			$this->fpdf->Text(12.2,4.5,$JAB_PLT);
		}else{
			$this->fpdf->Text(12.2,4.5,substr($JAB_PLT,0,28));
			$this->fpdf->Text(12.5,5.0,substr($JAB_PLT,29));
		}		
		$this->fpdf->SetXY(12.5,6.7);
		$this->fpdf->Cell(6,0.5,$TTD,0,'','C');
		
		$this->fpdf->Ln();		
		$this->fpdf->setFont('Verdana','B',9);
		$this->fpdf->Text(2.6,8,'Tembusan :');
		$this->fpdf->Text(2.6,8.05,'_________');
		$this->fpdf->setFont('Verdana','',9);
		$this->fpdf->Text(2.6,8.5,'Disampaikan Yth. kepada :');
		$this->fpdf->Text(2.6,9,'1. Kabag Keuangan');
		$this->fpdf->Text(2.6,9.5,'2. Kasubag TU Dit. Pengendalian');		
		$this->fpdf->Text(3,10,'SDPPI, mohon penyiapan');
		$this->fpdf->Text(3,10.5,'SPPD/BOP bagi ybs');
		
		/*****
		//Insert Page Number
		$this->fpdf->setFont('Arial','',7);				
		$this->fpdf->Text(10.4,28.7, 'Page ' . $this->fpdf->PageNo());		
		******/		
		$this->fpdf->Output();
		//$this->fpdf->Output('pdf/SPPT '.ucwords(mb_strtolower($namaStaff)).' '.date("dmY").'.pdf','F');		
	}	
	function cetakSpjMulti() {		
		$id = $this->uri->segment(3);				
		$this->mr->setIdPerjalanan($id);
				
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		$this->fpdf->AcceptPageBreak();
		$this->fpdf->SetMargins(2,2,2);
		//Header
		//$this->fpdf->SetFont('helvetica','',11);
		//$this->fpdf->SetTextColor(5, 76, 143);
		//$this->fpdf->Text(3.5,1.2,'KEMENTERIAN KOMUNIKASI DAN INFORMATIKA REPUBLIK INDONESIA');
		//$this->fpdf->Text(3.5,1.7,'DIREKTORAT JENDERAL SUMBER DAYA DAN PERANGKAT POS DAN INFORMATIKA');
		//$this->fpdf->Text(3.5,2.2,'BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI');		
		//$this->fpdf->Image('../appspj/assets/images/informasi.jpg',3.3,2.4,8.5,0.65,'JPG');				
		//$this->fpdf->SetFont('helvetica','',9);
		//$this->fpdf->Text(3.5,3.4,'Jl. BINTARA RAYA No.17');
		//$this->fpdf->Text(9,3.4,'Tel : 021 - 3835992');
		//$this->fpdf->Text(13,3.4,'Fax : 021 - 3522915');
		//$this->fpdf->Text(16.9,3.4,'www.depkominfo.go.id');
		//$this->fpdf->Text(3.5,3.8,'BEKASI BARAT 17134');
		//$this->fpdf->Text(10.53,3.8,'3835977');
		//$this->fpdf->Text(17.7,3.8,'www.postel.go.id');		
		//$this->fpdf->Image('../appspj/assets/images/kominfo.jpg',1,0.8,2.3,2.6,'JPG');
		//$this->fpdf->Image('../appspj/assets/images/header_line_blue.png',1,4,19.1,0.2,'PNG');
		
		$sql = ("select pmd.personil from perjalanan_multi_detail pmd where pmd.id_perjalanan= '".$id."'");
		$query = $this->db->query($sql);
		$arrayPersonil = array();		
		foreach ($query->result() as $row) { $arrayPersonil[]= $row->personil; }			
		//Content
		$sql = ("SELECT	pmd.id_detail,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						k.kota,
						pmd.no_spt,
						pm.tgl_spt,
						pm.tiket1
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_perjalanan = '".$id."'
				ORDER BY pmd.id_detail");
						
		$query = $this->db->query($sql);
		$namaStaff 	= array();
		$nip 		= array();
		$golongan 	= array();
		$jabatan 	= array();	
		$tujuan 	= array();
		$noSpt 		= array();
		$tglSpt 	= array();		 	
		
		foreach ($query->result() as $row)
		{
			$namaStaff []	= $row->nama;
			$nip []			= $row->nip;
			$golongan []	= $row->golongan;		   		   		   
			$pangkat []		= $row->pangkat;
			$jabatan []		= $row->jabatan;	
			$tujuan []		= $row->kota;
			$noSpt []		= $row->no_spt;
			$tglSpt []		= $row->tgl_spt;  	
			$tiket1 		= $row->tiket1;  	
		}
		//Set variable Id Perjalanan to class ModelReport					
		$resultNotaDinas 	= $this->mr->getNotaDinas();		
		$nomorNotaDinas		= $resultNotaDinas[0];
		$tanggalNotaDinas	= $resultNotaDinas[1];
		$tentangNotaDinas 	= $resultNotaDinas[2];
		$isExtraNotes		= $resultNotaDinas[3];
				
		$this->fpdf->SetTextColor(0, 0, 0);
		$this->fpdf->SetFont('Arial','U',12);
		$this->fpdf->Text(6.8,5.3,'SURAT PERINTAH PELAKSANAAN TUGAS');		
		$this->fpdf->setFont('Arial','',12);
		$this->fpdf->Text(6.3,5.9,'Nomor :    '.$noSpt[0],'L');		
		
		$this->fpdf->setFont('Arial','',10);
		$this->fpdf->setXY(2.6,6.8);
		$this->fpdf->MultiCell(0.6,0.5,'A.','');
		$this->fpdf->setXY(3.3,6.8);
		$this->fpdf->MultiCell(6.5,0.5,'Pejabat Pemberi Tugas','');
		$this->fpdf->setXY(8.5,6.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.2,6.8);
		$this->fpdf->MultiCell(9.3,0.5,'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI','');
		
		
		//for Multi Staff
		$this->fpdf->setXY(2.6,7.8);
		$this->fpdf->MultiCell(0.6,0.5,'B.','');
		$this->fpdf->setXY(3.3,7.8);
		$this->fpdf->MultiCell(6.5,0.5,'Dasar Pelaksanaan Tugas','');
		$this->fpdf->setXY(8.5,7.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(3.3,8.4);			
		$this->fpdf->MultiCell(5.6,0.5,'1. Undangan',0);
		$this->fpdf->setXY(8.5,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.3,8.4);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.1,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.4);
		$this->fpdf->MultiCell(8,0.5,$nomorNotaDinas);
		$this->fpdf->setXY(9.3,8.9);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.1,8.9);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.9);
		$this->fpdf->MultiCell(8,0.5,day($tanggalNotaDinas));
		$this->fpdf->setXY(9.3,9.4);
		$this->fpdf->MultiCell(2,0.5,'Tentang');
		$this->fpdf->setXY(11.1,9.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,9.4);
		$this->fpdf->MultiCell(7.5,0.5,$tentangNotaDinas);
		
		/**
		 * add nota tambahan(Rutin/Undangan [Internal or Eksternal],Rutin,)Kontrak , SK TIM)
		 */
		$headerNote = '';
		if(!empty($isExtraNotes))
		{
				$jenisNotaExtra = $this->mr->getJenisNotaDinasExtra();
				if($jenisNotaExtra == 'Internal'){$headerNote = '2. Nota-Dinas';}
				else if($jenisNotaExtra == 'Eksternal'){$headerNote = '2. Undangan';}
				else if($jenisNotaExtra == 'Kontrak'){ $headerNote = '2. Kontrak';}
				else if($jenisNotaExtra == 'Tim'){ $headerNote = '2. SK.TIM';}		
				$ResultExtraNotes = $this->mr->getExtraNotaDinas();
		
		$nomorExtra 	= $ResultExtraNotes[0];
		$tanggalExtra	= $ResultExtraNotes[1];
		$perihalExtra	= $ResultExtraNotes[2];
		
		$koorY = 10.9;
		$rs = 0.5;
		$this->fpdf->Ln();
		$this->fpdf->setXY(3.3,$koorY);			
		$this->fpdf->MultiCell(5.6,0.5,$headerNote);
		$this->fpdf->setXY(9.3,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,$nomorExtra);
		
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,day($tanggalExtra));
		$koorY = $koorY + $rs;		
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Perihal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(7.5,0.5,$perihalExtra);
		}else{
			$koorY = 8;
			$rs = 0.5;
		}
		$rr = 2.5;
		$koorY = $koorY + $rr;
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->MultiCell(0.6,0.5,'C.','');
		$this->fpdf->setXY(3.3,$koorY);
		$this->fpdf->MultiCell(6.5,0.5,'Pegawai yang ditugaskan','');
		$this->fpdf->setXY(8.5,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(2.5,$koorY);
		$this->fpdf->MultiCell(1,1,'NO','LTR','C');
		$this->fpdf->setXY(3.5,$koorY);
		$this->fpdf->MultiCell(4.5,1,'N   A   M   A  ','T','C');
		$this->fpdf->setXY(8,$koorY);
		$this->fpdf->MultiCell(5.5,1,'GOL / NIP','LT','C');
		$this->fpdf->setXY(13.5,$koorY);
		$this->fpdf->MultiCell(6,1,'JABATAN','LTR','C');
		$this->fpdf->Ln();
		$rs = 1;
		//$koorY = $this->fpdf->getY();
		$koorY = $koorY +$rs;//15.9;
		$no = 1;
		$counter = 0;		
		for($i = 0 ; $i < sizeof($namaStaff);$i++){
			$koorX = array(2.5, 3.5, 8, 8, 13.5);
			$rr = 1;					
			$this->fpdf->setXY($koorX[0],$koorY);
			$this->fpdf->Cell(1,1,$no.'.','LRBT','','C');			
			$this->fpdf->Cell(4.5,1,' '.ucwords(mb_strtolower($namaStaff[$i])),'BT','','L');			
			$this->fpdf->Cell(5.5,0.5,' '.$pangkat[$i].' '.$golongan[$i],'LT','','L');
			$this->fpdf->setXY($koorX[3],$koorY+0.5);			
			$this->fpdf->Cell(5.5,0.5,' '.'NIP : '.$nip[$i],'LB','','L');
			$this->fpdf->setXY($koorX[4],$koorY);			
			if(strlen($jabatan[$i]) <= 26 ){
				$this->fpdf->MultiCell(6,1,$jabatan[$i],'LRBT','L');
			}else if(strlen($jabatan[$i]) > 26){
				if($jabatan[$i] == "KASI STANDAR PENERTIBAN PPI")
				$this->fpdf->MultiCell(6,1,$jabatan[$i],'LRBT','');
				else $this->fpdf->MultiCell(6,0.5,$jabatan[$i],'LRBT','');
			}			
			$koorY = $koorY+$rr;
			if ($koorY >= 26.5){
				$this->fpdf->AddPage();	
				$koorY = 1;				
			}
			$no++;
		}
		$GetY= $this->fpdf->GetY();
		if($GetY > 25){
				$koorY = 1;
				$this->fpdf->AddPage();
				$rr = 1.5;}
		$GetY= $this->fpdf->GetY();
		$koorY = $GetY+0.5;
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi p inner join dinas d on p.dinas = d.id where p.id = '".$id."'");		
		$query = $this->db->query($sql);		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat 	= $row->berangkat;		   		   
		   $tanggalKembali 		= $row->kembali;
		}
		$this->fpdf->Ln();
		$this->fpdf->setXY(2.6,$koorY );
		$this->fpdf->Cell(0.6,0.5,'D.','');		
		$this->fpdf->Cell(5.3,0.5,'Maksud dan Tujuan Penugasan','');		
		$this->fpdf->Cell(0.5,0.5,':');
		$GetY= $this->fpdf->GetY();
		$rr = 1;
			$koorY = $GetY + $rr; //		
			$this->fpdf->Ln();
			$this->fpdf->Text(3.25,$koorY ,'1. Tempat Tujuan');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getKotaTujuan());
			$GetY= $this->fpdf->GetY(); //
			$rr = 1.1;
			$koorY = $GetY + $rr;
			$this->fpdf->Text(3.25,$koorY,'2. Tanggal Kegiatan');
			$this->fpdf->Text(8,$koorY,':');
			$tgl_berangkat = str_split($tanggalBerangkat,4);
			$this->fpdf->Text(8.3,$koorY,$tgl_berangkat[2].' s/d '.day($tanggalKembali));
			$GetY = $this->fpdf->GetY(); //
			$rr = 1.7;
			$koorY = $GetY + $rr;
			$this->fpdf->Text(3.65,$koorY,'');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getTotalHariDinas().' ('.$this->mr->terbilang($this->mr->getTotalHariDinas()).')'.' Hari');
			$GetYCur = $this->fpdf->GetY(); //
			$koorY = $GetY + $rr;
			$rr = 1;
			if ($GetYCur  >= 25){
				$this->fpdf->AddPage();
				$koorY = 1;
				$rr = 1.5;
				$koorY = $rr;
				//$this->fpdf->SetTopMargin(3);
			}else {
				$GetY = $this->fpdf->GetY(); //
				$rr = 2;
				$koorY = $GetY + $rr;  
			}	
			$this->fpdf->SetXY(3.15,$koorY);
			$this->fpdf->Cell(4,0.5,'3. Untuk');
			$this->fpdf->SetXY(7.9,$koorY);
			$this->fpdf->Cell(1,0.5,':');			
			$this->fpdf->SetXY(8.2,$koorY);
			$this->fpdf->MultiCell(10.5,0.5,$this->mr->getMaksud(),'');
			$GetY= $this->fpdf->GetY(); //
			
			$rr = 0.5;
			if (empty($tiket1)){
				$this->setTransportasi("Kendaraan Umum");
			}else(
				$this->setTransportasi("Pesawat Udara")
			);
		
			$koorY = $GetY + $rr;
			$this->fpdf->Text(3.25,$koorY,'4. Alat Transportasi');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->TRANSPORTASI);
			$this->fpdf->Ln();
		$GetY= $this->fpdf->GetY(); //
		$rr = 1;
		$koorY = $GetY + $rr;
		$this->fpdf->Ln();
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->Cell(0.6,0.5,'E.','');		
		$this->fpdf->Cell(4.7,0.5,'Keterangan lain-lain','');		
		$this->fpdf->Cell(0.5,0.5,':');
		$GetY= $this->fpdf->GetY(); //
		$koorY = $GetY + $rr;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'1.','');
		$this->fpdf->MultiCell(15.5,0.5,'Melaporkan hasil pelaksanaan tugas selambat-lambatnya 7 (tujuh) hari kerja setelah pelaksanaan dengan melampirkan dokumen pendukung administrasi lainnya sesuai peraturan perundang-undangan yang berlaku.','');
		$rr = 0.5;
		$GetY= $this->fpdf->GetY(); //
		$koorY = $GetY + $rr;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'2.','');
		$this->fpdf->MultiCell(15.5,0.5,'Para nama pegawai yang ditugaskan agar melaksanakan tugas ini dengan penuh tanggung jawab dan berlaku sejak tanggal ditetapkan.','');		
		$GetY= $this->fpdf->GetY(); //
		$koorY = $GetY + $rr;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'3.','');
		$this->fpdf->MultiCell(15.5,0.5,'Segala biaya yang dikeluarkan berkenaan dengan pelaksanaan kegiatan ini dibebankan pada kegiatan tersebut diatas.','');
		//Footer
		$rr = 1;
		$GetYCur  = $this->fpdf->GetY();
		$koorY = $GetY + $rr;
			if ($GetYCur  > 17){
				$this->fpdf->AddPage();
				$koorY = $this->fpdf->GetY();;
				$rr = 3;
				$koorY = $rr;
				//$this->fpdf->SetTopMargin(3);
			}else {
				$GetY = $this->fpdf->GetY(); //
				$rr = 2;
				$koorY = $GetY + $rr;  
			}
		$this->fpdf->Ln();
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->SetXY(11.5,$koorY);
		$this->fpdf->MultiCell(2.5,0.5,'Ditetapkan');
		$this->fpdf->SetXY(14.5,$koorY);
		$this->fpdf->MultiCell(1,0.5,':');
		$this->fpdf->SetXY(15,$koorY);
		$this->fpdf->MultiCell(3,0.5,'    BEKASI');
		$rr = 0;
		$GetY= $this->fpdf->GetY();
		$koorY = $GetY;		
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->SetXY(11.5,$koorY);
		$this->fpdf->Cell(3,0.5,'Pada tanggal','B');
		$this->fpdf->SetXY(14.5,$koorY);
		$this->fpdf->Cell(0.2,0.5,':','B');
		$this->fpdf->SetXY(14.7,$koorY);
		$this->fpdf->Cell(3.6,0.5,'       '.month(date("m")).' '.date("Y "),'B');
		
				
		$GetY = $this->fpdf->GetY();
		$rr = 1.5; 
		$koorY = $GetY + $rr;
		$this->fpdf->setFont('Arial','B',10);
		$this->fpdf->Text(12,$koorY,'KEPALA BALAI BESAR PENGUJIAN');
		$GetY = $this->fpdf->GetY();
		$rr = 2.0; 
		$koorY = $GetY + $rr;
		$this->fpdf->setFont('Arial','B',10);
		$this->fpdf->Text(12,$koorY,'   PERANGKAT TELEKOMUNIKASI');
		$GetY= $this->fpdf->GetY();
		$rr = 4.5;
		$koorY = $GetY + $rr;
		if($namaStaff === "DWI HANDOKO"){$TTD = $this->NAMA_PLT;}
		else {$TTD = $this->NAMA_DIREKTUR;}
		
		$this->fpdf->SetXY(12,$koorY);
		$this->fpdf->Cell(6,0.5,$TTD,0,'','C');
		$GetY= $this->fpdf->GetY();
		$rr = 1;
		$koorY = $GetY + $rr;
		$this->fpdf->Ln();		
		$this->fpdf->setFont('Verdana','B',9);
		$this->fpdf->Text(2.6,$koorY,'Tembusan :');
		$this->fpdf->Text(2.6,$koorY+0.05,'_________');
		$this->fpdf->setFont('Verdana','',9);
		$GetY= $this->fpdf->GetY();
		$rr = 1;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(2.6,$koorY,'Disampaikan Yth. kepada :');
		$GetY = $this->fpdf->GetY();
		$rr = 1.5;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(2.6,$koorY,'1. Kabag Keuangan');
		$GetY = $this->fpdf->GetY();
		$rr = 2.05;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(2.6,$koorY,'2. Kasubag TU Dit. Pengendalian');
		$GetY= $this->fpdf->GetY();
		$rr = 2.5;
		$koorY = $GetY + $rr;		
		$this->fpdf->Text(3,$koorY,'SDPPI, mohon penyiapan');
		$GetY= $this->fpdf->GetY();
		$rr = 3;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(3,$koorY,'SPPD/BOP bagi ybs');
		
		/*****
		//Insert Page Number
		$this->fpdf->setFont('Arial','',7);				
		$this->fpdf->Text(10.4,28.7, 'Page ' . $this->fpdf->PageNo());		
		******/
		
		$this->fpdf->Output();
		//$this->fpdf->Output('pdf/SPPT '.ucwords(mb_strtolower($namaStaff)).' '.date("dmY").'.pdf','F');			
	}

	function printSpt(){
		$id 	= $this->uri->segment(3);		
		$this -> mr -> setID($id);
		$sql = ("SELECT	pmd.id_perjalanan											
				 FROM
						pangkat p
						INNER JOIN staff s ON p.golongan = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				 WHERE
						pmd.id_detail = '".$id."'");
				 
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
			$idPerjalanan = $row->id_perjalanan;						
		}
		
		$this->mr->setIdPerjalanan($idPerjalanan);
		
		$noSpt_last = $this -> mr -> saveNoSpt();		
		//$noSpt_last = $this->mr->getLastNoSptofMonth();
		
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		
		//Header
		$this->fpdf->setFont('Arial','B',15);
		$this->fpdf->Text(4.9,7,$noSpt_last,'C');
		$this->fpdf->Ln();
		$this->fpdf->Output();				
	}
	/*function cetakKuitansi() {
		$id 	= $this->uri->segment(3);	
		$this -> mr -> setID($id);						
		$sql = ("SELECT	pmd.id_perjalanan,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						k.kota,
						pmd.no_spt,
						pm.tgl_spt
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_detail = '".$id."'");
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$idPerjalanan 	= $row->id_perjalanan;
			$namaStaff 		= $row->nama;
			$nip 			= $row->nip;
			$golongan 		= $row->golongan;		   		   		   
			$pangkat 		= $row->pangkat;
			$jabatan 		= $row->jabatan;	
			$noSpt 			= $row->no_spt;
			$tglSpt 		= $row->tgl_spt;		
		}
		$this->mr->setIdPerjalanan($idPerjalanan);
		
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi pm inner join dinas d on pm.dinas = d.id where pm.id = '".$idPerjalanan."'");
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat = $row->berangkat;		   		   
		   $tanggalKembali 	 = $row->kembali;
		}		
		//** =============================== Mainly Important =========================== **
			$totalHariDinas 	= $this->mr->getTotalHariDinas();				
			$uangSaku 			= $this->mr->getUangSaku();
			$totalUangSaku 		= $this->mr->sumUangSaku();		
			$totalTiket 		= $this->mr->sumTicket();				
			$taxi				= $this->mr->getTaxi();	
			$taxiAsal 			= $taxi['taxi_asal'];
			$taxiTujuan  		= $taxi['taxi_tujuan'];				
			$sumTaxTujuan 		= ($taxiTujuan) * (2);
			$sumTaxAsal 		= ($taxiAsal) * (2);		
			$sumAirportTax		= $this->mr->sumAirportTax();							
			$hargaHotel			= $this->mr->getTotalHotel();
			$sumHotel			= $this->mr->sumHotel();
			$sumAll				= $this->mr->sumAll();
			//$sumAll			= 9999999;
	
			if( $jabatan === "KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI")
			{
				$sumRepresentative = (($totalHariDinas)*($this->REPRESENTATIF));
				$sumAll			  = (($this->mr->sumAll()) + ($sumRepresentative));
			}
			else{
				$sumAll			  = $this->mr->sumAll();
			}	
			
		//*******************************************************************************
			
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		
		//Header
		$this->fpdf->setFont('Arial','B',12.6);
		$this->fpdf->Text(4.9,1,'KEMENTERIAN KOMUNIKASI DAN INFORMATIKA','C');
		$this->fpdf->Text(1.7,1.6,'DIREKTORAT JENDERAL SUMBER DAYA DAN PERANGKAT POS DAN INFORMATIKA');
		$this->fpdf->Image('../appspj/assets/images/header_line.png',1.7,1.8,18.3,0.3,'PNG');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','',9.5);
		$this->fpdf->Text(1.7,2.7,'Nomor','L');
		$this->fpdf->Text(3.7,2.7,':','L');
		$this->fpdf->Text(1.7,3.2,'N.a','L');
		$this->fpdf->Text(3.7,3.2,':','L');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','BU',12);
		$this->fpdf->Text(9.2,3.7,'K U I T A N S I');	
		$this->fpdf->Ln();
		
		//Content Head
		$this->fpdf->setFont('Arial','',9);
		$this->fpdf->Text(1.7,4.5,'Sudah terima dari','L');
		$this->fpdf->Text(5.1,4.5,':','L');
		$this->fpdf->Text(5.5,4.5,'Kuasa Pengguna Anggaran / Pejabat Pembuat Komitmen Ditjen SDPPI BEKASI','L');				 
		$this->fpdf->Ln();
		
		$arX = 5.4;
		$arY = 4.68;
				
		$this->fpdf->Text(1.7,5,'Uang sebesar','L');
		$this->fpdf->Text(5.1,5,':','L');
		$this->fpdf->SetXY($arX,$arY);
		//$sumAll = 999993;
		$this->fpdf->MultiCell(15,0.55,'Rp. '.$this->mr->value_to_rupiah($sumAll).',- ('.$this->mr->terbilang($sumAll,3).' Rupiah)',0,'L');
		$this->fpdf->Ln();
				
		$this->fpdf->Text(1.7,6.1,'Guna Pembayaran','L');
		$this->fpdf->Text(5.1,6.1,':','L');
		$this->fpdf->Text(5.5,6.1,'Biaya perjalanan dinas (lumpsum) menurut Surat Perintah Perjalanan Dinas','L');		
		$this->fpdf->Ln();
		
		$this->fpdf->Text(1.7,6.7,'Dari','L');
		$this->fpdf->Text(5.1,6.7,':','L');
		$this->fpdf->Text(5.5,6.7,'Direktur Pengendalian ','L');
		$this->fpdf->Text(5.5,7.2,'Direktorat Jenderal Sumber Daya dan Perangkat Pos dan Informatika.','L');
		
				$this->fpdf->Text(5.5,7.7,'Tanggal','L');	  
				$this->fpdf->Text(7.4,7.7,':','L');
				$this->fpdf->Text(7.8,7.7,day($this->mr->getTglSptApprove()),'L');
				$this->fpdf->Ln();
				
				$this->fpdf->Text(5.5,8.2,'SPT','L');
				$this->fpdf->Text(7.4,8.2,':','L');
				$this->fpdf->Text(7.8,8.2,$this->mr->getNoSptApprove(),'L');
				$this->fpdf->Ln();
		
		$this->fpdf->Text(1.7,8.7,'Untuk','L');
		$this->fpdf->Text(5.1,8.7,':','L');
		$this->fpdf->Text(5.5,8.7,'Perjalanan Dinas dari '.$this->mr->getKotaAsal().' ke '.$this->mr->getKotaTujuan().' PP.','L');
		 
		$this->fpdf->Ln();
		
		$this->fpdf->Text(1.7,9.3,'Terbilang','L');
		$this->fpdf->Text(5.1,9.3,':','L');		
		$this->fpdf->SetXY(5.4,8.96);
		$this->fpdf->MultiCell(15,0.55,'Rp. '.$this->mr->value_to_rupiah($sumAll).',-'.' ( '.$this->mr->terbilang($sumAll,3).' Rupiah )',0,'L');
		$this->fpdf->Ln();
		
		//Footer Head		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.9,10.3);		
		$this->fpdf->Cell(6,0.5,'SETUJU DIBAYAR : ',0,0,'C');
		$this->fpdf->SetXY(1,10.68);
		$this->fpdf->Cell(8,0.5,'A.n. KUASA PENGGUNA ANGGARAN /',0,0,'C');
		$this->fpdf->setFont('Arial','',9);
		$this->fpdf->Text(15,10.35,'BEKASI,    '.month(date("m")).' '.date("Y"));
		$this->fpdf->SetXY(1,11.12);
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->Cell(8,0.5,'PEJABAT PEMBUAT KOMITMEN',0,0,'C');
		$this->fpdf->SetXY(13.5,10.68);
		$this->fpdf->Cell(6,0.5,'Yang bepergian , ',0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','BU',9);
		$this->fpdf->SetXY(1.2,13);
		$this->fpdf->Cell(7,0.5,$this->NAMA_PPK,0,0,'C');
		$this->fpdf->SetXY(13.5,13);
		$this->fpdf->Cell(6,0.5,$namaStaff,0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.2,13.4);			
		$this->fpdf->Cell(7,0.5,'NIP : '.$this->NIP_PPK,0,0,'C');
		$this->fpdf->SetXY(13.5,13.4);
		$this->fpdf->Cell(6,0.5,'NIP : '.$nip,0,0,'C');	
		$this->fpdf->Ln();			
		
		//Content Rincian Biaya
		$this->fpdf->setFont('Arial','BU',10);
		$this->fpdf->Text(6,14.5,'PERINCIAN PERHITUNGAN BIAYA PERJALANAN DINAS');		
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','B',10);
		$this->fpdf->Text(6.9,14.9,'SPPD NO. '.$noSpt,'L');
		
		$this->fpdf->setFont('Arial','B',8.5);
		$this->fpdf->SetXY(1.7,15.3);	
		$this->fpdf->Cell(0.7,0.5,'No ','LRT',0,'C');
		$this->fpdf->Cell(11,0.5,'PERINCIAN BIAYA','TRB',0,'C');
		$this->fpdf->Cell(3.5,0.5,'JUMLAH ','TRB',0,'C');
		$this->fpdf->Cell(3,0.5,'KET','TR',0,'C');		
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','',8.8);		 	
		$this->fpdf->SetX(1.7);	
		$this->fpdf->Cell(0.7,0.5,'I. ','LT',0,'C');
		$this->fpdf->Cell(6,0.5,'Uang Harian Lumpsum '.$totalHariDinas.' ('.$this->mr->terbilang($totalHariDinas).')'.' Hari','L',0,'L');		
		$this->fpdf->Cell(1,0.5,'=','',0,'C');
		$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($uangSaku),'',0,'L');
		$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
		$this->fpdf->Cell(0.9,0.5,$totalHariDinas,'',0,'L');
		$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
		$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($totalUangSaku).' ,-','',0,'R');
		$this->fpdf->Cell(3,0.5,'','LTR',0,'C');				
		$this->fpdf->Ln();
		
		$this->fpdf->SetX(1.7);
		$this->fpdf->Cell(0.7,0.5,'II. ','L',0,'C');
		$this->fpdf->Cell(6,0.5,'Biaya Transportasi (Biaya Rill)','L',0,'L');
		$this->fpdf->Cell(1,0.5,'','',0,'C');
		$this->fpdf->Cell(4,0.5,'','',0,'L');
		$this->fpdf->Cell(3.5,0.5,'','LR',0,'C');
		$this->fpdf->Cell(3,0.5,'','R',0,'C');				
		$this->fpdf->Ln();
		
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'a. Pesawat Udara','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(4,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'C');
				$this->fpdf->Cell(3,0.5,'','LR',0,'L');
				$this->fpdf->Ln();
				
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'    '.$this->mr->getKotaAsal().' / '.$this->mr->getKotaTujuan().' PP','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(4,0.5,'Rp. '.$this->mr->value_to_rupiah($totalTiket),'',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($totalTiket).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'*)','LR',0,'L');
				$this->fpdf->Ln();
				
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','LB',0,'C');
				$this->fpdf->Cell(6,0.5,'b. Airport tax Asal/Airport tax tujuan','LB',0,'L');
				$this->fpdf->Cell(1,0.5,'=','B',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp.'.$this->mr->value_to_rupiah($sumAirportTax),'B',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','B',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','BR',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','LB',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumAirportTax).' ,-','B',0,'R');
				$this->fpdf->Cell(3,0.5,'','LBR',0,'C');				
				$this->fpdf->Ln();
						
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(4,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'L');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');
				$this->fpdf->Ln();
								
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'c. Transportasi lokal tempat kedudukan','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($taxiAsal),'',0,'L');
				$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'2','',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumTaxAsal).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'*)','LR',0,'L');
				$this->fpdf->Ln();

				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'','',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'L');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');
				$this->fpdf->Ln();
								
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'d. Transportasi lokal tempat tujuan','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($taxiTujuan),'',0,'L');
				$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'2','',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumTaxTujuan).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');				
				$this->fpdf->Ln();
				
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'','',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'L');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');				
				$this->fpdf->Ln();
				
				if( $jabatan === "KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI")
				{				
					$this->fpdf->SetX(1.7);
					$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
					$this->fpdf->Cell(6,0.5,'d. Representatif','L',0,'L');
					$this->fpdf->Cell(1,0.5,'=','',0,'C');
					$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($this->REPRESENTATIF),'',0,'L');
					$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
					$this->fpdf->Cell(0.9,0.5,$totalHariDinas,'',0,'L');
					$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
					$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah(($this->REPRESENTATIF) * ($totalHariDinas)).' ,-','',0,'R');					
					$this->fpdf->Cell(3,0.5,'','LR',0,'C');
					$this->fpdf->Ln();										
				}				
				// tampilan output airport tax di kwitansi//
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','LB',0,'C');
				$this->fpdf->Cell(6,0.5,'d. Airport tax','LB',0,'L');
				$this->fpdf->Cell(1,0.5,'=','B',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($sumAirportTax),'B',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','B',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','BR',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','LB',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumAirportTax).' ,-','B',0,'R');
				$this->fpdf->Cell(3,0.5,'','LBR',0,'C');				
				$this->fpdf->Ln();
			
			
		$idProvTujuan 	= $this->mr->getIdProvTujuan();
		$TypeInap 		= $this->mr->getHotel();		
		if($TypeInap == 'no' ) //&& ($idProvTujuan = 11 && $idProvTujuan = 12))
		{
			$hargaHotel = $hargaHotel * 0.3;
		}
		$this->fpdf->SetX(1.7);	
		$this->fpdf->Cell(0.7,0.5,'III. ','L',0,'C');
		$this->fpdf->Cell(6,0.5,'Biaya Penginapan (Biaya Riil)','L',0,'L');
		$this->fpdf->Cell(1,0.5,'=','',0,'C');
		$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($hargaHotel),'',0,'L');
		$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
		$this->fpdf->Cell(0.9,0.5,($totalHariDinas) - (1),'',0,'L');
		$this->fpdf->Cell(0.8,0.5,'Rp.','LB',0,'R');
		$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumHotel).' ,-','B',0,'R');
		$this->fpdf->Cell(3,0.5,'*)','LR',0,'L');
		$this->fpdf->Ln();				
		
		
		$this->fpdf->SetX(1.7);	
		$this->fpdf->Cell(0.7,0.5,'','LB',0,'C');
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->Cell(11,0.5,'Jumlah    ','LB',0,'R');
		$this->fpdf->Cell(0.8,0.5,'Rp.','LB',0,'R');
		$this->fpdf->setFont('Arial','B',8.5);
		$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumAll).' ,-','B',0,'R');
		$this->fpdf->Cell(3,0.5,'**)','LBR',0,'L');		
		$this->fpdf->Ln();
		
		//Footer Rincian Biaya				
		$this->fpdf->setFont('Arial','',9);
		$this->fpdf->Text(13.3,22.3,'BEKASI ,'.'      '.month(date("m")).' '.date("Y"),'L');
		
		$this->fpdf->setFont('Arial','',8);
		$this->fpdf->Text(1.7,22.75,'Telah dibayar sejumlah','L');
		$this->fpdf->Text(12,22.75,'Telah menerima uang sebesar','L');
		
		$this->fpdf->setFont('Arial','B',8);
		$this->fpdf->SetXY(1.6,22.78);
		$this->fpdf->Cell(6,0.5,'Rp. '.$this->mr->value_to_rupiah($sumAll).' ,-','',0,'L');
		
		$this->fpdf->SetXY(11.9,22.78);
		$this->fpdf->Cell(6,0.5,'Rp. '.$this->mr->value_to_rupiah($sumAll).' ,-','',0,'L');
		
		$this->fpdf->setFont('Arial','BI',8);
		$this->fpdf->SetXY(1.6,23.28);
		$this->fpdf->MultiCell(8.3,0.3,'('.$this->mr->terbilang($sumAll,3).' Rupiah)',0);
		
		$this->fpdf->SetXY(11.9,23.28);
		$this->fpdf->MultiCell(8.3,0.3,'('.$this->mr->terbilang($sumAll,3).' Rupiah)',0);
		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.5,24);
		$this->fpdf->Cell(6,0.5,'LUNAS DIBAYAR',0,0,'C');
		$this->fpdf->SetXY(1.5,24.4);
		$this->fpdf->Cell(6,0.5,'BENDAHARA PENGELUARAN',0,0,'C');
		$this->fpdf->SetXY(13.5,24);
		$this->fpdf->Cell(6,0.5,'YANG BEPERGIAN',0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','BU',9);
		$this->fpdf->SetXY(1.5,26.2);
		$this->fpdf->Cell(6,0.5,$this->NAMA_BENDAHARA,0,0,'C');
		$this->fpdf->SetXY(13.5,26.2);
		$this->fpdf->Cell(6,0.5,$namaStaff,0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.5,26.6);			
		$this->fpdf->Cell(6,0.5,'NIP : '.$this->NIP_BENDAHARA,0,0,'C');
		$this->fpdf->SetXY(13.5,26.6);
		$this->fpdf->Cell(6,0.5,'NIP : '.$nip,0,0,'C');
			
		$this->fpdf->SetAutoPageBreak(FALSE);
		$this->fpdf->setFont('Arial','',7);
		$this->fpdf->SetXY(1.5,27.4);			
		$this->fpdf->Cell(1,0.3,'*)',0,'L');
		$this->fpdf->SetXY(2.5,27.4);			
		$this->fpdf->MultiCell(8.5,0.3,'Apabila tidak didapatkan bukti pengeluaran/kwitansi yang sah harus membuat Surat Pernyataan Pengeluaran Riil terlampir.',0,'L');
		$this->fpdf->SetXY(1.5,28.1);
		$this->fpdf->Cell(1,0.3,'**)',0,'L');
		$this->fpdf->SetXY(2.5,28.1);			
		$this->fpdf->MultiCell(8.5,0.3,'Apabila dikemudian hari terdapat kesalahan dan atau kelebihan atas pembayaran Perjalanan Dinas, tersebut, kami (yang bepergian) bertanggung jawab sepenuhnya dan bersedia menyetor atas kesalahan dan atau kelebihan pembayaran tersebut ke rekening Kas Negara.',0,'L');
		
		$this->fpdf->Output();
		//$this->fpdf->Output('Kuitansi Perjalanan Dinas_'.ucwords(mb_strtolower($namaStaff)).'_'.date("d_m_Y").'.pdf','D');
	}	
	*/
	//fungsi cetak kwitansi baru
	function cetakKuitansi() {
		$id 	= $this->uri->segment(3);	
		$this -> mr -> setID($id);	
		$sql = ("SELECT	pmd.id_perjalanan,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						k.kota,
						pmd.no_spt,
						pm.tgl_spt
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_detail = '".$id."'");
		$query = $this->db->query($sql);
		foreach ($query->result() as $row)
		{
			$idPerjalanan 	= $row->id_perjalanan;
			$namaStaff 		= $row->nama;
			$nip 			= $row->nip;
			$golongan 		= $row->golongan;		   		   		   
			$pangkat 		= $row->pangkat;
			$jabatan 		= $row->jabatan;	
			$noSpt 			= $row->no_spt;
			$tglSpt 		= $row->tgl_spt;		
		}
		$this->mr->setIdPerjalanan($idPerjalanan);
		
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi pm inner join dinas d on pm.dinas = d.id where pm.id = '".$idPerjalanan."'");
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat = $row->berangkat;		   		   
		   $tanggalKembali 	 = $row->kembali;
		}		
		/** =============================== Mainly Important =========================== **/
			$totalHariDinas 	= $this->mr->getTotalHariDinas();				
			$uangSaku 			= $this->mr->getUangSaku();
			$totalUangSaku 		= $this->mr->sumUangSaku();		
			$totalTiket 		= $this->mr->sumTicket();				
			$taxi				= $this->mr->getTaxi();	
			$taxiAsal 			= $taxi['taxi_asal'];
			$taxiTujuan  		= $taxi['taxi_tujuan'];				
			$sumTaxTujuan 		= ($taxiTujuan) * (2);
			$sumTaxAsal 		= ($taxiAsal) * (2);		
			$sumAirportTax		= $this->mr->sumAirportTax();							
			$hargaHotel			= $this->mr->getTotalHotel();
			$sumHotel			= $this->mr->sumHotel();
			$sumAll				= $this->mr->sumAll();
			//$sumAll			= 9999999;
			
			if (empty($sumAirportTax)){
				$this->setTransportasi("Kendaraan Umum");
			}else(
				$this->setTransportasi("Pesawat Udara")
			);
			
			if( $jabatan === "KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI")
			{
				$sumRepresentative = (($totalHariDinas)*($this->REPRESENTATIF));
				$sumAll			  = (($this->mr->sumAll()) + ($sumRepresentative));
			}
			else{
				$sumAll			  = $this->mr->sumAll();
			}	
			
		/*******************************************************************************/
			
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		
		//Header
		$this->fpdf->setFont('Arial','B',12.6);
		$this->fpdf->Text(4.9,1,'KEMENTERIAN KOMUNIKASI DAN INFORMATIKA','C');
		$this->fpdf->Text(1.7,1.6,'DIREKTORAT JENDERAL SUMBER DAYA DAN PERANGKAT POS DAN INFORMATIKA');
		$this->fpdf->Image('../appspj/assets/images/header_line.png',1.7,1.8,18.3,0.3,'PNG');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','',9.5);
		$this->fpdf->Text(1.7,2.7,'Nomor','L');
		$this->fpdf->Text(3.7,2.7,':','L');
		$this->fpdf->Text(1.7,3.2,'N.a','L');
		$this->fpdf->Text(3.7,3.2,':','L');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','BU',12);
		$this->fpdf->Text(9.2,3.7,'K U I T A N S I');
		$this->fpdf->Text(10,5,$this -> mr -> setID);
		$this->fpdf->Ln();
		
		//Content Head
		$this->fpdf->setFont('Arial','',9);
		$this->fpdf->Text(1.7,4.5,'Sudah terima dari','L');
		$this->fpdf->Text(5.1,4.5,':','L');
		$this->fpdf->Text(5.5,4.5,'Kuasa Pengguna Anggaran / Pejabat Pembuat Komitmen Ditjen SDPPI BEKASI','L');				 
		$this->fpdf->Ln();
		
		$arX = 5.4;
		$arY = 4.68;
				
		$this->fpdf->Text(1.7,5,'Uang sebesar','L');
		$this->fpdf->Text(5.1,5,':','L');
		$this->fpdf->SetXY($arX,$arY);
		//$sumAll = 999993;
		$this->fpdf->MultiCell(15,0.55,'Rp. '.$this->mr->value_to_rupiah($sumAll).',- ('.$this->mr->terbilang($sumAll,3).' Rupiah)',0,'L');
		$this->fpdf->Ln();
				
		$this->fpdf->Text(1.7,6.1,'Guna Pembayaran','L');
		$this->fpdf->Text(5.1,6.1,':','L');
		$this->fpdf->Text(5.5,6.1,'Biaya perjalanan dinas (lumpsum) menurut Surat Perintah Perjalanan Dinas','L');		
		$this->fpdf->Ln();
		
		$this->fpdf->Text(1.7,6.7,'Dari','L');
		$this->fpdf->Text(5.1,6.7,':','L');
		$this->fpdf->Text(5.5,6.7,'Direktur Pengendalian ','L');
		$this->fpdf->Text(5.5,7.2,'Direktorat Jenderal Sumber Daya dan Perangkat Pos dan Informatika.','L');
		
				$this->fpdf->Text(5.5,7.7,'Tanggal','L');	  
				$this->fpdf->Text(7.4,7.7,':','L');
				$this->fpdf->Text(7.8,7.7,day($this->mr->getTglSptApprove()),'L');
				$this->fpdf->Ln();
				
				$this->fpdf->Text(5.5,8.2,'SPT','L');
				$this->fpdf->Text(7.4,8.2,':','L');
				$this->fpdf->Text(7.8,8.2,$this->mr->getNoSptApprove(),'L');
				$this->fpdf->Ln();
		
		$this->fpdf->Text(1.7,8.7,'Untuk','L');
		$this->fpdf->Text(5.1,8.7,':','L');
		$this->fpdf->Text(5.5,8.7,'Perjalanan Dinas dari '.$this->mr->getKotaAsal().' ke '.$this->mr->getKotaTujuan().' PP.','L');
		 
		$this->fpdf->Ln();
		
		$this->fpdf->Text(1.7,9.3,'Terbilang','L');
		$this->fpdf->Text(5.1,9.3,':','L');		
		$this->fpdf->SetXY(5.4,8.96);
		$this->fpdf->MultiCell(15,0.55,'Rp. '.$this->mr->value_to_rupiah($sumAll).',-'.' ( '.$this->mr->terbilang($sumAll,3).' Rupiah )',0,'L');
		$this->fpdf->Ln();
		
		//Footer Head		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.9,10.3);		
		$this->fpdf->Cell(6,0.5,'SETUJU DIBAYAR : ',0,0,'C');
		$this->fpdf->SetXY(1,10.68);
		$this->fpdf->Cell(8,0.5,'A.n. KUASA PENGGUNA ANGGARAN /',0,0,'C');
		$this->fpdf->setFont('Arial','',9);
		$this->fpdf->Text(15,10.35,'BEKASI,    '.date("d").' '.month(date("m")).' '.date("Y"));
		$this->fpdf->SetXY(1,11.12);
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->Cell(8,0.5,'PEJABAT PEMBUAT KOMITMEN',0,0,'C');
		$this->fpdf->SetXY(13.5,10.68);
		$this->fpdf->Cell(6,0.5,'Yang bepergian , ',0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','BU',9);
		$this->fpdf->SetXY(1.2,13);
		$this->fpdf->Cell(7,0.5,$this->NAMA_PPK,0,0,'C');
		$this->fpdf->SetXY(13.5,13);
		$this->fpdf->Cell(6,0.5,$namaStaff,0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.2,13.4);			
		$this->fpdf->Cell(7,0.5,'NIP : '.$this->NIP_PPK,0,0,'C');
		$this->fpdf->SetXY(13.5,13.4);
		$this->fpdf->Cell(6,0.5,'NIP : '.$nip,0,0,'C');	
		$this->fpdf->Ln();			
		
		//Content Rincian Biaya
		$this->fpdf->setFont('Arial','BU',10);
		$this->fpdf->Text(6,14.5,'PERINCIAN PERHITUNGAN BIAYA PERJALANAN DINAS');		
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','B',10);
		$this->fpdf->Text(6.9,14.9,'SPPD NO. '.$noSpt,'L');
		
		$this->fpdf->setFont('Arial','B',8.5);
		$this->fpdf->SetXY(1.7,15.3);	
		$this->fpdf->Cell(0.7,0.5,'No ','LRT',0,'C');
		$this->fpdf->Cell(11,0.5,'PERINCIAN BIAYA','TRB',0,'C');
		$this->fpdf->Cell(3.5,0.5,'JUMLAH ','TRB',0,'C');
		$this->fpdf->Cell(3,0.5,'KET','TR',0,'C');		
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','',8.8);		 	
		$this->fpdf->SetX(1.7);	
		$this->fpdf->Cell(0.7,0.5,'I. ','LT',0,'C');
		$this->fpdf->Cell(6,0.5,'Uang Harian Lumpsum '.$totalHariDinas.' ('.$this->mr->terbilang($totalHariDinas).')'.' Hari','L',0,'L');		
		$this->fpdf->Cell(1,0.5,'=','',0,'C');
		$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($uangSaku),'',0,'L');
		$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
		$this->fpdf->Cell(0.9,0.5,$totalHariDinas,'',0,'L');
		$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
		$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($totalUangSaku).' ,-','',0,'R');
		$this->fpdf->Cell(3,0.5,'','LTR',0,'C');				
		$this->fpdf->Ln();
		
		$this->fpdf->SetX(1.7);
		$this->fpdf->Cell(0.7,0.5,'II. ','L',0,'C');
		$this->fpdf->Cell(6,0.5,'Biaya Transportasi (Biaya Rill)','L',0,'L');
		$this->fpdf->Cell(1,0.5,'','',0,'C');
		$this->fpdf->Cell(4,0.5,'','',0,'L');
		$this->fpdf->Cell(3.5,0.5,'','LR',0,'C');
		$this->fpdf->Cell(3,0.5,'','R',0,'C');				
		$this->fpdf->Ln();
			
			$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				//$this->fpdf->Cell(6,0.5,'a. Pesawat Udara / Kendaraan Umum','L',0,'L');
				$this->fpdf->Cell(6,0.5,'a. '.$this->TRANSPORTASI,'L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(4,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'C');
				$this->fpdf->Cell(3,0.5,'','LR',0,'L');
				$this->fpdf->Ln();
				
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'    '.$this->mr->getKotaAsal().' / '.$this->mr->getKotaTujuan().' PP','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(4,0.5,'Rp. '.$this->mr->value_to_rupiah($totalTiket),'',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($totalTiket).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'*)','LR',0,'L');
				$this->fpdf->Ln();
				
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(4,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'L');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');
				$this->fpdf->Ln();
								
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'b. Transportasi lokal tempat kedudukan','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($taxiAsal),'',0,'L');
				$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'2','',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumTaxAsal).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'*)','LR',0,'L');
				$this->fpdf->Ln();

				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'','',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'L');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');
				$this->fpdf->Ln();
								
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'c. Transportasi lokal tempat tujuan','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($taxiTujuan),'',0,'L');
				$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'2','',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumTaxTujuan).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');				
				$this->fpdf->Ln();
				
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'','L',0,'L');
				$this->fpdf->Cell(1,0.5,'','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'','',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','',0,'L');
				$this->fpdf->Cell(3.5,0.5,'','L',0,'L');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');				
				$this->fpdf->Ln();
								
				// tampilan output airport tax di kwitansi//
				$this->fpdf->SetX(1.7);
				$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
				$this->fpdf->Cell(6,0.5,'d. Airport tax','L',0,'L');
				$this->fpdf->Cell(1,0.5,'=','',0,'C');
				$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($sumAirportTax),'',0,'L');
				$this->fpdf->Cell(1.3,0.5,'','',0,'R');
				$this->fpdf->Cell(0.9,0.5,'','R',0,'L');
				$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
				$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumAirportTax).' ,-','',0,'R');
				$this->fpdf->Cell(3,0.5,'','LR',0,'C');				
				$this->fpdf->Ln();
				
				if( $jabatan === "KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI")
				{				
					$this->fpdf->SetX(1.7);
					$this->fpdf->Cell(0.7,0.5,'','L',0,'C');
					$this->fpdf->Cell(6,0.5,'e. Representatif','L',0,'L');
					$this->fpdf->Cell(1,0.5,'=','',0,'C');
					$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($this->REPRESENTATIF),'',0,'L');
					$this->fpdf->Cell(1.3,0.5,'X','',0,'R');
					$this->fpdf->Cell(0.9,0.5,$totalHariDinas,'',0,'L');
					$this->fpdf->Cell(0.8,0.5,'Rp.','L',0,'R');
					$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah(($this->REPRESENTATIF) * ($totalHariDinas)).' ,-','',0,'R');					
					$this->fpdf->Cell(3,0.5,'','LR',0,'C');
					$this->fpdf->Ln();										
				}
				
		$idProvTujuan 	= $this->mr->getIdProvTujuan();
		$TypeInap 		= $this->mr->getHotel();		
		if($TypeInap == 'no' ) //&& ($idProvTujuan = 11 && $idProvTujuan = 12))
		{
			$hargaHotel = $hargaHotel * 0.3;
		}
		$this->fpdf->SetX(1.7);	
		$this->fpdf->Cell(0.7,0.5,'III. ','LT',0,'C');
		$this->fpdf->Cell(6,0.5,'Biaya Penginapan (Biaya Riil)','LT',0,'L');
		$this->fpdf->Cell(1,0.5,'=','T',0,'C');
		$this->fpdf->Cell(1.8,0.5,'Rp. '.$this->mr->value_to_rupiah($hargaHotel),'T',0,'L');
		$this->fpdf->Cell(1.3,0.5,'X','T',0,'R');
		$this->fpdf->Cell(0.9,0.5,($totalHariDinas) - (1),'T',0,'L');
		$this->fpdf->Cell(0.8,0.5,'Rp.','TLB',0,'R');
		$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumHotel).' ,-','TB',0,'R');
		$this->fpdf->Cell(3,0.5,'*)','TLR',0,'L');	
		$this->fpdf->Ln();				
		
		
		$this->fpdf->SetX(1.7);	
		$this->fpdf->Cell(0.7,0.5,'','LB',0,'C');
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->Cell(11,0.5,'Jumlah    ','LB',0,'R');
		$this->fpdf->Cell(0.8,0.5,'Rp.','LB',0,'R');
		$this->fpdf->setFont('Arial','B',8.5);
		$this->fpdf->Cell(2.7,0.5,$this->mr->value_to_rupiah($sumAll).' ,-','B',0,'R');
		$this->fpdf->Cell(3,0.5,'**)','LBR',0,'L');		
		$this->fpdf->Ln();
		
		//Footer Rincian Biaya				
		$this->fpdf->setFont('Arial','',9);
		$this->fpdf->Text(12.3,22.6,'BEKASI ,'.'      '.date("d").' '.month(date("m")).' '.date("Y"),'L');
		
		$this->fpdf->setFont('Arial','',8);
		$this->fpdf->Text(1.7,22.90,'Telah dibayar sejumlah','L');
		$this->fpdf->Text(12,22.90,'Telah menerima uang sebesar','L');
		
		$this->fpdf->setFont('Arial','B',8);
		$this->fpdf->SetXY(1.6,22.91);
		$this->fpdf->Cell(6,0.5,'Rp. '.$this->mr->value_to_rupiah($sumAll).' ,-','',0,'L');
		
		$this->fpdf->SetXY(11.9,22.91);
		$this->fpdf->Cell(6,0.5,'Rp. '.$this->mr->value_to_rupiah($sumAll).' ,-','',0,'L');
		
		$this->fpdf->setFont('Arial','BI',8);
		$this->fpdf->SetXY(1.6,23.28);
		$this->fpdf->MultiCell(8.3,0.3,'('.$this->mr->terbilang($sumAll,3).' Rupiah)',0);
		
		$this->fpdf->SetXY(11.9,23.28);
		$this->fpdf->MultiCell(8.3,0.3,'('.$this->mr->terbilang($sumAll,3).' Rupiah)',0);
		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.5,24);
		$this->fpdf->Cell(6,0.5,'LUNAS DIBAYAR',0,0,'C');
		$this->fpdf->SetXY(1.5,24.4);
		$this->fpdf->Cell(6,0.5,'BENDAHARA PENGELUARAN',0,0,'C');
		$this->fpdf->SetXY(13.5,24);
		$this->fpdf->Cell(6,0.5,'YANG BEPERGIAN',0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','BU',9);
		$this->fpdf->SetXY(1.5,26.2);
		$this->fpdf->Cell(6,0.5,$this->NAMA_BENDAHARA,0,0,'C');
		$this->fpdf->SetXY(13.5,26.2);
		$this->fpdf->Cell(6,0.5,$namaStaff,0,0,'C');
		$this->fpdf->Ln();
		
		$this->fpdf->setFont('Arial','B',9);
		$this->fpdf->SetXY(1.5,26.6);			
		$this->fpdf->Cell(6,0.5,'NIP : '.$this->NIP_BENDAHARA,0,0,'C');
		$this->fpdf->SetXY(13.5,26.6);
		$this->fpdf->Cell(6,0.5,'NIP : '.$nip,0,0,'C');
			
		$this->fpdf->SetAutoPageBreak(FALSE);
		$this->fpdf->setFont('Arial','',7);
		$this->fpdf->SetXY(1.5,27.4);			
		$this->fpdf->Cell(1,0.3,'*)',0,'L');
		$this->fpdf->SetXY(2.5,27.4);			
		$this->fpdf->MultiCell(8.5,0.3,'Apabila tidak didapatkan bukti pengeluaran/kwitansi yang sah harus membuat Surat Pernyataan Pengeluaran Riil terlampir.',0,'L');
		$this->fpdf->SetXY(1.5,28.1);
		$this->fpdf->Cell(1,0.3,'**)',0,'L');
		$this->fpdf->SetXY(2.5,28.1);			
		$this->fpdf->MultiCell(8.5,0.3,'Apabila dikemudian hari terdapat kesalahan dan atau kelebihan atas pembayaran Perjalanan Dinas, tersebut, kami (yang bepergian) bertanggung jawab sepenuhnya dan bersedia menyetor atas kesalahan dan atau kelebihan pembayaran tersebut ke rekening Kas Negara.',0,'L');
		
		$this->fpdf->Output();
		//$this->fpdf->Output('Kuitansi Perjalanan Dinas_'.ucwords(mb_strtolower($namaStaff)).'_'.date("d_m_Y").'.pdf','D');
	}
	//akhir fungsi cetak kwitansi
	function cetakSppd() {
		$id = $this->uri->segment(3);
		$this->mr->setID($id);
		
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		
		//Header
		$this->fpdf->setFont('Arial','B',11);
		$this->fpdf->Text(6.5,1.7,'KEMENTERIAN KOMUNIKASI DAN INFORMATIKA');
		$this->fpdf->Text(3,2.3,'DIREKTORAT JENDERAL SUMBER DAYA DAN PERANGKAT POS DAN INFORMATIKA');		
		
		$this->fpdf->setFont('Verdana','',9);
		$this->fpdf->Text(4.4,3.2,'Jl. BINTARA RAYA No.17');
		$this->fpdf->Text(13,3.2,'Lembar ke','L');
		$this->fpdf->Text(15,3.2,':','L');
		$this->fpdf->Text(15.3,3.2,'............................','L');
		$this->fpdf->Ln();
		$this->fpdf->Text(13,3.7,'Kode No','L');
		$this->fpdf->Text(15,3.7,':','L');
		$this->fpdf->Text(15.3,3.7,'............................','L');		
		$this->fpdf->Text(4.4,3.7,'BEKASI BARAT 17134 ');
		$this->fpdf->Ln();
		$this->fpdf->Text(13,4.2,'Nomor','L');		
		$this->fpdf->Text(15,4.2,':','L');
		$this->fpdf->Text(15.3,4.2,'............................','L');
		$this->fpdf->Ln();	
		
		$this->fpdf->Image('../appspj/assets/images/header_line.png',3,4.5,16,0.3,'PNG');
		
		$this->fpdf->setFont('Arial','B',11);
		$this->fpdf->Text(7,5.7,'SURAT PERINTAH PERJALANAN DINAS');
		$this->fpdf->Text(7,5.7,'___________________________________');	
		
		//Content
		$this->fpdf->setFont('Arial','',10);
		$this->fpdf->SetXY(3,6.5);	
		$this->fpdf->Cell(0.7,1.3,'1. ',1,0,'L');
		$this->fpdf->Cell(7.6,1.3,'Pejabat berwenang yang memberi perintah','TBR',0,'L');
		$this->fpdf->setFont('Arial','',9.25);
		$this->fpdf->MultiCell(7.7,0.65,'KEPALA BBPPT, SESUAI SPT NO. '.$this->mr->getNoSptApprove(),'TRB');
		$this->fpdf->Ln();
		
		$sql = ("SELECT	pmd.id_perjalanan,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						pm.tiket1						
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_detail = '".$id."'");
				 
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
			$idPerjalanan	= $row->id_perjalanan;
			$namaStaff 		= $row->nama;
			$nip 			= $row->nip;
			$golongan 		= $row->golongan;		   		   		   
			$pangkat 		= $row->pangkat;
			$jabatan 		= $row->jabatan;			
			$tiket1			= $row->tiket1;
		}
		
		$this->mr->setIdPerjalanan($idPerjalanan);
		$this->fpdf->setFont('Arial','',10);
		$this->fpdf->SetXY(3,7.8);	
		$this->fpdf->Cell(0.7,0.5,'2. ','LBR',0,'L');
		$this->fpdf->Cell(7.6,0.5,'Nama pegawai yang diperintahkan','BR',0,'L');
		$this->fpdf->Cell(7.7,0.5,$namaStaff,'BR',0,'L');
		$this->fpdf->Ln();
		
		$this->fpdf->SetXY(3,8.3);	
		$this->fpdf->Cell(0.7,0.5,'3. ','LR',0,'L');
					$this->fpdf->Cell(7.6,0.5,'a. Pangkat dan golongan menurut PGPS 1968','R',0,'L');
					$this->fpdf->Cell(7.7,0.5,'a. '.$golongan.' / '.$pangkat,'R',0,'L');
					$this->fpdf->Ln();
					
					$this->fpdf->SetX(3);
					$this->fpdf->Cell(0.7,0.5,' ','LR',0,'L');
					$this->fpdf->Cell(7.6,0.5,'b. Jabatan','R',0,'L');
					$this->fpdf->Cell(7.7,0.5,'b. '.$jabatan,'R',0,'L');
					$this->fpdf->Ln();
					
					$this->fpdf->SetX(3);
					$this->fpdf->Cell(0.7,0.5,' ','L',0,'L');
					$this->fpdf->Cell(7.6,0.5,'c. Gaji Pokok','LR',0,'L');
					$this->fpdf->Cell(7.7,0.5,'c. ','R',0,'L');
					$this->fpdf->Ln();
					
					$this->fpdf->SetX(3);
					$this->fpdf->Cell(0.7,0.5,' ','LBR',0,'L');
					$this->fpdf->Cell(7.6,0.5,'d. Pangkat menurut peraturan perjalanan dinas','BR',0,'L');
					$this->fpdf->Cell(7.7,0.5,'d. ','BR',0,'L');		
		$maksud = $this->mr->getMaksud();
		$w = 1; $x = 1;
		$inW = array(0.5,1,1.5,2,2.5,3,3.5,4,4.5,5); 
			 if((strlen($maksud)) >= 370 && (strlen($maksud)) < 400) {$x = $inW[9] ;$w = $inW[0] ;}
		else if((strlen($maksud)) >= 300 && (strlen($maksud)) < 370) {$x = $inW[6] ;$w = $inW[0] ;}
		else if((strlen($maksud)) >= 240 && (strlen($maksud)) < 300) {$x = $inW[7] ;$w = $inW[0] ;}
		else if((strlen($maksud)) >= 190 && (strlen($maksud)) < 240) {$x = $inW[5] ;$w = $inW[0] ;}
		else if((strlen($maksud)) >= 150 && (strlen($maksud)) < 190) {$x = $inW[4] ;$w = $inW[0] ;} 
		else if((strlen($maksud)) >= 93  && (strlen($maksud)) < 150) {$x = $inW[2] ;$w = $inW[0] ;}
		else if((strlen($maksud)) > 43  && (strlen($maksud)) < 93)  {$w = $inW[0] ;} // range
		//else if((strlen($maksud)) >= 30  && (strlen($maksud)) < 49 ) {$w = $inW[1] ;} // range
				
		$this->fpdf->Ln();
		$this->fpdf->setFont('Arial','',10);			
		$this->fpdf->SetX(3);	
		$this->fpdf->MultiCell(0.7,$x,'4. ','LR',1);
		$this->fpdf->SetXY(3.7,10.3);
		$this->fpdf->MultiCell(7.6,$x,'Maksud perjalanan dinas','','L');
		$this->fpdf->setFont('Arial','',9.5);
		$this->fpdf->SetXY(11.3,10.3);
		$this->fpdf->MultiCell(7.7,$w,$maksud,'LR');
		
		if ($tiket1 ==''){
			//if($jabatan == 'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI' || $jabatan == 'STAF BAGIAN UMUM')
				// $this->setTransportasi("Kendaraan Dinas");
			//else
			$this->setTransportasi("Kendaraan Umum");
		}else(
				$this->setTransportasi("Pesawat Udara")
		);
		$this->fpdf->setFont('Arial','',10);				
		$this->fpdf->SetX(3);	
		$this->fpdf->Cell(0.7,0.5,'5. ','LTB',0,'L');
		$this->fpdf->Cell(7.6,0.5,'Alat angkutan yang dipergunakan','LTB',0,'L');
		$this->fpdf->Cell(7.7,0.5,$this->getTransportasi(),'LTRB',0,'L');
		$this->fpdf->Ln();			
		
		$this->fpdf->SetX(3);	
		$this->fpdf->Cell(0.7,0.5,'6. ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'a. Tempat berangkat','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,$this->mr->getKotaAsal(),'R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'  ','LB',0,'L');
			$this->fpdf->Cell(7.6,0.5,'b. Tempat tujuan','LB',0,'L');
			$this->fpdf->Cell(7.7,0.5,$this->mr->getKotaTujuan(),'LBR',0,'L');
		$this->fpdf->Ln();				
		
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi pm inner join dinas d on pm.dinas = d.id where pm.id = '".$idPerjalanan."'");
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat = $row->berangkat;		   		   
		   $tanggalKembali 	 = $row->kembali;
		}
		
		$totalHariDinas = $this->mr->getTotalHari($tanggalKembali,$tanggalBerangkat);
		
		$this->fpdf->SetX(3);	
		$this->fpdf->Cell(0.7,0.5,'  ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'a. Lama perjalanan','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,'a. '.$totalHariDinas.' ('.$this->mr->terbilang($totalHariDinas).')'.' Hari','R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'7. ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'b. Tanggal berangkat','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,'b. '.day($tanggalBerangkat),'R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'  ','LBR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'c. Tanggal kembali','BR',0,'L');
			$this->fpdf->Cell(7.7,0.5,'c. '.day($tanggalKembali),'BR',0,'L');
		$this->fpdf->Ln();
							
		$this->fpdf->SetX(3);	
		$this->fpdf->Cell(0.7,0.5,'  ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'Pengikut: Nama Umur Hubungan Keluarga','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,' ','R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'  ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'1. ','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,' ','R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'8. ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'2. ','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,' ','R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'  ','LBR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'3.','BR',0,'L');
			$this->fpdf->Cell(7.7,0.5,' ','BR',0,'L');
		$this->fpdf->Ln();
			
		$this->fpdf->SetX(3);	
		$this->fpdf->Cell(0.7,0.5,'  ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'Pembebasan Anggaran','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,'','R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'9. ','LR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'a. Instalasi','R',0,'L');
			$this->fpdf->Cell(7.7,0.5,'Ditjen SDPPI , BEKASI','R',0,'L');
			$this->fpdf->Ln();
			
			$this->fpdf->SetX(3);	
			$this->fpdf->Cell(0.7,0.5,'  ','LBR',0,'L');
			$this->fpdf->Cell(7.6,0.5,'b. Mata Anggaran :	','BR',0,'L');
			$this->fpdf->Cell(7.7,0.5,'DIPA Ditjen SDPPI Tahun '.date('Y'),'BR',0,'L');
		$this->fpdf->Ln();					
		
		$this->fpdf->SetX(3);	
		$this->fpdf->Cell(0.7,1.4,'10. ','LBR',0,'L');
		$this->fpdf->Cell(7.6,1.4,'Keterangan lain-lain :','BR',0,'L');
		$this->fpdf->MultiCell(7.7,0.7,$this->KET_SPPD,'BR','L');				
		
		//Footer
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->Text(10.7,22.5,'Dikeluarkan di');
		$this->fpdf->Text(13.5,22.5,':');
		$this->fpdf->Text(14.4,22.5,'J A K A R T A ');
		
		$this->fpdf->Text(10.7,23.1,'Pada Tanggal');
		$this->fpdf->Text(13.5,23.1,':');
		$this->fpdf->Text(14.4,23.1,month(date('m')).' '.date('Y'));		
		$this->fpdf->Text(10.7,23.2,'_________________________________');
		
		$this->fpdf->setFont('Arial','B',11);
		$this->fpdf->Text(10.7,23.8,'A.N. KUASA PENGGUNA ANGGARAN /');
		$this->fpdf->Text(11.2,24.4,'PEJABAT PEMBUAT KOMITMEN');
		
		$this->fpdf->setFont('Arial','BU',11);
		$this->fpdf->SetXY(11,26.5);
		$this->fpdf->Cell(6,0.5,$this->NAMA_PPK,0,'','C');		
		$this->fpdf->setFont('Arial','B',11);
		$this->fpdf->SetXY(11,27);		
		$this->fpdf->Cell(6,0.5,'NIP : '.$this->NIP_PPK,0,'','C');
		
			
		$this->fpdf->Output();
		//$this->fpdf->Output('Surat Perintah Perjalanan Dinas_'.ucwords(mb_strtolower($namaStaff)).'_'.date("d_m_Y").'.pdf','D');		
	}
	function cetakReviewSpj(){
		$idPerjalanan = $this->uri->segment(3);
		$this->mr->setIdPerjalanan($idPerjalanan);
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		
		//Header
		//$this->fpdf->SetFont('helvetica','',11);
		//$this->fpdf->SetTextColor(5, 76, 143);
		//$this->fpdf->Text(3.5,1.2,'KEMENTERIAN KOMUNIKASI DAN INFORMATIKA REPUBLIK INDONESIA');
		//$this->fpdf->Text(3.5,1.7,'DIREKTORAT JENDERAL SUMBER DAYA DAN PERANGKAT POS DAN INFORMATIKA');
		//$this->fpdf->Text(3.5,2.2,'BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI');		
		//$this->fpdf->Image('../appspj/assets/images/informasi.jpg',3.3,2.4,8.5,0.65,'JPG');				
		//$this->fpdf->SetFont('helvetica','',9);
		//$this->fpdf->Text(3.5,3.4,'Jl. BINTARA RAYA No.17');
		//$this->fpdf->Text(9,3.4,'Tel : 021 - 3835992');
		//$this->fpdf->Text(13,3.4,'Fax : 021 - 3522915');
		//$this->fpdf->Text(16.9,3.4,'www.depkominfo.go.id');
		//$this->fpdf->Text(3.5,3.8,'BEKASI BARAT 17134');
		//$this->fpdf->Text(10.53,3.8,'3835977');
		//$this->fpdf->Text(17.7,3.8,'www.postel.go.id');		
		//$this->fpdf->Image('../appspj/assets/images/kominfo.jpg',1,0.8,2.3,2.6,'JPG');
		//$this->fpdf->Image('../appspj/assets/images/header_line_blue.png',1,4,19.1,0.2,'PNG');		
		
		//Content
		$sql = ("SELECT	pmd.id_detail,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						k.kota,
						pmd.no_spt,
						pm.tgl_spt,
						pm.tiket1
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_perjalanan = '".$idPerjalanan."'");		
		$query = $this->db->query($sql);				
		foreach($query->result() as $row) {
			$id_detail		= $row->id_detail;
			$namaStaff 		= $row->nama;
			$nip 			= $row->nip;
			$golongan	 	= $row->golongan;		   		   		   
			$pangkat 		= $row->pangkat;
			$jabatan 		= $row->jabatan;	
			$tujuan 		= $row->kota;
			$noSpt 			= $row->no_spt;
			$tglSpt 		= $row->tgl_spt;
			$tiket1 		= $row->tiket1;
		}
		//Set variable Id Perjalanan to class ModelReport
		$this->mr->setID($id_detail);
		$resultNotaDinas 	= $this->mr->getNotaDinas();		
		$nomorNotaDinas		= $resultNotaDinas[0];
		$tanggalNotaDinas	= $resultNotaDinas[1];
		$tentangNotaDinas 	= $resultNotaDinas[2];
		$isExtraNotes		= $resultNotaDinas[3];
		
		$this->fpdf->SetTextColor(0, 0, 0);
		$this->fpdf->SetFont('Arial','U',12);
		$this->fpdf->Text(6.8,5.3,'SURAT PERINTAH PELAKSANAAN TUGAS');		
		$this->fpdf->setFont('Arial','',12);
		$this->fpdf->Text(6.3,5.9,'Nomor :    '.$noSpt,'L');		
		
		$this->fpdf->setFont('Arial','',10);
		$this->fpdf->setXY(2.6,6.8);
		$this->fpdf->MultiCell(0.6,0.5,'A.','');
		$this->fpdf->setXY(3.3,6.8);
		$this->fpdf->MultiCell(6.5,0.5,'Pejabat Pemberi Tugas','');
		$this->fpdf->setXY(8.5,6.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.2,6.8);
		$this->fpdf->MultiCell(9.3,0.5,'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI','');
		
		//for Single Staff
		$this->fpdf->setXY(2.6,7.8);
		$this->fpdf->MultiCell(0.6,0.5,'B.','');
		$this->fpdf->setXY(3.3,7.8);
		$this->fpdf->MultiCell(6.5,0.5,'Dasar Pelaksanaan Tugas','');
		$this->fpdf->setXY(8.5,7.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(3.3,8.4);			
		$this->fpdf->MultiCell(5.6,0.5,'1. Undangan',0);
		$this->fpdf->setXY(8.5,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.3,8.4);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.1,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.4);
		$this->fpdf->MultiCell(8,0.5,$nomorNotaDinas);
		$this->fpdf->setXY(9.3,8.9);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.1,8.9);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.9);
		$this->fpdf->MultiCell(8,0.5,day($tanggalNotaDinas));
		$this->fpdf->setXY(9.3,9.4);
		$this->fpdf->MultiCell(2,0.5,'Tentang');
		$this->fpdf->setXY(11.1,9.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,9.4);
		$this->fpdf->MultiCell(7.5,0.5,$tentangNotaDinas);
		
		/**
		 * add nota tambahan(Rutin/Undangan [Internal or Eksternal],Rutin,)Kontrak , SK TIM)
		 */
		$headerNote = '';
		if(!empty($isExtraNotes))
		{
				$jenisNotaExtra = $this->mr->getJenisNotaDinasExtra();
				if($jenisNotaExtra == 'Internal' || $jenisNotaExtra == 'Eksternal'){
				   $headerNote = '2. Nota-Dinas';}
				else if($jenisNotaExtra == 'Kontrak'){ $headerNote = '2. Kontrak';}
				else if($jenisNotaExtra == 'Tim'){ $headerNote = '2. SK.TIM';}		
				$ResultExtraNotes = $this->mr->getExtraNotaDinas();
		
		$nomorExtra 	= $ResultExtraNotes[0];
		$tanggalExtra	= $ResultExtraNotes[1];
		$perihalExtra	= $ResultExtraNotes[2];
		
		$koorY = 10.9;
		$rs = 0.5;
		$this->fpdf->Ln();
		$this->fpdf->setXY(3.3,$koorY);			
		$this->fpdf->MultiCell(5.6,0.5,$headerNote);
		$this->fpdf->setXY(9.3,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,$nomorExtra);
		
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,day($tanggalExtra));
		$koorY = $koorY + $rs;		
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Perihal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(7.5,0.5,$perihalExtra);
		}else{
			$koorY = 10.9;
			$rs = 0.5;
		}
		$rs = 1;
		$koorY = $koorY +$rs;//15.9;
		$rr = 2.5;		
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->MultiCell(0.6,0.5,'C.','');
		$this->fpdf->setXY(3.3,$koorY);
		$this->fpdf->MultiCell(6.5,0.5,'Pegawai yang ditugaskan','');
		$this->fpdf->setXY(8.5,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(2.5,$koorY);
		$this->fpdf->MultiCell(1,1,'NO','LTRB','C');
		$this->fpdf->setXY(3.5,$koorY);
		$this->fpdf->MultiCell(4.5,1,'N   A   M   A  ','TB','C');
		$this->fpdf->setXY(8,$koorY);
		$this->fpdf->MultiCell(5.5,1,'GOL / NIP','LTB','C');
		$this->fpdf->setXY(13.5,$koorY);
		$this->fpdf->MultiCell(6,1,'JABATAN','LTRB','C');
		$this->fpdf->Ln();
		$rs =0.5;
		$koorX = array(2.5, 3.5, 8, 8, 13.5); 
		$rr = 1;
		$koorY = $koorY + $rr;
		$this->fpdf->setXY($koorX[0],$koorY);
		$this->fpdf->Cell(1,1,'1.','LRB','C');
		$this->fpdf->setX($koorX[1],$koorY);
		$this->fpdf->Cell(4.5,1,' '.ucwords(mb_strtolower($namaStaff)),'B','L');
		$this->fpdf->setX($koorX[2],$koorY);
		$this->fpdf->Cell(5.5,0.5,' '.$pangkat.' '.$golongan,'L','L');
		$this->fpdf->setXY($koorX[3],$koorY + $rs);
		$this->fpdf->Cell(5.5,0.5,' '.'NIP : '.$nip,'LB','L');
		$this->fpdf->setXY($koorX[4],$koorY);
		if(strlen($jabatan) < 28){
			$this->fpdf->MultiCell(6,1,$jabatan,'LRB','L');
		}else{
			$this->fpdf->MultiCell(6,0.5,$jabatan,'LRB','L');
		}
		$koorY = $koorY + 1.2;
		$this->fpdf->Ln();
		$this->fpdf->setXY(2.6,$koorY );
		$this->fpdf->Cell(0.6,0.5,'D.','');		
		$this->fpdf->Cell(5.3,0.5,'Maksud dan Tujuan Penugasan','');		
		$this->fpdf->Cell(0.5,0.5,':');		
		
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi p inner join dinas d on p.dinas = d.id where p.id = '".$idPerjalanan."'");
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat 	= $row->berangkat;		   		   
		   $tanggalKembali 		= $row->kembali;
		}
			$koorY = $koorY + 1;
			$this->fpdf->Ln();
			$this->fpdf->Text(3.25,$koorY,'1. Tempat Tujuan');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getKotaTujuan());
			$rt = 0.7;
			$koorY = $koorY + $rt ;	
			$this->fpdf->Text(3.25,$koorY,'2. Tanggal Kegiatan');
			$this->fpdf->Text(8,$koorY,':');
			$tgl_berangkat = str_split($tanggalBerangkat,4);
			$this->fpdf->Text(8.3,$koorY,$tgl_berangkat[2].' s/d '.day($tanggalKembali));
			$koorY = $koorY + $rt ;
			$this->fpdf->Text(3.65,$koorY,'');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getTotalHariDinas().' ('.$this->mr->terbilang($this->mr->getTotalHariDinas()).')'.' Hari');
			$rt = 0.6;
			$koorY = $koorY + $rt ;
			$this->fpdf->Text(3.25,$koorY,'3. Untuk');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->SetXY(8.2,$koorY-0.35);
			$this->fpdf->MultiCell(11.3,0.5,$this->mr->getMaksud().'.','');
			$rt = 0.6;
			
			if (empty($tiket1)){
				//if($jabatan == 'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI')
					 //$this->setTransportasi("Kendaraan Dinas");
				//else 
				$this->setTransportasi("Kendaraan Umum");
			}else(
				$this->setTransportasi("Pesawat Udara")
			);
		
			$koorY = $koorY + (2.5 * $rt);			
			$this->fpdf->Text(3.25,$koorY,'4. Alat Transportasi');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->TRANSPORTASI);
			$this->fpdf->Ln();
		
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->Cell(0.6,0.5,'E.','');		
		$this->fpdf->Cell(4.7,0.5,'Keterangan lain-lain','');		
		$this->fpdf->Cell(0.5,0.5,':');
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'1.','');
		$this->fpdf->MultiCell(15.5,0.5,'Melaporkan hasil pelaksanaan tugas selambat-lambatnya 7 (tujuh) hari kerja setelah pelaksanaan dengan melampirkan dokumen pendukung administrasi lainnya sesuai peraturan perundang - undangan yang berlaku.','');
		$rt = 2;
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'2.','');
		$this->fpdf->MultiCell(15.5,0.5,'Para nama pegawai yang ditugaskan agar melaksanakan tugas ini dengan penuh tanggung jawab dan berlaku sejak tanggal ditetapkan.','');
		$koorY = $koorY+$rt;
		$this->fpdf->setXY(3.2,$koorY-0.5);
		$this->fpdf->Cell(0.6,0.5,'3.','');
		$this->fpdf->MultiCell(15.5,0.5,'Segala biaya yang dikeluarkan berkenaan dengan pelaksanaan kegiatan ini dibebankan pada kegiatan tersebut diatas.','');
		//Footer
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->Text(12,3,'Ditetapkan');
		$this->fpdf->Text(15,3,':');
		$this->fpdf->Text(15,3,'         BEKASI');
		
		$this->fpdf->setFont('Arial','U',11);
		$this->fpdf->Text(12,3.5,'Pada tanggal             ');
		$this->fpdf->Text(15,3.5,':');
		$this->fpdf->Text(15,3.5,'         '.month(date("m")).' '.date("Y "));		
		if($namaStaff === "DWI HANDOKO"){
				$TTD 	= $this->NAMA_PLT;
				$JAB_PLT = 'SEKRETARIS DIREKTORAT JENDERAL SDPPI';}
		else {$TTD = $this->NAMA_DIREKTUR;
			  $JAB_PLT = 'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI';}
		
		$this->fpdf->setFont('Arial','B',10);
		if($namaStaff === "DWI HANDOKO"){
			$this->fpdf->Text(12.2,4.5,$JAB_PLT);
		}else{
			$this->fpdf->Text(12.2,4.5,substr($JAB_PLT,0,28));
			$this->fpdf->Text(12.5,5.0,substr($JAB_PLT,29));
		}	
		
		$this->fpdf->SetXY(12.7,6.5);
		$this->fpdf->Cell(6,0.5,$TTD,0,'','C');
		
		$this->fpdf->Ln();		
		$this->fpdf->setFont('Verdana','B',9);
		$this->fpdf->Text(2.6,8,'Tembusan :');
		$this->fpdf->Text(2.6,8.05,'_________');
		$this->fpdf->setFont('Verdana','',9);
		$this->fpdf->Text(2.6,8.5,'Disampaikan Yth. kepada :');
		$this->fpdf->Text(2.6,9,'1. Kabag Keuangan');
		$this->fpdf->Text(2.6,9.5,'2. Kasubag TU Dit. Pengendalian');		
		$this->fpdf->Text(3,10,'SDPPI, mohon penyiapan');
		$this->fpdf->Text(3,10.5,'SPPD/BOP bagi ybs');
		
		/*****
		//Insert Page Number
		$this->fpdf->setFont('Arial','',7);				
		$this->fpdf->Text(10.4,28.7, 'Page ' . $this->fpdf->PageNo());		
		******/		
		$this->fpdf->Output();
		//$this->fpdf->Output('pdf/SPPT '.ucwords(mb_strtolower($namaStaff)).' '.date("dmY").'.pdf','F');		
	}	
	function cetakReviewSpjMulti() {		
		$id = $this->uri->segment(3);				
		$this->mr->setIdPerjalanan($id);
				
		$this->fpdf->FPDF('P','cm','A4');
		$this->fpdf->AddPage();
		$this->fpdf->Ln();
		$this->fpdf->AcceptPageBreak();
		$this->fpdf->SetMargins(2,2,2);
		//Header
		//$this->fpdf->SetFont('helvetica','',11);
		//$this->fpdf->SetTextColor(5, 76, 143);
		//$this->fpdf->Text(3.5,1.2,'KEMENTERIAN KOMUNIKASI DAN INFORMATIKA REPUBLIK INDONESIA');
		//$this->fpdf->Text(3.5,1.7,'DIREKTORAT JENDERAL SUMBER DAYA DAN PERANGKAT POS DAN INFORMATIKA');
		//$this->fpdf->Text(3.5,2.2,'BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI');		
		//$this->fpdf->Image('../appspj/assets/images/informasi.jpg',3.3,2.4,8.5,0.65,'JPG');				
		//$this->fpdf->SetFont('helvetica','',9);
		//$this->fpdf->Text(3.5,3.4,'Jl. BINTARA RAYA No.17');
		//$this->fpdf->Text(9,3.4,'Tel : 021 - 3835992');
		//$this->fpdf->Text(13,3.4,'Fax : 021 - 3522915');
		//$this->fpdf->Text(16.9,3.4,'www.depkominfo.go.id');
		//$this->fpdf->Text(3.5,3.8,'BEKASI BARAT 17134');
		//$this->fpdf->Text(10.53,3.8,'3835977');
		//$this->fpdf->Text(17.7,3.8,'www.postel.go.id');		
		//$this->fpdf->Image('../appspj/assets/images/kominfo.jpg',1,0.8,2.3,2.6,'JPG');
		//$this->fpdf->Image('../appspj/assets/images/header_line_blue.png',1,4,19.1,0.2,'PNG');
		
		$sql = ("select pmd.personil from perjalanan_multi_detail pmd where pmd.id_perjalanan= '".$id."'");
		$query = $this->db->query($sql);
		$arrayPersonil = array();		
		foreach ($query->result() as $row) { $arrayPersonil[]= $row->personil; }			
		//Content
		$sql = ("SELECT	pmd.id_detail,
						s.nama,
						s.nip,
						p.golongan,
						s.jabatan,
						p.pangkat,
						k.kota,
						pmd.no_spt,
						pm.tgl_spt,
						pm.tiket1
				FROM
						pangkat p
						INNER JOIN staff s ON p.id = s.golongan
						INNER JOIN perjalanan_multi_detail pmd ON s.id = pmd.personil
						INNER JOIN perjalanan_multi pm ON pmd.id_perjalanan = pm.id
						INNER JOIN dinas d ON pm.dinas = d.id
						INNER JOIN kota k ON d.kota_tujuan = k.id
				WHERE
						pmd.id_perjalanan = '".$id."'
				ORDER BY pmd.id_detail");
						
		$query = $this->db->query($sql);
		$namaStaff 	= array();
		$nip 		= array();
		$golongan 	= array();
		$jabatan 	= array();	
		$tujuan 	= array();
		$noSpt 		= array();
		$tglSpt 	= array();		 	
		
		foreach ($query->result() as $row)
		{
			$namaStaff []	= $row->nama;
			$nip []			= $row->nip;
			$golongan []	= $row->golongan;		   		   		   
			$pangkat []		= $row->pangkat;
			$jabatan []		= $row->jabatan;	
			$tujuan []		= $row->kota;
			$noSpt []		= $row->no_spt;
			$tglSpt []		= $row->tgl_spt;  	
			$tiket1 		= $row->tiket1;  	
		}
		//Set variable Id Perjalanan to class ModelReport					
		$resultNotaDinas 	= $this->mr->getNotaDinas();		
		$nomorNotaDinas		= $resultNotaDinas[0];
		$tanggalNotaDinas	= $resultNotaDinas[1];
		$tentangNotaDinas 	= $resultNotaDinas[2];
		$isExtraNotes		= $resultNotaDinas[3];
				
		$this->fpdf->SetTextColor(0, 0, 0);
		$this->fpdf->SetFont('Arial','U',12);
		$this->fpdf->Text(6.8,5.3,'SURAT PERINTAH PELAKSANAAN TUGAS');		
		$this->fpdf->setFont('Arial','',12);
		$this->fpdf->Text(6.3,5.9,'Nomor :    '.$noSpt[0],'L');		
		
		$this->fpdf->setFont('Arial','',10);
		$this->fpdf->setXY(2.6,6.8);
		$this->fpdf->MultiCell(0.6,0.5,'A.','');
		$this->fpdf->setXY(3.3,6.8);
		$this->fpdf->MultiCell(6.5,0.5,'Pejabat Pemberi Tugas','');
		$this->fpdf->setXY(8.5,6.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.2,6.8);
		$this->fpdf->MultiCell(9.3,0.5,'KEPALA BALAI BESAR PENGUJIAN PERANGKAT TELEKOMUNIKASI','');
		
		
		//for Multi Staff
		$this->fpdf->setXY(2.6,7.8);
		$this->fpdf->MultiCell(0.6,0.5,'B.','');
		$this->fpdf->setXY(3.3,7.8);
		$this->fpdf->MultiCell(6.5,0.5,'Dasar Pelaksanaan Tugas','');
		$this->fpdf->setXY(8.5,7.8);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(3.3,8.4);			
		$this->fpdf->MultiCell(5.6,0.5,'1. Undangan',0);
		$this->fpdf->setXY(8.5,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(9.3,8.4);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.1,8.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.4);
		$this->fpdf->MultiCell(8,0.5,$nomorNotaDinas);
		$this->fpdf->setXY(9.3,8.9);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.1,8.9);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,8.9);
		$this->fpdf->MultiCell(8,0.5,day($tanggalNotaDinas));
		$this->fpdf->setXY(9.3,9.4);
		$this->fpdf->MultiCell(2,0.5,'Tentang');
		$this->fpdf->setXY(11.1,9.4);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(11.4,9.4);
		$this->fpdf->MultiCell(7.5,0.5,$tentangNotaDinas);
		
		/**
		 * add nota tambahan(Rutin/Undangan [Internal or Eksternal],Rutin,)Kontrak , SK TIM)
		 */
		$headerNote = '';
		if(!empty($isExtraNotes))
		{
				$jenisNotaExtra = $this->mr->getJenisNotaDinasExtra();
				if($jenisNotaExtra == 'Internal'){$headerNote = '2. Nota-Dinas';}
				else if($jenisNotaExtra == 'Eksternal'){$headerNote = '2. Undangan';}
				else if($jenisNotaExtra == 'Kontrak'){ $headerNote = '2. Kontrak';}
				else if($jenisNotaExtra == 'Tim'){ $headerNote = '2. SK.TIM';}		
				$ResultExtraNotes = $this->mr->getExtraNotaDinas();
		
		$nomorExtra 	= $ResultExtraNotes[0];
		$tanggalExtra	= $ResultExtraNotes[1];
		$perihalExtra	= $ResultExtraNotes[2];
		
		$koorY = 10.9;
		$rs = 0.5;
		$this->fpdf->Ln();
		$this->fpdf->setXY(3.3,$koorY);			
		$this->fpdf->MultiCell(5.6,0.5,$headerNote);
		$this->fpdf->setXY(9.3,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Nomor');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,$nomorExtra);
		
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Tanggal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(8,0.5,day($tanggalExtra));
		$koorY = $koorY + $rs;		
		$this->fpdf->setXY(10.3,$koorY);
		$this->fpdf->MultiCell(2,0.5,'Perihal');
		$this->fpdf->setXY(11.9,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$this->fpdf->setXY(12.2,$koorY);
		$this->fpdf->MultiCell(7.5,0.5,$perihalExtra);
		}else{
			$koorY = 8;
			$rs = 0.5;
		}
		$rr = 2.5;
		$koorY = $koorY + $rr;
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->MultiCell(0.6,0.5,'C.','');
		$this->fpdf->setXY(3.3,$koorY);
		$this->fpdf->MultiCell(6.5,0.5,'Pegawai yang ditugaskan','');
		$this->fpdf->setXY(8.5,$koorY);
		$this->fpdf->MultiCell(0.5,0.5,':');
		$koorY = $koorY + $rs;
		$this->fpdf->setXY(2.5,$koorY);
		$this->fpdf->MultiCell(1,1,'NO','LTR','C');
		$this->fpdf->setXY(3.5,$koorY);
		$this->fpdf->MultiCell(4.5,1,'N   A   M   A  ','T','C');
		$this->fpdf->setXY(8,$koorY);
		$this->fpdf->MultiCell(5.5,1,'GOL / NIP','LT','C');
		$this->fpdf->setXY(13.5,$koorY);
		$this->fpdf->MultiCell(6,1,'JABATAN','LTR','C');
		$this->fpdf->Ln();
		$rs = 1;
		//$koorY = $this->fpdf->getY();
		$koorY = $koorY +$rs;//15.9;
		$no = 1;
		$counter = 0;		
		for($i = 0 ; $i < sizeof($namaStaff);$i++){
			$koorX = array(2.5, 3.5, 8, 8, 13.5);
			$rr = 1;					
			$this->fpdf->setXY($koorX[0],$koorY);
			$this->fpdf->Cell(1,1,$no.'.','LRBT','','C');			
			$this->fpdf->Cell(4.5,1,' '.ucwords(mb_strtolower($namaStaff[$i])),'BT','','L');			
			$this->fpdf->Cell(5.5,0.5,' '.$pangkat[$i].' '.$golongan[$i],'LT','','L');
			$this->fpdf->setXY($koorX[3],$koorY+0.5);			
			$this->fpdf->Cell(5.5,0.5,' '.'NIP : '.$nip[$i],'LB','','L');
			$this->fpdf->setXY($koorX[4],$koorY);			
			if(strlen($jabatan[$i]) <= 26 ){
				$this->fpdf->MultiCell(6,1,$jabatan[$i],'LRBT','L');
			}else if(strlen($jabatan[$i]) > 26){
				if($jabatan[$i] == "KASI STANDAR PENERTIBAN PPI")
				$this->fpdf->MultiCell(6,1,$jabatan[$i],'LRBT','');
				else $this->fpdf->MultiCell(6,0.5,$jabatan[$i],'LRBT','');
			}			
			$koorY = $koorY+$rr;
			if ($koorY >= 26.5){
				$this->fpdf->AddPage();	
				$koorY = 1;				
			}
			$no++;
		}
		$GetY= $this->fpdf->GetY();
		if($GetY > 25){
				$koorY = 1;
				$this->fpdf->AddPage();
				$rr = 1.5;}
		$GetY= $this->fpdf->GetY();
		$koorY = $GetY+0.5;
		$sql = ("SELECT d.berangkat,d.kembali from perjalanan_multi p inner join dinas d on p.dinas = d.id where p.id = '".$id."'");		
		$query = $this->db->query($sql);		
		foreach ($query->result() as $row)
		{
		   $tanggalBerangkat 	= $row->berangkat;		   		   
		   $tanggalKembali 		= $row->kembali;
		}
		$this->fpdf->Ln();
		$this->fpdf->setXY(2.6,$koorY );
		$this->fpdf->Cell(0.6,0.5,'D.','');		
		$this->fpdf->Cell(5.3,0.5,'Maksud dan Tujuan Penugasan','');		
		$this->fpdf->Cell(0.5,0.5,':');
		$GetY= $this->fpdf->GetY();
		$rr = 1;
			$koorY = $GetY + $rr; //		
			$this->fpdf->Ln();
			$this->fpdf->Text(3.25,$koorY ,'1. Tempat Tujuan');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getKotaTujuan());
			$GetY= $this->fpdf->GetY(); //
			$rr = 1.1;
			$koorY = $GetY + $rr;
			$this->fpdf->Text(3.25,$koorY,'2. Tanggal Kegiatan');
			$this->fpdf->Text(8,$koorY,':');
			$tgl_berangkat = str_split($tanggalBerangkat,4);
			$this->fpdf->Text(8.3,$koorY,$tgl_berangkat[2].' s/d '.day($tanggalKembali));
			$GetY = $this->fpdf->GetY(); //
			$rr = 1.7;
			$koorY = $GetY + $rr;
			$this->fpdf->Text(3.65,$koorY,'');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->mr->getTotalHariDinas().' ('.$this->mr->terbilang($this->mr->getTotalHariDinas()).')'.' Hari');
			$GetYCur = $this->fpdf->GetY(); //
			$koorY = $GetY + $rr;
			$rr = 1;
			if ($GetYCur  >= 25){
				$this->fpdf->AddPage();
				$koorY = 1;
				$rr = 1.5;
				$koorY = $rr;
				//$this->fpdf->SetTopMargin(3);
			}else {
				$GetY = $this->fpdf->GetY(); //
				$rr = 2;
				$koorY = $GetY + $rr;  
			}	
			$this->fpdf->SetXY(3.15,$koorY);
			$this->fpdf->Cell(4,0.5,'3. Untuk');
			$this->fpdf->SetXY(7.9,$koorY);
			$this->fpdf->Cell(1,0.5,':');			
			$this->fpdf->SetXY(8.2,$koorY);
			$this->fpdf->MultiCell(10.5,0.5,$this->mr->getMaksud(),'');
			$GetY= $this->fpdf->GetY(); //
			
			$rr = 0.5;
			if (empty($tiket1)){
				$this->setTransportasi("Kendaraan Umum");
			}else(
				$this->setTransportasi("Pesawat Udara")
			);
		
			$koorY = $GetY + $rr;
			$this->fpdf->Text(3.25,$koorY,'4. Alat Transportasi');
			$this->fpdf->Text(8,$koorY,':');
			$this->fpdf->Text(8.3,$koorY,$this->TRANSPORTASI);
			$this->fpdf->Ln();
		$GetY= $this->fpdf->GetY(); //
		$rr = 1;
		$koorY = $GetY + $rr;
		$this->fpdf->Ln();
		$this->fpdf->setXY(2.6,$koorY);
		$this->fpdf->Cell(0.6,0.5,'E.','');		
		$this->fpdf->Cell(4.7,0.5,'Keterangan lain-lain','');		
		$this->fpdf->Cell(0.5,0.5,':');
		$GetY= $this->fpdf->GetY(); //
		$koorY = $GetY + $rr;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'1.','');
		$this->fpdf->MultiCell(15.5,0.5,'Melaporkan hasil pelaksanaan tugas selambat-lambatnya 7 (tujuh) hari kerja setelah pelaksanaan dengan melampirkan dokumen pendukung administrasi lainnya sesuai peraturan perundang-undangan yang berlaku.','');
		$rr = 0.5;
		$GetY= $this->fpdf->GetY(); //
		$koorY = $GetY + $rr;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'2.','');
		$this->fpdf->MultiCell(15.5,0.5,'Para nama pegawai yang ditugaskan agar melaksanakan tugas ini dengan penuh tanggung jawab dan berlaku sejak tanggal ditetapkan.','');		
		$GetY= $this->fpdf->GetY(); //
		$koorY = $GetY + $rr;
		$this->fpdf->setXY(3.2,$koorY);
		$this->fpdf->Cell(0.6,0.5,'3.','');
		$this->fpdf->MultiCell(15.5,0.5,'Segala biaya yang dikeluarkan berkenaan dengan pelaksanaan kegiatan ini dibebankan pada kegiatan tersebut diatas.','');
		//Footer
		$rr = 1;
		$GetYCur  = $this->fpdf->GetY();
		$koorY = $GetY + $rr;
			if ($GetYCur  > 17){
				$this->fpdf->AddPage();
				$koorY = $this->fpdf->GetY();;
				$rr = 3;
				$koorY = $rr;
				//$this->fpdf->SetTopMargin(3);
			}else {
				$GetY = $this->fpdf->GetY(); //
				$rr = 2;
				$koorY = $GetY + $rr;  
			}
		$this->fpdf->Ln();
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->SetXY(11.5,$koorY);
		$this->fpdf->MultiCell(2.5,0.5,'Ditetapkan');
		$this->fpdf->SetXY(14.5,$koorY);
		$this->fpdf->MultiCell(1,0.5,':');
		$this->fpdf->SetXY(15,$koorY);
		$this->fpdf->MultiCell(3,0.5,'    BEKASI');
		$rr = 0;
		$GetY= $this->fpdf->GetY();
		$koorY = $GetY;		
		$this->fpdf->setFont('Arial','',11);
		$this->fpdf->SetXY(11.5,$koorY);
		$this->fpdf->Cell(3,0.5,'Pada tanggal','B');
		$this->fpdf->SetXY(14.5,$koorY);
		$this->fpdf->Cell(0.2,0.5,':','B');
		$this->fpdf->SetXY(14.7,$koorY);
		$this->fpdf->Cell(3.6,0.5,'       '.month(date("m")).' '.date("Y "),'B');
		
				
		$GetY = $this->fpdf->GetY();
		$rr = 1.5; 
		$koorY = $GetY + $rr;
		$this->fpdf->setFont('Arial','B',10);
		$this->fpdf->Text(12,$koorY,'KEPALA BALAI BESAR PENGUJIAN');
		$GetY = $this->fpdf->GetY();
		$rr = 2.0; 
		$koorY = $GetY + $rr;
		$this->fpdf->setFont('Arial','B',10);
		$this->fpdf->Text(12,$koorY,'   PERANGKAT TELEKOMUNIKASI');
		$GetY= $this->fpdf->GetY();
		$rr = 4.5;
		$koorY = $GetY + $rr;
		if($namaStaff === "DWI HANDOKO"){$TTD = $this->NAMA_PLT;}
		else {$TTD = $this->NAMA_DIREKTUR;}
		
		$this->fpdf->SetXY(12,$koorY);
		$this->fpdf->Cell(6,0.5,$TTD,0,'','C');
		$GetY= $this->fpdf->GetY();
		$rr = 1;
		$koorY = $GetY + $rr;
		$this->fpdf->Ln();		
		$this->fpdf->setFont('Verdana','B',9);
		$this->fpdf->Text(2.6,$koorY,'Tembusan :');
		$this->fpdf->Text(2.6,$koorY+0.05,'_________');
		$this->fpdf->setFont('Verdana','',9);
		$GetY= $this->fpdf->GetY();
		$rr = 1;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(2.6,$koorY,'Disampaikan Yth. kepada :');
		$GetY = $this->fpdf->GetY();
		$rr = 1.5;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(2.6,$koorY,'1. Kabag Keuangan');
		$GetY = $this->fpdf->GetY();
		$rr = 2.05;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(2.6,$koorY,'2. Kasubag TU Dit. Pengendalian');
		$GetY= $this->fpdf->GetY();
		$rr = 2.5;
		$koorY = $GetY + $rr;		
		$this->fpdf->Text(3,$koorY,'SDPPI, mohon penyiapan');
		$GetY= $this->fpdf->GetY();
		$rr = 3;
		$koorY = $GetY + $rr;
		$this->fpdf->Text(3,$koorY,'SPPD/BOP bagi ybs');
		
		/*****
		//Insert Page Number
		$this->fpdf->setFont('Arial','',7);				
		$this->fpdf->Text(10.4,28.7, 'Page ' . $this->fpdf->PageNo());		
		******/
		
		$this->fpdf->Output();
		//$this->fpdf->Output('pdf/SPPT '.ucwords(mb_strtolower($namaStaff)).' '.date("dmY").'.pdf','F');			
	}	
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
/* End of file Report.php */
