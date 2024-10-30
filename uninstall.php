<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options
delete_option( 'mailqueue' );

// Delete options in Multisite
delete_site_option( 'mailqueue' );

wp_clear_scheduled_hook( 'remove_old_emails' );
wp_clear_scheduled_hook( 'automatic_send_emails' );