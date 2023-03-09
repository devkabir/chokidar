<?php
/**
 * It will handle all ip related tasks
 *
 * @package    DevKabir\Chokidar
 * @subpackage Web
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

/* This is a security measure to prevent direct access to the plugin file. */

use DevKabir\Chokidar\Plugin;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class Ips
 *
 * @package DevKabir\Chokidar
 */
final class Ips {
	use Transient;

	private const KEY = 'chokidar_ips';


	/**
	 * It sets the transient.
	 * Data structure:
	 * [
	 *      ip => [
	 *          country,
	 *          region,
	 *          city,
	 *          first visit
	 *      ]
	 * ]
	 *
	 * @param string $ip          The IP address to store.
	 * @param array  $information This is the information you want to store. It can be anything.
	 */
	public static function set( string $ip, array $information ): void {
		$ips             = self::all();
		$data            = array();
		$data['country'] = $information['country'];
		if ( array_key_exists( 'region', $information ) ) {
			$data['region'] = $information['region'] ?? '-';
		}
		if ( array_key_exists( 'city', $information ) ) {
			$data['city'] = $information['city'] ?? '-';
		}
		$data['time'] = current_time( 'mysql' );
		$ips[ $ip ]   = $data;
		set_transient( self::KEY, $ips, Plugin::TRANSIENT_WEEK );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$collections = self::all();
		$data        = array();
		foreach ( $collections as $ip => $information ) {
			$holder            = array( 'ip' => $ip );
			$holder['country'] = $information['country'];
			if ( array_key_exists( 'region', $information ) ) {
				$holder['region'] = $information['region'] ?? '-';
			}
			if ( array_key_exists( 'city', $information ) ) {
				$holder['city'] = $information['city'] ?? '-';
			}
			$holder['first_visit'] = self::format_date( $information['time'] );
			$data[]                = $holder;
		}

		return array_reverse( $data );
	}

}
