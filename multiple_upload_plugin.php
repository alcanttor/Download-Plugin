<?php 
$dpwapObj = new dpwapuploader();
if(isset($_POST['dpid']) && $_POST['dpid']>0){ 
	$dpwapObj->dpwap_plugin_all_activate();
}
if($_GET['page']=="mul_upload") { ?>
<div class="wrap pc-wrap">
	<div class="mpiicon icon32"></div>
	<div id="mpiblock">
		<div><?php if($dpwapObj->dpwap_app_DirTesting()){} else{ _e('<div class="mpi_error">oops!!! Seems like the directory permission are not set right so some functionalities of plugin will not work.<br/>Please set the directory permission for the folder "uploads" inside "wp-content" directory to 777.</div>','dpwap'); } ?></div>
		
		<div id="dpwap-plugin-box" class="dpwap-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<h3 class="hndle"><span><?php _e('You can select and upload multiple Plugins in .ZIP format','dpwap'); ?></span></h3>
				<div class="inside">
					<br/>
					<form onsubmit="return check_valid_zipfile('dpwap_locFiles');" name="form_uppcs" method="post" action="" enctype="multipart/form-data">
						<?php wp_nonce_field($dpwapObj->key); ?>
						<div>					
							<input type="file" class="mpi_left" name="dpwap_locFiles[]" id="dpwap_locFiles" multiple="multiple" size="40" />
							<input class="button button-primary mpi_button" type="submit" name="dpwap_locInstall" value="<?php _e('Install & Activate plugins &raquo;','dpwap'); ?>"  />
							<div class="dpwap_clear"></div>
						</div>
					</form>
					<?php
						if (isset($_POST['dpwap_locInstall']) && $_FILES['dpwap_locFiles']['name'][0] != ""){
							echo '<form id="form_alldpwap" name="form_alldpwap" method="post" action="">';
							echo "<div class='dpwap_main'>";
							$dpwapObj->dpwap_plugin_locInstall();
							echo "</div>";
							echo '</form>';
						}
					?>
					
				</div>
			</div>		
		</div>
	</div>
</div>
<?php } ?>
   
	      


