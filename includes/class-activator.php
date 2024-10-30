<?php

namespace mailqueue;

/**
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator {
	/**
	 * Sets the default options in the options table on activation.
	 */
	public static function activate() {
		$option_name = INFO::OPTION_NAME;
		$settings    = get_option( $option_name );

		if ( empty( get_option( $option_name ) ) ) {
			$default_options = [
				'remove-old-emails' => false
			];
			update_option( $option_name, $default_options );
		}

		if ( ! wp_next_scheduled( 'remove_old_emails' ) && $settings['remove-old-emails'] ) {
			wp_schedule_event( time(), 'hourly', 'remove_old_emails' );
		}
	}
}
