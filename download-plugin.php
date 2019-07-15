<?php
/*
Plugin Name: Download Plugin
Plugin URI: http://metagauss.com
Description: Download any plugin from your wordpress admin panel's plugins page by just one click!
Version: 1.1.0
Author: metagauss
Author URI: https://profiles.wordpress.org/metagauss/
Text Domain: download-plugin
*/

/**
 * Basic plugin definitions 
 * 
 * @package Download Plugin
 * @since 1.0.0
 */

require_once 'dpwap-Class.php';

$dpwap_uploadDir = wp_upload_dir();
if( !defined( 'DPWAPUPLOADDIR_PATH' ) ) {
define('DPWAPUPLOADDIR_PATH', $dpwap_uploadDir['basedir']);
}
if( !defined( 'DPWAP_DIR' ) ) {
  define( 'DPWAP_DIR', dirname( __FILE__ ) );			// Plugin dir
}
if( !defined( 'DPWAP_URL' ) ) {
  define( 'DPWAP_URL', plugin_dir_url( __FILE__ ) );	// Plugin url
}
if(!defined('DPWAP_PREFIX')) {
  define('DPWAP_PREFIX', 'dpwap_'); // Plugin Prefix
}
/**
 * Load text domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Download Plugin
 * @since 1.0.0
 */
