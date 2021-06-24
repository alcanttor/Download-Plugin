<?php
if (!defined( 'ABSPATH')){
    exit;
}

class Dpwap_Premium_Upload_Service {
    
    private $dao;
    private static $instance = null;
    
    private function __construct() {
        
    }
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function import_all_users(){
        $response = array();
        $dpwap_security = sanitize_text_field(dpwap_m_get_param('dpwap_security'));
        if(empty($dpwap_security) || !wp_verify_nonce($dpwap_security, 'dpwap_user_upload')){
            $returnData['errors'] = __('Nonce check failed', 'download-plugin-premium');
            return $returnData;
        }
        $dpwap_helper = new Dpwap_Premium_Helper();
        $upload_user_format = sanitize_text_field(dpwap_m_get_param('upload_user_format'));
        $update_existing_users = sanitize_text_field(dpwap_m_get_param('update_existing_users'));
        $update_roles_existing_users = sanitize_text_field(dpwap_m_get_param('update_roles_existing_users'));
        $empty_metadata_action = sanitize_text_field(dpwap_m_get_param('empty_metadata_action'));
        $uploaded_file = $_FILES['upload_user_file'];
        // File is not uploaded
        if(!isset($uploaded_file['name']) || (isset($uploaded_file['name']) && trim($uploaded_file['name']) == '')){
            $returnData['errors'] = __('Please upload the file', 'download-plugin-premium');
            return $returnData;
        }
        // File name validation
        $ex = explode('.', $uploaded_file['name']);
        $name_end = end($ex);
        if($name_end != $upload_user_format){
            $returnData['errors'] = __('Please upload the valid file', 'download-plugin-premium');
            return $returnData;
        }
        // File Type is not valid
        if(!isset($uploaded_file['type'])){
            $returnData['errors'] = __('Please upload the valid file', 'download-plugin-premium');
            return $returnData;
        }
        else{
            if($upload_user_format == 'csv'){
                $allowedFileType = ['text/csv', 'application/vnd.ms-excel'];
                if(!in_array(strtolower($uploaded_file['type']), $allowedFileType)){
                    $returnData['errors'] = __('Please upload the valid file', 'download-plugin-premium');
                    return $returnData;
                }
            }
            if($upload_user_format == 'json'){
                $allowedFileType = ['application/json'];
                if(!in_array(strtolower($uploaded_file['type']), $allowedFileType)){
                    $returnData['errors'] = __('Please upload the valid file', 'download-plugin-premium');
                    return $returnData;
                }
            }
        }
        if($upload_user_format == 'csv'){
        // upload file
            $uploadfile = wp_handle_upload($uploaded_file, array('test_form' => false, 'mimes' => array('csv' => 'text/csv')));
            if (!$uploadfile || isset($uploadfile['error'])){
                $returnData['errors'] = __('Problem in uploading file to import. Error details: ' . var_export( $uploadfile['error'], true ), 'download-plugin-premium');
                return $returnData;
            }
            $attachment_id = $dpwap_helper->dpwap_get_attachment_id_by_url($uploadfile['url']);
            $file = $uploadfile['file'];
        }
        else{
            $filename = $uploaded_file['name'];
            $tmp_name = $uploaded_file['tmp_name'];
            $upload_dir = wp_upload_dir();
            if (move_uploaded_file($uploaded_file["tmp_name"], $upload_dir['path'] . "/" . $filename)) {
                $upload_url = $upload_dir['url'] . "/" . $filename;
                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = array(
                    'guid'           => $upload_url,
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                $attachment_id = wp_insert_attachment( $attachment, $upload_dir['path'] . "/" . $filename );
                if (!is_wp_error($attachment_id)){
                    $file = get_attached_file($attachment_id);
                }
            }
        }
        // now import
        
        @set_time_limit(0);
        $row = 0;$html = '';
        $results = array( 'created' => 0, 'updated' => 0 );
        $users_registered = $headers = $headers_filtered = $errors = array();
        $restricted_fields = $dpwap_helper->dpwap_get_restricted_fields();
        $all_roles = array_keys( wp_roles()->roles );
        $manager = new SplFileObject($file);
        if($upload_user_format == 'csv'){
            while ($data = $manager->fgetcsv()){
                $row++;
                if(count($data) == 1)
                    $data = $data[0];
                
                if($data == NULL){
                    break;
                }
                else if(!is_array($data)){
                    $returnData['errors'] = __('CSV file seems to be bad formed', 'download-plugin-premium');
                    return $returnData;
                }
                foreach($data as $key => $value){
                    $data[$key] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', trim($value));
                }
                for($i = 0; $i < count($data); $i++){
                    $data[$i] = $dpwap_helper->dpwap_string_conversion($data[$i]);
                    if( is_serialized( $data[$i] ) ) // serialized
                        $data[$i] = maybe_unserialize( $data[$i] );
                    elseif( strpos( $data[$i], "::" ) !== false  ) // list of items
                        $data[$i] = explode( "::", $data[$i] );
                }
                if($row == 1){
                    // check min columns username - email
                    if(count($data) < 2){
                        $returnData['errors'] = __('File must contain at least 2 columns: username and email', 'download-plugin-premium');
                        return $returnData;
                    }
                    $i = 0;
                    $password_position = false;
                    $id_position = false;
                    foreach ($restricted_fields as $dpwap_restricted_field) {
                        $positions[$dpwap_restricted_field] = false;
                    }
                    foreach($data as $element){
                        $headers[] = $element;
                        if(in_array(strtolower($element), $restricted_fields))
                            $positions[strtolower($element)] = $i;
                        if(!in_array(strtolower($element), $restricted_fields))
                            $headers_filtered[] = $element;
                        $i++;
                    }
                    $columns = count($data);
                    $html .= $dpwap_helper->print_table_header_footer( $headers );
                }
                else{
                    /*if(count($data) != $columns){ // if number of columns is not the same that columns in header
                        $errors[] = $dpwap_helper->new_error( $row, __( 'Row does not have the same columns than the header, we are going to ignore this row', 'download-plugin-premium') );
                        continue;
                    }*/
                    
                    $username = $data[0];
                    $email = $data[1];
                    $user_id = 0;
                    $password = wp_generate_password();
                    $role = "";
                    $role_position = $positions["role"];
                    $id_position = $positions["id"];
                    $id = (empty($id_position)) ? '' : $data[$id_position];
                    $created = false;
                    $roles_cells = explode(',', $data[$role_position]);
                    if(!is_array($roles_cells))
                        $roles_cells = array($roles_cells);
                    array_walk($roles_cells, 'trim');
                    foreach($roles_cells as $it => $role_cell)
                        $roles_cells[$it] = strtolower($role_cell);

                    $role = $roles_cells;

                    if(!empty($email) && sanitize_email($email) == ''){ // if email is invalid
                        $errors[] = $dpwap_helper->new_error($row, sprintf(__('Invalid email "%s"', 'download-plugin-premium'), $email));
                        continue;
                    }
                    elseif(empty($email)) {
                        $errors[] = $dpwap_helper->new_error($row, __('Email not specified', 'download-plugin-premium'));
                        continue;
                    }
                    if(!empty($id)){
                        if( $dpwap_helper->dpwap_user_id_exists( $id ) ){
                            if( $update_existing_users == 'no' ){
                                $errors[] = $dpwap_helper->new_error( $row, sprintf(__('User with ID "%s" exists, we ignore it', 'download-plugin-premium'), $id) );
                                continue;
                            }
                            // we check if username is the same than in row
                            $user = get_user_by('ID', $id);
                            if($user->user_login == $username){
                                if($user->user_email == $email){
                                    $user_id = $id;
                                }
                            }
                            else{
                                $errors[] = $dpwap_helper->new_error( $row, sprintf( __( 'Problems with ID "%s" username is not the same in the uploaded file and in database', 'download-plugin-premium' ), $id ) );     
                                continue;
                            }
                        }
                        else{
                            $user_id = wp_insert_user( array(
                                'ID'          =>  $id,
                                'user_login'  =>  $username,
                                'user_email'  =>  $email,
                                'user_pass'   =>  $password
                            ));
                            $created = true;
                        }
                    }
                    else if(username_exists($username)){
                        if($update_existing_users == 'no'){
                            $errors[] = $dpwap_helper->new_error($row, sprintf(__('User with username "%s" exists, we ignore it', 'download-plugin-premium' ), $username));
                            continue;
                        }
                        $user_object = get_user_by("login", $username);
                        if($user_object->user_email == $email){
                            $user_id = $user_object->ID;
                        }
                    }
                    if($columns > 2){
                        for($i = 2; $i < $columns; $i++){
                            if(!empty($data)){
                                if(empty($user_id)){
                                    $user_id = wp_insert_user( array(
                                        'ID'          =>  $id,
                                        'user_login'  =>  $username,
                                        'user_email'  =>  $email,
                                        'user_pass'   =>  $password
                                    ));
                                    $created = true;
                                }
                                if( strtolower($headers[$i]) == "password"){ // passwords -> continue
                                    continue;
                                }
                                if(empty($created)){
                                    if($update_existing_users == 'no') continue;

                                    if( strtolower($headers[$i]) == "role"){
                                        if($update_roles_existing_users == 'no') continue;

                                        if(!in_array('administrator', $dpwap_helper->dpwap_get_roles_by_user_id($user_id))){
                                            $user_object = get_user_by("ID", $user_id);
                                            $default_roles = $user_object->roles;
                                            // first delete old user roles
                                            foreach ( $default_roles as $default_role ) {
                                                $user_object->remove_role( $default_role );
                                            }
                                            // add role
                                            if( !empty($role)){
                                                if(is_array($role)){
                                                    foreach ($role as $single_role) {
                                                        $user_object->add_role($single_role);
                                                    }
                                                }
                                                else{
                                                    $user_object->add_role($role);
                                                }
                                            }
                                        }
                                    }
                                }
                                // delete empty metadata option
                                if($data[$i] === ''){
                                    if($empty_metadata_action == "delete")
                                        delete_user_meta($user_id, $headers[$i]);
                                    else
                                        continue;   
                                }
                                else{
                                    update_user_meta($user_id, $headers[$i], $data[$i]);
                                    continue;
                                }
                            }
                        }
                    }
                    // results
                    ($created) ? $results['created']++ : $results['updated']++;
                    $html .= $dpwap_helper->print_row_imported( $row, $data, $errors );
                }
            }
            $html .= $dpwap_helper->print_table_end();
            $html .= $dpwap_helper->print_errors( $errors );
            $html .= $dpwap_helper->print_results( $results, $errors );
            $returnData = array('success' => true, 'data' => $html);
        }
        if($upload_user_format == 'json'){
            $json_file = $manager->fread($manager->getSize());
            $json_string = json_decode($json_file, true);
            $row = 0;
            $i = 0;
            $password_position = $id_position = false;
            foreach($json_string as $data){
                $row++;
                if(count($data) < 2){
                    $returnData['errors'] = __('File must contain at least 2 columns: username and email', 'download-plugin-premium');
                    return $returnData;
                }
                foreach($restricted_fields as $acui_restricted_field){
                    $positions[$acui_restricted_field] = false;
                }
                foreach($data as $key => $value){
                    $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', trim($value));
                    $value = $dpwap_helper->dpwap_string_conversion($value);
                    if( is_serialized($value) ) // serialized
                        $value = maybe_unserialize( $value );
                    elseif( strpos( $value, "::" ) !== false  ) // list of items
                        $value = explode( "::", $value );

                    $data[$key] = $value;
                    if($row == 1){
                        $headers[] = $key;
                        if( in_array(strtolower($key), $restricted_fields))
                            $positions[strtolower($key)] = $i;

                        if( !in_array(strtolower($key), $restricted_fields))
                            $headers_filtered[] = $key;

                        $i++;
                    }
                }

                if($row == 1){
                    $columns = count($data);
                    $html .= $dpwap_helper->print_table_header_footer($headers);
                }
                $username = $data['user_login'];
                $email = $data['user_email'];
                $user_id = 0;
                $password = wp_generate_password();
                $role = "";
                /*$role_position = $positions["role"];
                $id_position = $positions["id"];*/
                $id = isset($data['id']) ? $data['id'] : '';
                $created = false;
                $roles_cells = isset($data['role']) ? explode(',', $data['role']) : '';
                if(!empty($roles_cells)){
                    if(!is_array($roles_cells))
                        $roles_cells = array($roles_cells);
                    array_walk($roles_cells, 'trim');
                    foreach($roles_cells as $it => $role_cell)
                        $roles_cells[$it] = strtolower($role_cell);

                    $role = $roles_cells;
                }

                if(!empty($email) && sanitize_email($email) == ''){ // if email is invalid
                    $errors[] = $dpwap_helper->new_error($row, sprintf(__('Invalid email "%s"', 'download-plugin-premium'), $email));
                    continue;
                }
                elseif(empty($email)) {
                    $errors[] = $dpwap_helper->new_error($row, __('Email not specified', 'download-plugin-premium'));
                    continue;
                }
                if(!empty($id)){
                    if($dpwap_helper->dpwap_user_id_exists($id)){
                        if($update_existing_users == 'no'){
                            $errors[] = $dpwap_helper->new_error( $row, sprintf(__('User with ID "%s" exists, we ignore it', 'download-plugin-premium'), $id) );
                            continue;
                        }
                        // we check if username is the same than in row
                        $user = get_user_by('ID', $id);
                        if($user->user_login == $username){
                            if($user->user_email == $email){
                                $user_id = $id;
                            }
                        }
                        else{
                            $errors[] = $dpwap_helper->new_error( $row, sprintf( __( 'Problems with ID "%s" username is not the same in the uploaded file and in database', 'download-plugin-premium' ), $id ) );     
                            continue;
                        }
                    }
                    else{
                        $user_id = wp_insert_user( array(
                            'ID'          =>  $id,
                            'user_login'  =>  $username,
                            'user_email'  =>  $email,
                            'user_pass'   =>  $password
                        ));
                        $created = true;
                    }
                }
                else if(username_exists($username)){
                    if($update_existing_users == 'no'){
                        $errors[] = $dpwap_helper->new_error($row, sprintf(__('User with username "%s" exists, we ignore it', 'download-plugin-premium' ), $username));
                        continue;
                    }
                    $user_object = get_user_by("login", $username);
                    if($user_object->user_email == $email){
                        $user_id = $user_object->ID;
                    }
                }
                if($columns > 2){
                    for($i = 2; $i < $columns; $i++){
                        if(!empty($data)){
                            if(empty($user_id)){
                                $user_id = wp_insert_user( array(
                                    'ID'          =>  $id,
                                    'user_login'  =>  $username,
                                    'user_email'  =>  $email,
                                    'user_pass'   =>  $password
                                ));
                                $created = true;
                            }
                            if( strtolower($headers[$i]) == "password"){ // passwords -> continue
                                continue;
                            }
                            if(empty($created)){
                                if($update_existing_users == 'no') continue;

                                if( strtolower($headers[$i]) == "role"){
                                    if($update_roles_existing_users == 'no') continue;

                                    if(!in_array('administrator', $dpwap_helper->dpwap_get_roles_by_user_id($user_id))){
                                        $user_object = get_user_by("ID", $user_id);
                                        $default_roles = $user_object->roles;
                                        // first delete old user roles
                                        foreach ( $default_roles as $default_role ) {
                                            $user_object->remove_role( $default_role );
                                        }
                                        // add role
                                        if( !empty($role)){
                                            if(is_array($role)){
                                                foreach ($role as $single_role) {
                                                    $user_object->add_role($single_role);
                                                }
                                            }
                                            else{
                                                $user_object->add_role($role);
                                            }
                                        }
                                    }
                                }
                            }
                            // delete empty metadata option
                            if($data[$headers[$i]] === ''){
                                if($empty_metadata_action == "delete")
                                    delete_user_meta($user_id, $headers[$i]);
                                else
                                    continue;   
                            }
                            else{
                                update_user_meta($user_id, $headers[$i], $data[$headers[$i]]);
                                continue;
                            }
                        }
                    }
                }
                // results
                ($created) ? $results['created']++ : $results['updated']++;
                $html .= $dpwap_helper->print_row_imported( $row, $data, $errors );
            }
            $html .= $dpwap_helper->print_table_end();
            $html .= $dpwap_helper->print_errors( $errors );
            $html .= $dpwap_helper->print_results( $results, $errors );
            $returnData = array('success' => true, 'data' => $html);
        }
        return $returnData;
    }

}
Dpwap_Premium_Upload_Service::get_instance();