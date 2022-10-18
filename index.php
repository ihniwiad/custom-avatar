<?php

/**
 * Plugin Name: Custom Avatar
 * Description: Replace Gravatar image by configurable user specific image.
 * Version: 1.0
 * Author: ihniwiad
 * Text Domain: custom-avatar
 * Domain Path: /languages
 */



/**
 * Load plugin textdomain.
 */

function ca_load_textdomain() {
	load_plugin_textdomain( 'custom-avatar', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'ca_load_textdomain' );




/**
 * Include jQuery to admin only
 */

add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'jquery' );
} );



// base 64 svg placeholder

$ca_placeholder_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150" width="150" height="150">
<rect fill="#BBBBBB" width="150" height="150"/>
<path fill="#FFFFFF" d="M89,85.1L89,85.1c0-2.7,1.4-5.1,3.7-6.5c10.9-6.4,18-18.5,17.2-32.2c-1-17.4-15-31.7-32.4-32.9
	C57,12.2,40,28.3,40,48.5c0,12.8,6.9,24.1,17.2,30.1c2.3,1.4,3.8,3.8,3.8,6.5v0c0,3.6-2.5,6.6-6,7.3C26.1,98.2,5,116,5,137.2V150
	h140v-12.8c0-21.1-21.1-39-50-44.7C91.5,91.7,89,88.7,89,85.1z"/>
</svg>';
$ca_base64_placeholder = 'data:image/svg+xml;base64,' . base64_encode( $ca_placeholder_svg );



// create / edit
function ca_user_meta_edit_form( WP_User $user ) {
	//enqueue media gallery
    wp_enqueue_media();

    global $ca_base64_placeholder;

	$user_image = get_user_meta( $user->ID, 'user_image', true );
	?>
		<h2><?php esc_html_e( 'Image', 'custom-avatar' ); ?></h2>
		<table class="form-table" data-uifn="form-item">
			<tr>
				<th><?php esc_html_e( 'User image', 'custom-avatar' ); ?></th>
				<td>
					<input name="user_image" type="hidden" value="<?php echo esc_attr( $user_image ); ?>" data-uifn="meta-img-input">
					<div>
						<?php
							if ( ! empty( $user_image ) ) {
			                    $image_attributes = wp_get_attachment_image_src($user_image, 'thumbnail' ); // returns array( $url, $width, $height )
			                    $image_url = $image_attributes[ 0 ];
							}
							else {
								$image_url = $ca_base64_placeholder;
							}
		                    echo '<img class="avatar avatar-96 photo" src="' . $image_url . '" width="96" height="96" alt="User image" data-uifn="meta-img" />';
						?>
					</div>
					<div>
						<button class="button wp-generate-pw hide-if-no-js" data-uifn="browse-btn"><?php esc_html_e( 'Upload / change image', 'custom-avatar' ); ?></button>
						<button class="button hide-if-no-js<?php echo ( empty( $user_image ) ) ? ' hidden' : ''; ?>" data-uifn="img-delete" style="color: #b32d2e; border-color: transparent;"><?php esc_html_e( 'Delete image', 'custom-avatar' ); ?></button>
					</div>
					<p class="description"><?php esc_html_e( 'Shown in blog list. Replacing Gravatar image.', 'custom-avatar' ); ?></p>
				</td>
			</tr>
		</table>
		<script>
if ( window.jQuery ) {
	( function( $ ) {
	    $( document.currentScript ).parent().parent().find( '[data-uifn="form-item"]' ).each( function() {
	        var $metaItemWrapper = $( this );

	        var $imgInput = $metaItemWrapper.find( '[data-uifn="meta-img-input"]' );
	        var $imgDisplay = $metaItemWrapper.find( '[data-uifn="meta-img"]' );
	        var $browseButton = $metaItemWrapper.find( '[data-uifn="browse-btn"]' );
	        var $deleteButton = $metaItemWrapper.find( '[data-uifn="img-delete"]' );

	        var meta_image_frame


	        $.fn.browseImage = function() {
	            // If the frame already exists, re-open it.
	            if ( meta_image_frame ) {
	                meta_image_frame.open();
	                return;
	            }
	            // Sets up the media library frame
	            meta_image_frame = wp.media.frames.file_frame = wp.media( {
	                multiple: false // Set to true to allow multiple files to be selected
	            } );
	            // Runs when an image is selected.
	            meta_image_frame.on( 'select', function() {
	                // Grabs the attachment selection and creates a JSON representation of the model.
	                var media_attachment = meta_image_frame.state().get( 'selection' ).first().toJSON();

	                $imgInput.val( media_attachment.id );
	                $imgDisplay.attr( 'src', media_attachment.sizes.thumbnail.url );
	            	$deleteButton.removeClass( 'hidden' );
	            } );
	            // preselect selected images
	            meta_image_frame.on( 'open', function() {
	                var selection = meta_image_frame.state().get( 'selection' );
	                var idsString = $imgInput.val();

	                if ( idsString.length > 0 ) {
	                    var ids = idsString.split( ',' );

	                    ids.forEach( function( id ) {
	                        attachment = wp.media.attachment( id );
	                        attachment.fetch();
	                        selection.add( attachment ? [ attachment ] : [] );
	                    } );
	                 }
	            } );
	            // Opens the media library frame.
	            meta_image_frame.open();
	        }

	        $browseButton.on( 'click', function( event ) {
	            event.preventDefault();
	            $( this ).browseImage();
	        } );

	        var imgPlaceholder = '<?php echo $ca_base64_placeholder ?>';
	        // remove image button
	        $( '[data-uifn="img-delete"]' ).click( function() {
	            event.preventDefault();
	            $imgInput.val( '' );
	            $imgDisplay.attr( 'src', imgPlaceholder );
	            $deleteButton.addClass( 'hidden' );
	        } );
	    } );
	} )( jQuery );
}
else {
    console.error( 'Missing jQuery plugin.' );
}
	    </script>
	<?php
}
add_action( 'show_user_profile', 'ca_user_meta_edit_form' ); // editing your own profile
add_action( 'edit_user_profile', 'ca_user_meta_edit_form' ); // editing another user
add_action( 'user_new_form', 'ca_user_meta_edit_form' ); // creating a new user

// save
function ca_user_meta_save( $userId ) {
	if ( ! current_user_can( 'edit_user', $userId ) ) {
		return;
	}
	if ( isset( $_REQUEST[ 'user_image' ] ) ) {
		update_user_meta( $userId, 'user_image', $_REQUEST[ 'user_image' ] );
	}
}
add_action( 'personal_options_update', 'ca_user_meta_save' );
add_action( 'edit_user_profile_update', 'ca_user_meta_save' );
add_action( 'user_register', 'ca_user_meta_save' );








// replace user image (avatar)

function ca_replace_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
    global $ca_base64_placeholder;

    $user = false;

    if ( is_numeric( $id_or_email ) ) {

        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } 
    elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } 
    else {
        $user = get_user_by( 'email', $id_or_email );	
    }

    if ( $user && is_object( $user ) ) {

    	$user_id = $user->data->ID;
		$user_image = get_user_meta( $user_id, 'user_image', true );
		$image_attributes = wp_get_attachment_image_src( $user_image, 'thumbnail' ); // returns array( $url, $width, $height )
		$image_url = ( isset( $image_attributes[ 0 ] ) ) ? $image_attributes[ 0 ] : $ca_base64_placeholder;

		$avatar = "<img alt='{$alt}' src='{$image_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
    }

    return $avatar;
}
add_filter( 'get_avatar' , 'ca_replace_avatar' , 1 , 5 );