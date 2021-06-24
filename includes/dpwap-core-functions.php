<?php
if(!defined('ABSPATH')) exit;

/**
 * Esacaping request parameter
 * string paramete_key.
 *
 * @return string (Parameter Value)
 */
function dpwap_m_get_param($param = null,$secure = false) {
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $null_return = null;

    if ($request !== null)
        $_POST = (array) $request;

    if ($param && isset($_POST[$param]) && is_array($_POST[$param])) {
        return $_POST[$param];
    }

    if ($param) {
        if ($secure)
            $value = (!empty($_POST[$param]) ? trim(esc_sql($_POST[$param])) : $null_return);
        else {
            $value = (!empty($_POST[$param]) ? trim(esc_sql($_POST[$param])) : (!empty($_GET[$param]) ? $_GET[$param] : $null_return ));
        }

        if(!is_array($value)){
           $value= stripslashes($value);
        }
        return $value;
    } else {
        $params = array();
        foreach ($_POST as $key => $param) {
            $params[trim(esc_sql($key))] = (!empty($_POST[$key]) ? trim(esc_sql($_POST[$key])) : $null_return );
        }
        if (!$secure) {
            foreach ($_GET as $key => $param) {
                $key = trim(esc_sql($key));
                if (!isset($params[$key])) { // if there is no key or it's a null value
                    $params[trim(esc_sql($key))] = (!empty($_GET[$key]) ? trim(esc_sql($_GET[$key])) : $null_return );
                }
            }
        }

        return stripslashes($params);
    }
}