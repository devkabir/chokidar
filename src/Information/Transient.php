<?php
/**
 * Summary (no period for file headers)
 *
 * @package    DevKabir\Chokidar
 * @subpackage Component
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

trait Transient {

	/**
	 * It returns specific ip information from all ips
	 *
	 * @param string $ip The IP address to check.
	 *
	 * @return false|array The value of the key in the array.
	 */
	public static function get( string $ip ) {
		$ips = self::all();
		if ( array_key_exists( $ip, $ips ) ) {
			return $ips[ $ip ];
		}

		return false;
	}

	/**
	 * It returns all IPs from the transient cache
	 *
	 * @return array An array of IP addresses.
	 */
	public static function all(): array {
		$ips = get_transient( self::KEY );
		if ( ! $ips ) {
			return array();
		}

		return $ips;
	}

	/**
	 * It deletes the transient
	 */
	public static function destroy(): void {
		delete_transient( self::KEY );
	}

	/**
	 * It formats the date to a specific format.
	 *
	 * @param string $date The date to format.
	 *
	 * @return string The date in the format of d-m-Y g:i A
	 */
	public static function format_date( string $date ): string {
		return date_i18n( 'd-m-Y g:i A', strtotime( $date ) );
	}


	/**
	 * It sorts an array of arrays by a key
	 *
	 * @param array  $data The array to sort.
	 * @param string $key  The key to sort by.
	 *
	 * @return array The array is being sorted by the key.
	 */
	public static function sort_by( array $data, string $key ): array {
		usort( $data, static function ( $a, $b ) use ( $key ) {
			return $b[ $key ] - $a[ $key ];
		} );

		return $data;
	}
}
