<?php
/**
 * It will hold Device information, till 24 hour.
 *
 * @package    DevKabir\Chokidar
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Information;

/* This is a security measure to prevent direct access to the plugin file. */

use DevKabir\Chokidar\Plugin;
use DeviceDetector\DeviceDetector;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class Device
 *
 * @package DevKabir\Chokidar
 */
final class Device {

	use Transient;

	public const KEY = 'chokidar-devices';

	/**
	 * It sets the transient.
	 * Data Structure:
	 * [
	 *      device name => [
	 *          model of device => [
	 *              operating system => [
	 *                  internet browser => [
	 *                      visiting time => visitor's ip
	 *                  ]
	 *              ]
	 *          ]
	 *      ]
	 * ]
	 *
	 * @param string         $ip     The IP address of the user.
	 * @param DeviceDetector $device The device name, e.g. "iPhone".
	 */
	public static function set( string $ip, DeviceDetector $device ): void {
		$devices                                                                                                                                           = self::all();
		$devices[ $device->getDeviceName() ][ $device->getModel() ][ $device->getOs( 'name' ) ][ $device->getClient( 'name' ) ][ current_time( 'mysql' ) ] = $ip;
		set_transient( self::KEY, $devices, Plugin::TRANSIENT_WEEK );
	}

	/**
	 * It takes the data from the transient and turns it into a format that's easier to work with
	 *
	 * @return array An array of arrays.
	 */
	public static function prepare(): array {
		$collections = self::all();
		$data        = array();
		foreach ( $collections as $device => $models ) {
			$device = '' === $device ? 'Unknown' : $device;
			$holder = array( 'device' => $device );
			foreach ( $models as $model => $os ) {
				$holder['model'] = '' === $model ? 'Unknown' : $model;
				foreach ( $os as $os_name => $browsers ) {
					$holder['os'] = $os_name;
					foreach ( $browsers as $browser => $hits ) {
						$holder['browser'] = $browser;
						$holder['hits']    = count( $hits );
						$data[]            = $holder;
					}
				}
			}
		}

		return self::sort_by( $data, 'hits' );
	}
}
