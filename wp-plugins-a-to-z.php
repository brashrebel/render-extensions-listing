<?php

/*
Plugin Name: WP Plugins A To Z
Plugin URI: http://realbigplugins.com
Description: This plugin integrates the awesome reviews from the WP Plugins A to Z podcast with the Add New Plugin search.
Version: 0.1
Author: Kyle Maurer
Author URI: http://kyleblog.net
License: GPLv2 or later
Text Domain: wppatoz
*/

class WP_Plugins_AtoZ {
	/**
	 *
	 */
	public function __construct() {
		add_filter( 'install_plugins_tabs', array( $this, 'add_tab' ) );
		//add_action( 'load-plugin-install.php', array( $this, 'add_tab' ) );
	}

	public function add_tab( $tabs ) {
		$tabs['wppatoz'] = _x( 'WP Plugins A to Z', 'Plugin Installer' );
		return $tabs;
	}
}
new WP_Plugins_AtoZ();