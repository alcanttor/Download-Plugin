<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://metagauss.com/
 * @since             3.0.0
 * @package           download_plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Download Plugin Premium
 * Plugin URI:        https://metagauss.com/
 * Description:       Unlocks full potential of Download Plugin! Adds all Premium features. Requires Download Plugin Standard to work properly.
 * Version:           1.0.0
 * Tags:              download plugin, upload plugin, multiple plugin upload
 * Requires at least: 1.5.6
 * Requires PHP:        5.6
 * Author:            Metagauss
 * Author URI:        https://metagauss.com/
 * Text Domain:       download-plugin-premium
 * Domain Path:       /languages
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Download_Plugin_Premium')) {

    final class Download_Plugin_Premium {
        /**
         * Plugin version.
         *
         * @var string
         */
        public $version = '1.0.0';

        protected static $_instance = null;

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Define Constructor.
         */
        private function __construct() {
            $this->define_constants();
            $this->load_textdomain();
            $this->includes(); 
            $this->define_hooks();
        }
        
        public function define_constants(){
            $this->define('DPWAP_PREMIUM_URL', plugin_dir_url(__FILE__));
            $this->define('DPWAP_PREMIUM_VERSION', $this->version);
        }

        public function includes(){
            include_once('includes/dpwap-core-functions.php');
            if(is_admin()){
                include('includes/admin/class-admin.php');
                include_once('includes/services/class-dpwap-download.php');
                include_once('includes/services/class-dpwap-upload.php');
                include_once('includes/helper/dpwap-helper.php');
            }
        }
        
        public function define_hooks(){
            // download users
            add_action( 'admin_post_download_users', array($this, 'dpwap_download_users' ));
            // upload users
            add_action( 'admin_post_upload_users', array($this, 'dpwap_upload_users' ));
        }
        
        public function load_textdomain(){
            load_plugin_textdomain('download-plugin-premium', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        public function define($name, $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        public function dpwap_download_users(){
            $service = Download_Plugin_Premium::get_service('Dpwap_Premium_Download_Service');
            $returnData = $service->export_all_users();
        }

        public function dpwap_upload_users(){
            $service = Download_Plugin_Premium::get_service('Dpwap_Premium_Upload_Service');
            $returnData = $service->import_all_users();
            if(isset($returnData['success'])){
                wp_send_json_success(array('data' => $returnData['data']));
            }
            if(isset($returnData['errors'])){
                wp_send_json_error(array('data' => $returnData['errors']));
            }
        }

        public static function get_service($type){
            if(class_exists($type)){
                return $type::get_instance();
            }
            else{
                throw new Exception("Class not found.");
            }
        }
    }
}
/**
 * Check download plugin installed
 */
add_action('plugins_loaded', function(){
    if(!get_option('dpwap_popup_status')){ 
        add_action('admin_notices','dpwap_basic_checks');
    }
    else{
        Download_Plugin_Premium::instance();
    }
});

function dpwap_basic_checks(){ ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Download Plugin Premium won\'t work as Download Plugin is not active/installed.', 'download-plugin-premium' ); ?></p>
    </div>
<?php }