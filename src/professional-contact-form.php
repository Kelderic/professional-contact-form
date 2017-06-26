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

require_once dirname( PCF_FILE ) . '/includes/pcf_enqueuer.php';
require_once dirname( PCF_FILE ) . '/includes/pcf_settings.php';
require_once dirname( PCF_FILE ) . '/includes/pcf_mailer.php';
require_once dirname( PCF_FILE ) . '/includes/pcf_recaptcha.php';
require_once dirname( PCF_FILE ) . '/includes/pcf_form.php';

/***********************************************************************/
/*****************************  INITIATE  ******************************/
/***********************************************************************/

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