<?php

if ( ! class_exists( 'AZM_Settings_Page' ) ) {

	class AZM_Settings_Page {

		/***********************************************************************/
		/***************************  CONSTRUCTOR  *****************************/
		/***********************************************************************/

		public $prefix;
		public $page;
		public $tabs;

		function __construct( $params ) {

			// HANDLE USER DEFINED PARAMETERS

			$this->prefix = $params['prefix'];

			$this->page = [
				'page_title' => $params['page']['page_title'],
				'page_desc' => array_key_exists( 'page_desc', $params['page'] ) ? $params['page']['page_desc'] : '',
				'menu_title' => $params['page']['menu_title'],
				'capability' => $params['page']['capability'],
				'menu_slug' => $this->prefix . '_' . $params['page']['menu_slug'],
				'desc' => $params['page']['desc']
			];

			$this->tabs = $params['page']['tabs'];

			// REGISTER MENU PAGE AND SETTINGS

			add_action( 'admin_menu', array( $this, 'azm_register_settings_page' ) );
			add_action( 'admin_init',  array( $this, 'azm_initialize_settings' ) );
			add_action( 'admin_head',  array( $this, 'azm_add_toggle_styles' ) );

		}

		public function azm_add_toggle_styles() {

			global $_wp_admin_css_colors;
			global $admin_colors;

			$color = get_user_meta(get_current_user_id(), 'admin_color', true);
			$admin_colors = $_wp_admin_css_colors[$color]->colors[2];

			$current_screen = get_current_screen();

			if ( $current_screen->id == 'settings_page_' . $this->page['menu_slug'] ) {

				echo '<style>.input-toggle {height:30px;}.input-toggle input {position: absolute;margin-left: -9999px;visibility: hidden;}.input-toggle input + label {display: block;position: absolute;cursor: pointer;outline: none;user-select: none;}.input-toggle input + label {width: 60px;height: 30px;border-radius: 15px;}.input-toggle input + label.toggle {width: 60px;height: 30px;border-radius: 15px;}.input-toggle input + label.toggle::before,.input-toggle input + label.toggle::after {display: block;position: absolute;top: 0px;left: 0px;bottom: 0px;content: "";}.input-toggle input + label.toggle::before {right: 0px;background-color:rgba(210, 210, 210,1);border-radius: 15px;transition: background 0.4s;}.input-toggle input + label.toggle:after {width: 50%;background-color: #fff;border-radius: 100%;box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);transition: transform 0.4s;}.input-toggle input:checked + label.toggle {pointer-events:none;}.input-toggle input:checked + label.toggle::before {background-color: rgba(255,255,255,0.1);}.input-toggle input:checked + label.toggle::after {transform:translateX(100%);}</style>';

			}

		}


		public function azm_register_settings_page() {

			add_options_page(  
				$this->page['page_title'],
				$this->page['menu_title'],
				$this->page['capability'],
				$this->page['menu_slug'],
				array( $this, 'azm_create_settings_page' )
			);	

		}

		public function azm_create_settings_page() {

			// SET TAB TO FIRST BY DEFAULT

			$active_tab_slug = $this->prefix . '_tab_' . $this->tabs[0]['slug'];

			// IF THERE IS A USER PROVIDED ONE, MEANING THE USER HAS CLICKED ON A TAB:

			if ( array_key_exists( 'tab', $_GET ) ) {

				// GRAB IT AND SANITIZE IT

				$user_provided_tab_slug = sanitize_key( $_GET['tab'] );

				// LOOP THROUGH ALL POSSIBLE TABS TO SEE IF THE USER PROVIDED TEXT REALLY IS AN OPTION

				foreach ( $this->tabs as $tab ) {

					if ( $user_provided_tab_slug == $this->prefix . '_tab_' . $tab['slug'] ) {

						$active_tab_slug = $user_provided_tab_slug;

					}

				}

			}

			echo '

			<div class="wrap">

				<h2>' . esc_html( $this->page['page_title'] ) . '</h2>

				' . $this->page['page_desc'] . '

				<form method="post" enctype="multipart/form-data" action="options.php">

				' . $this->page['desc'] . '

				<h2 class="nav-tab-wrapper">';

				foreach( $this->tabs as $tab ) {

					$tab_slug = $this->prefix . '_tab_' . $tab['slug'];

					echo '<a href="?page=' . $this->page['menu_slug'] . '&tab=' . $tab_slug . '" class="nav-tab' . ( $active_tab_slug == $tab_slug ? ' nav-tab-active' : '' ) . '">' . $tab['label'] . '</a>';

				}

				echo '</h2>';

				settings_fields( str_replace( 'tab', 'group', $active_tab_slug ) );
				do_settings_sections(str_replace( 'tab', 'options', $active_tab_slug ) );
				submit_button();

				echo '	

				</form>

			</div>';

		}

		public function azm_initialize_settings() {

			foreach( $this->tabs as $tab ) {

				foreach( $tab['sections'] as $section ) {

					add_settings_section(
						$this->prefix . '_tab_' . $tab['slug'] . '_section_' . $section['slug'],
						$section['heading'],
						function ( $section_passed ) use ( $section ) {
							echo $section['desc'];
						},
						$this->prefix . '_options_' . $tab['slug']
					);

					foreach( $section['fields'] as $field ) {

						add_settings_field(
							$this->prefix . '_' . $field['slug'],
							$field['label'],
							array( $this, 'azm_field_callback' ),
							$this->prefix . '_options_' . $tab['slug'],
							$this->prefix . '_tab_' . $tab['slug'] . '_section_' . $section['slug'],
							[
								'option' => $this->prefix . '_options_' . $tab['slug'],
								'key' => $this->prefix . '_' . $field['slug'],
								'type' => $field['type'],
								'choices' => $field['choices'],
								'default' => $field['default'],
								'desc' => $field['desc'],
								'placeholder' => $field['placeholder'],
								'beforeInput' => $field['beforeInput'],
								'afterInput' => $field['afterInput']
							]
						);

					}

					/*********************************************/
					/********  REGISTER THE TAB SETTING  *********/
					/*********************************************/

					register_setting(
						$this->prefix . '_group_' . $tab['slug'], 
						$this->prefix . '_options_' . $tab['slug'],
						array( $this, 'azm_validation' )
					);

				}

			}

		}

		public function azm_field_callback( $params ) {

			global $admin_colors; 

			$option = array_key_exists('option', $params) ? $params['option'] : '';
			$key = array_key_exists('key', $params) ? $params['key'] : '';
			$type = array_key_exists('type', $params) ? $params['type'] : 'text';
			$desc = array_key_exists('desc', $params) ? $params['desc'] : '';
			$placeholder = array_key_exists('placeholder', $params) ? $params['placeholder'] : '';
			$choices = array_key_exists('choices', $params) ? $params['choices'] : '';
			$default = array_key_exists('default', $params) ? $params['default'] : '';
			$beforeInput = array_key_exists('beforeInput', $params) ? $params['beforeInput'] : '';
			$afterInput = array_key_exists('beforeInput', $params) ? $params['afterInput'] : '';

			$options = get_option($option) ? get_option($option) : [];
			$value = array_key_exists($key, $options) ? $options[$key] : $default;

			echo $beforeInput;

			switch ( $type ) {
				case 'textarea':
					echo '<textarea name="' . $option . '[' . $key . ']" id="' . $key . '" placeholder="' . $placeholder . '">' . $options[$key] . '</textarea>';
					break;
				case 'toggle':
					echo '
						<div class="input-toggle" id="' . $key . '">
							<input name="' . $option . '[' . $key . ']" id="' . $key . '_' . $choices[0] . '" placeholder="' . $placeholder . '" value="' . $choices[0] . '" type="radio" ' . checked( $value, $choices[0], false ) .'>
							<label for="' . $key . '_' . $choices[0] . '"></label>
							<input name="' . $option . '[' . $key . ']" id="' . $key . '_' . $choices[1] . '" placeholder="' . $placeholder . '" value="' . $choices[1] . '" type="radio" ' . checked( $value, $choices[1], false ) .'>
							<label for="' . $key . '_' . $choices[1] . '" class="toggle" style="background-color: ' . $admin_colors . '"></label>
						</div>';
					break;
				case 'select':
					echo '<select name="' . $option . '[' . $key . ']" id="' . $key . '">';
					foreach ( $choices as $selectValue => $selectLabel ) {
						echo '<option value="' . $selectValue . '"' . ( $value == $selectValue ? ' selected="selected"' : '' ).'>' . $selectLabel . '</option>';
					}
					echo '</select>';
					break;
				default:
					echo '<input name="' . $option . '[' . $key . ']" id="' . $key . '" placeholder="' . $placeholder . '" type="' . $type . '" value="' . $options[$key] . '" class="regular-text" />';
					break;
			}

			echo $afterInput;

			echo '<p class="description">' . $desc . '</p>';

		}

		public function azm_validation( $input ) {

			$output = array();

			foreach( $input as $key => $value ) {

				if ( isset( $input[$key] ) ) {

					$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );

				}

			}

			return apply_filters('azm_validation', $output, $input);

		}

	}

}