<?php

class PCF_Settings {

	/***********************************************************************/
	/***************************  CONSTRUCTOR  *****************************/
	/***********************************************************************/

	public $settings;

	function __construct() {

		$this->settings = [
			'prefix' => 'pcf',
			'page' => [
				'page_title' => 'Professional Contact Form',
				'page_desc' => '<p>You shouldn\'t have to spend 20 minutes figuring out a contact form.</p>',
				'menu_title' => 'Contact Form',
				'capability' => 'administrator',
				'menu_slug' => 'options_page',
				'desc' => '',
				'tabs' => [
					[
						'slug' => 'main',
						'label' => 'Main Settings',
						'sections' => [
							[
								'slug' => 'one',
								'heading' => '',
								'desc' => '<h2>Instructions</h2><p>1. Fill in your email address below (where does the contact form send emails to).</p><p>2. Place the shortcode (<code>[pcf-display-form]</code>) on a page of your choosing.</p><p><i>(Of course, I know that you also might <b>want</b> to tweak stuff, so  the above tabs provide some additional customization options.)</i></p>',
								'fields' => [
									[
										'slug' => 'mail_to',
										'label' => 'Your Email Address (Required)',
										'type' => 'email',
										'choices' => null,
										'default' => '',
										'desc' => 'Provide an email address to send contact form emails to.',
										'placeholder' => 'name@email.com',
										'beforeInput' => '',
										'afterInput' => '<a href="?page=pcf_options_page&action=pcf_send_test" class="button pcf_test" id="send_test_email">Send Test Email</a>'
									]
								]
							],
						]
					],
					[
						'slug' => 'form',
						'label' => 'Optional: Customize Contact Form',
						'sections' => [
							[
								'slug' => 'one',
								'heading' => 'Google Invisible reCaptcha Integration',
								'desc' => '<p>reCAPTCHA is a free service from Google that protects your website from spam and abuse. You can enable it on your form by turning it on in your Google account, and then entering the two keys that Google provides you here. <a href="https://www.google.com/recaptcha/intro/invisible.html">More information</a></p><p><span style="color:rgb(210, 119, 0);font-weight:bold">NOTE: Invisible reCaptcha doesn\'t work without Javascript.</span> That means we can either set it to disallow any messages if Javascript is turned off (more secure), or set it to allow all messages if Javascript is turned off (more user friendly). Right now, I have it set as the latter, so this will only protect you if the user has Javascript enabled.</p>',
								'fields' => [
									[
										'slug' => 'reCaptcha_status',
										'label' => 'Use reCaptcha on Form',
										'type' => 'toggle',
										'choices' => [
											'disabled',
											'enabled'
										],
										'default' => 'disabled',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'reCaptcha_site_key',
										'label' => 'reCaptcha Site Key',
										'type' => 'text',
										'choices' => null,
										'default' => '',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'reCaptcha_secret_key',
										'label' => 'reCaptcha Secret Key',
										'type' => 'text',
										'choices' => null,
										'default' => '',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '',
										'afterInput' => ''
									]
								]
							],
							[
								'slug' => 'two',
								'heading' => 'Customize Fields',
								'desc' => '<p>All contact forms need to let users add a message. However, some fields are optional. Name, Email, etc. You can toggle optional fields on and off, and customize some of their attributes, like placeholder.</p>',
								'fields' => [
									[
										'slug' => 'field_name_status',
										'label' => 'Show Name Field on Form',
										'type' => 'toggle',
										'choices' => [
											'disabled',
											'enabled'
										],
										'default' => 'enabled',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '<button type="button" class="button hide-if-no-js" style="float:right" id="pcf_field_name_status_customize">Customize</button>',
										'afterInput' => ''
									],
									[
										'slug' => 'field_name_required',
										'label' => 'Make Field Required',
										'type' => 'toggle',
										'choices' => [
											'no',
											'yes'
										],
										'default' => 'yes',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'field_name_placeholder',
										'label' => 'Custom Placeholder',
										'type' => 'text',
										'choices' => null,
										'default' => '',
										'desc' => '',
										'placeholder' => 'Leave empty for default.',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'field_email_status',
										'label' => 'Show Email Field on Form',
										'type' => 'toggle',
										'choices' => [
											'disabled',
											'enabled'
										],
										'default' => 'enabled',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '<button type="button" class="button hide-if-no-js" style="float:right" id="pcf_field_email_status_customize">Customize</button>',
										'afterInput' => ''
									],
									[
										'slug' => 'field_email_required',
										'label' => 'Make Field Required',
										'type' => 'toggle',
										'choices' => [
											'no',
											'yes'
										],
										'default' => 'yes',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'field_email_placeholder',
										'label' => 'Custom Placeholder',
										'type' => 'text',
										'choices' => null,
										'default' => '',
										'desc' => '',
										'placeholder' => 'Leave empty for default.',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'field_phone_status',
										'label' => 'Show Phone Field on Form',
										'type' => 'toggle',
										'choices' => [
											'disabled',
											'enabled'
										],
										'default' => 'disabled',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '<button type="button" class="button hide-if-no-js" style="float:right" id="pcf_field_phone_status_customize">Customize</button>',
										'afterInput' => ''
									],
									[
										'slug' => 'field_phone_required',
										'label' => 'Make Field Required',
										'type' => 'toggle',
										'choices' => [
											'no',
											'yes'
										],
										'default' => 'yes',
										'desc' => '',
										'placeholder' => '',
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'field_phone_placeholder',
										'label' => 'Custom Placeholder',
										'type' => 'text',
										'choices' => null,
										'default' => '',
										'desc' => '',
										'placeholder' => 'Leave empty for default.',
										'beforeInput' => '',
										'afterInput' => ''
									]
								]
							],
							[
								'slug' => 'three',
								'heading' => 'Select CSS for Form',
								'desc' => '<p>This plugin provides default CSS for the form, broken into a couple options. You can use the pieces that you want, or let for theme style the form.</p>',
								'fields' => [
									[
										'slug' => 'css_layout',
										'label' => 'Layout Styles',
										'type' => 'toggle',
										'choices' => [
											'disabled',
											'enabled'
										],
										'default' => 'enabled',
										'desc' => 'These styles control the layout of the form, in rows and columns.',
										'placeholder' => null,
										'beforeInput' => '',
										'afterInput' => ''
									],
									[
										'slug' => 'css_inputs',
										'label' => 'Input Styles',
										'type' => 'toggle',
										'choices' => [
											'disabled',
											'enabled'
										],
										'default' => 'disabled',
										'desc' => 'These styles attempt to make the form inputs match Google\'s reCaptcha style, which can\'t be changed. However, most themes already define a look for inputs, so these are disabled by default.',
										'placeholder' => null,
										'beforeInput' => '',
										'afterInput' => ''
									]
								]
							],
						]
					],
					[
						'slug' => 'email',
						'label' => 'Optional: Customize Email',
						'sections' => [
							[
								'slug' => 'one',
								'heading' => 'Email Details',
								'desc' => '<p>Here you can override some of the default settings for the email that is sent when a user uses the contact form.</p>',
								'fields' => [
									[
										'slug' => 'mail_subject',
										'label' => 'Subject',
										'type' => 'text',
										'choices' => null,
										'default' => '',
										'desc' => 'All emails from the contact form will have the same subject. This makes using Inbox Rules on your email client easier.',
										'placeholder' => 'Leave empty for default.',
										'beforeInput' => '',
										'afterInput' => ''
									]
								]
							]
						]
					]
				]
			]
		];

		new AZM_Settings_Page( $this->settings );

	}

}