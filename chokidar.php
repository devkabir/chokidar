<?php
/**
 * Chokidar
 *
 * @package           DevKabir\Chokidar
 * @author            Dev Kabir
 * @copyright         2023 Dev Kabir
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Chokidar
 * Plugin URI:        https://devkabir.shop/plugins/website-security-and-visitor-insights-by-chokidar/
 * Description:       Boost Your Website Performance and Security with Top 10 Visitor Insights!
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Dev Kabir
 * Author URI:        https://devkabir.shop/
 * Text Domain:       chokidar
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * This is a security measure to prevent direct access to the plugin PHP files.
 */


if ( ! defined( 'WPINC' ) ) {
	exit;
}
/**
 * Loading the classes from the vendor folder.
 */
require_once __DIR__ . '/vendor/autoload.php';

use DevKabir\Chokidar\Plugin;

/**
 * Codes to run when the plugin is activated.
 */
register_activation_hook( __FILE__, array( Plugin::class, 'activate' ) );

/**
 * Codes to run when the plugin is deactivated.
 */
register_deactivation_hook( __FILE__, array( Plugin::class, 'deactivate' ) );

/**
 * Codes to run when the plugin is uninstalled.
 */
register_uninstall_hook( __FILE__, array( Plugin::class, 'uninstall' ) );

/**
 * Run chokidar run.
 */
Plugin::init();