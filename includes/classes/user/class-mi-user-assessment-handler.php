<?php
/**
 * Class contains user assessment related functions
 *
 * @link       https://ministryinsights.com/
 * @since      2.0.0
 *
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 */

/**
 *
 * This class is used to define main user related functionality in WordPress admin user's profile.
 *
 * @since      2.0.0
 * @package    Mi_Assessments/includes
 * @subpackage Mi_Assessments/includes/classes
 * @author     Ministry Insights <support@ministryinsights.com>
 */
class Mi_User_Assessment_Handler {

	/**
	 * Function to show edit user assessment form
	 *
	 * @since   1.7.0
	 *
	 * @param int $user_id Contain current user id.
	 *
	 * @return void
	 */
	public function tti_show_edit_user_form( $user_id ) {

		$link_id = isset( $_GET['link_id'] ) ? sanitize_text_field( $_GET['link_id'] ) : false; // phpcs:ignore

		if ( $link_id ) {

			$list_data = $this->tti_return_assessments_by_link_id( $link_id, $user_id );

			require_once MI_ADMIN_PATH . 'partials/user/mi-user-level-assessments-edit.php';

		}

	}

	/**
	 * Function to perform delete user action
	 *
	 * @since   1.7.0
	 *
	 * @param int $user_id Contain current user id.
	 *
	 * @return boolean
	 */
	public function tti_delete_user_assessment( $user_id ) {

		$link_id = isset( $_GET['link_id'] ) ? sanitize_text_field( $_GET['link_id'] ) : false; // phpcs:ignore

		if ( $link_id ) {

			$lists = get_user_meta( $user_id, 'user_assessment_data', true );

			$lists = unserialize( $lists ); // phpcs:ignore

			if ( count( $lists ) >= 1 ) {

				unset( $lists[ $link_id ] );

				if ( count( $lists ) >= 1 ) {
					update_user_meta( $user_id, 'user_assessment_data', serialize( $lists ) ); // phpcs:ignore
				} else {
					delete_user_meta( $user_id, 'user_assessment_data' ); // delete the meta data in case nothing assessment remains.
				}

				return true;

			}
		}

		return false;
	}

	/**
	 * Function to return assessments by user id
	 *
	 * @since   1.7.0
	 *
	 * @param int $user_id Contain current user id.
	 *
	 * @return boolean
	 */
	public function tti_return_assessments_curr_user( $user_id ) {

		$lists = get_user_meta( $user_id, 'user_assessment_data', true );

		if ( isset( $lists ) && ! empty( $lists ) && count( unserialize( $lists ) ) >= 1 ) { // phpcs:ignore
			return unserialize( $lists ); // phpcs:ignore
		}

		return false;
	}

	/**
	 * Function to return assessments settings by user id
	 *
	 * @since   1.7.0
	 * @param int $user_id Contain current user id.
	 *
	 * @return boolean
	 */
	public function tti_return_assessments_settings( $user_id ) {

		$settings_data = get_user_meta( $user_id, 'user_assessment_settings', true );

		if ( isset( $settings_data ) && ! empty( $settings_data ) && count( unserialize( $settings_data ) ) >= 1 ) { // phpcs:ignore
			return unserialize( $settings_data ); // phpcs:ignore
		}

		return false;
	}

	/**
	 * Function to return assessments by user id
	 *
	 * @since   1.7.0
	 * @param string $link_id Contain assessment link id.
	 * @param int    $user_id Contain current user id.
	 *
	 * @return boolean
	 */
	public function tti_return_assessments_by_link_id( $link_id, $user_id ) {

		$lists = get_user_meta( $user_id, 'user_assessment_data', true );

		if ( isset( $lists ) && ! empty( $lists ) && count( unserialize( $lists ) ) >= 1 ) { // phpcs:ignore
			$lists = unserialize( $lists ); // phpcs:ignore
			return $lists[ $link_id ];
		}

		return false;
	}

}
