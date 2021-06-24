<?php
if (!defined( 'ABSPATH')){
    exit;
}

class Dpwap_Premium_Helper{

    public function dpwap_get_attachment_id_by_url( $url ) {
        $wp_upload_dir = wp_upload_dir();
        // Strip out protocols, so it doesn't fail because searching for http: in https: dir.
        $dir = set_url_scheme( trailingslashit( $wp_upload_dir['baseurl'] ), 'relative' );
        // Is URL in uploads directory?
        if ( false !== strpos( $url, $dir ) ) {
            $file = basename( $url );
            $query_args = array(
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'fields'      => 'ids',
                'meta_query'  => array(
                    array(
                        'key'     => '_wp_attachment_metadata',
                        'compare' => 'LIKE',
                        'value'   => $file,
                    ),
                ),
            );
            $query = new WP_Query( $query_args );
            if ( $query->have_posts() ) {
                foreach ( $query->posts as $attachment_id ) {
                    $meta          = wp_get_attachment_metadata( $attachment_id );
                    $original_file = basename( $meta['file'] );
                    $cropped_files = wp_list_pluck( $meta['sizes'], 'file' );
                    if ( $original_file === $file || in_array( $file, $cropped_files ) ) {
                        return (int) $attachment_id;
                    }
                }
            }
        }
        return false;
    }

    public function dpwap_string_conversion($string){
        if(!preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string)){
            return utf8_encode($string);
        }
        else
            return $string;
    }

    public function dpwap_get_wp_users_fields(){
        return array("id", "user_email", "user_nicename", "user_url", "display_name", "nickname", "first_name", "last_name", "description", "user_registered", "password", "user_pass", "locale", "show_admin_bar_front", "user_login");
    }

    public function dpwap_get_restricted_fields(){
        $wp_users_fields = $this->dpwap_get_wp_users_fields();
        $wp_min_fields = array("Username", "Email", "role" );
        $wp_restricted_fields = array_merge($wp_users_fields, $wp_min_fields);
        return $wp_restricted_fields;
    }

    public function dpwap_get_random_unique_username( $prefix = '' ){
        do {
            $rnd_str = sprintf("%06d", mt_rand(1, 999999));
        } 
        while( username_exists( $prefix . $rnd_str ) );
        return $prefix . $rnd_str;
    }

    public function dpwap_user_id_exists( $user_id ){
        if ( get_userdata( $user_id ) === false )
            return false;
        else
            return true;
    }

    public function dpwap_get_roles_by_user_id($user_id){
        $roles = array();
        $user = new WP_User($user_id);
        if ( !empty($user->roles) && is_array($user->roles)) {
            foreach ($user->roles as $role)
                $roles[] = $role;
        }
        return $roles;
    }

    public function new_error( $row, $message = '', $type = 'error' ){
        return array( 'row' => $row, 'message' => $message, 'type' => $type );
    }

    public function print_table_header_footer( $headers ){
        $html = '';
        $html .= '<h3>'.__( 'Inserting and updating data', 'download-plugin-premium' ).'</h3>';
        $html .= '<div style="overflow-x:auto;">';
        $html .= '<table id="dpwap_results" class="table"><thead><tr>';
            $html .= '<th>'. __( 'Row', 'download-plugin-premium' ).'</th>';
                foreach( $headers as $element ): 
                    $html .= '<th>' . $element . '</th>'; 
                endforeach;
            $html .= '</tr></thead>';
            $html .= '<tfoot><tr>';
                $html .= '<th>'. __( 'Row', 'download-plugin-premium' ).'</th>';
                foreach( $headers as $element ): 
                    $html .= '<th>' . $element . '</th>';
                endforeach;
            $html .= '</tr></tfoot>';
        $html .= '<tbody>';
        return $html;
    }

    public function print_row_imported( $row, $data, $errors ){
        $styles = $html = '';
        if( !empty( Dpwap_Premium_Helper::get_errors_by_row( $errors, $row, 'any' ) ) )
            $styles = "background-color:red; color:white;";

        $html .= '<tr style="'.$styles.'"><td>' . ($row - 1) . '</td>';
        foreach ( $data as $element ){
            if( is_wp_error( $element ) )
                $element = $element->get_error_message();
            elseif( is_array( $element ) ){
                $element_string = '';
                $i = 0;
                foreach( $element as $it => $el ){
                    $element_string .= ( is_wp_error( $el ) ? $el->get_error_message() : '' );

                    if(++$i !== count( $element ) ){
                        $element_string .= ',';
                    }
                }
                $element = $element_string;
            }
            $element = sanitize_textarea_field( $element );
            $html .= '<td>'.$element.'</td>';
        }
        $html .= '</tr>';
        return $html;
    }

    public function print_table_end(){
        $html = '</tbody></table></div>';
        return $html;
    }

    static function get_errors_by_row( $errors, $row, $type = 'error' ){
        $errors_found = array();
        foreach( $errors as $error ){
            if( $error['row'] == $row && ( $error['type'] == $type || 'any' == $type ) ){
                $errors_found[] = $error['message'];
            }
        }
        return $errors_found;
    }

    public function print_errors( $errors ){
        if(empty($errors))
            return;

        $html = '<h3>'.__( 'Errors, warnings and notices', 'download-plugin-premium' ).'</h3>';
        $html .= '<table id="dpwap_errors" class="table"><thead><tr>';
                    $html .= '<th>'.__( 'Row', 'download-plugin-premium' ).'</th>';
                    $html .= '<th>'.__( 'Details', 'download-plugin-premium' ).'</th>';
                    $html .= '<th>'.__( 'Type', 'download-plugin-premium' ).'</th>';
            $html .= '</tr>
            </thead>
            <tfoot>
                <tr>';
                    $html .= '<th>'.__( 'Row', 'download-plugin-premium' ).'</th>';
                    $html .= '<th>'.__( 'Details', 'download-plugin-premium' ).'</th>';
                    $html .= '<th>'.__( 'Type', 'download-plugin-premium' ).'</th>';
            $html .= '</tr>
            </tfoot>
            <tbody>';
                foreach( $errors as $error ){
                    $html .= '<tr>';
                        $html .= '<td>'.$error['row'].'</td>';
                        $html .= '<td>'.$error['message'].'</td>';
                        $html .= '<td>'.$error['type'].'</td>';
                    $html .= '</tr>';
                }
        $html .= '</tbody></table>';
        return $html;
    }

    public function print_results( $results, $errors ){
        $html = '<h3>'.__( 'Results', 'download-plugin-premium' ).'</h3>';
        $html .= '<table id="dpwap_errors" class="table"><tbody><tr>';
                    $html .= '<th>'.__( 'Users processed', 'download-plugin-premium' ).'</th>';
                    $html .= '<td>'.($results['created'] + $results['updated']).'</td>';
                $html .= '</tr>
                <tr>';
                    $html .= '<th>'.__( 'Users created', 'download-plugin-premium' ).'</th>';
                    $html .= '<td>'.$results['created'].'</td>';
                $html .= '</tr>
                <tr>';
                    $html .= '<th>'.__( 'Users updated', 'download-plugin-premium' ).'</th>';
                    $html .= '<td>'.$results['updated'].'</td>';
                $html .= '</tr>
                <tr>';
                    $html .= '<th>'.__( 'Errors, warnings and notices found', 'download-plugin-premium' ).'</td>';
                    $html .= '<td>'.count( $errors ).'</td>';
                $html .= '</tr>
            </tbody>
        </table>';
        return $html;
    }

}