<?php	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Description of Class model m_master.php
 *
 * @version 1.0
 * @author Guntar 18/07/2013
 */

class ModelMaster extends CI_Model{
		
	function __construct()
	{
		//parent::__construct;
		
	}	
	
	public function record_count($table_name) {
        return $this->db->count_all($table_name);
    }
	
	public function fetch_table($table_name,$limit, $start) {
        $this->db->limit($limit, $start);
        $query = $this->db->get($table_name);
 
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
   }
	
   public function getPerjalanan()
   {
  		$sql = ('Select * from perjalanan');
  		$query = $this->db->query($sql);
		
		foreach ($query->result() as $row)
		{		   		   		   
		   $status = $row->status;
		}
		return ($status);
   }
   
   public function getKota($id)
   {
  		$sql = ("SELECT hsl.kota as kota from (SELECT CONCAT(c.kota,'-',b.kota) as kota FROM perjalanan_multi as d LEFT JOIN sbu_pesawat as a ON 
  				 (d.tiket1 = a.id) LEFT JOIN kota as b ON(a.tujuan = b.id) LEFT JOIN kota as c ON(a.asal=c.id)
  				 where d.tiket1 = '".$id."' ) hsl , perjalanan_multi as pm 
  				 where pm.tiket1 = '".$id."'
  				 group by pm.id,hsl.kota");
				 
		/**$sql = ("SELECT hsl.kota as kota from (SELECT CONCAT(c.kota,'-',b.kota) as kota FROM perjalanan_multi as d LEFT JOIN sbu_pesawat as a ON 
  				 (d.tiket1 = a.id) LEFT JOIN kota as b ON(a.tujuan = b.id) LEFT JOIN kota as c ON(a.asal=c.id)
  				 where d.id = '".$id."' and d.tiket1 = (select pm.tiket1 from perjalanan_multi pm where pm.id ='".$id."')) hsl , perjalanan_multi as pm 
  				 where pm.id = '".$id."'and pm.tiket1 = (select pm.tiket1 from perjalanan_multi pm where pm.id ='".$id."')
  				 group by pm.id,hsl.kota");
		**/	 
  		$query = $this->db->query($sql);		
		//print_r($query->result() );
				
		foreach ($query->result() as $row)
		{		   		   		   
		   $kota = $row->kota;
		}
		return $kota;
		
   }
   public function getKotaList()
   {
  		$sql = ("SELECT hsl.kota as kota from (SELECT CONCAT(c.kota,'-',b.kota) as kota FROM sbu_pesawat as a  
  				 LEFT JOIN kota as b ON(a.tujuan = b.id) LEFT JOIN kota as c ON(a.asal=c.id)
  				 ) hsl ");
				 			 
  		$query = $this->db->query($sql);		
		//print_r($query->result() );
				
		foreach ($query->result() as $row)
		{		   		   		   
		   $kota = $row->kota;
		}
		return $query->result();
		
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
	function total_time_reverse($time) {
		$time_name = array(	365*24*60*60=> "Tahun",
							30*24*60*60	=> "Bulan",
							7*24*60*60	=> "Minggu",
							24*60*60	=> "Hari",
							60*60		=> "Jam",
							60			=> "Menit",
							1			=> "Detik");

		$do_calculation = strtotime(gmdate ("Y-m-d H:i:s", time () + 60 * 60 * 8)) - ($time);
		
		$result = array();
		
		if($do_calculation < 5)
		{
			$result = 'Kurang dari 5 detik yang lalu';
		}
		else
		{
			$end = 0;
			foreach ($time_name as $period => $satuan)
			{
				if($end >= 6 || ($end > 0 && $period < 60)) break;
				
				$divisor = floor($do_calculation / $period);
				
				if($divisor > 0)
				{
					$result[] = $divisor.' '.$satuan;
					$do_calculation -= $divisor * $period;
					$end++;
				}
				else if($end > 0) $end++;
			}
			$result = implode(' ',$result).' yang lalu';
		}
		return $result;
	}
	
}
/* End of file ModelMaster.php */
/* Location: ./appspj/modules/master/models/m_master.php */