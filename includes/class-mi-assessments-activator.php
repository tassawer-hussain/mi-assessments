<?php
/**
 * Fired during plugin activation
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/includes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// create listener page.
		self::create_listener_page();

		// create tables in database.
		self::create_database_tables();

		// Schedule cron events.
		self::schdule_cron_job();
	}

	/**
	 * Create listner page on plugin activation.
	 *
	 * @return void
	 */
	public static function create_listener_page() {

		// return if current user can't install plugin.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$mi_error_log           = array();
		$mi_error_log['ACTION'] = 'Creating listener page.';

		global $wpdb;
		$is_page_exist = $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'listener'", 'ARRAY_A' ); // WPCS: cache ok. db call ok.

		if ( null === $is_page_exist ) {

			$mi_error_log['status'] = 'Listener page not exist already.';

			$current_user = wp_get_current_user();

			$page = array(
				'post_title'   => __( 'Listener', 'mi-assessments' ),
				'post_content' => '[assessment_listener]',
				'post_status'  => 'publish',
				'post_author'  => $current_user->ID,
				'post_type'    => 'page',
			);

			$post_id = wp_insert_post( $page );
			update_option( 'listener_page_id', $post_id );

			$mi_error_log['message'] = 'Listener page created and Page ID is ' . $post_id . '. Updated listener_page_id option';
		}

		$mi_error_log['message'] = 'Listener page exist already.';

		Mi_Error_Log::put_error_log( $mi_error_log, 'array', 'primary' );
	}

	/**
	 * Function to create tables.
	 *
	 * @since   1.0.0
	 *
	 * @return void
	 */
	public static function create_database_tables() {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// create users limit table.
		$table_name = $wpdb->prefix . 'tti_users_limit';

		$query = "CREATE TABLE IF NOT EXISTS $table_name (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(20),
			`email` varchar(200),
			`group_id` varchar(200),
			`limits` int(20),
			`data_link` varchar(255),
			PRIMARY KEY  (`ID`),
			KEY user_id (user_id),
			KEY email (email),
			KEY data_link (data_link)
		) $charset_collate;";
		dbDelta( $query );

		// create assessments table.
		$table_name = $wpdb->prefix . 'assessments';

		$query = "CREATE TABLE IF NOT EXISTS $table_name (
			`id` mediumint(9) NOT NULL AUTO_INCREMENT,
			`user_id` int(20),
			`first_name` varchar(255),
			`last_name` varchar(255),
			`email` varchar(255),
			`service_location` varchar(255),
			`account_id` varchar(255),
			`link_id` varchar(255),
			`report_id` int(20),
			`api_token` varchar(255),
			`gender` varchar(255),
			`company` varchar(255),
			`position_job` varchar(255),
			`password` varchar(255),
			`created_at` varchar(255),
			`updated_at` varchar(255),
			`status` int(20),
			`assessment_result` longtext,
			`selected_all_that_apply` longtext,
			`version` int(11),
			`assess_type` mediumint(9),
			PRIMARY KEY  (`id`)
		) $charset_collate;";
		dbDelta( $query );

		// Creating indexes.
		$index_user = $wpdb->get_row( $wpdb->prepare( 'SHOW INDEX FROM %s WHERE Key_name = %s', $table_name, 'user_id' ) );  // WPCS: cache ok. db call ok.
		if ( null === $index_user ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %s ADD INDEX `user_id` (`user_id`)', $table_name ) );
		}

		$index_email = $wpdb->get_row( $wpdb->prepare( 'SHOW INDEX FROM %s WHERE Key_name = %s', $table_name, 'email' ) );  // WPCS: cache ok. db call ok.
		if ( null === $index_email ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %s ADD INDEX `email` (`email`)', $table_name ) );
		}

		$index_password = $wpdb->get_row( $wpdb->prepare( 'SHOW INDEX FROM %s WHERE Key_name = %s', $table_name, 'password' ) );  // WPCS: cache ok. db call ok.
		if ( null === $index_password ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %s ADD INDEX `password` (`password`)', $table_name ) );
		}

		$index_link_id = $wpdb->get_row( $wpdb->prepare( 'SHOW INDEX FROM %s WHERE Key_name = %s', $table_name, 'link_id' ) );  // WPCS: cache ok. db call ok.
		if ( null === $index_link_id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %s ADD INDEX `link_id` (`link_id`)', $table_name ) );
		}

		$index_version = $wpdb->get_row( $wpdb->prepare( 'SHOW INDEX FROM %s WHERE Key_name = %s', $table_name, 'version' ) );  // WPCS: cache ok. db call ok.
		if ( null === $index_version ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %s ADD INDEX `version` (`version`)', $table_name ) );
		}

	}

	/**
	 * Schdule the CRON job.
	 *
	 * @since  1.0.0
	 */
	public static function schdule_cron_job() {

		// Schedule 15 minutes cron.
		if ( ! wp_next_scheduled( 'assessments_status_checker' ) ) {
			wp_schedule_event( time(), 'twenty_four_hours_ttsi_cron', 'assessments_status_checker' );
		}

		// Schedule 3 days cron.
		if ( ! wp_next_scheduled( 'assessments_pdf_files_checker' ) ) {
			wp_schedule_event( time(), 'three_days_ttsi_cron', 'assessments_pdf_files_checker' );
		}

	}

}
