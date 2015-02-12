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
		add_action( 'install_plugins_renderx', array( $this, 'contents' ) );
		add_action( 'install_plugins_renderx', 'display_plugins_table' );
	}

	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function add_tab( $tabs ) {
		$tabs['renderx'] = _x( 'Render', 'Plugin Installer' );

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
//			echo 'DEBUGGING<pre>';
//			print_r( $data );
//			echo '</pre>';

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
			$object->author            = 'Here we go';
			$object->author_profile    = 'Here we go';
			$object->contributors      = array( 'Kyle' => 'http://kyleblog.net' );
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
			$object->description       = 'Here we go';
			$object->short_description = 'Here we go';
			$object->icons             = array( 'default' => '#' );
			$object->downloaded        = 1100;
			$object->last_updated      = '1 month ago';

			$objects[] = $object;
		}

		return $objects;
	}

	/**
	 * Contents of the tab
	 */
	public function contents() {
		global $wp_list_table;

//		echo 'TESTING<pre>';
//		var_dump( $this->get_stuff() );
//		echo '</pre>';
		// Testing the contents of our manually built object
		echo 'TESTING<pre>';
		var_dump( $this->setup_results() );
		echo '</pre>';

		// Used for customizing a .org call
		$args = array( 'user' => 'BrashRebel' );
		$api  = plugins_api( 'query_plugins', $args );

		// Give the table list the source array of objects
		$wp_list_table->items = $this->setup_results();//$api->plugins;

		// Testing the output from our .org call
		echo 'DEBUGGING<pre>';
		var_dump( $api->plugins );
		echo '</pre>';

//		$wp_list_table->set_pagination_args(
//			array(
//				'total_items' => $api->info['results'],
//				'per_page'    => 5,
//			)
//		);
	}
}

new Render_Extension_List();