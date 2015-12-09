<div id="header-container">
	</br>
	<ul id="">
			<!--<li><a href="<?php	echo base_url();?>import/sbu_penginapan">Import Sbu Penginapan</a></li>
			<li><a href="<?php	echo base_url();?>import/sbu_pesawat">Import SBU Pesawat</a></li>
			<li><a href="<?php	echo base_url();?>import/sbu_taxi">Import SBU Taxi</a></li>
			<li><a href="<?php	echo base_url();?>import/sbu_uang_saku">Import SBU Uang Saku</a></li>-->					
	</ul>
</div>
<div id="container">
	<h1><?php echo $judul; ?></h1>
	</br>
	<div id="content">
		<p align ="center">
			Silahkan Import File Excell
		</p>
	<form id ="import" name="import" action="<?php echo base_url();?>import/upload" method="post" enctype="multipart/form-data">		
	</br>
		<div class="center-box">
			<p>
	            <!--<span class="label"><span class="required">(*)</span> Table Name</span>
	            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	      		<select id="tabel_name" name="tabel_name" style="width: 235px">
	            	<option value="" align="center" > ====== Pilih Nama Tabel ======</option>
	            	<option value="sbu_taxi">Tabel Sbu Taxi</option>
	            	<option value="sbu_penginapan">Tabel Sbu Penginapan</option>
	            	<option value="sbu_pesawat">Tabel Sbu Pesawat</option>
	            	<option value="sbu_uang_saku">Tabel Sbu Uang Saku</option>
	            </select>
	            !-->
	        </p>	        
			<p>
	            <label>
	            	<span class="label" ><span class="required">(*)</span> Upload File (.xls | .xlsx | .ods)</span>&nbsp;
	                <input name="userfile" id="userfile" type="file" size="30" maxlength="50" />
	            </label>
	        </p>
	        </br>
	        <p>
	            <span class="label"></span> 
	                <input type="submit" class="button" id="save_upload_xls" name="save_upload_xls" value="Upload" /> 
	                <input type="reset" class="button" id="clear" name="clear" value="Clear" />
	        </p>       
	    </div>
	</form>
	</div>
	</div>	
	
		<?php	foreach ($css_files as $file):	?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
		<?php
			  	endforeach; 			
				foreach ($js_files as $file): 
		?>						
	<script src="<?php echo $file; ?>"></script>			
		<?php 	endforeach; ?>	
	</br>
	
<div id="container">	
	<div id="content-area">		
		<?php echo $output; ?>
	</div>
</br></br></br>
				
</div>