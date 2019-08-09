<?php  
$dpwapObj = new dpwapuploader();
echo '<style>.notice { display: none; }</style>';
echo '<style>#dolly { display: none; }</style>';
if(isset($_POST['dpid']) && $_POST['dpid']>0){ 
	$dpwapObj->dpwap_plugin_all_activate();
}
if($_GET['page']=="mul_upload") { 
	$max_size_upload = (int)(ini_get('upload_max_filesize'));
?>
<div class="wrap pc-wrap">
	<div class="mpiicon icon32"></div>
	<div id="mpiblock">
		<div><?php if($dpwapObj->dpwap_app_DirTesting()){} else{ _e('<div class="mpi_error">oops!!! Seems like the directory permission are not set right so some functionalities of plugin will not work.<br/>Please set the directory permission for the folder "uploads" inside "wp-content" directory to 777.</div>','dpwap'); } ?></div>
		
		<div id="dpwap-plugin-box" class="dpwap-meta-box">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br/></div>
				<div id="dpwap-plugin-zipbox">
				<h3 class="hndle"><span><?php _e('You can select and upload multiple Plugins in .zip format','dpwap'); ?></span></h3>
									<br/>
					<form class="dpwap_multiple_upload_form" onsubmit="return check_valid_zipfile('dpwap_locFiles',<?php echo $max_size_upload; ?>);" name="form_uppcs" method="post" action="" enctype="multipart/form-data">
						<?php wp_nonce_field($dpwapObj->key); ?>
						<div class="upload-section-btn">					
							<!-- <input type="file" class="mpi_left" name="dpwap_locFiles[]" id="dpwap_locFiles" multiple="multiple" size="40" /> -->
							<!--link href="https://cdnjs.cloudflare.com/ajax/libs/ratchet/2.0.2/css/ratchet.css" rel="stylesheet"/-->
<label for="dpwap_locFiles" class="btn btn-primary btn-block btn-outlined" style="background: #d3d3d3; padding: 5px 12px;">Choose File</label>
							
							
							<input type="file" class="mpi_left middle sm_select_file" name="dpwap_locFiles[]" id="dpwap_locFiles" multiple="multiple" style="display: none;"/>
							<input id="install_button" class="button mpi_button sm_btn_hide" type="submit" name="dpwap_locInstall" value="<?php _e('Install Now','dpwap'); ?>"  />
							<div class="dpwap_clear"></div>
							
							<div class="message"></div>
						</div>
					</form>
				</div>
				<div class="inside">
					<?php
						if (isset($_POST['dpwap_locInstall']) && $_FILES['dpwap_locFiles']['name'][0] != ""){
							echo '<form id="form_alldpwap" name="form_alldpwap" method="post" action="admin.php?page=activate-status">';
							echo "<div class='dpwap_main'>";
							$dpwapObj->dpwap_plugin_locInstall();
							echo "</div>";
							echo '<input class="button button-primary dpwap_allactive" type="submit" name="dpwap_locInstall" onclick="activateAllPLugins()" value="Activate all">';
							echo '</form>';
						}
					?>
				</div>
			</div>		
		</div>
	</div>
</div>

<div class="containerul">
<h1 style="colour : #fff;">Uploading is in progress...</h1>
 <ul class="loader">
      <li></li><li></li><li></li><li></li><li></li><li></li>
 </ul>
</div>

<script type="text/javascript">
		jQuery(document).ready(function(){
	  jQuery('.sm_btn_hide').attr("disabled", "disabled");

        jQuery('input[type="file"]').change(function(e){
            var fileName = e.target.files[0].name;
            		jQuery('.sm_btn_hide').removeAttr("disabled", "disabled");

        });
		
		
		jQuery('input#dpwap_locFiles').change(function(){
			var files = jQuery(this)[0].files;
			jQuery('.message').html(files.length + ' files selected...');
		});
		
		
    });
	

</script>
<?php }  ?>
       

<style>
.containerul {
  position: fixed;
  z-index: 4;
  margin: 0 auto;
  left: 0;
  right: 0;
  top: 0;
  margin-top: -45px;
  width: 100%;
  height: 100%;
  list-style: none;
  border: 2px solid rgba(255,255,255,0.4);
  background: rgba(0,0,0,0.8);
  z-index: 999;
  overflow: hidden;
  display: none;
}
.containerul h1{
	position: fixed;
    top: 35%;
    width: 102%;
    z-index: 9999999999999;
    color: white;
    text-align: center;
}
.loader{
    position: fixed;
    z-index: 3;
    margin: 0 auto;
    left: 0;
    right: 0;
    top: 50%;
    margin-top: -30px;
    width: 130px;
    height: 60px;
 list-style: none;}@-webkit-keyframes 'loadbars' {
    0%{
        height: 10px;
        margin-top: 25px;
    }
    50%{
        height:50px;
        margin-top: 0px;
    }
    100%{
        height: 10px;
        margin-top: 25px;
    }
}.loader li{
        background-color: #ffffff
;
        width: 10px;
        height: 10px;
        float: right;
        margin-right: 5px;
   box-shadow: 0px 20px 10px rgba(0,0,0,0.2);
    }
.loader li:first-child{
            -webkit-animation: loadbars 1s cubic-bezier(0.645,0.045,0.355,1) infinite 0s;
        }
.loader li:nth-child(2){
            -webkit-animation: loadbars 1s ease-in-out infinite -0.2s;
        }
.loader li:nth-child(3){
            -webkit-animation: loadbars 1s ease-in-out infinite -0.4s;
 }
.loader li:nth-child(4){
            -webkit-animation: loadbars 1s ease-in-out infinite -0.6s;
   }
.loader li:nth-child(5){
            -webkit-animation: loadbars 1s ease-in-out infinite -0.8s;
   }
.loader li:nth-child(6){
            -webkit-animation: loadbars 1s ease-in-out infinite -1.0s;
   }
}
</style>



