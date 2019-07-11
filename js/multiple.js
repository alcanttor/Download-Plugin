/*-- MPI Jquery Script
-------------------------------------------------------*/

//validate upload plugin and check ZIP
function check_valid_zipfile(dpwap_eleId){ 
	var	extension = ".zip";
	var inp = document.getElementById(dpwap_eleId);
    var count = inp.files.length;
    for(var a=0; a < count; a++){
		var fieldvalue = inp.files.item(a).name;
		var thisext = fieldvalue.substr(fieldvalue.lastIndexOf('.'));
		if(thisext == extension){ 
		return true; 
		}
    }

	alert("Please upload vaild .zip extension file.");
	return false;
}

function activateAllPLugins(){ 
	document.getElementById('form_alldpwap').submit();
}

//single plugin activated function
jQuery(document).ready(function() {
    jQuery('.dpwap_inner a').click(function() {
    	 var that = this;
    	 var dpwapUrl = jQuery(this). attr("href");
        
    	 var dpwapUrl2 = decodeURIComponent(dpwapUrl).split("&");
    	 var dpwapUrl3= dpwapUrl2[1].split('=');
    	 jQuery.ajax({
            url    : ajaxurl,
            type : 'post',
            data : {
                action : 'dpwap_plugin_activate',
                dpwap_url : dpwapUrl3[1]
            },
            success : function( response ) {
            	alert("Plugin activated successfully");
            	jQuery(that).replaceWith('<h4>Plugin activated</h4>');
            }
        }); 
         return false;
    });
});

