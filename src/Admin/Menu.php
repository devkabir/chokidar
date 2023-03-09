<?php
/**
 * Summary (no period for file headers)
 *
 * @package    DevKabir\Chokidar\Admin
 * @subpackage Component
 * @since      1.0.0
 */

namespace DevKabir\Chokidar\Admin;

/* This is a security measure to prevent direct access to the plugin file. */

use DOMDocument;
use DevKabir\Chokidar\Plugin;
use DevKabir\Chokidar\Information\Ips;
use DevKabir\Chokidar\Information\Bots;
use DevKabir\Chokidar\Information\Visits;
use DevKabir\Chokidar\Information\Device;
use DevKabir\Chokidar\Information\Hackers;
use DevKabir\Chokidar\Information\FailedLogin;
use DevKabir\Chokidar\Information\LoginAttempts;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Class Menu
 *
 * @package DevKabir\Chokidar\Admin
 */
final class Menu {

	/**
	 * It adds a widget to the dashboard.
	 */
	public static function init(): void {
		add_action( 'admin_menu', function () {
			// Add an item to the menu.
			add_menu_page(
				__( 'Chokidar', 'chokidar' ),
				__( 'Chokidar', 'chokidar' ),
				'manage_options',
				'chokidar',
				array( self::class, 'render' ),
				'dashicons-welcome-view-site'
			);
		} );

		add_action( 'plugins_loaded', array( self::class, 'load_usernames' ) );
	}

