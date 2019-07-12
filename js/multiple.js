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

//all plugins form submit  function
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

  //feature poup second section activated function
   jQuery('#next_first').click(function() {
       jQuery('#dpwap_section_first').fadeOut('1000');
       jQuery("#dpwap_section_second").show();
   });

//feature poup third(suggest theme and plugins) section activated function
   jQuery('#next_second').click(function() {
       jQuery("#dpwap_section_first").hide();
       jQuery('#dpwap_section_second').fadeOut('100');
       jQuery("#dpwap_section_third").show();

            var wpdapFeature = [];
            jQuery.each(jQuery("input[name='feature']:checked"), function(){            
            wpdapFeature.push(jQuery(this).val());
            });
             jQuery.ajax({
            url    : ajaxurl,
            type : 'post',
            data : {
                action : 'dpwap_feature_select',
                dpwap_feature : wpdapFeature
            },
            success : function( response ) {
                 jQuery("#dpwap_third_inner").html(response); 
            }
        }); 
        });
//feature poup back button click function
       jQuery('#back_second').click(function() {
         jQuery("#dpwap_section_first").hide();
         jQuery('#dpwap_section_second').show();
         jQuery("#dpwap_section_third").fadeOut('1000');
       });
 
    });

//feature poup form submit function
function activateFeaturePLugins(){ 
    document.getElementById('dpwapActivate').submit();
}

