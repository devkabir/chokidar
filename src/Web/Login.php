<?php
/**
 * Summary (no period for file headers)
 *
 * @package    DevKabir\Chokidar\Web
 * @subpackage Component
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Web;

/* This is a security measure to prevent direct access to the plugin file. */

use WP_Error;
use DevKabir\Chokidar\Information\FailedLogin;
use DevKabir\Chokidar\Information\LoginAttempts;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class Login
 *
 * @package DevKabir\Chokidar\Web
 */
final class Login {

	/**
	 * The IP address of the user attempting to login.
	 *
	 * @var string
	 */
	private static string $ip;

	/**
	 * It sets the IP address and adds two actions to the WordPress authentication process
	 *
	 * @param string $ip The IP address of the user attempting to login.
	 */
	public static function track( string $ip ): void {
		self::$ip = $ip;
		add_action( 'wp_authenticate', array( self::class, 'attempts' ), 10, 2 );
		add_action( 'wp_login_failed', array( self::class, 'failed_logins' ), 10, 2 );
	}

	/**
	 * If the username and password are empty, return. Otherwise, set the login attempts
	 *
	 * @param mixed $username The username that was entered.
	 * @param mixed $password The password to check.
	 */
	public static function attempts( $username, $password ): void {
		if ( empty( $username ) && empty( $password ) ) {
			return;
		}
		LoginAttempts::set( self::$ip, $username, $password );
	}

	/**
	 * It takes a username and an error message, and then it creates a new FailedLogin object with the current IP
	 * address, the username, and the error message.
	 *
	 * @param mixed    $username The username that was attempted to be logged in.
	 * @param WP_Error $error    The error message that was displayed to the user.
	 */
	public static function failed_logins( $username, WP_Error $error ): void {
		// Set the maximum number of failed login attempts.
		$failed_login_limit = 3;


		// Generate a unique transient name for the user's login attempts.
		$transient_name = 'login_attempt_' . $username;

		// Retrieve the number of failed login attempts for the user from the transient.
		$login_attempts = get_transient( $transient_name );

		// If the transient does not exist, set the number of login attempts to 0.
		if ( $login_attempts === false ) {
			$login_attempts = 0;
		}

		// If the number of login attempts has exceeded the limit, IP will be flagged as potential hacker.
		if ( $login_attempts >= $failed_login_limit ) {
			FailedLogin::set( self::$ip, $username, $error );
		}

		// Increment the number of login attempts and update the transient.
		$login_attempts ++;
		set_transient( $transient_name, $login_attempts, 3600 );


	}
}
