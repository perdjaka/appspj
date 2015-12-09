<div id="header-container">
	</br>
	<?php	//print_r($userdata);	?>
	<ul id="sidebar">
			<li><a href="<?php	echo base_url();?>master/pangkat">Pangkat</a></li>
			<li><a href="<?php	echo base_url();?>master/staff">Staff</a></li>			
			<li><a href="<?php	echo base_url();?>master/provinsi">Provinsi</a></li>
			<li><a href="<?php	echo base_url();?>master/kota">Kota</a></li>
			<li><a href="<?php	echo base_url();?>master/sbuPenginapan">Sbu Penginapan</a></li>
			<li><a href="<?php	echo base_url();?>master/sbuPesawat">SBU Pesawat</a></li>
			<li><a href="<?php	echo base_url();?>master/sbuTaxi">SBU Taxi</a></li>
			<li><a href="<?php	echo base_url();?>master/sbuUangSaku">SBU Uang Saku</a></li>			
			<li><a href="<?php	echo base_url();?>master/dinas">Dinas</a></li>
			<!--<li><a href="<?php	//echo base_url();?>master/kegiatan">Kegiatan</a></li>-->						
			<li><a href="<?php	echo base_url();?>master/perjalanan">Perjalanan</a></li>
		</ul>
	</div>	
	</br></br></br></br>
		<?php foreach ($css_files as $file):?>
			<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
		<?php endforeach; ?>			
		<?php foreach ($js_files as $file): ?>			
			<script src="<?php echo $file; ?>"></script>			
		<?php endforeach; ?>	
	</br>
<div id="container">
	<div id="content-area">	
		<?php echo $output; ?>
		<?php /*	$user_data = $this->session->userdata('username');
				echo $user_data;*/
		?>
	</div>
</div>