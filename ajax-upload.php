<?php
/**
 * Plugin Name: WP Ajax uploader
 * Plugin URI: http://tareq.weDevs.com
 * Description: Ajax fileupload from WordPress frontend
 * Author: Tareq Hasan
 * Author URI: http://tareq.weDevs.com
 */

/**
 * Main plugin for Ajax Upload
 *
 * @author Tareq Hasan
 */
class TP_Ajax_Uploader {

    private $plugin_url;

    function __construct() {
        $this->plugin_url = plugins_url( '', __FILE__ );
        $this->actions();
    }

    function actions() {
        add_action( 'wp_enqueue_scripts', array($this, 'scripts') );
        add_shortcode( 'ajax-upload', array($this, 'upload_form') );
        add_action( 'wp_head', array($this, 'js_scripts') );
        add_action( 'wp_ajax_wp_uploadify', array($this, 'upload_handler') );
        add_action( 'wp_ajax_nopriv_wp_uploadify', array($this, 'upload_handler') );
    }

    function scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'swfobject' );
        wp_enqueue_script( 'uploadify', $this->plugin_url . '/js/jquery.uploadify.v2.1.4.min.js' );

        $logged_in_cookie = ( isset( $_COOKIE[LOGGED_IN_COOKIE] ) ) ? $_COOKIE[LOGGED_IN_COOKIE] : '';
        wp_localize_script( 'uploadify', 'wpUploadify', array(
            'logged_in_cookie' => $logged_in_cookie,
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'cancel_img' => $this->plugin_url . '/images/cancel.png'
        ) );

        wp_enqueue_style( 'uploadify', $this->plugin_url . '/css/uploadify.css' );
    }

    function upload_form() {
        return '<input id="tp-ajax-upload" name="file_upload" type="file" /><div id="tp-ajax-upload-result"></div>';
    }

    function upload_handler() {
        $user_id = wp_validate_auth_cookie( $_POST['user'], "logged_in" );

        //set user to admin, if s/he is not logged in
        $user_id = ( $user_id == 0 ) ? 1 : $user_id;

        $upload = wp_upload_bits( $_FILES["Filedata"]["name"], null, file_get_contents( $_FILES["Filedata"]["tmp_name"] ) );
        $new_thumbnail = $upload['url'];

        $filename = $upload['file'];

        $wp_filetype = wp_check_filetype( basename( $filename ), null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => basename( $filename ),
            'post_content' => '',
            'post_author' => $user_id,
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $filename );

        $response = array(
            'field' => '<input type="hidden" name="attachments[]" value="dflk" />',
            'image' => includes_url( '/images/crystal/spreadsheet.png' ),
            'wrap' => '<div class="attachment"></div>'
        );
        print_r($wp_filetype);
        exit;
    }

    function js_scripts() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#tp-ajax-upload').uploadify({
                    'uploader'  : '<?php echo $this->plugin_url; ?>/js/uploadify.swf',
                    'script'    : wpUploadify.ajaxurl + '?action=wp_uploadify',
                    'cancelImg' : wpUploadify.cancel_img,
                    'auto'      : true,
                    'removeCompleted': true,
                    'scriptData'  : {'user': wpUploadify.logged_in_cookie},
                    'multi': true,
                    'onComplete': function(event, ID, fileObj, response, data) {
                        //var uploadify = $.parseJSON(response);
                        //$('#tp-ajax-upload-result').append('<div class="uploadifyQueueItem"><div class="cancel"><a href="#" class="closeWPupload"><img border="0" src="'+wpUploadify.cancel+'"></a></div><img src="'+uploadify.image+'"></div>');
                        alert(response);
                        console.log(response);
                    }
                });

                $('a.closeWPupload').live('click',function(){
                    $(this).parents('.uploadifyQueueItem').remove();
                    console.log('clicked');
                    return false;
                });
            });
        </script>
        <?php
    }

}

$ajax_upload = new TP_Ajax_Uploader();
