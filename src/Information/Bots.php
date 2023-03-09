<?php
/**
 * It will hold bots information, till 24 hour.
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
 * Class Bots
 *
 * @package    DevKabir\Chokidar
 */
final class Bots {

	use Transient;

	private const KEY = 'chokidar_bots';

	/**
	 * It takes a page URL and a bot array, and adds the current time to the bot's array of page URLs
	 * Data Structure:
	 * [
	 *      bot name => [
	 *          page url => [
	 *              time of visits
	 *          ]
	 *      ]
	 * ]
	 *
	 * @param string $page_url The URL of the page that the bot is visiting.
	 * @param array  $bot      bot data.
	 */
	public static function set( string $page_url, array $bot ): void {
		$bots                                = self::all();
		$bots[ $bot['name'] ][ $page_url ][] = current_time( 'mysql' );
		set_transient( self::KEY, $bots, Plugin::TRANSIENT_TIME );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$bots = self::all();
		$data = array();
		foreach ( $bots as $name => $visits ) {
			$holder = array( 'name' => $name );
			foreach ( $visits as $page => $hits ) {
				$holder['page'] = $page;
				$holder['hits'] = count( $hits );
				$data[]         = $holder;
			}
		}

		return self::sort_by( $data, 'hits' );
	}
}
