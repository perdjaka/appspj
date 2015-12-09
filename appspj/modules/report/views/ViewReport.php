<div id="header-container">
	</br>
	
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
		<?php	/*$user_data = $this->session->userdata('username');
				echo $user_data;*/
		?>
	</div>
</div>