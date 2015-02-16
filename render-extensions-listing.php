<?php

/*
Plugin Name: Render Extensions Listing
Plugin URI: http://realbigplugins.com
Description: This plugin integrates the listing of available Render addons from realbigplugins.com to a new tab on the Add New Plugins page.
Version: 0.1
Author: Kyle Maurer
Author URI: http://realbigplugins.com
License: GPLv2 or later
Text Domain: renderx
*/

class Render_Extension_List {

	/**
	 *
	 */
	public function __construct() {
		add_filter( 'install_plugins_tabs', array( $this, 'add_tab' ) );
		add_action( 'install_plugins_render', array( $this, 'contents' ) );
		add_action( 'install_plugins_render', 'display_plugins_table' );

		add_action( 'admin_notices', function () {

//			$args = array( 'user' => 'BrashRebel' );
//			$api  = plugins_api( 'query_plugins', $args );
//			// Testing the output from our .org call
//			echo '<h1>External</h1><pre>';
//			var_dump( $api->plugins );
//			echo '</pre>';
			// Data from RBP
			echo '<h1>Before</h1><pre>';
			var_dump( $this->get_stuff() );
			echo '</pre>';
			// Data converted
			echo '<h1>After</h1><pre>';
			var_dump( $this->setup_results() );
			echo '</pre>';
		} );
	}

	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function add_tab( $tabs ) {
		$tabs['render'] = _x( 'Render', 'Plugin Installer' );

		return $tabs;
	}

	/**
	 * @return mixed|string
	 */
	public function get_stuff() {

		$url = 'http://realbigplugins.com/wp-json/posts?type[]=download';
		//GET the remote site
		$response = wp_remote_get( $url );

		//Check for error
		if ( is_wp_error( $response ) ) {
			return sprintf( 'The URL %1s could not be retrieved.', $url );
		}

		//get just the body
		$data = wp_remote_retrieve_body( $response );

		//return if not an error
		if ( ! is_wp_error( $data ) ) {
			$data = json_decode( $data );

			//decode and return
			return $data;

		}
	}

	public function setup_results() {
		// Building an object which matches what the plugins list table expects
		$objects = array();

		foreach ( $this->get_stuff() as $plugin ) {
			$object                    = new stdClass();
			$object->name              = $plugin->title;
			$object->slug              = $plugin->slug;
			$object->version           = '1.0';
			$object->author            = $plugin->author->name;
			$object->author_profile    = $plugin->author->URL;
			$object->contributors      = array( $plugin->author->name => $plugin->author->URL );
			$object->requires          = '1.0';
			$object->tested            = '4.0';
			$object->compatibility     = array( '4.0' => array( '1.0' => array( '100' ) ) );
			$object->rating            = 99.5;
			$object->num_ratings       = 234;
			$object->ratings           = array(
				'5' => 25,
				'4' => 2,
				'3' => 1,
				'2' => 8,
				'1' => 1
			);
			$object->description       = $plugin->excerpt;
			$object->short_description = substr( $plugin->excerpt, 0, strpos( $plugin->excerpt, ' ', 80 ) ) . '...';
			$object->icons             = array( 'default' => $plugin->featured_image->source );
			$object->downloaded        = 1100;
			$object->last_updated      = $plugin->modified;

			$objects[] = $object;
		}

		return $objects;
	}

	/**
	 * Contents of the tab
	 */
	public function contents() {
		global $wp_list_table;

		// Testing the contents of our manually built object


		// Used for customizing a .org call
//		$args = array( 'user' => 'BrashRebel' );
//		$api  = plugins_api( 'query_plugins', $args );

		// Give the table list the source array of objects
		$wp_list_table->items = $this->setup_results();//$api->plugins;


		$wp_list_table->set_pagination_args(
			array(
				'total_items' => $api->info['results'],
				'per_page'    => 5,
			)
		);
	}
}

$render = new Render_Extension_List();