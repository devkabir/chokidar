<?php
/**
 * It will handle all functionality of chokidar.
 *
 * @package    DevKabir\Chokidar
 * @subpackage Component
 * @since      1.0.0
 */

namespace DevKabir\Chokidar;

/* This is a security measure to prevent direct access to the plugin file. */


if ( ! defined( 'WPINC' ) ) {
	exit;
}


use DevKabir\Chokidar\Web\Login;
use DevKabir\Chokidar\Admin\Menu;
use DevKabir\Chokidar\Information\Ips;
use DevKabir\Chokidar\Information\Bots;
use DevKabir\Chokidar\Information\Visits;
use DevKabir\Chokidar\Information\Device;
use DevKabir\Chokidar\Information\Hackers;
use DevKabir\Chokidar\Information\FailedLogin;
use DevKabir\Chokidar\Information\LoginAttempts;

/**
 * Class Plugin
 *
 * @package DevKabir\Chokidar
 */
final class Plugin {

	public const ADMIN_IP       = 'chokidar_admin_ip';
	public const USERNAMES      = 'chokidar-usernames';
	public const USER_AT_RISK   = 'chokidar-users-at-risk';
	public const HACKER         = 'chokidar-hackers';
	public const TRANSIENT_TIME = 604800;

	/**
	 * It will load all codes need for chokidar run properly.
	 * - store admin ip to give a pass on every visit.
	 *
	 * @return void
	 */
	public static function activate(): void {
		add_action('init', function (){
			load_plugin_textdomain( 'chokidar', false, plugin_dir_path(__DIR__) . '/languages' );
		});
		set_transient( self::ADMIN_IP, sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '-' ) ) );
	}

	/**
	 * It will load all codes need for chokidar run properly.
	 * - store admin ip to give a pass on every visit.
	 *
	 * @return void
	 */
	public static function deactivate(): void {

	}

	/**
	 * It will load all codes need for chokidar run properly.
	 * - store admin ip to give a pass on every visit.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		// Removing all cache.
		delete_transient( self::ADMIN_IP );
		Bots::destroy();
		Device::destroy();
		FailedLogin::destroy();
		Hackers::destroy();
		LoginAttempts::destroy();
		Ips::destroy();
		Visits::destroy();
	}

	/**
	 * It will load all classes based on user's screen.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( is_admin() ) {
			// 1. Initiate admin menu and pages.
			Menu::init();
			add_action( 'admin_enqueue_scripts', function () {
				wp_enqueue_style( 'chokidar', plugins_url( 'assets/admin.css', __DIR__ ) );
			} );
			// 2.Initiate Dashboard Widgets.
		} else {
			// 1. collect information
			$information = new Information();
			$device      = $information->device;
			$ip          = $information->ip;
			$page        = $information->page_url;
			$referer     = $information->referer;
			// 2. Lookup for hackers. and stop them at door.
			$hackers    = Hackers::all();
			$hacker_ips = array_keys( $hackers );
			if ( in_array( $ip, $hacker_ips, true ) ) {
				wp_die( 0 );
			}
			// 3. Exclude admin from watching.
			if ( get_transient( self::ADMIN_IP ) === $ip ) {
				return;
			}
			// 4. Exclude allowed lists
			if ( self::ignore_page( $page ) ) {
				return;
			}
			// 5. Exclude wordpress bots.
			if ( $device->isBot() && 'WordPress' === $device->getBot()['name'] ) {
				return;
			}
			// 6. Track visiting pages.
			Visits::set( $ip, $information->method, $page, $referer );
			if ( $device->isBot() ) {
				// 7. Track bots.
				Bots::set( $page, $device->getBot() );
			} else {
				// 8. Track devices info.
				Device::set( $ip, $device );
				// 9. Track login page.
				Login::track( $ip );
			}
		}

	}

	/**
	 * If the URL contains any of the words in the array, return true
	 *
	 * @param string $url The URL of the page being loaded.
	 *
	 * @return bool A boolean value.
	 */
	private static function ignore_page( string $url ): bool {
		// Array of words to match against.
		$words = array( 'wp-cron' );

		// Combine the words into a regular expression pattern.
		$pattern = '/' . implode( '|', $words ) . '/i';

		// Use preg_match to check if the pattern matches the string.
		return preg_match( $pattern, $url );

	}
}
