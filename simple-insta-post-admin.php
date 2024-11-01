
<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 

if (filter_var($_POST['sip_hidden'], FILTER_SANITIZE_STRING) == 'Y') {
    //Form data sent
    $user = sanitize_text_field($_POST['sip_dbuser']);
    $dbuser = str_replace('', '', $user);

    $arrayResponse = sip_getUserInstaId($dbuser);

    $instaUserId = $arrayResponse['id'];
    $imgProfile = $arrayResponse['url'];
    $instaName = '@'.$arrayResponse['name'];
    update_option('sip_dbuser', $dbuser);
    update_option('sip_instaUserId', $instaUserId);
    update_option('sip_instaName', $instaName);
    update_option('sip_instaProfile', $imgProfile);

    $response = sip_getUserRecentInstaPost($instaUserId);
//         
    ?>
    <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
    <?php
} else {
    //Normal page display
    $dbuser = get_option('sip_dbuser');
    $instaUserId = get_option('sip_instaUserId');
    $imgProfile = get_option('sip_instaProfile');
    $instaName = get_option('sip_instaName');
    $response = sip_getUserRecentInstaPost($instaUserId);
}

if ((null == $imgProfile) || ('' == $imgProfile)) {
    $imgProfile = plugins_url('images/default-user.png', __FILE__);
}
ob_start();
echo '<input type="hidden" name="ajax-nounce-sip" id="ajax-nounce-sip" value="' . wp_create_nonce(SIP_MY_NOUNCE) . '" />';
?>

<!--<link href="<?php // echo plugins_url('css/style.css', __FILE__)               ?>" rel="stylesheet" type="text/css"/>-->
<!--<script src="<?php // echo plugins_url('js/instaPost.js', __FILE__)               ?>"></script>-->
<input type="hidden" id="sip-url" value="<?php echo SIP_URL; ?>">
<div class="wrap sip-col">
    <div class='sip-col-left'>
        <?php echo "<h2>" . __('Simple Insta Post Settings Options', 'sip_trdom') . "</h2>"; ?>


        <?php
        if (('' != $dbuser) || (null != $dbuser)) {
            $class = 'item-collapse';
            $classChange = '';
        } else {
            $class = '';
            $classChange = 'item-collapse';
        }
        ?>
        <div id='token-div' class="<?php echo $class ?>">
            <a class="instagram-token" target="_blank" href="https://instagram.com/oauth/authorize?client_id=<?php echo SIP_CLIENT_ID; ?>&redirect_uri=<?php echo SIP_REDIRECT_URI; ?>&response_type=token" class="login_header_desktop bouton bt-instagram">
                <img src=" <?php echo plugins_url('images/instagram-logo.png', __FILE__) ?>" alt="instagram-logo">
                <span> SIGN IN WITH INSTAGRAM</span>
            </a>
            
            <form name="sip_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                <input type="hidden" name="sip_hidden" value="Y">
                <input type="hidden" name="instaUserId" value="<?php echo sanitize_text_field($instaUserId); ?>">
                <p><?php _e("User Token: "); ?></p><input class='token-input' type="text" name="sip_dbuser" value="<?php echo sanitize_text_field($dbuser); ?>">



                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Update Options', 'sip_trdom') ?>" />
                </p>
            </form>
        </div>

        <div class="change-token <?php echo $classChange ?>">
            <a  onclick="changeToken()">Change token</a>
        </div>
        <h1><?php echo $instaName; ?></h1>
        <img class="profile-img" alt="profile-pic" src="<?php echo esc_url($imgProfile); ?>">
    </div>
    <div class='sip-col-right'>
        <a href="https://www.simplesolutionsfs.com/"><img src='<?php echo esc_url(plugins_url('images/simpleLogo.png', __FILE__)) ?>'></a>
        <div class='descrip'>
            <p>We are a development company. Feel free to contact us for any support or customization required. </p>
            <p><strong>(305) 433-3375</strong></p>
            <p>info@simplesolutionsfs.com</p>
            <a href="https://www.simplesolutionsfs.com/">www.simplesolutionsfs.com</a>
        </div>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="MSPASN2K4VSG6">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
    </div>
</div>
<div class="separator-div" >
    <h2>Recent Post</h2>
</div>
<input type="hidden" name="pageNumber" id="pageNumber" value="1">
<div id='sip-image-results' clas="sip-image-results">

    <?php echo $response; ?>
</div>


