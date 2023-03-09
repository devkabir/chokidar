<?php
/**
 * It will extract user information from server and store through all plugin classes.
 *
 * @package    DevKabir\Chokidar\Web
 * @subpackage Component
 * @since      1.0.0
 */

namespace DevKabir\Chokidar;

/* This is a security measure to prevent direct access to the plugin file. */
if ( ! defined( 'WPINC' ) ) {
	exit;
}


use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DevKabir\Chokidar\Information\Ips;

/**
 * Class Information
 *
 * @property DeviceDetector device
 * @property array          location
 * @property string         processor
 * @property string         agent
 * @property string         referer
 * @property string         ip
 * @property string         page_url
 * @property string         method
 * @property int            user_id
 * @package DevKabir\Chokidar\Web
 */
final class Information {

	/**
	 * Info constructor.
	 */
	public function __construct() {
		$this->user_id   = get_current_user_id();
		$this->method    = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '-' ) );
		$this->page_url  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '-' ) );
		$this->ip        = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '-' ) );
		$this->referer   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ?? '-' ) );
		$this->agent     = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '-' ) );
		$this->processor = sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ?? '-' ) );
		$this->location  = $this->get_user_location( $this->ip );
		$this->device    = $this->detect_device( $this->agent );
	}

	/**
	 * It takes an IP address as a string, and returns the country of the IP address as a string
	 *
	 * @param string $ip IP address of visitor.
	 *
	 * @return array location info of user
	 */
	private function get_user_location( string $ip ): array {
		$information = Ips::get( $ip );
		if ( ! empty( $information ) && is_array( $information ) ) {
			return $information;
		}

		$response = wp_remote_get( 'https://ip2c.org/' . $ip );
		$response = wp_remote_retrieve_body( $response );
		if ( '1' === $response[0] ) {
			$country     = explode( ';', $response )[3];
			$information = array( 'country' => $country );
			Ips::set( $ip, $information );
		} else {
			$information = array( 'country' => 'Not Found' );
		}

		return $information;
	}

	/**
	 * It detects the devices and bots
	 *
	 * @param string $agent The user agent string.
	 *
	 * @return DeviceDetector A DeviceDetector object.
	 */
	private function detect_device( string $agent ): DeviceDetector {
		$client_hints = ClientHints::factory( $_SERVER );
		$dd           = new DeviceDetector( $agent, $client_hints );
		$dd->parse();

		return $dd;
	}

}
