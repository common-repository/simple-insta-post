<?php

/*
  Plugin Name: Simple Insta Post
  Plugin URI: http://www.simplesolutionsfs.com
  Description: Plugin for Publish a Post with your instagram account
  Author: Simple Solutions
  Version: 1.2
  Author URI: http://www.simplesolutionsfs.com
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 

define('SIP_URL', plugin_dir_url(__FILE__));
define('SIP_PATH', plugin_dir_path(__FILE__));
define('SIP_VERSION', '1.2');
define('SIP_INSTA_CODE', '3bf218037c83478ba935344634eff88d');

define('SIP_INSTA_TOKEN_DEV', '2199826204.bbf8ab8.1e89eea0112249018a473f9d9033e90e');
define('SIP_POST_PERPAGE', 20);
define('SIP_MY_NOUNCE', 'SIP_NOUNCE');

define('SIP_CLIENT_SECRET', ' 9f4a38a463804fe59f8d7cbc24b2bd6e');
define('SIP_CLIENT_ID', '772d4407c5a344deba15c2e7433c17cf');
define('SIP_REDIRECT_URI', 'http://wordpress.plugins.simplesolutionsfs.com/simple-insta-post/index.php');


//*************************************************//
//************      INIT     *****************//
//*************************************************//

add_action('admin_init', 'sip_admin_init');

function sip_admin_init() {
    wp_register_style('sip-style', SIP_URL . 'assets/css/style.css', array(), SIP_VERSION);
    wp_register_script('ajax-script', SIP_URL . 'assets/js/instaPost.js', array(), SIP_VERSION);
}

//*************************************************//
//************      ADMIN PAGE     *****************//
//*************************************************//

function sip_admin_tab_page() {
    include('simple-insta-post-admin.php');
}

//*************************************************//
//************      ADMIN TAB     *****************//
//*************************************************//



function sip_admin_tab() {
    global $page;
    $page = add_menu_page("Simple Insta Post", "Simple Insta Post", 1, "simple-insta-post-admin", "sip_admin_tab_page", plugins_url('images/logo.png', __FILE__));
    add_action('admin_print_styles-' . $page, 'sip_admin_scripts');
    add_action('admin_enqueue_scripts', 'sip_admin_scripts');
}

add_action('admin_menu', 'sip_admin_tab');

//*************************************************//
//************    CSS AND JS      *****************//
//*************************************************//

function sip_admin_scripts() {
    /*
     * It will be called only on your plugin admin page, enqueue our stylesheet here
     */
    wp_enqueue_style('sip-style');
    wp_enqueue_script('ajax-script');
}

//*************************************************//
//************      API      *****************//
//*************************************************//

/**
 *  This method gets the id of the username
 * @param String $param is the username
 * @return Array with the Instagram-id and Url of the profile picture
 */
function sip_getUserInstaId($param) {

//    $ch = curl_init("https://api.instagram.com/v1/users/search?q=" . $param . "&access_token=" . SIP_INSTA_TOKEN_DEV . "&count=1");
    $ch = curl_init("https://api.instagram.com/v1/users/self/?access_token=" . $param);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //for more secure
    //http://flwebsites.biz/posts/how-fix-curl-error-60-ssl-issue
    //curl_setopt($process, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
    //curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);

    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    if ($curl_errno > 0) {
        echo "cURL Error ($curl_errno): $curl_error\n";
    }
    $json = json_decode($response);


    $arrayResponse = Array(
        'id' => $json->data->id,
        'url' => $json->data->profile_picture,
        'name' => $json->data->username
    );

    return $arrayResponse;
}

/**
 * This metho return all the recent posts 
 * @param type $id The instagram id
 * @param type $param custom parammeter
 * @return Array with the images
 */
