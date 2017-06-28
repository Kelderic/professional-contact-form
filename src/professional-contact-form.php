<?php

/**
 * Plugin Name: Professional Contact Form
 * Plugin URI: https://wordpress.org/plugins/professional-contact-form/
 * Version: 1.0.0
 * Author: Andy Mercer
 * Author URI: http://www.andymercer.net
 * Description: This plugin creates a professional looking contact form, that requires very little setup.
 * License: GPL2
 */

/***********************************************************************/
/*************************  DEFINE CONSTANTS  **************************/
/***********************************************************************/

define( 'PCF_FILE', __FILE__ );
define( 'PCF_VERSION', '1.0.0' );


/***********************************************************************/
/**********************  INCLUDE REQUIRED FILES  ***********************/
/***********************************************************************/

require_once dirname( PCF_FILE ) . '/includes/libs/azm_settings_page.php';

require_once dirname( PCF_FILE ) . '/includes/enqueuer.php';
require_once dirname( PCF_FILE ) . '/includes/settings.php';
require_once dirname( PCF_FILE ) . '/includes/mailer.php';
require_once dirname( PCF_FILE ) . '/includes/recaptcha.php';
require_once dirname( PCF_FILE ) . '/includes/form.php';

/***********************************************************************/
/*****************************  INITIATE  ******************************/
/***********************************************************************/

// CONFIRM THAT PHP VERSION IS 5.4 OR HIGHER

if ( version_compare( PHP_VERSION, '5.4' ) >= 0 ) {

	// GENERAL SETUP

	add_filter( 'plugin_action_links', function( $links, $file ) {
		if ( $file == plugin_basename( PCF_FILE ) ) {
			$links = array_merge($links, array( sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=pcf_options_page' ), __( 'Settings' ) ) ) );
		}
		return $links;
	}, 10, 2 );

	// CLASSES SETUP

	$pcf_enqueuer = new PCF_Enqueuer();
	$pcf_settings = new PCF_Settings();
	$pcf_mailer = new PCF_Mailer();
	$pcf_form = new PCF_Form([
		'formID' => 'pcf-1',
	]);

} else {

	add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>".__('Professional Contact Form requires PHP 5.4 to function properly. Please upgrade PHP or deactivate Plugin Name.', 'professional-contact-form') ."</p></div>';" ) );

}