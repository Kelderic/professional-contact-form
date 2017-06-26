<?php

class PCF_Enqueuer {

	/***********************************************************************/
	/***************************  CONSTRUCTOR  *****************************/
	/***********************************************************************/

	function __construct() {

		if ( is_admin() ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		} else {

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
			add_filter( 'script_loader_tag', array( $this, 'add_async_and_defer_attributes' ), 10, 2 );

		}

	}

	/***********************************************************************/
	/***********************  BACK-END ADMIN SETUP  ************************/
	/***********************************************************************/

	public function enqueue_admin_assets() {

		$screen = get_current_screen();
		$version = PCF_VERSION;

		if ( $screen->id == 'settings_page_pcf_options_page' ) {

			wp_enqueue_script( 'pcf-admin-script', plugin_dir_url( PCF_FILE ) . 'assets/scripts/pcf-admin.js', [], $version, true );
			wp_enqueue_style( 'pcf-admin-stylesheet', plugin_dir_url( PCF_FILE ) . 'assets/stylesheets/pcf-admin.css', [], $version );

		}

	}

	/***********************************************************************/
	/*************************  FRONT-END SETUP  ***************************/
	/***********************************************************************/

	public function enqueue_frontend_assets() {

		$version = PCF_VERSION;

		$options_form = get_option('pcf_options_form') ? get_option('pcf_options_form') : [];

		// ENQUEUE OPTIONAL STYLESHEETS

		if ( $options_form['pcf_css_inputs'] != 'disabled' ) {
			wp_enqueue_style( 'pcf-front-inputs', plugin_dir_url( PCF_FILE ) . 'assets/stylesheets/pcf-front-inputs.css', [], $version );
		}

		if ( $options_form['pcf_css_layout'] != 'disabled' ) {
			wp_enqueue_style( 'pcf-front-layout', plugin_dir_url( PCF_FILE ) . 'assets/stylesheets/pcf-front-layout.css', [], $version );
		}

		// ENQUEUE USER RESPONSE STYLESHEETS

		wp_enqueue_style( 'pcf-front-response-two', plugin_dir_url( PCF_FILE ) . 'assets/stylesheets/pcf-front-response.css', [], $version );

		// ENQUEUE SCRIPTS

		wp_enqueue_script( 'pcf-front-script', plugin_dir_url( PCF_FILE ) . 'assets/scripts/pcf-front.js', [], $version );

		if ( $options_form['pcf_reCaptcha_status'] != 'disabled' ) {
			wp_enqueue_script( 'google-recaptcha-script', 'https://www.google.com/recaptcha/api.js', [], $version );
		}

	}

	public function add_async_and_defer_attributes( $tag, $handle ) {

		if ( 'google-recaptcha-script' !== $handle ) {
			return $tag;
		}

		return str_replace( ' src', ' async defer src', $tag );

	}

}