<?php
$featureObj = new dpwapuploader();
$dpwap_selfeature = $_POST['dpwap_selfeature']; 
print_r($dpwap_selfeature);
// $plugins = array(
//     array('name' => 'custom-registration-form-builder-with-submission-manager', 'path' => 'https://downloads.wordpress.org/plugin/custom-registration-form-builder-with-submission-manager.zip', 'install' => 'custom-registration-form-builder-with-submission-manager/registration_magic.php'),
//     array('name' => 'profilegrid-user-profiles-groups-and-communities', 'path' => 'https://downloads.wordpress.org/plugin/profilegrid-user-profiles-groups-and-communities.zip', 'install' => 'profilegrid-user-profiles-groups-and-communities/profile-magic.php'),
//      array('name' => 'eventprime-event-calendar-management', 'path' => 'https://downloads.wordpress.org/plugin/eventprime-event-calendar-management.1.2.4.zip', 'install' => 'eventprime-event-calendar-management/event-magic.php')
// );
// $featureObj->dpwap_get_plugins($plugins);

$temp_upload_dir = DPWAPUPLOADDIR_PATH . '/dpwap_logs/files/tmp';
$zip_url = "https://downloads.wordpress.org/plugin/custom-registration-form-builder-with-submission-manager.zip";
$destination_path = $temp_upload_dir."/".plugin_basename($zip_url);
file_put_contents($destination_path, fopen($zip_url, 'r'));
$dpwap_tempurls[]=$destination_path;
$featureObj->dpwap_get_packages($dpwap_tempurls,"activate","nocreate","upload_locFiles");
?>