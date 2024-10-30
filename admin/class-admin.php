<?php

namespace mailqueue;

/**
 * The code used in the admin.
 */
class Admin {
	private $plugin_slug;
	private $version;
	private $option_name;
	private $settings;
	private $settings_group;

	public function __construct( $plugin_slug, $version, $option_name ) {
		$this->plugin_slug    = $plugin_slug;
		$this->version        = $version;
		$this->option_name    = $option_name;
		$this->settings       = get_option( $this->option_name );
		$this->settings_group = $this->option_name . '_group';
	}

	/**
	 * Generate settings fields by passing an array of data (see the render method).
	 *
	 * @param array $field_args The array that helps build the settings fields
	 * @param array $settings   The settings array from the options table
	 *
	 * @return string The settings fields' HTML to be output in the view
	 */
	private function custom_settings_fields( $field_args, $settings ) {
		$output = '';

		foreach ( $field_args as $field ) {
			$slug    = $field['slug'];
			$setting = $this->option_name . '[' . $slug . ']';
			$label   = esc_attr__( $field['label'], 'mailqueue' );

			switch ( $field['type'] ) {
				case 'text':
					$output .= '<tr>';
					$output .= '<th>';
					$output .= '<label for="' . $setting . '">' . $label . '</label>';
					$output .= '</th>';
					$output .= '<td>';
					$output .= '<input type="text" id="' . $setting . '" name="' . $setting . '" value="' . $settings[ $slug ] . '">';
					$output .= '</td>';
					$output .= '<tr>';
					break;
				case 'password':
					$output .= '<tr>';
					$output .= '<th>';
					$output .= '<label for="' . $setting . '">' . $label . '</label>';
					$output .= '</th>';
					$output .= '<td>';
					$output .= '<input type="password" id="' . $setting . '" name="' . $setting . '" value="' . $settings[ $slug ] . '">';
					$output .= '</td>';
					$output .= '<tr>';
					break;
				case 'textarea':
					$output .= '<tr>';
					$output .= '<th>';
					$output .= '<label for="' . $setting . '">' . $label . '</label>';
					$output .= '</th>';
					$output .= '<td>';
					$output .= '<textarea id="' . $setting . '" name="' . $setting . '" rows="10" style="width: 400px;">' . $settings[ $slug ] . '</textarea>';
					$output .= '</td>';
					$output .= '<tr>';
					break;
				case 'checkbox':
					$output .= '<tr>';
					$output .= '<th>';
					$output .= '<label for="' . $setting . '">' . $label . '</label>';
					$output .= '</th>';
					$output .= '<td>';
					$output .= '<input type="checkbox" id="' . $setting . '" name="' . $setting . '" value="1" ';
					if ( 1 == $settings[ $slug ] ) {
						$output .= ' checked';
					}
					$output .= '>';
					$output .= '</td>';
					$output .= '<tr>';
					break;
			}
		}

		return $output;
	}

	public function assets() {
		wp_enqueue_style( $this->plugin_slug . '-admin', plugin_dir_url( __FILE__ ) . 'css/mailqueue-admin.css', [], $this->version );
		wp_enqueue_script( $this->plugin_slug . '-admin', plugin_dir_url( __FILE__ ) . 'js/mailqueue-admin.js', [ 'jquery' ], $this->version, true );
		wp_localize_script( $this->plugin_slug . '-admin', 'ajax_object', array(
			'url' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function register_settings() {
		register_setting( $this->settings_group, $this->option_name );
	}

	public function add_menus() {
		$plugin_name = Info::get_plugin_title();
		add_submenu_page(
			'options-general.php',
			$plugin_name,
			$plugin_name,
			'manage_options',
			$this->plugin_slug,
			[ $this, 'render' ]
		);
	}

	/**
	 * Render the view using MVC pattern.
	 */
	public function render() {
		$field_args = [
			[
				'label' => 'Automatic processing',
				'slug'  => 'automatic-processing',
				'type'  => 'checkbox'
			],
			[
				'label' => 'SMTP server',
				'slug'  => 'smtp-server',
				'type'  => 'text'
			],
			[
				'label' => 'SMTP username',
				'slug'  => 'smtp-username',
				'type'  => 'text'
			],
			[
				'label' => 'SMTP login',
				'slug'  => 'smtp-login',
				'type'  => 'text'
			],
			[
				'label' => 'SMTP Password',
				'slug'  => 'smtp-password',
				'type'  => 'password'
			],
			[
				'label' => 'SMTP Port',
				'slug'  => 'smtp-port',
				'type'  => 'text'
			],
			[
				'label' => 'SMTP Encryption',
				'slug'  => 'smtp-encryption',
				'type'  => 'text'
			],
			[
				'label' => 'Disable SSL validation',
				'slug'  => 'disable-ssl-validation',
				'type'  => 'checkbox'
			],
			[
				'label' => 'Number of emails in one batch',
				'slug'  => 'number-of-emails',
				'type'  => 'text'
			],
			[
				'label' => 'Email subjects',
				'slug'  => 'email-subjects',
				'type'  => 'textarea'
			],
			[
				'label' => 'Remove old emails',
				'slug'  => 'remove-old-emails',
				'type'  => 'checkbox'
			]
		];

		// Model
		$settings = $this->settings;

		if ( ! empty( $settings ) && $settings['automatic-processing'] ) {
			if ( ! wp_next_scheduled( 'automatic_send_emails' ) ) {
				wp_schedule_event( time(), '15minutes', 'automatic_send_emails' );
			}
		} else {
			wp_clear_scheduled_hook( 'automatic_send_emails' );
		}


		// Controller
		$fields         = $this->custom_settings_fields( $field_args, $settings );
		$settings_group = $this->settings_group;
		$heading        = Info::get_plugin_title();
		$submit_text    = esc_attr__( 'Submit', 'mailqueue' );

		// View
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/view.php';
	}
}
