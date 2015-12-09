<?php
    $err = 0;
    $data_i = array("err"=>$err);     
    $this->load->vars($data_i);       
?>
<script>
$(document).ready(function){
	$('.ajax-open').click(function(){
		$.ajax({
				type	:"POST",
				data	:string,
				success	: function(data){
					$("#dialog").dialog({
						width:400,
						resizable: false,
						autoOpen: true					
					});
				}
		});
	});
});
</script>
<div id ="container">
	<div id="content">		
		<div>
			<h1>Content Upload</h1><br/>
			<div>
			    <p>Hasil upload anda sebagai berikut :</p>
  			</div>  			
		</div>
		<input type="hidden" id="filename" name="filename" value="<?php echo $file;?>" />
		<ul class="tabs">
		  <li><a href="#tab1"><?php echo strtoupper("SBU Pesawat");?></a></li>
		  <li><a href="#tab2" onclick="return get_sbu_penginapan('import_data')";><?php echo strtoupper("SBU Penginapan");?></a></li>
		  <li><a href="#tab3" onclick="return get_sbu_uang_saku('import_data')";><?php echo strtoupper("SBU Uang Saku");?></a></li>
		  <li><a href="#tab4" onclick="return get_sbu_taxi('import_data')";><?php echo strtoupper("SBU Taxi");?></a></li>
		</ul>
		<div class="tab_container">
			<div id="tab1" class="tab_content">
				<?php echo Modules::run('import/Import_data/view_xls_sbu_pesawat'); ?>
			</div>
			<div id="tab2" class="tab_content">	      		
	      		<?php echo Modules::run('import/Import_data/view_xls_sbu_penginapan'); ?>     		
	    	</div>
	    	 <div id="tab3" class="tab_content">
              	<?php echo Modules::run('import/Import_data/view_xls_sbu_uang_saku'); ?>
            </div>            
            <div id="tab4" class="tab_content">
               	<?php echo Modules::run('import/Import_data/view_xls_sbu_taxi'); ?> 
            </div>
		</div>
	</div>
</div>