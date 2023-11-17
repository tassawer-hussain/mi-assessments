<?php
/**
 * Class to handle error log.
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 * Class to handle error log.
 *
 * This class defines all code necessary to log the error handling.
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_LDGR_Public_Functionality {

	/**
	 * Define the constructor
	 *
	 * @since    2.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Enqueue public scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'registered_public_styles_scripts' ) );

		// Override the Template which is sued to display the list of Groups on Enrolled User page.
		add_filter( 'ldgr_filter_template_path', array( $this, 'ttisi_override_the_group_list_template' ), 99, 2 );

		// Update group email headers.
		add_filter( 'ldgr_group_email_headers', array( $this, 'mi_assessment_set_ldgr_group_email_headers' ), 99, 2 );

		// Update the Group page tabs.
		add_filter( 'ldgr_filter_group_registration_tab_headers', array( $this, 'mi_assessments_group_registration_tab_headers' ), 99, 2 );

		// Update the group page tabs content.
		add_filter( 'ldgr_filter_group_registration_tab_contents', array( $this, 'mi_assessments_group_registration_tab_contents' ), 99, 2 );

		// update the email sending settings to true on sub-group creation.
		add_filter( 'ldgr_filter_sub_group_update_result', array( $this, 'set_sub_group_leader_email_settings' ), 99, 2 );

		// Save group email settings.
		add_action( 'wp_ajax_tti_group_save_settings', array( $this, 'set_group_leader_email_settings' ) );
		add_action( 'wp_ajax_nopriv_tti_group_save_settings', array( $this, 'set_group_leader_email_settings' ) );

		// enable/disable new user enrollment email base on group settings and save time when user assigned an assessment.
		add_filter( 'wdm_group_enrollment_email_status', array( $this, 'mi_assessments_group_enrollment_email_status' ), 10, 2 );

		// Update Labels for product purchase using "LearnDash Group Registration".
		add_filter( 'wdm_gr_single_label', array( $this, 'mi_update_wdm_gr_single_label' ), 99, 1 );
		add_filter( 'wdm_gr_group_label', array( $this, 'mi_update_wdm_gr_group_label' ), 99, 1 );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function registered_public_styles_scripts() {

		// Deques the LearnDash Group Registration WDM File and register modified version.
		wp_dequeue_script( 'wdm_remove_js' );
		wp_deregister_script( 'wdm_remove_js' );
		wp_register_script(
			'wdm_remove_js',
			MI_PUBLIC_URL . 'js/wdm_remove.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		// default style sheet.
		wp_register_style(
			'mi-assessments-learndash-group-registration',
			MI_PUBLIC_URL . 'css/mi-assessments-learndash-group-registration.css',
			array(),
			generate_random_string(),
			'all'
		);

		// default script file.
		wp_register_script(
			'mi-assessments-learndash-group-registration',
			MI_PUBLIC_URL . 'js/mi-assessments-learndash-group-registration.js',
			array( 'jquery' ),
			generate_random_string(),
			true
		);

		wp_localize_script(
			'mi-assessments-learndash-group-registration',
			'mi_assessments_ldgr',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'siteurl'      => site_url(),
				'mildgr_nonce' => wp_create_nonce( 'mi-assessments-ldgr' ), // Create a nonce for security.
				'menu_display' => __( 'Show _MENU_ Users', 'tti-platform' ),
				'zeroRecords'  => __( 'Nothing found - sorry', 'tti-platform' ),
				'info'         => __( 'Showing page _PAGE_ of _PAGES_', 'tti-platform' ),
				'infoEmpty'    => __( 'No records available', 'tti-platform' ),
				'infoFiltered' => __( '(filtered from _MAX_ total records)', 'tti-platform' ),
				'Search'       => __( 'Search', 'tti-platform' ),
				'First'        => __( 'First', 'tti-platform' ),
				'Previous'     => __( 'Previous', 'tti-platform' ),
				'Last'         => __( 'Last', 'tti-platform' ),
				'Next'         => __( 'Next', 'tti-platform' ),
				'limit_ends'   => __( 'This group don\'t have any user registration left. Please buy more registrations before allow user to retake assessment.', 'tti-platform' ),
			)
		);

	}

	/**
	 * Override the Templates
	 *
	 * @param string $template_path Template path.
	 * @param array  $args          Template arguments.
	 * @return string
	 */
	public function ttisi_override_the_group_list_template( $template_path, $args ) {

		if ( false !== strpos( $template_path, 'ldgr-group-users-select-wrapper.template.php' ) ) {
			$template_path = MI_PUBLIC_PATH . 'partials/ld-group-registration/ldgr-group-users-select-wrapper.template.php';
		}

		if ( false !== strpos( $template_path, 'ldgr-group-users-tabs.template.php' ) ) {
			$template_path = MI_PUBLIC_PATH . 'partials/ld-group-registration/ldgr-group-users-tabs.template.php';
		}

		return $template_path;
	}

	/**
	 * Filter group email headers
	 *
	 * @param string $headers Headers of the email to be sent.
	 * @param array  $extra_data     Additional information related to the emails to be sent.
	 * @return string
	 */
	public function mi_assessment_set_ldgr_group_email_headers( $headers, $extra_data ) {

		$user_data  = wp_get_current_user();
		$headers[0] = 'Reply-To: ' . $user_data->data->display_name . ' <' . $user_data->data->user_email . '>';

		return $headers;

	}

	/**
	 * Filter tab headers on the groups dashboard.
	 *
	 * @param array $tab_headers    Array of tab headers.
	 * @param int   $group_id       ID of the group.
	 * @return array
	 */
	public function mi_assessments_group_registration_tab_headers( $tab_headers, $group_id ) {

		wp_enqueue_style( 'mi-assessments-learndash-group-registration' );
		wp_enqueue_style( 'mi-assessments-sweetalert' );
		wp_enqueue_script( 'mi-assessments-sweetalert' );
		wp_enqueue_script( 'mi-assessments-learndash-group-registration' );

		$is_assessment_group = is_group_has_assessment_shortcode( $group_id );

		foreach ( $tab_headers as $key => $value ) {

			if ( 'Enrolled Users' === $value['title'] ) {
				$tab_headers[ $key ]['title'] = 'People Enrolled';

				// if assessment related group.
				if ( $is_assessment_group ) {
					$tab_headers[ $key ]['slug'] = 'tti_platform_retake_assessment';
				}
			}

			if ( 'Report' === $value['title'] ) {
				$tab_headers[ $key ]['title'] = 'Progress';
			}
		}

		// Settings Tab.
		$tab_headers[] = array(
			'title' => __( 'Settings', 'tti-platform' ),
			'slug'  => 'tti_platform_group_settings',
			'id'    => 4,
			'icon'  => '',
		);

		usort(
			$tab_headers,
			function( $a, $b ) {
				return $a['id'] <=> $b['id'];
			}
		);

		return $tab_headers;
	}

	/**
	 * Filter tab contents on the groups dashboard.
	 *
	 * @param array $tab_contents   Array of tab contents.
	 * @param int   $group_id       ID of the group.
	 * @return array
	 */
	public function mi_assessments_group_registration_tab_contents( $tab_contents, $group_id ) {

		$is_assessment_group = is_group_has_assessment_shortcode( $group_id );

		foreach ( $tab_contents as $key => $value ) {

			$tab_contents[ $key ]['active'] = false;

			// Enrolled User tab.
			if ( false !== strpos( $value['template'], 'enrolled-users-tab.template.php' ) ) {

				$tab_contents[ $key ]['active'] = true;
				// if assessment related group - update the template.
				if ( $is_assessment_group ) {
					$tab_contents[ $key ]['template'] = MI_PUBLIC_PATH . 'partials/ld-group-registration/group-leader-tab.template.php';
				}
			}
		}

		// Settings Tab.
		$tab_contents[] = array(
			'id'       => 4,
			'active'   => false,
			'template' => MI_PUBLIC_PATH . 'partials/ld-group-registration/group-leader-tab-settings.template.php',
		);

		usort(
			$tab_contents,
			function( $a, $b ) {
				return $a['id'] <=> $b['id'];
			}
		);

		return $tab_contents;
	}

	/**
	 * Filter sub-group update results
	 *
	 * @param array $result     Array of sub group update details.
	 * @param array $post  Post data submitted to update sub-group.
	 * @return array
	 */
	public function set_sub_group_leader_email_settings( $result, $post ) {

		// Let's make all selected leaders as group leaders.
		if ( ! empty( $post['groupLeaders'] ) ) {

			foreach ( $post['groupLeaders'] as $sub_group_leader_id ) {
				update_user_meta( $sub_group_leader_id, 'group_user_' . $sub_group_leader_id . '_settings', 'false' );
			}
		}
		return $result;
	}

	/**
	 * Function to retaking assessment ajax action process.
	 *
	 * @since    1.5.1
	 */
	public function set_group_leader_email_settings() {

		// check nonce.
		check_ajax_referer( 'mi-assessments-ldgr', 'nonce' );

		if ( isset( $_POST['block_email'] ) ) {
			$block_email = sanitize_text_field( wp_unslash( $_POST['block_email'] ) );
		}

		if ( isset( $_POST['group_id'] ) ) {
			$group_id = sanitize_text_field( wp_unslash( $_POST['group_id'] ) );
		}

		if ( isset( $_POST['group_leader_id'] ) ) {
			$group_leader_id = sanitize_text_field( wp_unslash( $_POST['group_leader_id'] ) );
		}

		$key = 'group_user_' . $group_leader_id . '_settings';

		update_user_meta( $group_leader_id, $key, $block_email );

		$resp = array(
			'status' => 1,
		);

		echo wp_json_encode( $resp );

		exit;
	}

	/**
	 * Filter the group enrollment email to user base on group settings.
	 *
	 * @param boolean $status Send enrollment email to enrolled user or not.
	 * @param int     $group_id Group ID, in which user is getting enrolled.
	 * @return boolean
	 */
	public function mi_assessments_group_enrollment_email_status( $status, $group_id ) {

		$status          = true;
		$group_leader_id = get_current_user_id();

		// update user assessment assigned time.
		$assess_id = get_transient( 'group_dashboard_assess_id_' . $group_id );

		if ( isset( $assess_id[0] ) ) {

			foreach ( $_POST['wdm_members_email'] as $key => $user_email ) { // phpcs:ignore
				$user    = get_user_by( 'email', $user_email );
				$user_id = $user->ID;
				update_user_meta( $user_id, 'assigned_group_' . $group_id . '_' . $group_leader_id . '_' . $assess_id[0], time(), true );
			}
		}
		// update user assessment assigned status ends.
		$keys = 'group_user_' . $group_leader_id . '_settings';

		$sett_email_block = get_user_meta( $group_leader_id, $keys, true );

		if ( 'true' === $sett_email_block ) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Change Individual Label
	 *
	 * @param string $label Label for "Individual" radio button.
	 * @return string
	 */
	public function mi_update_wdm_gr_single_label( $label ) {
		return __( 'Take Myself', 'wdm_ld_group' );
	}

	/**
	 * Change Group Label
	 *
	 * @param string $label Label for "Group" radio button.
	 * @return string
	 */
	public function mi_update_wdm_gr_group_label( $label ) {
		return __( 'Assign to Others', 'wdm_ld_group' );
	}

}
