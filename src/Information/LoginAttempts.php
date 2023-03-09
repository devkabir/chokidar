<?php
/**
 * It will hold information, till 24 hour.
 *
 * @package    DevKabir\Chokidar
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

/* This is a security measure to prevent direct access to the plugin file. */

use DevKabir\Chokidar\Plugin;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class LoginAttempts
 *
 * @package    DevKabir\Chokidar
 */
final class LoginAttempts {
	use Transient;

	public const KEY = 'chokidar-login-attempts';

	/**
	 * It takes an IP address, a username, and a password, and stores them in a transient
	 * Data Structure:
	 * [
	 *      user name => [
	 *          password => [
	 *              attempts
	 *          ]
	 *      ]
	 * ]
	 *
	 * @param string $ip       The IP address of the user attempting to login.
	 * @param string $username The username that was attempted.
	 * @param string $password The password that was attempted.
	 */
	public static function set( string $ip, string $username, string $password ): void {
		if (in_array($username, get_transient(Plugin::USERNAMES), true)) {
			$password = self::format_password($password);
		}
		$attempts                                                      = self::all();
		$attempts[ $username ][ $password ][ current_time( 'mysql' ) ] = $ip;
		set_transient( self::KEY, $attempts, Plugin::TRANSIENT_TIME );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$collections = self::all();
		$data        = array();
		foreach ( $collections as $username => $passwords ) {
			$holder = array( 'username' => $username );
			foreach ( $passwords as $password => $hits ) {
				$holder['password'] = $password;
				$holder['hits']     = count( $hits );
				$data[]             = $holder;
			}
		}

		return self::sort_by( $data, 'hits' );
	}

	/**
	 * It replace all letters with asterisks except last 3.
	 *
	 * @param string $password The password to be formatted.
	 *
	 * @return string The password is being returned with the last 3 characters replaced with asterisks.
	 */
	private static function format_password( string $password ): string {

		if (strlen($password) > 3) {
			return substr($password, 0, -3) . '***';
		}

		return $password;

	}
}
