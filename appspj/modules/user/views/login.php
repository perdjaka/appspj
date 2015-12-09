<div id="container-login">
        <div id="information-area">
            <div id="title-info">Aplikasi SPJ</div>
            <p>Direktorat Jenderal SDPPI ini berfokus pada pengaturan, pengelolaan dan pengendalian sumberdaya dan perangkat pos dan informatika yang terkait dengan penggunaan oleh internal (pemerintahan) maupun publik luas/masyarakat.</p>
        </div>
        <div id="login-area">
            <div class="title">Login</div>
			<?php echo validation_errors(); ?>            
            <form action="user/login" method="post" accept-charset="utf-8">        
                <label>
                    <strong class="label">User Pengguna </strong>
                    <input type="text" name="username" value="" id="username" spellcheck="false" maxlength="32"  />     
                </label>
                <label>
                    <strong class="label">Kode Akses </strong>
                    <input type="password" name="password" value="" id="password"  />       
                </label>
                <input type="submit" name="signin" value=" Sign In " id="signin"  />
            </form>
            <?php 
            	//if($message!= ''){
                  //      echo '<span class="error-notification">'.$message.'</span>';
                  //}
             ?>
        </div>
        <div id="content-area"></div>
        <div class="holder"></div>
</div>