<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ministryinsights.com/
 * @since      1.0.0
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mi_Assessments
 * @subpackage Mi_Assessments/public
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_Assessments_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if ( ! is_admin() ) {

			// default style sheet.
			wp_register_style(
				$this->plugin_name,
				MI_PUBLIC_URL . 'css/mi-assessments-public.css',
				array(),
				generate_random_string(),
				'all'
			);

			// sweetalert style.
			wp_register_style(
				$this->plugin_name . '-sweetalert',
				'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.css',
				array(),
				generate_random_string(),
				'all'
			);

			// datatable style.
			wp_register_style(
				'mi-assessments-datatable',
				'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css',
				array(),
				generate_random_string(),
				'all'
			);

			// responsive datatable style.
			wp_register_style(
				'mi-assessments-responsive-datatable',
				'https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css',
				array(),
				generate_random_string(),
				'all'
			);

		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// default script file.
		wp_register_script(
			$this->plugin_name,
			MI_PUBLIC_URL . 'js/mi-assessments-public.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		// Localized the default script.
		wp_localize_script(
			$this->plugin_name,
			'mi_assessment_public_ajax_obj',
			array(
				'ajaxurl'         => admin_url( 'admin-ajax.php' ),
				'mi_public_nonce' => wp_create_nonce( 'mi_assessment_public_nonce' ),
			)
		);

		// sweetalert script file.
		wp_register_script(
			$this->plugin_name . '-sweetalert',
			'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.min.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		wp_register_script(
			'mi-assessments-datatable',
			'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		wp_register_script(
			'mi-assessments-responsive-datatable',
			'https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js',
			array( 'jquery', 'mi-assessments-datatable' ),
			generate_random_string(),
			true
		);

		wp_register_script(
			'mi-assessments-onsite',
			'https://justrespond.com/ttisi-survey-loader.js',
			array(),
			generate_random_string(),
			false
		);

	}

	/**
	 * Register the functionality to run on init hook.
	 *
	 * @since    2.0.0
	 */
	public function init_hook_tasks() {

		// Include LearnDash Group Registration Admin Functionality class.
		require_once MI_INCLUDES_PATH . 'classes/ld-group-registration/class-mi-ldgr-admin-functionality.php';

		// Include LearnDash Group Registration Public Functionality class.
		require_once MI_INCLUDES_PATH . 'classes/ld-group-registration/class-mi-ldgr-public-functionality.php';

		// Include LearnDash Group Registration Group Leader Email class.
		require_once MI_INCLUDES_PATH . 'classes/ld-group-registration/class-mi-ldgr-group-leader-emails.php';

		// Include LearnDash Group Registration Seat Rollback class.
		require_once MI_INCLUDES_PATH . 'classes/ld-group-registration/class-mi-ldgr-rollback-group-seat.php';

		// Include Shortcode class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-assessments-shortcodes.php';

		// Include Take assessment class.
		require_once MI_INCLUDES_PATH . 'classes/class-mi-take-assessment.php';

		// initilize the class - Display retake assessment settings and save it. LearnDash Group Registration.
		$mi_ldgr_admin = new Mi_LDGR_Admin_Functionality();

		// initilize the class - LearnDash Group Registration Public Face Customization.
		$mi_ldgr_public = new Mi_LDGR_Public_Functionality( $this->plugin_name, $this->version );

		// initilize the class - LearnDash Group Registration Group Leader Email Customization.
		$mi_ldgr_groupleader_email = new Mi_LDGR_Group_Leader_Emails();

		// rollback group seat if user has not started the assessment/course.
		$mi_ldgr_seat_rollback = new Mi_LDGR_Rollback_Group_Seat();

		// initilize the class - MI Cron Jobs.
		$mi_cron_job = new Mi_Cron_Jobs();

		// initilize the class - MI Assessments Shortcodes.
		$mi_shortcodes = new Mi_Assessments_Shortcodes( $this->plugin_name, $this->version );

		// initilize the class - MI Take Assessment.
		$mi_take_assessment = new Mi_Take_Assessment();

		// filter Global $_GET variable.
		$_get_data = filter_input_array( INPUT_GET );

		// Process assessment PDF files.
		if ( isset( $_get_data['tti_print_consolidation_report'] ) && '1' === $_get_data['tti_print_consolidation_report'] ) {

			// Initiate process.
			require_once MI_INCLUDES_PATH . 'pdf/class-mi-assessments-pdf-report.php';

			$print_button = new Mi_Assessments_Pdf_Report();
			$assess_id    = sanitize_text_field( $_get_data['assess_id'] );
			$report_type  = sanitize_text_field( $_get_data['report_type'] );
			$print_button->download_report( $assess_id, $report_type );

			wp_die();
		}

	}

	/**
	 * Function to retaking assessment ajax action process.
	 *
	 * @since    1.5.1
	 */
	public function mi_grant_user_retake_assessments() {

		// check nonce.
		check_ajax_referer( 'mi-assessments-ldgr', 'nonce' );

		if ( isset( $_POST['user_id'] ) ) {
			$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );
		}

		if ( isset( $_POST['email'] ) ) {
			$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
		}

		if ( isset( $_POST['group_id'] ) ) {
			$group_id = sanitize_text_field( wp_unslash( $_POST['group_id'] ) );
		}

		if ( isset( $_POST['link_id'] ) ) {
			$link_id = sanitize_text_field( wp_unslash( $_POST['link_id'] ) );
		}

		if ( isset( $_POST['group_leader_id'] ) ) {
			$group_leader_id = sanitize_text_field( wp_unslash( $_POST['group_leader_id'] ) );
		}

		// Include LearnDash Group Registration Retake Assessment class.
		require_once MI_INCLUDES_PATH . 'classes/ld-group-registration/class-mi-ldgr-retake-assessment.php';

		$retake_assessment = new Mi_LDGR_Retake_Assessment( $user_id, $email, $group_id, $link_id, $group_leader_id );
		$retake_assessment->retake_assessment_process();

	}

	/**
	 * Process retake assessment on self purchase product.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function retake_assessment_on_self_purchase( $order_id ) {

		// retrive order object.
		$order = wc_get_order( $order_id );
		$user  = $order->get_user();

		// Include LearnDash Group Registration Retake Assessment class.
		require_once MI_INCLUDES_PATH . 'classes/ld-group-registration/class-mi-ldgr-retake-assessment.php';

		$retake_assessment = new Mi_LDGR_Retake_Assessment( $user->ID, $user->user_email, 0, '', 0 );
		$retake_assessment->process_retake_assessment_on_self_purchase( $order );

	}

}
