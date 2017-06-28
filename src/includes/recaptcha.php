<?php

class PCF_ReCaptcha {

	public $secret_key;
	
	public function __construct(){

		$options_form = get_option('pcf_options_form') ? get_option('pcf_options_form') : [];

		$this->secret_key = $options_form['pcf_reCaptcha_secret_key'];

	}

	public function testCode( $code ) {

		if ( empty( $code ) ) {

			return false;

		}

		$responseFromGoogle = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $this->secret_key . '&remoteip=' . $this->getIPAddress() . '&response=' . $code);

		$responseFromGoogle = json_decode( $responseFromGoogle, true);

		return $responseFromGoogle['success'];

	}

	private function getIPAddress() {

		if ( ! empty($_SERVER['HTTP_CLIENT_IP']) ) {

			return $_SERVER['HTTP_CLIENT_IP'];

		} elseif ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) { 

			return $_SERVER['HTTP_X_FORWARDED_FOR'];

		} else {

			return $_SERVER['REMOTE_ADDR'];

		}

	}

}

