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

	public $tab = 'render';

	/**
	 * Load all the things
	 */
	public function __construct() {
		add_filter( 'install_plugins_tabs', array( $this, 'add_tab' ) );
		add_action( 'install_plugins_render', array( $this, 'contents' ) );
		add_action( 'install_plugins_render', 'display_plugins_table' );
	}

	/**
	 * Add a new tab to the plugin-install.php page
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function add_tab( $tabs ) {
		$tabs[ $this->tab ] = _x( 'Render', 'Plugin Installer' );

		return $tabs;
	}

	/**
	 * Make a remote request to realbigplugins.com
	 *
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

	/**
	 * Convert incoming data from realbigplugins.com to array of objects
	 *
	 * @return array
	 */
	public function setup_results() {
		$objects = array();

		foreach ( $this->get_stuff() as $plugin ) {
			$object                    = new stdClass();
			$object->name              = $plugin->title;
			$object->slug              = $plugin->slug;
			$object->version           = $plugin->more->version;
			$object->author            = $plugin->author->name;
			$object->author_profile    = $plugin->author->URL;
			$object->contributors      = array( $plugin->author->name => $plugin->author->URL );
			$object->requires          = null;
			$object->tested            = null;
			$object->compatibility     = array( '4.0' => array( '1.0' => array( '100' ) ) );
			$object->rating            = null;
			$object->num_ratings       = null;
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
			$object->downloaded        = $plugin->more->sales;
			$object->last_updated      = $plugin->modified;

			$objects[] = $object;

		}

		return $objects;
	}

	public function change_action_links( $action_links, $plugin ) {
		$action_links = array();

		if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
			$status   = install_plugin_install_status( $plugin );
			$tracking = '?utm_source=Add-New-Plugin&utm_medium=Action-Link&utm_content=' . $_SERVER['HTTP_HOST'] . '&utm_campaign=Add-new-plugins-action-links';

			switch ( $status['status'] ) {
				case 'install':
					if ( $status['url'] ) {
						$action_links[] = '<a class="install-now button" href="http://realbigplugins.com/plugins/' . $plugin['slug'] . '/' . $tracking . '" aria-label="' . esc_attr( sprintf( __( 'Download %s now' ), $name ) ) . '">' . __( 'Download Now' ) . '</a>';
					} else {
						$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( 'This plugin is already installed and is up to date' ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
					}

					break;
				case 'update_available':
					if ( $status['url'] ) {
						$action_links[] = '<a class="button" href="' . $status['url'] . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '">' . __( 'Update Now' ) . '</a>';
					}

					break;
				case 'latest_installed':
				case 'newer_installed':
					$action_links[] = '<span class="button button-disabled" title="' . esc_attr__( 'This plugin is already installed and is up to date' ) . ' ">' . _x( 'Installed', 'plugin' ) . '</span>';
					break;
			}
		}
		$details_link = 'http://realbigplugins.com/plugins/' . $plugin['slug'] . '/' . $tracking . '&amp;TB_iframe=true&amp;width=600&amp;height=550';

		$action_links[] = '<a href="' . esc_url( $details_link ) . '" class="thickbox" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '">' . __( 'More Details' ) . '</a>';

		return $action_links;
	}

	/**
	 * Contents of the tab
	 */
	public function contents() {
		global $wp_list_table;

		// Give the table list the source array of objects
		$wp_list_table->items = $this->setup_results();
		add_filter( 'plugin_install_action_links', array( $this, 'change_action_links' ), 99, 2 );
	}
}

$render = new Render_Extension_List();