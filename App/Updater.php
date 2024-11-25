<?php
/**
 * @package snow-monkey-diet
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Snow_Monkey\Plugin\Diet\App;

use Inc2734\WP_GitHub_Plugin_Updater\Bootstrap;

class Updater {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, '_init' ) );
		add_action( 'admin_init', array( $this, '_admin_init' ) );
		add_filter( 'inc2734_github_plugin_updater_zip_url_inc2734/snow-monkey-diet', array( $this, '_zip_url' ) );
		add_filter( 'inc2734_github_plugin_updater_request_url_inc2734/snow-monkey-diet', array( $this, '_request_url' ), 10, 4 );
	}

	/**
	 * Activate auto update using GitHub.
	 */
	public function _init() {
		new Bootstrap(
			plugin_basename( SNOW_MONKEY_DIET_PATH . '/snow-monkey-diet.php' ),
			'inc2734',
			'snow-monkey-diet',
			array(
				'homepage' => 'https://snow-monkey.2inc.org',
			)
		);
	}

	/**
	 * Force update check.
	 */
	public function _admin_init() {
		if ( is_admin() && current_user_can( 'update_core' ) ) {
			$force_check = filter_input( INPUT_GET, 'force-check' );
			if ( ! empty( $force_check ) ) {
				set_site_transient( 'update_plugins', null );
			}
		}
	}

	/**
	 * There is a case that comes back to GitHub's zip url.
	 * In that case it returns false because it is illegal.
	 *
	 * @param string $url Zip URL.
	 * @return string|false
	 */
	public function _zip_url( $url ) {
		if ( 0 !== strpos( $url, 'https://snow-monkey.2inc.org/' ) ) {
			return false;
		}
		return $url;
	}

	/**
	 * Customize request URL that for updating.
	 *
	 * @param string $url Request URL.
	 * @param string $user_name GitHub usename.
	 * @param string $repository GitHub repository.
	 * @param string $version Version.
	 * @return string
	 */
	public function _request_url( $url, $user_name, $repository, $version ) {
		return ! $version
			? 'https://snow-monkey.2inc.org/github-api/snow-monkey-diet/response.json'
			: sprintf(
				'https://snow-monkey.2inc.org/github-api/snow-monkey-diet/packages/%1$s/response.json',
				$version
			);
	}
}