function sip_getUserRecentInstaPost($id, $param = null) {


    $arrayResponse = '';
    if ($id != null) {
        $token = get_option('sip_dbuser');
        $ch = curl_init("https://api.instagram.com/v1/users/" . $id . "/media/recent/?access_token=" . $token);
//        $ch = curl_init("https://api.instagram.com/v1/users/self/media/recent/?access_token=" . $token);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //for more secure
        //http://flwebsites.biz/posts/how-fix-curl-error-60-ssl-issue
        //curl_setopt($process, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        //curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);

        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        if ($curl_errno > 0) {
            echo "cURL Error ($curl_errno): $curl_error\n";
        }
        $json = json_decode($response);
        $cont = 0;
        $arrayResponse.= '<input type="hidden" id="nextUrl" value="' . $json->pagination->next_url . '">';
        foreach ($json->data as $item) {
//            var_dump($item->users_in_photo);
            if ($item->type == 'video') {
//                var_dump($item);

                $media_item = '<video width="640" controls><source src="' . esc_url($item->videos->standard_resolution->url) . '" type="video/mp4"></video>';
                $image = $item->images->standard_resolution->url;
                $video = $item->videos->standard_resolution->url;
                $label_class = 'video-color';
            } else {
                $media_item = '<img alt="" src="' . esc_url($item->images->low_resolution->url) . '">';
                $image = $item->images->standard_resolution->url;
                $video = '';
                $label_class = 'post-color';
            }
            $cont++;
            $tags = '';
            foreach ($item->tags as $tag) {
                $tags.= $tag . ',';
            }
            $location = $item->location->name;
            $title = $item->caption->text;

            $link = $item->link;

            $arrayResponse.='<div id="item-results" class="sip-item-result">
                                    <input type="hidden" id="location-' . $cont . '" value="' . $location . '">
                                    <input type="hidden" id="title-' . $cont . '" value="' . $title . '">
                                    <input type="hidden" id="image-' . $cont . '" value="' . $image . '">
                                                 <input type="hidden" id="video-' . $cont . '" value="' . $video . '">
                                    <input type="hidden" id="link-' . $cont . '" value="' . $link . '">
                                    <input type="hidden" id="tags-' . $cont . '" value="' . $tags . '">
                                    ' . $media_item . '
                                     <span class="label '.$label_class.'">' . $item->type . '</span>    
                                  <input type="button" class="simple-btn" value="Post" onclick="createPost(' . $cont . ',' ."'" .  $item->type ."'" . ')">                           
                            </div>';
            //var_dump($item->images->low_resolution->url);
        }
        $arrayResponse .='<div class="load-btn"><input id="sip_load_more" class="simple-btn" type="button" value="Load More" onclick="getMorePost()"></div>';
    }
    return $arrayResponse;
}

//*************************************************//
//************      AJAX       ****************//
//*************************************************//

add_action('admin_enqueue_scripts', 'sip_load_scripts');

function sip_load_scripts($hook) {
    global $page;

    if ($page != $hook) {
        // Only applies to dashboard panel
        return;
    }

    wp_enqueue_script('sip-ajax', SIP_URL . 'assets/js/simple-ajax.js', array('jquery'), SIP_VERSION);
    // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
//    wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'we_value' => 1234));
}

add_action('wp_ajax_sip_create_post', 'sip_create_post');

function sip_create_post() {



    if (!wp_verify_nonce($_POST['security'], SIP_MY_NOUNCE))
        die("Security check");
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $image = filter_var($_POST['image'], FILTER_SANITIZE_STRING);
    $link = filter_var($_POST['link'], FILTER_SANITIZE_STRING);
    $tags = filter_var($_POST['tags'], FILTER_SANITIZE_STRING);
    $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
    $video = filter_var($_POST['video'], FILTER_SANITIZE_STRING);
    $postTitle = 'My Instagram Post ' . date('d F Y');

    $media = media_sideload_image($image, $post_id, $postTitle);
    if ($type == 'video') {
        $content_media = '<video width="640" height="640" controls><source src="' . $video . '" type="video/mp4"></video>';
//        $content_media = '<iframe src="' . $video . '"></iframe>';
        $post_type = 'post';
    } else {
        $content_media = $media;
        $post_type = 'post';
    }



//$image_src = preg_replace("/.*(?<=src=["'])([^"']*)(?=["']).*/", '$1', $media);
//    $content = $media . '</br>'
//            . 'Location: ' . $location . '<br> '
//            . $title . '<br> 
//            <a href="' . $link . '">See the instagram post: ' . $link . '</a><br> '
//    ;
    $filename = sip_get_string_between($media, "src='", "'");


    $attach_id = sip_get_attachment_id_from_src($filename);

    $content = $content_media;

    if ($location != '') {
        $content.= '<p id="sip-location">' . $location . '</p></br> ';
    }

    $content.= '<p id="sip-title">' . $title . '</p><br> ';
//            <a href="' . $link . '">See the instagram post: ' . $link . '</a><br> ';


    $current_user = wp_get_current_user();
    // Create post object
    $my_post = array(
        'post_type' => $post_type,
        'post_title' => $postTitle,
        'post_content' => $content,
        'post_status' => 'draft',
        'post_author' => $current_user->ID,
        'tags_input' => $tags
    );
////
//// Insert the post into the database
    $post_id = wp_insert_post($my_post);




    $wp_filetype = wp_check_filetype($filename);
    $attachment = array(
        'guid' => $filename,
        'post_mime_type' => $wp_filetype['type']
    );




    $postUrl = site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit';
    $complete_url = wp_nonce_url($postUrl, 'trash-post_' . $post_id, SIP_MY_NOUNCE);
    echo $complete_url . '||';


//    $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);
    add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
//    wp_delete_attachment($attach_id);


    wp_die();
}

