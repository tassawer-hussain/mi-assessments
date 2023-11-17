<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ministryinsights.com/
 * @since             1.0.0
 * @package           Mi_Assessments
 *
 * @wordpress-plugin
 * Plugin Name:       MI Assessments
 * Plugin URI:        https://ministryinsights.com/
 * Description:       Eliminate your people's problems with our assessment tools, management techniques, and global network of experts.
 * Version:           2.0.0
 * Author:            Ministry Insights
 * Author URI:        https://ministryinsights.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mi-assessments
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MI_ASSESSMENTS_VERSION', '2.0.0' );

/**
 * Define plugin wise constant.
 */
if ( ! defined( 'MI_ADMIN_PATH' ) ) {
	define( 'MI_ADMIN_PATH', plugin_dir_path( __FILE__ ) . 'admin/' );
}

if ( ! defined( 'MI_ADMIN_URL' ) ) {
	define( 'MI_ADMIN_URL', plugin_dir_url( __FILE__ ) . 'admin/' );
}

if ( ! defined( 'MI_PUBLIC_PATH' ) ) {
	define( 'MI_PUBLIC_PATH', plugin_dir_path( __FILE__ ) . 'public/' );
}

if ( ! defined( 'MI_PUBLIC_URL' ) ) {
	define( 'MI_PUBLIC_URL', plugin_dir_url( __FILE__ ) . 'public/' );
}

if ( ! defined( 'MI_INCLUDES_PATH' ) ) {
	define( 'MI_INCLUDES_PATH', plugin_dir_path( __FILE__ ) . 'includes/' );
}

if ( ! defined( 'MI_INCLUDES_URL' ) ) {
	define( 'MI_INCLUDES_URL', plugin_dir_url( __FILE__ ) . 'includes/' );
}

if ( ! defined( 'MI_BASE_NAME' ) ) {
	define( 'MI_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'MI_ERROR_LOG_PATH' ) ) {
	define( 'MI_ERROR_LOG_PATH', plugin_dir_path( __FILE__ ) . 'error-log/' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mi-assessments-activator.php
 */
function activate_mi_assessments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mi-assessments-activator.php';
	Mi_Assessments_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mi-assessments-deactivator.php
 */
function deactivate_mi_assessments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mi-assessments-deactivator.php';
	Mi_Assessments_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mi_assessments' );
register_deactivation_hook( __FILE__, 'deactivate_mi_assessments' );

/**
 * Add custom cron intervals.
 *
 * @param array $schedules Array of schedules intervals.
 * @return array
 */
function mi_assessments_custom_cron_intervals( $schedules ) {

	// Set 24 hours interval.
	if ( ! isset( $schedules['twenty_four_hours_ttsi_cron'] ) ) {
		$schedules['twenty_four_hours_ttsi_cron'] = array(
			'interval' => 24 * 60 * 60,
			'display'  => __( 'Every Day', 'mi-assessments' ),
		);
	}

	// Set 3 days interval.
	if ( ! isset( $schedules['three_days_ttsi_cron'] ) ) {
		$schedules['three_days_ttsi_cron'] = array(
			'interval' => 3 * 24 * 60 * 60,
			'display'  => __( 'Every 3 Days', 'mi-assessments' ),
		);
	}

	return $schedules;

}
add_filter( 'cron_schedules', 'mi_assessments_custom_cron_intervals' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mi-assessments.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mi_assessments() {

	$plugin = new Mi_Assessments();
	$plugin->run();

}
run_mi_assessments();

/**
 * Function use for testing purpose.
 *
 * @return void
 */
function checking_in_head() {

	// $user_data  = wp_get_current_user();
	// mipd( $user_data );
}
add_action( 'wp_head', 'checking_in_head' );

