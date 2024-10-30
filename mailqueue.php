<?php
/**
 * Plugin Name:       Mailqueue
 * Plugin URI:        https://whitelabelcoders.com/
 * Description:       Store all emails in database and send them later.
 * Version:           1.0.2
 * Author:            WhiteLabelCoders
 * Author URI:        https://whitelabelcoders.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mailqueue
 * Domain Path:       /languages
 */

namespace mailqueue;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The class that contains the plugin info.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-info.php';

/**
 * The code that runs during plugin activation.
 */
function activation() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	Activator::activate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activation' );

/**
 * Run the plugin.
 */
function run() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';
	$plugin = new Plugin();
	$plugin->run();
}

run();
