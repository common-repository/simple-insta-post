jQuery(document).ready(function ($) {

//	var data = {
//		'action': 'my_action',
//		'whatever': ajax_object.we_value      // We pass php values differently!
//	};
//	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
//	jQuery.post(ajax_object.ajax_url, data, function(response) {
//		alert('Got this from the server: ' + response);
//	});


});

function createPost(id,type) {

var nounce = jQuery('#ajax-nounce-sip').val();
    var data = {
        action: 'sip_create_post',
        link: jQuery('#link-' + id).val(),
        title: jQuery('#title-' + id).val(),
        tags: jQuery('#tags-' + id).val(),
        image: jQuery('#image-' + id).val(),
        video: jQuery('#video-' + id).val(),
        location: jQuery('#location-' + id).val(),
        type: type,
        security:nounce
    };
    jQuery.post(ajaxurl, data, function (response) {
        var res = response.split("||", 1);
        var cleanUrl = res[0].replace("amp;", "");
        if (confirm('The post has been Created! do you want to edit right now?')) {
            jQuery(location).attr('href', cleanUrl);
        }


    });

}

function getMorePost() {
    var nounce = jQuery('#ajax-nounce-sip').val();
    var url = jQuery('#nextUrl').val();
    var pageNumber = jQuery('#pageNumber').val();
    jQuery('#nextUrl').remove();
    jQuery('#sip_load_more').remove();
    var data = {
        action: 'sip_more_post',
        url: url,
        pageNumber: pageNumber,
        security:nounce
    };
    jQuery.post(ajaxurl, data, function (response) {
        jQuery('#sip-image-results').append(response);
        jQuery('#pageNumber').val(parseInt(pageNumber) + 1);
//        alert(response);


    });

}