	/**
	 * It adds a widget to the dashboard
	 */
	public static function render(): void {
		wp_enqueue_script( 'dashboard' );
		add_thickbox();
		$screen      = get_current_screen();
		$columns     = absint( $screen->get_columns() );
		$columns_css = '';

		if ( $columns ) {
			$columns_css = " columns-$columns";
		}
		self::widgets();
		?>
        <div class="wrap">
            <h1><?php echo esc_html( __( 'Chokidar', 'chokidar' ) ); ?></h1>
            <h3>
                <strong><?php echo esc_html( __( 'Boost Your Website Performance and Security with Top 10 Visitor Insights!', 'chokidar' ) ); ?></strong>
            </h3>

            <div id="dashboard-widgets-wrap">

                <div id="dashboard-widgets" class="metabox-holder<?php echo $columns_css; ?>">
                    <div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $screen->id, 'normal', '' ); ?>
                    </div>
                    <div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $screen->id, 'side', '' ); ?>
                    </div>
                </div>

                <div class="clear"></div>
            </div><!-- dashboard-widgets-wrap -->

        </div><!-- wrap -->


		<?php
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
	}

	/**
	 * It adds a meta box to the dashboard for each of the six tables in the database
	 */
	private static function widgets(): void {
		$screen = get_current_screen();
		$boxes  = [
			'ips'             => __( 'IPs', 'chokidar' ),
			'users_at_risk'   => __( 'Users at risk', 'chokidar' ),
			'devices'         => __( 'Devices', 'chokidar' ),
			'bots'            => __( 'Bots', 'chokidar' ),
			'attempts'        => __( 'Attempts', 'chokidar' ),
			'failed_attempts' => __( 'Failed Attempts', 'chokidar' ),
			'visits'          => __( 'Visits', 'chokidar' ),
			'hackers'         => __( 'Hackers', 'chokidar' ),
		];
		foreach ( $boxes as $box => $title ) {
			$context      = in_array( $box, array(
				'attempts',
				'failed_attempts',
				'visits'
			), true ) ? 'normal' : 'side';
			$title_prefix = in_array( $box, array(
				'hackers',
				'ips'
			), true ) ? __( 'Latest 10 ', 'chokidar' ) : __( 'Top 10 ', 'chokidar' );
			add_meta_box(
				'chokidar-latest-' . $box,
				$title_prefix . $title,
				array( self::class, 'render_' . $box ),
				$screen,
				$context,
			);
		}
	}

	/**
	 * It takes the last 10 hackers from the database and prints them out in a pretty format
	 */
	public static function render_hackers(): void {
		$data = array_slice( Hackers::prepare(), 0, 10 );
		echo self::generateTable( $data );
	}

	/**
	 * Generate HTML table from array
	 *
	 * @param array $data Data to convert into table.
	 *
	 * @return string
	 */
	public static function generateTable( array $data ): string {

		$table = new DOMDocument();

		// Create table element.
		$tableElement = $table->createElement( 'table' );
		$tableElement->setAttribute( 'class', 'wp-list-table widefat fixed striped table-view-list' );
		// Create table header.
		$thead = $table->createElement( 'thead' );
		$tr    = $table->createElement( 'tr' );
		if ( count( $data ) !== 0 ) {
			foreach ( array_keys( $data[0] ) as $index => $key ) {
				$class = 'manage-column';
				if ( $index === 0 ) {
					$class .= ' column-primary';
				}
				$name = ucwords( str_replace( '_', ' ', $key ) );
				$th   = $table->createElement( 'th', $name );
				$th->setAttribute( 'class', $class );
				$th->setAttribute( 'scope', 'col' );
				$tr->appendChild( $th );
			}
			$thead->appendChild( $tr );
			$tableElement->appendChild( $thead );
		}


		// Create table body.
		$tbody = $table->createElement( 'tbody' );
		if ( count( $data ) !== 0 ) {
			foreach ( $data as $row ) {
				$tr = $table->createElement( 'tr' );

				foreach ( $row as $value ) {
					$td = $table->createElement( 'td', $value );
					$tr->appendChild( $td );
				}

				$tbody->appendChild( $tr );
			}
		} else {
			$tr = $table->createElement( 'tr' );
			$td = $table->createElement( 'td', __( "We couldn't find any records!", 'chokidar' ) );
			$tr->appendChild( $td );
			$tbody->appendChild( $tr );
		}
		$tableElement->appendChild( $tbody );
		$table->appendChild( $tableElement );

		return $table->saveHTML();
	}

	/**
	 * It gets the last 10 visits from the database and prints them out in a pretty format
	 */
	public static function render_visits(): void {
		$data = array_slice( Visits::prepare(), 0, 10 );
		echo self::generateTable( $data );
	}

	/**
	 * It takes the last 10 login attempts and prints them out in a pretty format
	 */
	public static function render_attempts(): void {
		$data = array_slice( LoginAttempts::prepare(), - 10 );
		echo self::generateTable( $data );
	}

	/**
	 * It takes the last 10 failed login attempts and prints them out in a pretty format
	 */
	public static function render_failed_attempts(): void {
		$data = array_slice( FailedLogin::prepare(), 0, 10 );
		echo self::generateTable( $data );
	}

	/**
	 * It takes the last 10 bots from the database and prints them out in a pretty format
	 */
	public static function render_bots(): void {
		$data = array_slice( Bots::prepare(), 0, 10 );
		echo self::generateTable( $data );
	}

	/**
	 * It gets the last 10 IPs from the database and prints them out in a pretty format
	 */
	public static function render_ips(): void {
		$data = array_slice( Ips::prepare(), 0, 10 );
		echo self::generateTable( $data );
	}

	/**
	 * It gets the last 10 devices from the database and prints them out in a pretty format
	 */
	public static function render_devices(): void {
		$prepare = Device::prepare();
		$data    = array_slice( $prepare, 0, 10 );
		echo self::generateTable( $data );
	}

	/**
	 * It loads all the usernames from the database into a transient
	 */
	public static function load_usernames(): void {
		global $wpdb;
		$usernames = $wpdb->get_col( "Select user_login From $wpdb->users" );
		set_transient( Plugin::USERNAMES, $usernames );
		$user_logins = $wpdb->get_results(
			"SELECT user_login as user_name FROM {$wpdb->users} WHERE user_login = user_nicename AND user_nicename = display_name",
			ARRAY_A
		);

		delete_transient(Plugin::USER_AT_RISK);
		// Check if the transient exists and update it if any of the users in the list update their profile.
		if ( false === ( $users_at_risk = get_transient( Plugin::USER_AT_RISK ) ) || ! is_array( $users_at_risk ) ) {
			// Transient does not exist or is invalid, set a new transient.
			set_transient( Plugin::USER_AT_RISK, $user_logins, Plugin::TRANSIENT_TIME );
		} else {
			// Check if any of the users in the list have updated their profile.
			foreach ( $users_at_risk as $user_login ) {
				$user = get_user_by( 'login', $user_login );
				if ( $user && $user->has_prop( 'last_updated' ) ) {
					$last_updated = $user->get( 'last_updated' );
					if ( $last_updated > time() ) {
						// User has updated their profile since the last check, update the transient.
						set_transient( Plugin::USER_AT_RISK, $user_logins, Plugin::TRANSIENT_TIME );
						$updated = true;
						break;
					}
				}
			}
		}
	}
	/**
	 * It gets the last 10 user from the database and prints them out in a pretty format
	 */
	public static function render_users_at_risk():void {
		$data = get_transient( Plugin::USER_AT_RISK ) ?? array();
		$data = array_slice( $data, 0, 10 );
		echo self::generateTable( $data );
	}

}
