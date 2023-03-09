<?php
/**
 * Summary (no period for file headers)
 *
 * @package    DevKabir\Chokidar
 * @subpackage Component
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

/* This is a security measure to prevent direct access to the plugin file. */

use DevKabir\Chokidar\Plugin;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class Pages
 *
 * @package    DevKabir\Chokidar
 */
final class Visits {
	use Transient;

	private const KEY = 'chokidar-page-visits';

	/**
	 * It takes an IP address, a page URL, and a referer, and adds them to the transient
	 * Data Structure:
	 * [
	 *      visiting page => [
	 *          visiting method => [
	 *              referer => [
	 *              visiting time => visitor's ip
	 *          ]
	 *      ]
	 * ]
	 *
	 * @param string $ip       The IP address of the visitor.
	 * @param string $method   Visitor's visiting method.
	 * @param string $page_url The URL of the page that the user is on.
	 * @param string $referer  The URL of the page that linked to the current page.
	 */
	public static function set( string $ip, string $method, string $page_url, string $referer ): void {
		$pages = self::all();
		$pages[ $page_url ][ $method ][ $referer ][ current_time( 'mysql' ) ] = $ip;
		set_transient( self::KEY, $pages, Plugin::TRANSIENT_WEEK );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$collections = self::all();
		$data        = array();
		foreach ( $collections as $page => $methods ) {
			$holder = array( 'page' => $page );
			foreach ( $methods as $method => $referrers ) {
				switch ( $method ) {
					case 'GET':
						$method = 'Visit';
						break;
					case 'POST':
						$method = 'Submit';
						break;
					case 'PUT':
						$method = 'Replace';
						break;
					case 'PATCH':
						$method = 'Modify';
						break;
					case 'DELETE':
						$method = 'Delete';
						break;
					default:
						$method = 'Unknown';
						break;
				}
				$holder['method'] = $method;
				foreach ( $referrers as $referer => $hits ) {
					$holder['referer'] = wp_parse_url( $referer, PHP_URL_HOST );
					$holder['hits']    = count( $hits );
					$data[]            = $holder;
				}
			}
		}
		return self::sort_by( $data, 'hits' );
	}

}
