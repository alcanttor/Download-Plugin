<div class="wrap">
    <div class="dpwap-promo-nav-tabs">
        
        <nav class="nav-tab-wrapper woo-nav-tab-wrapper" id="dpwap-promo-tabs">
       
            <a href="javascript:void(0)" id="tab1" class="nav-tab nav-tab-active"><?php _e('Download Users', 'download-plugin-premium'); ?></a>
            <a href="javascript:void(0)" id="tab2" class="nav-tab"><?php _e('Upload Users', 'download-plugin-premium'); ?></a>
      
        </nav>
    </div>
    <div class="dpwap-users-nav-container" id="tab1C">
        <div class="dpwap-container">
            <h2><?php _e('Download Users', 'download-plugin-premium');?></h2>       
            <form name="dpwap_user_download" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                <input type="hidden" name="action" value="download_users">
                <?php wp_nonce_field('dpwap_user_download', 'dpwap_security');?>
                    <table class="form-table">
                <tbody>
                    <tr valign="top" class="dpwap-user-format">
                     <th scope="row" class="titledesc">
                    <label for="download_user_format"><?php _e('Select Format', 'download-plugin-premium');?></label>
                     </th>
                       <td class="forminp forminp-select">
                    <select name="download_user_format" required>
                        <option value=""><?php _e('Select Format', 'download-plugin-premium');?></option>
                        <option value="csv"><?php _e('CSV', 'download-plugin-premium');?></option>
                        <option value="json"><?php _e('JSON', 'download-plugin-premium');?></option>
                    </select>
       
                        </td>
                    </tr>
                    <tr valign="top"><td>
                <div class="dpwap-user-button">
                    <button type="submit" name="dpwap_download_user" class="button-primary">
                        <?php _e('Download', 'download-plugin-premium');?>
                    </button>
                </div>
                        </td></tr>
                </tbody>
                  </table>
            </form>
                  
        </div>
    </div>

    <div class="dpwap-users-nav-container" id="tab2C">
        <div class="dpwap-container">
            <h2><?php _e('Upload Users', 'download-plugin-premium');?></h2>  
            <form id="dpwap_user_upload" name="dpwap_user_upload" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_users">
                <?php wp_nonce_field('dpwap_user_upload', 'dpwap_security');?>
                <table class="table dpwap-user-format form-table">
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="upload_user_format"><?php _e('Select Format', 'download-plugin-premium');?></label>
                        </th>
                        <td class="forminp forminp-select">
                            <select name="upload_user_format" class="upload_user_format" required>
                                <option value=""><?php _e('Select Format', 'download-plugin-premium');?></option>
                                <option value="csv"><?php _e('CSV', 'download-plugin-premium');?></option>
                                <option value="json"><?php _e('JSON', 'download-plugin-premium');?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="dpwap-file-format" style="display: none;">
                    <table class="form-table">
                        <tr valign="top">
                            <th  scope="row" class="titledesc">
                                <label for="upload_user_file"><?php _e('Select File', 'download-plugin-premium');?></label>
                                </th>
                            <td class="forminp forminp-select">
                                <input type="file" name="upload_user_file" id="upload_user_file" required data-invalid_file_error="<?php _e('Please upload a valid file', 'download-plugin-premium');?>">
                            </td>
                        </tr>
                    </table>
                    <div class="dpwap-user-import-setting-container">
                        <h2 class="dpwap-setting-header"><?php _e('Settings', 'download-plugin-premium');?></h2>
                        <div class="dpwap-setting-body">
                            <table class="form-table">
                                <tr valign="top">
                                    <th  scope="row" class="titledesc"><label for="update_existing_users"><?php _e('Update existing users?', 'download-plugin-premium');?></label></th>
                                    <td class="forminp forminp-select">
                                        <select name="update_existing_users">
                                            <option value="yes"><?php _e('Yes', 'download-plugin-premium');?></option>
                                            <option value="no"><?php _e('No', 'download-plugin-premium');?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row" class="titledesc"><label for="update_roles_existing_users"><?php _e('Update role for existing users?', 'download-plugin-premium');?></label></th>
                                    <td class="forminp forminp-select">
                                        <select name="update_roles_existing_users">
                                            <option value="no"><?php _e('No', 'download-plugin-premium');?></option>
                                            <option value="yes"><?php _e('Yes', 'download-plugin-premium');?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row" class="titledesc"><label for="empty_metadata_action"><?php _e('Action for empty metadata', 'download-plugin-premium');?></label></th>
                                    <td class="forminp forminp-select">
                                        <select name="empty_metadata_action">
                                            <option value="leave"><?php _e('Leave the old value for this metadata', 'download-plugin-premium');?></option>
                                            <option value="delete"><?php _e('Delete the old value for this metadata', 'download-plugin-premium');?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="dpwap-user-button">
                    <button type="submit" name="dpwap_upload_user" id="submitButton" class="button-primary">
                        <?php _e('Upload', 'download-plugin-premium');?>
                    </button>
                </div>
            </form>
            <!-- Progress bar -->
            <div class="dpwap-progress-container" style="display: none;">
                <div class="dpwap-progress-bar">
                    <div class="dpwap-percent">0%</div>
                </div>
            </div>
            <div class="dpwap-status-message"></div>
        </div>
    </div>
