<?php
/**
 * Plugin Name: Contact Form 7 - Success Page Redirects
 * Description: An add-on for Contact Form 7 that provides a straightforward method to redirect visitors to success pages or thank you pages.
 * Version: 1.1.2
 * Author: Ryan Nevius
 * Author URI: http://www.ryannevius.com
 * License: GPLv3
 */

/**
 * Verify CF7 dependencies.
 */
function cf7_success_page_admin_notice() {
	// Verify that CF7 is active and updated to the required version (currently 3.9.0)
	if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
		$wpcf7_path = plugin_dir_path( dirname(__FILE__) ) . 'contact-form-7/wp-contact-form-7.php';
		$wpcf7_plugin_data = get_plugin_data( $wpcf7_path, false, false);
		$wpcf7_version = (int)preg_replace('/[.]/', '', $wpcf7_plugin_data['Version']);
		// If CF7 version is < 3.9.0
		if ( $wpcf7_version < 390 ) {
			echo '<div class="error"><p><strong>Warning: </strong>Contact Form 7 - Success Page Redirects requires that you have the latest version of Contact Form 7 installed. Please upgrade now.</p></div>';
		}
	}
	// If it's not installed and activated, throw an error
    else {
	    echo '<div class="error"><p>Contact Form 7 is not activated. The Contact Form 7 Plugin must be installed and activated before you can use Success Page Redirects.</p></div>';
	}
}
add_action( 'admin_notices', 'cf7_success_page_admin_notice' );


/**
 * Disable Contact Form 7 JavaScript completely
 */
add_filter( 'wpcf7_load_js', '__return_false' );


/**
 * Adds a box to the main column on the form edit page.
 */
// Register the meta boxes
function cf7_success_page_settings() {
	add_meta_box( 'cf7-redirect-settings', 'Success Page Redirect', 'cf7_success_page_metaboxes', '', 'form', 'low');
}
add_action( 'wpcf7_add_meta_boxes', 'cf7_success_page_settings' );

// Create the meta boxes
function cf7_success_page_metaboxes( $post ) {
	wp_nonce_field( 'cf7_success_page_metaboxes', 'cf7_success_page_metaboxes_nonce' );
	$cf7_success_page = get_post_meta( $post->id(), '_cf7_success_page_key', true );

	// The meta box content
	echo '<label for="cf7-redirect-page-id"><strong>Redirect to: </strong></label><br> ';
	$dropdown_options = array (
			'name' => 'cf7-redirect-page-id', 
			'show_option_none' => '--', 
			'option_none_value' => '0',
			'selected' => $cf7_success_page
		);
	wp_dropdown_pages( $dropdown_options );
}

// Store Success Page Info
function cf7_success_page_save_contact_form( $cf7 ) {
	$cf7_id = $cf7->id;

	if ( !isset( $_POST ) || empty( $_POST ) || !isset( $_POST['cf7-redirect-page-id'] ) ) {
		return;
	}
	else {
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['cf7_success_page_metaboxes_nonce'], 'cf7_success_page_metaboxes' ) ) {
			return;
		}
		// Update the stored value
        update_post_meta( $cf7_id, '_cf7_success_page_key', $_POST['cf7-redirect-page-id'] );
    }
}
add_action( 'wpcf7_save_contact_form', 'cf7_success_page_save_contact_form' );


/**
 * Redirect the user, after a successful email is sent
 */
function cf7_success_page_form_submitted( $cf7 ) {
	$cf7_id = $cf7->id;

	// Send us to a success page, if there is one
	$success_page = get_post_meta( $cf7_id, '_cf7_success_page_key', true );
	if ( !empty($success_page) ) {
		wp_redirect( get_permalink( $success_page ) );
	    die();
	}
}
add_action( 'wpcf7_mail_sent', 'cf7_success_page_form_submitted' );

?>