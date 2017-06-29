<?php

class PCF_Form {

	public $html;
	public $structure;
	public $reCaptcha;
	public $formID;

	/***********************************************************************/
	/***************************  CONSTRUCTOR  *****************************/
	/***********************************************************************/

	function __construct( $params ) {

		// HANDLE USER DEFINED PARAMETERS

		$this->formID = array_key_exists('formID', $params) ? $params['formID'] : '';

		// RETRIEVE OPTIONS FROM DB

		self::retrieve_options();

		// BUILD FORM HTML

		self::build_form();

		// SET UP ACTION TO CHANGE THE STATUS AFTER THE STATUS IS AVAILABLE,
		// FOR NO-JS SITUATIONS

		add_action( 'init', array( $this, 'set_nojs_status' ) );

		// ADD SHORTCODE TO ALLOW USER TO DISPLAY CONTACT FORM

		add_shortcode( 'pcf-display-form', array( $this, 'display_contact_form' ) );

	}

	/***********************************************************************/
	/**************************  PUBLIC INTERFACE  *************************/
	/***********************************************************************/

	public function display_contact_form() {

		return apply_filters( 'pcf_display_form', $this->html );

	}

	/***********************************************************************/
	/***********************  PRIVATE SETUP METHODS  ***********************/
	/***********************************************************************/

	private function retrieve_options() {

		$options_form = get_option('pcf_options_form') ? get_option('pcf_options_form') : [];

		$this->structure = [
			[
				[
					'name' => 'pcf_name',
					'type' => 'text',
					'label' => 'Name',
					'status' => $options_form['pcf_field_name_status'] ? $options_form['pcf_field_name_status'] : 'enabled',
					'required' => $options_form['pcf_field_name_required'] ? $options_form['pcf_field_name_required'] : 'yes',
					'placeholder' => $options_form['pcf_field_name_placeholder'] ? $options_form['pcf_field_name_placeholder'] : 'What does your mom call you?'
				],
				[
					'name' => 'pcf_email',
					'type' => 'email',
					'label' => 'Email',
					'status' => $options_form['pcf_field_email_status'] ? $options_form['pcf_field_email_status'] : 'enabled',
					'required' => $options_form['pcf_field_email_required'] ? $options_form['pcf_field_email_required'] : 'yes',
					'placeholder' => $options_form['pcf_field_email_placeholder'] ? $options_form['pcf_field_email_placeholder'] : 'How can we email you?'
				],
				[
					'name' => 'pcf_phone',
					'type' => 'tel',
					'label' => 'Phone Number',
					'status' => $options_form['pcf_field_phone_status'] ? $options_form['pcf_field_phone_status'] : 'disabled',
					'required' => $options_form['pcf_field_phone_required'] ? $options_form['pcf_field_phone_required'] : 'no',
					'placeholder' => $options_form['pcf_field_phone_placeholder'] ? $options_form['pcf_field_phone_placeholder'] : 'What\'re your digits?'
				]
			],
			[
				[
					'name' => 'pcf_message',
					'type' => 'textarea',
					'label' => 'Message',
					'status' => 'enabled',
					'required' => true,
					'placeholder' => 'What\'s on your mind?'
				]
			]
		];

		$this->reCaptcha = [
			'status' => $options_form['pcf_reCaptcha_status'] ? $options_form['pcf_reCaptcha_status'] : 'disabled',
			'site_key' => $options_form['pcf_reCaptcha_site_key'] ? $options_form['pcf_reCaptcha_site_key'] : '',
		];

	}

	private function build_form() {

		$this->html .= '

			<form class="pcf pcf_default" action="" ajax-action="' . admin_url( 'admin-ajax.php' ) . '" method="post" id="' . $this->formID . '">
				<div class="pcf_response_wrap">
					<div class="pcf_overlay pcf_reCaptcha"></div>
					<div class="pcf_overlay pcf_submitting"></div>
					<div class="pcf_overlay pcf_success"></div>
					<div class="pcf_overlay pcf_error"></div>
					<div class="pcf_response pcf_reCaptcha" style="color:white;">
						<pcf_icon class="pcf_reCaptcha" style="background: white;border-radius:50%;">' . file_get_contents( plugin_dir_path( PCF_FILE ) . 'assets/icons/reCaptcha.svg' ) . '</pcf_icon>
						Verifying that you are human...
					</div>
					<div class="pcf_response pcf_submitting">
						<pcf_icon class="pcf_loading">' . file_get_contents( plugin_dir_path( PCF_FILE ) . 'assets/icons/loading.svg' ) . '</pcf_icon>
						Sending message...
					</div>
					<div class="pcf_response pcf_success" style="color:white;">
						<pcf_icon class="pcf_success">' . file_get_contents( plugin_dir_path( PCF_FILE ) . 'assets/icons/success.svg' ) . '</pcf_icon>
						Message sent!
					</div>
					<div class="pcf_response pcf_error" style="color:white;">
						<pcf_icon class="pcf_error">' . file_get_contents( plugin_dir_path( PCF_FILE ) . 'assets/icons/error.svg' ) . '</pcf_icon>
						Error
					</div>
				</div>
				<input type="hidden" name="action" value="pcf_send_email" />';

			foreach ( $this->structure as $row ) {

				$this->html .= '<div class="row">';

				foreach ( $row as $field ) {

					$this->html .= self::build_field( $field );

				}

				$this->html .= '</div>';

			}

			if ( $this->reCaptcha['status'] == 'enabled' ) {

				$this->html .= '

				<div class="row">
					<field style="margin-left:4px" id="recaptcha" class="g-recaptcha" data-badge="inline" data-sitekey="' . esc_attr( $this->reCaptcha['site_key'] ) . '" data-size="invisible" data-callback="submitForm_' . str_replace( '-', '', $this->formID ) . '"></field>
				</div>

				<script>
					function submitForm_' . str_replace( '-', '', $this->formID ) . '() {
						' . str_replace( '-', '', $this->formID ) . '.submitViaAjax();
					}
				</script>

				';

			}

			$this->html .= '

				<div class="row">
					<button id="' . $this->formID . '_submit">Send Message</button>
				</div>
			</form>

			<script>

				var ' . str_replace( '-', '', $this->formID ) . ' = new PFC_Form({
					formID: \'' . $this->formID . '\',
					submitButtonID : \'' . $this->formID . '_submit\'
				});

			</script>

			';

	}

	private function build_field( $field ) {

		if ( $field['status'] != 'enabled' ) {

			return '';

		}

		$html = '<field><label for="' . esc_attr( $field['name'] ) . '">' . esc_html( $field['label'] ) . ( $field['required'] == 'yes' ? ' *' : '' ) . '</label>';

		switch ( $field['type'] ) {
			case 'textarea':
				$html .= '<textarea name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['name'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '"' . ( $field['required'] == 'yes' ? ' required' : '' ) . '></textarea>';
				break;
			default:
				$html .= '<input name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['name'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" type="' . esc_attr( $field['type'] ) . '" value=""' . ( $field['required'] == 'yes' ? ' required' : '' ) . ' />';
				break;
		}

		return $html . '</field>';

	}

	public function set_nojs_status() {

		global $pcf_mailer;

		if ( $pcf_mailer->status['success'] === true ) {

			$class = 'pcf pcf_success';

		} elseif ( $pcf_mailer->status['success'] === false ) {

			$class = 'pcf pcf_error';

		} else {

			$class = 'pcf pcf_default';

		}

		$this->html = str_replace( 'pcf pcf_default', $class, $this->html );

	}

}