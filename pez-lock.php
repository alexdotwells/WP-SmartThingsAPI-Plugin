<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Paddle EZ Lock Control
 * Version:           1.0.0
 * Author:            AWells
 * Description:       Paddle EZ Smart Lock & Locker Management
 * Text Domain:       pezlock
 */

/* If this file is called directly, abort. */
if ( ! defined( 'WPINC' ) ) {
	die;
}

require plugin_dir_path( __FILE__ ) . 'includes/class-pezlock.php';

function run_pezlock() {
	$plugin = new Pezlock();
	$plugin->run();
}

run_pezlock();
