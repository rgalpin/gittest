<?php
/**
 * Plugin Name:  Cooper Hewitt Enforce Password Policy
 * Plugin URI:   
 * Description:  Forces users to include a symbol and at least 12 characters in their password.
 * Version:      0.1
 * Author:       
 * Author URI:   
 * License:      
 * License URI:  
 * Text Domain:  
 * Domain Path:  
 *
 * @link         
 * @package      WordPress
 * @author       
 * @version      0.1
 */

global $wp_version;

/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

/**
 * Initialize constants.
 */

// Our plugin.
define( 'cooperhewitt_EPP_PLUGIN_BASE', __FILE__ );

// Allow changing the version number in only one place (the header above).
$plugin_data = get_file_data( cooperhewitt_EPP_PLUGIN_BASE, array( 'Version' => 'Version' ) );
define( 'cooperhewitt_EPP_PLUGIN_VERSION', $plugin_data['Version'] );

// Initialize other stuff.
add_action( 'plugins_loaded', 'cooperhewitt_epp_init' );
function cooperhewitt_epp_init() {

	// Text domain for translation.
	// load_plugin_textdomain( 'cooperhewitt-force-strong-passwords', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Hooks.
	add_action( 'user_profile_update_errors', 'cooperhewitt_epp_validate_profile_update', 0, 3 );
	add_action( 'validate_password_reset', 'cooperhewitt_epp_validate_strong_password', 10, 2 );
	add_action( 'resetpass_form', 'cooperhewitt_epp_validate_resetpass_form', 10 );
}


/**
 * Check user profile update and throw an error if the password isn't strong.
 */
function cooperhewitt_epp_validate_profile_update( $errors, $update, $user_data ) {
	return cooperhewitt_epp_validate_strong_password( $errors, $user_data );
}

/**
 * Check password reset form and throw an error if the password isn't strong.
 */
function cooperhewitt_epp_validate_resetpass_form( $user_data ) {
	return cooperhewitt_epp_validate_strong_password( false, $user_data );
}


/**
 * Functionality used by both user profile and reset password validation.
 */
function cooperhewitt_epp_validate_strong_password( $errors, $user_data ) {
	$password_ok = true;
	$password    = ( isset( $_POST['pass1'] ) && trim( $_POST['pass1'] ) ) ? sanitize_text_field( $_POST['pass1'] ) : false;

	// If the password does not have symbols, it's not ok
	if ( ! cooperhewitt_pw_has_symbols( $password, $username ) ) {
		$password_ok = false;
	}
	// If the password does not have 12 characters, it's not ok
	if ( strlen( $password ) < 12 ) {
		$password_ok = false;
	}

	// Error?
	if ( ! $password_ok && is_wp_error( $errors ) ) { // Is this a WP error object?
		$errors->add( 'pass', apply_filters( 'cooperhewitt_epp_error_message', __( '<strong>ERROR</strong>: Please include a symbol and at least 12 characters in the password.', 'cooperhewitt-enforce-password-policy' ) ) );
	}
	return $errors;
}


/**
 * Check for password for the presence of symbols
 *
 * @added by InfoStructures for the Smithsonian
 * @param   string $i   The password.
 * @param   string $f   The user's username.
 */
function cooperhewitt_pw_has_symbols( $i, $f ) {
	$has_symbols = false;
	if ( preg_match( '/[^a-zA-Z0-9]/', $i ) ) {
		$has_symbols = true;
	}
	return $has_symbols;
}
