<?php
/**
 * It will hold hackers information, till 24 hour.
 *
 * @package    DevKabir\Chokidar
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

/* This is a security measure to prevent direct access to the plugin file. */

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class Hackers
 *
 * @package    DevKabir\Chokidar
 */
final class Hackers {
	use Transient;

	public const KEY = 'chokidar-hackers';

	/**
	 * It sets a transient with the IP address of the hacker and the code of the error they received
	 * Data Structure: [ ip : why flagged as hacker ]
	 *
	 * @param string     $ip   The IP address of the hacker.
	 * @param string|int $code The code to set.
	 */
	public static function set( string $ip, $code ): void {
		$hackers        = self::all();
		$hackers[ $ip ] = ucwords( str_replace( '_', ' ', $code ) );
		set_transient( self::KEY, $hackers );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$collections = self::all();
		$data        = array();
		foreach ( $collections as $ip => $reason ) {
			$data[] = array(
				'ip'  => $ip,
				'why' => $reason,
			);
		}

		return array_reverse( $data );
	}
}
