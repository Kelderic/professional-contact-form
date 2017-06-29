<?php

class PCF_Mailer {

	public $html;
	public $structure;
	public $reCaptcha;
	public $status;

	/***********************************************************************/
	/***************************  CONSTRUCTOR  *****************************/
	/***********************************************************************/

	function __construct() {

		$this->status = [
			'success' => null,
			'msg' => 'Haven\'t send yet.'
		];

		$this->reCaptcha = new PCF_ReCaptcha();

		add_action( 'init', array( $this, 'watch_for_contact_form_submit' ) );

	}

	/***********************************************************************/
	/**************************  PUBLIC INTERFACE  *************************/
	/***********************************************************************/

	public function watch_for_contact_form_submit() {

		// WE MOSTLY USE GET REQUESTS, BUT FOR JS-DISABLED FRONT-END REAL EMAILS, WE
		// DON'T WANT THE USER TO SEE ALL THE QUERY STRINGS, SO WE USE POST.

		if ( array_key_exists( 'action', $_POST ) ) {

			$action = sanitize_key( $_POST['action'] );

		} elseif ( array_key_exists( 'action', $_GET ) ) {

			$action = sanitize_key( $_GET['action'] );

		}

		// NOW THAT WE HAVE AN ACTION, LETS CHECK TO SEE IF IT IS ONE OF OUR ACTIONS,
		// BY CHECKING TO SEE IF IT STARTS WITH pcf_

		if ( isset( $action ) && substr($action, 0, 4) === 'pcf_' ) {

			// DETERMINE EXACTLY WHAT WE ARE DOING

			$ajax = ( substr($action, -5) === '_ajax' ? true : false );

			$testing = ( strpos($action, '_test') !== false ? true : false );

			// SEND EMAIL

			$status = $this->send_email( $testing, $ajax );

			// WE ARE SENDING EITHER A TEST EMAIL OR A REAL EMAIL, VIA AJAX

			if ( $ajax )  {

				// RESPOND TO USER, ENCODED AS JSON

				header( 'Content-type: application/json' );

				echo json_encode([ 
					'success' => $status['success'],
					'msg' => $status['msg']
				]);

				wp_die();

			} else {

				// WE ARE SENDING A TEST EMAIL, NOT VIA AJAX

				if ( $testing )  {

					add_action('admin_notices', function () use ( $status ) {

						if ( $status['success'] == true ) {

							echo '
								<div class="updated notice">

									<p>Your test email has been successfully sent. Please check and make sure that you recieved it.</p>

								</div>
							';

						} else {

							echo '
								<div class="error notice">

									<p>Oops, something went wrong and the email wasn\'t sent. Please check your server\'s email configuration. If you are running on localhost, please make sure that you have an email client installed.</p>

								</div>
							';

						}

					});

				// WE ARE SENDING A REAL EMAIL, NOT VIA AJAX

				} else {

					$this->status = $status;

				}

			}


		}

	}

	private function send_email( $testing, $ajax ) {

		$options_main = get_option('pcf_options_main') ? get_option('pcf_options_main') : [];
		$options_email = get_option('pcf_options_email') ? get_option('pcf_options_email') : [];
		$options_form = get_option('pcf_options_form') ? get_option('pcf_options_form') : [];

		if ( ! $options_main['pcf_mail_to'] ) {
			return [
				'success' => false,
				'msg' => 'Professional Contact Form is incorrectly configured (Error code PCF-1). Please contact website administrator.'
			];
		}

		// GET USER DATA FROM POST OBJECT

		if ( $testing ) {

			$name = 'Person';
			$email = 'person@website.com';
			$phone = '(555) 555-5555';
			$message = 'This is a test message. This is where the message that the user enters in the contact form will appear.';
			$from = 'Person <wordpress@functionaldevices.com>';

		} else {

			$name = array_key_exists( 'pcf_name', $_POST ) ? sanitize_text_field( $_POST['pcf_name'] ) : null;
			$email = array_key_exists( 'pcf_email', $_POST ) ? sanitize_email( $_POST['pcf_email'] ) : null;
			$phone = array_key_exists( 'pcf_phone', $_POST ) ? sanitize_text_field( $_POST['pcf_phone'] ) : null;
			$message = sanitize_textarea_field( $_POST['pcf_message'] );

			// VALIDATE NOT A BOT

			if ( $options_form['pcf_reCaptcha_status'] == 'enabled' && $ajax ) {

				// NO NEED TO SANITIZE THIS BECAUSE IT IS BEING SENT STRAIGHT TO GOOGLE

				if ( $this->reCaptcha->testCode( $_POST['g-recaptcha-response'] ) != true ) {

					return [
						'success' => false,
						'msg' => 'Professional Contact Form is incorrectly configured (Error code PCF-2). Please contact website administrator.'
					];

				}

			}

		}

		// BUILD DATA TO SEND VIA EMAIL

		$to = $options_main['pcf_mail_to'];

		$subject = $options_email['pcf_mail_subject'] ? $options_email['pcf_mail_subject'] : ( get_bloginfo( 'name' ) . ' Contact Form Message' );
		
		$body = self::build_email_body( $subject, $name, $email, $phone, $message );

		if ( $email ) {

			$headers  = 'Content-Type: text/html' . "\n";
			$headers .= 'X-WPCF7-Content-Type: text/html' . "\n";
			$headers .= 'Reply-To: ' . $email . "\n";

		}

		// SEND EMAIL

		$success = wp_mail( $to, $subject, $body, $headers );

		return [
			'success' => $success,
			'msg' => ( $success ? 'Email successfully sent.' : 'Professional Contact Form is incorrectly configured (Error code PCF-3). Please contact website administrator.' )
		];

	}

