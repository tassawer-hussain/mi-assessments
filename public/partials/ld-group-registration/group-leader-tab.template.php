<?php
/**
 * Enrolled users tab contents display template (Procedural Programming)
 *
 * @since      1.6
 * @package    TTI_Platform
 * @subpackage TTI_Platform/includes
 * @author     Presstigers
 */

$counter        = 0;
$assess_names   = array();
$assessment_ids = array();

// Get link ids, assessments ids and enrolled course related to current group.
list($links_id, $assess_ids, $enrolled_course_id) = get_link_assessment_enrolled_course_ids_from_group_id( $group_id );

if ( isset( $links_id ) && count( $links_id ) > 0 ) {
	$links_id   = array_unique( $links_id );
	$assess_ids = array_unique( $assess_ids );
}

set_transient( 'group_dashboard_assess_id_' . $group_id, $assess_ids, DAY_IN_SECONDS );

?>
<div id="tab-1" class="tab-content current">

	<?php
	/*
	<input type='button' id='bulk_remove' value='<?php esc_html_e( 'Bulk Remove', 'tti-platform' ); ?>'>
	*/
	?>

	<table id='tti_group_leader_retake' class="display responsive nowrap">

		<thead>
			<?php if ( isset( $links_id ) && ! empty( $links_id ) ) { ?>
			<tr>
				<th><?php esc_html_e( 'Name', 'tti-platform' ); ?></th> 						<!-- # 2 -->
				<th><?php esc_html_e( 'Email', 'tti-platform' ); ?></th> 						<!-- # 3 -->
				<th><?php esc_html_e( 'Date Assigned', 'tti-platform' ); ?></th> 				<!-- # 4 -->
				<th><?php esc_html_e( 'Date Completed', 'tti-platform' ); ?></th> 				<!-- # 5 -->
				<th><?php esc_html_e( 'Report Details', 'tti-platform' ); ?></th> 				<!-- # 8 -->
				<th><?php esc_html_e( 'Action', 'tti-platform' ); ?></th> 						<!-- # 10 -->
				<?php
				/*
				<th><input type="checkbox" name="select_all" class="bb-custom-check"></th> 		<!-- # 1 -->
				<th><?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?></th> 			<!-- # 6 -->
				<th><?php esc_html_e( 'Users Limit', 'tti-platform' ); ?></th> 					<!-- # 7 -->
				<th><?php esc_html_e( 'Status', 'tti-platform' ); ?></th> 						<!-- # 9 -->
				*/
				?>
			</tr>
			<?php } else { ?>
			<tr>
				<!-- <th><input type="checkbox" name="select_all" class="bb-custom-check"></th> -->
				<th><?php esc_html_e( 'Name', 'wdm_ld_group' ); ?></th>
				<th><?php esc_html_e( 'Email', 'wdm_ld_group' ); ?></th>
				<th><?php esc_html_e( 'Action', 'wdm_ld_group' ); ?></th>
			</tr>
			<?php } ?>
		</thead>

		<tbody>
			<?php
			if ( ! empty( $users ) ) {
				$default                     = array( 'requests' => array() );
				$removal_request['requests'] = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );
				$removal_request             = array_filter( $removal_request );
				$removal_request             = wp_parse_args( $removal_request, $default );
				$removal_request             = $removal_request['requests'];

				$ldgr_reinvite_user  = get_option( 'ldgr_reinvite_user' );
				$reinvite_class_data = 'wdm-reinvite';
				$reinvite_text_data  = apply_filters( 'wdm_change_reinvite_label', __( 'Re-Invite', 'tti-platform' ) );

				$new_users_arr = array();

				if ( isset( $links_id ) && ! empty( $links_id ) ) {

					foreach ( $links_id as $key => $value ) {
						$new_users_arr[ $value ]  = $users;
						$assess_names[ $value ]   = get_the_title( $assess_ids[ $key ] );
						$assessment_ids[ $value ] = $assess_ids[ $key ];
					}

					$counter2 = 0;
					foreach ( $new_users_arr as $assess_link_id => $values ) { // $value contains user id

						foreach ( $values as $k => $value ) {

							$group_leade_report = get_post_meta( $assessment_ids[ $assess_link_id ], 'send_rep_group_lead', true );

							if ( ! in_array( $value, $removal_request ) ) { // phpcs:ignore
								$class_data = 'wdm_remove';
								$text_data  = __( 'Remove', 'tti-platform' );
							} else {
								$class_data = 'request_sent';
								$text_data  = __( 'Request sent', 'tti-platform' );
							}

							$user_data = get_user_by( 'id', $value );

							$counter++;
							?>
							<tr id="userid-<?php echo esc_attr( $value . '-' . $assess_link_id ); ?>">
								<?php
								/*
								<td class="select_action">
									<input
										type="checkbox"
										name="bulk_select"
										data-user_id ="<?php echo esc_attr( $value ); ?>"
										data-group_id="<?php echo esc_attr( $group_id ); ?>">
								</td>
								*/
								?>
								<td data-title="Name">
									<?php
										echo esc_html( get_user_meta( $value, 'first_name', true ) . ' ' . get_user_meta( $value, 'last_name', true ) );
									?>
								</td>
								<td data-title="Email">
									<?php echo esc_html( $user_data->user_email ); ?>
								</td>
								<td data-title="Enrolled Date">
									<?php
									$enrolled_date = get_user_meta( $value, 'course_' . $enrolled_course_id . '_access_from', true );
									if ( ( empty( $enrolled_date ) || ! isset( $enrolled_date ) )
										&& function_exists( 'learndash_user_group_enrolled_to_course_from' )
									) {
										/** If the user registered AFTER the course was enrolled into the group then we use the user registration date. */
										$enrolled_date = learndash_user_group_enrolled_to_course_from( $value, $enrolled_course_id );

										if ( empty( $enrolled_date ) ) {
											$enrolled_date = strtotime( $user_data->user_registered );
										}
									} else {
										$enrolled_date = strtotime( $user_data->user_registered );
									}
									echo esc_html( gmdate( 'M j, Y', $enrolled_date ) );
									?>
								</td>
								<td data-title="Report Date">
									<?php
									$pdf_version = mi_get_completed_assessment_counts_by_user( $value, $assess_link_id );
									if ( $pdf_version ) {
										$assess_date = mi_get_current_assessment_create_date_by_user( $value, $assess_link_id, $pdf_version );
										$assess_date = new DateTime( $assess_date );
										$report_date = $assess_date->format( 'M j, Y' );
										echo esc_html( $report_date );
									} else {
										echo '-';
									}
									?>
								</td>
								<?php
								/*
								<td class="ldgr-actions">
									<button
										type="button"
										id="tti_retake_assessment_<?php echo esc_attr( $value ); ?>"
										class="tti_retake_assessment"
										data-link_id="<?php echo esc_attr( $assess_link_id ); ?>"
										data-user_id="<?php echo esc_attr( $value ); ?>"
										data-group_id="<?php echo esc_attr( $group_id ); ?>"
										data-mail="<?php echo esc_attr( $user_data->user_email ); ?>">
										<?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?>
									</button>
									<img src="<?php echo esc_url( MI_PUBLIC_URL . 'images/ttisi-spinner.gif' ); ?>" id="retake_assessment_loader_<?php echo esc_attr( $value ); ?>" class="retake_assessment_loader" style="display: none"/>
								</td>
								*/
								?>
								<td class="ldgr-pdf-details ldgr-user-limit">
									<?php
									// $value contains current user id.
									$ass_comp_re = mi_check_is_current_user_completed_assessment( $value, $assess_link_id );
									if ( $ass_comp_re ) {
										echo '<small class="assess-complete-text ttisi-info-gp ttisi-info-success-color">Assessment Completed</small>';
										if ( 'QuickStrengths' === $assess_names[ $assess_link_id ] ) {
											$course_complete_date = learndash_user_get_course_completed_date( $value, (int) $enrolled_course_id );
											if ( $course_complete_date ) {
												echo '<span><span class="ttisi-info-blue-color ttisi-info-gp ttisi-pos-rel-tp-6-minus">' . esc_html__( 'Course Completed', 'tti-platform' ) . '</span></span>';
											} else {
												echo '<span><span class="logged-in ttisi-info-gp ttisi-info-logged-color">' . esc_html__( 'Course Not Completed', 'tti-platform' ) . '</span></span>';
											}
										}
									} else {
										echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">' . esc_html__( 'Assessment Not Completed', 'tti-platform' ) . '</small>';
										// Check if user logged in or not.
										$user_last_login = mi_user_last_login_time( $value );
										if ( $user_last_login ) {
											echo '<small class="logged-in ttisi-info-gp ttisi-info-logged-color">' . esc_html__( 'User Only Logged In', 'tti-platform' ) . '</small>';
										}
									}
									?>
									<span><?php echo '<span class="ttisi-info-blue-color ttisi-info-gp ttisi-pos-rel-tp-6-minus">' . esc_html( $assess_names[ $assess_link_id ] ) . '</span>'; ?></span>
									<?php
									if ( strtolower( $group_leade_report ) === 'yes' ) {
										if ( $pdf_version ) {
											$pdf_download_link = get_site_url() . '?cp_page=true&user_id=' . $value . '&assessment_id=' . $assessment_ids[ $assess_link_id ] . '&version=' . $pdf_version . '&group_leader=true';
											?>
											<a target="_blank"
												id="tti_cp_download_btn"
												data-email="<?php echo esc_attr( $user_data->user_email ); ?>"
												data-assess="<?php echo esc_attr( $assessment_ids[ $assess_link_id ] ); ?>"
												href="<?php echo esc_url( $pdf_download_link ); ?>">
												<u>
													<img class="tti-gp-column" src="<?php echo esc_url( MI_PUBLIC_URL . 'images/download.png' ); ?>" />
													<?php esc_html_e( 'Download Latest PDF', 'tti-platform' ); ?>
												</u>
											</a>
											<sub><strong style="font-size: 9px;">(<?php esc_html_e( 'Version', 'tti-platform' ); ?> : <?php echo esc_html( $pdf_version . '.0' ); ?>)</strong></sub>
											<?php
										} else {
											?>
											<span class="ttisi-info-gp"><?php esc_html_e( 'No PDF Available', 'tti-platform' ); ?></span>
											<?php
										}
									}
									?>
									<!-- Show user limits -->
									<small class="ttisi-info-gp ttisi-info-limit-color ttisi-info-limit ttisi-pos-rel-tp-6">
									<?php echo esc_html__( 'Retake Limit', 'tti-platform' ) . ' : ' . esc_html( get_user_limits( $value, $group_id, $assess_link_id ) ); ?>
									</small>
								</td>
								<td class="ldgr-actions">
									<span  style="overflow: visible; position: relative; width: 80px;">
										<div class="dropdown">
											<ul class="kt-nav">
												<li class="kt-nav__item">
													<a class="kt-nav__link" href="#">
														<span class="kt-nav__link-text">
															<span 
																type="button"
																id="tti_retake_assessment_<?php echo esc_attr( $value ); ?>" 
																class="tti_retake_assessment" 
																data-link_id="<?php echo esc_attr( $assess_link_id ); ?>" 
																data-user_id="<?php echo esc_attr( $value ); ?>" 
																data-group_id="<?php echo esc_attr( $group_id ); ?>"
																data-reg-left="<?php echo esc_attr( get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true ) ); ?>"
																data-group_leader_id="<?php echo esc_attr( get_current_user_id() ); ?>" 
																data-mail="<?php echo esc_attr( $user_data->user_email ); ?>">
																<?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?>
															</span>
														</span>
													</a>
												</li>
												<?php if ( ! $ass_comp_re && ! $user_last_login && 'on' === $ldgr_reinvite_user ) { ?>
													<li class="kt-nav__item">
														<span
															href="#"
															data-user_id ="<?php echo esc_html( $value ); ?>"
															data-group_id="<?php echo esc_html( $group_id ); ?>"
															class="<?php echo esc_html( $reinvite_class_data ); ?> button">
															<?php echo esc_html( $reinvite_text_data ); ?>
														</span>&nbsp;
													</li>
												<?php } ?>
												<?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
													<li class="kt-nav__item"> 
														<?php if ( 'wdm_remove' === $class_data ) : ?>
															<span
																data-assessment-status = "<?php echo ( $ass_comp_re ) ? 'complete' : 'not-complete'; ?>"
																data-group-type = "assessment"
																class="tti-user-removal button">
																<?php echo esc_html( $text_data ); ?>
															</span>
														<?php endif; ?> 
														<span
															<?php echo ( 'wdm_remove' === $class_data ) ? 'style="display: none;"' : ''; ?>
															href="#"
															data-user_id ="<?php echo esc_html( $value ); ?>"
															data-group_id="<?php echo esc_html( $group_id ); ?>"
															data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr_nonce_remove_user' ) ); ?>"
															class="ldgr-user-removal <?php echo esc_html( $class_data ); ?> button">
															<?php echo esc_html( $text_data ); ?>
														</span>
													</li>
												<?php } ?>
											</ul>
										</div>
									</span>
								</td>
							</tr>
							<?php
							$counter2++;
						}
					}
				} else {
					foreach ( $users as $k => $value ) {
						$user_data = get_user_by( 'id', $value );

						if ( ! in_array( $value, $removal_request ) ) { // phpcs:ignore
							$class_data = 'wdm_remove';
							$text_data  = __( 'Remove', 'wdm_ld_group' );
						} else {
							$class_data = 'request_sent';
							$text_data  = __( 'Request sent', 'wdm_ld_group' );
						}
						?>
						<tr>
							<td class="select_action">
								<input
									type="checkbox"
									name="bulk_select"
									data-user_id="<?php echo esc_attr( $value ); ?>"
									data-group_id="<?php echo esc_attr( $group_id ); ?>">
							</td>
							<td data-title="Name">
								<p><?php echo esc_html( get_user_meta( $value, 'first_name', true ) . ' ' . get_user_meta( $value, 'last_name', true ) ); ?></p>
							</td>
							<td data-title="Email">
								<p><?php echo esc_html( $user_data->user_email ); ?></p>
							</td>
							<td class="ldgr-actions">
								<?php if ( 'on' === $ldgr_reinvite_user ) { ?>
								<a
									href="#"
									data-user_id="<?php echo esc_attr( $value ); ?>"
									data-group_id="<?php echo esc_attr( $group_id ); ?>"
									class="<?php echo esc_attr( $reinvite_class_data ); ?> button">
									<?php echo esc_html( $reinvite_text_data ); ?>
								</a>&nbsp;
								<?php } ?>
								<?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
									<?php
									if ( 'wdm_remove' === $class_data ) :
										$course_id     = get_the_courses_id_by_group_id( $group_id );
										$course_status = learndash_user_get_course_progress( $value, $course_id[0] );
										?>
										<a
											href="#"
											data-assessment-status="<?php echo esc_attr( $course_status['status'] ); ?>"
											data-group-type="course"
											class="tti-user-removal button">
											<?php echo esc_html( $text_data ); ?>
										</a>
									<?php endif; ?>
									<a
										<?php echo ( 'wdm_remove' === $class_data ) ? 'style="display: none;"' : ''; ?>
										href="#"
										data-user_id="<?php echo esc_attr( $value ); ?>"
										data-group_id="<?php echo esc_attr( $group_id ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr_nonce_remove_user' ) ); ?>"
										class="<?php echo esc_attr( $class_data ); ?> button">
										<?php echo esc_html( $text_data ); ?>
									</a>
								<?php } ?>
								<?php do_action( 'ldgr_group_row_action', $value, $group_id ); ?>
							</td>
						</tr>
						<?php
					}
				}
			}
			?>
		</tbody>
	</table>
</div>

</form><!-- End of first Tab  -->
