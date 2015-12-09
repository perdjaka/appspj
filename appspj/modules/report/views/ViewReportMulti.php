<script>
    function goBack() {
        window.history.back()
    }
</script>
<div id="header-container">
    </br>
    </br>
    </br>
    <ul id="sidebar">
        
    </ul>
    <div id="container">
        <div id="content">
            <p align="left">
                <p>
                    Perjalanan Dinas untuk Kegiatan
                    <?php echo $kegiatan;?>
                        yang dilaksanakan di
                        <?php echo $kota;?> selama
                            <?php echo $hari;?> Hari.
                                <p>Berjumlah
                                    <?php echo $jumlah;?> orang </p>
                </p>
            </p>
        </div>
    </div>
</div>
<?php foreach ($css_files as $file):?>
    <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php endforeach; ?>
        <?php foreach ($js_files as $file): ?>
            <script src="<?php echo $file; ?>"></script>
            <?php endforeach; ?>
                <div id="container">
                    <div id="content-area">
                        <?php echo $output; ?>
                            <?php /*	$user_data = $this->session->userdata('username');
				echo $user_data*/;
		?>
                    </div>
                    </br>
                    <div>
                        <div style="position: relative; float:left">
                            <input type="button" value="Back" onclick="goBack()">
                        </div>
                        <!--<a href="<?php echo base_url();?>report/cetakSpjMulti/<?php echo $id;?>"><input type="button" value="Cetak SPPT"></a>-->
                        <div style="position: relative; left: 86%; top: 0px; width: 100px; height: 26px; z-index: 99; float:left">
                            <a class="edit_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button" href="<?php echo base_url();?>report/cetakSpjMulti/<?php echo $id;?>">

                                <span class="ui-button-icon-primary ui-icon S3b707683"></span>
                                <span class="ui-button-text">&nbsp;Cetak SPPT</span>

                            </a>
                        </div>
                    </div>

                </div>
                </br>
                </br>
                </br>
                </br>