</div>
<script>
    (function($){ 
        $(document).ready(function() {
            $('.dpwap-users-nav-container').hide();
            $('.dpwap-users-nav-container:first').show();

            $('#dpwap-promo-tabs a').click(function(){
                var t = $(this).attr('id');
                $('#dpwap-promo-tabs a').toggleClass('nav-tab-active');
                if($(this).hasClass('nav-tab-active')){ 
                    //$('#dpwap-promo-tabs a').toggleClass('nav-tab-active');           

                 

                    $('.dpwap-users-nav-container').hide();
                    $('#'+ t + 'C').fadeIn('slow');
                }
            });
        });
        $(".upload_user_format").change(function(){
            $(".dpwap-file-format").hide();
            if($(this).val() != ''){
                $(".dpwap-file-format").show();
            }
        });
        // validate uploaded file
        $("#upload_user_file").change(function(){
            $(".dpwap-status-message").html('');
            var upload_user_format = $(".upload_user_format").val();
            if(upload_user_format == 'csv'){
                var allowedFileType = ['text/csv', 'application/vnd.ms-excel'];
            }
            else if(upload_user_format == 'json'){
                var allowedFileType = ['application/json'];
            }
            var file = this.files[0];
            var fileType = file.type;
            if(!allowedFileType.includes(fileType)){
                var errorMsg = $("#upload_user_file").data('invalid_file_error');
                $(".dpwap-status-message").html('<div class="dpwap-error notice-error error"><p>'+errorMsg+'</p></div>');
                $("#upload_user_file").val('');
                return false;
            }
        });
        
        $('#submitButton').click(function () {
            $('#dpwap_user_upload').ajaxForm({
                url: "<?php echo esc_url( admin_url('admin-post.php') ); ?>",
                beforeSubmit: function () {
                    $(".dpwap-status-message").html('');
                    if($("#upload_user_file").val() == "") {
                        $(".dpwap-status-message").html('<div class="dpwap-error notice-error error"><p>Choose a file to upload.</p></div>');
                        return false; 
                    }
                    $(".dpwap-progress-container").show();
                    var percentValue = '0%';
                    $('.dpwap-progress-bar').width(percentValue);
                    $('.dpwap-percent').html(percentValue);
                },
                uploadProgress: function (event, position, total, percentComplete) {
                    var percentValue = percentComplete + '%';
                    $(".dpwap-progress-bar").animate({
                        width: '' + percentValue + ''
                    }, {
                        duration: 10000,
                        easing: "linear",
                        step: function (x) {
                        percentText = Math.round(x * 100 / percentComplete);
                            $(".dpwap-percent").text(percentText + "%");
                            /*if(percentText == "100") {
                                   $("#outputImage").show();
                            }*/
                        }
                    });
                },
                error: function (response, status, e) {
                    $('.dpwap-status-message').html('<div class="dpwap-error notice-error error"><p>File upload failed, please try again.</p></div>');
                },
                
                complete: function (xhr) {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.success == true){
                        $(".dpwap-status-message").html(response.data.data);
                    }
                    else{  
                        $(".dpwap-status-message").show();
                        $(".dpwap-status-message").html('<div class="dpwap-error notice-error error"><p>'+response.data.data+'<p></div>');
                        $(".dpwap-progress-bar").stop();
                    }
                }
            });
        });
    })(jQuery);
</script>