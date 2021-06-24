<?php
if (!defined( 'ABSPATH')){
    exit;
}

class Dpwap_Premium_Download_Service {
    
    private $dao;
    private static $instance = null;
    private $path_csv;
    private $user_data;
    
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->path_csv = $upload_dir['basedir'] . "/dpwap-export-users.csv";
        $this->user_data = array("user_login", "user_email", "user_nicename", "user_url", "user_registered", "display_name");
    }
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function export_all_users(){
        $dpwap_security = sanitize_text_field(dpwap_m_get_param('dpwap_security'));
        if(empty($dpwap_security) || !wp_verify_nonce($dpwap_security, 'dpwap_user_download')){
            wp_die(__('Nonce check failed', 'download-plugin-premium'));
        }
        $format = sanitize_text_field(dpwap_m_get_param('download_user_format'));
        if($format == 'csv'){
            $fsize = filesize( $this->path_csv ) + 3;
            $path_parts = pathinfo( $this->path_csv );
            header( "Content-type: text/csv;charset=utf-8" );
            header( "Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"" );
            header( "Content-length: $fsize" );
            header( "Cache-control: privfilefleate" );
            header( "Content-Description: File Transfer" );
            header( "Content-Transfer-Encoding: binary" );
            header( "Expires: 0" );
            header( "Cache-Control: must-revalidate" );
            header( "Pragma: public" );

            $row = array();
            // header
            foreach ( $this->get_user_data() as $key ) {
                $row[] = $key;
            }
            foreach ( $this->get_user_meta_keys() as $key ) {
                $row[] = $key;
            }
            $output = fopen('php://output', 'w');
            fputcsv($output, $row);
            $data[] = $row;
            $users = $this->get_user_id_list();
            foreach ($users as $user){
                $row = array();
                $userdata = get_userdata($user);
                $user_data = (array) $userdata->data;
                foreach ($this->get_user_data() as $key){
                    $row[$key] = self::prepare($key, $user_data[$key], $user);
                }
                foreach ($this->get_user_meta_keys() as $key){
                    $row[$key] = self::prepare($key, get_user_meta($user, $key, true), $user);
                }
                $data[] = array_values($row);
            }
            foreach($data as $line){
                fputcsv( $output, $line );
            }
            exit();
        }
        else if($format == 'json'){
            $output = array();
            $users = $this->get_user_id_list();
            $meta_val = array();
            foreach($users as $user){
                $userdata = get_userdata($user);
                foreach ($this->get_user_data() as $key){
                    $meta_val[$key] = self::prepare($key, $userdata->data->{$key}, $user);
                }
                foreach ($this->get_user_meta_keys() as $key){
                    $meta_val[$key] = self::prepare($key, get_user_meta($user, $key, true), $user);
                }
                $output[] = $meta_val;
            }
            header('Content-type: application/force-download; charset=utf-8'); 
            header('Content-Disposition: attachment; filename="dpwap-user-'.date('YmdTHi').'.json"');
            echo json_encode($output);
            exit;
        }
    }

    public function get_user_data(){
        return $this->user_data;
    }

    public function get_user_meta_keys() {
        global $wpdb;
        $meta_keys = array();
        $select = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
        $usermeta = $wpdb->get_results( $select, ARRAY_A );
        foreach ($usermeta as $key => $value) {
            if( $value["meta_key"] == 'role' )
                continue;

            $meta_keys[] = $value["meta_key"];
        }
        return $meta_keys;
    }

    public function get_user_id_list(){
        $args = array( 'fields' => array( 'ID' ) );
        $users = get_users( $args );
        $list = array();
        foreach ( $users as $user ) {
            $list[] = $user->ID;
        }
        return $list;
    }

    static function prepare( $key, $value, $user = 0 ){
        $original_value = $value;
        if($key == 'role'){
            return self::get_role($user);
        }
        if(is_array($value) || is_object($value)){
            return serialize( $value );
        }
        elseif(strtotime($value)){ // dates in datetime format
            return date("Y-m-d H:i:s", strtotime($value));
        }
        elseif(is_int($value) && ((self::is_valid_timestamp($value) && strlen($value) > 4))){ // dates in timestamp format
            return date("Y-m-d H:i:s", $value);
        }
        else{
            return apply_filters( 'dpwap_export_prepare', self::clean_bad_characters_formulas( $value ), $original_value );
        }
    }

    static function get_role( $user_id ){
        $user = get_user_by( 'id', $user_id );
        return implode( ',', $user->roles );
    }

    static function is_valid_timestamp( $timestamp ){
        return ( (string) (int) $timestamp === $timestamp ) && ( $timestamp <= PHP_INT_MAX ) && ( $timestamp >= ~PHP_INT_MAX );
    }

    static function clean_bad_characters_formulas( $value ){
        if( strlen( $value ) == 0 )
            return $value;

        $bad_characters = array( '+', '-', '=', '@' );
        $first_character = substr( $value, 0, 1 );
        if( in_array( $first_character, $bad_characters ) )
            $value = "\\" . $first_character . substr( $value, 1 );

        return $value;
    }

}
Dpwap_Premium_Download_Service::get_instance();