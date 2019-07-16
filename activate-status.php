<?php 
$dpwapObj = new dpwapuploader();
if($_GET['page']=="activate-status") { 
if(isset($_POST['dpid']) && $_POST['dpid']>0){  ?>
<div class="wrap pc-wrap">
	 <div id="mpiblock">
		<div class="postbox">
				<h3 class="hndle"><span><?php _e('Activation Status','dpwap'); ?></span></h3>
				  <div class="inside">
					<?php $dpwapObj->dpwap_plugin_all_activate(); 
                     delete_option("dpwap_plugins");
					?>
				</div>
				<span><a href="plugin-install.php"><?php _e('Return to Plugin Installer ','dpwap'); ?></span>
			</div>		
		</div>
	</div>
</div>
<?php } } ?>
   
	      


