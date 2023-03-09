<?php
/**
 * It will hold information, till 24 hour.
 *
 * @package    DevKabir\Chokidar
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

/* This is a security measure to prevent direct access to the plugin file. */

use WP_Error;
use DevKabir\Chokidar\Plugin;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class FailedLogin
 *
 * @package    DevKabir\Chokidar
 */
final class FailedLogin {
	use Transient;

	public const KEY = 'chokidar-failed-logins';

	/**
	 * It takes an IP address, a username, and an error object, and it stores the error messages in a transient
	 *
	 * @param string   $ip       The IP address of the user.
	 * @param string   $username The username of the user who tried to login.
	 * @param WP_Error $error    The error object that was returned by the login function.
	 */
	public static function set( string $ip, string $username, WP_Error $error ): void {
		$fails                                                = self::all();
		$fails[ $username ][ $ip ][ current_time( 'mysql' ) ] = $error->get_error_code();
		set_transient( self::KEY, $fails, Plugin::TRANSIENT_TIME );
		Hackers::set( $ip, $error->get_error_code() );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$collections = self::all();
		$data        = array();
		foreach ( $collections as $username => $ips ) {
			$holder         = array( 'username' => $username );
			$holder['hits'] = count( $ips );
			$data[]         = $holder;
		}

		return self::sort_by( $data, 'hits' );
	}

}
