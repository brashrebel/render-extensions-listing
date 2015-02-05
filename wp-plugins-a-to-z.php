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
		//add_action( 'install_plugins_wppatoz', array( $this, 'get_stuff' ) );
		add_action( 'install_plugins_wppatoz', array( $this, 'contents' ) );
		add_action( 'install_plugins_wppatoz', 'display_plugins_table' );
		//add_filter( 'plugins_api_result', array( $this, 'add_results' ) );
	}

	/**
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function add_tab( $tabs ) {
		$tabs['wppatoz'] = _x( 'WP Plugins A to Z', 'Plugin Installer' );

		return $tabs;
	}

	/**
	 * @return mixed|string
	 */
	public function get_stuff() {

		$url = 'http://rbmin.staging.wpengine.com/wp-json/posts?type[]=download';
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

	/**
	 * Contents of the tab
	 */
	public function contents() {
		global $wp_list_table;
//		$data = $this->get_stuff();
//		echo 'DEBUGGING<pre>';
//		print_r( $data );
//		echo '</pre>';

		$object = new stdClass();
		$object->name = 'Here we go';
		$object->slug = 'Here we go';
		$object->version = '1.0';
		$object->author = 'Here we go';
		$object->author_profile = 'Here we go';
		$object->contributors = array( 'Kyle' => 'http://kyleblog.net' );
		$object->requires = '1.0';
		$object->tested = '4.0';
		$object->compatibility = array( '4.0' => array( '1.0' => array( '100' )));
		$object->rating = 99.5;
		$object->num_ratings = 234;
		$object->ratings = array(
			'5' => 25,
			'4' => 2,
			'3' => 1,
			'2' => 8,
			'1' => 1
		);
		$object->description = 'Here we go';
		$object->short_description = 'Here we go';
		$object->icons = array('default' => '#');
		$object->downloaded = 1100;
		$object->last_updated = '1 month ago';

		$object = array( $object );
		echo 'TESTING<pre>';
		var_dump($object);
		echo '</pre>';

		$args = array( 'user' => 'BrashRebel' );

		$api = plugins_api( 'query_plugins', $args );

		$wp_list_table->items = $object;//$api->plugins;

		echo 'DEBUGGING<pre>';
		var_dump($api->plugins);
		echo '</pre>';

		$wp_list_table->set_pagination_args(
			array(
				'total_items' => $api->info['results'],
				'per_page'    => 5,
			)
		);
	}
}

new WP_Plugins_AtoZ();