<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dpwap_Premium_Admin {
	
    public function __construct() { 
        add_action('admin_menu', array($this, 'dpwap_premium_menus'));
        add_action('admin_enqueue_scripts', array($this,'enqueue'));
        $this->current_page = isset($_GET['page']) ? $_GET['page'] : '';
    }
    
    public function dpwap_premium_menus(){
        add_menu_page("Download Plugin", __('Download Plugin', 'download-plugin-premium'), "manage_options", "dpwap_plugin", array($this, 'dpwap_menu'));
        add_submenu_page("dpwap_plugin", __('User', 'download-plugin-premium'), __('User', 'download-plugin-premium'), "manage_options", "dpwap_users", array($this, 'dpwap_users'));
    }
    
    public function dpwap_menu() {
       
    }
    
    public function dpwap_users() {
        include_once( 'template/users.php' );
    }

    public function enqueue(){
        wp_enqueue_style('dpwap-premium-admin-style', DPWAP_PREMIUM_URL.'includes/admin/template/css/dpwap-premium-admin.css', false, DPWAP_PREMIUM_VERSION);
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-form');
        wp_enqueue_script('dpwap-premium-admin-script', DPWAP_PREMIUM_URL.'includes/admin/template/js/dpwap-premium-admin.js', array('jquery'), DPWAP_PREMIUM_VERSION);
    }

}
new Dpwap_Premium_Admin;