load_plugin_textdomain( 'download-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Enqueue styles/scripts on admin side
 * 
 * @package Download Plugin
 * @since 1.0.0
 */
function dpwap_admin_scripts( $hook ){
	wp_enqueue_script( 'up_admin_script', DPWAP_URL.'js/bootstrap.min.js', array('jquery'));
	wp_enqueue_script( 'up_admin_func', DPWAP_URL.'js/multiple.js');
	wp_register_style( 'dpwap-admin-style', DPWAP_URL.'/css/dpwap-admin.css' );
	wp_enqueue_style( 'dpwap-admin-style' );
}
add_action( 'admin_enqueue_scripts', 'dpwap_admin_scripts' );

// Add new page for download dashboard
function dpwap_register_menupage(){
    add_menu_page('multiple upload', 'multiple upload', 'administrator','mul_upload', 'dpwap_multiple_upload_func');
    add_menu_page('dpwap activate', 'dpwap activate', 'administrator','dpwap-activate', 'dpwap_package_activate_func');
    remove_menu_page('mul_upload');
    remove_menu_page('dpwap-activate');
  }
function dpwap_multiple_upload_func()
  {
   require_once 'multiple_upload_plugin.php'; 
  }

  function dpwap_package_activate_func(){
  require_once 'feature-package.php'; 	
  }
add_action( 'admin_menu', 'dpwap_register_menupage' );

// Add download link to plugins bulk action dropdown
function add_download_bulk_actions( $bulk_array ) { 
	$lastKey=end(array_keys($bulk_array));
    if($lastKey=='delete-selected'){ 
    unset($bulk_array['delete-selected']);
  	$bulk_array['all_download'] = 'Download';
    $bulk_array['delete-selected'] = 'Delete';
    }else{
    $bulk_array['all_download'] = 'Download';	
    }
  	return $bulk_array;
}
add_filter( 'bulk_actions-plugins', 'add_download_bulk_actions');

//plugins bulk action dropdown handler function 
function dpwap_download_bulk_action_handler( $redirect, $doaction, $object_ids ) {  
	if ( $doaction == 'all_download' ) {
		if( !function_exists( 'get_plugins' ) ) {
	     require_once ABSPATH . 'wp-admin/includes/plugin.php';
         }
         $plugins_arr=array();
		foreach( $object_ids as $key=>$value ){ 
				 if( strpos( $value, '/' ) !== false ) {
		    	 $explode = explode( '/', $value );
				 $path    = $explode[0];
				 $folder  = 1;
				}else {
				$path   = $value;
				$folder = 2;
			   }
          $downurl="?dpwap_download=".$path."&f=".$folder;
          $second_array = array($path=>$downurl);
          $plugins_arr2[] = array_merge((array)$plugins_arr, (array)$second_array); 
         }
         $plugins_arry=maybe_serialize($plugins_arr2);
         update_option( 'dpwap_download_plugins', $plugins_arry );
        global $pagenow;
	    $redirect=admin_url('plugins.php?action=mul_download&upload=').count($object_ids);
	}
	return $redirect;
}
add_filter( 'handle_bulk_actions-plugins', 'dpwap_download_bulk_action_handler', 10, 3 );

//admin multiple download function
function dpwap_admin_multiple_download_func() {
    global $pagenow;
    if ( $pagenow == 'plugins.php' && $_GET['action']=='mul_download' && $_GET['upload']>0) {
    	  $dpwap_plugins=maybe_unserialize(get_option('dpwap_download_plugins'));
         foreach ($dpwap_plugins as $arrValue) {
	 	     foreach ($arrValue as $dpwapValue) {  ?>
	 	       <script language="javascript" type="text/javascript">
			    var iframe = document.createElement('iframe');
			     iframe.src = "<?php echo $dpwapValue; ?>";
			     iframe.style.display = 'none';
			    document.body.appendChild(iframe);
	         </script>
	       <?php } 
	  }
  }
}
add_action( 'admin_footer', 'dpwap_admin_multiple_download_func' );

//admin multiple download function
function dpwap_setting_popup_func() {
	
    global $pagenow;
    if ( $pagenow == 'plugins.php') {
      //if(!get_option('dpwap_popup_status')){	
      require_once 'dpwap_setting.php'; 
      //add_option('dpwap_popup_status',1);	 
     // }
      
  }
}
add_action( 'admin_footer', 'dpwap_setting_popup_func' );

//admin multiple upload form
function dpwap_multiple_upload_admin_func(){
    global $pagenow;
    if ( $pagenow == 'plugin-install.php' ) {
     	$redirect=admin_url('admin.php?page=mul_upload');
    	 echo '<div class="wrap">
              <a id="mul_upload" class="page-title-action show" href="'.$redirect.'"><span class="upload">Upload Multiple Plugins</span></a>
          </div>';
         }
     }
 add_action('admin_notices', 'dpwap_multiple_upload_admin_func');


//all plugins activate get ajax response code
function dpwap_plugin_activate_func() {
   $waplugin = $_POST['dpwap_url'];
   $waplugins = get_option('active_plugins');
    if($waplugins){
		//$waplugin = $dpwap_data;
		if (!in_array($waplugin, $waplugins)) {
			 array_push($waplugins,$waplugin);
			 update_option('active_plugins',$waplugins);
			}
		}
}
add_action( 'wp_ajax_dpwap_plugin_activate', 'dpwap_plugin_activate_func');

 
 //user based feature select function
function dpwap_feature_select_func() { 
    $waplugin = $_POST['dpwap_feature']; 
        foreach ($waplugin as $value) {
     	if($value == 1){ 
     	$feature1=1;
     	}elseif (($value >= 2) && ($value <= 8)) {
   	    $feature2 =1;
   	    }elseif(($value >= 9) && ($value <= 15)){
        $feature3 =1;
   	    }else{  $feature4 =1;   }
     }
      require_once 'dpwap-add-feature.php';
       wp_die(); 
}
add_action( 'wp_ajax_dpwap_feature_select', 'dpwap_feature_select_func'); 

/**
 * Add download link to plugins page
 * 
 * @package Download Plugin
 * @since 1.0.0
 */
if( !function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$dpwap_all_plugins = get_plugins();

foreach( $dpwap_all_plugins as $key=>$value ){
	add_filter( 'plugin_action_links_' . $key, 'dpwap_download_link', 20, 2 );
}

function dpwap_download_link( $links, $plugin_file ){
	
	if( strpos( $plugin_file, '/' ) !== false ) {
		$explode = explode( '/', $plugin_file );
		$path    = $explode[0];
		$folder  = 1;
		
	} else {
		
		$path   = $plugin_file;
		$folder = 2;
	}
	
	$download_link = array(
						'<a href="?dpwap_download='.$path.'&f='.$folder.'" class="dpwap_download_link">'.__( 'Download', 'download-plugin' ).'</a>',
	);
	
	return array_merge( $links, $download_link );
}

/**
 * Delete temporary folder created for single file plugin
 * 
 * @package Download Plugin
 * @since 1.0.0
 */
function dpwap_delete_temp_folder( $path ){
    
	if( is_dir( $path ) === true ) {
        
		$files = array_diff( scandir( $path ), array( '.', '..' ) );

        foreach( $files as $file ){
            
        	dpwap_delete_temp_folder( realpath( $path ) . '/' . $file );
        }

        return rmdir( $path );
        
    } else if( is_file( $path ) === true ) {
        return unlink($path);
    }

    return false;
}

/**
 * Download plugin zip
 * 
 * @package Download Plugin
 * @since 1.0.0
 */
function dpwap_download(){
	
	if( is_user_logged_in() && current_user_can( 'activate_plugins' ) && isset( $_GET['dpwap_download'] ) && !empty( $_GET['dpwap_download'] ) && preg_match( "/^[A-Za-z0-9-]+$/", $_GET['dpwap_download'] ) && isset( $_GET['f'] ) && !empty( $_GET['f'] ) ){
		global $dpwap_all_plugins;
		$all_plugins    = array_keys( $dpwap_all_plugins );
		$plugins_arr    = array();
		$dpwap_download = $_GET['dpwap_download'];
		
		foreach( $all_plugins as $key=>$value ){
			
			$explode = explode( '/', $value );
			
			array_push( $plugins_arr, $explode[0] );
		}
		
		if( in_array( $dpwap_download, $plugins_arr ) ){
		
			$folder		    = $_GET['f'];
			
			if( $folder == 2 ) {
				
				$dpwap_download = basename( $dpwap_download, '.php' );
				
				$folder_path  = WP_PLUGIN_DIR.'/'.$dpwap_download;
				
				if( !file_exists( $folder_path ) ) {
					mkdir( $folder_path, 0777, true );
				}
				
				$source       = $folder_path.'.php';
				$destination  = $folder_path.'/'.$dpwap_download.'.php';
				
				copy( $source, $destination );
				
			} else {
				$folder_path  = WP_PLUGIN_DIR.'/'.$dpwap_download;
				
			}
			
			$root_path    = realpath( $folder_path );
			
			$zip = new ZipArchive();
			$zip->open( $folder_path.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
			
			$files = new RecursiveIteratorIterator(
			    new RecursiveDirectoryIterator( $root_path ),
			    RecursiveIteratorIterator::LEAVES_ONLY
			);
			
			foreach( $files as $name=>$file ){
			    
				if ( !$file->isDir() ){
			        
					$file_path	   = $file->getRealPath();
			        $relative_path = substr( $file_path, strlen( $root_path ) + 1 );
			        
			        $zip->addFile( $file_path, $relative_path );
			    }
			}
			
			$zip->close();
			
			if( $folder == 2 ){
				dpwap_delete_temp_folder( $folder_path );
			}
			// Download Zip
			$zip_file = $folder_path.'.zip';
			
			if( file_exists( $zip_file ) ) {
				
			    header('Content-Description: File Transfer');
			    header('Content-Type: application/octet-stream');
			    header('Content-Disposition: attachment; filename="'.basename($zip_file).'"');
			    header('Expires: 0');
			    header('Cache-Control: must-revalidate');
			    header('Pragma: public');
			    header('Content-Length: ' . filesize($zip_file));
			    header('Set-Cookie:fileLoading=true');
			    readfile($zip_file);
			    unlink($zip_file);
			    exit;
			}
		}
	}	
}
add_action( 'admin_init', 'dpwap_download' );

// plugin deactivation function
function dpwap_plugin_deactivation_function(){
	delete_option('dpwap_popup_status');
}
register_deactivation_hook( __FILE__, 'dpwap_plugin_deactivation_function' );