	/***********************************************************************/
	/***********************  PRIVATE SETUP METHODS  ***********************/
	/***********************************************************************/

	private function build_email_body( $subject, $name, $email, $phone, $message ) {

		$html = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
			<html>
				<head>   
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
					<title>' . $subject . '</title>
					<style type="text/css">
						body,#body_style {margin: 0; padding: 0; background: white; color: #5b656e;}
						a {color: #09c;}
						a img {border: none; text-decoration: none;}
						table, table td {border-collapse: collapse;}
						h1, h2 {color: #313a42 !important; font-weight: normal; line-height: 1.2;}
						h1 {font-size: 24px;}
						h2 {font-size: 18px;}
						p {margin: 0 0 1.6em 0;}
						#outlook a {padding:0;}
						body, table, tr, th, td, span, div, h1, h2 {
							font-family: "Segoe UI", "Roboto", "Calibri", Arial, sans-serif !important;
						}
					</style>
				</head>
				<body yahoofix>
					<span id="body_style" style="display:block">
						<table border="0" cellspacing="0" cellpadding="0" width="100%" summary="" style="background: #556270;">
							<tr>
								<td></td>
								<td width="640" style="color: #ffffff; font-size: 26px; padding: 30px 20px 0 20px; font-weight: bold;">
									Contact Form Message
								</td>
								<td></td>
							</tr>
							<tr>
								<td></td>
								<td width="640" style="color: #ffffff; padding: 20px">
									You have recieved a message via the contact form on your website.
								</td>
								<td></td>
							</tr>
						</table>
						<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background:rgb(230,230,230);">
							<tr>
								<td></td>
								<td width="640" style="padding: 20px">
									<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" summary="">
										<tbody>';
											if ( $name ) { $html .= '
											<tr>
												<td style="padding: 6px 0;">Name:</td>
												<td style="padding: 6px 0; font-weight:bold;">' . $name . '</td>
											</tr>'; }
											if ( $email ) { $html .= '
											<tr>
												<td style="padding: 6px 0;">Email:</td>
												<td style="padding: 6px 0; font-weight:bold;">' . $email . '</td>
											</tr>'; }
											if ( $phone ) { $html .= '
											<tr>
												<td style="padding: 6px 0;">Phone:</td>
												<td style="padding: 6px 0; font-weight:bold;">' . $phone . '</td>
											</tr>'; }
											$html .= '
										</tbody>
									</table>
								</td>
								<td></td>
							</tr>
						</table>
						<table style="background-color: #ffffff;" border="0" cellpadding="0" cellspacing="0" width="100%" summary="">
							<tr>
								<td></td>
								<td width="640" style=" padding:20px 20px 300px 20px">
									<p style="font-size:1.3em;" style="font-family: "Segoe UI", "Roboto", "Calibri", Arial, sans-serif !important">' . $message . '</p>
								</td>
								<td></td>
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="0" width="100%"  style="background: #556270;">
							<tr><td height="20">&nbsp;</td></tr>
							<tr><td height="20">&nbsp;</td></tr>
						</table>
					</span>
				</body>
			</html>';

		return $html;

	}


}