add_action('wp_ajax_sip_more_post', 'sip_more_post');

function sip_more_post() {

    if (!wp_verify_nonce($_POST['security'], SIP_MY_NOUNCE))
        die("Security check");
    $url = filter_var($_POST['url'], FILTER_SANITIZE_STRING);
    $pageNumber = filter_var($_POST['pageNumber'], FILTER_VALIDATE_INT);

    $arrayResponse = '';
    if ($url != null) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);
        if ($curl_errno > 0) {
            echo "cURL Error ($curl_errno): $curl_error\n";
        }
        $json = json_decode($response);
        $cont = $pageNumber * SIP_POST_PERPAGE;

        $arrayResponse.= '<input type="hidden" id="nextUrl" value="' . $json->pagination->next_url . '">';
        foreach ($json->data as $item) {
//            var_dump($item);
            if ($item->type == 'video') {


                $media_item = '<video width="640" controls><source src="' . esc_url($item->videos->standard_resolution->url) . '" type="video/mp4"></video>';
                $image = $item->images->standard_resolution->url;
                $video = $item->videos->standard_resolution->url;
                 $label_class = 'video-color';
            } else {
                $media_item = '<img alt="" src="' . esc_url($item->images->low_resolution->url) . '">';
                $image = $item->images->standard_resolution->url;
                $video = '';
                 $label_class = 'post-color';
            }
            $cont++;
            $tags = '';
            foreach ($item->tags as $tag) {
                $tags.= $tag . ',';
            }
            $location = $item->location->name;
            $title = $item->caption->text;
//            $image = $item->images->standard_resolution->url;
            $link = $item->link;

            $arrayResponse.='<div id="item-results" class="sip-item-result">
                                    <input type="hidden" id="location-' . $cont . '" value="' . $location . '">
                                    <input type="hidden" id="title-' . $cont . '" value="' . $title . '">
                                    <input type="hidden" id="image-' . $cont . '" value="' . $image . '">
                                        <input type="hidden" id="video-' . $cont . '" value="' . $video . '">
                                    <input type="hidden" id="link-' . $cont . '" value="' . $link . '">
                                    <input type="hidden" id="tags-' . $cont . '" value="' . $tags . '">
                                    ' . $media_item . '
                                    <span class="label '.$label_class.'">' . $item->type . '</span>    
                                  <input type="button" value="Post" class="simple-btn" onclick="createPost(' . $cont . ',' ."'" . $item->type ."'" . ')">
                               
                            </div>';
            //var_dump($item->images->low_resolution->url);
        }
        $arrayResponse .='<div class="load-btn"><input id="sip_load_more" class="simple-btn" type="button" value="Load More" onclick="getMorePost()"></div>';
    }


    echo $arrayResponse;
    wp_die();
}

//*************************************************//
//************      OTHER       ****************//
//*************************************************//


function sip_get_string_between($string, $start, $end) {
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
        return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function sip_get_attachment_id_from_src($image_src) {
    global $wpdb;
    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
    $id = $wpdb->get_var($query);
    return $id;
}

?>
